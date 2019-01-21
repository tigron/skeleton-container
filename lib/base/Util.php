<?php
/**
 * Util Class
 *
 * Some utils for general purpose
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */

class Util {

	/**
	* Recursively removes a folder along with all its files and directories
	*
	* @param String $path
	*/
	public static function rrmdir($path) {
		// Open the source directory to read in files
		$i = new DirectoryIterator($path);
		foreach($i as $f) {
			if($f->isFile()) {
				unlink($f->getRealPath());
			} else if(!$f->isDot() && $f->isDir()) {
				self::rrmdir($f->getRealPath());
			}
		}
		rmdir($path);
	}

	/**
	 * Call
	 *
	 * @access public
	 * @param string $method
	 * @param array $arguments
	 */
	public static function __callstatic($method, $arguments) {
		list($classname, $method) = explode('_', $method, 2);
		$classname = 'Util_' . $classname;

		if (!method_exists($classname, $method)) {
			throw new Exception('method ' . $method . ' does not exists');
		}

		$result = forward_static_call_array([ $classname, $method ], $arguments);
		return $result;
	}
}
