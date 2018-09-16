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
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'privmsgs_folder',
				$this->table_prefix . 'privmsgs_rules',
			),
			'drop_columns'	=> array(
				$this->table_prefix . 'privmsgs'	=> array(
					'icon_id',
					'enable_bbcode',
					'enable_smilies',
					'enable_magic_url',
					'enable_sig',
					'bcc_address', // TODO: move 'bcc_address' to 'to_address'
				),
				// TODO: we also need to remove duplicates from the table now
				$this->table_prefix . 'privmsgs_to'	=> array(
					'folder_id',
				),
				$this->table_prefix . 'users'	=> array(
					'user_message_rules',
					'user_full_folder'
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.remove', array('pm_max_boxes')),
			array('config.remove', array('pm_max_msgs')),
			array('config.remove', array('full_folder_action')),
			array('config.remove', array('auth_smilies_pm')),
			array('config.remove', array('allow_sig_pm')),
			array('config.remove', array('auth_flash_pm')),
			array('config.remove', array('enable_pm_icons')),
		);
	}
}
