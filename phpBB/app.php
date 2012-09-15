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

$controller_resolver = $phpbb_container->get('controller.resolver');
$kernel = $phpbb_container->get('kernel');
$response = $kernel->handle($symfony_request);

// Response objects recieve a message as the first argument to the constructor
// The send() method echoes that message. We capture it using Output Buffering
// and can use it for error handling. A controller should send a blank message
// by default. Any non-empty string will result in a trigger error containing
// the message. This allows controllers to send errors as a response instead
// of using trigger_error() directly.
ob_start();
$response->send();
$error = ob_get_clean();

if (!empty($error))
{
	trigger_error($error);
}
