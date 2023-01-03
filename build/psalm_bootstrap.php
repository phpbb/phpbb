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

define('IN_PHPBB', true);
define('PHPBB_ENVIRONMENT', 'test');

$phpbb_root_path = 'phpBB/';
$phpEx = 'php';

global $table_prefix;
require_once $phpbb_root_path . 'includes/startup.php';

$table_prefix = 'phpbb_';
require_once $phpbb_root_path . 'includes/constants.php';
require_once $phpbb_root_path . 'phpbb/class_loader.' . $phpEx;
require_once $phpbb_root_path . 'includes/acp/acp_database.' . $phpEx;
require_once $phpbb_root_path . 'includes/utf/utf_tools.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_acp.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_admin.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_compatibility.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_compress.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_content.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_display.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_mcp.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_messenger.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_module.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_posting.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_transfer.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_user.' . $phpEx;
require_once $phpbb_root_path . 'includes/sphinxapi.' . $phpEx;
require_once $phpbb_root_path . 'includes/diff/diff.' . $phpEx;
require_once $phpbb_root_path . 'includes/diff/engine.' . $phpEx;
require_once $phpbb_root_path . 'includes/compatibility_globals.' . $phpEx;

$phpbb_class_loader = new \phpbb\class_loader('phpbb\\', $phpbb_root_path . 'phpbb/', "php");
$phpbb_class_loader->register();

// Include files that require class loader to be initialized
require_once $phpbb_root_path . 'includes/acp/auth.' . $phpEx;
require_once $phpbb_root_path . 'includes/acp/acp_captcha.' . $phpEx;

class phpbb_cache_container extends \Symfony\Component\DependencyInjection\Container
{
}
