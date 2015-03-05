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
        Schema::create(
            'auth_reset_t',
            function ( Blueprint $table )
            {
                $table->string( 'email_addr_text' )->index();
                $table->string( 'token_text' )->index();
                $table->timestamp( 'create_date' );
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
        Schema::drop( 'auth_reset_t' );
    }

}
