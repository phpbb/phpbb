<?php
/**
*
* @package core
* @version $Id: core.php 9200 2008-12-15 18:06:53Z acydburn $
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
* Get system/server information variables.
*
* @package core
*/
class phpbb_system_info extends phpbb_plugin_support implements ArrayAccess
{
	/**
	* @var array required phpBB objects
	*/
	public $phpbb_required = array('config', 'url');

	/**
	* @var array Optional phpBB objects
	*/
	public $phpbb_optional = array();

	/**
	* @var array Array for storing/accessing information
	*/
	private $data = array();

	/**#@+
	* Part of the ArrayAccess implementation.
	* @access public
	*/
	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
	/**#@-*/

	/**
	* Get system information - Part of the ArrayAccess implementation.
	*
	* System information ought to be received from {@link $data phpbb::$user->system[key]}.
	* The key used is mapped to a method with get_ as prefix.
	* For example getting phpbb::$user->system['host'] results in calling the method get_host().
	*
	* @param string $offset	The key to get.
	* @return mixed	The result
	* @access public
	*/
	public function offsetGet($offset)
	{
		if (isset($this->data[$offset]))
		{
			return $this->data[$offset];
		}

		$identifier = 'get_' . strtolower($offset);

		// Not static, because we are not able to use late static bindings
		$this->data[$offset] = $this->$identifier();
		return $this->data[$offset];
	}

	/**
	* Get valid hostname/port. HTTP_HOST is used, SERVER_NAME if HTTP_HOST not present.
	*
	* @return string	Host (lowercase, not specialchared)
	* @access protected
	*/
	protected function get_host()
	{
		// Get hostname
		$host = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : ((!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME'));

		// Should be a string and lowered
		$host = (string) strtolower($host);

		// If host is equal the cookie domain or the server name (if config is set), then we assume it is valid
		if ((isset(phpbb::$config['cookie_domain']) && $host === phpbb::$config['cookie_domain']) || (isset(phpbb::$config['server_name']) && $host === phpbb::$config['server_name']))
		{
			return $host;
		}

		// Is the host actually a IP? If so, we use the IP... (IPv4)
		if (long2ip(ip2long($host)) === $host)
		{
			return $host;
		}

		// Now return the hostname (this also removes any port definition). The http:// is prepended to construct a valid URL, hosts never have a scheme assigned
		$host = @parse_url('http://' . $host, PHP_URL_HOST);

		// Remove any portions not removed by parse_url (#)
		$host = str_replace('#', '', $host);

		// If, by any means, the host is now empty, we will use a "best approach" way to guess one
		if (empty($host))
		{
			if (!empty(phpbb::$config['server_name']))
			{
				$host = phpbb::$config['server_name'];
			}
			else if (!empty(phpbb::$config['cookie_domain']))
			{
				$host = (strpos(phpbb::$config['cookie_domain'], '.') === 0) ? substr(phpbb::$config['cookie_domain'], 1) : phpbb::$config['cookie_domain'];
			}
			else
			{
				// Set to OS hostname or localhost
				$host = (function_exists('php_uname')) ? strtolower(php_uname('n')) : 'localhost';
			}
		}

		// It may be still no valid host, but for sure only a hostname (we may further expand on the cookie domain... if set)
		return $host;
	}

	/**
	* Extract current session page, relative from current root path (PHPBB_ROOT_PATH)
	*
	* The array returned consist of the following key/value pairs:
	* page_name:		The current basename'd page name, for example: index.php (urlencoded, htmlspecialchared)
	* page_dir:			The current directory within the phpBB root, for example: adm
	* query_string:		The current query string, for example: i=10&b=2 (the parameter 'sid' is never included)
	* script_path:		The script path from the webroot to the current directory, for example: /phpBB3/adm/
	* 					The script path is always prefixed with / and ends in /. Specialchared, whitespace replaced with %20.
	* root_script_path:	The script path from the webroot to the phpBB root, for example: /phpBB3/
	* 					The root script path is always prefixed with / and ends in /. Specialchared, whitespace replaced with %20.
	* page:				Current page from phpBB root, for example: adm/index.php?i=10&b=2
	* forum:			Current forum id (determined by {@link request_var() request_var('f', 0)})
	*
	* @return array	Array containing page information.
	* @access protected
	*/
	protected function get_page()
	{
		$page_array = array();

		// First of all, get the request uri...
		$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
		$args = (!empty($_SERVER['QUERY_STRING'])) ? explode('&', $_SERVER['QUERY_STRING']) : explode('&', getenv('QUERY_STRING'));

		// If we are unable to get the script name we use REQUEST_URI as a failover and note it within the page array for easier support...
		if (!$script_name)
		{
			$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
			$script_name = (($pos = strpos($script_name, '?')) !== false) ? substr($script_name, 0, $pos) : $script_name;
			$page_array['failover'] = 1;
		}

		// Replace backslashes and doubled slashes (could happen on some proxy setups)
		$script_name = str_replace(array('\\', '//'), '/', $script_name);

		// Now, remove the sid and let us get a clean query string...
		$use_args = array();

		// Since some browser do not encode correctly we need to do this with some "special" characters...
		// " -> %22, ' => %27, < -> %3C, > -> %3E
		$find = array('"', "'", '<', '>');
		$replace = array('%22', '%27', '%3C', '%3E');

		foreach ($args as $argument)
		{
			if (strpos($argument, 'sid=') === 0)
			{
				continue;
			}

			$use_args[] = str_replace($find, $replace, $argument);
		}
		unset($args);

		// The following examples given are for an request uri of {path to the phpbb directory}/adm/index.php?i=10&b=2

		// The current query string
		$query_string = trim(implode('&', $use_args));

		// basenamed page name (for example: index.php)
		$page_name = basename($script_name);
		$page_name = urlencode(htmlspecialchars($page_name));

		// current directory within the phpBB root (for example: adm)
		$root_dirs = explode('/', str_replace('\\', '/', phpbb::$url->realpath(PHPBB_ROOT_PATH)));
		$page_dirs = explode('/', str_replace('\\', '/', phpbb::$url->realpath('./')));
		$intersection = array_intersect_assoc($root_dirs, $page_dirs);

		$root_dirs = array_diff_assoc($root_dirs, $intersection);
		$page_dirs = array_diff_assoc($page_dirs, $intersection);

		$page_dir = str_repeat('../', sizeof($root_dirs)) . implode('/', $page_dirs);

		if ($page_dir && substr($page_dir, -1, 1) == '/')
		{
			$page_dir = substr($page_dir, 0, -1);
		}

		// Current page from phpBB root (for example: adm/index.php?i=10&b=2)
		$page = (($page_dir) ? $page_dir . '/' : '') . $page_name . (($query_string) ? "?$query_string" : '');

		// The script path from the webroot to the current directory (for example: /phpBB3/adm/) : always prefixed with / and ends in /
		$script_path = trim(str_replace('\\', '/', dirname($script_name)));

		// The script path from the webroot to the phpBB root (for example: /phpBB3/)
		$script_dirs = explode('/', $script_path);
		array_splice($script_dirs, -sizeof($page_dirs));
		$root_script_path = implode('/', $script_dirs) . (sizeof($root_dirs) ? '/' . implode('/', $root_dirs) : '');

		// We are on the base level (phpBB root == webroot), lets adjust the variables a bit...
		if (!$root_script_path)
		{
			$root_script_path = ($page_dir) ? str_replace($page_dir, '', $script_path) : $script_path;
		}

		$script_path .= (substr($script_path, -1, 1) == '/') ? '' : '/';
		$root_script_path .= (substr($root_script_path, -1, 1) == '/') ? '' : '/';

		$page_array += array(
			'page_name'			=> $page_name,
			'page_dir'			=> $page_dir,

			'query_string'		=> $query_string,
			'script_path'		=> str_replace(' ', '%20', htmlspecialchars($script_path)),
			'root_script_path'	=> str_replace(' ', '%20', htmlspecialchars($root_script_path)),

			'page'				=> $page,
			'forum'				=> request_var('f', 0),
		);

		return ($this->method_inject(__FUNCTION__, 'return')) ? $this->call_inject(__FUNCTION__, array('return', $page_array)) : $page_array;
	}

	/**
	* Get user agent string.
	*
	* @return string User agent, determined from $_SERVER['HTTP_USER_AGENT']. Specialchared.
	* @access protected
	*/
	protected function get_browser()
	{
		return (!empty($_SERVER['HTTP_USER_AGENT'])) ? htmlspecialchars((string) $_SERVER['HTTP_USER_AGENT']) : '';
	}

	/**
	* Get current referer
	*
	* @return string Referer, determined from $_SERVER['HTTP_REFERER']. Specialchared.
	* @access protected
	*/
	protected function get_referer()
	{
		return (!empty($_SERVER['HTTP_REFERER'])) ? htmlspecialchars((string) $_SERVER['HTTP_REFERER']) : '';
	}

	/**
	* Get server port
	*
	* @return int Sertver port, determined from $_SERVER/$_ENV['SERVER_PORT'].
	* @access protected
	*/
	protected function get_port()
	{
		return (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
	}

	/**
	* Get forwarded-for string.
	* If the forwarded for check is enabled in phpBB the ip's are checked for valid data and invalid data being removed.
	*
	* @return string Forwarded-for string, determined from $_SERVER['HTTP_X_FORWARDED_FOR'].
	* @access protected
	*/
	protected function get_forwarded_for()
	{
		$forwarded_for = (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? (string) $_SERVER['HTTP_X_FORWARDED_FOR'] : '';

		// if the forwarded for header shall be checked we have to validate its contents
		if (phpbb::$config['forwarded_for_check'])
		{
			$forwarded_for = preg_replace('#, +#', ', ', $forwarded_for);

			// split the list of IPs
			$ips = explode(', ', $forwarded_for);
			foreach ($ips as $ip)
			{
				// check IPv4 first, the IPv6 is hopefully only going to be used very seldomly
				if (!empty($ip) && !preg_match(get_preg_expression('ipv4'), $ip) && !preg_match(get_preg_expression('ipv6'), $ip))
				{
					// contains invalid data, don't use the forwarded for header
					return '';
				}
			}
		}
		else
		{
			return '';
		}
	}

	/**
	* Get remote ip
	*
	* @return string Remote IP, determined from $_SERVER['REMOTE_ADDR']. Specialchared.
	* @access protected
	*/
	protected function get_ip()
	{
		return (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : '';
	}

	/**
	* Get server load.
	*
	* Server load is retrieved if load limitation is enabled in phpBB and server supports {@link sys_getloadavg() sys_getloadavg}
	* or file /proc/loadavg exists on the server.
	*
	* @return double Server load.
	* @access protected
	*/
	protected function get_load()
	{
		$load = false;

		// Load limit check (if applicable)
		if (phpbb::$config['limit_load'] || phpbb::$config['limit_search_load'])
		{
			if ((function_exists('sys_getloadavg') && $load = sys_getloadavg()) || ($load = explode(' ', @file_get_contents('/proc/loadavg'))))
			{
				$load = array_slice($load, 0, 1);
				$load = floatval($load[0]);
			}
			else
			{
				set_config('limit_load', '0');
				set_config('limit_search_load', '0');
			}
		}

		return $load;
	}

	/**
	* Get current request method.
	*
	* @return string Request method, determined from $_SERVER['REQUEST_METHOD']. Specialchared, lowercase.
	* @access protected
	*/
	protected function get_request_method()
	{
		return (isset($_SERVER['REQUEST_METHOD'])) ? strtolower(htmlspecialchars((string) $_SERVER['REQUEST_METHOD'])) : '';
	}
}

?>