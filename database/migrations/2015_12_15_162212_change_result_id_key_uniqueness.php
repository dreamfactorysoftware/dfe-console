<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class ChangeResultIdKeyUniqueness extends Migration
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('job_result_t',
            function (Blueprint $table) {
                //  Remove old keys
                $_keys = \DB::select('SHOW KEYS FROM job_result_t WHERE KEY_NAME = :key_name', [':key_name' => 'ux_job_result_result_id',]);

                if (!empty($_keys)) {
                    $table->dropIndex('ux_job_result_result_id');
                }

                //  Remove old keys
                $_keys = \DB::select('SHOW KEYS FROM job_result_t WHERE KEY_NAME = :key_name', [':key_name' => 'ix_job_result_result_id',]);

                if (!empty($_keys)) {
                    $table->dropIndex('ix_job_result_result_id');
                }

                //  Add our new key
                $table->index('result_id_text', 'ix_job_result_result_id');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('job_result_t',
            function (Blueprint $table) {
                $_keys = \DB::select('SHOW KEYS FROM job_result_t WHERE KEY_NAME = :key_name', [':key_name' => 'ux_job_result_result_id',]);

                if (!empty($_keys)) {
                    $table->dropIndex('ux_job_result_result_id');
                }
            });
    }
}
