<?php namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\JobCommand;

class NotifyJob extends JobCommand
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
    protected $_view;
    /**
     * @type array
     */
    protected $_data = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new command instance.
     *
     * @param string $view The name of the view for the email body
     * @param array  $data The data for the view
     */
    public function __construct( $view, $data = [] )
    {
        $this->_view = $view;
        $this->_data = $data;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @return string The handler class for this job if different from "[class-name]Handler"
     */
    public function getHandler()
    {
        return 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\NotifyHandler';
    }
}
