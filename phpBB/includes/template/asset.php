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
	* @param string $url phpBB URL
	*/
	public function __construct($url)
	{
		$this->set_url($url);
	}

	public function set_url($url)
	{
		if (version_compare(PHP_VERSION, '5.4.7') < 0 && substr($url, 0, 2) === '//')
		{
			// Workaround for PHP 5.4.6 and older bug #62844 - add fake scheme and then remove it
			$this->components = parse_url('http:' . $url);
			if (isset($result['port']))
			{
				return;
			}
			unset($result['scheme']);
			$this->components = $result;
			return;
		}
		$this->components = parse_url($url);
	}

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

	public function get_url()
	{
		return $this->join_url($this->components);
	}

	public function is_relative()
	{
		if (empty($this->components) || !isset($this->components['path']))
		{
			// Invalid URL
			return false;
		}
		return !isset($this->components['scheme']) && !isset($this->components['host']) && substr($this->components['path'], 0, 1) !== '/';
	}

	public function get_path()
	{
		return isset($this->components['path']) ? $this->components['path'] : '';
	}

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
			$separator = (strpos($query, '&') === false) && (strpos($query, ';') !== false) && preg_match('/^.*=.*;.*=.*$/', $query) ? ';' : '&amp;';
			$this->components['query'] = $query . $separator . 'assets_version=' . $version;
		}
	}
}
