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

namespace phpbb\db\migration\data\v31x;

class update_custom_bbcodes_with_idn extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\phpbb\db\migration\data\v31x\v312',
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_bbcodes_table'))),
		);
	}

	public function update_bbcodes_table()
	{
		if (!class_exists('acp_bbcodes'))
		{
			include($this->phpbb_root_path . 'includes/acp/acp_bbcodes.' . $this->php_ext);
		}

		$bbcodes = new \acp_bbcodes();

		$sql = 'SELECT bbcode_id, bbcode_match, bbcode_tpl
			FROM ' . BBCODES_TABLE;
		$result = $this->sql_query($sql);

		$sql_ary = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$data = array();
			if (preg_match('/(URL|LOCAL_URL|RELATIVE_URL)/', $row['bbcode_match']))
			{
				$data = $bbcodes->build_regexp($row['bbcode_match'], $row['bbcode_tpl']);
				$sql_ary[$row['bbcode_id']] = array(
					'first_pass_match'			=> $data['first_pass_match'],
					'first_pass_replace'		=> $data['first_pass_replace'],
					'second_pass_match'			=> $data['second_pass_match'],
					'second_pass_replace'		=> $data['second_pass_replace']
				);
			}
		}
		$this->db->sql_freeresult($result);

		foreach ($sql_ary as $bbcode_id => $bbcode_data)
		{
			$sql = 'UPDATE ' . BBCODES_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $bbcode_data) . '
				WHERE bbcode_id = ' . (int) $bbcode_id;
			$this->sql_query($sql);
		}
	}
}
