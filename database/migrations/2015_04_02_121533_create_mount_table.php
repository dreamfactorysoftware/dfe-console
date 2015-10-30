<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        !\Schema::hasTable('mount_t') && \Schema::create('mount_t',
            function (Blueprint $table){
                $table->increments('id');
                $table->integer('mount_type_nbr')->default(0);
                $table->string('mount_id_text', 64)->unique()->index();
                $table->string('root_path_text', 128)->nullable();
                $table->integer('owner_id')->nullable();
                $table->integer('owner_type_nbr')->nullable();
                $table->mediumText('config_text')->nullable();
                $table->dateTime('last_mount_date')->nullable();
                $table->dateTime('create_date');
                $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
            });

        !\Schema::hasColumn('server_t', 'mount_id') && \Schema::table('server_t',
            function (Blueprint $table){
                $table->integer('mount_id')->index()->nullable();
                $table->foreign('mount_id', 'fk_mount_server_id')->references('id')->on('mount_t');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::hasTable('mount_t') && \Schema::drop('mount_t');

        \Schema::hasColumn('server_t', 'mount_id') && \Schema::table('server_t',
            function (Blueprint $table){
                $table->dropForeign('fk_mount_server_id');
                $table->dropColumn('mount_id');
            });
    }
}
