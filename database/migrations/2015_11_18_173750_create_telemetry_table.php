<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTelemetryTable extends Migration
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('telemetry_t')) {
            Schema::create('telemetry_t',
                function (Blueprint $table){
                    $table->bigIncrements('id');
                    $table->string('provider_id_text')->index();
                    $table->dateTime('gather_date')->index();
                    $table->mediumText('data_text');
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
        if (Schema::hasTable('telemetry_t')) {
            Schema::drop('telemetry_t');
        }
    }
}
