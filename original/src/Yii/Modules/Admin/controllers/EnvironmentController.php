<?php
/**
 * EnvironmentController.php
 *
 * @link       http:// www.dreamfactory.com DreamFactory Software, Inc.
 * @author     Jerry Ablan <jerryablan@dreamfactory.com>
 * @filesource
 */
use DreamFactory\Yii\Controllers\DreamController;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Log;

/**
 * EnvironmentController class
 */
class EnvironmentController extends DreamController
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
			'Name'        => 'environment_name_text',
			'Owner'         => 'user_id',
			'Last Modified' => 'lmod_date',
		);

		$this->setCrudOptions(
			array(
				'name'       => 'Environment',
				'short_name' => 'Environment',
				'model_name' => 'Environment',
				'crumb'      => 'Environment Manager',
				'columns'    => $this->_ajaxColumns,
			)
		);
	}
}
