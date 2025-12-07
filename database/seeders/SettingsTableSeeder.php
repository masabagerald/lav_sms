<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->delete();

        $data = [
            ['type' => 'current_session', 'description' => '2026'],
            ['type' => 'system_title', 'description' => 'MPSS'],
            ['type' => 'system_name', 'description' => 'MUBENDE PARENTS SECONDARY SCHOOL'],
            ['type' => 'term_ends', 'description' => '1/1/2026'],
            ['type' => 'term_begins', 'description' => '1/5/2026'],
            ['type' => 'phone', 'description' => '0123456789'],
            ['type' => 'address', 'description' => 'MUBENDE MUNICIPALITY'],
            ['type' => 'system_email', 'description' => 'mpsschool@mpss.com'],
            ['type' => 'alt_email', 'description' => ''],
            ['type' => 'email_host', 'description' => ''],
            ['type' => 'email_pass', 'description' => ''],
            ['type' => 'lock_exam', 'description' => 0],
            ['type' => 'logo', 'description' => ''],
            ['type' => 'next_term_fees_o', 'description' => '20000'],
            ['type' => 'next_term_fees_a', 'description' => '25000'],
        ];

        DB::table('settings')->insert($data);

    }
}
