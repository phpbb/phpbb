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
class phpbb_functional_feed_test extends phpbb_functional_test_case
{
	protected $data = array();

	static public $init_values = array();

	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

		$this->backupStaticAttributesBlacklist += array(
			'phpbb_functional_feed_test' => array('init_values'),
		);
	}

	public function test_setup_config_before_state()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?sid={$this->sid}&i=acp_board&mode=feed");

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		self::$init_values['post_base_items'] = (int) $values['config[feed_limit_post]'];
		self::$init_values['topic_base_items'] = (int) $values['config[feed_limit_topic]'];

		// Enable all feeds
		$values['config[feed_enable]'] = true;
		$values['config[feed_forum]'] = true;
		$values['config[feed_item_statistics]'] = true;
		$values['config[feed_overall]'] = true;
		$values['config[feed_overall_forums]'] = true;
		$values['config[feed_topic]'] = true;
		$values['config[feed_topics_active]'] = true;
		$values['config[feed_topics_new]'] = true;

		$form->setValues($values);

		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('.successbox')->text());

		// Special config (Guest can't see attachments)
		$this->add_lang('acp/permissions');

		$crawler = self::request('GET', "adm/index.php?i=acp_permissions&sid={$this->sid}&icat=16&mode=setting_group_global&group_id[0]=1");
		$this->assertContains($this->lang('ACL_SET'), $crawler->filter('h1')->eq(1)->text());

		$form = $crawler->selectButton($this->lang('APPLY_PERMISSIONS'))->form();
		$form['setting[1][0][u_download]']->select(-1);

		$crawler = self::submit($form);
		$this->assertContainsLang('AUTH_UPDATED', $crawler->filter('.successbox')->text());
	}

	public function test_dump_board_state()
	{
		$crawler = self::request('GET', 'feed.php?mode=forums', array(), false);
		self::assert_response_xml();
		self::$init_values['disapprove_user']['forums_value'] = $crawler->filterXPath('//entry')->count();

		$crawler = self::request('GET', 'feed.php?mode=overall', array(), false);
		self::assert_response_xml();
		self::$init_values['disapprove_user']['overall_value'] = $crawler->filterXPath('//entry')->count();

		$crawler = self::request('GET', 'feed.php?mode=topics', array(), false);
		self::assert_response_xml();
		self::$init_values['disapprove_user']['topics_value'] = $crawler->filterXPath('//entry')->count();

		$crawler = self::request('GET', 'feed.php?mode=topics_new', array(), false);
		self::assert_response_xml();
		self::$init_values['disapprove_user']['topics_new_value'] = $crawler->filterXPath('//entry')->count();

		$crawler = self::request('GET', 'feed.php?mode=topics_active', array(), false);
		self::assert_response_xml();
		self::$init_values['disapprove_user']['topics_active_value'] = $crawler->filterXPath('//entry')->count();

		$this->login();

		$crawler = self::request('GET', 'feed.php?mode=forums', array(), false);
		self::assert_response_xml();
		self::$init_values['admin']['forums_value'] = $crawler->filterXPath('//entry')->count();

		$crawler = self::request('GET', 'feed.php?mode=overall', array(), false);
		self::assert_response_xml();
		self::$init_values['admin']['overall_value'] = $crawler->filterXPath('//entry')->count();

		$crawler = self::request('GET', 'feed.php?mode=topics', array(), false);
		self::assert_response_xml();
		self::$init_values['admin']['topics_value'] = $crawler->filterXPath('//entry')->count();

		$crawler = self::request('GET', 'feed.php?mode=topics_new', array(), false);
		self::assert_response_xml();
		self::$init_values['admin']['topics_new_value'] = $crawler->filterXPath('//entry')->count();

		$crawler = self::request('GET', 'feed.php?mode=topics_active', array(), false);
		self::assert_response_xml();
		self::$init_values['admin']['topics_active_value'] = $crawler->filterXPath('//entry')->count();


	}

	public function test_setup_forums()
	{
		$this->login();
		$this->admin_login();
		$this->create_user('disapprove_user');
		$this->add_user_group('NEWLY_REGISTERED', array('disapprove_user'));

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Feeds #1',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		$crawler = self::submit($form);

		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
		));

		// 'Feeds #1.1' is a sub-forum of 'Feeds #1'
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&sid={$this->sid}&icat=6&mode=manage&parent_id={$this->data['forums']['Feeds #1']}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Feeds #1.1',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		$crawler = self::submit($form);

		// 'Feeds #news' will be used for feed.php?mode=news
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Feeds #news',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		$crawler = self::submit($form);

		// 'Feeds #exclude' will not be displayed on feed.php?mode=forums
		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Feeds #exclude',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
		));
		$crawler = self::submit($form);
	}

	public function test_setup_config_after_forums()
	{
		$this->login();
		$this->admin_login();

		$this->load_ids(array(
			'forums' => array(
				'Feeds #news',
				'Feeds #exclude',
			),
		));

		$crawler = self::request('GET', "adm/index.php?sid={$this->sid}&i=acp_board&mode=feed");

		$form = $crawler->selectButton('Submit')->form();

		// News/Exclude's forums config
		$form['feed_news_id']->select(array($this->data['forums']['Feeds #news']));
		$form['feed_exclude_id']->select(array($this->data['forums']['Feeds #exclude']));

		$crawler = self::submit($form);
		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('.successbox')->text());
	}

	public function test_feeds_empty()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
				'Feeds #1.1',
			),
		));

		// Excluded forums (and topics under them) shouldn't be displayed in feeds
		$this->assert_feeds(array(
			'f' => array(
				array(
					'id' => $this->data['forums']['Feeds #1'],
					'nb_entries' => 0,
				),
				array(
					'id' => $this->data['forums']['Feeds #1.1'],
					'nb_entries' => 0,
				),
			),
			'forums' => array(
				array(
					'nb_entries' => 3,
					'xpath' => array(
						'//entry/category[@label="Feeds #exclude"]' => 0,
					),
				),
			),
			'news' => array(
				array(
					'nb_entries' => 0,
				),
			),
		), 'admin');
	}

	public function test_create_exclude_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Feeds #exclude',
			),
		));

		$post = $this->create_topic($this->data['forums']['Feeds #exclude'], 'Feeds #exclude - Topic #1', 'This is a test topic posted by the testing framework.');
		$this->data['topics']['Feeds #exclude - Topic #1'] = (int) $post['topic_id'];
	}

	public function test_feeds_exclude()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #exclude',
			),
			'topics' => array(
				'Feeds #exclude - Topic #1',
			),
		));

		// Assert that feeds aren't available for excluded forums
		$this->assert_feeds(array(
			'f' => array(
				array(
					'id' => $this->data['forums']['Feeds #exclude'],
					'contents_lang' => array('NO_FEED'),
					'invalid' => true,
				),
			),
			't' => array(
				array(
					'id' => $this->data['topics']['Feeds #exclude - Topic #1'],
					'contents_lang' => array('NO_FEED'),
					'invalid' => true,
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 0,
					'xpath' => array(
						'//entry/title[contains(., "#exclude")]' => 0,
					),
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 0,
					'xpath' => array(
						'//entry/title[contains(., "#exclude")]' => 0,
					),
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 0,
					'xpath' => array(
						'//entry/title[contains(., "#exclude")]' => 0,
					),
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 0,
					'xpath' => array(
						'//entry/title[contains(., "#exclude")]' => 0,
					),
				),
			),
		), 'admin');
	}

	public function test_create_news_topics()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Feeds #news',
			),
		));

		$post = $this->create_topic($this->data['forums']['Feeds #news'], 'Feeds #news - Topic #1', 'This is a test topic posted by the testing framework.');
		$this->data['topics']['Feeds #news - Topic #1'] = (int) $post['topic_id'];

		$post = $this->create_topic($this->data['forums']['Feeds #news'], 'Feeds #news - Topic #2', 'This is a test topic posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		$this->assertContains('Feeds #news - Topic #2', $crawler->filter('html')->text());
		$this->data['topics']['Feeds #news - Topic #2'] = (int) $post['topic_id'];
		$this->data['posts']['Feeds #news - Topic #2'] = (int) $this->get_parameter_from_link($crawler->filter('.post')->selectLink($this->lang('POST', '', ''))->link()->getUri(), 'p');

		// Test creating a reply
		$post2 = $this->create_post($this->data['forums']['Feeds #news'], $post['topic_id'], 'Re: Feeds #news - Topic #2', 'This is a test post posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");

		$this->assertContains('Re: Feeds #news - Topic #2', $crawler->filter('html')->text());
		$this->data['posts']['Re: Feeds #news - Topic #2'] = (int) $post2['post_id'];
	}

	public function test_feeds_news_admin()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #news',
			),
			'topics' => array(
				'Feeds #news - Topic #1',
				'Feeds #news - Topic #2',
			),
			'posts' => array(
				'Feeds #news - Topic #2',
			),
		));

		// Assert that the first post of the two topics are displayed in news feed
		$this->assert_feeds(array(
			'news' => array(
				array(
					'nb_entries' => 2,
					'contents' => array(
						1 => 'This is a test topic posted by the testing framework.',
						2 => 'This is a test topic posted by the testing framework.',
					),
				),
			),
			// News should also be displayed in other feeds
			'f' => array(
				array(
					'nb_entries' => 3,
					'id' => $this->data['forums']['Feeds #news'],
				),
			),
			't' => array(
				array(
					'nb_entries' => 1,
					'id' => $this->data['topics']['Feeds #news - Topic #1'],
				),
				array(
					'nb_entries' => 2,
					'id' => $this->data['topics']['Feeds #news - Topic #2'],
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 3,
					'xpath' => array(
						'//entry/title[contains(., "#news")]' => 3,
					),
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 2,
					'xpath' => array(
						'//entry/title[contains(., "#news")]' => 2,
					),
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 2,
					'xpath' => array(
						'//entry/title[contains(., "#news")]' => 2,
					),
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 2,
					'xpath' => array(
						'//entry/title[contains(., "#news")]' => 2,
					),
				),
			),
		), 'admin');
	}

	public function test_feeds_news_guest()
	{
		$this->load_ids(array(
			'posts' => array(
				'Feeds #news - Topic #2',
			),
		));

		// Assert that first post of the the two topics are displayed in news feed
		$this->assert_feeds(array(
			'news' => array(
				array(
					'nb_entries' => 2,
					'contents' => array(
						1 => 'This is a test topic posted by the testing framework.',
						2 => 'This is a test topic posted by the testing framework.',
					),
				),
			),
		));
	}

	public function test_create_sub_forum_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
				'Feeds #1.1',
			),
		));

		$post = $this->create_topic($this->data['forums']['Feeds #1'], 'Feeds #1 - Topic #1', 'This is a test topic posted by the testing framework.');
		$this->data['topics']['Feeds #1 - Topic #1'] = (int) $post['topic_id'];

		$post = $this->create_topic($this->data['forums']['Feeds #1.1'], 'Feeds #1.1 - Topic #1', 'This is a test topic posted by the testing framework.');
		$this->data['topics']['Feeds #1.1 - Topic #1'] = (int) $post['topic_id'];
	}

	public function test_feeds_sub_forum()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
		));

		// The topics of the sub-forum shouldn't be displayed
		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 1,
					'id' => $this->data['forums']['Feeds #1'],
				),
			),
		), 'admin');
	}

	public function test_create_softdelete_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
		));

		$post = $this->create_topic($this->data['forums']['Feeds #1'], 'Feeds #1 - Topic #2', 'This is a test topic posted by the testing framework.');
		$this->data['topics']['Feeds #1 - Topic #2'] = (int) $post['topic_id'];

		// Test creating a reply
		$post2 = $this->create_post($this->data['forums']['Feeds #1'], $post['topic_id'], 'Re: Feeds #1 - Topic #2', 'This is a test post posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");

		$this->assertContains('Re: Feeds #1 - Topic #2', $crawler->filter('html')->text());
		$this->data['posts']['Re: Feeds #1 - Topic #2'] = (int) $post2['post_id'];
	}

	public function test_softdelete_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #2',
			),
			'posts' => array(
				'Re: Feeds #1 - Topic #2',
			),
		));
		$this->add_lang('posting');

		$crawler = self::request('GET', "posting.php?mode=delete&f={$this->data['forums']['Feeds #1']}&p={$this->data['posts']['Re: Feeds #1 - Topic #2']}&sid={$this->sid}");
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_DELETED', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Feeds #1 - Topic #2']}&sid={$this->sid}");
		$this->assertContains($this->lang('POST_DISPLAY', '', ''), $crawler->text());
	}

	public function test_feeds_softdeleted_post_admin()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #2',
			),
		));

		// Assert that the soft-deleted post is marked as soft-delete for users that have the right to see it.
		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 3,
					'id' => $this->data['forums']['Feeds #1'],
					'contents_lang' => array(
						1 => 'POST_DELETED',
					),
				),
			),
			't' => array(
				array(
					'nb_entries' => 2,
					'id' => $this->data['topics']['Feeds #1 - Topic #2'],
					'contents_lang' => array(
						1 => 'POST_DELETED',
					),
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 7,
					'contents_lang' => array(
						1 => 'POST_DELETED',
					),
				),
			),
		), 'admin');
	}

	public function test_feeds_softdeleted_post_guest()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #2',
			),
		));

		// Assert that the soft-deleted post is marked as soft-delete for users that have the right to see it.
		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 2,
					'id' => $this->data['forums']['Feeds #1'],
				),
			),
			't' => array(
				array(
					'nb_entries' => 1,
					'id' => $this->data['topics']['Feeds #1 - Topic #2'],
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 6,
				),
			),
		));
	}

	public function test_softdelete_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #2',
			),
		));

		$this->add_lang('posting');
		$crawler = $this->get_quickmod_page($this->data['topics']['Feeds #1 - Topic #2'], 'DELETE_TOPIC');
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$this->add_lang('mcp');
		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_DELETED_SUCCESS', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Feeds #1 - Topic #2']}&sid={$this->sid}");
		$this->assertContains('Feeds #1 - Topic #2', $crawler->filter('h2')->text());
	}

	public function test_feeds_softdeleted_topic_admin()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #2',
			),
		));

		// Assert that the soft-deleted post is marked as soft-delete for users that have the right to see it.
		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 3,
					'id' => $this->data['forums']['Feeds #1'],
					'contents_lang' => array(
						1 => 'POST_DELETED',
						2 => 'POST_DELETED',
					),
				),
			),
			't' => array(
				array(
					'nb_entries' => 2,
					'id' => $this->data['topics']['Feeds #1 - Topic #2'],
					'contents_lang' => array(
						1 => 'POST_DELETED',
						2 => 'POST_DELETED',
					),
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 7,
					'contents_lang' => array(
						1 => 'POST_DELETED',
						2 => 'POST_DELETED',
					),
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 5,
					'contents_lang' => array(
						1 => 'TOPIC_DELETED',
					),
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 5,
					'contents_lang' => array(
						1 => 'TOPIC_DELETED',
					),
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 5,
					'contents_lang' => array(
						1 => 'TOPIC_DELETED',
					),
				),
			),
		), 'admin');
	}

	public function test_feeds_softdeleted_topic_guest()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #2',
			),
		));

		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 1,
					'id' => $this->data['forums']['Feeds #1'],
				),
			),
			't' => array(
				array(
					'id' => $this->data['topics']['Feeds #1 - Topic #2'],
					'contents_lang' => array('SORRY_AUTH_READ'),
					'invalid' => true,
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 5,
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 4,
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 4,
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 4,
				),
			),
		));
	}

	public function test_create_unapproved_post()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1.1',
			),
		));

		$this->login('admin');
		$post = $this->create_topic($this->data['forums']['Feeds #1.1'], 'Feeds #1.1 - Topic #2', 'This is a test topic posted by the testing framework.');
		$this->data['topics']['Feeds #1.1 - Topic #2'] = (int) $post['topic_id'];
		$this->logout();

		// Test creating a reply
		$this->login('disapprove_user');
		$post2 = $this->create_post($this->data['forums']['Feeds #1.1'], $post['topic_id'], 'Re: Feeds #1.1 - Topic #2', 'This is a test post posted by the testing framework.', array(), 'POST_STORED_MOD');

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Feeds #1.1 - Topic #2']}&sid={$this->sid}");
		$this->assertNotContains('Re: Feeds #1.1 - Topic #2', $crawler->filter('html')->text());
	}

	public function test_feeds_unapproved_post_admin()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1.1',
			),
			'topics' => array(
				'Feeds #1.1 - Topic #2',
			),
		));

		// Assert that the unapproved post is marked as unapproved for users that have the right to see it.
		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 3,
					'id' => $this->data['forums']['Feeds #1.1'],
					'contents_lang' => array(
						1 => 'POST_UNAPPROVED',
					),
				),
			),
			't' => array(
				array(
					'nb_entries' => 2,
					'id' => $this->data['topics']['Feeds #1.1 - Topic #2'],
					'contents_lang' => array(
						1 => 'POST_UNAPPROVED',
					),
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 9,
					'contents_lang' => array(
						1 => 'POST_UNAPPROVED',
					),
				),
			),
		), 'admin');
	}

	public function test_feeds_unapproved_post_disapprove_user()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1.1',
			),
			'topics' => array(
				'Feeds #1.1 - Topic #2',
			),
		));

		// Assert that the unapproved isn't displayed for regular users
		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 2,
					'id' => $this->data['forums']['Feeds #1.1'],
				),
			),
			't' => array(
				array(
					'nb_entries' => 1,
					'id' => $this->data['topics']['Feeds #1.1 - Topic #2'],
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 6,
				),
			),
		), 'disapprove_user');
	}

	public function test_create_unapproved_topic()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1.1',
			),
		));
		$this->set_flood_interval(0);

		$this->login('disapprove_user');
		$post = $this->create_topic($this->data['forums']['Feeds #1.1'], 'Feeds #1.1 - Topic #3', 'This is a test topic posted by the testing framework.', array(), 'POST_STORED_MOD');
		$this->data['topics']['Feeds #1 - Topic #3'] = (int) $post['topic_id'];
		$crawler = self::request('GET', "viewforum.php?f={$this->data['forums']['Feeds #1.1']}&sid={$this->sid}");

		$this->assertNotContains('Feeds #1.1 - Topic #3', $crawler->filter('html')->text());

		$this->logout();
		$this->set_flood_interval(15);
	}

	protected function set_flood_interval($flood_interval)
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', 'adm/index.php?sid=' . $this->sid . '&i=acp_board&mode=post');

		$form = $crawler->selectButton('Submit')->form();
		$values = $form->getValues();

		$values["config[flood_interval]"] = $flood_interval;
		$form->setValues($values);
		$crawler = self::submit($form);
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());

		$this->logout();
	}

	public function test_feeds_unapproved_topic_admin()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1.1',
			),
			'topics' => array(
				'Feeds #1.1 - Topic #3',
			),
		));

		// Assert that the unapproved topic is marked as unapproved for users that have the right to see it.
		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 4,
					'id' => $this->data['forums']['Feeds #1.1'],
					'contents_lang' => array(
						1 => 'POST_UNAPPROVED',
					),
				),
			),
			't' => array(
				array(
					'nb_entries' => 1,
					'id' => $this->data['topics']['Feeds #1.1 - Topic #3'],
					'contents_lang' => array(
						1 => 'POST_UNAPPROVED',
					),
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 10,
					'contents_lang' => array(
						1 => 'POST_UNAPPROVED',
					),
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 7,
					'contents_lang' => array(
						1 => 'TOPIC_UNAPPROVED',
					),
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 7,
					'contents_lang' => array(
						1 => 'TOPIC_UNAPPROVED',
					),
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 7,
					'contents_lang' => array(
						1 => 'TOPIC_UNAPPROVED',
					),
				),
			),
		), 'admin');
	}

	public function test_feeds_unapproved_topic_disapprove_user()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1.1',
			),
			'topics' => array(
				'Feeds #1.1 - Topic #3',
			),
		));

		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 2,
					'id' => $this->data['forums']['Feeds #1.1'],
				),
			),
			't' => array(
				array(
					'id' => $this->data['topics']['Feeds #1.1 - Topic #3'],
					'contents_lang' => array('SORRY_AUTH_READ'),
					'invalid' => true,
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 6,
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 5,
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 5,
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 5,
				),
			),
		), 'disapprove_user');
	}

	public function test_create_attachment_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
		));

		// Test creating a topic with 1 attachment
		$post = $this->create_topic($this->data['forums']['Feeds #1'], 'Feeds #1 - Topic #3', 'This is a test topic posted by the testing framework. [attachment=0]Attachment #0[/attachment]', array('upload_files' => 1));
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		$this->assertContains('Feeds #1 - Topic #3', $crawler->filter('html')->text());
		$this->data['topics']['Feeds #1 - Topic #3'] = (int) $post['topic_id'];
	}

	public function test_feeds_attachment_admin()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #3',
			),
			'posts' => array(
				'Feeds #1 - Topic #3',
			),
			'attachments' => true,
		));

		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 4,
					'id' => $this->data['forums']['Feeds #1'],
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => true,
							),
						),
					),
				),
			),
			't' => array(
				array(
					'nb_entries' => 1,
					'id' => $this->data['topics']['Feeds #1 - Topic #3'],
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => true,
							),
						),
					),
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 11,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => true,
							),
						),
					),
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 8,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => true,
							),
						),
					),
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 8,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => true,
							),
						),
					),
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 8,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => true,
							),
						),
					),
				),
			),
		), 'admin');
	}

	public function test_feeds_attachment_guest()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #3',
			),
			'posts' => array(
				'Feeds #1 - Topic #3',
			),
			'attachments' => true,
		));

		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 2,
					'id' => $this->data['forums']['Feeds #1'],
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => false,
							),
						),
					),
				),
			),
			't' => array(
				array(
					'nb_entries' => 1,
					'id' => $this->data['topics']['Feeds #1 - Topic #3'],
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => false,
							),
						),
					),
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 7,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => false,
							),
						),
					),
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 6,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => false,
							),
						),
					),
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 6,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => false,
							),
						),
					),
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 6,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => false,
							),
						),
					),
				),
			),
		));
	}

	public function test_create_missing_attachment_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #3',
			),
		));

		// Test creating a reply with 1 missing attachment
		$post2 = $this->create_post($this->data['forums']['Feeds #1'], $this->data['topics']['Feeds #1 - Topic #3'], 'Re: Feeds #1 - Topic #3-1', 'This is a test post posted by the testing framework. [attachment=0]Attachment #0[/attachment]');
		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");

		$this->assertContains('Re: Feeds #1 - Topic #3-1', $crawler->filter('html')->text());
		$this->data['posts']['Re: Feeds #1 - Topic #3-1'] = (int) $post2['post_id'];
	}

	public function test_feeds_missing_attachment_admin()
	{
		$this->load_ids(array(
			'forums' => array(
				'Feeds #1',
			),
			'topics' => array(
				'Feeds #1 - Topic #3',
			),
			'posts' => array(
				'Feeds #1 - Topic #3',
			),
		));

		$this->add_lang('viewtopic');

		$this->assert_feeds(array(
			'f' => array(
				array(
					'nb_entries' => 5,
					'id' => $this->data['forums']['Feeds #1'],
					'contents' => array(
						1 => 'Attachment #0',
					),
				),
			),
			't' => array(
				array(
					'nb_entries' => 2,
					'id' => $this->data['topics']['Feeds #1 - Topic #3'],
					'contents' => array(
						1 => 'Attachment #0',
					),
				),
			),
			'overall' => array(
				array(
					'nb_entries' => 12,
					'contents' => array(
						1 => 'Attachment #0',
					),
				),
			),
			'topics' => array(
				array(
					'nb_entries' => 8,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => true,
							),
						),
					),
				),
			),
			'topics_new' => array(
				array(
					'nb_entries' => 8,
					'attachments' => array(
						1 => array( // First entry
							array( // First attachment to fetch
								'id' => $this->data['attachments'][$this->data['posts']['Feeds #1 - Topic #3']][0],
								'displayed' => true,
							),
						),
					),
				),
			),
			'topics_active' => array(
				array(
					'nb_entries' => 8,
					'contents' => array(
						1 => 'Attachment #0',
					),
				),
			),
		), 'admin');
	}

	protected function assert_feeds($data, $username = false)
	{
		if ($username)
		{
			$this->login($username);
			$init_values = self::$init_values[$username];
		}
		else
		{
			$init_values = self::$init_values['disapprove_user'];
		}

		foreach ($data as $mode => $feeds)
		{
			foreach ($feeds as $feed_data)
			{
				if ($mode === 'f' || $mode === 't')
				{
					$params = "?{$mode}={$feed_data['id']}";
					$this->assert_feed($params, $feed_data);
				}
				else
				{
					switch ($mode) {
						case 'forums':
							$feed_data['nb_entries'] = ((int)$feed_data['nb_entries'] + $init_values['forums_value']);
							break;
						case 'overall':
							$feed_data['nb_entries'] = min($feed_data['nb_entries'] + $init_values['overall_value'], self::$init_values['post_base_items']);
							break;
						case 'topics':
							$feed_data['nb_entries'] = min($feed_data['nb_entries'] + $init_values['topics_value'],  self::$init_values['topic_base_items']);
							break;
						case 'topics_new':
							$feed_data['nb_entries'] = min($feed_data['nb_entries'] + $init_values['topics_new_value'],  self::$init_values['topic_base_items']);
							break;
						case 'topics_active':
							$feed_data['nb_entries'] = min($feed_data['nb_entries'] + $init_values['topics_active_value'], self::$init_values['topic_base_items']);
							break;
						case 'news':
							break;
						default:
							$this->fail('Unsupported feed mode: ' . $mode);
					}

					$params = "?mode={$mode}";
					$this->assert_feed($params, $feed_data);
				}
			}
		}
	}

	protected function assert_feed($params, $data)
	{
		$crawler = self::request('GET', 'feed.php' . $params, array(), false);

		if (empty($data['invalid']))
		{
			self::assert_response_xml();
			$this->assertEquals($data['nb_entries'], $crawler->filter('entry')->count(), "Tested feed : 'feed.php{$params}'");

			if (!empty($data['xpath']))
			{

				foreach($data['xpath'] as $xpath => $count_expected)
				{
					$this->assertCount($count_expected, $crawler->filterXPath($xpath), "Tested feed : 'feed.php{$params}', Search for {$xpath}");
				}
			}

			if (!empty($data['contents']))
			{
				foreach($data['contents'] as $entry_id => $string)
				{
					$content = $crawler->filterXPath("//entry[{$entry_id}]/content")->text();
					$this->assertContains($string, $content, "Tested feed : 'feed.php{$params}'");
				}
			}

			if (!empty($data['contents_lang']))
			{
				foreach($data['contents_lang'] as $entry_id => $string)
				{
					$content = $crawler->filterXPath("//entry[{$entry_id}]/content")->text();
					$this->assertContainsLang($string, $content, "Tested feed : 'feed.php{$params}'");
				}
			}

			if (!empty($data['attachments']))
			{
				foreach($data['attachments'] as $entry_id => $attachments)
				{
					foreach ($attachments as $i => $attachment)
					{
						$content = $crawler->filterXPath("//entry[{$entry_id}]/content")->text();
						$url = "./download/file.php?id={$attachment['id']}";
						$string = "Attachment #{$i}";

						if ($attachment['displayed'])
						{
							$this->assertContains($url, $content, "Tested feed : 'feed.php{$params}'");
							$this->assertNotContains($string, $content, "Tested feed : 'feed.php{$params}'");
						}
						else
						{
							$this->assertContains($string, $content, "Tested feed : 'feed.php{$params}'");
							$this->assertNotContains($url, $content, "Tested feed : 'feed.php{$params}'");
						}
					}
				}
			}
		}
		else
		{
			self::assert_response_html();

			if (!empty($data['contents_lang']))
			{
				foreach($data['contents_lang'] as $string)
				{
					$content = $crawler->filter('html')->text();
					$this->assertContainsLang($string, $content, "Tested feed : 'feed.php{$params}'");
				}
			}
		}
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

		$post_ids = array();
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
					$post_ids[] = (int) $row['post_id'];
				}
			}
			$this->db->sql_freeresult($result);

			if (isset($data['attachments']))
			{
				$sql = 'SELECT *
					FROM phpbb_attachments
					WHERE in_message = 0 AND ' . $this->db->sql_in_set('post_msg_id', $post_ids);
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->data['attachments'][(int) $row['post_msg_id']][] = (int) $row['attach_id'];
				}
				$this->db->sql_freeresult($result);
			}
		}
	}
}
