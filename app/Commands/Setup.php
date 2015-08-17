<?php namespace DreamFactory\Enterprise\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Config\ClusterManifest;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Library\Utility\Disk;
use DreamFactory\Library\Utility\JsonFile;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Setup extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @var string The console command name */
    protected $name = 'dfe:setup';
    /**  @var string The console command description */
    protected $description = 'Initializes a new installation and generates a cluster environment file.';
    /**
     * @type array Any configuration read from config/dfe.setup.php
     */
    protected $_config = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function fire()
    {
        parent::fire();

        $this->_config = config('commands.setup');

        //  1. Make sure it's a clean install
        if (0 != ServiceUser::count()) {
            if ($this->option('force')) {
                $this->writeln('system has users. <comment>--force</comment> override in place.');
                $this->_backupServiceUsers();
            } else {
                $this->writeln('system has users. use --force to override.', 'error');

                return 1;
            }
        }

        //  2. Create initial admin user
        try {
            //  Delete all users
            \DB::table('service_user_t')->delete();

            //  Add our new user
            $_user = ServiceUser::create([
                'first_name_text' => 'System',
                'last_name_text'  => 'Administrator',
                'nickname_text'   => 'Admin',
                'email_addr_text' => $this->argument('admin-email'),
                'password_text'   => \Hash::make($this->option('admin-password')),
                'active_ind'      => 1,
            ]);

            if (empty($_user)) {
                throw new \Exception('Invalid response from user::create');
            }

            $this->writeln('user <comment>' . $this->argument('admin-email') . '</comment> created.', 'info');
        } catch (\Exception $_ex) {
            $this->writeln('Error while creating admin user: ' . $_ex->getMessage(), 'error');

            return 1;
        }

        //  2. Check permissions and required directories
        $_paths = config('commands.setup.required-directories', []);

        foreach ($_paths as $_path) {
            if (!Disk::ensurePath($_path)) {
                $this->writeln('Unable to create directory: ' . $_path, 'error');
            }
        }

        //  3. Create console and dashboard API key sets
        $_apiSecret = $this->option('api-secret') ?: $this->_generateApiSecret();
        $_consoleKey = AppKey::createKey(0, OwnerTypes::CONSOLE, ['server_secret' => $_apiSecret]);
        $_dashboardKey = AppKey::createKey(0, OwnerTypes::DASHBOARD, ['server_secret' => $_apiSecret]);

        //  4. Generate .dfe.cluster.json file
        ClusterManifest::make(base_path('database/dfe'), [
            'cluster-id'       => config('dfe.cluster-id'),
            'default-domain'   => config('provisioning.default-domain'),
            'signature-method' => config('dfe.signature-method'),
            'storage-root'     => config('provisioning.storage-root'),
            'console-api-url'  => config('dfe.security.console-api-url'),
            'console-api-key'  => $_apiSecret,
            'client-id'        => $_dashboardKey->client_id,
            'client-secret'    => $_dashboardKey->client_secret,
        ]);

        //  5.  Make a console environment
        $_config = <<<INI
DFE_CONSOLE_API_KEY={$_apiSecret}
DFE_CONSOLE_API_CLIENT_ID={$_consoleKey->client_id}
DFE_CONSOLE_API_CLIENT_SECRET={$_consoleKey->client_secret}
INI;

        $this->_writeFile('console.env', $_config);

        //  6.  Make a dashboard config file...
        $_config = <<<INI
DFE_CONSOLE_API_KEY={$_apiSecret}
DFE_CONSOLE_API_CLIENT_ID={$_dashboardKey->client_id}
DFE_CONSOLE_API_CLIENT_SECRET={$_dashboardKey->client_secret}
INI;

        return $this->_writeFile('dashboard.env', $_config);
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['admin-email', InputArgument::REQUIRED, 'The admin email address.'],
        ]);
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['force', null, InputOption::VALUE_NONE, 'Use to force re-initialization of system.'],
            ['no-manifest', null, InputOption::VALUE_NONE, 'Do not create a manifest file.'],
            ['no-keys', null, InputOption::VALUE_NONE, 'Do not create initialization keys.'],
            [
                'admin-password',
                null,
                InputOption::VALUE_OPTIONAL,
                'The admin account password to use.',
                'dfe.admin',
            ],
            [
                'api-secret',
                null,
                InputOption::VALUE_OPTIONAL,
                'The API secret to use. If not specified, one will be generated',
            ],
        ]);
    }

    /**
     * @param string $filename The name of the file relative to /database/dfe/
     * @param mixed  $contents
     * @param bool   $jsonEncode
     *
     * @return bool
     */
    protected function _writeFile($filename, $contents, $jsonEncode = false)
    {
        $_path = base_path() . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'dfe';

        if (!Disk::ensurePath($_path)) {
            $this->writeln('Unable to write to backup path <comment>' . $_path . '</comment>. Aborting.', 'error');

            return false;
        }

        return false !== file_put_contents($_path . DIRECTORY_SEPARATOR . ltrim($filename, DIRECTORY_SEPARATOR),
            $jsonEncode ? JsonFile::encode($contents) : $contents);
    }

    /**
     * @return bool
     */
    protected function _backupServiceUsers()
    {
        $_backupPath = base_path() . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'dfe';

        if (!Disk::ensurePath($_backupPath)) {
            $this->writeln('Unable to write to backup path <comment>' . $_backupPath . '</comment>. Aborting.',
                'error');

            return false;
        }

        $_users = [];

        /** @type ServiceUser $_user */
        foreach (ServiceUser::all() as $_user) {
            $_users[] = $_user->toArray();
        }

        JsonFile::encodeFile($_backupPath . DIRECTORY_SEPARATOR . 'service-user.backup.' . date('YmdHis') . '.json',
            $_users);

        return true;
    }

    /**
     * @return string
     */
    private function _generateApiSecret()
    {
        return rtrim(base64_encode(hash('sha256', microtime())), '=');
    }
}
