<?php
/**
 * ImageController.php
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
use DreamFactory\Yii\Controllers\DreamController;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Log;

/**
 * ImageController class
 */
class ImageController extends DreamController
{
	//*************************************************************************
	//* Public Actions
	//*************************************************************************

	/**
	 * Initializes the controller. By the time we get to this method, the header
	 * and request have been scraped for parameters defined in $headerParameters
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();

		$this->_ajaxColumns = array(
			'ID'            => 'image_id_text',
			'Vendor'        => 'vendor.vendor_name_text',
			'Name'          => 'image_name_text',
			'Last Modified' => 'lmod_date',
		);

		$this->setCrudOptions(
			array(
				'name'       => 'Machine Image',
				'short_name' => 'Image',
				'model_name' => 'VendorImage',
				'crumb'      => 'Image Manager',
				'ajaxData'   => array(
					'columns' => array_values( $this->_ajaxColumns ),
					'url'     => Pii::url( $this->route . '/dataTables' ),
				),
				'columns'    => $this->_ajaxColumns,
			)
		);

		$this->addUserAction( static::Authenticated, 'dataTables' );
	}
}
