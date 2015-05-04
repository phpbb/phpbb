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

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);

		$this->version_helper = new \phpbb\version_helper(
			$this->cache,
			new \phpbb\config\config(array(
				'version'	=> '3.1.0',
			)),
			new \phpbb\file_downloader(),
			new \phpbb\user(new \phpbb\language\language($lang_loader), '\phpbb\datetime')
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
