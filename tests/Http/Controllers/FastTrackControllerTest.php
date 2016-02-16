<?php namespace DreamFactory\Enterprise\Console\Tests\Http\Controllers;

use DreamFactory\Library\Utility\Curl;
use Illuminate\Http\Response;

class FastTrackControllerTest extends \TestCase
{
    static $userId;

    public function testAutoRegister()
    {
        $_response = $this->post('http://console.dfe.3wipes.com/fast-track',
            [
                'email'      => 'mistertestler@gmail.com',
                'password'   => 'password',
                'first-name' => 'Mister',
                'last-name'  => 'Testler',
                'nickname'   => 'Mister',
            ]);

        $this->assertNotEmpty($_response->getStatus() != Response::HTTP_BAD_REQUEST);
        static::$userId = 'mistertesterler@gmail.com';
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass(); // TODO: Change the autogenerated stub

        try {
            \Artisan::call('dfe:deprovision', ['instance-id' => 'mistertestler']);
        } catch (\Exception $_ex) {
        }

        try {
            static::$userId && Curl::delete('/ops/v1/user/' . static::$userId);
        } catch (\Exception $_ex) {
        }
    }

}
