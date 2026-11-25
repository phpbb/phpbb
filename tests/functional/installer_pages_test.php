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
 * Verifies that the main phpBB pages are reachable after the board has been
 * installed by the installer.
 *
 * The board is expected to already be installed (e.g. by the CLI installer
 * driven from the installer_tests workflow), so the in-process installation
 * performed by the functional test base class is skipped.
 *
 * @group functional
 */
class phpbb_functional_installer_pages_test extends phpbb_functional_test_case
{
	protected static $board_preinstalled = true;

	public function test_index()
	{
		$crawler = self::request('GET', 'index.php');
		$this->assertGreaterThan(0, $crawler->filter('.topiclist')->count());
	}

	public function test_viewforum()
	{
		$crawler = self::request('GET', 'viewforum.php?f=2');
		$this->assertGreaterThan(0, $crawler->filter('.topiclist')->count());
	}

	public function test_viewtopic()
	{
		$crawler = self::request('GET', 'viewtopic.php?t=1');
		$this->assertGreaterThan(0, $crawler->filter('.postbody')->count());
	}
}
