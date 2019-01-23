<?php
/**
 * Container_Permission Class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Container_Permission {

	public static $key = null;

	/**
	 * Is authenticated
	 *
	 * @access public
	 * @return bool $authenticated
	 */
	public function is_authenticated() {
		if (isset(self::$key) and self::validate_key(self::$key)) {
			return true;
		}

		return false;
	}

	/**
	 * Is paired
	 *
	 * @access public
	 * @return bool is_paired
	 */
	public static function is_paired() {
		try {
			self::get_pair_key();
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Validate key
	 *
	 * @access public
	 * @param string $key
	 * @return bool
	 */
	public static function validate_key($key) {
		try {
			$stored_key = self::get_pair_key();
		} catch (Exception $e) {
			return false;
		}
		if (trim($stored_key) == trim($key)) {
			return true;
		}

		return false;
	}

	/**
	 * Remove key
	 *
	 * @access public
	 */
	public static function unpair() {
		$path  = dirname(__FILE__) . '/../../../config/pair.key';
		file_put_contents($path, '');
		chmod($path, 0600);
	}

	/**
	 * Pair
	 *
	 * @access public
	 * @return string $pair_key
	 */
	public static function pair() {
		if (self::is_paired()) {
			throw new Exception('Already paired');
		}

		// Create random pair string
		$key = implode('', array_map(
			function () {
				return chr(rand(0, 1) ? rand(48, 57) : rand(97, 122));
			}, range(0, 249)));
		$path  = dirname(__FILE__) . '/../../../config/pair.key';
		file_put_contents($path, $key);
		chmod($path, 0600);		
		return $key;
	}

	/**
	 * Get the pairing key
	 *
	 * @access private
	 * @return string $key
	 */
	private static function get_pair_key() {
		$path  = dirname(__FILE__) . '/../../../config/pair.key';
		if (!file_exists($path)) {
			throw new Exception('Key file not found');
		}
		$pair_key = file_get_contents($path);
		if (trim($pair_key) == '') {
			throw new Exception('No pair key set');
		}

		return $pair_key;
	}

}
