<?php
/**
 * Configuration Class
 *
 * Implemented as singleton (only one instance globally).
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */


class Config {
	/**
	 * Config array
	 *
	 * @var array
	 * @access private
	 */
	protected $config_data = [];

	/**
	 * Config object
	 *
	 * @var Config
	 * @access private
	 */
	private static $config = null;

	/**
	 * Private (disabled) constructor
	 *
	 * @access private
	 */
	public function __construct() {
		$this->config_data = array_merge($this->read(), $this->config_data);

		// See if we have an environment file, if that is the case, its contents
		// should override any configuration defined in our current config_data
		$environment_file = dirname(__FILE__) . '/../.environment.php';
		if (file_exists($environment_file)) {
			require($environment_file);
			$this->config_data = array_merge($this->config_data, $environment);
		}
	}

	/**
	 * Get config vars as properties
	 *
	 * @param string name
	 * @return mixed
	 * @throws Exception When accessing an unknown config variable, an Exception is thrown
	 * @access public
	 */
	public function __get($name) {
		if (!array_key_exists($name, $this->config_data)) {
			throw new Exception('Attempting to read unkown config key: '.$name);
		}
		return $this->config_data[$name];
	}

	/**
	 * Get function, returns a Config object
	 *
	 * @return Config
	 * @access public
	 */
	public static function Get() {
		if (!isset(self::$config)) {
			try {
				self::$config = \Skeleton\Core\Application::Get()->config;
			} catch (Exception $e) {
				return new Config();
			}
		}
		return self::$config;
	}

	/**
	 * Check if config var exists
	 *
	 * @param string key
	 * @return bool $isset
	 * @access public
	 */
	public function __isset($key) {
		if (!isset($this->config_data) OR $this->config_data === null) {
			$this->read();
		}

		if (array_key_exists($key, $this->config_data)) {
			return true;
		}

		return false;
	}

	/**
	 * Read config file
	 *
	 * Populates the $this->config var, now the config is just in this function
	 * but it could easily be replaced with something else
	 *
	 * @access private
	 */
	private function read() {
		return [
			/**
			 * Default module
			 */
			'module_default' => 'index',
		];
	}
}
