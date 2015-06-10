<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use Symfony\Component\HttpFoundation\Request;

class AppController extends FactoryController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type Request */
    protected $_request = null;
    /** @type string */
    protected $_action = null;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->layout = 'layouts.main';
        $this->_request = Request::createFromGlobals();
    }

    /**
     * @param array $parameters
     *
     * @return \Illuminate\View\View|mixed
     */
    public function missingMethod($parameters = [])
    {
        if (!is_array($parameters)) {
            $parameters = [$parameters];
        }

        $this->_action = array_shift($parameters);

        $_viewName = 'app.' . $this->_action;
        $parameters['_trail'] = null;

        if (\View::exists($_viewName)) {
            //@todo remove this
            $parameters['_active'] = [
                'instances' => 0,
                'clusters'  => 0,
                'users'     => 0,
                'user'      => null,
            ];

            return \View::make($_viewName, $parameters);
        }

        //  Show 404
        try {
            return parent::missingMethod($parameters);
        } catch (\Exception $_ex) {
            return \View::make('app.404', ['_trail' => null]);
        }
    }

}