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

/**
* OAuth authentication provider for phpBB3
*
* @package auth
*/
class phpbb_auth_provider_oauth extends phpbb_auth_provider_base
{
	/**
	* OAuth Authentication Constructor
	*
	* @param 	phpbb_db_driver 	$db
	* @param 	phpbb_config 		$config
	* @param 	phpbb_request 		$request
	* @param 	phpbb_user 			$user
	*/
	public function __construct(phpbb_db_driver $db, phpbb_config $config, phpbb_request $request, phpbb_user $user)
	{
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
	}

	/**
	* {@inheritdoc}
	*/
	public function login($username, $password)
	{

	}
}
