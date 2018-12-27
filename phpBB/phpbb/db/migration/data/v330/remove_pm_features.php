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

class remove_pm_features extends \phpbb\db\migration\migration
{
	public function update_schema()
	{
		return [
			'drop_tables'	=> [
				$this->table_prefix . 'privmsgs_folder',
				$this->table_prefix . 'privmsgs_rules',
			],
			'drop_keys' => [
				$this->table_prefix . 'privmsgs_to'	=> [
					'usr_flder_id',
				],
			],
			'drop_columns'	=> [
				$this->table_prefix . 'privmsgs'	=> [
					'icon_id',
					'enable_bbcode',
					'enable_smilies',
					'enable_magic_url',
					'enable_sig',
					'bcc_address', // TODO: move 'bcc_address' to 'to_address'
				],
				// TODO: we also need to remove duplicates from the table now
				$this->table_prefix . 'privmsgs_to'	=> [
					'folder_id',
				],
				$this->table_prefix . 'users'	=> [
					'user_message_rules',
					'user_full_folder'
				],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.remove', ['pm_max_boxes']],
			['config.remove', ['pm_max_msgs']],
			['config.remove', ['full_folder_action']],
			['config.remove', ['auth_smilies_pm']],
			['config.remove', ['allow_sig_pm']],
			['config.remove', ['auth_flash_pm']],
			['config.remove', ['enable_pm_icons']],
		];
	}
}
