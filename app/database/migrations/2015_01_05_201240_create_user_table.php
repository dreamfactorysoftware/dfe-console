<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
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
        Schema::dropIfExists( 'user_t' );
        Schema::create(
            'user_t',
            function ( $table )
            {
                $table->increments( 'id' );
                $table->string( 'email_addr_text', 200 )->unique();
                $table->string( 'password_text', 200 );
                $table->string( 'first_name_text', 32 );
                $table->string( 'last_name_text', 32 );
                $table->string( 'nickname_text', 64 );
                $table->integer( 'external_id' );
                $table->string( 'external_password_text', 200 );
                $table->string( 'storage_id_text', 64 );
                $table->string( 'api_token_text', 128 );
                $table->integer( 'active_ind' );
                $table->integer( 'owner_id' )->unsigned()->index();
                $table->integer( 'owner_type_nbr' );
                $table->dateTime( 'last_login_date' );
                $table->string( 'last_login_ip_text' );
                $table->integer( 'role_id' )->unsigned()->index();
                $table->timestamp( 'create_date' );
                $table->timestamp( 'lmod_date' );

                $table->foreign( 'owner_id' )->references( 'id' )->on( 'user_t' )->onDelete( 'cascade' );
                $table->foreign( 'role_id' )->references( 'id' )->on( 'role_t' )->onDelete( 'cascade' );
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
        Schema::drop( 'user_t' );
    }

}
