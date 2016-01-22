<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * a base controller with AJAX data handling methods
 */
abstract class DataController extends FactoryController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array
     */
    protected $columns;
    /**
     * @type array These columns will be forced to search on the base table
     */
    protected $forcedColumns = ['id', 'create_date', 'lmod_date', 'user_id'];
    /**
     * @type string The resource type
     */
    protected $resource = null;
    /**
     * @type string The name of the table
     */
    protected $tableName = null;
    /**
     * @type int The number of rows
     */
    protected $rowCount = null;
    /**
     * @type string The name of the model
     */
    protected $model = null;

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
    public function processDataRequest($table, $count, array $columns = ['*'], Builder $builder = null)
    {
        try {
            $this->parseDataRequest(null, $columns);

            /** @type Builder $_table */
            $_table = $builder ?: \DB::table($table);
            $_table->select($columns);

            if (!empty($this->order)) {
                foreach ($this->order as $_column => $_direction) {
                    $_table->orderByRaw($_column . ' ' . $_direction);
                }
            }

            if ($this->search && $this->columns) {
                $_where = [];

                foreach ($this->columns as $_column) {
                    if ($_column['searchable']) {
                        $_name = !empty($_column['name']) ? $_column['name'] : (!empty($_column['data']) ? $_column['data'] : null);

                        if (!empty($_name)) {
                            //  Add table name?
                            if (in_array($_name, $this->forcedColumns)) {
                                $_name = $table . '.' . $_name;
                            }

                            $_where[] = $_name . ' LIKE \'%' . $this->search . '%\'';
                        }
                    }
                }

                $_table->whereRaw(implode(' OR ', $_where));
            }

            if (false === $count) {
                return $_table;
            }

            if (!empty($this->limit)) {
                if (!empty($this->skip)) {
                    $_table->skip($this->skip);
                }

                $_table->take($this->limit);
            }

            /** @type array|Model $_response */
            $_response = $_table->get();

            return $this->respond($_response, $count, 0);
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
    protected function parseDataRequest($defaultSort = null, array &$columns = null)
    {
        $this->setDataTables(isset($_REQUEST, $_REQUEST['length']));
        $this->setSkip(array_get($_REQUEST, 'start', 0));
        $this->limit = array_get($_REQUEST, 'length', EnterpriseDefaults::DEFAULT_ITEMS_PER_PAGE);
        $this->order = $defaultSort;
        $this->search = trim(str_replace('\'', null, IfSet::getDeep($_REQUEST, 'search', 'value')));

        if (null === ($_sortOrder = array_get($_REQUEST, 'order'))) {
            return;
        }

        //  Parse the columns
        if (empty($this->columns) && empty($columns)) {
            $_dataColumns = array_get($_REQUEST, 'columns', []);

            $_columns = [];

            foreach ($_dataColumns as $_column) {
                if (null !== ($_name = array_get($_column, 'data', array_get($_column, 'name')))) {
                    $_columns[] = $_name;
                }
            }

            if (!empty($_columns)) {
                $this->columns = $columns = $_columns;
            }
        }

        $_sort = [];

        if (is_array($_sortOrder)) {
            foreach ($_sortOrder as $_key => $_value) {
                if (isset($_value['column'])) {
                    $_sort[($_value['column'] + 1)] = array_get($_value, 'dir', 'ASC');
                }
            }
        } elseif (is_string($_sortOrder)) {
            $this->order = $_sort[$_sortOrder] = array_get($_REQUEST, 'dir', 'ASC');
        }

        if (!empty($_sort)) {
            $this->order = $_sort;
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
    protected function respond($data, $totalRows = null, $totalFiltered = null)
    {
        //  Don't wrap if there are no totals
        if (!$this->dataTables || (null === $totalRows && null === $totalFiltered)) {
            return SuccessPacket::make($data);
        }

        $_response = [
            'draw'            => (int)array_get($_REQUEST, 'draw'),
            'recordsTotal'    => (int)($totalRows ?: 0),
            'recordsFiltered' => (int)($totalFiltered ?: $totalRows),
            'data'            => $this->prepareResponseData($data),
        ];

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
    protected function prepareResponseData($data)
    {
        $_cleaned = [];

        /** @type Model[] $data */
        foreach ($data as $_item) {
            $_values = (is_object($_item) && method_exists($_item, 'getAttributes')) ? $_item->getAttributes() : (array)$_item;

            if (null !== ($_id = array_get($_values, 'id'))) {
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
    protected function hashValue($value, $algorithm = ConsoleDefaults::SIGNATURE_METHOD, $salt = null, $rawOutput = false)
    {
        if (null === $value) {
            return null;
        }

        return hash($algorithm, $value, $salt . $rawOutput);
    }
}
