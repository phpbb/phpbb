<?php
/**
*
* @package Icy Phoenix
* @version $Id$
* @copyright (c) 2008 Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *		 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

require_once(PHPBB_ROOT_PATH . "includes/social_connect/facebook/Base_Facebook." . PHP_EXT);

/**
 * Extends the BaseFacebook class with the intent of using
 * PHP sessions to store user ids and access tokens.
 */
class Facebook extends BaseFacebook
{
	const FBSS_COOKIE_NAME = 'fbss';

	// We can set this to a high number because the main session
	// expiration will trump this.
	const FBSS_COOKIE_EXPIRE = 31556926; // 1 year

	// Stores the shared session ID if one is set.
	protected $sharedSessionID;

	/**
	 * Identical to the parent constructor, except that
	 * we start a PHP session to store the user ID and
	 * access token if during the course of execution
	 * we discover them.
	 *
	 * @param Array $board_config the application configuration. Additionally
	 * accepts "sharedSession" as a boolean to turn on a secondary
	 * cookie for environments with a shared session (that is, your app
	 * shares the domain with other apps).
	 * @see BaseFacebook::__construct in facebook.php
	 */
	public function __construct($facebook_config) 
	{
		if (!session_id()) 
		{
			@session_start();
		}
			
		if (!is_array($facebook_config)) 
		{
	 		global $board_config;
			$facebook_config = array(
				'appId'  => $board_config['facebook_app_id'], //old id
				'app_id'  => $board_config['facebook_app_id'], //new id				
				'app_secret' => $board_config['facebook_app_secret'], 
				'default_graph_version' => 'v2.12',
			);	          
        }
	
		require_once(PHPBB_ROOT_PATH . "includes/social_connect/facebook/FacebookSDKException." . PHP_EXT);
		
		if (!isset($facebook_config['app_id'])) 
		{
            throw new FacebookSDKException('Required "app_id" key not set in config and could not find fallback environment variable "' . '"');
        }
		elseif (!$facebook_config['app_id'])
		{
            throw new FacebookSDKException('Required "app_id" key not supplied in config and could not find fallback environment variable "' . $facebook_config['app_id'] . '"');
        }		
		parent::__construct($facebook_config);
		if (!empty($facebook_config['sharedSession'])) 
		{
			$this->initSharedSession();
		}
	}

	protected static $kSupportedKeys =
		array('state', 'code', 'access_token', 'user_id');

	protected function initSharedSession() 
	{
		$cookie_name = $this->getSharedSessionCookieName();
		if (isset($_COOKIE[$cookie_name])) 
		{
			$data = $this->parseSignedRequest($_COOKIE[$cookie_name]);
			if ($data && !empty($data['domain']) &&
					self::isAllowedDomain($this->getHttpHost(), $data['domain'])) {
				// good case
				$this->sharedSessionID = $data['id'];
				return;
			}
			// ignoring potentially unreachable data
		}
		// evil/corrupt/missing case
		$base_domain = $this->getBaseDomain();
		$this->sharedSessionID = md5(uniqid(mt_rand(), true));
		$cookie_value = $this->makeSignedRequest(
			array(
				'domain' => $base_domain,
				'id' => $this->sharedSessionID,
			)
		);
		$_COOKIE[$cookie_name] = $cookie_value;
		if (!headers_sent())
		{
			$expire = time() + self::FBSS_COOKIE_EXPIRE;
			setcookie($cookie_name, $cookie_value, $expire, '/', '.'.$base_domain);
		} 
		else 
		{
			// @codeCoverageIgnoreStart
			self::errorLog(
				'Shared session ID cookie could not be set! You must ensure you '.
				'create the Facebook instance before headers have been sent. This '.
				'will cause authentication issues after the first request.'
			);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Provides the implementations of the inherited abstract
	 * methods.	The implementation uses PHP sessions to maintain
	 * a store for authorization codes, user ids, CSRF states, and
	 * access tokens.
	 */
	protected function setPersistentData($key, $value) {
		if (!in_array($key, self::$kSupportedKeys)) {
			self::errorLog('Unsupported key passed to setPersistentData.');
			return;
		}

		$session_var_name = $this->constructSessionVariableName($key);
		$_SESSION[$session_var_name] = $value;
	}

	protected function getPersistentData($key, $default = false) {
		if (!in_array($key, self::$kSupportedKeys)) {
			self::errorLog('Unsupported key passed to getPersistentData.');
			return $default;
		}

		$session_var_name = $this->constructSessionVariableName($key);
		return isset($_SESSION[$session_var_name]) ?
			$_SESSION[$session_var_name] : $default;
	}

	protected function clearPersistentData($key) {
		if (!in_array($key, self::$kSupportedKeys)) {
			self::errorLog('Unsupported key passed to clearPersistentData.');
			return;
		}

		$session_var_name = $this->constructSessionVariableName($key);
		unset($_SESSION[$session_var_name]);
	}

	protected function clearAllPersistentData() {
		foreach (self::$kSupportedKeys as $key) {
			$this->clearPersistentData($key);
		}
		if ($this->sharedSessionID) {
			$this->deleteSharedSessionCookie();
		}
	}

	protected function deleteSharedSessionCookie() {
		$cookie_name = $this->getSharedSessionCookieName();
		unset($_COOKIE[$cookie_name]);
		$base_domain = $this->getBaseDomain();
		setcookie($cookie_name, '', 1, '/', '.'.$base_domain);
	}

	protected function getSharedSessionCookieName() {
		return self::FBSS_COOKIE_NAME . '_' . $this->getAppId();
	}

	protected function constructSessionVariableName($key) {
		$parts = array('fb', $this->getAppId(), $key);
		if ($this->sharedSessionID) {
			array_unshift($parts, $this->sharedSessionID);
		}
		return implode('_', $parts);
	}
}

?>