<?php
/***************************************************************************
 *                            lang_main.php [romanian]
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
// translated by phpBB@xayk.net
//

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = 'iso-8859-1';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] =  'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
$lang['TRANSLATION_INFO'] = 'tradus de <a href="mailto:phpBB@xayk.net">xayk</a>';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Forum';
$lang['Category'] = 'Categorie';
$lang['Topic'] = 'Subiect';
$lang['Topics'] = 'Subiecte';
$lang['Replies'] = 'Raspunsuri';
$lang['Views'] = 'Vizualizari';
$lang['Post'] = 'Mesaj';
$lang['Posts'] = 'Mesaje';
$lang['Posted'] = 'Publicat';
$lang['Username'] = 'Utilizator';
$lang['Password'] = 'Parola';
$lang['Email'] = 'Email';
$lang['Poster'] = 'Autor';
$lang['Author'] = 'Autor';
$lang['Time'] = 'Timp';
$lang['Hours'] = 'Ore';
$lang['Message'] = 'Mesaj';

$lang['1_Day'] = 'O Zi';
$lang['7_Days'] = '7 Zile';
$lang['2_Weeks'] = '2 Saptamani';
$lang['1_Month'] = 'O Luna';
$lang['3_Months'] = '3 Luni';
$lang['6_Months'] = '6 Luni';
$lang['1_Year'] = 'Un An';

$lang['Go'] = 'Go';
$lang['Jump_to'] = 'Mergi la';
$lang['Submit'] = 'Trimite';
$lang['Reset'] = 'Sterge';
$lang['Cancel'] = 'Renunta';
$lang['Preview'] = 'Previzualizare';
$lang['Confirm'] = 'Confirma';
$lang['Spellcheck'] = 'Spellcheck';
$lang['Yes'] = 'Da';
$lang['No'] = 'Nu';
$lang['Enabled'] = 'Activat';
$lang['Disabled'] = 'Dezactivat';
$lang['Error'] = 'Eroare';

$lang['Next'] = 'Urmatorul';
$lang['Previous'] = 'Anteriorul';
$lang['Goto_page'] = 'Mergi la pagina';
$lang['Joined'] = 'Inscris in';
$lang['IP_Address'] = 'Adresa IP';

$lang['Select_forum'] = 'Alege un forum';
$lang['View_latest_post'] = 'Vezi ultimul mesaj';
$lang['View_newest_post'] = 'Vezi cel mai nou mesaj';
$lang['Page_of'] = 'Pagina <b>%d</b> din <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ #';
$lang['AIM'] = 'AIM';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo Messenger';

$lang['Forum_Index'] = '%s Forum Index';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Publica un nou topic';
$lang['Reply_to_topic'] = 'Raspunde la topic';
$lang['Reply_with_quote'] = 'Raspunde cu quote';

$lang['Click_return_topic'] = 'Apasa %sAICI%s pentru a te reintoarce la topic'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Apasa %sAICI%s pentru a reincerca';
$lang['Click_return_forum'] = 'Apasa %sAICI%s pentru a te reintoarce in forum';
$lang['Click_view_message'] = 'Apasa %sAICI%s pentru a-ti vedea mesajul';
$lang['Click_return_modcp'] = 'Apasa %sAICI%s pentru a te intoarce in Control Panel-ul Moderatorului';
$lang['Click_return_group'] = 'Apasa %sAICI%s pentru a te intoarce la informatiile despre grup';

$lang['Admin_panel'] = 'Mergi in Control Panel-ul de Administrare';

$lang['Board_disable'] = 'Sorry but this board is currently unavailable, please try again later';


//
// Global Header strings
//
$lang['Registered_users'] = 'Membri:';
$lang['Browsing_forum'] = 'Membri online pe forum:';
$lang['Online_users_zero_total'] = 'In total sunt <b>zero</b> utilizatori online :: ';
$lang['Online_users_total'] = 'In total sunt <b>%d</b> utilizatori online :: ';
$lang['Online_user_total'] = '<b>Un</b> singur utilizator e online :: ';
$lang['Reg_users_zero_total'] = '0 Membri, ';
$lang['Reg_users_total'] = '%d Membri, ';
$lang['Reg_user_total'] = '%d Membru, ';
$lang['Hidden_users_zero_total'] = '0 Invizibili si ';
$lang['Hidden_user_total'] = '%d Invizibil si ';
$lang['Hidden_users_total'] = '%d Invizibili si ';
$lang['Guest_users_zero_total'] = '0 Vizitatori';
$lang['Guest_users_total'] = '%d Vizitatori';
$lang['Guest_user_total'] = '%d Vizitator';
$lang['Record_online_users'] = 'Numarul cel mai mare de utilizatori simultan online a fost <b>%s</b> (%s)'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAdministrator%s';
$lang['Mod_online_color'] = '%sModerator%s';

$lang['You_last_visit'] = 'Ultima vizita: %s'; // %s replaced by date/time
$lang['Current_time'] = 'Data curenta: %s'; // %s replaced by time

$lang['Search_new'] = 'Vezi mesajele publicate de la ultima ta vizita';
$lang['Search_your_posts'] = 'Vezi mesaje publicate de tine';
$lang['Search_unanswered'] = 'Vezi mesajele fara raspuns';

$lang['Register'] = 'Inregistrare';
$lang['Profile'] = 'Profil';
$lang['Edit_profile'] = 'Editeaza-ti profilul';
$lang['Search'] = 'Cauta';
$lang['Memberlist'] = 'Lista membrilor';
$lang['FAQ'] = 'FAQ';
$lang['BBCode_guide'] = 'Ghid BBCode';
$lang['Usergroups'] = 'Grupuri';
$lang['Last_Post'] = 'Ultimul Mesaj';
$lang['Moderator'] = 'Moderator';
$lang['Moderators'] = 'Moderatori';


//
// Stats block text
$lang['Posted_articles_zero_total'] = 'Nu a fost publicat nici un mesaj'; // Number of posts
$lang['Posted_articles_total'] = 'Au fost publicate <b>%d</b> mesaje'; // Number of posts
$lang['Posted_article_total'] = 'A fost publicat un singur mesaj'; // Number of posts
$lang['Registered_users_zero_total'] = 'Nu avem nici un membru'; // # registered users
$lang['Registered_users_total'] = 'Exista <b>%d</b> membri'; // # registered users
$lang['Registered_user_total'] = 'Exista un singur membru'; // # registered users
$lang['Newest_user'] = 'Cel mai nou membru este <b>%s%s%s</b>'; // a href, username, /a 

$lang['No_new_posts_last_visit'] = 'Nici un mesaj nou de la ultima vizita';
$lang['No_new_posts'] = 'Nici un mesaj nou';
$lang['New_posts'] = 'Mesaje noi';
$lang['New_post'] = 'Mesaj nou';
$lang['No_new_posts_hot'] = 'Nici un mesaj nou [ Popular ]';
$lang['New_posts_hot'] = 'Mesaje noi [ Popular ]';
$lang['No_new_posts_locked'] = 'Nici un mesaj nou [ Blocat ]';
$lang['New_posts_locked'] = 'Mesaje noi [ Blocat ]';
$lang['Forum_is_locked'] = 'Forumul e blocat';


//
// Login
//
$lang['Enter_password'] = 'Introduceti userul si parola';
$lang['Login'] = 'Login';
$lang['Logout'] = 'Logout';

$lang['Forgotten_password'] = 'Am uitat parola';

$lang['Log_me_in'] = 'Login automat';

$lang['Error_login'] = 'Ai furnizat un utilizator dezactivat sau inexistent sau o parola invalida';


//
// Index page
//
$lang['Index'] = 'Index';
$lang['No_Posts'] = 'Nici un mesaj';
$lang['No_forums'] = 'Nu exista nici un forum';

$lang['Private_Message'] = 'Mesaj Privat';
$lang['Private_Messages'] = 'Mesaje Private';
$lang['Who_is_Online'] = 'Cine e Online';

$lang['Mark_all_forums'] = 'Seteaza toate forumurile ca citite';
$lang['Forums_marked_read'] = 'Toate forumurile au fost setate ca fiind citite';


//
// Viewforum
//
$lang['View_forum'] = 'View Forum';

$lang['Forum_not_exist'] = 'Forumul selectat nu exista';
$lang['Reached_on_error'] = 'Ai ajuns in aceasta pagina datorita unei erori';

$lang['Display_topics'] = 'Display topics from previous';
$lang['All_Topics'] = 'Toate Subiectele';

$lang['Topic_Announcement'] = '<b>Anunt:</b>';
$lang['Topic_Sticky'] = '<b>Sticky:</b>';
$lang['Topic_Moved'] = '<b>Mutat:</b>';
$lang['Topic_Poll'] = '<b>[ Sondaj ]</b>';

$lang['Mark_all_topics'] = 'Marcheaza toate subiectele ca citite';
$lang['Topics_marked_read'] = 'Toate subiectele au fost marcate ca fiind citite';

$lang['Rules_post_can'] = '<b>Poti</b> publica subiecte noi';
$lang['Rules_post_cannot'] = '<b>Nu poti</b> publica subiecte noi';
$lang['Rules_reply_can'] = '<b>Poti</b> raspunde la subiectele existente';
$lang['Rules_reply_cannot'] = '<b>Nu poti</b> raspunde la subiectele existente';
$lang['Rules_edit_can'] = '<b>Poti</b> modifica propriile mesaje';
$lang['Rules_edit_cannot'] = '<b>Nu poti</b> modifica propriile mesaje';
$lang['Rules_delete_can'] = '<b>Poti</b> sterge propriile mesaje';
$lang['Rules_delete_cannot'] = '<b>Nu poti</b> sterge propriile mesaje';
$lang['Rules_vote_can'] = '<b>Poti</b> vota in sondaje';
$lang['Rules_vote_cannot'] = '<b>Nu poti</b> vota in sondaje';
$lang['Rules_moderate'] = '<b>Poti</b> fi %smoderator%s'; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = 'Nu sunt mesaje in acest forum<br />Apasa pe <b>Subiect nou</b> pentru a publica un mesaj';


//
// Viewtopic
//
$lang['View_topic'] = 'Vezi subiect';

$lang['Guest'] = 'Vizitator';
$lang['Post_subject'] = 'Post subject';
$lang['View_next_topic'] = 'Vezi subiectul urmator';
$lang['View_previous_topic'] = 'Vezi subiectul anterior';
$lang['Submit_vote'] = 'Voteaza';
$lang['View_results'] = 'Vezi rezultate';

$lang['No_newer_topics'] = 'Nu sunt subiecte mai recente in acest forum';
$lang['No_older_topics'] = 'Nu sunt subiecte mai vechi in acest forum';
$lang['Topic_post_not_exist'] = 'Subiectul sau sesajul cautat nu exista';
$lang['No_posts_topic'] = 'Nu exista mesaje in acest subiect';

$lang['Display_posts'] = 'Arata mesajele de cel mult';
$lang['All_Posts'] = 'Toate mesajele';
$lang['Newest_First'] = 'Noi Primele';
$lang['Oldest_First'] = 'Vechi Primele';

$lang['Back_to_top'] = 'La inceput';

$lang['Read_profile'] = 'Vezi profilul userilor'; 
$lang['Send_email'] = 'Trimite email la utilizator';
$lang['Visit_website'] = 'Viziteaza websitul';
$lang['ICQ_status'] = 'ICQ Status';
$lang['Edit_delete_post'] = 'Modifica/Sterge mesajul';
$lang['View_IP'] = 'Vezi IP-ul';
$lang['Delete_post'] = 'Sterge mesajul';

$lang['wrote'] = 'a scris'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Citeaza'; // comes before bbcode quote output.
$lang['Code'] = 'Cod'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Ultima modificare de %s la %s, modificat de %d ori in total'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Last edited by %s on %s, edited %d times in total'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Blocheaza acest subiect';
$lang['Unlock_topic'] = 'Deblocheaza acest subiect';
$lang['Move_topic'] = 'Muta acest subiect';
$lang['Delete_topic'] = 'Sterge acest subiect';
$lang['Split_topic'] = 'Imparte acest subiect';

$lang['Stop_watching_topic'] = 'Nu mai urmari acest subiect';
$lang['Start_watching_topic'] = 'Urmareste raspunsurile acest subiect';
$lang['No_longer_watching'] = 'Nu mai urmaresti acest subiect';
$lang['You_are_watching'] = 'Urmaresti mesajele aparute la acest subiect';

$lang['Total_votes'] = 'Voturi totale';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Corpul mesajului';
$lang['Topic_review'] = 'Topic review';

$lang['No_post_mode'] = 'Nu este specificat nici un modul de publicare'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Publica un subiect nou';
$lang['Post_a_reply'] = 'Raspunde';
$lang['Post_topic_as'] = 'Publica mesajul ca';
$lang['Edit_Post'] = 'Modifica mesaj';
$lang['Options'] = 'Optiuni';

$lang['Post_Announcement'] = 'Anunt';
$lang['Post_Sticky'] = 'Sticky';
$lang['Post_Normal'] = 'Normal';

$lang['Confirm_delete'] = 'Esti sigur ca vrei sa stergi acest mesaj?';
$lang['Confirm_delete_poll'] = 'Esti sigur ca poti sterge acest sondaj?';

$lang['Flood_Error'] = 'Nu mai poti publica inca un mesaj asa de repede. Incearca putin mai tarziu.';
$lang['Empty_subject'] = 'Trebuie sa completezi campul de subiect';
$lang['Empty_message'] = 'Trebuie sa scri ceva in corpul mesajului';
$lang['Forum_locked'] = 'Forum blocat. Nu poti publica mesaje noi si nici modifica mesaje existente';
$lang['Topic_locked'] = 'Subiect blocat. Nu poti publica mesaje noi si nici modifica mesajele existente.';
$lang['No_post_id'] = 'Trebuie sa selectezi un mesaj pt. modificare';
$lang['No_topic_id'] = 'Trebuie sa alegi un subiect la care sa raspunzi';
$lang['No_valid_mode'] = 'Poti doar edita raspunsurile si cita mesaje. Intoarce-te si reincearca.';
$lang['No_such_post'] = 'Nu exista un asemenea mesaj. Intoarce-te si reincearca.';
$lang['Edit_own_posts'] = 'Poti sa modifici doar propriile mesaje';
$lang['Delete_own_posts'] = 'Poti sa stergi doar propriile mesaje';
$lang['Cannot_delete_replied'] = 'Nu poti sterge mesaje la care exista un raspuns';
$lang['Cannot_delete_poll'] = 'Nu poti sterge un sondaj activ';
$lang['Empty_poll_title'] = 'Trebuie sa completezi titlul sondajului';
$lang['To_few_poll_options'] = 'Trebuie sa completezi cel putin doua optiuni in sondaj';
$lang['To_many_poll_options'] = 'Ai incercat sa introduci prea multe optiuni in sondaj';
$lang['Post_has_no_poll'] = 'Acest mesaj nu are sondaj';
$lang['Already_voted'] = 'Ai votat deja in acest sondaj';
$lang['No_vote_option'] = 'Trebuie sa alegi o optiune cand votezi';

$lang['Add_poll'] = 'Adauga un Sondaj';
$lang['Add_poll_explain'] = 'Daca nu vrei sa adaugi un sondaj lasa campurile goale';
$lang['Poll_question'] = 'Intrebarea sondajului';
$lang['Poll_option'] = 'Optiune';
$lang['Add_option'] = 'Adauga o optiune';
$lang['Update'] = 'Modifica';
$lang['Delete'] = 'Sterge';
$lang['Poll_for'] = 'Pastreaza sondajul activ timp de';
$lang['Days'] = 'Zile'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ 0 sau nimic pt. o perioada nelimitata ]';
$lang['Delete_poll'] = 'Sterge Sondaj';

$lang['Disable_HTML_post'] = 'Dezactiveaza HTML';
$lang['Disable_BBCode_post'] = 'Dezactiveaza BBCode';
$lang['Disable_Smilies_post'] = 'Dezactiveaza Smilies';

$lang['HTML_is_ON'] = 'HTML e <u>ACTIV</u>';
$lang['HTML_is_OFF'] = 'HTML e <u>INACTIV</u>';
$lang['BBCode_is_ON'] = '%sBBCode%s e <u>ACTIV</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s e <u>INACTIV</u>';
$lang['Smilies_are_ON'] = 'Smilies sunt <u>ACTIVE</u>';
$lang['Smilies_are_OFF'] = 'Smilies sunt <u>INACTIVE</u>';

$lang['Attach_signature'] = 'Ataseaza semnatura';
$lang['Notify'] = 'Anunta-ma cand cineva raspunde';
$lang['Delete_post'] = 'Sterge acest mesaj';

$lang['Stored'] = 'Mesajul a fost introdus';
$lang['Deleted'] = 'Mesajul a fost sters';
$lang['Poll_delete'] = 'Sondajul a fost sters';
$lang['Vote_cast'] = 'Votul a fost inregistrat';

$lang['Topic_reply_notification'] = 'Informare: Raspuns la Subiect';

$lang['bbcode_b_help'] = 'Bold text: [b]text[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Italic text: [i]text[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Underline text: [u]text[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Quote text: [quote]text[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Code display: [code]code[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'List: [list]text[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Ordered list: [list=]text[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Insert image: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Close all open bbCode tags';
$lang['bbcode_s_help'] = 'Font color: [color=red]text[/color]  Tip: you can also use color=#FF0000';
$lang['bbcode_f_help'] = 'Font size: [size=x-small]small text[/size]';

$lang['Emoticons'] = 'Emoticons';
$lang['More_emoticons'] = 'Mai multe Emoticons';

$lang['Font_color'] = 'Culoare';
$lang['color_default'] = 'Default';
$lang['color_dark_red'] = 'Dark Red';
$lang['color_red'] = 'Red';
$lang['color_orange'] = 'Orange';
$lang['color_brown'] = 'Brown';
$lang['color_yellow'] = 'Yellow';
$lang['color_green'] = 'Green';
$lang['color_olive'] = 'Olive';
$lang['color_cyan'] = 'Cyan';
$lang['color_blue'] = 'Blue';
$lang['color_dark_blue'] = 'Dark Blue';
$lang['color_indigo'] = 'Indigo';
$lang['color_violet'] = 'Violet';
$lang['color_white'] = 'White';
$lang['color_black'] = 'Black';

$lang['Font_size'] = 'Marime';
$lang['font_tiny'] = 'Minuscul';
$lang['font_small'] = 'Mic';
$lang['font_normal'] = 'Normal';
$lang['font_large'] = 'Mare';
$lang['font_huge'] = 'Imens';

$lang['Close_Tags'] = 'Inchide Tagurile';
$lang['Styles_tip'] = 'Tip: Stilul poate fi aplicat rapid pe textul selectat';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Private Messaging';

$lang['Login_check_pm'] = 'Autentifica-te pentru a-ti vedea mesajele private';
$lang['New_pms'] = 'Ai %d mesaje noi'; // You have 2 new messages
$lang['New_pm'] = 'Ai %d messaj nou'; // You have 1 new message
$lang['No_new_pm'] = 'Nu ai nici un mesaj nou';
$lang['Unread_pms'] = 'Ai You have %d unread messages';
$lang['Unread_pm'] = 'You have %d unread message';
$lang['No_unread_pm'] = 'You have no unread messages';
$lang['You_new_pm'] = 'A new private message is waiting for you in your Inbox';
$lang['You_new_pms'] = 'New private messages are waiting for you in your Inbox';
$lang['You_no_new_pm'] = 'No new private messages are waiting for you';

$lang['Unread_message'] = 'Mesaj necitit';
$lang['Read_message'] = 'Mesaj citit';

$lang['Read_pm'] = 'Citeste mesaj';
$lang['Post_new_pm'] = 'Publica mesaj';
$lang['Post_reply_pm'] = 'Raspunde la mesaj';
$lang['Post_quote_pm'] = 'Citeaza mesaj';
$lang['Edit_pm'] = 'Modifica Mesaj';

$lang['Inbox'] = 'Mesaje primite';
$lang['Outbox'] = 'Mesaje ce vor fi trimise';
$lang['Savebox'] = 'Mesaje salvate';
$lang['Sentbox'] = 'Mesaje trimise';
$lang['Flag'] = 'Flag';
$lang['Subject'] = 'Subiect';
$lang['From'] = 'De La';
$lang['To'] = 'La';
$lang['Date'] = 'Data';
$lang['Mark'] = 'Selecteaza';
$lang['Sent'] = 'Trimis';
$lang['Saved'] = 'Salvat';
$lang['Delete_marked'] = 'Sterge Selectatele';
$lang['Delete_all'] = 'Sterge Tot';
$lang['Save_marked'] = 'Salveaza Selectatele'; 
$lang['Save_message'] = 'Salveaza Mesaj';
$lang['Delete_message'] = 'Sterge Mesaj';

$lang['Display_messages'] = 'Afiseaza mesajele de cel mult'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Toate Mesajele';

$lang['No_messages_folder'] = 'Nu ai nici un mesaj in acest director';

$lang['PM_disabled'] = 'Nu se pot trimite mesaje private (optiune dezactivata de administrator)';
$lang['Cannot_send_privmsg'] = 'Nu poti trimite mesaje private (optiune dezactivata de administrator)';
$lang['No_to_user'] = 'Trebuie sa completezi numele membrului caruia vrei sa-i trimiti mesajul';
$lang['No_such_user'] = 'Nu exista un asemenea membru';

$lang['Disable_HTML_pm'] = 'Dezactiveaza HTML in acest mesaj';
$lang['Disable_BBCode_pm'] = 'Dezactiveaza BBCode in acest mesaj';
$lang['Disable_Smilies_pm'] = 'Dezactiveaza Smilies in acest mesaj';

$lang['Message_sent'] = 'Mesajul a fost trimis';

$lang['Click_return_inbox'] = 'Apasa %sAICI%s pentru a te intoarce in directorul de Mesaje Primite';
$lang['Click_return_index'] = 'Apasa %sAICI%s pentru a te intoarce in Index';

$lang['Send_a_new_message'] = 'Trimite un nou mesaj privat';
$lang['Send_a_reply'] = 'Raspunde la un mesaj privat';
$lang['Edit_message'] = 'Modifica mesajul privat';

$lang['Notification_subject'] = 'Ai primit un nou mesaj privat';

$lang['Find_username'] = 'Cauta un utilizator';
$lang['Find'] = 'Cauta';
$lang['No_match'] = 'N-am gasit nimic';

$lang['No_post_id'] = 'Nu ai specificat ID-ul mesajului';
$lang['No_such_folder'] = 'Nu exista un asemenea director';
$lang['No_folder'] = 'Nu ai specificat nici un director';

$lang['Mark_all'] = 'Selecteaza tot';
$lang['Unmark_all'] = 'Deselecteaza tot';

$lang['Confirm_delete_pm'] = 'Sigur vrei sa stergi mesajul?';
$lang['Confirm_delete_pms'] = 'Sigur vrei sa stergi aceste mesaje?';

$lang['Inbox_size'] = 'Mesaje primite: %d%% plin'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Mesaje trimise: %d%% plin'; 
$lang['Savebox_size'] = 'Mesaje salvate: %d%% plin'; 

$lang['Click_view_privmsg'] = 'Apasa %sAICI%s pentru a vedea mesajele primite';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Vizualizare profil :: %s'; // %s is username 
$lang['About_user'] = 'Totul despre %s'; // %s is username

$lang['Preferences'] = 'Preferinte';
$lang['Items_required'] = 'Campurile marcate cu * sunt obligatorii';
$lang['Registration_info'] = 'INREGISTRARE';
$lang['Profile_info'] = 'DATE';
$lang['Profile_info_warn'] = 'Aceste informatii vor fi publice';
$lang['Avatar_panel'] = 'Avatar control panel';
$lang['Avatar_gallery'] = 'Galeria Avatar';

$lang['Website'] = 'Website';
$lang['Location'] = 'Localitate';
$lang['Contact'] = 'Contact';
$lang['Email_address'] = 'Adresa email';
$lang['Email'] = 'Email';
$lang['Send_private_message'] = 'Trimite mesaj privat';
$lang['Hidden_email'] = '[ Ascuns ]';
$lang['Search_user_posts'] = 'Cauta mesaje ale acestui membru';
$lang['Interests'] = 'Pasiuni';
$lang['Occupation'] = 'Ocupatie'; 
$lang['Poster_rank'] = 'Poster rank';

$lang['Total_posts'] = 'Total mesaje';
$lang['User_post_pct_stats'] = '%.2f%% din total'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f mesaje pe zi'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Cauta toate mesajele membrului %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Nu exista utilizatorul';
$lang['Wrong_Profile'] = 'Nu poti modifica un profil ce nu iti apartine.';

$lang['Only_one_avatar'] = 'Un ziongur tip poate fi specificat';
$lang['File_no_data'] = 'Fisierul specificat este gol sau nu exista';
$lang['No_connection_URL'] = 'Conectarea la URL-ul specificat nu s-a putut realiza';
$lang['Incomplete_URL'] = 'URL-ul specificat este incomplet';
$lang['Wrong_remote_avatar_format'] = 'URL-ul nu este valid';
$lang['No_send_account_inactive'] = 'Parola nu poate fi trimisa deoarece contul este inactiv. Contactati admin-ul pt. informatii suplimentare.';

$lang['Always_smile'] = 'Foloseste Smilies';
$lang['Always_html'] = 'Foloseste HTML';
$lang['Always_bbcode'] = 'Foloseste BBCode';
$lang['Always_add_sig'] = 'Ataseaza semnatura';
$lang['Always_notify'] = 'Anunta-ma cad se raspune mesajelor mele';
$lang['Always_notify_explain'] = 'Trimite email cand cineva raspunde la un topic propriu.';

$lang['Board_style'] = 'Stil';
$lang['Board_lang'] = 'Limba';
$lang['No_themes'] = 'Nici o tema in baza de date';
$lang['Timezone'] = 'Timezone';
$lang['Date_format'] = 'Formatul datei';
$lang['Date_format_explain'] = 'Sintaxa e identica cu sintaxa din PHP a functiei <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a>';
$lang['Signature'] = 'Semnatura';
$lang['Signature_explain'] = 'Bloc de text ce poate si adaugat la fiecare mesaj pe care il publici. Limita este de %d caractere';
$lang['Public_view_email'] = 'Arata adresa mea de email';

$lang['Current_password'] = 'Parola curenta';
$lang['New_password'] = 'Parola noua';
$lang['Confirm_password'] = 'Confirma parola';
$lang['Confirm_password_explain'] = 'Trebuie sa confirmi parola daca doresti sa o schimbi sau sa modifici adresa de email';
$lang['password_if_changed'] = 'Trebuie sa introduci parola doar daca doresti sa o schimbi';
$lang['password_confirm_if_changed'] = 'Trebuie sa confirmi parola doar daca doresti sa o schimbi';

$lang['Avatar'] = 'Avatar';
$lang['Avatar_explain'] = 'Afiseaza o mica imagine la fiecare mesaj. O singura imagine poate fi afisata la un moment dat, latimea nu poate fi mai mare decat %d pixeli, inaltimea nu poate fi mai mare decat %d pixeli si fisierul nu poate fi mai mare decat %dkB.';
$lang['Upload_Avatar_file'] = 'Copiaza imagiea de pe propriul calculator';
$lang['Upload_Avatar_URL'] = 'Copiaza imaginea de la un URL';
$lang['Upload_Avatar_URL_explain'] = 'Introdu URL cu imaginea (imaginea va fi copiata).';
$lang['Pick_local_Avatar'] = 'Alege imaginea din galerie';
$lang['Link_remote_Avatar'] = 'Link catre imagine externa';
$lang['Link_remote_Avatar_explain'] = 'Introdu link catre imaginea dorita.';
$lang['Avatar_URL'] = 'URL-ul imaginii \'Avata\'';
$lang['Select_from_gallery'] = 'Alege o imagine din galerie';
$lang['View_avatar_gallery'] = 'Arata galeria';

$lang['Select_avatar'] = 'Alege imagine';
$lang['Return_profile'] = 'Renunta la imagine';
$lang['Select_category'] = 'Alege categoria';

$lang['Delete_Image'] = 'Sterge Imaginea';
$lang['Current_Image'] = 'Imaginea Curenta';

$lang['Notify_on_privmsg'] = 'Anunta-ma cand apar mesaje private noi';
$lang['Popup_on_privmsg'] = 'Anunt \'pop up\' la mesaje private noi'; 
$lang['Popup_on_privmsg_explain'] = 'Unele templaturi vor deschide o fereastra pentru a te informa despre mesajele private noi'; 
$lang['Hide_user'] = 'Ascunde prezenta mea online';

$lang['Profile_updated'] = 'Profilul a fost salvat';
$lang['Profile_updated_inactive'] = 'Profilul a fost salvat dar modificarile facute au fost substantiale si contul este inactiv. Verifica-ti emailul pentru a vedea cum poti reactiva contul. In cazul in care reactivarea contului trebuie facuta de catre administrator ascteapta ca acesta sa activeze contul';

$lang['Password_mismatch'] = 'Parola introdusa nu se potriveste';
$lang['Current_password_mismatch'] = 'Parola introdusa nu se potriveste cy cea existenta in baza de date';
$lang['Password_long'] = 'Parola trebuie sa fie mai mica de 32 caractere';
$lang['Username_taken'] = 'Acest username este deja ales';
$lang['Username_invalid'] = 'Numele ales contine caractere invalide ex: \'';
$lang['Username_disallowed'] = 'Nu este permis un astfel de nume';
$lang['Email_taken'] = 'Un alt membru foloseste adresa de email specificata';
$lang['Email_banned'] = 'Adresa de email nu poate fi folosita (interzisa de administrator)';
$lang['Email_invalid'] = 'Adresa email invalida';
$lang['Signature_too_long'] = 'Semnatura este prea lunga';
$lang['Fields_empty'] = 'Trebuie completate atoate campurile cerute';
$lang['Avatar_filetype'] = 'Imaginea trebuie sa aiba extensia .jpg, .gif sau .png';
$lang['Avatar_filesize'] = 'Imaginea trebuie sa fie mai mica decat %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Imaginea trebuie sa aiba latimea mai mica de %dx%d pixeli'; 

$lang['Welcome_subject'] = 'Bine ai venit la forumul %s'; // Welcome to my.com forums
$lang['New_account_subject'] = 'Cont nou';
$lang['Account_activated_subject'] = 'Cont Activat';

$lang['Account_added'] = 'Multumim pentru inregistrare. Contul a fost creeat.';
$lang['Account_inactive'] = 'Contul a fost creeat dar necesita confirmare. Modalitatea de confirmare a fost trimisa prin email la adresa specificata la inregistrare.';
$lang['Account_inactive_admin'] = 'Contul a fost creeat. Activarea trebuie aprobata de catre administrator. Veti fi informat prin email cand contul a fost aprobat.';
$lang['Account_active'] = 'Contul a fost activat. Va multumim pentru inregistrare';
$lang['Account_active_admin'] = 'Contul a fost activat';
$lang['Reactivate'] = 'Reactiveaza contul!';
$lang['Already_activated'] = 'Contul a fost deja activat';
$lang['COPPA'] = 'Contul a fost creeat dar inca nu a fost aprobat. Detalii v-au fort trimise prin email.';

$lang['Registration'] = 'Termeni de Inregistrare';
$lang['Reg_agreement'] = 'Cu toate ca administratorii si moderatorii acestui forum vor incerca sa modifice/interzica eventualele mesaje ce nu se inscriu in scopul acestui forum este totusi imposibil ca fiecare mesaj sa fie analizat. Toate mesajele publicate aici sunt pareri personale ale autorilor mesajelor si nu ale administratorilor sau moderatorilor (exceptie facand mesajele publicate de catre acestia).<br /><br />Odata cu inregistrarea va angajati sa nu publicati material ce nu se incadreaza in scopul acestui forum, respectiv material ce incalca legea. Publicarea unor astfel de materiale va duce la interziceerea dreptului de a publica. Adresa IP a tuturor mesajelor este inregistrata. Esti de acord ca administratori si moderatorii acestui forum pot modifica, sterge, bloca orice subiect, in orice moment in cazurile in care cred ei de cuviinta. Toate informatiile introduse la inregistrare vor si introduse in baza de date. Aceste informatii nu vor fi divulgate unor terte parti fara acordul dv.<br /><br />Acest forum foloseste cookie-uri pentru a stoca informatii pe calculatorul dv. Aceste cookie-uri nu contin informatiile introduse de dv. ci doar informatii pentru personalizarea forumului. Adresa de email introdusa este folosita doar pentru emailul de confirmare si pentru a primi parola in cazul in care o uitati.';

$lang['Agree_under_13'] = 'Sunt de acord cu cele scrise mai sus';
$lang['Agree_over_13'] = 'Sunt de acord cu cele scrise mai sus';
$lang['Agree_not'] = 'Nu sunt de acord cu ce scrie mai sus';

$lang['Wrong_activation'] = 'Cheia de activare nu se potriveste cu cheia din baza de date';
$lang['Send_password'] = 'Trimite-mi o parola noua'; 
$lang['Password_updated'] = 'Ti-am trimis o parola noua prin email impreuna cu modalitatea de activare a acestei parole';
$lang['No_email_match'] = 'Adresa email nu se potriveste cu cea din baza de date pentru acel utilizator';
$lang['New_password_activation'] = 'Activarea noii parole';
$lang['Password_activated'] = 'Contul a fost reactivat. Autentificarea se face cu parola primita in email';

$lang['Send_email_msg'] = 'Trimite un email';
$lang['No_user_specified'] = 'Nu a fost specificat nici un membru';
$lang['User_prevent_email'] = 'Acest membru nu doreste sa primeasca emailuri. Incearca sa-i trimiti un mesaj privat';
$lang['User_not_exist'] = 'Acest utilizator nu exista';
$lang['CC_email'] = 'Trimite-ti o copie al acestui email';
$lang['Email_message_desc'] = 'Acest mesaj va fi trimis plain text, nu include HTML sau BBCode.';
$lang['Flood_email_limit'] = 'Nu poti trimite un nou email in acest moment. Incearca mai tarziu';
$lang['Recipient'] = 'Destinatar';
$lang['Email_sent'] = 'Emailul a fost trimis';
$lang['Send_email'] = 'Trimite email';
$lang['Empty_subject_email'] = 'Trebuie sa completezi subiectul';
$lang['Empty_message_email'] = 'Trebuie sa scri ceva in corbul mesajului';


//
// Memberslist
//
$lang['Select_sort_method'] = 'Selecteaza ordinea de sortare';
$lang['Sort'] = 'Sorteaza';
$lang['Sort_Top_Ten'] = 'Top 10 Autori';
$lang['Sort_Joined'] = 'Data de inscriere';
$lang['Sort_Username'] = 'Username';
$lang['Sort_Location'] = 'Localitate';
$lang['Sort_Posts'] = 'Total mesaje';
$lang['Sort_Email'] = 'Email';
$lang['Sort_Website'] = 'Website';
$lang['Sort_Ascending'] = 'Crescator';
$lang['Sort_Descending'] = 'Descrescator';
$lang['Order'] = 'Ordine';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Control Panel pentru grupuri';
$lang['Group_member_details'] = 'Detalii pentru grup';
$lang['Group_member_join'] = 'Intra intr-un Grup';

$lang['Group_Information'] = 'Informatii despre Grup';
$lang['Group_name'] = 'Numele grupului';
$lang['Group_description'] = 'Descrierea grupului';
$lang['Group_membership'] = 'Grup info';
$lang['Group_Members'] = 'Membri Grupului';
$lang['Group_Moderator'] = 'Moderatorul Grpuluilui';
$lang['Pending_members'] = 'Membri in Asteptare';

$lang['Group_type'] = 'Tipul Grupului';
$lang['Group_open'] = 'Grup Deschis';
$lang['Group_closed'] = 'Grup Inchis';
$lang['Group_hidden'] = 'Grup Invizibil';

$lang['Current_memberships'] = 'Grupuri in care esti membru';
$lang['Non_member_groups'] = 'Grupuri in care nu esti membru';
$lang['Memberships_pending'] = 'Aprobare necesara pt. a deveni membru';

$lang['No_groups_exist'] = 'Nu exista nici un grup';
$lang['Group_not_exist'] = 'That user group does not exist';

$lang['Join_group'] = 'Intra in Grup';
$lang['No_group_members'] = 'Acest grup nu are membri';
$lang['Group_hidden_members'] = 'Acest grup e invizibvil, nu ii poti vedea alte informatii';
$lang['No_pending_group_members'] = 'Acest grup nu are utilizatori ce asteapta aprobarea pt. a deveni membri';
$lang['Group_joined'] = 'Ai devenit trimis cererea<br />Vei fi informat cand cererea iti va fi aprobata de moderatorul grupului';
$lang['Group_request'] = 'A fost facuta o cerere de acceptare in grup';
$lang['Group_approved'] = 'Cererea a fost aprobata';
$lang['Group_added'] = 'Ai devenit membru al acestui grup'; 
$lang['Already_member_group'] = 'Esti deja membru al grupului';
$lang['User_is_member_group'] = 'Utilizatorul e deja membru al grupului';
$lang['Group_type_updated'] = 'S-a efectuat schimbarea de tip a grupului';

$lang['Could_not_add_user'] = 'Membrul ales nu exista';
$lang['Could_not_anon_user'] = 'Nu poti face membru un utilizator anonim';

$lang['Confirm_unsub'] = 'Esti sigur ca vrei sa parasesti grupul?';
$lang['Confirm_unsub_pending'] = 'Nu ti-a fost inca aprobata cererea de a deveni membru al acestui grup. Esti sigur ca vrei sa-ti retragi cererea?';

$lang['Unsub_success'] = 'Ai parasit acest grup.';

$lang['Approve_selected'] = 'Acepta selectatii';
$lang['Deny_selected'] = 'Nu accepta selectatii';
$lang['Not_logged_in'] = 'Trebuie sa fi autentificat pt. a intra intr-un grup.';
$lang['Remove_selected'] = 'Sterge selectatii';
$lang['Add_member'] = 'Adauga membru';
$lang['Not_group_moderator'] = 'Nu esti moderatorul acestui grup.';

$lang['Login_to_join'] = 'Autentifica-te pentru a te alatura sau modifica lista de membri ai grupului.';
$lang['This_open_group'] = 'Grupul este descshis. Apasa pentru a face o cerere de acceptare.';
$lang['This_closed_group'] = 'Grup inchis. Nu sunt acceptati membrii noi.';
$lang['This_hidden_group'] = 'Grup invizibil. Adaugarea automata de membri nu este permisa.';
$lang['Member_this_group'] = 'Esti membru al acestui grup.';
$lang['Pending_this_group'] = 'Astepti sa devi membru al acestui grup.';
$lang['Are_group_moderator'] = 'Esti moderator al acestui grup.';
$lang['None'] = 'Nimeni';

$lang['Subscribe'] = 'Aboneaza';
$lang['Unsubscribe'] = 'Dezaboneaza';
$lang['View_Information'] = 'Vezi informatii';


//
// Search
//
$lang['Search_query'] = 'Cauta';
$lang['Search_options'] = 'Optiuni';

$lang['Search_keywords'] = 'Cauta Cuvinte Cheie';
$lang['Search_keywords_explain'] = 'Poti folosi <u>AND</u> pentru a defini cuvinte ce trebuie sa existe in rezultat, <u>OR</u> pentru a defini cuvinte ce pot fi in rezultat si <u>NOT</u> pentru a defini cuvinte ce nu trebuie sa existe in rezultat. Foloseste * ca \'wildcard\' pentru rezultate partiale';
$lang['Search_author'] = 'Cauta dupa Author';
$lang['Search_author_explain'] = 'Foloseste * ca \'wildcard\' pentru rezultate partiale';

$lang['Search_for_any'] = 'Cauta orice cuvant';
$lang['Search_for_all'] = 'Cauta toate cuvintele';
$lang['Search_title_msg'] = 'Cauta in subiect si in corpul mesajului';
$lang['Search_msg_only'] = 'Cauta doar in corpul mesajului';

$lang['Return_first'] = 'Returneaza primele '; // followed by xxx characters in a select box
$lang['characters_posts'] = 'caractere din mesaj';

$lang['Search_previous'] = 'Cauta in rezultate anterioare'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Ordonate dupa';
$lang['Sort_Time'] = 'Data Publicarii';
$lang['Sort_Post_Subject'] = 'Subiect';
$lang['Sort_Topic_Title'] = 'Title';
$lang['Sort_Author'] = 'Autor';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'Afiseaza rezultatele ca';
$lang['All_available'] = 'Toate';
$lang['No_searchable_forums'] = 'Nu ai permisiunea de a cauta in nici un forum';

$lang['No_search_match'] = 'Nici un subiect sau mesaj nu a indeplinit conditia cautata';
$lang['Found_search_match'] = 'A fost gasit un rezultat'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Au fost gasite %d rezultate'; // eg. Search found 24 matches

$lang['Close_window'] = 'Inchide fereastra';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Nu poti publica decat %s mesaje in acest forum';
$lang['Sorry_auth_sticky'] = 'Nu poti publica decat %s mesaje sticky in acest forum'; 
$lang['Sorry_auth_read'] = 'Nu poti citi decat %s subiecte din acest forum'; 
$lang['Sorry_auth_post'] = 'Nu poti publica decat %s subiecte in acest forum'; 
$lang['Sorry_auth_reply'] = 'Nu poti raspunde decat la %s mesaje in acest forum'; 
$lang['Sorry_auth_edit'] = 'Nu poti modifica decat %s mesaje in acest forum'; 
$lang['Sorry_auth_delete'] = 'Nu poti sterge decat %s mesaje in acest forum'; 
$lang['Sorry_auth_vote'] = 'Nu poti vota in sondaje decat de %s ori in acest forum'; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>anonimi</b>';
$lang['Auth_Registered_Users'] = '<b>membri</b>';
$lang['Auth_Users_granted_access'] = '<b>utilizatori cu drepturi speciale</b>';
$lang['Auth_Moderators'] = '<b>moderatori</b>';
$lang['Auth_Administrators'] = '<b>administratori</b>';

$lang['Not_Moderator'] = 'Nu esti moderatori in acest forum';
$lang['Not_Authorised'] = 'NEAUTORIZAT';

$lang['You_been_banned'] = 'Ti s-a interzis accesul in acest forum<br />Contacteaza administratorul pentru mai multe informatii';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Nu exista nici un membru si '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Exista %d Membrii si '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Esista un singur membru si '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = 'Nici un membru online'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d Membrii online'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d Membru online'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Exista %d Vizitatori online'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Nu exista Vizitatori online'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Exista un Vizitator online'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Nu exista nici un utilizator pe acest forum';

$lang['Online_explain'] = 'Aceste date sunt calculate in functie de utilizatorii activi din ultimele 5 minute';

$lang['Forum_Location'] = 'Localizarea Forumului';
$lang['Last_updated'] = 'Ultima Modificare';

$lang['Forum_index'] = 'Forum index';
$lang['Logging_on'] = 'Autentificare';
$lang['Posting_message'] = 'Publicare mesaj';
$lang['Searching_forums'] = 'Cautare in forum';
$lang['Viewing_profile'] = 'Vizualizare profil';
$lang['Viewing_online'] = 'Cine e online';
$lang['Viewing_member_list'] = 'Vizualizare lista membrilor';
$lang['Viewing_priv_msgs'] = 'Vizualizare mesajele private';
$lang['Viewing_FAQ'] = 'Vizualizare FAQ';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Moderator Control Panel';
$lang['Mod_CP_explain'] = 'Folosind formularul de mai jos poti efectua operatii de moderare in masa. (blocare, deblocare, mutare, stergere)';

$lang['Select'] = 'Selecteaza';
$lang['Delete'] = 'Sterge';
$lang['Move'] = 'Muta';
$lang['Lock'] = 'Blocheaza';
$lang['Unlock'] = 'Deblocheaza';

$lang['Topics_Removed'] = 'Subiectele selectate au fost sterse.';
$lang['Topics_Locked'] = 'Subiectele selectate au fost blocate';
$lang['Topics_Moved'] = 'Subiectele selectate au fost mutate';
$lang['Topics_Unlocked'] = 'Subiectele selectate au fost deblocate';
$lang['No_Topics_Moved'] = 'Nici un subiect nu a fost mutat';

$lang['Confirm_delete_topic'] = 'Sigur vrei sa stergi?';
$lang['Confirm_lock_topic'] = 'Esti sigur ca vrei sa blochezi subiectele selectate?';
$lang['Confirm_unlock_topic'] = '100% vrei sa deblochezi subiectele selectate?';
$lang['Confirm_move_topic'] = 'Esti sigur ca vrei sa muti subiectele selectate?';

$lang['Move_to_forum'] = 'Muta in forum';
$lang['Leave_shadow_topic'] = 'Lasa subiectul \'shadow\' si in vechiul forum.';

$lang['Split_Topic'] = 'Imparte Subiectul - Control Panel';
$lang['Split_Topic_explain'] = 'Folosind formularul de mai jos poti imparti un subiect in doua, fie selectand individual mesajele fie rapand subiectul in doua dupa un anumit mesaj';
$lang['Split_title'] = 'Titlul noului subiect';
$lang['Split_forum'] = 'Forum pentru noul subiect';
$lang['Split_posts'] = 'Imparte mesajele selectate';
$lang['Split_after'] = 'Imparte de la mesajul selectat';
$lang['Topic_split'] = 'Impartirea subiectului s-a efectuat';

$lang['Too_many_error'] = 'Ai selectat prea multe mesaje. Poti selecta un singur mesaj dupa care poti imparti subiectul!';

$lang['None_selected'] = 'Nu ai selectat nici un subiect pe care sa executi operatia. Mergi inapoi si selecteaza cel putin un subiect.';
$lang['New_forum'] = 'Forum nou';

$lang['This_posts_IP'] = 'IP for this post';
$lang['Other_IP_this_user'] = 'Alte adrese IP de la care trimite mesaje acest utilizator';
$lang['Users_this_IP'] = 'Utilizatori ce trimit mesaje de la acest IP';
$lang['IP_info'] = 'Informatii despre IP';
$lang['Lookup_IP'] = 'Cauta IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Timpul afisat este setat %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 Ore';
$lang['-11'] = 'GMT - 11 Ore';
$lang['-10'] = 'GMT - 10 Ore';
$lang['-9'] = 'GMT - 9 Ore';
$lang['-8'] = 'GMT - 8 Ore';
$lang['-7'] = 'GMT - 7 Ore';
$lang['-6'] = 'GMT - 6 Ore';
$lang['-5'] = 'GMT - 5 Ore';
$lang['-4'] = 'GMT - 4 Ore';
$lang['-3.5'] = 'GMT - 3.5 Ore';
$lang['-3'] = 'GMT - 3 Ore';
$lang['-2'] = 'GMT - 2 Ore';
$lang['-1'] = 'GMT - 1 Ore';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + O Ora';
$lang['2'] = 'GMT + 2 Ore';
$lang['3'] = 'GMT + 3 Ore';
$lang['3.5'] = 'GMT + 3.5 Ore';
$lang['4'] = 'GMT + 4 Ore';
$lang['4.5'] = 'GMT + 4.5 Ore';
$lang['5'] = 'GMT + 5 Ore';
$lang['5.5'] = 'GMT + 5.5 Ore';
$lang['6'] = 'GMT + 6 Ore';
$lang['6.5'] = 'GMT + 6.5 Ore';
$lang['7'] = 'GMT + 7 Ore';
$lang['8'] = 'GMT + 8 Ore';
$lang['9'] = 'GMT + 9 Ore';
$lang['9.5'] = 'GMT + 9.5 Ore';
$lang['10'] = 'GMT + 10 Ore';
$lang['11'] = 'GMT + 11 Ore';
$lang['12'] = 'GMT + 12 Ore';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 Ore';
$lang['tz']['-11'] = 'GMT - 11 Ore';
$lang['tz']['-10'] = 'GMT - 10 Ore';
$lang['tz']['-9'] = 'GMT - 9 Ore';
$lang['tz']['-8'] = 'GMT - 8 Ore';
$lang['tz']['-7'] = 'GMT - 7 Ore';
$lang['tz']['-6'] = 'GMT - 6 Ore';
$lang['tz']['-5'] = 'GMT - 5 Ore';
$lang['tz']['-4'] = 'GMT - 4 Ore';
$lang['tz']['-3.5'] = 'GMT - 3.5 Ore';
$lang['tz']['-3'] = 'GMT - 3 Ore';
$lang['tz']['-2'] = 'GMT - 2 Ore';
$lang['tz']['-1'] = 'GMT - 1 Ore';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + O Ora';
$lang['tz']['2'] = 'GMT + 2 Ore';
$lang['tz']['3'] = 'GMT + 3 Ore';
$lang['tz']['3.5'] = 'GMT + 3.5 Ore';
$lang['tz']['4'] = 'GMT + 4 Ore';
$lang['tz']['4.5'] = 'GMT + 4.5 Ore';
$lang['tz']['5'] = 'GMT + 5 Ore';
$lang['tz']['5.5'] = 'GMT + 5.5 Ore';
$lang['tz']['6'] = 'GMT + 6 Ore';
$lang['tz']['6.5'] = 'GMT + 6.5 Ore';
$lang['tz']['7'] = 'GMT + 7 Ore';
$lang['tz']['8'] = 'GMT + 8 Ore';
$lang['tz']['9'] = 'GMT + 9 Ore';
$lang['tz']['9.5'] = 'GMT + 9.5 Ore';
$lang['tz']['10'] = 'GMT + 10 Ore';
$lang['tz']['11'] = 'GMT + 11 Ore';
$lang['tz']['12'] = 'GMT + 12 Ore';

$lang['datetime']['Sunday'] = 'Duminica';
$lang['datetime']['Monday'] = 'Luni';
$lang['datetime']['Tuesday'] = 'Marti';
$lang['datetime']['Wednesday'] = 'Miercuri';
$lang['datetime']['Thursday'] = 'Joi';
$lang['datetime']['Friday'] = 'Vineri';
$lang['datetime']['Saturday'] = 'Sambata';
$lang['datetime']['Sun'] = 'D';
$lang['datetime']['Mon'] = 'L';
$lang['datetime']['Tue'] = 'Ma';
$lang['datetime']['Wed'] = 'Mi';
$lang['datetime']['Thu'] = 'J';
$lang['datetime']['Fri'] = 'V';
$lang['datetime']['Sat'] = 'S';
$lang['datetime']['January'] = 'Ianuarie';
$lang['datetime']['February'] = 'Februarie';
$lang['datetime']['March'] = 'Martie';
$lang['datetime']['April'] = 'Aprilie';
$lang['datetime']['May'] = 'Mai';
$lang['datetime']['June'] = 'Junie';
$lang['datetime']['July'] = 'Julie';
$lang['datetime']['August'] = 'August';
$lang['datetime']['September'] = 'Septembrie';
$lang['datetime']['October'] = 'Octombrie';
$lang['datetime']['November'] = 'Noiembrie';
$lang['datetime']['December'] = 'Decembrie';
$lang['datetime']['Jan'] = 'Jan';
$lang['datetime']['Feb'] = 'Feb';
$lang['datetime']['Mar'] = 'Mar';
$lang['datetime']['Apr'] = 'Apr';
$lang['datetime']['May'] = 'Mai';
$lang['datetime']['Jun'] = 'Jun';
$lang['datetime']['Jul'] = 'Jul';
$lang['datetime']['Aug'] = 'Aug';
$lang['datetime']['Sep'] = 'Sep';
$lang['datetime']['Oct'] = 'Oct';
$lang['datetime']['Nov'] = 'Nov';
$lang['datetime']['Dec'] = 'Dec';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Informatii';
$lang['Critical_Information'] = 'Informatii Critice';

$lang['General_Error'] = 'Eroare Generala';
$lang['Critical_Error'] = 'Eroare Critica';
$lang['An_error_occured'] = 'Eroare !';
$lang['A_critical_error'] = 'Eroare Critica !';

//
// That's all Folks!
// -------------------------------------------------

?>
