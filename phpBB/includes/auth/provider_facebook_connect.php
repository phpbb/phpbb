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
class phpbb_auth_provider_facebook_connect extends phpbb_auth_common_provider
	implements phpbb_auth_provider_custom_login_interface, phpbb_auth_provider_custom_registration_interface
{
	protected $request;
	protected $db;
	protected $config;
	protected $user;

	protected $phpbb_root_path;
	protected $phpEx;
	protected $SID;
	protected $_SID;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(phpbb_request $request, dbal $db, phpbb_config_db $config)
	{
		$this->request = $request;
		$this->db = $db;
		$this->config = $config;

		global $phpbb_root_path, $phpEx, $SID, $_SID;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->phpEx = $phpEx;
		$this->SID = $SID;
		$this->_SID = $_SID;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_configuration()
	{
		return array(
			'NAME'		=> 'facebook_connect',
			'OPTIONS'	=> array(
				'enabled'	=> array('setting' => $this->config['auth_provider_facebook_connect_enabled'],	'lang' => 'AUTH_ENABLE',		'validate' => 'bool',	'type' => 'radio:enabled_disabled',	'explain' => false),
				'admin'		=> array('setting' => $this->config['auth_provider_facebook_connect_admin'],	'lang' => 'ALLOW_ADMIN_LOGIN',	'validate' => 'bool',	'type' => 'radio:yes_no',			'explain' => true),
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_login_box(phpbb_template $template, $redirect = '', $admin = false, $full_login_box = true)
	{
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generate_registration(phpbb_template $template) {
		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function process($admin = false)
	{
		$provider_config = $this->get_configuration();
		if (!$provider_config['OPTIONS']['enabled']['setting'])
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
		if (!$provider_config['OPTIONS']['enabled']['setting'])
		{
			throw new phpbb_auth_exception('AUTH_DISABLED');
		}
	}
}
