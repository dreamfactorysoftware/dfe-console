<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Run the database seeds.
     */
    public function run()
    {
        Model::unguard();
        //$this->call('LookupSeeder');
    }

}
