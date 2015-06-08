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
                    $table->integer( 'user_id' )->index();
                    $table->integer( 'instance_id' )->index();
                    $table->integer( 'route_hash_id' )->index();
                    $table->string( 'snapshot_id_text', 128 )->index()->nullable();
                    $table->boolean( 'public_ind' )->default( true );
                    $table->string( 'public_url_text', 1024 );
                    $table->dateTime( 'expire_date' );

                    $table->dateTime( 'create_date' );
                    $table->timestamp( 'lmod_date' )->default( \DB::raw( 'CURRENT_TIMESTAMP' ) );

                    //  Make snapshot ID unique for user
                    $table->unique( ['user_id', 'snapshot_id_text'] );

                    //  Foreign keys
                    $table->foreign( 'user_id' )->references( 'id' )->on( 'user_t' )->onDelete( 'cascade' );
                    $table->foreign( 'instance_id' )->references( 'id' )->on( 'instance_t' )->onDelete( 'cascade' );
                    $table->foreign( 'route_hash_id' )->references( 'id' )->on( 'route_hash_t' )->onDelete( 'cascade' );
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
