<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJobResultTable extends Migration
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('job_result_t')) {
            Schema::create('job_result_t',
                function (Blueprint $table){
                    $table->bigIncrements('id');
                    /** @noinspection PhpUndefinedMethodInspection */
                    $table->string('result_id_text', 256)->index();
                    $table->mediumText('result_text');
                    $table->dateTime('create_date');
                    $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasTable('job_result_t')) {
            Schema::drop('job_result_t');
        }
    }
}
