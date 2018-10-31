<?php
/**
*
* recaptcha [Română]
*
* @package language
* @version $Id: captcha_recaptcha.php 9933 2009-08-06 09:12:21Z marshalrusty $
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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

$lang = array_merge($lang, array(
	'RECAPTCHA_LANG'				=> 'ro',
	'RECAPTCHA_NOT_AVAILABLE'		=> 'Pentru a folosi reCaptcha, trebuie să vă creaţi un cont la adresa <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',
	'CAPTCHA_RECAPTCHA'				=> 'reCaptcha',
	'RECAPTCHA_INCORRECT'			=> 'Codul de confirmare vizuală trimis a fost incorect',

	'RECAPTCHA_PUBLIC'				=> 'Cheia publică reCaptcha',
	'RECAPTCHA_PUBLIC_EXPLAIN'		=> 'Cheia publică reCaptcha proprie. Cheile pot fi obţinute la adresa <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',
	'RECAPTCHA_PRIVATE'				=> 'Cheia privată reCaptcha',
	'RECAPTCHA_PRIVATE_EXPLAIN'		=> 'Cheia privată reCaptcha proprie. Cheile pot fi obţinute la adresa <a href="http://www.google.com/recaptcha">www.google.com/recaptcha</a>.',

	'RECAPTCHA_EXPLAIN'				=> 'Pentru a preveni trimiterile automate, vă rugăm să scrieţi ambele cuvinte afişate în căsuţa de text de mai jos.',
));

?>