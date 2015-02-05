<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;
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
        $this->layout = 'layouts.main';
        $this->_request = Request::createFromGlobals();
    }

    /**
     * @param array $parameters
     *
     * @return mixed|void
     */
    public function missingMethod( $parameters = array() )
    {
        if ( !is_array( $parameters ) )
        {
            $parameters = array($parameters);
        }

        $this->_action = array_shift( $parameters );

        $_viewName = 'app.' . $this->_action;
        $parameters['_trail'] = null;

        if ( View::exists( $_viewName ) )
        {
            //@todo remove this
            $parameters['_active'] = array(
                'instances' => 0,
                'clusters'  => 0,
                'users'     => 0,
                'user'      => null,
            );

            return View::make( $_viewName, $parameters );
        }

        //  Show 404
        return View::make( 'app.404', array('_trail' => null) );
    }

}