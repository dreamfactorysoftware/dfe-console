<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Database\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Session;
use Validator;
use Excel;



class UserController extends ViewController
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

    public function edit($id, $user_type = null)
    {
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

    }

    public function store(Request $request)
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

        /** @noinspection PhpUndefinedMethodInspection */
        $user_data['password_text'] = Hash::make($user_data['new_password']);

        unset($user_data['active']);
        unset($user_data['new_password']);
        unset($user_data['system_admin']);

        try {
            $create_user->create($user_data);

            $result_text =
                'The user "' .
                $user_data['first_name_text'] .
                ' ' .
                $user_data['last_name_text'] .
                '" was created successfully!';
            $result_status = 'alert-success';

            Session::flash('flash_message', $result_text);
            Session::flash('flash_type', $result_status);

            return \Redirect::to($this->makeRedirectUrl('users'));
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

    public function update(Request $request, $id)
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

        if ($user_data['new_password'] != '********') {
            /** @noinspection PhpUndefinedMethodInspection */
            $user_data['password_text'] = Hash::make($user_data['new_password']);
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
                'The user "' .
                $user_data['first_name_text'] .
                ' ' .
                $user_data['last_name_text'] .
                '" was updated successfully!';
            $result_status = 'alert-success';

            Session::flash('flash_message', $result_text);
            Session::flash('flash_type', $result_status);

            return \Redirect::to($this->makeRedirectUrl('users'));
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

            Session::flash('flash_message', $result_text);
            Session::flash('flash_type', $result_status);

            return \Redirect::to($this->makeRedirectUrl('users'));
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

        $users = $this->_get_users();

        return $this->renderView('app.users', ['users' => $users]);

    }

    /**
     * Gets all users
     * @return string All users
     */
    public function get_users(Request $request){
        /* Need to get users anyway */
        $users = $this->_get_users();
        $rt = count($users);

        /* Determine if a datatables request */
        if($request->has('draw')){
            $dtParams = $request->all();

            /* Search for terms */
            if(isset($dtParams['search']['value']) && !empty($dtParams['search']['value'])){
                $users = $this->_search_users($users, $dtParams['search']['value']);
            }

            /* Sort users */
            $this->_sort_users($users, $dtParams);

            /* Important to get count before slicing */
            $tUsers = count($users);

            /* Slice users */
            $users = array_slice($users, $dtParams['start'], $dtParams['length']);

            $return = [
                'draw' => $dtParams['draw'],
                'recordsTotal' => $rt,
                'recordsFiltered' => $tUsers,
                'data' => []
            ];

            foreach($users as $k=>$user){
                $is_active = 0;
                /* Normalize active user flag - TODO: Need to redo this column in the database when combining tables. */
                if((isset($user['active_ind']) && $user['active_ind'] === true) || isset($user['active_ind']) && $user['active_ind'] == 1){
                    $is_active = 1;
                }
                $return['data'][] = [
                'original'   => json_encode($user),
                'create_date'=> $user['create_date'],
                'first_name' => $user['first_name_text'],
                'last_name'  => $user['last_name_text'],
                'email'      => $user['email_addr_text'],
                'company'    => (!is_null($user['company_name_text'])) ? $user['company_name_text'] : '',
                'phoneText'  => (!is_null($user['phone_text'])) ? $user['phone_text'] : '',
                    'is_active'  => $is_active
                ];
            }
        }

        return json_encode($return);
    }

    /**
     * Combinator function for combining and sorting service and admin users
     * @return array
     */
    protected function _get_users(){
        $users_owners = new ServiceUser;
        $users_admins = new User;

        $_columns = [
            'id',
            'create_date',
            'first_name_text',
            'last_name_text',
            'nickname_text',
            'email_addr_text',
            'owner_id',
            'active_ind',
        ];
        $o_users = $users_owners->get($_columns)->toArray();

        array_push($_columns,
            'company_name_text',
            'phone_text'
        );
        $a_users = $users_admins->get($_columns)->toArray();
        array_walk($o_users,
            function (&$o_users){
                $o_users['admin'] = true;
                $o_users['company_name_text'] = '';
                $o_users['phone_text'] = '';
            });

        array_walk($a_users,
            function (&$a_users){
                $a_users['admin'] = false;
            });

        /* Now we have a merged & complete array of users */
        $users = array_merge($o_users, $a_users);
        return $users;

    }

    public function export_excel(){
        $users = $this->_get_users();
        $this->_sort_users($users, array(), 'create_date', 'desc');

        $header = [
            'Create Date',
            'First Name',
            'Last Name',
            'Nickname',
            'Email Address',
            'Company Name',
            'Phone Number',
        ];

        $mapping_order = [
            'create_date',
            'first_name_text',
            'last_name_text',
            'nickname_text',
            'email_addr_text',
            'company_name_text',
            'phone_text',
        ];

        $target = array();
        foreach ($users as $idx=>$user){
            if(!isset($target[$idx])){
                foreach($mapping_order as $tgt_order){
                    $target[$idx][] = $user[$tgt_order];
                }
            }
        }
        array_unshift($target, $header);
        Excel::create('DFE_Users', function($excel) use ($target){
            $excel->sheet('DFE_Users', function($sheet) use ($target) {

                $sheet->fromArray($target, null, 'A1', true, false);

            });

        })->download('xlsx');




    }

    protected function _search_users($users, $term){
        $finds = array();
        foreach($users as $idx=>$user){
            if(stripos($user['email_addr_text'], $term) !== false ||
                stripos($user['first_name_text'], $term) !== false ||
                stripos($user['last_name_text'], $term) !== false){
                $finds[$idx] = $user;
            }

        }
        return $finds;
    }

    protected function _sort_users(&$users, $dtParams = array(), $srtCol = NULL, $srtDir = NULL){

        if(is_null($srtCol) && is_null($srtDir)){
            $srtCol = $dtParams['columns'][(int)$dtParams['order'][0]['column']]['name'];
            $srtDir = $dtParams['order'][0]['dir'];
        }


        /* Pull out sort order and act upon it */
        $sortable = array();
        foreach($users as $key=>$user){
            $sortable[$key] = strtolower($user[$srtCol]);
        }

        switch ($srtDir){
            case 'asc':
            default:
                array_multisort($sortable, SORT_ASC, $users);
                break;
            case 'desc':
                array_multisort($sortable, SORT_DESC, $users);
                break;
        }
    }

}
