<?php
/***************************************************************************
 *                            lang_main.php [românã cu diacritice]
 *                              -------------------
 *     begin                : Sat Sep 7 2002
 *     copyright 1          : (C) Daniel Tãnasie
 *     copyright 2          : (C) Bogdan Toma
 *     email     1          : danielt@mgbd.ro
 *     email     2          : bog_tom@yahoo.com
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
// Add your details here if wanted, e.g. Name, username, email address, website
//

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = 'iso-8859-2';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] =  'd M Y'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.

$lang['TRANSLATION_INFO'] = 'Varianta în limba românã: <a href="http://members.lycos.co.uk/rophpbb">Romanian phpBB online community</a>';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Forum';
$lang['Category'] = 'Categorie';
$lang['Topic'] = 'Subiect';
$lang['Topics'] = 'Subiecte';
$lang['Replies'] = 'Rãspunsuri';
$lang['Views'] = 'Vizualizãri';
$lang['Post'] = 'Mesaj';
$lang['Posts'] = 'Mesaje';
$lang['Posted'] = 'Trimis';
$lang['Username'] = 'Nume de utilizator';
$lang['Password'] = 'Parola';
$lang['Email'] = 'Email';
$lang['Poster'] = 'Poster';
$lang['Author'] = 'Autor';
$lang['Time'] = 'Timp';
$lang['Hours'] = 'Ore';
$lang['Message'] = 'Mesaj';

$lang['1_Day'] = '1 Zi';
$lang['7_Days'] = '7 Zile';
$lang['2_Weeks'] = '2 Sãptãmâni';
$lang['1_Month'] = '1 Lunã';
$lang['3_Months'] = '3 Luni';
$lang['6_Months'] = '6 Luni';
$lang['1_Year'] = '1 An';

$lang['Go'] = 'Du-te';
$lang['Jump_to'] = 'Salt la';
$lang['Submit'] = 'Trimite';
$lang['Reset'] = 'Reseteazã';
$lang['Cancel'] = 'Renunþã';
$lang['Preview'] = 'Previzualizeazã';
$lang['Confirm'] = 'Confirmare';
$lang['Spellcheck'] = 'Verificã';
$lang['Yes'] = 'Da';
$lang['No'] = 'Nu';
$lang['Enabled'] = 'Activat';
$lang['Disabled'] = 'Dezactivat';
$lang['Error'] = 'Eroare';

$lang['Next'] = 'Urmãtorul';
$lang['Previous'] = 'Anteriorul';
$lang['Goto_page'] = 'Du-te la pagina';
$lang['Joined'] = 'Conectat la';
$lang['IP_Address'] = 'Adresa IP';

$lang['Select_forum'] = 'Alegeþi un forum';
$lang['View_latest_post'] = 'Vizualizarea celui mai vechi mesaj';
$lang['View_newest_post'] = 'Vizualizarea celui cel mai nou mesaj';
$lang['Page_of'] = 'Pagina <b>%d</b> din <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'Numãrul ICQ';
$lang['AIM'] = 'Adresa AIM';
$lang['MSNM'] = 'Codul MSN Messenger';
$lang['YIM'] = 'Codul Yahoo Messenger';

$lang['Forum_Index'] = 'Pagina de start a forumului %s';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Creazã un subiect nou';
$lang['Reply_to_topic'] = 'Rãspunde la subiect';
$lang['Reply_with_quote'] = 'Rãspunde cu citat (quote)';

$lang['Click_return_topic'] = 'Apãsaþi %saici%s pentru a reveni la subiect'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Apãsaþi %saici%s pentru a încerca din nou';
$lang['Click_return_forum'] = 'Apãsaþi %saici%s pentru a reveni la forum';
$lang['Click_view_message'] = 'Apãsaþi %saici%s pentru a vizualiza mesajul';
$lang['Click_return_modcp'] = 'Apãsaþi %saici%s pentru a reveni la secþiunea Panoul de Control al Moderatorului';
$lang['Click_return_group'] = 'Apãsaþi %saici%s pentru a reveni la informaþiile grupului';

$lang['Admin_panel'] = 'Panoul Administratorului';

$lang['Board_disable'] = 'Ne pare rãu dar aceastã facilitate nu este momentan disponibilã; vã rugãm încercaþi mai târziu';


//
// Global Header strings
//
$lang['Registered_users'] = 'Utilizatori înregistraþi:';
$lang['Browsing_forum'] = 'Utilizatori ce navigheazã în acest forum:';
$lang['Online_users_zero_total'] = 'În total aici sunt <b>0</b> utilizatori conectaþi : ';
$lang['Online_users_total'] = 'În total aici sunt <b>%d</b> utilizatori conectaþi : ';
$lang['Online_user_total'] = 'În total aici este <b>%d</b> utilizator conectat : ';
$lang['Reg_users_zero_total'] = '0 Înregistraþi, ';
$lang['Reg_users_total'] = '%d Înregistraþi, ';
$lang['Reg_user_total'] = '%d Înregistraþi, ';
$lang['Hidden_users_zero_total'] = '0 Ascunºi ºi ';
$lang['Hidden_user_total'] = '%d Ascunºi ºi ';
$lang['Hidden_users_total'] = '%d Ascunºi ºi ';
$lang['Guest_users_zero_total'] = '0 Vizitatori';
$lang['Guest_users_total'] = '%d Vizitatori';
$lang['Guest_user_total'] = '%d Vizitator';
$lang['Record_online_users'] = 'Cei mai mulþi utilizatori conectaþi au fost <b>%s</b> la data de %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAdministrator%s';
$lang['Mod_online_color'] = '%sModerator%s';

$lang['You_last_visit'] = 'Ultima dumneavoastrã vizitã a fost %s'; // %s replaced by date/time
$lang['Current_time'] = 'Acum este: %s'; // %s replaced by time

$lang['Search_new'] = 'Vizualizarea mesajelor scrise de la ultima dumneavoastrã vizitã';
$lang['Search_your_posts'] = 'Vizualizarea mesajelor dumneavoastrã';
$lang['Search_unanswered'] = 'Vizualizarea mesajelor la care nu s-a rãspuns';

$lang['Register'] = 'Înregistrare';
$lang['Profile'] = 'Profil';
$lang['Edit_profile'] = 'Editare profil';
$lang['Search'] = 'Cãutare';
$lang['Memberlist'] = 'Lista membrilor';
$lang['FAQ'] = 'FAQ';
$lang['BBCode_guide'] = 'Ghid pentru codul BB';
$lang['Usergroups'] = 'Grupuri de utilizatori';
$lang['Last_Post'] = 'Ultimul mesaj';
$lang['Moderator'] = 'Moderator';
$lang['Moderators'] = 'Moderatori';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Utilizatorii noºtri au scris un numãr de <b>0</b> articole'; // Number of posts
$lang['Posted_articles_total'] = 'Utilizatorii noºtri au scris un numãr de <b>%d</b> articole'; // Number of posts
$lang['Posted_article_total'] = 'Utilizatorii noºtri au scris un numãr de <b>%d</b> articol'; // Number of posts
$lang['Registered_users_zero_total'] = 'Avem <b>0</b> utilizatori înregistraþi'; // # registered users
$lang['Registered_users_total'] = 'Avem <b>%d</b> utilizatori înregistraþi'; // # registered users
$lang['Registered_user_total'] = 'Avem <b>%d</b> utilizator înregistrat'; // # registered users
$lang['Newest_user'] = 'Cel mai nou utilizator înregistrat este: <b>%s%s%s</b>'; // a href, username, /a

$lang['No_new_posts_last_visit'] = 'Nu sunt mesaje noi de la ultima ta vizita';
$lang['No_new_posts'] = 'Nu sunt mesaje noi';
$lang['New_posts'] = 'Mesaje noi';
$lang['New_post'] = 'Mesaj nou';
$lang['No_new_posts_hot'] = 'Nu sunt mesaje noi [ Popular ]';
$lang['New_posts_hot'] = 'Mesaje noi [ Popular ]';
$lang['No_new_posts_locked'] = 'Nu sunt mesaje noi [ Închis ]';
$lang['New_posts_locked'] = 'Mesaje noi [ Închis ]';
$lang['Forum_is_locked'] = 'Forumul este închis';


//
// Login
//
$lang['Enter_password'] = 'Vã rugãm introduceþi un nume de utilizator ºi o parolã pentru a va autentifica';
$lang['Login'] = 'Intrare';
$lang['Logout'] = 'Ieºire';

$lang['Forgotten_password'] = 'Mi-am uitat parola';

$lang['Log_me_in'] = 'Autentificã-mã automat la fiecare vizitã';

$lang['Error_login'] = 'Aþi introdus un nume de utilizator incorect sau inactiv sau o parolã greºitã';


//
// Index page
//
$lang['Index'] = 'Pagina de start';
$lang['No_Posts'] = 'Nici un mesaj';
$lang['No_forums'] = 'Nu existã forumuri';

$lang['Private_Message'] = 'Mesaj privat';
$lang['Private_Messages'] = 'Mesaje private';
$lang['Who_is_Online'] = 'Cine este conectat';

$lang['Mark_all_forums'] = 'Marcheazã toate forumurile ca fiind citite';
$lang['Forums_marked_read'] = 'Toate forumurile au fost marcate ca fiind citite';


//
// Viewforum
//
$lang['View_forum'] = 'Vezi forum';

$lang['Forum_not_exist'] = 'Forumul selectat nu existã';
$lang['Reached_on_error'] = 'Aþi gãsit aceastã paginã datoritã unei erori';

$lang['Display_topics'] = 'Afiºeazã subiectul pentru previzualizare';
$lang['All_Topics'] = 'Toate subiectele';

$lang['Topic_Announcement'] = '<b>Anunþ:</b>';
$lang['Topic_Sticky'] = '<b>Lipicios (Sticky):</b>';
$lang['Topic_Moved'] = '<b>Mutat:</b>';
$lang['Topic_Poll'] = '<b>[ Chestionar ]</b>';

$lang['Mark_all_topics'] = 'Marcheazã toate subiectele ca fiind citite';
$lang['Topics_marked_read'] = 'Toate subiectele au fost marcate ca fiind citite';

$lang['Rules_post_can'] = '<b>Puteþi</b> crea un subiect nou în acest forum';
$lang['Rules_post_cannot'] = '<b>Nu puteþi</b> crea un subiect nou în acest forum';
$lang['Rules_reply_can'] = '<b>Puteþi</b> rãspunde la subiectele acestui forum';
$lang['Rules_reply_cannot'] = '<b>Nu puteþi</b> rãspunde în subiectele acestui forum';
$lang['Rules_edit_can'] = '<b>Puteþi</b> modifica mesajele proprii din acest forum';
$lang['Rules_edit_cannot'] = '<b>Nu puteþi</b> modifica mesajele proprii din acest forum';
$lang['Rules_delete_can'] = '<b>Puteþi</b> ºterge mesajele proprii din acest forum';
$lang['Rules_delete_cannot'] = '<b>Nu puteþi</b> ºterge mesajele proprii din acest forum';
$lang['Rules_vote_can'] = '<b>Puteþi</b> vota în chestionarele din acest forum';
$lang['Rules_vote_cannot'] = '<b>Nu puteþi</b> vota în chestionarele din acest forum';
$lang['Rules_moderate'] = '<b>Puteþi</b> %smodera acest forum%s'; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = '<br />Nu este nici un mesaj în acest forum<br /><br />Apãsaþi pe butonul <b>Subiect nou</b> din aceastã paginã pentru a scrie un mesaj';


//
// Viewtopic
//
$lang['View_topic'] = 'Vizualizare subiect';

$lang['Guest'] = 'Vizitator';
$lang['Post_subject'] = 'Titlul subiectului';
$lang['View_next_topic'] = 'Subiectul urmãtor';
$lang['View_previous_topic'] = 'Subiectul anterior';
$lang['Submit_vote'] = 'Trimite votul';
$lang['View_results'] = 'Vizualizare rezultate';

$lang['No_newer_topics'] = 'Nu sunt subiecte noi în acest forum';
$lang['No_older_topics'] = 'Nu sunt subiecte vechi în acest forum';
$lang['Topic_post_not_exist'] = 'Nu existã subiectul sau mesajul cerut';
$lang['No_posts_topic'] = 'Nu existã mesaje în acest subiect';

$lang['Display_posts'] = 'Afiºeazã mesajele pentru a le previzualiza';
$lang['All_Posts'] = 'Toate mesajele';
$lang['Newest_First'] = 'Primele, cele mai noi mesaje';
$lang['Oldest_First'] = 'Primele, cele mai vechi mesaje';

$lang['Back_to_top'] = 'Sus';

$lang['Read_profile'] = 'Vezi profilul utilizatorului';
$lang['Send_email'] = 'Trimite email utilizatorului';
$lang['Visit_website'] = 'Viziteazã site-ul autorului';
$lang['ICQ_status'] = 'Statutul ICQ';
$lang['Edit_delete_post'] = 'Modificã/ªterge acest mesaj';
$lang['View_IP'] = 'IP-ul autorului';
$lang['Delete_post'] = 'ªterge acest mesaj';

$lang['wrote'] = 'a scris'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Citat'; // comes before bbcode quote output.
$lang['Code'] = 'Cod'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Ultima modificare efectuatã de cãtre %s la %s, modificat de %d datã în total'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Ultima modificare efectuatã %s la %s, modificat de %d ori în total'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Închide acest subiect';
$lang['Unlock_topic'] = 'Deschide acest subiect';
$lang['Move_topic'] = 'Mutã acest subiect';
$lang['Delete_topic'] = 'ªterge acest subiect';
$lang['Split_topic'] = 'Desparte acest subiect';

$lang['Stop_watching_topic'] = 'Opreºte urmãrirea acestui subiect';
$lang['Start_watching_topic'] = 'Marcheazã acest subiect pentru urmãrirea rãspunsurilor';
$lang['No_longer_watching'] = 'Aþi oprit urmãrirea acestui subiect';
$lang['You_are_watching'] = 'Acest subiect este marcat pentru urmãrire';

$lang['Total_votes'] = 'Voturi totale';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Corpul mesajului';
$lang['Topic_review'] = 'Previzualizare revizie';

$lang['No_post_mode'] = 'Nu a fost specificat modul de trimitere a mesajului'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Creazã un nou subiect';
$lang['Post_a_reply'] = 'Rãspunde';
$lang['Post_topic_as'] = 'Creazã un mesaj la';
$lang['Edit_Post'] = 'Modificã';
$lang['Options'] = 'Opþiuni';

$lang['Post_Announcement'] = 'Anunþ';
$lang['Post_Sticky'] = 'Lipicios (Sticky)';
$lang['Post_Normal'] = 'Normal';

$lang['Confirm_delete'] = 'Sunteþi sigur cã vreþi sã ºtergeþi acest mesaj?';
$lang['Confirm_delete_poll'] = 'Sunteþi sigur cã vreþi sã ºtergeþi acest chestionar?';

$lang['Flood_Error'] = 'Nu puteþi sã trimiteþi un mesaj nou la un interval atât de scurt dupa anteriorul; vã rugãm, încearcaþi mai târziu.';
$lang['Empty_subject'] = 'Trebuie specificat titlul';
$lang['Empty_message'] = 'Trebuie sa scrieþi un mesaj';
$lang['Forum_locked'] = 'Acest forum este închis, nu se pot scrie, crea, rãspunde sau modifica subiecte';
$lang['Topic_locked'] = 'Acest subiect este închis, nu se pot crea sau rãspunde la mesaje';
$lang['No_post_id'] = 'Trebuie sa selectaþi un mesaj pentru modificare';
$lang['No_topic_id'] = 'Trebuie sa selectaþi un mesaj pentru a da un rãspuns la';
$lang['No_valid_mode'] = 'Puteþi doar sã adãugaþi, sã modificaþi, sã citaþi sau sã rãspundeþi la mesaje; reveniþi ºi încercaþi din nou';
$lang['No_such_post'] = 'Aici nu este nici un mesaj, reveniþi ºi încercaþi din nou';
$lang['Edit_own_posts'] = 'Scuze dar puteþi modifica doar mesajele dumneavoastrã';
$lang['Delete_own_posts'] = 'Scuze dar puteþi ºterge doar mesajele dumneavoastrã';
$lang['Cannot_delete_replied'] = 'Scuze dar nu puteþi ºterge mesaje la care s-a rãspuns deja';
$lang['Cannot_delete_poll'] = 'Scuze dar nu puteþi ºterge un chestionar aflat în derulare';
$lang['Empty_poll_title'] = 'Trebuie sã introduceþi un titlu pentru chestionar';
$lang['To_few_poll_options'] = 'Trebuie sã introduceþi cel puþin douã opþiuni de vot în chestionar';
$lang['To_many_poll_options'] = 'Aþi încercat sã introduceþi prea multe opþiuni de vot în chestionar';
$lang['Post_has_no_poll'] = 'Acest mesaj nu are chestionar';
$lang['Already_voted'] = 'Aþi votat deja în acest chestionar';
$lang['No_vote_option'] = 'Trebuie sã specificaþi o opþiune la votare';

$lang['Add_poll'] = 'Adaugã un chestionar';
$lang['Add_poll_explain'] = 'Dacã nu vreþi sã adãugaþi un chestionar la mesajul dumneavoastrã, lãsaþi câmpurile necompletate';
$lang['Poll_question'] = 'Chestionar';
$lang['Poll_option'] = 'Opþiunile chestionarului';
$lang['Add_option'] = 'Adaugã o opþiune';
$lang['Update'] = 'Actualizeazã';
$lang['Delete'] = 'ªterge';
$lang['Poll_for'] = 'Ruleazã chestionarul pentru';
$lang['Days'] = 'Zile'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Introduceþi 0 sau lãsaþi necompletat pentru un chestionar nelimitat în timp ]';
$lang['Delete_poll'] = 'ªterge chestionarul';

$lang['Disable_HTML_post'] = 'Dezactiveazã codul HTML în acest mesaj';
$lang['Disable_BBCode_post'] = 'Dezactiveazã codul BBCode în acest mesaj';
$lang['Disable_Smilies_post'] = 'Dezactiveazã zâmbetele în acest mesaj';

$lang['HTML_is_ON'] = 'Codul HTML este <u>Activat</u>';
$lang['HTML_is_OFF'] = 'Codul HTML este <u>Dezactivat</u>';
$lang['BBCode_is_ON'] = '%sCodulBB%s este <u>Activat</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sCodul%s este <u>Dezactivat</u>';
$lang['Smilies_are_ON'] = 'Zâmbetele sunt <u>Activate</u>';
$lang['Smilies_are_OFF'] = 'Zâmbetele sunt <u>Dezactivate</u>';

$lang['Attach_signature'] = 'Adaugã semnãtura (semnãtura poate fi schimbatã din Profil)';
$lang['Notify'] = 'Anunþa-mã când apare un rãspuns';
$lang['Delete_post'] = 'ªterge acest mesaj';

$lang['Stored'] = 'Mesajul a fost introdus cu succes';
$lang['Deleted'] = 'Mesajul a fost ºters cu succes';
$lang['Poll_delete'] = 'Chestionarul a fost ºters cu succes';
$lang['Vote_cast'] = 'Votul a fost acceptat';

$lang['Topic_reply_notification'] = 'Anunþ de rãspuns la mesaj';

$lang['bbcode_b_help'] = "Text îngroºat (bold): [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Text înclinat (italic): [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Text subliniat: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Text citat: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Cod sursã: [code]cod sursa[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Listã: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Listã ordonatã: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Insereazã imagine: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Insereazã URL: [url]http://url[/url] sau [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Închide toate tag-urile de cod BB deschise";
$lang['bbcode_s_help'] = "Culoare text: [color=red]text[/color]  Sfat: poþi folosi ºi color=#FF0000";
$lang['bbcode_f_help'] = "Mãrime font: [size=x-small]text mãrunt[/size]";

$lang['Emoticons'] = 'Iconiþe emotive';
$lang['More_emoticons'] = 'Alte iconiþe emotive';

$lang['Font_color'] = "Culoare text";
$lang['color_default'] = "Implicitã";
$lang['color_dark_red'] = "Roºu închis";
$lang['color_red'] = "Roºu";
$lang['color_orange'] = "Oranj";
$lang['color_brown'] = "Maro";
$lang['color_yellow'] = "Galben";
$lang['color_green'] = "Verde";
$lang['color_olive'] = "Mãsliniu";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Albastru";
$lang['color_dark_blue'] = "Albastru închis";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violet";
$lang['color_white'] = "Alb";
$lang['color_black'] = "Negru";

$lang['Font_size'] = "Mãrime text";
$lang['font_tiny'] = "Mãruntã";
$lang['font_small'] = "Micã";
$lang['font_normal'] = "Normalã";
$lang['font_large'] = "Mare";
$lang['font_huge'] = "Imensã";

$lang['Close_Tags'] = 'Închide tag-uri';
$lang['Styles_tip'] = 'Sfat: Stilurile pot fi aplicate imediat textului selectat';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Mesagerie privatã';

$lang['Login_check_pm'] = 'Autentificare pentru mesaje private';
$lang['New_pms'] = 'Aveþi %d mesaje noi'; // You have 2 new messages
$lang['New_pm'] = 'Aveþi %d mesaj nou'; // You have 1 new message
$lang['No_new_pm'] = 'Nu aveþi mesaje noi';
$lang['Unread_pms'] = 'Aveþi %d mesaje necitite';
$lang['Unread_pm'] = 'Aveþi %d mesaj necitit';
$lang['No_unread_pm'] = 'Nu aveþi mesaje necitite';
$lang['You_new_pm'] = 'Un mesaj nou privat aºteaptã în dosarul cu mesaje';
$lang['You_new_pms'] = 'Mai multe mesaje noi aºteaptã în dosarul cu mesaje';
$lang['You_no_new_pm'] = 'Nu sunt mesaje noi în aºteptare în dosarul cu mesaje';

$lang['Unread_message'] = 'Mesaj necitit';
$lang['Read_message'] = 'Mesaj citit';

$lang['Read_pm'] = 'Mesaj citit';
$lang['Post_new_pm'] = 'Scrie mesaj';
$lang['Post_reply_pm'] = 'Retrimite mesajul';
$lang['Post_quote_pm'] = 'Comenteazã mesajul';
$lang['Edit_pm'] = 'Modificã mesajul';

$lang['Inbox'] = 'Dosarul cu mesaje';
$lang['Outbox'] = 'Dosarul cu mesaje în curs de trimitere';
$lang['Savebox'] = 'Dosarul cu mesaje salvate';
$lang['Sentbox'] = 'Dosarul cu mesaje trimise';
$lang['Flag'] = 'Marcaj';
$lang['Subject'] = 'Subiect';
$lang['From'] = 'De la';
$lang['To'] = 'La';
$lang['Date'] = 'Data';
$lang['Mark'] = 'Marcat';
$lang['Sent'] = 'Trimis';
$lang['Saved'] = 'Salvat';
$lang['Delete_marked'] = 'ªterge mesajele marcate';
$lang['Delete_all'] = 'ªterge toate mesajele';
$lang['Save_marked'] = 'Salveazã mesajele marcate';
$lang['Save_message'] = 'Salveazã mesajul';
$lang['Delete_message'] = 'ªterge mesajul';

$lang['Display_messages'] = 'Afiºeazã mesajele din urmã'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Toate mesajele';

$lang['No_messages_folder'] = 'Nu aveþi mesaje noi în acest dosar';

$lang['PM_disabled'] = 'Mesajele private au fost dezactivate de pe acest panou';
$lang['Cannot_send_privmsg'] = 'Scuze dar administratorul vã împiedicã în trimiterea mesajelor private';
$lang['No_to_user'] = 'Trebuie specificat un nume de utilizator pentru a putea trimite mesajul';
$lang['No_such_user'] = 'Scuze dar acest utilizator nu existã';

$lang['Disable_HTML_pm'] = "Deactiveazã codul HTML în acest mesaj";
$lang['Disable_BBCode_pm'] = "Deactiveazã codul BB în acest mesaj";
$lang['Disable_Smilies_pm'] = "Deactiveazã zâmbetele în acest mesaj";

$lang['Message_sent'] = 'Mesajul a fost trimis';

$lang['Click_return_inbox'] = "Apãsaþi %saici%s pentru a reveni la dosarul cu mesaje";
$lang['Click_return_index'] = "Apãsaþi %saici%s pentru a reveni la Pagina de start a forumului";

$lang['Send_a_new_message'] = "Trimite un nou mesaj privat";
$lang['Send_a_reply'] = "Rãspunde la un mesaj privat";
$lang['Edit_message'] = "Modificã un mesaj privat";

$lang['Notification_subject'] = 'Un nou mesaj privat a sosit';

$lang['Find_username'] = "Cautã un utilizator";
$lang['Find'] = "Cautã";
$lang['No_match'] = "Nu a fost gãsit nici un utilizator";

$lang['No_post_id'] = "ID-ul mesajului nu a fost specificat";
$lang['No_such_folder'] = "Directorul specificat nu existã";
$lang['No_folder'] = "Nu a fost specificat directorul";

$lang['Mark_all'] = "Marcheazã toate";
$lang['Unmark_all'] = "Demarcheazã toate";

$lang['Confirm_delete_pm'] = "Sunteþi sigur cã vreþi sã ºtergeþi acest mesaj?";
$lang['Confirm_delete_pms'] = "Sunteþi sigur cã vreþi sã ºtergeþi aceste mesaje?";

$lang['Inbox_size'] = "Dosarul dumneavoastrã cu mesaje este %d%% plin"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Dosarul dumneavoastrã cu mesaje trimise este %d%% plin";
$lang['Savebox_size'] = "Dosarul dumneavoastrã cu mesaje salvate este %d%% plin";

$lang['Click_view_privmsg'] = "Apãsaþi %saici%s pentru a ajunge la dosarul dumneavoastrã cu mesaje";

//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Vezi profilul : %s'; // %s is username
$lang['About_user'] = 'Totul despre %s'; // %s is username

$lang['Preferences'] = 'Preferinþe';
$lang['Items_required'] = 'Ce este marcat cu * este obligatoriu';
$lang['Registration_info'] = 'Informaþii de înregistrare';
$lang['Profile_info'] = 'Informaþii despre profil';
$lang['Profile_info_warn'] = 'Aceste informaþii vor fi fãcute publice';
$lang['Avatar_panel'] = 'Panoul de control al imaginilor asociate';
$lang['Avatar_gallery'] = 'Galeria de imagini';

$lang['Website'] = 'Site Web';
$lang['Location'] = 'Locaþie';
$lang['Contact'] = 'Contact';
$lang['Email_address'] = 'Adresa de email';
$lang['Email'] = 'Email';
$lang['Send_private_message'] = 'Trimite mesaj privat';
$lang['Hidden_email'] = '[ Ascuns ]';
$lang['Search_user_posts'] = 'cautã mesaje scrise de acest utilizator';
$lang['Interests'] = 'Interese';
$lang['Occupation'] = 'Ocupaþia';
$lang['Poster_rank'] = 'Rangul utilizatorului';

$lang['Total_posts'] = 'Numãrul total de mesaje';
$lang['User_post_pct_stats'] = '%.2f%% din total'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f mesaje pe zi'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Cautã toate mesajele lui %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Scuze dar acest utilizator nu existã';
$lang['Wrong_Profile'] = 'Nu puteþi modifica un profil dacã nu este propriul dumneavoastrã profil.';

$lang['Only_one_avatar'] = 'Se poate specifica doar un tip de imagine asociatã';
$lang['File_no_data'] = 'Fiºierul specificat de URL-ul dumneavoastrã nu conþine informaþii';
$lang['No_connection_URL'] = 'Conexiunea nu poate fi facutã la URL-ul specificat';
$lang['Incomplete_URL'] = 'URL-ul introdus este incomplet';
$lang['Wrong_remote_avatar_format'] = 'URL-ul cãtre imaginea asociatã nu este valid';
$lang['No_send_account_inactive'] = 'Scuze, dar parola dumneavoastrã nu mai poate fi folositã deoarece contul este inactiv. Te rog contacteaza administratorul forumului pentru mai multe informatii';

$lang['Always_smile'] = 'Folosesc întotdeauna zâmbete';
$lang['Always_html'] = 'Folosesc întotdeauna cod HTML';
$lang['Always_bbcode'] = 'Folosesc întotdeauna cod BB';
$lang['Always_add_sig'] = 'Adaugã întotdeauna semnãtura mea la mesaje';
$lang['Always_notify'] = 'Anunþã-mã întotdeauna de rãspunsuri la mesajele mele';
$lang['Always_notify_explain'] = 'Trimite-mi un email când cineva rãspunde la mesajele mele. Opþiunea poate fi schimbatã la fiecare mesaj nou.';

$lang['Board_style'] = 'Stilul interfeþei';
$lang['Board_lang'] = 'Limba interfeþei';
$lang['No_themes'] = 'Nici o temã în baza de date';
$lang['Timezone'] = 'Timpul zonal';
$lang['Date_format'] = 'Formatul datei';
$lang['Date_format_explain'] = 'Sintaxa utilizatã este identicã cu cea folositã de funcþia PHP <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a>';
$lang['Signature'] = 'Semnãtura';
$lang['Signature_explain'] = 'Acesta este un bloc de text care poate fi adãugat mesajelor scrise de dumneavoastrã. Limita este de %d caractere';
$lang['Public_view_email'] = 'Afiºeazã întotdeauna adresa mea de email';

$lang['Current_password'] = 'Parola curentã';
$lang['New_password'] = 'Parola nouã';
$lang['Confirm_password'] = 'Confirmaþi parola';
$lang['Confirm_password_explain'] = 'Trebuie sã confirmaþi parola curentã dacã vreþi sã o schimbaþi sau vreþi sã aveþi altã adresã de email';
$lang['password_if_changed'] = 'Este necesar sã specificaþi parola dacã vreþi sã o schimbaþi';
$lang['password_confirm_if_changed'] = 'Este necesar sã confirmaþi parola dacã aþi schimbat-o anterior';

$lang['Avatar'] = 'Imagine asociatã (Avatar)';
$lang['Avatar_explain'] = 'Afiºeazã o imagine micuþa sub detaliile dumneavoastrã din mesaje. Doar o imagine poate fi afiºatã în acelaºi timp, mãrimea ei nu poate fi mai mare de %d pixeli ca înalþime ºi %d ca lãþime ºi mãrimea fiºierului poate fi cel mult de %dko.';
$lang['Upload_Avatar_file'] = 'Încãrcaþi de pe calculatorul dumneavoastrã imaginea asociatã';
$lang['Upload_Avatar_URL'] = 'Încãrcaþi cu un URL imaginea asociatã';
$lang['Upload_Avatar_URL_explain'] = 'Introduceþi URL-ul locului unde este imaginea asociatãr pentru a fi copiatã pe acest site.';
$lang['Pick_local_Avatar'] = 'Alegeþi o imagine asociatã din galerie';
$lang['Link_remote_Avatar'] = 'Legãtura spre un alt site ce conþine imagini asociate';
$lang['Link_remote_Avatar_explain'] = 'Introduceþi URL-ul locului unde este imaginea asociatã pentru a face o legãturã la ea.';
$lang['Avatar_URL'] = 'URL-ul imaginii asociate';
$lang['Select_from_gallery'] = 'Alegeþi o imagine asociatã din galerie';
$lang['View_avatar_gallery'] = 'Aratã galeria de imagini asociate';

$lang['Select_avatar'] = 'Alegeþi o imagine asociatã';
$lang['Return_profile'] = 'Renunþaþi la imaginea asociatã';
$lang['Select_category'] = 'Alegeþi o categorie';

$lang['Delete_Image'] = 'ªtergeþi imaginea';
$lang['Current_Image'] = 'Imaginea curentã';

$lang['Notify_on_privmsg'] = 'Atenþioneazã-mã când primesc un mesaj privat nou';
$lang['Popup_on_privmsg'] = 'Deschide o fereastrã când primesc un mesaj privat nou';
$lang['Popup_on_privmsg_explain'] = 'Unele ºabloane pot deschide o fereastrã nouã pentru a vã informa de faptul cã aþi primit un mesaj privat nou';
$lang['Hide_user'] = 'Ascundeþi indicatorul de conectare';

$lang['Profile_updated'] = 'Profilul dumneavoastrã a fost actualizat';
$lang['Profile_updated_inactive'] = 'Profilul dumneavoastrã a fost actualizat, dar deoarece au fost modificate detalii importante contul este momentan inactiv. Verificaþi-vã email-ul pentru a afla cum iþi va fi reactivat contul sau dacã este necesarã intervenþia administratorului aºteptaþi pânã ce acesta vã va reactiva contul.';

$lang['Password_mismatch'] = 'Parolele introduse nu sunt valide';
$lang['Current_password_mismatch'] = 'Parola furnizata de dumneavoastrã nu este gasitã în baza de date';
$lang['Password_long'] = 'Parola nu trebuie sã depãºeascã 32 de caractere';
$lang['Username_taken'] = 'Scuze, dar numele de utilizator introdus, existã deja';
$lang['Username_invalid'] = 'Scuze, dar numele de utilizator introdus conþine caractere greºite, ca de exemplu: \'';
$lang['Username_disallowed'] = 'Scuze, dar acest nume de utilizator a fost interzis';
$lang['Email_taken'] = 'Scuze, dar adresa de email introdusã este deja folositã de un alt utilizator';
$lang['Email_banned'] = 'Scuze, dar aceastã adresã de email a fost interzisã';
$lang['Email_invalid'] = 'Scuze, dar aceastã adresã de email nu este corectã';
$lang['Signature_too_long'] = 'Semnãtura dumneavoastrã este prea lungã';
$lang['Fields_empty'] = 'Trebuie sã completaþi câmpurile obligatorii';
$lang['Avatar_filetype'] = 'Imaginile asociater trebuie sã fie de tipul: .jpg, .gif sau .png';
$lang['Avatar_filesize'] = 'Imaginile asociate trebuie sã fie mai mici de: %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Imaginile asociate trebuie sã fie mai mici de %d pixeli pe lãþime ºi %d pixeli pe înãlþime';

$lang['Welcome_subject'] = 'Bine aþi venit pe forumul %s '; // Welcome to my.com forums
$lang['New_account_subject'] = 'Cont nou de utilizator';
$lang['Account_activated_subject'] = 'Contul a fost activat';

$lang['Account_added'] = 'Vã mulþumim pentru înregistrare, contul a fost creat. Puteþi sã vã autentificaþi cu numele de utilizator ºi parola';
$lang['Account_inactive'] = 'Contul a fost creat. Acest forum necesitã activarea contului, o cheie de activare a fost trimisa pe adresa de email furnizata de dumneavoastrã. Vã rugãm sã vã verificaþi cãsuþa de email pentru mai multe informaþii.';
$lang['Account_inactive_admin'] = 'Contul a fost creat. Acest forum necesitã activarea contului de cãtre administrator. Veþi fi informat prin email când contul va fi activat.';
$lang['Account_active'] = 'Contul a fost activat. Multumim pentru inregistrare.';
$lang['Account_active_admin'] = 'Contul a fost activat';
$lang['Reactivate'] = 'Reactivaþi-vã contul!';
$lang['Already_activated'] = 'Contul a fost deja activat';
$lang['COPPA'] = 'Contul a fost creat dar trebuie sa fie aprobat, verificaþi-vã, vã rugãm, casuþa de email.';

$lang['Registration'] = 'Termenii acordului de înregistrare';
$lang['Reg_agreement'] = 'Întotdeauna administratorii ºi moderatorii acestui forum vor încerca sã îndepãrteze sau sã modifice orice material deranjant cât mai repede posibil; este imposibil sã parcurgã fiecare mesaj în parte. Din acest motiv trebuie sã ºtiþi cã toate mesajele exprimã punctul de vedere ºi opiniile autorilor ºi nu ale administratorilor,
moderatorilor sau a web master-ului (excepþie fãcând mesajele scrise chiar de cãtre ei) ºi de aceea ei nu pot fi fãcuþi responsabili.<br /><br />Trebuie sã fiþi de acord sã nu publicaþi mesaje cu conþinut abuziv, obscen, vulgar, calomnios, de urã, ameninþãtor, sexual sau orice alt material ce poate viola legile aflate în vigoare. Dacã publicaþi astfel de materiale puteþi fi imediat ºi pentru totdeauna îndepãrtat din forum (ºi firma care vã oferã accesul la Internet va fi anunþatã). Adresele IP ale tuturor mesajelor trimise sunt stocate pentru a fi de ajutor în rezolvarea unor astfel de încãlcãri ale regulilor. Trebuie sã fiþi de acord cã webmaster-ul, administratorul ºi moderatorii acestui forum au dreptul de a ºterge, modifica sau închide orice subiect, oricând cred ei cã acest lucru se impune. Ca utilizator, trebuie sã fiþi de acord cã orice informaþie introdusã de dumneavoastrã sã fie stocatã în baza de date. Aceste informaþii nu vor fi arãtate unei terþe persoane fãrã consimþãmântul webmaster-ului, administratorului ºi moderatorilor care nu pot fi facuþi responsabili de atacurile de furt sau de vandalism care pot sã ducã la compromiterea datelor.<br /><br />Acest forum utilizeazã fiºierele tip cookie pentru a stoca informaþiile pe calculatorul dumneavoastrã. Aceste fiºiere cookie nu conþin informaþii despre alte aplicaþii ci ele sunt folosite doar pentru uºurarea navigãrii pe forum. Adresele de email sunt utilizate doar pentru confirmarea înregistrãrii dumneavoastrã ca utilizator ºi pentru parolã (ºi pentru trimiterea unei noi parole dacã aþi uitat-o pe cea curentã).<br /><br />Prin apãsarea pe butonul de înregistrare se considerã cã sunteþi de acord cu aceste condiþii.';

$lang['Agree_under_13'] = 'Sunt de acord cu aceste condiþii ºi declar cã am <b>sub</b> 13 ani';
$lang['Agree_over_13'] = 'Sunt de acord cu aceste condiþii ºi declar cã am <b>peste</b> 13 ani';
$lang['Agree_not'] = 'Nu sunt de acord cu aceste condiþii';

$lang['Wrong_activation'] = 'Cheia de activare furnizatã nu se regãseºte în baza de date';
$lang['Send_password'] = 'Trimiteþi-mi o parolã nouã';
$lang['Password_updated'] = 'O parola nouã a fost creatã, vã rugãm verificaþi-vã cãsuþa de email pentru informaþiile de activare';
$lang['No_email_match'] = 'Adresa de email furnizatã nu corespunde celei asociate acestui utilizator';
$lang['New_password_activation'] = 'Activarea parolei noi';
$lang['Password_activated'] = 'Contul dumneavoastrã a fost reactivat. La autentificare utilizaþi parola trimisã în la adresa de email primitã';

$lang['Send_email_msg'] = "Trimite un email";
$lang['No_user_specified'] = "Nu a fost specificat utilizatorul";
$lang['User_prevent_email'] = "Acest utilizator nu doreºte sã primeasca mesaje. Încearcaþi sã-i trimiteþi un mesaj privat";
$lang['User_not_exist'] = "Acest utilizator nu existã";
$lang['CC_email'] = "Trimiteþi-vã o copie";
$lang['Email_message_desc'] = "Acest mesaj va fi trimis în mod text, nu include cod HTML sau cod BB. Adresa de întoarcere pentru acest mesaj va fi setatã cãtre adresa dumneavoastrã de email.";
$lang['Flood_email_limit'] = "Nu puteþi trimite înca un email în acest moment, încearcaþi mai târziu.";
$lang['Recipient'] = "Recipient";
$lang['Email_sent'] = "Mesajul a fost trimis";
$lang['Send_email'] = "Trimite un mesaj";
$lang['Empty_subject_email'] = "Trebuie specificat un subiect pentru mesaj";
$lang['Empty_message_email'] = "Trebuie introdus conþinut în mesaj";


//
// Memberslist
//
$lang['Select_sort_method'] = 'Selectaþi metoda de sortare';
$lang['Sort'] = 'Sorteazã';
$lang['Sort_Top_Ten'] = 'Top 10 utilizatori';
$lang['Sort_Joined'] = 'Data înregistrãrii';
$lang['Sort_Username'] = 'Nume utilizator';
$lang['Sort_Location'] = 'Locaþia';
$lang['Sort_Posts'] = 'Numãr total de mesaje';
$lang['Sort_Email'] = 'Email';
$lang['Sort_Website'] = 'Site Web';
$lang['Sort_Ascending'] = 'Ascendent';
$lang['Sort_Descending'] = 'Descendent';
$lang['Order'] = 'Ordine';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Panoul de control al gupurilor';
$lang['Group_member_details'] = 'Detalii ale membrilor grupului';
$lang['Group_member_join'] = 'Aderaþi la un grup';

$lang['Group_Information'] = 'Informaþii despre grup';
$lang['Group_name'] = 'Numele grupului';
$lang['Group_description'] = 'Descrierea grupului';
$lang['Group_membership'] = 'Membrii grupului';
$lang['Group_Members'] = 'Membrii grupului';
$lang['Group_Moderator'] = 'Moderatorul grupului';
$lang['Pending_members'] = 'Membrii în aºteptare';

$lang['Group_type'] = 'Tipul grupului';
$lang['Group_open'] = 'Grup deschis';
$lang['Group_closed'] = 'Grup închis';
$lang['Group_hidden'] = 'Grup ascuns';

$lang['Current_memberships'] = 'Membrii curenþi ai grupului';
$lang['Non_member_groups'] = 'Membrii care nu fac parte din grupuri';
$lang['Memberships_pending'] = 'Membrii în aºteptare';

$lang['No_groups_exist'] = 'Nu existã grupuri';
$lang['Group_not_exist'] = 'Acest grup de utilizatori nu existã';

$lang['Join_group'] = 'Aderã la grup';
$lang['No_group_members'] = 'Acest grup nu are membrii';
$lang['Group_hidden_members'] = 'Acest grup este ascuns, nu-i puteþi vedea membrii';
$lang['No_pending_group_members'] = 'Acest grup nu are membrii în aºteptare';
$lang['Group_joined'] = 'Înscrierea la acest grup a fost facutã cu succes.<br />Veþi fi anunþat când cererea dumneavoastrã va fi aprobatã de moderatorul grupului';
$lang['Group_request'] = 'A fost depusã o cerere de aderare la grupul dumneavoastrã';
$lang['Group_approved'] = 'Cererea dumneavoastrã de aderare la grup a fost aprobatã';
$lang['Group_added'] = 'Aþi fost acceptat la acest grup de utilizatori';
$lang['Already_member_group'] = 'Sunteþi deja membru al acestui grup';
$lang['User_is_member_group'] = 'Utilizatorul este deja membru al acestui grup';
$lang['Group_type_updated'] = 'Modificarea tipului de grup s-a realizat cu succes';

$lang['Could_not_add_user'] = 'Utilizatorul selectat nu existã';
$lang['Could_not_anon_user'] = 'Un Anonim nu poate fi facut membru de grup';

$lang['Confirm_unsub'] = 'Sunteþi sigur cã vreþi sã pãrãsiþi acest grup?';
$lang['Confirm_unsub_pending'] = 'Cererea dumneavoastrã de aderare la acest grup nu a fost înca aprobatã, sunteþi sigur cã vreþi sã-l pãrãsiþi?';

$lang['Unsub_success'] = 'Dorinþa dumneavoastrã de pãrãsire a grupului a fost îndeplinitã.';

$lang['Approve_selected'] = 'Aprobã selecþiile';
$lang['Deny_selected'] = 'Respinge selecþiile';
$lang['Not_logged_in'] = 'Trebuie sã fiþi autentificat pentru a adera la grup.';
$lang['Remove_selected'] = 'ªterge selecþiile';
$lang['Add_member'] = 'Adaugã membru';
$lang['Not_group_moderator'] = 'Nu sunteþi moderator în acest grup; prin urmare nu puteþi efectua aceste acþiuni.';

$lang['Login_to_join'] = 'Autentificaþi-vã pentru a adera la grup sau pentru a organiza membrii';
$lang['This_open_group'] = 'Acesta este un grup deschis, apãsaþi aici pentru a deveni membru';
$lang['This_closed_group'] = 'Acesta este un grup închis, nu mai acceptã noi membrii';
$lang['This_hidden_group'] = 'Acesta este un grup ascuns, cererile de aderare automate nu sunt acceptate';
$lang['Member_this_group'] = 'Sunteþi membru al acestui grup';
$lang['Pending_this_group'] = 'Cererea de membru al acestui grup este în aºteptare';
$lang['Are_group_moderator'] = 'Sunteþi moderatorul grupului';
$lang['None'] = 'Nu';

$lang['Subscribe'] = "Înscriere";
$lang['Unsubscribe'] = "Pãrãsire";
$lang['View_Information'] = "Vizualizare informaþii";

//
// Search
//
$lang['Search_query'] = 'Interogare de cãutare';
$lang['Search_options'] = 'Opþiuni de cãutare';

$lang['Search_keywords'] = 'Cautã dupã cuvintele cheie';
$lang['Search_keywords_explain'] = 'Puteþi folosi <u>AND</u> pentru a defini cuvintele ce trebuie sã fie în rezultate, <u>OR</u> pentru a defini cuvintele care pot sa fie în rezultat, ºi <u>NOT</u> pentru a defini cuvintele care nu trebuie sã fie în rezultate. Se poate utiliza * pentru pãrþi de cuvinte.';
$lang['Search_author'] = 'Cautã dupã autor';
$lang['Search_author_explain'] = 'Utilizaþi * pentru parþi de cuvinte';

$lang['Search_for_any'] = "Cautã dupa oricare dintre termeni sau utilizeazã o interogare ca intrare";
$lang['Search_for_all'] = "Cautã dupa toþi termenii";
$lang['Search_title_msg'] = "Cautã în titlul subiectelor ºi în textele mesajelor";
$lang['Search_msg_only'] = "Cautã doar în textele mesajelor";

$lang['Return_first'] = 'Întoarce primele'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'de caractere ale mesajelor';

$lang['Search_previous'] = 'Cautã în urmã'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Sorteazã dupã';
$lang['Sort_Time'] = 'Data mesajului';
$lang['Sort_Post_Subject'] = 'Subiectul mesajului';
$lang['Sort_Topic_Title'] = 'Titlul subiectului';
$lang['Sort_Author'] = 'Autor';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'Afiºeazã rezultatele ca';
$lang['All_available'] = 'Disponibile toate';
$lang['No_searchable_forums'] = 'nu aveþi drepturi de cãutare în nici un forum de pe acest site';

$lang['No_search_match'] = 'Nici un subiect sau mesaj nu îndeplineºte criteriul introdus la cãutare';
$lang['Found_search_match'] = 'Cãutarea a gasit %d rezultat'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Cãutarea a gasit %d rezultate'; // eg. Search found 24 matches

$lang['Close_window'] = 'Închide fereastra';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Ne pare rãu dar doar %s poate pune anunþuri în acest forum';
$lang['Sorry_auth_sticky'] = 'Ne pare rãu dar doar %s poate pune mesaje lipicioase (sticky) în acest forum';
$lang['Sorry_auth_read'] = 'Ne pare rãu dar doar %s poate citi subiectele din acest forum';
$lang['Sorry_auth_post'] = 'Ne pare rãu dar doar %s poate scrie subiecte în acest forum';
$lang['Sorry_auth_reply'] = 'Ne pare rãu dar doar %s poate replica în acest forum';
$lang['Sorry_auth_edit'] = 'Ne pare rãu dar doar %s poate modifica un mesaj în acest forum';
$lang['Sorry_auth_delete'] = 'Ne pare rãu dar doar %s poate sterge un mesaj din acest forum';
$lang['Sorry_auth_vote'] = 'Ne pare rãu dar doar %s poate vota în chestionarele din acest forum';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>utilizator anonim</b>';
$lang['Auth_Registered_Users'] = '<b>utilizator înregistrat</b>';
$lang['Auth_Users_granted_access'] = '<b>utilizatori cu drepturi speciale de acces</b>';
$lang['Auth_Moderators'] = '<b>moderatori</b>';
$lang['Auth_Administrators'] = '<b>administratori</b>';

$lang['Not_Moderator'] = 'Dumneavoastrã nu sunteþi moderator în acest forum';
$lang['Not_Authorised'] = 'Nu sunteþi autorizat';

$lang['You_been_banned'] = 'Accesul dumneavoastrã în acest forum este blocat<br />Vã rugãm sã contactaþi webmaster-ul sau administratorul pentru mai multe informaþii';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Sunt 0 utilizatori înregistraþi ºi '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Sunt %d utilizatori înregistraþi ºi '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Sunt %d utilizatori înregistraþi si '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = '0 utilizatori ascunºi conectaþi'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d utilizatori ascunºi conectaþi'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d utilizatori ascunºi conectaþi'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Sunt %d utilizatori vizitatori conectaþi'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Sunt 0 utilizatori vizitatori conectaþi'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Este %d utilizator vizitator conectat'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Nici un utilizator nu navigheazã acum în acest forum';

$lang['Online_explain'] = 'Aceste date se bazeazã pe utilizatorii activi de peste 5 minute';

$lang['Forum_Location'] = 'Situaþia forumului';
$lang['Last_updated'] = 'Ultima îmbunãtãþire';

$lang['Forum_index'] = 'Pagina de start a forumului';
$lang['Logging_on'] = 'Autentificare';
$lang['Posting_message'] = 'Scrie un mesaj';
$lang['Searching_forums'] = 'Cautã în forumuri';
$lang['Viewing_profile'] = 'Vezi profilul';
$lang['Viewing_online'] = 'Vezi cine este conectat';
$lang['Viewing_member_list'] = 'Vezi lista cu membri';
$lang['Viewing_priv_msgs'] = 'Vezi mesajele private';
$lang['Viewing_FAQ'] = 'Vezi lista cu întrebari/rãspunsuri (FAQ)';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Panoul de control al moderatorului';
$lang['Mod_CP_explain'] = 'Utilizând formularul de mai jos puteþi efectua operaþii de moderare masivã în forum. Puteþi închide, deschide, muta sau ºterge orice numãr de subiecte.';

$lang['Select'] = 'Selecteazã';
$lang['Delete'] = 'ªterge';
$lang['Move'] = 'Mutã';
$lang['Lock'] = 'Închide';
$lang['Unlock'] = 'Deschide';

$lang['Topics_Removed'] = 'Subiectele selectate au fost cu succes ºterse din baza de date.';
$lang['Topics_Locked'] = 'Subiectele selectate au fost închise';
$lang['Topics_Moved'] = 'Subiectele selectate au fost mutate';
$lang['Topics_Unlocked'] = 'Subiectele selectate au fost deschise';
$lang['No_Topics_Moved'] = 'Nici un subiect nu a fost mutat';

$lang['Confirm_delete_topic'] = "Sunteþi sigur cã vreþi sã ºtergeþi subiectul/subiectele selectate?";
$lang['Confirm_lock_topic'] = "Sunteþi sigur cã vreþi sã închideþi subiectul/subiectele selectate?";
$lang['Confirm_unlock_topic'] = "Sunteþi sigur cã vreþi sã deschideþi subiectul/subiectele selectate?";
$lang['Confirm_move_topic'] = "Sunteþi sigur cã vreþi sã mutaþi subiectul/subiectele selectate?";

$lang['Move_to_forum'] = 'Mutã forumul';
$lang['Leave_shadow_topic'] = 'Pastreazã o umbrã a subiectului în vechiul forum.';

$lang['Split_Topic'] = 'Panoul de control a împãrþirii subiectelor';
$lang['Split_Topic_explain'] = 'Utilizând formularul de mai jos puteþi împãrþi un subiect în douã, pe rând sau începand de la cel deja selectat';
$lang['Split_title'] = 'Titlul noului subiect';
$lang['Split_forum'] = 'Forum pentru un subiect nou';
$lang['Split_posts'] = 'Împarte mesajele alese';
$lang['Split_after'] = 'Împarte mesajul ales';
$lang['Topic_split'] = 'Subiectul selectat a fost împãrþit cu succes';

$lang['Too_many_error'] = 'Aþi selectat prea multe mesaje. Puteþi sã selectaþi doar un mesaj la care sã împãrþiþi subiectul!';

$lang['None_selected'] = 'Nu aþi selectat nici un subiect pentru a efectua aceasta operaþie. Vã rugãm întoarceþi-vã ºi selectaþi cel puþin un subiect.';
$lang['New_forum'] = 'Forum nou';

$lang['This_posts_IP'] = 'IP-ul mesajului';
$lang['Other_IP_this_user'] = 'Alte adrese IP de la care acest utilizator a trimis mesaje';
$lang['Users_this_IP'] = 'Utilizatori care au trimis mesaje de la acest IP';
$lang['IP_info'] = 'Informaþii IP';
$lang['Lookup_IP'] = 'Vizualizare IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Data este %s'; // eg. All times are GMT - 12 Hours (times from next block)

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
$lang['1'] = 'GMT + 1 Ora';
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
$lang['tz']['-3'] = 'GMT - 3 Ores';
$lang['tz']['-2'] = 'GMT - 2 Ore';
$lang['tz']['-1'] = 'GMT - 1 Ore';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 Ora';
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

$lang['datetime']['Sunday'] = 'Duminicã';
$lang['datetime']['Monday'] = 'Luni';
$lang['datetime']['Tuesday'] = 'Marþi';
$lang['datetime']['Wednesday'] = 'Miercuri';
$lang['datetime']['Thursday'] = 'Joi';
$lang['datetime']['Friday'] = 'Vineri';
$lang['datetime']['Saturday'] = 'Sâmbãtã';
$lang['datetime']['Sun'] = 'Dum';
$lang['datetime']['Mon'] = 'Lun';
$lang['datetime']['Tue'] = 'Mar';
$lang['datetime']['Wed'] = 'Mie';
$lang['datetime']['Thu'] = 'Joi';
$lang['datetime']['Fri'] = 'Vin';
$lang['datetime']['Sat'] = 'Sâm';
$lang['datetime']['January'] = 'Ianuarie';
$lang['datetime']['February'] = 'Februarie';
$lang['datetime']['March'] = 'Martie';
$lang['datetime']['April'] = 'Aprilie';
$lang['datetime']['May'] = 'Mai';
$lang['datetime']['June'] = 'Iunie';
$lang['datetime']['July'] = 'Iulie';
$lang['datetime']['August'] = 'August';
$lang['datetime']['September'] = 'Septembrie';
$lang['datetime']['October'] = 'Octobrie';
$lang['datetime']['November'] = 'Noiembrie';
$lang['datetime']['December'] = 'Decembrie';
$lang['datetime']['Jan'] = 'Ian';
$lang['datetime']['Feb'] = 'Feb';
$lang['datetime']['Mar'] = 'Mar';
$lang['datetime']['Apr'] = 'Apr';
$lang['datetime']['May'] = 'Mai';
$lang['datetime']['Jun'] = 'Iun';
$lang['datetime']['Jul'] = 'Iul';
$lang['datetime']['Aug'] = 'Aug';
$lang['datetime']['Sep'] = 'Sep';
$lang['datetime']['Oct'] = 'Oct';
$lang['datetime']['Nov'] = 'Noi';
$lang['datetime']['Dec'] = 'Dec';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Informaþii';
$lang['Critical_Information'] = 'Informaþii primejdioase';

$lang['General_Error'] = 'Eroare generalã';
$lang['Critical_Error'] = 'Eroare primejdioasã';
$lang['An_error_occured'] = 'A apãrut o eroare';
$lang['A_critical_error'] = 'A apãrut o eroare primejdioasã';

//
// That's all Folks!
// -------------------------------------------------

?>
