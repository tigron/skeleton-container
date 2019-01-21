<?php
/**
 * Hooks
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Hook_Container {

	/**
	 * Start time
	 *
	 * @access private
	 */
	private static $start = 0;

	/**
	 * Bootstrap the application
	 *
	 * @access public
	 */
	public static function bootstrap(\Skeleton\Core\Web\Module $module) {
		if (isset($_SERVER['HTTP_KEY'])) {
			Container_Permission::$key = $_SERVER['HTTP_KEY'];
		}
	}


	/**
	 * Bootstrap the application
	 *
	 * @access public
	 */
	public static function module_not_found() {
		if (isset($_SERVER['HTTP_KEY'])) {
			Container_Permission::$key = $_SERVER['HTTP_KEY'];
		}

		/**
		 * Parse the requested URL
		 */
		$components = parse_url($_SERVER['REQUEST_URI']);

		if (isset($components['query'])) {
			$query_string = $components['query'];
		} else {
			$query_string = '';
		}

		if (isset($components['path']) and $components['path'] !== '/') {
			$request_uri_parts = explode('/', $components['path']);
			array_shift($request_uri_parts);
		} else {
			$request_uri_parts = [];
		}

		if (!is_array($request_uri_parts) or count($request_uri_parts) == 0) {
			Skeleton\Core\Web\HTTP\Status::code_404('Service not found');
		}
		$service_name = array_shift($request_uri_parts);
		try {
			$service = Service::get_by_name($service_name);
		} catch (Exception $e) {
			Skeleton\Core\Web\HTTP\Status::code_404('Service not found');
		}

		try {
			$module = $service->get_module(implode('/', $request_uri_parts));
		} catch (Exception $e) {
			Skeleton\Core\Web\HTTP\Status::code_404('Module not found');
		}

		$autoloader = new \Skeleton\Core\Autoloader();
		$autoloader->add_include_path($service->lib_path);
		$autoloader->register();

		$module->accept_request();
	}

}
