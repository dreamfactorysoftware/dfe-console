<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Users extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The console command name
     */
    protected $name = 'dfe:users';
    /**
     * @type string The console command description
     */
    protected $description = 'Manage <comment>DFE Dashboard</comment> users.';
    /**
     * @type array The allowed operation list
     */
    protected $operations = ['show', 'create', 'update', 'delete', 'activate', 'deactivate',];

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

        if (!filter_var($_email = $this->argument('email'), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('The email "' . $_email . '" is not valid.');
        }

        $_operation = trim(strtolower($this->argument('operation')));

        if (in_array($_operation, $this->operations) && method_exists($this, $_operation . 'User')) {
            return call_user_func([$this, $_operation . 'User'], $_email);
        }

        throw new \InvalidArgumentException('The operation "' . $_operation . '" is not valid.');
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function showUser($email)
    {
        try {
            /** @type User $_user */
            $_user = User::byEmail($email)->firstOrFail();
            $this->writeln($this->formatArray($_user->toArray(), !$this->option('ugly'), 'user'));

            return true;
        } catch (ModelNotFoundException $_ex) {
            $this->writeln('User "<comment>' . $email . '</comment>" not found.');

            return false;
        }
    }

    /**
     * @param string $email
     *
     * @return bool|int
     */
    protected function createUser($email)
    {
        try {
            if (false === ($_user = User::artisanRegister($this))) {
            }
        } catch (\Exception $_ex) {
            $this->error($_ex->getMessage());

            return 1;
        }

        $this->writeln('User ID#' . $_user->id . ' created:');
        $this->comment($this->formatArray($_user->toArray(), !$this->option('ugly'), 'user'));

        return 1;
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function updateUser($email)
    {
        try {
            $_first = $this->option('first-name');
            $_last = $this->option('last-name');
            $_nickname = $this->option('nickname');
            $_password = $this->option('password');

            if (empty($_first) && empty($_last) && empty($_password) && empty($_nickname)) {
                $this->error('At least one of "first name", "last name", "nickname", or "password" are required for an update.');

                return false;
            }

            $_user = User::byEmail($email)->firstOrFail();

            $_updates = [];

            $_first && $_updates['first_name_text'] = $_first;
            $_last && $_updates['last_name_text'] = $_last;
            $_nickname && $_updates['nickname'] = $_nickname;
            $_password && $_updates['password'] = $_password;

            if ($_user->update($_updates)) {
                $this->writeln('User "<info>' . $email . '</info>" updated');

                return 0;
            }

            $this->writeln('No changes saved for user "<info>' . $email . '</info>"');

            return 0;
        } catch (\Exception $_ex) {
            if (false !== stripos($_ex->getMessage(), 'duplicate entry')) {
                $this->error('User "' . $email . '" already exists');
            } else {
                $this->error('Error creating user "' . $email . '": ' . $_ex->getMessage());
            }

            return 1;
        }
    }

    /**
     * @param string $email
     *
     * @return bool
     */
    protected function deleteUser($email)
    {
        try {
            if (!User::byEmail($email)->delete()) {
                $this->error('User "' . $email . '" not found');

                return 1;
            }

            $this->writeln('User "' . $email . '" deleted');

            return 0;
        } catch (\Exception $_ex) {
            $this->error('Error deleting user "' . $email . '": ' . $_ex->getMessage());

            return 1;
        }
    }

    /**
     * @param string $email
     *
     * @return bool|int
     */
    protected function activateUser($email)
    {
        /** @type User $_user */
        $_user = User::byEmail($email)->firstOrFail();

        if ($_user->activate_ind) {
            $this->writeln('User "' . $email . '" already activated.');

            return true;
        }

        if (!$_user->update(['activate_ind' => 1,])) {
            $this->error('Error activating user "' . $email . '".');

            return false;
        }

        $this->writeln('User "' . $email . '" activated.');

        return true;
    }

    /**
     * @param string $email
     *
     * @return bool|int
     */
    protected function deactivateUser($email)
    {
        /** @type User $_user */
        $_user = User::byEmail($email)->firstOrFail();

        if (!$_user->activate_ind) {
            $this->writeln('User "' . $email . '" already deactivated.');

            return true;
        }

        if (!$_user->update(['activate_ind' => 0,])) {
            $this->error('Error deactivating user "' . $email . '".');

            return false;
        }

        $this->writeln('User "' . $email . '" deactivated.');

        return true;
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                [
                    'operation',
                    InputArgument::REQUIRED,
                    'The operation to perform: ' . implode(', ', $this->operations),
                ],
                ['email', InputArgument::REQUIRED, 'The email address of the target user'],
            ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),
            [
                [
                    'format',
                    null,
                    InputOption::VALUE_REQUIRED,
                    'Output <comment>show</comment> in alternate format. Valid types are: <info>json</info> and <info>xml</info>',
                ],
                [
                    'ugly',
                    'u',
                    InputOption::VALUE_NONE,
                    'For formatted output, does not <comment>pretty-print</comment> output',
                ],
                [
                    'escaped-slashes',
                    null,
                    InputOption::VALUE_NONE,
                    'For JSON formatted output, slashes will be escaped (default is that they are <comment>not</comment>)',
                    null,
                ],
                ['force', null, InputOption::VALUE_NONE, 'Use to force current operation.'],
                ['password', null, InputOption::VALUE_REQUIRED, 'The password of the target user'],
                ['first-name', 'f', InputOption::VALUE_REQUIRED, 'The first name of the target user'],
                ['last-name', 'l', InputOption::VALUE_REQUIRED, 'The last name of the target user'],
                ['nickname', null, InputOption::VALUE_OPTIONAL, 'The nickname of the target user'],
            ]);
    }
}
