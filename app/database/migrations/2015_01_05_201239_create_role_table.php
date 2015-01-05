<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists( 'role_t' );

        Schema::create(
            'role_t',
            function ( Blueprint $table )
            {
                $table->increments( 'id' );
                $table->string( 'role_name_text', 64 )->unique();
                $table->text( 'description_text' );
                $table->tinyInteger( 'active_ind' );
                $table->string( 'home_view_text' );
                $table->timestamp( 'create_date' );
                $table->timestamp( 'lmod_date' );
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
        Schema::drop( 'role_t' );
    }
}
