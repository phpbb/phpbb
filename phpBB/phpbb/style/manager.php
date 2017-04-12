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

namespace phpbb\style;

use phpbb\style\exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* The extension manager provides means to activate/deactivate extensions.
*/
class manager
{
	protected $cache;

	/** @var ContainerInterface */
	protected $container;

	protected $db;
	protected $config;
	protected $filesystem;
	protected $styles_table;
	protected $users_table;
	protected $phpbb_root_path;

	/** @var \phpbb\textformatter\cache_interface */
	protected $text_formatter_cache;

	protected $reserved_style_names = array('adm', 'admin', 'all');

	protected $styles_path;
	protected $styles_path_absolute = 'styles';

	public function __construct(\phpbb\cache\service $cache = null, \phpbb\config\config $config, ContainerInterface $container, \phpbb\db\driver\driver_interface $db, \phpbb\filesystem\filesystem_interface $filesystem, $styles_table, $users_table, $phpbb_root_path)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->container = $container;
		$this->db = $db;
		$this->filesystem = $filesystem;
		$this->styles_table = $styles_table;
		$this->users_table = $users_table;
		$this->phpbb_root_path = $phpbb_root_path;

		$this->text_formatter_cache = $container->get('text_formatter.cache');
		$this->default_style = $config['default_style'];
		$this->styles_path = $this->phpbb_root_path . $this->styles_path_absolute . '/';
	}


	public function install($dir)
	{
		if (in_array($dir, $this->reserved_style_names))
		{
			throw new exception('STYLE_NAME_RESERVED');
		}

		if ($this->get_style_data('style_path', $dir))
		{
			throw new exception('STYLE_ENABLED');
		}

		$cfg = $this->read_style_cfg($dir);

		if (!$cfg)
		{
			throw new exception('STYLE_FOLDER_INVALID');
		}

		// Style should be available for installation
		$sql_ary = array(
			'style_name'		=> $cfg['name'],
			'style_copyright'	=> $cfg['copyright'],
			'style_active'		=> 1,
			'style_path'		=> $dir,
			'bbcode_bitfield'	=> $cfg['template_bitfield'],
			'style_parent_id'	=> 0,
			'style_parent_tree'	=> '',
		);

		if($cfg['parent'])
		{
			$parent_data = $this->get_style_name('style_name', $cfg['parent']);
			if($parent_data)
			{
				$sql_ary['style_parent_id'] = $parent_data['style_id'];
				$sql_ary['style_parent_tree'] = $parent_data['tree'];
			}
		}

		$sql = 'INSERT INTO ' . $this->styles_table . '
			' . $this->db->sql_build_array('INSERT', $sql_ary);

		if(!$this->db->sql_query($sql))
		{
			throw new exception('STYLE_NOT_INSTALLED');
		}

		$this->text_formatter_cache->invalidate();
	}

	public function uninstall($dir)
	{
		$style_data = $this->get_style_data('style_path', $dir);

		if(!$style_data)
		{
			throw new exception('STYLE_NOT_FOUND'); // TODO: lang string
		}

		$id = $style_data['style_id'];
		$path = $style_data['style_path'];

		// Check if style has child styles
		$sql = 'SELECT style_id
			FROM ' . $this->styles_table . '
			WHERE style_parent_id = ' . (int) $id . " OR style_parent_tree = '" . $this->db->sql_escape($path) . "'";
		$result = $this->db->sql_query($sql);

		if(!$result)
		{
			throw new exception('STYLE_UNINSTALL_UNABLE_CHECK_CHILD');
		}

		$conflict = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($conflict !== false)
		{
			throw new exception('STYLE_UNINSTALL_DEPENDENT');
		}

		// Change default style for users
		$sql = 'UPDATE ' . $this->users_table . '
			SET user_style = 0
			WHERE user_style = ' . $id;

		if(!$this->db->sql_query($sql))
		{
			throw new exception('STYLE_UNINSTALL_UNABLE_UPDATE_USERS'); // TODO: lang string
		}

		// Uninstall style
		$sql = 'DELETE FROM ' . $this->styles_table . '
			WHERE style_id = ' . $id;

		if(!$this->db->sql_query($sql))
		{
			throw new exception('STYLE_NOT_UNINSTALLED'); // TODO: lang string
		}
	}


	// TODO: check if ids exist, check if already active, create exception
	public function activate($ids)
	{
		// Activate styles
		$sql = 'UPDATE ' . $this->styles_table . '
			SET style_active = 1
			WHERE style_id IN (' . implode(', ', $ids) . ')';
		$this->db->sql_query($sql);

		// Purge cache
		$this->cache->destroy('sql', $this->styles_table);
	}


	// TODO: check if ids exist, check if already inactive, create exception
	public function deactivate($ids)
	{
		// Check for default style
		foreach ($ids as $id)
		{
			if ($id == $this->default_style)
			{
				trigger_error($this->user->lang['DEACTIVATE_DEFAULT'] . adm_back_link($this->u_action), E_USER_WARNING);
			}
		}

		// Reset default style for users who use selected styles
		$sql = 'UPDATE ' . $this->users_table . '
			SET user_style = 0
			WHERE user_style IN (' . implode(', ', $ids) . ')';
		$this->db->sql_query($sql);

		// Deactivate styles
		$sql = 'UPDATE ' . $this->styles_table . '
			SET style_active = 0
			WHERE style_id IN (' . implode(', ', $ids) . ')';
		$this->db->sql_query($sql);

		// Purge cache
		$this->cache->destroy('sql', $this->styles_table);
	}

	/**
	* Delete all files in style directory
	*
	* @param string $path Style directory
	* @param string $dir Directory to remove inside style's directory
	* @return bool True on success, false on error
	*/
	public function delete_style_files($path, $dir = '')
	{
		$dirname = $this->styles_path . $path . $dir;
		$result = true;

		$dp = @opendir($dirname);

		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				if ($file == '.' || $file == '..')
				{
					continue;
				}
				$filename = $dirname . '/' . $file;
				if (is_dir($filename))
				{
					if (!$this->delete_style_files($path, $dir . '/' . $file))
					{
						$result = false;
					}
				}
				else
				{
					if (!@unlink($filename))
					{
						$result = false;
					}
				}
			}
			closedir($dp);
		}
		if (!@rmdir($dirname))
		{
			$result = false;
		}

		if(!$result)
		{
			throw new exception('DELETE_STYLE_FILES_FAILED');
		}
	}

	/**
	* Find styles available for installation
	*
	* @param bool $all if true, function will return all installable styles. if false, function will return only styles that can be installed
	* @return array List of styles
	*/
	public function find_available($all)
	{
		// Get list of installed styles
		$installed = $this->get_installed_styles();

		$installed_dirs = array();
		$installed_names = array();
		foreach ($installed as $style)
		{
			$installed_dirs[] = $style['style_path'];
			$installed_names[$style['style_name']] = array(
				'path'		=> $style['style_path'],
				'id'		=> $style['style_id'],
				'parent'	=> $style['style_parent_id'],
				'tree'		=> (strlen($style['style_parent_tree']) ? $style['style_parent_tree'] . '/' : '') . $style['style_path'],
			);
		}

		// Get list of directories
		$dirs = $this->find_style_dirs();

		// Find styles that can be installed
		$styles = array();
		foreach ($dirs as $dir)
		{
			if (in_array($dir, $installed_dirs))
			{
				// Style is already installed
				continue;
			}
			$cfg = $this->read_style_cfg($dir);
			if ($cfg === false)
			{
				// Invalid style.cfg
				continue;
			}

			// Style should be available for installation
			$parent = $cfg['parent'];
			$style = array(
				'style_id'			=> 0,
				'style_name'		=> $cfg['name'],
				'style_copyright'	=> $cfg['copyright'],
				'style_active'		=> 0,
				'style_path'		=> $dir,
				'bbcode_bitfield'	=> $cfg['template_bitfield'],
				'style_parent_id'	=> 0,
				'style_parent_tree'	=> '',
				// Extra values for styles list
				// All extra variable start with _ so they won't be confused with data that can be added to styles table
				'_inherit_name'			=> $parent,
				'_available'			=> true,
				'_note'					=> '',
			);

			// Check style inheritance
			if ($parent != '')
			{
				if (isset($installed_names[$parent]))
				{
					// Parent style is installed
					$row = $installed_names[$parent];
					$style['style_parent_id'] = $row['id'];
					$style['style_parent_tree'] = $row['tree'];
				}
				else
				{
					// Parent style is not installed yet
					$style['_available'] = false;
					$style['_note'] = sprintf($this->user->lang['REQUIRES_STYLE'], htmlspecialchars($parent));
				}
			}

			if ($all || $style['_available'])
			{
				$styles[] = $style;
			}
		}

		return $styles;
	}

	/**
	* Find all directories that have styles
	*
	* @return array Directory names
	*/
	protected function find_style_dirs()
	{
		$styles = array();

		$dp = @opendir($this->styles_path);
		if ($dp)
		{
			while (($file = readdir($dp)) !== false)
			{
				$dir = $this->styles_path . $file;
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

	/**
	* Lists all styles
	*
	* @return array Rows with styles data
	*/
	public function get_installed_styles()
	{
		$sql = 'SELECT *
			FROM ' . $this->styles_table;
		$result = $this->db->sql_query($sql);

		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	protected function get_style_data($field, $value)
	{
		// TODO: Review this, possible security issue
		// if not, maybe field doesnt need to be escaped
		$sql = "SELECT *
			FROM " . $this->styles_table . "
			WHERE " . $this->db->sql_escape($field) . " = '" . $this->db->sql_escape($value) . "'";
		$result = $this->db->sql_query($sql);

		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	/**
	* Read style configuration file
	*
	* @param string $dir style directory
	* @return array|bool Style data, false on error
	*/
	public function read_style_cfg($dir)
	{
		static $required = array('name', 'phpbb_version', 'copyright');
		$cfg = parse_cfg_file($this->styles_path . $dir . '/style.cfg');

		// Check if it is a valid file
		foreach ($required as $key)
		{
			if (!isset($cfg[$key]))
			{
				return false;
			}
		}

		// Check data
		if (!isset($cfg['parent']) || !is_string($cfg['parent']) || $cfg['parent'] == $cfg['name'])
		{
			$cfg['parent'] = '';
		}
		if (!isset($cfg['template_bitfield']))
		{
			$cfg['template_bitfield'] = $this->default_bitfield();
		}

		return $cfg;
	}

	/**
	* Generates default bitfield
	*
	* This bitfield decides which bbcodes are defined in a template.
	*
	* @return string Bitfield
	*/
	protected function default_bitfield()
	{
		// Hardcoded template bitfield to add for new templates
		$bitfield = new \phpbb\bitfield();
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
}
