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

use phpbb\db\migration\container_aware_migration;

/**
 * Migration to convert the Soft Delete MOD for 3.0
 *
 * https://www.phpbb.com/customise/db/mod/soft_delete/
 */
class soft_delete_mod_convert extends container_aware_migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\alpha3',
		);
	}

	public function effectively_installed()
	{
		return !$this->db_tools->sql_column_exists($this->table_prefix . 'posts', 'post_deleted');
	}

	public function update_data()
	{
		return array(
			array('permission.remove', array('m_harddelete', true)),
			array('permission.remove', array('m_harddelete', false)),

			array('custom', array(array($this, 'convert_posts'))),
			array('custom', array(array($this, 'convert_topics'))),
		);
	}

	public function convert_posts($start)
	{
		$content_visibility = $this->get_content_visibility();

		$limit = 250;
		$i = 0;

		$sql = 'SELECT p.*, t.topic_first_post_id, t.topic_last_post_id
			FROM ' . $this->table_prefix . 'posts p, ' . $this->table_prefix . 'topics t
			WHERE p.post_deleted > 0
				AND t.topic_id = p.topic_id';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$content_visibility->set_post_visibility(
				ITEM_DELETED,
				$row['post_id'],
				$row['topic_id'],
				$row['forum_id'],
				$row['post_deleted'],
				$row['post_deleted_time'],
				'',
				($row['post_id'] == $row['topic_first_post_id']) ? true : false,
				($row['post_id'] == $row['topic_last_post_id']) ? true : false
			);

			$i++;
		}

		$this->db->sql_freeresult($result);

		if ($i == $limit)
		{
			return $start + $i;
		}
	}

	public function convert_topics($start)
	{
		$content_visibility = $this->get_content_visibility();

		$limit = 100;
		$i = 0;

		$sql = 'SELECT *
			FROM ' . $this->table_prefix . 'topics
			WHERE topic_deleted > 0';
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$content_visibility->set_topic_visibility(
				ITEM_DELETED,
				$row['topic_id'],
				$row['forum_id'],
				$row['topic_deleted'],
				$row['topic_deleted_time'],
				''
			);

			$i++;
		}

		$this->db->sql_freeresult($result);

		if ($i == $limit)
		{
			return $start + $i;
		}
	}

	/**
	 * @return \phpbb\content_visibility
	 */
	protected function get_content_visibility()
	{
		return $this->container->get('content.visibility');
	}
}
