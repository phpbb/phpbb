<?php
/** 
*
* acp_modules [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE 
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang += array(
	'ACP_MODULE_MANAGEMENT_EXPLAIN'	=> 'Here you are able to manage all kind of modules. Please note that if you place the same module under different categories, the category selected will be the first one found within the tree.',
	'ADD_MODULE'					=> 'Add module',
	'ADD_MODULE_CONFIRM'			=> 'Are you sure you want to add the selected module with the selected mode?',
	'ADD_MODULE_TITLE'				=> 'Add Module',

	'CANNOT_REMOVE_MODULE'	=> 'Unable to remove module, it has assigned childs. Please remove or move all childs before performing this action',
	'CATEGORY'				=> 'Category',
	'CHOOSE_MODE'			=> 'Choose Module Mode',
	'CHOOSE_MODE_EXPLAIN'	=> 'Choose the modules mode being used.',
	'CHOOSE_MODULE'			=> 'Choose Module',
	'CHOOSE_MODULE_EXPLAIN'	=> 'Choose the file being called by this module.',
	'CREATE_MODULE'			=> 'Create new module',

	'DEACTIVATED_MODULE'	=> 'Deactivated module',
	'DELETE_MODULE'			=> 'Delete module',
	'DELETE_MODULE_CONFIRM'	=> 'Are you sure you want to remove this module?',

	'EDIT_MODULE'			=> 'Edit module',
	'EDIT_MODULE_EXPLAIN'	=> 'Here you are able to enter module specific settings',

	'MODULE'					=> 'Module',
	'MODULE_ADDED'				=> 'Module successfully added',
	'MODULE_DELETED'			=> 'Module successfully removed',
	'MODULE_DISPLAYED'			=> 'Module displayed',
	'MODULE_DISPLAYED_EXPLAIN'	=> 'If you do not whish to display this module, but want to use it, set this to no.',
	'MODULE_EDITED'				=> 'Module successfully edited',
	'MODULE_ENABLED'			=> 'Module enabled',
	'MODULE_LANGNAME'			=> 'Module Language Name',
	'MODULE_LANGNAME_EXPLAIN'	=> 'Enter the displayed module name. Use language constant if name is served from language file.',
	'MODULE_TYPE'				=> 'Module type',

	'NO_CATEGORY_TO_MODULE'	=> 'Unable to turn category into module. Please remove/move all childs before performing this action.',
	'NO_MODULE'				=> 'No module found',
	'NO_MODULE_ID'			=> 'No module id specified',
	'NO_MODULE_LANGNAME'	=> 'No module language name specified',
	'NO_PARENT'				=> 'No Parent',

	'PARENT'				=> 'Parent',
	'PARENT_NO_EXIST'		=> 'Parent does not exist',

	'SELECT_MODULE'			=> 'Select a module',
);

?>