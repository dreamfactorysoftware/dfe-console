<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFailedJobsTable extends Migration
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!\Schema::hasTable('job_fail_t')) {
            \Schema::create(
                'job_fail_t',
                function (Blueprint $table){
                    $table->increments('id');
                    $table->text('connection');
                    $table->text('queue');
                    $table->text('payload');
                    $table->timestamp('failed_at');
                }
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('job_fail_t');
    }

}
