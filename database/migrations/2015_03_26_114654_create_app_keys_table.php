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
        if ( !\Schema::hasTable( 'app_key_t' ) )
        {
            \Schema::create(
                'app_key_t',
                function ( Blueprint $table )
                {
                    $table->increments( 'id' );
                    $table->integer( 'owner_id' );
                    $table->integer( 'owner_type_nbr' );
                    $table->string( 'client_id', 128 )->unique();
                    $table->string( 'client_secret', 128 );
                    $table->string( 'server_secret', 128 );
                    $table->string( 'key_class_text', 64 )->index();
                    $table->dateTime( 'create_date' );
                    $table->timestamp( 'lmod_date' )->default( \DB::raw( 'CURRENT_TIMESTAMP' ) );
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
        if ( \Schema::hasTable( 'app_key_t' ) )
        {
            \Schema::drop( 'app_key_t' );
        }
    }

}
