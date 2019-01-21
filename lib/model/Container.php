<?php
/**
 * Container Class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Container {

	/**
	 * Is paired
	 *
	 * @access public
	 * @return bool is_paired
	 */
	public static function get_name() {
		$config = Config::get();
		if (!isset($config->container_name)) {
			return gethostname();
		}
		return $config->container_name;
	}

	/**
	 * Is paired
	 *
	 * @access public
	 * @return bool is_paired
	 */
	public static function is_paired() {
		return Container_Permission::is_paired();
	}

}
