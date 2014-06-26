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

namespace phpbb\db\migration\data\v30x;

class local_url_bbcode extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_12_rc1');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_local_url_bbcode'))),
		);
	}

	/**
	* Update BBCodes that currently use the LOCAL_URL tag
	*
	* To fix http://tracker.phpbb.com/browse/PHPBB3-8319 we changed
	* the second_pass_replace value, so that needs updating for existing ones
	*/
	public function update_local_url_bbcode()
	{
		$sql = 'SELECT *
			FROM ' . BBCODES_TABLE . '
			WHERE bbcode_match ' . $this->db->sql_like_expression($this->db->get_any_char() . 'LOCAL_URL' . $this->db->get_any_char());
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!class_exists('acp_bbcodes'))
			{
				if (function_exists('phpbb_require_updated'))
				{
					phpbb_require_updated('includes/acp/acp_bbcodes.' . $this->php_ext);
				}
				else
				{
					require($this->phpbb_root_path . 'includes/acp/acp_bbcodes.' . $this->php_ext);
				}
			}

			$bbcode_match = $row['bbcode_match'];
			$bbcode_tpl = $row['bbcode_tpl'];

			$acp_bbcodes = new \acp_bbcodes();
			$sql_ary = $acp_bbcodes->build_regexp($bbcode_match, $bbcode_tpl);

			$sql = 'UPDATE ' . BBCODES_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE bbcode_id = ' . (int) $row['bbcode_id'];
			$this->sql_query($sql);
		}
		$this->db->sql_freeresult($result);
	}
}
