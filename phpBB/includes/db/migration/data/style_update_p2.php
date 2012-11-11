<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_style_update_p2 extends phpbb_db_migration
{
	public function depends_on()
	{
		return array('phpbb_db_migration_data_style_update_p1');
	}

	public function update_schema()
	{
		return array(
			'drop_columns'	=> array(
				STYLES_TABLE		=> array(
					'imageset_id',
					'template_id',
					'theme_id',
				),
			),

			'drop_tables'	=> array(
				STYLES_IMAGESET_TABLE,
				STYLES_IMAGESET_DATA_TABLE,
				STYLES_TEMPLATE_TABLE,
				STYLES_TEMPLATE_DATA_TABLE,
				STYLES_THEME_TABLE,
			),
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'styles_update'))),
		);
	}

	public function styles_update()
	{
		// Remove old entries from styles table
		if (!sizeof($valid_styles))
		{
			// No valid styles: remove everything and add prosilver
			$this->sql_query('DELETE FROM ' . STYLES_TABLE, $errored, $error_ary);

			$sql = 'INSERT INTO ' . STYLES_TABLE . " (style_name, style_copyright, style_active, style_path, bbcode_bitfield, style_parent_id, style_parent_tree) VALUES ('prosilver', '&copy; phpBB Group', 1, 'prosilver', 'kNg=', 0, '')";
			$this->sql_query($sql);

			$sql = 'SELECT style_id
				FROM ' . $table . "
				WHERE style_name = 'prosilver'";
			$result = $this->sql_query($sql);
			$default_style = $this->db->sql_fetchfield($result);
			$this->db->sql_freeresult($result);

			set_config('default_style', $default_style);

			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_style = 0';
			$this->sql_query($sql);
		}
		else
		{
			// There are valid styles in styles table. Remove styles that are outdated
			$this->sql_query('DELETE FROM ' . STYLES_TABLE . ' WHERE ' . $this->db->sql_in_set('style_id', $valid_styles, true), $errored, $error_ary);

			// Change default style
			if (!in_array($config['default_style'], $valid_styles))
			{
				set_config('default_style', $valid_styles[0]);
			}

			// Reset styles for users
			$this->sql_query('UPDATE ' . USERS_TABLE . ' SET user_style = 0 WHERE ' . $this->db->sql_in_set('user_style', $valid_styles, true), $errored, $error_ary);
		}
	}
}
