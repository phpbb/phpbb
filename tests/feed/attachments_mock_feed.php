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
 * Board wide feed (aka overall feed)
 *
 * This will give you the newest {$this->num_items} posts
 * from the whole board.
 */
class phpbb_feed_attachments_mock_feed extends \phpbb\feed\attachments_base
{
	public $topic_ids = array();
	public $post_ids = array();

	function get_sql()
	{
		parent::fetch_attachments($this->post_ids, $this->topic_ids);

		return true;
	}

	public function adjust_item(&$item_row, &$row)
	{
		return array();
	}
}
