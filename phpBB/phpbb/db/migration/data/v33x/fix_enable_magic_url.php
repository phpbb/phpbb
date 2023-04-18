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

 namespace phpbb\db\migration\data\v33x;

class fix_enable_magic_url extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return [
            '\phpbb\db\migration\data\v330\v330',
        ];
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'disable_magic_urls']]],
			['custom', [[$this, 'update_posts']]],
			['custom', [[$this, 'update_privmsgs']]],
		];
	}

	/**
	 * Sets all enable_magic_url data to 0 in posts and private messages
	 *
	 * @return void
	 */
	public function disable_magic_urls()
	{
		// Nothing to do if allow_post_links is enabled
        if ($this->config['allow_post_links'])
		{
			return;
		}

		$sql = 'UPDATE ' . $this->table_prefix . 'posts SET enable_magic_url = 0';
		$this->sql_query($sql);

		$sql = 'UPDATE ' . $this->table_prefix . 'privmsgs SET enable_magic_url = 0';
		$this->sql_query($sql);
	}

	/**
	 * Perform an update of enable_magic_url in posts
	 *
	 * @param int $start Limit start value
	 * @return int|void  Null if update is finished, next start value if not
	 */
	public function update_posts($start)
	{
		// Nothing to do if allow_post_links is enabled
		if ($this->config['allow_post_links'])
		{
			return;
		}

		return $this->update_magic_urls('posts', $start);
	}

	/**
	 * Perform an update of enable_magic_url in private messages
	 *
	 * @param int $start Limit start value
	 * @return int|void  Null if conversupdateion is finished, next start value if not
	 */
	public function update_privmsgs($start)
	{
		// Nothing to do if allow_post_links is enabled
		if ($this->config['allow_post_links'])
		{
			return;
		}

		return $this->update_magic_urls('privmsgs', $start);
	}

	/**
	 * Update enable_magic_url = 1 for messages that have rendered URLs in them
	 *
	 * @param string $table Name of table
	 * @param int $start Limit start value
	 * @return int|void Null if update is finished, next start value if not
	 */
	public function update_magic_urls($table, $start)
	{
		$start = (int) $start;
		$limit = 50;
		$updated_items = 0;

		$columns = [
			'posts'	=> [
				'item_id'	=> 'post_id',
				'item_txt'	=> 'post_text',
			],
			'privmsgs'	=> [
				'item_id'	=> 'msg_id',
				'item_txt'	=> 'message_text',
			],
		];

		$sql = "SELECT {$columns[$table]['item_id']} AS id, {$columns[$table]['item_txt']} AS text
			FROM " . $this->table_prefix . $table;
		$result = $this->db->sql_query_limit($sql, $limit, $start);

		$items = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$updated_items++;

			// Look for <!-- m --> or for a URL tag that's not immediately followed by <s>
			if (strpos($row['text'], '<!-- m -->') !== false || preg_match('(<URL [^>]++>(?!<s>))', $row['text']))
			{
				$items[] = $row['id'];
			}
		}
		$this->db->sql_freeresult($result);

		foreach ($items as $item_id)
		{
			$sql = 'UPDATE ' . $this->table_prefix . $table . "
				SET enable_magic_url = 1
				WHERE {$columns[$table]['item_id']} = " . (int) $item_id;
			$this->sql_query($sql);
		}

		if ($updated_items < $limit)
		{
			// There are no more items to be converted
			return;
		}

		// There are still more items to query, return the next start value
		return $start + $limit;
	}
}
