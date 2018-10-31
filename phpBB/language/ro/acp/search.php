<?php
/**
*
* acp_search [Română]
*
* @package language
* @version $Id: search.php,v 1.21 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: search.php,v 1.21 2008/01/13 17:05:00 www.phpbb.ro (shara21jonny) Exp $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
    'ACP_SEARCH_INDEX_EXPLAIN'                => 'Aici puteţi administra indecşii căutării din spatele aplicaţiei. Din moment ce în mod normal folosiţi doar unul singur în backend, ar trebui să ştergeţi toţi indecşii pe care nu-i folosiţi. După ce modificaţi unele din setările de căutare (de exemplu numărul minim/maxim de caractere) s-ar putea să se justifice recrearea indexului astfel încât să reflecte aceste schimbări.',
    'ACP_SEARCH_SETTINGS_EXPLAIN'            => 'Aici puteţi defini ce căutare backend va fi folosită pentru indexarea mesajelor şi efectuarea căutărilor. Puteţi specifica numeroase opţiuni care pot influenţa cât de multă procesare necesită aceste acţiuni. Unele dintre aceste setări sunt aceleaşi pentru toate motoarele de căutare backend.',

    'COMMON_WORD_THRESHOLD'                    => 'Limita pentru cuvânt comun',
    'COMMON_WORD_THRESHOLD_EXPLAIN'            => 'Cuvintele ce sunt conţinute într-un procent mai mare în toate mesagele vor fi privite ca fiind comune. Cuvintele comune sunt ignorate în interogările de căutare. Setaţi 0 pentru a dezactiva. Are efect doar dacă sunt mai mult de 100 de mesaje. Dacă doriţi cuvinte care sunt privite ca şi comune să fie reconsiderate, atunci trebuie să recreaţi indexul.',
    'CONFIRM_SEARCH_BACKEND'                => 'Sunteţi sigur că doriţi să treceţi la un altă căutare în backend? După ce schimbaţi căutarea din backend, va trebui să creaţi un index pentru noua căutare. Dacă nu plănuiţi schimbarea înapoi a vechiului sistem de căutare backend, puteţi de asemenea să ştergeţi toate vechile indexuri de backend pentru a elibera resursele sistemului.',
    'CONTINUE_DELETING_INDEX'                => 'Continua procesul de ştergere a indexului anterior',
    'CONTINUE_DELETING_INDEX_EXPLAIN'        => 'A început procesul de ştergere al unui index. Pentru a accesa pagina indexului de căutare trebuie să se finalizeze sau să anulaţi cererea.',
    'CONTINUE_INDEXING'                        => 'Continuă procesul de indexare anterior',
    'CONTINUE_INDEXING_EXPLAIN'                => 'A început un proces de indexare. Pentru a accesa pagina indexului de căutare trebuie să se finalizeze sau să anulaţi cererea.',
    'CREATE_INDEX'                            => 'Crează index',

    'DELETE_INDEX'                            => 'Şterge index',
    'DELETING_INDEX_IN_PROGRESS'            => 'Ştergerea indexului este în progres',
    'DELETING_INDEX_IN_PROGRESS_EXPLAIN'    => 'Căutarea backend îşi curăţă indexul. Această operaţie poate dura câteva minute.',

    'FULLTEXT_MYSQL_INCOMPATIBLE_VERSION'    => 'Textul MySQL al backend-ului poate fi folosit doar cu versiunea MySQL4 sau alta mai nouă.',
    'FULLTEXT_MYSQL_NOT_SUPPORTED'           => 'Textul MySQL pentru indecşi poate fi folosit doar cu tabele MyISAM sau InnoDB. MySQL 5.6.4 sau o versiune mai veche este necesară pentru indecșii fulltext sau pentru tabele InnoDB.',
    'FULLTEXT_MYSQL_TOTAL_POSTS'            => 'Numărul total al mesajelor indexate',
    'FULLTEXT_MYSQL_MBSTRING'                => 'Suport pentru caracterele ne-latine UTF-8 folosind mbstring:',
    'FULLTEXT_MYSQL_PCRE'                    => 'Suport pentru caracterele ne-latine UTF-8 folosind PCRE:',
    'FULLTEXT_MYSQL_MBSTRING_EXPLAIN'        => 'Dacă PCRE nu are proprietăţi de caracter unicod, căutarea backend va încerca să folosească motorul expresiilor mbstring.',
    'FULLTEXT_MYSQL_PCRE_EXPLAIN'            => 'Căutarea backend necesită proprietăţile de caracter unicod PCRE, disponibile numai în PHP 4.4, 5.1 sau mai nou, dacă vreţi să căutaţi caractere ne-latine.',
    'FULLTEXT_MYSQL_MIN_SEARCH_CHARS_EXPLAIN'   => 'Cuvintele cu cel puţin atâtea caractere vor fi indexate pentru căutare. Veţi putea schimba această setare doar modificând fişierul de configurare mysql.',
    'FULLTEXT_MYSQL_MAX_SEARCH_CHARS_EXPLAIN'   => 'Cuvintele cu cel mult atâtea caractere vor fi indexate pentru căutare. Veţi putea schimba această setare doar modificând fişierul de configurare mysql..',


    'GENERAL_SEARCH_SETTINGS'                => 'Setări generale de căutare',
    'GO_TO_SEARCH_INDEX'                    => 'Mergi la pagina principală de căutare',

    'INDEX_STATS'                            => 'Statistici index',
    'INDEXING_IN_PROGRESS'                    => 'Indexare în progres',
    'INDEXING_IN_PROGRESS_EXPLAIN'            => 'Momentan căutarea backend indexează toate mesajele de pe forum. Aceast proces poate dura de la câteva minute la câteva ore, în funcţie de dimensiunile forumului.',

    'LIMIT_SEARCH_LOAD'                        => 'Limita de încărcare a sistemului cu pagina de căutare',
    'LIMIT_SEARCH_LOAD_EXPLAIN'                => 'Dacă este depăşită limita de 1 minut pentru încărcarea sistemului, paginile de căutare vor fi închise, 1.0 egal ~100% utilizarea unui procesor. Aceasta funcţionează numai pe serverele UNIX.',

    'MAX_SEARCH_CHARS'                        => 'Numărul maxim de caractere indexate de către căutare',
    'MAX_SEARCH_CHARS_EXPLAIN'                => 'Cuvintele cu nu mai mult de atâtea caractere vor fi indexate pentru căutare.',
   	'MAX_NUM_SEARCH_KEYWORDS'									=> 'Numărul maxim al cuvintelor cheie permise',
  	'MAX_NUM_SEARCH_KEYWORDS_EXPLAIN'				=> 'Numărul maxim al cuvintelor după care utilizatorul poate să caute. Valoarea 0 permite un număr nelimitat de cuvinte.',

    'MIN_SEARCH_CHARS'                        => 'Numărul minim de caractere indexate de către căutare',
    'MIN_SEARCH_CHARS_EXPLAIN'                => 'Cuvintele cu cel puţin atâtea caractere vor fi indexate pentru căutare.',
    'MIN_SEARCH_AUTHOR_CHARS'                => 'Numărul minim de caractere pentru numele autorului',
    'MIN_SEARCH_AUTHOR_CHARS_EXPLAIN'        => 'Utilizatorii trebuie să introducă cel puţin atâtea caractere pentru nume atunci când efectuează o căutare după autor folosind un wildcard. Dacă numele de utilizator al autorului este mai scurt decât acest număr, puteţi în continuare căuta mesajele autorului specificând numele complet de utilizator.',

    'PROGRESS_BAR'                            => 'Bară de progres',

    'SEARCH_GUEST_INTERVAL'                    => 'Intervalul de flood al vizitatorilor pentru căutare',
    'SEARCH_GUEST_INTERVAL_EXPLAIN'            => 'Numărul de secunde pe care vizitatorii trebuie să-l aştepte între căutări. Dacă un vizitator efectueză o căutare, atunci toţi ceilalţi vizitatori trebuie să aştepe până trece acest interval.',
    'SEARCH_INDEX_CREATE_REDIRECT'            => 'Au fost indexate toate mesajele până la id-ul mesajului %1$d, din care %2$d mesaje au fost în această etapă.<br />Rata curentă a indexării este de aproximativ %3$.1f mesaje pe secundă.<br />Indexare în progres…',
    'SEARCH_INDEX_DELETE_REDIRECT'            => 'Au fost scoase din indexul căutării toate mesajele până la id-ul mesajului %1$d .<br />Ştergere în progres…',
    'SEARCH_INDEX_CREATED'                    => 'Toate mesajele au fost indexate cu succes în baza de date a forumului.',
    'SEARCH_INDEX_REMOVED'                    => 'Indexul de căutare a fost şters cu succes.',
    'SEARCH_INTERVAL'                        => 'Intervalul de flood al utilizatorilor pentru căutare',
    'SEARCH_INTERVAL_EXPLAIN'                => 'Numărul de secunde pe care utilizatorii trebuie să-l aştepte între căutări. Intervalul este independent pentru fiecare utilizator.',
    'SEARCH_STORE_RESULTS'                    => 'Mărimea cache pentru rezultatele căutării',
    'SEARCH_STORE_RESULTS_EXPLAIN'            => 'Cache-ul de la rezultatele căutării va expira după acest timp, în secunde. Atribuiţi valoarea 0 dacă doriţi să dezactivaţi cache-ul pentru căutări.',
    'SEARCH_TYPE'                            => 'Căutare backend',
    'SEARCH_TYPE_EXPLAIN'                    => 'phpBB permite să alegeţi backend-ul folosit pentru căutarea textelor în cuprinsul mesajelor. Iniţial căutările vor folosi propriul text de căutare phpBB.',
    'SWITCHED_SEARCH_BACKEND'                => 'Aţi înlocuit căutarea backend. Pentru a putea folosi noi căutări backend ar trebui să vă asiguraţi că există un index pentru backend-ul ales.',

    'TOTAL_WORDS'                            => 'Numărul total de cuvinte indexate',
    'TOTAL_MATCHES'                            => 'Numărul total al cuvântului pentru a scrie relaţii indexate',

    'YES_SEARCH'                            => 'Permite facilităţile căutării',
    'YES_SEARCH_EXPLAIN'                    => 'Permite utilizatorilor să efectueze căutări incluzând căutarea membrilor.',
    'YES_SEARCH_UPDATE'                        => 'Permite actualizarea textului',
    'YES_SEARCH_UPDATE_EXPLAIN'                => 'Actualizarea indecşilor text se face în timpul scrierii, această opţiune este suprascrisă dacă este dezactivată căutarea.',
));

?>