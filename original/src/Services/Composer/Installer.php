<?php
/**
 * Installer.php
 *
 * @copyright Copyright (c) 2013 DreamFactory Software, Inc.
 * @link      DreamFactory Software, Inc. <http://www.dreamfactory.com>
 * @package   cerberus
 * @filesource
 */
namespace Cerberus\Services\Composer;

use Composer\Script\Event;

/**
 * Installer
 *
 * @author    Jerry Ablan <jerryablan@dreamfactory.com>
 */
class Installer
{
	/**
	 * @param \Composer\Script\Event $event
	 *
	 * @return bool
	 */
	public static function postUpdate( Event $event )
	{
		$_configPath = getcwd() . '/config';

		$_script
			= <<<BASH
cd {$_configPath}
./scripts/composer-post-update.sh
cd - >/dev/null 2>&1
BASH;

		$_out = system( $_script, $_result );

		return ( 0 == $_result );
	}

	/**
	 * @param \Composer\Script\Event $event
	 */
	public static function postPackageInstall( Event $event )
	{
		$_installedPackage = $event->getOperation()->getPackage();
	}

	/**
	 * @param \Composer\Script\Event $event
	 */
	public static function warmCache( Event $event )
	{
		// make cache toasty
	}
}
