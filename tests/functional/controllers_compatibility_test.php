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

/**
* @group functional
*/

class phpbb_functional_controllers_compatibility_test extends phpbb_functional_test_case
{
	public function test_report_compatibility()
	{
		$this->assert301('report.php?f=1&p=1', 'app.php/post/1/report');
		$this->assert301('report.php?p=1', 'app.php/post/1/report');
		$this->assert301('report.php?pm=1', 'app.php/pm/1/report');
	}

	protected function assert301($from, $to)
	{
		self::$client->followRedirects(false);
		self::request('GET', $from, array(), false);

		// Fix sid issues
		$location = self::$client->getResponse()->getHeader('Location');
		$location = preg_replace('#sid=[^&]+(&(amp;)?)?#', '', $location);
		if (substr($location, -1) === '?')
		{
			$location = substr($location, 0, -1);
		}

		$this->assertEquals(301, self::$client->getResponse()->getStatus());
		$this->assertStringEndsWith($to, $location);
	}
}
