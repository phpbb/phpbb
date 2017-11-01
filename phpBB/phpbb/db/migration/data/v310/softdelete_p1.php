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

namespace phpbb\db\migration\data\v310;

class softdelete_p1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'posts', 'post_visibility');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'forums'		=> array(
					'forum_posts_approved'		=> array('UINT', 0),
					'forum_posts_unapproved'	=> array('UINT', 0),
					'forum_posts_softdeleted'	=> array('UINT', 0),
					'forum_topics_approved'		=> array('UINT', 0),
					'forum_topics_unapproved'	=> array('UINT', 0),
					'forum_topics_softdeleted'	=> array('UINT', 0),
				),
				$this->table_prefix . 'posts'		=> array(
					'post_visibility'		=> array('TINT:3', 0),
					'post_delete_time'		=> array('TIMESTAMP', 0),
					'post_delete_reason'	=> array('STEXT_UNI', ''),
					'post_delete_user'		=> array('UINT', 0),
				),
				$this->table_prefix . 'topics'		=> array(
					'topic_visibility'		=> array('TINT:3', 0),
					'topic_delete_time'		=> array('TIMESTAMP', 0),
					'topic_delete_reason'	=> array('STEXT_UNI', ''),
					'topic_delete_user'		=> array('UINT', 0),
					'topic_posts_approved'		=> array('UINT', 0),
					'topic_posts_unapproved'	=> array('UINT', 0),
					'topic_posts_softdeleted'	=> array('UINT', 0),
				),
			),
			'add_index'		=> array(
				$this->table_prefix . 'posts'		=> array(
					'post_visibility'		=> array('post_visibility'),
				),
				$this->table_prefix . 'topics'		=> array(
					'topic_visibility'		=> array('topic_visibility'),
					'forum_vis_last'		=> array('forum_id', 'topic_visibility', 'topic_last_post_id'),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'forums'		=> array(
					'forum_posts_approved',
					'forum_posts_unapproved',
					'forum_posts_softdeleted',
					'forum_topics_approved',
					'forum_topics_unapproved',
					'forum_topics_softdeleted',
				),
				$this->table_prefix . 'posts'		=> array(
					'post_visibility',
					'post_delete_time',
					'post_delete_reason',
					'post_delete_user',
				),
				$this->table_prefix . 'topics'		=> array(
					'topic_visibility',
					'topic_delete_time',
					'topic_delete_reason',
					'topic_delete_user',
					'topic_posts_approved',
					'topic_posts_unapproved',
					'topic_posts_softdeleted',
				),
			),
			'drop_keys'		=> array(
				$this->table_prefix . 'posts'		=> array('post_visibility'),
				$this->table_prefix . 'topics'	=> array('topic_visibility', 'forum_vis_last'),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_post_visibility'))),
			array('custom', array(array($this, 'update_topic_visibility'))),
			array('custom', array(array($this, 'update_topics_post_counts'))),
			array('custom', array(array($this, 'update_forums_topic_and_post_counts'))),

			array('permission.add', array('f_softdelete', false)),
			array('permission.add', array('m_softdelete', false)),
		);
	}

	public function update_post_visibility()
	{
		$sql = 'UPDATE ' . $this->table_prefix . 'posts
			SET post_visibility = post_approved';
		$this->sql_query($sql);
	}

	public function update_topic_visibility()
	{
		$sql = 'UPDATE ' . $this->table_prefix . 'topics
			SET topic_visibility = topic_approved';
		$this->sql_query($sql);
	}

	public function update_topics_post_counts()
	{
		/*
		* Using sql_case here to avoid "BIGINT UNSIGNED value is out of range" errors.
		* As we update all topics in 2 queries, one broken topic would stop the conversion
		* for all topics and the suppressed error will cause the admin to not even notice it.
		*/
		$sql = 'UPDATE ' . $this->table_prefix . 'topics
			SET topic_posts_approved = topic_replies + 1,
				topic_posts_unapproved = ' . $this->db->sql_case('topic_replies_real > topic_replies', 'topic_replies_real - topic_replies', '0') . '
			WHERE topic_visibility = ' . ITEM_APPROVED;
		$this->sql_query($sql);

		$sql = 'UPDATE ' . $this->table_prefix . 'topics
			SET topic_posts_approved = 0,
				topic_posts_unapproved = (' . $this->db->sql_case('topic_replies_real > topic_replies', 'topic_replies_real - topic_replies', '0') . ') + 1
			WHERE topic_visibility = ' . ITEM_UNAPPROVED;
		$this->sql_query($sql);
	}

	public function update_forums_topic_and_post_counts($start)
	{
		$start = (int) $start;
		$limit = 10;
		$converted_forums = 0;

		if (!$start)
		{
			// Preserve the forum_posts value for link forums as it represents redirects.
			$sql = 'UPDATE ' . $this->table_prefix . 'forums
				SET forum_posts_approved = forum_posts
				WHERE forum_type = ' . FORUM_LINK;
			$this->db->sql_query($sql);
		}

		$sql = 'SELECT forum_id, topic_visibility, COUNT(topic_id) AS sum_topics, SUM(topic_posts_approved) AS sum_posts_approved, SUM(topic_posts_unapproved) AS sum_posts_unapproved
			FROM ' . $this->table_prefix . 'topics
			GROUP BY forum_id, topic_visibility
			ORDER BY forum_id, topic_visibility';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$update_forums = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$converted_forums++;

			$forum_id = (int) $row['forum_id'];
			if (!isset($update_forums[$forum_id]))
			{
				$update_forums[$forum_id] = array(
					'forum_posts_approved'		=> 0,
					'forum_posts_unapproved'	=> 0,
					'forum_topics_approved'		=> 0,
					'forum_topics_unapproved'	=> 0,
				);
			}

			$update_forums[$forum_id]['forum_posts_approved'] += (int) $row['sum_posts_approved'];
			$update_forums[$forum_id]['forum_posts_unapproved'] += (int) $row['sum_posts_unapproved'];

			$update_forums[$forum_id][(($row['topic_visibility'] == ITEM_APPROVED) ? 'forum_topics_approved' : 'forum_topics_unapproved')] += (int) $row['sum_topics'];
		}
		$this->db->sql_freeresult($result);

		foreach ($update_forums as $forum_id => $forum_data)
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $forum_data) . '
				WHERE forum_id = ' . $forum_id;
			$this->sql_query($sql);
		}

		if ($converted_forums < $limit)
		{
			// There are no more topics, we are done
			return;
		}

		// There are still more topics to query, return the next start value
		return $start + $limit;
	}
}
