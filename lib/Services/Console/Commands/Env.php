<?php namespace DreamFactory\Enterprise\Services\Console\Commands;

use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Services\Commands\EnvJob;
use DreamFactory\Enterprise\Services\Contracts\EnterpriseJob;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Env extends Command implements EnterpriseJob
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @var string The console command name */
    protected $name = 'dfe:env';
    /**  @var string The console command description */
    protected $description = 'Generates an environment file (.env.cluster.json) for use with DFE installations.';
    /** @type EnvJob */
    protected $_job = null;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function fire()
    {
        $this->_job = new EnvJob(
            $this->argument( 'cluster-id' ),
            $this->argument( 'web-server-id' )
        );

        \Queue::push( $this->_job );

        return $this->_job->getResult();
    }

    /** @inheritdoc */
    protected function getArguments()
    {
        return array_merge(
            parent::getArguments(),
            [
                ['cluster-id', InputArgument::REQUIRED, 'The id/name of the cluster',],
                ['web-server-id', InputArgument::REQUIRED, 'The id/name of the web server from "cluster-id"'],
            ]
        );
    }

    /** @inheritdoc */
    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            ['output-file', 'o', 'The destination of the output file. Otherwise it is written to the current working directory.']
        );
    }

    /**
     * @param int|string $clusterId
     *
     * @return string Return the id/name of the cluster involved in this job
     */
    public function getCluster( $clusterId = null )
    {
        return $this->_findCluster( $clusterId ?: $this->_job->getClusterId() );
    }

    /**
     * @param int|string $serverId
     *
     * @return string Return the id/name of the server involved in this job
     */
    public function getServer( $serverId = null )
    {
        return $this->_findCluster( $serverId ?: $this->_job->getServerId() );
    }

    /**
     * @return string Return the absolute path of the output file
     */
    public function getOutputFile()
    {
        return $this->_job->getOutputFile();
    }
}
