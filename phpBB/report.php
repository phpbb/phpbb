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

use Symfony\Component\HttpFoundation\RedirectResponse;

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

$post_id		= $request->variable('p', 0);
$pm_id			= $request->variable('pm', 0);

$redirect_route_name = ($pm_id === 0) ? 'phpbb_report_post_controller' : 'phpbb_report_pm_controller';

/** @var \phpbb\controller\helper $controller_helper */
$controller_helper = $phpbb_container->get('controller.helper');
$response = new RedirectResponse(
	$controller_helper->route($redirect_route_name, array(
		'id'	=> ($pm_id === 0) ? $post_id : $pm_id,
	)),
	301
);
$response->send();
