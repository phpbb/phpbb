<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_admin.php';

/**
* @group slow
*/
class phpbb_functions_get_remote_file extends phpbb_test_case
{
	public function test_version_phpbb_com()
	{
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
		$info = get_remote_file($hostname, '/phpbb', 'versions.json', $errstr, $errno);

		$this->assertNotEquals(
			0,
			strlen($info),
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

		$info = json_decode($info);

		$this->assertGreaterThanOrEqual(
			2,
			sizeof($info),
			'Failed asserting that the version file has at least two lines.'
		);

		$this->assertStringStartsWith(
			'3.',
			$info['30x']['current'],
			"Failed asserting that the first line of the version file starts with '3.'"
		);

		$this->assertNotSame(
			false,
			filter_var($info['30x']['announcement'], FILTER_VALIDATE_URL),
			'Failed asserting that the second line of the version file is a valid URL.'
		);

		$this->assertContains('http', $info['30x']['announcement']);
		$this->assertContains('phpbb.com', $info['30x']['announcement'], '', true);
	}
}
