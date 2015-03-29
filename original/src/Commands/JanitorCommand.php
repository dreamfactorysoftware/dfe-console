<?php
namespace Cerberus\Commands;

use Cerberus\Yii\Models\Auth\User;
use Cerberus\Yii\Models\Deploy\Instance;
use Cerberus\Yii\Utility\Pii;
use DreamFactory\Services\CouchDb\WorkQueue;
use DreamFactory\Yii\Commands\CliProcess;
use Kisma\Core\Enums\GlobFlags;
use Kisma\Core\Interfaces\HttpResponse;
use Kisma\Core\Utility\FileSystem;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Sql;

ini_set( 'display_errors', 1 );
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

/**
 * JanitorCommand
 * Sweeps vendors for instance states to keep local db in sync
 */
class JanitorCommand extends CliProcess
{
    //*************************************************************************
    //	Members
    //*************************************************************************

    /**
     * @var WorkQueue
     */
    protected $_workQueue = null;

    //*************************************************************************
    //* Methods
    //*************************************************************************

    /**
     * Resets any error jobs
     *
     * @param null $queue
     * @param int  $limit
     *
     * @return bool
     */
    public function actionResetErrors( $queue = null, $limit = 1 )
    {
        if ( false === ( $_errors = $this->_getErrorJobs( $queue, $limit ) ) )
        {
            Log::info( 'Command Complete > Janitor::resetErrors > No errors found to process' );

            return true;
        }

        Log::info( 'Command Start > Janitor::resetErrors' );

        $_dm = $this->_workQueue()->getDm();

        foreach ( $_errors as $_row )
        {
            $_id = $_row['id'];
            Log::debug( '  * Processing row id "' . $_id . '"' );

            if ( null !== ( $_job = $_dm->find( WorkQueue::DocumentType, $_id ) ) )
            {
                $_job->setProcessed( false );
                $_job->setState( 1 );
            }

            $_dm->persist( $_job );
            $_job = null;
        }

        $_dm->flush();

        Log::info( 'Command Complete > Janitor::resetErrors' );
    }

    /**
     * @param string $queue
     * @param int    $limit
     *
     * @return bool
     */
    public function actionClearPending( $queue = 'deprovision', $limit = 10 )
    {
        if ( false === ( $_jobs = $this->_getPendingJobs( $queue, $limit ) ) )
        {
            Log::info( 'Command Complete > Janitor::clearPending > No pending jobs found to process' );

            return true;
        }

        Log::info( 'Command Start > Janitor::clearPending' );

        $_dm = $this->_workQueue()->getDm();

        foreach ( $_jobs as $_row )
        {
            //  Get the document
            $_id = $_row['id'];
            Log::debug( '  * Clearing job' );
            Log::debug( '    - Retrieving document id: ' . $_id );

            /** @var $_doc \DreamFactory\Documents\WorkUnit */
            if ( null === ( $_doc = $_doc = $_dm->find( WorkQueue::DocumentType, $_id ) ) )
            {
                Log::error( '    ! Unable to retreive document id "' . $_id . '".' );
                continue;
            }

            //  Clear and save
            try
            {
                $_doc->setInFlight( false );
                $_doc->setProcessed( true );
                $_doc->setProcessedAt( date( 'c' ) );
                $_doc->setState( 4 );
                $_doc->setResponse( 'cleared by janitor @ ' . date( 'c' ) );

                $_dm->persist( $_doc );

                Log::debug( '    - Cleared' );
            }
            catch ( \Exception $_ex )
            {
                Log::error( '    ! Exception changing status of id "' . $_id . '": ' . $_ex->getMessage() );
            }
        }

        $_dm->flush();

        Log::info( 'Command Complete > Janitor::clearPending' );
    }

    /**
     * clears the in-flight status of jobs
     *
     * @param string $queue
     * @param int    $limit
     *
     * @return bool
     * @throws \CHttpException
     */
    public function actionClearInFlight( $queue = null, $limit = 1 )
    {
        if ( false === ( $_inFlight = $this->_getInFlightJobs( $queue, $limit ) ) )
        {
            Log::info( 'Command Complete > Janitor::clearInFlight > No in-flight jobs found to process' );

            return true;
        }

        Log::info( 'Command Start > Janitor::clearInFlight' );

        $_dm = $this->_workQueue()->getDm();

        foreach ( $_inFlight as $_row )
        {
            //  Get the document
            $_id = $_row['id'];
            Log::debug( '  * Clearing in-flight status' );
            Log::debug( '    - Retrieving document id: ' . $_id );

            /** @var $_doc \DreamFactory\Documents\WorkUnit */
            if ( null === ( $_doc = $_doc = $_dm->find( WorkQueue::DocumentType, $_id ) ) )
            {
                Log::error( '    ! Unable to retreive document id "' . $_id . '".' );
                continue;
            }

            //  Clear and save
            try
            {
                $_doc->setInFlight( false );
                $_dm->persist( $_doc );

                Log::debug( '    - Cleared' );
            }
            catch ( \Exception $_ex )
            {
                Log::error( '    ! Exception changing in-flight status of id "' . $_id . '": ' . $_ex->getMessage() );
            }
        }

        $_dm->flush();

        Log::info( 'Command Complete > Janitor::clearInFlight' );
    }

    /**
     * @throws \Exception
     */
    public function actionInstanceMap()
    {
        Sql::setConnection( Pii::pdo( 'db.fabric_deploy' ) );

        $_files = FileSystem::glob( '/data/storage/*', GlobFlags::NoDots );

        Log::info( 'Scanning containment area. ' . count( $_files ) . ' file(s) found.' );

        foreach ( $_files as $_file )
        {
            $_private = false;

            /** @var User $_model */
            if ( null === ( $_model = User::model()->find( ':storage_id_text = storage_id_text', array(':storage_id_text' => $_file) ) ) )
            {
                if ( null === ( $_model = Instance::model()->find( ':storage_id_text = storage_id_text', array(':storage_id_text' => $_file) ) ) )
                {
                    Log::error( '  * Directory found for non-existent user/instance.', array('path' => '/data/storage/' . $_file) );

                    $_source = '/data/storage/' . $_file;
                    $_target = '/data/storage/no.user.' . $_file;

//					$_result = `tar czf {$_target}.tar.gz {$_source}`;

                    if ( false === @rename( $_source, $_target ) )
                    {
                        throw new \Exception( '  * ERROR moving file from "' . $_source . '" to "' . $_target . '"' );
                    }

                    Log::error( '  * Moved to tempspace', array('path' => '/media/tempspace/storage/' . $_file) );
                    continue;
                }

                $_private = true;
            }

            Sql::execute( 'DELETE FROM fabric_deploy.instance_janitor_t' );

            $_sql
                = <<<SQL
INSERT INTO fabric_deploy.instance_janitor_t
(
	storage_id_text,
	private_ind,
	registration_ind,
	user_id,
	user_storage_id_text
) VALUES (
	:storage_id_text,
	:private_ind,
	:registration_ind,
	:user_id,
	:user_storage_id_text
) ON DUPLICATE KEY UPDATE
	storage_id_text = values(storage_id_text),
	private_ind = values(private_ind),
	registration_ind = values(registration_ind),
	user_id = values(user_id),
	user_storage_id_text = values(user_storage_id_text)
SQL;

            $_data = array(
                ':storage_id_text'      => $_model->storage_id_text,
                ':private_ind'          => $_private ? 1 : 0,
                ':registration_ind'     => $_model instanceof User ? $this->_hasUserEverActivated( $_model, true ) : $this->_isActivated( $_model ),
                ':user_id'              => $_model->id,
                ':user_storage_id_text' => $_model->storage_id_text,
            );

            if ( 1 < $_rows = Sql::execute( $_sql, $_data ) )
            {
                Log::error( '    * Could not save record' );
            }
        }
    }

    /**
     *
     * @param string $email user to check or null for all
     *
     * @return void
     */
    public function actionActiveUsers( $email = null )
    {
        $_criteria = new \CDbCriteria();
        $_criteria->select = 'id, drupal_id, storage_id_text, activate_ind';

        if ( !empty( $email ) )
        {
            $_criteria->condition = 'email_addr_text = :email_addr_text';
            $_criteria->params = array(':email_addr_text' => $email);
        }

        $_counts = array('on' => 0, 'off' => 0, 'total' => 0, 'error' => 0);

        /** @var Instance[] $_models */
        $_models = User::model()->findAll( $_criteria );

        foreach ( $_models as $_model )
        {
            $_log
                = array(
                'id'              => $_model->id,
                'email'           => $_model->email_addr_text,
                'storage_id_text' => $_model->storage_id_text,
                'activate_ind'    => $_model->activate_ind
            );

            if ( false === ( $_files = $this->_hasUserEverActivated( $_model ) ) )
            {
                $_model->activate_ind = 2;
                $_model->update( array('activate_ind') );

                Log::info( '  * Private directory not created. Marked error.', $_log );
                $_counts['error']++;
            }
            else if ( empty( $_files ) )
            {
                $_model->activate_ind = 0;
                $_model->update( array('activate_ind') );

                Log::info( '  * Private directory found, but not activation file. Marked inactive.', $_log );
                $_counts['off']++;
            }
            else
            {
                Log::info( ' * Private directory and activation found.', $_log );

                $_counts['on']++;

                if ( 0 == $_model->activate_ind )
                {
                    //	Make active
                    $_model->activate_ind = 1;
                    $_model->update( array('activate_ind') );

                    Log::info( '    * Marked activated.' );
                }
                else
                {
                    Log::debug( '     * Already activated.' );
                }
            }

            $_counts['total']++;

            unset( $_model, $_log );
        }

        unset( $_models );

        Log::debug( 'Janitor activeUsers complete', $_counts );
    }

    /**
     *
     * @param string $dspName DSP to check, otherwise all will be checked
     *
     * @return void
     */
    public function actionActive( $dspName = null )
    {
        $_criteria = new \CDbCriteria();
        $_criteria->select = 'id, user_id, storage_id_text, instance_name_text, activate_ind';

        if ( !empty( $dspName ) )
        {
            $_criteria->condition = 'instance_name_text = :instance_name_text';
            $_criteria->params = array(':instance_name_text' => $dspName);
        }

        $_counts = array('on' => 0, 'off' => 0, 'total' => 0);

        /** @var Instance[] $_models */
        $_models = Instance::model()->findAll( $_criteria );

        foreach ( $_models as $_model )
        {
            if ( $this->_isActivated( $_model ) )
            {
                $_counts['on']++;
                if ( 0 == $_model->activate_ind )
                {
                    //	Make active
                    $_model->activate_ind = 1;
                    $_model->update( array('activate_ind') );
                    Log::debug( 'ACTIVE = 1 : ' . $_model->instance_name_text );
                }
            }
            else
            {
                $_counts['off']++;
                if ( 1 == $_model->activate_ind )
                {
                    //	Make inactive
                    $_model->activate_ind = 0;
                    $_model->update( array('activate_ind') );
                    Log::debug( 'ACTIVE = 0 : ' . $_model->instance_name_text );
                }
            }

            $_counts['total']++;

            unset( $_model );
        }

        unset( $_models );

        Log::debug( 'Janitor Report: Activated: ' . $_counts['on'] . '  Deactivated: ' . $_counts['off'] . '  Total: ' . $_counts['total'] );
    }

    /**
     * @param \Cerberus\Yii\Models\Deploy\Instance $dsp
     *
     * @return bool
     */
    protected function _isActivated( Instance $dsp )
    {
        $_id = $dsp['storage_id_text'];

        if ( !is_dir( '/data/storage/' . $_id . '/blob/applications' ) )
        {
            return false;
        }

        return true;
    }

    /**
     * @param \Cerberus\Yii\Models\Auth\User $user
     * @param bool                           $returnBool
     *
     * @return bool
     */
    protected function _hasUserEverActivated( User $user, $returnBool = false )
    {
        $_path = '/data/storage/' . $user->storage_id_text . '/.private/';

        if ( !is_dir( $_path ) )
        {
            return false;
        }

        $_files = FileSystem::glob( $_path . '?registration_complete*' );

        if ( $returnBool )
        {
            return is_array( $_files ) && !empty( $_files );
        }

        return $_files;
    }

    /**
     * @param null|string|array $configFile Either /path/to/config/file or array of config parameters or nada
     *
     * @return \DreamFactory\Services\CouchDb\WorkQueue
     */
    protected function _workQueue( $configFile = null )
    {
        if ( null === $this->_workQueue )
        {
            $_config = null;

            if ( is_array( $configFile ) )
            {
                $_config = $configFile;
            }

            if ( null === $configFile || is_string( $configFile ) )
            {
                $_configFile = \Kisma::get( 'app.config_path' ) . '/chairlift.config.php';

                if ( file_exists( $_configFile ) )
                {
                    $_config = @include( $_configFile );
                }
            }

            $this->_workQueue = new WorkQueue( $this, $_config );

            //	Put a copy in the global space for a goof
            if ( null === \Kisma::get( 'app.work_queue' ) )
            {
                \Kisma::set( 'app.work_queue', $this->_workQueue );
            }
        }

        return $this->_workQueue;
    }

    /**
     * Get work items from the queue
     *
     * @param string $queue
     * @param int    $limit
     * @param bool   $includeDocs
     *
     * @throws \CHttpException
     * @return array|bool
     */
    protected function _getInFlightJobs( $queue = null, $limit = 1, $includeDocs = false )
    {
        return $this->_getView( 'in_flight', $queue, $limit, $includeDocs );
    }

    /**
     * Get pending work items from a queue
     *
     * @param string $queue
     * @param int    $limit
     * @param bool   $includeDocs
     *
     * @throws \CHttpException
     * @return array|bool
     */
    protected function _getPendingJobs( $queue = null, $limit = 1, $includeDocs = false )
    {
        return $this->_getView( 'pending_jobs', $queue, $limit, $includeDocs );
    }

    /**
     * Get work items from the queue
     *
     * @param string $queue
     * @param int    $limit
     *
     * @param bool   $includeDocs
     *
     * @throws \CHttpException
     * @return array|bool
     */
    protected function _getErrorJobs( $queue = null, $limit = 1, $includeDocs = false )
    {
        return $this->_getView( 'error_jobs', $queue, $limit, $includeDocs );
    }

    /**
     * Get a view from couch
     *
     * @param string $view        The CouchDB view
     * @param string $queue       The name of the queue
     * @param int    $limit       How many to get
     * @param bool   $includeDocs Return docs too?
     *
     * @throws \CHttpException
     * @return array|bool
     */
    protected function _getView( $view, $queue = null, $limit = 1, $includeDocs = false )
    {
        $_dm = $this->_workQueue()->getDm();

        $_path = '/' . $_dm->getDatabase() . '/_design/system/_view/' . $view . '?include_docs=' . ( $includeDocs ? 'true' : 'false' );

        if ( null !== $queue )
        {
            $_path .= '&startkey=["' . $queue . '"]&endkey=["' . $queue . '",{}]';
        }

        if ( !empty( $limit ) )
        {
            $_path .= '&limit=' . $limit;
        }

        $_result = $_dm->getCouchDBClient()->getHttpClient()->request( 'GET', $_path );

        if ( HttpResponse::Ok !== $_result->status )
        {
            throw new \CHttpException( 500, 'The queue is inaccessible' );
        }

        //	Return queue item(s)
        return empty( $_result->body['rows'] ) ? false : $_result->body['rows'];
    }

}
