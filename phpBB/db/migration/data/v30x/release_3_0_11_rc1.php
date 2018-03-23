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

class release_3_0_11_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.11-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_10');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'cleanup_deactivated_styles'))),
			array('custom', array(array(&$this, 'delete_orphan_private_messages'))),

			array('config.update', array('version', '3.0.11-RC1')),
		);
	}

	public function cleanup_deactivated_styles()
	{
		// Updates users having current style a deactivated one
		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . '
			WHERE style_active = 0';
		$result = $this->sql_query($sql);

		$deactivated_style_ids = array();
		while ($style_id = $this->db->sql_fetchfield('style_id', false, $result))
		{
			$deactivated_style_ids[] = (int) $style_id;
		}
		$this->db->sql_freeresult($result);

		if (!empty($deactivated_style_ids))
		{
			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_style = ' . (int) $this->config['default_style'] .'
				WHERE ' . $this->db->sql_in_set('user_style', $deactivated_style_ids);
			$this->sql_query($sql);
		}
	}

	public function delete_orphan_private_messages()
	{
		// Delete orphan private messages
		$batch_size = 500;

		$sql_array = array(
			'SELECT'	=> 'p.msg_id',
			'FROM'		=> array(
				PRIVMSGS_TABLE	=> 'p',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(PRIVMSGS_TO_TABLE => 't'),
					'ON'	=> 'p.msg_id = t.msg_id',
				),
			),
			'WHERE'		=> 't.user_id IS NULL',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);

		$result = $this->db->sql_query_limit($sql, $batch_size);

		$delete_pms = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$delete_pms[] = (int) $row['msg_id'];
		}
		$this->db->sql_freeresult($result);

		if (!empty($delete_pms))
		{
			$sql = 'DELETE FROM ' . PRIVMSGS_TABLE . '
				WHERE ' . $this->db->sql_in_set('msg_id', $delete_pms);
			$this->sql_query($sql);

			// Return false to have the Migrator call this function again
			return false;
		}
	}
}
