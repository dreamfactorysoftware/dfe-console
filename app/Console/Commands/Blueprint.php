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

            $_blueprint = ['instance' => $_instance->toArray()];
            $_resources = [];

            //  Get services
            $_result = $_client->resources();

            foreach ($_result as $_resource) {
                $_resources[$_resource->name] = [];

                try {
                    $_response = $_client->get($_resource->name);
                    $_resources[$_resource->name] =
                        isset($_response->resource) ? $_response->resource : $_response;
                } catch (\Exception $_ex) {
                }
            }

            $_blueprint['resources'] = $_resources;
            $this->writeln(json_encode($_blueprint, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

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
        return array_merge(
            parent::getArguments(),
            [
                ['instance-id', InputArgument::REQUIRED, 'The id of the instance to inspect.'],
                ['instance-uri', InputArgument::OPTIONAL, 'The URI of the instance (i.e. "http://localhost")'],
                ['admin-email', InputArgument::OPTIONAL, 'An instance administrator email'],
                ['admin-password', InputArgument::OPTIONAL, 'An instance administrator password'],
            ]
        );
    }
}
