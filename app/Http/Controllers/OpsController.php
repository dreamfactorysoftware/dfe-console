<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Commands\DeprovisionJob;
use DreamFactory\Enterprise\Services\Commands\ProvisionJob;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use DreamFactory\Library\Fabric\Database\Models\Deploy\InstanceArchive;
use DreamFactory\Library\Fabric\Database\Models\Deploy\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class OpsController extends Controller
{
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

            return SuccessPacket::make( $_result );
        }
        catch ( \Exception $_ex )
        {
            \Log::debug( 'Queuing error: ' . $_ex->getMessage() );

            return ErrorPacket::make( null, $_ex->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR, $_ex );
        }
    }

    /**
     * Deprovision an instance...
     *
     * @param Request $request
     *
     * @return array
     */
    public function postDeprovision( Request $request )
    {
        try
        {
            $_payload = $request->input();

            \Log::debug( 'Queuing deprovisioning request: ' . print_r( $_payload, true ) );

            $_result = \Queue::push( new DeprovisionJob( $request->input( 'instance-id' ), $_payload ) );

            return SuccessPacket::make( $_result );
        }
        catch ( \Exception $_ex )
        {
            \Log::debug( 'Queuing error: ' . $_ex->getMessage() );

            return ErrorPacket::make( null, $_ex->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR, $_ex );
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function postInstances( Request $request )
    {
        /** auth.client middleware sticks the validated owner into the session for us */
        $_owner = \Session::get( 'client.' . $request->input( 'access-token' ) );

        if ( empty( $_owner ) )
        {
            throw new \RuntimeException( 'No owner found in current session for request.' );
        }

        $_response = array();

        $_instances = Instance::where( 'user_id', $_owner->id )->get();

        if ( !empty( $_instances ) )
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
        \Log::debug( 'ops.status: ' . print_r( $request->input(), true ) );

        try
        {
            $_instance = $this->_findInstance( $request->input( 'id' ) );
            $_archived = false;
        }
        catch ( \Exception $_ex )
        {
            //  Check the deleted instances
            if ( null === ( $_instance = InstanceArchive::byNameOrId( $_id )->first() ) )
            {
                return ErrorPacket::create( Response::HTTP_NOT_FOUND, 'Instance not found.' );
            }

            $_archived = true;
        }

        $_rootStoragePath = $_instance->getRootStoragePath();
        $_storagePath = $_instance->getStoragePath();

        return SuccessPacket::make(
            array(
                'id'                 => $_instance->id,
                'archived'           => $_archived,
                'deleted'            => false,
                'metadata'           => $_instance->instance_data_text,
                'root-storage-path'  => $_rootStoragePath,
                'storage-path'       => $_storagePath,
                'owner-private-path' => $_rootStoragePath . DIRECTORY_SEPARATOR . '.private',
                'private-path'       => $_storagePath . DIRECTORY_SEPARATOR . '.private',
                //  snake
                'instance_name_text' => $_instance->instance_name_text,
                'instance_id_text'   => $_instance->instance_id_text,
                'state_nbr'          => $_instance->state_nbr,
                'vendor_state_nbr'   => $_instance->vendor_state_nbr,
                'vendor_state_text'  => $_instance->vendor_state_text,
                'provision_ind'      => ( 1 == $_instance->provision_ind ),
                'trial_instance_ind' => ( 1 == $_instance->trial_instance_ind ),
                'deprovision_ind'    => ( 1 == $_instance->deprovision_ind ),
                'start_date'         => (string)$_instance->start_date,
                'create_date'        => (string)$_instance->create_date,
                //  camel
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
                //  morse
                'instance-id'        => $_instance->id,
                'vendor-instance-id' => $_instance->instance_id_text,
                'instance-name'      => $_instance->instance_name_text,
                'instance-state'     => $_instance->state_nbr,
                'vendor-state'       => $_instance->vendor_state_nbr,
                'vendor-state-name'  => $_instance->vendor_state_text,
                'start-date'         => (string)$_instance->start_date,
                'create-date'        => (string)$_instance->create_date,
            )
        );
    }
}
