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
* This auth provider uses the legacy usernam/password system.
*
* @package auth
*/
class phpbb_auth_provider_olympus implements phpbb_auth_provider_interface
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
			'NAME'		=> 'olympus',
			'ENABLED'	=> true,
			'OPTIONS'	=> array(
				'ADMIN'		=> true,
			),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function process()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function verify()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	public function login()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	protected function register()
	{

	}

	/**
	 * {@inheritDoc}
	 */
	protected function link()
	{

	}
}
