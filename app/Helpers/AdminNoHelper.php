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

            $prefix = strtoupper(sprintf(
                '%s/%s/%s/',
                \Qs::getAppCode(),
                $ct,
                $year
            ));

            // ✅ Lock the table row to prevent race conditions
            $lastSequence = DB::table('student_records')
                ->where('year_admitted', $year)
                ->where('adm_no', 'like', $prefix . '%')
                ->lockForUpdate()
                ->selectRaw(
                    "MAX(CAST(SUBSTRING(adm_no, -$length) AS UNSIGNED)) as max_seq"
                )
                ->value('max_seq');

            $nextNumber = ($lastSequence ?? 0) + 1;
            $sequence   = str_pad($nextNumber, $length, '0', STR_PAD_LEFT);
            $admNo      = $prefix . $sequence;

            // ✅ Double-check uniqueness before returning
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