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
	protected $server = array();

	/**
	* Set up the required user object and server variables for the suites
	*/
	protected function setUp()
	{
		global $user, $phpbb_root_path, $phpEx, $request, $symfony_request, $phpbb_filesystem;

		// Put this into a global function being run by every test to init a proper user session
		$this->server['HTTP_HOST']				= 'localhost';
		$this->server['SERVER_NAME']			= 'localhost';
		$this->server['SERVER_ADDR']			= '127.0.0.1';
		$this->server['SERVER_PORT']			= 80;
		$this->server['REMOTE_ADDR']			= '127.0.0.1';
		$this->server['QUERY_STRING']			= '';
		$this->server['REQUEST_URI']			= '/tests/';
		$this->server['SCRIPT_NAME']			= '/tests/index.php';
		$this->server['SCRIPT_FILENAME']		= '/var/www/tests/index.php';
		$this->server['PHP_SELF']				= '/tests/index.php';
		$this->server['HTTP_USER_AGENT']		= 'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.8.1.14) Gecko/20080404 Firefox/2.0.0.14';
		$this->server['HTTP_ACCEPT_LANGUAGE']	= 'de-de,de;q=0.8,en-us;q=0.5,en;q=0.3';

/*
		[HTTP_ACCEPT_ENCODING] => gzip,deflate
		[HTTP_ACCEPT_CHARSET] => ISO-8859-1,utf-8;q=0.7,*;q=0.7
		DOCUMENT_ROOT] => /var/www/
		[SCRIPT_FILENAME] => /var/www/tests/index.php
*/

		$request = new phpbb_mock_request(array(), array(), array(), $this->server);
		$symfony_request = new \phpbb\symfony_request($request);

		$phpbb_filesystem = new \phpbb\filesystem\filesystem();

		// Set no user and trick a bit to circumvent errors
		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->lang = true;
		$user->browser				= $this->server['HTTP_USER_AGENT'];
		$user->referer				= '';
		$user->forwarded_for		= '';
		$user->host					= $this->server['HTTP_HOST'];
		$user->page = \phpbb\session::extract_current_page($phpbb_root_path);
	}

	protected function tearDown()
	{
		global $user;
		$user = NULL;
	}
}
