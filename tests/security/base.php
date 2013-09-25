<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

abstract class phpbb_security_test_base extends phpbb_test_case
{
	/**
	* Set up the required user object and server variables for the suites
	*/
	protected function setUp()
	{
		global $user, $phpbb_root_path, $request;

		// Put this into a global function being run by every test to init a proper user session
		$server['HTTP_HOST']		= 'localhost';
		$server['SERVER_NAME']		= 'localhost';
		$server['SERVER_ADDR']		= '127.0.0.1';
		$server['SERVER_PORT']		= 80;
		$server['REMOTE_ADDR']		= '127.0.0.1';
		$server['QUERY_STRING']	= '';
		$server['REQUEST_URI']		= '/tests/';
		$server['SCRIPT_NAME']		= '/tests/index.php';
		$server['PHP_SELF']		= '/tests/index.php';
		$server['HTTP_USER_AGENT']	= 'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.8.1.14) Gecko/20080404 Firefox/2.0.0.14';
		$server['HTTP_ACCEPT_LANGUAGE']	= 'de-de,de;q=0.8,en-us;q=0.5,en;q=0.3';

/*
		[HTTP_ACCEPT_ENCODING] => gzip,deflate
		[HTTP_ACCEPT_CHARSET] => ISO-8859-1,utf-8;q=0.7,*;q=0.7
		DOCUMENT_ROOT] => /var/www/
		[SCRIPT_FILENAME] => /var/www/tests/index.php
*/

		$request = new phpbb_mock_request(array(), array(), array(), $server);

		// Set no user and trick a bit to circumvent errors
		$user = new \phpbb\user();
		$user->lang = true;
		$user->browser				= $server['HTTP_USER_AGENT'];
		$user->referer				= '';
		$user->forwarded_for		= '';
		$user->host					= $server['HTTP_HOST'];
		$user->page = \phpbb\session::extract_current_page($phpbb_root_path);
	}

	protected function tearDown()
	{
		global $user;
		$user = NULL;
	}
}
