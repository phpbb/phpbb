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
		// Get all currently available styles
		$styles = $this->find_style_dirs();
		$style_paths = $style_ids = array();

		$sql = 'SELECT style_path, style_id
				FROM ' . $this->table_prefix . 'styles';
		$result = $this->db->sql_query($sql);
		while ($styles_row = $this->db->sql_fetchrow())
		{
			if (in_array($styles_row['style_path'], $styles))
			{
				$style_paths[] = $styles_row['style_path'];
				$style_ids[] = $styles_row['style_id'];
			}
		}
		$this->db->sql_freeresult($result);

		// Install prosilver if no style is available and prosilver can be installed
		if (empty($style_paths) && in_array('prosilver', $styles))
		{
			// Try to parse config file
			$cfg = parse_cfg_file($this->phpbb_root_path . 'styles/prosilver/style.cfg');

			// Stop running this if prosilver cfg file can't be read
			if (empty($cfg))
			{
				throw new \RuntimeException('No styles available and could not fall back to prosilver.');
			}

			$style = array(
				'style_name'		=> 'prosilver',
				'style_copyright'	=> '&copy; phpBB Limited',
				'style_active'		=> 1,
				'style_path'		=> 'prosilver',
				'bbcode_bitfield'	=> 'kNg=',
				'style_parent_id'	=> 0,
				'style_parent_tree'	=> '',
			);

			// Add to database
			$this->db->sql_transaction('begin');

			$sql = 'INSERT INTO ' . $this->table_prefix . 'styles
					' . $this->db->sql_build_array('INSERT', $style);
			$this->db->sql_query($sql);

			$style_id = $this->db->sql_nextid();
			$style_ids[] = $style_id;

			$this->db->sql_transaction('commit');

			// Set prosilver to default style
			$this->config->set('default_style', $style_id);
		}
		else if (empty($styles) && empty($available_styles))
		{
			throw new \RuntimeException('No valid styles available');
		}

		// Make sure default style is available
		if (!in_array($this->config['default_style'], $style_ids))
		{
			$this->config->set('default_style', array_pop($style_ids));
		}

		// Reset users to default style if their user_style is nonexistent
		$sql = 'UPDATE ' . $this->table_prefix . "users
			SET user_style = {$this->config['default_style']}
			WHERE " . $this->db->sql_in_set('user_style', $style_ids, true, true);
		$this->db->sql_query($sql);
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
