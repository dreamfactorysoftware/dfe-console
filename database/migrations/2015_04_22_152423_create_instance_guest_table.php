<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInstanceGuestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!\Schema::hasTable('instance_guest_t')) {
            Schema::create('instance_guest_t',
                function (Blueprint $table){
                    $table->increments('id');
                    $table->integer('instance_id')->index();
                    $table->integer('vendor_id')->index();
                    $table->integer('vendor_image_id')->index();
                    $table->integer('vendor_credentials_id')->nullable()->index();
                    $table->integer('flavor_nbr');
                    $table->string('base_image_text', 32)->default('dfe.standard');
                    $table->string('region_text', 32);
                    $table->string('availability_zone_text', 32);
                    $table->string('security_group_text', 1024);
                    $table->string('ssh_key_text', 64);
                    $table->integer('root_device_type_nbr')->default(0);
                    $table->string('public_host_text', 256);
                    $table->string('public_ip_text', 20);
                    $table->string('private_host_text', 256);
                    $table->string('private_ip_text', 20);
                    $table->integer('state_nbr')->default(0);
                    $table->string('state_text', 64)->nullable();
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
        if (\Schema::hasTable('instance_guest_t')) {
            Schema::drop('instance_guest_t');
        }
    }

}
