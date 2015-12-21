<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLimitTypeToLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\Schema::hasTable('limit_t') && !\Schema::hasColumn('limit_t', 'limit_type_nbr'))
        {
            \Schema::table('limit_t', function (Blueprint $table) {
                $table->tinyInteger('limit_type_nbr')->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (\Schema::hasTable('limit_t') && \Schema::hasColumn('limit_t', 'limit_type_nbr'))
        {
            \Schema::table('limit_t', function (Blueprint $table) {
                $table->dropColumn('limit_type_nbr');
            });
        }
    }
}
