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

namespace phpbb\api\repository;

use phpbb\config\config;
use phpbb\db\driver\driver;
use phpbb\api\exception\api_exception;
use phpbb\api\exception\duplicate_name_exception;
use phpbb\api\exception\invalid_key_exception;
use phpbb\api\exception\invalid_request_exception;
use phpbb\api\exception\no_permission_exception;
use phpbb\api\exception\not_authed_exception;
use phpbb\request\request;

/**
 * This repository handles authentication
 * @package phpBB3
 */
class auth
{

	/**
	 * phpBB configuration
	 * @var config
	 */
	protected $phpbb_config;

	/** @var driver */
	protected $db;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/**
	 * Request object
	 * @var request
	 */
	protected $request;

	/**
	 * Constructor
	 *
	 * @param config 			$config
	 * @param driver 			$db
	 * @param \phpbb\auth\auth 	$auth
	 * @param request 			$request
	 */
	function __construct(config $config, $db, \phpbb\auth\auth $auth, request $request)
	{
		$this->phpbb_config = $config;
		$this->db = $db;
		$this->auth = $auth;
		$this->request = $request;
	}

	/**
	 * Don't let the user select auth and sign keys to protect user safety from malicious developers/others
	 *
	 * @return string An exchange key, later to be used to authorize the application and exchange for the real keys
	 */
	public function generate_keys()
	{
		$auth_key = unique_id();
		$sign_key = unique_id();
		$exchange_key = unique_id();

		$sql = 'INSERT INTO ' . API_EXCHANGE_KEYS_TABLE
			. " (timestamp, exchange_key, auth_key, sign_key, user_id, name) VALUES ('" . time() . "', '$exchange_key','$auth_key',
			'$sign_key', 0, '')";

		$this->db->sql_query($sql);
		$this->db->sql_freeresult();

		return $exchange_key;
	}

	/**
	 * Authorizes an application to the API
	 *
	 * @param string $exchange_key 	The exchange key provided by generate_keys(), should be 16 chars long
	 * @param int 	 $user_id 		The user_id of the currently logged in user (the one authorizing the application)
	 * @param string $name			The name of the application, given by the user
	 *
	 * @throws duplicate_name_exception	If the user already authorized an application by this name
	 * @throws invalid_key_exception	If the key is wrong length or otherwise invalid
	 */
	public function authorize($exchange_key, $user_id, $name)
	{
		if (strlen($exchange_key) !== 16)
		{
			throw new invalid_key_exception('Exchange key too short or too long.', 400);
		}

		$exchange_key = $this->db->sql_escape($exchange_key);
		$name = $this->db->sql_escape($name);

		$sql = 'SELECT *
			FROM ' . API_EXCHANGE_KEYS_TABLE . "
			WHERE exchange_key =  '$exchange_key'";

		$result = $this->db->sql_query($sql);

		if ($result->num_rows === 0)
		{
			throw new invalid_key_exception('Exchange key not found.', 400);
		}

		$this->db->sql_freeresult($result);

		$sql = 'SELECT *
			FROM ' . API_KEYS_TABLE . "
			WHERE user_id =  $user_id
				AND name = '$name'";

		$result = $this->db->sql_query($sql);
		$this->db->sql_freeresult($result);

		if ($result->num_rows !== 0) {
			throw new duplicate_name_exception('The user already have a key with this name.', 400);
		}

		$sql = 'UPDATE ' . API_EXCHANGE_KEYS_TABLE . "
			SET user_id =  $user_id,
			name = '$name'
			WHERE exchange_key = '$exchange_key'";

		$this->db->sql_query($sql);
		$this->db->sql_freeresult();
	}

	/**
	 * Exchanges an exchange key for the auth key and sign key
	 *
	 * @param string $exchange_key The exchange key provided by generate_keys()
	 *
	 * @throws invalid_key_exception If the exchange key is too long/short or does not exist
	 * @throws no_permission_exception If the user hasn't authorized this application
	 *
	 * @return array An array with an auth_key and an sign_key
	 */
	public function exchange_key($exchange_key)
	{
		if (strlen($exchange_key) !== 16)
		{
			throw new invalid_key_exception('Exchange key too short or too long.', 400);
		}

		$exchange_key = $this->db->sql_escape($exchange_key);

		$sql = 'SELECT *
			FROM ' . API_EXCHANGE_KEYS_TABLE . "
			WHERE exchange_key =  '$exchange_key'";

		$result = $this->db->sql_query($sql);

		if ($result->num_rows === 0)
		{
			throw new invalid_key_exception('Exchange key not found.', 400);
		}

		$row = $this->db->sql_fetchrow($result);

		$auth_key = $row['auth_key'];
		$sign_key = $row['sign_key'];
		$user_id = (int) $row['user_id'];
		$name = $row['name'];

		$this->db->sql_freeresult($result);

		// User has not allowed this application yet!!
		if ($user_id == 0)
		{
			throw new no_permission_exception('Application not allowed.', 400);
		}

		$sql = 'INSERT INTO ' . API_KEYS_TABLE . " (user_id, name, auth_key, sign_key, serial)
			VALUES ($user_id,
			'$name',
			'$auth_key',
			'$sign_key',
			0)";

		$this->db->sql_query($sql);
		$this->db->sql_freeresult();

		$sql = 'DELETE FROM ' . API_EXCHANGE_KEYS_TABLE
			. " WHERE exchange_key = '$exchange_key'";

		$this->db->sql_query($sql);
		$this->db->sql_freeresult();

		return array(
			'auth_key' => $auth_key,
			'sign_key' => $sign_key,
		);
	}

	/**
	 * Authenticates a request
	 *
	 * TODO: Refactor this function into 2 functions, one for base authentication and one for forum authentication
	 * (checking if user has access to a given forum).
	 *
	 * @param string $request
	 * @param string $auth_key
	 * @param int 	 $serial
	 * @param string $hash
	 *
	 * @throws \phpbb\api\exception\invalid_request_exception
	 * @throws \phpbb\api\exception\no_permission_exception
	 * @throws \phpbb\api\exception\not_authed_exception
	 * @throws \phpbb\api\exception\api_exception
	 *
	 * @return array|int
	 */
	public function authenticate($request, $auth_key, $serial, $hash)
	{

		if (!$this->phpbb_config['allow_api'])
		{
			throw new api_exception('The API is not enabled on this board', 500);
		}

		if ($auth_key != ANONYMOUS)
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
				// @TODO: This probably needs to be changed before release
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
				$sql = 'UPDATE ' . API_KEYS_TABLE
					. " SET serial = $serial
					  WHERE user_id = $user_id";
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
				return ANONYMOUS;
			}
			else
			{
				throw new no_permission_exception('User has no permission to use the API', 403);
			}
		}
	}
}
