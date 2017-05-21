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

namespace phpbb\db\migration\data\v31x;

class add_latest_topics_index extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v31x\v3110',
		);
	}

	public function update_schema()
	{
		return array(
			'add_index' => array(
				$this->table_prefix . 'topics' => array(
					'latest_topics'	=> array(
						'forum_id',
						'topic_last_post_time',
						'topic_last_post_id',
						'topic_moved_id',
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_keys' => array(
				$this->table_prefix . 'topics' => array(
					'latest_topics',
				),
			),
		);
	}
}
