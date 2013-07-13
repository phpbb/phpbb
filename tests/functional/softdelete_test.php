<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @group functional
*/
class phpbb_functional_softdelete_test extends phpbb_functional_test_case
{
	protected $data = array();

	public function test_setup_forums()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Soft Delete #1',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		$crawler = self::submit($form);

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Soft Delete #2',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		$crawler = self::submit($form);
	}

	public function test_create_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Soft Delete #1',
				'Soft Delete #2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'initial comparison');

		// Test creating topic
		$post = $this->create_topic($this->data['forums']['Soft Delete #1'], 'Soft Delete Topic #1', 'This is a test topic posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		$this->assertContains('Soft Delete Topic #1', $crawler->filter('html')->text());
		$this->data['topics']['Soft Delete Topic #1'] = (int) $post['topic_id'];
		$this->data['posts']['Soft Delete Topic #1'] = (int) $this->get_parameter_from_link($crawler->filter('.post')->selectLink($this->lang('POST', '', ''))->link()->getUri(), 'p');

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'after creating topic #1');

		// Test creating a reply
		$post2 = $this->create_post($this->data['forums']['Soft Delete #1'], $post['topic_id'], 'Re: Soft Delete Topic #1-#2', 'This is a test post posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");

		$this->assertContains('Re: Soft Delete Topic #1-#2', $crawler->filter('html')->text());
		$this->data['posts']['Re: Soft Delete Topic #1-#2'] = (int) $this->get_parameter_from_link($crawler->filter('.post')->eq(1)->selectLink($this->lang('POST', '', ''))->link()->getUri(), 'p');

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Soft Delete Topic #1-#2'],
		), 'after replying');
	}

	public function test_softdelete_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Soft Delete #1',
				'Soft Delete #2',
			),
			'topics' => array(
				'Soft Delete Topic #1',
			),
			'posts' => array(
				'Soft Delete Topic #1',
				'Re: Soft Delete Topic #1-#2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Soft Delete Topic #1-#2'],
		), 'before softdelete');

		$this->add_lang('posting');
		$crawler = self::request('GET', "posting.php?mode=delete&f={$this->data['forums']['Soft Delete #1']}&p={$this->data['posts']['Re: Soft Delete Topic #1-#2']}&sid={$this->sid}");
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_DELETED', $crawler->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 1,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'after softdelete');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");
		$this->assertContains($this->lang('POST_DISPLAY', '', ''), $crawler->text());
	}

	public function test_move_softdeleted_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Soft Delete #1',
				'Soft Delete #2',
			),
			'topics' => array(
				'Soft Delete Topic #1',
			),
			'posts' => array(
				'Soft Delete Topic #1',
				'Re: Soft Delete Topic #1-#2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 1,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'before moving #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'before moving #2');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");

		$form = $crawler->selectButton('Go')->eq(2)->form();
		$form['action']->select('move');
		$crawler = self::submit($form);
		$this->assertContainsLang('SELECT_DESTINATION_FORUM', $crawler->text());

		$this->add_lang('mcp');
		$form = $crawler->selectButton('Yes')->form();
		$form['to_forum_id']->select($this->data['forums']['Soft Delete #2']);
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_MOVED_SUCCESS', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");
		$this->assertContains('Soft Delete #2', $crawler->filter('.navlinks')->text());
		$this->assertContains('Soft Delete Topic #1', $crawler->filter('h2')->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'after moving #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 1,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'after moving #2');
	}

	public function test_softdelete_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Soft Delete #1',
				'Soft Delete #2',
			),
			'topics' => array(
				'Soft Delete Topic #1',
			),
			'posts' => array(
				'Soft Delete Topic #1',
				'Re: Soft Delete Topic #1-#2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'before softdeleting #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 1,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'before softdeleting #2');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");

		$this->add_lang('posting');
		$form = $crawler->selectButton('Go')->eq(2)->form();
		$form['action']->select('delete_topic');
		$crawler = self::submit($form);
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$this->add_lang('mcp');
		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_DELETED_SUCCESS', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");
		$this->assertContains('Soft Delete #2', $crawler->filter('.navlinks')->text());
		$this->assertContains('Soft Delete Topic #1', $crawler->filter('h2')->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'after moving #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 1,
			'forum_last_post_id'		=> 0,
		), 'after moving #2');
	}

	public function test_move_softdeleted_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Soft Delete #1',
				'Soft Delete #2',
			),
			'topics' => array(
				'Soft Delete Topic #1',
			),
			'posts' => array(
				'Soft Delete Topic #1',
				'Re: Soft Delete Topic #1-#2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'before moving #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 1,
			'forum_last_post_id'		=> 0,
		), 'before moving #2');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");

		$form = $crawler->selectButton('Go')->eq(2)->form();
		$form['action']->select('move');
		$crawler = self::submit($form);
		$this->assertContainsLang('SELECT_DESTINATION_FORUM', $crawler->text());

		$this->add_lang('mcp');
		$form = $crawler->selectButton('Yes')->form();
		$form['to_forum_id']->select($this->data['forums']['Soft Delete #1']);
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_MOVED_SUCCESS', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");
		$this->assertContains('Soft Delete #1', $crawler->filter('.navlinks')->text());
		$this->assertContains('Soft Delete Topic #1', $crawler->filter('h2')->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 1,
			'forum_last_post_id'		=> 0,
		), 'after moving #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'after moving #2');
	}

	public function test_restore_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Soft Delete #1',
				'Soft Delete #2',
			),
			'topics' => array(
				'Soft Delete Topic #1',
			),
			'posts' => array(
				'Soft Delete Topic #1',
				'Re: Soft Delete Topic #1-#2',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 1,
			'forum_last_post_id'		=> 0,
		), 'before restoring #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'before restoring #2');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");

		$this->add_lang('mcp');
		$form = $crawler->selectButton($this->lang('RESTORE'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('RESTORE_POST', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_RESTORED_SUCCESS', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");
		$this->assertContains('Soft Delete #1', $crawler->filter('.navlinks')->text());
		$this->assertContains('Soft Delete Topic #1', $crawler->filter('h2')->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 1,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'after restoring #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'after restoring #2');
	}

	public function assert_forum_details($forum_id, $details, $additional_error_message = '')
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

	public function load_ids($data)
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
