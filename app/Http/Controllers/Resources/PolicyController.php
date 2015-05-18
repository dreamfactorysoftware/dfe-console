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
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Policy';
    /** @type string */
    protected $_resource = 'policy';

    protected $_prefix = 'v1';

    public function index()
    {
        //$test = $id;
        //
        //echo 'asd';
        return \View::make( 'app.policies' )->with( 'prefix', $this->_prefix );//.index');//->with('nerd', $test);
    }

}

?>