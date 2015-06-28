<?php namespace DreamFactory\Enterprise\Services\Jobs;

use DreamFactory\Enterprise\Common\Jobs\BaseJob;

class NotifyJob extends BaseJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'email';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string $_view
     */
    protected $view;
    /**
     * @type array
     */
    protected $data = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new command instance.
     *
     * @param string $view The name of the view for the email body
     * @param array  $data The data for the view
     */
    public function __construct($view, $data = [])
    {
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
