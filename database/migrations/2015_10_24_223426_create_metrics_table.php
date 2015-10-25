<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMetricsTable extends Migration
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
        if (!Schema::hasTable('metrics_t')) {
            Schema::create('metrics_t',
                function (Blueprint $table){
                    $table->bigIncrements('id');
                    $table->mediumText('metrics_data_text');
                    $table->tinyInteger('sent_ind')->default(0);
                    $table->dateTime('create_date');
                    $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
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
        if (Schema::hasTable('metrics_t')) {
            Schema::drop('metrics_t');
        }
    }
}
