<?php namespace DreamFactory\Enterprise\Console\Tests\Http\Controllers;

use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use GuzzleHttp\Client;
use GuzzleHttp\Message\ResponseInterface;
use Illuminate\Http\Request;

/**
 * Tests the OpsController
 */
class OpsControllerTest extends \TestCase
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type Client
     */
    protected static $_client;
    /**
     * @type string
     */
    protected static $_clientId;
    /**
     * @type string
     */
    protected static $_signature;
    /**
     * @type string
     */
    protected static $_baseUrl = 'http://dfe-console.local/api/v1/ops/';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$_client = new Client(
            [
                'base_url' => static::$_baseUrl,
                'defaults' => ['exceptions' => false],
            ]
        );
    }

    /**
     * Tests /status of valid and invalid instances
     */
    public function testPostStatus()
    {
        //  Get status of an invalid instance (xx)
        $_response = $this->_apiCall( 'status', ['id' => 'xx'] );
        $this->assertFalse( $_response->success );
        $this->assertEquals( 404, $_response->status_code );

        //  Get status of a valid instance
        $_response = $this->_apiCall( 'status', ['id' => 'rave-test1'] );
        $this->assertTrue( $_response->success );
        $this->assertEquals( 200, $_response->status_code );
    }

    public function testPostInstances()
    {
        $_response = $this->_apiCall( 'instances' );

        $this->assertEquals( 200, $_response->status_code );
    }

    /**
     * @param array $payload
     *
     * @return array
     */
    protected function _signPayload( array $payload )
    {
        return array_merge(
            array(
                'client-id'    => static::$_clientId,
                'access-token' => static::$_signature,
            ),
            $payload ?: []
        );

    }

    /**
     * @param string $clientId
     * @param string $clientSecret
     *
     * @return string
     */
    protected static function _generateSignature( $clientId, $clientSecret )
    {
        return hash_hmac( config( 'dfe.signature-method', EnterpriseDefaults::DEFAULT_SIGNATURE_METHOD ), $clientId, $clientSecret );
    }

    /**
     * @param ResponseInterface $response
     * @param bool              $object
     *
     * @return mixed
     */
    protected function _ensureResponse( $response, $object = true )
    {
        $this->assertTrue( $response instanceof ResponseInterface );

        return $response->json( ['object' => $object] );
    }

    /**
     * @param string $url
     * @param array  $payload
     * @param array  $options
     * @param string $method
     * @param bool   $object
     *
     * @return mixed
     */
    protected function _apiCall( $url, $payload = [], $options = [], $method = Request::METHOD_POST, $object = true )
    {
        static::$_clientId = config( 'dfe.console-api-client-id' );
        static::$_signature = static::_generateSignature( static::$_clientId, config( 'dfe.console-api-client-secret' ) );

        $_request = static::$_client->createRequest(
            $method,
            ltrim( $url, '/ ' ),
            array_merge( $options, ['json' => $this->_signPayload( $payload )] )
        );

        $_response = static::$_client->send( $_request );

        return $this->_ensureResponse( $_response, $object );
    }
}
