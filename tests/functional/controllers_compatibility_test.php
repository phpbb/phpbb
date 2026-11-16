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
		$this->assert301('report.php?f=1&p=1', 'index.php/post/1/report');
		$this->assert301('report.php?p=1', 'index.php/post/1/report');
		$this->assert301('report.php?pm=1', 'index.php/pm/1/report');
	}

	public function test_feed_compatibility()
	{
		$this->assert301('feed.php', 'index.php/feed');
		$this->assert301('feed.php?mode=foobar', 'index.php/feed/foobar');
		$this->assert301('feed.php?mode=news', 'index.php/feed/news');
		$this->assert301('feed.php?mode=topics', 'index.php/feed/topics');
		$this->assert301('feed.php?mode=topics_news', 'index.php/feed/topics_news');
		$this->assert301('feed.php?mode=topics_active', 'index.php/feed/topics_active');
		$this->assert301('feed.php?mode=forums', 'index.php/feed/forums');
		$this->assert301('feed.php?f=1', 'index.php/feed/forum/1');
		$this->assert301('feed.php?t=1', 'index.php/feed/topic/1');
	}

	public function test_cron_compatibility()
	{
		$this->assert301('cron.php?cron_type=foo', 'index.php/cron/foo');
		$this->assert301('cron.php?cron_type=foo&bar=foobar', 'index.php/cron/foo?bar=foobar');
		$this->assert301('cron.php?cron_type=foo&bar=foobar&who=me', 'index.php/cron/foo?bar=foobar&who=me');
	}

	protected function assert301($from, $to)
	{
		self::$client->followRedirects(false);
		self::request('GET', $from, array(), false);

		// Fix sid issues
		$location = self::$client->getResponse()->getHeader('Location');
		$location = str_replace('&amp;', '&', $location);
		$location = preg_replace('#sid=[^&]+(&(amp;)?)?#', '', $location);
		if (substr($location, -1) === '?')
		{
			$location = substr($location, 0, -1);
		}

		$this->assertEquals(301, self::$client->getResponse()->getStatusCode());
		$this->assertStringEndsWith($to, $location);
	}
}
