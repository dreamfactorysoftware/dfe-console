<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Library\Fabric\Database\Models\Deploy;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

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


    public function store()
    {

    }


    public function edit( $id )
    {


    }


    public function update( $id )
    {

    }


    public function destroy( $ids )
    {

    }


    public function create( array $viewData = [] )
    {
        return \View::make( 'app.policies.create', ['prefix' => $this->_prefix] );
    }


    public function index()
    {
        return View::make('app.policies')->with('prefix', $this->_prefix)->with( 'policies', []);//.index');//->with('nerd', $test);
    }

}

?>