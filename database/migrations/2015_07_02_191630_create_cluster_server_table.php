<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClusterServerTable extends Migration
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
        if (!\Schema::hasTable('cluster_server_asgn_t')) {
            Schema::create('cluster_server_asgn_t',
                function (Blueprint $table) {
                    $table->bigIncrements('id')->unsigned();
                    $table->integer('cluster_id');
                    $table->integer('server_id');
                    $table->dateTime('create_date');
                    $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));

                    $table->unique(['cluster_id', 'server_id']);
                    $table->foreign('cluster_id')->references('id')->on('cluster_t');
                    $table->foreign('server_id')->references('id')->on('server_t');
                });
        }

        if (!\Schema::hasTable('cluster_server_asgn_arch_t')) {
            Schema::create('cluster_server_asgn_arch_t',
                function (Blueprint $table) {
                    $table->bigInteger('id');
                    $table->integer('cluster_id');
                    $table->integer('server_id');
                    $table->dateTime('create_date');
                    $table->timestamp('lmod_date');
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
        if (\Schema::hasTable('cluster_server_asgn_t')) {
            Schema::drop('cluster_server_asgn_t');
        }

        if (\Schema::hasTable('cluster_server_asgn_arch_t')) {
            Schema::drop('cluster_server_asgn_arch_t');
        }
    }
}
