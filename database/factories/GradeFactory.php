<?php

namespace Database\Factories;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition()
    {
        return [
            'name'          => $this->faker->regexify('[A-F][1-9]'),
            'class_type_id' => null,
            'mark_from'     => 50,
            'mark_to'       => 64,
            'remark'        => 'Credit',
        ];
    }
}
