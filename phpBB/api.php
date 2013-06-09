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
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/api/api.' . $phpEx);

$api = new api();

$pathinfo = preg_split('|/|', $request->server('PATH_INFO'), -1, PREG_SPLIT_NO_EMPTY);

$function = $pathinfo[0];

switch($request->server('REQUEST_METHOD'))
{
	case 'GET':
		$data = array_shift($pathinfo);
	break;

	case 'POST':
		$data = json_decode($request->post('data'));
	break;

	default:

	break;
}

switch($function)
{
	case 'listforums':
		$result = $api->list_forums();
	break;
}

http_response_code($result[0]);
echo json_encode($result[1]);