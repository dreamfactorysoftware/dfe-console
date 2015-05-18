<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

<<<<<<< HEAD
=======
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Library\Fabric\Database\Models\Deploy;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

>>>>>>> master
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

<<<<<<< HEAD
=======



    public function create( array $viewData = [] )
    {
        return \View::make( 'app.policies.create', ['prefix' => $this->_prefix] );
    }


>>>>>>> master
    public function index()
    {
        //$test = $id;
        //
        //echo 'asd';
<<<<<<< HEAD
        return \View::make( 'app.policies' )->with( 'prefix', $this->_prefix );//.index');//->with('nerd', $test);
=======
        return View::make('app.policies')->with('prefix', $this->_prefix)->with( 'policies', []);//.index');//->with('nerd', $test);
>>>>>>> master
    }

}

?>