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
class phpbb_functional_last_post_link_test extends phpbb_functional_test_case
{
	public function test_last_post_links()
	{
		$this->login();
		$this->add_lang('viewtopic');

		// Global announcement so the UCP front page lists a topic
		$post = $this->create_topic(2, 'Global announcement', 'Global announcement for the last post link test.', ['topic_type' => POST_GLOBAL]);
		$topic_url = 'viewtopic.php?t=' . $post['topic_id'] . '&sid=' . $this->sid;

		// Bookmark and subscribe to the topic, subscribe to its forum
		$crawler = self::request('GET', $topic_url);
		$crawler = self::request('GET', $crawler->filter('a.bookmark-link')->attr('href'));
		$this->assertContainsLang('BOOKMARK_ADDED', $crawler->text());

		$crawler = self::request('GET', $topic_url);
		$crawler = self::request('GET', $crawler->filter('a.watch-topic-link')->attr('href'));
		$this->assertContainsLang('ARE_WATCHING_TOPIC', $crawler->text());

		$crawler = self::request('GET', 'viewforum.php?f=2&sid=' . $this->sid);
		$crawler = self::request('GET', $crawler->filter('a[href*="watch=forum"]')->attr('href'));
		$this->assertContainsLang('ARE_WATCHING_FORUM', $crawler->text());

		$pages = [
			'index.php',
			'viewforum.php?f=2',
			'search.php?keywords=announcement&sr=topics',
			'ucp.php?i=ucp_main&mode=front',
			'ucp.php?i=ucp_main&mode=bookmarks',
			'ucp.php?i=ucp_main&mode=subscribed',
		];

		foreach ($pages as $page)
		{
			$crawler = self::request('GET', $page);
			$labels = $crawler->filter('dd.lastpost a i.c-last-post-icon + span.sr-only');
			$this->assertGreaterThan(0, $labels->count(), "No last post link on $page");

			foreach ($labels as $label)
			{
				$this->assertEquals($this->lang('VIEW_LATEST_POST'), trim($label->textContent), "Empty last post link text on $page");
			}
		}
	}
}
