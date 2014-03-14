<?php
/**
*
* @package testing
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* @group slow
*/
class phpbb_version_helper_fetch_test extends phpbb_test_case
{
	public function setUp()
	{
		parent::setUp();

		global $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		$this->cache = $this->getMockBuilder('\phpbb\cache\service')
			->disableOriginalConstructor()
			->getMock();

		$this->version_helper = new \phpbb\version_helper(
			$this->cache,
			new \phpbb\config\config(array(
				'version'	=> '3.1.0',
			)),
			new \phpbb\user()
		);
	}

	public function test_version_phpbb_com()
	{
		global $phpbb_root_path, $phpEx;
		include_once($phpbb_root_path . 'includes/functions.' . $phpEx);

		if (!phpbb_checkdnsrr('version.phpbb.com', 'A'))
		{
			$this->markTestSkipped(sprintf(
				'Could not find a DNS record for hostname %s. ' .
				'Assuming network is down.',
				'version.phpbb.com'
			));
		}

		$this->version_helper->get_versions();

		// get_versions checks to make sure we got a valid versions file or
		// throws an exception if we did not. We don't need to test anything
		// here, but adding an assertion so we do not get a warning about no
		// assertions in this test
		$this->assertSame(true, true);
	}
}
