<?php
namespace Cerberus\Services\Provisioning\DreamFactory;

use Cerberus\Enums\Provisioners;
use DreamFactory\Exceptions\ProvisioningException;
use Cerberus\Interfaces\ProvisionerLike;
use Cerberus\Services\Hosting\BaseHostingService;
use Cerberus\Yii\Models\Deploy\Instance;
use DreamFactory\Services\DreamService;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Enums\HttpResponse;
use Kisma\Core\Interfaces\ConsumerLike;
use Kisma\Core\Utility\Log;
use Kisma\Core\Utility\Option;
use Kisma\Core\Utility\Sql;

/**
 * DreamFactory
 * DF Fabric provisioning service
 *
 * @author        Jerry Ablan <jerryablan@dreamfactory.com>
 */
class Storage extends BaseHostingService implements ProvisionerLike
{
	//*************************************************************************
	//* Constants
	//*************************************************************************

	/**
	 * @var string
	 */
	const DEFAULT_DSP_HOST = 'cumulus.fabric.dreamfactory.com';
	/**
	 * @var string
	 */
	const DEFAULT_DSP_ZONE = 'cloud';
	/**
	 * @var string
	 */
	const DEFAULT_DSP_DOMAIN = '.dreamfactory.com';
	/**
	 * @var string
	 */
	const BASE_STORAGE_PATH = '/data/storage';
	/**
	 * @var seasoning
	 */
	const SaltyGoodness = 'MA62HQ,PTx8oec~TQ;)Td*wc4(-{8WO*Mf-d+&p7*-d+D0P[6r?@ dW39<+H$~X>';

	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * @param ConsumerLike   $consumer
	 * @param Instance|array $settings
	 */
	public function __construct( ConsumerLike $consumer, $settings = array() )
	{
		if ( $settings instanceof Instance )
		{
			$settings = array( 'instance' => $settings );
		}

		parent::__construct( $consumer, $settings );
	}

	/**
	 * @param      $path
	 * @param int  $mode
	 * @param bool $recursive
	 *
	 * @return bool
	 */
	protected function _ensurePath( $path, $mode = 0777, $recursive = true )
	{
		if ( file_exists( $path ) || is_dir( $path ) )
		{
			return true;
		}

		if ( false === @mkdir( $path, $mode, $recursive ) )
		{
			$this->logError( 'Error ensuring path: ' . $path );

			return false;
		}

		if ( !is_writeable( $path ) )
		{
			$this->logWarning( 'Path "' . $path . '" does not appear to writeable.' );
		}

		return true;
	}

	/**
	 * Provisions storage for a hosted instance
	 *
	 * @param Instance $instance
	 *
	 * @return bool|\CFResponse|mixed
	 */
	public function provision( $instance )
	{
		$this->_validateInstance( $instance->instance_name_text );

		//	Storage path
		$_storagePath = $this->getStoragePath();

		if ( !$this->_ensurePath( $_storagePath ) )
		{
			if ( !is_writable( $_storagePath ) )
			{
				$this->logError( 'Storage path not writable!' );
			}
			$this->logError( 'Error provisioning hosted storage path: ' . $_storagePath );

			return false;
		}

		$this->logDebug( 'Provisioned hosted storage path: ' . $_storagePath );

		//	Private path
		if ( !$this->_ensurePath( $_privatePath = $this->getPrivatePath() ) )
		{
			if ( !is_writable( $_privatePath ) )
			{
				$this->logError( 'Private path not writable!' );
			}

			$this->logError( 'Error provisioning hosted private path: ' . $_privatePath );

			return false;
		}

		$this->logDebug( 'Provisioned hosted private path: ' . $_privatePath );

		//	Blob storage path
		if ( !$this->_ensurePath( $_blobStoragePath = $this->getBlobStoragePath() ) )
		{
			if ( !is_writable( $_blobStoragePath ) )
			{
				$this->logError( 'Blob storage path not writable!' );
			}

			$this->logError( 'Error provisioning hosted blob storage path: ' . $_blobStoragePath );

			return false;
		}

		$this->logDebug( 'Provisioned hosted blob storage path: ' . $_blobStoragePath );

		//	Snapshot path
		if ( !$this->_ensurePath( $_snapshotPath = $this->getSnapshotPath() ) )
		{
			if ( !is_writable( $_snapshotPath ) )
			{
				$this->logError( 'Snapshot path not writable!' );
			}

			$this->logError( 'Error provisioning hosted snapshot: ' . $_snapshotPath );

			return false;
		}

		$this->logDebug( 'Provisioned hosted snapshots path: ' . $_snapshotPath );

		//	Done
		$this->logInfo( 'Provisioned storage for key: ' . $instance->instance_name_text . '::' . $instance->user->storage_id_text . '::' . $instance->storage_id_text );

		return array(
			'storage_path'  => $this->getStoragePath(),
			'snapshot_path' => $this->getSnapshotPath(),
			'blob_path'     => $this->getBlobStoragePath(),
			'private_path'  => $this->getPrivatePath(),
		);
	}

	/**
	 * @param mixed $request
	 *
	 * @throws ProvisioningException
	 */
	public function deprovision( $request )
	{
		// Disabled for now...
		return true;

		if ( is_string( $request ) )
		{
			$_storageKey = $request;
		}
		else if ( null === ( $_storageKey = Option::get( $request, 'storage_key' ) ) )
		{
			throw new ProvisioningException( 'You must specify the "storage_key" for the instance . ' );
		}

		$this->_validateRequest( null, $_storageKey );

		$_command
			=
			'/usr/bin/ssh ' . static::DFOPS_SSH_OPTIONS . ' dfadmin@cumulus.fabric.dreamfactory.com "/data/scripts/deprovision.sh ' . $_storageKey . '"';

		exec( $_command, $_output, $_returnValue );

		if ( 0 != $_returnValue )
		{
			$this->logError(
				'Error deprovisioning remote hosted storage: ' . $_storageKey . PHP_EOL .
					'Command: ' . $_command . PHP_EOL .
					'Output:' . PHP_EOL .
					implode( PHP_EOL, (array)$_output )
			);

			return false;
		}

		$this->logInfo( 'Deprovisioned storage for key: ' . $_storageKey );

		return true;
	}
}