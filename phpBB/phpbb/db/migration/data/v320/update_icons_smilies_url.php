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

namespace phpbb\db\migration\data\v320;

class update_icons_smilies_url extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\v320rc1',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_smiley_url'))),
			array('custom', array(array($this, 'update_icons_url'))),
		);
	}

	public function update_smiley_url()
	{
		$smiley_path = $this->db->sql_escape($this->config['smilies_path'] . '/');

		$sql = 'UPDATE ' . $this->table_prefix . "smilies
			SET smiley_url = '" . $this->db->sql_concatenate($smiley_path, 'smiley_url') . "'";
		$this->sql_query($sql);
	}

	public function update_icons_url()
	{
		$icon_path = $this->db->sql_escape($this->config['icons_path'] . '/');

		$sql = 'UPDATE ' . $this->table_prefix . "icons
			SET icons_url = '" . $this->db->sql_concatenate($icon_path, 'icons_url') . "'";
		$this->sql_query($sql);
	}
}
