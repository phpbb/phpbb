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

class style_update extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\gold');
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'update_installed_styles'))),
		);
	}

	public function update_installed_styles()
	{
		// First check if prosilver is properly installed
		$sql = 'SELECT style_id, style_active
				FROM ' . $this->table_prefix . "styles
				WHERE style_name = 'prosilver'";
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Make sure prosilver is installed
		if (empty($row) || !isset($row['style_id']))
		{
			// Try to parse config file
			$cfg = parse_cfg_file($this->phpbb_root_path . 'styles/prosilver/style.cfg');

			// Stop running this if prosilver doesn't exist
			if (empty($cfg))
			{
				return;
			}

			// Check data
			if (!isset($cfg['template_bitfield']))
			{
				$cfg['template_bitfield'] = $this->default_bitfield();
			}

			$style = array(
				'style_name'		=> 'prosilver',
				'style_copyright'	=> '&copy; phpBB Limited',
				'style_active'		=> 1,
				'style_path'		=> 'prosilver',
				'bbcode_bitfield'	=> $cfg['template_bitfield'],
				'style_parent_id'	=> 0,
				'style_parent_tree'	=> '',
			);

			// Add to database
			$this->db->sql_transaction('begin');

			$sql = 'INSERT INTO ' . $this->table_prefix . 'styles
					' . $this->db->sql_build_array('INSERT', $style);
			$this->db->sql_query($sql);

			$row = array('style_id'		=> $this->db->sql_nextid());

			$this->db->sql_transaction('commit');
		}
		// Make sure prosilver is activated
		else if (!isset($row['style_active']) || !$row['style_active'])
		{
			$sql = 'UPDATE ' . STYLES_TABLE . ' SET style_active = 1 WHERE style_id = ' . $row['style_id'];
			$this->db->sql_query($sql);
		}

		// Get all currently available styles
		$styles = $this->find_style_dirs();

		// Get IDs of the available styles
		$style_ids = array();
		$sql = 'SELECT DISTINCT(style_id) AS style_id
				FROM ' . $this->table_prefix . 'styles
				WHERE ' . $this->db->sql_in_set('style_name', $styles);
		$result = $this->db->sql_query($sql);
		while ($styles_row = $this->db->sql_fetchrow())
		{
			$style_ids[] = $styles_row['style_id'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_style = {$row['style_id']}
			WHERE " . $this->db->sql_in_set('user_style', $style_ids, true);
		$this->db->sql_query($sql);
	}

	/**
	 * Generates default bitfield
	 * Copied from acp_styles
	 *
	 * This bitfield decides which bbcodes are defined in a template.
	 *
	 * @return string Bitfield
	 */
	protected function default_bitfield()
	{
		static $value;
		if (isset($value))
		{
			return $value;
		}

		if (!class_exists('bitfield'))
		{
			include($this->phpbb_root_path . 'includes/functions_content.' . $this->php_ext);
		}

		// Hardcoded template bitfield to add for new templates
		$bitfield = new \bitfield();
		$bitfield->set(0);
		$bitfield->set(1);
		$bitfield->set(2);
		$bitfield->set(3);
		$bitfield->set(4);
		$bitfield->set(8);
		$bitfield->set(9);
		$bitfield->set(11);
		$bitfield->set(12);
		$value = $bitfield->get_base64();
		return $value;
	}

	/**
	 * Find all directories that have styles
	 * Copied from acp_styles
	 *
	 * @return array Directory names
	 */
	protected function find_style_dirs()
	{
		$styles = array();
		$styles_path = $this->phpbb_root_path . 'styles/';

		$dp = @opendir($styles_path);
		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				$dir = $styles_path . $file;
				if ($file[0] == '.' || !is_dir($dir))
				{
					continue;
				}

				if (file_exists("{$dir}/style.cfg"))
				{
					$styles[] = $file;
				}
			}
			closedir($dp);
		}

		return $styles;
	}
}
