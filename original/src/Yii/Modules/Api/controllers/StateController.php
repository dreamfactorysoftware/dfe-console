<?php
use Cerberus\Yii\Controllers\ResourceController;
use Cerberus\Yii\Models\Deploy\Instance;
use DreamFactory\Yii\Exceptions\RestException;
use Kisma\Core\Interfaces\HttpResponse;
use Kisma\Core\Utility\FilterInput;
use Kisma\Core\Utility\Log;

/**
 * API to set instance states
 */
class StateController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array An array of valid states to change
     */
    private static $_stateNames = array(
        'ready'    => 'ready_state_nbr',
        'platform' => 'platform_state_nbr',
    );

    //*************************************************************************
    //* Public Actions
    //*************************************************************************

    /**
     * Initialize and set our resource type
     */
    public function init()
    {
        $this->_resourceClass = 'Cerberus\\Yii\\Models\\Deploy\\Instance';

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function delete( $id )
    {
        throw new RestException( HttpResponse::Forbidden );
    }

    /**
     * @inheritdoc
     */
    public function put( $id, $payload )
    {
        throw new RestException( HttpResponse::Forbidden );
    }

    /**
     * @inheritdoc
     */
    public function get( $id )
    {
        $_instance = $this->_validateInstance( $id );

        return array(
            'ready_state'    => $_instance->ready_state_nbr,
            'platform_state' => $_instance->platform_state_nbr,
            'state'          => $_instance->state_nbr,
        );
    }

    /**
     * @inheritdoc
     */
    public function post( $id, $payload = null )
    {
        $_instanceId = FilterInput::post( 'instance_id' );
        $_stateName = trim( strtolower( FilterInput::post( 'state_name' ) ) );
        $_state = FilterInput::post( 'state' );

        if ( !array_key_exists( $_stateName, static::$_stateNames ) )
        {
            throw new RestException( 400, 'Invalid state name' );
        }

        $_instance = $this->_validateInstance( $_instanceId );

        $_count = $_instance->update( array(static::$_stateNames[$_stateName] => $_state) );

        return array(
            'state_name' => $_stateName,
            'state'      => $_state,
            'updated'    => $_count,
        );
    }

    /**
     * @param string $instanceId
     *
     * @return Instance
     * @throws RestException
     */
    protected function _validateInstance( $instanceId )
    {
        if ( null === ( $_instance = Instance::model()->byNameOrId( $instanceId )->find() ) )
        {
            throw new RestException( 404, 'Instance "' . $instanceId . '" was not found. ' . print_r( $_POST, true ) );
        }

        return $_instance;
    }

    /**
     * @inheritdoc
     */
    protected function _validateRequest( $id, $payload = null )
    {
        Log::debug( 'State validate: ' . print_r( $_POST, true ) . PHP_EOL . print_r( $_REQUEST, true ) );

        parent::_validateRequest( $id, $payload );
    }
}