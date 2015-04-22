<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSnapshotTable extends Migration
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
        if ( !\Schema::hasTable( 'snapshot_t' ) )
        {
            \Schema::create(
                'snapshot_t',
                function ( Blueprint $table )
                {
                    $table->bigIncrements( 'id' );
                    $table->string( 'snapshot_id_text', 128 );
                    $table->integer( 'user_id' )->index();
                    $table->integer( 'instance_id' )->index();
                    $table->string( 'url_text', 1024 );
                    $table->dateTime( 'expire_date' );
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
        if ( \Schema::hasTable( 'snapshot_t' ) )
        {
            \Schema::drop( 'snapshot_t' );
        }
    }

}
