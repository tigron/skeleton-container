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

class Module extends \Skeleton\Core\Event {

	/**
	 * Module not found
	 *
	 * @access public
	 */
	public function not_found() {
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
			\Skeleton\Core\Web\HTTP\Status::code_404('Service not found');
		}
		$service_name = array_shift($request_uri_parts);
		try {
			$service = \Service::get_by_name($service_name);
		} catch (Exception $e) {
			\Skeleton\Core\Web\HTTP\Status::code_404('Service not found');
		}

		try {
			$module = $service->get_module(implode('/', $request_uri_parts));
		} catch (\Exception $e) {
			\Skeleton\Core\Web\HTTP\Status::code_404('Module not found for service ' . $service_name);
		}

		$autoloader = new \Skeleton\Core\Autoloader();
		$autoloader->add_include_path($service->lib_path);
		$autoloader->register();

		$module->accept_request();
	}
}
