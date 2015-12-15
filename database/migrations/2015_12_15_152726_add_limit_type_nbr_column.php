<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLimitTypeNbrColumn extends Migration
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Run the migration
     */
    public function up()
    {
        Schema::table('limit_t',
            function (Blueprint $table) {
                $table->integer('limit_type_nbr')->default(0);
            });
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        Schema::table('limit_t',
            function (Blueprint $table) {
                $table->dropColumn('limit_type_nbr');
            });
    }
}
