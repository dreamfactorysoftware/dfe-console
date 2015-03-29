<?php
/**
 * VendorRefreshCommand.php
 */
use DreamFactory\Enums\CloudVendors;

/**
 * VendorRefreshCommand
 * Job dequeue/process thread
 */
class VendorRefreshCommand extends \DreamFactory\Yii\Commands\CliProcess
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 *
	 * @param string $credentials A path to a file containing credentials
	 * @param bool   $images      Include full image refresh
	 * @param bool   $verbose     Verbose mode
	 *
	 * @return void
	 */
	public function actionAmazon( $credentials = null, $images = false, $verbose = false )
	{
		$this->_authorize( $credentials );
		$this->_refreshAmazon( $images, $verbose );
	}

	/**
	 * @param string $credentials A path to a file containing credentials
	 */
	protected function _authorize( $credentials = null )
	{
		$_file = $credentials ? : \Kisma::get( 'app.config_path' ) . '/amazon.api-user.keys.php';
		\CFCredentials::set( @include( $_file ) );
	}

	/**
	 * @param bool   $images      If true, the image cache is refreshed as well
	 * @param bool   $verbose     Verbose mode
	 */
	protected function _refreshAmazon( $images = false, $verbose = false )
	{
		$_update = $_create = 0;

		$this->logInfo( '>> Begin Amazon refresh' );

		$_service = new \AmazonEC2();

		//	1.	Refresh regions
		$this->logDebug( '  -- Refreshing regions' );
		$_response = $_service->describe_regions();

		if ( $_response->isOk() )
		{
			foreach ( $_response->body->regionInfo->item as $_region )
			{
//				\Kisma\Core\Utility\Log::info( '  -- Region: ' . print_r( $_region, true ) );
				unset( $_region );
			}
		}

		unset( $_response );

		//	2.	Refresh zones
		$this->logDebug( '  -- Refreshing availability zones' );
		$_response = $_service->describe_availability_zones(
			array(
				'Filter' => array(
					1 => array(
						'Name'  => 'state',
						'Value' => 'available',
					),
				),
			)
		);

		if ( $_response->isOk() )
		{
			foreach ( $_response->body->availabilityZoneInfo->item as $_zone )
			{
//				\Kisma\Core\Utility\Log::info( '  -- Zone: ' . print_r( $_zone, true ) );
				unset( $_region );
			}
		}

		unset( $_response );

		//	3.	Refresh images
		if ( false === $images )
		{
			$this->logDebug( '  -- Image refresh skipped.' );
		}
		else
		{
			$this->logDebug( '  -- Refreshing available images' );

			$_response = $_service->describe_images(
				array(
					'Filter' => array(
						1 => array(
							'Name'  => 'image-type',
							'Value' => 'machine',
						),
						2 => array(
							'Name'  => 'state',
							'Value' => 'available',
						),
					),
				)
			);

			if ( $_response->isOk() )
			{
				foreach ( $_response->body->imagesSet->item as $_ami )
				{
					//	Skip unavailable AMIs
					if ( 'available' != (string)$_ami->imageState || 'machine' != (string)$_ami->imageType )
					{
						continue;
					}

					$_imageId = (string)$_ami->imageId;

					$_model = \VendorImage::model()->find(
						'vendor_id = :vendor_id and image_id_text = :image_id_text',
						array(
							':vendor_id'     => CloudVendors::Amazon,
							':image_id_text' => $_imageId,
						)
					);

					if ( null === $_model )
					{
						$_model = new \VendorImage();
						$_model->vendor_id = CloudVendors::Amazon;
						$_model->image_id_text = $_imageId;
						$_create++;
					}
					else
					{
						$_update++;
					}

					$_model->architecture_nbr = \DreamFactory\Enums\Architectures::nameOf( (string)$_ami->architecture, false );
					$_model->license_text = ( 'true' == (string)$_ami->isPublic ? 'Public' : 'Private' );
					$_model->image_name_text = isset( $_ami->name ) ? (string)$_ami->name : null;
					$_model->image_description_text = isset( $_ami->description ) ? (string)$_ami->description : null;
					$_model->os_text = ( isset( $_ami->platform ) && '' != (string)$_ami->platform ? 'Windows' : 'Linux' );
					$_model->root_storage_text = (string)$_ami->rootDeviceType;
					$_model->save();

					unset( $_model, $_ami );
				}
			}
		}

		$this->logInfo( '<< Complete < Amazon refresh < Created ' . $_create . ' image(s) and updated ' . $_update . ' image(s).' );

		if ( false !== $verbose )
		{
			$this->writeln( 'Created ' . $_create . ' image(s) and updated ' . $_update . ' image(s).' );
		}
	}

}