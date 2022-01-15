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
class phpbb_functional_visibility_unapproved_test extends phpbb_functional_test_case
{
	protected $data = [];

	public function test_setup_forums()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form([
			'forum_name'	=> 'Unapproved Posts Test #1',
		]);
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form([
			'forum_perm_from'	=> 2,
		]);
		$crawler = self::submit($form);

		// Set flood interval to 0
		$this->set_flood_interval(0);
	}

	public function test_create_posts()
	{
		$this->login();
		$this->load_ids([
			'forums' => [
				'Unapproved Posts Test #1',
			],
		]);

		$this->assert_forum_details($this->data['forums']['Unapproved Posts Test #1'], [
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		], 'initial comparison');

		// Test creating topic #1
		$post = $this->create_topic($this->data['forums']['Unapproved Posts Test #1'], 'Unapproved Posts Test Topic #1', 'This is a test topic posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		$this->assertStringContainsString('Unapproved Posts Test Topic #1', $crawler->filter('h2')->text());
		$this->data['topics']['Unapproved Posts Test Topic #1'] = (int) $post['topic_id'];
		$this->data['posts']['Unapproved Posts Test Topic #1'] = (int) $this->get_parameter_from_link($crawler->filter('.post')->selectLink($this->lang('POST', '', ''))->link()->getUri(), 'p');

		$this->assert_forum_details($this->data['forums']['Unapproved Posts Test #1'], [
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Unapproved Posts Test Topic #1'],
		], 'after creating topic #1');

		$this->logout();
		$this->create_user('unapproved_posts_test_user#1');
		$this->add_user_group('NEWLY_REGISTERED', ['unapproved_posts_test_user#1']);
		$this->login('unapproved_posts_test_user#1');

		// Test creating a reply
		$post2 = $this->create_post($this->data['forums']['Unapproved Posts Test #1'], $post['topic_id'], 'Re: Unapproved Posts Test Topic #1-#2', 'This is a test post posted by the testing framework.', [], 'POST_STORED_MOD');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Unapproved Posts Test Topic #1']}&sid={$this->sid}");
		$this->assertStringNotContainsString('Re: Unapproved Posts Test Topic #1-#2', $crawler->filter('#page-body')->text());

		$this->assert_forum_details($this->data['forums']['Unapproved Posts Test #1'], [
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 1,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Unapproved Posts Test Topic #1'],
		], 'after replying');

		// Test creating topic #2
		$post = $this->create_topic($this->data['forums']['Unapproved Posts Test #1'], 'Unapproved Posts Test Topic #2', 'This is a test topic posted by the testing framework.', [], 'POST_STORED_MOD');
		$crawler = self::request('GET', "viewforum.php?f={$this->data['forums']['Unapproved Posts Test #1']}&sid={$this->sid}");

		$this->assertStringNotContainsString('Unapproved Posts Test Topic #2', $crawler->filter('html')->text());

		$this->assert_forum_details($this->data['forums']['Unapproved Posts Test #1'], [
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 2,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 1,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Unapproved Posts Test Topic #1'],
		], 'after creating topic #2');

		$this->logout();
	}

	public function test_view_unapproved_post_disabled()
	{
		// user who created post
		$this->login('unapproved_posts_test_user#1');
		$this->load_ids([
			'forums' => [
				'Unapproved Posts Test #1',
			],
			'topics' => [
				'Unapproved Posts Test Topic #1',
				'Unapproved Posts Test Topic #2',
			],
			'posts' => [
				'Unapproved Posts Test Topic #1',
				'Re: Unapproved Posts Test Topic #1-#2',
				'Unapproved Posts Test Topic #2',
			],
		]);

		$this->assert_forum_details($this->data['forums']['Unapproved Posts Test #1'], [
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 2,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 1,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Unapproved Posts Test Topic #1'],
		], 'before approving post');

		$this->add_lang('posting');
		$this->add_lang('viewtopic');
		$this->add_lang('mcp');

		// should be able to see topic 1 but not unapproved post
		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Unapproved Posts Test Topic #1']}&sid={$this->sid}");
		$this->assertStringContainsString('Unapproved Posts Test Topic #1', $crawler->filter('h2')->text());
		$this->assertStringNotContainsString('Re: Unapproved Posts Test Topic #1-#2', $crawler->filter('#page-body')->text());
		$this->assertStringNotContainsString('This post is not visible to other users until it has been approved', $crawler->filter('#page-body')->text());

		// should not be able to see topic 2
		$crawler = self::request('GET', "viewforum.php?f={$this->data['forums']['Unapproved Posts Test #1']}&sid={$this->sid}");
		$this->assertStringNotContainsString('Unapproved Posts Test Topic #2', $crawler->filter('html')->text());
		$this->logout();

		// another user
		$this->create_user('unapproved_posts_test_user#2');
		$this->login('unapproved_posts_test_user#2');

		$this->add_lang(['posting', 'viewtopic', 'mcp']);

		// should be able to see topic 1 but not unapproved post
		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Unapproved Posts Test Topic #1']}&sid={$this->sid}");
		$this->assertStringContainsString('Unapproved Posts Test Topic #1', $crawler->filter('h2')->text());
		$this->assertStringNotContainsString('Re: Unapproved Posts Test Topic #1-#2', $crawler->filter('#page-body')->text());
		$this->assertStringNotContainsString('This post is not visible to other users until it has been approved', $crawler->filter('#page-body')->text());

		// should not be able to see topic 2
		$crawler = self::request('GET', "viewforum.php?f={$this->data['forums']['Unapproved Posts Test #1']}&sid={$this->sid}");
		$this->assertStringNotContainsString('Unapproved Posts Test Topic #2', $crawler->filter('html')->text());
	}

	public function test_view_unapproved_post_enabled()
	{
		$this->config_display_unapproved_posts_state(true);

		// user who created post
		$this->login('unapproved_posts_test_user#1');
		$this->load_ids([
			'forums' => [
				'Unapproved Posts Test #1',
			],
			'topics' => [
				'Unapproved Posts Test Topic #1',
				'Unapproved Posts Test Topic #2',
			],
			'posts' => [
				'Unapproved Posts Test Topic #1',
				'Re: Unapproved Posts Test Topic #1-#2',
				'Unapproved Posts Test Topic #2',
			],
		]);

		$this->assert_forum_details($this->data['forums']['Unapproved Posts Test #1'], [
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 2,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 1,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Unapproved Posts Test Topic #1'],
		], 'before approving post');

		$this->add_lang('posting');
		$this->add_lang('viewtopic');
		$this->add_lang('mcp');

		// should be able to see topic 1 and unapproved post
		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Unapproved Posts Test Topic #1']}&sid={$this->sid}");
		$this->assertStringContainsString('Unapproved Posts Test Topic #1', $crawler->filter('h2')->text());
		$this->assertStringContainsString('Re: Unapproved Posts Test Topic #1-#2', $crawler->filter('#page-body')->text());
		$this->assertStringContainsString('This post is not visible to other users until it has been approved', $crawler->filter('#page-body')->text());

		// should be able to see topic 2
		$crawler = self::request('GET', "viewforum.php?f={$this->data['forums']['Unapproved Posts Test #1']}&sid={$this->sid}");
		$this->assertStringContainsString('Unapproved Posts Test Topic #2', $crawler->filter('html')->text());

		// should be able to see post in topic 2
		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Unapproved Posts Test Topic #2']}&sid={$this->sid}");
		$this->assertStringContainsString('Unapproved Posts Test Topic #2', $crawler->filter('#page-body')->text());
		$this->assertStringContainsString('This post is not visible to other users until it has been approved', $crawler->filter('#page-body')->text());
		$this->logout();

		// another user
		$this->login('unapproved_posts_test_user#2');

		$this->add_lang('posting');
		$this->add_lang('viewtopic');
		$this->add_lang('mcp');

		// should be able to see topic 1 but not unapproved post
		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Unapproved Posts Test Topic #1']}&sid={$this->sid}");
		$this->assertStringContainsString('Unapproved Posts Test Topic #1', $crawler->filter('h2')->text());
		$this->assertStringNotContainsString('Re: Unapproved Posts Test Topic #1-#2', $crawler->filter('#page-body')->text());
		$this->assertStringNotContainsString('This post is not visible to other users until it has been approved', $crawler->filter('#page-body')->text());

		// should not be able to see topic 2
		$crawler = self::request('GET', "viewforum.php?f={$this->data['forums']['Unapproved Posts Test #1']}&sid={$this->sid}");
		$this->assertStringNotContainsString('Unapproved Posts Test Topic #2', $crawler->filter('html')->text());
		$this->logout();
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

	protected function load_ids($data)
	{
		$this->db = $this->get_db();

		if (!empty($data['forums']))
		{
			$sql = 'SELECT forum_id, forum_name
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

	protected function config_display_unapproved_posts_state($state)
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?sid={$this->sid}&i=acp_board&mode=features");

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		// Enable display of unapproved posts to posters
		$values['config[display_unapproved_posts]'] = $state;

		$form->setValues($values);

		$crawler = self::submit($form);
		self::assertContainsLang('CONFIG_UPDATED', $crawler->filter('.successbox')->text());
		$this->logout();
	}
}
