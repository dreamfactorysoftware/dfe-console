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
            \Schema::create(
                'limit_t',
                function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->integer('cluster_id')->index();
                    $table->integer('instance_id')->index();
                    $table->string('limit_key_text');
                    $table->integer('limit_nbr')->nullable();
                    $table->integer('period_nbr')->nullable();;
                    $table->dateTime('create_date');
                    $table->string('label_text')->nullable();
                    $table->boolean('is_active')->default(1);
                    $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
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
