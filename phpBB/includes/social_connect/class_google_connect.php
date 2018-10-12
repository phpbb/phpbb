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

// NOT USED!
//define('OAUTH2_PATH', PHPBB_ROOT_PATH . "includes/social_connect/oauth2/src/OAuth2/");

class GoogleConnect extends SocialConnect
{
	private $client;
	const AUTH_ENDPOINT = 'https://accounts.google.com/o/oauth2/auth';
	const TOKEN_ENDPOINT = 'https://www.googleapis.com/oauth2/v4/token';
	private $scope = array('email', 'profile');

	public function __construct($network_name)
	{
		global $config;

		parent::__construct($network_name);

		include_once(IP_ROOT_PATH . "includes/social_connect/google/autoload.php");

		$app_id = $config['google_app_id'];
		$app_secret = $config['google_app_secret'];

		$this->client = new Google_Client();
		$this->client->setClientId($app_id);
		$this->client->setClientSecret($app_secret);
		$this->client->setScopes($this->scope);
	}

	public function do_login($redirect, $force_retry = false)
	{
		global $config;

		$code = request_get_var('code', '');
		$this->client->setRedirectUri($this->get_redirect_url('', true));

		if ($code && !$force_retry)
		{
			try
			{
				$this->client->authenticate($code);
				$data = $this->client->verifyIdToken()->getAttributes();
				$_SESSION['google_access_token'] = $this->client->getAccessToken();
				unset($_SESSION['login_social_network']);

				return $this->retrieve_basic_user_data($data['payload']['sub']);
			}
			catch (Exception $e)
			{
				message_die(GENERAL_ERROR, $e->getMessage());
			}
		}
		else
		{
			// TODO store the redirect as well
			$_SESSION['login_social_network'] = 'google';
			$login_url = $this->client->createAuthUrl();
			header('Location: ' . $login_url);
			exit;
		}
	}

	private function retrieve_basic_user_data($user_google_id)
	{
		global $db;

		$sql = "SELECT user_id, user_level
			FROM " . USERS_TABLE . "
			WHERE user_google_id = '" . $db->sql_escape($user_google_id) . "'
			LIMIT 1";
		$result = $db->sql_query($sql);
		if ($db->sql_numrows($result) > 0)
		{
			// User is registered
			return $db->sql_fetchrow($result);
		}
		else
		{
			// User is not registered
			return null;
		}
	}

	public function get_user_data()
	{
		$google_data = array();

		$code = request_get_var('code', '');
		try
		{
			if (isset($_SESSION['google_access_token']))
			{
				$this->client->setAccessToken($_SESSION['google_access_token']);
			}
			else
			{
				$this->do_login('');
			}
			$google_user_id = $this->client->verifyIdToken()->getAttributes()['payload']['sub'];

			$google_oauth = new Google_Service_Oauth2($this->client);
			$google_data = $google_oauth->userinfo->get();
		}
		catch (Exception $e)
		{
			unset($_SESSION['google_access_token']);
			$this->do_login('');
			return $this->get_user_data();
		}

		$google_user_gender = 0;
		if (!empty($google_data['gender']))
		{
			if (($google_data['gender'] == 'male') || ($google_data['gender'] == 1))
			{
				$google_user_gender = 1;
			}
			elseif (($google_data['gender'] == 'female') || ($google_data['gender'] == 2))
			{
				$google_user_gender = 2;
			}
		}

		$email = explode('@', $google_data['email']);
		$username = empty($google_data['name']) ? $email[0] . $google_data['sub'] : $google_data['name'];

		$user_data = array(
			'username' => $username,
			'email' => $google_data['email'],
			'gender' => $google_user_gender,
			'user_google_id' => $google_user_id,
			'u_profile_photo' => empty($google_data['picture']) ? '' : $google_data['picture'],
			'user_real_name' => empty($google_data['name']) ? 'Profile #' . $google_data['sub'] : $google_data['name'],
		);

		return $user_data;
	}

	public function shim_register_request()
	{
		// $mode uses request_var()
		$_REQUEST['mode'] = 'register';

		// request_get_var() uses both $_GET and $_REQUEST
		$_REQUEST['social_network'] = $_GET['social_network'] = 'google';

		$_POST['agreed'] = $_POST['privacy'] = 'true';
	}
}

?>