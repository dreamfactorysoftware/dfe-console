<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMetricsDetailTable extends Migration
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function up()
    {
        if (!\Schema::hasTable('metrics_detail_t')) {
            Schema::create('metrics_detail_t',
                function(Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->integer('user_id')->index('ix_metrics_detail_user_id');
                    $table->integer('instance_id')->index('ix_metrics_detail_instance_id');
                    $table->date('gather_date');
                    $table->mediumText('data_text');
                    $table->timestamps();

                    //  Foreign keys
                    $table->foreign('user_id', 'fk_metrics_detail_user_id')->references('id')->on('user_t')->onDelete('cascade');
                    $table->foreign('instance_id', 'fk_metrics_detail_instance_id')->references('id')->on('instance_t')->onDelete('cascade');
                });
        }
    }

    /** @inheritdoc */
    public function down()
    {
        if (\Schema::hasTable('metrics_detail_t')) {
            Schema::drop('metrics_detail_t');
        }
    }
}
