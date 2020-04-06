<?php
/**
 * Event
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace App\Container\Event;

use \Skeleton\Core\Web\Session;
use \Skeleton\Core\Web\Template;

class Application extends \Skeleton\Core\Event {

	/**
	 * Bootstrap the application
	 *
	 * @access public
	 */
	public function bootstrap(\Skeleton\Core\Web\Module $module) {
		if (isset($_SERVER['HTTP_KEY'])) {
			\Container_Permission::$key = $_SERVER['HTTP_KEY'];
		}
	}

}