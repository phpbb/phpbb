<?php
/**
*
* @package TwitterOAuth
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

class TwitterConnect extends SocialConnect
{
    private $_oauth = NULL;
	private $client;
	private $scope = array('email', 'profile');
	
    function __construct($network_name) 
	{
		global $phpbb_root_path, $board_config, $phpEx;
		
		parent::__construct($network_name);
		
		require_once($phpbb_root_path . "includes/auth/twitter/SignatureMethod." . $phpEx);
		require_once($phpbb_root_path . "includes/auth/twitter/HmacSha1." . $phpEx);
		require_once($phpbb_root_path . "includes/auth/twitter/Token." . $phpEx);
		require_once($phpbb_root_path . "includes/auth/twitter/Consumer." . $phpEx);
		require_once($phpbb_root_path . "includes/auth/twitter/Util." . $phpEx);
		require_once($phpbb_root_path . "includes/auth/twitter/Request." . $phpEx);
		require_once($phpbb_root_path . "includes/auth/twitter/TwitterOAuthException." . $phpEx);
		include_once($phpbb_root_path . "includes/auth/twitter/TwitterOAuth." . $phpEx);

		
		
		$app_id = $board_config['twitter_app_id'];
		$app_secret = $board_config['twitter_app_secret']; 
		$app_access_token= $board_config['twitter_access_token'];
		$app_token_secret = $board_config['twitter_access_token_secret'];

		/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
		$settings = array(
		    'oauth_access_token' => $app_access_token,
		    'oauth_access_token_secret' => $app_token_secret,
		    'consumer_key' => $app_id,
		    'consumer_secret' => $app_secret
		);		
		
		error_reporting(E_ALL & ~E_NOTICE);
		
		/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
		$url = 'https://api.twitter.com/1.1/blocks/create.json';
		$requestMethod = 'POST';
		
		//$this->client = new TwitterOAuth($app_id, $app_secret, $app_access_token, $app_token_secret);
		$this->client = new TwitterOAuth($app_id, $app_secret);
		$this->client->setOauthToken($app_access_token, $app_token_secret);
        //$this->assertObjectHasAttribute('consumer', $this->client);
        //$this->assertObjectHasAttribute('token', $this->client);
		$this->client->get('friendships/show', array('target_screen_name' => 'twitterapi'));

    }

	public function do_login($redirect, $force_retry = false)
	{
		global $board_config, $user;

		$code = request_get_var('code', '');
		$this->client->setRedirectUri($this->get_redirect_url('', true));

		if ($code && !$force_retry)
		{
			try
			{
				$this->client->authenticate($code);
				$data = $this->client->verifyIdToken()->getAttributes();
				$_SESSION['twitter_access_token'] = $this->client->getAccessToken();
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
			$_SESSION['login_social_network'] = 'twitter';
			$login_url = $this->client->createAuthUrl();
			header('Location: ' . $login_url);
			exit;
		}
	}

	private function retrieve_basic_user_data($user_twitter_id)
	{
		global $db, $user;
		if (!isset($user->data['user_twitter_id']))
		{
			print('<p><span style="color: red;"'.'>No user_twitter_id ...</span></p><i><p>Refreshing the users table!</p></i>');
			$this->db_tools->sql_column_add(USERS_TABLE, 'user_twitter_id ', array('column_type_sql' => 'varchar(255)', 'null' => 'NOT NULL', 'default' => '', 'after' => 'username'), false);
		}
		$sql = "SELECT user_id, user_level
			FROM " . USERS_TABLE . "
			WHERE user_twitter_id = '" . $db->sql_escape($user_twitter_id ) . "'
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
		$twitter_data = array();

		$code = request_get_var('code', '');
		try
		{
			if (isset($_SESSION['twitter_access_token']))
			{
				global $board_config;
				
				$app_id = $board_config['twitter_app_id'];
				$app_secret = $board_config['twitter_app_secret']; 
				
				/* Get user access tokens out of the session. */
				$access_token = $_SESSION['access_token'];

				/* Create a TwitterOauth object with consumer/user tokens. */
				$connection = new TwitterOAuth($app_id, $app_secret, $access_token['oauth_token'], $access_token['oauth_token_secret']); 
				$params = isset($_REQUEST['oauth_verifier']) ? $_REQUEST['oauth_verifier'] : array('include_email' => 'true', 'include_entities' => 'true', 'skip_status' => 'true');
				$twitter->oauth("oauth/access_token", array("oauth_verifier" => $params));
			}
			else
			{
				$this->do_login('');
			}
			
			/* If method is set change API call made. Test is called by default. */
			$twitter_data = $this->client->get('account/verify_credentials', ['tweet_mode' => 'extended', 'include_entities' => 'true']);
		}
		catch (Exception $e)
		{
			unset($_SESSION['twitter_access_token']);
			$this->do_login('');
			return $this->get_user_data();
		}

		$twitter_user_gender = 0;
		if (!empty($twitter_data['gender']))
		{
			if (($twitter_data['gender'] == 'male') || ($twitter_data['gender'] == 1))
			{
				$twitter_user_gender = 1;
			}
			elseif (($twitter_data['gender'] == 'female') || ($twitter_data['gender'] == 2))
			{
				$twitter_user_gender = 2;
			}
		}

		$email = explode('@', $twitter_data['email']);
		$username = empty($twitter_data['name']) ? $email[0] . $twitter_data['sub'] : $twitter_data['name'];

		$user_data = array(
			'username' => $username,
			'email' => $twitter_data['email'],
			'gender' => $twitter_user_gender,
			'user_twitter_id' => $twitter_user_id,
			'u_profile_photo' => empty($twitter_data['picture']) ? '' : $twitter_data['picture'],
			'user_real_name' => empty($twitter_data['name']) ? 'Profile #' . $twitter_data['sub'] : $twitter_data['name'],
		);

		return $user_data;
	}

	public function shim_register_request()
	{
		// $mode uses request_var()
		$_REQUEST['mode'] = 'register';

		// request_get_var() uses both $_GET and $_REQUEST
		$_REQUEST['social_network'] = $_GET['social_network'] = 'twitter';

		$_POST['agreed'] = $_POST['privacy'] = 'true';
	}

    function __call($method, $args) 
	{
		if (method_exists($this, $method)) 
		{
			return call_user_func_array(array($this, $method), $args);
		}
		return call_user_func_array(array($this->client, $method), $args);
    }

    function logged_in() 
	{
		return $this->client->loggedIn();
    }

    function set_callback($url) 
	{
		$this->client->setCallback($url);
    }

    function login() 
	{
		return $this->client->login();
    }

    function logout()
	{
		return $this->client->logout();
    }

    function get_tokens()
	{
		$tokens = array(
		    'oauth_token' => $this->client->getAccessKey(),
		    'oauth_token_secret' => $this->client->getAccessSecret()
		);
		return $tokens;
    }

    function set_tokens($tokens)
	{
		return $this->client->setAccessTokens($tokens);
    }

}

class tweetException extends Exception 
{
    function __construct($string)
	{
		parent::__construct($string);
    }

    public function __toString() 
	{
		return "exception '" . __CLASS__ . "' with message '" . $this->getMessage() . "' in " . $this->getFile() . ":" . $this->getLine() . "\nStack trace:\n" . $this->getTraceAsString();
    }

}

class tweetConnection 
{
    // Allow multi-threading.

    private $_mch = NULL;
    private $_properties = array();

    function __construct() 
	{
		$this->_mch = curl_multi_init();

		$this->_properties = array(
		    'code' => CURLINFO_HTTP_CODE,
		    'time' => CURLINFO_TOTAL_TIME,
		    'length' => CURLINFO_CONTENT_LENGTH_DOWNLOAD,
		    'type' => CURLINFO_CONTENT_TYPE
		);
    }

    private function _initConnection($url) 
	{
		$this->_ch = curl_init($url);
		curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, TRUE);
    }

    public function get($url, $params)
	{
		if (count($params['request']) > 0)
		{
		    $url .= '?';

		    foreach ($params['request'] as $k => $v) 
			{
				$url .= "{$k}={$v}&";
		    }

		    $url = substr($url, 0, -1);
		}

		$this->_initConnection($url);
		$response = $this->_addCurl($url, $params);

		return $response;
    }

    public function post($url, $params)
	{
		// Todo
		$post = '';

		foreach ($params['request'] as $k => $v)
		{
		    $post .= "{$k}={$v}&";
		}

		$post = substr($post, 0, -1);

		$this->_initConnection($url, $params);
		curl_setopt($this->_ch, CURLOPT_POST, 1);
		curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $post);

		$response = $this->_addCurl($url, $params);

		return $response;
    }

    private function _addOauthHeaders(&$ch, $url, $oauthHeaders)
	{
		$_h = array('Expect:');
		$urlParts = parse_url($url);
		$oauth = 'Authorization: OAuth realm="' . $urlParts['path'] . '",';

		foreach ($oauthHeaders as $name => $value) 
		{
		    $oauth .= "{$name}=\"{$value}\",";
		}

		$_h[] = substr($oauth, 0, -1);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $_h);
    }

    private function _addCurl($url, $params = array())
	{
		if (!empty($params['oauth'])) 
		{
		    $this->_addOauthHeaders($this->_ch, $url, $params['oauth']);
		}

		$ch = $this->_ch;

		$key = (string) $ch;
		$this->_requests[$key] = $ch;

		$response = curl_multi_add_handle($this->_mch, $ch);

		if ($response === CURLM_OK || $response === CURLM_CALL_MULTI_PERFORM)
		{
		    do 
			{
				$mch = curl_multi_exec($this->_mch, $active);
		    } while ($mch === CURLM_CALL_MULTI_PERFORM);

		    return $this->_getResponse($key);
		} 
		else 
		{
		    return $response;
		}
    }

    private function _getResponse($key = NULL) 
	{
		if ($key == NULL)
		    return FALSE;

		if (isset($this->_responses[$key])) {
		    return $this->_responses[$key];
		}

		$running = NULL;

		do {
			    $response = curl_multi_exec($this->_mch, $running_curl);

			    if ($running !== NULL && $running_curl != $running) 
				{
					$this->_setResponse($key);

					if (isset($this->_responses[$key])) 
					{
						$response = new tweetResponseOauth((object) $this->_responses[$key]);

						if (isset($response->__resp->code) && $response->__resp->code !== 200 && isset($response->__resp->data))
						{
							throw new tweetException($response->__resp->code . ' | Request Failed: ' . $response->__resp->data->request . ' - ' . $response->__resp->data->error);
						}

						return $response;
					}
			    }

				$running = $running_curl;
			} 
			while ($running_curl > 0);
	}

    private function _setResponse($key) 
	{
		while ($done = curl_multi_info_read($this->_mch))
		{
		    $key = (string) $done['handle'];
		    $this->_responses[$key]['data'] = curl_multi_getcontent($done['handle']);

		    foreach ($this->_properties as $curl_key => $value)
			{
				$this->_responses[$key][$curl_key] = curl_getinfo($done['handle'], $value);

				curl_multi_remove_handle($this->_mch, $done['handle']);
		    }
		}
    }

}
