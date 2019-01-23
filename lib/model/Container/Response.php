<?php
/**
 * Container_Response Class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

class Container_Response {

	/**
	 * Message string
	 *
	 * @access private
	 * @var string $message
	 */
	private $message = '';

	/**
	 * HTTP status code
	 *
	 * @access private
	 * @var string $status_code
	 */
	private $status_code = 200;

	/**
	 * Data (the payload)
	 *
	 * @access private
	 * @var mixed $data
	 */
	private $data = null;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() { }

	/**
	 * Set the message
	 *
	 * @access public
	 */
	public function set_message($message) {
		$this->message = $message;
	}

	/**
	 * Set status_code
	 *
	 * @access public
	 * @param string $status_code
	 */
	public function set_status_code($status_code) {
		$this->status_code = $status_code;
	}

	/**
	 * Set data
	 *
	 * @access public
	 * @param mixed $data
	 */
	public function set_data($data) {
		$this->data = $data;
	}

	/**
	 * Output
	 *
	 * @access public
	 */
	public function output() {
		header($_SERVER['SERVER_PROTOCOL'] . ' ' . $this->status_code);
		header('Content-Type: application/json');
		$response = [];
		$response['message'] = $this->message;
		if (isset($this->data)) {
			$response['data'] = $this->data;
		}
		echo json_encode($response, JSON_PRETTY_PRINT);
		exit;
	}

	/**
	 * Output forbidden
	 *
	 * @access public
	 */
	public static function output_forbidden() {
		self::code_403('Not allowed to perform this action');
	}

	/**
	 * Call
	 *
	 * @access public
	 * @param string $method
	 * @param array $arguments
	 */
	public static function __callstatic($method, $arguments = []) {
		preg_match('/code_(\d*)/', $method, $output_array);
		if (!isset($output_array[1])) {
			throw new \Exception('Use Container_Response::code_XXX to respond');
		}

		if (!is_array($arguments) or count($arguments) == 0) {
			throw new \Exception('Please give a response message');
		}

		$response= new self();
		$response->set_status_code($output_array[1]);
		$response->set_message($arguments[0]);
		$response->output();
		exit;
	}

}
