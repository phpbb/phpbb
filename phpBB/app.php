<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
*/
use Symfony\Component\HttpFoundation\Request;

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

$request->enable_super_globals();

$symfony_request = Request::createFromGlobals();
$controller_resolver = $phpbb_container->get('controller.resolver');
$response = $controller_resolver->load($symfony_request);

// We use output buffering because the send() method uses echo()
// We store this into a variable for future reference if ever needed
ob_start();
$response->send();
$return = ob_get_flush();
