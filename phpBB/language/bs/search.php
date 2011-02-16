<?php
/**
*
* search [Bosnian]
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
	'ALL_AVAILABLE'			=> 'Svi dostupni',
	'ALL_RESULTS'			=> 'Svi rezultati',

	'DISPLAY_RESULTS'		=> 'Prikaži rezultate kao',

	'FOUND_SEARCH_MATCH'		=> 'Pretraga je pronašla %d rezultat',
	'FOUND_SEARCH_MATCHES'		=> 'Pretraga je pronašla %d rezultata',
	'FOUND_MORE_SEARCH_MATCHES'	=> 'Pretraga je pronašla više od %d rezultata',

	'GLOBAL'				=> 'Globalno obavještenje',

	'IGNORED_TERMS'			=> 'zanemareno',
	'IGNORED_TERMS_EXPLAIN'	=> 'Sljedeće riječi su zanemarene u vašem upitu iz razloga što se često pojavljuju u rečenicama: <strong>%s</strong>.',

	'JUMP_TO_POST'			=> 'Idi na poruku',

	'LOGIN_EXPLAIN_EGOSEARCH'	=> 'Forum od vas zahtjeva registraciju i prijavu da biste mogli pregledati vaše poruke.',
	'LOGIN_EXPLAIN_UNREADSEARCH'=> 'Forum od vas zahtjeva registraciju i prijavu da biste mogli pregledati vaše nepročitane poruke.',

	'MAX_NUM_SEARCH_KEYWORDS_REFINE'	=> 'Upisani ste previše riječi za pretragu. Molimo vas da ne unosite više od %1$d riječi.',

	'NO_KEYWORDS'			=> 'Morate upisate najmanje jednu riječ za pretragu. Svaka riječ mora sadržavati najmanje %d a najviše %d znakova isključujući opće oznake (wildcards).',
	'NO_RECENT_SEARCHES'	=> 'Nema nedavnih pretraga.',
	'NO_SEARCH'				=> 'Žao nam je, ali vam nije dozvoljena upotreba sistema za pretragu.',
	'NO_SEARCH_RESULTS'		=> 'Nema rezultata.',
	'NO_SEARCH_TIME'		=> 'Trenutno ne možete koristiti pretragu. Molimo vas da pokušate za par minuta.',
	'NO_SEARCH_UNREADS'		=> 'Pretraga nepročitanih poruka je onemogućena na ovom forumu.',
	'WORD_IN_NO_POST'		=> 'Nema pronađenih poruka zbog toga što riječ <strong>%s</strong> nije sadržana ni u jednoj poruci.',
	'WORDS_IN_NO_POST'		=> 'Nema pronađenih poruka zbog toga što riječi <strong>%s</strong> nisu sadržane ni u jednoj poruci.',

	'POST_CHARACTERS'		=> 'znakova poruke',

	'RECENT_SEARCHES'		=> 'Nedavne pretrage',
	'RESULT_DAYS'			=> 'Ograniči rezultate na prethodnih',
	'RESULT_SORT'			=> 'Poredaj rezultate po',
	'RETURN_FIRST'			=> 'Vrati prvih',
	'RETURN_TO_SEARCH_ADV'	=> 'Nazad na napredno pretraživanje',

	'SEARCHED_FOR'				=> 'Korišteni pojam za pretragu',
	'SEARCHED_TOPIC'			=> 'Pretražena tema',
	'SEARCH_ALL_TERMS'			=> 'Traži sve pojmove ili koristi uneseni upit',
	'SEARCH_ANY_TERMS'			=> 'Traži bilo koje pojmove',
	'SEARCH_AUTHOR'				=> 'Traži autora',
	'SEARCH_AUTHOR_EXPLAIN'		=> 'Koristite * kao opću oznaku za djelimične rezultate.',
	'SEARCH_FIRST_POST'			=> 'Prva poruke iz teme',
	'SEARCH_FORUMS'				=> 'Pretraga u forumima',
	'SEARCH_FORUMS_EXPLAIN'		=> 'Odaberite forum ili forume koje želite pretraživati. Podforumi se pretražuju automatski osim to niste onemogućili ispod u opciji “Pretraga podforuma“.',
	'SEARCH_IN_RESULTS'			=> 'Pretraži ove rezultate',
	'SEARCH_KEYWORDS_EXPLAIN'	=> 'Upišite <strong>+</strong> ispred riječi koja mora biti pronađena a <strong>-</strong> ispred one koja ne smije biti pronađena. Upišite listu riječi odvojenih pomoću <strong>|</strong> u zagradi ako samo jedna od njih mora biti pronađena. Koristite * kao opću oznaku za djelimične rezultate.',
	'SEARCH_MSG_ONLY'			=> 'Teksta poruke',
	'SEARCH_OPTIONS'			=> 'Opcije pretrage',
	'SEARCH_QUERY'				=> 'Upit za pretragu',
	'SEARCH_SUBFORUMS'			=> 'Pretraga podforuma',
	'SEARCH_TITLE_MSG'			=> 'Naslova i teksta poruke',
	'SEARCH_TITLE_ONLY'			=> 'Naslova teme',
	'SEARCH_WITHIN'				=> 'Traži unutar',
	'SORT_ASCENDING'			=> 'Uzlazno',
	'SORT_AUTHOR'				=> 'Autoru',
	'SORT_DESCENDING'			=> 'Silazno',
	'SORT_FORUM'				=> 'Forumu',
	'SORT_POST_SUBJECT'			=> 'Temi poruke',
	'SORT_TIME'					=> 'Vremenu poruke',

	'TOO_FEW_AUTHOR_CHARS'	=> 'Morate upisati najmanje %d znakova autorovog imena.',
));

?>