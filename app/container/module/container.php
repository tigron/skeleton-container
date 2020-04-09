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
		$zip->extractTo(Skeleton\Core\Config::$tmp_dir . '/../lib/service/' . $name);
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
