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

namespace phpbb\db\migration\data\v32x;

class fix_user_styles extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v320\v320',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'styles_fix'))),
		);
	}

	public function styles_fix()
	{
		$default_style = (int) $this->config['default_style'];
		$enabled_styles = array();

		// Get enabled styles
		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . '
			WHERE style_active = 1';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$enabled_styles[] = (int) $row['style_id'];
		}
		$this->db->sql_freeresult($result);

		// Set the default style to users who have an invalid style
		$this->sql_query('UPDATE ' . USERS_TABLE . '
			SET user_style = ' . (int) $default_style . '
			WHERE ' . $this->db->sql_in_set('user_style', $enabled_styles, true));
	}
}
