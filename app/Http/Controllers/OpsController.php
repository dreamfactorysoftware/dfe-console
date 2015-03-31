<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Contracts\InstanceProvisioner;
use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use DreamFactory\Enterprise\Services\Auditing\Services\AuditingService;
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\InstanceArchive;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class OpsController extends Controller
{
    //*************************************************************************
    //	Constants
    //*************************************************************************

    /**
     * @var string
     */
    const DEFAULT_FACILITY = AuditingService::DEFAULT_FACILITY;

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var string
     */
    protected $_instanceName;
    /**
     * @type User
     */
    protected $_user;
    /**
     * @type string
     */
    protected $_clientId;

    //********************************************************************************
    //* Public Methods
    //********************************************************************************

    /**
     * ctor
     */
    public function __construct()
    {
        $this->middleware( 'auth.client' );
    }

    /**
     * Provision an instance...
     *
     * @param Request $request
     *
     * @return array
     */
    public function postProvision( Request $request )
    {
        try
        {
            $_payload = $request->input();

            \Log::debug( 'Queuing provisioning request: ' . print_r( $_payload, true ) );

            $_result = \Queue::push( new ProvisionJob( $request->input( 'instance-id' ), $_payload ) );

            \Log::debug( 'Queuing returned result: ' . print_r( $_result, true ) );
        }
        catch ( \Exception $_ex )
        {
            \Log::debug( 'Queuing error: ' . $_ex->getMessage() );

            return ErrorPacket::make( null, $_ex->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR, $_ex );
        }

        return SuccessPacket::make();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function postInstances( Request $request )
    {
        /** auth.client middleware sticks the validated user into the session for us */
        $_user = \Session::get( 'client.' . $request->input( 'access-token' ) );

        $_response = array();

        $_instances = Instance::where( 'user_id', $_user->id )->get();

        if ( $_instances )
        {
            /** @type Instance $_instance */
            foreach ( $_instances as $_instance )
            {
                if ( !empty( $_instance->instance_name_text ) )
                {
                    $_response[$_instance->instance_name_text] = $_instance->toArray();
                }

                unset( $_instance );
            }
        }

        return SuccessPacket::make( $_response );
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function postStatus( Request $request )
    {
        $_id = $request->input( 'id' );

        try
        {
            $_instance = $this->_findInstance( $request->input( 'id' ) );
            $_archived = false;
        }
        catch ( ModelNotFoundException $_ex )
        {
            //  Check the deleted instances
            if ( null === ( $_instance = InstanceArchive::byNameOrId( $_id )->first() ) )
            {
                return ErrorPacket::make( 'Instance not found.' );
            }

            $_archived = true;
        }

        return SuccessPacket::make(
            array(
                'instance_name_text' => $_instance->instance_name_text,
                'id'                 => $_instance->id,
                'instance_id_text'   => $_instance->instance_id_text,
                'state_nbr'          => $_instance->state_nbr,
                'vendor_state_nbr'   => $_instance->vendor_state_nbr,
                'vendor_state_text'  => $_instance->vendor_state_text,
                'provision_ind'      => ( 1 == $_instance->provision_ind ),
                'trial_instance_ind' => ( 1 == $_instance->trial_instance_ind ),
                'deprovision_ind'    => ( 1 == $_instance->deprovision_ind ),
                'start_date'         => (string)$_instance->start_date,
                'create_date'        => (string)$_instance->create_date,
                'instanceName'       => $_instance->instance_name_text,
                'instanceId'         => $_instance->id,
                'vendorInstanceId'   => $_instance->instance_id_text,
                'instanceState'      => $_instance->state_nbr,
                'vendorState'        => $_instance->vendor_state_nbr,
                'vendorStateName'    => $_instance->vendor_state_text,
                'provisioned'        => ( 1 == $_instance->provision_ind ),
                'trial'              => ( 1 == $_instance->trial_instance_ind ),
                'deprovisioned'      => ( 1 == $_instance->deprovision_ind ),
                'startDate'          => (string)$_instance->start_date,
                'createDate'         => (string)$_instance->create_date,
                'archived'           => $_archived,
                'deleted'            => false,
            )
        );
    }
}
