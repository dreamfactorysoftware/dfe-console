<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Library\Fabric\Database\Models\Deploy\ServiceUser;
use DreamFactory\Library\Fabric\Database\Models\Deploy\User;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class UserController extends ResourceController //FactoryController //
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'user_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\User';
    /** @type string */
    protected $_resource = 'user';
    /**
     * @type string
     */
    protected $_prefix = 'v1';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->_active = array();
    }

    /**
     * @return $this
     */
    public function create( $viewData = [] )
    {
        return \View::make( 'app.users.create' )->with( 'prefix', $this->_prefix );
    }

    public function edit( $id )
    {
        //echo $_GET['user_type'];

        if ( isset( $_GET['user_type'] ) )
        {
            $user_type = $_GET['user_type'];
            $is_admin = false;

            if ( $user_type == 'admin' )
            {
                $users = new ServiceUser();
                $is_admin = true;
            }
            else
            {
                $users = new User();
            }

            $user_data = $users->find( $id );

            return View::make( 'app.users.edit' )
                ->with( 'user_id', $id )
                ->with( 'prefix', $this->_prefix )
                ->with( 'user', $user_data )
                ->with( 'is_admin', $is_admin );
        }
        else
        {
            return 'FAIL';
        }

    }

    public function store()
    {

        $is_system_admin = Input::get( 'system_admin' );

        $create_user = null;

        if ( $is_system_admin == 'true' )
        {
            if ( ServiceUser::where( 'email_addr_text', '=', Input::get( 'email_addr_text' ) )->exists() )
            {
                return 'EXISTS';
            }
            $create_user = new ServiceUser;
        }
        else
        {
            if ( User::where( 'email_addr_text', '=', Input::get( 'email_addr_text' ) )->exists() )
            {
                return 'EXISTS';
            }
            $create_user = new User;
        }

        $is_password_set = Input::get( 'set_password' );

        $create_user->email_addr_text = Input::get( 'email_addr_text' );
        $create_user->first_name_text = Input::get( 'first_name_text' );
        $create_user->last_name_text = Input::get( 'last_name_text' );
        $create_user->nickname_text = Input::get( 'nickname_text' );

        if ( $is_password_set )
        {
            $create_user->password_text = Input::get( 'password_text' );
        }
        else
        {
            $create_user->password_text = null;
        }

        $create_user->owner_id = null;
        $create_user->owner_type_nbr = null;
        $create_user->last_login_date = '';
        $create_user->last_login_ip_text = null;

        //$inserted_id = '';

        if ( !$create_user->save() )
        {
            return 'FAIL';
        }

        /*
        if($create_user->save()){
            $inserted_id = $create_user->id;

            if($is_system_admin){
                $create_user->owner_id = $inserted_id;
                $create_user->save();
            }
        }
        */

        return 'OK';
    }

    public function update( $id )
    {
        $is_password_set = Input::get( 'set_password' );
        $is_system_admin = Input::get( 'system_admin' );

        $users = null;

        if ( $is_system_admin == 'true' )
        {
            $user = ServiceUser::where( 'email_addr_text', '=', Input::get( 'email_addr_text' ) )->first();
            if ( $user != null )
            {
                if ( $user->id != $id )
                {
                    return 'FAIL';
                }
            }
            $users = new ServiceUser;
        }
        else
        {
            $user = User::where( 'email_addr_text', '=', Input::get( 'email_addr_text' ) )->first();
            if ( $user != null )
            {
                if ( $user->id != $id )
                {
                    return 'FAIL';
                }
            }
            $users = new User;
        }

        //$users = new ServiceUser;
        $user_data = $users->find( $id );

        $user_data->email_addr_text = Input::get( 'email_addr_text' );
        $user_data->first_name_text = Input::get( 'first_name_text' );
        $user_data->last_name_text = Input::get( 'last_name_text' );
        $user_data->nickname_text = Input::get( 'nickname_text' );

        if ( $is_password_set )
        {
            $user_data->password_text = bcrypt( Input::get( 'password_text' ) );
        }

        $user_data->save();

        return 'OK';
    }

    public function destroy( $ids )
    {
        $a_users = new ServiceUser;
        $o_users = new User;;

        $id_array = explode( ',', $ids );

        foreach ( $id_array as $id )
        {

            if ( strpos( $id, '_' ) !== false )
            {
                $a_users->find( str_replace( '_admin', '', $id ) )->delete();
            }
            else
            {
                $o_users->find( $id )->delete();
            }
        }

        return 'OK';
    }

    public function index()
    {
        $users_owners = new ServiceUser;
        $users_admins = new User;

        $_columns =
            [
                'id',
                'first_name_text',
                'last_name_text',
                'nickname_text',
                'email_addr_text',
                'owner_id'
            ];

        $o_users = $users_owners->take( 500 )->get( $_columns );
        $a_users = $users_admins->take( 500 )->get( $_columns );

        $o_users_array = json_decode( $o_users );
        $a_users_array = json_decode( $a_users );

        array_walk(
            $o_users_array,
            function ( &$o_user_array )
            {
                $o_user_array->admin = true;
            }
        );

        array_walk(
            $a_users_array,
            function ( &$a_user_array )
            {
                $a_user_array->admin = false;
            }
        );

        $result = array_merge( $o_users_array, $a_users_array );
        $result = array_map( "unserialize", array_unique( array_map( "serialize", $result ) ) );
        sort( $result );

        $servers_tabledata = [];
        foreach ( $result as $value )
        {

            $manage =
                '<div><input type="hidden" id="user_id" value="' .
                $value->id .
                '"><input type="hidden" id="user_admin" value="' .
                $value->admin .
                '">';

            if ( $value->admin )
            {
                $manage .= '<input type="checkbox" value="' .
                    $value->id .
                    '_admin" id="user_checkbox_' .
                    $value->id .
                    '_admin">&nbsp;&nbsp;
                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" id="user_button_' .
                    $value->id .
                    '_admin" onclick="confirmRemoveUser(' .
                    $value->id .
                    ', true)" value="delete" style="width: 25px"></button>&nbsp;&nbsp;
                            <button type="button" class="btn btn-default btn-xs" id="user_button_cancel_' .
                    $value->id .
                    '_admin" onclick="cancelRemoveUser(' .
                    $value->id .
                    ', true)" value="delete" style="display: none">Cancel</button>';
                $role = '<span class="label label-primary" id="user_type">System Administrator</span>';
            }
            else
            {
                $manage .= '<input type="checkbox" value="' .
                    $value->id .
                    '" id="user_checkbox_' .
                    $value->id .
                    '">&nbsp;&nbsp;
                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" id="user_button_' .
                    $value->id .
                    '" onclick="confirmRemoveUser(' .
                    $value->id .
                    ', false)" value="delete" style="width: 25px"></button>&nbsp;&nbsp;
                            <button type="button" class="btn btn-default btn-xs" id="user_button_cancel_' .
                    $value->id .
                    '" onclick="cancelRemoveUser(' .
                    $value->id .
                    ', false)" value="delete" style="display: none">Cancel</button>';
                $role = '<span class="label label-info" id="user_type">DSP Owner</span>';
            }

            $manage .= '</div>';

            array_push(
                $servers_tabledata,
                array(
                    '<input type="hidden" id="user_id" value="' . $value->id . '">',
                    $manage,
                    $value->first_name_text . ' ' . $value->last_name_text,
                    $value->nickname_text,
                    $value->email_addr_text,
                    $role,
                    '<span class="label label-success">Active</span>'
                )
            );
        }

        $result = json_encode( $servers_tabledata );

        return \View::make( 'app.users' )->with( 'prefix', $this->_prefix )->with( 'users', $result );//$users_owners->all());

        //$test = $this->_processDataRequest( 'instance_t', Instance::count(), $_columns, $_query );
        //return View::make('app.users')->with('prefix', $this->_prefix)->with('users', $users->all());//take(10)->get());
    }
}