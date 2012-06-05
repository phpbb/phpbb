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
* This auth provider uses the OpenID form of login.
*
* @package auth
*/
class phpbb_auth_provider_open_id implements phpbb_auth_provider_interface
{
	protected $request;
	protected $db;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(phpbb_request $request, dbal $db)
	{
		$this->request = $request;
		$this->db = $db;
	}
	/**
	 * Performs the login request on the external server specified by
	 * open_id_identifier. Redirects the browser first to the external server
	 * for authentication then back to /check_auth_openid.php to complete
	 * login.
	 */
	public function process()
	{
		$storage = new phpbb_auth_zend_storage($this->db);
		$this->consumer = $consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);

		if($this->request->variable('open_id_identifier', '') == '')
		{
			throw new phpbb_auth_exception('No OpenID identifier supplied.');
		}
		else
		{
			$identifier = $this->request->variable('open_id_identifier', '');
		}
		$return_to = 'phpBB/check_auth_openid.php';

		// Enable super globals so Zend Framework does not throw errors.
		$this->request->enable_super_globals();

		if (!$consumer->login($identifier, $return_to, 'http://192.168.0.112/'))
		{
			$this->request->disable_super_globals();
			throw new phpbb_auth_exception($consumer->getError());
		}
		$this->request->disable_super_globals();
	}

	/**
	 * Verifies the returned values from the external server.
	 */
	public function verify()
	{
		$storage = new phpbb_auth_zend_storage($this->db);
		$consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);

		// Enable super globals so Zend Framework does not throw errors.
		$this->request->enable_super_globals();
		$id = '';
		if ($consumer->verify($_GET, $id))
		{
			$user_id = 0;
			$this->link($user_id, $this->request->variable('openid_response_nonce', ''));
		}
		else
		{
			$this->request->disable_super_globals();
			throw new phpbb_auth_exception('OpenID authentication failed.');
		}
		$this->request->disable_super_globals();

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function link($nonce)
	{
		$linker = new phpbb_auth_link_manager();
	}
}
