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
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;

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

$context = new RequestContext();
$context->fromRequest($symfony_request);

$provider = new phpbb_controller_provider;
$routes = $provider
	->get_paths($phpbb_extension_manager->get_finder())
	->find();

$matcher = new UrlMatcher($routes, $context);

$phpbb_dispatcher->addSubscriber(new RouterListener($matcher));

$kernel = new HttpKernel($phpbb_dispatcher, $phpbb_container->get('controller.resolver'));
$response = $kernel->handle($symfony_request);
$response->send();
$kernel->terminate($symfony_request, $response);

exit_handler();
