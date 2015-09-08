<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Http\Controllers\BaseController;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

abstract class FactoryController extends BaseController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type bool True if this is a datatables request
     */
    protected $dataTables = false;
    /**
     * @type int
     */
    protected $skip;
    /**
     * @type string
     */
    protected $search;
    /**
     * @type int
     */
    protected $limit;
    /**
     * @type array
     */
    protected $order;
    /**
     * @type string
     */
    protected $uiPrefix = ConsoleDefaults::UI_PREFIX;
    /**
     * @type string Any data to merge into view data when rendering
     */
    protected $extraViewData = ['prefix' => ConsoleDefaults::UI_PREFIX];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function __construct()
    {
        $this->middleware('auth');
        $this->setLumberjackPrefix('dfe-console');
        $this->uiPrefix = config('dfe.ui.prefix', ConsoleDefaults::UI_PREFIX);
    }

    /**
     * @param string $view      The view name
     * @param array  $data      The view data
     * @param array  $mergeData Any additional data to merge with the view data which is merged
     *                          with this class's $extraViewData
     *
     * @return \Illuminate\View\View
     */
    protected function renderView($view, array $data = [], array $mergeData = [])
    {
        return \View::make($view, $data, array_merge($this->extraViewData, $mergeData));
    }

    /**
     * @return boolean
     */
    public function isDataTables()
    {
        return $this->dataTables;
    }

    /**
     * @param boolean $dataTables
     *
     * @return $this
     */
    public function setDataTables($dataTables)
    {
        $this->dataTables = $dataTables;

        return $this;
    }

    /**
     * @return int
     */
    public function getSkip()
    {
        return $this->skip;
    }

    /**
     * @param int $skip
     *
     * @return $this
     */
    public function setSkip($skip)
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * @param string $search
     *
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param array $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtraViewData()
    {
        return $this->extraViewData;
    }

    /**
     * @param string $extraViewData
     *
     * @return $this
     */
    public function setExtraViewData($extraViewData)
    {
        $this->extraViewData = $extraViewData;

        return $this;
    }

    /**
     * @return string
     */
    public function getUiPrefix()
    {
        return $this->uiPrefix;
    }

}
