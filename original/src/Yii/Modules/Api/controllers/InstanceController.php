<?php
/**
 * no namespace, Yii don't like 'em in controllers...
 * namespace Cerberus\Yii\Modules\Api\Controllers;
 */
use Cerberus\Enums\InstanceStates;
use Cerberus\Services\Hosting\Instance\Snapshot;
use Cerberus\Services\Provisioning\BaseProvisioner;
use Cerberus\Services\Provisioning\DreamFactory\Storage;
use Cerberus\Yii\Controllers\AuthResourceController;
use Cerberus\Yii\Models\Deploy\Instance;
use DreamFactory\Services\Provisioning\Jobs\Amazon\EC2;
use DreamFactory\Yii\Exceptions\RestException;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Log;

/**
 * InstanceController
 *
 * @var User $_requestUser
 */
class InstanceController extends AuthResourceController
{
    //*************************************************************************
    //* Constants
    //*************************************************************************

    /**
     * @var string
     */
    const CREDENTIAL_SET_DATABASE = 'database';
    /**
     * @var string
     */
    const CREDENTIAL_SET_PROVIDER = 'provider';
    /**
     * @var int
     */
    const State_Pending = 0;
    /**
     * @var int
     */
    const State_Running = 16;
    /**
     * @var int
     */
    const State_ShuttingDown = 32;
    /**
     * @var int
     */
    const State_Terminated = 48;
    /**
     * @var int
     */
    const State_Stopping = 64;
    /**
     * @var int
     */
    const State_Stopped = 80;

    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var bool
     */
    protected $_adminView = false;

    //*************************************************************************
    //* Public Actions
    //*************************************************************************

    /**
     * Initialize and set our resource type
     */
    public function init()
    {
        $this->_resourceClass = '\\Cerberus\\Yii\\Models\\Deploy\\Instance';

        parent::init();
    }

    /**
     * @param string $instanceId
     *
     * @return array
     */
    public function postProvisionStorage( $instanceId )
    {
        $_instance = $this->_validateRequest( $instanceId );

        $_storageService = new Storage( $this, $_instance );

        //	Storage key is defaulted to DSP name now.
        return $_storageService->provision( $_instance );
    }

    /**
     * @param int|string $instanceId
     * @param string     $credentialSet
     * @param string     $providerId For future use. Does nothing.
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     * @return array
     */
    public function getCredentials( $instanceId, $credentialSet = self::CREDENTIAL_SET_DATABASE, $providerId = null )
    {
        $_instance = $this->_validateRequest( $instanceId );

        switch ( $credentialSet )
        {
            case static::CREDENTIAL_SET_DATABASE:
                return array(
                    'instance'            => $_instance->toArray(),
                    'db_name'             => $_instance->db_name_text,
                    'db_user'             => $_instance->db_user_text,
                    'db_password'         => $_instance->db_password_text,
                    'storage_key'         => $_instance->storage_id_text,
                    'private_storage_key' => $_instance->user->storage_id_text,
                    'private_path'        => $_instance->getPrivatePath(),
                    'blob_storage_path'   => $_instance->getBlobStoragePath(),
                    'storage_path'        => $_instance->getStoragePath(),
                    'snapshot_path'       => $_instance->getSnapshotPath(),
                );

            case static::CREDENTIAL_SET_PROVIDER:
                $_models = GlobalProvider::model()->findAll( 'enable_ind = 1' );

                $_response = array();

                if ( !empty( $_models ) )
                {
                    /** @type GlobalProvider $_model */
                    foreach ( $_models as $_model )
                    {
                        $_response[] = $_model->toArray();
                    }
                }

                return $_response;

            default:
                throw new RestException( HttpResponse::BadRequest );
        }
    }

    /**
     * Delete a resource
     *
     * @param string|int $id
     *
     * @return bool
     * @throws \CDbException
     * @throws DreamFactory\Yii\Exceptions\RestException
     */
    public function delete( $id )
    {
        $_instance = $this->_validateRequest( $id );

        if ( 1 != $_instance->deprovision_ind && InstanceStates::DEPROVISIONED != $_instance->state_nbr )
        {
            throw new RestException( 400, 'Instance must be deprovisioned before deletion.' );
        }

        try
        {
            if ( !$_instance->delete() )
            {
                throw new \CDbException( $_instance->getErrorsForLogging() );
            }
        }
        catch ( \CDbException $_ex )
        {
            \Kisma\Core\Utility\Log::error( 'Exception deleting instance "' . $_instance->instance_name_text . '": ' . $_ex->getMessage() );
            throw new RestException( HttpResponse::BadRequest );
        }

        return true;
    }

    /**
     * @param string|int $id
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     * @return array|null
     */
    public function postStart( $id )
    {
        $_instance = $this->_validateRequest( $id );

        BaseProvisioner::getService( $_instance->guest_location_nbr, $_instance->user );

        $_ec2 = new EC2();
        $_response = $_ec2->getService()->start_instances( $_instance->instance_id_text );

        if ( !$_response->isOK() )
        {
            /** @noinspection PhpUndefinedMethodInspection */
            throw new RestException( HttpResponse::InternalServerError, $_response->body->to_json() );
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $_status = $_response->body->instancesSet->item->currentState;

        if ( $_status->code != static::State_Pending && $_status->code != static::State_Running )
        {
            throw new RestException( HttpResponse::InternalServerError, 'Invalid status returned from provisioning.' );
        }

        $_instance->vendor_state_nbr = (string)$_status->code;
        $_instance->vendor_state_text = (string)$_status->name;
        $_instance->save();

        return $_instance->getRestAttributes();
    }

    /**
     * @param int|string $id
     *
     * @return bool
     * @throws DreamFactory\Yii\Exceptions\RestException
     */
    public function postStop( $id )
    {
        $_instance = $this->_validateRequest( $id );
        $_ec2 = new EC2();

        $_response = $_ec2->getService()->stop_instances( $_instance->instance_id_text );

        /** @noinspection PhpUndefinedFieldInspection */
        $_status = $_response->body->instancesSet->item->currentState;

        if ( $_status->code != static::State_Stopping && $_status->code != static::State_Stopped )
        {
            throw new RestException( HttpResponse::InternalServerError, 'Invalid status returned from provisioning.' );
        }

        $_instance->vendor_state_nbr = (string)$_status->code;
        $_instance->vendor_state_text = (string)$_status->name;
        $_instance->save();

        return $_instance->getRestAttributes();
    }

    /**
     * @param int|string $id
     *
     * @return array
     * @throws DreamFactory\Yii\Exceptions\RestException
     */
    public function getVendorStatus( $id )
    {
        $_instance = $this->_validateRequest( $id );
        $_ec2 = new EC2();

        $_response = $_ec2->getService()->describe_instances( array('InstanceId' => $_instance->instance_id_text) );

        /** @noinspection PhpUndefinedFieldInspection */
        $_status = $_response->body->instancesSet->item->currentState;

        $_instance->vendor_state_nbr = (string)$_status->code;
        $_instance->vendor_state_text = (string)$_status->name;
        $_instance->save();

        return array(
            'vendorState'     => $_instance->vendor_state_nbr,
            'vendorStateName' => $_instance->vendor_state_text,
        );
    }

    /**
     * Creates a DSP snapshot
     *
     * @param int        $userId
     * @param string     $token
     * @param string|int $instanceId
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     * @return array|bool
     */
    public function postSnapshot( $userId, $token = null, $instanceId = null )
    {
        if ( $userId && $token )
        {
            $this->_validateUser( array('user_id' => $userId, 'access_token' => $token) );
        }
        else
        {
            if ( !$token && !$instanceId && $userId )
            {
                $instanceId = $userId;
            }
        }

        if ( empty( $instanceId ) )
        {
            throw new RestException( HttpResponse::BadRequest );
        }

        $_instance = $this->_validateRequest( $instanceId );

        try
        {
            $_id = $this->queueWork( $_instance->getAttributes(), 'snapshot' );
            Log::info( 'Snapshot queued: ' . $_id );

            return $_id;
        }
        catch ( \Exception $_ex )
        {
            throw new RestException( HttpResponse::InternalServerError, 'The snapshot service is currently not available. Please try again later.' );
        }
    }

    /**
     * Deletes a DSP snapshot
     *
     * @param string|int $instanceId
     * @param string     $snapshot
     *
     * @return array|bool
     */
    public function deleteSnapshot( $instanceId, $snapshot )
    {
        return true;
    }

    /**
     * Initiates an import of a snapshot to an instance
     *
     * @param string     $userId
     * @param string     $token
     * @param string|int $instanceId
     * @param string     $snapshot
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     * @return bool
     */
    public function postImport( $userId, $token, $instanceId, $snapshot )
    {
        $this->_validateUser( array('user_id' => $userId, 'access_token' => $token) );

        if ( empty( $instanceId ) || empty( $snapshot ) )
        {
            throw new RestException( HttpResponse::BadRequest );
        }

        $_instance = $this->_validateRequest( $instanceId );

        try
        {
            $_id = $this->queueWork( array_merge( array('snapshot_id' => $snapshot), $_instance->getAttributes() ), 'import' );
            Log::info( 'Import queued: ' . $_id );

            return $_id;
        }
        catch ( \Exception $_ex )
        {
            throw new RestException( HttpResponse::InternalServerError, 'The import service is currently not available. Please try again later.' );
        }
    }

    /**
     * Retrieves a DSP snapshot
     *
     * @param string|int $instanceId
     * @param string     $snapshot
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     *
     * @return array|bool
     */
    public function getSnapshot( $instanceId, $snapshot = null )
    {
        $_service = new Snapshot( $this );
        if ( null === $snapshot || 'latest' == strtolower( trim( $snapshot ) ) )
        {
            return $_service->latest( $instanceId );
        }

        if ( false === ( $_snapshot = $_service->download( $instanceId, $snapshot ) ) )
        {
            throw new RestException( HttpResponse::InternalServerError, 'Error retrieving snapshot.' );
        }

        return $_snapshot;
    }

    /**
     * Gets all snapshots
     *
     * @param string|int $instanceId
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     *
     * @return array|bool
     */
    public function getSnapshots( $instanceId )
    {
        $_service = new Snapshot( $this );
        if ( false === ( $_snapshot = $_service->all( $instanceId ) ) )
        {
            throw new RestException( HttpResponse::InternalServerError, 'Error enumerating snapshots.' );
        }

        return $_snapshot;
    }

    /**
     * Retrieve the status of an instance
     *
     * @param string $id
     *
     * @throws RestException
     * @return array
     */
    public function requestStatus( $id )
    {
        $_instance = $this->_validateRequest( $id );

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

    /**
     * @param int|string $id
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     */
    public function postTerminate( $id )
    {
        $_instance = $this->_validateRequest( $id );
        $_ec2 = new EC2();

        $_service = new Snapshot( $this );
        if ( false === ( $_snapshot = $_service->create( $id ) ) )
        {
            throw new RestException( HttpResponse::InternalServerError, 'Error creating pre-termination snapshot.' );
        }

        $_response = $_ec2->getService()->terminate_instances( $_instance->instance_id_text );

        if ( !$_response->isOK() )
        {
            throw new RestException( HttpResponse::InternalServerError, $_response->body );
        }

        /** @noinspection PhpUndefinedMethodInspection */
        Log::debug( 'Response body: ' . print_r( $_response->body->to_json(), true ) );

        /** @noinspection PhpUndefinedMethodInspection */

        return $_response->body->to_json();
    }

    /**
     * Starts a free trial
     *
     * @param array $payload
     *
     * @return bool
     * @throws CDbException
     * @throws RestException
     */
    public function postTrial( $payload )
    {
        list( $this->_resourceUser, $this->_requestUser ) = $this->_authenticateRequest( $payload );

        $_instance = Instance::model()->userOwned( $this->_resourceUser->id, $this->_adminView, true )->find();

        if ( null !== $_instance )
        {
            throw new RestException( HttpResponse::Conflict, 'Trial instance already started for this user.' );
        }

        $_requestedName = FilterInput::post( 'name', 'dsp_' . $this->_resourceUser->id );

        if ( null !== ( $_instance = Instance::model()->findByAttributes( array('instance_name_text' => $_requestedName) ) ) )
        {
            throw new RestException( HttpResponse::NotAcceptable, 'The name "' . $_requestedName . '" is not available.' );
        }

        return Instance::launch( $this->_resourceUser, $_requestedName );
    }

    /**
     * @param int|string $id
     * @param array      $payload
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     * @return Instance
     */
    protected function _validateRequest( $id, $payload = null )
    {
        if ( empty( $id ) )
        {
            throw new RestException( HttpResponse::BadRequest );
        }

        $_instance = Instance::model()->find(
            'instance_name_text = :id or id = :id',
            array(
                ':id' => $id,
            )
        );

        if ( null === $_instance )
        {
            throw new RestException( HttpResponse::NotFound, 'Instance ID "' . $id . '" Not found.' );
        }

        return $_instance;
    }

    /**
     * @param array $payload
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     * @return User
     */
    protected function _authenticateRequest( $payload = null )
    {
        if ( empty( $payload ) && !empty( $_POST ) )
        {
            $payload = $_POST;
        }

        if ( false !== strpos( $_SERVER['REQUEST_URI'], '/api/instance/credentials/' ) || false !== strpos(
                $_SERVER['REQUEST_URI'],
                '/api/instance/provisionStorage/'
            )
        )
        {
            return array();
        }

        return $this->_validateUser( $payload );
    }

    /**
     * @return array
     */
    public function postInstances()
    {
        if ( !$this->_resourceUser->instances )
        {
            return array();
        }

        $_response = array();

        /** @var $_instance Instance */
        foreach ( $this->_resourceUser->instances as $_instance )
        {
            if ( !empty( $_instance->instance_name_text ) && !empty( $_instance->instance_id_text ) )
            {
                if ( !isset( $_response[$_instance->instance_name_text] ) )
                {
                    $_response[$_instance->instance_name_text] = array();
                }

                $_response[$_instance->instance_name_text][] = $_instance->getRestAttributes();
            }

            unset( $_instance );
        }

        return $_response;
    }

    /**
     * Locates an instance based on db user/pass
     *
     * @param string $user
     * @param string $pass
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     * @return bool|array
     */
    public function postLocate( $user, $pass )
    {
        if ( empty( $user ) || empty( $pass ) )
        {
            throw new RestException( HttpResponse::BadRequest, 'Missing credentials.' );
        }

        /** @var Instance $_instance */
        $_instance = Instance::model()->find(
            'db_user_text = :user and db_password_text = :pass',
            array(
                ':user' => $user,
                ':pass' => $pass,
            )
        );

        if ( empty( $_instance ) )
        {
            throw new RestException( HttpResponse::NotFound );
        }

        return $_instance->getRestAttributes();
    }

    /**
     * Sets instance_t.ready_state_nbr
     *
     * @param string $id
     * @param int    $state
     *
     * @return array
     * @throws RestException
     */
    public function postReadyState( $id, $state )
    {
        return array(
            'success' => $this->_validateRequest( $id )->update( array('ready_state_nbr' => $state) ) ? true : false
        );
    }

    /**
     * Sets instance_t.platform_state_nbr
     *
     * @param string $id
     * @param int    $state
     *
     * @return array
     * @throws RestException
     */
    public function postPlatformState( $id, $state )
    {

        return array(
            'success' => $this->_validateRequest( $id )->update( array('platform_state_nbr' => $state) ) ? true : false
        );
    }

    /**
     * Gets all platform states
     *
     * @param string $id
     *
     * @return array
     * @throws RestException
     */
    public function getStates( $id )
    {
        $_instance = $this->_validateRequest( $id );

        return array(
            'states' => array(
                'state'          => $_instance->state_nbr,
                'ready_state'    => $_instance->ready_state_nbr,
                'platform_state' => $_instance->platform_state_nbr,
            ),
        );
    }

    /**
     * @param string $id
     *
     * @throws DreamFactory\Yii\Exceptions\RestException
     * @return GlobalProvider
     */
    protected function _validateProvider( $id )
    {
        $_provider =
            GlobalProvider::model()->find( 'provider_name_text = :id AND enable_ind = :enable_ind', array(':id' => $id, ':enable_ind' => 1) );

        if ( null === $_provider )
        {
            throw new RestException( HttpResponse::NotFound, 'Invalid or disabled provider' );
        }

        return $_provider;
    }
}
