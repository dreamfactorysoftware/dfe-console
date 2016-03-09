<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Services\Jobs\RegisterJob;
use DreamFactory\Library\Utility\IfSet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Queue;
use Symfony\Component\Console\Input\InputArgument;

class Register extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @var string The console command name */
    protected $name = 'dfe:register';
    /**  @var string The console command description */
    protected $description = 'Creates a key pair used to communicate with the DFE Console';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $_command = new RegisterJob($this->argument('owner-id'), strtolower($this->argument('owner-type')));

        Queue::push($_command);

        $_result = $_command->getResult();

        if (empty($_result) || null === ($_id = IfSet::getDeep($_result, 'success', 'id'))) {
            $this->error('Results not found for request. Please try again.');

            return 1;
        }

        try {
            /** @type AppKey $_key */
            $_key = AppKey::findOrFail($_id);
        } catch (ModelNotFoundException $_ex) {
            $this->error('The key has been misplaced. Please try again.');

            return 2;
        }

        $this->writeln('<info>Key pair id "' . $_id . '" created. Please keep secure.</info>');
        $this->writeln('    <comment>client_id</comment>: <info>' . $_key->client_id . '</info>');
        $this->writeln('<comment>client_secret</comment>: <info>' . $_key->client_secret . '</info>');

        return 0;
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
                [
                    'owner-id',
                    InputArgument::REQUIRED,
                    'The id of the owner of this key',
                ],
                [
                    'owner-type',
                    InputArgument::REQUIRED,
                    'One of the following owner types: ' . OwnerTypes::prettyList(),
                ],
            ]);
    }
}
