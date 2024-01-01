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
class phpbb_functional_viewonline_test extends phpbb_functional_test_case
{
	protected function get_forum_name_by_topic_id($topic_id)
	{
		$db = $this->get_db();

		// Forum info
		$sql =  'SELECT f.forum_name
			FROM ' . FORUMS_TABLE . ' f,' . TOPICS_TABLE . ' t
			WHERE t.forum_id = f.forum_id
				AND t.topic_id = ' . (int) $topic_id;
		$result = $db->sql_query($sql);
		$forum_name = $db->sql_fetchfield('forum_name');
		$db->sql_freeresult($result, 1800); // cache for 30 minutes

		return $forum_name;
	}

	protected function get_forum_name_by_forum_id($forum_id)
	{
		$db = $this->get_db();

		// Forum info
		$sql =  'SELECT forum_name
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $db->sql_query($sql);
		$forum_name = $db->sql_fetchfield('forum_name');
		$db->sql_freeresult($result, 1800); // cache for 30 minutes

		return $forum_name;
	}

	public function test_viewonline()
	{
		$this->create_user('viewonline-test-user1');

		// Log in as test user
		self::$client->restart();
		$this->login('viewonline-test-user1');
		$crawler = self::request('GET', 'posting.php?mode=reply&t=1&sid=' . $this->sid);
		$this->assertContainsLang('POST_REPLY', $crawler->text());
		// Log in as another user
		self::$client->restart();
		$this->login();
		// PHP goes faster than DBMS, make sure session data got written to the database
		sleep(1);
		$crawler = self::request('GET', 'viewonline.php?sid=' . $this->sid);
		// Make sure posting reply page is in the list
		$this->assertStringContainsString('viewonline-test-user1', $crawler->text());
		$this->assertStringContainsString($this->lang('REPLYING_MESSAGE', $this->get_forum_name_by_topic_id(1)), $crawler->text());

		// Log in as test user
		self::$client->restart();
		$this->login('viewonline-test-user1');
		$crawler = self::request('GET', 'posting.php?mode=post&f=2&sid=' . $this->sid);
		$this->assertContainsLang('POST_TOPIC', $crawler->text());
		// Log in as another user
		self::$client->restart();
		$this->login();
		// PHP goes faster than DBMS, make sure session data got written to the database
		sleep(1);
		$crawler = self::request('GET', 'viewonline.php?sid=' . $this->sid);
		// Make sure posting message page is in the list
		$this->assertStringContainsString('viewonline-test-user1', $crawler->text());
		$this->assertStringContainsString($this->lang('POSTING_MESSAGE', $this->get_forum_name_by_forum_id(2)), $crawler->text());

		// Log in as test user
		self::$client->restart();
		$this->login('viewonline-test-user1');
		$test_post_data = $this->create_post(2, 1, 'Viewonline test post #1', 'Viewonline test post message');
		$crawler = self::request('GET', 'posting.php?mode=edit&p=' . $test_post_data['post_id'] .  '&sid=' . $this->sid);
		$this->assertContainsLang('EDIT_POST', $crawler->text());
		// Log in as another user
		self::$client->restart();
		$this->login();
		// PHP goes faster than DBMS, make sure session data got written to the database
		sleep(1);
		$crawler = self::request('GET', 'viewonline.php?sid=' . $this->sid);
		// Make sure posting message page is in the list
		$this->assertStringContainsString('viewonline-test-user1', $crawler->text());
		$this->assertStringContainsString($this->lang('POSTING_MESSAGE', $this->get_forum_name_by_forum_id(2)), $crawler->text());

		// Log in as test user
		self::$client->restart();
		$this->login('viewonline-test-user1');
		self::request('GET', 'viewtopic.php?t=1&sid=' . $this->sid);
		// Log in as another user
		self::$client->restart();
		$this->login();
		// PHP goes faster than DBMS, make sure session data got written to the database
		sleep(1);
		$crawler = self::request('GET', 'viewonline.php?sid=' . $this->sid);
		// Make sure reading topic page is in the list
		$this->assertStringContainsString('viewonline-test-user1', $crawler->text());
		$this->assertStringContainsString($this->lang('READING_TOPIC', $this->get_forum_name_by_topic_id(1)), $crawler->text());

		// Log in as test user
		self::$client->restart();
		$this->login('viewonline-test-user1');
		self::request('GET', 'viewforum.php?f=2&sid=' . $this->sid);
		// Log in as another user
		self::$client->restart();
		$this->login();
		// PHP goes faster than DBMS, make sure session data got written to the database
		sleep(1);
		$crawler = self::request('GET', 'viewonline.php?sid=' . $this->sid);
		// Make sure reading forum page is in the list
		$this->assertStringContainsString('viewonline-test-user1', $crawler->text());
		$this->assertStringContainsString($this->lang('READING_FORUM', $this->get_forum_name_by_forum_id(2)), $crawler->text());
	}
}
