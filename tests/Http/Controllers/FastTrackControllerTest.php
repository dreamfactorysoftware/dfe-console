<?php namespace DreamFactory\Enterprise\Console\Tests\Http\Controllers;

use DreamFactory\Enterprise\Database\Models\User;
use Illuminate\Http\Response;

class FastTrackControllerTest extends \TestCase
{
    static $emailAddress = 'mistertestler@gmail.com';
    static $deleteTestObjects = true;

    /**
     * Test the FastTrack instance endpoint
     */
    public function testAutoRegister()
    {
        $_response = $this->post('http://console.dfe.3wipes.com/fast-track',
            [
                'email'      => static::$emailAddress,
                'password'   => 'password',
                'first-name' => 'Mister',
                'last-name'  => 'Testler',
                'nickname'   => 'Mister',
            ]);

        $this->dump();

        $this->assertTrue($_response->getStatus() != Response::HTTP_BAD_REQUEST);
    }

    /** @inheritdoc */
    public function tearDown()
    {
        parent::tearDown();

        if (static::$deleteTestObjects) {
            try {
                \Artisan::call('dfe:deprovision', ['instance-id' => 'mistertestler']);
            } catch (\Exception $_ex) {
            }

            try {
                User::byEmail(static::$emailAddress)->delete();
            } catch (\Exception $_ex) {
            }
        }
    }
}
