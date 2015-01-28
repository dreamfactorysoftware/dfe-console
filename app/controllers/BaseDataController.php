<?php
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
class BaseDataController extends BaseController
{
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

            return $this->_respond( $_response, $count, $count );
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

        $_recordsFiltered = (integer)( $totalFiltered ?: $totalRows );
        $data = array('data' => $this->_removeArrayKeys( $data ));

        $data['draw'] = (integer)IfSet::get( $_REQUEST, 'draw' );
        $data['recordsTotal'] = (integer)$totalRows;
        $data['recordsFiltered'] = $_recordsFiltered;

        return Response::json( $data );
    }

    /**
     * Removes the keys from objects in an array. Used by dataTables
     *
     * @param array $data
     *
     * @return array
     */
    protected function _removeArrayKeys( $data )
    {
        $_cleaned = array();
        $_collection = ( $data instanceof Collection );

        /** @type Model[] $data */
        foreach ( $data as $_item )
        {
            $_cleaned[] = array_values( $_collection ? $_item->getAttributes() : $_item );
        }

        return $_cleaned;
    }
}
