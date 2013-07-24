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
class phpbb_auth_provider_oauth_token_storage implements TokenStorageInterface
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
	* OAuth token table
	*
	* @var string
	*/
	protected $auth_provider_oauth_table;

	/**
	* @var object|TokenInterface
	*/
	protected $cachedToken;

	/**
	* Creates token storage for phpBB.
	*
	* @param	phpbb_db_driver	$db
	* @param	phpbb_user		$user
	* @param	string			$service_name
	* @param	string			$auth_provider_oauth_table
	*/
	public function __construct(phpbb_db_driver $db, phpbb_user $user, $service_name, $auth_provider_oauth_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->service_name = $service_name;
		$this->auth_provider_oauth_table = $auth_provider_oauth_table;
	}

	/**
	* {@inheritdoc}
	*/
	public function retrieveAccessToken()
	{
		if( $this->cachedToken instanceOf TokenInterface ) {
			return $this->token;
		}

		$data = array(
			'user_id'			=> $this->user->data['user_id'],
			'oauth_provider'	=> $this->service_name,
		);

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$data['session_id']	= $this->user->data['session_id'];
		}

		$sql = 'SELECT oauth_token FROM ' . $this->auth_provider_oauth_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', $data);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			// TODO: translate
			throw new TokenNotFoundException('Token not stored');
		}

		$token = unserialize($row['oauth_token']);

		// Ensure that the token was serialized/unserialized correctly
		if (!($token instanceof TokenInterface))
		{
			$this->clearToken();
			// TODO: translate
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

		$data = array(
			'user_id'			=> $this->user->data['user_id'],
			'oauth_provider'	=> $this->service_name,
			'oauth_token'		=> serialize($token),
		);

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$data['session_id']	= $this->user->data['session_id'];
		}

		$sql = 'INSERT INTO ' . $this->auth_provider_oauth_table . '
			' . $this->db->sql_build_array('INSERT', $data);
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

		$data = array(
			'user_id'			=> $this->user->data['user_id'],
			'oauth_provider'	=> $this->service_name,
		);

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$data['session_id']	= $this->user->data['session_id'];
		}

		$sql = 'SELECT oauth_token FROM ' . $this->auth_provider_oauth_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', $data);
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

		$sql = 'DELETE FROM ' . $this->auth_provider_oauth_table . '
			WHERE user_id = ' . $this->user->data['user_id'] . '
				AND oauth_provider = ' . $this->db->sql_escape($this->oauth_provider);

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			$sql .= ' AND session_id = ' . $this->user->data['session_id'];
		}

		$this->db->sql_query($sql);
	}

	/**
	* Updates the user_id field in the database assosciated with the token
	*
	* @param	int	$user_id
	*/
	public function set_user_id($user_id)
	{
		if (!$this->cachedToken)
		{
			return;
		}

		$sql = 'UPDATE ' . $this->auth_provider_oauth_table . '
			SET ' . $db->sql_build_array('UPDATE', array(
					'user_id' => (int) $user_id
				)) . '
				WHERE user_id = ' . $this->user->data['user_id'] . '
					AND session_id = ' . $this->user->data['session_id'];
		$this->db->sql_query($sql);
	}
}
