<?php
/**
*
* recaptcha [Bosnian]
*
* @package   language
* @author    Kenan Dervišević <kenan3008@gmail.com>
* @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
* @version   1.0
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
	'RECAPTCHA_LANG'				=> 'bs',
	'RECAPTCHA_NOT_AVAILABLE'		=> 'Da biste mogli koristiti servis reCaptcha, morat ćete se registrovati na stranici <a href="http://recaptcha.net">reCaptcha.net</a>.',
	'CAPTCHA_RECAPTCHA'				=> 'reCaptcha',
	'RECAPTCHA_INCORRECT'			=> 'Vizualni potvrdni kod koji ste unijeli nije ispravan',

	'RECAPTCHA_PUBLIC'				=> 'Javni reCaptcha ključ',
	'RECAPTCHA_PUBLIC_EXPLAIN'		=> 'Vaš javni reCaptcha ključ. Ključeve možete nabaviti na stranici <a href="http://recaptcha.net">reCaptcha.net</a>.',
	'RECAPTCHA_PRIVATE'				=> 'Privatni reCaptcha ključ',
	'RECAPTCHA_PRIVATE_EXPLAIN'		=> 'Vaš privatni reCaptcha ključ. Ključeve možete nabaviti na stranici <a href="http://recaptcha.net">reCaptcha.net</a>.',

	'RECAPTCHA_EXPLAIN'				=> 'U cilju sprečavanja automatskog objavljivanja od vas zahtjevamo da unesete obje riječi prikazane ispod u tekstnom okviru.',
));

?>