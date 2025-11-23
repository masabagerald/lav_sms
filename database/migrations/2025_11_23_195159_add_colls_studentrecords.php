<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCollsStudentrecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_records', function (Blueprint $table) {
            $table->integer('upe_results')->nullable();
            $table->integer('uce_results')->nullable();
            $table->string('religion')->nullable();
            $table->string('guardian_name')->nullable();          
            $table->decimal('fees', 10, 2); // t
            $table->text('general_comments')->nullable();            
  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_records', function (Blueprint $table) {
            //
        });
    }
}
