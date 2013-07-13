<?php
/**
*
* @package auth
* @copyright (c) 2013 phpBB Group
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


use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Storage\Exception\StorageException;
use OAuth\Common\Storage\Exception\TokenNotFoundException;

/**
* OAuth storage wrapper for phpbb's cache
*
* @package auth
*/
class phpbb_auth_oauth_token_storage implements TokenStorageInterface
{
	/**
	* Cache driver.
	*
	* @var phpbb_db_driver
	*/
	protected $db;

	/**
	* phpBB user
	*
	* @var phpbb_user
	*/
	protected $user;

	/**
	* Name of the OAuth provider
	*
	* @var string
	*/
	protected $service_name;

	/**
	* @var object|TokenInterface
	*/
	protected $cachedToken;

	/**
	* Creates token storage for phpBB.
	*
	* @param phpbb_db_driver	$db
	* @param phpbb_user			$user
	* @param string				$service_name
	*/
	public function __construct(phpbb_db_driver $db, phpbb_user $user, $service_name)
	{
		$this->db = $db;
		$this->user = $user;
		$this->service_name = $service_name;
	}

	/**
	* {@inheritdoc}
	*/
	public function retrieveAccessToken()
	{
		if( $this->cachedToken instanceOf TokenInterface ) {
			return $this->token;
		}

		$sql = 'SELECT oauth_token FROM ' . AUTH_PROVIDER_OAUTH .
			$db->sql_build_array('SELECT', array(
				'user_id'			=> $this->user->data['user_id'],
				'oauth_provider'	=> $this->service_name,
			));
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			throw new TokenNotFoundException('Token not stored');
		}

		$token = unserialize($row['oauth_token']);

		// Ensure that the token was serialized/unserialized correctly
		if (!($token instanceof TokenInterface))
		{
			$this->clearToken();
			throw new TokenNotFoundException('Token not stored correctly');
		}

		$this->cachedToken = $token;
		return $token;
	}

	/**
	* {@inheritdoc}
	*/
	public function storeAccessToken(TokenInterface $token)
	{
		$this->cachedToken = $token;

		$sql = 'INSERT INTO ' . AUTH_PROVIDER_OAUTH . ' ' . $this->db->sql_build_array('INSERT', array(
			'user_id'			=> $this->user->data['user_id'],
			'oauth_provider'	=> $this->service_name,
			'oauth_token'		=> serialize($token),
		));
		$this->db->sql_query($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function hasAccessToken()
	{
		if( $this->cachedToken ) {
			return true;
		}

		$sql = 'SELECT oauth_token FROM ' . AUTH_PROVIDER_OAUTH .
			$db->sql_build_array('SELECT', array(
				'user_id'			=> $this->user->data['user_id'],
				'oauth_provider'	=> $this->service_name,
			));
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return false;
		}

		return true;
	}

	/**
	* {@inheritdoc}
	*/
	public function clearToken()
	{
		$this->cachedToken = null;

		$sql = 'DELETE FROM ' . AUTH_PROVIDER_OAUTH . 'WHERE user_id = ' . $this->user->data['user_id'] .
			' AND oauth_provider = ' . $this->db->sql_escape($this->oauth_provider);
		$this->db->sql_query($sql);
	}
}
