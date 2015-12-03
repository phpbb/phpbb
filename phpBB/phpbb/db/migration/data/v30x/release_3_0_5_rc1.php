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

use phpbb\db\migration\container_aware_migration;

class release_3_0_5_rc1 extends container_aware_migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.5-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_4');
	}

	public function update_schema()
	{
		return array(
			'change_columns' => array(
				$this->table_prefix . 'forums' => array(
					'forum_style' => array('UINT', 0),
				),
			),
		);
	}

	public function update_data()
	{
		$search_indexing_state = $this->config['search_indexing_state'];

		return array(
			array('config.add', array('captcha_gd_wave', 0)),
			array('config.add', array('captcha_gd_3d_noise', 1)),
			array('config.add', array('captcha_gd_fonts', 1)),
			array('config.add', array('confirm_refresh', 1)),
			array('config.add', array('max_num_search_keywords', 10)),
			array('config.remove', array('search_indexing_state')),
			array('config.add', array('search_indexing_state', $search_indexing_state, true)),
			array('custom', array(array(&$this, 'hash_old_passwords'))),
			array('custom', array(array(&$this, 'update_ichiro_bot'))),
		);
	}

	public function hash_old_passwords()
	{
		/* @var $passwords_manager \phpbb\passwords\manager */
		$passwords_manager = $this->container->get('passwords.manager');

		$sql = 'SELECT user_id, user_password
				FROM ' . $this->table_prefix . 'users
				WHERE user_pass_convert = 1';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			if (strlen($row['user_password']) == 32)
			{
				$sql_ary = array(
					'user_password'	=> '$CP$' . $passwords_manager->hash($row['user_password'], 'passwords.driver.salted_md5'),
				);

				$this->sql_query('UPDATE ' . $this->table_prefix . 'users SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE user_id = ' . $row['user_id']);
			}
		}
		$this->db->sql_freeresult($result);
	}

	public function update_ichiro_bot()
	{
		// Adjust bot entry
		$sql = 'UPDATE ' . $this->table_prefix . "bots
			SET bot_agent = 'ichiro/'
			WHERE bot_agent = 'ichiro/2'";
		$this->sql_query($sql);
	}

	public function remove_duplicate_auth_options()
	{
		// Before we are able to add a unique key to auth_option, we need to remove duplicate entries
		$sql = 'SELECT auth_option
			FROM ' . $this->table_prefix . 'acl_options
			GROUP BY auth_option
			HAVING COUNT(*) >= 2';
		$result = $this->db->sql_query($sql);

		$auth_options = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$auth_options[] = $row['auth_option'];
		}
		$this->db->sql_freeresult($result);

		// Remove specific auth options
		if (!empty($auth_options))
		{
			foreach ($auth_options as $option)
			{
				// Select auth_option_ids... the largest id will be preserved
				$sql = 'SELECT auth_option_id
					FROM ' . ACL_OPTIONS_TABLE . "
					WHERE auth_option = '" . $this->db->sql_escape($option) . "'
					ORDER BY auth_option_id DESC";
				// sql_query_limit not possible here, due to bug in postgresql layer
				$result = $this->db->sql_query($sql);

				// Skip first row, this is our original auth option we want to preserve
				$this->db->sql_fetchrow($result);

				while ($row = $this->db->sql_fetchrow($result))
				{
					// Ok, remove this auth option...
					$this->sql_query('DELETE FROM ' . ACL_OPTIONS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id']);
					$this->sql_query('DELETE FROM ' . ACL_ROLES_DATA_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id']);
					$this->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id']);
					$this->sql_query('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE auth_option_id = ' . $row['auth_option_id']);
				}
				$this->db->sql_freeresult($result);
			}
		}
	}
}
