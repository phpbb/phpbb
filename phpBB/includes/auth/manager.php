<?php
/**
 *
 * @package auth
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * This class handles the selection of which authentication provider to use for
 * login and registration. It also provides functions to get enabled and
 * registered providers.
 *
 * @package auth
 */
class phpbb_auth_manager
{
	protected $request;
	protected $db;
	protected $config;
	protected $user;

	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config, phpbb_user $user)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
	}

	public function get_provider($auth_type)
	{
		$provider = 'phpbb_auth_provider_' . $auth_type;
		if (class_exists($provider))
		{
			return new $provider($this->request, $this->db, $this->config, $this->user);
		}
		else
		{
			throw new phpbb_auth_exception('Authentication provider, ' . $provider . ', not found.');
		}
	}

	// Return temporary set array of providers.
	public function get_registered_providers() {
		$providers = array(
			'olympus',
			'openid',
			'facebook_connect',
		);

		foreach($providers as &$provider)
		{
			$provider = $this->get_provider($provider);
		}

		return $providers;
	}

	public function get_enabled_providers() {
		$providers = $this->get_registered_providers();

		foreach($providers as &$provider) {
			$provider_config->get_configuration();
			if(!$provider_config['OPTIONS']['enabled']['setting'])
			{
				unset($provider);
			}
		}

		return $providers;
	}
}
