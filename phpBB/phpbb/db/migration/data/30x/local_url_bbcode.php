<?php
/**
*
* @package migration
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\db\migration\datax;

class local_url_bbcode extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('phpbb_db_migration_data_30x_3_0_12_rc1');
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
			WHERE bbcode_match ' . $this->db->sql_like_expression($this->db->any_char . 'LOCAL_URL' . $this->db->any_char);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (!class_exists('acp_bbcodes'))
			{
				global $phpEx;
				phpbb_require_updated('includes/acp/acp_bbcodes.' . $phpEx);
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
