<?php
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Request;

class AppController extends BaseController
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
        $this->_action = array_shift( $parameters );

        $_viewName = 'app.' . $this->_action;
        $parameters['_trail'] = renderBreadcrumbs( array(ucwords( $this->_action ) => false), false );

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
        return View::make( 'app.404', array('_trail' => renderBreadcrumbs( array('Page Not Found' => false), false )) );
    }

}