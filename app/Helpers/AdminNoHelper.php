<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class AdminNoHelper
{
    /**
     * Generate a new sequential student username.
     *
     * @param string $ct       Class or category code
     * @param int    $year     Year admitted
     * @param int    $length   Sequence length (default 4)
     * @return string
     */
    public static function generateAdmissionNo($ct, $year, $length = 4)
{
    return DB::transaction(function () use ($ct, $year, $length) {

        $prefix = strtoupper(sprintf(
            '%s/%s/%s/',
            \Qs::getAppCode(),
            $ct,
            $year
        ));

        $lastSequence = DB::table('student_records')
            ->where('year_admitted', $year)
            ->where('adm_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->selectRaw("MAX(CAST(SUBSTRING(adm_no, -{$length}) AS UNSIGNED)) as max_seq")
            ->value('max_seq');

        $nextNumber = ($lastSequence ?? 0) + 1;

        $sequence = str_pad($nextNumber, $length, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    });
}

}
