<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Eloquent
{
    use HasFactory;

    protected $fillable = ['name', 'class_type_id', 'mark_from', 'mark_to', 'remark'];
}
