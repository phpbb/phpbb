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

	public function generate_token()
	{
		$chars = '0123456789abcdefghijklmnopqrstyvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$token = '';
		for($i = 0; $i < 32; $i++)
		{
			$chars = str_shuffle($chars);
			$token .= substr($chars, mt_rand(0, 61), 1);
		}

		return $token;
	}

	public function allow($token, $sign_token, $user_id, $name)
	{
		$sql = 'INSERT INTO ' . API_TOKENS_TABLE
			. " (user_id, name, token, sign_token) VALUES (' " . $user_id
			. "', '" . $this->db->sql_escape($name)
			. "', '" . $this->db->sql_escape($token)
			. "', '" . $this->db->sql_escape($sign_token) . "')";

		$this->db->sql_query($sql);
	}

	public function verify($token, $timestamp, $hash)
	{
		$sql = 'SELECT sign_token
				FROM ' . API_TOKENS_TABLE
			. " WHERE token = '" . $this->db->sql_escape($token) . "'";

		$result = $this->db->sql_query($sql);

		$row = $this->db->sql_fetchrow($result);
		$sign_token = $row['sign_token'];

		if (empty($sign_token))
		{
			return false;
		}

		$test_hash = hash_hmac('sha256', 'api/auth/verify/' . $token . '/' . $timestamp, $sign_token);

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
