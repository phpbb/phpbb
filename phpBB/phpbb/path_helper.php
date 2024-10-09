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

	/** @var \phpbb\filesystem\filesystem_interface */
	protected $filesystem;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $adm_relative_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $web_root_path;

	/** @var bool Flag whether we're in adm path */
	protected $in_adm_path = false;

	/**
	* Constructor
	*
	* @param \phpbb\symfony_request $symfony_request
	* @param \phpbb\filesystem\filesystem_interface $filesystem
	* @param \phpbb\request\request_interface $request
	* @param string $phpbb_root_path Relative path to phpBB root
	* @param string $php_ext PHP file extension
	* @param mixed $adm_relative_path Relative path admin path to adm/ root
	*/
	public function __construct(\phpbb\symfony_request $symfony_request, \phpbb\filesystem\filesystem_interface $filesystem, \phpbb\request\request_interface $request, $phpbb_root_path, $php_ext, $adm_relative_path = null)
	{
		$this->symfony_request = $symfony_request;
		$this->filesystem = $filesystem;
		$this->request = $request;
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
		$web_root_path = $this->get_web_root_path();

		// Removes the web root path if it is already present
		if (strpos($path, $web_root_path) === 0)
		{
			$path = $this->phpbb_root_path . substr($path, strlen($web_root_path));
		}

		if (strpos($path, $this->phpbb_root_path) === 0)
		{
			$path = substr($path, strlen($this->phpbb_root_path));

			if (substr($web_root_path, -8) === 'app.php/' && substr($path, 0, 7) === 'app.php')
			{
				$path = substr($path, 8);
			}

			$path = $this->filesystem->clean_path($web_root_path . $path);

			// Further clean path if we're in adm
			if ($this->in_adm_path && strpos($path, $this->phpbb_root_path . $this->adm_relative_path) === 0)
			{
				$path = substr($path, strlen($this->phpbb_root_path . $this->adm_relative_path));
			}
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
		if (null !== $this->web_root_path)
		{
			return $this->web_root_path;
		}

		if (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH)
		{
			return $this->web_root_path = generate_board_url() . '/';
		}

		// We do not need to escape $path_info, $request_uri and $script_name because we can not find their content in the result.
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

		if ($path_info === '/' && defined('ADMIN_START') && preg_match('/\/' . preg_quote($this->adm_relative_path, '/') . 'index\.' . $this->php_ext . '$/', $script_name))
		{
			$this->in_adm_path = true;
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
		* Check AJAX request:
		* If the current request is a AJAX we need to fix the paths.
		* We need to get the root path based on the Referer, so we can use
		* the generated URLs in the template of the Referer. If we do not
		* generate the relative path based on the Referer, but based on the
		* currently requested URL, the generated URLs will not point to the
		* intended locations:
		*	Referer				desired URL			desired relative root path
		*	memberlist.php		faq.php				./
		*	memberlist.php		app.php/foo/bar		./
		*	app.php/foo			memberlist.php		../
		*	app.php/foo			app.php/fox			../
		*	app.php/foo/bar		memberlist.php		../../
		*	../page.php			memberlist.php		./phpBB/
		*	../sub/page.php		memberlist.php		./../phpBB/
		*
		* The referer must be specified as a parameter in the query.
		*/
		if ($this->request->is_ajax() && $this->request->header('Referer'))
		{
			// We need to escape $absolute_board_url because it can be partially concatenated to the result.
			$absolute_board_url = $this->request->escape($this->symfony_request->getSchemeAndHttpHost() . $this->symfony_request->getBasePath(), true);

			$referer_web_root_path = $this->get_web_root_path_from_ajax_referer(
				$this->request->header('Referer'),
				$absolute_board_url
			);
			return $this->web_root_path = $referer_web_root_path;
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
			'./' . str_repeat('../', max(0, $corrections)) . $this->phpbb_root_path
		);
		return $this->web_root_path;
	}

	/**
	* Get the web root path of the referer form an ajax request
	*
	* @param string $absolute_referer_url
	* @param string $absolute_board_url
	* @return string
	*/
	public function get_web_root_path_from_ajax_referer($absolute_referer_url, $absolute_board_url)
	{
		// If the board URL is in the beginning of the referer, this means
		// we the referer is in the board URL or a subdirectory of it.
		// So we just need to count the / (slashes) in the left over part of
		// the referer and prepend ../ the the current root_path, to get the
		// web root path of the referer.
		if (strpos($absolute_referer_url, $absolute_board_url) === 0)
		{
			$relative_referer_path = substr($absolute_referer_url, strlen($absolute_board_url));
			$has_params = strpos($relative_referer_path, '?');
			if ($has_params !== false)
			{
				$relative_referer_path = substr($relative_referer_path, 0, $has_params);
			}
			$corrections = substr_count($relative_referer_path, '/');
			return $this->phpbb_root_path . str_repeat('../', max(0, $corrections - 1));
		}

		// If not, it's a bit more complicated. We go to the parent directory
		// of the referer until we find the remaining referer in the board URL.
		// Foreach directory we need to add a ../ to the fixed root_path.
		// When we finally found it, we need to remove the remaining referer
		// from the board URL, to get the boards root path.
		// If the then append these two strings, we get our fixed web root path.
		$fixed_root_path = '';
		$referer_dir = $absolute_referer_url;
		$has_params = strpos($referer_dir, '?');
		if ($has_params !== false)
		{
			$referer_dir = substr($referer_dir, 0, $has_params);
		}

		// If we do not find a slash at the end of the referer, we come
		// from a file. So the first dirname() does not need a traversal
		// path correction.
		if (substr($referer_dir, -1) !== '/')
		{
			$referer_dir = dirname($referer_dir);
		}

		while (($dir_position = strpos($absolute_board_url, $referer_dir)) !== 0)
		{
			$fixed_root_path .= '../';
			$referer_dir = dirname($referer_dir);

			// Just return phpbb_root_path if we reach the top directory
			if ($referer_dir === '.')
			{
				return $this->phpbb_root_path;
			}
		}

		$fixed_root_path .= substr($absolute_board_url, strlen($referer_dir) + 1);
		// Add trailing slash
		return $this->phpbb_root_path . $fixed_root_path . '/';
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
	* @return string Returns the glued string, e.g. name1=value1&amp;name2&amp;name3=value3
	*/
	public function glue_url_params($params)
	{
		$_params = array();

		foreach ($params as $key => $value)
		{
			// some parameters do not have value
			if ($value !== null)
			{
				$_params[] = $key . '=' . $value;
			}
			else
			{
				$_params[] = $key;
			}
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

				// some parameters don't have value
				if (strpos($argument, '=') !== false)
				{
					list($key, $value) = explode('=', $argument, 2);
				}
				else
				{
					$key = $argument;
					$value = null;
				}

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

	/**
	 * Get a valid page
	 *
	 * @param string $page The page to verify
	 * @param bool $mod_rewrite Whether mod_rewrite is enabled, default: false
	 *
	 * @return string A valid page based on given page and mod_rewrite
	 */
	public function get_valid_page($page, $mod_rewrite = false)
	{
		// We need to be cautious here.
		// On some situations, the redirect path is an absolute URL, sometimes a relative path
		// For a relative path, let's prefix it with $phpbb_root_path to point to the correct location,
		// else we use the URL directly.
		$url_parts = parse_url($page);

		// URL
		if ($url_parts === false || empty($url_parts['scheme']) || empty($url_parts['host']))
		{
			// Remove 'app.php/' from the page, when rewrite is enabled.
			// Treat app.php as a reserved file name and remove on mod rewrite
			// even if it might not be in the phpBB root.
			if ($mod_rewrite && ($app_position = strpos($page, 'app.' . $this->php_ext . '/')) !== false)
			{
				$page = substr($page, 0, $app_position) . substr($page, $app_position + strlen('app.' . $this->php_ext . '/'));
			}

			// Remove preceding slashes from page name and prepend root path
			$page = $this->get_phpbb_root_path() . ltrim($page, '/\\');
		}

		return $page;
	}

	/**
	 * Tells if the router is currently in use (if the current page is a route or not)
	 *
	 * @return bool
	 */
	public function is_router_used()
	{
		// Script name URI (e.g. phpBB/app.php)
		$script_name = $this->symfony_request->getScriptName();

		return basename($script_name) === 'app.' . $this->php_ext;
	}
}
