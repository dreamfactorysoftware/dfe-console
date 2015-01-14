<?php
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

/**
 * Our base controller
 */
class BaseController extends Controller
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type int
     */
    const DEFAULT_PER_PAGE = 25;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type bool True if this is a datatables request
     */
    protected $_dtRequest = false;
    /**
     * @type int
     */
    protected $_skip = null;
    /**
     * @type int
     */
    protected $_limit = null;
    /**
     * @type string
     */
    protected $_order = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if ( !is_null( $this->layout ) )
        {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->layout = View::make( $this->layout );
        }
    }

    /**
     * Parses inbound data request for limits and sort and search
     *
     * @param int|string $defaultSort Default sort column name or number
     */
    protected function _parseDataRequest( $defaultSort = 1 )
    {
        $this->_dtRequest = isset( $_REQUEST, $_REQUEST['length'] );
        $this->_skip = IfSet::get( $_REQUEST, 'start', 0 );
        $this->_limit = IfSet::get( $_REQUEST, 'length', static::DEFAULT_PER_PAGE );
        $this->_order = $defaultSort;

        if ( null === ( $_sortOrder = IfSet::get( $_REQUEST, 'order' ) ) )
        {
            return;
        }

        if ( is_array( $_sortOrder ) )
        {
            $_sort = array();

            foreach ( $_sortOrder as $_key => $_value )
            {
                if ( isset( $_value['column'] ) )
                {
                    $_sort[] = ( $_value['column'] + 1 ) . ' ' . IfSet::get( $_value, 'dir' );
                }
            }

            if ( !empty( $_sort ) )
            {
                $this->_order = implode( ', ', $_sort );
            }
        }
        elseif ( is_string( $_sortOrder ) )
        {
            $this->_order = trim( $_sortOrder . ' ' . IfSet::get( $_REQUEST, 'dir' ) );
        }
    }

    /**
     * Converts data to JSON and spits it out
     *
     * @param array $data
     * @param int   $totalRows
     * @param int   $totalFiltered
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function _respond( $data, $totalRows = null, $totalFiltered = null )
    {
        //  Don't wrap if there are no totals
        if ( !$this->_dtRequest || ( null === $totalRows && null === $totalFiltered ) )
        {
            return Response::json( $data );
        }

        $_recordsFiltered = (integer)( $totalFiltered ?: $totalRows );
        $data = array('data' => $data);

        $data['draw'] = (integer)IfSet::get( $_REQUEST, 'draw' );
        $data['recordsTotal'] = (integer)$totalRows;
        $data['recordsFiltered'] = $_recordsFiltered;

        return Response::json( $data );
    }

    /**
     * Renders a breadcrumb trail
     *
     * @param array $trail
     * @param bool  $buttons
     *
     * @return string
     */
    public static function renderBreadcrumbs( $trail = array(), $buttons = true )
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
