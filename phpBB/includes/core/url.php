<?php
/**
*
* @package core
* @version $Id: url.php 9219 2008-12-24 12:43:15Z acydburn $
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
* Class responsible for URL handling, URL building, redirects, meta refreshs and session id handling.
* Basically everything url/sid-related.
*
* @package core
*/
class phpbb_url extends phpbb_plugin_support
{
	/**
	* @var array required phpBB objects
	*/
	public $phpbb_required = array('user', 'config');

	/**
	* @var array Optional phpBB objects
	*/
	public $phpbb_optional = array('template');

	public function __construct() {}

	/**
	* Checks if a path ($path) is absolute or relative
	*
	* @param string	$path	Path to check absoluteness of
	* @return bool	True if path is absolute
	* @access public
	*/
	public function is_absolute($path)
	{
		return ($path[0] == '/' || (DIRECTORY_SEPARATOR == '\\' && preg_match('#^[a-z]:/#i', $path))) ? true : false;
	}

	/**
	* Mimic PHP realpath implementation
	*
	* @author Chris Smith <chris@project-minerva.org>
	* @copyright 2006 Project Minerva Team
	* @param string	$path	The path which we should attempt to resolve.
	* @return mixed realpath
	* @access private
	*/
	private function own_realpath($path)
	{
		// Switch to use UNIX slashes
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
		$path_prefix = '';

		// Determine what sort of path we have
		if ($this->is_absolute($path))
		{
			$absolute = true;

			if ($path[0] == '/')
			{
				// Absolute path, *NIX style
				$path_prefix = '';
			}
			else
			{
				// Absolute path, Windows style
				// Remove the drive letter and colon
				$path_prefix = $path[0] . ':';
				$path = substr($path, 2);
			}
		}
		else
		{
			// Relative Path
			// Prepend the current working directory
			if (function_exists('getcwd'))
			{
				// This is the best method, hopefully it is enabled!
				$path = str_replace(DIRECTORY_SEPARATOR, '/', getcwd()) . '/' . $path;
				$absolute = true;
				if (preg_match('#^[a-z]:#i', $path))
				{
					$path_prefix = $path[0] . ':';
					$path = substr($path, 2);
				}
				else
				{
					$path_prefix = '';
				}
			}
			else if (!empty($_SERVER['SCRIPT_FILENAME']))
			{
				// Warning: If chdir() has been used this will lie!
				// Warning: This has some problems sometime (CLI can create them easily)
				$path = str_replace(DIRECTORY_SEPARATOR, '/', dirname($_SERVER['SCRIPT_FILENAME'])) . '/' . $path;
				$absolute = true;
				$path_prefix = '';
			}
			else
			{
				// We have no way of getting the absolute path, just run on using relative ones.
				$absolute = false;
				$path_prefix = '.';
			}
		}

		// Remove any repeated slashes
		$path = preg_replace('#/{2,}#', '/', $path);

		// Remove the slashes from the start and end of the path
		$path = trim($path, '/');

		// Break the string into little bits for us to nibble on
		$bits = explode('/', $path);

		// Remove any . in the path, renumber array for the loop below
		$bits = array_values(array_diff($bits, array('.')));

		// Lets get looping, run over and resolve any .. (up directory)
		for ($i = 0, $max = sizeof($bits); $i < $max; $i++)
		{
			// @todo Optimise
			if ($bits[$i] == '..' )
			{
				if (isset($bits[$i - 1]))
				{
					if ($bits[$i - 1] != '..')
					{
						// We found a .. and we are able to traverse upwards, lets do it!
						unset($bits[$i]);
						unset($bits[$i - 1]);
						$i -= 2;
						$max -= 2;
						$bits = array_values($bits);
					}
				}
				else if ($absolute) // ie. !isset($bits[$i - 1]) && $absolute
				{
					// We have an absolute path trying to descend above the root of the filesystem
					// ... Error!
					return false;
				}
			}
		}

		// Prepend the path prefix
		array_unshift($bits, $path_prefix);

		$resolved = '';

		$max = sizeof($bits) - 1;

		// Check if we are able to resolve symlinks, Windows cannot.
		$symlink_resolve = (function_exists('readlink')) ? true : false;

		foreach ($bits as $i => $bit)
		{
			if (@is_dir("$resolved/$bit") || ($i == $max && @is_file("$resolved/$bit")))
			{
				// Path Exists
				if ($symlink_resolve && is_link("$resolved/$bit") && ($link = readlink("$resolved/$bit")))
				{
					// Resolved a symlink.
					$resolved = $link . (($i == $max) ? '' : '/');
					continue;
				}
			}
			else
			{
				// Something doesn't exist here!
				// This is correct realpath() behaviour but sadly open_basedir and safe_mode make this problematic
				// return false;
			}
			$resolved .= $bit . (($i == $max) ? '' : '/');
		}

		// @todo If the file exists fine and open_basedir only has one path we should be able to prepend it
		// because we must be inside that basedir, the question is where...
		// @internal The slash in is_dir() gets around an open_basedir restriction
		if (!@file_exists($resolved) || (!is_dir($resolved . '/') && !is_file($resolved)))
		{
			return false;
		}

		// Put the slashes back to the native operating systems slashes
		$resolved = str_replace('/', DIRECTORY_SEPARATOR, $resolved);

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($resolved, -1) == DIRECTORY_SEPARATOR)
		{
			return substr($resolved, 0, -1);
		}

		// We got here, in the end!
		return $resolved;
	}

	/**
	* A wrapper for realpath
	*
	* @param string		$path					The path which we should attempt to resolve.
	* @staticvar string	$_phpbb_realpath_exist	This is set to false if the PHP function realpath() is not accessible or returns incorrect results
	*
	* @return string	Real path
	* @access public
	*/
	public function realpath($path)
	{
		static $_phpbb_realpath_exist;

		if (!isset($_phpbb_realpath_exist))
		{
			$_phpbb_realpath_exist = (!function_exists('realpath')) ? false : true;
		}

		if (!$_phpbb_realpath_exist)
		{
			return $this->own_realpath($path);
		}

		$realpath = realpath($path);

		// Strangely there are provider not disabling realpath but returning strange values. :o
		// We at least try to cope with them.
		if ($realpath === $path || $realpath === false)
		{
			$_phpbb_realpath_exist = false;
			return $this->own_realpath($path);
		}

		// Check for DIRECTORY_SEPARATOR at the end (and remove it!)
		if (substr($realpath, -1) == DIRECTORY_SEPARATOR)
		{
			$realpath = substr($realpath, 0, -1);
		}

		return $realpath;
	}

	/**
	* URL wrapper
	* All urls are run through this... either after {@link append_sid() append_sid} or directly
	*
	* @param string	$url	URL to process
	* @return string	URL
	* @access public
	*/
	public function get($url)
	{
		return $url;
	}

	/**
	* Append session id to url.
	*
	* Examples:
	* <code>
	* append_sid(PHPBB_ROOT_PATH . 'viewtopic.' . PHP_EXT . '?t=1&amp;f=2'); // VALID
	* append_sid(PHPBB_ROOT_PATH . 'viewtopic.' . PHP_EXT, 't=1&amp;f=2'); // VALID
	* append_sid('viewtopic', 't=1&amp;f=2'); // short notation of the above example - VALID
	* append_sid('viewtopic', 't=1&f=2', false);  // Instead of &amp; use &
	* append_sid('viewtopic', array('t' => 1, 'f' => 2));  // Instead of parameter in string notation, use an array
	* </code>
	*
	* @param string			$url		The url the session id needs to be appended to (without parameter)
	* @param string|array	$params		String or array of additional url parameter.
	* @param bool			$is_amp		Is url using &amp; (true) or & (false)
	* @param string			$session_id	Possibility to use a custom session id instead of the global one. This also forces the use of a session id.
	*
	* @plugin-support default, return
	* @return string URL
	* @access public
	*/
	public function append_sid($url, $params = false, $is_amp = true, $session_id = false)
	{
		static $parsed_urls = array();

		// The following code is used to make sure such calls like append_sid('viewtopic') (ommitting phpbb_root_path and php_ext) work as intended
		if (isset($parsed_urls[$url]))
		{
			// Set an url like 'viewtopic' to PHPBB_ROOT_PATH . 'viewtopic.' . PHP_EXT
			$url = $parsed_urls[$url];
		}
		else
		{
			// If we detect an url without root path and extension, and also not a relative or absolute path, we add it and put it to the parsed urls
			if (strpos($url, '.' . PHP_EXT) === false && $url[0] != '.' && $url[0] != '/')
			{
				$parsed_urls[$url] = $url = PHPBB_ROOT_PATH . $url . '.' . PHP_EXT;
			}
		}

		if (empty($params))
		{
			$params = false;
		}

		$params_is_array = is_array($params);

		// Get anchor
		$anchor = '';
		if (strpos($url, '#') !== false)
		{
			list($url, $anchor) = explode('#', $url, 2);
			$anchor = '#' . $anchor;
		}
		else if (!$params_is_array && strpos($params, '#') !== false)
		{
			list($params, $anchor) = explode('#', $params, 2);
			$anchor = '#' . $anchor;
		}

		// Handle really simple cases quickly
		if ($session_id === false && !phpbb::$user->need_sid && empty(phpbb::$user->extra_url) && !$params_is_array && !$anchor)
		{
			if ($params === false)
			{
				return $this->get($url);
			}

			$url_delim = (strpos($url, '?') === false) ? '?' : (($is_amp) ? '&amp;' : '&');
			return $this->get($url . ($params !== false ? $url_delim . $params : ''));
		}

		// Assign sid if session id is not specified
		if (phpbb::$user->need_sid && $session_id === false)
		{
			$session_id = phpbb::$user->session_id;
		}

		$amp_delim = ($is_amp) ? '&amp;' : '&';
		$url_delim = (strpos($url, '?') === false) ? '?' : $amp_delim;

		// Appending custom url parameter?
		$append_url = (!empty(phpbb::$user->extra_url)) ? implode($amp_delim, phpbb::$user->extra_url) : '';

		if ($this->method_inject(__FUNCTION__)) $this->call_inject(__FUNCTION__, array('default', &$url, &$params, &$session_id, &$append_url, &$anchor, &$amp_delim, &$url_delim));

		if ($this->method_inject(__FUNCTION__, 'return'))
		{
			$url = $this->call_inject(__FUNCTION__, array('return', $url, $params, $session_id, $append_url, $anchor, $amp_delim, $url_delim));
			return $this->get($url);
		}

		// Use the short variant if possible ;)
		if ($params === false)
		{
			// Append session id
			if (!$session_id)
			{
				return $this->get($url . (($append_url) ? $url_delim . $append_url : '') . $anchor);
			}
			else
			{
				return $this->get($url . (($append_url) ? $url_delim . $append_url . $amp_delim : $url_delim) . 'sid=' . $session_id . $anchor);
			}
		}

		// Build string if parameters are specified as array
		if ($params_is_array)
		{
			$output = array();

			foreach ($params as $key => $item)
			{
				if ($item === NULL)
				{
					continue;
				}

				if ($key == '#')
				{
					$anchor = '#' . $item;
					continue;
				}

				$output[] = $key . '=' . $item;
			}

			$params = implode($amp_delim, $output);
		}

		// Append session id and parameter
		return $this->get($url . (($append_url) ? $url_delim . $append_url : '') . (($params) ? (($append_url) ? $amp_delim : $url_delim) . $params : '') . ((!$session_id) ? '' : $amp_delim . 'sid=' . $session_id) . $anchor);
	}

	/**
	* Generate board url (example: http://www.example.com/phpBB)
	*
	* @param bool	$without_script_path	If set to true the script path gets not appended (example: http://www.example.com instead of http://www.example.com/phpBB)
	* @return string	Board URL
	* @access public
	*/
	public function generate_board_url($without_script_path = false)
	{
		$server_name = phpbb::$user->system['host'];
		$server_port = phpbb::$user->system['port'];

		// Forcing server vars is the only way to specify/override the protocol
		if (phpbb::$config['force_server_vars'] || !$server_name)
		{
			$server_protocol = (phpbb::$config['server_protocol']) ? phpbb::$config['server_protocol'] : ((phpbb::$config['cookie_secure']) ? 'https://' : 'http://');
			$server_name = phpbb::$config['server_name'];
			$server_port = (int) phpbb::$config['server_port'];
			$script_path = phpbb::$config['script_path'];

			$url = $server_protocol . $server_name;
			$cookie_secure = phpbb::$config['cookie_secure'];
		}
		else
		{
			// Do not rely on cookie_secure, users seem to think that it means a secured cookie instead of an encrypted connection
			$cookie_secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;
			$url = (($cookie_secure) ? 'https://' : 'http://') . $server_name;

			$script_path = phpbb::$user->page['root_script_path'];
		}

		if ($server_port && (($cookie_secure && $server_port <> 443) || (!$cookie_secure && $server_port <> 80)))
		{
			// HTTP HOST can carry a port number (we fetch $user->host, but for old versions this may be true)
			if (strpos($server_name, ':') === false)
			{
				$url .= ':' . $server_port;
			}
		}

		if (!$without_script_path)
		{
			$url .= $script_path;
		}

		// Strip / from the end
		if (substr($url, -1, 1) == '/')
		{
			$url = substr($url, 0, -1);
		}

		return $url;
	}

	/**
	* Redirects the user to another page then exits the script nicely
	* This function is intended for urls within the board. It's not meant to redirect to cross-domains.
	*
	* @param string	$url				The url to redirect to
	* @param bool	$return				If true, do not redirect but return the sanitized URL.
	* @param bool	$disable_cd_check	If true, redirect() will support redirects to an external domain.
	* 									If false, the redirect points to the boards url if it does not match the current domain.
	*
	* @return mixed	Sanitized URL if $return is true
	* @access public
	*/
	public function redirect($url, $return = false, $disable_cd_check = false)
	{
		if (empty(phpbb::$user->lang))
		{
			phpbb::$user->add_lang('common');
		}

		if (!$return)
		{
			garbage_collection();
		}

		// Make sure no &amp;'s are in, this will break the redirect
		$url = str_replace('&amp;', '&', $url);

		// Determine which type of redirect we need to handle...
		$url_parts = parse_url($url);

		if ($url_parts === false)
		{
			// Malformed url, redirect to current page...
			$url = $this->generate_board_url() . '/' . phpbb::$user->page['page'];
		}
		else if (!empty($url_parts['scheme']) && !empty($url_parts['host']))
		{
			// Attention: only able to redirect within the same domain if $disable_cd_check is false (yourdomain.com -> www.yourdomain.com will not work)
			if (!$disable_cd_check && $url_parts['host'] !== phpbb::$user->system['host'])
			{
				$url = $this->generate_board_url();
			}
		}
		else if ($url[0] == '/')
		{
			// Absolute uri, prepend direct url...
			$url = $this->generate_board_url(true) . $url;
		}
		else
		{
			// Relative uri
			$pathinfo = pathinfo($url);

			// Is the uri pointing to the current directory?
			if ($pathinfo['dirname'] == '.')
			{
				$url = str_replace('./', '', $url);

				// Strip / from the beginning
				if ($url && substr($url, 0, 1) == '/')
				{
					$url = substr($url, 1);
				}

				if (phpbb::$user->page['page_dir'])
				{
					$url = $this->generate_board_url() . '/' . phpbb::$user->page['page_dir'] . '/' . $url;
				}
				else
				{
					$url = $this->generate_board_url() . '/' . $url;
				}
			}
			else
			{
				// Used ./ before, but PHPBB_ROOT_PATH is working better with urls within another root path
				$root_dirs = explode('/', str_replace('\\', '/', $this->realpath(PHPBB_ROOT_PATH)));
				$page_dirs = explode('/', str_replace('\\', '/', $this->realpath($pathinfo['dirname'])));
				$intersection = array_intersect_assoc($root_dirs, $page_dirs);

				$root_dirs = array_diff_assoc($root_dirs, $intersection);
				$page_dirs = array_diff_assoc($page_dirs, $intersection);

				$dir = str_repeat('../', sizeof($root_dirs)) . implode('/', $page_dirs);

				// Strip / from the end
				if ($dir && substr($dir, -1, 1) == '/')
				{
					$dir = substr($dir, 0, -1);
				}

				// Strip / from the beginning
				if ($dir && substr($dir, 0, 1) == '/')
				{
					$dir = substr($dir, 1);
				}

				$url = str_replace($pathinfo['dirname'] . '/', '', $url);

				// Strip / from the beginning
				if (substr($url, 0, 1) == '/')
				{
					$url = substr($url, 1);
				}

				$url = (!empty($dir) ? $dir . '/' : '') . $url;
				$url = $this->generate_board_url() . '/' . $url;
			}
		}

		// Make sure no linebreaks are there... to prevent http response splitting for PHP < 4.4.2
		if (strpos(urldecode($url), "\n") !== false || strpos(urldecode($url), "\r") !== false || strpos($url, ';') !== false)
		{
			trigger_error('Tried to redirect to potentially insecure url.', E_USER_ERROR);
		}

		// Now, also check the protocol and for a valid url the last time...
		$allowed_protocols = array('http', 'https', 'ftp', 'ftps');
		$url_parts = parse_url($url);

		if ($url_parts === false || empty($url_parts['scheme']) || !in_array($url_parts['scheme'], $allowed_protocols))
		{
			trigger_error('Tried to redirect to potentially insecure url.', E_USER_ERROR);
		}

		if ($return)
		{
			return $url;
		}

		// Redirect via an HTML form for PITA webservers
		if (@preg_match('#Microsoft|WebSTAR|Xitami#', getenv('SERVER_SOFTWARE')))
		{
			header('Refresh: 0; URL=' . $url);

			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
			echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="' . phpbb::$user->lang['DIRECTION'] . '" lang="' . phpbb::$user->lang['USER_LANG'] . '" xml:lang="' . phpbb::$user->lang['USER_LANG'] . '">';
			echo '<head>';
			echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
			echo '<meta http-equiv="refresh" content="0; url=' . str_replace('&', '&amp;', $url) . '" />';
			echo '<title>' . phpbb::$user->lang['REDIRECT'] . '</title>';
			echo '</head>';
			echo '<body>';
			echo '<div style="text-align: center;">' . phpbb::$user->lang('URL_REDIRECT', '<a href="' . str_replace('&', '&amp;', $url) . '">', '</a>') . '</div>';
			echo '</body>';
			echo '</html>';

			exit;
		}

		// Behave as per HTTP/1.1 spec for others
		header('Location: ' . $url);
		exit;
	}

	/**
	* Meta refresh assignment
	*
	* If the template object is present, the META template variable holds the meta refresh, else a normal redirect is done.
	*
	* @param int	$time				The time in seconds when to redirect
	* @param string	$url				The URL to redirect to
	* @param bool	$disable_cd_check	If true, redirect() will support redirects to an external domain.
	* 									If false, the redirect points to the boards url if it does not match the current domain.
	*
	* @return string	Sanitized URL
	* @plugin-support return
	* @access public
	*/
	public function meta_refresh($time, $url, $disable_cd_check = false)
	{
		if (phpbb::registered('template'))
		{
			$result_url = $this->redirect($url, true, $disable_cd_check);
			$result_url = str_replace('&', '&amp;', $result_url);

			// For XHTML compatibility we change back & to &amp;
			phpbb::$template->assign_var('META', '<meta http-equiv="refresh" content="' . $time . ';url=' . $result_url . '" />');
		}
		else
		{
			$this->redirect($url, false, $disable_cd_check);
		}

		return ($this->method_inject(__FUNCTION__, 'return')) ? $this->call_inject(__FUNCTION__, array('return', $result_url, $time, $url, $disable_cd_check)) : $result_url;
	}

	/**
	* Re-Apply session id after page reloads
	*
	* @param string	$url	URL to re-apply session id to
	* @return string	URL with re-applied session id
	* @access public
	*/
	public function reapply_sid($url)
	{
		if ($url === 'index.' . PHP_EXT)
		{
			return $this->append_sid('index.' . PHP_EXT);
		}
		else if ($url === PHPBB_ROOT_PATH . 'index.' . PHP_EXT)
		{
			return $this->append_sid('index');
		}

		// Remove previously added sid
		if (strpos($url, '?sid=') !== false)
		{
			$url = preg_replace('/(\?)sid=[a-z0-9]+(&amp;|&)?/', '\1', $url);
		}
		else if (strpos($url, '&sid=') !== false)
		{
			$url = preg_replace('/&sid=[a-z0-9]+(&)?/', '\1', $url);
		}
		else if (strpos($url, '&amp;sid=') !== false)
		{
			$url = preg_replace('/&amp;sid=[a-z0-9]+(&amp;)?/', '\1', $url);
		}

		return $this->append_sid($url);
	}

	/**
	* Returns url from the session/current page with an re-appended SID with optionally stripping vars from the url
	*
	* @param array|string	$strip_vars	An array containing variables to be stripped from the URL.
	* @return string	Current page URL with re-applied SID and optionally stripped parameter
	* @access public
	*/
	public function build_url($strip_vars = false)
	{
		// Append SID
		$redirect = $this->append_sid(phpbb::$user->page['page'], false, false);

		// Add delimiter if not there...
		if (strpos($redirect, '?') === false)
		{
			$redirect .= '?';
		}

		// Strip vars...
		if ($strip_vars !== false && strpos($redirect, '?') !== false)
		{
			if (!is_array($strip_vars))
			{
				$strip_vars = array($strip_vars);
			}

			$query = $_query = array();

			$args = substr($redirect, strpos($redirect, '?') + 1);
			$args = ($args) ? explode('&', $args) : array();
			$redirect = substr($redirect, 0, strpos($redirect, '?'));

			foreach ($args as $argument)
			{
				$arguments = explode('=', $argument);
				$key = $arguments[0];
				unset($arguments[0]);

				$query[$key] = implode('=', $arguments);
			}

			// Strip the vars off
			foreach ($strip_vars as $strip)
			{
				if (isset($query[$strip]))
				{
					unset($query[$strip]);
				}
			}

			// Glue the remaining parts together... already urlencoded
			foreach ($query as $key => $value)
			{
				$_query[] = $key . '=' . $value;
			}
			$query = implode('&', $_query);

			$redirect .= ($query) ? '?' . $query : '';
		}

		return PHPBB_ROOT_PATH . str_replace('&', '&amp;', $redirect);
	}
}

?>