<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class AdminNoHelper
{
    public static function generateAdmissionNo(
    string $ct,
    int $year,
    int $length = 4
): string {
    return DB::transaction(function () use ($ct, $year, $length) {

        $appCode = strtoupper(\Qs::getAppCode());
        $prefix  = sprintf('%s/%s/%s/', $appCode, strtoupper($ct), $year);
        $prefixLen = strlen($prefix);

        // ✅ Lock matching rows to prevent race conditions
        $lastSequence = DB::table('student_records')
            ->where('year_admitted', $year)
            ->where('adm_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->selectRaw(
                "MAX(CAST(SUBSTRING(adm_no, ?) AS UNSIGNED)) as max_seq",
                [$prefixLen + 1] // ✅ dynamic offset — safe for any sequence length
            )
            ->value('max_seq');

        $nextNumber = ($lastSequence ?? 0) + 1;

        // ✅ Warn if sequence exceeds fixed-length padding
        if ($nextNumber > pow(10, $length) - 1) {
            \Log::warning("Admission number sequence exceeding pad length {$length} for prefix {$prefix}");
        }

        $sequence = str_pad($nextNumber, $length, '0', STR_PAD_LEFT);
        $admNo    = $prefix . $sequence;

        // ✅ Final uniqueness guard
        $exists = DB::table('student_records')
            ->where('adm_no', $admNo)
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            throw new \RuntimeException(
                "Admission number collision detected: {$admNo}"
            );
        }

        return $admNo;

    }, 5); // ✅ Auto-retry up to 5 times on deadlock
}
}