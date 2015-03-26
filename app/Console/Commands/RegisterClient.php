<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Library\Fabric\Database\Models\Deploy\AppKey;
use Illuminate\Console\Command;
use Illuminate\Http\Response;
use Symfony\Component\Console\Input\InputArgument;

class RegisterClient extends Command
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @var string The console command name */
    protected $name = 'dfe:register-client';

    /**  @var string The console command description */
    protected $description = 'Creates a client ID and secret for a user';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $_key = config( 'dfe.client-hash-key' );
        $_clientId = hash_hmac( 'sha256', str_random( 40 ), $_key );
        $_clientSecret = hash_hmac( 'sha256', str_random( 40 ), $_key . $_clientId );

        $_result = AppKey::insert(
            array(
                'owner_id'      => $this->argument( 'user-id' ),
                'client_id'     => $_clientId,
                'client_secret' => $_clientSecret,
                'created_at'    => date( 'Y-m-d H-i-s' ),
            )
        );

        if ( !$_result )
        {
            abort( Response::HTTP_INTERNAL_SERVER_ERROR, 'Could not save new keys to database.' );
        }

        $this->getOutput()->writeln(
            [
                'Client registered successfully.',
                'Client ID:     ' . $_clientId,
                'Client Secret: ' . $_clientSecret,
            ]

        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['user-id', InputArgument::REQUIRED, 'The user id'],
        ];
    }

}
