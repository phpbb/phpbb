<?php
/**
*
* acp_language [Română]
*
* @package language
* @version $Id: language.php,v 1.16 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: language.php,v 1.16 2007/12/29 17:05:00 www.phpbb.ro (NemoXP) Exp $
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

$lang = array_merge($lang, array(
    'ACP_FILES'                        => 'Fişierele de limbă ale administratorului',
    'ACP_LANGUAGE_PACKS_EXPLAIN'    => 'Aici puteţi instala/dezinstala pachetele de limbă. Pachetul de limbă standard este marcat cu un asterix (*).',
    
    'EMAIL_FILES'                     => 'Şabloane pentru mesajele electronice',

    'FILE_CONTENTS'                => 'Conţinut fişiere',
    'FILE_FROM_STORAGE'            => 'Fişiere din directorul de stocare',

    'HELP_FILES'                => 'Fişiere de ajutor',

    'INSTALLED_LANGUAGE_PACKS'    => 'Pachete de limbă instalate',
    'INVALID_LANGUAGE_PACK'        => 'Pachetul de limbă selectate pare să nu fie vaild. Vă rugăm să verificaţi pachetul de limbă şi să îl încărcaţi din nou dacă este necesar.',
    'INVALID_UPLOAD_METHOD'        => 'Metoda selectată de încărcare nu este validă, vă rugăm să alegeţi o altă metodă.',

    'LANGUAGE_DETAILS_UPDATED'            => 'Detaliile de limbaj au fost actualizate cu succes.',
    'LANGUAGE_ENTRIES'                    => 'Variabile limbă',
    'LANGUAGE_ENTRIES_EXPLAIN'            => 'Aici puteţi schimba variabilele pachetelor de limbă existente sau a celor care încă nu au fost traduse.<br /><strong>Reţineţi:</strong> Odată ce aţi schimbat un fişer de limbă, schimbările vor fi stocate într-un director separat pentru a putea fi descărcat. Schimbările nu vor fi vizibile de către utilizatori până nu înlocuiţi fişierele originale de limbă din spaţiul web propriu (prin upload).',
    'LANGUAGE_FILES'                    => 'Fişiere de limbă',
    'LANGUAGE_KEY'                        => 'Cheie',
    'LANGUAGE_PACK_ALREADY_INSTALLED'    => 'Acest pachet de limbă este deja instalat.',
    'LANGUAGE_PACK_DELETED'                => 'Pachetul de limbă <strong>%s</strong> a fost eliminat cu succes. Toţi utilizatorii ce folosesc acest limbaj au fost resetaţi la fişierele standard de limbă ale forumului.',
    'LANGUAGE_PACK_DETAILS'                => 'Detalii pachet de limbă',
    'LANGUAGE_PACK_INSTALLED'            => 'Pachetul de limbă <strong>%s</strong> a fost instalat cu succes.',
	'LANGUAGE_PACK_CPF_UPDATE'			=> 'Textele de limbă pentru câmpurile de profil personalizate au fost copiate din limba standard. Vă rugăm să le modificați dacă este cazul.',
    'LANGUAGE_PACK_ISO'                    => 'ISO',
    'LANGUAGE_PACK_LOCALNAME'            => 'Nume local',
    'LANGUAGE_PACK_NAME'                => 'Nume',
    'LANGUAGE_PACK_NOT_EXIST'            => 'Pachetul de limbaj selectat nu există.',
    'LANGUAGE_PACK_USED_BY'                => 'Folosit de (incluzând roboţii)',
    'LANGUAGE_VARIABLE'                    => 'Variabilă',
    'LANG_AUTHOR'                        => 'Autorul Pachetului de limbă',
    'LANG_ENGLISH_NAME'                    => 'Nume în engleză',
    'LANG_ISO_CODE'                        => 'Cod ISO',
    'LANG_LOCAL_NAME'                    => 'Nume local',

    'MISSING_LANGUAGE_FILE'        => 'Lipseşte fişierul de limbă: <strong style="color:red">%s</strong>',
    'MISSING_LANG_VARIABLES'    => 'Lipsesc variabile de limbă',
    'MODS_FILES'                => 'MOD-ificările fişierelor de limbă',

    'NO_FILE_SELECTED'                => 'Nu aţi specificat un fişier de limbă.',
    'NO_LANG_ID'                    => 'Nu aţi specificat un pachet de limbă.',
    'NO_REMOVE_DEFAULT_LANG'        => 'Nu puteţi elimina pachetul de limbă iniţial.<br />Dacă vreţi să eliminaţi acest pachet de limbă, schimbaţi mai întâi limba standard folosită pe forum.',
    'NO_UNINSTALLED_LANGUAGE_PACKS'    => 'Niciun pachet de limbă dezinstalat',

    'REMOVE_FROM_STORAGE_FOLDER'        => 'Eliminaţi din directorul de stocare',

    'SELECT_DOWNLOAD_FORMAT'    => 'Selectaţi formatul descărcării',
    'SUBMIT_AND_DOWNLOAD'        => 'Trimiteţi şi descărcaţi fişierul',
    'SUBMIT_AND_UPLOAD'            => 'Trimiteţi şi încărcaţi fişierul',

    'THOSE_MISSING_LANG_FILES'            => 'Următoarele fişiere de limbă lispesc din directorul de limbă %s',
    'THOSE_MISSING_LANG_VARIABLES'        => 'Următoarele variabile de limbă lipsesc din pachetul de limbă <strong>%s</strong>',

    'UNINSTALLED_LANGUAGE_PACKS'    => 'Pachetele de limbă dezinstalate',

    'UNABLE_TO_WRITE_FILE'        => 'Fişierul nu a putut fi scris în %s.',
    'UPLOAD_COMPLETED'            => 'Încărcarea a fost efectuată cu succes.',
    'UPLOAD_FAILED'                => 'Încărcarea a eşuat din motive necunoscute. Va trebui să înlocuiţi manual fişierul aferent.',
    'UPLOAD_METHOD'                => 'Metoda de încărcare',
    'UPLOAD_SETTINGS'            => 'Setări de încărcare',

    'WRONG_LANGUAGE_FILE'        => 'Fişierul de limbă selectat nu este valid.',
));

?>