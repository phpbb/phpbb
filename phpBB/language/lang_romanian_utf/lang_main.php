<?php
// Romanian phpBB online community - Versiune actualizata pentru PhpBB 2.0.20
/***************************************************************************
 *                            lang_main.php [română]
 *                              -------------------
 *     begin                : Ian 14 2003
 *     last update          : Jun 11, 2005
 *     language version     : 8.0
 *     copyright            : Romanian phpBB online community
 *     website              : http://www.phpbb.ro
 *     copyright 1          : (C) Daniel Tănasie
 *     email     1          : danielt@phpbb.ro
 *     copyright 2          : (C) Bogdan Toma
 *     email     2          : bogdan@phpbb.ro
 *
 *     $Id: lang_main.php,v 1.1 2010/04/02 11:17:59 orynider Exp $
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

$lang['ENCODING'] = 'UTF-8';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] =  'd/M/Y'; // This should be changed to the default date format for your language, php date() format


// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.

$lang['TRANSLATION_INFO'] = 'Varianta în limba română: <a href="http://www.phpbb.ro" target="_blank">Romanian phpBB online community</a>';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Forum';
$lang['Category'] = 'Categorie';
$lang['Topic'] = 'Subiect';
$lang['Topics'] = 'Subiecte';
$lang['Replies'] = 'Răspunsuri';
$lang['Views'] = 'Vizualizări';
$lang['Post'] = 'Mesaj';
$lang['Posts'] = 'Mesaje';
$lang['Posted'] = 'Trimis';
$lang['Username'] = 'Utilizator';
$lang['Password'] = 'Parola';
$lang['Email'] = 'Email';
$lang['Poster'] = 'Autor';
$lang['Author'] = 'Autor';
$lang['Time'] = 'Timp';
$lang['Hours'] = 'Ore';
$lang['Message'] = 'Mesaj';

$lang['1_Day'] = '1 Zi';
$lang['7_Days'] = '7 Zile';
$lang['2_Weeks'] = '2 Săptămâni';
$lang['1_Month'] = '1 Lună';
$lang['3_Months'] = '3 Luni';
$lang['6_Months'] = '6 Luni';
$lang['1_Year'] = '1 An';

$lang['Go'] = 'Du-te';
$lang['Jump_to'] = 'Mergi direct la';
$lang['Submit'] = 'Trimite';
$lang['Reset'] = 'Resetează';
$lang['Cancel'] = 'Renunţă';
$lang['Preview'] = 'Previzualizează';
$lang['Confirm'] = 'Confirmare';
$lang['Spellcheck'] = 'Verifică';
$lang['Yes'] = 'Da';
$lang['No'] = 'Nu';
$lang['Enabled'] = 'Activat';
$lang['Disabled'] = 'Dezactivat';
$lang['Error'] = 'Eroare';

$lang['Next'] = 'Următoare';
$lang['Previous'] = 'Anterioară';
$lang['Goto_page'] = 'Du-te la pagina';
$lang['Joined'] = 'Data înscrierii';
$lang['IP_Address'] = 'Adresa IP';

$lang['Select_forum'] = 'Alegeţi un forum';
$lang['View_latest_post'] = 'Vizualizarea ultimului mesaj';
$lang['View_newest_post'] = 'Vizualizarea celui cel mai nou mesaj';
$lang['Page_of'] = 'Pagina <b>%d</b> din <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'Numărul ICQ';
$lang['AIM'] = 'Adresa AIM';
$lang['MSNM'] = 'Codul MSN Messenger';
$lang['YIM'] = 'Codul Yahoo Messenger';

$lang['Forum_Index'] = 'Pagina de start a forumului %s';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Crează un subiect nou';
$lang['Reply_to_topic'] = 'Răspunde la subiect';
$lang['Reply_with_quote'] = 'Răspunde cu citat (quote)';

$lang['Click_return_topic'] = 'Apăsaţi %saici%s pentru a reveni la subiect'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Apăsaţi %saici%s pentru a încerca din nou';
$lang['Click_return_forum'] = 'Apăsaţi %saici%s pentru a reveni la forum';
$lang['Click_view_message'] = 'Apăsaţi %saici%s pentru a vizualiza mesajul';
$lang['Click_return_modcp'] = 'Apăsaţi %saici%s pentru a reveni la secţiunea Panoul de Control al Moderatorului';
$lang['Click_return_group'] = 'Apăsaţi %saici%s pentru a reveni la informaţiile grupului';

$lang['Admin_panel'] = 'Panoul Administratorului';

$lang['Board_disable'] = 'Ne pare rău dar această facilitate nu este momentan disponibilă; vă rugăm încercaţi mai târziu';
$lang['Please_remove_install_contrib'] = 'Te rog asigură-te că ambele directoare install/ şi contrib/ sunt şterse.'; 

//
// Global Header strings
//
$lang['Registered_users'] = 'Utilizatori înregistraţi:';
$lang['Browsing_forum'] = 'Utilizatori ce navighează în acest forum:';
$lang['Online_users_zero_total'] = 'În total aici sunt <b>0</b> utilizatori conectaţi : ';
$lang['Online_users_total'] = 'În total aici sunt <b>%d</b> utilizatori conectaţi : ';
$lang['Online_user_total'] = 'În total aici este <b>%d</b> utilizator conectat : ';
$lang['Reg_users_zero_total'] = '0 Înregistraţi, ';
$lang['Reg_users_total'] = '%d Înregistraţi, ';
$lang['Reg_user_total'] = '%d Înregistrat, ';
$lang['Hidden_users_zero_total'] = '0 Ascunşi şi ';
$lang['Hidden_user_total'] = '%d Ascuns şi ';
$lang['Hidden_users_total'] = '%d Ascunşi şi ';
$lang['Guest_users_zero_total'] = '0 Vizitatori';
$lang['Guest_users_total'] = '%d Vizitatori';
$lang['Guest_user_total'] = '%d Vizitator';
$lang['Record_online_users'] = 'Cei mai mulţi utilizatori conectaţi au fost <b>%s</b> la data de %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAdministrator%s';
$lang['Mod_online_color'] = '%sModerator%s';

$lang['You_last_visit'] = 'Ultima vizită a fost %s'; // %s replaced by date/time
$lang['Current_time'] = 'Acum este: %s'; // %s replaced by time

$lang['Search_new'] = 'Mesajele scrise de la ultima vizită';
$lang['Search_your_posts'] = 'Mesajele proprii';
$lang['Search_unanswered'] = 'Mesajele la care nu s-a răspuns';

$lang['Register'] = 'Înregistrare';
$lang['Profile'] = 'Profil';
$lang['Edit_profile'] = 'Editare profil';
$lang['Search'] = 'Căutare';
$lang['Memberlist'] = 'Membri';
$lang['FAQ'] = 'FAQ';
$lang['BBCode_guide'] = 'Ghid pentru codul BB';
$lang['Usergroups'] = 'Grupuri';
$lang['Last_Post'] = 'Ultimul mesaj';
$lang['Moderator'] = 'Moderator';
$lang['Moderators'] = 'Moderatori';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Utilizatorii noştri au scris un număr de <b>0</b> articole'; // Number of posts
$lang['Posted_articles_total'] = 'Utilizatorii noştri au scris un număr de <b>%d</b> articole'; // Number of posts
$lang['Posted_article_total'] = 'Utilizatorii noştri au scris un număr de <b>%d</b> articol'; // Number of posts
$lang['Registered_users_zero_total'] = 'Avem <b>0</b> utilizatori înregistraţi'; // # registered users
$lang['Registered_users_total'] = 'Avem <b>%d</b> utilizatori înregistraţi'; // # registered users
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
$lang['Enter_password'] = 'Vă rugăm introduceţi un nume de utilizator şi o parolă pentru a va autentifica';
$lang['Login'] = 'Autentificare';
$lang['Logout'] = 'Ieşire';

$lang['Forgotten_password'] = 'Mi-am uitat parola';

$lang['Log_me_in'] = 'Autentifică-mă automat la fiecare vizită';

$lang['Error_login'] = 'Aţi introdus un nume de utilizator incorect sau inactiv sau o parolă greşită';


//
// Index page
//
$lang['Index'] = 'Pagina de start';
$lang['No_Posts'] = 'Nici un mesaj';
$lang['No_forums'] = 'Nu există forumuri';

$lang['Private_Message'] = 'Mesaj privat';
$lang['Private_Messages'] = 'Mesaje private';
$lang['Who_is_Online'] = 'Cine este conectat';

$lang['Mark_all_forums'] = 'Marchează toate forumurile ca fiind citite';
$lang['Forums_marked_read'] = 'Toate forumurile au fost marcate ca fiind citite';


//
// Viewforum
//
$lang['View_forum'] = 'Vezi forum';

$lang['Forum_not_exist'] = 'Forumul selectat nu există';
$lang['Reached_on_error'] = 'Aţi găsit această pagină datorită unei erori';

$lang['Display_topics'] = 'Afişează subiectul pentru previzualizare';
$lang['All_Topics'] = 'Toate subiectele';

$lang['Topic_Announcement'] = '<b>Anunţ:</b>';
$lang['Topic_Sticky'] = '<b>Important:</b>';
$lang['Topic_Moved'] = '<b>Mutat:</b>';
$lang['Topic_Poll'] = '<b>[ Chestionar ]</b>';

$lang['Mark_all_topics'] = 'Marchează toate subiectele ca fiind citite';
$lang['Topics_marked_read'] = 'Toate subiectele au fost marcate ca fiind citite';

$lang['Rules_post_can'] = '<b>Puteţi</b> crea un subiect nou în acest forum';
$lang['Rules_post_cannot'] = '<b>Nu puteţi</b> crea un subiect nou în acest forum';
$lang['Rules_reply_can'] = '<b>Puteţi</b> răspunde la subiectele acestui forum';
$lang['Rules_reply_cannot'] = '<b>Nu puteţi</b> răspunde în subiectele acestui forum';
$lang['Rules_edit_can'] = '<b>Puteţi</b> modifica mesajele proprii din acest forum';
$lang['Rules_edit_cannot'] = '<b>Nu puteţi</b> modifica mesajele proprii din acest forum';
$lang['Rules_delete_can'] = '<b>Puteţi</b> şterge mesajele proprii din acest forum';
$lang['Rules_delete_cannot'] = '<b>Nu puteţi</b> şterge mesajele proprii din acest forum';
$lang['Rules_vote_can'] = '<b>Puteţi</b> vota în chestionarele din acest forum';
$lang['Rules_vote_cannot'] = '<b>Nu puteţi</b> vota în chestionarele din acest forum';
$lang['Rules_moderate'] = '<b>Puteţi</b> %smodera acest forum%s'; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = '<br />Nu este nici un mesaj în acest forum<br /><br />Apăsaţi pe butonul <b>Subiect nou</b> din această pagină pentru a scrie un mesaj';


//
// Viewtopic
//
$lang['View_topic'] = 'Vizualizare subiect';

$lang['Guest'] = 'Vizitator';
$lang['Post_subject'] = 'Titlul subiectului';
$lang['View_next_topic'] = 'Subiectul următor';
$lang['View_previous_topic'] = 'Subiectul anterior';
$lang['Submit_vote'] = 'Trimite votul';
$lang['View_results'] = 'Vizualizare rezultate';

$lang['No_newer_topics'] = 'Nu sunt subiecte noi în acest forum';
$lang['No_older_topics'] = 'Nu sunt subiecte vechi în acest forum';
$lang['Topic_post_not_exist'] = 'Nu există subiectul sau mesajul cerut';
$lang['No_posts_topic'] = 'Nu există mesaje în acest subiect';

$lang['Display_posts'] = 'Afişează mesajele pentru a le previzualiza';
$lang['All_Posts'] = 'Toate mesajele';
$lang['Newest_First'] = 'Primele, cele mai noi mesaje';
$lang['Oldest_First'] = 'Primele, cele mai vechi mesaje';

$lang['Back_to_top'] = 'Sus';

$lang['Read_profile'] = 'Vezi profilul utilizatorului';
$lang['Visit_website'] = 'Vizitează site-ul autorului';
$lang['ICQ_status'] = 'Statutul ICQ';
$lang['Edit_delete_post'] = 'Modifică/Şterge acest mesaj';
$lang['View_IP'] = 'IP-ul autorului';
$lang['Delete_post'] = 'Şterge acest mesaj';

$lang['wrote'] = 'a scris'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Citat'; // comes before bbcode quote output.
$lang['Code'] = 'Cod'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Ultima modificare efectuată de către %s la %s, modificat de %d dată în total'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Ultima modificare efectuată %s la %s, modificat de %d ori în total'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Închide acest subiect';
$lang['Unlock_topic'] = 'Deschide acest subiect';
$lang['Move_topic'] = 'Mută acest subiect';
$lang['Delete_topic'] = 'Şterge acest subiect';
$lang['Split_topic'] = 'Desparte acest subiect';

$lang['Stop_watching_topic'] = 'Opreşte urmărirea acestui subiect';
$lang['Start_watching_topic'] = 'Marchează acest subiect pentru urmărirea răspunsurilor';
$lang['No_longer_watching'] = 'Aţi oprit urmărirea acestui subiect';
$lang['You_are_watching'] = 'Acest subiect este marcat pentru urmărire';

$lang['Total_votes'] = 'Voturi totale';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Corpul mesajului';
$lang['Topic_review'] = 'Previzualizare revizie';

$lang['No_post_mode'] = 'Nu a fost specificat modul de trimitere a mesajului'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Crează un nou subiect';
$lang['Post_a_reply'] = 'Răspunde';
$lang['Post_topic_as'] = 'Crează un mesaj la';
$lang['Edit_Post'] = 'Modifică';
$lang['Options'] = 'Opţiuni';

$lang['Post_Announcement'] = 'Anunţ';
$lang['Post_Sticky'] = 'Important';
$lang['Post_Normal'] = 'Normal';

$lang['Confirm_delete'] = 'Sunteţi sigur că vreţi să ştergeţi acest mesaj?';
$lang['Confirm_delete_poll'] = 'Sunteţi sigur că vreţi să ştergeţi acest chestionar?';

$lang['Flood_Error'] = 'Nu puteţi să trimiteţi un mesaj nou la un interval atât de scurt dupa anteriorul; vă rugăm, încercaţi mai târziu.';
$lang['Empty_subject'] = 'Trebuie specificat titlul';
$lang['Empty_message'] = 'Trebuie sa scrieţi un mesaj';
$lang['Forum_locked'] = 'Acest forum este închis, nu se pot scrie, crea, răspunde sau modifica subiecte';
$lang['Topic_locked'] = 'Acest subiect este închis, nu se pot crea sau răspunde la mesaje';
$lang['No_post_id'] = 'Trebuie sa selectaţi un mesaj pentru modificare';
$lang['No_topic_id'] = 'Trebuie sa selectaţi un mesaj pentru a da un răspuns la';
$lang['No_valid_mode'] = 'Puteţi doar să adăugaţi, să modificaţi, să citaţi sau să răspundeţi la mesaje; reveniţi şi încercaţi din nou';
$lang['No_such_post'] = 'Aici nu este nici un mesaj, reveniţi şi încercaţi din nou';
$lang['Edit_own_posts'] = 'Scuze dar puteţi modifica doar mesajele dumneavoastră';
$lang['Delete_own_posts'] = 'Scuze dar puteţi şterge doar mesajele dumneavoastră';
$lang['Cannot_delete_replied'] = 'Scuze dar nu puteţi şterge mesaje la care s-a răspuns deja';
$lang['Cannot_delete_poll'] = 'Scuze dar nu puteţi şterge un chestionar aflat în derulare';
$lang['Empty_poll_title'] = 'Trebuie să introduceţi un titlu pentru chestionar';
$lang['To_few_poll_options'] = 'Trebuie să introduceţi cel puţin două opţiuni de vot în chestionar';
$lang['To_many_poll_options'] = 'Aţi încercat să introduceţi prea multe opţiuni de vot în chestionar';
$lang['Post_has_no_poll'] = 'Acest mesaj nu are chestionar';
$lang['Already_voted'] = 'Aţi votat deja în acest chestionar';
$lang['No_vote_option'] = 'Trebuie să specificaţi o opţiune la votare';

$lang['Add_poll'] = 'Adaugă un chestionar';
$lang['Add_poll_explain'] = 'Dacă nu vreţi să adăugaţi un chestionar la mesajul dumneavoastră, lăsaţi câmpurile necompletate';
$lang['Poll_question'] = 'Chestionar';
$lang['Poll_option'] = 'Opţiunile chestionarului';
$lang['Add_option'] = 'Adaugă o opţiune';
$lang['Update'] = 'Actualizează';
$lang['Delete'] = 'Şterge';
$lang['Poll_for'] = 'Rulează chestionarul pentru';
$lang['Days'] = 'Zile'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Introduceţi 0 sau lăsaţi necompletat pentru un chestionar nelimitat în timp ]';
$lang['Delete_poll'] = 'Şterge chestionarul';

$lang['Disable_HTML_post'] = 'Dezactivează codul HTML în acest mesaj';
$lang['Disable_BBCode_post'] = 'Dezactivează codul BBCode în acest mesaj';
$lang['Disable_Smilies_post'] = 'Dezactivează zâmbetele în acest mesaj';

$lang['HTML_is_ON'] = 'Codul HTML este <u>Activat</u>';
$lang['HTML_is_OFF'] = 'Codul HTML este <u>Dezactivat</u>';
$lang['BBCode_is_ON'] = '%sCodulBB%s este <u>Activat</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sCodul%s este <u>Dezactivat</u>';
$lang['Smilies_are_ON'] = 'Zâmbetele sunt <u>Activate</u>';
$lang['Smilies_are_OFF'] = 'Zâmbetele sunt <u>Dezactivate</u>';

$lang['Attach_signature'] = 'Adaugă semnătura (semnătura poate fi schimbată din Profil)';
$lang['Notify'] = 'Anunţa-mă când apare un răspuns';

$lang['Stored'] = 'Mesajul a fost introdus cu succes';
$lang['Deleted'] = 'Mesajul a fost şters cu succes';
$lang['Poll_delete'] = 'Chestionarul a fost şters cu succes';
$lang['Vote_cast'] = 'Votul a fost acceptat';

$lang['Topic_reply_notification'] = 'Anunţ de răspuns la mesaj';

$lang['bbcode_b_help'] = "Text îngroşat (bold): [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Text înclinat (italic): [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Text subliniat: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Text citat: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Cod sursă: [code]cod sursa[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Listă: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Listă ordonată: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Inserează imagine: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Inserează URL: [url]http://url[/url] sau [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Închide toate tag-urile de cod BB deschise";
$lang['bbcode_s_help'] = "Culoare text: [color=red]text[/color]  Sfat: poţi folosi şi color=#FF0000";
$lang['bbcode_f_help'] = "Mărime font: [size=x-small]text mărunt[/size]";

$lang['Emoticons'] = 'Iconiţe emotive';
$lang['More_emoticons'] = 'Alte iconiţe emotive';

$lang['Font_color'] = "Culoare text";
$lang['color_default'] = "Implicită";
$lang['color_dark_red'] = "Roşu închis";
$lang['color_red'] = "Roşu";
$lang['color_orange'] = "Oranj";
$lang['color_brown'] = "Maro";
$lang['color_yellow'] = "Galben";
$lang['color_green'] = "Verde";
$lang['color_olive'] = "Măsliniu";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Albastru";
$lang['color_dark_blue'] = "Albastru închis";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violet";
$lang['color_white'] = "Alb";
$lang['color_black'] = "Negru";

$lang['Font_size'] = "Mărime text";
$lang['font_tiny'] = "Măruntă";
$lang['font_small'] = "Mică";
$lang['font_normal'] = "Normală";
$lang['font_large'] = "Mare";
$lang['font_huge'] = "Imensă";

$lang['Close_Tags'] = 'Închide tag-uri';
$lang['Styles_tip'] = 'Sfat: Stilurile pot fi aplicate imediat textului selectat';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Mesagerie privată';

$lang['Login_check_pm'] = 'Mesaje private';
$lang['New_pms'] = 'Aveţi %d mesaje noi'; // You have 2 new messages
$lang['New_pm'] = 'Aveţi %d mesaj nou'; // You have 1 new message
$lang['No_new_pm'] = 'Nu aveţi mesaje noi';
$lang['Unread_pms'] = 'Aveţi %d mesaje necitite';
$lang['Unread_pm'] = 'Aveţi %d mesaj necitit';
$lang['No_unread_pm'] = 'Nu aveţi mesaje necitite';
$lang['You_new_pm'] = 'Un mesaj nou privat aşteaptă în cutia cu mesaje';
$lang['You_new_pms'] = 'Mai multe mesaje noi aşteaptă în cutia cu mesaje';
$lang['You_no_new_pm'] = 'Nu sunt mesaje noi în aşteptare în cutia cu mesaje';

$lang['Unread_message'] = 'Mesaj necitit';
$lang['Read_message'] = 'Mesaj citit';

$lang['Read_pm'] = 'Mesaj citit';
$lang['Post_new_pm'] = 'Scrie mesaj';
$lang['Post_reply_pm'] = 'Retrimite mesajul';
$lang['Post_quote_pm'] = 'Comentează mesajul';
$lang['Edit_pm'] = 'Modifică mesajul';

$lang['Inbox'] = 'Cutia cu mesaje';
$lang['Outbox'] = 'Cutia cu mesaje în curs de trimitere';
$lang['Savebox'] = 'Cutia cu mesaje salvate';
$lang['Sentbox'] = 'Cutia cu mesaje trimise';
$lang['Flag'] = 'Marcaj';
$lang['Subject'] = 'Subiect';
$lang['From'] = 'De la';
$lang['To'] = 'Către';
$lang['Date'] = 'Data';
$lang['Mark'] = 'Marcat';
$lang['Sent'] = 'Trimis';
$lang['Saved'] = 'Salvat';
$lang['Delete_marked'] = 'Şterge mesajele marcate';
$lang['Delete_all'] = 'Şterge toate mesajele';
$lang['Save_marked'] = 'Salvează mesajele marcate';
$lang['Save_message'] = 'Salvează mesajul';
$lang['Delete_message'] = 'Şterge mesajul';

$lang['Display_messages'] = 'Afişează mesajele din urmă'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Toate mesajele';

$lang['No_messages_folder'] = 'Nu aveţi mesaje noi în acestă cutie pentru mesaje';

$lang['PM_disabled'] = 'Mesajele private au fost dezactivate de pe acest panou';
$lang['Cannot_send_privmsg'] = 'Scuze dar administratorul vă împiedică în trimiterea mesajelor private';
$lang['No_to_user'] = 'Trebuie specificat un nume de utilizator pentru a putea trimite mesajul';
$lang['No_such_user'] = 'Scuze dar acest utilizator nu există';

$lang['Disable_HTML_pm'] = "Deactivează codul HTML în acest mesaj";
$lang['Disable_BBCode_pm'] = "Deactivează codul BB în acest mesaj";
$lang['Disable_Smilies_pm'] = "Deactivează zâmbetele în acest mesaj";

$lang['Message_sent'] = 'Mesajul a fost trimis';

$lang['Click_return_inbox'] = "Apăsaţi %saici%s pentru a reveni la cutia cu mesaje";
$lang['Click_return_index'] = "Apăsaţi %saici%s pentru a reveni la Pagina de start a forumului";

$lang['Send_a_new_message'] = "Trimite un nou mesaj privat";
$lang['Send_a_reply'] = "Răspunde la un mesaj privat";
$lang['Edit_message'] = "Modifică un mesaj privat";

$lang['Notification_subject'] = 'Un nou mesaj privat a sosit';

$lang['Find_username'] = "Caută un utilizator";
$lang['Find'] = "Caută";
$lang['No_match'] = "Nu a fost găsit nici un utilizator";

$lang['No_post_id'] = "ID-ul mesajului nu a fost specificat";
$lang['No_such_folder'] = "Directorul specificat nu există";
$lang['No_folder'] = "Nu a fost specificat directorul";

$lang['Mark_all'] = "Marchează toate";
$lang['Unmark_all'] = "Demarchează toate";

$lang['Confirm_delete_pm'] = "Sunteţi sigur că vreţi să ştergeţi acest mesaj?";
$lang['Confirm_delete_pms'] = "Sunteţi sigur că vreţi să ştergeţi aceste mesaje?";

$lang['Inbox_size'] = "Cutia dumneavoastră cu mesaje este %d%% plină"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Cutia dumneavoastră cu mesaje trimise este %d%% plină";
$lang['Savebox_size'] = "Cutia dumneavoastră cu mesaje salvate este %d%% plină";

$lang['Click_view_privmsg'] = "Apăsaţi %saici%s pentru a ajunge la cutia dumneavoastră cu mesaje";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Vezi profilul : %s'; // %s is username
$lang['About_user'] = 'Totul despre %s'; // %s is username

$lang['Preferences'] = 'Preferinţe';
$lang['Items_required'] = 'Ce este marcat cu * este obligatoriu';
$lang['Registration_info'] = 'Informaţii de înregistrare';
$lang['Profile_info'] = 'Informaţii despre profil';
$lang['Profile_info_warn'] = 'Aceste informaţii vor fi făcute publice';
$lang['Avatar_panel'] = 'Panoul de control al imaginilor asociate';
$lang['Avatar_gallery'] = 'Galeria de imagini';

$lang['Website'] = 'Site Web';
$lang['Location'] = 'Locaţie';
$lang['Contact'] = 'Contact';
$lang['Email_address'] = 'Adresa de email';
$lang['Send_private_message'] = 'Trimite mesaj privat';
$lang['Hidden_email'] = '[ Ascuns ]';
$lang['Interests'] = 'Interese';
$lang['Occupation'] = 'Ocupaţia';
$lang['Poster_rank'] = 'Rangul utilizatorului';

$lang['Total_posts'] = 'Numărul total de mesaje';
$lang['User_post_pct_stats'] = '%.2f%% din total'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f mesaje pe zi'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Caută toate mesajele lui %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Scuze dar acest utilizator nu există';
$lang['Wrong_Profile'] = 'Nu puteţi modifica un profil dacă nu este propriul dumneavoastră profil.';

$lang['Only_one_avatar'] = 'Se poate specifica doar un tip de imagine asociată';
$lang['File_no_data'] = 'Fişierul specificat de URL-ul dumneavoastră nu conţine informaţii';
$lang['No_connection_URL'] = 'Conexiunea nu poate fi facută la URL-ul specificat';
$lang['Incomplete_URL'] = 'URL-ul introdus este incomplet';
$lang['Wrong_remote_avatar_format'] = 'URL-ul către imaginea asociată nu este valid';
$lang['No_send_account_inactive'] = 'Scuze, dar parola dumneavoastră nu mai poate fi folosită deoarece contul este inactiv. Te rog contacteaza administratorul forumului pentru mai multe informatii';

$lang['Always_smile'] = 'Folosesc întotdeauna zâmbete';
$lang['Always_html'] = 'Folosesc întotdeauna cod HTML';
$lang['Always_bbcode'] = 'Folosesc întotdeauna cod BB';
$lang['Always_add_sig'] = 'Adaugă întotdeauna semnătura mea la mesaje';
$lang['Always_notify'] = 'Anunţă-mă întotdeauna de răspunsuri la mesajele mele';
$lang['Always_notify_explain'] = 'Trimite-mi un email când cineva răspunde la mesajele mele. Opţiunea poate fi schimbată la fiecare mesaj nou.';

$lang['Board_style'] = 'Stilul interfeţei';
$lang['Board_lang'] = 'Limba interfeţei';
$lang['No_themes'] = 'Nici o temă în baza de date';
$lang['Timezone'] = 'Timpul zonal';
$lang['Date_format'] = 'Formatul datei';
$lang['Date_format_explain'] = 'Sintaxa utilizată este identică cu cea folosită de funcţia PHP <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a>';
$lang['Signature'] = 'Semnătura';
$lang['Signature_explain'] = 'Acesta este un bloc de text care poate fi adăugat mesajelor scrise de dumneavoastră. Limita este de %d caractere';
$lang['Public_view_email'] = 'Afişează întotdeauna adresa mea de email';

$lang['Current_password'] = 'Parola curentă';
$lang['New_password'] = 'Parola nouă';
$lang['Confirm_password'] = 'Confirmaţi parola';
$lang['Confirm_password_explain'] = 'Trebuie să confirmaţi parola curentă dacă vreţi să o schimbaţi sau vreţi să aveţi altă adresă de email';
$lang['password_if_changed'] = 'Este necesar să specificaţi parola dacă vreţi să o schimbaţi';
$lang['password_confirm_if_changed'] = 'Este necesar să confirmaţi parola dacă aţi schimbat-o anterior';

$lang['Avatar'] = 'Imagine asociată (Avatar)';
$lang['Avatar_explain'] = 'Afişează o imagine micuţa sub detaliile dumneavoastră din mesaje. Doar o imagine poate fi afişată în acelaşi timp, mărimea ei nu poate fi mai mare de %d pixeli ca înalţime şi %d ca lăţime şi mărimea fişierului poate fi cel mult de %dko.';
$lang['Upload_Avatar_file'] = 'Încărcaţi de pe calculatorul dumneavoastră imaginea asociată';
$lang['Upload_Avatar_URL'] = 'Încărcaţi cu un URL imaginea asociată';
$lang['Upload_Avatar_URL_explain'] = 'Introduceţi URL-ul locului unde este imaginea asociatăr pentru a fi copiată pe acest site.';
$lang['Pick_local_Avatar'] = 'Alegeţi o imagine asociată din galerie';
$lang['Link_remote_Avatar'] = 'Legătura spre un alt site ce conţine imagini asociate';
$lang['Link_remote_Avatar_explain'] = 'Introduceţi URL-ul locului unde este imaginea asociată pentru a face o legătură la ea.';
$lang['Avatar_URL'] = 'URL-ul imaginii asociate';
$lang['Select_from_gallery'] = 'Alegeţi o imagine asociată din galerie';
$lang['View_avatar_gallery'] = 'Arată galeria de imagini asociate';

$lang['Select_avatar'] = 'Alegeţi o imagine asociată';
$lang['Return_profile'] = 'Renunţaţi la imaginea asociată';
$lang['Select_category'] = 'Alegeţi o categorie';

$lang['Delete_Image'] = 'Ştergeţi imaginea';
$lang['Current_Image'] = 'Imaginea curentă';

$lang['Notify_on_privmsg'] = 'Atenţionează-mă când primesc un mesaj privat nou';
$lang['Popup_on_privmsg'] = 'Deschide o fereastră când primesc un mesaj privat nou';
$lang['Popup_on_privmsg_explain'] = 'Unele şabloane pot deschide o fereastră nouă pentru a vă informa de faptul că aţi primit un mesaj privat nou';
$lang['Hide_user'] = 'Ascundeţi indicatorul de conectare';

$lang['Profile_updated'] = 'Profilul dumneavoastră a fost actualizat';
$lang['Profile_updated_inactive'] = 'Profilul dumneavoastră a fost actualizat, dar deoarece au fost modificate detalii importante contul este momentan inactiv. Verificaţi-vă email-ul pentru a afla cum iţi va fi reactivat contul sau dacă este necesară intervenţia administratorului aşteptaţi până ce acesta vă va reactiva contul.';

$lang['Password_mismatch'] = 'Parolele introduse nu sunt valide';
$lang['Current_password_mismatch'] = 'Parola furnizata de dumneavoastră nu este gasită în baza de date';
$lang['Password_long'] = 'Parola nu trebuie să depăşească 32 de caractere';
$lang['Username_taken'] = 'Scuze, dar numele de utilizator introdus, există deja';
$lang['Username_invalid'] = 'Scuze, dar numele de utilizator introdus conţine caractere greşite, ca de exemplu: \'';
$lang['Username_disallowed'] = 'Scuze, dar acest nume de utilizator a fost interzis';
$lang['Email_taken'] = 'Scuze, dar adresa de email introdusă este deja folosită de un alt utilizator';
$lang['Email_banned'] = 'Scuze, dar această adresă de email a fost interzisă';
$lang['Email_invalid'] = 'Scuze, dar această adresă de email nu este corectă';
$lang['Signature_too_long'] = 'Semnătura dumneavoastră este prea lungă';
$lang['Fields_empty'] = 'Trebuie să completaţi câmpurile obligatorii';
$lang['Avatar_filetype'] = 'Imaginile asociate trebuie să fie de tipul: .jpg, .gif sau .png';
$lang['Avatar_filesize'] = 'Imaginile asociate trebuie să fie mai mici de: %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Imaginile asociate trebuie să fie mai mici de %d pixeli pe lăţime şi %d pixeli pe înălţime';

$lang['Welcome_subject'] = 'Bine aţi venit pe forumul %s '; // Welcome to my.com forums
$lang['New_account_subject'] = 'Cont nou de utilizator';
$lang['Account_activated_subject'] = 'Contul a fost activat';

$lang['Account_added'] = 'Vă mulţumim pentru înregistrare, contul a fost creat. Puteţi să vă autentificaţi cu numele de utilizator şi parola';
$lang['Account_inactive'] = 'Contul a fost creat. Acest forum necesită activarea contului, o cheie de activare a fost trimisa pe adresa de email furnizata de dumneavoastră. Vă rugăm să vă verificaţi căsuţa de email pentru mai multe informaţii.';
$lang['Account_inactive_admin'] = 'Contul a fost creat. Acest forum necesită activarea contului de către administrator. Veţi fi informat prin email când contul va fi activat.';
$lang['Account_active'] = 'Contul a fost activat. Multumim pentru inregistrare.';
$lang['Account_active_admin'] = 'Contul a fost activat';
$lang['Reactivate'] = 'Reactivaţi-vă contul!';
$lang['Already_activated'] = 'Contul a fost deja activat';
$lang['COPPA'] = 'Contul a fost creat dar trebuie sa fie aprobat, verificaţi-vă, vă rugăm, casuţa de email.';

$lang['Registration'] = 'Termenii acordului de înregistrare';
$lang['Reg_agreement'] = 'Întotdeauna administratorii şi moderatorii acestui forum vor încerca să îndepărteze sau să modifice orice material deranjant cât mai repede posibil; este imposibil să parcurgă fiecare mesaj în parte. Din acest motiv trebuie să ştiţi că toate mesajele exprimă punctul de vedere şi opiniile autorilor şi nu ale administratorilor,
moderatorilor sau a web master-ului (excepţie făcând mesajele scrise chiar de către ei) şi de aceea ei nu pot fi făcuţi responsabili.<br /><br />Trebuie să fiţi de acord să nu publicaţi mesaje cu conţinut abuziv, obscen, vulgar, calomnios, de ură, ameninţător, sexual sau orice alt material ce poate viola legile aflate în vigoare. Dacă publicaţi astfel de materiale puteţi fi imediat şi pentru totdeauna îndepărtat din forum (şi firma care vă oferă accesul la Internet va fi anunţată). Adresele IP ale tuturor mesajelor trimise sunt stocate pentru a fi de ajutor în rezolvarea unor astfel de încălcări ale regulilor. Trebuie să fiţi de acord că webmaster-ul, administratorul şi moderatorii acestui forum au dreptul de a şterge, modifica sau închide orice subiect, oricând cred ei că acest lucru se impune. Ca utilizator, trebuie să fiţi de acord că orice informaţie introdusă de dumneavoastră să fie stocată în baza de date. Aceste informaţii nu vor fi arătate unei terţe persoane fără consimţământul webmaster-ului, administratorului şi moderatorilor care nu pot fi facuţi responsabili de atacurile de furt sau de vandalism care pot să ducă la compromiterea datelor.<br /><br />Acest forum utilizează fişierele tip cookie pentru a stoca informaţiile pe calculatorul dumneavoastră. Aceste fişiere cookie nu conţin informaţii despre alte aplicaţii ci ele sunt folosite doar pentru uşurarea navigării pe forum. Adresele de email sunt utilizate doar pentru confirmarea înregistrării dumneavoastră ca utilizator şi pentru parolă (şi pentru trimiterea unei noi parole dacă aţi uitat-o pe cea curentă).<br /><br />Prin apăsarea pe butonul de înregistrare se consideră că sunteţi de acord cu aceste condiţii.';

$lang['Agree_under_13'] = 'Sunt de acord cu aceste condiţii şi declar că am <b>sub</b> 13 ani';
$lang['Agree_over_13'] = 'Sunt de acord cu aceste condiţii şi declar că am <b>peste</b> 13 ani';
$lang['Agree_not'] = 'Nu sunt de acord cu aceste condiţii';

$lang['Wrong_activation'] = 'Cheia de activare furnizată nu se regăseşte în baza de date';
$lang['Send_password'] = 'Trimiteţi-mi o parolă nouă';
$lang['Password_updated'] = 'O parola nouă a fost creată, vă rugăm verificaţi-vă căsuţa de email pentru informaţiile de activare';
$lang['No_email_match'] = 'Adresa de email furnizată nu corespunde celei asociate acestui utilizator';
$lang['New_password_activation'] = 'Activarea parolei noi';
$lang['Password_activated'] = 'Contul dumneavoastră a fost reactivat. La autentificare utilizaţi parola trimisă în la adresa de email primită';

$lang['Send_email_msg'] = "Trimite un email";
$lang['No_user_specified'] = "Nu a fost specificat utilizatorul";
$lang['User_prevent_email'] = "Acest utilizator nu doreşte să primeasca mesaje. Încearcaţi să-i trimiteţi un mesaj privat";
$lang['User_not_exist'] = "Acest utilizator nu există";
$lang['CC_email'] = "Trimiteţi-vă o copie";
$lang['Email_message_desc'] = "Acest mesaj va fi trimis în mod text, nu include cod HTML sau cod BB. Adresa de întoarcere pentru acest mesaj va fi setată către adresa dumneavoastră de email.";
$lang['Flood_email_limit'] = "Nu puteţi trimite înca un email în acest moment, încearcaţi mai târziu.";
$lang['Recipient'] = "Recipient";
$lang['Email_sent'] = "Mesajul a fost trimis";
$lang['Send_email'] = "Trimite un mesaj";
$lang['Empty_subject_email'] = "Trebuie specificat un subiect pentru mesaj";
$lang['Empty_message_email'] = "Trebuie introdus conţinut în mesaj";


//
// Visual confirmation system strings
//
$lang['Confirm_code_wrong'] = 'Codul de confirmare pe care l-aţ introdus este incorect';
$lang['Too_many_registers'] = 'Aţi depăşit numărul de înregistrări setat pentru această sesiune. Vă rugăm încercaţi mai tarziu.';
$lang['Confirm_code_impaired'] = 'Dacă nu puteţi citi codul sau acesta este neinteligibil, vă rugăm contactaţi %sAdministrator%s pentru ajutor .';
$lang['Confirm_code'] = 'Cod de confirmare';
$lang['Confirm_code_explain'] = 'Introduceţi codul exact cum îl vedeţi. Codul este case sensitive şi zero este tăiat de o linie diagonală.';

//
// Memberslist
//
$lang['Select_sort_method'] = 'Selectaţi metoda de sortare';
$lang['Sort'] = 'Sortează';
$lang['Sort_Top_Ten'] = 'Top 10 utilizatori';
$lang['Sort_Joined'] = 'Data înregistrării';
$lang['Sort_Username'] = 'Nume utilizator';
$lang['Sort_Location'] = 'Locaţia';
$lang['Sort_Posts'] = 'Număr total de mesaje';
$lang['Sort_Email'] = 'Email';
$lang['Sort_Website'] = 'Site Web';
$lang['Sort_Ascending'] = 'Ascendent';
$lang['Sort_Descending'] = 'Descendent';
$lang['Order'] = 'Ordine';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Panoul de control al grupurilor';
$lang['Group_member_details'] = 'Detalii despre membrii grupului';
$lang['Group_member_join'] = 'Aderaţi la un grup';

$lang['Group_Information'] = 'Informaţii despre grup';
$lang['Group_name'] = 'Numele grupului';
$lang['Group_description'] = 'Descrierea grupului';
$lang['Group_membership'] = 'Membrii grupului';
$lang['Group_Members'] = 'Membrii grupului';
$lang['Group_Moderator'] = 'Moderatorul grupului';
$lang['Pending_members'] = 'Membri în aşteptare';

$lang['Group_type'] = 'Tipul grupului';
$lang['Group_open'] = 'Grup deschis';
$lang['Group_closed'] = 'Grup închis';
$lang['Group_hidden'] = 'Grup ascuns';

$lang['Current_memberships'] = 'Grupurile din care fac parte';
$lang['Non_member_groups'] = 'Grupurile din care nu fac parte';
$lang['Memberships_pending'] = 'Membri în aşteptare';

$lang['No_groups_exist'] = 'Nu există grupuri';
$lang['Group_not_exist'] = 'Acest grup de utilizatori nu există';

$lang['Join_group'] = 'Aderă la grup';
$lang['No_group_members'] = 'Acest grup nu are membri';
$lang['Group_hidden_members'] = 'Acest grup este ascuns, nu-i puteţi vedea membrii';
$lang['No_pending_group_members'] = 'Acest grup nu are membri în aşteptare';
$lang['Group_joined'] = 'Înscrierea la acest grup a fost facută cu succes.<br />Veţi fi anunţat când cererea dumneavoastră va fi aprobată de moderatorul grupului';
$lang['Group_request'] = 'A fost depusă o cerere de aderare la grupul dumneavoastră';
$lang['Group_approved'] = 'Cererea dumneavoastră de aderare la grup a fost aprobată';
$lang['Group_added'] = 'Aţi fost acceptat la acest grup de utilizatori';
$lang['Already_member_group'] = 'Sunteţi deja membru al acestui grup';
$lang['User_is_member_group'] = 'Utilizatorul este deja membru al acestui grup';
$lang['Group_type_updated'] = 'Modificarea tipului de grup s-a realizat cu succes';

$lang['Could_not_add_user'] = 'Utilizatorul selectat nu există';
$lang['Could_not_anon_user'] = 'Un Anonim nu poate fi facut membru de grup';

$lang['Confirm_unsub'] = 'Sunteţi sigur că vreţi să părăsiţi acest grup?';
$lang['Confirm_unsub_pending'] = 'Cererea dumneavoastră de aderare la acest grup nu a fost înca aprobată, sunteţi sigur că vreţi să-l părăsiţi?';

$lang['Unsub_success'] = 'Dorinţa dumneavoastră de părăsire a grupului a fost îndeplinită.';

$lang['Approve_selected'] = 'Aprobă selecţiile';
$lang['Deny_selected'] = 'Respinge selecţiile';
$lang['Not_logged_in'] = 'Trebuie să fiţi autentificat pentru a adera la grup.';
$lang['Remove_selected'] = 'Şterge selecţiile';
$lang['Add_member'] = 'Adaugă membru';
$lang['Not_group_moderator'] = 'Nu sunteţi moderator în acest grup; prin urmare nu puteţi efectua aceste acţiuni.';

$lang['Login_to_join'] = 'Autentificaţi-vă pentru a adera la grup sau pentru a organiza membrii';
$lang['This_open_group'] = 'Acesta este un grup deschis, apăsaţi aici pentru a deveni membru';
$lang['This_closed_group'] = 'Acesta este un grup închis, nu mai acceptă noi membri';
$lang['This_hidden_group'] = 'Acesta este un grup ascuns, cererile de aderare automate nu sunt acceptate';
$lang['Member_this_group'] = 'Sunteţi membru al acestui grup';
$lang['Pending_this_group'] = 'Cererea de membru al acestui grup este în aşteptare';
$lang['Are_group_moderator'] = 'Sunteţi moderatorul grupului';
$lang['None'] = 'Nici unul';

$lang['Subscribe'] = "Înscriere";
$lang['Unsubscribe'] = "Părăsire";
$lang['View_Information'] = "Vizualizare informaţii";

//
// Search
//
$lang['Search_query'] = 'Interogare de căutare';
$lang['Search_options'] = 'Opţiuni de căutare';

$lang['Search_keywords'] = 'Caută după cuvintele cheie';
$lang['Search_keywords_explain'] = 'Puteţi folosi <u>AND</u> pentru a defini cuvintele ce trebuie să fie în rezultate, <u>OR</u> pentru a defini cuvintele care pot sa fie în rezultat, şi <u>NOT</u> pentru a defini cuvintele care nu trebuie să fie în rezultate. Se poate utiliza * pentru părţi de cuvinte.';
$lang['Search_author'] = 'Caută după autor';
$lang['Search_author_explain'] = 'Utilizaţi * pentru parţi de cuvinte';

$lang['Search_for_any'] = "Caută dupa oricare dintre termeni sau utilizează o interogare ca intrare";
$lang['Search_for_all'] = "Caută dupa toţi termenii";
$lang['Search_title_msg'] = "Caută în titlul subiectelor şi în textele mesajelor";
$lang['Search_msg_only'] = "Caută doar în textele mesajelor";

$lang['Return_first'] = 'Întoarce primele'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'de caractere ale mesajelor';

$lang['Search_previous'] = 'Caută în urmă'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Sortează după';
$lang['Sort_Time'] = 'Data mesajului';
$lang['Sort_Post_Subject'] = 'Subiectul mesajului';
$lang['Sort_Topic_Title'] = 'Titlul subiectului';
$lang['Sort_Author'] = 'Autor';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'Afişează rezultatele ca';
$lang['All_available'] = 'Disponibile toate';
$lang['No_searchable_forums'] = 'nu aveţi drepturi de căutare în nici un forum de pe acest site';

$lang['No_search_match'] = 'Nici un subiect sau mesaj nu îndeplineşte criteriul introdus la căutare';
$lang['Found_search_match'] = 'Căutarea a gasit %d rezultat'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Căutarea a gasit %d rezultate'; // eg. Search found 24 matches
$lang['Search_Flood_Error'] = 'Nu poţi face altă căutare atât de recentă faţă de ultima; te rog încearcă din nou în câteva clipe.';

$lang['Close_window'] = 'Închide fereastra';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Ne pare rău dar doar cei care sunt %s pot pune anunţuri în acest forum';
$lang['Sorry_auth_sticky'] = 'Ne pare rău dar doar cei care sunt %s pot pune mesaje importante în acest forum';
$lang['Sorry_auth_read'] = 'Ne pare rău dar doar cei care sunt %s pot citi subiectele din acest forum';
$lang['Sorry_auth_post'] = 'Ne pare rău dar doar cei care sunt %s pot scrie subiecte în acest forum';
$lang['Sorry_auth_reply'] = 'Ne pare rău dar doar cei care sunt %s pot răspunde în acest forum';
$lang['Sorry_auth_edit'] = 'Ne pare rău dar doar cei care sunt %s pot modifica un mesaj în acest forum';
$lang['Sorry_auth_delete'] = 'Ne pare rău dar doar cei care sunt %s pot sterge un mesaj din acest forum';
$lang['Sorry_auth_vote'] = 'Ne pare rău dar doar cei care sunt %s pot vota în chestionarele din acest forum';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>utilizator anonim</b>';
$lang['Auth_Registered_Users'] = '<b>utilizator înregistrat</b>';
$lang['Auth_Users_granted_access'] = '<b>utilizatori cu drepturi speciale de acces</b>';
$lang['Auth_Moderators'] = '<b>moderatori</b>';
$lang['Auth_Administrators'] = '<b>administratori</b>';

$lang['Not_Moderator'] = 'Dumneavoastră nu sunteţi moderator în acest forum';
$lang['Not_Authorised'] = 'Nu sunteţi autorizat';

$lang['You_been_banned'] = 'Accesul dumneavoastră în acest forum este blocat<br />Vă rugăm să contactaţi webmaster-ul sau administratorul pentru mai multe informaţii';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Sunt 0 utilizatori înregistraţi şi '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Sunt %d utilizatori înregistraţi şi '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Este %d utilizator înregistrat şi '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = '0 utilizatori ascunşi conectaţi'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d utilizatori ascunşi conectaţi'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d utilizator ascuns conectat'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Sunt %d utilizatori vizitatori conectaţi'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Sunt 0 utilizatori vizitatori conectaţi'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Este %d utilizator vizitator conectat'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Nici un utilizator nu navighează acum în acest forum';

$lang['Online_explain'] = 'Aceste date se bazează pe utilizatorii activi de peste 5 minute';

$lang['Forum_Location'] = 'Unde se găseşte';
$lang['Last_updated'] = 'Conectat la';

$lang['Forum_index'] = 'Pagina de start a forumului';
$lang['Logging_on'] = 'Autentificare';
$lang['Posting_message'] = 'Scrie un mesaj';
$lang['Searching_forums'] = 'Caută în forumuri';
$lang['Viewing_profile'] = 'Vezi profilul';
$lang['Viewing_online'] = 'Vezi cine este conectat';
$lang['Viewing_member_list'] = 'Vezi lista cu membri';
$lang['Viewing_priv_msgs'] = 'Vezi mesajele private';
$lang['Viewing_FAQ'] = 'Vezi lista cu întrebari frecvente';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Panoul de control al moderatorului';
$lang['Mod_CP_explain'] = 'Utilizând formularul de mai jos puteţi efectua operaţii de moderare masivă în forum. Puteţi închide, deschide, muta sau şterge orice număr de subiecte.';

$lang['Select'] = 'Selectează';
$lang['Delete'] = 'Şterge';
$lang['Move'] = 'Mută';
$lang['Lock'] = 'Închide';
$lang['Unlock'] = 'Deschide';

$lang['Topics_Removed'] = 'Subiectele selectate au fost cu succes şterse din baza de date.';
$lang['Topics_Locked'] = 'Subiectele selectate au fost închise';
$lang['Topics_Moved'] = 'Subiectele selectate au fost mutate';
$lang['Topics_Unlocked'] = 'Subiectele selectate au fost deschise';
$lang['No_Topics_Moved'] = 'Nici un subiect nu a fost mutat';

$lang['Confirm_delete_topic'] = "Sunteţi sigur că vreţi să ştergeţi subiectul/subiectele selectate?";
$lang['Confirm_lock_topic'] = "Sunteţi sigur că vreţi să închideţi subiectul/subiectele selectate?";
$lang['Confirm_unlock_topic'] = "Sunteţi sigur că vreţi să deschideţi subiectul/subiectele selectate?";
$lang['Confirm_move_topic'] = "Sunteţi sigur că vreţi să mutaţi subiectul/subiectele selectate?";

$lang['Move_to_forum'] = 'Mută forumul';
$lang['Leave_shadow_topic'] = 'Pastrează o umbră a subiectului în vechiul forum.';

$lang['Split_Topic'] = 'Panoul de control a împărţirii subiectelor';
$lang['Split_Topic_explain'] = 'Utilizând formularul de mai jos puteţi împărţi un subiect în două, pe rând sau începand de la cel deja selectat';
$lang['Split_title'] = 'Titlul noului subiect';
$lang['Split_forum'] = 'Forumul pentru noul subiect';
$lang['Split_posts'] = 'Împarte mesajele selectate';
$lang['Split_after'] = 'Împarte mesajul selectat';
$lang['Topic_split'] = 'Subiectul selectat a fost împărţit cu succes';

$lang['Too_many_error'] = 'Aţi selectat prea multe mesaje. Puteţi să selectaţi doar un mesaj la care să împărţiţi subiectul!';

$lang['None_selected'] = 'Nu aţi selectat nici un subiect pentru a efectua aceasta operaţie. Vă rugăm întoarceţi-vă şi selectaţi cel puţin un subiect.';
$lang['New_forum'] = 'Forum nou';

$lang['This_posts_IP'] = 'IP-ul mesajului';
$lang['Other_IP_this_user'] = 'Alte adrese IP de la care acest utilizator a trimis mesaje';
$lang['Users_this_IP'] = 'Utilizatori care au trimis mesaje de la acest IP';
$lang['IP_info'] = 'Informaţii IP';
$lang['Lookup_IP'] = 'Vizualizare IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Ora este %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 ore';
$lang['-11'] = 'GMT - 11 ore';
$lang['-10'] = 'GMT - 10 ore';
$lang['-9'] = 'GMT - 9 ore';
$lang['-8'] = 'GMT - 8 ore';
$lang['-7'] = 'GMT - 7 ore';
$lang['-6'] = 'GMT - 6 ore';
$lang['-5'] = 'GMT - 5 ore';
$lang['-4'] = 'GMT - 4 ore';
$lang['-3.5'] = 'GMT - 3.5 ore';
$lang['-3'] = 'GMT - 3 ore';
$lang['-2'] = 'GMT - 2 ore';
$lang['-1'] = 'GMT - 1 ora';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 ora';
$lang['2'] = 'GMT + 2 ore';
$lang['3'] = 'GMT + 3 ore';
$lang['3.5'] = 'GMT + 3.5 ore';
$lang['4'] = 'GMT + 4 ore';
$lang['4.5'] = 'GMT + 4.5 ore';
$lang['5'] = 'GMT + 5 ore';
$lang['5.5'] = 'GMT + 5.5 ore';
$lang['6'] = 'GMT + 6 ore';
$lang['6.5'] = 'GMT + 6.5 ore';
$lang['7'] = 'GMT + 7 ore';
$lang['8'] = 'GMT + 8 ore';
$lang['9'] = 'GMT + 9 ore';
$lang['9.5'] = 'GMT + 9.5 ore';
$lang['10'] = 'GMT + 10 ore';
$lang['11'] = 'GMT + 11 ore';
$lang['12'] = 'GMT + 12 ore';
$lang['13'] = 'GMT + 13 ore';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 ore';
$lang['tz']['-11'] = 'GMT - 11 ore';
$lang['tz']['-10'] = 'GMT - 10 ore';
$lang['tz']['-9'] = 'GMT - 9 ore';
$lang['tz']['-8'] = 'GMT - 8 ore';
$lang['tz']['-7'] = 'GMT - 7 ore';
$lang['tz']['-6'] = 'GMT - 6 ore';
$lang['tz']['-5'] = 'GMT - 5 ore';
$lang['tz']['-4'] = 'GMT - 4 ore';
$lang['tz']['-3.5'] = 'GMT - 3.5 ore';
$lang['tz']['-3'] = 'GMT - 3 ore';
$lang['tz']['-2'] = 'GMT - 2 ore';
$lang['tz']['-1'] = 'GMT - 1 ora';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 ora';
$lang['tz']['2'] = 'GMT + 2 ore';
$lang['tz']['3'] = 'GMT + 3 ore';
$lang['tz']['3.5'] = 'GMT + 3.5 ore';
$lang['tz']['4'] = 'GMT + 4 ore';
$lang['tz']['4.5'] = 'GMT + 4.5 ore';
$lang['tz']['5'] = 'GMT + 5 ore';
$lang['tz']['5.5'] = 'GMT + 5.5 ore';
$lang['tz']['6'] = 'GMT + 6 ore';
$lang['tz']['6.5'] = 'GMT + 6.5 ore';
$lang['tz']['7'] = 'GMT + 7 ore';
$lang['tz']['8'] = 'GMT + 8 ore';
$lang['tz']['9'] = 'GMT + 9 ore';
$lang['tz']['9.5'] = 'GMT + 9.5 ore';
$lang['tz']['10'] = 'GMT + 10 ore';
$lang['tz']['11'] = 'GMT + 11 ore';
$lang['tz']['12'] = 'GMT + 12 ore';
$lang['tz']['13'] = 'GMT + 13 ore';

$lang['datetime']['Sunday'] = 'Duminică';
$lang['datetime']['Monday'] = 'Luni';
$lang['datetime']['Tuesday'] = 'Marţi';
$lang['datetime']['Wednesday'] = 'Miercuri';
$lang['datetime']['Thursday'] = 'Joi';
$lang['datetime']['Friday'] = 'Vineri';
$lang['datetime']['Saturday'] = 'Sâmbătă';
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

// Begin Simple Subforums MOD
$lang['Subforums'] = 'Subforumuri';
// End Simple Subforums MO

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Informaţii';
$lang['Critical_Information'] = 'Informaţii primejdioase';

$lang['General_Error'] = 'Eroare generală';
$lang['Critical_Error'] = 'Eroare primejdioasă';
$lang['An_error_occured'] = 'A apărut o eroare';
$lang['A_critical_error'] = 'A apărut o eroare primejdioasă';

$lang['Admin_reauthenticate'] = 'Pentru a administra forumul trebuie să vă autentificaţi din nou.';

// Start add - Bin Mod
$lang['Move_bin'] = 'Move this topic to bin';
$lang['Topics_Moved_bin'] = 'The selected topics have been moved to bin.';
$lang['Bin_disabled'] = 'Bin has been disabled';
$lang['Bin_recycle'] = 'Recycle';
// End add - Bin Mod

$lang['Draft_posting']="Fă acest mesaj draft";
$lang['Draft_on']="Mesaj În Construcţie !";
$lang['Drafted_posts']="Draft-urile tale";

//====================================================================== |
//==== Start Advanced BBCode Box MOD =================================== |
//==== v5.1.0 ========================================================== |
//====
$lang['BBCode_box_hidden'] = 'Ascuns';
$lang['BBcode_box_view'] = 'Click să vezi conţinutul';
$lang['BBcode_box_hide'] = 'Click să acunzi conţinutul';
$lang['bbcode_help']['GVideo'] = 'GVideo: [GVideo]GVideo URL[/GVideo]';
$lang['GVideo_link'] = 'Link';
$lang['bbcode_help']['youtube'] = 'YouTube: [youtube]YouTube URL[/youtube]';
$lang['youtube_link'] = 'Link';
//====
//==== End Advanced BBCode Box MOD ==================================== |
//===================================================================== |

// Begin Simple Subforums MOD
$lang['Subforums'] = 'Subforumuri';
// End Simple Subforums MOD

//
// That's all Folks!
// -------------------------------------------------

?>
