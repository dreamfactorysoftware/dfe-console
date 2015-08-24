<?php namespace DreamFactory\Enterprise\Console\Listeners;

use Illuminate\Http\Request;

/**
 * A base class for event handling
 */
class BaseEventHandler
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Request
     */
    protected $request;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
