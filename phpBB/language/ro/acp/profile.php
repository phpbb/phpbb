<?php
/**
*
* acp_profile [Română]
*
* @package language
* @version $Id: profile.php,v 1.26 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: profile.php,v 1.26 2008/01/13 17:05:00 www.phpbb.ro (shara21jonny) Exp $
* @copyright (c) 2005 phpBB Group
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

// Custom profile fields
$lang = array_merge($lang, array(
    'ADDED_PROFILE_FIELD'    => 'Câmp profil particularizat adăugat cu succes.',
    'ALPHA_ONLY'            => 'Numai alfanumeric',
    'ALPHA_SPACERS'            => 'Alfanumeric şi spaţii',
    'ALWAYS_TODAY'            => 'Întotdeauna data curentă',

    'BOOL_ENTRIES_EXPLAIN'    => 'Introduceţi acum opţiunile dumneavoastră',
    'BOOL_TYPE_EXPLAIN'        => 'Definiţi tipul, fie o căsuţă de marcaj fie butoane radio. O căsuţă de marcaj va fi afişată doar dacă a fost selectată pentru un utulizator dat. În acest caz va fi folosită a <strong>doua</strong> variantă de limbă. Butoanele radio vor fi afişate independent de valoarea lor.',

    'CHANGED_PROFILE_FIELD'        => 'Câmp profil schimbat cu succes.',
    'CHARS_ANY'                    => 'Orice caracter',
    'CHECKBOX'                    => 'Căsuţă marcaj',
    'COLUMNS'                    => 'Coloane',
    'CP_LANG_DEFAULT_VALUE'        => 'Valoare iniţială',
    'CP_LANG_EXPLAIN'            => 'Descriere câmp',
    'CP_LANG_EXPLAIN_EXPLAIN'    => 'Explicaţia acestui câmp prezentat utilizatorului',
    'CP_LANG_NAME'                => 'Nume/titlu câmp prezentat utilizatorului',
    'CP_LANG_OPTIONS'            => 'Opţiuni',
    'CREATE_NEW_FIELD'            => 'Crează un câmp nou',
    'CUSTOM_FIELDS_NOT_TRANSLATED'    => 'Cel puţin un câmp profil particularizat nu a fost încă tradus. Introduceţi informaţiile necesare folosind legătura &quot;Traduce&quot; .',

    'DEFAULT_ISO_LANGUAGE'            => 'Limba standard [%s]',
    'DEFAULT_LANGUAGE_NOT_FILLED'    => 'Variantele de limbă pentru limba standard nu sunt completate în acest câmp de profil.',
    'DEFAULT_VALUE'                    => 'Valoare iniţială',
    'DELETE_PROFILE_FIELD'            => 'Elimină câmp profil',
    'DELETE_PROFILE_FIELD_CONFIRM'    => 'Sunteţi sigur că vreţi să ştergeţi acest câmp de profil?',
    'DISPLAY_AT_PROFILE'            => 'Afişează in panoul de control al utilizatorului',
    'DISPLAY_AT_PROFILE_EXPLAIN'    => 'Utilizatorul este capabil să schimbe acest câmp de profil din panoul de control al utilizatorului.',
    'DISPLAY_AT_REGISTER'            => 'Afişează în fereastra de înregistrare',
    'DISPLAY_AT_REGISTER_EXPLAIN'    => 'Dacă această opţiune este activată, câmpul va fi afişat la înregistrare.',
    'DISPLAY_ON_VT'					=> 'Afişează în fereastra de vizualizare a subiectului',
	  'DISPLAY_ON_VT_EXPLAIN'			=> 'Dacă această opţiune este activată, câmpul va fi afişat în mini-profilul din pagina subiectului.',
    'DISPLAY_PROFILE_FIELD'            => 'Afişează public câmpul de profil',
    'DISPLAY_PROFILE_FIELD_EXPLAIN'    => 'Câmpul de profil va fi afişat în toate locurile permise în cadrul preferinţelor încărcate. Selectând „Nu” în acest câmp, câmpul va fi ascuns din paginile subiectului, profilurilor şi listei cu membri.',
    'DROPDOWN_ENTRIES_EXPLAIN'        => 'Introduceţi acum opţiunile dumneavoastră, fiecare opţiune pe o linie',

    'EDIT_DROPDOWN_LANG_EXPLAIN'    => 'Reţineţi că puteţi schimba textul opţiunilor şi de asemenea puteţi adăuga noi opţiuni la sfârşit. Nu este recomandat să adăugaţi noi opţiuni între cele existente - această operaţie poate avea rezultate eronate în opţiunile alocate utilizatorilor. De asemenea, aceast lucru se poate întâmpla dacă eliminaţi opţiunile din interior. Eliminarea opţiunilor de la sfârşit are drept consecinţă faptul că utilizatorii care au avut atribuită această opţiune vor reveni la cea iniţială.',
    'EMPTY_FIELD_IDENT'                => 'Câmp gol de identificare',
    'EMPTY_USER_FIELD_NAME'            => 'Specificaţi un nume/titlu de câmp',
    'ENTRIES'                        => 'Variante',
    'EVERYTHING_OK'                    => 'Totul este în regulă',

    'FIELD_BOOL'                => 'Boolean (Da/Nu)',
    'FIELD_DATE'                => 'Dată',
    'FIELD_DESCRIPTION'            => 'Descriere câmp',
    'FIELD_DESCRIPTION_EXPLAIN'    => 'Explicaţia acestui câmp prezentată utilizatorului',
    'FIELD_DROPDOWN'            => 'Căsuţă dropdown',
    'FIELD_IDENT'                => 'Identificare câmp',
    'FIELD_IDENT_ALREADY_EXIST'    => 'Numele câmpului ales există deja. Alege alt nume.',
    'FIELD_IDENT_EXPLAIN'        => 'Identificarea câmpului este un nume pentru a identifica câmpul de profil în baza de date şi şabloane.',
    'FIELD_INT'                    => 'Numere',
    'FIELD_LENGTH'                => 'Mărimea căsuţei de intrare',
    'FIELD_NOT_FOUND'            => 'Câmpul de profil nu a fost găsit.',
    'FIELD_STRING'                => 'Un singur câmp text',
    'FIELD_TEXT'                => 'Suprafaţă text',
    'FIELD_TYPE'                => 'Tip câmp',
    'FIELD_TYPE_EXPLAIN'        => 'Nu puteţi schimba mai târziu tipul câmpului.',
    'FIELD_VALIDATION'            => 'Validare câmp',
    'FIRST_OPTION'                => 'Prima opţiune',

    'HIDE_PROFILE_FIELD'            => 'Ascunde câmp profil',
    'HIDE_PROFILE_FIELD_EXPLAIN'    => 'Ascunde câmpul profil pentru toţi utilizatorii cu excepţia administratorilor şi moderatorilor care încă pot vedea acest câmp. Dacă opţiunea de afişare din Panoul utilizatorului este dezactivată atunci utilizatorul nu va putea să vadă sau să schimbe acest câmp şi câmpul poate fi modificat doar de către administratori.',
    
    'INVALID_CHARS_FIELD_IDENT'    => 'Câmpul de identificare poate conţine litere mici a-z şi _',
    'INVALID_FIELD_IDENT_LEN'    => 'Câmpul de identificare poate fi de maxim 17 caractere',
    'ISO_LANGUAGE'                => 'Limba [%s]',

    'LANG_SPECIFIC_OPTIONS'        => 'Opţiuni specifice limbii [<strong>%s</strong>]',

    'MAX_FIELD_CHARS'        => 'Număr maxim de caractere',
    'MAX_FIELD_NUMBER'        => 'Cel mai mare număr permis',
    'MIN_FIELD_CHARS'        => 'Număr minim de caractere',
    'MIN_FIELD_NUMBER'        => 'Cel mai mic număr permis',

    'NO_FIELD_ENTRIES'            => 'Nicio variantă nu a fost definită',
    'NO_FIELD_ID'                => 'Niciun identificator de câmp nu a fost specificat.',
    'NO_FIELD_TYPE'                => 'Niciun tip de câmp nu a fost specificat.',
    'NO_VALUE_OPTION'            => 'Opţiune egală cu valore nespecificată',
    'NO_VALUE_OPTION_EXPLAIN'    => 'Valoare pentru o variantă neacceptată. Dacă se impune pentru acest câmp, utilizatorul primeşte o eroare dacă a ales opţiunea selectată aici',
    'NUMBERS_ONLY'                => 'Numai numere (0-9)',

    'PROFILE_BASIC_OPTIONS'        => 'Opţiuni de bază',
    'PROFILE_FIELD_ACTIVATED'    => 'Câmp profil activat cu succes.',
    'PROFILE_FIELD_DEACTIVATED'    => 'Câmp profil dezactivat cu succes.',
    'PROFILE_LANG_OPTIONS'        => 'Opţiuni specifice limbajului',
    'PROFILE_TYPE_OPTIONS'        => 'Opţiuni specifice tipului de profil',

    'RADIO_BUTTONS'                => 'Butoane radio',
    'REMOVED_PROFILE_FIELD'        => 'Câmp de profil eliminat cu succes.',
    'REQUIRED_FIELD'            => 'Câmp necesar',
    'REQUIRED_FIELD_EXPLAIN'    => 'Forţează completarea sau specificarea câmpului de profil de către utilizator sau administrator. Dacă opţiunea de afişare din pagina de înregistrare este dezactivată atunci câmpul va fi obligatoriu doar când utilizatorul îşi modifică profilul.',
    'ROWS'                        => 'Rânduri',

    'SAVE'                            => 'Salvează',
    'SECOND_OPTION'                    => 'A doua opţiune',
	'SHOW_NOVALUE_FIELD'			=> 'Arată câmpul dacă nu a fost selectată nicio valuare',
	'SHOW_NOVALUE_FIELD_EXPLAIN'	=> 'Determină dacă câmpul profil trebuie afișat dacă nicio valuare nu a fost selectată pentru câmpurile opționale sau dacă nicio valuare nu a fost selectată încă pentru câmpurile obligatorii.',
    'STEP_1_EXPLAIN_CREATE'            => 'Aici puteţi specifica primii parametrii de bază pentru noul dumneavoastră câmp de profil. Această informaţie este necesare pentru al doilea pas unde veţi putea specifica opţiunile rămase şi modifica mai departe câmpul de profil.',
    'STEP_1_EXPLAIN_EDIT'            => 'Aici puteţi specifica parametrii de bază pentru câmpul dumneavoastră de profil. Opţiunile relevante sunt recalculate în al doilea pas.',
    'STEP_1_TITLE_CREATE'            => 'Adăugă câmp profil',
    'STEP_1_TITLE_EDIT'                => 'Modificare câmp profil',
    'STEP_2_EXPLAIN_CREATE'            => 'Aici puteţi defini câteva opţiuni comune pe care aţi dori să le modificaţi.',
    'STEP_2_EXPLAIN_EDIT'            => 'Aici puteţi schimba câteva opţiuni comune.<br /><strong>Reţineţi că schimbările în câmpurile de profil nu vor afecta valorile existente specificate de utilizatori în câmpurile de profil.</strong>',
    'STEP_2_TITLE_CREATE'            => 'Creare opţiuni specifice tipului de profil',
    'STEP_2_TITLE_EDIT'                => 'Modificare opţiuni specifice tipului de profil',
    'STEP_3_EXPLAIN_CREATE'            => 'Din moment ce aveţi mai mult de o limbă instalată în forum, va trebui să completaţi elementele celorlalte limbi. Câmpul de profil va folosi limba activată iniţial, puteţi să completaţi elementele celorlalte limbi mai târziu.',
    'STEP_3_EXPLAIN_EDIT'            => 'Din moment ce aveţi mai mult de o limbă instalată în forum, puteţi acum să modificaţi sau să adăugaţi elementele celorlalte limbi. Câmpul de profil va folosi limba activată iniţial.',
    'STEP_3_TITLE_CREATE'            => 'Definiţii limbă rămase',
    'STEP_3_TITLE_EDIT'                => 'Definiţii limbă',
    'STRING_DEFAULT_VALUE_EXPLAIN'    => 'Specificaţi o frază iniţială pentru a fi afişată, o valoare iniţială. Lăsaţi gol dacă vreţi ca la început să nu fie completat ceva.',

    'TEXT_DEFAULT_VALUE_EXPLAIN'    => 'Specificaţi un text iniţial pentru a fi afişat, o valoare iniţială. Lăsaţi gol dacă vreţi ca la început să nu fie completat ceva.',
    'TRANSLATE'                        => 'Traduce',

    'USER_FIELD_NAME'    => 'Nume/titlu câmp afişat utilizatorului',

    'VISIBILITY_OPTION'                => 'Opţiuni vizibilitate',
));

?>