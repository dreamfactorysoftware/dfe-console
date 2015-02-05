<?php
namespace App\Http\Controllers;

use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * a base controller with AJAX data handling methods
 */
class DataController extends FactoryController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_search;
    /**
     * @type array
     */
    protected $_columns;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Generic data retrieval method
     *
     * @param string                             $table
     * @param int|bool                           $count
     * @param array                              $columns
     * @param \Illuminate\Database\Query\Builder $builder
     *
     * @return \Illuminate\Database\Query\Builder|mixed
     */
    public function _processDataRequest( $table, $count, array $columns = array('*'), $builder = null )
    {
        try
        {
            $this->_parseDataRequest( $table );

            /** @type Builder $_table */
            $_table = $builder ?: DB::table( $table );

            if ( !empty( $this->_order ) )
            {
                foreach ( $this->_order as $_column => $_direction )
                {
                    $_table->orderByRaw( $_column . ' ' . $_direction );
                }
            }

            if ( $this->_search && $this->_columns )
            {
                $_where = array();

                foreach ( $this->_columns as $_column )
                {
                    if ( $_column['searchable'] )
                    {
                        $_where[] = $_column['name'] . ' LIKE \'%' . IfSet::getDeep( $_column, 'search', 'value', $this->_search ) . '%\'';
                    }
                }

                $_table->whereRaw( implode( ' OR ', $_where ) );
            }

            if ( false === $count )
            {
                return $_table;
            }

            if ( !empty( $this->_limit ) )
            {
                if ( !empty( $this->_skip ) )
                {
                    $_table->skip( $this->_skip );
                }

                $_table->take( $this->_limit );
            }

            /** @type array|Model $_response */
            $_response = $_table->get();

            return $this->_respond( $_response, $count, 0 );
        }
        catch ( \Exception $_ex )
        {
            throw new BadRequestHttpException( $_ex->getMessage() );
        }
    }

    /**
     * Parses inbound data request for limits and sort and search
     *
     * @param int|string $defaultSort Default sort column name or number
     */
    protected function _parseDataRequest( $defaultSort = null )
    {
        $this->_dtRequest = isset( $_REQUEST, $_REQUEST['length'] );
        $this->_skip = IfSet::get( $_REQUEST, 'start', 0 );
        $this->_limit = IfSet::get( $_REQUEST, 'length', static::DEFAULT_PER_PAGE );
        $this->_order = $defaultSort;
        $this->_search = str_replace( '\'', null, IfSet::getDeep( $_REQUEST, 'search', 'value' ) );
        $this->_columns = IfSet::get( $_REQUEST, 'columns', array() );

        if ( null === ( $_sortOrder = IfSet::get( $_REQUEST, 'order' ) ) )
        {
            return;
        }

        $_sort = array();

        if ( is_array( $_sortOrder ) )
        {
            foreach ( $_sortOrder as $_key => $_value )
            {
                if ( isset( $_value['column'] ) )
                {
                    $_sort[( $_value['column'] + 1 )] = IfSet::get( $_value, 'dir', 'ASC' );
                }
            }
        }
        elseif ( is_string( $_sortOrder ) )
        {
            $this->_order = $_sort[$_sortOrder] = IfSet::get( $_REQUEST, 'dir', 'ASC' );
        }

        if ( !empty( $_sort ) )
        {
            $this->_order = $_sort;
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

        $totalRows = (integer)( $totalRows ?: 0 );

        $_response = array(
            'draw'            => (integer)IfSet::get( $_REQUEST, 'draw' ),
            'recordsTotal'    => $totalRows,
            'recordsFiltered' => (integer)( $totalFiltered ?: $totalRows ),
            'data'            => $this->_prepareResponseData( $data ),
        );

        return Response::json( $_response );
    }

    /**
     * Cleans up any necessary things before the data is shipped back to the client. The default implementation adds a "DT_RowId" key to
     * each returned row.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareResponseData( $data )
    {
        $_cleaned = array();
        $_collection = ( $data instanceof Collection );

        /** @type Model[] $data */
        foreach ( $data as $_item )
        {
            $_values = $_collection ? $_item->getAttributes() : $_item;

            if ( null !== ( $_id = IfSet::get( $_values, 'id' ) ) )
            {
                $_values['DT_RowId'] = 'row_' . $this->_hashValue( $_id );
            }

            $_cleaned[] = $_values;
        }

        return $_cleaned;
    }

    /**
     * @param string $value     The value to hash
     * @param string $algorithm The algorithm to use. @See hash()
     * @param null   $salt      Optional salt that is prefixed prior to hashing
     * @param bool   $rawOutput Returns the binary hash
     *
     * @return null|string
     */
    protected function _hashValue( $value, $algorithm = 'sha256', $salt = null, $rawOutput = false )
    {
        if ( null === $value )
        {
            return null;
        }

        return hash( $algorithm, $value, $salt . $rawOutput );
    }
}
