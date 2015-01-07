<?php
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Request;

class AppController extends BaseController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Request
     */
    protected $_request = null;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_request = Request::createFromGlobals();
    }

    /**
     * @param array $parameters
     *
     * @return mixed|void
     */
    public function missingMethod( $parameters = array() )
    {
        try
        {
            $this->_processRequest( $parameters );
        }
        catch ( \Exception $_ex )
        {
            parent::missingMethod( $parameters );
        }
    }

    /**
     * @param array $parameters
     */
    protected function _processRequest( $parameters = array() )
    {
        $_action = array_shift( $parameters );
        $this->layout = null;

        return View::make( 'app.' . $_action, $parameters );
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