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

use \phpbb\config\config;
use \phpbb\db\driver\driver_interface;
use \phpbb\event\dispatcher;

/**
 * Attachment delete class
 */

class delete
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var array Attachement IDs */
	protected $ids;

	/**
	 * Attachment delete class constructor
	 *
	 * @param config $config
	 * @param driver_interface $db
	 * @param dispatcher>>>>>>> 85b6020... [ticket/14168] Move function for attachment deletion into class
	 */
	public function __construct(config $config, driver_interface $db, dispatcher $dispatcher)
	{
		$this->config = $config;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Delete Attachments
	 *
	 * @param string $mode can be: post|message|topic|attach|user
	 * @param mixed $ids can be: post_ids, message_ids, topic_ids, attach_ids, user_ids
	 * @param bool $resync set this to false if you are deleting posts or topics
	 *
	 * @return int|bool Number of deleted attachments or false if something
	 *			went wrong during attachment deletion
	 */
	function delete($mode, $ids, $resync = true)
	{
		if (!$this->set_attachment_ids($ids))
		{
			return false;
		}

		$sql_where = '';

		switch ($mode)
		{
			case 'post':
			case 'message':
				$sql_id = 'post_msg_id';
				$sql_where = ' AND in_message = ' . ($mode == 'message' ? 1 : 0);
				break;

			case 'topic':
				$sql_id = 'topic_id';
				break;

			case 'user':
				$sql_id = 'poster_id';
				break;

			case 'attach':
			default:
				$sql_id = 'attach_id';
				break;
		}

		$post_ids = $message_ids = $topic_ids = $physical = array();

		/**
		 * Perform additional actions before collecting data for attachment(s) deletion
		 *
		 * @event core.delete_attachments_collect_data_before
		 * @var	string	mode			Variable containing attachments deletion mode, can be: post|message|topic|attach|user
		 * @var	mixed	ids				Array or comma separated list of ids corresponding to the mode
		 * @var	bool	resync			Flag indicating if posts/messages/topics should be synchronized
		 * @var	string	sql_id			The field name to collect/delete data for depending on the mode
		 * @since 3.1.7-RC1
		 */
		$vars = array(
			'mode',
			'ids',
			'resync',
			'sql_id',
		);
		extract($this->dispatcher->trigger_event('core.delete_attachments_collect_data_before', compact($vars)));

		// Collect post and topic ids for later use if we need to touch remaining entries (if resync is enabled)
		$sql = 'SELECT post_msg_id, topic_id, in_message, physical_filename, thumbnail, filesize, is_orphan
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $this->db->sql_in_set($sql_id, $ids);

		$sql .= $sql_where;

		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			// We only need to store post/message/topic ids if resync is enabled and the file is not orphaned
			if ($resync && !$row['is_orphan'])
			{
				if (!$row['in_message'])
				{
					$post_ids[] = $row['post_msg_id'];
					$topic_ids[] = $row['topic_id'];
				}
				else
				{
					$message_ids[] = $row['post_msg_id'];
				}
			}

			$physical[] = array('filename' => $row['physical_filename'], 'thumbnail' => $row['thumbnail'], 'filesize' => $row['filesize'], 'is_orphan' => $row['is_orphan']);
		}
		$this->db->sql_freeresult($result);

		/**
		 * Perform additional actions before attachment(s) deletion
		 *
		 * @event core.delete_attachments_before
		 * @var	string	mode			Variable containing attachments deletion mode, can be: post|message|topic|attach|user
		 * @var	mixed	ids				Array or comma separated list of ids corresponding to the mode
		 * @var	bool	resync			Flag indicating if posts/messages/topics should be synchronized
		 * @var	string	sql_id			The field name to collect/delete data for depending on the mode
		 * @var	array	post_ids		Array with post ids for deleted attachment(s)
		 * @var	array	topic_ids		Array with topic ids for deleted attachment(s)
		 * @var	array	message_ids		Array with private message ids for deleted attachment(s)
		 * @var	array	physical		Array with deleted attachment(s) physical file(s) data
		 * @since 3.1.7-RC1
		 */
		$vars = array(
			'mode',
			'ids',
			'resync',
			'sql_id',
			'post_ids',
			'topic_ids',
			'message_ids',
			'physical',
		);
		extract($this->dispatcher->trigger_event('core.delete_attachments_before', compact($vars)));

		// Delete attachments
		$sql = 'DELETE FROM ' . ATTACHMENTS_TABLE . '
		WHERE ' . $this->db->sql_in_set($sql_id, $ids);

		$sql .= $sql_where;

		$this->db->sql_query($sql);
		$num_deleted = $this->db->sql_affectedrows();

		/**
		 * Perform additional actions after attachment(s) deletion from the database
		 *
		 * @event core.delete_attachments_from_database_after
		 * @var	string	mode			Variable containing attachments deletion mode, can be: post|message|topic|attach|user
		 * @var	mixed	ids				Array or comma separated list of ids corresponding to the mode
		 * @var	bool	resync			Flag indicating if posts/messages/topics should be synchronized
		 * @var	string	sql_id			The field name to collect/delete data for depending on the mode
		 * @var	array	post_ids		Array with post ids for deleted attachment(s)
		 * @var	array	topic_ids		Array with topic ids for deleted attachment(s)
		 * @var	array	message_ids		Array with private message ids for deleted attachment(s)
		 * @var	array	physical		Array with deleted attachment(s) physical file(s) data
		 * @var	int		num_deleted		The number of deleted attachment(s) from the database
		 * @since 3.1.7-RC1
		 */
		$vars = array(
			'mode',
			'ids',
			'resync',
			'sql_id',
			'post_ids',
			'topic_ids',
			'message_ids',
			'physical',
			'num_deleted',
		);
		extract($this->dispatcher->trigger_event('core.delete_attachments_from_database_after', compact($vars)));

		if (!$num_deleted)
		{
			return 0;
		}

		// Delete attachments from filesystem
		$space_removed = $files_removed = 0;
		foreach ($physical as $file_ary)
		{
			if (phpbb_unlink($file_ary['filename'], 'file', true) && !$file_ary['is_orphan'])
			{
				// Only non-orphaned files count to the file size
				$space_removed += $file_ary['filesize'];
				$files_removed++;
			}

			if ($file_ary['thumbnail'])
			{
				phpbb_unlink($file_ary['filename'], 'thumbnail', true);
			}
		}

		/**
		 * Perform additional actions after attachment(s) deletion from the filesystem
		 *
		 * @event core.delete_attachments_from_filesystem_after
		 * @var	string	mode			Variable containing attachments deletion mode, can be: post|message|topic|attach|user
		 * @var	mixed	ids				Array or comma separated list of ids corresponding to the mode
		 * @var	bool	resync			Flag indicating if posts/messages/topics should be synchronized
		 * @var	string	sql_id			The field name to collect/delete data for depending on the mode
		 * @var	array	post_ids		Array with post ids for deleted attachment(s)
		 * @var	array	topic_ids		Array with topic ids for deleted attachment(s)
		 * @var	array	message_ids		Array with private message ids for deleted attachment(s)
		 * @var	array	physical		Array with deleted attachment(s) physical file(s) data
		 * @var	int		num_deleted		The number of deleted attachment(s) from the database
		 * @var	int		space_removed	The size of deleted files(s) from the filesystem
		 * @var	int		files_removed	The number of deleted file(s) from the filesystem
		 * @since 3.1.7-RC1
		 */
		$vars = array(
			'mode',
			'ids',
			'resync',
			'sql_id',
			'post_ids',
			'topic_ids',
			'message_ids',
			'physical',
			'num_deleted',
			'space_removed',
			'files_removed',
		);
		extract($this->dispatcher->trigger_event('core.delete_attachments_from_filesystem_after', compact($vars)));

		if ($space_removed || $files_removed)
		{
			$this->config->increment('upload_dir_size', $space_removed * (-1), false);
			$this->config->increment('num_files', $files_removed * (-1), false);
		}

		// If we do not resync, we do not need to adjust any message, post, topic or user entries
		if (!$resync)
		{
			return $num_deleted;
		}

		// No more use for the original ids
		unset($ids);

		// Now, we need to resync posts, messages, topics. We go through every one of them
		$post_ids = array_unique($post_ids);
		$message_ids = array_unique($message_ids);
		$topic_ids = array_unique($topic_ids);

		// Update post indicators for posts now no longer having attachments
		if (sizeof($post_ids))
		{
			// Just check which posts are still having an assigned attachment not orphaned by querying the attachments table
			$sql = 'SELECT post_msg_id
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $this->db->sql_in_set('post_msg_id', $post_ids) . '
				AND in_message = 0
				AND is_orphan = 0';
			$result = $this->db->sql_query($sql);

			$remaining_ids = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$remaining_ids[] = $row['post_msg_id'];
			}
			$this->db->sql_freeresult($result);

			// Now only unset those ids remaining
			$post_ids = array_diff($post_ids, $remaining_ids);

			if (sizeof($post_ids))
			{
				$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_attachment = 0
				WHERE ' . $this->db->sql_in_set('post_id', $post_ids);
				$this->db->sql_query($sql);
			}
		}

		// Update message table if messages are affected
		if (sizeof($message_ids))
		{
			// Just check which messages are still having an assigned attachment not orphaned by querying the attachments table
			$sql = 'SELECT post_msg_id
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $this->db->sql_in_set('post_msg_id', $message_ids) . '
				AND in_message = 1
				AND is_orphan = 0';
			$result = $this->db->sql_query($sql);

			$remaining_ids = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$remaining_ids[] = $row['post_msg_id'];
			}
			$this->db->sql_freeresult($result);

			// Now only unset those ids remaining
			$message_ids = array_diff($message_ids, $remaining_ids);

			if (sizeof($message_ids))
			{
				$sql = 'UPDATE ' . PRIVMSGS_TABLE . '
				SET message_attachment = 0
				WHERE ' . $this->db->sql_in_set('msg_id', $message_ids);
				$this->db->sql_query($sql);
			}
		}

		// Now update the topics. This is a bit trickier, because there could be posts still having attachments within the topic
		if (sizeof($topic_ids))
		{
			// Just check which topics are still having an assigned attachment not orphaned by querying the attachments table (much less entries expected)
			$sql = 'SELECT topic_id
			FROM ' . ATTACHMENTS_TABLE . '
			WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids) . '
				AND is_orphan = 0';
			$result = $this->db->sql_query($sql);

			$remaining_ids = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$remaining_ids[] = $row['topic_id'];
			}
			$this->db->sql_freeresult($result);

			// Now only unset those ids remaining
			$topic_ids = array_diff($topic_ids, $remaining_ids);

			if (sizeof($topic_ids))
			{
				$sql = 'UPDATE ' . TOPICS_TABLE . '
				SET topic_attachment = 0
				WHERE ' . $this->db->sql_in_set('topic_id', $topic_ids);
				$this->db->sql_query($sql);
			}
		}

		return $num_deleted;
	}

	/**
	 * Set attachment IDs
	 *
	 * @param array $ids
	 *
	 * @return bool True if attachment IDs were set, false if not
	 */
	protected function set_attachment_ids($ids)
	{
		// 0 is as bad as an empty array
		if (empty($ids))
		{
			return false;
		}

		if (is_array($ids))
		{
			$ids = array_unique($ids);
			$this->ids = array_map('intval', $ids);
		}
		else
		{
			$this->ids = array((int) $ids);
		}

		return true;
	}
}