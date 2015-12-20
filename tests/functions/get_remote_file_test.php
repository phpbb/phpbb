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
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_compatibility.php';

/**
* @group slow
*/
class phpbb_functions_get_remote_file extends phpbb_test_case
{
	public function test_version_phpbb_com()
	{
		global $phpbb_container;
		$phpbb_container = new phpbb_mock_container_builder();
		$phpbb_container->set('file_downloader', new \phpbb\file_downloader());

		$hostname = 'version.phpbb.com';

		if (!phpbb_checkdnsrr($hostname, 'A'))
		{
			$this->markTestSkipped(sprintf(
				'Could not find a DNS record for hostname %s. ' .
				'Assuming network is down.',
				$hostname
			));
		}

		$errstr = $errno = null;
		$file = get_remote_file($hostname, '/phpbb', '30x.txt', $errstr, $errno);

		$this->assertNotEquals(
			0,
			strlen($file),
			'Failed asserting that the response is not empty.'
		);

		$this->assertSame(
			'',
			$errstr,
			'Failed asserting that the error string is empty.'
		);

		$this->assertSame(
			0,
			$errno,
			'Failed asserting that the error number is 0 (i.e. no error occurred).'
		);

		$lines = explode("\n", $file);

		$this->assertGreaterThanOrEqual(
			2,
			sizeof($lines),
			'Failed asserting that the version file has at least two lines.'
		);

		$this->assertStringStartsWith(
			'3.',
			$lines[0],
			"Failed asserting that the first line of the version file starts with '3.'"
		);

		$this->assertNotSame(
			false,
			filter_var($lines[1], FILTER_VALIDATE_URL),
			'Failed asserting that the second line of the version file is a valid URL.'
		);

		$this->assertContains('http', $lines[1]);
		$this->assertContains('phpbb.com', $lines[1], '', true);
	}
}
