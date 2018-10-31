<?php
/** 
*
* groups [Română]
*
* @package language
* @version $Id: groups.php 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: groups.php, 8479 2008-05-19 22:26:00 www.phpbb.ro (Aliniuz) Exp $
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
	'ALREADY_DEFAULT_GROUP'	=> 'Grupul selectat este deja grupul dumneavoastră predefinit.',
	'ALREADY_IN_GROUP'		=> 'Sunteţi deja membru al grupului selectat.',
	'ALREADY_IN_GROUP_PENDING'	=> 'Aţi făcut deja cerere de înregistrare pentru grupul selectat.',

	'CANNOT_JOIN_GROUP'			=> 'Nu puteţi să aderaţi la acest grup. Puteţi să aderaţi doar la grupurile deschise şi liber deschise.',
	'CANNOT_RESIGN_GROUP'		=> 'Nu puteţi să vă retrageţi din acest grup. Puteţi să vă retrageţi doar din grupurile deschise şi liber deschise.',
	'CHANGED_DEFAULT_GROUP'	=> 'Grupul predefinit a fost actualizat cu succes.',
	
	'GROUP_AVATAR'						=> 'Avatarul grupului', 
	'GROUP_CHANGE_DEFAULT'				=> 'Sunteţi sigur că vreţi să vă schimbaţi grupul predefinit pentru grupul „%s”?',
	'GROUP_CLOSED'						=> 'Închis',
	'GROUP_DESC'						=> 'Descrierea grupului',
	'GROUP_HIDDEN'						=> 'Ascuns',
	'GROUP_INFORMATION'					=> 'Informaţia grupului', 
	'GROUP_IS_CLOSED'					=> 'Acesta este un grup închis, noii membri pot să adere doar la invitaţia liderului grupului.',
	'GROUP_IS_FREE'						=> 'Acesta este un grup deschis liber, orice membru nou e binevenit.', 
	'GROUP_IS_HIDDEN'					=> 'Acesta este un grup ascuns, numai membrii acestui grup pot vedea ceilalţi membri.',
	'GROUP_IS_OPEN'						=> 'Acesta este un grup deschis, orice membru poate adera.',
	'GROUP_IS_SPECIAL'					=> 'Acesta este un grup special, grupurile speciale sunt conduse de administratori.', 
	'GROUP_JOIN'						=> 'Alătură-te grupului',
	'GROUP_JOIN_CONFIRM'				=> 'Eşti sigur că vrei să aderi la grupul selectat?',
	'GROUP_JOIN_PENDING'				=> 'Cerere de aderare la grup',
	'GROUP_JOIN_PENDING_CONFIRM'		=> 'Sunteţi sigur că vreţi să aderaţi la grupul selectat?',
	'GROUP_JOINED'						=> 'V-aţi alăturat cu succes acestui grup.',
	'GROUP_JOINED_PENDING'				=> 'Cerere de aderare la grup efectuată cu succes. Aşteptaţi ca liderul grupului să vă aprobe cererea.',
	'GROUP_LIST'						=> 'Administrare utilizatori',
	'GROUP_MEMBERS'						=> 'Membrii grupului',
	'GROUP_NAME'						=> 'Numele grupului',
	'GROUP_OPEN'						=> 'Deschis',
	'GROUP_RANK'						=> 'Rangul grupului', 
	'GROUP_RESIGN_MEMBERSHIP'			=> 'Părăseşte acest grup',
	'GROUP_RESIGN_MEMBERSHIP_CONFIRM'	=> 'Sunteţi sigur că doriţi să părăsiţi grupul selectat?',
	'GROUP_RESIGN_PENDING'				=> 'Părăsire grup în aşteptare la care aţi aderat',
	'GROUP_RESIGN_PENDING_CONFIRM'		=> 'Sunteţi sigur că vreţi să vă retrageţi aderarea din grupul selectat?',
	'GROUP_RESIGNED_MEMBERSHIP'			=> 'Aţi fost şters cu succes din grupul selectat.',
	'GROUP_RESIGNED_PENDING'			=> 'Aderarea dumneavoastră la grupul selectat a fost înlăturată cu succes.',
	'GROUP_TYPE'						=> 'Tipul grupului',
	'GROUP_UNDISCLOSED'					=> 'Grup ascuns',
	'FORUM_UNDISCLOSED'					=> 'Moderare forumuri ascunse',

	'LOGIN_EXPLAIN_GROUP'	=> 'Trebuie să vă autentificaţi pentru a vizualiza detaliile grupului.',

	'NO_LEADERS'					=> 'Nu sunteţi lider la niciun grup.',
	'NOT_LEADER_OF_GROUP'			=> 'Acţiunea cerută nu a putut fi finalizată întrucât nu sunteţi liderul grupului selectat.',
	'NOT_MEMBER_OF_GROUP'			=> 'Acţiunea cerută nu a putut fi finalizată întrucât nu sunteţi membru al grupului selectat sau apartenenţa dumneavoastră la grup nu a fost aprobată.',
	'NOT_RESIGN_FROM_DEFAULT_GROUP'	=> 'Nu puteţi să părăsiţi grupul dumneavoastră predefinit.',
	
	'PRIMARY_GROUP'		=> 'Grup primar',

	'REMOVE_SELECTED'		=> 'Şterge selecţiile',

	'USER_GROUP_CHANGE'			=> 'De la grupul „%1$s” la „%2$s”',
	'USER_GROUP_DEMOTE'			=> 'Retrogradează liderul grupului',
	'USER_GROUP_DEMOTE_CONFIRM'	=> 'Sunteţi sigur că vreţi să retrogradaţi liderul grupului selectat?',
	'USER_GROUP_DEMOTED'		=> 'Aţi retrogradat cu succes liderul grupului.',
));

?>