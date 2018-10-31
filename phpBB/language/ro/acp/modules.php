<?php
/**
*
* acp_modules [Română]
*
* @package language
* @version $Id: modules.php,v 1.13 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: modules.php,v 1.13 2007/12/29 17:05:00 www.phpbb.ro (NemoXP) Exp $
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
    'ACP_MODULE_MANAGEMENT_EXPLAIN'    => 'Aici puteţi administra tot felul de module. Tineţi cont de faptul că Panoul administratorului conţine un meniu structurat pe 3 nivele ( Categorie -> Categorie -> Modul ) aşa cum celelalte au un meniu structurat pe 2 nivele ( Categorie -> Modul ) ce trebuie păstrat. De asemenea aveţi grijă că vă puteţi bloca dacă dezactivaţi sau ştergeţi modulele ce ţin de modulul pentru administrare.',
    'ADD_MODULE'                    => 'Adăugaţi modul',
    'ADD_MODULE_CONFIRM'            => 'Sunteţi sigur că vreţi să adăugaţi modulului selectat cu stilul specificat?',
    'ADD_MODULE_TITLE'                => 'Adăugă modul',

    'CANNOT_REMOVE_MODULE'    => 'Modulul nu poate fi şters deoarece are submodule asociate. Ştergeţi sau mutaţi toate submodulele asociate şi apoi ştergeţi acest modul',
    'CATEGORY'                => 'Categorie',
    'CHOOSE_MODE'            => 'Selectaţi stilul modulului',
    'CHOOSE_MODE_EXPLAIN'    => 'Selectaţi stilul modulelor deja folosite.',
    'CHOOSE_MODULE'            => 'Alegeţi modulul',
    'CHOOSE_MODULE_EXPLAIN'    => 'Selectaţi fişierul apelat de acest modul.',
    'CREATE_MODULE'            => 'Creaţi un modul nou',

    'DEACTIVATED_MODULE'    => 'Dezactivaţi modulul',
    'DELETE_MODULE'            => 'Ştergeţi modulul',
    'DELETE_MODULE_CONFIRM'    => 'Sunteţi sigur că vreţi să ştergeţi acest modul?',

    'EDIT_MODULE'            => 'Modificaţi modulul',
    'EDIT_MODULE_EXPLAIN'    => 'Aici puteţi modifica setările specifice modulului',

    'HIDDEN_MODULE'            => 'Modul ascunse',

    'MODULE'                    => 'Modul',
    'MODULE_ADDED'                => 'Modul adăugat cu succes.',
    'MODULE_DELETED'            => 'Modul şters cu succes.',
    'MODULE_DISPLAYED'            => 'Modul afişat',
    'MODULE_DISPLAYED_EXPLAIN'    => 'Dacă nu vreţi să afisaţi acest modul dar vreţi să-l folosiţi, specificaţi aici opţiunea Nu.',
    'MODULE_EDITED'                => 'Modul modificat cu succes.',
    'MODULE_ENABLED'            => 'Modul activat',
    'MODULE_LANGNAME'            => 'Numele limbii modulului',
    'MODULE_LANGNAME_EXPLAIN'    => 'Introduceţi numele modulului ce va fi afişat. Folosiţi o constantă de limbă dacă numele este preluat din fişierul de limbă.',
    'MODULE_TYPE'                => 'Tipul modulului',

    'NO_CATEGORY_TO_MODULE'    => 'Categoria nu a putut fi transformată în modul. Mutaţi/Ştergeţi toate submodulele înainte de a efectua această acţiune.',
    'NO_MODULE'                => 'Niciun modul găsit.',
    'NO_MODULE_ID'            => 'ID-ul modulului nu a fost specificat.',
    'NO_MODULE_LANGNAME'    => 'Limba modulului nu a fost specificată.',
    'NO_PARENT'                => 'Niciun modul principal găsit',

    'PARENT'                => 'Modul principal',
    'PARENT_NO_EXIST'        => 'Nu există modul principal.',

    'SELECT_MODULE'            => 'Selectaţi un modul',
));

?>