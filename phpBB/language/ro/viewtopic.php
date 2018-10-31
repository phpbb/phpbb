<?php
/** 
*
* viewtopic [Română]
*
* @package language
* @version $Id: viewtopic.php,v 1.20 2007/10/29 13:39:34 kellanved Exp $
* @translate $Id: viewtopic.php,v 1.20 2008/01/07 01:13:00 www.phpbb.ro (Aliniuz) Exp $
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
	'ATTACHMENT'						=> 'Fişiere ataşate',
	'ATTACHMENT_FUNCTIONALITY_DISABLED'	=> 'Facilitatea de fişiere ataşate a fost dezactivată.',

	'BOOKMARK_ADDED'		=> 'Subiectul a fost însemnat cu succes.',
	'BOOKMARK_ERR'         => 'Încercarea de însemnare a subiectului a eşuat. Vă rugăm să încercaţi din nou.',
	'BOOKMARK_REMOVED'		=> 'Subiectul a fost şters din lista semnelor de carte.',
	'BOOKMARK_TOPIC'		=> 'Însemnare subiect',
	'BOOKMARK_TOPIC_REMOVE'	=> 'Şterge subiectul din lista semnelor de carte',
	'BUMPED_BY'				=> 'Subiect ridicat ultima oară de %1$s %2$s.',
	'BUMP_TOPIC'			=> 'Ridică subiect',

	'CODE'					=> 'Cod',
	'COLLAPSE_QR'			=> 'Ascunde Răspuns rapid',

	'DELETE_TOPIC'			=> 'Şterge subiect',
	'DOWNLOAD_NOTICE'		=> 'Nu aveţi permisiunea de a vizualiza fişierele ataşate acestui mesaj.',

	'EDITED_TIMES_TOTAL'	=> 'Ultima oară modificat de %1$s pe %2$s, modificat de %3$d ori în total.',
	'EDITED_TIME_TOTAL'		=> 'Ultima oară modificat de %1$s pe %2$s, modificat %3$d dată în total.',
	'EMAIL_TOPIC'			=> 'Trimite prin e-mail unui prieten',
	'ERROR_NO_ATTACHMENT'	=> 'Fişierul ataşat selectat nu mai există.',

	'FILE_NOT_FOUND_404'	=> 'Fişierul <strong>%s</strong> nu există.',
	'FORK_TOPIC'			=> 'Copiază subiect',
	'FULL_EDITOR' => 'Editor complet',

	'LINKAGE_FORBIDDEN'		=> 'Nu sunteţi autorizat să vizualizaţi, descărcaţi sau să puneţi un link către/pe acest site.',
	'LOGIN_NOTIFY_TOPIC'	=> 'Aţi primit o notificare cu privire la acest subiect, autentificaţi-vă pentru a o vizualiza.',
	'LOGIN_VIEWTOPIC'		=> 'Administratorul forumului necesită ca dumneavoastră să fiţi înregistrat şi autentificat pentru a vizualiza acest subiect.',

	'MAKE_ANNOUNCE'				=> 'Schimbă în „Anunţ”',
	'MAKE_GLOBAL'				=> 'Schimbă în „Global”',
	'MAKE_NORMAL'				=> 'Schimbă în „Subiect obişnuit”',
	'MAKE_STICKY'				=> 'Schimbă în „Important”',
	'MAX_OPTIONS_SELECT'		=> 'Puteţi selecta până la <strong>%d</strong> opţiuni',
	'MAX_OPTION_SELECT'			=> 'Puteţi selecta <strong>1</strong> opţiune',
	'MISSING_INLINE_ATTACHMENT'	=> 'Fişierul ataşat <strong>%s</strong> nu mai este disponibil',
	'MOVE_TOPIC'				=> 'Mută subiect',

	'NO_ATTACHMENT_SELECTED'=> 'Nu aţi selectat un fişier ataşat pe care să-l descărcaţi sau vizualizaţi.',
	'NO_NEWER_TOPICS'		=> 'Nu sunt subiecte mai noi în acest forum.',
	'NO_OLDER_TOPICS'		=> 'Nu sunt subiecte mai vechi în acest forum.',
	'NO_UNREAD_POSTS'		=> 'Nu sunt mesaje necitite noi în acest subiect.',
	'NO_VOTE_OPTION'		=> 'Trebuie să alegeţi o opţiune când votaţi.',
	'NO_VOTES'				=> 'Niciun vot',

	'POLL_ENDED_AT'			=> 'Chestionarul s-a închis pe data de %s',
	'POLL_RUN_TILL'			=> 'Chestionarul este deschis până pe data de %s',
	'POLL_VOTED_OPTION'		=> 'Aţi votat pentru această opţiune',
	'PRINT_TOPIC'			=> 'Versiune printabilă',

	'QUICK_MOD'				=> 'Utilităţi rapide',
	'QUICKREPLY'			=> 'Răspuns rapid',
	'QUOTE'					=> 'Citat',

	'REPLY_TO_TOPIC'		=> 'Răspunde la subiect',
	'RETURN_POST'			=> '%sÎntoarce-te la mesaj%s',
	'SHOW_QR'				=> 'Răspuns rapid',

	'SUBMIT_VOTE'			=> 'Trimite vot',

	'TOTAL_VOTES'			=> 'Voturi totale',

	'UNLOCK_TOPIC'			=> 'Deschide subiect',

	'VIEW_INFO'				=> 'Detaliile mesajului',
	'VIEW_NEXT_TOPIC'		=> 'Următorul subiect',
	'VIEW_PREVIOUS_TOPIC'	=> 'Subiectul anterior',
	'VIEW_RESULTS'			=> 'Vezi rezultatele',
	'VIEW_TOPIC_POST'		=> '1 mesaj',
	'VIEW_TOPIC_POSTS'		=> '%d mesaje',
	'VIEW_UNREAD_POST'		=> 'Primul mesaj necitit',
	'VISIT_WEBSITE'			=> 'WWW',
	'VOTE_SUBMITTED'		=> 'Votul dumneavoastră a fost adăugat.',
	'VOTE_CONVERTED'		=> 'Schimbarea voturilor nu este suportată pentru voturile convertite.',
	
));

?>