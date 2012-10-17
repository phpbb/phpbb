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

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('app');

$compiler = $phpbb_container->get('compiler');
$compiler->addPass(new phpbb_event_kernel_compiler_pass($phpbb_container));
$compiler->compile($phpbb_container);

$kernel = $phpbb_container->get('kernel');
$response = $kernel->handle($symfony_request);
$response->send();
$kernel->terminate($symfony_request, $response);
