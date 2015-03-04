<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Deprovision extends Command implements ShouldBeQueued
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InteractsWithQueue, SerializesModels;

    //******************************************************************************
    //* Methods
    //******************************************************************************

}
