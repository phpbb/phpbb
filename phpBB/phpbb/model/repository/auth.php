<?php
/**
 *
 * @package api
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbb\model\repository;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

use phpbb\model\exception\api_exception;
use phpbb\model\exception\invalid_request_exception;
use phpbb\model\exception\no_permission_exception;
use phpbb\model\exception\not_authed_exception;

/**
 * This repository handles authentication
 * @package phpBB3
 */
class auth
{

	/**
	 * phpBB configuration
	 * @var \phpbb\config\config
	 */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/**
	 * Request object
	 * @var \phpbb\request\request
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver $db
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\request\request $request
	 */
	function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver $db, \phpbb\auth\auth $auth, \phpbb\request\request $request)
	{
		$this->config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->request = $request;
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
	 * @param int $forum_id
	 * @param null $request
	 * @param null $auth_key
	 * @param null $serial
	 * @param null $hash
	 * @throws \phpbb\model\exception\invalid_request_exception
	 * @throws \phpbb\model\exception\no_permission_exception
	 * @throws \phpbb\model\exception\not_authed_exception
	 * @throws \phpbb\model\exception\api_exception
	 * @internal param String $request The request url, for example api/forums/2....
	 * @internal param string $auth_key
	 * @internal param $serial
	 * @internal param $hash
	 * @return array|int
	 */
	public function auth($forum_id = 0, $request = null, $auth_key = null, $serial = null, $hash = null)
	{
		$request = (isset($request)) ? $request : $this->request->server("PATH_INFO");
		$auth_key = (isset($auth_key)) ? $auth_key : $this->request->variable('auth_key', 'guest');
		$serial = (isset($serial)) ? $serial : $this->request->variable('serial', -1);
		$hash = (isset($hash)) ? $hash : $this->request->variable('hash', '');

		if (!$this->config['allow_api'])
		{
			throw new api_exception('The API is not enabled on this board', 500);
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
				throw new not_authed_exception('The user has not authenticated this application', 401);
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
				throw new invalid_request_exception('Invalid hash', 400);
			}

			if ($serial <= $dbserial)
			{
				throw new invalid_request_exception('Invalid serial', 400);
			}

			$userdata = $this->auth->obtain_user_data($user_id);
			$this->auth->acl($userdata);

			if ($this->auth->acl_get('u_api'))
			{
				if ($forum_id != 0)
				{
					if (!$this->auth->acl_get('f_read', $forum_id))
					{
						throw new no_permission_exception('User has no permission to read this forum', 403);
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
				throw new no_permission_exception('User has no permission to use the API', 403);
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
						throw new no_permission_exception('User has no permission to read this forum', 403);
					}
				}
				return ANONYMOUS;
			}
			else
			{
				throw new no_permission_exception('User has no permission to use the API', 403);

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
