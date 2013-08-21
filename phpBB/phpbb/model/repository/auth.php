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
	 * @param phpbb_auth $auth
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

	/**
	 * Verifies a request
	 *
	 * @param $request String The request url, for example api/forums/2....
	 * @param string $auth_key
	 * @param $serial
	 * @param $hash
	 * @param int $forum_id
	 * @return array|int
	 */
	public function auth($request = null, $auth_key = 'guest', $serial = null, $hash = null, $forum_id = 0)
	{
		if (!$this->config['allow_api'])
		{
			$response = array(
				'status' => 500,
				'data' => array(
					'error' => 'The API is not enabled on this board',
					'valid' => false,
				),
			);
			return $response;
		}

		if ($auth_key != 'guest')
		{
			$sql = 'SELECT sign_key, user_id, serial
					FROM ' . API_KEYS_TABLE
				. " WHERE auth_key = '" . $this->db->sql_escape($auth_key) . "'";

			$result = $this->db->sql_query($sql);

			$row = $this->db->sql_fetchrow($result);
			$sign_key = $row['sign_key'];
			$user_id = (int) $row['user_id'];
			$dbserial =  (int) $row['serial'];

			if (empty($sign_key))
			{
				$response = array(
					'status' => 401,
					'data' => array(
						'error' => 'The user has not authenticated this application',
						'valid' => false,
					),
				);
				return $response;
			}

			if (is_array($request))
			{
				$request = implode('/', $request);
			}
			else
			{
				// This probably needs to be changed before release
				$request .= 'auth_key=' . $auth_key . '&serial=' . $serial;
			}

			$test_hash = hash_hmac('sha256', $request, $sign_key);

			if ($hash != $test_hash)
			{
				$response = array(
					'status' => 400,
					'data' => array(
						'error' => 'Invalid hash',
						'valid' => false,
					),
				);
				return $response;
			}

			if ($serial <= $dbserial)
			{
				$response = array(
					'status' => 400,
					'data' => array(
						'error' => 'Invalid serial',
						'valid' => false,
					),
				);
				return $response;
			}

			$userdata = $this->auth->obtain_user_data($user_id);
			$this->auth->acl($userdata);

			if ($this->auth->acl_get('u_api'))
			{
				if ($forum_id != 0)
				{
					if (!$this->auth->acl_get('f_read', $forum_id))
					{
						$response = array(
							'status' => 403,
							'data' => array(
								'error' => 'User has no permission to see this forum.',
								'valid' => false,
							),
						);
						return $response;
					}
				}

				$sql = 'UPDATE ' . API_KEYS_TABLE
					. ' SET serial = ' . $this->db->sql_escape($serial)
					. " WHERE user_id = '" . $user_id . "'";
				$this->db->sql_query($sql);

				return $user_id;
			}
			else
			{
				$response = array(
					'status' => 403,
					'data' => array(
						'error' => 'User has no permission to use the API',
						'valid' => false,
					),
				);
				return $response;
			}
		}
		else
		{
			$userdata = $this->auth->obtain_user_data(ANONYMOUS);
			$this->auth->acl($userdata);

			if ($this->auth->acl_get('u_api'))
			{
				if ($forum_id != 0)
				{
					if (!$this->auth->acl_get('f_read', $forum_id))
					{
						$response = array(
							'status' => 403,
							'data' => array(
								'error' => 'User has no permission to see this forum.',
								'valid' => false,
							),
						);
						return $response;
					}
				}
				return ANONYMOUS;
			}
			else
			{
				$response = array(
					'status' => 403,
					'data' => array(
						'error' => 'User has no permission to use the API',
						'valid' => false,
					),
				);
				return $response;

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
