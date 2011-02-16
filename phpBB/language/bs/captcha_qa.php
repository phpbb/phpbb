<?php
/**
*
*
* captcha_qa [Bosnian]
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
	'CAPTCHA_QA'				=> 'Q&amp;A',
	'CONFIRM_QUESTION_EXPLAIN'	=> 'Ovo pitanje je način na koji se sprečava automatsko objavljivanje od strane spam robota.',
	'CONFIRM_QUESTION_WRONG'	=> 'Upisali se pogrešan odgovor na pitanje.',

	'QUESTION_ANSWERS'			=> 'Odgovori',
	'ANSWERS_EXPLAIN'			=> 'Molimo vas da unesete ispravne odgovore na pitanja. Jedan odgovor po redu.',
	'CONFIRM_QUESTION'			=> 'Pitanje',

	'ANSWER'					=> 'Odgovor',
	'EDIT_QUESTION'				=> 'Uredi pitanje',
	'QUESTIONS'					=> 'Odgovori',
	'QUESTIONS_EXPLAIN'			=> 'Za svako objavljivanje u kojem je omogućen Q&amp;A plugin, korisnicima će biti postavljeno jedno od pitanja upisanih ovdje. Za korištenje ovog plugina, barem jedno pitanje mora biti postavljeno na početnom jeziku. Ova pitanja bi trebala biti jednostavna i namijenjena običnim korisnicima vaše stranice, ali ipak ne prejednostavna za botove sposobne da koriste Google™ pretragu. Korištenjem velikog broja pitanja i njihovim redovnim mijenjanjem postići ćete najbolje rezultate. Omogućite stroge postavke ako se vaši odgovori oslanjaju na velika i mala slova, Enable the strict setting if your question relies on mixed case, interpunkcijske oznaka ili razmake.',
	'QUESTION_DELETED'			=> 'Pitanje obrisano',
	'QUESTION_LANG'				=> 'Jezik',
	'QUESTION_LANG_EXPLAIN'		=> 'Jezik na kojem su odgovor i pitanje napisani.',
	'QUESTION_STRICT'			=> 'Stroga provjera',
	'QUESTION_STRICT_EXPLAIN'	=> 'Omogućite da primorate upotrebu velikih i malih slova, interpunkcijskih znakova i razmaka.',

	'QUESTION_TEXT'				=> 'Pitanje',
	'QUESTION_TEXT_EXPLAIN'		=> 'Pitanje koje će biti postavljeno korisniku.',

	'QA_ERROR_MSG'				=> 'Molimo vas da popunite sva polja i unesete barem jedan odgovor.',
	'QA_LAST_QUESTION'			=> 'Ne možete obrisati sva pitanja sve dok je plugin aktivan.',

));

?>