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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_posting.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

/**
* @group functional
*/
class phpbb_functional_download_test extends phpbb_functional_test_case
{
	protected $data = array();

	public function test_setup_forums()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Download #1',
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
				'Download #1',
			),
		));

		// Test creating topic
		$post = $this->create_topic($this->data['forums']['Download #1'], 'Download Topic #1', 'This is a test topic posted by the testing framework.', array('upload_files' => 1));
		$crawler = self::request('GET', "viewtopic.php?t={$post['topic_id']}&sid={$this->sid}");

		$this->assertContains('Download Topic #1', $crawler->filter('html')->text());
		$this->data['topics']['Download Topic #1'] = (int) $post['topic_id'];
		$this->data['posts']['Download Topic #1'] = (int) $this->get_parameter_from_link($crawler->filter('.post')->selectLink($this->lang('POST', '', ''))->link()->getUri(), 'p');

		// Test creating a reply
		$post2 = $this->create_post($this->data['forums']['Download #1'], $post['topic_id'], 'Re: Download Topic #1-#2', 'This is a test post posted by the testing framework.', array('upload_files' => 1));
		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");

		$this->assertContains('Re: Download Topic #1-#2', $crawler->filter('html')->text());
		$this->data['posts']['Re: Download Topic #1-#2'] = (int) $post2['post_id'];
	}

	public function test_download_accessible()
	{
		if (!class_exists('finfo'))
		{
			$this->markTestSkipped('Unable to run test with fileinfo disabled');
		}

		$this->load_ids(array(
			'forums' => array(
				'Download #1',
			),
			'topics' => array(
				'Download Topic #1',
			),
			'posts' => array(
				'Download Topic #1',
				'Re: Download Topic #1-#2',
			),
			'attachments' => true,
		));

		// Download attachment as guest
		$crawler = self::request('GET', "download/file.php?id={$this->data['attachments'][$this->data['posts']['Re: Download Topic #1-#2']]}", array(), false);
		self::assert_response_status_code(200);
		$content = self::$client->getResponse()->getContent();
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		self::assertEquals('image/jpeg', $finfo->buffer($content));
	}

	public function test_softdelete_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Download #1',
			),
			'topics' => array(
				'Download Topic #1',
			),
			'posts' => array(
				'Download Topic #1',
				'Re: Download Topic #1-#2',
			),
		));
		$this->add_lang('posting');

		$crawler = self::request('GET', "posting.php?mode=delete&f={$this->data['forums']['Download #1']}&p={$this->data['posts']['Re: Download Topic #1-#2']}&sid={$this->sid}");
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('POST_DELETED', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Download Topic #1']}&sid={$this->sid}");
		$this->assertContains($this->lang('POST_DISPLAY', '', ''), $crawler->text());
	}

	public function test_download_softdeleted_post()
	{
		if (!class_exists('finfo'))
		{
			$this->markTestSkipped('Unable to run test with fileinfo disabled');
		}

		$this->load_ids(array(
			'forums' => array(
				'Download #1',
			),
			'topics' => array(
				'Download Topic #1',
			),
			'posts' => array(
				'Download Topic #1',
				'Re: Download Topic #1-#2',
			),
			'attachments' => true,
		));
		$this->add_lang('viewtopic');

		// No download attachment as guest
		$crawler = self::request('GET', "download/file.php?id={$this->data['attachments'][$this->data['posts']['Re: Download Topic #1-#2']]}", array(), false);
		self::assert_response_html(404);
		$this->assertContainsLang('ERROR_NO_ATTACHMENT', $crawler->filter('#message')->text());

		// Login as admin and try again, should work now.
		$this->login();

		// Download attachment as admin
		$crawler = self::request('GET', "download/file.php?id={$this->data['attachments'][$this->data['posts']['Re: Download Topic #1-#2']]}", array(), false);
		self::assert_response_status_code(200);
		$content = self::$client->getResponse()->getContent();
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		self::assertEquals('image/jpeg', $finfo->buffer($content));
	}

	public function test_softdelete_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Download #1',
			),
			'topics' => array(
				'Download Topic #1',
			),
			'posts' => array(
				'Download Topic #1',
				'Re: Download Topic #1-#2',
			),
		));

		$this->add_lang('posting');
		$crawler = $this->get_quickmod_page($this->data['topics']['Download Topic #1'], 'DELETE_TOPIC');
		$this->assertContainsLang('DELETE_PERMANENTLY', $crawler->text());

		$this->add_lang('mcp');
		$form = $crawler->selectButton('Yes')->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('TOPIC_DELETED_SUCCESS', $crawler->text());

		$crawler = self::request('GET', "viewtopic.php?t={$this->data['topics']['Download Topic #1']}&sid={$this->sid}");
		$this->assertContains('Download Topic #1', $crawler->filter('h2')->text());
	}

	public function test_download_softdeleted_topic()
	{
		if (!class_exists('finfo'))
		{
			$this->markTestSkipped('Unable to run test with fileinfo disabled');
		}

		$this->load_ids(array(
			'forums' => array(
				'Download #1',
			),
			'topics' => array(
				'Download Topic #1',
			),
			'posts' => array(
				'Download Topic #1',
				'Re: Download Topic #1-#2',
			),
			'attachments' => true,
		));
		$this->add_lang('viewtopic');

		// No download attachment as guest
		$crawler = self::request('GET', "download/file.php?id={$this->data['attachments'][$this->data['posts']['Re: Download Topic #1-#2']]}", array(), false);
		self::assert_response_html(404);
		$this->assertContainsLang('ERROR_NO_ATTACHMENT', $crawler->filter('#message')->text());

		// Login as admin and try again, should work now.
		$this->login();

		// Download attachment as admin
		$crawler = self::request('GET', "download/file.php?id={$this->data['attachments'][$this->data['posts']['Re: Download Topic #1-#2']]}", array(), false);
		self::assert_response_status_code(200);
		$content = self::$client->getResponse()->getContent();
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		self::assertEquals('image/jpeg', $finfo->buffer($content));
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
					$this->data['attachments'][(int) $row['post_msg_id']] = (int) $row['attach_id'];
				}
				$this->db->sql_freeresult($result);
			}
		}
	}
}
