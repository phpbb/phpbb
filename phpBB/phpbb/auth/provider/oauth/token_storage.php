<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\auth\provider\oauth;

use OAuth\OAuth1\Token\StdOAuth1Token;
use OAuth\Common\Token\TokenInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Storage\Exception\TokenNotFoundException;
use OAuth\Common\Storage\Exception\AuthorizationStateNotFoundException;

/**
 * OAuth storage wrapper for phpBB's cache
 */
class token_storage implements TokenStorageInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var string OAuth table: token storage */
	protected $oauth_token_table;

	/** @var string OAuth table: state */
	protected $oauth_state_table;

	/** @var TokenInterface OAuth token */
	protected $cachedToken;

	/** @var string OAuth state */
	protected $cachedState;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\db\driver\driver_interface	$db					Database object
	 * @param \phpbb\user						$user				User object
	 * @param string							$oauth_token_table	OAuth table: token storage
	 * @param string							$oauth_state_table	OAuth table: state
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $oauth_token_table, $oauth_state_table)
	{
		$this->db	= $db;
		$this->user	= $user;

		$this->oauth_token_table = $oauth_token_table;
		$this->oauth_state_table = $oauth_state_table;
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieveAccessToken($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedToken instanceof TokenInterface)
		{
			return $this->cachedToken;
		}

		$data = [
			'user_id'	=> (int) $this->user->data['user_id'],
			'provider'	=> $service,
		];

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$data['session_id']	= $this->user->data['session_id'];
		}

		return $this->_retrieve_access_token($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function storeAccessToken($service, TokenInterface $token)
	{
		$service = $this->get_service_name_for_db($service);

		$this->cachedToken = $token;

		$data = [
			'oauth_token'	=> $this->json_encode_token($token),
		];

		$sql = 'UPDATE ' . $this->oauth_token_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . "
				AND provider = '" . $this->db->sql_escape($service) . "'";

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$sql .= " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "'";
		}

		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			$data = [
				'user_id'		=> (int) $this->user->data['user_id'],
				'provider'		=> $service,
				'oauth_token'	=> $this->json_encode_token($token),
				'session_id'	=> $this->user->data['session_id'],
			];

			$sql = 'INSERT INTO ' . $this->oauth_token_table . $this->db->sql_build_array('INSERT', $data);

			$this->db->sql_query($sql);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAccessToken($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedToken)
		{
			return true;
		}

		$data = [
			'user_id'	=> (int) $this->user->data['user_id'],
			'provider'	=> $service,
		];

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$data['session_id']	= $this->user->data['session_id'];
		}

		return $this->has_access_token($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearToken($service)
	{
		$service = $this->get_service_name_for_db($service);

		$this->cachedToken = null;

		$sql = 'DELETE FROM ' . $this->oauth_token_table . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . "
				AND provider = '" . $this->db->sql_escape($service) . "'";

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$sql .= " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "'";
		}

		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearAllTokens()
	{
		$this->cachedToken = null;

		$sql = 'DELETE FROM ' . $this->oauth_token_table . ' 
			WHERE user_id = ' . (int) $this->user->data['user_id'];

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$sql .= " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "'";
		}

		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function storeAuthorizationState($service, $state)
	{
		$service = $this->get_service_name_for_db($service);

		$this->cachedState = $state;

		$data = [
			'user_id'		=> (int) $this->user->data['user_id'],
			'provider'		=> $service,
			'oauth_state'	=> $state,
			'session_id'	=> $this->user->data['session_id'],
		];

		$sql = 'INSERT INTO ' . $this->oauth_state_table . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasAuthorizationState($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedState)
		{
			return true;
		}

		$data = [
			'user_id'	=> (int) $this->user->data['user_id'],
			'provider'	=> $service,
		];

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$data['session_id']	= $this->user->data['session_id'];
		}

		return (bool) $this->get_state_row($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieveAuthorizationState($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedState)
		{
			return $this->cachedState;
		}

		$data = [
			'user_id'	=> (int) $this->user->data['user_id'],
			'provider'	=> $service,
		];

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$data['session_id']	= $this->user->data['session_id'];
		}

		return $this->get_state_row($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearAuthorizationState($service)
	{
		$service = $this->get_service_name_for_db($service);

		$this->cachedState = null;

		$sql = 'DELETE FROM ' . $this->oauth_state_table . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . "
				AND provider = '" . $this->db->sql_escape($service) . "'";

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$sql .= " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "'";
		}

		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearAllAuthorizationStates()
	{
		$this->cachedState = null;

		$sql = 'DELETE FROM ' . $this->oauth_state_table . '
			WHERE user_id = ' . (int) $this->user->data['user_id'];

		if ((int) $this->user->data['user_id'] === ANONYMOUS)
		{
			$sql .= " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "'";
		}

		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * Updates the user_id field in the database associated with the token.
	 *
	 * @param int		$user_id	The user identifier
	 * @return void
	 */
	public function set_user_id($user_id)
	{
		if (!$this->cachedToken)
		{
			return;
		}

		$data = [
			'user_id' => (int) $user_id,
		];

		$sql = 'UPDATE ' . $this->oauth_token_table . '
			SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . "
				AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "'";
		$this->db->sql_query($sql);
	}

	/**
	 * Checks to see if an access token exists solely by the session_id of the user.
	 *
	 * @param string	$service	The OAuth service name
	 * @return bool					true if the user's access token exists,
	 * 								false if the user's access token does not exist
	 */
	public function has_access_token_by_session($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedToken)
		{
			return true;
		}

		$data = [
			'session_id'	=> $this->user->data['session_id'],
			'provider'		=> $service,
		];

		return $this->has_access_token($data);
	}

	/**
	 * Checks to see if a state exists solely by the session_id of the user.
	 *
	 * @param string	$service	The OAuth service name
	 * @return bool					true if the user's state exists,
	 * 								false if the user's state does not exist
	 */
	public function has_state_by_session($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedState)
		{
			return true;
		}

		$data = [
			'session_id'	=> $this->user->data['session_id'],
			'provider'		=> $service,
		];

		return (bool) $this->get_state_row($data);
	}

	/**
	 * A helper function that performs the query for has access token functions.
	 *
	 * @param array		$data		The SQL WHERE data
	 * @return bool					true if the user's access token exists,
	 * 								false if the user's access token does not exist
	 */
	protected function has_access_token($data)
	{
		return (bool) $this->get_access_token_row($data);
	}

	/**
	 * A helper function that performs the query for retrieving access token functions by session.
	 * Also checks if the token is a valid token.
	 *
	 * @param string	$service	The OAuth service provider name
	 * @return TokenInterface
	 * @throws TokenNotFoundException
	 */
	public function retrieve_access_token_by_session($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedToken instanceof TokenInterface)
		{
			return $this->cachedToken;
		}

		$data = [
			'session_id'	=> $this->user->data['session_id'],
			'provider'		=> $service,
		];

		return $this->_retrieve_access_token($data);
	}

	/**
	 * A helper function that performs the query for retrieving state functions by session.
	 *
	 * @param string	$service	The OAuth service provider name
	 * @return string				The OAuth state
	 * @throws AuthorizationStateNotFoundException
	 */
	public function retrieve_state_by_session($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedState)
		{
			return $this->cachedState;
		}

		$data = [
			'session_id'	=> $this->user->data['session_id'],
			'provider'		=> $service,
		];

		return $this->_retrieve_state($data);
	}

	/**
	 * A helper function that performs the query for retrieve access token functions.
	 * Also checks if the token is a valid token.
	 *
	 * @param array		$data		The SQL WHERE data
	 * @return TokenInterface
	 * @throws TokenNotFoundException
	 */
	protected function _retrieve_access_token($data)
	{
		$row = $this->get_access_token_row($data);

		if (!$row)
		{
			throw new TokenNotFoundException('AUTH_PROVIDER_OAUTH_TOKEN_ERROR_NOT_STORED');
		}

		$token = $this->json_decode_token($row['oauth_token']);

		// Ensure that the token was serialized/unserialized correctly
		if (!($token instanceof TokenInterface))
		{
			$this->clearToken($data['provider']);

			throw new TokenNotFoundException('AUTH_PROVIDER_OAUTH_TOKEN_ERROR_INCORRECTLY_STORED');
		}

		$this->cachedToken = $token;

		return $token;
	}

	/**
	 * A helper function that performs the query for retrieve state functions.
	 *
	 * @param array		$data		The SQL WHERE data
	 * @return string				The OAuth state
	 * @throws AuthorizationStateNotFoundException
	 */
	protected function _retrieve_state($data)
	{
		$row = $this->get_state_row($data);

		if (!$row)
		{
			throw new AuthorizationStateNotFoundException();
		}

		$this->cachedState = $row['oauth_state'];

		return $this->cachedState;
	}

	/**
	 * A helper function that performs the query for retrieving an access token.
	 *
	 * @param array		$data		The SQL WHERE data
	 * @return array|false			array with the OAuth token row,
	 *                       		false if the token does not exist
	 */
	protected function get_access_token_row($data)
	{
		$sql = 'SELECT oauth_token 
			FROM ' . $this->oauth_token_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', $data);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * A helper function that performs the query for retrieving a state.
	 *
	 * @param array		$data		The SQL WHERE data
	 * @return array|false			array with the OAuth state row,
	 *                       		false if the state does not exist
	 */
	protected function get_state_row($data)
	{
		$sql = 'SELECT oauth_state 
			FROM ' . $this->oauth_state_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', $data);
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * A helper function that JSON encodes a TokenInterface's data.
	 *
	 * @param TokenInterface	$token
	 * @return string					The json encoded TokenInterface's data
	 */
	public function json_encode_token(TokenInterface $token)
	{
		$members = [
			'accessToken'	=> $token->getAccessToken(),
			'endOfLife'		=> $token->getEndOfLife(),
			'extraParams'	=> $token->getExtraParams(),
			'refreshToken'	=> $token->getRefreshToken(),

			'token_class'	=> get_class($token),
		];

		// Handle additional data needed for OAuth1 tokens
		if ($token instanceof StdOAuth1Token)
		{
			$members['requestToken']		= $token->getRequestToken();
			$members['requestTokenSecret']	= $token->getRequestTokenSecret();
			$members['accessTokenSecret']	= $token->getAccessTokenSecret();
		}

		return json_encode($members);
	}

	/**
	 * A helper function that JSON decodes a data string and creates a TokenInterface.
	 *
	 * @param string	$json			The json encoded TokenInterface's data
	 * @return TokenInterface
	 * @throws TokenNotFoundException
	 */
	public function json_decode_token($json)
	{
		$token_data = json_decode($json, true);

		if ($token_data === null)
		{
			throw new TokenNotFoundException('AUTH_PROVIDER_OAUTH_TOKEN_ERROR_INCORRECTLY_STORED');
		}

		$token_class	= $token_data['token_class'];
		$access_token	= $token_data['accessToken'];
		$refresh_token	= $token_data['refreshToken'];
		$endOfLife		= $token_data['endOfLife'];
		$extra_params	= $token_data['extraParams'];

		/**
		 * Create the token
		 * @var TokenInterface	$token
		 */
		$token = new $token_class($access_token, $refresh_token, TokenInterface::EOL_NEVER_EXPIRES, $extra_params);
		$token->setEndOfLife($endOfLife);

		// Handle OAuth 1.0 specific elements
		if ($token instanceof StdOAuth1Token)
		{
			$token->setRequestToken($token_data['requestToken']);
			$token->setRequestTokenSecret($token_data['requestTokenSecret']);
			$token->setAccessTokenSecret($token_data['accessTokenSecret']);
		}

		return $token;
	}

	/**
	 * Returns the service name as it must be stored in the database.
	 *
	 * @param string	$provider	The OAuth provider name
	 * @return string				The OAuth service name
	 */
	protected function get_service_name_for_db($provider)
	{
		// Enforce the naming convention for oauth services
		if (strpos($provider, 'auth.provider.oauth.service.') !== 0)
		{
			$provider = 'auth.provider.oauth.service.' . strtolower($provider);
		}

		return $provider;
	}
}
