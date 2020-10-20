<?php
/**
 * Container Module
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

use \Skeleton\Core\Web\Module;

class Web_Module_Container extends Service_Module {

	/**
	 * Login required
	 *
	 * @var $login_required
	 */
	protected $login_required = true;

	/**
	 * Template
	 *
	 * @access protected
	 * @var string $template
	 */
	protected $template = false;


	/**
	 * Display
	 *
	 * @access public
	 */
	public function display() {
	}

	/**
	 * Pair the container
	 *
	 * @access public
	 */
	public function handle_pair() {
		if (Container::is_paired()) {
			Container_Response::output_forbidden();
		}

		$key = Container_Permission::pair();
		$response = new Container_Response();
		$response->set_data($key);
		$response->output();
	}

	/**
	 * Pair the container
	 *
	 * @access public
	 */
	public function handle_unpair() {
		if (!Container::is_paired()) {
			Container_Response::output_forbidden();
		}

		if (!Container_Permission::is_authenticated()) {
			Container_Response::output_forbidden();
		}

		$services = Service::get_all();
		foreach ($services as $service) {
			$service->delete();
		}

		Container_Permission::unpair();
		$response = new Container_Response();
		$response->output();
	}

	/**
	 * Provision
	 *
	 * @access public
	 */
	public function handle_provision($name, $content) {
		if (!Container::is_paired()) {
			Container_Response::output_forbidden();
		}
		if (!Container_Permission::is_authenticated()) {
			Container_Response::output_forbidden();
		}

		$zipfile = base64_decode($content);
		file_put_contents(Skeleton\Core\Config::$tmp_dir . '/' . $name . '.zip', $zipfile);
		$zip = new ZipArchive();
		$zip->open(Skeleton\Core\Config::$tmp_dir . '/' . $name . '.zip');

		$config = Config::get();
		$zip->extractTo($config->service_directory . '/' . $name);
		$response = new Container_Response();
		$response->set_message('Provision successful');
		$response->output();
	}

	/**
	 * Provision
	 *
	 * @access public
	 */
	public function handle_deprovision($name) {
		if (!Container::is_paired()) {
			Container_Response::output_forbidden();
		}
		try {
			$service = Service::get_by_name($name);
		} catch (Exception $e) {
			$response = new Container_Response();
			$response->set_status_code(500);
			$response->set_message('The given service does not exist');
			$response->output();
			return;
		}

		$service->delete();
	}

	/**
	 * Get info about container
	 *
	 * @access public
	 */
	public function handle_info() {
		$services = Service::get_all();
		$service_names = [];
		foreach ($services as $service) {
			$service_names[] = $service->name;
		}

		$name = Container::get_name();
		$response = new Container_Response();
		$response->set_data([
			'name' => $name,
			'authenticated' => Container_Permission::is_authenticated(),
			'services' => $service_names,
		]);
		$response->output();
	}

	/**
	 * Get the difference between the local and remote service
	 *
	 * @access public
	 */
	public function handle_diff($name, $content) {
		// the code below uses 'remote' and 'local', where 'remote' is the
		// remote container, and 'local' is the controller
		$config = Config::get();
		$service = Service::get_by_name($name);

		$zip_path = Skeleton\Core\Config::$tmp_dir . '/' . $name . '.zip';
		$extract_path = Skeleton\Core\Config::$tmp_dir . '/' . $name;
		$service_path = $config->service_directory . '/' . $name;

		$zipfile = base64_decode($content);
		file_put_contents($zip_path, $zipfile);

		$zip = new ZipArchive();
		$zip->open($zip_path);
		$zip->extractTo($extract_path);

		$files = [];

		$iterator = new RecursiveDirectoryIterator($extract_path);
		foreach(new RecursiveIteratorIterator($iterator) as $file) {
			if ($file->isFile()) {
				$file_path = substr($file->getRealPath(), strlen($extract_path));

				$differ = new \SebastianBergmann\Diff\Differ(new \SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder([
					'fromFile' => 'local/' . $file_path,
					'toFile'   => 'remote/' . $file_path,
				]));

				if (!file_exists($service_path . '/' . $file_path)) {
					$files[$file_path] = 'local-only';
				} elseif (hash_file('sha256', $extract_path . '/' . $file_path) !== hash_file('sha256', $service_path . '/' . $file_path)) {
					$files[$file_path] = $differ->diff(
						file_get_contents($extract_path . '/' . $file_path),
						file_get_contents($service_path . '/' . $file_path)
					);
				}
			}
		}

		$response = new Container_Response();
		$response->set_message(count($files) . ' differ');
		$response->set_data($files);
		$response->output();
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		if (isset($_REQUEST['action']) and $_REQUEST['action'] == 'pair') {
			if (!Container_Permission::is_paired()) {
				return true;
			} else {
				return false;
			}
		}

		if (!Container_Permission::is_authenticated()) {
			return false;
		}
		return true;
	}
}
