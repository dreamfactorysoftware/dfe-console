<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClusterLimitTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!\Schema::hasTable('limit_t')) {
            \Schema::create('limit_t',
                function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->integer('cluster_id')->index();
                    $table->integer('instance_id')->index();
                    $table->string('limit_key_text', 200)->index();
                    $table->integer('period_nbr')->nullable();;
                    $table->integer('limit_nbr')->nullable();
                    $table->string('label_text', 64);
                    $table->boolean('active_ind')->default(1);
                    $table->dateTime('create_date');
                    $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));

                    //  Indices
                    $table->foreign('cluster_id', 'fk_limit_cluster_id')->references('id')->on('cluster_t')->onDelete('cascade');
                    $table->foreign('instance_id', 'fk_limit_instance_id')->references('id')->on('instance_t')->onDelete('cascade');
                    $table->unique(['cluster_id', 'instance_id', 'limit_key_text'], 'ux_limit_cluster_instance_key');
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
        if (\Schema::hasTable('limit_t')) {
            \Schema::drop('limit_t');
        }
    }

}
