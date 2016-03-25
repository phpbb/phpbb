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
class phpbb_functional_prune_shadow_topic_test extends phpbb_functional_test_case
{
	protected $data = array();
	protected $post;

	public function test_setup_forums()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_forums&mode=manage&sid={$this->sid}");
		$form = $crawler->selectButton('addforum')->form(array(
			'forum_name'	=> 'Prune Shadow',
		));
		$crawler = self::submit($form);
		$form = $crawler->selectButton('update')->form(array(
			'forum_perm_from'	=> 2,
			'enable_shadow_prune'	=> true,
			'prune_shadow_freq'	=> 1,
			'prune_shadow_days'	=> 1,
		));
		$crawler = self::submit($form);
	}

	public function test_create_post()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Prune Shadow',
			),
		));

		$this->assert_forum_details($this->data['forums']['Prune Shadow'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> 0,
		), 'initial comparison');

		// Test creating topic
		$this->post = $this->create_topic($this->data['forums']['Prune Shadow'], 'Prune Shadow #1', 'This is a test topic posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$this->post['topic_id']}&sid={$this->sid}");

		$this->assertContains('Prune Shadow #1', $crawler->filter('html')->text());
		$this->data['topics']['Prune Shadow #1'] = (int) $this->post['topic_id'];
		$this->data['posts']['Prune Shadow #1'] = (int) $this->get_parameter_from_link($crawler->filter('.post')->selectLink($this->lang('POST', '', ''))->link()->getUri(), 'p');

		$this->assert_forum_details($this->data['forums']['Prune Shadow'], array(
			'forum_posts_approved'		=> 1,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Prune Shadow #1'],
		), 'after creating topic #1');

		// Test creating a reply
		$post2 = $this->create_post($this->data['forums']['Prune Shadow'], $this->post['topic_id'], 'Re: Prune Shadow #1-#2', 'This is a test post posted by the testing framework.');
		$crawler = self::request('GET', "viewtopic.php?t={$post2['topic_id']}&sid={$this->sid}");

		$this->assertContains('Re: Prune Shadow #1-#2', $crawler->filter('html')->text());
		$this->data['posts']['Re: Prune Shadow #1-#2'] = (int) $post2['post_id'];

		$this->assert_forum_details($this->data['forums']['Prune Shadow'], array(
			'forum_posts_approved'		=> 2,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
			'forum_last_post_id'		=> $this->data['posts']['Re: Prune Shadow #1-#2'],
		), 'after replying');
	}

	public function test_move_topic()
	{
		$this->login();
		$this->load_ids(array(
			'forums' => array(
				'Prune Shadow',
			),
			'topics' => array(
				'Prune Shadow #1',
			),
		));

		$crawler = self::request('GET', "mcp.php?f={$this->data['forums']['Prune Shadow']}&i=main&action=move&mode=forum_view&start=0&topic_id_list[]={$this->data['topics']['Prune Shadow #1']}&sid={$this->sid}");
		$form = $crawler->selectButton('confirm')->form(array(
			'to_forum_id'	=> 2,
			'move_leave_shadow' => true,
		));
		$crawler = self::submit($form);

		$this->assert_forum_details($this->data['forums']['Prune Shadow'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 1,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
		), 'after moving');

		$this->db = $this->get_db();
		// Date topic 3 days back
		$sql = 'UPDATE phpbb_topics
			SET topic_last_post_time = ' . (time() - 60*60*24*3) . '
			WHERE topic_id = ' . ($this->data['topics']['Prune Shadow #1'] + 1);
		$result = $this->db->sql_query($sql);

		$crawler = self::request('GET', "viewforum.php?f={$this->data['forums']['Prune Shadow']}&sid={$this->sid}");
		$this->assertNotEmpty($crawler->filter('img')->last()->attr('src'));
		self::request('GET', "cron.php?cron_type=cron.task.core.prune_shadow_topics&f={$this->data['forums']['Prune Shadow']}&sid={$this->sid}", array(), false);

		$this->assert_forum_details($this->data['forums']['Prune Shadow'], array(
			'forum_posts_approved'		=> 0,
			'forum_posts_unapproved'	=> 0,
			'forum_posts_softdeleted'	=> 0,
			'forum_topics_approved'		=> 0,
			'forum_topics_unapproved'	=> 0,
			'forum_topics_softdeleted'	=> 0,
		), 'after the cron job');
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
