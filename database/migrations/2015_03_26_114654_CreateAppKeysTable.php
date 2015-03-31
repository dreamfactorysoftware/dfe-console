<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppKeysTable extends Migration
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
        \Schema::create(
            'app_key_t',
            function ( Blueprint $table )
            {
                $table->increments( 'id' );
                $table->string( 'client_id', 128 )->unique();
                $table->string( 'client_secret', 128 );
                $table->integer( 'owner_id' );
                $table->integer( 'owner_type_nbr' );
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop( 'app_key_t' );
    }

}