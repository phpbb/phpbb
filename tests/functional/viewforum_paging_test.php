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
class viewforum_paging_test extends phpbb_functional_test_case
{
	protected $data = array();

	public function test_setup_forums()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Viewforum Pagination Test #1',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		self::submit($form);

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Viewforum Pagination Test #2',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		self::submit($form);

		$this->set_post_settings(array(
			'flood_interval'	=> 0,
			'topics_per_page'	=> 3,
		));
	}

	public function test_create_posts()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Viewforum Pagination Test #1',
				'Viewforum Pagination Test #2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Viewforum Pagination Test #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'initial comparison');

		for ($topic_id = 1; $topic_id <= 6; $topic_id++)
		{
			$this->create_topic($this->data['forums']['Viewforum Pagination Test #1'], 'Viewforum Pagination TestTopic #' . $topic_id, 'This is a test topic posted by the testing framework.');
		}

		$this->create_topic($this->data['forums']['Viewforum Pagination Test #2'], 'Viewforum Pagination TestTopic #GA1', 'This is a test topic posted by the testing framework.', array(
			'topic_type' => POST_GLOBAL,
		));

		$this->assert_forum_details($this->data['forums']['Viewforum Pagination Test #1'], array(
			'forum_posts_approved'		=> 6,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 6,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
		), 'after creating topics');

		$this->assert_forum_details($this->data['forums']['Viewforum Pagination Test #2'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
		), 'after creating GA');

		// Set flood interval back to 15
		$this->admin_login();
		$this->set_post_settings(array(
			'flood_interval'	=> 15,
		));
	}

	public function test_viewforum_first_page()
	{
		$this->load_ids(array(
			'forums' => array(
				'Viewforum Pagination Test #1',
				'Viewforum Pagination Test #2',
			),
		));
		$crawler = self::request('GET', 'viewforum.php?f=' . $this->data['forums']['Viewforum Pagination Test #1']);

		// Test the topics that are displayed
		$topiclists = $crawler->filter('.forumbg .topics');
		$this->assertEquals(2, $topiclists->count());
		$topiclist = $topiclists->eq(0)->filter('li');
		$this->assertStringEndsWith('TestTopic #GA1', $topiclist->eq(0)->filter('.topictitle')->text());
		$topiclist = $topiclists->eq(1)->filter('li');
		$this->assertStringEndsWith('TestTopic #6', $topiclist->eq(0)->filter('.topictitle')->text());
		$this->assertStringEndsWith('TestTopic #5', $topiclist->eq(1)->filter('.topictitle')->text());
		$this->assertStringEndsWith('TestTopic #4', $topiclist->eq(2)->filter('.topictitle')->text());

		// Test the pagination, should only have: 1 - 2 - Next
		$this->assertEquals(2, $crawler->filter('div.pagination')->count());
		$top_pagination = $crawler->filter('div.pagination')->eq(0);
		$this->assertEquals(3, $top_pagination->filter('li')->count(), 'Number of pagination items on page 1 does not match');
		$this->assertContains('1', $top_pagination->filter('li')->eq(0)->text());
		$this->assertContains('2', $top_pagination->filter('li')->eq(1)->text());
		$this->assertContainsLang('NEXT', $top_pagination->filter('li')->eq(2)->text());
	}

	public function test_viewforum_second_page()
	{
		$this->load_ids(array(
			'forums' => array(
				'Viewforum Pagination Test #1',
				'Viewforum Pagination Test #2',
			),
		));
		$crawler = self::request('GET', 'viewforum.php?f=' . $this->data['forums']['Viewforum Pagination Test #1'] . '&start=3');

		// Test the topics that are displayed
		$topiclists = $crawler->filter('.forumbg .topics');
		$this->assertEquals(2, $topiclists->count());
		$topiclist = $topiclists->eq(0)->filter('li');
		$this->assertStringEndsWith('TestTopic #GA1', $topiclist->eq(0)->filter('.topictitle')->text());
		$topiclist = $topiclists->eq(1)->filter('li');
		$this->assertStringEndsWith('TestTopic #3', $topiclist->eq(0)->filter('.topictitle')->text());
		$this->assertStringEndsWith('TestTopic #2', $topiclist->eq(1)->filter('.topictitle')->text());
		$this->assertStringEndsWith('TestTopic #1', $topiclist->eq(2)->filter('.topictitle')->text());

		// Test the pagination, should only have: Previous - 1 - 2
		$this->assertEquals(2, $crawler->filter('div.pagination')->count());
		$top_pagination = $crawler->filter('div.pagination')->eq(0);
		$this->assertEquals(3, $top_pagination->filter('li')->count(), 'Number of pagination items on page 2 does not match');
		$this->assertContainsLang('PREVIOUS', $top_pagination->filter('li')->eq(0)->text());
		$this->assertContains('1', $top_pagination->filter('li')->eq(1)->text());
		$this->assertContains('2', $top_pagination->filter('li')->eq(2)->text());
	}

	protected function assert_forum_details($forum_id, $details, $additional_error_message = '')
	{
		$this->db = $this->get_db();

		$sql = 'SELECT ' . implode(', ', array_keys($details)) . '
			FROM phpbb_forums
			WHERE forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$this->assertEquals($details, $data, "Forum {$forum_id} does not match expected {$additional_error_message}");
	}

	/**
	 * Sets the post setting via the ACP page
	 *
	 * @param array $settings
	 */
	protected function set_post_settings($settings)
	{
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_board&mode=post');

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		foreach ($settings as $setting => $value)
		{
			$values["config[{$setting}]"] = $value;
		}
		$form->setValues($values);
		$crawler = self::submit($form);
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());
	}

	/**
	 * Loads forum, topic and post IDs
	 *
	 * @param array $data
	 */
	protected function load_ids($data)
	{
		$this->db = $this->get_db();

		if (!empty($data['forums']))
		{
			$sql = 'SELECT *
				FROM phpbb_forums
				WHERE ' . $this->db->sql_in_set('forum_name', $data['forums']);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				if (in_array($row['forum_name'], $data['forums']))
				{
					$this->data['forums'][$row['forum_name']] = (int) $row['forum_id'];
				}
			}
			$this->db->sql_freeresult($result);
		}

		if (!empty($data['topics']))
		{
			$sql = 'SELECT *
				FROM phpbb_topics
				WHERE ' . $this->db->sql_in_set('topic_title', $data['topics']);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				if (in_array($row['topic_title'], $data['topics']))
				{
					$this->data['topics'][$row['topic_title']] = (int) $row['topic_id'];
				}
			}
			$this->db->sql_freeresult($result);
		}

		if (!empty($data['posts']))
		{
			$sql = 'SELECT *
				FROM phpbb_posts
				WHERE ' . $this->db->sql_in_set('post_subject', $data['posts']);
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				if (in_array($row['post_subject'], $data['posts']))
				{
					$this->data['posts'][$row['post_subject']] = (int) $row['post_id'];
				}
			}
			$this->db->sql_freeresult($result);
		}
	}
}
