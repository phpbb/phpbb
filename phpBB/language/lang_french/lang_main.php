<?php
/***************************************************************************
 *                            lang_main.php [French]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$
 *
 ****************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

//
//	Translation produced by Helix
//	http://www.phpbb-fr.com/
//


// setlocale(LC_ALL, "fr");
$lang['ENCODING'] = "ISO-8859-1";
$lang['DIRECTION'] = "ltr"; // do not translate this, it's the Left to Right direction of text
$lang['LEFT'] = "left"; // do not translate this, it's the normal 'left' direction of text
$lang['RIGHT'] = "right"; // do not translate this, it's the normal 'right' direction of text
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format


//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Category'] = "Catégorie";
$lang['Topic'] = "Sujet";
$lang['Topics'] = "Sujets";
$lang['Replies'] = "Réponses";
$lang['Views'] = "Vus";
$lang['Post'] = "Message";
$lang['Posts'] = "Messages";
$lang['Posted'] = "Posté le";
$lang['Username'] = "Nom d'utilisateur";
$lang['Password'] = "Mot de passe";
$lang['Email'] = "Email";
$lang['Poster'] = "Poster";
$lang['Author'] = "Auteur";
$lang['Time'] = "Temps";
$lang['Hours'] = "Heures";
$lang['Message'] = "Message";

$lang['1_Day'] = "1 Jour";
$lang['7_Days'] = "7 Jours";
$lang['2_Weeks'] = "2 Semaines";
$lang['1_Month'] = "1 Mois";
$lang['3_Months'] = "3 Mois";
$lang['6_Months'] = "6 Mois";
$lang['1_Year'] = "1 An";

$lang['Go'] = "Aller";
$lang['Jump_to'] = "Sauter vers";
$lang['Submit'] = "Envoyer";
$lang['Reset'] = "Réinitialiser";
$lang['Cancel'] = "Annuler";
$lang['Preview'] = "Prévisualisation";
$lang['Confirm'] = "Confirmer";
$lang['Spellcheck'] = "Vérificateur d'orthographe";
$lang['Yes'] = "Oui";
$lang['No'] = "Non";
$lang['Enabled'] = "Activé";
$lang['Disabled'] = "Désactivé";
$lang['Error'] = "Erreur";

$lang['Next'] = "Suivante";
$lang['Previous'] = "Précédente";
$lang['Goto_page'] = "Aller à la page";
$lang['Joined'] = "Inscrit le";
$lang['IP_Address'] = "Adresse IP";

$lang['Select_forum'] = "Sélectionner un forum";
$lang['View_latest_post'] = "Voir le dernier message";
$lang['View_newest_post'] = "Voir le message le plus récent";
$lang['Page_of'] = "Page <b>%d</b> sur <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "Numéro ICQ";
$lang['AIM'] = "Adresse AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Index du Forum";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Poster un nouveau sujet";
$lang['Reply_to_topic'] = "Répondre au sujet";
$lang['Reply_with_quote'] = "Répondre en citant";

$lang['Click_return_topic'] = "Cliquez %sici%s pour retourner au sujet de discussion"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Cliquez %sici%s pour réessayer";
$lang['Click_return_forum'] = "Cliquez %sici%s pour retourner au forum";
$lang['Click_view_message'] = "Cliquez %sici%s pour voir votre message";
$lang['Click_return_modcp'] = "Cliquez %sici%s pour retourner au Panneau de Contrôle du Modérateur";
$lang['Click_return_group'] = "Cliquez %sici%s pour retourner aux informations du groupe";

$lang['Admin_panel'] = "Aller au Panneau d'Administration";

$lang['Board_disable'] = "Désolé, mais ce forum est actuellement indisponible, veuillez réessayer plus tard";


//
// Global Header strings
//
$lang['Registered_users'] = "Utilisateurs enregistrés:";
$lang['Browsing_forum'] = "Utilisateurs parcourant actuellement ce forum:";
$lang['Online_users_zero_total'] = "Il y a en tout <b>0</b> utilisateur en ligne :: ";
$lang['Online_users_total'] = "Il y a en tout <b>%d</b> utilisateurs en ligne :: ";
$lang['Online_user_total'] = "Il y a en tout <b>%d</b> utilisateur en ligne :: ";
$lang['Reg_users_zero_total'] = "0 Enregistré, ";
$lang['Reg_users_total'] = "%d Enregistrés, ";
$lang['Reg_user_total'] = "%d Enregistré, ";
$lang['Hidden_users_zero_total'] = "0 Invisible et ";
$lang['Hidden_users_total'] = "%d Invisibles et ";
$lang['Hidden_user_total'] = "%d Invisible et ";
$lang['Guest_users_zero_total'] = "0 Invité";
$lang['Guest_users_total'] = "%d Invités";
$lang['Guest_user_total'] = "%d Invité";
$lang['Record_online_users'] = "Le record du nombre d'utilisateurs en ligne est de <b>%s</b> le %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sAdministrateur%s";
$lang['Mod_online_color'] = "%sModérateur%s";

$lang['You_last_visit'] = "Dernière visite le %s"; // %s replaced by date/time
$lang['Current_time'] = "La date/heure actuelle est %s"; // %s replaced by date/time
$lang['Search_new'] = "Voir les nouveaux messages depuis votre dernière visite";
$lang['Search_your_posts'] = "Voir ses messages";
$lang['Search_unanswered'] = "Voir les messages sans réponses";
$lang['Register'] = "S'enregistrer";
$lang['Profile'] = "Profil";
$lang['Edit_profile'] = "Editer votre profil";
$lang['Search'] = "Rechercher";
$lang['Memberlist'] = "Liste des Membres";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Guide du BBCode";
$lang['Usergroups'] = "Groupes d'utilisateurs";
$lang['Last_Post'] = "Derniers Messages";
$lang['Moderator'] = "Modérateur";
$lang['Moderators'] = "Modérateurs";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Nos membres ont posté un total de <b>0</b> message"; // Number of posts
$lang['Posted_articles_total'] = "Nos membres ont posté un total de <b>%d</b> messages"; // Number of posts
$lang['Posted_article_total'] = "Nos membres ont posté un total de <b>%d</b> message"; // Number of posts
$lang['Registered_users_zero_total'] = "Nous avons <b>0</b> utilisateur enregistré"; // # registered users
$lang['Registered_users_total'] = "Nous avons <b>%d</b> membres enregistrés"; // # registered users
$lang['Registered_user_total'] = "Nous avons <b>%d</b> membre enregistré"; // # registered users
$lang['Newest_user'] = "L'utilisateur enregistré le plus récent est <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Pas de nouveaux messages depuis votre dernière visite";
$lang['No_new_posts'] = "Pas de nouveaux messages";
$lang['New_posts'] = "Nouveaux messages";
$lang['New_post'] = "Nouveau message";
$lang['No_new_posts_hot'] = "Pas de nouveaux messages [ Populaire ]";
$lang['New_posts_hot'] = "Nouveaux messages [ Populaire ]";
$lang['No_new_posts_locked'] = "Pas de nouveaux messages [ Verrouillé ]";
$lang['New_posts_locked'] = "Nouveaux messages [ Verrouillé ]";
$lang['Forum_is_locked'] = "Forum Verrouillé";


//
// Login
//
$lang['Enter_password'] = "Veuillez entrer votre nom d'utilisateur et votre mot de passe pour vous connecter";
$lang['Login'] = "Connexion";
$lang['Logout'] = "Déconnexion";

$lang['Forgotten_password'] = "J'ai oublié mon mot de passe";

$lang['Log_me_in'] = "Se connecter automatiquement à chaque visite";

$lang['Error_login'] = "Vous avez spécifié un nom d'utilisateur incorrect ou inactif ou un mot de passe invalide";


//
// Index page
//
$lang['Index'] = "Index";
$lang['No_Posts'] = "Pas de Messages";
$lang['No_forums'] = "Ce Forum n'a pas de sous-forums";

$lang['Private_Message'] = "Message Privé";
$lang['Private_Messages'] = "Messages Privés";
$lang['Who_is_Online'] = "Qui est en ligne ?";

$lang['Mark_all_forums'] = "Marquer tous les forums comme lus";
$lang['Forums_marked_read'] = "Tous les forums ont été marqués comme lus";


//
// Viewforum
//
$lang['View_forum'] = "Voir le Forum";

$lang['Forum_not_exist'] = "Le forum que vous avez sélectionné n'existe pas";
$lang['Reached_on_error'] = "Vous avez atteint cette page par erreur";

$lang['Display_topics'] = "Montrer les sujets depuis";
$lang['All_Topics'] = "Tous les Sujets";

$lang['Topic_Announcement'] = "<b>Annonce:</b>";
$lang['Topic_Sticky'] = "<b>Post-it:</b>";
$lang['Topic_Moved'] = "<b>Déplacé:</b>";
$lang['Topic_Poll'] = "<b>[ Sondage ]</b>";

$lang['Mark_all_topics'] = "Marquez tous les sujets comme lus";
$lang['Topics_marked_read'] = "Les sujets de forum sont à présent marqués comme lus.";

$lang['Rules_post_can'] = "Vous <b>pouvez</b> poster de nouveaux sujets dans ce forum";
$lang['Rules_post_cannot'] = "Vous <b>ne pouvez pas</b> poster de nouveaux sujets dans ce forum";
$lang['Rules_reply_can'] = "Vous <b>pouvez</b> répondre aux sujets dans ce forum";
$lang['Rules_reply_cannot'] = "Vous <b>ne pouvez pas</b> répondre aux sujets dans ce forum";
$lang['Rules_edit_can'] = "Vous <b>pouvez</b> éditer vos messages dans ce forum";
$lang['Rules_edit_cannot'] = "Vous <b>ne pouvez pas</b> éditer vos messages dans ce forum";
$lang['Rules_delete_can'] = "Vous <b>pouvez</b> supprimer vos messages dans ce forum";
$lang['Rules_delete_cannot'] = "Vous <b>ne pouvez pas</b> supprimer vos messages dans ce forum";
$lang['Rules_vote_can'] = "Vous <b>pouvez</b> voter dans les sondages de ce forum";
$lang['Rules_vote_cannot'] = "Vous <b>ne pouvez pas</b> voter dans les sondages de ce forum";
$lang['Rules_moderate'] = "Vous <b>pouvez</b> %smodérer ce forum%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Il n'y a pas de messages dans ce forum<br />Cliquez sur le lien <b>Poster un Nouveau Sujet</b> sur cette page pour en poster un";

//
// Viewtopic
//
$lang['View_topic'] = "Voir le sujet";

$lang['Guest'] = 'Invité';
$lang['Post_subject'] = "Sujet du message";
$lang['View_next_topic'] = "Voir le sujet suivant";
$lang['View_previous_topic'] = "Voir le sujet précédent";
$lang['Submit_vote'] = "Envoyer le vote";
$lang['View_results'] = "Voir les résultats";

$lang['No_newer_topics'] = "Il n'y a pas de nouveaux sujets dans ce forum";
$lang['No_older_topics'] = "Il n'y a pas d'anciens sujets dans ce forum";
$lang['Topic_post_not_exist'] = "Le sujet ou message que vous recherchez n'existe pas";
$lang['No_posts_topic'] = "Il n'existe pas de messages pour ce sujet";

$lang['Display_posts'] = "Montrer les messages depuis";
$lang['All_Posts'] = "Tous les messages";
$lang['Newest_First'] = "Le plus récent en premier";
$lang['Oldest_First'] = "Le plus ancien en premier";

$lang['Back_to_top'] = "Revenir en haut";

$lang['Read_profile'] = "Voir le profil de l'utilisateur"; 
$lang['Send_email'] = "Envoyer un email à l'utilisateur";
$lang['Visit_website'] = "Visiter le site web du posteur";
$lang['ICQ_status'] = "Statut ICQ";
$lang['Edit_delete_post'] = "Editer/Supprimer ce message";
$lang['View_IP'] = "Voir l'IP du posteur";
$lang['Delete_post'] = "Supprimer ce message";

$lang['wrote'] = "a écrit"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Citation"; // comes before bbcode quote output.
$lang['Code'] = "Code"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Dernière édition par %s le %s, édité %d fois"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Dernière édition par %s le %s, édité %d fois"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Verrouiller le sujet";
$lang['Unlock_topic'] = "Dévérouiller le sujet";
$lang['Move_topic'] = "Déplacer le sujet";
$lang['Delete_topic'] = "Supprimer le sujet";
$lang['Split_topic'] = "Diviser le sujet";

$lang['Stop_watching_topic'] = "Arrêter de surveiller ce sujet";
$lang['Start_watching_topic'] = "Surveiller les réponses de ce sujet";
$lang['No_longer_watching'] = "Vous ne surveillez plus ce sujet";
$lang['You_are_watching'] = "Vous surveillez ce sujet à présent";

$lang['Total_votes'] = "Total des votes";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Corps du message";
$lang['Topic_review'] = "Revue du sujet";

$lang['No_post_mode'] = "Mode du sujet non spécifié"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Poster un nouveau sujet";
$lang['Post_a_reply'] = "Poster une réponse";
$lang['Post_topic_as'] = "Poster le sujet en tant que";
$lang['Edit_Post'] = "Editer le sujet";
$lang['Options'] = "Options";

$lang['Post_Announcement'] = "Annonce";
$lang['Post_Sticky'] = "Post-it";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "Etes-vous sûr de vouloir supprimer ce message ?";
$lang['Confirm_delete_poll'] = "Etes-vous sûr de vouloir supprimer ce sondage ?";

$lang['Flood_Error'] = "Vous ne pouvez pas poster un autre sujet en si peu de temps après le dernier, veuillez réessayer dans un court instant";
$lang['Empty_subject'] = "Vous devez préciser le nom du sujet avant de pouvoir poster un nouveau sujet";
$lang['Empty_message'] = "Vous devez entrer un message avant de poster";
$lang['Forum_locked'] = "Ce forum est verrouillé, vous ne pouvez pas poster, ni répondre, ni éditer les sujets";
$lang['Topic_locked'] = "Ce sujet est verrouillé, vous ne pouvez pas éditer les messages ou faire de réponses";
$lang['No_post_id'] = "Vous devez sélectionner un message à éditer";
$lang['No_topic_id'] = "Vous devez sélectionner le sujet auquel répondre";
$lang['No_valid_mode'] = "Vous pouvez seulement poster, répondre, éditer ou citer des messages, veuillez revenir en arrière et réessayer";
$lang['No_such_post'] = "Il n'y a pas de message de ce type, veuillez revenir en arrière et réessayer";
$lang['Edit_own_posts'] = "Désolé, mais vous pouvez seulement éditer vos propres messages";
$lang['Delete_own_posts'] = "Désolé, mais vous ne pouvez seulement supprimer vos propres messages";
$lang['Cannot_delete_replied'] = "Désolé, mais vous ne pouvez pas supprimer un message ayant eu des réponses";
$lang['Cannot_delete_poll'] = "Désolé, mais vous ne pouvez pas supprimer un sondage actif";
$lang['Empty_poll_title'] = "Vous devez entrer un titre pour le sondage";
$lang['To_few_poll_options'] = "Vous devez au moins entrer deux options pour le sondage";
$lang['To_many_poll_options'] = "Vous avez entré trop d'options pour le sondage";
$lang['Post_has_no_poll'] = "Ce sujet n'a pas de sondage";

$lang['Add_poll'] = "Ajouter un sondage";
$lang['Add_poll_explain'] = "Si vous ne voulez pas ajouter de sondage à votre sujet, laissez ces champs vides";
$lang['Poll_question'] = "Question du sondage";
$lang['Poll_option'] = "Option du sondage";
$lang['Add_option'] = "Ajouter l'option";
$lang['Update'] = "Mettre à jour";
$lang['Delete'] = "Supprimer";
$lang['Poll_for'] = "Sondage pendant";
$lang['Days'] = "Jours"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Entrez 0 ou laissez vide pour ne jamais terminer le sondage ]";
$lang['Delete_poll'] = "Supprimer le sondage";

$lang['Disable_HTML_post'] = "Désactiver le HTML dans ce message";
$lang['Disable_BBCode_post'] = "Désactiver le BBCode dans ce message";
$lang['Disable_Smilies_post'] = "Désactiver les Smilies dans ce message";

$lang['HTML_is_ON'] = "Le HTML est <u>Activé</u>";
$lang['HTML_is_OFF'] = "Le HTML est <u>Désactivé</u>";
$lang['BBCode_is_ON'] = "Le %sBBCode%s est <u>Activé</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "Le %sBBCode%s est <u>Désactivé</u>";
$lang['Smilies_are_ON'] = "Les Smilies sont <u>Activé</u>";
$lang['Smilies_are_OFF'] = "Les Smilies sont <u>Désactivé</u>";

$lang['Attach_signature'] = "Attacher sa signature (les signatures peuvent être modifiées dans le profil)";
$lang['Notify'] = "M'avertir lorsqu'une réponse est postée";
$lang['Delete_post'] = "Supprimer ce message";

$lang['Stored'] = "Votre message a été enregistré avec succès";
$lang['Deleted'] = "Votre message a été supprimé avec succès";
$lang['Poll_delete'] = "Votre sondage a été supprimé avec succès";
$lang['Vote_cast'] = "Votre vote a été pris en compte";

$lang['Topic_reply_notification'] = "Notification de Réponse au Sujet";

$lang['bbcode_b_help'] = "Texte gras: [b]texte[/b] (alt+b)";
$lang['bbcode_i_help'] = "Texte italique: [i]texte[/i] (alt+i)";
$lang['bbcode_u_help'] = "Texte souligné: [u]texte[/u] (alt+u)";
$lang['bbcode_q_help'] = "Citation: [quote]texte cité[/quote] (alt+q)";
$lang['bbcode_c_help'] = "Afficher du code: [code]code[/code] (alt+c)";
$lang['bbcode_l_help'] = "Liste: [list]texte[/list] (alt+l)";
$lang['bbcode_o_help'] = "Liste ordonnée: [list=]texte[/list] (alt+o)";
$lang['bbcode_p_help'] = "Insérer une image: [img]http://image_url/[/img] (alt+p)";
$lang['bbcode_w_help'] = "Insérer un lien: [url]http://url/[/url] ou [url=http://url/]Nom[/url] (alt+w)";
$lang['bbcode_a_help'] = "Fermer toutes les balises BBCode ouvertes";
$lang['bbcode_s_help'] = "Couleur du texte: [color=red]texte[/color] Astuce: #FF0000 fonctionne aussi";
$lang['bbcode_f_help'] = "Taille du texte: [size=x-small]texte en petit[/size]";

$lang['Emoticons'] = "Emoticons";
$lang['More_emoticons'] = "Voir plus d'Emoticons";

$lang['Font_color'] = "Couleur";
$lang['color_default'] = "Défaut";
$lang['color_dark_red'] = "Rouge foncé";
$lang['color_red'] = "Rouge";
$lang['color_orange'] = "Orange";
$lang['color_brown'] = "Marron";
$lang['color_yellow'] = "Jaune";
$lang['color_green'] = "Vert";
$lang['color_olive'] = "Olive";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Bleu";
$lang['color_dark_blue'] = "Bleu foncé";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violet";
$lang['color_white'] = "Blanc";
$lang['color_black'] = "Noir";

$lang['Font_size'] = "Taille";
$lang['font_tiny'] = "Très petit";
$lang['font_small'] = "Petit";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Grand";
$lang['font_huge'] = "Très grand";

$lang['Close_Tags'] = "Fermer les Balises";
$lang['Styles_tip'] = "Astuce: Une mise en forme peut être appliquée au texte sélectionné";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Messages Privés";

$lang['Login_check_pm'] = "Se connecter pour vérifier ses messages privés";
$lang['New_pms'] = "Vous avez %d nouveaux messages"; // You have 2 new messages
$lang['New_pm'] = "Vous avez %d nouveau message"; // You have 1 new message
$lang['No_new_pm'] = "Vous n'avez pas de nouveaux messages";
$lang['Unread_pms'] = "Vous avez %d messages non lus";
$lang['Unread_pm'] = "Vous avez %d message non lu";
$lang['No_unread_pm'] = "Vous n'avez pas de messages non lus";
$lang['You_new_pm'] = "Un nouveau message privé vous attend dans votre Boîte de réception";
$lang['You_new_pms'] = "De nouveaux messages privés vous attendent dans votre Boîte de réception";
$lang['You_no_new_pm'] = "No new private messages are waiting for you";

$lang['Inbox'] = "Boîte de réception";
$lang['Outbox'] = "Boîte d'envoi";
$lang['Savebox'] = "Archives";
$lang['Sentbox'] = "Messages envoyés";
$lang['Flag'] = "Flag";
$lang['Subject'] = "Sujet";
$lang['From'] = "De";
$lang['To'] = "A";
$lang['Date'] = "Date";
$lang['Mark'] = "Marquer";
$lang['Sent'] = "Envoyé";
$lang['Saved'] = "Sauvé";
$lang['Delete_marked'] = "Supprimer la Sélection";
$lang['Delete_all'] = "Tout Supprimer";
$lang['Save_marked'] = "Sauvegarder la Sélection"; 
$lang['Save_message'] = "Sauvegarder le Message";
$lang['Delete_message'] = "Supprimer le Message";

$lang['Display_messages'] = "Montrer les messages depuis"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Tous les Messages";

$lang['No_messages_folder'] = "Vous n'avez pas de messages dans ce dossier";

$lang['PM_disabled'] = "Les messages privés ont été désactivés sur ce forum";
$lang['Cannot_send_privmsg'] = "Désolé, mais l'administrateur vous a empêché d'envoyer des messages privés";
$lang['No_to_user'] = "Vous devez préciser un nom d'utilisateur pour envoyer ce message";
$lang['No_such_user'] = "Désolé, mais cet utilisateur n'existe pas";

$lang['Disable_HTML_pm'] = "Désactiver le HTML dans ce message";
$lang['Disable_BBCode_pm'] = "Désactiver le BBCode dans ce message";
$lang['Disable_Smilies_pm'] = "Désactiver les Smilies dans ce message";

$lang['Message_sent'] = "Votre message a été envoyé";

$lang['Click_return_inbox'] = "Cliquez %sici%s pour retourner à votre Boîte de réception";
$lang['Click_return_index'] = "Cliquez %sici%s pour retourner à l'Index";

$lang['Send_a_new_message'] = "Envoyer un nouveau message privé";
$lang['Send_a_reply'] = "Répondre à un message privé";
$lang['Edit_message'] = "Editer un message privé";

$lang['Notification_subject'] = "Un Nouveau Message Privé vient d'arriver";

$lang['Find_username'] = "Trouver un nom d'utilisateur";
$lang['Find'] = "Trouver";
$lang['No_match'] = "Aucun enregistrement trouvé";

$lang['No_post_id'] = "L'ID du message n'a pas été spécifiée";
$lang['No_such_folder'] = "Le dossier n'existe pas";
$lang['No_folder'] = "Pas de dossier spécifié";

$lang['Mark_all'] = "Tout séléctionner";
$lang['Unmark_all'] = "Tout désélectionner";

$lang['Confirm_delete_pm'] = "Etes-vous sûr de vouloir supprimer ce message ?";
$lang['Confirm_delete_pms'] = "Etes-vous sûr de vouloir supprimer ces messages ?";

$lang['Inbox_size'] = "Votre Boîte de réception est pleine à %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Votre Boîte des Messages envoyés est pleine à %d%%"; 
$lang['Savebox_size'] = "Votre Boîte des Archives est pleine à %d%%"; 

$lang['Click_view_privmsg'] = "Cliquez %sici%s pour voir votre Boîte de réception";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Voir le profil :: %s"; // %s is username 
$lang['About_user'] = "Tout à propos de %s"; // %s is username

$lang['Preferences'] = "Préférences";
$lang['Items_required'] = "Les champs marqué d'un * sont obligatoires";
$lang['Registration_info'] = "Enregistrement";
$lang['Profile_info'] = "Profil";
$lang['Profile_info_warn'] = "Ces informations seront visibles publiquement";
$lang['Avatar_panel'] = "Panneau de contrôle des Avatars";
$lang['Avatar_gallery'] = "Galerie des Avatars";

$lang['Website'] = "Site Web";
$lang['Location'] = "Localisation";
$lang['Contact'] = "Contact";
$lang['Email_address'] = "Adresse email";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Envoyer un message privé";
$lang['Hidden_email'] = "[ Invisible ]";
$lang['Search_user_posts'] = "Rechercher les messages de cet utilisateur";
$lang['Interests'] = "Loisirs";
$lang['Occupation'] = "Emploi"; 
$lang['Poster_rank'] = "Rang du posteur";

$lang['Total_posts'] = "Messages";
$lang['User_post_pct_stats'] = "%.2f%% du total"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f messages par jour"; // 1.5 posts per day
$lang['Search_user_posts'] = "Trouver tous les messages de %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Désolé, mais cet utilisateur n'existe pas";
$lang['Wrong_Profile'] = "Vous ne pouvez pas modifier un profil qui n'est pas le vôtre.";
$lang['Only_one_avatar'] = "Seul un type d'avateur peut être spécifié";
$lang['File_no_data'] = "Le fichier de l'URL que vous avez donné ne contient aucune données";
$lang['No_connection_URL'] = "Une connexion ne peut être établie avec l'URL que vous avez donnée";
$lang['Incomplete_URL'] = "L'URL que vous avez entrée est incomplète";
$lang['Wrong_remote_avatar_format'] = "L'URL de l'avatar est invalide";
$lang['No_send_account_inactive'] = "Désolé, mais votre mot de passe ne peut pas être retrouvé parce que votre compte est actuellement inactif. Veuillez contacter l'administrateur du forum pour plus d'informations";

$lang['Always_smile'] = "Toujours activer les Smilies";
$lang['Always_html'] = "Toujours autoriser le HTML";
$lang['Always_bbcode'] = "Toujours autoriser le BBCode";
$lang['Always_add_sig'] = "Toujours attacher sa signature";
$lang['Always_notify'] = "Toujours m'avertir des réponses";
$lang['Always_notify_explain'] = "Envoi un email lorsque quelqu'un répond aux sujets que vous avez posté. Ceci peut être changé chaque fois que vous postez";

$lang['Board_style'] = "Thème du Forum";
$lang['Board_lang'] = "Langue du Forum";
$lang['No_themes'] = "Pas de Thème dans la base de données";
$lang['Timezone'] = "Fuseau horaire";
$lang['Date_format'] = "Format de la date";
$lang['Date_format_explain'] = "La syntaxe utilisée est identique à la fonction <a href=\"http://www.php.net/manual/fr/function.date.php\" target=\"_other\">date()</a> du PHP";
$lang['Signature'] = "Signature";
$lang['Signature_explain'] = "Ceci est un bloc de texte qui peut être ajouté aux messages que vous postez. Il y a une limite de %d caractères";
$lang['Public_view_email'] = "Toujours montrer mon Adresse Email";

$lang['Current_password'] = "Mot de passe actuel";
$lang['New_password'] = "Nouveau mot de passe";
$lang['Confirm_password'] = "Confirmer le mot de passe";
$lang['Confirm_password_explain'] = "Vous devez confirmer votre mot de passe si vous souhaitez modifier votre adresse email";
$lang['password_if_changed'] = "Vous avez seulement besoin de fournir un mot de passe si vous voulez le changer";
$lang['password_confirm_if_changed'] = "Vous avez seulement besoin de confirmer votre mot de passe si vous l'avez changé ci-dessus";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Affiche une petite image au-dessous de vos détails dans vos messages. Seule une image peut être affichée à la fois, sa largeur ne peut pas dépasser %d pixels, sa hauteur %d pixels et la taille du fichier, pas plus de %dKo."; $lang['Upload_Avatar_file'] = "Envoyer l'Avatar depuis votre ordinateur";
$lang['Upload_Avatar_URL'] = "Envoyer l'Avatar à partir d'une URL";
$lang['Upload_Avatar_URL_explain'] = "Entrez l'URL de l'image Avatar, elle sera copiée sur ce site.";
$lang['Pick_local_Avatar'] = "Sélectionner un Avatar de la Gallerie";
$lang['Link_remote_Avatar'] = "Lier l'Avatar à partir d'un autre site";
$lang['Link_remote_Avatar_explain'] = "Entrez l'URL de l'image Avatar que vous voulez lier.";
$lang['Avatar_URL'] = "URL de l'Image Avatar";
$lang['Select_from_gallery'] = "Sélectionner un Avatar à partir de la Gallerie";
$lang['View_avatar_gallery'] = "Montrer la Gallerie";

$lang['Select_avatar'] = "Sélectionner l'avatar";
$lang['Return_profile'] = "Annuler l'avatar";
$lang['Select_category'] = "Sélectioner une catégorie";

$lang['Delete_Image'] = "Supprimer l'Image";
$lang['Current_Image'] = "Image Actuelle";

$lang['Notify_on_privmsg'] = "M'avertir des nouveaux Messages Privés";
$lang['Popup_on_privmsg'] = "Ouverture d'une Pop-Up lors de nouveaux Messages Privés"; 
$lang['Popup_on_privmsg_explain'] = "Certains templates peuvent ouvrir une nouvelle fenêtre pour vous informer de l'arrivée de nouveaux messages privés"; 
$lang['Hide_user'] = "Cacher sa présence en ligne";

$lang['Profile_updated'] = "Votre profil a été mis à jour";
$lang['Profile_updated_inactive'] = "Votre profil a été mis à jour, toutefois vous avez modifié des détails vitaux, ainsi votre compte redevient inactif. Vérifier votre boîte email pour savoir comment réactiver votre compte, ou si l'activation par l'administrateur est requise, patientez jusqu'à ce qu'il l'réactive";

$lang['Password_mismatch'] = "Les mots de passes que avez entrés sont différents";
$lang['Current_password_mismatch'] = "Le mot de passe que vous avez fourni est différent de celui stocké sur la base de données";
$lang['Password_long'] = "Votre mot de passe ne doit pas dépasser 32 caractères";
$lang['Username_taken'] = "Désolé, mais ce nom d'utilisateur est déjà pris";
$lang['Username_invalid'] = "Désolé, mais ce nom d'utilisateur contient contient un caractère invalide comme \" par exemple";
$lang['Username_disallowed'] = "Désolé, mais ce nom d'utilisateur a été interdit d'utilisation";
$lang['Email_taken'] = "Désolé, mais cette adresse email est déjà enregistrée par un autre utilisateur";
$lang['Email_banned'] = "Désolé, mais cette adresse email a été bannie";
$lang['Email_invalid'] = "Désolé, mais cette adresse email est invalide";
$lang['Signature_too_long'] = "Votre signature est trop longue";
$lang['Fields_empty'] = "Vous devez compléter les champs obligatoires";
$lang['Avatar_filetype'] = "Le type de fichier de l'avatar doit être .jpg, .gif ou .png";
$lang['Avatar_filesize'] = "La taille de l'image de l'avatar doit être inférieure à %d Ko"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "La taille de l'avatar doit être de %d pixels de largeur et de %d pixels de hauteur"; 

$lang['Welcome_subject'] = "Bienvenue sur les Forums de %s"; // Welcome to my.com forums
$lang['New_account_subject'] = "Nouveau compte utilisateur";
$lang['Account_activated_subject'] = "Compte activé";

$lang['Account_added'] = "Merci de vous être enregistré, votre compte a été créé. Vous pouvez vous connecter avec votre nom d'utilisateur et mot de passe";
$lang['Account_inactive'] = "Votre compte a été créé. Toutefois, ce forum requière l'activation du compte, une clef d'activation a été envoyée vers l'adresse email que vous avez fournie. Veuillez vérifier votre boîte email pour de plus amples informations";
$lang['Account_inactive_admin'] = "Votre compte a été créé. Toutefois, ce forum requière l'activation du compte par l'administrateur. Un email lui a été envoyé et vous serez informé lorsque votre compte sera activé";
$lang['Account_active'] = "Votre compte a été activé. Merci de vous être enregistré";
$lang['Account_active_admin'] = "Le compte a été activé";
$lang['Reactivate'] = "Réactivez votre compte !";
$lang['COPPA'] = "Votre compte a été créé, mais il doit être approuvé, veuillez vérifier votre boîte email pour plus de détails.";

$lang['Registration'] = "Enregistrement - Règlement";
$lang['Reg_agreement'] = "Les administrateurs et modérateurs de ce forum s'efforceront de supprimer ou éditer tous les messages à caractère répréhensible aussi rapidement que possible, toutefois, il leur est impossible de passer en revue tous les messages. Vous admettez donc que tous les messages postés sur ces forums expriment la vue et opinion de leurs auteurs respectifs, et non pas des administrateurs, ou modérateurs, ou webmestres (excepté les messages postés par ceux-ci) et par conséquent ne peuvent pas être tenus pour responsables.<br /><br />Vous consentez à ne pas poster de messages injurieux, obscènes, vulgaires, diffamatoires, menaçants, sexuels ou tout autre message qui violerait les lois applicables. Le faire peut vous conduire à être banni immédiatement de façon permanente (et votre fournisseur d'accès à internet en sera informé). L'adresse IP de chaque message est enregistrée afin d'aider à faire respecter ces conditions. Vous êtes d'accord sur le fait que le webmestre, l'administrateur et les modérateurs ce de forum ont le droit de supprimer, éditer, déplacer ou verrouiller n'importe quel sujet de discussion à tout moment. En tant qu'utilisateur, vous êtes d'accord sur le fait que toutes les informations que vous donnerez ci-après seront stockées dans une base de données. Cependant, ces informations ne seront divulguées à aucune tierce personne ou société sans votre accord. Le webmestre, l'administrateur, et les modérateurs ne peuvent pas être tenus pour responsables si une tentative de piratage informatique conduit à l'accès de ces données.<br /><br />Ce forum utilise les cookies pour stocker des informations sur votre ordinateur. Ces cookies ne contiendront aucune information que vous aurez entré ci-après, ils servent uniquement à améliorer le confort d'utilisation. L'adresse email est uniquement utilisée afin de confirmer les détails de votre enregistrement ainsi que votre mot de passe (et aussi pour vous envoyer un nouveau mot de passe dans la cas où vous l'oubliriez).<br /><br />En vous enregistrant, vous vous portez garant du fait d'être en accord avec le règlement ci-dessus.";

$lang['Agree_under_13'] = "J'accepte le règlement et j'ai <b>moins</b> de 13 ans";
$lang['Agree_over_13'] = "J'accepte le règlement et j'ai <b>plus</b> 13 ans";
$lang['Agree_not'] = "Je n'accepte pas le règlement";

$lang['Wrong_activation'] = "La clef d'activation que vous avez fournie ne correspond pas à celle de la base de données";
$lang['Send_password'] = "Envoyez moi un nouveau mot de passe"; 
$lang['Password_updated'] = "Un nouveau mot de passe a été créé, veuillez vérifier votre boîte email pour plus de détails concernant l'activation de celui-ci";
$lang['No_email_match'] = "L'adresse email que vous avez fournie ne correspond pas avec celle qui a été utilisée pour ce nom d'utilisateur";
$lang['New_password_activation'] = "Activation d'un nouveau mot de passe";
$lang['Password_activated'] = "Votre compte a été réactivé. Pour vous connecter, veuillez utiliser le mot de passe fourni dans l'email que vous avez reçu";

$lang['Send_email_msg'] = "Envoyer un message email";
$lang['No_user_specified'] = "Aucun utilisateur spécifié";
$lang['User_prevent_email'] = "Cet utilisateur ne souhaite pas recevoir d'email. Essayez de lui envoyer un message privé";
$lang['User_not_exist'] = "Cet utilisateur n'existe pas";
$lang['CC_email'] = "Envoyer une copie de cet email à vous-même";
$lang['Email_message_desc'] = "Ce message sera envoyé en texte plein, n'insérez aucun code HTML ou BBCode. L'adresse de retour pour ce message sera dirigée vers votre adresse email.";
$lang['Flood_email_limit'] = "Vous ne pouvez pas envoyer un autre email pour le moment, essayez plus tard";
$lang['Recipient'] = "Destinataire";
$lang['Email_sent'] = "L'email a été envoyé";
$lang['Send_email'] = "Envoyer un email";
$lang['Empty_subject_email'] = "Vous devez spécifier le sujet pour l'email";
$lang['Empty_message_email'] = "Vous devez entrer un message pour qu'il soit expédié";


//
// Memberslist
//
$lang['Select_sort_method'] = "Sélectionner la méthode de tri";
$lang['Sort'] = "Trier";
$lang['Sort_Top_Ten'] = "Top10 des Posteurs";
$lang['Sort_Joined'] = "Inscrit le";
$lang['Sort_Username'] = "Nom d'utilisateur";
$lang['Sort_Location'] = "Localisation";
$lang['Sort_Posts'] = "Messages";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Site Web";
$lang['Sort_Ascending'] = "Croissant";
$lang['Sort_Descending'] = "Décroissant";
$lang['Order'] = "Ordre";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Panneau de Contrôle des Groupes";
$lang['Group_member_details'] = "Appartenance à un groupe";
$lang['Group_member_join'] = "Rejoindre un Groupe";

$lang['Group_Information'] = "Informations du groupe";
$lang['Group_name'] = "Nom du groupe";
$lang['Group_description'] = "Description du groupe";
$lang['Group_membership'] = "Votre statut";
$lang['Group_Members'] = "Membres du groupe";
$lang['Group_Moderator'] = "Modérateur du groupe";
$lang['Pending_members'] = "Membres en attente";

$lang['Group_type'] = "Type du groupe";
$lang['Group_open'] = "Groupe ouvert";
$lang['Group_closed'] = "Groupe fermé";
$lang['Group_hidden'] = "Groupe invisible";

$lang['Current_memberships'] = "Membre du groupe";
$lang['Non_member_groups'] = "Non-membre du groupe";
$lang['Memberships_pending'] = "Adhésions en attente";

$lang['No_groups_exist'] = "Aucun groupe n'existe";
$lang['Group_not_exist'] = "Ce groupe d'utilisateurs n'existe pas";

$lang['Join_group'] = "Rejoindre le Groupe";
$lang['No_group_members'] = "Ce groupe n'a pas de membres";
$lang['Group_hidden_members'] = "Ce groupe est invisible, vous ne pouvez pas voir son effectif";
$lang['No_pending_group_members'] = "Ce groupe n'a pas d'utilisateurs en attente";
$lang["Group_joined"] = "Vous vous êtes inscrit à ce groupe avec succès<br />Vous serez averti lorsque votre inscription sera approuvée par le modérateur du groupe";
$lang['Group_request'] = "Une requête d'adhésion à votre groupe a été faites";
$lang['Group_approved'] = "Votre requête a été approuvée";
$lang['Group_added'] = "Vous avez été rajouté à ce groupe d'utilisateurs"; 
$lang['Already_member_group'] = "Vous êtes déjà membre de ce groupe";
$lang['User_is_member_group'] = "L'utilisateur est déjà membre de ce groupe";
$lang['Group_type_updated'] = "Vous avez mis à jour le type du groupe avec succès";

$lang['Could_not_add_user'] = "L'utilisateur que vous avez sélectionné n'existe pas";
$lang['Could_not_anon_user'] = "Vous ne pouvez pas rendre des utilisateurs Anonymes membre d'un groupe";

$lang['Confirm_unsub'] = "Etes-vous sûr de vous vouloir vous désinscrire de ce groupe ?";
$lang['Confirm_unsub_pending'] = "Votre inscription à ce groupe n'a pas encore été approuvée, êtes-vous sûr de vouloir vous désinscrire ?";

$lang['Unsub_success'] = "Vous avez été désinscrit de ce groupe.";

$lang['Approve_selected'] = "Approuver la Sélection";
$lang['Deny_selected'] = "Refusé la Sélection";
$lang['Not_logged_in'] = "Vous devez être connecté pour joindre un groupe.";
$lang['Remove_selected'] = "Enlever la Sélection";
$lang['Add_member'] = "Ajouter le Membre";
$lang['Not_group_moderator'] = "Vous n'êtes pas le modérateur de ce groupe, vous ne pouvez donc pas accomplir cette action.";

$lang['Login_to_join'] = "Connectez-vous pour joindre ou gérer les adhésions du groupe";
$lang['This_open_group'] = "Ceci est un groupe ouvert, cliquez pour faire une demande d'adhésion";
$lang['This_closed_group'] = "Ceci est un groupe fermé, plus aucun utilisateurs accepté";
$lang['This_hidden_group'] = "Ceci est groupe invisible, l'ajout automatique d'utilisateurs n'est pas autorisé";
$lang['Member_this_group'] = "Vous êtes Membre du groupe";
$lang['Pending_this_group'] = "Votre adhésion à ce groupe est en attente";
$lang['Are_group_moderator'] = "Vous êtes le Modérateur du groupe";
$lang['None'] = "Aucun";

$lang['Subscribe'] = "S'inscrire";
$lang['Unsubscribe'] = "Se désinscrire";
$lang['View_Information'] = "Voir les Informations";


//
// Search
//
$lang['Search_query'] = "Rechercher";
$lang['Search_options'] = "Options de Recherche";

$lang['Search_keywords'] = "Rercherche par Mots clefs";
$lang['Search_keywords_explain'] = "Vous pouvez utiliser <u>AND</u> pour déterminer les mots qui doivent être présents dans les résultats, <u>OR</u> pour déterminer les mots qui peuvent être présents dans les résultats et <u>NOT</u> pour déterminer les mots qui ne devraient pas être présents dans les résultats. Utilisez * comme un joker pour des recherches partielles";
$lang['Search_author'] = "Recherche par Auteur";
$lang['Search_author_explain'] = "Utilisez * comme un joker pour des recherches partielles";

$lang['Search_for_any'] = "Rerchercher n'importe quel de ces termes";
$lang['Search_for_all'] = "Rechercher tous les termes";
$lang['Search_title_msg'] = "Rechercher dans les titres et messages";
$lang['Search_msg_only'] = "Rechercher dans les messages uniquement";

$lang['Return_first'] = "Retourner les"; // followed by xxx characters in a select box
$lang['characters_posts'] = "premiers caractères des messages";

$lang['Search_previous'] = "Rechercher depuis"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Trier par";
$lang['Sort_Time'] = "Heure du Message";
$lang['Sort_Post_Subject'] = "Sujet du Message";
$lang['Sort_Topic_Title'] = "Titre du Sujet";
$lang['Sort_Author'] = "Auteur";
$lang['Sort_Forum'] = "Forum";

$lang['Display_results'] = "Afficher les résultats sous forme de";
$lang['All_available'] = "Tous disponible";
$lang['No_searchable_forums'] = "Vous n'avez pas la permission de rechercher n'importe quel forum de ce site";

$lang['No_search_match'] = "Aucun sujets ou messages ne correspondent à vos critères de recherche";
$lang['Found_search_match'] = "%d résultat trouvé"; // eg. Search found 1 match
$lang['Found_search_matches'] = "%d résultats trouvés"; // eg. Search found 24 matches

$lang['Close_window'] = "Fermer la Fenêtre";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Désolé, mais seuls les %s peuvent poster des annonces dans ce forum";
$lang['Sorry_auth_sticky'] = "Désolé, mais seuls les %s peuvent poster des post-it dans ce forum"; 
$lang['Sorry_auth_read'] = "Désolé, mais seuls les %s peuvent lire des sujets dans ce forum"; 
$lang['Sorry_auth_post'] = "Désolé, mais seuls les %s peuvent poster dans ce forum"; 
$lang['Sorry_auth_reply'] = "Désolé, mais seuls les %s peuvent répondre aux messages dans ce forum"; 
$lang['Sorry_auth_edit'] = "Désolé, mais seuls les %s peuvent éditer des messages dans ce forum"; 
$lang['Sorry_auth_delete'] = "Désolé, mais seuls les %s peuvent supprimer des messages dans ce forum"; 
$lang['Sorry_auth_vote'] = "Désolé, mais seuls les %s peuvent voter aux sondages dans ce forum"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>utilisateurs anonymes</b>";
$lang['Auth_Registered_Users'] = "<b>utilisateurs enregistrés</b>";
$lang['Auth_Users_granted_access'] = "<b>utilisateurs avec un accès spécial</b>";
$lang['Auth_Moderators'] = "<b>modérateurs</b>";
$lang['Auth_Administrators'] = "<b>administrateurs</b>";

$lang['Not_Moderator'] = "Vous n'êtes pas modérateur sur ce forum";
$lang['Not_Authorised'] = "Non Autorisé";

$lang['You_been_banned'] = "Vous avez été banni de ce forum<br />Veuillez contacter le webmestre ou l'administrateur du forum pour plus d'informations";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Il y a 0 utilisateur enregistré et "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Il y a %d utilisateurs enregistrés et "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Il y a %d utilisateur enregistré et "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 utilisateur invisible en ligne"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d utilisateurs invisibles en ligne"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d utilisateur invisible en ligne"; // 6 Hidden users online
$lang['Guest_users_zero_online'] = "Il y a 0 invité en ligne"; // There are 10 Guest users online
$lang['Guest_users_online'] = "Il y a %d invités en ligne"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Il y a %d invité en ligne"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Il n'y a actuellement personne sur ce forum";

$lang['Online_explain'] = "Ces données sont basées sur les utilisateurs actifs des cinq dernières minutes";

$lang['Forum_Location'] = "Localisation sur le Forum";
$lang['Last_updated'] = "Dernière mise à jour";

$lang['Forum_index'] = "Index du Forum";
$lang['Logging_on'] = "Se connecte";
$lang['Posting_message'] = "Poste un message";
$lang['Searching_forums'] = "Recherche sur le forum";
$lang['Viewing_profile'] = "Regarde un profil";
$lang['Viewing_online'] = "Regarde qui est en ligne";
$lang['Viewing_member_list'] = "Regarde la liste des membres";
$lang['Viewing_priv_msgs'] = "Regarde ses Messages Privés";
$lang['Viewing_FAQ'] = "Regarde la FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Panneau de Contrôle de Modération";
$lang['Mod_CP_explain'] = "En utilisant le formulaire ci-dessous, vous pouvez accomplir des opérations de modération de masse sur ce forum. Vous pouvez vérouiller, dévérouiller, déplacer ou supprimer n'importe quel nombre de sujets.";

$lang['Select'] = "Sélectionner";
$lang['Delete'] = "Supprimer";
$lang['Move'] = "Déplacer";
$lang['Lock'] = "Vérouiller";
$lang['Unlock'] = "Dévérouiller";

$lang['Topics_Removed'] = "Les sujets sélectionnés ont été retirés de la base de données avec succès.";
$lang['Topics_Locked'] = "Les sujets sélectionnés ont été verrouillés";
$lang['Topics_Moved'] = "Les sujets sélectionnés ont été déplacés";
$lang['Topics_Unlocked'] = "Les sujets sélectionnés ont été déverrouillés";
$lang['No_Topics_Moved'] = "Aucun sujet n'a été déplacé";

$lang['Confirm_delete_topic'] = "Etes-vous sûr de vouloir supprimer le(s) sujet(s) sélectionné(s) ?";
$lang['Confirm_lock_topic'] = "Etes-vous sûr de vouloir vérouiller le(s) sujet(s) sélectionné(s) ?";
$lang['Confirm_unlock_topic'] = "Etes-vous sûr de vouloir dévérouiller le(s) sujet(s) sélectionné(s) ?";
$lang['Confirm_move_topic'] = "Etes-vous sûr de vouloir déplacer le(s) sujet(s) sélectionné(s) ?";

$lang['Move_to_forum'] = "Déplacer vers le forum";
$lang['Leave_shadow_topic'] = "Laisser un sujet-traceur dans l'ancien forum.";

$lang['Split_Topic'] = "Panneau de Contrôle de la division des Sujets";
$lang['Split_Topic_explain'] = "En utilisant le formulaire ci-dessous, vous pouvez diviser un sujet en deux sujets, soit en sélectionnant les messages individuellement, soit en divisant à partir d'un message sélectionné";
$lang['Split_title'] = "Titre du nouveau sujet";
$lang['Split_forum'] = "Forum du nouveau sujet";
$lang['Split_posts'] = "Diviser les messages sélectionnés";
$lang['Split_after'] = "Diviser à partir des messages sélectionnés";
$lang['Topic_split'] = "Le sujet sélectionné a été divisé avec succès";

$lang['Too_many_error'] = "Vous avez sélectionné trop de messages. Vous ne pouvez seulement sélectionner qu'un seul message pour diviser le sujet à partir de ce message!";

$lang['None_selected'] = "Vous n'avez sélectionné aucun sujet pour accomplir cette opération. Veuillez revenir en arrière et sélectionnez-en au moins un.";
$lang['New_forum'] = "Nouveau forum";

$lang['This_posts_IP'] = "IP de ce message";
$lang['Other_IP_this_user'] = "Autres IP à partir desquelles cet utilisateur à posté";
$lang['Users_this_IP'] = "Utilisateurs postant à partir de cette IP";
$lang['IP_info'] = "Informations sur l'IP";
$lang['Lookup_IP'] = "Chercher l'IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Toutes les heures sont au format %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Heures";
$lang['-11'] = "GMT - 11 Heures";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Heures";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 Heures";
$lang['-3.5'] = "GMT - 3.5 Heures";
$lang['-3'] = "GMT - 3 Heures";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 Heure";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europe)";
$lang['2'] = "EET (Europe)";
$lang['3'] = "GMT + 3 Heures";
$lang['3.5'] = "GMT + 3.5 Heures";
$lang['4'] = "GMT + 4 Heures";
$lang['4.5'] = "GMT + 4.5 Heures";
$lang['5'] = "GMT + 5 Heures";
$lang['5.5'] = "GMT + 5.5 Heures";
$lang['6'] = "GMT + 6 Heures";
$lang['7'] = "GMT + 7 Heures";
$lang['8'] = "WST (Australie)";
$lang['9'] = "GMT + 9 Heures";
$lang['9.5'] = "CST (Australie)";
$lang['10'] = "EST (Australie)";
$lang['11'] = "GMT + 11 Heures";
$lang['12'] = "GMT + 12 Heures";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 Heures) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 Heures) Iles Midway, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 Heures) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 Heures) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 Heures) Pacifique (USA &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 Heures) Montagnes (USA &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 Heures) Central (USA &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 Heures) Est (USA &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 Heures) Heure Atlantique (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 Heures) Terre-Neuve";
$lang['tz']['-3'] = "(GMT -3:00 Heures) Brasilia, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 Heures) Centre-Atlantique, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 Heure) Les Açores, Iles du Cap Vert";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, Londres, Lisbonne, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 Heure) Amsterdam, Berlin, Bruxelles, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 Heures) Le Caire, Helsinki, Kaliningrad, Afrique du Sud";
$lang['tz']['3'] = "(GMT +3:00 Heures) Bagdad, Riyah, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 Heures) Téhéran";
$lang['tz']['4'] = "(GMT +4:00 Heures) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 Heures) Kaboul";
$lang['tz']['5'] = "(GMT +5:00 Heures) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 Heures) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 Heures) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 hours) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 Heures) Bangkok, Hanoï, Djakarta";
$lang['tz']['8'] = "(GMT +8:00 Heures) Pékin, Hong Kong, Perth, Singapour, Taïpei";
$lang['tz']['9'] = "(GMT +9:00 Heures) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 Heures) Adélaïde, Darwin";
$lang['tz']['10'] = "(GMT +10:00 Heures) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 Heures) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 Heures) Auckland, Wellington, Fiji, Marshall Island";

$lang['datetime']['Sunday'] = "Dimanche";
$lang['datetime']['Monday'] = "Lundi";
$lang['datetime']['Tuesday'] = "Mardi";
$lang['datetime']['Wednesday'] = "Mercredi";
$lang['datetime']['Thursday'] = "Jeudi";
$lang['datetime']['Friday'] = "Vendredi";
$lang['datetime']['Saturday'] = "Samedi";
$lang['datetime']['Sun'] = "Dim";
$lang['datetime']['Mon'] = "Lun";
$lang['datetime']['Tue'] = "Mar";
$lang['datetime']['Wed'] = "Mer";
$lang['datetime']['Thu'] = "Jeu";
$lang['datetime']['Fri'] = "Ven";
$lang['datetime']['Sat'] = "Sam";
$lang['datetime']['January'] = "Janiver";
$lang['datetime']['February'] = "Février";
$lang['datetime']['March'] = "Mars";
$lang['datetime']['April'] = "Avril";
$lang['datetime']['May'] = "Mai";
$lang['datetime']['June'] = "Juin";
$lang['datetime']['July'] = "Juillet";
$lang['datetime']['August'] = "Août";
$lang['datetime']['September'] = "Septembre";
$lang['datetime']['October'] = "Octobre";
$lang['datetime']['November'] = "Novembre";
$lang['datetime']['December'] = "Décembre";
$lang['datetime']['Jan'] = "Jan";
$lang['datetime']['Feb'] = "Fév";
$lang['datetime']['Mar'] = "Mar";
$lang['datetime']['Apr'] = "Avr";
$lang['datetime']['May'] = "Mai";
$lang['datetime']['Jun'] = "Juin";
$lang['datetime']['Jul'] = "Juil";
$lang['datetime']['Aug'] = "Auo";
$lang['datetime']['Sep'] = "Sep";
$lang['datetime']['Oct'] = "Oct";
$lang['datetime']['Nov'] = "Nov";
$lang['datetime']['Dec'] = "Déc";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Informations";
$lang['Critical_Information'] = "Informations Critiques";

$lang['General_Error'] = "Erreur Générale";
$lang['Critical_Error'] = "Erreur Critique";
$lang['An_error_occured'] = "Une Erreur est Survenue";
$lang['A_critical_error'] = "Une Erreur Critique est Survenue";


// Translator credit 
$lang['TRANSLATION_INFO'] = "Traduction par : <a href=\"http://www.phpbb-fr.com/\" target=\"_blank\">Helix</a>";

//
// That's all Folks!
// -------------------------------------------------

?>