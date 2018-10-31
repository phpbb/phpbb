<?php
/**
*
* acp_ban [Română]
*
* @package language
* @version $Id: ban.php,v 1.19 2007/10/04 15:07:24 acydburn Exp $
* @translate $Id: ban.php,v 1.19 2007/12/29 17:05:00 www.phpbb.ro (shara21jonny) Exp $
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

// Banning
$lang = array_merge($lang, array(
	'1_HOUR'		=> '1 oră',
	'30_MINS'		=> '30 minute',
	'6_HOURS'		=> '6 ore',

	'ACP_BAN_EXPLAIN'	=> 'Aici puteţi controla banarea utilizatorilor după nume, IP sau adresă de e-mail. Aceste metode nu permit unui utilizator să vizualizeze vreo secţiune a forumului. Dacă doriţi, puteţi să furnizaţi un motiv scurt (maxim 3000 caractere) pentru ban. Acesta va fi afişat în fişierul de log al administratorului. De asemenea, se poate specifica şi durata banului. Dacă vreţi ca banarea să expire la o anumită dată, decât să specificaţi perioada de banare mai bine completaţi data limită <span style="text-decoration: underline;">Până -&gt;</span> pentru banare în formatul <kbd>YYYY-MM-DD</kbd>.',

	'BAN_EXCLUDE'			=> 'Exclude din banare',
	'BAN_LENGTH'			=> 'Durata banării',
	'BAN_REASON'			=> 'Motivul banării',
	'BAN_GIVE_REASON'		=> 'Motivul afişat utilizatorului banat',
	'BAN_UPDATE_SUCCESSFUL'	=> 'Lista de banare a fost actualizată cu succes.',
	'BANNED_UNTIL_DATE'		=> 'până la %s', // Examplu: "până la Mon 13.Jul.2009, 14:44"
	'BANNED_UNTIL_DURATION'	=> '%1$s (până la %2$s)', // Example: "7 days (până la Tue 14.Jul.2009, 14:44)"

	'EMAIL_BAN'					=> 'Banaţi una sau mai multe adrese de e-mail',
	'EMAIL_BAN_EXCLUDE_EXPLAIN'	=> 'Selectaţi pentru a exclude adresele de e-mail specificate din lista curentă de banare.',
	'EMAIL_BAN_EXPLAIN'			=> 'Pentru a specifica mai mult de o adresă de e-mail, introduceţi fiecare adresă pe o singură linie. Pentru a potrivi adresele parţiale, folosiţi  * ca un şi wildcard, de exemplu <samp>*@hotmail.com</samp>, <samp>*@*.domain.tld</samp>, etc.',
	'EMAIL_NO_BANNED'			=> 'Nicio adresă de e-mail nu există în lista de banare',
	'EMAIL_UNBAN'				=> 'Debanează sau exclude adrese de e-mail',
	'EMAIL_UNBAN_EXPLAIN'		=> 'Puteţi debana (sau exclude) mai multe adrese de e-mail folosind cea mai apropiată combinaţie a mouse-ului şi a tastaturii pentru calculatorul şi browser-ul propriu. Adresele de e-mail excluse au fundalul marcat.',

	'IP_BAN'					=> 'Banaţi unul sau mai multe IP-uri',
	'IP_BAN_EXCLUDE_EXPLAIN'	=> 'Selectaţi pentru a exclude IP-ul specificat din toate banările curente.',
	'IP_BAN_EXPLAIN'			=> 'Pentru a specifica mai multe IP-uri sau hosturi, introduceţi fiecare adresă pe un rând nou. Pentru a specifica clasa unei adrese IP, separaţi începutul şi sfârşitul cu liniuţă (-), pentru a specifica un wildcard folosiţi*',
	'IP_HOSTNAME'				=> 'Adrese IP sau hosturi',
	'IP_NO_BANNED'				=> 'Nicio adresă IP banată',
	'IP_UNBAN'					=> 'IP-uri debanate sau incluse',
	'IP_UNBAN_EXPLAIN'			=> 'Puteţi debana (sau include) mai multe adrese IP printr-o singură mişcare folosind cea mai apropiată combinaţie a mouse-ului şi a tastaturii calculatorului şi browser-ului propriu. IP-urile excluse au fundalul marcat.',

	'LENGTH_BAN_INVALID'		=> 'Data a trebuit să fie fie formatată <kbd>YYYY-MM-DD</kbd>.',

	'OPTIONS_BANNED'			=> 'Interzis',
	'OPTIONS_EXCLUDED'			=> 'Exclus',
	
	'PERMANENT'		=> 'Permanent',
	
	'UNTIL'						=> 'Până',
	'USER_BAN'					=> 'Banaţi unul sau mai mulţi utilizatori',
	'USER_BAN_EXCLUDE_EXPLAIN'	=> 'Selectaţi pentru a exclude utilizatorii specificaţi din toate banările curente.',
	'USER_BAN_EXPLAIN'			=> 'Puteţi bana mai mulţi utilizatori deodată introducând fiecare nume pe un rând. Folosiţi funcția <span style="text-decoration: underline;">Caută un membru</span> pentru a găsi şi adăuga automat unul sau mai mulţi utilizatori.',
	'USER_NO_BANNED'			=> 'Niciun utilizator banat',
	'USER_UNBAN'				=> 'Eliminați banarea sau excluderea folosind numele de utilizator',
	'USER_UNBAN_EXPLAIN'		=> 'Puteţi elimina banarea (sau excluderea) mai multor utilizatori printr-o singură mişcare folosind cea mai apropiată combinaţie a mouse-ului şi a tastaturii calculatorului şi browser-ului propriu. Utilizatorii excluşi au fundalul marcat.',
	

));

?>