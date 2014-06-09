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
class phpbb_functional_visibility_reapprove_test extends phpbb_functional_test_case
{
	protected $data = array();

	public function test_setup_forums()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Reapprove Test #1',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		$crawler = self::submit($form);

		// Set flood interval to 0
		$this->set_flood_interval(0);
	}

	public function test_create_posts()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Reapprove Test #1',
			),
		));

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'initial comparison');

		// Test creating topic #1
		$post = $this->create_topic($this->data['forums']['Reapprove Test #1'], 'Reapprove Test Topic #1', 'This is a test topic posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		$this->assertContains('Reapprove Test Topic #1', $crawler->filter('h2')->text());
		$this->data['topics']['Reapprove Test Topic #1'] = (int) $post['topic_id'];
		$this->data['posts']['Reapprove Test Topic #1'] = (int) $this->get_parameter_from_link($crawler->filter('.post')->selectLink($this->lang('POST', '', ''))->link()->getUri(), 'p');

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Reapprove Test Topic #1'],
		), 'after creating topic #1');

		$this->logout();
		$this->create_user('reapprove_testuser');
		$this->add_user_group('NEWLY_REGISTERED', array('reapprove_testuser'));
		$this->login('reapprove_testuser');

		// Test creating a reply
		$post2 = $this->create_post($this->data['forums']['Reapprove Test #1'], $post['topic_id'], 'Re: Reapprove Test Topic #1-#2', 'This is a test post posted by the testing framework.', array(), 'POST_STORED_MOD');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Reapprove Test Topic #1']}&sid={$this->sid}");
		$this->assertNotContains('Re: Reapprove Test Topic #1-#2', $crawler->filter('#page-body')->text());

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 1,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Reapprove Test Topic #1'],
		), 'after replying');

		// Test creating topic #2
		$post = $this->create_topic($this->data['forums']['Reapprove Test #1'], 'Reapprove Test Topic #2', 'This is a test topic posted by the testing framework.', array(), 'POST_STORED_MOD');
		$crawler = self::request('GET', "viewforum.php?f={$this->data['forums']['Reapprove Test #1']}&sid={$this->sid}");

		$this->assertNotContains('Reapprove Test Topic #2', $crawler->filter('html')->text());

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 2,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 1,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Reapprove Test Topic #1'],
		), 'after creating topic #2');

		$this->logout();
	}

	public function test_approve_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Reapprove Test #1',
			),
			'topics' => array(
				'Reapprove Test Topic #1',
				'Reapprove Test Topic #2',
			),
			'posts' => array(
				'Reapprove Test Topic #1',
				'Re: Reapprove Test Topic #1-#2',
				'Reapprove Test Topic #2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 2,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 1,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Reapprove Test Topic #1'],
		), 'before approving post');

		$this->add_lang('posting');
		$this->add_lang('viewtopic');
		$this->add_lang('mcp');
		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Reapprove Test Topic #1']}&sid={$this->sid}");
		$this->assertContains('Reapprove Test Topic #1', $crawler->filter('h2')->text());
		$this->assertContains('Re: Reapprove Test Topic #1-#2', $crawler->filter('#page-body')->text());

		$form = $crawler->selectButton($this->lang('APPROVE'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_APPROVED_SUCCESS', $crawler->text());

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 1,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 1,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Reapprove Test Topic #1-#2'],
		), 'after approving post');

		$link = $crawler->selectLink($this->lang('RETURN_PAGE', '', ''))->link();
		$link_url = $link->getUri();
		$this->assertContains('viewtopic.php?f=' . $this->data['forums']['Reapprove Test #1'] . '&t=' . $this->data['topics']['Reapprove Test Topic #1'], $link_url);

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Reapprove Test Topic #1']}&sid={$this->sid}");
		$this->assertContains('Reapprove Test Topic #1', $crawler->filter('h2')->text());
		$this->assertContains('Re: Reapprove Test Topic #1-#2', $crawler->filter('#page-body')->text());
	}

	public function test_approve_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Reapprove Test #1',
			),
			'topics' => array(
				'Reapprove Test Topic #1',
				'Reapprove Test Topic #2',
			),
			'posts' => array(
				'Reapprove Test Topic #1',
				'Re: Reapprove Test Topic #1-#2',
				'Reapprove Test Topic #2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 1,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 1,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Reapprove Test Topic #1-#2'],
		), 'before approving topic');

		$this->add_lang('posting');
		$this->add_lang('viewtopic');
		$this->add_lang('mcp');
		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Reapprove Test Topic #2']}&sid={$this->sid}");
		$this->assertContains('Reapprove Test Topic #2', $crawler->filter('h2')->text());

		$form = $crawler->selectButton($this->lang('APPROVE'))->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton($this->lang('YES'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_APPROVED_SUCCESS', $crawler->text());

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 3,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 2,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Reapprove Test Topic #2'],
		), 'after approving topic');

		$link = $crawler->selectLink($this->lang('RETURN_PAGE', '', ''))->link();
		$link_url = $link->getUri();
		$this->assertContains('viewtopic.php?f=' . $this->data['topic']['Reapprove Test Topic #2'], $link_url);

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Reapprove Test Topic #2']}&sid={$this->sid}");
		$this->assertContains('Reapprove Test Topic #2', $crawler->filter('h2')->text());
	}

	public function test_edit_posts()
	{
		$this->load_ids(array(
			'forums' => array(
				'Reapprove Test #1',
			),
			'topics' => array(
				'Reapprove Test Topic #1',
				'Reapprove Test Topic #2',
			),
			'posts' => array(
				'Reapprove Test Topic #1',
				'Re: Reapprove Test Topic #1-#2',
				'Reapprove Test Topic #2',
			),
		));
		$this->add_lang('posting');

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 3,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 2,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Reapprove Test Topic #2'],
		), 'before editing post');

		$this->login('reapprove_testuser');
		$this->add_user_group('NEWLY_REGISTERED', array('reapprove_testuser'));

		// Test editing a post
		$posting_url = "posting.php?mode=edit&f={$this->data['forums']['Reapprove Test #1']}&p={$this->data['posts']['Re: Reapprove Test Topic #1-#2']}&sid={$this->sid}";
		$form_data = array(
			'message'	=> 'Post edited by testing framework',
			'subject'	=> 'Re: Reapprove Test Topic #1-#2',
			'post'		=> true,
		);
		$this->submit_post($posting_url, 'EDIT_POST', $form_data, 'POST_EDITED_MOD');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Reapprove Test Topic #1']}&sid={$this->sid}");
		$this->assertNotContains('Re: Reapprove Test Topic #1-#2', $crawler->filter('#page-body')->text());
		$this->assertNotContains('Post edited by testing framework', $crawler->filter('#page-body')->text());

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 1,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 2,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Reapprove Test Topic #2'],
		), 'after editing post');

		// Test editing a topic
		$posting_url = "posting.php?mode=edit&f={$this->data['forums']['Reapprove Test #1']}&p={$this->data['posts']['Reapprove Test Topic #2']}&sid={$this->sid}";
		$form_data = array(
			'message'	=> 'Post edited by testing framework',
			'subject'	=> 'Reapprove Test Topic #2',
			'post'		=> true,
		);
		$this->submit_post($posting_url, 'EDIT_POST', $form_data, 'POST_EDITED_MOD');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Reapprove Test Topic #2']}&sid={$this->sid}", array(), false);
		self::assert_response_html(404);
		$this->assertNotContains('Reapprove Test Topic #2', $crawler->filter('#page-body')->text());
		$this->assertNotContains('Post edited by testing framework', $crawler->filter('#page-body')->text());

		$this->assert_forum_details($this->data['forums']['Reapprove Test #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 2,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 1,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Reapprove Test Topic #1'],
		), 'after editing topic');

		$this->logout();
		$this->login();

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Reapprove Test Topic #1']}&sid={$this->sid}");
		$this->assertContains('Re: Reapprove Test Topic #1-#2', $crawler->filter('#page-body')->text());
		$this->assertContains('Post edited by testing framework', $crawler->filter('#page-body')->text());
	}

	public function test_approve_post_again()
	{
		$this->test_approve_post();
	}

	public function test_approve_topic_again()
	{
		$this->test_approve_topic();
	}

	public function test_reset_flood_interval()
	{
		$this->login();
		$this->admin_login();

		// Set flood interval back to 15
		$this->set_flood_interval(15);
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

	protected function set_flood_interval($flood_interval)
	{
		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_board&mode=post');

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		$values["config[flood_interval]"] = $flood_interval;
		$form->setValues($values);
		$crawler = self::submit($form);
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());
	}

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
