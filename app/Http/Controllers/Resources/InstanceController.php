<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Common\Utility\ConfigWriter;
use DreamFactory\Enterprise\Common\Utility\Ini;
use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Config;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;
use DreamFactory\Library\Utility\Disk;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class InstanceController extends ViewController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $tableName = 'instance_t';
    /**
     * @type string
     */
    protected $model = 'DreamFactory\\Enterprise\\Database\\Models\\Instance';
    /** @type string */
    protected $resource = 'instance';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @noinspection PhpMissingParentCallCommonInspection */
    public function index()
    {
        logger('index');

        return $this->renderView('app.instances', ['instances' => Instance::orderBy('instance_id_text')->with(['user', 'cluster'])->get()]);
    }

    public function get_instances(Request $request){

        $instances = Instance::orderBy('instance_id_text')->with(['user', 'cluster'])->get()->toArray();
        $rt = count($instances);

        /* Determine if a datatables request */
        if($request->has('draw')){
            $dtParams = $request->all();

            /* Search for terms */
            if(isset($dtParams['search']['value']) && !empty($dtParams['search']['value'])){
                $instances = $this->_search_instances($instances, $dtParams['search']['value']);
            }

            /* Sort users */
            $this->_sort_instances($instances, $dtParams);

            /* Important to get count before slicing */
            $tInstances = count($instances);

            /* Slice users */
            $instances = array_slice($instances, $dtParams['start'], $dtParams['length']);


            $return = [
                'draw' => $dtParams['draw'],
                'recordsTotal' => $rt,
                'recordsFiltered' => $tInstances,
                'data' => []
            ];

            foreach($instances as $k=>$instance){
                $return['data'][] = [
                    'id'               => $instance['id'],
                    'instance_id_text' => $instance['instance_id_text'],
                    'email_addr_text'  => $instance['user']['email_addr_text'],
                    'cluster'          => $instance['cluster']['cluster_id_text'],
                    'create_date'      => $instance['create_date'],
                    'default_domain'   => $instance['instance_data_text']['env']['default-domain']
                ];
            }
        }

        return isset($return) ? json_encode($return) : array();
    }

    protected function _search_instances($instances, $term){
        $finds = array();
        foreach($instances as $idx=>$instance){
            $email = (isset($instance['user']['email_addr_text'])) ? $instance['user']['email_addr_text'] : null;
            if(stripos($instance['instance_id_text'], $term) !== false ||
                stripos($email, $term) !== false ){
                $finds[$idx] = $instance;
            }

        }
        return $finds;
    }

    protected function _sort_instances(&$instances, $dtParams){
        $srtCol = $dtParams['columns'][(int)$dtParams['order'][0]['column']]['name'];
        $srtDir = $dtParams['order'][0]['dir'];

        /* Pull out sort order and act upon it */
        $sortable = array();
        foreach($instances as $key=>$instance){
            switch($srtCol){
                case 'email_addr_text':
                    $sortable[$key] = strtolower($instance['user'][$srtCol]);
                    break;
                case 'cluster':
                    $sortable[$key] = strtolower($instance['cluster']['cluster_id_text']);
                    break;
                default:
                    $sortable[$key] = strtolower($instance[$srtCol]);
                    break;
            }
        }

        switch ($srtDir){
            case 'asc':
            default:
                array_multisort($sortable, SORT_ASC, $instances);
                break;
            case 'desc':
                array_multisort($sortable, SORT_DESC, $instances);
                break;
        }
    }


    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * @param array $viewData
     *
     * @return \Illuminate\View\View
     */
    public function create(array $viewData = [])
    {
        return $this->renderView('app.instances.create', ['clusters' => Cluster::orderBy('cluster_id_text')->get()]);
    }

    /**
     * @param string $where
     * @param array  $errors
     *
     * @return $this
     */
    protected function bounceBack($where, $errors = [])
    {
        return \Redirect::to('/' . $this->getUiPrefix() . '/' . ltrim($where, '/'))->withInput()->withErrors($errors);
    }

    /**
     * @param int $instanceId
     *
     * @return \DreamFactory\Enterprise\Console\Http\Controllers\Resources\InstanceController|\Illuminate\Support\Facades\Response|\Illuminate\View\View
     */
    public function delete($instanceId)
    {
        //  Delete an instance
        try {
            /** @type Instance $_instance */
            $_instance = Instance::findOrFail($instanceId);

            if (0 != \Artisan::call('dfe:deprovision', ['instance-id' => $instanceId,])) {
                return $this->bounceBack('/instances',
                    ['Instance "' . $_instance->instance_id_text . '" deprovisioning queue failure. Check logs for details.']);
            }

            \Session::flash('flash_message', 'Instance "' . $_instance->instance_id_text . '" deprovisioning queued.');
            \Session::flash('flash_type', 'alert-success');

            return $this->bounceBack('instances');
        } catch (ModelNotFoundException $_ex) {
            return $this->bounceBack('instances', ['The instance was not found. Deprovisioning failure.']);
        }
    }

    /** @inheritdoc */
    public function store(Request $request)
    {
        if (empty($_name = strtolower(trim(filter_var($request->input('instance_name_text', FILTER_SANITIZE_STRING)))))) {
            return $this->bounceBack('/instances/create', 'Instance name "' . $_name . '" is invalid.');
        }

        /** @noinspection PhpUndefinedFieldInspection */
        if (false === ($_instanceId = Instance::isNameAvailable($_name, \Auth::user()->admin_ind))) {
            return $this->bounceBack('/instances/create', 'Instance name "' . $_name . '" is not available.');
        }

        $_email = strtolower(trim(filter_var($request->input('owner_email'), FILTER_SANITIZE_EMAIL)));

        try {
            if (empty($_email)) {
                return $this->bounceBack('/instances/create', 'Invalid email address.');
            }

            /** @type User $_owner */
            $_owner = User::byEmail($_email)->firstOrFail();
        } catch (ModelNotFoundException $_ex) {
            return $this->bounceBack('/instances/create', 'Owner "' . $_email . '" not registered.');
        }

        if (0 != \Artisan::call('dfe:provision', ['owner-id' => $_owner->id, 'instance-id' => $_name, 'guest-location' => GuestLocations::DFE_CLUSTER])) {
            return $this->bounceBack('/instances/create', 'Error while provisioning instance. Check console log for details.');
        }

        \Session::flash('flash_message', 'Instance "' . $_name . '" provisioning queued.');
        \Session::flash('flash_type', 'alert-success');

        return $this->index();
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * @param $id
     *
     * @return array|\Illuminate\View\View
     */
    public function edit($id)
    {
        return $this->renderView('app.instances.edit',
            [
                'instance_id' => $id,
                'instance'    => Instance::with(['user', 'cluster'])->find($id),
                'clusters'    => Cluster::orderBy('cluster_id_text')->get(),
            ]);
    }

    /**
     * Show default provisioning settings page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPackages()
    {
        logger('getPackages');

        return $this->renderView('app.instances.packages',
            [
                'package_storage_path' => Config::getValue('packages.storage-path'),
                'packages'             => Config::getValue('packages.package-list', []),
                'default_packages'     => Config::getValue('packages.default-packages', Ini::parseDelimitedString(env('DFE_DEFAULT_PACKAGES'))),
            ]);
    }

    /**
     * Store default provisioning settings
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \DreamFactory\Enterprise\Console\Http\Controllers\Resources\InstanceController
     */
    public function postPackages(Request $request)
    {
        logger('postPackages');

        try {
            \Session::flash('flash_type', 'alert-success');

            $_packages = $request->input('package-list', []);
            Config::putValue('packages.default-packages', $_packages);
            \Session::flash('flash_message', 'Default packages updated.');

            if (\Input::file('package-upload')) {
                $_name = \Input::file('package-upload')->getClientOriginalName();
                logger('[dfe.resources.instance] package upload: ' . \Input::file('package-upload')->getClientOriginalName());
                \Input::file('package-upload')->move(Disk::path([config('provisioning.package-storage-path')], true),
                    $_name);

                $_packages = Config::getValue('packages.package-list', []);

                if (!in_array($_name, $_packages)) {
                    $_packages[] = $_name;
                    Config::putValue('packages.package-list', $_packages);
                    \Session::flash('flash_message', 'Package uploaded.');
                } else {
                    \Session::flash('flash_message', 'Duplicate package. Not saved.');
                    \Session::flash('flash_type', 'alert-warning');
                };
            }

            return $this->bounceBack('instances/packages');
        } catch (FileException $_ex) {
            return $this->bounceBack('instances/packages', ['There was a problem with your package upload.']);
        } catch
        (\Exception $_ex) {
            return $this->bounceBack('instances/packages', ['Error storing provisioning defaults. ' . $_ex->getMessage()]);
        }
    }
}
