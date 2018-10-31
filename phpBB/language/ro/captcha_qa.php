<?php
/**
*
* captcha_qa [Română]
*
* @package language
* @version $Id: captcha_qa.php 9966 2009-08-12 15:12:03Z Kellanved $
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
	'CAPTCHA_QA'				=> 'Întrebări&amp;Răspunsuri',
	'CONFIRM_QUESTION_EXPLAIN'	=> 'Această întrebare este o metodă de a preveni trimiterile automate de către boţii de spam.',
	'CONFIRM_QUESTION_WRONG'	=> 'Aţi răspuns incorect la întrebare.',

	'QUESTION_ANSWERS'			=> 'Răspunsuri',
	'ANSWERS_EXPLAIN'			=> 'Vă rugăm să specificaţi răspunsurile corecte la această întrebare, câte unul pe linie.',
	'CONFIRM_QUESTION'			=> 'Întrebare',

	'ANSWER'					=> 'Răspuns',
	'EDIT_QUESTION'				=> 'Modificare Întrebare',
	'QUESTIONS'					=> 'Întrebări',
	'QUESTIONS_EXPLAIN'			=> 'Pentru fiecare trimitere de formular la care aţi activat componenta Întrebări&amp;Răspunsuri utilizatorii vor fi întrebaţi folosind una din întrebările specificate aici. Pentru a folosi acest modul, cel puţin o întrebare trebuie specificată în limba standard. Aceste întrebări ar trebui să aibă un răspuns simplu pentru ca audienţa forumului să poată răspunde dar să nu permită unui robot ce foloseşte căutările Google să găsească răspunsul. Cele mai bune rezultate se obţin folosind un set mare de întrebări ce ar trebui schimbat frecvent. Activaţi verificarea strictă dacă întrebarea se bazează pe litere mari şi mici, punctuaţie sau spaţii albe.',
	'QUESTION_DELETED'			=> 'Întrebare ştearsă',
	'QUESTION_LANG'				=> 'Limbă',
	'QUESTION_LANG_EXPLAIN'		=> 'Limba în care a fost compusă această întrebare şi răspunsurile corespunzătoare.',
	'QUESTION_STRICT'			=> 'Verificare strictă',
	'QUESTION_STRICT_EXPLAIN'	=> 'Dacă este activată, literele mari şi mici, punctuaţia ca şi spaţiile albe vor fi luate în considerare.',

	'QUESTION_TEXT'				=> 'Întrebare',
	'QUESTION_TEXT_EXPLAIN'		=> 'Întrebarea care va fi adresată utilizatorului.',

	'QA_ERROR_MSG'				=> 'Vă rugăm să completaţi toate câmpurile şi să specificaţi cel puţin un răspuns.',
    'QA_LAST_QUESTION'			=> 'Nu puteţi şterge toate întrebările cât timp componenta captcha este activă.',
));

?>
