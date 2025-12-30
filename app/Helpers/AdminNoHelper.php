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

            $lastStudent = DB::table('student_records')
                ->where('year_admitted', $year)
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = $lastStudent
                ? ((int) substr($lastStudent->adm_no, -$length) + 1)
                : 1;

            $sequence = str_pad($nextNumber, $length, '0', STR_PAD_LEFT);

            return strtoupper(sprintf(
                '%s/%s/%s/%s',
                \Qs::getAppCode(),
                $ct,
                $year,
                $sequence
            ));
        });
    }
}
