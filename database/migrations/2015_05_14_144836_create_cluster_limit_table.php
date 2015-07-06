<?php

use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClusterLimitTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!\Schema::hasTable('limit_t')) {
            \Schema::create(
                'limit_t',
                function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->integer('owner_id')->index();
                    $table->integer('owner_type_nbr')->default(OwnerTypes::INSTANCE)->index();
                    $table->mediumText('parameters_text')->nullable();
                    $table->dateTime('create_date');
                    $table->timestamp('lmod_date')->default(\DB::raw('CURRENT_TIMESTAMP'));
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
        if (\Schema::hasTable('limit_t')) {
            \Schema::drop('limit_t');
        }
    }

}
