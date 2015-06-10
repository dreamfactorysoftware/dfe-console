<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Facades\Packet;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
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
    /**
     * @type array These columns will be forced to search on the base table
     */
    protected $_forcedColumns = array('id', 'create_date', 'lmod_date', 'user_id');
    /**
     * @type string The resource type
     */
    protected $_resource = null;
    /**
     * @type string The name of the table
     */
    protected $_tableName = null;
    /**
     * @type int The number of rows
     */
    protected $_rowCount = null;
    /**
     * @type string The name of the model
     */
    protected $_model = null;
    /**
     * @type string A prefix prepended to outbound API requests
     */
    protected $_apiPrefix = null;

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
    public function _processDataRequest($table, $count, array $columns = array('*'), $builder = null)
    {
        try {
            $this->_parseDataRequest(null, $columns);

            /** @type Builder $_table */
            $_table = $builder ?: \DB::table($table);
            $_table->select($columns);

            if (!empty($this->_order)) {
                foreach ($this->_order as $_column => $_direction) {
                    $_table->orderByRaw($_column . ' ' . $_direction);
                }
            }

            if ($this->_search && $this->_columns) {
                $_where = array();

                foreach ($this->_columns as $_column) {
                    if ($_column['searchable']) {
                        $_name =
                            !empty($_column['name'])
                                ? $_column['name']
                                : (!empty($_column['data']) ? $_column['data']
                                : null);

                        if (!empty($_name)) {
                            //  Add table name?
                            if (in_array($_name, $this->_forcedColumns)) {
                                $_name = $table . '.' . $_name;
                            }

                            $_where[] = $_name . ' LIKE \'%' . $this->_search . '%\'';
                        }
                    }
                }

                $_table->whereRaw(implode(' OR ', $_where));
            }

            if (false === $count) {
                return $_table;
            }

            if (!empty($this->_limit)) {
                if (!empty($this->_skip)) {
                    $_table->skip($this->_skip);
                }

                $_table->take($this->_limit);
            }

            /** @type array|Model $_response */
            $_response = $_table->get();

            return $this->_respond($_response, $count, 0);
        } catch (\Exception $_ex) {
            throw new BadRequestHttpException($_ex->getMessage());
        }
    }

    /**
     * Parses inbound data request for limits and sort and search
     *
     * @param int|string $defaultSort Default sort column name or number
     * @param array      $columns
     */
    protected function _parseDataRequest($defaultSort = null, array &$columns = null)
    {
        $this->_dtRequest = isset($_REQUEST, $_REQUEST['length']);
        $this->_skip = IfSet::get($_REQUEST, 'start', 0);
        $this->_limit = IfSet::get($_REQUEST, 'length', static::DEFAULT_PER_PAGE);
        $this->_order = $defaultSort;
        $this->_search = trim(str_replace('\'', null, IfSet::getDeep($_REQUEST, 'search', 'value')));

        if (null === ($_sortOrder = IfSet::get($_REQUEST, 'order'))) {
            return;
        }

        //  Parse the columns
        if (empty($this->_columns) && empty($columns)) {
            $_dataColumns = IfSet::get($_REQUEST, 'columns', array());

            $_columns = array();

            foreach ($_dataColumns as $_column) {
                if (null !== ($_name = IfSet::get($_column, 'data', IfSet::get($_column, 'name')))) {
                    $_columns[] = $_name;
                }
            }

            if (!empty($_columns)) {
                $this->_columns = $columns = $_columns;
            }
        }

        $_sort = array();

        if (is_array($_sortOrder)) {
            foreach ($_sortOrder as $_key => $_value) {
                if (isset($_value['column'])) {
                    $_sort[($_value['column'] + 1)] = IfSet::get($_value, 'dir', 'ASC');
                }
            }
        } elseif (is_string($_sortOrder)) {
            $this->_order = $_sort[$_sortOrder] = IfSet::get($_REQUEST, 'dir', 'ASC');
        }

        if (!empty($_sort)) {
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
    protected function _respond($data, $totalRows = null, $totalFiltered = null)
    {
        //  Don't wrap if there are no totals
        if (!$this->_dtRequest || (null === $totalRows && null === $totalFiltered)) {
            return Packet::success($data);
        }

        $totalRows = (integer)($totalRows ?: 0);

        $_response = array(
            'draw'            => (integer)IfSet::get($_REQUEST, 'draw'),
            'recordsTotal'    => $totalRows,
            'recordsFiltered' => (integer)($totalFiltered ?: $totalRows),
            'data'            => $this->_prepareResponseData($data),
        );

        return \Response::json($_response);
    }

    /**
     * Cleans up any necessary things before the data is shipped back to the client. The default implementation adds a
     * "DT_RowId" key to each returned row.
     *
     * @param array $data
     *
     * @return array
     */
    protected function _prepareResponseData($data)
    {
        $_cleaned = array();

        /** @type Model[] $data */
        foreach ($data as $_item) {
            $_values = (is_object($_item) && method_exists($_item, 'getAttributes'))
                ? $_item->getAttributes() : (array)$_item;

            if (null !== ($_id = IfSet::get($_values, 'id'))) {
                $_values['DT_RowId'] = $_id;
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
    protected function _hashValue(
        $value,
        $algorithm = ConsoleDefaults::SIGNATURE_METHOD,
        $salt = null,
        $rawOutput = false
    ){
        if (null === $value) {
            return null;
        }

        return hash($algorithm, $value, $salt . $rawOutput);
    }

    /**
     * @return string
     */
    public function getApiPrefix()
    {
        return $this->_apiPrefix;
    }

    /**
     * @param string $apiPrefix
     *
     * @return DataController
     */
    public function setApiPrefix($apiPrefix)
    {
        $this->_apiPrefix = $apiPrefix;

        return $this;
    }

}
