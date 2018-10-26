<?php
/**
*
* @package Icy Phoenix
* @version $Id$
* @copyright (c) 2008 Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

class FacebookConnect extends SocialConnect
{
	private $client;
	private $scope = 'email,user_website,user_location,user_birthday';

	public function __construct($network_name)
	{
		global $phpbb_root_path, $board_config, $phpEx;

		parent::__construct($network_name);
		
		require_once($phpbb_root_path . "includes/auth/facebook/Facebook." . $phpEx);
		//require_once($phpbb_root_path . "includes/social_connect/Facebook/autoload.php");
		
		$app_id = $board_config['facebook_app_id'];
		$app_secret = $board_config['facebook_app_secret']; 
		
		if (!isset($board_config['facebook_app_id']))
		{
			throw new Exception('Required "facebook_app_id" key not set in config and could not find fallback environment variable "' . '"');
		}
		elseif (!$board_config['facebook_app_id'])
		{
			throw new Exception('Required "facebook_app_id" key not supplied in config and could not find fallback environment variable "' . $board_config['facebook_app_id'] . '"');
		}
		
		$facebook_config = array(
			'appId'  => $board_config['facebook_app_id'], //old id
			'app_id'  => $board_config['facebook_app_id'], //new id			
			'app_secret' => $board_config['facebook_app_secret'], 
			'default_graph_version' => 'v2.12',
		);	
		$this->client = new Facebook($facebook_config);
		//$this->client = new Facebook\Facebook($facebook_config);
	}

	public function do_login($redirect, $force_retry = false)
	{
		global $board_config, $user;

		// If user is already logged in and granted our application, we don't need to redirect him to facebook
		$user_fb_id = $this->client->getUser();
		if (!empty($user_fb_id))
		{
			return $this->retrieve_user_basic_data($user_fb_id);
		}

		$confirm = request_get_var('confirm', 0);
		if ($confirm != 1 || $force_retry)
		{
			// Build the social network return url
			$current_page = extract_current_page(IP_ROOT_PATH);
			$return_url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://';
			$return_url .= extract_current_hostname() . $current_page['script_path'] . $current_page['page'];
			$return_url .= (strpos($return_url, '?') ? '&' : '?') . 'redirect=' . $redirect . '&confirm=1';
			$return_url .= (!empty($_GET['admin'])) ? '&admin=1' : '';

			$params = array(
				'scope' => $this->scope,
				'redirect_uri' => $return_url,
			);

			$login_url = $this->client->getLoginUrl($params);

			header('Location: ' . $login_url);
			exit;
		}
		else
		{
			$token = '';
			$user_fb_data = array();

			try
			{
				$token = $this->client->getAccessToken();
				$user_fb_data = $this->client->api('/me');
			}
			catch (OAuthException $e)
			{
				// Retry on failure
				return $this->do_login(true);
			}

			return $this->retrieve_user_id($user_fb_data['id']);
		}
	}

	private function retrieve_user_basic_data($user_fb_id)
	{
		global $db;

		$sql = "SELECT user_id, user_level
			FROM " . USERS_TABLE . "
			WHERE user_facebook_id = '" . $db->sql_escape($user_fb_id) . "'
			LIMIT 1";
		$result = $db->sql_query($sql);
		if ($db->sql_numrows($result) > 0)
		{
			// User is registered
			$user_data = $db->sql_fetchrow($result);
			return $user_data;
		}
		else
		{
			// User is not registered
			return null;
		}
	}

	public function get_user_data()
	{
		$token = '';
		$user_fb_data = array();

		try
		{
			$token = $this->client->getAccessToken();
			$user_fb_data = $this->client->api('/me');
		}
		catch (Exception $e)
		{
			// If user isn't logged in on facebook, then log him in and retry!
			//$this->do_login(true);die();
			$this->do_login(true);
			return $this->get_user_data();
		}

		$username = empty($user_fb_data['username']) ? $user_fb_data['id'] : $user_fb_data['username'];

		$gender = 0;
		if (!empty($user_fb_data['gender']))
		{
			$gender = ($user_fb_data['gender'] == 'male') ? 1 : 2;
		}

		$birthday = '';
		$birthday_y = '';
		$birthday_m = '';
		$birthday_d = '';
		if (!empty($user_fb_data['birthday']))
		{
			if (!function_exists('mkrealdate'))
			{
				include($phpbb_root_path . 'includes/functions_profile.' . PHP_EXT);
			}

			// FB birthday is in MM/DD/YYYY format
			$birthday_parts = explode('/', $user_fb_data['birthday']);
			$birthday_y = $birthday_parts[2];
			$birthday_m = $birthday_parts[0];
			$birthday_d = $birthday_parts[1];
			$birthday = mkrealdate($birthday_parts[1], $birthday_parts[0], $birthday_parts[2]);
		}

		// Convert social network data to Icy Phoenix data
		$user_data = array(
			'username' => $username,
			'email' => empty($user_fb_data['email']) ? '' : $user_fb_data['email'],
			'email_confirm' => empty($user_fb_data['email']) ? '' : $user_fb_data['email'],
			'user_website' => empty($user_fb_data['website']) ? '' : $user_fb_data['website'],
			'gender' => $gender,
			'birthday' => $birthday,
			/*
			'birthday_y' => $birthday_y,
			'birthday_m' => $birthday_m,
			'birthday_d' => $birthday_d,
			*/
			'user_timezone' => empty($user_fb_data['timezone']) ? '' : $user_fb_data['timezone'],
			'user_facebook' => $username,
			'user_facebook_id' => $user_fb_data['id'],

			'u_profile_photo' => 'https://graph.facebook.com/' . $username . '/picture',
			'user_real_name' => empty($user_fb_data['name']) ? '' : $user_fb_data['name'],
			'u_profile_link' => empty($user_fb_data['link']) ? '' : $user_fb_data['link'],
		);

		return $user_data;
	}
}

?>