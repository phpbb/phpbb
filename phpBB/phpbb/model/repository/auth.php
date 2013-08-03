<?php
/**
 *
 * @package api
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

use Symfony\Component\HttpFoundation\Response;

/**
 * This repository handles authentication
 * @package phpBB3
 */
class phpbb_model_repository_auth
{

	/**
	 * phpBB configuration
	 * @var phpbb_config
	 */
	protected $config;

	/** @var phpbb_db_driver */
	protected $db;

	/** @var phpbb_auth */
	protected $auth;

	/**
	 * Constructor
	 *
	 * @param phpbb_config $config
	 * @param phpbb_db_driver $db
	 */
	function __construct(phpbb_config $config, phpbb_db_driver $db, phpbb_auth $auth)
	{
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
	}

	public function allow($auth_key, $sign_key, $user_id, $name)
	{
		$sql = 'INSERT INTO ' . API_KEYS_TABLE
			. " (user_id, name, auth_key, sign_key) VALUES (' " . $user_id
			. "', '" . $this->db->sql_escape($name)
			. "', '" . $this->db->sql_escape($auth_key)
			. "', '" . $this->db->sql_escape($sign_key) . "')";

		$this->db->sql_query($sql);
	}

	public function verify($auth_key, $serial, $hash)
	{
		$sql = 'SELECT sign_key
				FROM ' . API_KEYS_TABLE
			. " WHERE auth_key = '" . $this->db->sql_escape($auth_key) . "'";

		$result = $this->db->sql_query($sql);

		$row = $this->db->sql_fetchrow($result);
		$sign_key = $row['sign_key'];

		if (empty($sign_key))
		{
			return false;
		}

		$test_hash = hash_hmac('sha256', 'api/auth/verify/' . $auth_key . '/' . $serial, $sign_key);

		if ($hash == $test_hash)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Verifies a request
	 *
	 * @param $request String The request url, for example api/forums/2....
	 * @param $auth_key
	 * @param $serial
	 * @param $hash
	 * @param bool $api_response Weather or not to return a boolean or a response array on failure
	 * @return array|int
	 */
	public function auth($request, $auth_key, $serial, $hash,  $api_response = true)
	{
		if (!$this->config['allow_api'])
		{
			if ($api_response)
			{
				$response = array(
					'status' => 500,
					'data' => 'The API is not enabled on this board',
				);
				return $response;
			}
			else
			{
				return false;
			}
		}

		if ($auth_key != 'guest')
		{
			$sql = 'SELECT sign_key, user_id
					FROM ' . API_KEYS_TABLE
				. " WHERE auth_key = '" . $this->db->sql_escape($auth_key) . "'";

			$result = $this->db->sql_query($sql);

			$row = $this->db->sql_fetchrow($result);
			$sign_key = $row['sign_key'];
			$user_id = $row['user_id'];

			if (empty($sign_key))
			{
				if ($api_response)
				{
					$response = array(
						'status' => 401,
						'data' => 'The user has not authenticated this application',
					);
					return $response;
				}
				else
				{
					return false;
				}
			}

			// This probably needs to be changed before release
			$request .= '&auth_key=' . $auth_key . '&serial=' . $serial;

			$test_hash = hash_hmac('sha256', $request, $sign_key);

			if ($hash != $test_hash)
			{
				if ($api_response)
				{
					$response = array(
						'status' => 400,
						'data' => 'Invalid hash',
					);
					return $response;
				}
				else
				{
					return false;
				}
			}

			$userdata = $this->auth->obtain_user_data($user_id);
			$this->auth->acl($userdata);

			if ($this->auth->acl_get('u_api'))
			{
				return $user_id;
			}
			else
			{
				if ($api_response)
				{
					$response = array(
						'status' => 403,
						'data' => 'User has no permission to use the API',
					);
					return $response;
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			$userdata = $this->auth->obtain_user_data(ANONYMOUS);
			$this->auth->acl($userdata);

			if ($this->auth->acl_get('u_api'))
			{
				return ANONYMOUS;
			}
			else
			{
				if ($api_response)
				{
					$response = array(
						'status' => 403,
						'data' => 'User has no permission to use the API',
					);
					return $response;
				}
				else
				{
					return false;
				}
			}
		}
	}

	public function has_permission($user_id, $permission, $forum_id = 0)
	{
		$userdata = $this->auth->obtain_user_data($user_id);
		$this->auth->acl($userdata);

		return $this->auth->acl_get($permission, $forum_id);
	}
}
