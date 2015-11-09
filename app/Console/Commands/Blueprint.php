<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Instance\Ops\Facades\InstanceApiClient;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;

class Blueprint extends ConsoleCommand implements SelfHandling
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:blueprint';
    /** @inheritdoc */
    protected $description = 'Generates an instance blueprint';
    /**
     * @type string Our session token
     */
    protected $token;
    /**
     * @type string Our endpoint
     */
    protected $endpoint;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function fire()
    {
        parent::fire();

        try {
            $_instance = $this->findInstance($this->argument('instance-id'));
            $_client = InstanceApiClient::connect($_instance);

            $_payload = [
                'email'       => $this->argument('admin-email'),
                'password'    => $this->argument('admin-password'),
                'remember_me' => false,
            ];

            //  Get services
            $_result = $_client->resources();

            $_result = $_client->post('/system/admin/session', $_payload);

            if (!is_object($_result) || !isset($_result->session_token)) {
                $this->writeln('response : ' . print_r($_result, true));
                throw new \Exception('Invalid response.');
            }

            $this->token = $_result->session_token;

            //  Get services
            $_result = $_client->get('/system?as_list=true');

            $this->writeln('response : ' . print_r($_result, true));

            return true;
        } catch (ModelNotFoundException $_ex) {
            $this->error('The instance-id "' . $this->argument('instance-id') . '" was not found.');
        } catch (\Exception $_ex) {
            $this->error($_ex->getMessage());
        }

        return false;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['instance-id', InputArgument::REQUIRED, 'The id of the instance to inspect.'],
                ['instance-uri', InputArgument::OPTIONAL, 'The URI of the instance (i.e. "http://localhost")'],
                ['admin-email', InputArgument::OPTIONAL, 'An instance administrator email'],
                ['admin-password', InputArgument::OPTIONAL, 'An instance administrator password'],
            ]);
    }
}
