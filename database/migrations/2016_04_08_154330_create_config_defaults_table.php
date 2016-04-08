<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigDefaultsTable extends Migration
{
    //******************************************************************************
    //* Methods
    //******************************************************************************
    
    /**
     * Create config_t
     */
    public function up()
    {
        !Schema::hasTable('config_t') && Schema::create('config_t',
            function(Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name_text', 64)->unique('ux_config_name_text');
                $table->text('value_text');
                $table->dateTime('create_date');
                $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
            });
    }

    /**
     * Drop config_t
     */
    public function down()
    {
        Schema::hasTable('config_t') && Schema::drop('config_t');
    }
}
