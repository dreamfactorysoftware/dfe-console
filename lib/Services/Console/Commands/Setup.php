<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Common\Config\ClusterManifest;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\OwnerTypes;
use DreamFactory\Enterprise\Database\Models\AppKey;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Library\Utility\JsonFile;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Setup extends Command
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
        $this->output->writeln(
            '<info>DreamFactory Enterprise Setup and Initialization</info> (<comment>' . config( 'dfe.common.display-version' ) . '</comment>)'
        );
        $this->output->writeln( '<info>Copyright (c) 2012-2015 All Rights Reserved</info>' );
        $this->output->writeln( '' );

        //  1. Make sure it's a clean install
        if ( 0 != ServiceUser::count() )
        {
            if ( $this->option( 'force' ) )
            {
                $this->_writeln( 'system has users. <comment>--force</comment> override in place.' );
                $this->_backupServiceUsers();
            }
            else
            {
                $this->_error( 'system has users. use --force to override.' );

                return 1;
            }
        }

        //  2. Create initial admin user
        try
        {
            //  Delete all users
            \DB::table( 'service_user_t' )->delete();

            //  Add our new user
            $_user = ServiceUser::create(
                [
                    'first_name_text' => 'System',
                    'last_name_text'  => 'Administrator',
                    'nickname_text'   => 'Admin',
                    'email_addr_text' => $this->argument( 'admin-email' ),
                    'password_text'   => \Hash::make( $this->option( 'admin-password' ) ),
                ]
            );

            if ( empty( $_user ) )
            {
                throw new \Exception( 'Invalid response from user::create' );
            }

            $this->_info( 'user <comment>' . $this->argument( 'admin-email' ) . '</comment> created.' );
        }
        catch ( \Exception $_ex )
        {
            $this->_error( 'Error while creating admin user: ' . $_ex->getMessage() );

            return 1;
        }

        //  2. Check permissions and required directories

        //  3. Create console and dashboard API key sets
        $_consoleKey = AppKey::createKey( 0, OwnerTypes::CONSOLE );
        $_dashboardKey = AppKey::createKey( 0, OwnerTypes::DASHBOARD );
        $_apiSecret = $this->option( 'api-secret' ) ?: $this->_generateApiSecret();

        //  4. Generate .dfe.cluster.json file
        $_cluster = new ClusterManifest( base_path() );
        $_cluster->fill(
            [
                'cluster-id'                => config( 'dfe.cluster-id' ),
                'default-domain'            => config( 'dfe.provisioning.default-domain' ),
                'signature-method'          => config( 'dfe.signature-method' ),
                'storage-root'              => config( 'dfe.provisioning.storage-root' ),
                'console-api-url'           => config( 'dfe.console-api-url' ),
                'console-api-key'           => $_apiSecret,
                'console-api-client-id'     => $_consoleKey->client_id,
                'console-api-client-secret' => $_consoleKey->client_secret,
                'dashboard-client-id'       => $_dashboardKey->client_id,
                'dashboard-client-secret'   => $_dashboardKey->client_secret,
            ]
        );

        $_cluster->write();

        return 0;
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(
            parent::getArguments(),
            [
                ['admin-email', InputArgument::REQUIRED, 'The admin email address.'],
            ]
        );
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['force', null, InputOption::VALUE_NONE, 'Use to force re-initialization of system.'],
                ['no-manifest', null, InputOption::VALUE_NONE, 'Do not create a manifest file.'],
                ['no-keys', null, InputOption::VALUE_NONE, 'Do not create initialization keys.'],
                ['admin-password', null, InputOption::VALUE_OPTIONAL, 'The admin account password to use.', 'dfe.admin'],
                ['api-secret', null, InputOption::VALUE_OPTIONAL, 'The API secret to use. If not specified, one will be generated'],
            ]
        );
    }

    /**
     * @param string|array $message
     * @param int          $type
     *
     * @return mixed
     */
    protected function _error( $message, $type = OutputInterface::OUTPUT_NORMAL )
    {
        return $this->output->writeln( $this->_prefixOutput( 'setup', $message, 'error' ), $type );
    }

    /**
     * @param string|array $message
     * @param int          $type
     *
     * @return mixed
     */
    protected function _info( $message, $type = OutputInterface::OUTPUT_NORMAL )
    {
        return $this->output->writeln( $this->_prefixOutput( 'setup', $message, 'info' ), $type );
    }

    /**
     * @param string|array $message
     * @param int          $type
     *
     * @return mixed
     */
    protected function _writeln( $message, $type = OutputInterface::OUTPUT_NORMAL )
    {
        return $this->output->writeln( $this->_prefixOutput( 'setup', $message, 'info' ), $type );
    }

    /**
     * @param string       $prefix
     * @param string|array $messages
     * @param string       $context  An output context "info", "comment", "error", etc.
     * @param bool         $addColon If true, a colon will be appended to the prefix before concatenation
     *
     * @return array|string
     */
    protected function _prefixOutput( $prefix, $messages, $context = null, $addColon = true )
    {
        !is_array( $messages ) && ( $messages = array($messages) );

        $_prefixed = [];
        $_cOn = $_cOff = null;

        if ( $context )
        {
            $_cOn = '<' . $context . '>';
            $_cOff = '</' . $context . '>';
        }

        $_prefix = $_cOn . $prefix . $_cOff . ( $addColon ? ':' : null );

        foreach ( $messages as $_message )
        {
            $_prefixed[] = trim( $_prefix ) . ' ' . trim( $_message );
        }

        return 1 == sizeof( $_prefixed ) ? $_prefixed[0] : $_prefixed;
    }

    /**
     * @return bool
     */
    protected function _backupServiceUsers()
    {
        $_backupPath = base_path() . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'dfe';

        if ( !is_dir( $_backupPath ) && false === @mkdir( $_backupPath, 0777, true ) )
        {
            $this->_error( 'Unable to write to backup path <comment>' . $_backupPath . '</comment>. Aborting.' );

            return false;
        }

        $_users = [];

        /** @type ServiceUser $_user */
        foreach ( ServiceUser::all() as $_user )
        {
            $_users[] = $_user->toArray();
        }

        JsonFile::encodeFile( $_backupPath . DIRECTORY_SEPARATOR . 'service-user.backup.json', $_users );
    }

    /**
     * @return string
     */
    private function _generateApiSecret()
    {
        return rtrim( base64_encode( hash( 'sha256', microtime() ) ), '=' );
    }
}
