<?php
/**
 * Service class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

class Service {

	/**
	 * Path
	 *
	 * @var string $path
	 * @access public
	 */
	public $path = null;

	/**
	 * Lib Path
	 *
	 * @var string $lib_path
	 * @access public
	 */
	public $lib_path = null;

	/**
	 * Module Path
	 *
	 * @var string $module_path
	 * @access public
	 */
	public $module_path = null;

	/**
	 * Name
	 *
	 * @var string $name
	 * @access public
	 */
	public $name = null;

	/**
	 * Delete
	 *
	 * @access public
	 */
	public function delete() {
		Util::rrmdir($this->path);
	}

	/**
	 * Get the requested module
	 *
	 * @param string Module name
	 * @access public
	 * @return Web_Module Requested module
	 * @throws Exception
	 */
	public function get_module($request_relative_uri) {
		$application = \Skeleton\Core\Application::get();
		$relative_uri_parts = array_values(array_filter(explode('/', $request_relative_uri)));

		$autoloader = new \Skeleton\Core\Autoloader();
		$autoloader->add_include_path($this->module_path, 'Web_Module_');
		$autoloader->register();

		$classnames = [];
		$classnames[] = trim('Web_Module_' . implode('_', $relative_uri_parts), '_');
		$classnames[] = trim('Web_Module_' . implode('_', $relative_uri_parts), '_') . '_' . $application->config->module_default;

		try {
			$classnames[] = 'Web_Module_' . $application->config->module_404;
		} catch (\Exception $e) { }

		foreach ($classnames as $classname) {
			if (class_exists($classname)) {
				$class = new $classname;
				$class->service = $this;
				return $class;
			}
		}
		throw new \Exception('Module not found');
	}

	/**
	 * Get all
	 *
	 * @access public
	 * @return array $applications
	 */
	public static function get_all() {
		$config = Config::get();
		$service_directories = scandir($config->service_directory);
		$services = [];
		foreach ($service_directories as $service_directory) {
			if ($service_directory[0] == '.') {
				continue;
			}

			$service_path = realpath($config->service_directory . '/' . $service_directory);
			if (!is_dir($service_path)) {
				continue;
			}
			$service = new Service();
			$service->module_path = $service_path . '/module/';
			$service->lib_path = $service_path . '/lib/';
			$service->path = $service_path;
			$service->name = $service_directory;
			$services[] = $service;
		}
		return $services;
	}

	/**
	 * Get by name
	 *
	 * @access public
	 * @param string $name
	 * @return Service $service
	 */
	public static function get_by_name($name) {
		$services = self::get_all();
		foreach ($services as $service) {
			if ($service->name == $name) {
				return $service;
			}
		}
		throw new \Exception('Service with name ' . $name . ' not found');
	}
}
