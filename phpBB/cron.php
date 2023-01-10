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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ExceptionInterface;

/**
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Do not update users last page entry
$user->session_begin(false);
$auth->acl($user->data);

$cron_type = $request->variable('cron_type', '');

$get_params_array = $request->get_super_global(\phpbb\request\request_interface::GET);

/** @var \phpbb\controller\helper $controller_helper */
$controller_helper = $phpbb_container->get('controller.helper');
try
{
	$response = new RedirectResponse(
		$controller_helper->route('phpbb_cron_run', $get_params_array, false),
		Response::HTTP_MOVED_PERMANENTLY
	);
	$response->send();
}
catch(ExceptionInterface $exception)
{
	$language = $phpbb_container->get('language');
	$response = new Response(
		$language->lang('PAGE_NOT_FOUND'),
		Response::HTTP_BAD_REQUEST
	);
	$response->send();
}
