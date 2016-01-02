<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Facades as Facades;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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

    /**
     * Fire the command
     *
     * @return bool
     */
    public function handle()
    {
        parent::handle();

        try {
            $_service = Facades\Blueprint::service();

            $_blueprint = $_service->make($this->argument('instance-id'),
                [
                    'commit' => !$this->option('no-commit'),
                    'user'   => [
                        'email'       => $this->argument('admin-email'),
                        'password'    => $this->argument('admin-password'),
                        'remember_me' => false,
                    ],
                ]);

            if ($this->option('dump')) {
                $this->writeln(json_encode($_blueprint, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            }

            return true;
        } catch (ModelNotFoundException $_ex) {
            $this->error('The instance-id "' . $this->argument('instance-id') . '" was not found.');
        } catch (\Exception $_ex) {
            $this->error($_ex->getMessage());
        }

        return false;
    }

    /** @inheritdoc */
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

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['no-commit', null, InputOption::VALUE_NONE, 'Do not commit the result to the repo',],
                ['dump', 'd', InputOption::VALUE_NONE, 'Dump the blueprint to stdout',],
            ]);
    }
}
