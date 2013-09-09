<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use Symfony\Component\HttpFoundation\Request;

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
class phpbb_filesystem
{
	/** @var string */
	protected $phpbb_root_path;

	/**
	* Constructor
	*
	* @param string $phpbb_root_path
	*/
	public function __construct($phpbb_root_path)
	{
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	* Get the phpBB root path
	*
	* @return string
	*/
	public function get_phpbb_root_path()
	{
		return $this->phpbb_root_path;
	}

	/**
	* Update a path to the correct relative root path
	*
	* This replaces $phpbb_root_path . some_url with
	*	get_web_root_path() . some_url OR if $phpbb_root_path
	*	is not at the beginning of $path, just prepends the
	*	web root path
	*
	* @param Request $symfony_request Symfony Request object
	* @return string
	*/
	public function update_web_root_path($path, Request $symfony_request = null)
	{
		$web_root_path = $this->get_web_root_path($symfony_request);

		if (strpos($path, $this->phpbb_root_path) === 0)
		{
			$path = substr($path, strlen($this->phpbb_root_path));
		}

		return $web_root_path . $path;
	}

	/**
	* Get a relative root path from the current URL
	*
	* @param Request $symfony_request Symfony Request object
	* @return string
	*/
	public function get_web_root_path(Request $symfony_request = null)
	{
		if ($symfony_request === null)
		{
			return $this->phpbb_root_path;
		}

		static $path;
		if (null !== $path)
		{
			return $path;
		}

		$path_info = $symfony_request->getPathInfo();
		if ($path_info === '/')
		{
			return $path = $this->phpbb_root_path;
		}

		$path_info = $this->clean_path($path_info);

		// Do not count / at start of path
		$corrections = substr_count(substr($path_info, 1), '/');

		// When URL Rewriting is enabled, app.php is optional. We have to
		// correct for it not being there
		if (strpos($symfony_request->getRequestUri(), $symfony_request->getScriptName()) === false)
		{
			$corrections -= 1;
		}

		return $path = $this->phpbb_root_path . str_repeat('../', $corrections);
	}

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
