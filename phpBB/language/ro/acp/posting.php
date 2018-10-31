<?php
/**
*
* acp_posting [Română]
*
* @package language
* @version $Id: posting.php 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: posting.php, 8479 2008-05-19 20:49:11 www.phpbb.ro (shara21jonny) Exp $
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

// BBCodes
// Note to translators: you can translate everything but what's between { and }
$lang = array_merge($lang, array(
	'ACP_BBCODES_EXPLAIN'		=> 'Codul BB este o implementare specială de tip HTML oferind un control mai mare asupra a ce şi cum este afişat. Din această pagină puteţi adăuga, şterge şi modifica codurile BB personalizate.',
	'ADD_BBCODE'				=> 'Adaugă un cod BB nou',
	'BBCODE_DANGER'				=> 'Codul BB pe care vreţi să-l adăugaţi pare să folosească simbolul {TEXT} în cadrul unui atribut HTML. Acesta poate fi o problemă de securitate XSS. Încercaţi să folosiţi tipuri mai restrictive de genul {SIMPLETEXT} sau {INTTEXT}. Continuaţi doar dacă înţelegeţi riscurile implicate şi consideraţi că folosirea {TEXT} este absolut de neevitat.',
	'BBCODE_DANGER_PROCEED'		=> 'Continuă', //'Înţeleg riscul',


	'BBCODE_ADDED'				=> 'Codul BB a fost adăugat cu succes.',
	'BBCODE_EDITED'				=> 'Codul BB a fost modificat cu succes.',
	'BBCODE_NOT_EXIST'			=> 'Codul BB pe care l-aţi selectat nu există.',
	'BBCODE_HELPLINE'			=> 'Asistenţă',
	'BBCODE_HELPLINE_EXPLAIN'	=> 'Acest câmp conţine efectul de text "mouseover" al codului BB',
	'BBCODE_HELPLINE_TEXT'		=> 'Text asistenţă',
	'BBCODE_HELPLINE_TOO_LONG'   => 'Textul de asistenţă specificat este prea lung.',
	'BBCODE_INVALID_TAG_NAME'	=> 'Numele etichetei de codul BB pe care l-aţi selectat există deja.',
	'BBCODE_INVALID'			=> 'Codul BB este construit într-o formă invalidă.',
	'BBCODE_OPEN_ENDED_TAG'		=> 'Codul BB dumneavoastră personalizat trebuie să conţină ambele etichete, de deschidere şi de închidere.',
	'BBCODE_TAG'				=> 'Etichetă',
	'BBCODE_TAG_TOO_LONG'		=> 'Numele etichetei pe care aţi selectat-o este prea lung.',
	'BBCODE_TAG_DEF_TOO_LONG'	=> 'Definiţia etichetei pe care aţi specificat-o este prea lungă, vă rugăm să scurtaţi definiţia etichetei.',
	'BBCODE_USAGE'				=> 'Folosire cod BB',
	'BBCODE_USAGE_EXAMPLE'		=> '[highlight={CULOARE}]{TEXT}[/highlight]<br /><br />[font={TEXTSIMPLU1}]{TEXTSIMPLU2}[/font]',
	'BBCODE_USAGE_EXPLAIN'		=> 'Aici definiţi cum să folosiţi codul BB. Înlocuiţi orice variabilă de citire cu simbolul corespunzator (%svezi mai jos%s)',

	'EXAMPLE'						=> 'Exemplu:',
	'EXAMPLES'						=> 'Exemple:',

	'HTML_REPLACEMENT'				=> 'Schimbare HTML',
	'HTML_REPLACEMENT_EXAMPLE'		=> '&lt;span style="background-color: {CULOARE};"&gt;{TEXT}&lt;/span&gt;<br /><br />&lt;span style="font-family: {TEXTSIMPLU1};"&gt;{TEXTSIMPLU2}&lt;/span&gt;',
	'HTML_REPLACEMENT_EXPLAIN'		=> 'Aici puteţi defini modul de substituire al HTML-ului iniţal. Nu uitaţi să puneţi înapoi simbolurile folosite mai sus!',

	'TOKEN'					=> 'Simbol',
	'TOKENS'				=> 'Simboluri',
	'TOKENS_EXPLAIN'		=> 'Simbolurile sunt locuri de păstrare pentru specificaţiile utilizatorului. Specificaţiile vor fi validate doar dacă se potrivesc cu definiţa corespondentă. Dacă e nevoie, le puteţi numerota adăugând un număr ca ultimul caracter între acolade, de exemplu {TEXT1}, {TEXT2}.<br /><br />În cadrul înclocuirii HTML-ului puteţi folosi orice şir de limbaj prezent în directorul language/ ca şi acesta: {L_<em>&lt;NUMESIR&gt;</em>} unde <em>&lt;NUMESIR&gt;</em> este numele şirului translatat pe care vreţi să îl adăugaţi. De exemplu, {L_WROTE} va fi afişat ca „scrie” sau corespunzător cu traducerea locală a utilizatorului',
	'TOKEN_DEFINITION'		=> 'Ce poate fi?',
	'TOO_MANY_BBCODES'		=> 'Nu puteţi crea niciun cod BB. Trebuie să ştergeţi unul sau mai multe coduri BB iar apoi încercaţi din nou.',

	'tokens'	=>	array(
		'TEXT'			=> 'Orice text, inclusiv caractere străine, numere, etc… Ar trebui să nu folosiţi acest simbol în etichetele HTML. Încercaţi să folosiţi IDENTIFIER, INTTEXT sau SIMPLETEXT ',
		'SIMPLETEXT'	=> 'Caracterele din alfabetul latin (A-Z), numere, spaţii, virgule, puncte, minus, plus, cratimă şi liniuţă de subliniere',
		'INTTEXT'		=> 'Caractere litere Unicode, numere, spaţii, virgule, puncte, minus, plus, cratimă, liniuţă de subliniere şi spaţii libere.',
		'IDENTIFIER'	=> 'Caracterele din alfabetul latin (A-Z), numere, cratimă şi liniuţă de subliniere',
		'NUMBER'		=> 'Orice serie de numere',
		'EMAIL'			=> 'O adresă de email validă',
		'URL'			=> 'O adresă web validă ce foloseşte orice protocol (http, ftp, etc… nu poate fi folosit pentru scripturi java). Dacă nu este specificat, „http://” este adăugat şirului ca şi prefix',
		'LOCAL_URL'		=> 'O adresă web locală. Adresa web trebuie să fie relativă la pagina subiectului şi nu poate conţine un nume de server sau protocol, iar legăturile să înceapă cu „%s”',
		'RELATIVE_URL'   => 'Adresă URL relativă. Puteți utiliza aceasta opțiune pentru a potrivi părți dintr-o adresă URL, dar atenție: a adresă URL completă este o adresă URL relativă corectă. Dacă doriți să să utilizați adrese URL relative pe forum, utilizați simbolul LOCAL_URL.',
		'COLOR'			=> 'O culoare HTML, poate fi in forma numerică <samp>#FF1234</samp> sau o <a href="http://www.w3.org/TR/CSS21/syndata.html#value-def-color">cuvânt cheie pentru culoare CSS</a> la fel ca <samp>fucşie</samp>  sau <samp>ChenarInactiv</samp>'
	)
));

// Smilies and topic icons
$lang = array_merge($lang, array(
	'ACP_ICONS_EXPLAIN'		=> 'Din această pagină puteţi adăuga, şterge sau modifica iconiţele utilizatorilor pe care le pot adăuga subiectelor sau mesajelor proprii. Aceste iconiţe sunt în general afişate lângă titlurile subiectelor când se afişează forumul sau lângă titlul mesajului când se afişează subiectul. De asemenea, puteţi instala şi crea un nou pachet pentru iconiţe.',
	'ACP_SMILIES_EXPLAIN'	=> 'Zâmbetele şi iconiţele emotive sunt de obicei mici, uneori imaginile animate sunt folosite pentru a exprima o emoţie sau un sentiment. Din această pagină puteţi adăuga, şterge şi modifica iconiţele emotive pe care utilizatorii le pot folosi în replicile sau mesajele lor private. De asemenea, puteţi instala şi crea un nou pachet pentru zâmbete.',
	'ADD_SMILIES'			=> 'Adaugă mai multe zâmbete',
	'ADD_SMILEY_CODE'		=> 'Adaugă cod de zâmbet adiţional',
	'ADD_ICONS'				=> 'Adaugă mai multe iconiţe',
	'AFTER_ICONS'			=> 'După %s',
	'AFTER_SMILIES'			=> 'După %s',

	'CODE'						=> 'Cod',
	'CURRENT_ICONS'				=> 'Iconiţe curente',
	'CURRENT_ICONS_EXPLAIN'		=> 'Alegeţi ce să faceţi cu iconiţele instalate',
	'CURRENT_SMILIES'			=> 'Zâmbete curente',
	'CURRENT_SMILIES_EXPLAIN'	=> 'Alegeţi ce să faceţi cu zâmbetele instalate',

	'DISPLAY_ON_POSTING'		=> 'Afişează în pagina cu mesaje',
	'DISPLAY_POSTING'			=> 'Pe pagina de răspuns',
	'DISPLAY_POSTING_NO'		=> 'Nu pe pagina de răspuns',
	
	

	'EDIT_ICONS'				=> 'Modificare iconiţe',
	'EDIT_SMILIES'				=> 'Modificare zâmbete',
	'EMOTION'					=> 'Emoţie',
	'EXPORT_ICONS'				=> 'Exportă şi descarcă icons.pak',
	'EXPORT_ICONS_EXPLAIN'		=> '%sAccesând acest link, configuraţia pentru iconiţele instalate va fi adăugată în fişierul <samp>icons.pak</samp> care odată descărcat poate fi folosit pentru a crea un fişier de tip <samp>.zip</samp> or <samp>.tgz</samp> conţinând toate iconiţele proprii plus acest fişier de configurare <samp>icons.pak</samp>%s.',
	'EXPORT_SMILIES'			=> 'Exportă şi descarcă smilies.pak',
	'EXPORT_SMILIES_EXPLAIN'	=> '%sAccesând acest link, configuraţia pentru zâmbetele instalate va fi adăugată în fişierul <samp>smilies.pak</samp> care odată descărcat poate fi folosit pentru a crea un fişier de tip <samp>.zip</samp> or <samp>.tgz</samp> conţinând toate zâmbetele proprii plus acest fişier de configurare <samp>smilies.pak</samp>%s.',

	'FIRST'			=> 'Primul',

	'ICONS_ADD'				=> 'Adaugă o iconiţă',
	'ICONS_NONE_ADDED'		=> 'Nicio iconiţă nu a fost adăugată.',
	'ICONS_ONE_ADDED'		=> 'Iconiţa a fost adăugată cu succes.',
	'ICONS_ADDED'			=> 'Iconiţele au fost adăugate cu succes.',
	'ICONS_CONFIG'			=> 'Configurăre iconiţă',
	'ICONS_DELETED'			=> 'Iconiţa a fost ştearsă cu succes.',
	'ICONS_EDIT'			=> 'Modifică iconiţă',
	'ICONS_ONE_EDITED'		=> 'Iconiţa a fost actualizată cu succes.',
	'ICONS_NONE_EDITED'		=> 'Nicio iconiţă nu a fost actualizată.',
	'ICONS_EDITED'			=> 'Iconiţele au fost actualizate cu succes.',
	'ICONS_HEIGHT'			=> 'Înălţime iconiţă',
	'ICONS_IMAGE'			=> 'Imagine iconiţă',
	'ICONS_IMPORTED'		=> 'Pachetul de iconiţe a fost instalat cu succes.',
	'ICONS_IMPORT_SUCCESS'	=> 'Pachetul de iconiţe a fost importat cu succes.',
	'ICONS_LOCATION'		=> 'Locaţie iconiţă',
	'ICONS_NOT_DISPLAYED'	=> 'Următoarele iconiţe nu sunt afişate în pagina de răspuns',
	'ICONS_ORDER'			=> 'Ordine iconiţe',
	'ICONS_URL'				=> 'Fişier imagine iconiţă',
	'ICONS_WIDTH'			=> 'Lăţime iconiţă',
	'IMPORT_ICONS'			=> 'Instalare pachet iconiţe',
	'IMPORT_SMILIES'		=> 'Instalare pachet zâmbete',

	'KEEP_ALL'			=> 'Păstrează tot',

	'MASS_ADD_SMILIES'	=> 'Adaugă mai multe zâmbete',

	'NO_ICONS_ADD'		=> 'Nu sunt iconiţe disponibile pentru adăugare.',
	'NO_ICONS_EDIT'		=> 'Nu sunt iconiţe disponibile pentru modificare.',
	'NO_ICONS_EXPORT'	=> 'Nu aveţi iconiţe cu care să creaţi un pachet.',
	'NO_ICONS_PAK'		=> 'Nu a fost găsit niciun pachet de iconiţe.',
	'NO_SMILIES_ADD'	=> 'Nu sunt zâmbete disponibile pentru adăugare.',
	'NO_SMILIES_EDIT'	=> 'Nu sunt zâmbete disponibile pentru modificare.',
	'NO_SMILIES_EXPORT'	=> 'Nu aveţi zâmbete cu care să creaţi un pachet.',
	'NO_SMILIES_PAK'	=> 'Nu a fost găsit niciun pachet de zâmbete.',

	'PAK_FILE_NOT_READABLE'		=> 'Nu s-a putut citi fişierul <samp>.pak</samp>.',

	'REPLACE_MATCHES'	=> 'Înlocuieşte potrivirile',

	'SELECT_PACKAGE'			=> 'Selectează un fişier pachet',
	'SMILIES_ADD'				=> 'Adaugă un zâmbet nou',
	'SMILIES_NONE_ADDED'		=> 'Nu a fost adăugat niciun zâmbet.',
	'SMILIES_ONE_ADDED'			=> 'Zâmbetul a fost adăugat cu succes.',
	'SMILIES_ADDED'				=> 'Zâmbetele au fost adăugate cu succes.',
	'SMILIES_CODE'				=> 'Cod zâmbet',
	'SMILIES_CONFIG'			=> 'Configuraţie zâmbet',
	'SMILIES_DELETED'			=> 'Zâmbetul a fost şters cu succes.',
	'SMILIES_EDIT'				=> 'Modifică zâmbet',
	'SMILIE_NO_CODE'			=> 'Zâmbetul „%s” a fost ignorat pentru că niciun cod nu a fost specificat.',
	'SMILIE_NO_EMOTION'			=> 'Zâmbetul „%s” a fost ignorat pentru că nicio emoţie nu a fost specificată.',
	'SMILIE_NO_FILE'			=> 'Zâmbetul „%s” a fost ignorat pentru că lipsește fișierul.',
	'SMILIES_NONE_EDITED'		=> 'Nu a fost actualizat niciun zâmbet.',
	'SMILIES_ONE_EDITED'		=> 'Zâmbetul a fost actualizat cu succes.',
	'SMILIES_EDITED'			=> 'Zâmbetul a fost updatat cu succes.',
	'SMILIES_EMOTION'			=> 'Emoţie',
	'SMILIES_HEIGHT'			=> 'Înălţime zâmbet',
	'SMILIES_IMAGE'				=> 'Imagine zâmbet',
	'SMILIES_IMPORTED'			=> 'Pachetul de zâmbete a fost instalat cu succes.',
	'SMILIES_IMPORT_SUCCESS'	=> 'Pachetul de zâmbete a fost importat cu succes.',
	'SMILIES_LOCATION'			=> 'Locaţie zâmbet',
	'SMILIES_NOT_DISPLAYED'		=> 'Următoarele zâmbete nu sunt afişate în pagina de răspuns',
	'SMILIES_ORDER'				=> 'Ordine zâmbete',
	'SMILIES_URL'				=> 'Fişier imagine zâmbet',
	'SMILIES_WIDTH'				=> 'Lăţime zâmbet',
	'TOO_MANY_SMILIES'			=> 'Limita de %d zâmbete atinsă.',

	'WRONG_PAK_TYPE'	=> 'Pachetul specificat nu conţine datele corespunzătoare.',
));

// Word censors
$lang = array_merge($lang, array(
	'ACP_WORDS_EXPLAIN'		=> 'În acest panou de control puteţi adăuga, modifica şi elimina cuvintele care vor fi cenzurate automat în forum. Utilizatorilor li se va permite în continuare să-şi înregistreze numele folosind aceste cuvinte. Wildcardurile (*) sunt acceptate în câmpul de cuvinte, de exemplu *test* va fi găsit detestabil, test* va fi găsit testare, *test va fi găsit detestat.',
	
	'ADD_WORD'				=> 'Adaugă un cuvânt nou',

	'EDIT_WORD'		=> 'Modificare cuvânt cenzurat',
	'ENTER_WORD'	=> 'Trebuie să specificaţi un cuvânt şi înlocuitorul acestuia.',

	'NO_WORD'	=> 'Niciun cuvânt nu a fost selectat pentru modificare.',

	'REPLACEMENT'	=> 'Înlocuire',

	'UPDATE_WORD'	=> 'Actualizare cuvânt cenzurat',

	'WORD'				=> 'Cuvânt',
	'WORD_ADDED'		=> 'Cuvântul cenzurat a fost adăugat cu succes.',
	'WORD_REMOVED'		=> 'Cuvântul cenzurat selectat a fost şters cu succes.',
	'WORD_UPDATED'		=> 'Cuvântul cenzurat selectat a fost actualizat cu succes.',
));

// Ranks
$lang = array_merge($lang, array(
	'ACP_RANKS_EXPLAIN'		=> 'Utilizând aceast formular puteţi adăuga, modifica, vizualiza şi şterge ranguri. De asemenea, puteţi crea ranguri speciale care pot fi aplicate unui utilizator prin secţiunea Panoul utilizatorului.',
	'ADD_RANK'				=> 'Adaugă un rang nou',

	'MUST_SELECT_RANK'		=> 'Trebuie să selectaţi un rang.',
	
	'NO_ASSIGNED_RANK'		=> 'Nu a fost atribuit niciun rang special.',
	'NO_RANK_TITLE'			=> 'Nu aţi specificat un titlu pentru rang.',
	'NO_UPDATE_RANKS'		=> 'Rangul a fost şters cu succes. Oricum conturile utilizatorilor ce folosesc acest rang nu au fost actualizate.  Va trebui să resetaţi manual rangul pentru aceste conturi.',

	'RANK_ADDED'			=> 'Rangul a fost adăugat cu succes.',
	'RANK_IMAGE'			=> 'Imagine rang',
	'RANK_IMAGE_EXPLAIN'	=> 'Foloseşte la a defini o imagine mică asociată cu rangul. Calea este relativă către directorul rădăcină al forumului phpBB.',
	'RANK_IMAGE_IN_USE'		=> '(În folosire)',
	'RANK_MINIMUM'			=> 'Număr minim de mesaje',
	'RANK_REMOVED'			=> 'Rangul a fost şters cu succes.',
	'RANK_SPECIAL'			=> 'Setează ca rang special',
	'RANK_TITLE'			=> 'Titlu rang',
	'RANK_UPDATED'			=> 'Rangul a fost actualizat cu succes.',
));

// Disallow Usernames
$lang = array_merge($lang, array(
	'ACP_DISALLOW_EXPLAIN'	=> 'Aici puteţi controla numele de utilizatori ce nu pot fi folosite. Numele dezactivate ale utilizatorilor pot conţine un wildcard pentru *.',
	'ADD_DISALLOW_EXPLAIN'	=> 'Puteţi dezactiva un nume utilizator folosind caracterul wildcard * pentru a se potrivi cu orice caracter',
	'ADD_DISALLOW_TITLE'	=> 'Adaugă un nume utilizator dezactivat',

	'DELETE_DISALLOW_EXPLAIN'	=> 'Puteţi dezactiva un nume utilizator selectând numele din listă şi accesând Trimite.',
	'DELETE_DISALLOW_TITLE'		=> 'Şterge un nume utilizator dezactivat',
	'DISALLOWED_ALREADY'		=> 'Numele specificat este deja dezactivat.',
	'DISALLOWED_DELETED'		=> 'Numele utilizatorului dezactivat a fost şters cu succes.',
	'DISALLOW_SUCCESSFUL'		=> 'Numele utilizatorului dezactivat a fost adăugat cu succes.',

	'NO_DISALLOWED'				=> 'Niciun nume utilizator nu este dezactivat',
	'NO_USERNAME_SPECIFIED'		=> 'Nu aţi selectat sau introdus un nume utilizator.',
));

// Reasons
$lang = array_merge($lang, array(
	'ACP_REASONS_EXPLAIN'	=> 'Aici puteţi administra motivele folosite în raportări mesaje şi contestări când se dezaprobă mesajele. Exista un motiv iniţial (marcat cu *) pe care nu puteţi să îl ştergeţi, acesta fiind folosit normal pentru mesajele private dacă niciun motiv nu se potriveşte.',
	'ADD_NEW_REASON'		=> 'Adaugă motiv nou',
	'AVAILABLE_TITLES'		=> 'Titlurile motivelor localizate disponibile',
	
	'IS_NOT_TRANSLATED'			=> 'Motivul <strong>nu</strong> a fost localizat.',
	'IS_NOT_TRANSLATED_EXPLAIN'	=> 'Motivul <strong>nu</strong> a fost localizat. Dacă vreţi să specificaţi varianta localizată, specificaţi cheia corectă din fişierele de limbă, secţiunea motivelor raportărilor.',
	'IS_TRANSLATED'				=> 'Motivul a fost localizat.',
	'IS_TRANSLATED_EXPLAIN'		=> 'Motivul a fost localizat. Dacă titlul specificat există şi în fişierele de limbă, secţiunea motivelor raportărilor, vor fi folosite forma localizată a titlului şi descrierea acestuia.',
	
	'NO_REASON'					=> 'Motivul nu a putut fi găsit.',
	'NO_REASON_INFO'			=> 'Trebuie să specificaţi un titlu şi o descriere pentru acest motiv.',
	'NO_REMOVE_DEFAULT_REASON'	=> 'Nu puteţi şterge motivul iniţial „Altele”.',

	'REASON_ADD'				=> 'Adaugă motive raportări/constestări',
	'REASON_ADDED'				=> 'Motivul raportării/contestării a fost adăugat cu succes.',
	'REASON_ALREADY_EXIST'		=> 'Un motiv cu acest titlu există deja, specificaţi un alt titlu pentru acest motiv.',
	'REASON_DESCRIPTION'		=> 'Descriere motiv',
	'REASON_DESC_TRANSLATED'	=> 'Descrierea motivului afişată',
	'REASON_EDIT'				=> 'Modifică motiv de raportare/contestare',
	'REASON_EDIT_EXPLAIN'		=> 'Aici puteţi adăuga sau modifica un motiv. Dacă motivul este tradus, versiunea localizată este folosită în locul descrierii specificate aici.',
	'REASON_REMOVED'			=> 'Motivul de Raportare/Contestare a fost şters cu succes.',
	'REASON_TITLE'				=> 'Titlu motiv',
	'REASON_TITLE_TRANSLATED'	=> 'Titlu motiv afişat',
	'REASON_UPDATED'			=> 'Motivul de Raportare/Contestare a fost actualizat cu succes.',

	'USED_IN_REPORTS'		=> 'Folosit în rapoarte',
));

?>