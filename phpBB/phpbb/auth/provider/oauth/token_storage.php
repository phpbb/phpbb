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

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use League\OAuth2\Client\Token\ResourceOwnerAccessTokenInterface;

/**
 * OAuth storage wrapper for phpBB's cache
 */
class token_storage
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var string OAuth table: token storage */
	protected $oauth_token_table;

	/** @var string OAuth table: state */
	protected $oauth_state_table;

	/** @var AccessTokenInterface OAuth token */
	protected $cachedToken;

	/** @var string OAuth state */
	protected $cachedState;

	/** @var string|null PKCE verifier for the cached state */
	protected $cachedCodeVerifier;

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

		if ($this->cachedToken instanceof AccessTokenInterface)
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
	public function storeAccessToken($service, AccessTokenInterface $token)
	{
		$service = $this->get_service_name_for_db($service);

		$this->cachedToken = $token;

		$data = $this->get_token_data($token);

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
				...$this->get_token_data($token),
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

		if ($this->cachedToken instanceof AccessTokenInterface)
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

		if ((int) $this->user->data['user_id'] === ANONYMOUS && isset($this->user->data['session_id']))
		{
			$sql .= " AND session_id = '" . $this->db->sql_escape($this->user->data['session_id']) . "'";
		}

		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function storeAuthorizationState($service, $state, $code_verifier = '')
	{
		$service = $this->get_service_name_for_db($service);

		$this->cachedState = $state;
		$this->cachedCodeVerifier = $code_verifier;

		$data = [
			'user_id'		=> (int) $this->user->data['user_id'],
			'provider'		=> $service,
			'oauth_state'	=> $state,
			'oauth_code_verifier' => $code_verifier,
			'session_id'	=> $this->user->data['session_id'],
			'state_time'	=> time(),
		];

		$sql = 'INSERT INTO ' . $this->oauth_state_table . ' ' . $this->db->sql_build_array('INSERT', $data);
		$this->db->sql_query($sql);

		return $this;
	}

	/**
	 * Retrieve and consume an exact state for the current user/session.
	 *
	 * @param string $service
	 * @param string $state
	 * @return array State and code verifier.
	 * @throws \RuntimeException
	 */
	public function consumeAuthorizationState($service, $state): array
	{
		$service = $this->get_service_name_for_db($service);
		$data = [
			'user_id' => (int) $this->user->data['user_id'],
			'provider' => $service,
			'oauth_state' => $state,
			'session_id' => $this->user->data['session_id'],
		];

		$row = $this->get_state_row($data);
		if (!$row || (int) $row['state_time'] < time() - 600)
		{
			throw new \RuntimeException('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
		}

		$sql = 'DELETE FROM ' . $this->oauth_state_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', $data);
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows())
		{
			throw new \RuntimeException('AUTH_PROVIDER_OAUTH_ERROR_REQUEST');
		}

		$this->cachedState = null;
		$this->cachedCodeVerifier = null;

		return [
			'state' => $row['oauth_state'],
			'code_verifier' => $row['oauth_code_verifier'],
		];
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
			'user_id'		=> (int) $this->user->data['user_id'],
			'provider'		=> $service,
			'session_id'	=> $this->user->data['session_id'],
		];

		return $this->_retrieve_state($data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function clearAuthorizationState($service)
	{
		$service = $this->get_service_name_for_db($service);

		$this->cachedState = null;
		$this->cachedCodeVerifier = null;

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
		$this->cachedCodeVerifier = null;

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
	 * @return AccessTokenInterface
	 * @throws \RuntimeException
	 */
	public function retrieve_access_token_by_session($service)
	{
		$service = $this->get_service_name_for_db($service);

		if ($this->cachedToken instanceof AccessTokenInterface)
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
	 * @return string|null			The OAuth state, or null if not stored
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
	 * @return AccessTokenInterface
	 * @throws \RuntimeException
	 */
	protected function _retrieve_access_token($data)
	{
		$row = $this->get_access_token_row($data);

		if (!$row)
		{
			throw new \RuntimeException('AUTH_PROVIDER_OAUTH_TOKEN_ERROR_NOT_STORED');
		}

		try
		{
			$token = $this->json_decode_token($row['oauth_token'], $row['oauth_resource_owner_id']);
		}
		catch (\RuntimeException $e)
		{
			$this->clearToken($data['provider']);
			throw $e;
		}

		// Ensure that the token was serialized/unserialized correctly
		if (!($token instanceof AccessTokenInterface))
		{
			$this->clearToken($data['provider']);

			throw new \RuntimeException('AUTH_PROVIDER_OAUTH_TOKEN_ERROR_INCORRECTLY_STORED');
		}

		$this->cachedToken = $token;

		return $token;
	}

	/**
	 * A helper function that performs the query for retrieve state functions.
	 *
	 * @param array $data The SQL WHERE data
	 * @return string                The OAuth state, or empty string if not stored
	 * @throws \RuntimeException If state was not stored
	 */
	protected function _retrieve_state(array $data): string
	{
		$row = $this->get_state_row($data);

		if (!$row)
		{
			throw new \RuntimeException('State not stored');
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
		$sql = 'SELECT oauth_token, oauth_resource_owner_id
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
	 * @param array $data		The SQL WHERE data
	 * @return array|false			array with the OAuth state row,
	 *								false if the state does not exist
	 */
	protected function get_state_row(array $data)
	{
		$sql = 'SELECT oauth_state, oauth_code_verifier, state_time
			FROM ' . $this->oauth_state_table . '
			WHERE ' . $this->db->sql_build_array('SELECT', $data) . '
			ORDER BY state_time DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * A helper function that JSON encodes a League access token's data.
	 *
	 * @param AccessTokenInterface $token
	 * @return false|string The json encoded token data
	 */
	public function json_encode_token(AccessTokenInterface $token): false|string
	{
		return json_encode([
			'access_token' => $token->getToken(),
			'refresh_token' => $token->getRefreshToken(),
			'expires' => $token->getExpires(),
			'values' => $token instanceof AccessToken ? $token->getValues() : [],
		]);
	}

	/**
	 * A helper function that JSON decodes a data string and creates an AccessToken.
	 *
	 * @param string	$json					The json encoded TokenInterface's data
	 * @param string	$resource_owner_id	The persisted resource owner identifier
	 * @return AccessTokenInterface
	 * @throws \RuntimeException
	 */
	public function json_decode_token($json, $resource_owner_id = ''): AccessTokenInterface
	{
		$token_data = json_decode($json, true);

		if (!is_array($token_data) || !is_string($token_data['access_token'] ?? null) || $token_data['access_token'] === '')
		{
			throw new \RuntimeException('AUTH_PROVIDER_OAUTH_TOKEN_ERROR_INCORRECTLY_STORED');
		}

		$values = is_array($token_data['values'] ?? null) ? $token_data['values'] : [];
		$values = array_diff_key($values, array_flip([
			'access_token',
			'refresh_token',
			'expires',
			'expires_in',
			'resource_owner_id',
		]));

		$options = [
			'access_token' => $token_data['access_token'],
			'refresh_token' => $token_data['refresh_token'] ?? null,
			...$values,
		];

		if (is_string($resource_owner_id) && $resource_owner_id !== '')
		{
			$options['resource_owner_id'] = $resource_owner_id;
		}

		if (($token_data['expires'] ?? null) === 0)
		{
			$options['expires_in'] = 0;
		}
		else if (isset($token_data['expires']))
		{
			$options['expires'] = $token_data['expires'];
		}

		return new AccessToken($options);
	}

	/**
	 * Returns the database fields for an access token.
	 *
	 * @param AccessTokenInterface $token
	 * @return array
	 */
	protected function get_token_data(AccessTokenInterface $token): array
	{
		$resource_owner_id = $token instanceof ResourceOwnerAccessTokenInterface ? $token->getResourceOwnerId() : null;

		return [
			'oauth_token'				=> $this->json_encode_token($token),
			'oauth_resource_owner_id'	=> is_string($resource_owner_id) ? $resource_owner_id : '',
		];
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
