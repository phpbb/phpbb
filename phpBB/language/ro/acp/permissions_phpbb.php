<?php
/**
* acp_permissions_phpbb (phpBB Permission Set) [Română]
*
* @package language
* @version $Id: permissions_phpbb.php, 8479 2008-03-29 00:22:48Z naderman $
* @translate $Id: permissions_phpbb.php, 8479 2008-05-19 20:49:11 www.phpbb.ro (shara21jonny) Exp $
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

/**
*   MODDERS PLEASE NOTE
*   
*   You are able to put your permission sets into a separate file too by
*   prefixing the new file with permissions_ and putting it into the acp
*   language folder.
*
*   An example of how the file could look like:
*
*   <code>
*
*   if (empty($lang) || !is_array($lang))
*   {
*       $lang = array();
*   }
*
*   // Adding new category
*   $lang['permission_cat']['bugs'] = 'Bugs';
*
*   // Adding new permission set
*   $lang['permission_type']['bug_'] = 'Bug Permissions';
*
*   // Adding the permissions
*   $lang = array_merge($lang, array(
*       'acl_bug_view'      => array('lang' => 'Can view bug reports', 'cat' => 'bugs'),
*       'acl_bug_post'      => array('lang' => 'Can post bugs', 'cat' => 'post'), // Using a phpBB category here
*   ));
*
*   </code>
*/

// Define categories and permission types
$lang = array_merge($lang, array(
    'permission_cat'    => array(
        'actions'        => 'Acţiuni',
        'content'        => 'Conţinut',
        'forums'        => 'Forumuri',
        'misc'            => 'General',
        'permissions'    => 'Permisiuni',
        'pm'            => 'Mesaje private',
        'polls'            => 'Chestionare',
        'post'            => 'Mesaj',
        'post_actions'    => 'Acţiuni mesaj',
        'posting'        => 'Scrie',
        'profile'        => 'Profil',
        'settings'        => 'Configurări',
        'topic_actions'    => 'Acţiuni subiect',
        'user_group'    => 'Grupuri &amp; utilizatori',
    ),

    // With defining 'global' here we are able to specify what is printed out if the permission is within the global scope.
    'permission_type'    => array(
        'u_'            => 'Permisiuni utilizator',
        'a_'            => 'Permisiuni administrator',
        'm_'            => 'Permisiuni moderator',
        'f_'            => 'Permisiuni forum',
        'global'        => array(
            'm_'            => 'Permisiuni moderator global',
        ),
    ),
));

// User Permissions
$lang = array_merge($lang, array(
    'acl_u_viewprofile'    => array('lang' => 'Poate vedea profiluri, lista membrilor şi cea cu utilizatorii conectaţi', 'cat' => 'profile'),
    'acl_u_chgname'        => array('lang' => 'Poate schimba nume utilizator', 'cat' => 'profile'),
    'acl_u_chgpasswd'    => array('lang' => 'Poate schimba parola', 'cat' => 'profile'),
    'acl_u_chgemail'    => array('lang' => 'Poate schimba adresa de email', 'cat' => 'profile'),
    'acl_u_chgavatar'    => array('lang' => 'Poate schimba imaginea asociată (Avatar)', 'cat' => 'profile'),
    'acl_u_chggrp'        => array('lang' => 'Poate schimba grupul implicit de utilizatori', 'cat' => 'profile'),

    'acl_u_attach'        => array('lang' => 'Poate ataşa fişiere', 'cat' => 'post'),
    'acl_u_download'    => array('lang' => 'Poate descărca fişiere', 'cat' => 'post'),
    'acl_u_savedrafts'    => array('lang' => 'Poate salva versiuni intermediare', 'cat' => 'post'),
    'acl_u_chgcensors'    => array('lang' => 'Poate dezactiva cenzurarea cuvintelor', 'cat' => 'post'),
    'acl_u_sig'            => array('lang' => 'Poate folosi semnătura', 'cat' => 'post'),

    'acl_u_sendpm'        => array('lang' => 'Poate trimite mesaje private', 'cat' => 'pm'),
    'acl_u_masspm'        => array('lang' => 'Poate trimite mesaje private către mai mulţi utilizatori şi grupuri', 'cat' => 'pm'),
    'acl_u_masspm_group'=> array('lang' => 'Poate trimite mesaje private către grupuri', 'cat' => 'pm'),
    'acl_u_readpm'        => array('lang' => 'Poate citi mesaje private', 'cat' => 'pm'),
    'acl_u_pm_edit'        => array('lang' => 'Poate modifica propriile mesaje private', 'cat' => 'pm'),
    'acl_u_pm_delete'    => array('lang' => 'Poate şterge mesajele private din propria cutie cu mesaje', 'cat' => 'pm'),
    'acl_u_pm_forward'    => array('lang' => 'Poate trimite mai departe mesajele private', 'cat' => 'pm'),
    'acl_u_pm_emailpm'    => array('lang' => 'Poate trimite mesajele private via email', 'cat' => 'pm'),
    'acl_u_pm_printpm'    => array('lang' => 'Poate tipări mesajele private', 'cat' => 'pm'),
    'acl_u_pm_attach'    => array('lang' => 'Poate ataşa fişiere în mesajele private', 'cat' => 'pm'),
    'acl_u_pm_download'    => array('lang' => 'Poate descărca fişiere în mesajele private', 'cat' => 'pm'),
    'acl_u_pm_bbcode'    => array('lang' => 'Poate folosi cod BB în mesajele private', 'cat' => 'pm'),
    'acl_u_pm_smilies'    => array('lang' => 'Poate folosi zâmbete în mesajele private', 'cat' => 'pm'),
    'acl_u_pm_img'        => array('lang' => 'Poate folosi codul BB [img] în mesajele private', 'cat' => 'pm'),
    'acl_u_pm_flash'    => array('lang' => 'Poate folosi codul BB [flash] în mesajele private', 'cat' => 'pm'),

    'acl_u_sendemail'    => array('lang' => 'Poate trimite email-uri', 'cat' => 'misc'),
    'acl_u_sendim'        => array('lang' => 'Poate trimite mesaje instant', 'cat' => 'misc'),
    'acl_u_ignoreflood'    => array('lang' => 'Poate ignora limita de flood', 'cat' => 'misc'),
    'acl_u_hideonline'    => array('lang' => 'Poate ascunde starea online', 'cat' => 'misc'),
    'acl_u_viewonline'    => array('lang' => 'Poate vedea utilizatorii online ascunşi', 'cat' => 'misc'),
    'acl_u_search'        => array('lang' => 'Poate căuta în forum', 'cat' => 'misc'),
));

// Forum Permissions
$lang = array_merge($lang, array(
    'acl_f_list'        => array('lang' => 'Poate vedea forumul', 'cat' => 'post'),
    'acl_f_read'        => array('lang' => 'Poate citi forumul', 'cat' => 'post'),
    'acl_f_post'        => array('lang' => 'Poate crea noi subiecte', 'cat' => 'post'),
    'acl_f_reply'       => array('lang' => 'Poate răspunde la subiecte', 'cat' => 'post'),
    'acl_f_icons'       => array('lang' => 'Poate folosi iconiţe pentru subiecte/mesaje', 'cat' => 'post'),
    'acl_f_announce'    => array('lang' => 'Poate publica anunţuri', 'cat' => 'post'),
    'acl_f_sticky'      => array('lang' => 'Poate publica anunţuri lipicoase', 'cat' => 'post'),

    'acl_f_poll'        => array('lang' => 'Poate crea chestionare', 'cat' => 'polls'),
    'acl_f_vote'        => array('lang' => 'Poate vota în chestionare', 'cat' => 'polls'),
    'acl_f_votechg'        => array('lang' => 'Poate schimba votul existent', 'cat' => 'polls'),

    'acl_f_attach'        => array('lang' => 'Poate ataşa fişiere', 'cat' => 'content'),
    'acl_f_download'    => array('lang' => 'Poate descărca fişiere', 'cat' => 'content'),
    'acl_f_sigs'        => array('lang' => 'Poate folosi semnături', 'cat' => 'content'),
    'acl_f_bbcode'        => array('lang' => 'Poate folosi cod BB', 'cat' => 'content'),
    'acl_f_smilies'        => array('lang' => 'Poate folosi zâmbete', 'cat' => 'content'),
    'acl_f_img'            => array('lang' => 'Poate folosi codul BB [img]', 'cat' => 'content'),
    'acl_f_flash'        => array('lang' => 'Poate folosi codul BB [flash]', 'cat' => 'content'),

    'acl_f_edit'        => array('lang' => 'Poate să-şi modifice mesajele', 'cat' => 'actions'),
    'acl_f_delete'        => array('lang' => 'Poate să-şi şteargă mesajele', 'cat' => 'actions'),
    'acl_f_user_lock'    => array('lang' => 'Poate să-şi blocheze subiectele', 'cat' => 'actions'),
    'acl_f_bump'        => array('lang' => 'Poate readuce subiecte în atenţie', 'cat' => 'actions'),
    'acl_f_report'        => array('lang' => 'Poate raporta mesaje', 'cat' => 'actions'),
    'acl_f_subscribe'    => array('lang' => 'Se poate abona la forum', 'cat' => 'actions'),
    'acl_f_print'        => array('lang' => 'Poate tipări subiecte', 'cat' => 'actions'),
    'acl_f_email'        => array('lang' => 'Poate trimite subiecte via email', 'cat' => 'actions'),

    'acl_f_search'        => array('lang' => 'Poate căuta în forum', 'cat' => 'misc'),
    'acl_f_ignoreflood' => array('lang' => 'Poate ignora limita de flood', 'cat' => 'misc'),
    'acl_f_postcount'    => array('lang' => 'Incrementează contorul de mesaje<br /><em>Reţineţi că această setare afectează doar mesajele noi.</em>', 'cat' => 'misc'),
    'acl_f_noapprove'    => array('lang' => 'Poate scrie fără aprobare', 'cat' => 'misc'),
));

// Moderator Permissions
$lang = array_merge($lang, array(
    'acl_m_edit'        => array('lang' => 'Poate modifica mesaje', 'cat' => 'post_actions'),
    'acl_m_delete'        => array('lang' => 'Poate şterge mesaje', 'cat' => 'post_actions'),
    'acl_m_approve'        => array('lang' => 'Poate aproba mesaje', 'cat' => 'post_actions'),
    'acl_m_report'        => array('lang' => 'Poate închide şi şterge mesaje raportate', 'cat' => 'post_actions'),
    'acl_m_chgposter'    => array('lang' => 'Poate schimba autorul mesajului', 'cat' => 'post_actions'),

    'acl_m_move'    => array('lang' => 'Poate muta subiecte', 'cat' => 'topic_actions'),
    'acl_m_lock'    => array('lang' => 'Poate închide subiecte', 'cat' => 'topic_actions'),
    'acl_m_split'    => array('lang' => 'Poate despărţi subiecte', 'cat' => 'topic_actions'),
    'acl_m_merge'    => array('lang' => 'Poate uni subiecte', 'cat' => 'topic_actions'),

    'acl_m_info'    => array('lang' => 'Poate vizualiza detaliile mesajului', 'cat' => 'misc'),
    'acl_m_warn'    => array('lang' => 'Poate da avertismente<br /><em>Această setare este alocată doar global. Nu este bazată pe forum.</em>', 'cat' => 'misc'), // This moderator setting is only global (and not local)
    'acl_m_ban'        => array('lang' => 'Poate administra restricţiile<br /><em>Această setare este alocată doar global. Nu este bazată pe forum.</em>', 'cat' => 'misc'), // This moderator setting is only global (and not local)
));

// Admin Permissions
$lang = array_merge($lang, array(
    'acl_a_board'        => array('lang' => 'Poate modifica setările forumului/verifica versiunile noi', 'cat' => 'settings'),
    'acl_a_server'        => array('lang' => 'Poate modifica setările de comunicaţie/server-ul', 'cat' => 'settings'),
    'acl_a_jabber'        => array('lang' => 'Poate modifica configuraţiile Jabber', 'cat' => 'settings'),
    'acl_a_phpinfo'        => array('lang' => 'Poate vizualiza setările php', 'cat' => 'settings'),

    'acl_a_forum'        => array('lang' => 'Poate administra forumuri', 'cat' => 'forums'),
    'acl_a_forumadd'    => array('lang' => 'Poate adăuga noi forumuri', 'cat' => 'forums'),
    'acl_a_forumdel'    => array('lang' => 'Poate şterge forumuri', 'cat' => 'forums'),
    'acl_a_prune'        => array('lang' => 'Poate curăţi forumuri', 'cat' => 'forums'),

    'acl_a_icons'        => array('lang' => 'Poate modifica iconiţe de subiect/mesaj şi zâmbete', 'cat' => 'posting'),
    'acl_a_words'        => array('lang' => 'Poate modifica lista de cuvinte cenzurate', 'cat' => 'posting'),
    'acl_a_bbcode'        => array('lang' => 'Poate defini etichetele de cod BB', 'cat' => 'posting'),
    'acl_a_attach'        => array('lang' => 'Poate schimba setările fişierelor ataşate asociate', 'cat' => 'posting'),

    'acl_a_user'        => array('lang' => 'Poate administra utilizatorii<br /><em>Această facilitate include de asemenea vizualizarea agentului de browser utilizatorului în lista cu utilizatorii conectaţi.</em>', 'cat' => 'user_group'),
    'acl_a_userdel'        => array('lang' => 'Poate şterge/curăţi utilizatorii', 'cat' => 'user_group'),
    'acl_a_group'        => array('lang' => 'Poate administra grupuri', 'cat' => 'user_group'),
    'acl_a_groupadd'    => array('lang' => 'Poate adăuga grupuri noi', 'cat' => 'user_group'),
    'acl_a_groupdel'    => array('lang' => 'Poate şterge grupuri', 'cat' => 'user_group'),
    'acl_a_ranks'        => array('lang' => 'Poate administra ranguri', 'cat' => 'user_group'),
    'acl_a_profile'        => array('lang' => 'Poate administra câmpurile din profil particularizate', 'cat' => 'user_group'),
    'acl_a_names'        => array('lang' => 'Poate administra numele dezactivate', 'cat' => 'user_group'),
    'acl_a_ban'            => array('lang' => 'Poate administra restricţiile', 'cat' => 'user_group'),

    'acl_a_viewauth'    => array('lang' => 'Poate vizualiza măştile permisiunilor', 'cat' => 'permissions'),
    'acl_a_authgroups'  => array('lang' => 'Poate modifica permisiunile pentru grupurile individuale', 'cat' => 'permissions'),
    'acl_a_authusers'   => array('lang' => 'Poate modifica permisiunile pentru utilizatorii individuali', 'cat' => 'permissions'),
    'acl_a_fauth'       => array('lang' => 'Poate modifica clasa permisiunilor forumului', 'cat' => 'permissions'),
    'acl_a_mauth'       => array('lang' => 'Poate modifica clasa permisiunilor moderatorului', 'cat' => 'permissions'),
    'acl_a_aauth'       => array('lang' => 'Poate modifica clasa permisiunilor administratorului', 'cat' => 'permissions'),
    'acl_a_uauth'       => array('lang' => 'Poate modifica clasa permisiunilor utilizatorului', 'cat' => 'permissions'),
    'acl_a_roles'       => array('lang' => 'Poate administra roluri', 'cat' => 'permissions'),
    'acl_a_switchperm'  => array('lang' => 'Poate folosi permisiunile altora', 'cat' => 'permissions'),

    'acl_a_styles'        => array('lang' => 'Poate administra stiluri', 'cat' => 'misc'),
    'acl_a_viewlogs'    => array('lang' => 'Poate vizualiza jurnale', 'cat' => 'misc'),
    'acl_a_clearlogs'    => array('lang' => 'Poate curăţa jurnale', 'cat' => 'misc'),
    'acl_a_modules'        => array('lang' => 'Poate administra module', 'cat' => 'misc'),
    'acl_a_language'    => array('lang' => 'Poate administra pachete de limbă', 'cat' => 'misc'),
    'acl_a_email'        => array('lang' => 'Poate trimite email în bloc', 'cat' => 'misc'),
    'acl_a_bots'        => array('lang' => 'Poate administra roboţi', 'cat' => 'misc'),
    'acl_a_reasons'        => array('lang' => 'Poate administra motive raportate/contestări', 'cat' => 'misc'),
    'acl_a_backup'        => array('lang' => 'Poate salva/restaura baza de date', 'cat' => 'misc'),
    'acl_a_search'        => array('lang' => 'Poate administra setările căutărilor', 'cat' => 'misc'),
));

?>