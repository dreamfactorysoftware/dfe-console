<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Library\Fabric\Auditing\Services\AuditingService;
use DreamFactory\Library\Fabric\Database\Models\Auth\User;
use DreamFactory\Library\Fabric\Database\Models\Deploy\Instance;
use Illuminate\Http\Request;
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
        $_instance = $this->_findInstance( $request->input( 'id' ) );

        return array(
            'instanceName'     => $_instance->instance_name_text,
            'instanceId'       => $_instance->id,
            'vendorInstanceId' => $_instance->instance_id_text,
            'instanceState'    => $_instance->state_nbr,
            'vendorState'      => $_instance->vendor_state_nbr,
            'vendorStateName'  => $_instance->vendor_state_text,
            'provisioned'      => ( 1 == $_instance->provision_ind ),
            'trial'            => ( 1 == $_instance->trial_instance_ind ),
            'deprovisioned'    => ( 1 == $_instance->deprovision_ind ),
        );
    }
}
