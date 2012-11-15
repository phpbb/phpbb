<?php
/**
 *
 * @package phpBB3
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @ignore
 */
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$format = $request->header('Accept') === 'text/xml' ? 'xml' : 'json';
$path = explode('/', $request->server('PATH_INFO'));

if (preg_match('/[^a-z0-9]/i', $path[1]) || $path[1] === 'api')
{
	$api = new phpbb_api;
	$api->show_404($user->lang('API_CONTROLLER_NOT_FOUND'), $format);
}

if (substr($path[1], -1) === 's')
{
	$path[1] = substr($path[1], 0, -1);
}

if (is_readable($phpbb_root_path . '/includes/api/' . $path[1] . '.' . $phpEx))
{
	include($phpbb_root_path . '/includes/api/' . $path[1] . '.' . $phpEx);
}

$class_name = 'phpbb_api_' . $path[1];
if (!class_exists($class_name))
{
	$api = new phpbb_api;
	$api->show_404($user->lang('API_CONTROLLER_NOT_FOUND'), $format);
}
$controller = new $class_name;

$method = $request->server('REQUEST_METHOD');

$path_data = array(
	'args'      => array()
);

foreach ($path as $index => $path_section)
{
	if ($index === 1)
	{
		$path_data['controller'] = htmlspecialchars_decode($path_section);
	}
	else if ($path_section)
	{
		$path_data['args'][] = htmlspecialchars_decode($path_section);
	}
}

if (!$controller->call($method, $path_data, $format))
{
	$controller->show_404($user->lang('API_METHOD_NOT_FOUND'));
}

garbage_collection();
exit_handler();
