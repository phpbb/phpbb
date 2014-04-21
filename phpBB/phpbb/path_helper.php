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

namespace phpbb;

/**
* A class with various functions that are related to paths, files and the filesystem
*/
class path_helper
{
	/** @var \phpbb\symfony_request */
	protected $symfony_request;

	/** @var \phpbb\filesystem */
	protected $filesystem;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\config\config */
	protected $config;

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
	* @param \phpbb\request\request $request
	* @param \phpbb\config\config $config
	* @param string $phpbb_root_path Relative path to phpBB root
	* @param string $php_ext PHP extension (php)
	*/
	public function __construct(\phpbb\symfony_request $symfony_request, \phpbb\filesystem $filesystem, \phpbb\request\request $request, \phpbb\config\config $config, $phpbb_root_path, $php_ext, $adm_relative_path = null)
	{
		$this->symfony_request = $symfony_request;
		$this->filesystem = $filesystem;
		$this->request = $request;
		$this->config = $config;
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
	* Update a web path to the correct relative root path
	*
	* This replaces $phpbb_root_path . some_url with
	*	get_web_root_path() . some_url
	*
	* @param string $path The path to be updated
	* @return string
	*/
	public function update_web_root_path($path)
	{
		if (strpos($path, $this->phpbb_root_path) === 0)
		{
			$path = substr($path, strlen($this->phpbb_root_path));

			return $this->filesystem->clean_path($this->get_web_root_path() . $path);
		}

		return $path;
	}

	/**
	* Strips away the web root path and prepends the normal root path
	*
	* This replaces get_web_root_path() . some_url with
	*	$phpbb_root_path . some_url
	*
	* @param string $path The path to be updated
	* @return string
	*/
	public function remove_web_root_path($path)
	{
		if (strpos($path, $this->get_web_root_path()) === 0)
		{
			$path = substr($path, strlen($this->get_web_root_path()));

			return $this->phpbb_root_path . $path;
		}

		return $path;
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
		* If the path info is empty but we're using app.php, then we
		*	might be using an empty route like app.php/ which is
		*	supported by symfony's routing
		*/
		if ($path_info === '/' && preg_match('/app\.' . $this->php_ext . '\/$/', $request_uri))
		{
			return $this->web_root_path = $this->filesystem->clean_path('./../' . $this->phpbb_root_path);
		}

		/*
		* If the path info is empty (single /), then we're not using
		*	a route like app.php/foo/bar
		*/
		if ($path_info === '/')
		{
			return $this->web_root_path = $this->phpbb_root_path;
		}

		/*
		* Check AJAX request
		*/
		if ($this->request->is_ajax())
		{
			// Check referer
			$referer = strtolower($this->request->header('Referer'));

			// Count chars
			$chars = strlen($this->config['server_name'] . $this->config['script_path']) - 1;

			/*
			* Return string without server name and script path
			*	e.g. 'http://localhost/phpBB/app.php', where server name is 'localhost'
			*	and script path is '/phpBB', will be cut to '/app.php'
			*/ 
			$ref = substr(strstr($referer, strtolower($this->config['server_name'] . $this->config['script_path'])), $chars);

			// How many slashes does the referer used?
			$count_slashes = substr_count($ref, '/');

			/*
			* If the shorten referer has only 1 slash,
			*	return default path
			*/
			if ($count_slashes == 1)
			{
				return $this->web_root_path = $this->phpbb_root_path;
			}
			/*
			* Otherwise we are on routed page so we must correct the relative path
			*	for web URLs. We must append ../ to the end of the root path
			*	as many times as / exists in shorten referer less one time
			*/
			else
			{
				return $this->web_root_path = $this->phpbb_root_path . str_repeat('../', $count_slashes - 1);
			}
		}

		// How many corrections might we need? 
		$corrections = substr_count($path_info, '/');

		/*
		* If the script name (e.g. phpBB/app.php) does not exists in the
		* requestUri (e.g. phpBB/app.php/foo/template), then we are rewriting
		* the URL. So we must reduce the slash count by 1.
		*/
		if (strpos($request_uri, $script_name) !== 0)
		{
			$corrections--;
		}

		// Prepend ../ to the phpbb_root_path as many times as / exists in path_info
		$this->web_root_path = $this->filesystem->clean_path(
			'./' . str_repeat('../', $corrections) . $this->phpbb_root_path
		);
		return $this->web_root_path;
	}

	/**
	* Eliminates useless . and .. components from specified URL
	*
	* @param string $url URL to clean
	*
	* @return string Cleaned URL
	*/
	public function clean_url($url)
	{
		$delimiter_position = strpos($url, '://');
		// URL should contain :// but it shouldn't start with it.
		// Do not clean URLs that do not fit these constraints.
		if (empty($delimiter_position))
		{
			return $url;
		}
		$scheme = substr($url, 0, $delimiter_position) . '://';
		// Add length of URL delimiter to position
		$path = substr($url, $delimiter_position + 3);

		return $scheme . $this->filesystem->clean_path($path);
	}

	/**
	* Glue URL parameters together
	*
	* @param array $params URL parameters in the form of array(name => value)
	* @return string Returns the glued string, e.g. name1=value1&amp;name2=value2
	*/
	public function glue_url_params($params)
	{
		$_params = array();

		foreach ($params as $key => $value)
		{
			$_params[] = $key . '=' . $value;
		}
		return implode('&amp;', $_params);
	}

	/**
	* Get the base and parameters of a URL
	*
	* @param string $url URL to break apart
	* @param bool $is_amp Is the parameter separator &amp;. Defaults to true.
	* @return array Returns the base and parameters in the form of array('base' => string, 'params' => array(name => value))
	*/
	public function get_url_parts($url, $is_amp = true)
	{
		$separator = ($is_amp) ? '&amp;' : '&';
		$params = array();

		if (strpos($url, '?') !== false)
		{
			$base = substr($url, 0, strpos($url, '?'));
			$args = substr($url, strlen($base) + 1);
			$args = ($args) ? explode($separator, $args) : array();

			foreach ($args as $argument)
			{
				if (empty($argument))
				{
					continue;
				}
				list($key, $value) = explode('=', $argument, 2);

				if ($key === '')
				{
					continue;
				}

				$params[$key] = $value;
			}
		}
		else
		{
			$base = $url;
		}

		return array(
			'base'		=> $base,
			'params'	=> $params,
		);
	}

	/**
	* Strip parameters from an already built URL.
	*
	* @param string $url URL to strip parameters from
	* @param array|string $strip Parameters to strip.
	* @param bool $is_amp Is the parameter separator &amp;. Defaults to true.
	* @return string Returns the new URL.
	*/
	public function strip_url_params($url, $strip, $is_amp = true)
	{
		$url_parts = $this->get_url_parts($url, $is_amp);
		$params = $url_parts['params'];

		if (!is_array($strip))
		{
			$strip = array($strip);
		}

		if (!empty($params))
		{
			// Strip the parameters off
			foreach ($strip as $param)
			{
				unset($params[$param]);
			}
		}

		return $url_parts['base'] . (($params) ? '?' . $this->glue_url_params($params) : '');
	}

	/**
	* Append parameters to an already built URL.
	*
	* @param string $url URL to append parameters to
	* @param array $new_params Parameters to add in the form of array(name => value)
	* @param bool $is_amp Is the parameter separator &amp;. Defaults to true.
	* @return string Returns the new URL.
	*/
	public function append_url_params($url, $new_params, $is_amp = true)
	{
		$url_parts = $this->get_url_parts($url, $is_amp);
		$params = array_merge($url_parts['params'], $new_params);

		// Move the sid to the end if it's set
		if (isset($params['sid']))
		{
			$sid = $params['sid'];
			unset($params['sid']);
			$params['sid'] = $sid;
		}

		return $url_parts['base'] . (($params) ? '?' . $this->glue_url_params($params) : '');
	}
}
