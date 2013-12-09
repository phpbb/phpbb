<?php
/**
*
* form [English]
*
* @package language
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
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
	$lang = array();
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
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'VALIDATE_FALSE'				=> 'This value should be false.',
	'VALIDATE_TRUE'					=> 'This value should be true.',
	'VALIDATE_TYPE'					=> 'This value should be of type %s.',
	'VALIDATE_BLANK'				=> 'This value should be blank.',
	'VALIDATE_SELECTED_INVALID'		=> 'The value you selected is not a valid choice.',
	'VALIDATE_SELECT_ATLEAST'		=> array(
		1	=> 'You must select at least %d choice.',
		2	=> 'You must select at least %d choices.',
	),
	'VALIDATE_SELECT_ATMOST'		=> array(
		1	=> 'You must select at most %d choice.',
		2	=> 'You must select at most %d choices.',
	),
	'VALIDATE_VALUES_INVALID'		=> 'One or more of the given values is invalid.',
	'VALIDATE_UNEXPECTED'			=> 'The fields %s were not expected.',
	'VALIDATE_MISSING'				=> 'The fields %s are missing.',
	'VALIDATE_DATE'					=> 'This value is not a valid date.',
	'VALIDATE_DATETIME'				=> 'This value is not a valid datetime.',
	'VALIDATE_EMAIL'				=> 'This value is not a valid email address.',
	'VALIDATE_FILE_NOT_FOUND'		=> 'The file could not be found.',
	'VALIDATE_FILE_NOT_READABLE'	=> 'The file is not readable.',
	'VALIDATE_FILE_TOO_LARGE'		=> 'The file is too large (%s %s). Allowed maximum size is %s %s).',
	'VALIDATE_FILE_MIMETYPE'		=> 'The mime type of the file is invalid (%s). Allowed mime types are %s.',
	'VALIDATE_TOO_LARGE'			=> 'This value should be %s or less.',
	'VALIDATE_TOO_LONG'				=> array(
		1	=> 'This value is too long. It should have %2$s character or less.',
		2	=> 'This value is too long. It should have %2$s characters or less.',
	),
	'VALIDATE_TOO_SMALL'			=> 'This value should be %s or more.',
	'VALIDATE_TOO_SHORT'			=> array(
		1	=> 'This value is too short. It should have %2$s character or more.',
		2	=> 'This value is too short. It should have %2$s characters or more.',
	),
	'VALIDATE_NOT_BLANK'			=> 'This value should not be blank.',
	'VALIDATE_NOT_NULL'				=> 'This value should not be null.',
	'VALIDATE_NULL'					=> 'This value should be null.',
	'VALIDATE_NOT_VALID'			=> 'This value is not valid.',
	'VALIDATE_NOT_VALID_TIME'		=> 'This value is not a valid time.',
	'VALIDATE_NOT_VALID_URL'		=> 'This value is not a valid URL.',
	'VALIDATE_EQUAL'				=> 'The two values should be equal.',
	'VALIDATE_FILE_TOO_LARGE'		=> 'The file is too large. Allowed maximum size is %s %s.',
	'VALIDATE_FILE_UPLOAD_ERROR'	=> 'The file could not be uploaded.',
	'VALIDATE_NUMBER'				=> 'This value should be a valid number.',
	'VALIDATE_IMAGE'				=> 'This file is not a valid image.',
	'VALIDATE_IP_ADDRESS'			=> 'This is not a valid IP address.',
	'VALIDATE_LANGUAGE'				=> 'This value is not a valid language.',
	'VALIDATE_LOCALE'				=> 'This value is not a valid locale.',
	'VALIDATE_COUNTRY'				=> 'This value is not a valid country.',
	'VALIDATE_DUPLICATE'			=> 'This value is already used.',
	'VALIDATE_IMAGE_SIZE'			=> 'The size of the image could not be detected.',
	'VALIDATE_IMAGE_WIDTH_LARGE'	=> 'The image width is too big (%spx). Allowed maximum width is %spx.',
	'VALIDATE_IMAGE_WIDTH_SMALL'	=> 'The image width is too small (%spx). Minimum width expected is %spx.',
	'VALIDATE_IMAGE_HEIGHT_LARGE'	=> 'The image height is too big (%spx). Allowed maximum height is %spx.',
	'VALIDATE_IMAGE_HEIGHT_SMALL'	=> 'The image height is too small (%spx). Minimum height expected is %spx.',
	'VALIDATE_PASSWORD_MATCH'		=> 'This value should be the user current password.',
	'VALIDATE_LENGTH_EXACT'			=> array(
		1	=> 'This value should have exactly %s character.',
		2	=> 'This value should have exactly %s characters.',
	),
	'VALIDATE_FILE_UPLOAD_PARTIAL'	=> 'The file was only partially uploaded.',
	'VALIDATE_FILE_UPLOAD_EMPTY'	=> 'No file was uploaded.',
	'VALIDATE_COLLECTION_SMALL'		=> array(
		1	=> 'This collection should contain %s element or more.',
		2	=> 'This collection should contain %s elements or more.',
	),
	'VALIDATE_COLLECTION_LARGE'		=> array(
		1	=> 'This collection should contain %s element or less.',
		2	=> 'This collection should contain %s elements or less.',
	),
	'VALIDATE_COLLECTION_EXACT'		=> array(
		1	=> 'This collection should contain exactly %s element.',
		2	=> 'This collection should contain exactly %s elements.',
	),
	'VALIDATE_CARD'					=> 'Invalid card number.',
	'VALIDATE_CARD_TYPE'			=> 'Unsupported card type or invalid card number.',
));
