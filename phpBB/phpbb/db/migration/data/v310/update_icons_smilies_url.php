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

class update_icons_smilies_url extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\rc5',
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
		$smilies_url = array();

		$sql = 'SELECT smiley_id, smiley_url
		FROM ' . $this->table_prefix . 'smilies
		ORDER BY smiley_order';
		$result = $this->db->sql_query($sql);

		while($row = $this->db->sql_fetchrow($result))
		{
			$smilies_url[(int) $row['smiley_id']] = $row['smiley_url'];
		}
		$this->db->sql_freeresult($result);

		foreach ($smilies_url as $smiley_id => $smiley_url)
		{
			$sql = 'UPDATE ' . $this->table_prefix . "smilies
				SET smiley_url = '" . $this->db->sql_escape("{$this->config['smilies_path']}/$smiley_url") . "'
				WHERE smiley_id = '$smiley_id'";
			$this->sql_query($sql);
		}
	}

	public function update_icons_url()
	{
		$icons_url = array();

		$sql = 'SELECT icons_id, icons_url
		FROM ' . $this->table_prefix . 'icons
		ORDER BY icons_order';
		$result = $this->db->sql_query($sql);

		while($row = $this->db->sql_fetchrow($result))
		{
			$icons_url[(int) $row['icons_id']] = $row['icons_url'];
		}
		$this->db->sql_freeresult($result);

		foreach ($icons_url as $icons_id => $icons_url)
		{
			$sql = 'UPDATE ' . $this->table_prefix . "icons
				SET icons_url = '" . $this->db->sql_escape("{$this->config['icons_path']}/$icons_url") . "'
				WHERE icons_id = '$icons_id'";
			$this->sql_query($sql);
		}
	}
}
