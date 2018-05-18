<?php
/**
*
* @package phpBB2
* @version $Id: lang_main.php,v 1.1 2013/10/03 08:32:41 orynider Exp $
* @copyright (c) 2001 The phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* @modified by Unfesque for phpBB Fully Modded Lite
* @version $Id: 11/01/2007 12:26
* @support http://phpbbfm.net
*
*/

//
// CONTRIBUTORS:
//	 Add your details here if wanted, e.g. Name, username, email address, website
//
// 05/03/2007 Electronico, (New-Caledonia), French Translation, me@electronico.nc

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = 'iso-8859-1';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'gauche';
$lang['RIGHT'] = 'droite';
$lang['DATE_FORMAT'] =  'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
// $lang['TRANSLATION_INFO'] = '';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Welcome'] = 'Bienvenue'; // Welcome
$lang['Forum'] = 'Forum';
$lang['Category'] = 'Cat&eacute;gorie';
$lang['Topic'] = 'Sujet';
$lang['Topics'] = 'Sujets';
$lang['Replies'] = 'R&eacute;ponses';
$lang['Views'] = 'Lus';
$lang['Post'] = 'Message';
$lang['Posts'] = 'Messages';
$lang['Posted'] = 'Envoy&eacute;';
$lang['Username'] = 'Utilisateur';
$lang['Password'] = 'Mot de Passe';
$lang['Email'] = 'Email';
$lang['Poster'] = 'Poster';
$lang['Author'] = 'Auteur';
$lang['Time'] = 'Heure';
$lang['Hours'] = 'Heures';
$lang['Message'] = 'Message';

$lang['1_Day'] = '1 Jour';
$lang['7_Days'] = '7 Jours';
$lang['2_Weeks'] = '2 Semaines';
$lang['1_Month'] = '1 Mois';
$lang['3_Months'] = '3 Mois';
$lang['6_Months'] = '6 Mois';
$lang['1_Year'] = '1 An';

$lang['Go'] = 'Go';
$lang['Style'] = 'Style';
$lang['Jump_to'] = 'Allez &agrave;';
$lang['Submit'] = 'Envoyer';
$lang['Reset'] = 'RAZ';
$lang['Cancel'] = 'Annuler';
$lang['Preview'] = 'Pr&eacute;visualisation';
$lang['Confirm'] = 'Confirmer';
$lang['Spellcheck'] = 'Correcteur';
$lang['Yes'] = 'Oui';
$lang['No'] = 'Non';
$lang['Enabled'] = 'Activ&eacute;';
$lang['Disabled'] = 'D&eacute;activ&eacute;';
$lang['Error'] = 'Erreur';
$lang['Sort_Ascending'] = 'Croissant';
$lang['Sort_Descending'] = 'D&eacute;croisant';
$lang['Sub_to_forum'] = 'Surveiller ce Forum';
$lang['Sub_to_thread'] = 'Surveiller ce Sujet';

$lang['Next'] = 'Suivant';
$lang['Previous'] = 'Pr&eacute;c&eacute;dent';
$lang['Goto_page'] = 'Allez &agrave; la page';
$lang['Joined'] = 'Rejoint';
$lang['IP_Address'] = 'Adresse IP';

$lang['Select_forum'] = 'S&eacute;lectionnez un Forum';
$lang['View_latest_post'] = 'Voir derniers messages';
$lang['View_newest_post'] = 'Voir messages r&eacute;cents';
$lang['Online_explain'] = 'Ces donn&eacute;es sont bas&eacute;es sur les Membres actifs des derni&egrave;res ' . $board_config['whosonline_time'] . ' minutes';
$lang['Page_of'] = 'Page <b>%d</b> sur <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ';
$lang['AIM'] = 'AIM';
$lang['MSNM'] = 'MSN';
$lang['YIM'] = 'Yahoo Messenger';
$lang['Local_time'] = 'Heure locale';

$lang['Be_visible'] = 'Visible';
$lang['Be_invisible'] = 'Invisible';

$lang['Forum_Index'] = 'Forum';  // eg. sitename Forum Index, %s can be removed if you prefer
$lang['Admin_panel'] = 'Admin CP';
$lang['Super_Mod_panel'] = 'Super Moderator CP';
$lang['Mod_panel'] = 'Moderator CP';
$lang['Stats_panel'] = 'Statistics CP';
$lang['Sitemap'] = 'Pan du site';

$lang['All_Attachments'] = 'Voir toutes les Pi&egrave;ces Jointes';
$lang['None'] = 'Aucun';

$lang['All_content'] = 'Tout le contenu est enregist&eacute;';
$lang['Original_author'] = 'et ces auteurs originaux';

$lang['Post_new_topic'] = 'Envoyer un nouveau Sujet';
$lang['Reply_to_topic'] = 'R&eacute;pondre au Sujet';
$lang['Reply_with_quote'] = 'R&eacute;pondre en citant';
$lang['Quick_Reply_to_topic'] = 'R&eacute;ponse rapide';

$lang['Click_return_topic'] = 'Cliquez %sIci%s pour revenir au Sujet'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Cliquez %sIci%s pour re-essayer';
$lang['Click_return_forum'] = 'Cliquez %sIci%s pour revenir au Forum';
$lang['Click_view_message'] = 'Cliquez %sIci%s pour voir votre Message';
$lang['Click_return_modcp'] = 'Cliquez %sIci%s pour revenir au Panneau de Controle Mod&eacute;rateur';
$lang['Click_return_group'] = 'Cliquez %sIci%s pour revenir aux informations de Groupe';

$lang['Board_disable'] = 'D&eacute;sol&eacute; le Forum est actuellement indisponible. R&eacute;-essayez plus tard.';
$lang['disable_reg_msg'] = 'D&eacute;sol&eacute; les inscriptions sont d&eacute;-activ&eacute;es. R&eacute;-essayez plus tard.';

//
// Global Header strings
//
$lang['Registered_users'] = 'Membres: ';
$lang['Browsing_forum'] = 'Utilisateurs consultant ce Forum: ';
$lang['Browsing_topic'] = 'Utilisateurs consultant ce Sujet: ';
$lang['Online_users_zero_total'] = 'Il y a <b>0</b> utilisateurs connect&eacute;s :: ';
$lang['Online_users_total'] = 'Il y a <b>%d</b> utilisateurs connect&eacute;s :: ';
$lang['Online_user_total'] = 'Il y a <b>%d</b> utilisateur connect&eacute; :: ';
$lang['Reg_users_zero_total'] = '0 Membres, ';
$lang['Reg_users_total'] = '%d Membres, ';
$lang['Reg_user_total'] = '%d Membre, ';
$lang['Hidden_users_zero_total'] = '0 Invisibles et ';
$lang['Hidden_user_total'] = '%d Invisible et ';
$lang['Hidden_users_total'] = '%d Invisibles et ';
$lang['Guest_users_zero_total'] = '0 Invit&eacute;s';
$lang['Guest_users_total'] = '%d Invit&eacute;s';
$lang['Guest_user_total'] = '%d Invit&eacute;';
$lang['Record_online_users'] = 'Le maximum d\'utilisateurs simultan&eacute;s connect&eacute;s a &eacute;t&eacute; de <b>%s</b> le %s'; // first %s = number of users, second %s is the date.
$lang['Record_day_users'] = 'Le record d\'utilisateurs connect&eacute;s sur 24H a &eacute;t&eacute; <b>%s</b> le %s'; // first %s = number of users, second %s is the date.

$lang['Legend'] = 'Legend';
$lang['Admin_online_color'] = '%sAdministrateurs%s';
$lang['Super_Mod_online_color'] = '%sSuper Mod&eacute;rateurs%s';
$lang['Mod_online_color'] = '%sMod&eacute;rateurs%s';

$lang['You_last_visit'] = 'Derni&egrave;re visite le: %s'; // %s replaced by date/time
$lang['You_points'] = 'Total %s: %d'; // %s replaced by points name, %d replaced by points value
$lang['Current_time'] = 'Il est %s'; // %s replaced by time
$lang['Session_invalid'] = 'Session invalide. Renvoyez le formulaire.';

$lang['Search_new'] = 'Nouveaux Messages depuis votre derni&egrave;re visite';
$lang['Search_your_posts'] = 'Voir vos Messages';
$lang['Search_unanswered'] = 'Voir les Messages sans r&eacute;ponse';
$lang['View_random_topic'] = 'Voir un Sujet au hasard';

$lang['Register'] = 'S\'enregistrer';
$lang['Profile'] = 'PC Membre';
$lang['Profile_Full'] = 'Voir Profil';
$lang['Edit_profile'] = 'Modifier Profil';
$lang['Search'] = 'Rechercher';
$lang['Memberlist'] = 'Membres';
$lang['FAQ'] = 'FAQ';
$lang['BBCode_guide'] = 'Guide BBCode';
$lang['Usergroups'] = 'Groupes';
$lang['Last_Post'] = 'Dernier Message';
$lang['Moderator'] = 'Mod&eacute;rateur:';
$lang['Moderators'] = 'Mod&eacute;rateurs:';

$lang['Posted_articles_zero_total'] = '<b>0</b> Messages'; // Number of posts
$lang['Posted_articles_total'] = '<b>%d</b> <Messages'; // Number of posts
$lang['Posted_article_total'] = '<b>%d</b> Message'; // Number of posts
$lang['Posted_topics_total'] = '  <b>%s</b> Sujets';
$lang['Registered_users_zero_total'] = '<b>0</b> Membres'; // # registered users
$lang['Registered_users_total'] = '<b>%d</b> Membres'; // # registered users
$lang['Registered_user_total'] = '<b>%d</b> Membre'; // # registered user
$lang['Newest_user'] = 'Dernier Membre <b>%s%s%s</b>'; // a href, username, /a
$lang['Within'] = 'dans';
$lang['No_new_posts_last_visit'] = 'Pas de Message depuis votre derni&egrave;re visite';
$lang['No_new_posts'] = 'Pas de nouveaux Messages';
$lang['New_posts'] = 'Nouveaux Messages';
$lang['New_post'] = 'Nouveau Message';
$lang['No_new_posts_hot'] = 'Pas de nouveaux Messages [ Populaire ]';
$lang['New_posts_hot'] = 'Nouveaux Messages [ Populaire ]';
$lang['No_new_posts_locked'] = 'Pas de nouveaux Messages [ Verrouill&eacute; ]';
$lang['New_posts_locked'] = 'Nouveaux Messages [ Verrouill&eacute; ]';
$lang['Forum_is_locked'] = 'Forum Verrouill&eacute;';

$lang['Forum_no_active'] = '';
$lang['Forum_one_active'] = '<b>%d</b> Membre actif: ';
$lang['Forum_more_active'] = '<b>%d</b> Membres actifs: ';
$lang['Forum_one_hidden_active'] = '<b>%d</b> Invisible';
$lang['Forum_more_hidden_active'] = '<b>%d</b> Invisibles';
$lang['Forum_one_guest_active'] = '<b>%d</b> Invit&eacute;';
$lang['Forum_more_guests_active'] = '<b>%d</b> Invit&eacute;s';

//
// Login
//
$lang['Enter_password'] = 'Entrez votre nom d\'Utilisateur et mot de Passe pour vous connecter.';
$lang['Login'] = 'Connexion';
$lang['Logout'] = 'D&eacute;connexion';

$lang['Forgotten_password'] = 'J\'ai oubli&eacute; mon mot de Passe';
$lang['Resend_Activation'] = 'Renvoyer l\'Eamil d\'activation';

$lang['Log_me_in'] = 'Connexion automatique';
$lang['Log_me_in_auto'] = 'Connexion automatique';
$lang['Terms_of_use'] = 'Conditions d\'utilisation';
$lang['Error_login'] = 'Vous avez entr&eacute; un Nom d\'Utilsateur invalide, d&eacute;sactiv&eacute; ou un mauvais Mot de Passe.';
$lang['Login_attempts_exceeded'] = 'Le nombre maximum de tentatives de connexion de %s est atteint. Vous ne pourrez pas vous connecter avant %s minutes.';

//
// Index page
//
$lang['Lite_Index'] = 'Index All&eacute;g&eacute;';
$lang['Main_Board'] = 'Index Principal';
$lang['No_Posts'] = 'Pas de Messages';
$lang['No_forums'] = 'Pas de Forums';
$lang['Toggle_description'] = 'Description';

$lang['Forum_admin'] = 'Ce Forum n\'est visible que par les Administrateurs';
$lang['Topic_Jump'] = 'Allez au Sujet';
$lang['Linkdb'] = 'Liens';

$lang['Private_Message'] = 'Message Priv&eacute;';
$lang['Private_Messages'] = 'Messages Priv&eacute;s';
$lang['Private_Messages_Full'] = 'Mode avanc&eacute; Messagerie Priv&eacute;e';
$lang['Who_is_Online'] = 'Qui est en ligne?';

$lang['Mark_all_forums'] = 'Marquer tout les Forums lus';
$lang['Forums_marked_read'] = 'Tout les Forums ont &eacute;t&eacute; marqu&eacute;s lus';
$lang['Board_lang'] = 'Ma langue';

$lang['Index_New_posts'] = 'nouveaux messages';
$lang['Index_New_post'] = 'nouveau message';
$lang['Index_New_topics'] = 'nouveaux sujets';
$lang['Index_New_topic'] = 'nouveau sujet';

$lang['Today_at'] = 'AUjourd\'hui &agrave; %s'; // %s is the time
$lang['Yesterday_at'] = 'Hier &agrave; %s'; // %s is the time

//
// Viewforum
//
$lang['View_forum'] = 'Voir Forum';
$lang['Forum_rules'] = 'R&egrave;gles du Forum';

$lang['Forum_issub'] = 'Le Forum s&eacute;lectionn&eacute; contient des sous-cat&eacute;gories.<br />Il n\'est donc pas possible de voir les Messages.';
$lang['Forum_not_exist'] = 'Le Forum s&eacute;lectionn&eacute; n\'existe pas.';
$lang['Reached_on_error'] = 'VOus avez atteint cette page par erreur.';

$lang['Display_topics'] = 'Afficher les Sujet des derniers';
$lang['All_Topics'] = 'Tout les Sujets';

$lang['Topic_Global_Announcement']='<b>Annonce Globale:</b>';
$lang['Topic_Announcement'] = '<b>Annonce:</b>';
$lang['Topic_Sticky'] = '<b>Post-it:</b>';
$lang['Topic_Moved'] = '<b>D&eacute;plac&eacute;:</b>';
$lang['Topic_Linked'] = '<b>Lien:</b>';
$lang['Topic_Poll'] = '<b>[ Sondage ]</b>';

$lang['Mark_all_topics'] = 'Marquer tout les Sujets lus';
$lang['Topics_marked_read'] = 'Tout les Sujets ont &eacute;t&eacute; marqu&eacute;s lus';

$lang['Stop_watching_forum'] = 'Arreter de surveiller ce Forum';
$lang['Start_watching_forum'] = 'Surveiller ce Forum';
$lang['No_longer_watching_forum'] = 'Vous ne surveillez plus ce Forum';
$lang['You_are_watching_forum'] = 'Vous surveillez maintenant ce Forum';

$lang['Rules_post_can'] = 'Vous <b>pouvez</b> poster de nouveaux Sujets dans ce Forum';
$lang['Rules_post_cannot'] = 'Vous <b>ne pouvez pas</b> poster de nouveaux Sujets dans ce Forum';
$lang['Rules_reply_can'] = 'Vous <b>pouvez</b> r&eacute;pondre aux Sujets dans ce Forum';
$lang['Rules_reply_cannot'] = 'Vous <b>ne pouvez pas</b> r&eacute;pondre aux Sujets dans ce Forum';
$lang['Rules_edit_can'] = 'Vous <b>can</b> modifier vos Messages dans ce Forum';
$lang['Rules_edit_cannot'] = 'Vous <b>ne pouvez pas</b> modifier vos Messages dans ce Forum';
$lang['Rules_delete_can'] = 'Vous <b>pouvez</b> effacer vos Messages dans ce Forum';
$lang['Rules_delete_cannot'] = 'Vous <b>ne pouvez pas</b> effacer vos Messages dans ce Forum';
$lang['Rules_vote_can'] = 'Vous <b>pouvez</b> voter aux Sondages dans ce Forum';
$lang['Rules_vote_cannot'] = 'Vous <b>ne pouvez pas</b> voter aux Sondages dans ce Forum';
$lang['Rules_moderate'] = 'Vous <b>pouvez</b> %smod&eacute;rer ce Forum%s'; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = 'Il n\'y a pas de Sujets dans ce Forum.<br />Cliquez sur <b>Nouveau Sujet</b> pour en envoyer un.';

// External Forum Redirection
$lang['External_text'] = 'Total redirections';
$lang['External_members'] = 'Membres';
$lang['External_guests'] = 'Invit&eacute;s';

// Forum Enter Limit
$lang['Forum_enter_limit_error'] = 'D&eacute;sol&eacute;, vous ne pouvez pas acc&eacute;der &agrave; "%s" avant d\'avoir envoy&eacute; un minimum de %d Messages.';

// Journal forum
$lang['Journal_reply_message'] = 'Seuls les Administrateurs, Mod&eacute;rateurs et l\'auteur original du Sujet peuvent r&eacute;pondre.<br />';
$lang['Journal_topic_message'] = '1 seul Sujet par Membre est autoris&eacute; dans ce Forum.<br />';

// Password Protected Forums
$lang['Forum_password'] = 'Mot de Passe Forum';
$lang['Enter_forum_password'] = 'Entrez le Mot de Passe pour acc&eacute;der au Forum';
$lang['Enter_password'] = 'Mot de Passe';
$lang['Forum_password_explain'] = 'Pour consulter ou envoyer dans ce Forum, vous devez entrer un Mot de Passe.';
$lang['Incorrect_forum_password'] = 'Vous avez envoy&eacute; un mauvais Mot de Passe.';
$lang['Password_login_success'] = 'Mot de Passe OK.';
$lang['Click_return_page'] = 'Cliquez %sIci%s pour acc&eacute;der au Forum';
$lang['Only_alpha_num_chars'] = 'Le mot de Pase doit &ecirc;tre compos&eacute; de 3 &agrave; 20 charact&egrave;res et ne doit contenir que des caract&egrave;res alpha-num&eacute;riques (A-Z, a-z, 0-9).';

// Picture alert
$lang['Picture_alert'] = ' <span class="gensmall">(1 photo)</span>';
$lang['Pictures_alert'] = ' <span class="gensmall">(%d photos)</span>'; // %d is more than 1 pic, do not remove!

//
// Gender & Age
//
$lang['Male'] = 'Homme';
$lang['Female'] = 'Femme';
$lang['Age'] = 'Age';
$lang['Profile_photo'] = 'Photo';
//
// AJAXed
//
$lang['AJAXed_delete_confirm'] = 'Etes-vous certain de vouloir effacer ce Sujet et tout les Messages qu\'il contient?';
$lang['AJAXed_deleted_topic'] = 'Vous avez effc&eacute; ce Sujet et tout les Messages qu\'il contenait!';
$lang['AJAXed_loading'] = 'Chargement...';
$lang['AJAXed_editor_premission'] = '<b>Vous n`\'&ecirc;tes pas autoris&eacute; &agrave; modifier ce Message.</b></br /><br />';
$lang['AJAXed_check_username1'] = 'Ce nom d\'utilisateur est d&eacute;j&agrave; pris!';
$lang['AJAXed_check_username2'] = 'Ce nom d\'utilisateur est d&eacute;j&agrave; le votre!';
$lang['AJAXed_check_username3'] = 'Ce nom d\'utilisateur est disponible!';
$lang['AJAXed_no_username'] = 'Pas de Membre avec cette lettre';
$lang['AJAXed_post_menu'] = 'Menu Envoi';
$lang['AJAXed_post_ip'] = "Adresse IP exp&eacute;diteur";
$lang['AJAXed_post_back'] = "Retour";
$lang['AJAXed_quick_edit'] = "Modification rapide";
$lang['AJAXed_normal_edit'] = "Modification normale";
$lang['AJAXed_view_ip'] = "Voir adresse IP";
$lang['AJAXed_error'] = "Il y a ey une erreur avec AJAXed.";
$lang['AJAXed_poll_menu'] = 'Menu Sondage';
$lang['AJAXed_poll_mod'] = 'Modification Sondage';
$lang['AJAXed_poll_title'] = 'Modifier Titre Sondage';
$lang['AJAXed_poll_options'] = 'Modifier Options Sondage';
$lang['AJAXed_close'] = 'Fermer';
$lang['AJAXed_poll_confirm'] = 'Etes-vous certain de vouloir supprimer cette option dans le Sondage?';
$lang['AJAXed_poll_cast'] = 'Comptabiliser mon Vote';
$lang['AJAXed_poll_select'] = 'Vous devez s&eacute;lectionner une Option avant que votre Vote ne soit comptabilis&eacute;.';
$lang['AJAXed_Timed_out'] = 'AJAXed a mis trop de temps! r&eacute;-essayez.';
$lang['AJAXed_moduale_disabled'] = 'Ce modules est d&eacute;sactiv&eacute;.';
$lang['AJAXed_check_true'] = 'Les Mots de Passe sont corrects!';
$lang['AJAXed_check_false'] = 'Les Mot de Passe ne sont pas identiques!';
$lang['AJAXed_add_update'] = 'Afficher que ce Mesage a &eacute;t&eacute; modifi&eacute;?';
$lang['AJAXed_Go_To_Top'] = 'Aller en haut de la pr&eacute;visualisation';
$lang['AJAXed_Go_To_Editor'] = 'Aller &agrave; l\'&eacute;diteur';
$lang['AJAXed_Go_To_Full_Mode'] = 'Passer au mode complet';
$lang['AJAXed_Invaild_ID'] = 'ID invalide';

//
// Message length
//
$lang['Message_too_long'] = 'Votre Message est trop long.<br /><br />La taille du Message st limit&eacute;e &agrave;  %d caract&egrave;res';
$lang['Message_length'] = 'caract&egrave;res maxi par Message';
$lang['Message_length_explain'] = 'Mettre &agrave; z&eacute;ro pour ne pas limiter la longueur du Message.';
$lang['Max_message_length'] = 'Longueur maxi du Message: %d caract&egrave;res.';
$lang['Check_message_length'] = 'V&eacute;rifier la longueur du Message';
$lang['Current_message_length'] = 'Longueur actuelle: ';

//
// Viewtopic
//
$lang['View_topic'] = 'Voir Sujet';

$lang['Guest'] = 'Invit&eacute;';
$lang['Sponsor'] = 'Sponsor';
$lang['Post_subject'] = 'Titre du Message';
$lang['No_Subject'] = '(Pas de titre)';
$lang['View_next_topic'] = 'Voir Sujet suivant';
$lang['View_previous_topic'] = 'Voir Sujet pr&eacute;c&eacute;dent';

$lang['Refresh_page'] = 'Rafraichir le page';
$lang['Topic_bookmark'] = 'Ajouter ce Sujet &agrave; mes Favoris';
$lang['Tell_Friend'] = 'Informer un ami';
$lang['Print_View'] = 'Version imprimable';
$lang['Member_number'] = 'Membre: #';

$lang['Submit_vote'] = 'Envoyer le Vote';
$lang['View_results'] = 'Voir les r&eacute;sultats';
$lang['Vote_until'] ='Voter jusqu\'&agrave;';
$lang['Vote_endless'] = 'ind&eacute;finiment';
$lang['Vote_closed'] = 'VOte termin&eacute;';

$lang['No_newer_topics'] = 'Pas de Sujets plus r&eacute;cents dans ce Forum';
$lang['No_older_topics'] = 'Pas de Sujets plus anciens dans ce Forum';
$lang['Topic_post_not_exist'] = 'Le Sujet ou le Message demand&eacute; n\'existe pas';
$lang['No_posts_topic'] = 'Pas de Messages dans ce Sujet';

$lang['Display_posts'] = 'Afficher les Messages des derniers';
$lang['All_Posts'] = 'Tout les Messages';
$lang['Newest_First'] = 'Plus r&eacute;cents en premier';
$lang['Oldest_First'] = 'Plus anciens en premier';

$lang['Back_to_top'] = 'Haut de la page';
$lang['Back_at_bottom'] = 'Bas de la page';

$lang['Read_profile'] = 'Voir le Profil';
$lang['Visit_website'] = 'Visiter le site de l\'exp&eacute;diteur';
$lang['ICQ_status'] = 'ICQ';
$lang['Edit_delete_post'] = 'Modifier/effacer ce Message';
$lang['View_IP'] = 'Voir IP de l\'exp&eacute;diteur';
$lang['Delete_post'] = 'Effacer ce Message';

$lang['wrote'] = 'a &eacute;crit'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Citation'; // comes before bbcode quote output.
$lang['Code'] = 'Code'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Derni&egrave;re modification par %s le %s; modifi&eacute; %d fois en tout'; // Last edited by me on 12 Oct 2001; edited 1 time in total
$lang['Edited_times_total'] = 'Derni&egrave;re modification par %s le %s; modifi&eacute; %d fois en tout'; // Last edited by me on 12 Oct 2001; edited 2 times in total

$lang['Lock_topic'] = 'Verrouiller le Sujet';
$lang['Unlock_topic'] = 'D&eacute;verrouiller le Sujet';
$lang['Move_topic'] = 'D&eacute;placer ce sujet';
$lang['Link_topic'] = 'Lien vers ce sujet';
$lang['Delete_topic'] = 'Effacer ce sujet';
$lang['Split_topic'] = 'Split this topic';
$lang['Sticky_topic'] = 'Post-it';
$lang['Bump_topic'] = 'Booster ce Sujet';
$lang['Unsticky_topic'] = 'Oter Post-it au Sujet';
$lang['Announce_topic'] = 'Annoncer ce Sujet';
$lang['Unannounce_topic'] = 'Oter Annonce &agrave; ce Sujet';
$lang['Globalannounce_topic'] = 'Annoncer Globalement ce Sujet';
$lang['Unglobalannounce_topic'] = 'Oter Annonce Globale &agrave; ce Sujet';
$lang['Stop_watching_topic'] = 'Arr&ecirc;ter de surveiller ce Sujet';
$lang['Start_watching_topic'] = 'Surveiller ce Sujet';
$lang['No_longer_watching'] = 'Vous ne surveillez plus ce sujet';
$lang['You_are_watching'] = 'Vous surveillez ce sujet';
$lang['Topics_Title_Edited'] = 'Le titre du Sujet a &eacute;t&eacute; modifi&eacute;.';
$lang['Edit_title'] = 'Modifier titre';
$lang['Merge_post'] = 'Rassembler les Messages dans ce Sujet';
$lang['Total_votes'] = 'Votes total';

$lang['Online'] = 'Connect&eacute;';
$lang['Offline'] = 'D&eacute;connect&eacute;';
$lang['is_online'] = '%s est connect&eacute;';
$lang['is_offline'] = '%s est d&eacute;connect&eacute;';
$lang['is_hidden'] = '%s est invisible';
$lang['View_status'] = 'Etat';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Corps du message';
$lang['Topic_review'] = 'R&eacute;capitulif du Sujet';

$lang['No_post_mode'] = 'No post mode specified'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Envoyer un nouveau Sujet';
$lang['Post_a_reply'] = 'R&eacute;pondre';
$lang['Post_topic_as'] = 'Envoyer le Sujet comme';
$lang['Edit_Post'] = 'Modifier le Message';
$lang['Options'] = 'Options';

$lang['Post_Global_Announcement'] = 'Global';
$lang['Post_Announcement'] = 'Annonce';
$lang['Post_Sticky'] = 'Post-it';
$lang['Post_Poll'] = 'Sondage';
$lang['Post_Normal'] = 'Normal';
$lang['Post_Linked'] = 'Lien';
$lang['Post_Moved'] = 'D&eacute;plac&eacute;';

$lang['Confirm_delete'] = 'Etes-vous certain de vouloir effacer ce Message?';
$lang['Confirm_delete_poll'] = 'Etes-vous certain de vouloir effacer ce Sondage?';

$lang['Flood_Error'] = 'Vous ne pouvez pas poster aussi rapidement; R&eacute;-essayez dans quelques instants.';
$lang['Empty_subject'] = 'Vous devez sp&eacute;cifier un titre lorsque vous postez un nouveau sujet.';
$lang['Empty_message'] = 'You must enter a message when posting.';
$lang['Forum_locked'] = 'Ce forum est verrouill&eacute;, vous ne pouvez ni envoyer, ni r&eacute;pondre, ni modifier les messages.';
$lang['Topic_locked'] = 'Ce sujet est verrouill&eacute;, vous ne pouvez ni r&eacute;pondre, ni modifier les message.';
$lang['No_post_id'] = 'Vous devez s&eacute;lectionner un message &agrave; modifer';
$lang['No_topic_id'] = 'Vous devez s&eacute;lectionner un message auquel r&eacute;pondre';
$lang['No_valid_mode'] = 'Vous ne pouvez que envoyer, r&eacute;pondre, &eacute;diter ou citer les messages.';
$lang['No_such_post'] = 'Message non trouv&eacute;.';
$lang['Edit_own_posts'] = 'D&eacute;sol&eacute; vous ne pouvez &eacute;diter que vos propres messages.';
$lang['Delete_own_posts'] = 'D&eacute;sol&eacute; vous ne pouvez effacer que vos propres messages.';
$lang['Cannot_delete_replied'] = 'D&eacute;sol&eacute; vous ne pouvez pas effacer des messages avec des r&eacute;ponses.';
$lang['Cannot_delete_poll'] = 'D&eacute;sol&eacute; vous ne pouvez pas effacer un sondage en cours.';
$lang['Empty_poll_title'] = 'Vous devez entrer un titre pour le sondage.';
$lang['To_few_poll_options'] = 'Vous devez entrer au moins 2 options de vote.';
$lang['To_many_poll_options'] = 'Vous avez entr&eacute; trop d\'options de vote.';
$lang['Post_has_no_poll'] = 'Ce message ne contient pas de sondage.';
$lang['Already_voted'] = 'Vous avez d&eacute;j&agrave; vot&eacute; pour ce sondage.';
$lang['No_vote_option'] = 'Vous devez choisir une option pour voter.';

$lang['Add_poll'] = 'Ajouter un sondage';
$lang['Add_poll_explain'] = 'Si vous ne voulez pas ajouter de sondage &agrave; votre message, laissez ce champ vide.';
$lang['Poll_question'] = 'Question du sondage';
$lang['Poll_option'] = 'Option du sondage';
$lang['Add_option'] = 'Ajouter une option';
$lang['Update'] = 'Mettre &agrave; jour';
$lang['Delete'] = 'Effacer';
$lang['Poll_for'] = 'Dur&eacute;e du sondage';
$lang['Days'] = 'Jours'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = 'Entrez 0 ou laissez vide pour un sondage permanent';
$lang['Delete_poll'] = 'Effacer sondage';

$lang['Disable_HTML_post'] = 'D&eacute;sactiver HTML';
$lang['Disable_BBCode_post'] = 'D&eacute;sactiver BBCode';
$lang['Disable_Smilies_post'] = 'D&eacute;sactiver Smilies';

$lang['HTML_is_ON'] = 'HTML est <u>ON</u>';
$lang['HTML_is_OFF'] = 'HTML est <u>OFF</u>';
$lang['BBCode_is_ON'] = '%sBBCode%s est <u>ON</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s est <u>OFF</u>';
$lang['Smilies_are_ON'] = 'Les Smileys sont <u>ON</u>';
$lang['Smilies_are_OFF'] = 'Les Smileys sont <u>OFF</u>';

$lang['Attach_signature'] = 'joindre ma Signature (les signatures sont g&eacute;r&eacute;es dans le Panneau de Controle)';
$lang['Notify'] = 'Envoyez-moi un Email quand une r&eacute;ponse est post&eacute;e';

$lang['Stored'] = 'Votre message a bien &eacute;t&eacute; envoy&eacute;.';
$lang['Deleted'] = 'Votre message a bien &eacute;t&eacute; effac&eacute;.';
$lang['Poll_delete'] = 'Votre sondage a bien &eacute;t&eacute; effac&eacute;.';
$lang['Vote_cast'] = 'Votre vote a &eacute;t&eacute; pris en compte.';

$lang['Null_vote_cast'] = 'Votre vote a &eacute;t&eacute; comptabilis&eacute;';
$lang['Click_view_results'] = 'Cliquez %sIci%s pour voir les r&eacute;sultats du sondage';
$lang['Null_vote'] = 'Vote blanc';

$lang['Topic_reply_notification'] = 'Notification de reponse au Sujet';

$lang['bbcode_b_help'] = 'Texte en gras: [b]text[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Texte en italique: [i]text[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Texte soulign&eacute;: [u]text[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Texte Cit&eacute;: [quote]text[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Afficher du Code: [code]code[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Liste: [list]text[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Liste ordonn&eacute;e: [list=]text[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Ins&eacute;rer une Image: [img ( | =left | =right )]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Ins&eacute;rer un Lien: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Fermer toutes les balises BBCode ouvertes';
$lang['bbcode_s_help'] = 'Couleur du Texte: [color=red]text[/color] Astuce: vous pouvez aussi utiliser color=#FF0000';
$lang['bbcode_f_help'] = 'Taille du Texte: [size=x-small]small text[/size]';
$lang['bbcode_sc_help'] = 'Cr&eacute;ateur de smiley: [schild=1]text[/schild] Astuce: cr&eacute;e un Smiley a partir d\'un signe';

$lang['Emoticons'] = 'Smileys';
$lang['Emoticons_disable'] = 'D&eacute;sol&eacute;, les Smileys ont &eacute;t&eacute; d&eacute;sactiv&eacute;s.';
$lang['Smilie_creator'] = 'Cr&eacute;ateur de Smileys';
$lang['More_emoticons'] = 'Voir plus de Smileys';

$lang['Font_color'] = 'Couleur de police';
$lang['color_default'] = 'Couleur par d&eacute;faut';
$lang['color_dark_red'] = 'Rouge fonc&eacute;';
$lang['color_red'] = 'Rouge';
$lang['color_orange'] = 'Orange';
$lang['color_brown'] = 'Marron';
$lang['color_yellow'] = 'Jaune';
$lang['color_green'] = 'Vert';;
$lang['color_olive'] = 'Olive';
$lang['color_cyan'] = 'Cyan';
$lang['color_blue'] = 'Bleu';
$lang['color_dark_blue'] = 'Bleu fonc&eacute;';
$lang['color_indigo'] = 'Indigo';
$lang['color_violet'] = 'Violet';
$lang['color_white'] = 'Blanc';
$lang['color_black'] = 'Noir';

$lang['Font_size'] = 'Taille de police';
$lang['font_tiny'] = 'Tr&egrave;s petite';
$lang['font_small'] = 'Petite';
$lang['font_normal'] = 'Normale';
$lang['font_large'] = 'Grande';
$lang['font_huge'] = 'Enorme';

$lang['Close_Tags'] = 'Fermer les Balises';
$lang['Styles_tip'] = 'Astuce: appliquez les balises rapidement en s&eacute;lectionnant le texte auparavant.';

//
// Private Messaging
//
$lang['Private_Messaging'] = 'Messagerie Priv&eacute;';
$lang['Login_check_pm'] = 'Messages Priv&eacute;s';

$lang['New_nsnd_pms'] = '<b>%d</b> nouveaux messages priv&eacute;s'; // You have 2 new messages no sound/marquee
$lang['New_pms'] = "<b>%d</b> <span class=pm>*<blink> <marquee width=50 behavior=alternate>NOUVEAUX</marquee> </blink>*</span> messages<EMBED SRC=$snd LOOP=false HIDDEN=true VOLUME=50 AUTOSTART=true WIDTH=0 HEIGHT=0 NAME=foobar MASTERSOUND>"; // You have 2 new message
$lang['New_nsnd_pm'] = '<b>%d</b> nouveau message'; // You have 1 new message no sound/marquee
$lang['New_pm'] = "<b>%d</b> <span class=pm>*<blink> <marquee width=50 behavior=alternate>NOUVEAU</marquee> </blink>*</span> message<EMBED SRC=$snd LOOP=false HIDDEN=true VOLUME=50 AUTOSTART=true WIDTH=0 HEIGHT=0 NAME=foobar MASTERSOUND>"; // You have 1 new message
$lang['No_new_pm'] = '<b>0</b> nouveau message';
$lang['Unread_pms'] = '<b>0</b> messages non-lus';
$lang['Unread_pm'] = '<b>0</b> message non-lu';
$lang['No_unread_pm'] = '<b>0</b> message non-lu';
$lang['You_new_pm'] = 'Un nouveau Message Priv&eacute; vous attends dans la Boite de R&eacute;ception';
$lang['You_new_pms'] = 'De nouveaux Messages Priv&eacute;s vous attendent dans la Boite de R&eacute;ception';
$lang['You_no_new_pm'] = 'Pas de nouveaux Messages Priv&eacute;s';

$lang['Read_pm'] = 'message lu';
$lang['Post_new_pm'] = 'Envoyer un Message Priv&eacute;';
$lang['Post_reply_pm'] = 'R&eacute;pondre au message';
$lang['Post_quote_pm'] = 'Citer message';
$lang['Edit_pm'] = 'Editer message';

$lang['Inbox'] = 'Boite de r&eacute;ception';
$lang['Outbox'] = 'Boite d\'envoi';
$lang['Savebox'] = 'Sauvegarde';
$lang['Sentbox'] = 'El&eacute;ments envoy&eacute;s';
$lang['Flag'] = 'Drapeau';
$lang['Subject'] = 'Sujet';
$lang['From'] = 'De';
$lang['To'] = 'A';
$lang['Date'] = 'Date';
$lang['Mark'] = 'Cocher';
$lang['Sent'] = 'Envoy&eacute;';
$lang['Saved'] = 'Sauv&eacute;';
$lang['Delete_marked'] = 'Effacer Coch&eacute;s';
$lang['Delete_all'] = 'Tout effacer';
$lang['Save_marked'] = 'Sauvegarder Coch&eacute;s';
$lang['Save_message'] = 'Sauvegarder message';
$lang['Delete_message'] = 'Effacer Message';

$lang['Display_messages'] = 'Afficher les Messages des pr&eacute;c&eacute;dents'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Tout les Messages';

$lang['No_messages_folder'] = 'Pas de message dans ce dossier.';

$lang['PM_disabled'] = 'D&eacute;sol&eacute;, la Messagerie Priv&eacute;e est d&eacute;sactiv&eacute;e.';
$lang['Cannot_send_privmsg'] = 'D&eacute;sol&eacute;, les Administrateurs ne vous autorisent pas &agrave; envoyer de Messages Priv&eacute;s.';
$lang['No_to_user'] = 'Vous devez sp&eacute;cifier &agrave; qui envoyer le message.';
$lang['No_such_user'] = 'D&eacute;sol&eacute; cet utilisateur n\'existe pas.';

$lang['Disable_HTML_pm'] = 'D&eacute;sactiver HTML dans le message';
$lang['Disable_BBCode_pm'] = 'D&eacute;sactiver BBCode dans le message';
$lang['Disable_Smilies_pm'] = 'D&eacute;sactiver Smileys dans le message';

$lang['Message_sent'] = 'Message envoy&eacute;.';

$lang['Click_return_inbox'] = 'Cliquez %sIci%s pour revenir &agrave; Boite de R&eacute;ception';
$lang['Click_return_index'] = 'Cliquez %sIci%s pour revenir &agrave; l\'Index';

$lang['Send_a_new_message'] = 'Envoyer un Message Priv&eacute;';
$lang['Send_a_reply'] = 'R&eacute;pondre &agrave; un Message Priv&eacute;';
$lang['Edit_message'] = 'Editer un Message Priv&eacute;';

$lang['Notification_subject'] = 'Un nouveau Message Priv&eacute; est arriv&eacute; !';

$lang['Find_username'] = 'Trouver un utilisateur';
$lang['Find'] = 'Trouver';
$lang['No_match'] = 'Pas de r&eacute;ponse trouv&eacute;e.';

$lang['No_post_id'] = 'ID Message non sp&eacute;cifi&eacute;';
$lang['No_such_folder'] = 'Ce dossier n\'existe pas';
$lang['No_folder'] = 'Pas de dossier sp&eacute;cifi&eacute;';

$lang['Mark_all'] = 'Cocher Tout';
$lang['Unmark_all'] = 'D&eacute;cocher Tout';

$lang['Confirm_delete_pm'] = 'Etes-vous certain de vouloir effacer ce message?';
$lang['Confirm_delete_pms'] = 'Etes-vous certain de vouloir effacer ces messages?';

$lang['Inbox_size'] = 'Boite de R&eacute;ception est pleine &agrave; %d%%'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'El&eacute;ments Envoy&eacute; est plein &agrave; %d%%';
$lang['Savebox_size'] = 'Sauvegarde est plein &agrave; %d%%';

$lang['Click_view_privmsg'] = 'Cliquez %sIci%s pour aller &agrave; Boite de R&eacute;ception';

//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Profil de %s'; // %s is username
$lang['View_main_profile'] = 'Profil principal de %s'; // %s is username
$lang['About_user'] = 'Tout sur %s'; // %s is username
$lang['Avatar'] = 'Avatar';

$lang['Website'] = 'Site Internet';
$lang['Location'] = 'Situation g&eacute;ographique';
$lang['Contact'] = 'Contact';
$lang['Email_address'] = 'Adresse Email';
$lang['Send_private_message'] = 'Envoyer un Message Priv&eacute;';
$lang['Hidden_email'] = '[ Cach&eacute; ]';
$lang['Interests'] = 'Centres d\'int&eacute;ret';
$lang['Occupation'] = 'Activit&eacute;s';
$lang['Poster_rank'] = 'Niveau';

$lang['Total_posts'] = 'Total Messages envoy&eacute;s';
$lang['User_post_pct_stats'] = '%.2f%% de tout les messages'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f messages par jour'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Trouver tout les messages de %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'D&eacute;sol&eacute;, cet utilisateur n\'existe pas.';
$lang['Wrong_Profile'] = 'Vous ne pouvez pas modifi&eacute; un Profil autre que le votre.';

$lang['Send_email_msg'] = 'Envoyer un Email';
$lang['No_user_specified'] = 'Aucun nom d\'utilisateur sp&eacute;cifi&eacute;';
$lang['User_prevent_email'] = 'Cet utilisateur ne d&eacute;sire pas recevoir d\'Email. Essayez de lui envoyer un Message Priv&eacute;.';
$lang['User_not_exist'] = 'Cet utilisateur n\'existe pas';
$lang['CC_email'] = 'Envoyez-vous une copie de l\'Email';
$lang['Email_message_desc'] = 'Ce message sera envoy&eacute; en Texte, inutile de mettre du HTML ou BBCode. L\'adresse de retour de ce message sera votre adresse Email.';
$lang['Flood_email_limit'] = 'Vous ne pouvez pas envoyer un autre Email maintenant. Re-essayez plus tard.';
$lang['Recipient'] = 'Recipient';
$lang['Email_sent'] = 'L\'Email a &eacute;t&eacute; envoy&eacute;.';
$lang['Send_email'] = 'Envoyer Email';
$lang['Empty_subject_email'] = 'Vous devez sp&eacute;cifier un sujet pour cet Email.';
$lang['Empty_message_email'] = 'Vous devez entrer un message.';

//
// Search
//
$lang['Search_query'] = 'Requete de Recherche';
$lang['Search_options'] = 'Options de Recherche';

$lang['Search_keywords'] = 'Rechercher les mot-cl&eacute;';
$lang['Search_keywords_explain'] = 'Vous pouvez utiliser <u>AND</u> pour d&eacute;finir des mots qui DOIVENT figurer dans les r&eacute;sultats, <u>OR</u> pour d&eacute;finir des mots qui PEUVENT figurer dans les r&eacute;sultats et <u>NOT</u> pour d&eacute;finir des mots qui NE DOIVENT PAS figurer dans les r&eacute;sultats. Utilisez * comme Joker';
$lang['Search_author'] = 'Rechercher un Auteur';
$lang['Search_author_explain'] = 'Utilisez * comme Joker';

$lang['Search_for_any'] = 'Rechercher CHACUN des mots ou effectuer la requete sp&eacute;cifi&eacute;e';
$lang['Search_for_all'] = 'Rechercher TOUT les mots';
$lang['Search_title_msg'] = 'Dans les titres de sujets et les corps de messages';
$lang['Search_msg_only'] = 'Dans les corps de messages uniquement';

$lang['Search_only_bluecards'] = 'Chercher uniquement les messages signal&eacute;s';
$lang['Topic_starter'] = '1er message du sujet';

$lang['Return_first'] = 'Afficher les '; // followed by xxx characters in a select box
$lang['characters_posts'] = ' premiers caract&egrave;res';

$lang['Search_previous'] = 'Rechercher dans les derniers'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Trier par';
$lang['Sort_Time'] = 'Date d\'envoi';
$lang['Sort_Post_Subject'] = 'Titre des Messages';
$lang['Sort_Topic_Title'] = 'Titre des Sjuets';
$lang['Sort_Author'] = 'Auteur';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'Afficher les r&eacute;sultats en';
$lang['All_available'] = 'Tout disponibles';
$lang['No_searchable_forums'] = 'Vous n\'avez pas la permission de chercher les Forums sur ce site.';

$lang['No_search_match'] = 'Aucun Sujet ou Message ne correspond &agrave; vos crit&egrave;res';
$lang['Found_search_match'] = ' %d r&eacute;sultat trouv&eacute;'; // eg. Search found 1 match
$lang['Found_search_matches'] = ' %d r&eacute;sultat trouv&eacute;s'; // eg. Search found 24 matches
$lang['Search_Flood_Error'] = 'Vous ne pouvez pas lancer une autre recherche aussi rapidement. Attendez quelques instants.';

$lang['Close_window'] = 'Fermer la fenetre';
$lang['Search_for'] = 'Rechercher dans ce Forum';

//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'D&eacute;sol&eacute; seuls les %s peuvent poster des Annonces dans ce Forum.';
$lang['Sorry_auth_sticky'] = 'D&eacute;sol&eacute; seuls les %s peuvent envoyer des Post-It dans ce Forum.';
$lang['Sorry_auth_read'] = 'D&eacute;sol&eacute; seuls les %s peuvent lire les Sujets de ce Forum.';
$lang['Sorry_auth_post'] = 'D&eacute;sol&eacute; seuls les %s peuvent poster des Sujets dans ce Forum.';
$lang['Sorry_auth_reply'] = 'D&eacute;sol&eacute; seuls les %s peuvent r&eacute;pondre aux messages dans ce Forum.';
$lang['Sorry_auth_edit'] = 'D&eacute;sol&eacute; seuls les %s peuvent modifier les Messages dans ce Forum.';
$lang['Sorry_auth_delete'] = 'D&eacute;sol&eacute; seuls les %s peuvent effacer les Messages dans ce Forum.';
$lang['Sorry_auth_vote'] = 'D&eacute;sol&eacute; seuls les %s peuvent voter aux Sondages dans ce Forum.';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>Invit&eacute;</b>';
$lang['Auth_Registered_Users'] = '<b>Membres</b>';
$lang['Auth_Users_granted_access'] = '<b>Membres avec acc&egrave;s sp&eacute;ciaux</b>';
$lang['Auth_Moderators'] = '<b>Mod&eacute;rateurs</b>';
$lang['Auth_Administrators'] = '<b>Administrateurs</b>';

$lang['Not_Moderator'] = 'Vous n\'&ecirc;tes pas Mod&eacute;rateur de ce Forum.';
$lang['Not_Authorised'] = 'Vous n\'&ecirc;tes pas autoris&eacute; &agrave acc&eacute;der &agrave; cette page.';

$lang['You_been_banned'] = 'Vous avez &eacute;t&eacute; banni de ce forum.<br />Contactez l\'Administrateur pour plus d\'informations.';

//
// View Single Post
//
$lang['View_single_post'] = 'Voir Message';
$lang['Viewing_post'] = 'Voir Message';

// PayPal IP Group Subscriptions
$lang['Click_return_subscribe_lw'] = 'Cliquez %sIci%s pour s&eacute;lectionner un Groupe &agrave; rejoindre. Une participation financi&egrave;re vous sera demand&eacute;e.';

//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Toutes les heures sont au format %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 Heures';
$lang['-11'] = 'GMT - 11 Heures';
$lang['-10'] = 'GMT - 10 Heures';
$lang['-9.5'] = 'GMT - 9.5 Heures';
$lang['-9'] = 'GMT - 9 Heures';
$lang['-8'] = 'GMT - 8 Heures';
$lang['-7'] = 'GMT - 7 Heures';
$lang['-6'] = 'GMT - 6 Heures';
$lang['-5'] = 'GMT - 5 Heures';
$lang['-4'] = 'GMT - 4 Heures';
$lang['-3.5'] = 'GMT - 3.5 Heures';
$lang['-3'] = 'GMT - 3 Heures';
$lang['-2'] = 'GMT - 2 Heures';
$lang['-1'] = 'GMT - 1 Heure';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 Heure';
$lang['2'] = 'GMT + 2 Heures';
$lang['3'] = 'GMT + 3 Heures';
$lang['3.5'] = 'GMT + 3.5 Heures';
$lang['4'] = 'GMT + 4 Heures';
$lang['4.5'] = 'GMT + 4.5 Heures';
$lang['5'] = 'GMT + 5 Heures';
$lang['5.5'] = 'GMT + 5.5 Heures';
$lang['5.75'] = 'GMT + 5.75 Heures';
$lang['6'] = 'GMT + 6 Heures';
$lang['6.5'] = 'GMT + 6.5 Heures';
$lang['7'] = 'GMT + 7 Heures';
$lang['8'] = 'GMT + 8 Heures';
$lang['8.75'] = 'GMT + 8.75 Heures';
$lang['9'] = 'GMT + 9 Heures';
$lang['9.5'] = 'GMT + 9.5 Heures';
$lang['10'] = 'GMT + 10 Heures';
$lang['10.5'] = 'GMT + 10.5 Heures';
$lang['11'] = 'GMT + 11 Heures';
$lang['11.5'] = 'GMT + 11.5 Heures';
$lang['12'] = 'GMT + 12 Heures';
$lang['12.75'] = 'GMT + 12.75 Heures';
$lang['13'] = 'GMT + 13 Heures';
$lang['14'] = 'GMT + 14 Heures';

$lang['datetime']['Sunday'] = 'Dimanche';
$lang['datetime']['Monday'] = 'Lundi';
$lang['datetime']['Tuesday'] = 'Mardi';
$lang['datetime']['Wednesday'] = 'Mercredi';
$lang['datetime']['Thursday'] = 'Jeudi';
$lang['datetime']['Friday'] = 'Vendredi';
$lang['datetime']['Saturday'] = 'Samedi';
$lang['datetime']['Sun'] = 'Dim';
$lang['datetime']['Mon'] = 'Lun';
$lang['datetime']['Tue'] = 'Mar';
$lang['datetime']['Wed'] = 'Mer';
$lang['datetime']['Thu'] = 'Jeu';
$lang['datetime']['Fri'] = 'Ven';
$lang['datetime']['Sat'] = 'Sam';
$lang['datetime']['January'] = 'Janvier';
$lang['datetime']['February'] = 'F&eacute;vrier';
$lang['datetime']['March'] = 'Mars';
$lang['datetime']['April'] = 'Avril';
$lang['datetime']['May'] = 'Mai';
$lang['datetime']['June'] = 'Juin';
$lang['datetime']['July'] = 'Juillet';
$lang['datetime']['August'] = 'Aout';
$lang['datetime']['September'] = 'Septembre';
$lang['datetime']['October'] = 'Octobre';
$lang['datetime']['November'] = 'Novembre';
$lang['datetime']['December'] = 'D&eacute;cembre';
$lang['datetime']['Jan'] = 'Jan';
$lang['datetime']['Feb'] = 'Fev';
$lang['datetime']['Mar'] = 'Mar';
$lang['datetime']['Apr'] = 'Avr';
$lang['datetime']['May'] = 'Mai';
$lang['datetime']['Jun'] = 'Jui';
$lang['datetime']['Jul'] = 'Juil';
$lang['datetime']['Aug'] = 'Aou';
$lang['datetime']['Sep'] = 'Sep';
$lang['datetime']['Oct'] = 'Oct';
$lang['datetime']['Nov'] = 'Nov';
$lang['datetime']['Dec'] = 'Dec';

$lang['Login_attempts_exceeded'] = 'Vous avez atteint les %s tentatives de connexion. Vous devez maintenant attendre %s minutes avant de pouvoir essayer &agrave; nouveau.';

//
// Serverload & Unique Hits
//
$lang['Pages_served'] = ' pages charg&eacute;es ces 5 derni&egrave;res minutes';
$lang['Unique_hits'] = 'unique hits ces derni&egrave;res %s heures';
$lang['ON'] = 'ON'; // This is for GZip compression
$lang['OFF'] = 'OFF';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Information';
$lang['Critical_Information'] = 'Information Critique';

$lang['General_Error'] = 'Erreur G&eacute;n&eacute;rale';
$lang['Critical_Error'] = 'Erreur Critique';
$lang['An_error_occured'] = 'Une erreur est arriv&eacute;e';
$lang['A_critical_error'] = 'Une erreur critiques est arriv&eacute;e';

//
// That's all, Folks!
// -------------------------------------------------

?>
