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

namespace phpbb\db\migration\data\v310;

class style_update_p1 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return !$this->db_tools->sql_table_exists($this->table_prefix . 'styles_imageset');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v30x\release_3_0_11');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'styles'		=> array(
					'style_path'			=> array('VCHAR:100', ''),
					'bbcode_bitfield'		=> array('VCHAR:255', 'kNg='),
					'style_parent_id'		=> array('UINT', 0),
					'style_parent_tree'		=> array('TEXT', ''),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'	=> array(
				$this->table_prefix . 'styles'		=> array(
					'style_path',
					'bbcode_bitfield',
					'style_parent_id',
					'style_parent_tree',
				),
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
		// Get list of valid 3.1 styles
		$available_styles = array('prosilver');

		$iterator = new \DirectoryIterator($this->phpbb_root_path . 'styles');
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
		if ($this->db_tools->sql_table_exists($this->table_prefix . 'styles_imageset'))
		{
			$sql = 'SELECT s.style_id, t.template_path, t.template_id, t.bbcode_bitfield, t.template_inherits_id, t.template_inherit_path, c.theme_path, c.theme_id, i.imageset_path
				FROM ' . STYLES_TABLE . ' s, ' . $this->table_prefix . 'styles_template t, ' . $this->table_prefix . 'styles_theme c, ' . $this->table_prefix . "styles_imageset i
				WHERE t.template_id = s.template_id
					AND c.theme_id = s.theme_id
					AND i.imageset_id = s.imageset_id";
		}
		else
		{
			$sql = 'SELECT s.style_id, t.template_path, t.template_id, t.bbcode_bitfield, t.template_inherits_id, t.template_inherit_path, c.theme_path, c.theme_id
				FROM ' . STYLES_TABLE . ' s, ' . $this->table_prefix . 'styles_template t, ' . $this->table_prefix . "styles_theme c
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
				$this->sql_query('UPDATE ' . STYLES_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE style_id = ' . $style_row['style_id']);
				$valid_styles[] = (int) $style_row['style_id'];
			}
		}

		// Remove old entries from styles table
		if (!count($valid_styles))
		{
			// No valid styles: remove everything and add prosilver
			$this->sql_query('DELETE FROM ' . STYLES_TABLE);

			$sql_ary = array(
				'style_name'		=> 'prosilver',
				'style_copyright'	=> '&copy; phpBB Limited',
				'style_active'		=> 1,
				'style_path'		=> 'prosilver',
				'bbcode_bitfield'	=> 'lNg=',
				'style_parent_id'	=> 0,
				'style_parent_tree'	=> '',

				// Will be removed in the next step
				'imageset_id'		=> 0,
				'template_id'		=> 0,
				'theme_id'			=> 0,
			);

			$sql = 'INSERT INTO ' . STYLES_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
			$this->sql_query($sql);

			$sql = 'SELECT style_id
				FROM ' . STYLES_TABLE . "
				WHERE style_name = 'prosilver'";
			$result = $this->sql_query($sql);
			$default_style = (int) $this->db->sql_fetchfield('style_id');
			$this->db->sql_freeresult($result);

			$this->config->set('default_style', $default_style);

			$sql = 'UPDATE ' . USERS_TABLE . ' SET user_style = ' .  (int) $default_style;
			$this->sql_query($sql);
		}
		else
		{
			// There are valid styles in styles table. Remove styles that are outdated
			$this->sql_query('DELETE FROM ' . STYLES_TABLE . '
				WHERE ' . $this->db->sql_in_set('style_id', $valid_styles, true));

			// Change default style
			if (!in_array($this->config['default_style'], $valid_styles))
			{
				$this->sql_query('UPDATE ' . CONFIG_TABLE . "
					SET config_value = '" . $valid_styles[0] . "'
					WHERE config_name = 'default_style'");
			}

			// Reset styles for users
			$this->sql_query('UPDATE ' . USERS_TABLE . "
				SET user_style = '" . (int) $valid_styles[0] . "'
				WHERE " . $this->db->sql_in_set('user_style', $valid_styles, true));
		}
	}
}
