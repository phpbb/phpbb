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
	public function test_feed_compatibility()
	{
		$this->assert301('feed.php', 'app.php/feed');
		$this->assert301('feed.php?mode=foobar', 'app.php/feed/foobar');
		$this->assert301('feed.php?mode=news', 'app.php/feed/news');
		$this->assert301('feed.php?mode=topics', 'app.php/feed/topics');
		$this->assert301('feed.php?mode=topics_news', 'app.php/feed/topics_news');
		$this->assert301('feed.php?mode=topics_active', 'app.php/feed/topics_active');
		$this->assert301('feed.php?mode=forums', 'app.php/feed/forums');
		$this->assert301('feed.php?f=1', 'app.php/feed/forum/1');
		$this->assert301('feed.php?t=1', 'app.php/feed/topic/1');
	}

	protected function assert301($from, $to)
	{
		self::$client->followRedirects(false);
		self::request('GET', $from, array(), false);
		$this->assertEquals(301, self::$client->getResponse()->getStatus());
		$this->assertStringEndsWith($to, self::$client->getResponse()->getHeader('Location'));
	}
}
