<?php
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
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
     * @param bool $asArray
     *
     * @return array|string The hashed email address
     */
    public static function getUserHash( $asArray = false )
    {
        $_hash = md5( strtolower( Auth::user() ? Auth::user()->email : 'nobody@dreamfactory.com' ) );

        return $asArray ? array('_userHash' => $_hash) : $_hash;
    }

    public static function getUserInfo()
    {
        $_name = Auth::user() ? Auth::user()->email : 'nobody@dreamfactory.com';
        $_hash = md5( strtolower( $_name ) );

        return array(
            'name' => $_name,
            'hash' => $_hash,
        );
    }

}
