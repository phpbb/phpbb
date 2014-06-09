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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';

/**
* Extends the session class to overwrite the setting of cookies.
*
* The session class directly writes cookie headers making it impossible to
* test it without warnings about sent headers. This class only stores cookie
* data for later verification.
*/
class phpbb_mock_session_testable extends \phpbb\session
{
	private $_cookies = array();

	public function set_cookie($name, $data, $time, $httponly = true)
	{
		$this->_cookies[$name] = array($data, $time);
	}

	/**
	* Checks if the cookies were set correctly.
	*
	* @param PHPUnit_Framework_Assert test    The test from which this is called
	* @param array(string => mixed)   cookies The cookie data to check against.
	*				The keys are cookie names, the values can either be null to
	*				check only the existance of the cookie, or an array(d, t),
	*				where d is the cookie data to check, or null to skip the
	*				check and t is the cookie time to check, or null to skip.
	*/
	public function check_cookies(PHPUnit_Framework_Assert $test, $cookies)
	{
		$test->assertEquals(array_keys($cookies), array_keys($this->_cookies), 'Incorrect cookies were set');

		foreach ($cookies as $name => $cookie)
		{
			if (!is_null($cookie))
			{
				$data = $cookie[0];
				$time = $cookie[1];

				if (!is_null($data))
				{
					$test->assertEquals($data, $this->_cookies[$name][0], "Cookie $name contains incorrect data");
				}

				if (!is_null($time))
				{
					$test->assertEquals($time, $this->_cookies[$name][1], "Cookie $name expires at the wrong time");
				}
			}
		}
	}

	public function setup()
	{
	}
}

