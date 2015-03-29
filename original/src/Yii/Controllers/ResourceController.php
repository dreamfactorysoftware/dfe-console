<?php
namespace Cerberus\Yii\Controllers;

use CAction;
use Cerberus\Yii\Models\Auth\User;
use DreamFactory\Library\Utility\IfSet;
use DreamFactory\Yii\Controllers\SecureRestController;
use DreamFactory\Yii\Exceptions\RestException;
use DreamFactory\Yii\Models\BaseModel;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;
use Symfony\Component\HttpFoundation\Response;

/**
 * ResourceController
 * A generic resource controller
 */
class ResourceController extends SecureRestController
{
    //*************************************************************************
    //* Members
    //*************************************************************************

    /**
     * @var BaseModel
     */
    protected $_resource = null;
    /**
     * @var string
     */
    protected $_resourceClass = null;
    /**
     * @var User
     */
    protected $_resourceUser = null;
    /**
     * @var bool
     */
    protected $_adminView = false;
    /**
     * @var bool
     */
    protected $_enableWhitelist = true;
    /**
     * @type array An array of IP addresses that are allowed to talk to me
     */
    protected $_whitelistHosts = array('localhost');

    //*************************************************************************
    //* Public Actions
    //*************************************************************************

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        //  Load our whitelist
        $this->_whitelistHosts = $this->_loadWhitelist();
    }

    /**
     * @param CAction $action
     *
     * @throws \DreamFactory\Yii\Exceptions\RestException
     * @return bool
     */
    protected function beforeAction( $action )
    {
        if ( $this->_enableWhitelist )
        {
            $_ip = IfSet::get( $_SERVER, 'REMOTE_ADDR', '0.0.0.0' );

            if ( empty( $_ip ) || !in_array( $_ip, $this->_whitelistHosts ) )
            {
                Log::error( 'IP address not allowed: ' . $_ip );
                $this->_sendResponse( Response::HTTP_FORBIDDEN );
            }
        }

        return parent::beforeAction( $action );
    }

    /**
     * @return array
     */
    public function accessRules()
    {
        return array();
    }

    /**
     *
     */
    public function actionError()
    {
        $_error = Pii::currentError();

        $this->_sendResponse(
            Option::get( $_error, 'code' ),
            Option::get( $_error, 'message' )
        );
    }

    /**
     * Retrieve an instance
     *
     * @param string|int $id
     *
     * @return array
     */
    public function get( $id )
    {
        return $this->_validateRequest( $id )->getRestAttributes();
    }

    /**
     * @param string|int $id
     * @param array      $payload
     *
     * @throws RestException
     * @return array|null
     */
    public function put( $id, $payload )
    {
        return $this->post( $id, $payload );
    }

    /**
     * Delete a resource
     *
     * @param string|int $id
     *
     * @return bool
     * @throws \CDbException
     * @throws \DreamFactory\Yii\Exceptions\RestException
     */
    public function delete( $id )
    {
        return $this->_validateRequest( $id )->delete();
    }

    /**
     * Create/update a resource
     *
     * @param string|int $id
     * @param array      $payload
     *
     * @return array|null
     * @throws \DreamFactory\Yii\Exceptions\RestException
     */
    public function post( $id, $payload = null )
    {
        if ( empty( $this->_resourceClass ) )
        {
            throw new RestException( Response::HTTP_NOT_IMPLEMENTED );
        }

        if ( is_array( $id ) )
        {
            //	new
            $_resource = new $this->_resourceClass;
            $payload = $id;
            unset( $payload['id'] );
        }
        else
        {
            $_resource = $this->_validateRequest( $id, $payload );
        }

        unset( $payload['createDate'], $payload['lastModifiedDate'], $payload['userId'] );

        try
        {
            $_resource->setRestAttributes( $payload );
            $payload['user_id'] = Pii::user()->id;

            $_resource->save();

            return $_resource->getRestAttributes();
        }
        catch ( \CDbException $_ex )
        {
            Log::error( 'Exception saving resource "' . $this->_resourceClass . '::' . $_resource->id . '": ' . $_ex->getMessage() );
            throw new RestException( Response::HTTP_INTERNAL_SERVER_ERROR );
        }
    }

    /**
     * @param int|string $id
     * @param array      $payload
     *
     * @throws \DreamFactory\Yii\Exceptions\RestException
     * @return \Instance
     */
    protected function _validateRequest( $id, $payload = null )
    {
        if ( empty( $id ) )
        {
            throw new RestException( Response::HTTP_BAD_REQUEST );
        }

        throw new RestException( Response::HTTP_NOT_IMPLEMENTED );
    }

    /**
     * Loads the whitelist from the configuration file and resolves to IP addresses
     *
     * @return array The IP addresses of all allowed communicants. Unresolvable names will remain names.
     */
    protected function _loadWhitelist()
    {
        if ( null === ( $_hosts = Pii::appStoreGet( 'app.api_whitelist' ) ) )
        {
            $_hosts = array_merge( $this->_whitelistHosts, Pii::getParam( 'app.api_whitelist', array() ) );

            if ( is_array( $_hosts ) )
            {
                foreach ( $_hosts as $_index => $_host )
                {
                    $_hosts[$_index] = gethostbyname( $_host );
                }
            }

            Pii::appStoreSet( 'app.api_whitelist', $_hosts );

            Log::debug( 'API whitelist loaded: ', implode( ', ', $_hosts ) );
        }

        return $_hosts;
    }

    /**
     * @param \Cerberus\Yii\Models\Auth\User $resourceUser
     *
     * @return $this
     */
    public function setResourceUser( $resourceUser )
    {
        $this->_resourceUser = $resourceUser;

        return $this;
    }

    /**
     * @return \Cerberus\Yii\Models\Auth\User
     */
    public function getResourceUser()
    {
        return $this->_resourceUser;
    }

    /**
     * @param \DreamFactory\Yii\Models\BaseModel $resource
     *
     * @return ResourceController
     */
    public function setResource( $resource )
    {
        $this->_resource = $resource;

        return $this;
    }

    /**
     * @return \DreamFactory\Yii\Models\BaseModel
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * @param string $resourceClass
     *
     * @return $this
     */
    public function setResourceClass( $resourceClass )
    {
        $this->_resourceClass = $resourceClass;

        return $this;
    }

    /**
     * @return string
     */
    public function getResourceClass()
    {
        return $this->_resourceClass;
    }

    /**
     * @param boolean $adminView
     *
     * @return AuthResourceController
     */
    public function setAdminView( $adminView )
    {
        $this->_adminView = $adminView;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getAdminView()
    {
        return $this->_adminView;
    }

    /**
     * @param boolean $enableWhitelist
     *
     * @return ResourceController
     */
    public function setEnableWhitelist( $enableWhitelist )
    {
        $this->_enableWhitelist = $enableWhitelist;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableWhitelist()
    {
        return $this->_enableWhitelist;
    }

}