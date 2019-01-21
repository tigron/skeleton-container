<?php
/**
 * App Configuration Class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */
class Config_Container extends Config {

	/**
	 * Config array
	 *
	 * @var array
	 * @access private
	 */
	protected $config_data = [

		/**
		 * Hostnames
		 */
		'hostnames' => ['*'],

		/**
		 * Routes
		 */
		'routes' => [

			'web_module_login' => [
				'$language[en]/login',
				'$language[en]/login/$action',

				'$language[nl]/aanmelden',
				'$language[nl]/aanmelden/$action'
			],

		],
	];
}
