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
class path_helper
{
	/** @var \phpbb\symfony_request */
	protected $symfony_request;

	/** @var \phpbb\filesystem */
	protected $filesystem;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $adm_relative_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $web_root_path;

	/**
	* Constructor
	*
	* @param \phpbb\symfony_request $symfony_request
	* @param \phpbb\filesystem $filesystem
	* @param string $phpbb_root_path Relative path to phpBB root
	* @param string $php_ext PHP extension (php)
	*/
	public function __construct(\phpbb\symfony_request $symfony_request, \phpbb\filesystem $filesystem, $phpbb_root_path, $php_ext, $adm_relative_path = null)
	{
		$this->symfony_request = $symfony_request;
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->adm_relative_path = $adm_relative_path;
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
	* Get the adm root path
	*
	* @return string
	*/
	public function get_adm_relative_path()
	{
		return $this->adm_relative_path;
	}

	/**
	* Get the php extension
	*
	* @return string
	*/
	public function get_php_ext()
	{
		return $this->php_ext;
	}

	/**
	* Update a path to the correct relative root path
	*
	* This replaces $phpbb_root_path . some_url with
	*	get_web_root_path() . some_url OR if $phpbb_root_path
	*	is not at the beginning of $path, just prepends the
	*	web root path
	*
	* @param string $path The path to be updated
	* @return string
	*/
	public function update_web_root_path($path)
	{
		$web_root_path = $this->get_web_root_path($this->symfony_request);

		if (strpos($path, $this->phpbb_root_path) === 0)
		{
			$path = substr($path, strlen($this->phpbb_root_path));
		}

		return $web_root_path . $path;
	}

	/**
	* Get a relative root path from the current URL
	*
	* @return string
	*/
	public function get_web_root_path()
	{
		if ($this->symfony_request === null)
		{
			return $this->phpbb_root_path;
		}

		if (null !== $this->web_root_path)
		{
			return $this->web_root_path;
		}

		// Path info (e.g. /foo/bar)
		$path_info = $this->filesystem->clean_path($this->symfony_request->getPathInfo());

		// Full request URI (e.g. phpBB/app.php/foo/bar)
		$request_uri = $this->symfony_request->getRequestUri();

		// Script name URI (e.g. phpBB/app.php)
		$script_name = $this->symfony_request->getScriptName();

		/*
		* If the path info is empty (single /), then we're not using
		*	a route like app.php/foo/bar
		*/
		if ($path_info === '/')
		{
			return $this->web_root_path = $this->phpbb_root_path;
		}

		// How many corrections might we need?
		$corrections = substr_count($path_info, '/');

		/*
		* If the script name (e.g. phpBB/app.php) exists in the
		*	requestUri (e.g. phpBB/app.php/foo/template), then we
		*	are have a non-rewritten URL.
		*/
		if (strpos($request_uri, $script_name) === 0)
		{
			/*
			* Append ../ to the end of the phpbb_root_path as many times
			*	as / exists in path_info
			*/
			return $this->web_root_path = $this->phpbb_root_path . str_repeat('../', $corrections);
		}

		/*
		* If we're here it means we're at a re-written path, so we must
		*	correct the relative path for web URLs. We must append ../
		*	to the end of the root path as many times as / exists in path_info
		*	less one time (because the script, e.g. /app.php, doesn't exist in
		*	the URL)
		*/
		return $this->web_root_path = $this->phpbb_root_path . str_repeat('../', $corrections - 1);
	}
}
