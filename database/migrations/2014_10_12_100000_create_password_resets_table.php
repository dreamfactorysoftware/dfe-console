<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePasswordResetsTable extends Migration
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
        if ( \Schema::hasTable( 'auth_reset_t' ) )
        {
            Schema::create(
                'auth_reset_t',
                function ( Blueprint $table )
                {
                    $table->string( 'email' )->index();
                    $table->string( 'token' )->index();
                    $table->timestamp( 'created_at' );
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
        if ( Schema::hasTable( 'auth_reset_t' ) )
        {
            Schema::drop( 'auth_reset_t' );
        }
    }

}
