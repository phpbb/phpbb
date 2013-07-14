<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class phpbb_template_asset
{
	protected $components = array();

	/**
	* Constructor
	*
	* @param string $url URL
	*/
	public function __construct($url)
	{
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
		return $this->join_url($this->components);
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
