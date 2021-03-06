<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJobsTable extends Migration
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
        if (!\Schema::hasTable('job_t')) {
            \Schema::create('job_t',
                function (Blueprint $table){
                    $table->unsignedBigInteger('id', true);
                    $table->string('queue', 256);
                    $table->text('payload');
                    $table->tinyInteger('attempts')->unsigned();
                    $table->tinyInteger('reserved')->unsigned();
                    $table->unsignedInteger('reserved_at')->nullable();
                    $table->unsignedInteger('available_at');
                    $table->unsignedInteger('created_at');
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\Schema::hasTable('job_t')) {
            \Schema::drop('job_t');
        }
    }

}
