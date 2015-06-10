<?php namespace DreamFactory\Enterprise\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Enums\ServerTypes;
use DreamFactory\Enterprise\Database\Models;
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
                ['server-type', 't', InputOption::VALUE_REQUIRED, 'The type of server: ' . implode( ', ', ServerTypes::getDefinedConstants( true ) )],
                ['mount-id', 'm', InputOption::VALUE_REQUIRED, 'The id of the storage mount for this server'],
                ['host-name', 'a', InputOption::VALUE_REQUIRED, 'The host name of this server',],
                ['config', 'c', InputOption::VALUE_REQUIRED, 'JSON-encoded array of configuration data for this server'],
            ]
        );
    }

    /**
     * Handle the command
     *
     * @return mixed
     */
    public function fire()
    {
        parent::fire();

        switch ( $_command = trim( strtolower( $this->argument( 'operation' ) ) ) )
        {
            case 'create':
            case 'update':
            case 'delete':
                return $this->{'_' . $_command . 'Server'}( $this->argument( 'server-id' ) );
        }

        throw new \InvalidArgumentException( 'The "' . $_command . '" operation is not valid' );
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

        $_server = Models\Server::create( $_data );

        $this->concat( 'server id ' )->asComment( $serverId )->flush( ' created.' );

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
            if ( false === ( $_data = $this->_prepareData() ) )
            {
                return false;
            }

            if ( $this->_findServer( $serverId )->update( $_data ) )
            {
                $this->concat( 'server id ' )->asComment( $serverId )->flush( ' updated.' );

                return true;
            }

            $this->writeln( 'error updating server id "' . $serverId . '"', 'error' );
        }
        catch ( ModelNotFoundException $_ex )
        {
            $this->writeln( 'server-id "' . $serverId . '" is not valid.', 'error' );
        }
        catch ( \Exception $_ex )
        {
            $this->writeln( 'error updating server record: ' . $_ex->getMessage(), 'error' );
        }

        return false;
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
                $this->concat( 'server id ' )->asComment( $serverId )->flush( ' deleted.' );

                return true;
            }

            $this->writeln( 'error deleting server id "' . $serverId . '"', 'error' );

            return true;
        }
        catch ( ModelNotFoundException $_ex )
        {
            $this->writeln( 'the server-id "' . $serverId . '" is not valid.', 'error' );

            return false;
        }
        catch ( \Exception $_ex )
        {
            $this->writeln( 'error deleting server record: ' . $_ex->getMessage(), 'error' );

            return false;
        }
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

                $this->writeln( 'the server-id "' . $_serverId . '" already exists.', 'error' );

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
                $this->writeln( 'the server-type "' . $_serverType . '" is not valid.', 'error' );

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
                $this->writeln( 'the mount-id "' . $_mountId . '" does not exists.', 'error' );

                return false;
            }
        }

        //  Host name
        $_host = $this->option( 'host-name' );

        if ( $create && empty( $_host ) )
        {
            $this->writeln( '"host-name" is required.', 'error' );

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
                $this->writeln( 'the "config" provided does not contain valid JSON.' );

                return false;
            }
        }

        return $_data;
    }

}
