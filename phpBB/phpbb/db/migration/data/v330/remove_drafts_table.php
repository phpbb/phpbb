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

class remove_drafts_table extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v330\migrate_drafts_table');
	}

	public function update_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'drafts',
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'add_tables'		=> array(
				$this->table_prefix . 'drafts'	=> array(
					'COLUMNS'			=> array(
						'draft_id'  				=> array('UINT', null, 'auto_increment'),
						'user_id'					=> array('UINT', 0),
						'topic_id'					=> array('UINT', 0),
						'forum_id'					=> array('UINT', 0),
						'save_time'					=> array('TIMESTAMP', 1),
						'draft_subject'				=> array('VCHAR:255', ''),
						'draft_message'			   	=> array('TEXT_UNI', ''),
					),
					'PRIMARY_KEY'		=> array('draft_id'),
					'KEYS'				=> array(
						'save_time'		=> array('INDEX', array('item_type', 'item_id')),
					),
				),
			),
		);
	}
}
