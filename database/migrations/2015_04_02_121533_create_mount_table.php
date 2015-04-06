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
        if ( !\Schema::hasTable( 'mount_t' ) )
        {
            \Schema::create(
                'mount_t',
                function ( Blueprint $table )
                {
                    $table->increments( 'id' );
                    $table->integer( 'mount_type_nbr' )->default( 0 );
                    $table->string( 'mount_id_text', 64 )->unique();
                    $table->integer( 'owner_id' )->default( 0 );
                    $table->string( 'root_path_text', 128 )->nullable();
                    $table->mediumText( 'config_text' )->nullable();
                    $table->dateTime( 'last_mount_date' )->nullable();
                    $table->dateTime( 'create_date' );
                    $table->timestamp( 'lmod_date' )->default( \DB::raw( 'CURRENT_TIMESTAMP' ) );
                }
            );
        }

        !\Schema::hasColumn( 'server_t', 'mount_id' ) && \Schema::table(
            'server_t',
            function ( Blueprint $table )
            {
                $table->integer( 'mount_id' )->nullable();
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
        if ( \Schema::hasTable( 'mount_t' ) )
        {
            Schema::drop( 'mount_t' );
        }
    }

}
