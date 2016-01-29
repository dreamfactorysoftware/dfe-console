<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;
use DreamFactory\Enterprise\Database\Enums\GuestLocations;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class InstanceController extends ViewController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_tableName = 'instance_t';
    /**
     * @type string
     */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Instance';
    /** @type string */
    protected $_resource = 'instance';

    protected $_prefix = 'v1';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function index()
    {
        return $this->renderView('app.instances', ['instances' => Instance::orderBy('instance_id_text')->with(['user', 'cluster'])->get()]);
    }

    /** @inheritdoc */
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
     * @return \DreamFactory\Enterprise\Console\Http\Controllers\Resources\InstanceController|\Illuminate\Support\Facades\Response|\Illuminate\View\View
     */
    public function delete()
    {
        //  Delete an instance
        $_instance = Instance::findOrFail($_id = \Input::get('instance-id'));

        if (0 != \Artisan::call('dfe:deprovision', ['instance-id' => $_id,])) {
            return $this->bounceBack('/instances', 'Instance "' . $_instance->instance_id_text . '" deprovisioning queue failure. Check logs for details.');
        }

        \Session::flash('flash_message', 'Instance "' . $_instance->instance_id_text . '" deprovisioning queued.');
        \Session::flash('flash_type', 'alert-success');

        return $this->index();
    }

    /** @inheritdoc */
    public function store(Request $request)
    {
        if (empty($_name = strtolower(trim(filter_var($request->input('instance_name_text', FILTER_SANITIZE_STRING)))))) {
            return $this->bounceBack('/instances/create', 'Instance name "' . $_name . '" is invalid.');
        }

        if (false === ($_instanceId = Instance::isNameAvailable($_name, \Auth::user()->admin_ind))) {
            return $this->bounceBack('/instances/create', 'Instance name "' . $_name . '" is not available.');
        }

        $_email = strtolower(trim(filter_var($request->input('owner_email'), FILTER_SANITIZE_EMAIL)));

        try {
            if (empty($_email)) {
                return $this->bounceBack('/instances/create', 'Invalid email address.');
            }

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

    /** @inheritdoc */
    public function edit($id)
    {
        return $this->renderView('app.instances.edit',
            [
                'instance_id' => $id,
                'instance'    => Instance::with(['user', 'cluster'])->find($id),
                'clusters'    => Cluster::orderBy('cluster_id_text')->get(),
            ]);
    }
}
