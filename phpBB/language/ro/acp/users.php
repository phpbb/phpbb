<?php
/**
*
* acp_users [Română]
*
* @package language
* @version $Id: users.php 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: users.php, 8479 2008-05-19 17:05:00 www.phpbb.ro (shara21jonny) Exp $
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
	'ADMIN_SIG_PREVIEW'		=> 'Previzualizare semnătură',
	'AT_LEAST_ONE_FOUNDER'	=> 'Nu sunteţi autorizat să schimbaţi acest fondator într-un utilizator normal. Trebuie să existe cel puţin un fondator activat pentru acest forum. Dacă vreţi să schimbaţi statul de fondator al acestui utilizator, promovaţi la acest statut un alt utilizator înainte de a efectua modificarea.',

	'BAN_ALREADY_ENTERED'	=> 'Restricţia a fost setată anterior cu succes. Lista cu interziceri nu a fost actualizată.',
	'BAN_SUCCESSFUL'		=> 'Restricţie adăugată cu succes.',
	'CANNOT_BAN_ANONYMOUS'         => 'Nu aveţi dreptul de a interzice contul anonim. Permisiunile pentru utilizatorii anonimi pot fi setate sub secţiunea Permisiuni.',


	'CANNOT_BAN_FOUNDER'			=> 'Nu aveţi permisiunea de a interzice conturile fondatorilor.',
	'CANNOT_BAN_YOURSELF'			=> 'Nu vă este permisă interzicerea propriului cont.',
	'CANNOT_DEACTIVATE_BOT'			=> 'Nu vă este permis să dezactivaţi conturile roboţilor. În schimb, vă rugăm să dezactivaţi roboţii din pagina roboţilor.',
	'CANNOT_DEACTIVATE_FOUNDER'		=> 'Nu vă este permis să dezactivaţi contul fondatorilor.',
	'CANNOT_DEACTIVATE_YOURSELF'	=> 'Nu vă este permis să dezactivaţi propriul cont.',
	'CANNOT_FORCE_REACT_BOT'		=> 'Nu vă este permis să forţaţi reactivarea conturilor boţilor. În schimb, vă rugăm să reactivaţi roboţii din pagina roboţilor.',
	'CANNOT_FORCE_REACT_FOUNDER'	=> 'Nu vă este permis să forţaţi reactivarea conturilor fondatorilor.',
	'CANNOT_FORCE_REACT_YOURSELF'	=> 'Nu vă este permis să forţaţi reactivarea propriul cont.',
	'CANNOT_REMOVE_ANONYMOUS'		=> 'Nu aveţi permisiunea să ştergeţi conturile vizitatorilor.',
	'CANNOT_REMOVE_FOUNDER'			=> 'Nu aveţi permisiunea să ştergeţi conturile fondator.',
	'CANNOT_REMOVE_YOURSELF'		=> 'Nu aveţi permisiunea să ştergeţi propriul cont de utilizator.',
	'CANNOT_SET_FOUNDER_IGNORED'	=> 'Nu puteţi să promovaţi utilizatorii ignoraţi ca şi fondatori.',
	'CANNOT_SET_FOUNDER_INACTIVE'	=> 'Trebuie să activaţi utilizatorii înainte să-i promovaţi ca fondatori, numai utilizatorilor activaţi le este permis să fie promovaţi.',
	'CONFIRM_EMAIL_EXPLAIN'			=> 'Trebuie specificat acest lucru dacă schimbaţi adresa de email a utilizatorului.',

	'DELETE_POSTS'			=> 'Şterge mesaje',
	'DELETE_USER'			=> 'Şterge utilizator',
	'DELETE_USER_EXPLAIN'	=> 'Reţineţi că ştergerea unui utilizator este definitivă, ei nu mai pot fi recuperaţi. Mesajele private necitite trimise de acest utilizator vor fi șterse și nu vor fi disponibile destinatarilor acestora.',

	'FORCE_REACTIVATION_SUCCESS'	=> 'Reactivare forţată efectuată cu succes.',
	'FOUNDER'						=> 'Fondator',
	'FOUNDER_EXPLAIN'				=> 'Fondatorii au toate permisiunile administratorilor şi nu pot fi niciodată interzişi, şterşi sau modificaţi de către alţi membrii ce nu sunt fondatori.',

	'GROUP_APPROVE'					=> 'Aprobare membru',
	'GROUP_DEFAULT'					=> 'Asociaza ca grup iniţial pentru membru',
	'GROUP_DELETE'					=> 'Elimină membrul din grup',
	'GROUP_DEMOTE'					=> 'Retrogradează liderul de grup',
	'GROUP_PROMOTE'					=> 'Promovează ca lider de grup',

	'IP_WHOIS_FOR'			=> 'Identifică IP for %s',

	'LAST_ACTIVE'			=> 'Ultima dată activ',

	'MOVE_POSTS_EXPLAIN'	=> 'Selectaţi forumul în care doriţi să mutaţi toate mesajele scrise de către utilizatorul selectat.',

	'NO_SPECIAL_RANK'		=> 'Niciun rang special atribuit',
	'NO_WARNINGS'			=> 'Niciun avertisment.',
	'NOT_MANAGE_FOUNDER'	=> 'Aţi încercat să administraţi un utilizator cu statutul de fondator. Numai fondatorii sunt autorizaţi să administreze alţi fondatori.',

	'QUICK_TOOLS'			=> 'Unelte rapide',

	'REGISTERED'			=> 'Înregistrat',
	'REGISTERED_IP'			=> 'Înregistrat de la IP-ul',
	'RETAIN_POSTS'			=> 'Reţine mesaje',

	'SELECT_FORM'			=> 'Selectează formular',
	'SELECT_USER'			=> 'Selectează utilizator',

    'USER_ADMIN'                    => 'Administrare utilizatori',
    'USER_ADMIN_ACTIVATE'            => 'Activare cont',
    'USER_ADMIN_ACTIVATED'            => 'Utilizatori activaţi cu succes',
    'USER_ADMIN_AVATAR_REMOVED'        => 'Avatar eliminat cu succes din contul utilizatorului.',
    'USER_ADMIN_BAN_EMAIL'            => 'Banaţi după adresa de email',
    'USER_ADMIN_BAN_EMAIL_REASON'    => 'Adresă de email banată via managementul utilizatorilor',
    'USER_ADMIN_BAN_IP'                => 'Interzişi după IP',
    'USER_ADMIN_BAN_IP_REASON'        => 'IP interzis via managementul utilizatorilor',
    'USER_ADMIN_BAN_NAME_REASON'    => 'Nume utilizator interzis via managementul utilizatorilor',
    'USER_ADMIN_BAN_USER'            => 'Banaţi după nume utilizator',
    'USER_ADMIN_DEACTIVATE'            => 'Dezactivare cont',
    'USER_ADMIN_DEACTIVED'            => 'Utilizator dezactivat cu succes.',
    'USER_ADMIN_DEL_ATTACH'            => 'Şterge toate fişierele ataşate',
    'USER_ADMIN_DEL_AVATAR'            => 'Şterge avatar',
    'USER_ADMIN_DEL_OUTBOX'			=> 'Şterge dosarul cu mesaje expediate',
    'USER_ADMIN_DEL_POSTS'            => 'Şterge toate mesajele',
    'USER_ADMIN_DEL_SIG'            => 'Şterge semnătură',
    'USER_ADMIN_EXPLAIN'            => 'Aici puteţi schimba informaţiile utilizatorilor şi anumite opţiuni specifice.',
    'USER_ADMIN_FORCE'                => 'Forţează reactivarea',
    'USER_ADMIN_LEAVE_NR'			=> 'Şterge din grupul de utilizatori înregistraţi recent',
    'USER_ADMIN_MOVE_POSTS'            => 'Mută toate mesajele',
    'USER_ADMIN_SIG_REMOVED'        => 'Semnătură eliminata cu succes din contul utilizatorului.',
    'USER_ATTACHMENTS_REMOVED'        => 'Toate fişierele ataşate adăugate de către acest utilizator au fost eliminate cu succes.',
    'USER_AVATAR_NOT_ALLOWED'		=> 'Avatarul nu poate fi afişat deoarece avatarele au fost dezactivate.',
    'USER_AVATAR_UPDATED'            => 'Detaliile avatarului utilizatorului au fost actualizate cu succes.',
    'USER_AVATAR_TYPE_NOT_ALLOWED'	=> 'Avatarul curent nu poate fi afişat pentru că tipul de fişier a fost dezactivat.',
    'USER_CUSTOM_PROFILE_FIELDS'    => 'Câmpuri de profil particularizate',
    'USER_DELETED'                    => 'Utilizator şters cu succes.',
    'USER_GROUP_ADD'                => 'Adaugă utilizator la grup',
    'USER_GROUP_NORMAL'                => 'Utilizatorul normal al grupului este un membru al',
    'USER_GROUP_PENDING'            => 'Utilizatorii grupului sunt în modul de aşteptare',
    'USER_GROUP_SPECIAL'            => 'Utilizatorul special al grupului este un membru',
    'USER_LIFTED_NR'				=> 'Marcajul de utilizator recent înregistrat al utilizatorului curent a fost şters cu succes.',
    'USER_NO_ATTACHMENTS'            => 'Nu sunt fişiere ataşate pentru a fi afişate.',
	'USER_NO_POSTS_TO_DELETE'			=> 'Utilizatorul nu are mesaje pentru păstrare sau ștergere.',
    'USER_OUTBOX_EMPTIED'			=> 'Casuţa utilizatorului cu mesaje private expediate a fost curăţată cu succes.',
   	'USER_OUTBOX_EMPTY'				=> 'Casuţa utilizatorului cu mesaje private expediate este deja goală.',
    'USER_OVERVIEW_UPDATED'            => 'Detaliile utilizatorului actualizate.',
    'USER_POSTS_DELETED'            => 'Toate mesajele scrise de către acest utilizator au fost eliminate cu succes.',
    'USER_POSTS_MOVED'                => 'Mesajele utilizatorilot au fost mutate succes în forumul specificat.',
    'USER_PREFS_UPDATED'            => 'Preferinţele utilizatorului actualizate.',
    'USER_PROFILE'                    => 'Profil utilizator',
    'USER_PROFILE_UPDATED'            => 'Profil utilizator actualizat.',
    'USER_RANK'                        => 'Rang utilizator',
    'USER_RANK_UPDATED'                => 'Rang utilizator actualizat.',
    'USER_SIG_UPDATED'                => 'Semnătura utilizatorului a fost actualizată cu succes.',
    'USER_WARNING_LOG_DELETED'		=> 'Nicio informaţie disponibilă. Este posibil ca jurnalul cu înregistrări să fi fost şters',
    'USER_TOOLS'                    => 'Unelte de bază',
));

?>