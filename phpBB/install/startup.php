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

/** @ignore */
if (!defined('IN_PHPBB') || !defined('IN_INSTALL'))
{
	exit;
}

function phpbb_require_updated($path, $phpbb_root_path, $optional = false)
{
	$new_path = $phpbb_root_path . 'install/update/new/' . $path;
	$old_path = $phpbb_root_path . $path;

	if (file_exists($new_path))
	{
		require($new_path);
	}
	else if (!$optional || file_exists($old_path))
	{
		require($old_path);
	}
}

function phpbb_include_updated($path, $phpbb_root_path, $optional = false)
{
	$new_path = $phpbb_root_path . 'install/update/new/' . $path;
	$old_path = $phpbb_root_path . $path;

	if (file_exists($new_path))
	{
		include($new_path);
	}
	else if (!$optional || file_exists($old_path))
	{
		include($old_path);
	}
}

function installer_msg_handler($errno, $msg_text, $errfile, $errline)
{
	global $phpbb_installer_container, $msg_long_text;

	// Acording to https://www.php.net/manual/en/language.operators.errorcontrol.php
	// error_reporting() return a different error code inside the error handler after php 8.0
	$suppresed = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE;
	if (PHP_VERSION_ID < 80000)
	{
		$suppresed = 0;
	}

	if (error_reporting() == $suppresed)
	{
		return true;
	}

	// If the message handler is stripping text, fallback to the long version if available
	if (!$msg_text && !empty($msg_long_text))
	{
		$msg_text = $msg_long_text;
	}

	switch ($errno)
	{
		case E_NOTICE:
		case E_WARNING:
		case E_USER_WARNING:
		case E_USER_NOTICE:
			$msg = '[phpBB Debug] "' . $msg_text . '" in file ' . $errfile . ' on line ' . $errline;

			if (!empty($phpbb_installer_container))
			{
				try
				{
					/** @var \phpbb\install\helper\iohandler\iohandler_interface $iohandler */
					$iohandler = $phpbb_installer_container->get('installer.helper.iohandler');
					$iohandler->add_warning_message($msg);
				}
				catch (\phpbb\install\helper\iohandler\exception\iohandler_not_implemented_exception $e)
				{
					print($msg);
				}
			}
			else
			{
				print($msg);
			}

			return;
		break;
		case E_USER_ERROR:
			$msg = '<b>General Error:</b><br>' . $msg_text . '<br> in file ' . $errfile . ' on line ' . $errline . '<br><br>';

			if (!empty($phpbb_installer_container))
			{
				try
				{
					/** @var \phpbb\install\helper\iohandler\iohandler_interface $iohandler */
					$iohandler = $phpbb_installer_container->get('installer.helper.iohandler');
					$iohandler->add_error_message($msg);
					$iohandler->send_response(true);
					exit();
				}
				catch (\phpbb\install\helper\iohandler\exception\iohandler_not_implemented_exception $e)
				{
					throw new \phpbb\exception\runtime_exception($msg);
				}
			}
			throw new \phpbb\exception\runtime_exception($msg);
		break;
		case E_DEPRECATED:
			return true;
		break;
	}

	return false;
}

/**
 * Register class loaders for installer
 *
 * @param string $phpbb_root_path phpBB root path
 * @param string $phpEx PHP file extension
 */
function installer_class_loader($phpbb_root_path, $phpEx)
{
	$phpbb_class_loader_new = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}install/update/new/phpbb/", $phpEx);
	$phpbb_class_loader_new->register();
	$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', "{$phpbb_root_path}phpbb/", $phpEx);
	$phpbb_class_loader->register();
	$phpbb_class_loader = new \phpbb\class_loader('phpbb\\convert\\', "{$phpbb_root_path}install/convert/", $phpEx);
	$phpbb_class_loader->register();
	$phpbb_class_loader_ext = new \phpbb\class_loader('\\', "{$phpbb_root_path}ext/", $phpEx);
	$phpbb_class_loader_ext->register();
}

/**
 * Installer shutdown function. Tries to resolve errors that might have occured
 * during execution of installer
 *
 * @param int $display_errors Original display errors value
 */
function installer_shutdown_function($display_errors)
{
	$error = error_get_last();

	if ($error)
	{
		// Restore original display errors value
		@ini_set('display_errors', $display_errors);

		// Manually define phpBB root path and phpEx. These will not be passed
		// on from app.php
		$phpbb_root_path = __DIR__ . '/../';
		$phpEx = 'php';

		installer_class_loader($phpbb_root_path, $phpEx);
		$supported_error_levels = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED;

		$cache = new \phpbb\cache\driver\file(__DIR__ . '/../cache/installer/');
		$filesystem = new \phpbb\filesystem\filesystem();
		if (strpos($error['file'], $filesystem->realpath($cache->cache_dir)) !== false && is_writable($cache->cache_dir))
		{
			$file_age = @filemtime($error['file']);

			if ($file_age !== false && ($file_age + 60) < time())
			{
				$cache->purge();

				$symfony_request = new \phpbb\symfony_request(new \phpbb\request\request(new \phpbb\request\type_cast_helper()));

				header('Location: ' . $symfony_request->getRequestUri());
				exit();
			}
			else
			{
				// Language system is not available
				die('The installer has detected an issue with a cached file. Try reloading the page and/or manually clearing the cache to resolve the issue. If you require further assistance, please visit the <a href="https://www.phpbb.com/community/" target="_blank">phpBB support forums</a>.');
			}
		}
		else if ($error['type'] & $supported_error_levels)
		{
			// Convert core errors to user warnings for trigger_error()
			if ($error['type'] == E_CORE_ERROR || $error['type'] == E_COMPILE_ERROR)
			{
				$error['type'] = E_USER_ERROR;
			}
			else if ($error['type'] == E_CORE_WARNING)
			{
				$error['type'] = E_USER_WARNING;
			}

			try
			{
				installer_msg_handler($error['type'], $error['message'], $error['file'], $error['line']);
			}
			catch (\phpbb\exception\runtime_exception $exception)
			{
				echo '<!DOCTYPE html>';
				echo '<html dir="ltr">';
				echo '<head>';
				echo '<meta charset="utf-8">';
				echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
				echo '<title>General Error</title>';
				echo '<style type="text/css">' . "\n" . '/* <![CDATA[ */' . "\n";
				echo '* { margin: 0; padding: 0; } html { font-size: 100%; height: 100%; margin-bottom: 1px; background-color: #E4EDF0; } body { font-family: "Lucida Grande", Verdana, Helvetica, Arial, sans-serif; color: #536482; background: #E4EDF0; font-size: 62.5%; margin: 0; } ';
				echo 'a:link, a:active, a:visited { color: #006699; text-decoration: none; } a:hover { color: #DD6900; text-decoration: underline; } ';
				echo '#wrap { padding: 0 20px 15px 20px; min-width: 615px; } #page-header { text-align: right; height: 40px; } #page-footer { clear: both; font-size: 1em; text-align: center; } ';
				echo '.panel { margin: 4px 0; background-color: #FFFFFF; border: solid 1px  #A9B8C2; } ';
				echo '#errorpage #page-header a { font-weight: bold; line-height: 6em; } #errorpage #content { padding: 10px; } #errorpage #content h1 { line-height: 1.2em; margin-bottom: 0; color: #DF075C; } ';
				echo '#errorpage #content div { margin-top: 20px; margin-bottom: 5px; border-bottom: 1px solid #CCCCCC; padding-bottom: 5px; color: #333333; font: bold 1.2em "Lucida Grande", Arial, Helvetica, sans-serif; text-decoration: none; line-height: 120%; text-align: left; } ';
				echo "\n" . '/* ]]> */' . "\n";
				echo '</style>';
				echo '</head>';
				echo '<body id="errorpage">';
				echo '<div id="wrap">';
				echo '	<div id="acp">';
				echo '	<div class="panel">';
				echo '		<div id="content">';
				echo '			<h1>General Error</h1>';

				echo '			<div>' . $exception->getMessage() . '</div>';

				echo '		</div>';
				echo '	</div>';
				echo '	</div>';
				echo '	<div id="page-footer">';
				echo '		Powered by <a href="https://www.phpbb.com/">phpBB</a>&reg; Forum Software &copy; phpBB Limited';
				echo '	</div>';
				echo '</div>';
				echo '</body>';
				echo '</html>';
			}
		}
	}
}

phpbb_require_updated('includes/startup.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('phpbb/class_loader.' . $phpEx, $phpbb_root_path);

installer_class_loader($phpbb_root_path, $phpEx);

// In case $phpbb_adm_relative_path is not set (in case of an update), use the default.
$phpbb_adm_relative_path = (isset($phpbb_adm_relative_path)) ? $phpbb_adm_relative_path : 'adm/';
$phpbb_admin_path = (defined('PHPBB_ADMIN_PATH')) ? PHPBB_ADMIN_PATH : $phpbb_root_path . $phpbb_adm_relative_path;

// Include files
phpbb_require_updated('includes/compatibility_globals.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('includes/functions.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('includes/functions_content.' . $phpEx, $phpbb_root_path);
phpbb_include_updated('includes/functions_compatibility.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('includes/functions_user.' . $phpEx, $phpbb_root_path);
phpbb_require_updated('includes/utf/utf_tools.' . $phpEx, $phpbb_root_path);

// Set PHP error handler to ours
set_error_handler(defined('PHPBB_MSG_HANDLER') ? PHPBB_MSG_HANDLER : 'installer_msg_handler');
$php_ini = new \bantu\IniGetWrapper\IniGetWrapper();

$ini_display_errors = $php_ini->getNumeric('display_errors');
register_shutdown_function('installer_shutdown_function', $ini_display_errors);
// Suppress errors until we have created the containers
@ini_set('display_errors', 0);

$phpbb_installer_container_builder = new \phpbb\di\container_builder($phpbb_root_path, $phpEx);
$phpbb_installer_container_builder
	->with_environment('installer')
	->without_extensions();

$other_config_path = $phpbb_root_path . 'install/update/new/config';
$config_path = (file_exists($other_config_path . '/installer/config.yml')) ? $other_config_path : $phpbb_root_path . 'config';

$phpbb_installer_container = $phpbb_installer_container_builder
	->with_config_path($config_path)
	->with_custom_parameters(array('cache.driver.class' => 'phpbb\cache\driver\file'))
	->get_container();

@ini_set('display_errors', $ini_display_errors);
