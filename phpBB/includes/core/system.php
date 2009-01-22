<?php
/**
*
* @package core
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit();
}

/**
* System-specific methods. For example chmod(), unlink()...
*
* @package core
*/
class phpbb_system extends phpbb_plugin_support
{
	/**
	* @var array required phpBB objects
	*/
	public $phpbb_required = array();

	/**
	* @var array Optional phpBB objects
	*/
	public $phpbb_optional = array();

	/**
	* @var array Holding some information for chmod()
	*/
	private $chmod_info = array();

	/**
	* Method for chmodding directories and files for internal use.
	*
	* This function determines owner and group whom the file belongs to and user and group of PHP and then set safest possible file permissions.
	* The function determines owner and group from common.php file and sets the same to the provided file.
	* The function uses bit fields to build the permissions.
	* The function sets the appropiate execute bit on directories.
	*
	* Supported constants representing bit fields are:
	*
	* phpbb::CHMOD_ALL - all permissions (7)
	* phpbb::CHMOD_READ - read permission (4)
	* phpbb::CHMOD_WRITE - write permission (2)
	* phpbb::CHMOD_EXECUTE - execute permission (1)
	*
	* NOTE: The function uses POSIX extension and fileowner()/filegroup() functions. If any of them is disabled, this function tries to build proper permissions, by calling is_readable() and is_writable() functions.
	*
	* @param string	$filename	The file/directory to be chmodded
	* @param int	$perms		Permissions to set
	*
	* @return bool	true on success, otherwise false
	* @author faw, phpBB Group
	* @access public
	*/
	public function chmod($filename, $perms = phpbb::CHMOD_READ)
	{
		// Return if the file no longer exists.
		if (!file_exists($filename))
		{
			return false;
		}

		// Determine some common vars
		if (empty($this->chmod_info))
		{
			if (!function_exists('fileowner') || !function_exists('filegroup'))
			{
				// No need to further determine owner/group - it is unknown
				$this->chmod_info['process'] = false;
			}
			else
			{
				// Determine owner/group of common.php file and the filename we want to change here
				$common_php_owner = fileowner(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);
				$common_php_group = filegroup(PHPBB_ROOT_PATH . 'common.' . PHP_EXT);

				// And the owner and the groups PHP is running under.
				$php_uid = (function_exists('posix_getuid')) ? @posix_getuid() : false;
				$php_gids = (function_exists('posix_getgroups')) ? @posix_getgroups() : false;

				if (!$php_uid || empty($php_gids) || !$common_php_owner || !$common_php_group)
				{
					$this->chmod_info['process'] = false;
				}
				else
				{
					$this->chmod_info = array(
						'process'		=> true,
						'common_owner'	=> $common_php_owner,
						'common_group'	=> $common_php_group,
						'php_uid'		=> $php_uid,
						'php_gids'		=> $php_gids,
					);
				}
			}
		}

		if ($this->chmod_info['process'])
		{
			// Change owner
			if (@chown($filename, $this->chmod_info['common_owner']))
			{
				clearstatcache();
				$file_uid = fileowner($filename);
			}

			// Change group
			if (@chgrp($filename, $this->chmod_info['common_group']))
			{
				clearstatcache();
				$file_gid = filegroup($filename);
			}

			// If the file_uid/gid now match the one from common.php we can process further, else we are not able to change something
			if ($file_uid != $this->chmod_info['common_owner'] || $file_gid != $this->chmod_info['common_group'])
			{
				$this->chmod_info['process'] = false;
			}
		}

		// Still able to process?
		if ($this->chmod_info['process'])
		{
			if ($file_uid == $this->chmod_info['php_uid'])
			{
				$php = 'owner';
			}
			else if (in_array($file_gid, $this->chmod_info['php_gids']))
			{
				$php = 'group';
			}
			else
			{
				// Since we are setting the everyone bit anyway, no need to do expensive operations
				$this->chmod_info['process'] = false;
			}
		}

		// We are not able to determine or change something
		if (!$this->chmod_info['process'])
		{
			$php = 'other';
		}

		// Owner always has read/write permission
		$owner = phpbb::CHMOD_READ | phpbb::CHMOD_WRITE;
		if (is_dir($filename))
		{
			$owner |= phpbb::CHMOD_EXECUTE;

			// Only add execute bit to the permission if the dir needs to be readable
			if ($perms & phpbb::CHMOD_READ)
			{
				$perms |= phpbb::CHMOD_EXECUTE;
			}
		}

		switch ($php)
		{
			case 'owner':
				$result = @chmod($filename, ($owner << 6) + (0 << 3) + (0 << 0));

				clearstatcache();

				if (!is_null($php) || (is_readable($filename) && is_writable($filename)))
				{
					break;
				}

			case 'group':
				$result = @chmod($filename, ($owner << 6) + ($perms << 3) + (0 << 0));

				clearstatcache();

				if (!is_null($php) || ((!($perms & phpbb::CHMOD_READ) || is_readable($filename)) && (!($perms & phpbb::CHMOD_WRITE) || is_writable($filename))))
				{
					break;
				}

			case 'other':
				$result = @chmod($filename, ($owner << 6) + ($perms << 3) + ($perms << 0));

				clearstatcache();

				if (!is_null($php) || ((!($perms & phpbb::CHMOD_READ) || is_readable($filename)) && (!($perms & phpbb::CHMOD_WRITE) || is_writable($filename))))
				{
					break;
				}

			default:
				return false;
			break;
		}

		return $result;
	}

}

?>