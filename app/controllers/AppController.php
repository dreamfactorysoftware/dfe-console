<?php
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Cluster;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
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
            $parameters['_active'] = array(
                'instances' => number_format( Instance::count(), 0 ),
                'clusters'  => number_format( Cluster::count(), 0 ),
                'users'     => number_format( User::count(), 0 ),
            );

            return View::make( $_viewName, $parameters );
        }

        //  Show 404
        return View::make( 'app.404', array('_trail' => renderBreadcrumbs( array('Page Not Found' => false), false )) );
    }

}