<?php
namespace Database\Seeders;

use App\Models\MyClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class SectionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('sections')->delete();
        $c = MyClass::pluck('id')->all();

        $data = [
            ['name' => 'S1K', 'my_class_id' => $c[0], 'active' => 1],
            ['name' => 'S1S', 'my_class_id' => $c[0], 'active' => 1],
            ['name' => 'S1P', 'my_class_id' => $c[0], 'active' => 1],
            ['name' => 'S1Q', 'my_class_id' => $c[0], 'active' => 1],
            ['name' => 'S2K', 'my_class_id' => $c[1], 'active' => 1],
            ['name' => 'S2S', 'my_class_id' => $c[1], 'active' => 1],
            ['name' => 'S2P', 'my_class_id' => $c[1], 'active' => 1],
            ['name' => 'S2Q', 'my_class_id' => $c[1], 'active' => 1],
            ['name' => 'S3K', 'my_class_id' => $c[2], 'active' => 1],
            ['name' => 'S3S', 'my_class_id' => $c[2], 'active' => 1],
            ['name' => 'S3P', 'my_class_id' => $c[2], 'active' => 1],
            ['name' => 'S3Q', 'my_class_id' => $c[2], 'active' => 1],
            ['name' => 'S4K', 'my_class_id' => $c[3], 'active' => 1],
            ['name' => 'S4S', 'my_class_id' => $c[3], 'active' => 1],
            ['name' => 'S4P', 'my_class_id' => $c[3], 'active' => 1],
            ['name' => 'S4Q', 'my_class_id' => $c[3], 'active' => 1],
            ['name' => 'S5K', 'my_class_id' => $c[4], 'active' => 1],
            ['name' => 'S5S', 'my_class_id' => $c[4], 'active' => 1],
            ['name' => 'S5P', 'my_class_id' => $c[4], 'active' => 1],
            ['name' => 'S5Q', 'my_class_id' => $c[4], 'active' => 1],
            ['name' => 'S6K', 'my_class_id' => $c[5], 'active' => 1],
            ['name' => 'S6S', 'my_class_id' => $c[5], 'active' => 1],
            ['name' => 'S6P', 'my_class_id' => $c[5], 'active' => 1],
            ['name' => 'S6Q', 'my_class_id' => $c[5], 'active' => 1],
        ];

        DB::table('sections')->insert($data);
    }
}
