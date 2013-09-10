<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb;
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* A class with various functions that are related to paths, files and the filesystem
* @package phpBB3
*/
class filesystem
{
	/**
	* Eliminates useless . and .. components from specified path.
	*
	* @param string $path Path to clean
	* @return string Cleaned path
	*/
	public function clean_path($path)
	{
		$exploded = explode('/', $path);
		$filtered = array();
		foreach ($exploded as $part)
		{
			if ($part === '.' && !empty($filtered))
			{
				continue;
			}

			if ($part === '..' && !empty($filtered) && $filtered[sizeof($filtered) - 1] !== '..')
			{
				array_pop($filtered);
			}
			else
			{
				$filtered[] = $part;
			}
		}
		$path = implode('/', $filtered);
		return $path;
	}
}
