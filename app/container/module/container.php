<?php
/**
 * Container Module
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

use \Skeleton\Core\Web\Module;

class Web_Module_Container extends Module {

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
	public function display_pair() {
		if (Container::is_paired()) {
			Container_Response::output_forbidden();
			return;
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
	public function display_unpair() {
		if (!Container::is_paired()) {
			Container_Response::output_forbidden();
			return;
		}

		if (!Container_Permission::is_authenticated()) {
			Container_Response::output_forbidden();
			return;
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
	public function display_provision() {
		if (!Container::is_paired()) {
			Container_Response::output_forbidden();
			return;
		}
		if (!Container_Permission::is_authenticated()) {
			Container_Response::output_forbidden();
			return;
		}

		$zipfile = base64_decode($_POST['content']);
		file_put_contents(Skeleton\Core\Config::$tmp_dir . '/' . $_POST['name'] . '.zip', $zipfile);
		$zip = new ZipArchive();
		$zip->open(Skeleton\Core\Config::$tmp_dir . '/' . $_POST['name'] . '.zip');
		$zip->extractTo(Skeleton\Core\Config::$tmp_dir . '/../lib/service/' . $_POST['name']);
		$response = new Container_Response();
		$response->set_message('Provision successful');
		$response->output();
	}

	/**
	 * Provision
	 *
	 * @access public
	 */
	public function display_deprovision() {
		if (!Container::is_paired()) {
			Container_Response::output_forbidden();
			return;
		}
		if (!Container_Permission::is_authenticated()) {
			Container_Response::output_forbidden();
			return;
		}
		try {
			$service = Service::get_by_name($_POST['name']);
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
	public function display_info() {
		if (Container::is_paired() and !Container_Permission::is_authenticated()) {
			Container_Response::output_forbidden();
			return;
		}

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

}
