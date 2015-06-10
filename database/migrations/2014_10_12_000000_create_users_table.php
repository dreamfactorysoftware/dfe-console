<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!\Schema::hasTable('service_user_t')) {
            Schema::create(
                'service_user_t',
                function (Blueprint $table){
                    $table->increments('id');
                    $table->string('first_name_text', 64);
                    $table->string('last_name_text', 64);
                    $table->string('email_addr_text', 320)->unique();
                    $table->string('password_text', 200);
                    $table->integer('owner_id')->nullable();
                    $table->integer('owner_type_nbr')->nullable();
                    $table->dateTime('last_login_date')->nullable();
                    $table->string('last_login_ip_text', 64)->nullable();
                    $table->string('remember_token', 128)->nullable();
                    $table->tinyInteger('active_ind')->default(0);
                    $table->dateTime('create_date');
                    $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
                }
            );
        }

        if (!\Schema::hasTable('user_t')) {
            Schema::create(
                'service_user_t',
                function (Blueprint $table){
                    $table->increments('id');
                    $table->string('first_name_text', 64);
                    $table->string('last_name_text', 64);
                    $table->string('email_addr_text', 320)->unique();
                    $table->string('password_text', 200);
                    $table->integer('owner_id')->nullable();
                    $table->integer('owner_type_nbr')->nullable();
                    $table->dateTime('last_login_date')->nullable();
                    $table->string('last_login_ip_text', 64)->nullable();
                    $table->string('remember_token', 128)->nullable();
                    $table->tinyInteger('active_ind')->default(0);
                    $table->dateTime('create_date');
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
        if (\Schema::hasTable('service_user_t')) {
            Schema::drop('service_user_t');
        }

        if (\Schema::hasTable('user_t')) {
            Schema::drop('user_t');
        }
    }

}
