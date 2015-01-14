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
        $parameters['_trail'] = $this->_renderTrail( array('Dashboard' => false), false );

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
        return View::make( 'app.404', array('_trail' => 'Page Not Found') );
    }

    /**
     * Renders a breadcrumb trail
     *
     * @param array $trail
     *
     * @param bool  $buttons
     *
     * @return string
     */
    protected function _renderTrail( $trail = array(), $buttons = true )
    {
        $_html = null;

        foreach ( $trail as $_name => $_href )
        {
            $_class = false === $_href ? ' class="active" ' : null;
            $_href = false !== $_href ? '<a href="' . $_href . '">' . $_name . '</a>' : $_name;

            $_html .= '
<li ' . $_class . '>' . $_href . '</li>';
        }

        $_spinner = <<<HTML
<span class="breadcrumb-loader pull-right" style="display: none;"><img src="/img/bc-loading.gif" alt="" /></span>
HTML;

        $_buttons =
            false === $buttons
                ? null
                : <<<HTML
<div class="bc-controls">
    <button type="button" id="resource-new" class="btn btn-sm btn-primary">New</button>
    <button type="button" id="resource-save" disabled="disabled" class="btn btn-sm btn-warning">Save</button>
    <button type="button" id="resource-delete" disabled="disabled" class="btn btn-sm btn-danger">Delete</button>
</div>
HTML;

        return <<<HTML
<div id="breadcrumb" class="col-md-12">
    <a href="#" class="show-sidebar"><i class="fa fa-bars"></i></a>
    <ol class="breadcrumb pull-left">{$_html}</ol>
    {$_buttons}{$_spinner}
</div>
HTML;
    }
}