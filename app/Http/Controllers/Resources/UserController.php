<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Library\Fabric\Database\Models\Deploy\ServiceUser;
use DreamFactory\Library\Fabric\Database\Models\Deploy\User;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
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

    public function __construct()
    {
        parent::__construct();

        $this->_active = array();
    }

    /**
     * @param array $viewData
     *
     * @return \Illuminate\View\View
     */
    public function create( array $viewData = [] )
    {
        $this->_resourceView = 'app.users.create';

        return parent::create( array_merge( ['prefix' => $this->_prefix], $viewData ) );
    }

    public function edit( $id )
    {
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

            unset( $user_data['password_text'] );

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
        $is_system_admin = '';
        $is_password_set = false;
        $user = null;
        $user_data = Input::all();

        if ( array_key_exists( 'system_admin', $user_data ) )
        {
            $is_system_admin = 1;
        }

        if ( $is_system_admin != '' )
        {
            $user = new ServiceUser;
        }
        else
        {
            $user = new User;
        }

        if ( array_key_exists( 'set_password', $user_data ) )
        {
            $is_password_set = $user_data['set_password'];
        }

        if ( $is_password_set )
        {
            $user->password_text = bcrypt( $user_data['new_password'] );
        }

        if ( array_key_exists( 'active', $user_data ) )
        {
            $user->active_ind = 1;
        }
        else
        {
            $user->active_ind = 0;
        }

        $user->email_addr_text = $user_data['email_addr_text'];
        $user->first_name_text = $user_data['first_name_text'];
        $user->last_name_text = $user_data['last_name_text'];
        $user->nickname_text = $user_data['nickname_text'];

        $user->save();

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/users';

        return Redirect::to( $_redirect );
    }

    public function update( $id )
    {
        $is_system_admin = '';
        $is_password_set = false;
        $users = null;
        $user_data = Input::all();

        if ( array_key_exists( 'user_type', $user_data ) )
        {
            $is_system_admin = $user_data['user_type'];
        }

        if ( array_key_exists( 'set_password', $user_data ) )
        {
            $is_password_set = $user_data['set_password'];
        }

        if ( array_key_exists( 'active_ind', $user_data ) )
        {
            $user_data['active_ind'] = 1;
        }
        else
        {
            $user_data['active_ind'] = 0;
        }

        if ( $is_system_admin != '' )
        {
            $user = ServiceUser::where( 'email_addr_text', '=', $user_data['email_addr_text'] )->first();

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
            $user = User::where( 'email_addr_text', '=', $user_data['email_addr_text'] )->first();

            if ( $user != null )
            {
                if ( $user->id != $id )
                {
                    return 'FAIL';
                }
            }

            $users = new User;
        }

        if ( $is_password_set )
        {
            $user_data['password_text'] = bcrypt( $user_data['new_password'] );
        }

        unset( $user_data['_method'] );
        unset( $user_data['_token'] );
        unset( $user_data['new_password'] );
        unset( $user_data['set_password'] );
        unset( $user_data['user_type'] );

        $user = $users->find( $id );
        $user->update( $user_data );

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/users';

        return Redirect::to( $_redirect );
    }

    public function destroy( $ids )
    {
        $user_data = Input::all();
        $a_users = new ServiceUser;
        $o_users = new User;

        if ( $ids != 'multi' )
        {
            if ( $user_data['user_type'] != "" )
            {
                $a_users->find( $ids )->delete();
            }
            else
            {
                $o_users->find( $ids )->delete();
            }
        }
        else
        {
            $id_array = explode( ',', $user_data['_selectedIds'] );
            $type_array = explode( ',', $user_data['_selectedTypes'] );

            foreach ( $id_array as $i => $id )
            {
                if ( $type_array[$i] != "" )
                {
                    $a_users->find( $id_array[$i] )->delete();
                }
                else
                {
                    $o_users->find( $id_array[$i] )->delete();
                }
            }
        }

        $_redirect = '/';
        $_redirect .= $this->_prefix;
        $_redirect .= '/users';

        return Redirect::to( $_redirect );
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
                'owner_id',
                'active_ind'
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

        return View::make( 'app.users' )->with( 'prefix', $this->_prefix )->with( 'users', $result );//$users_owners->all());
    }

}




