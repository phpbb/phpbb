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
use Symfony\Component\Routing\Exception\RouteNotFoundException;

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

/* @var $http_kernel \Symfony\Component\HttpKernel\HttpKernel */
$http_kernel = $phpbb_container->get('http_kernel');

/* @var $symfony_request \phpbb\symfony_request */
$symfony_request = $phpbb_container->get('symfony_request');

/** @var \phpbb\controller\helper $controller_helper */
$controller_helper = $phpbb_container->get('controller.helper');
$cron_route = 'phpbb_cron_run';

try
{
	$response = new RedirectResponse(
		$controller_helper->route($cron_route, $get_params_array, false),
		Response::HTTP_MOVED_PERMANENTLY
	);
	$response->send();
	$http_kernel->terminate($symfony_request, $response);
	exit();
}
catch (RouteNotFoundException $exception)
{
	$error = 'ROUTE_NOT_FOUND';
	$error_parameters = $cron_route;
	$error_code = Response::HTTP_NOT_FOUND;
}
catch (ExceptionInterface $exception)
{
	$error = 'ROUTE_INVALID_MISSING_PARAMS';
	$error_parameters = $cron_route;
	$error_code = Response::HTTP_BAD_REQUEST;
}
catch (Throwable $exception)
{
	$error = $exception->getMessage();
	$error_parameters = [];
	$error_code = Response::HTTP_INTERNAL_SERVER_ERROR;
}

$language = $phpbb_container->get('language');
$response = new Response(
	$language->lang($error, $error_parameters),
	$error_code
);
$response->send();
$http_kernel->terminate($symfony_request, $response);
