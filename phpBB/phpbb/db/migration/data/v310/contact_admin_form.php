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

class contact_admin_form extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['contact_admin_form_enable']);
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\config_db_text');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('contact_admin_form_enable', 1)),
			array('custom', array(array($this, 'contact_admin_info'))),
		);
	}

	public function contact_admin_info()
	{
		$text_config = new \phpbb\config\db_text($this->db, $this->table_prefix . 'config_text');
		$text_config->set_array(array(
			'contact_admin_info'			=> '',
			'contact_admin_info_uid'		=> '',
			'contact_admin_info_bitfield'	=> '',
			'contact_admin_info_flags'		=> OPTION_FLAG_BBCODE + OPTION_FLAG_SMILIES + OPTION_FLAG_LINKS,
		));
	}
}
