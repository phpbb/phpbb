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

namespace phpbb\template;

class asset
{
	protected $components = array();

	/** @var \phpbb\path_helper **/
	protected $path_helper;

	/**
	* Constructor
	*
	* @param string $url URL
	* @param \phpbb\path_helper $path_helper Path helper object
	*/
	public function __construct($url, \phpbb\path_helper $path_helper)
	{
		$this->path_helper = $path_helper;

		$this->set_url($url);
	}

	/**
	* Set URL
	*
	* @param string $url URL
	*/
	public function set_url($url)
	{
		if (version_compare(PHP_VERSION, '5.4.7') < 0 && substr($url, 0, 2) === '//')
		{
			// Workaround for PHP 5.4.6 and older bug #62844 - add fake scheme and then remove it
			$this->components = parse_url('http:' . $url);
			$this->components['scheme'] = '';
			return;
		}
		$this->components = parse_url($url);
	}

	/**
	* Convert URL components into string
	*
	* @param array $components URL components
	* @return string URL
	*/
	protected function join_url($components)
	{
		$path = '';
		if (isset($components['scheme']))
		{
			$path = $components['scheme'] === '' ? '//' : $components['scheme'] . '://';
		}

		if (isset($components['user']) || isset($components['pass']))
		{
			if ($path === '' && !isset($components['port']))
			{
				$path = '//';
			}
			$path .= $components['user'];
			if (isset($components['pass']))
			{
				$path .= ':' . $components['pass'];
			}
			$path .= '@';
		}

		if (isset($components['host']))
		{
			if ($path === '' && !isset($components['port']))
			{
				$path = '//';
			}
			$path .= $components['host'];
			if (isset($components['port']))
			{
				$path .= ':' . $components['port'];
			}
		}

		if (isset($components['path']))
		{
			$path .= $components['path'];
		}

		if (isset($components['query']))
		{
			$path .= '?' . $components['query'];
		}

		if (isset($components['fragment']))
		{
			$path .= '#' . $components['fragment'];
		}

		return $path;
	}

	/**
	* Get URL
	*
	* @return string URL
	*/
	public function get_url()
	{
		return $this->path_helper->update_web_root_path($this->join_url($this->components));
	}

	/**
	* Checks if URL is local and relative
	*
	* @return boolean True if URL is local and relative
	*/
	public function is_relative()
	{
		if (empty($this->components) || !isset($this->components['path']))
		{
			// Invalid URL
			return false;
		}
		return !isset($this->components['scheme']) && !isset($this->components['host']) && substr($this->components['path'], 0, 1) !== '/';
	}

	/**
	* Get path component of current URL
	*
	* @return string Path
	*/
	public function get_path()
	{
		return isset($this->components['path']) ? $this->components['path'] : '';
	}

	/**
	* Set path component
	*
	* @param string $path Path component
	* @param boolean $urlencode If true, parts of path should be encoded with rawurlencode()
	*/
	public function set_path($path, $urlencode = false)
	{
		// Since 1.7.0 Twig returns the real path of the file. We need it to be relative to the working directory.
		$real_root_path = realpath($this->path_helper->get_phpbb_root_path()) . DIRECTORY_SEPARATOR;

		// If the asset is under the phpBB root path we need to remove its path and then prepend $phpbb_root_path
		if (substr($path . DIRECTORY_SEPARATOR, 0, strlen($real_root_path)) === $real_root_path)
		{
			$path = $this->path_helper->get_phpbb_root_path() . str_replace('\\', '/', substr($path, strlen($real_root_path)));
		}
		else
		{
			// Else we make the path relative to the current working directory
			$real_root_path = realpath('.') . DIRECTORY_SEPARATOR;
			if ($real_root_path && substr($path . DIRECTORY_SEPARATOR, 0, strlen($real_root_path)) === $real_root_path)
			{
				$path = str_replace('\\', '/', substr($path, strlen($real_root_path)));
			}
		}

		if ($urlencode)
		{
			$paths = explode('/', $path);
			foreach ($paths as &$dir)
			{
				$dir = rawurlencode($dir);
			}
			$path = implode('/', $paths);
		}

		$this->components['path'] = $path;
	}

	/**
	* Add assets_version parameter to URL.
	* Parameter will not be added if assets_version already exists in URL
	*
	* @param string $version Version
	*/
	public function add_assets_version($version)
	{
		if (!isset($this->components['query']))
		{
			$this->components['query'] = 'assets_version=' . $version;
			return;
		}
		$query = $this->components['query'];
		if (!preg_match('/(^|[&;])assets_version=/', $query))
		{
			$this->components['query'] = $query . '&amp;assets_version=' . $version;
		}
	}
}
