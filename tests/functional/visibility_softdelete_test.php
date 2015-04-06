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
class phpbb_functional_visibility_softdelete_test extends phpbb_functional_test_case
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

		// Create second user which does not have m_delete permission
		$this->add_lang('acp/permissions');

		$second_user = $this->create_user('no m_delete moderator');
		$this->add_user_group("GLOBAL_MODERATORS", 'no m_delete moderator', true);

		// Set m_delete to never
		$crawler = self::request('GET', "adm/index.php?i=acp_permissions&icat=16&mode=setting_user_global&user_id[0]=$second_user&type=m_&sid={$this->sid}");
		$form = $crawler->selectButton($this->lang('APPLY_PERMISSIONS'))->form();
		$data = array("setting[$second_user][0][m_delete]" => ACL_NEVER);
		$form->setValues($data);
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
		$this->data['posts']['Re: Soft Delete Topic #1-#2'] = (int) $post2['post_id'];

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Soft Delete Topic #1-#2'],
		), 'after replying');

		// Test creating another reply
		$post3 = $this->create_post($this->data['forums']['Soft Delete #1'], $post['topic_id'], 'Re: Soft Delete Topic #1-#3', 'This is another test post posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post3['topic_id']}&sid={$this->sid}");

		$this->assertContains('Re: Soft Delete Topic #1-#3', $crawler->filter('html')->text());
		$this->data['posts']['Re: Soft Delete Topic #1-#3'] = (int) $post3['post_id'];

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 3,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Soft Delete Topic #1-#3'],
		), 'after replying a second time');
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
				'Re: Soft Delete Topic #1-#3',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 3,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Soft Delete Topic #1-#3'],
		), 'before softdelete');

		$this->add_lang('posting');
		$crawler = self::request('GET', "posting.php?mode=delete&f={$this->data['forums']['Soft Delete #1']}&p={$this->data['posts']['Re: Soft Delete Topic #1-#3']}&sid={$this->sid}");
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_DELETED', $crawler->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 1,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Soft Delete Topic #1-#2'],
		), 'after softdelete');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");
		$this->assertContains($this->lang('POST_DISPLAY', '', ''), $crawler->text());
	}

	public function test_softdelete_post_no_m_delete()
	{
		$this->login('no m_delete moderator');
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
				'Re: Soft Delete Topic #1-#3',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 1,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Soft Delete Topic #1-#2'],
		), 'before softdelete without m_delete');

		$this->add_lang('posting');
		$crawler = self::request('GET', "posting.php?mode=delete&f={$this->data['forums']['Soft Delete #1']}&p={$this->data['posts']['Re: Soft Delete Topic #1-#2']}&sid={$this->sid}");
		$this->assertNotContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_DELETED', $crawler->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'after softdelete without m_delete');

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
				'Re: Soft Delete Topic #1-#3',
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
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

		$crawler = $this->get_quickmod_page($this->data['topics']['Soft Delete Topic #1'], 'MOVE_TOPIC');
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
			'forum_posts_softdeleted'	=> 2,
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
				'Re: Soft Delete Topic #1-#3'
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
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'before softdeleting #2');

		$this->add_lang('posting');
		$crawler = $this->get_quickmod_page($this->data['topics']['Soft Delete Topic #1'], 'DELETE_TOPIC');
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
			'forum_posts_softdeleted'	=> 3,
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
				'Re: Soft Delete Topic #1-#3'
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
			'forum_posts_softdeleted'	=> 3,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 1,
			'forum_last_post_id'		=> 0,
		), 'before moving #2');

		$crawler = $this->get_quickmod_page($this->data['topics']['Soft Delete Topic #1'], 'MOVE_TOPIC');
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
			'forum_posts_softdeleted'	=> 3,
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
				'Re: Soft Delete Topic #1-#3'
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 3,
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
		$form = $crawler->filter('#p' . $this->data['posts']['Soft Delete Topic #1'])->selectButton($this->lang('RESTORE'))->form();
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
			'forum_posts_softdeleted'	=> 2,
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

	public function test_split_topic()
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
				'Re: Soft Delete Topic #1-#3'
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'before splitting #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'before splitting #2');

		$crawler = $this->get_quickmod_page($this->data['topics']['Soft Delete Topic #1'], 'SPLIT_TOPIC');

		$this->add_lang('mcp');
		$this->assertContainsLang('SPLIT_TOPIC_EXPLAIN', $crawler->text());

		$form = $crawler->selectButton('Submit')->form(array(
			'subject'			=> 'Soft Delete Topic #2',
		));
		$form['to_forum_id']->select($this->data['forums']['Soft Delete #2']);
		$form['post_id_list'][1]->tick();
		$crawler = self::submit($form);

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_SPLIT_SUCCESS', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");
		$this->assertContains('Soft Delete Topic #1', $crawler->filter('h2')->text());
		$this->assertNotContains('Re: Soft Delete Topic #1-#2', $crawler->text());

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
			'forum_posts_softdeleted'	=> 1,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 1,
			'forum_last_post_id'		=> 0,
		), 'after restoring #2');
	}

	public function test_move_topic_back()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Soft Delete #1',
				'Soft Delete #2',
			),
			'topics' => array(
				'Soft Delete Topic #1',
				'Soft Delete Topic #2',
			),
			'posts' => array(
				'Soft Delete Topic #1',
				'Re: Soft Delete Topic #1-#2',
				'Re: Soft Delete Topic #1-#3'
			),
		));

		$crawler = $this->get_quickmod_page($this->data['topics']['Soft Delete Topic #2'], 'MOVE_TOPIC');
		$form = $crawler->selectButton('Yes')->form();
		$form['to_forum_id']->select($this->data['forums']['Soft Delete #1']);
		$crawler = self::submit($form);

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 1,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'after moving back');
	}

	public function test_merge_topics()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Soft Delete #1',
				'Soft Delete #2',
			),
			'topics' => array(
				'Soft Delete Topic #1',
				'Soft Delete Topic #2',
			),
			'posts' => array(
				'Soft Delete Topic #1',
				'Re: Soft Delete Topic #1-#2',
				'Re: Soft Delete Topic #1-#3'
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 1,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'before merging #1');

		$this->add_lang('viewtopic');
		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #2']}&sid={$this->sid}");

		$bookmark_tag = $crawler->filter('a.bookmark-link');
		$this->assertContainsLang('BOOKMARK_TOPIC', $bookmark_tag->text());
		$bookmark_link = $bookmark_tag->attr('href');
		$crawler_bookmark = self::request('GET', $bookmark_link);
		$this->assertContainsLang('BOOKMARK_ADDED', $crawler_bookmark->text());

		$this->add_lang('mcp');
		$crawler = $this->get_quickmod_page($this->data['topics']['Soft Delete Topic #2'], 'MERGE_TOPIC', $crawler); 
		$this->assertContainsLang('SELECT_MERGE', $crawler->text());

		$crawler = self::request('GET', "mcp.php?f={$this->data['forums']['Soft Delete #1']}&t={$this->data['topics']['Soft Delete Topic #2']}&i=main&mode=forum_view&action=merge_topic&to_topic_id={$this->data['topics']['Soft Delete Topic #1']}");
		$this->assertContainsLang('MERGE_TOPICS_CONFIRM', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POSTS_MERGED_SUCCESS', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Soft Delete Topic #1']}&sid={$this->sid}");
		$this->assertContains('Soft Delete Topic #1', $crawler->filter('h2')->text());
		$this->assertContainsLang('POST_DELETED_ACTION', $crawler->filter('body')->text());
		$this->assertContainsLang('BOOKMARK_TOPIC_REMOVE', $crawler->filter('body')->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'after merging #1');
	}

	public function test_fork_topic()
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
				'Re: Soft Delete Topic #1-#3'
			),
		));

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'before forking #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'before forking #2');

		$this->add_lang('mcp');
		$crawler = $this->get_quickmod_page($this->data['topics']['Soft Delete Topic #1'], 'FORK_TOPIC');
		$this->assertContainsLang('FORK_TOPIC', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$form['to_forum_id']->select($this->data['forums']['Soft Delete #2']);
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_FORKED_SUCCESS', $crawler->text());

		$this->assert_forum_details($this->data['forums']['Soft Delete #1'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'],
		), 'after forking #1');

		$this->assert_forum_details($this->data['forums']['Soft Delete #2'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 2,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Soft Delete Topic #1'] + 3,
		), 'after forking #2');
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
