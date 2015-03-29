<?php
/**
 * VendorController.php
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
use DreamFactory\Yii\Controllers\DreamController;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Log;

/**
 * VendorController class
 */
class VendorController extends DreamController
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
			'ID'            => 'id',
			'Name'          => 'vendor_name_text',
			'Create Date'   => 'create_date',
			'Last Modified' => 'lmod_date',
		);

		$this->setCrudOptions(
			array(
				'name'       => 'Vendor',
				'short_name' => 'Vendor',
				'model_name' => 'Vendor',
				'crumb'      => 'Vendor Manager',
//	Uncomment these and the line below to enable AJAX loading
//				'ajaxData'   => array(
//					'columns' => array_values( $this->_ajaxColumns ),
//					'url'     => Pii::url( $this->route . '/dataTables' ),
//				),
				'columns'    => $this->_ajaxColumns,
			)
		);

//		$this->addUserAction( static::Authenticated, 'dataTables' );
	}
}
