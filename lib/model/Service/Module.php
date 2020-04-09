<?php
/**
 * Module management class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 */

abstract class Service_Module {

	/**
	 * Accept the request
	 *
	 * @access public
	 */
	public function accept_request() {
		if (isset($_SERVER['HTTP_KEY'])) {
			\Container_Permission::$key = $_SERVER['HTTP_KEY'];
		}

		// Call our magic secure() method before passing on the request
		$allowed = false;
		if (method_exists($this, 'secure')) {
			$allowed = $this->secure();
		}

		// If the request is not allowed, make sure it gets handled properly
		if ($allowed === false) {
			Container_Response::output_forbidden();
		} else {
			$this->handle_request();
		}
	}

	/**
	 * get module_path
	 *
	 * @access public
	 * @return string $path
	 */
	public function get_module_path() {
		$reflection = new \ReflectionClass($this);
		$application = Application::Get();
		$path = '/' . str_replace($application->module_path, '', $reflection->getFileName());
		$path = str_replace('.php', '', $path);
		return $path;
	}

	/**
	 * Handle the request
	 *
	 * @access public
	 */
	public function handle_request() {
		$input = file_get_contents('php://input');
		$post = json_decode($input, true);
		if ($post === null) {
			$post = [];
		}

		// Find out which method to call, fall back to calling displa()
		if (isset($_REQUEST['action']) AND method_exists($this, 'handle_' . $_REQUEST['action'])) {
			try {
				call_user_func_array([$this, 'handle_'.$_REQUEST['action']], $post);
			} catch (\ArgumentCountError $e) {
				$response = new Container_Response();
				$response->set_status_code(500);
				$response->set_message($e->getMessage());
				$response->output();
			} catch (\Exception $e) {
				$response = new Container_Response();
				$response->set_status_code(500);
				$response->set_message($e->getMessage());
				$response->output();
			}
		} else {
			$response = new Container_Response();
			$response->set_status_code(404);
			$response->set_message('Action ' . $_REQUEST['action'] . ' not found for service ' . $this->get_name());
			$response->output();
		}
	}

	/**
	 * Get the classname of the current module
	 *
	 * @access public
	 */
	public function get_name() {
		if (strpos(get_class($this), 'Web_Module_') !== false) {
			return strtolower(substr(get_class($this),strlen('Web_Module_')));
		}

		return strtolower(get_class($this));
	}

	/**
	 * Secure
	 *
	 * @access public
	 */
	public function secure() {
		if (!Container_Permission::is_authenticated()) {
			return false;
		}
		return true;
	}
}
