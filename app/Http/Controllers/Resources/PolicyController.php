<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

class PolicyController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'policy_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Policy';
    /** @type string */
    protected $_resource = 'policy';

    protected $_prefix = 'v1';

    public function create(array $viewData = [])
    {
        return \View::make('app.policies.create', ['prefix' => $this->_prefix]);
    }

    public function index()
    {
        return \View::make('app.policies')->with('prefix', $this->_prefix)->with('policies', []);
    }
}
