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

class release_3_0_3_rc1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return phpbb_version_compare($this->config['version'], '3.0.3-RC1', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_2');
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'styles_template' => array(
					'template_inherits_id' => array('UINT:4', 0),
					'template_inherit_path' => array('VCHAR', ''),
				),
				$this->table_prefix . 'groups' => array(
					'group_max_recipients' => array('UINT', 0),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'styles_template' => array(
					'template_inherits_id',
					'template_inherit_path',
				),
				$this->table_prefix . 'groups' => array(
					'group_max_recipients',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('enable_queue_trigger', '0')),
			array('config.add', array('queue_trigger_posts', '3')),
			array('config.add', array('pm_max_recipients', '0')),
			array('custom', array(array(&$this, 'set_group_default_max_recipients'))),
			array('config.add', array('dbms_version', $this->db->sql_server_info(true))),
			array('permission.add', array('u_masspm_group', true, 'u_masspm')),
			array('custom', array(array(&$this, 'correct_acp_email_permissions'))),

			array('config.update', array('version', '3.0.3-RC1')),
		);
	}

	public function correct_acp_email_permissions()
	{
		$sql = 'UPDATE ' . $this->table_prefix . 'modules
			SET module_auth = \'acl_a_email && cfg_email_enable\'
			WHERE module_class = \'acp\'
				AND module_basename = \'email\'';
		$this->sql_query($sql);
	}

	public function set_group_default_max_recipients()
	{
		// Set maximum number of recipients for the registered users, bots, guests group
		$sql = 'UPDATE ' . GROUPS_TABLE . ' SET group_max_recipients = 5
			WHERE ' . $this->db->sql_in_set('group_name', array('GUESTS', 'REGISTERED', 'REGISTERED_COPPA', 'BOTS'));
		$this->sql_query($sql);
	}
}
