<?php
/**
*
* @package migration
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

class phpbb_db_migration_data_style_update_p1 extends phpbb_db_migration
{
	public function depends_on()
	{
		return array('phpbb_db_migration_data_3_0_11');
	}

	public function update_schema()
	{
		return array();
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'styles_update'))),
		);
	}

	public function styles_update()
	{
		// Get list of valid 3.1 styles
		$available_styles = array('prosilver');

		$iterator = new DirectoryIterator($phpbb_root_path . 'styles');
		$skip_dirs = array('.', '..', 'prosilver');
		foreach ($iterator as $fileinfo)
		{
			if ($fileinfo->isDir() && !in_array($fileinfo->getFilename(), $skip_dirs) && file_exists($fileinfo->getPathname() . '/style.cfg'))
			{
				$style_cfg = parse_cfg_file($fileinfo->getPathname() . '/style.cfg');
				if (isset($style_cfg['phpbb_version']) && version_compare($style_cfg['phpbb_version'], '3.1.0-dev', '>='))
				{
					// 3.1 style
					$available_styles[] = $fileinfo->getFilename();
				}
			}
		}

		// Get all installed styles
		if ($this->db_tools->sql_table_exists(STYLES_IMAGESET_TABLE))
		{
			$sql = 'SELECT s.style_id, t.template_path, t.template_id, t.bbcode_bitfield, t.template_inherits_id, t.template_inherit_path, c.theme_path, c.theme_id, i.imageset_path
				FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . ' c, ' . STYLES_IMAGESET_TABLE . " i
				WHERE t.template_id = s.template_id
					AND c.theme_id = s.theme_id
					AND i.imageset_id = s.imageset_id";
		}
		else
		{
			$sql = 'SELECT s.style_id, t.template_path, t.template_id, t.bbcode_bitfield, t.template_inherits_id, t.template_inherit_path, c.theme_path, c.theme_id
				FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . " c
				WHERE t.template_id = s.template_id
					AND c.theme_id = s.theme_id";
		}
		$result = $this->db->sql_query($sql);

		$styles = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$styles[] = $row;
		}
		$this->db->sql_freeresult($result);

		// Decide which styles to keep, all others will be deleted
		$valid_styles = array();
		foreach ($styles as $style_row)
		{
			if (
				// Delete styles with parent style (not supported yet)
				$style_row['template_inherits_id'] == 0 &&
				// Check if components match
				$style_row['template_path'] == $style_row['theme_path'] && (!isset($style_row['imageset_path']) || $style_row['template_path'] == $style_row['imageset_path']) &&
				// Check if components are valid
				in_array($style_row['template_path'], $available_styles)
				)
			{
				// Valid style. Keep it
				$sql_ary = array(
					'style_path'	=> $style_row['template_path'],
					'bbcode_bitfield'	=> $style_row['bbcode_bitfield'],
					'style_parent_id'	=> 0,
					'style_parent_tree'	=> '',
				);
				$this->sql_query('UPDATE ' . STYLES_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE style_id = ' . $style_row['style_id'], $errored, $error_ary);
				$valid_styles[] = (int) $style_row['style_id'];
			}
		}
	}
}
