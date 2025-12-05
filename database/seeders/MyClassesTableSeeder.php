<?php
namespace Database\Seeders;

use App\Models\ClassType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class MyClassesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('my_classes')->delete();
        $ct = ClassType::pluck('id')->all();

        $data = [
            ['name' => 'Senior 1', 'class_type_id' => $ct[0]],
            ['name' => 'Senior 2', 'class_type_id' => $ct[0]],
            ['name' => 'Senior 3', 'class_type_id' => $ct[0]],
            ['name' => 'Senior 4', 'class_type_id' => $ct[0]],
            ['name' => 'Senior 5', 'class_type_id' => $ct[1]],
            ['name' => 'Senior 6', 'class_type_id' => $ct[1]],
            ];

        DB::table('my_classes')->insert($data);

    }
}
