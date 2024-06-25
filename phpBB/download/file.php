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
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);

/** @var \phpbb\controller\helper $controller_helper */
$controller_helper = $phpbb_container->get('controller.helper');

if ($request->is_set('avatar'))
{
	$response = new RedirectResponse(
		$controller_helper->route('phpbb_storage_avatar', array(
			'file'	=> $request->variable('avatar', ''),
		), false),
		301
	);
	$response->send();

	exit;
}

$attach_id = $request->variable('id', 0);
$mode = $request->variable('mode', '');
$thumbnail = $request->variable('t', false);

$response = new RedirectResponse(
	$controller_helper->route('phpbb_storage_attachment', array(
		'id'	=> $attach_id,
		't'		=> $thumbnail,
	), false),
	301
);
$response->send();
