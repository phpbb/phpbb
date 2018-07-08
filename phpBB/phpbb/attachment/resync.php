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

namespace phpbb\attachment;

use \phpbb\db\driver\driver_interface;

/**
 * Attachment resync class
 */
class resync
{
	/** @var driver_interface */
	protected $db;

	/** @var string Attachment table SQL ID */
	private $attach_sql_id;

	/** @var string Resync table SQL ID  */
	private $resync_sql_id;

	/** @var string Resync SQL table */
	private $resync_table;

	/** @var string SQL where statement */
	private $sql_where;

	/**
	 * Constructor for attachment resync class
	 *
	 * @param driver_interface $db Database driver
	 */
	public function __construct(driver_interface $db)
	{
		$this->db = $db;
	}

	/**
	 * Set type constraints for attachment resync
	 *
	 * @param string $type Type of resync; can be: message|post|topic
	 */
	protected function set_type_constraints($type)
	{
		switch ($type)
		{
			case 'message':
				$this->attach_sql_id = 'post_msg_id';
				$this->sql_where = ' AND in_message = 1
					AND is_orphan = 0';
				$this->resync_table = PRIVMSGS_TABLE;
				$this->resync_sql_id = 'msg_id';
			break;

			case 'post':
				$this->attach_sql_id = 'post_msg_id';
				$this->sql_where = ' AND in_message = 0
					AND is_orphan = 0';
				$this->resync_table = POSTS_TABLE;
				$this->resync_sql_id = 'post_id';
			break;

			case 'topic':
				$this->attach_sql_id = 'topic_id';
				$this->sql_where = ' AND is_orphan = 0';
				$this->resync_table = TOPICS_TABLE;
				$this->resync_sql_id = 'topic_id';
			break;
		}
	}

	/**
	 * Resync specified type
	 *
	 * @param string $type Type of resync
	 * @param array $ids IDs to resync
	 */
	public function resync($type, $ids)
	{
		if (empty($type) || !is_array($ids) || !count($ids) || !in_array($type, array('post', 'topic', 'message')))
		{
			return;
		}

		$this->set_type_constraints($type);

		// Just check which elements are still having an assigned attachment
		// not orphaned by querying the attachments table
		$sql = 'SELECT ' . $this->attach_sql_id . '
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $this->db->sql_in_set($this->attach_sql_id, $ids)
				. $this->sql_where;
		$result = $this->db->sql_query($sql);

		$remaining_ids = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$remaining_ids[] = $row[$this->attach_sql_id];
		}
		$this->db->sql_freeresult($result);

		// Now only unset those ids remaining
		$ids = array_diff($ids, $remaining_ids);

		if (count($ids))
		{
			$sql = 'UPDATE ' . $this->resync_table . '
				SET ' . $type . '_attachment = 0
				WHERE ' . $this->db->sql_in_set($this->resync_sql_id, $ids);
			$this->db->sql_query($sql);
		}
	}

}
