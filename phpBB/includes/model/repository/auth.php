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

	/**
	 * Constructor
	 *
	 * @param phpbb_config $config
	 * @param phpbb_db_driver $db
	 */
	function __construct(phpbb_config $config, phpbb_db_driver $db)
	{
		$this->config = $config;
		$this->db = $db;
	}

	public function generate_key()
	{
		$chars = '0123456789abcdefghijklmnopqrstyvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$key = '';
		for($i = 0; $i < 32; $i++)
		{
			$chars = str_shuffle($chars);
			$key .= substr($chars, mt_rand(0, 61), 1);
		}

		return $key;
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

	public function verify($auth_key, $timestamp, $hash)
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

		$test_hash = hash_hmac('sha256', 'api/auth/verify/' . $auth_key . '/' . $timestamp, $sign_key);

		if ($hash == $test_hash)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
