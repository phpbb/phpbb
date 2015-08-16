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
class phpbb_functional_browse_test extends phpbb_functional_test_case
{
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

	public function test_help_faq()
	{
		$crawler = self::request('GET', 'app.php/help/faq');
		$this->assertGreaterThan(0, $crawler->filter('h2.faq-title')->count());
	}

	public function test_help_bbcode()
	{
		$crawler = self::request('GET', 'app.php/help/bbcode');
		$this->assertGreaterThan(0, $crawler->filter('h2.faq-title')->count());
	}

	public function test_feed()
	{
		$crawler = self::request('GET', 'feed.php', array(), false);
		self::assert_response_xml();
		$this->assertGreaterThan(0, $crawler->filter('entry')->count());
	}
}
