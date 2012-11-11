<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_v303rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_v302');
	}

	function update_schema()
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

	function update_data()
	{
		return array(
			array('config.add', array('enable_queue_trigger', '0')),
			array('config.add', array('queue_trigger_posts', '3')),
			array('config.add', array('pm_max_recipients', '0')),
			array('custom', array(array(&$this, 'set_group_default_max_recipients'))),
			array('config.add', array('dbms_version', '')),
			array('permission.add', array('u_masspm_group', phpbb_auth::IS_GLOBAL),
			array('custom', array(array(&$this, 'correct_acp_email_permissions'))),
		));
	}

	function correct_acp_email_permissions()
	{
		$sql = 'UPDATE ' . $this->table_prefix . 'modules
			SET module_auth = \'acl_a_email && cfg_email_enable\'
			WHERE module_class = \'acp\'
				AND module_basename = \'email\'';
		$this->sql_query($sql);
	}

	function set_group_default_max_recipients()
	{
		// Set maximum number of recipients for the registered users, bots, guests group
		$sql = 'UPDATE ' . GROUPS_TABLE . ' SET group_max_recipients = 5
			WHERE ' . $this->db->sql_in_set('group_name', array('GUESTS', 'REGISTERED', 'REGISTERED_COPPA', 'BOTS'));
		$this->sql_query($sql);
	}
}
