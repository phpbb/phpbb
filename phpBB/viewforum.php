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

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session
$user->session_begin();
$auth->acl($user->data);

// Start initial var setup
$forum_id	= $request->variable('f', 0);
$mark_read	= $request->variable('mark', '');
$start		= $request->variable('start', 0);

$default_sort_days	= (!empty($user->data['user_topic_show_days'])) ? $user->data['user_topic_show_days'] : 0;
$default_sort_key	= (!empty($user->data['user_topic_sortby_type'])) ? $user->data['user_topic_sortby_type'] : 't';
$default_sort_dir	= (!empty($user->data['user_topic_sortby_dir'])) ? $user->data['user_topic_sortby_dir'] : 'd';

$sort_days	= $request->variable('st', $default_sort_days);
$sort_key	= $request->variable('sk', $default_sort_key);
$sort_dir	= $request->variable('sd', $default_sort_dir);

/** @var \phpbb\controller\helper $controller_helper */
$controller_helper = $phpbb_container->get('controller.helper');

$param_array = [
	'forum_id' => $forum_id,
	'parameters' => ''
];

$response = new \Symfony\Component\HttpFoundation\RedirectResponse(
	$controller_helper->route(
		'phpbb_view_forum',
		$param_array
	),
	301
);
$response->send();
