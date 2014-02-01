<?php
/**
*
* app [English]
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
	'CONTROLLER_ARGUMENT_VALUE_MISSING'	=> 'Missing value for argument #%1$s: <strong>%3$s</strong> in class <strong>%2$s</strong>',
	'CONTROLLER_NOT_SPECIFIED'			=> 'No controller has been specified.',
	'CONTROLLER_NOT_FOUND'				=> 'The requested page could not be found.',
	'CONTROLLER_METHOD_NOT_SPECIFIED'	=> 'No method was specified for the controller.',
	'CONTROLLER_SERVICE_NOT_GIVEN'		=> 'The controller "<strong>%s</strong>" must have a service specified in ./config/routing.yml.',
	'CONTROLLER_SERVICE_UNDEFINED'		=> 'The service for controller "<strong>%s</strong>" is not defined in ./config/services.yml.',
	'CONTROLLER_RETURN_TYPE_INVALID'	=> 'The controller object <strong>%s</strong> must return a Symfony\Component\HttpFoundation\Response object.',
));
