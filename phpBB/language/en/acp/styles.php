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
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, [
	'ACP_STYLES_EXPLAIN'						=> 'Here you can manage the styles available on your board.<br>Please note you cannot uninstall the “<strong>prosilver</strong>” style as it is phpBB’s default and primary parent style.',

	'CANNOT_BE_INSTALLED'						=> 'Cannot be installed',
	'CONFIRM_UNINSTALL_STYLES'					=> 'Are you sure you wish to uninstall selected styles?',
	'COPYRIGHT'									=> 'Copyright',

	'DEACTIVATE_DEFAULT'						=> 'You cannot deactivate the default style.',
	'DELETE_FROM_FS'							=> 'Delete from filesystem',
	'DELETE_STYLE_FILES_FAILED'					=> 'Error deleting files for style "%s".',
	'DELETE_STYLE_FILES_SUCCESS'				=> 'Files for style "%s" have been deleted.',
	'DETAILS'									=> 'Details',

	'INHERITING_FROM'							=> 'Inherits from',
	'INSTALL_STYLE'								=> 'Install style',
	'INSTALL_STYLES'							=> 'Install styles',
	'INSTALL_STYLES_EXPLAIN'					=> 'Here you can install new styles.<br>If you cannot find a specific style in list below, check to make sure style is already installed. If it is not installed, check if it was uploaded correctly.',
	'INVALID_STYLE_ID'							=> 'Invalid style ID.',

	'NO_MATCHING_STYLES_FOUND'					=> 'No styles match your query.',
	'NO_UNINSTALLED_STYLE'						=> 'No uninstalled styles detected.',

	'PURGED_CACHE'								=> 'Cache was purged.',

	'REQUIRES_STYLE'							=> 'This style requires the style "%s" to be installed.',

	'STYLE_ACTIVATE'							=> 'Activate',
	'STYLE_ACTIVE'								=> 'Active',
	'STYLE_DEACTIVATE'							=> 'Deactivate',
	'STYLE_DEFAULT'								=> 'Make default style',
	'STYLE_DEFAULT_CHANGE_INACTIVE'				=> 'You must activate style before making it default style.',
	'STYLE_ERR_INVALID_PARENT'					=> 'Invalid parent style.',
	'STYLE_ERR_NAME_EXIST'						=> 'A style with that name already exists.',
	'STYLE_ERR_STYLE_NAME'						=> 'You must supply a name for this style.',
	'STYLE_INSTALLED'							=> 'Style "%s" has been installed.',
	'STYLE_INSTALLED_RETURN_INSTALLED_STYLES'	=> 'Return to installed styles list',
	'STYLE_INSTALLED_RETURN_UNINSTALLED_STYLES'	=> 'Install more styles',
	'STYLE_NAME'								=> 'Style name',
	'STYLE_NAME_RESERVED'						=> 'Style "%s" can not be installed, because the name is reserved.',
	'STYLE_NOT_INSTALLED'						=> 'Style "%s" was not installed.',
	'STYLE_PATH'								=> 'Style path',
	'STYLE_UNINSTALL'							=> 'Uninstall',
	'STYLE_UNINSTALL_DEPENDENT'					=> 'Style "%s" cannot be uninstalled because it has one or more child styles.',
	'STYLE_UNINSTALLED'							=> 'Style "%s" uninstalled successfully.',
	'STYLE_PHPBB_VERSION'						=> 'phpBB Version',
	'STYLE_USED_BY'								=> 'Used by (including robots)',
	'STYLE_VERSION'								=> 'Style version',

	'UNINSTALL_PROSILVER'						=> 'You cannot uninstall the style “prosilver”.',
	'UNINSTALL_DEFAULT'							=> 'You cannot uninstall the default style.',

	'BROWSE_STYLES_DATABASE'					=> 'Browse styles database',
]);
