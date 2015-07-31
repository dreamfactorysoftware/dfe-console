<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\User;
use Illuminate\Database\QueryException;
use Session;
use Validator;


class UserController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $tableName = 'user_t';
    /** @type string */
    protected $model = 'DreamFactory\\Enterprise\\Database\\Models\\User';
    /** @type string */
    protected $resource = 'user';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function create(array $viewData = [])
    {
        $this->resourceView = 'app.users.create';

        return parent::create($viewData);
    }

    public function edit($id)
    {
        if (isset($_GET['user_type'])) {
            $user_type = $_GET['user_type'];
            $is_admin = false;

            if ($user_type == 'admin') {
                $users = new ServiceUser();
                $is_admin = true;
            } else {
                $users = new User();
            }

            $user_data = $users->find($id);
            $user_data['password_text'] = '********';

            return $this->renderView('app.users.edit',
                ['user_id' => $id, 'user' => $user_data, 'is_admin' => $is_admin]);
        } else {
            return 'FAIL';
        }
    }

    public function store()
    {
        $is_system_admin = '';
        $user = null;
        $create_user = null;
        $user_data = \Input::all();

        $validator = Validator::make($user_data,
            [
                'email_addr_text' => 'required|email',
                'first_name_text' => 'required|string',
                'last_name_text'  => 'required|string',
                'nickname_text'   => 'required|string',
                'new_password'    => 'required|string',
            ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach ($messages as $key => $value) {
                switch ($key) {

                    case 'email_addr_text':
                        $flash_message = 'Email is blank or format is invalid (use abc@domain.tld)';
                        break;
                    case 'first_name_text':
                        $flash_message =
                            'First Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'last_name_text':
                        $flash_message =
                            'Last Name is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'nickname_text':
                        $flash_message =
                            'Nickname is blank or contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'new_password':
                        $flash_message = 'Password is blank or contains invalid characters';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/users/create')->withInput();
        }

        if (array_key_exists('system_admin', $user_data)) {
            $is_system_admin = 1;
        }

        if ($is_system_admin != '') {
            $create_user = new ServiceUser;
        } else {
            $create_user = new User;
        }

        if (array_key_exists('active', $user_data)) {
            $user_data['active_ind'] = 1;
        } else {
            $user_data['active_ind'] = 0;
        }

        $user_data['password_text'] = bcrypt($user_data['new_password']);

        unset($user_data['active']);
        unset($user_data['new_password']);
        unset($user_data['system_admin']);

        try {
            $create_user->create($user_data);

            $result_text =
                'The user "' . $user_data['first_name_text'] . ' ' . $user_data['last_name_text'] . '" was created successfully!';
            $result_status = 'alert-success';

            return \Redirect::to($this->makeRedirectUrl('users',
                ['flash_message' => $result_text, 'flash_type' => $result_status]));

        } catch (QueryException $e) {
            $res_text = strtolower($e->getMessage());

            if (strpos($res_text, 'duplicate entry') !== false) {
                Session::flash('flash_message', 'Email is already in use.');
                Session::flash('flash_type', 'alert-danger');
            } else {
                Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
                Session::flash('flash_type', 'alert-danger');
            }

            return redirect('/v1/users/create')->withInput();
        }

    }

    public function update($id)
    {
        $is_system_admin = '';
        $users = null;
        $user_data = \Input::all();

        if (array_key_exists('user_type', $user_data)) {
            $is_system_admin = $user_data['user_type'];
        }

        $validator = Validator::make($user_data,
            [
                'email_addr_text' => 'required|email',
                'first_name_text' => 'required|string',
                'last_name_text'  => 'required|string',
                'nickname_text'   => 'required|string',
                'new_password'    => 'required|string',
            ]);

        if ($validator->fails()) {

            $messages = $validator->messages()->getMessages();

            $flash_message = '';

            foreach ($messages as $key => $value) {
                switch ($key) {

                    case 'email_addr_text':
                        $flash_message = 'Email is blank or format is invalid (use abc@domain.tld)';
                        break;
                    case 'first_name_text':
                        $flash_message = 'First Name contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'last_name_text':
                        $flash_message = 'Last Name contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'nickname_text':
                        $flash_message = 'Nickname contains invalid characters (use a-z, A-Z, 0-9, . and -)';
                        break;
                    case 'new_password':
                        $flash_message = 'Password is blank or contains invalid characters';
                        break;
                }

                break;
            }

            Session::flash('flash_message', $flash_message);
            Session::flash('flash_type', 'alert-danger');

            $_redirect = '/v1/users/' . $id . '/edit?user_type=';

            if ($is_system_admin) {
                $_redirect .= 'admin';
            } else {
                $_redirect .= 'user';
            }

            return redirect($_redirect)->withInput();
        }

        if (array_key_exists('user_type', $user_data)) {
            $is_system_admin = $user_data['user_type'];
        }

        if ($id != $user_data['user_auth']) {
            if (array_key_exists('active_ind', $user_data)) {
                $user_data['active_ind'] = 1;
            } else {
                $user_data['active_ind'] = 0;
            }
        }

        if ($is_system_admin != '') {
            $user = ServiceUser::where('email_addr_text', '=', $user_data['email_addr_text'])->first();

            if ($user != null) {
                if ($user->id != $id) {
                    return 'FAIL';
                }
            }

            $users = new ServiceUser;
        } else {
            $user = User::where('email_addr_text', '=', $user_data['email_addr_text'])->first();

            if ($user != null) {
                if ($user->id != $id) {
                    return 'FAIL';
                }
            }

            $users = new User;
        }

        if ($user_data['new_password'] != '1234567890') {
            $user_data['password_text'] = bcrypt($user_data['new_password']);
        } else {
            unset($user_data['password_text']);
        }

        unset($user_data['_method']);
        unset($user_data['_token']);
        unset($user_data['new_password']);
        unset($user_data['user_type']);
        unset($user_data['user_auth']);
        unset($user_data['instance_manage_ind']);
        unset($user_data['instance_policy_ind']);

        try {
            $user = $users->find($id);
            $user->update($user_data);

            $result_text =
                'The user "' . $user_data['first_name_text'] . ' ' . $user_data['last_name_text'] . '" was updated successfully!';
            $result_status = 'alert-success';

            return \Redirect::to($this->makeRedirectUrl('users',
                ['flash_message' => $result_text, 'flash_type' => $result_status]));
        } catch (QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message', 'An error occurred! Check for errors and try again.');
            Session::flash('flash_type', 'alert-danger');

            $_redirect = '/v1/users/' . $id . '/edit?user_type=';

            if ($is_system_admin) {
                $_redirect .= 'admin';
            } else {
                $_redirect .= 'user';
            }

            return redirect($_redirect)->withInput();
        }
    }

    public function destroy($ids)
    {
        $user_data = \Input::all();
        $id_array = [];

        $user_names = [];

        try {

            if ($ids != 'multi') {
                if ($user_data['user_type'] != "") {
                    $user = ServiceUser::where('id', '=', $ids);
                    $user_name = $user->get(['first_name_text', 'last_name_text']);
                    array_push($user_names,
                        '"' . $user_name[0]->first_name_text . ' ' . $user_name[0]->last_name_text . '"');
                    $user->delete();
                } else {
                    $user = User::where('id', '=', $ids);
                    $user_name = $user->get(['first_name_text', 'last_name_text']);
                    array_push($user_names,
                        '"' . $user_name[0]->first_name_text . ' ' . $user_name[0]->last_name_text . '"');
                    $user->delete();
                }
            } else {
                $id_array = explode(',', $user_data['_selectedIds']);
                $type_array = explode(',', $user_data['_selectedTypes']);

                foreach ($id_array as $i => $id) {
                    if ($type_array[$i] != "") {
                        $user = ServiceUser::where('id', '=', $id);
                        $user_name = $user->get(['first_name_text', 'last_name_text']);
                        array_push($user_names,
                            '"' . $user_name[0]->first_name_text . ' ' . $user_name[0]->last_name_text . '"');
                        $user->delete();
                    } else {
                        $user = User::where('id', '=', $id);
                        $user_name = $user->get(['first_name_text', 'last_name_text']);
                        array_push($user_names,
                            '"' . $user_name[0]->first_name_text . ' ' . $user_name[0]->last_name_text . '"');
                        $user->delete();
                    }
                }
            }

            if (count($id_array) > 1) {
                $names = '';
                foreach ($user_names as $i => $name) {
                    $names .= $name;

                    if (count($user_names) > $i + 1) {
                        $names .= ', ';
                    }
                }

                $result_text = 'The users ' . $names . ' were deleted successfully!';
            } else {
                $result_text = 'The user ' . $user_names[0] . ' was deleted successfully!';
            }

            $result_status = 'alert-success';

            return \Redirect::to($this->makeRedirectUrl('users',
                ['flash_message' => $result_text, 'flash_type' => $result_status]));
        } catch (QueryException $e) {
            //$res_text = $e->getMessage();
            Session::flash('flash_message',
                'Error! One or more users can\'t be deleted because a resource is assigned to the user(s). ');
            Session::flash('flash_type', 'alert-danger');

            return redirect('/v1/users')->withInput();
        }
    }

    public function index()
    {
        $users_owners = new ServiceUser;
        $users_admins = new User;

        $_columns = [
            'id',
            'first_name_text',
            'last_name_text',
            'nickname_text',
            'email_addr_text',
            'owner_id',
            'active_ind',
        ];

        $o_users = $users_owners->get($_columns);
        $a_users = $users_admins->get($_columns);

        $o_users_array = json_decode($o_users);
        $a_users_array = json_decode($a_users);

        array_walk($o_users_array,
            function (&$o_user_array) {
                $o_user_array->admin = true;
            });

        array_walk($a_users_array,
            function (&$a_user_array) {
                $a_user_array->admin = false;
            });

        $result = array_merge($o_users_array, $a_users_array);
        $result = array_map("unserialize", array_unique(array_map("serialize", $result)));
        sort($result);

        return $this->renderView('app.users', ['users' => $result]);
    }

}




