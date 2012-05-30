<?php
/**
*
* @package auth
* @copyright (c) 2012 phpBB Group
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
* This class handles the selection of auth method which
*
* @package auth
*/
class phpbb_auth_manager {

	public function __construct() {

	}

	public function auth_method_chooser($method, $params = null) {
		switch($method)
		{
			case 'traditional':
				return $this->auth_method_traditional();
			case 'OpenID':
				return $this->auth_method_OpenID($params['id']);
			case 'facebook_connect':
				return $this->auth_method_facebook_connect();
		}
	}

	public function auth_method_traditional() {

	}

	public function auth_method_OpenID($id) {
		global $request;
		$storage = new phpbb_auth_zend_storage();
		$consumer = new Zend\OpenId\Consumer\GenericConsumer($storage);
		$consumer->check($id, $request->server('PHP_SELF'), 'https://www.google.com/accounts/o8/id');
		if($consumer->getError())
		{
			die($consumer->getError());
		}
		else
		{
			return true;
		}
	}

	public function auth_method_facebook_connect() {
		return null;
	}
}
