<?php
/**
*
* @package testing
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = 'phpBB/';
$phpEx = 'php';
require_once $phpbb_root_path . 'includes/startup.php';

$table_prefix = 'phpbb_';
require_once $phpbb_root_path . 'includes/constants.php';

require_once 'test_framework/phpbb_test_case_helpers.php';
require_once 'test_framework/phpbb_test_case.php';
require_once 'test_framework/phpbb_database_test_case.php';
require_once 'test_framework/phpbb_database_test_connection_manager.php';

if (version_compare(PHP_VERSION, '5.3.0-dev', '>='))
{
	if (getenv('PHPBB_NO_COMPOSER_AUTOLOAD'))
	{
		if (getenv('PHPBB_AUTOLOAD'))
		{
			require(getenv('PHPBB_AUTOLOAD'));
		}
	}
	else
	{
		if (!file_exists($phpbb_root_path . 'vendor/autoload.php'))
		{
			trigger_error('You have not set up composer dependencies. See http://getcomposer.org/.', E_USER_ERROR);
		}
		require($phpbb_root_path . 'vendor/autoload.php');
	}
	require_once 'test_framework/phpbb_functional_test_case.php';
}
