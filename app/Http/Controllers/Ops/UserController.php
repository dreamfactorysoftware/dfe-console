<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Enterprise\Database\Models\User;
use Illuminate\Http\Request;

class UserController extends OpsResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'user_t';
    /** @type string */
    protected $model = User::class;
    /** @type string */
    protected $resource = 'user';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Create a new resource
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return User::register($request);
    }
}
