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

namespace phpbb\db\migration\data\v330;

class migrate_drafts_table extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v330\dev');
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'move_drafts'], ]],
		];
	}

	public function move_drafts()
	{
		define('MAX_ITEMS_PER_ITERATION', 100);

		$draft_id = 0;
		$more = true;

		while ($more)
		{
			$sql = 'SELECT *
				FROM ' . $this->table_prefix . "drafts d WHERE d.draft_id > $draft_id LIMIT " . MAX_ITEMS_PER_ITERATION;
			$result = $this->db->sql_query($sql);
			$rows = $this->db->sql_fetchrowset($result);
			$this->db->sql_freeresult($result);
			{
				foreach ($rows as $row)
				{
					if (($row['forum_id'] == 0) && ($row['topic_id'] == 0))
					{
						$this->insert_draft_pm($row);
					}
					else
					{
						$this->insert_draft_post($row);
					}
					$draft_id = $row['draft_id'];
				}
				if (count($rows) < MAX_ITEMS_PER_ITERATION)
				{
					$more = false;
				}
			}
		}
	}

	public function insert_draft_pm($row)
	{

		$pm_data = [
			'author_id'				=> (int) $row['user_id'],
			'message_time'			=> (int) $row['save_time'],
			'icon_id'				=> 0,
			'message_subject'		=> $row['draft_subject'],
			'message_text'			=> $row['draft_message'],
			'to_address'			=> '',
			'bcc_address'			=> '',
		];
		$this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'privmsgs ' . $this->db->sql_build_array('INSERT', $pm_data));
		$pm_data['msg_id'] = $this->db->sql_nextid();

		// for drafts, the statuses pm_replied pm_replied etc describe how the message was created, not actions after it was sent
		$this->db->sql_query('INSERT INTO ' . $this->table_prefix . 'privmsgs_to ' . $this->db->sql_build_array('INSERT', array(
			'msg_id'		=> (int) $pm_data['msg_id'],
			'author_id'		=> (int) $row['user_id'],
			'user_id'		=> (int) $row['user_id'],
			'folder_id'		=> PRIVMSGS_DRAFTBOX,
			'pm_forwarded'	=> 0,
			'pm_replied'	=> 0))
		);
	}

	public function insert_draft_post($row)
	{
		$forum_id = 0;

		if ($row['topic_id'])
		{
			// Check if topic exists
			$sql = 'SELECT forum_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . $row['topic_id'];
			$result = $this->db->sql_query($sql);
			$topic_forum_id = $this->db->sql_fetchfield('forum_id');
			$this->db->sql_freeresult($result);

			if (!empty($topic_forum_id))
			{
				// use this forum_id, in case the topic was moved
				$forum_id = (int) $topic_forum_id;
			}
		}

		// if topic not found, assume new topic, but check that the given forum exists
		if (!$forum_id && $row['forum_id'])
		{
			$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $row['forum_id'];
			$result = $this->db->sql_query($sql);
			$src_forum_name = $this->db->sql_fetchfield('forum_name');
			$this->db->sql_freeresult($result);

			if (!empty($src_forum_name))
			{
				$forum_id = (int) $row['forum_id'];
			}
		}

		if ($forum_id) // discard draft post if forum not found
		{
			if ($row['topic_id'])
			{
				// existing topic
				$topic_id = (int) $row['topic_id'];
			}
			else
			{
				//new topic
				$sql_data_topic = [
					'forum_id'					=> $forum_id,
					'icon_id'					=> 0,
					'topic_title'				=> $row['draft_subject'],
					'topic_type'				=> POST_NORMAL,
					'topic_time_limit'			=> 0,
					'topic_attachment'			=> 0,
					'topic_visibility'			=> ITEM_DRAFT,
				];

				$sql = 'INSERT INTO ' . TOPICS_TABLE . ' ' .
					$this->db->sql_build_array('INSERT', $sql_data_topic);
				$this->db->sql_query($sql);

				$topic_id = $this->db->sql_nextid();
			}

			// Submit new post
			$sql_data_post = [
				'forum_id'			=> $forum_id,
				'topic_id'  		=> $topic_id,
				'poster_id'			=> (int) $row['user_id'],
				'post_time'			=> (int) $row['save_time'],
				'icon_id'			=> 0,
				'post_visibility'	=> ITEM_DRAFT,
				'post_subject'		=> $row['draft_subject'],
				'post_text'			=> $row['draft_message'],
				'post_attachment'	=> 0,
				'post_edit_locked'	=> 0,
			];
			$sql = 'INSERT INTO ' . POSTS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_data_post);
			$this->db->sql_query($sql);
		}
	}
}
