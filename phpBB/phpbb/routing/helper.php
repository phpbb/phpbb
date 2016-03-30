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

namespace phpbb\routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

/**
* Controller helper class, contains methods that do things for controllers
*/
class helper
{
	/**
	 * config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * phpBB router
	 * @var \phpbb\routing\router
	 */
	protected $router;

	/**
	 * @var \phpbb\symfony_request
	 */
	protected $symfony_request;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\filesystem The filesystem object
	 */
	protected $filesystem;

	/**
	 * phpBB root path
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * PHP file extension
	 * @var string
	 */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\routing\router $router phpBB router
	 * @param \phpbb\symfony_request $symfony_request Symfony Request object
	 * @param \phpbb\request\request_interface $request phpBB request object
	 * @param \phpbb\filesystem\filesystem $filesystem The filesystem object
	 * @param string $phpbb_root_path phpBB root path
	 * @param string $php_ext PHP file extension
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\routing\router $router, \phpbb\symfony_request $symfony_request, \phpbb\request\request_interface $request, \phpbb\filesystem\filesystem $filesystem, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->router = $router;
		$this->symfony_request = $symfony_request;
		$this->request = $request;
		$this->filesystem = $filesystem;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Generate a URL to a route
	 *
	 * @param string	$route		Name of the route to travel
	 * @param array	$params		String or array of additional url parameters
	 * @param bool	$is_amp		Is url using &amp; (true) or & (false)
	 * @param string|bool		$session_id	Possibility to use a custom session id instead of the global one
	 * @param bool|string		$reference_type The type of reference to be generated (one of the constants)
	 * @return string The URL already passed through append_sid()
	 */
	public function route($route, array $params = array(), $is_amp = true, $session_id = false, $reference_type = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		$anchor = '';
		if (isset($params['#']))
		{
			$anchor = '#' . $params['#'];
			unset($params['#']);
		}

		$context = new RequestContext();
		$context->fromRequest($this->symfony_request);

		if ($this->config['force_server_vars'])
		{
			$context->setHost($this->config['server_name']);
			$context->setScheme(substr($this->config['server_protocol'], 0, -3));
			$context->setHttpPort($this->config['server_port']);
			$context->setHttpsPort($this->config['server_port']);
			$context->setBaseUrl(rtrim($this->config['script_path'], '/'));
		}

		$script_name = $this->symfony_request->getScriptName();
		$page_name = substr($script_name, -1, 1) == '/' ? '' : utf8_basename($script_name);

		$base_url = $context->getBaseUrl();

		// Append page name if base URL does not contain it
		if (!empty($page_name) && strpos($base_url, '/' . $page_name) === false)
		{
			$base_url .= '/' . $page_name;
		}

		// If enable_mod_rewrite is false we need to replace the current front-end by app.php, otherwise we need to remove it.
		$base_url = str_replace('/' . $page_name, empty($this->config['enable_mod_rewrite']) ? '/app.' . $this->php_ext : '', $base_url);

		// We need to update the base url to move to the directory of the app.php file if the current script is not app.php
		if ($page_name !== 'app.php' && !$this->config['force_server_vars'])
		{
			if (empty($this->config['enable_mod_rewrite']))
			{
				$base_url = str_replace('/app.' . $this->php_ext, '/' . $this->phpbb_root_path . 'app.' . $this->php_ext, $base_url);
			}
			else
			{
				$base_url .= preg_replace(get_preg_expression('path_remove_dot_trailing_slash'), '$2', $this->phpbb_root_path);
			}
		}

		$base_url = $this->request->escape($this->filesystem->clean_path($base_url), true);

		$context->setBaseUrl($base_url);

		$this->router->setContext($context);
		$route_url = $this->router->generate($route, $params, $reference_type);

		if ($is_amp)
		{
			$route_url = str_replace(array('&amp;', '&'), array('&', '&amp;'), $route_url);
		}

		if ($reference_type === UrlGeneratorInterface::RELATIVE_PATH && empty($this->config['enable_mod_rewrite']))
		{
			$route_url = 'app.' . $this->php_ext . '/' . $route_url;
		}

		return append_sid($route_url . $anchor, false, $is_amp, $session_id, true);
	}
}
