<?php
/** 
*
* posting [English]
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

// BBCodes 
// Note to translators: you can translate everything but what's between { and }
$lang += array(
	'ACP_BBCODES_EXPLAIN'		=> 'BBCode is a special implementation of HTML offering greater control over what and how something is displayed. Additionnally, you can save users from typing sometimes very long HTML code by providing them a single BBCode as replacement. From this page you can add, remove and edit custom BBCodes',
	'ADD_BBCODE'				=> 'Add a new BBCode',

	'BBCODE_ADDED'				=> 'BBCode added successfully',
	'BBCODE_EDITED'				=> 'BBCode edited successfully',
	'BBCODE_NOT_EXIST'			=> 'The BBCode you selected does not exist',
	'BBCODE_TAG'				=> 'Tag',
	'BBCODE_USAGE'				=> 'BBCode usage',
	'BBCODE_USAGE_EXAMPLE'		=> '[colour={COLOR}]{TEXT}[/colour]<br /><br />[font={TEXT1}]{TEXT2}[/font]',
	'BBCODE_USAGE_EXPLAIN'		=> 'Here you define how to use the bbcode. Replace any variable input by the corresponding token (see below)',

	'EXAMPLE'						=> 'Example:',
	'EXAMPLES'						=> 'Examples:',

	'HTML_REPLACEMENT'				=> 'HTML replacement',
	'HTML_REPLACEMENT_EXAMPLE'		=> '&lt;font color="{COLOR}"&gt;{TEXT}&lt;/font&gt;<br /><br />&lt;font face="{TEXT1}"&gt;{TEXT2}&lt;/font&gt;',
	'HTML_REPLACEMENT_EXPLAIN'		=> 'Here you define the default HTML replacement (each template can have its own HTML replacement). Do not forget to put back tokens you used above!',

	'TOKEN'					=> 'Token',
	'TOKENS'				=> 'Tokens',
	'TOKENS_EXPLAIN'		=> 'Tokens are placeholders for user input. The input will be validated only if it matches the corresponding definition. If needed, you can number them by adding a number as the last character between the braces, e.g. {USERNAME1}, {USERNAME2}.<br /><br />In addition to these tokens you can use any of lang string present in your language/ directory like this: {L_<i>&lt;stringname&gt;</i>} where <i>&lt;stringname&gt;</i> is the name of the translated string you want to add. For example, {L_WROTE} will be displayed as "wrote" or its translation according to user\'s locale',
	'TOKEN_DEFINITION'		=> 'What can it be?',
	'TOO_MANY_BBCODES'		=> 'You cannot create any more BBCodes. Please remove one or more BBCodes then try again',

	'tokens'	=>	array(
		'TEXT'			=> 'Any text, including foreign characters, numbers, etc...',
		'NUMBER'		=> 'Any serie of digits',
		'EMAIL'			=> 'A valid email address',
		'URL'			=> 'A valid URL using any protocol (http, ftp, etc... cannot be used for javascript exploits). If none is given, "http://" is prepended to to the string',
		'LOCAL_URL'		=> 'A local URL. The URL must be relative to the topic page and cannot contain a server name or protocol',
		'COLOR'			=> 'A HTML color, can be either in the numeric form #FF1234 or an english name such as "blue"'
	)
);

// Smilies and topic icons
$lang += array(
	'ACP_ICONS_EXPLAIN'		=> 'From this page you can add, remove and edit the icons users may add to their topics or posts. These icons are generally displayed next to topic titles on the forum listing, or the post subjects in topic listings. You can also install and create new packages of icons.',
	'ACP_SMILIES_EXPLAIN'	=> 'Smilies or emoticons are typically small, sometimes animated images used to convey an emotion or feeling. From this page you can add, remove and edit the emoticons users can use in their posts and private messages. You can also install and create new packages of smilies.',
	'ADD_SMILIES'			=> 'Add multiple smilies',
	'ADD_ICONS'				=> 'Add multiple icons',
	'AFTER_ICONS'			=> 'After %s',
	'AFTER_SMILIES'			=> 'After %s',

	'CODE'				=> 'Code',
	'CURRENT_ICONS'		=> 'Current icons',
	'CURRENT_SMILIES'	=> 'Current smilies',

	'DELETE_ALL'			=> 'Delete all',
	'DISPLAY_ON_POSTING'	=> 'Display on posting',

	'EDIT_ICONS'				=> 'Edit Icons',
	'EDIT_SMILIES'				=> 'Edit smilies',
	'EMOTION'					=> 'Emotion',
	'EXPORT_ICONS'				=> 'Create icons pak',
	'EXPORT_ICONS_EXPLAIN'		=> 'To create a package of your currently installed icons, click %sHERE%s to download the icons package file. Once downloaded create a zip or tgz file containing all of your icons plus this .pak configuration file.',
	'EXPORT_SMILIES'			=> 'Create smilies pak',
	'EXPORT_SMILIES_EXPLAIN'	=> 'To create a package of your currently installed smilies, click %sHERE%s to download the smilies.pak file. Once downloaded create a zip or tgz file containing all of your smilies plus this .pak configuration file.',

	'FIRST'			=> 'First',

	'ICONS_ADD'				=> 'Add a new Icon',
	'ICONS_ADDED'			=> 'The icon has been added successfully.',
	'ICONS_CONFIG'			=> 'Icon configuration',
	'ICONS_DELETED'			=> 'The icon has been removed successfully.',
	'ICONS_EDIT'			=> 'Edit Icon',
	'ICONS_EDITED'			=> 'The icon has been updated successfully.',
	'ICONS_HEIGHT'			=> 'Icon height',
	'ICONS_IMAGE'			=> 'Icon image',
	'ICONS_IMPORTED'		=> 'The icons pack has been installed successfully.',
	'ICONS_IMPORT_SUCCESS'	=> 'The icons pack was imported successfully',
	'ICONS_LOCATION'		=> 'Icon location',
	'ICONS_NOT_DISPLAYED'	=> 'The following icons are not displayed on the posting page',
	'ICONS_ORDER'			=> 'Icon order',
	'ICONS_URL'				=> 'Icon image file',
	'ICONS_WIDTH'			=> 'Icon width',
	'IMPORT_ICONS'			=> 'Install icons pak',
	'IMPORT_SMILIES'		=> 'Install smilies pak',

	'KEEP_ALL'			=> 'Keep all',

	'MASS_ADD_SMILIES'	=> 'Add multiple smilies',

	'NO_ICONS_EXPORT'	=> 'You have no icons with which to create a package.',
	'NO_ICONS_PAK'		=> 'No icon packages found.',
	'NO_SMILIES_EXPORT'	=> 'You have no smilies with which to create a package.',
	'NO_SMILIES_PAK'	=> 'No smiley packages found.',

	'PAK_FILE_NOT_READABLE'		=> 'Could not read pak file',

	'REPLACE_MATCHES'	=> 'Replace matches',

	'SELECT_PACKAGE'			=> 'Select a package file',
	'SMILIES_ADD'				=> 'Add a new Smiley',
	'SMILIES_ADDED'				=> 'The smiley has been added successfully.',
	'SMILIES_CODE'				=> 'Smiley code',
	'SMILIES_CONFIG'			=> 'Smiley configuration',
	'SMILIES_DELETED'			=> 'The smiley has been removed successfully.',
	'SMILIES_EDIT'				=> 'Edit Smiley',
	'SMILIES_EDITED'			=> 'The smiley has been updated successfully.',
	'SMILIES_EMOTION'			=> 'Emotion',
	'SMILIES_HEIGHT'			=> 'Smiley height',
	'SMILIES_IMAGE'				=> 'Smiley image',
	'SMILIES_IMPORTED'			=> 'The smilies pack has been installed successfully.',
	'SMILIES_IMPORT_SUCCESS'	=> 'The smilies pack was imported successfully',
	'SMILIES_LOCATION'			=> 'Smiley location',
	'SMILIES_NOT_DISPLAYED'		=> 'The following smilies are not displayed on the posting page',
	'SMILIES_ORDER'				=> 'Smiley order',
	'SMILIES_URL'				=> 'Smiley image file',
	'SMILIES_WIDTH'				=> 'Smiley width',

	'WRONG_PAK_TYPE'	=> 'The specified package does not contain the appropriate data.',
);

// Word censors
$lang += array(
	'ACP_WORDS_EXPLAIN'		=> 'From this control panel you can add, edit, and remove words that will be automatically censored on your forums. In addition people will not be allowed to register with usernames containing these words. Wildcards (*) are accepted in the word field, eg. *test* will match detestable, test* would match testing, *test would match detest.',
	'ADD_WORD'				=> 'Add new word',

	'EDIT_WORD'		=> 'Edit word censor',
	'ENTER_WORD'	=> 'You must enter a word and its replacement',

	'NO_WORD'	=> 'No word selected for editing',

	'REPLACEMENT'	=> 'Replacement',

	'UPDATE_WORD'	=> 'Update word censor',

	'WORD'				=> 'Word',
	'WORD_ADDED'		=> 'The word censor has been successfully added',
	'WORD_REMOVED'		=> 'The selected word censor has been successfully removed',
	'WORD_UPDATED'		=> 'The selected word censor has been successfully updated',
);

?>