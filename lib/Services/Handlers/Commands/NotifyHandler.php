<?php namespace DreamFactory\Enterprise\Services\Handlers\Commands;

use DreamFactory\Enterprise\Services\Commands\NotifyJob;
use Illuminate\Support\Facades\Mail;

class NotifyHandler
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param  NotifyJob $command
     */
    public function handle( NotifyJob $command )
    {
        return Mail::send( $command->getView(), $command->getData() );
    }

}
