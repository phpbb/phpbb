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

class create_terms_of_use_config extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v310\config_db_text',
			'\phpbb\db\migration\data\v32x\v322',
		);
	}

	public function update_data()
	{
		$sql = 'SELECT lang_iso
					FROM ' . LANG_TABLE ;
		$result = $this->db->sql_query($sql);
		$return_data = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$return_data[] = array('config_text.add', array('terms_of_use_' . $row['lang_iso'], $this->get_terms_of_use_from_lang($row['lang_iso'])));
		}

		$this->db->sql_freeresult($result);

		return $return_data;
	}

	private function get_terms_of_use_from_lang($lang_iso)
	{
		$lang = array();

		include($this->phpbb_root_path . 'language/' . $lang_iso . "/ucp." . $this->php_ext);
		return $lang['TERMS_OF_USE_CONTENT'];

	}
}
