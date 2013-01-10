<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_3_0_4_rc1 extends phpbb_db_migration
{
	function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_3');
	}

	function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'profile_fields' => array(
					'field_show_profile' => array('BOOL', 0),
				),
			),
			'change_columns' => array(
				$this->table_prefix . 'styles' => array(
					'style_id' => array('UINT', NULL, 'auto_increment'),
					'template_id' => array('UINT', 0),
					'theme_id' => array('UINT', 0),
					'imageset_id' => array('UINT', 0),
				),
				$this->table_prefix . 'styles_imageset' => array(
					'imageset_id' => array('UINT', NULL, 'auto_increment'),
				),
				$this->table_prefix . 'styles_imageset_data' => array(
					'image_id' => array('UINT', NULL, 'auto_increment'),
					'imageset_id' => array('UINT', 0),
				),
				$this->table_prefix . 'styles_theme' => array(
					'theme_id' => array('UINT', NULL, 'auto_increment'),
				),
				$this->table_prefix . 'styles_template' => array(
					'template_id' => array('UINT', NULL, 'auto_increment'),
				),
				$this->table_prefix . 'styles_template_data' => array(
					'template_id' => array('UINT', 0),
				),
				$this->table_prefix . 'forums' => array(
					'forum_style' => array('UINT', 0),
				),
				$this->table_prefix . 'users' => array(
					'user_style' => array('UINT', 0),
				),
			),
		);
	}

	function update_data()
	{
		return array(
			array('custom', array(array(&$this, 'update_custom_profile_fields'))),

			array('config.update', array('version', '3.0.4-rc1')),
		);
	}

	function update_custom_profile_fields()
	{
		// Update the Custom Profile Fields based on previous settings to the new format
		$sql = 'SELECT field_id, field_required, field_show_on_reg, field_hide
				FROM ' . PROFILE_FIELDS_TABLE;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$sql_ary = array(
				'field_required'	=> 0,
				'field_show_on_reg'	=> 0,
				'field_hide'		=> 0,
				'field_show_profile'=> 0,
			);

			if ($row['field_required'])
			{
				$sql_ary['field_required'] = $sql_ary['field_show_on_reg'] = $sql_ary['field_show_profile'] = 1;
			}
			else if ($row['field_show_on_reg'])
			{
				$sql_ary['field_show_on_reg'] = $sql_ary['field_show_profile'] = 1;
			}
			else if ($row['field_hide'])
			{
				// Only administrators and moderators can see this CPF, if the view is enabled, they can see it, otherwise just admins in the acp_users module
				$sql_ary['field_hide'] = 1;
			}
			else
			{
				// equivelant to "none", which is the "Display in user control panel" option
				$sql_ary['field_show_profile'] = 1;
			}

			$this->sql_query('UPDATE ' . $this->table_prefix . 'profile_fields SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE field_id = ' . $row['field_id'], $errored, $error_ary);
		}

		$this->db->sql_freeresult($result);
	}
}
