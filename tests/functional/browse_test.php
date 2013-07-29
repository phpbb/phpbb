<?php
/**
*
* @package testing
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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

	public function test_feed()
	{
		$crawler = self::request('GET', 'feed.php', array(), false);
		self::assert_response_xml();
		$this->assertGreaterThan(0, $crawler->filter('entry')->count());
	}
}
