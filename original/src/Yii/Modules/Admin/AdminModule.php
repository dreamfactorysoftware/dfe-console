<?php
/**
 * AdminModule.php
 */
/**
 * AdminModule
 * Provides admin services
 */
class AdminModule extends \DreamFactory\Yii\Modules\DreamWebModule
{
	//*************************************************************************
	//* Methods
	//*************************************************************************

	/**
	 * Override of init() to add in the deploy models
	 */
	public function init()
	{
		parent::init();

		$this->setImport(
			array(
				$this->id . '.models.deploy.*',
			)
		);
	}

}
