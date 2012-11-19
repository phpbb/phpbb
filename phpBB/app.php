<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
*/

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_url_matcher.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('app');

$symfony_request = phpbb_create_symfony_request($request);
$http_kernel = $phpbb_container->get('http_kernel');
$response = $http_kernel->handle($symfony_request);
$response->send();
$http_kernel->terminate($symfony_request, $response);
