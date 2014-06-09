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

abstract class phpbb_security_test_base extends phpbb_test_case
{
	/**
	* Set up the required user object and server variables for the suites
	*/
	protected function setUp()
	{
		global $user, $phpbb_root_path, $phpEx, $request, $symfony_request, $phpbb_filesystem;

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
		$symfony_request = $this->getMock("\phpbb\symfony_request", array(), array(
			$request,
		));
		$symfony_request->expects($this->any())
			->method('getScriptName')
			->will($this->returnValue($server['SCRIPT_NAME']));
		$symfony_request->expects($this->any())
			->method('getQueryString')
			->will($this->returnValue($server['QUERY_STRING']));
		$symfony_request->expects($this->any())
			->method('getBasePath')
			->will($this->returnValue($server['REQUEST_URI']));
		$symfony_request->expects($this->any())
			->method('getPathInfo')
			->will($this->returnValue('/'));
		$phpbb_filesystem = new \phpbb\filesystem($symfony_request, $phpbb_root_path, $phpEx);

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
