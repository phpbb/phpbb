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

$request->overwrite('auth_action', 'register');
$request->overwrite('openid_identifier', 'http://hardolaf.myopenid.com/');
$auth_manager = new phpbb_auth_manager($request, $db, $config, $user);
$provider = $auth_manager->get_provider('openid');
try
{
	$provider->process();
}
catch (phpbb_auth_exception $e)
{
	print_r('<pre>');
	print_r($provider);
	print_r('</pre>');
	trigger_error($e->getMessage());
}
?>
