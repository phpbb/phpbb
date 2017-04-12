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
	/** @var ContainerInterface */
	protected $container;

	protected $db;
	protected $config;
	protected $cache;
	protected $php_ext;
	protected $extensions;
	protected $extension_table;
	protected $phpbb_root_path;
	protected $cache_name;
	protected $user;

	/** @var \phpbb\textformatter\cache_interface */
	protected $text_formatter_cache;

	protected $reserved_style_names = array('adm', 'admin', 'all');

	protected $styles_path;
	protected $styles_path_absolute = 'styles';

	public function __construct(ContainerInterface $container, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\filesystem\filesystem_interface $filesystem, $style_table, $phpbb_root_path, $php_ext = 'php', \phpbb\user $user)
	{
		$this->config = $config;
		$this->container = $container;
		$this->db = $db;
		$this->style_table = $style_table;
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->user = $user;

		$this->text_formatter_cache = $container->get('text_formatter.cache');
		$this->default_style = $config['default_style'];
		$this->styles_path = $this->phpbb_root_path . $this->styles_path_absolute . '/';
	}

	public function install($dirs, &$messages)
	{
		// Get list of styles that can be installed
		$styles = $this->find_available(false);

		// Install each style
		$messages = array();
		$installed_names = array();
		$installed_dirs = array();
		foreach ($dirs as $dir)
		{
			if (in_array($dir, $this->reserved_style_names))
			{
				$messages[] = $this->user->lang('STYLE_NAME_RESERVED', htmlspecialchars($dir));
				continue;
			}

			$found = false;
			foreach ($styles as &$style)
			{
				// Check if:
				// 1. Directory matches directory we are looking for
				// 2. Style is not installed yet
				// 3. Style with same name or directory hasn't been installed already within this function
				if ($style['style_path'] == $dir && empty($style['_installed']) && !in_array($style['style_path'], $installed_dirs) && !in_array($style['style_name'], $installed_names))
				{
					// Install style
					$style['style_active'] = 1;
					$style['style_id'] = $this->install_style($style);
					$style['_installed'] = true;
					$found = true;
					$installed_names[] = $style['style_name'];
					$installed_dirs[] = $style['style_path'];
					$messages[] = sprintf($this->user->lang['STYLE_INSTALLED'], htmlspecialchars($style['style_name']));
				}
			}
			if (!$found)
			{
				$messages[] = sprintf($this->user->lang['STYLE_NOT_INSTALLED'], htmlspecialchars($dir));
			}
		}

		// Invalidate the text formatter's cache for the new styles to take effect
		if (!empty($installed_names))
		{
			$this->text_formatter_cache->invalidate();
		}

		return $messages;
	}

	public function uninstall($dir)
	{
		$style_data = $this->get_style_data($dir);

		if(!$style_data)
		{
			throw new exception('STYLE_NOT_FOUND'); // TODO: lang string
		}

		$id = $style_data['style_id'];
		$path = $style_data['style_path'];

		// Check if style has child styles
		$sql = 'SELECT style_id
			FROM ' . STYLES_TABLE . '
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
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_style = 0
			WHERE user_style = ' . $id;

		if(!$this->db->sql_query($sql))
		{
			throw new exception('STYLE_UNINSTALL_UNABLE_UPDATE_USERS'); // TODO: lang string
		}

		// Uninstall style
		$sql = 'DELETE FROM ' . STYLES_TABLE . '
			WHERE style_id = ' . $id;

		if(!$this->db->sql_query($sql))
		{
			throw new exception('STYLE_UNINSTALL_UNABLE_DELETE'); // TODO: lang string
		}
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

	protected function get_style_data($dir)
	{
		$sql = "SELECT *
			FROM " . STYLES_TABLE . "
			WHERE style_path = '" . $this->db->sql_escape($dir) . "'";
		$result = $this->db->sql_query($sql);

		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $data;
	}

	public function activate()
	{

	}

	public function deactivate()
	{

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
		$installed = $this->get_styles();

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
	* Lists all styles
	*
	* @return array Rows with styles data
	*/
	public function get_styles()
	{
		$sql = 'SELECT *
			FROM ' . STYLES_TABLE;
		$result = $this->db->sql_query($sql);

		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
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
		static $value;
		if (isset($value))
		{
			return $value;
		}

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

	/**
	* Install style
	*
	* @param array $style style data
	* @return int Style id
	*/
	protected function install_style($style)
	{
		global $user, $phpbb_log;

		// Generate row
		$sql_ary = array();
		foreach ($style as $key => $value)
		{
			if ($key != 'style_id' && substr($key, 0, 1) != '_')
			{
				$sql_ary[$key] = $value;
			}
		}

		// Add to database
		$this->db->sql_transaction('begin');

		$sql = 'INSERT INTO ' . STYLES_TABLE . '
			' . $this->db->sql_build_array('INSERT', $sql_ary);
		$this->db->sql_query($sql);

		$id = $this->db->sql_nextid();

		$this->db->sql_transaction('commit');

		$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_STYLE_ADD', false, array($sql_ary['style_name']));

		return $id;
	}

}
