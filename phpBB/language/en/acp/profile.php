<?php
/** 
*
* acp_profile [English]
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

// Custom profile fields
$lang += array(
	'ADDED_PROFILE_FIELD'	=> 'Successfully added custom profile field',
	'ALPHA_ONLY'			=> 'Alphanumeric only',
	'ALPHA_SPACERS'			=> 'Alphanumeric and spacers',
	'ALWAYS_TODAY'			=> 'Always the current date',

	'BOOL_ENTRIES_EXPLAIN'	=> 'Enter your options now',
	'BOOL_TYPE_EXPLAIN'		=> 'Define the Type, either a checkbox or radio buttons',

	'CHANGED_PROFILE_FIELD'		=> 'Successfully changed profile field',
	'CHARS_ANY'					=> 'Any character',
	'CHECKBOX'					=> 'Checkbox',
	'CP_LANG_DEFAULT_VALUE'		=> 'Default Value',
	'CP_LANG_EXPLAIN'			=> 'Field Description',
	'CP_LANG_EXPLAIN_EXPLAIN'	=> 'The Explanation for this field presented to the user',
	'CP_LANG_NAME'				=> 'Field Name presented to the user',
	'CP_LANG_OPTIONS'			=> 'Options',
	'CREATE_NEW_FIELD'			=> 'Create New Field',
	'COLUMNS'					=> 'Columns',

	'DEFAULT_ISO_LANGUAGE'			=> 'Default Language [%s]',
	'DEFAULT_VALUE'					=> 'Default Value',
	'DELETE_PROFILE_FIELD'			=> 'Remove profile field',
	'DELETE_PROFILE_FIELD_CONFIRM'	=> 'Are you sure you want to delete this profile field?',
	'DISPLAY_AT_REGISTRATION'		=> 'Display at registration screen',
	'DROPDOWN_ENTRIES_EXPLAIN'		=> 'Enter your options now, every option in one line',

	'EMPTY_FIELD_IDENT'			=> 'Empty field name',
	'EMPTY_USER_FIELD_NAME'		=> 'Empty Field Name presented to the user',
	'ENTRIES'					=> 'Entries',
	'EVERYTHING_OK'				=> 'Everything OK',
	'EXCLUDE_FROM_VIEW'			=> 'Do not display profile field',
	'EXCLUDE_FROM_VIEW_EXPLAIN'	=> 'The profile field will not be shown on viewtopic/viewprofile/memberlist/etc.',

	'FIELD_BOOL'				=> 'Boolean (Yes/No)',
	'FIELD_DATE'				=> 'Date',
	'FIELD_DESCRIPTION'			=> 'Field Description',
	'FIELD_DESCRIPTION_EXPLAIN'	=> 'The Explanation for this field presented to the user',
	'FIELD_DROPDOWN'			=> 'Dropdown Box',
	'FIELD_IDENT'				=> 'Field Name',
	'FIELD_IDENT_EXPLAIN'		=> 'The Field Name is a name for you to identify the profile field, it is not displayed to the user.',
	'FIELD_INT'					=> 'Numbers',
	'FIELD_LENGTH'				=> 'Length of input box',
	'FIELD_NOT_FOUND'			=> 'Profile field not found',
	'FIELD_STRING'				=> 'Single Textfield',
	'FIELD_TEXT'				=> 'Textarea',
	'FIELD_TYPE'				=> 'Field Type',
	'FIELD_TYPE_EXPLAIN'		=> 'You are not able to change the field type later.',
	'FIELD_VALIDATION'			=> 'Field Validation',
	'FIRST_OPTION'				=> 'First Option',

	'HIDE_PROFILE_FIELD'			=> 'Hide Profile Field',
	'HIDE_PROFILE_FIELD_EXPLAIN'	=> 'Only Administrators and Moderators are able to see/fill out this profile field',

	'INVALID_CHARS_FIELD_IDENT'	=> 'Field name can only contain lowercase a-z and _',
	'ISO_LANGUAGE'				=> 'Language [%s]',

	'LANG_SPECIFIC_OPTIONS'		=> 'Language specific options [<b>%s</b>]',

	'MAX_FIELD_CHARS'		=> 'Maximum number of characters',
	'MAX_FIELD_NUMBER'		=> 'Highest allowed number',
	'MIN_FIELD_CHARS'		=> 'Minimum number of characters',
	'MIN_FIELD_NUMBER'		=> 'Lowest allowed number',

	'NO_FIELD_ENTRIES'			=> 'No Entries defined',
	'NO_FIELD_ID'				=> 'No field id specified',
	'NO_FIELD_TYPE'				=> 'No Field type specified',
	'NO_VALUE_OPTION'			=> 'Option equal to non entered value',
	'NO_VALUE_OPTION_EXPLAIN'	=> 'Value for a non-entry. If the field is required, the user gets an error if he choose the option selected here',
	'NUMBERS_ONLY'				=> 'Only numbers (0-9)',

	'PREVIEW_PROFILE_FIELD'		=> 'Preview Profile Field',
	'PROFILE_BASIC_OPTIONS'		=> 'Basic Options',
	'PROFILE_FIELD_ACTIVATED'	=> 'Profile field successfully activated',
	'PROFILE_FIELD_DEACTIVATED'	=> 'Profile field successfully deactivated',
	'PROFILE_LANG_OPTIONS'		=> 'Language specific options',
	'PROFILE_TYPE_OPTIONS'		=> 'Profile type specific options',

	'RADIO_BUTTONS'				=> 'Radio Buttons',
	'REMOVED_PROFILE_FIELD'		=> 'Successfully removed profile field.',
	'REQUIRED_FIELD'			=> 'Required Field',
	'REQUIRED_FIELD_EXPLAIN'	=> 'Force profile field to be filled out or specified by user',
	'ROWS'						=> 'Rows',

	'SAVE'							=> 'Save',
	'SECOND_OPTION'					=> 'Second Option',
	'STEP_1_EXPLAIN_CREATE'			=> 'Here you can enter the first basic parameters of your new profile field. These informations are needed for the second step where you are able to set remaining options and where you are able to preview and tweak your profile field further.',
	'STEP_1_EXPLAIN_EDIT'			=> 'Here you can change the basic parameters of your profile field. The relevant options are re-calculated within the second step, where you are able to preview and test the changed settings.',
	'STEP_1_TITLE_CREATE'			=> 'Add Profile Field',
	'STEP_1_TITLE_EDIT'				=> 'Edit Profile Field',
	'STEP_2_EXPLAIN_CREATE'			=> 'Here you are able to define some common options. Further you are able to preview the field you generated, as the user will see it. Play around with it until you are satisfied as how the field behaves.',
	'STEP_2_EXPLAIN_EDIT'			=> 'Here you are able to change some common options. Further you are able to preview the changed field, as the user will see it. Play around with it until you are satisfied as how the field behaves.<br /><b>Please note that changes to profile fields will not affect existing profile fields entered by your users.</b>',
	'STEP_2_TITLE_CREATE'			=> 'Profile type specific options',
	'STEP_2_TITLE_EDIT'				=> 'Profile type specific options',
	'STEP_3_EXPLAIN_CREATE'			=> 'Since you have more than one board language installed, you have to fill out the remaining language items too. The profile field will work with the default language enabled, you are able to fill out the remaining language items later too.',
	'STEP_3_EXPLAIN_EDIT'			=> 'Since you have more than one board language installed, you now can change or add the remaining language items too. The profile field will work with the default language enabled.',
	'STEP_3_TITLE_CREATE'			=> 'Remaining Language Definitions',
	'STEP_3_TITLE_EDIT'				=> 'Language Definitions',
	'STRING_DEFAULT_VALUE_EXPLAIN'	=> 'Enter a default phrase to be displayed, a default value. Leave empty if you want to show it empty at the first place.',

	'TEXT_DEFAULT_VALUE_EXPLAIN'	=> 'Enter a default text to be displayed, a default value. Leave empty if you want to show it empty at the first place.',

	'UPDATE_PREVIEW'	=> 'Update Preview',
	'USER_FIELD_NAME'	=> 'Field Name presented to the user',
);

?>