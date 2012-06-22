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
* This auth provider uses Facebook Connect to register and login Facebook users.
*
* @package auth
*/
class phpbb_auth_provider_facebook_connect implements phpbb_auth_provider_interface
{
	protected $request;
	protected $db;
	protected $config;
	protected $user;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config, phpbb_user $user)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_configuration()
	{
		return array(
			'CUSTOM_ACP'=> false,
			'NAME'		=> 'facebook_connect',
			'OPTIONS'	=> array(
				'enabled'	=> array('setting' => $this->config['facebook_connect_enabled'], 'lang' => 'AUTH_ENABLE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => false),
				'admin'		=> array('setting' => $this->config['facebook_connect_admin'], 'lang' => 'ALLOW_ADMIN_LOGIN', 'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function process($admin = false)
	{
		$provider_config = $this->get_configuration();
		if(!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function verify()
	{
		$provider_config = $this->get_configuration();
		if(!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}
	}
}
