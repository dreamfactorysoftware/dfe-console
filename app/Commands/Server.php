<?php namespace DreamFactory\Enterprise\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\ServerTypes;
use DreamFactory\Library\Utility\JsonFile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Server extends ConsoleCommand
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:server';
    /** @inheritdoc */
    protected $description = 'Create, update, and delete servers';

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
        switch ( $_command = trim( strtolower( $this->argument( 'operation' ) ) ) )
        {
            case 'create':
            case 'update':
            case 'delete':
                return $this->{'_' . $_command . 'Server'}( $this->argument( 'server-id' ) );
        }

        throw new \InvalidArgumentException( 'The command "' . $_command . '" is invalid' );
    }

    /**
     * Create a server
     *
     * @param $serverId
     *
     * @return bool|\DreamFactory\Enterprise\Database\Models\Server
     */
    protected function _createServer( $serverId )
    {
        if ( false === ( $_data = $this->_prepareData( $serverId ) ) )
        {
            return false;
        }

        $_server = \DreamFactory\Enterprise\Database\Models\Server::create( $_data );

        $this->output->writeln( 'setup: server id <comment>' . $serverId . '</comment> created.' );

        return $_server;
    }

    /**
     * Update a server
     *
     * @param $serverId
     *
     * @return bool
     */
    protected function _updateServer( $serverId )
    {
        try
        {
            $_server = $this->_findServer( $serverId );

            if ( false === ( $_data = $this->_prepareData() ) )
            {
                return false;
            }

            if ( $_server->update( $_data ) )
            {
                $this->output->writeln( 'dfe:server: server id <comment>' . $serverId . '</comment> updated.' );

                return true;
            }

            $this->output->writeln( 'dfe:server: error updating server id <error>' . $serverId . '</error>' );

            return true;
        }
        catch ( ModelNotFoundException $_ex )
        {
            $this->error( 'dfe:server: The server-id "' . $serverId . '" is not valid.' );

            return false;
        }
        catch ( \Exception $_ex )
        {
            $this->error( 'dfe:server: Error updating server record: ' . $_ex->getMessage() );

            return false;
        }
    }

    /**
     * Update a server
     *
     * @param $serverId
     *
     * @return bool
     */
    protected function _deleteServer( $serverId )
    {
        try
        {
            $_server = $this->_findServer( $serverId );

            if ( $_server->delete() )
            {
                $this->output->writeln( 'dfe:server: server id <comment>' . $serverId . '</comment> deleted.' );

                return true;
            }

            $this->output->writeln( 'dfe:server: error deleting server id <error>' . $serverId . '</error>' );

            return true;
        }
        catch ( ModelNotFoundException $_ex )
        {
            $this->error( 'dfe:server: The server-id "' . $serverId . '" is not valid.' );

            return false;
        }
        catch ( \Exception $_ex )
        {
            $this->error( 'dfe:server: Error deleting server record: ' . $_ex->getMessage() );

            return false;
        }
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(
            parent::getArguments(),
            [
                [
                    'operation',
                    InputArgument::REQUIRED,
                    'The operation to perform: create, update, or delete',
                ],
                [
                    'server-id',
                    InputArgument::REQUIRED,
                    'The id of the server upon which to perform operation',
                ]
            ]
        );
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['server-type', 't', InputOption::VALUE_REQUIRED, 'The type of server: "db", "web", or "app"'],
                ['mount-id', 'm', InputOption::VALUE_REQUIRED, 'The id of the storage mount for this server'],
                ['host-name', 'a', InputOption::VALUE_REQUIRED, 'The host name of this server',],
                ['config', 'c', InputOption::VALUE_REQUIRED, 'JSON-encoded array of configuration data for this server'],
            ]
        );
    }

    /**
     * @param bool|string $create If false, no data will be required. Pass $serverId to have data be required and fill server_id_text field
     *
     * @return array|bool
     */
    protected function _prepareData( $create = false )
    {
        $_data = [];

        if ( !is_bool( $create ) )
        {
            $_serverId = trim( $create );
            $create = true;

            try
            {
                $this->_findServer( $_serverId );

                $this->error( 'dfe:server: The server-id "' . $_serverId . '" already exists.' );

                return false;
            }
            catch ( ModelNotFoundException $_ex )
            {
                //  This is what we want...
            }

            $_data['server_id_text'] = $_serverId;
        }

        //  Server type
        $_serverType = $this->option( 'server-type' );

        try
        {
            $_type = ServerTypes::defines( trim( strtoupper( $_serverType ) ), true );
            $_data['server_type_id'] = $_type;
        }
        catch ( \Exception $_ex )
        {
            if ( $create )
            {
                $this->error( 'dfe:server: The server-type "' . $_serverType . '" is not valid.' );

                return false;
            }
        }

        //  Mount
        $_mountId = $this->option( 'mount-id' );

        try
        {
            $_mount = $this->_findMount( $_mountId );
            $_data['mount_id'] = $_mount->id;
        }
        catch ( \Exception $_ex )
        {
            if ( $create )
            {
                $this->error( 'dfe:server: The mount-id "' . $_mountId . '" does not exist.' );

                return false;
            }
        }

        //  Host name
        $_host = $this->option( 'host-name' );

        if ( $create && empty( $_host ) )
        {
            $this->error( 'dfe:server: When creating a new server, you must specify a "host-name".' );

            return false;
        }

        !empty( $_host ) && ( $_data['host_text'] = $_host );

        //  Config (optional)
        $_config = $this->option( 'config' );

        empty( $_config ) && ( $_data['config_text'] = $_config = [] );

        if ( is_string( $_config ) )
        {
            try
            {
                $_config = JsonFile::decode( $_config );
                $_data['config_text'] = $_config;
            }
            catch ( \Exception $_ex )
            {
                $this->error( 'dfe:server: The "config" provided does not contain valid JSON.' );

                return false;
            }
        }

        return $_data;
    }

}
