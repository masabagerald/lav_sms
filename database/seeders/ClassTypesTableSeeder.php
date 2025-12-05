<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('class_types')->delete();

        $data = [
            ['name' => 'Ordinary Secondary', 'code' => 'O'],
            ['name' => 'Advanced Secondary', 'code' => 'A'],
        ];

        DB::table('class_types')->insert($data);

    }
}
