<?php
/***************************************************************************
 *                            lang_main.php [romana fara diacritice]
 *                              -------------------
 *     begin                : Sat Sep 7 2002
 *     copyright 1          : (C) Daniel Tanasie
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
$lang['Replies'] = 'Raspunsuri';
$lang['Views'] = 'Vizualizari';
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
$lang['2_Weeks'] = '2 Saptamani';
$lang['1_Month'] = '1 Luna';
$lang['3_Months'] = '3 Luni';
$lang['6_Months'] = '6 Luni';
$lang['1_Year'] = '1 An';

$lang['Go'] = 'Du-te';
$lang['Jump_to'] = 'Salt la';
$lang['Submit'] = 'Trimite';
$lang['Reset'] = 'Reseteaza';
$lang['Cancel'] = 'Renunta';
$lang['Preview'] = 'Previzualizeaza';
$lang['Confirm'] = 'Confirmare';
$lang['Spellcheck'] = 'Verifica';
$lang['Yes'] = 'Da';
$lang['No'] = 'Nu';
$lang['Enabled'] = 'Activat';
$lang['Disabled'] = 'Dezactivat';
$lang['Error'] = 'Eroare';

$lang['Next'] = 'Urmatorul';
$lang['Previous'] = 'Anteriorul';
$lang['Goto_page'] = 'Du-te la pagina';
$lang['Joined'] = 'Conectat la';
$lang['IP_Address'] = 'Adresa IP';

$lang['Select_forum'] = 'Alegeti un forum';
$lang['View_latest_post'] = 'Vizualizarea celui mai vechi mesaj';
$lang['View_newest_post'] = 'Vizualizarea celui cel mai nou mesaj';
$lang['Page_of'] = 'Pagina <b>%d</b> din <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'Numarul ICQ';
$lang['AIM'] = 'Adresa AIM';
$lang['MSNM'] = 'Codul MSN Messenger';
$lang['YIM'] = 'Codul Yahoo Messenger';

$lang['Forum_Index'] = 'Pagina de start a forumului %s';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Creaza un subiect nou';
$lang['Reply_to_topic'] = 'Raspunde la subiect';
$lang['Reply_with_quote'] = 'Raspunde cu citat (quote)';

$lang['Click_return_topic'] = 'Apasati %saici%s pentru a reveni la subiect'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Apasati %saici%s pentru a incerca din nou';
$lang['Click_return_forum'] = 'Apasati %saici%s pentru a reveni la forum';
$lang['Click_view_message'] = 'Apasati %saici%s pentru a vizualiza mesajul';
$lang['Click_return_modcp'] = 'Apasati %saici%s pentru a reveni la sectiunea Panoul de Control al Moderatorului';
$lang['Click_return_group'] = 'Apasati %saici%s pentru a reveni la informatiile grupului';

$lang['Admin_panel'] = 'Panoul Administratorului';

$lang['Board_disable'] = 'Ne pare rau dar aceasta facilitate nu este momentan disponibila; va rugam incercati mai tarziu';


//
// Global Header strings
//
$lang['Registered_users'] = 'Utilizatori inregistrati:';
$lang['Browsing_forum'] = 'Utilizatori ce navigheaza in acest forum:';
$lang['Online_users_zero_total'] = 'In total aici sunt <b>0</b> utilizatori conectati : ';
$lang['Online_users_total'] = 'In total aici sunt <b>%d</b> utilizatori conectati : ';
$lang['Online_user_total'] = 'In total aici este <b>%d</b> utilizator conectat : ';
$lang['Reg_users_zero_total'] = '0 Inregistrati, ';
$lang['Reg_users_total'] = '%d Inregistrati, ';
$lang['Reg_user_total'] = '%d Inregistrati, ';
$lang['Hidden_users_zero_total'] = '0 Ascunsi si ';
$lang['Hidden_user_total'] = '%d Ascunsi si ';
$lang['Hidden_users_total'] = '%d Ascunsi si ';
$lang['Guest_users_zero_total'] = '0 Vizitatori';
$lang['Guest_users_total'] = '%d Vizitatori';
$lang['Guest_user_total'] = '%d Vizitator';
$lang['Record_online_users'] = 'Cei mai multi utilizatori conectati au fost <b>%s</b> la data de %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAdministrator%s';
$lang['Mod_online_color'] = '%sModerator%s';

$lang['You_last_visit'] = 'Ultima dumneavoastra vizita a fost %s'; // %s replaced by date/time
$lang['Current_time'] = 'Acum este: %s'; // %s replaced by time

$lang['Search_new'] = 'Vizualizarea mesajelor scrise de la ultima dumneavoastra vizita';
$lang['Search_your_posts'] = 'Vizualizarea mesajelor dumneavoastra';
$lang['Search_unanswered'] = 'Vizualizarea mesajelor la care nu s-a raspuns';

$lang['Register'] = 'Inregistrare';
$lang['Profile'] = 'Profil';
$lang['Edit_profile'] = 'Editare profil';
$lang['Search'] = 'Cautare';
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
$lang['Posted_articles_zero_total'] = 'Utilizatorii nostri au scris un numar de <b>0</b> articole'; // Number of posts
$lang['Posted_articles_total'] = 'Utilizatorii nostri au scris un numar de <b>%d</b> articole'; // Number of posts
$lang['Posted_article_total'] = 'Utilizatorii nostri au scris un numar de <b>%d</b> articol'; // Number of posts
$lang['Registered_users_zero_total'] = 'Avem <b>0</b> utilizatori inregistrati'; // # registered users
$lang['Registered_users_total'] = 'Avem <b>%d</b> utilizatori inregistrati'; // # registered users
$lang['Registered_user_total'] = 'Avem <b>%d</b> utilizator inregistrat'; // # registered users
$lang['Newest_user'] = 'Cel mai nou utilizator inregistrat este: <b>%s%s%s</b>'; // a href, username, /a

$lang['No_new_posts_last_visit'] = 'Nu sunt mesaje noi de la ultima ta vizita';
$lang['No_new_posts'] = 'Nu sunt mesaje noi';
$lang['New_posts'] = 'Mesaje noi';
$lang['New_post'] = 'Mesaj nou';
$lang['No_new_posts_hot'] = 'Nu sunt mesaje noi [ Popular ]';
$lang['New_posts_hot'] = 'Mesaje noi [ Popular ]';
$lang['No_new_posts_locked'] = 'Nu sunt mesaje noi [ Inchis ]';
$lang['New_posts_locked'] = 'Mesaje noi [ Inchis ]';
$lang['Forum_is_locked'] = 'Forumul este inchis';


//
// Login
//
$lang['Enter_password'] = 'Va rugam introduceti un nume de utilizator si o parola pentru a va autentifica';
$lang['Login'] = 'Intrare';
$lang['Logout'] = 'Iesire';

$lang['Forgotten_password'] = 'Mi-am uitat parola';

$lang['Log_me_in'] = 'Autentifica-ma automat la fiecare vizita';

$lang['Error_login'] = 'Ati introdus un nume de utilizator incorect sau inactiv sau o parola gresita';


//
// Index page
//
$lang['Index'] = 'Pagina de start';
$lang['No_Posts'] = 'Nici un mesaj';
$lang['No_forums'] = 'Nu exista forumuri';

$lang['Private_Message'] = 'Mesaj privat';
$lang['Private_Messages'] = 'Mesaje private';
$lang['Who_is_Online'] = 'Cine este conectat';

$lang['Mark_all_forums'] = 'Marcheaza toate forumurile ca fiind citite';
$lang['Forums_marked_read'] = 'Toate forumurile au fost marcate ca fiind citite';


//
// Viewforum
//
$lang['View_forum'] = 'Vezi forum';

$lang['Forum_not_exist'] = 'Forumul selectat nu exista';
$lang['Reached_on_error'] = 'Ati gasit aceasta pagina datorita unei erori';

$lang['Display_topics'] = 'Afiseaza subiectul pentru previzualizare';
$lang['All_Topics'] = 'Toate subiectele';

$lang['Topic_Announcement'] = '<b>Anunt:</b>';
$lang['Topic_Sticky'] = '<b>Lipicios (Sticky):</b>';
$lang['Topic_Moved'] = '<b>Mutat:</b>';
$lang['Topic_Poll'] = '<b>[ Chestionar ]</b>';

$lang['Mark_all_topics'] = 'Marcheaza toate subiectele ca fiind citite';
$lang['Topics_marked_read'] = 'Toate subiectele au fost marcate ca fiind citite';

$lang['Rules_post_can'] = '<b>Puteti</b> crea un subiect nou in acest forum';
$lang['Rules_post_cannot'] = '<b>Nu puteti</b> crea un subiect nou in acest forum';
$lang['Rules_reply_can'] = '<b>Puteti</b> raspunde la subiectele acestui forum';
$lang['Rules_reply_cannot'] = '<b>Nu puteti</b> raspunde in subiectele acestui forum';
$lang['Rules_edit_can'] = '<b>Puteti</b> modifica mesajele proprii din acest forum';
$lang['Rules_edit_cannot'] = '<b>Nu puteti</b> modifica mesajele proprii din acest forum';
$lang['Rules_delete_can'] = '<b>Puteti</b> sterge mesajele proprii din acest forum';
$lang['Rules_delete_cannot'] = '<b>Nu puteti</b> sterge mesajele proprii din acest forum';
$lang['Rules_vote_can'] = '<b>Puteti</b> vota in chestionarele din acest forum';
$lang['Rules_vote_cannot'] = '<b>Nu puteti</b> vota in chestionarele din acest forum';
$lang['Rules_moderate'] = '<b>Puteti</b> %smodera acest forum%s'; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = '<br />Nu este nici un mesaj in acest forum<br /><br />Apasati pe butonul <b>Subiect nou</b> din aceasta pagina pentru a scrie un mesaj';


//
// Viewtopic
//
$lang['View_topic'] = 'Vizualizare subiect';

$lang['Guest'] = 'Vizitator';
$lang['Post_subject'] = 'Titlul subiectului';
$lang['View_next_topic'] = 'Subiectul urmator';
$lang['View_previous_topic'] = 'Subiectul anterior';
$lang['Submit_vote'] = 'Trimite votul';
$lang['View_results'] = 'Vizualizare rezultate';

$lang['No_newer_topics'] = 'Nu sunt subiecte noi in acest forum';
$lang['No_older_topics'] = 'Nu sunt subiecte vechi in acest forum';
$lang['Topic_post_not_exist'] = 'Nu exista subiectul sau mesajul cerut';
$lang['No_posts_topic'] = 'Nu exista mesaje in acest subiect';

$lang['Display_posts'] = 'Afiseaza mesajele pentru a le previzualiza';
$lang['All_Posts'] = 'Toate mesajele';
$lang['Newest_First'] = 'Primele, cele mai noi mesaje';
$lang['Oldest_First'] = 'Primele, cele mai vechi mesaje';

$lang['Back_to_top'] = 'Sus';

$lang['Read_profile'] = 'Vezi profilul utilizatorului';
$lang['Send_email'] = 'Trimite email utilizatorului';
$lang['Visit_website'] = 'Viziteaza site-ul autorului';
$lang['ICQ_status'] = 'Statutul ICQ';
$lang['Edit_delete_post'] = 'Modifica/Sterge acest mesaj';
$lang['View_IP'] = 'IP-ul autorului';
$lang['Delete_post'] = 'Sterge acest mesaj';

$lang['wrote'] = 'a scris'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Citat'; // comes before bbcode quote output.
$lang['Code'] = 'Cod'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Ultima modificare efectuata de catre %s la %s, modificat de %d data in total'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Ultima modificare efectuata %s la %s, modificat de %d ori in total'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Inchide acest subiect';
$lang['Unlock_topic'] = 'Deschide acest subiect';
$lang['Move_topic'] = 'Muta acest subiect';
$lang['Delete_topic'] = 'Sterge acest subiect';
$lang['Split_topic'] = 'Desparte acest subiect';

$lang['Stop_watching_topic'] = 'Opreste urmarirea acestui subiect';
$lang['Start_watching_topic'] = 'Marcheaza acest subiect pentru urmarirea raspunsurilor';
$lang['No_longer_watching'] = 'Ati oprit urmarirea acestui subiect';
$lang['You_are_watching'] = 'Acest subiect este marcat pentru urmarire';

$lang['Total_votes'] = 'Voturi totale';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Corpul mesajului';
$lang['Topic_review'] = 'Previzualizare revizie';

$lang['No_post_mode'] = 'Nu a fost specificat modul de trimitere a mesajului'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Creaza un nou subiect';
$lang['Post_a_reply'] = 'Raspunde';
$lang['Post_topic_as'] = 'Creaza un mesaj la';
$lang['Edit_Post'] = 'Modifica';
$lang['Options'] = 'Optiuni';

$lang['Post_Announcement'] = 'Anunt';
$lang['Post_Sticky'] = 'Lipicios (Sticky)';
$lang['Post_Normal'] = 'Normal';

$lang['Confirm_delete'] = 'Sunteti sigur ca vreti sa stergeti acest mesaj?';
$lang['Confirm_delete_poll'] = 'Sunteti sigur ca vreti sa stergeti acest chestionar?';

$lang['Flood_Error'] = 'Nu puteti sa trimiteti un mesaj nou la un interval atat de scurt dupa anteriorul; va rugam, incearcati mai tarziu.';
$lang['Empty_subject'] = 'Trebuie specificat titlul';
$lang['Empty_message'] = 'Trebuie sa scrieti un mesaj';
$lang['Forum_locked'] = 'Acest forum este inchis, nu se pot scrie, crea, raspunde sau modifica subiecte';
$lang['Topic_locked'] = 'Acest subiect este inchis, nu se pot crea sau raspunde la mesaje';
$lang['No_post_id'] = 'Trebuie sa selectati un mesaj pentru modificare';
$lang['No_topic_id'] = 'Trebuie sa selectati un mesaj pentru a da un raspuns la';
$lang['No_valid_mode'] = 'Puteti doar sa adaugati, sa modificati, sa citati sau sa raspundeti la mesaje; reveniti si incercati din nou';
$lang['No_such_post'] = 'Aici nu este nici un mesaj, reveniti si incercati din nou';
$lang['Edit_own_posts'] = 'Scuze dar puteti modifica doar mesajele dumneavoastra';
$lang['Delete_own_posts'] = 'Scuze dar puteti sterge doar mesajele dumneavoastra';
$lang['Cannot_delete_replied'] = 'Scuze dar nu puteti sterge mesaje la care s-a raspuns deja';
$lang['Cannot_delete_poll'] = 'Scuze dar nu puteti sterge un chestionar aflat in derulare';
$lang['Empty_poll_title'] = 'Trebuie sa introduceti un titlu pentru chestionar';
$lang['To_few_poll_options'] = 'Trebuie sa introduceti cel putin doua optiuni de vot in chestionar';
$lang['To_many_poll_options'] = 'Ati incercat sa introduceti prea multe optiuni de vot in chestionar';
$lang['Post_has_no_poll'] = 'Acest mesaj nu are chestionar';
$lang['Already_voted'] = 'Ati votat deja in acest chestionar';
$lang['No_vote_option'] = 'Trebuie sa specificati o optiune la votare';

$lang['Add_poll'] = 'Adauga un chestionar';
$lang['Add_poll_explain'] = 'Daca nu vreti sa adaugati un chestionar la mesajul dumneavoastra, lasati campurile necompletate';
$lang['Poll_question'] = 'Chestionar';
$lang['Poll_option'] = 'Optiunile chestionarului';
$lang['Add_option'] = 'Adauga o optiune';
$lang['Update'] = 'Actualizeaza';
$lang['Delete'] = 'Sterge';
$lang['Poll_for'] = 'Ruleaza chestionarul pentru';
$lang['Days'] = 'Zile'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Introduceti 0 sau lasati necompletat pentru un chestionar nelimitat in timp ]';
$lang['Delete_poll'] = 'Sterge chestionarul';

$lang['Disable_HTML_post'] = 'Dezactiveaza codul HTML in acest mesaj';
$lang['Disable_BBCode_post'] = 'Dezactiveaza codul BBCode in acest mesaj';
$lang['Disable_Smilies_post'] = 'Dezactiveaza zambetele in acest mesaj';

$lang['HTML_is_ON'] = 'Codul HTML este <u>Activat</u>';
$lang['HTML_is_OFF'] = 'Codul HTML este <u>Dezactivat</u>';
$lang['BBCode_is_ON'] = '%sCodulBB%s este <u>Activat</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sCodul%s este <u>Dezactivat</u>';
$lang['Smilies_are_ON'] = 'Zambetele sunt <u>Activate</u>';
$lang['Smilies_are_OFF'] = 'Zambetele sunt <u>Dezactivate</u>';

$lang['Attach_signature'] = 'Adauga semnatura (semnatura poate fi schimbata din Profil)';
$lang['Notify'] = 'Anunta-ma cand apare un raspuns';
$lang['Delete_post'] = 'Sterge acest mesaj';

$lang['Stored'] = 'Mesajul a fost introdus cu succes';
$lang['Deleted'] = 'Mesajul a fost sters cu succes';
$lang['Poll_delete'] = 'Chestionarul a fost sters cu succes';
$lang['Vote_cast'] = 'Votul a fost acceptat';

$lang['Topic_reply_notification'] = 'Anunt de raspuns la mesaj';

$lang['bbcode_b_help'] = "Text ingrosat (bold): [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Text inclinat (italic): [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Text subliniat: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Text citat: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Cod sursa: [code]cod sursa[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Lista: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Lista ordonata: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Insereaza imagine: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Insereaza URL: [url]http://url[/url] sau [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Inchide toate tag-urile de cod BB deschise";
$lang['bbcode_s_help'] = "Culoare text: [color=red]text[/color]  Sfat: poti folosi si color=#FF0000";
$lang['bbcode_f_help'] = "Marime font: [size=x-small]text marunt[/size]";

$lang['Emoticons'] = 'Iconite emotive';
$lang['More_emoticons'] = 'Alte iconite emotive';

$lang['Font_color'] = "Culoare text";
$lang['color_default'] = "Implicita";
$lang['color_dark_red'] = "Rosu inchis";
$lang['color_red'] = "Rosu";
$lang['color_orange'] = "Oranj";
$lang['color_brown'] = "Maro";
$lang['color_yellow'] = "Galben";
$lang['color_green'] = "Verde";
$lang['color_olive'] = "Masliniu";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Albastru";
$lang['color_dark_blue'] = "Albastru inchis";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violet";
$lang['color_white'] = "Alb";
$lang['color_black'] = "Negru";

$lang['Font_size'] = "Marime text";
$lang['font_tiny'] = "Marunta";
$lang['font_small'] = "Mica";
$lang['font_normal'] = "Normala";
$lang['font_large'] = "Mare";
$lang['font_huge'] = "Imensa";

$lang['Close_Tags'] = 'Inchide tag-uri';
$lang['Styles_tip'] = 'Sfat: Stilurile pot fi aplicate imediat textului selectat';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Mesagerie privata';

$lang['Login_check_pm'] = 'Autentificare pentru mesaje private';
$lang['New_pms'] = 'Aveti %d mesaje noi'; // You have 2 new messages
$lang['New_pm'] = 'Aveti %d mesaj nou'; // You have 1 new message
$lang['No_new_pm'] = 'Nu aveti mesaje noi';
$lang['Unread_pms'] = 'Aveti %d mesaje necitite';
$lang['Unread_pm'] = 'Aveti %d mesaj necitit';
$lang['No_unread_pm'] = 'Nu aveti mesaje necitite';
$lang['You_new_pm'] = 'Un mesaj nou privat asteapta in dosarul cu mesaje';
$lang['You_new_pms'] = 'Mai multe mesaje noi asteapta in dosarul cu mesaje';
$lang['You_no_new_pm'] = 'Nu sunt mesaje noi in asteptare in dosarul cu mesaje';

$lang['Unread_message'] = 'Mesaj necitit';
$lang['Read_message'] = 'Mesaj citit';

$lang['Read_pm'] = 'Mesaj citit';
$lang['Post_new_pm'] = 'Scrie mesaj';
$lang['Post_reply_pm'] = 'Retrimite mesajul';
$lang['Post_quote_pm'] = 'Comenteaza mesajul';
$lang['Edit_pm'] = 'Modifica mesajul';

$lang['Inbox'] = 'Dosarul cu mesaje';
$lang['Outbox'] = 'Dosarul cu mesaje in curs de trimitere';
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
$lang['Delete_marked'] = 'Sterge mesajele marcate';
$lang['Delete_all'] = 'Sterge toate mesajele';
$lang['Save_marked'] = 'Salveaza mesajele marcate';
$lang['Save_message'] = 'Salveaza mesajul';
$lang['Delete_message'] = 'Sterge mesajul';

$lang['Display_messages'] = 'Afiseaza mesajele din urma'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Toate mesajele';

$lang['No_messages_folder'] = 'Nu aveti mesaje noi in acest dosar';

$lang['PM_disabled'] = 'Mesajele private au fost dezactivate de pe acest panou';
$lang['Cannot_send_privmsg'] = 'Scuze dar administratorul va impiedica in trimiterea mesajelor private';
$lang['No_to_user'] = 'Trebuie specificat un nume de utilizator pentru a putea trimite mesajul';
$lang['No_such_user'] = 'Scuze dar acest utilizator nu exista';

$lang['Disable_HTML_pm'] = "Deactiveaza codul HTML in acest mesaj";
$lang['Disable_BBCode_pm'] = "Deactiveaza codul BB in acest mesaj";
$lang['Disable_Smilies_pm'] = "Deactiveaza zambetele in acest mesaj";

$lang['Message_sent'] = 'Mesajul a fost trimis';

$lang['Click_return_inbox'] = "Apasati %saici%s pentru a reveni la dosarul cu mesaje";
$lang['Click_return_index'] = "Apasati %saici%s pentru a reveni la Pagina de start a forumului";

$lang['Send_a_new_message'] = "Trimite un nou mesaj privat";
$lang['Send_a_reply'] = "Raspunde la un mesaj privat";
$lang['Edit_message'] = "Modifica un mesaj privat";

$lang['Notification_subject'] = 'Un nou mesaj privat a sosit';

$lang['Find_username'] = "Cauta un utilizator";
$lang['Find'] = "Cauta";
$lang['No_match'] = "Nu a fost gasit nici un utilizator";

$lang['No_post_id'] = "ID-ul mesajului nu a fost specificat";
$lang['No_such_folder'] = "Directorul specificat nu exista";
$lang['No_folder'] = "Nu a fost specificat directorul";

$lang['Mark_all'] = "Marcheaza toate";
$lang['Unmark_all'] = "Demarcheaza toate";

$lang['Confirm_delete_pm'] = "Sunteti sigur ca vreti sa stergeti acest mesaj?";
$lang['Confirm_delete_pms'] = "Sunteti sigur ca vreti sa stergeti aceste mesaje?";

$lang['Inbox_size'] = "Dosarul dumneavoastra cu mesaje este %d%% plin"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Dosarul dumneavoastra cu mesaje trimise este %d%% plin";
$lang['Savebox_size'] = "Dosarul dumneavoastra cu mesaje salvate este %d%% plin";

$lang['Click_view_privmsg'] = "Apasati %saici%s pentru a ajunge la dosarul dumneavoastra cu mesaje";

//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Vezi profilul : %s'; // %s is username
$lang['About_user'] = 'Totul despre %s'; // %s is username

$lang['Preferences'] = 'Preferinte';
$lang['Items_required'] = 'Ce este marcat cu * este obligatoriu';
$lang['Registration_info'] = 'Informatii de inregistrare';
$lang['Profile_info'] = 'Informatii despre profil';
$lang['Profile_info_warn'] = 'Aceste informatii vor fi facute publice';
$lang['Avatar_panel'] = 'Panoul de control al imaginilor asociate';
$lang['Avatar_gallery'] = 'Galeria de imagini';

$lang['Website'] = 'Site Web';
$lang['Location'] = 'Locatie';
$lang['Contact'] = 'Contact';
$lang['Email_address'] = 'Adresa de email';
$lang['Email'] = 'Email';
$lang['Send_private_message'] = 'Trimite mesaj privat';
$lang['Hidden_email'] = '[ Ascuns ]';
$lang['Search_user_posts'] = 'cauta mesaje scrise de acest utilizator';
$lang['Interests'] = 'Interese';
$lang['Occupation'] = 'Ocupatia';
$lang['Poster_rank'] = 'Rangul utilizatorului';

$lang['Total_posts'] = 'Numarul total de mesaje';
$lang['User_post_pct_stats'] = '%.2f%% din total'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f mesaje pe zi'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Cauta toate mesajele lui %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Scuze dar acest utilizator nu exista';
$lang['Wrong_Profile'] = 'Nu puteti modifica un profil daca nu este propriul dumneavoastra profil.';

$lang['Only_one_avatar'] = 'Se poate specifica doar un tip de imagine asociata';
$lang['File_no_data'] = 'Fisierul specificat de URL-ul dumneavoastra nu contine informatii';
$lang['No_connection_URL'] = 'Conexiunea nu poate fi facuta la URL-ul specificat';
$lang['Incomplete_URL'] = 'URL-ul introdus este incomplet';
$lang['Wrong_remote_avatar_format'] = 'URL-ul catre imaginea asociata nu este valid';
$lang['No_send_account_inactive'] = 'Scuze, dar parola dumneavoastra nu mai poate fi folosita deoarece contul este inactiv. Te rog contacteaza administratorul forumului pentru mai multe informatii';

$lang['Always_smile'] = 'Folosesc intotdeauna zambete';
$lang['Always_html'] = 'Folosesc intotdeauna cod HTML';
$lang['Always_bbcode'] = 'Folosesc intotdeauna cod BB';
$lang['Always_add_sig'] = 'Adauga intotdeauna semnatura mea la mesaje';
$lang['Always_notify'] = 'Anunta-ma intotdeauna de raspunsuri la mesajele mele';
$lang['Always_notify_explain'] = 'Trimite-mi un email cand cineva raspunde la mesajele mele. Optiunea poate fi schimbata la fiecare mesaj nou.';

$lang['Board_style'] = 'Stilul interfetei';
$lang['Board_lang'] = 'Limba interfetei';
$lang['No_themes'] = 'Nici o tema in baza de date';
$lang['Timezone'] = 'Timpul zonal';
$lang['Date_format'] = 'Formatul datei';
$lang['Date_format_explain'] = 'Sintaxa utilizata este identica cu cea folosita de functia PHP <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a>';
$lang['Signature'] = 'Semnatura';
$lang['Signature_explain'] = 'Acesta este un bloc de text care poate fi adaugat mesajelor scrise de dumneavoastra. Limita este de %d caractere';
$lang['Public_view_email'] = 'Afiseaza intotdeauna adresa mea de email';

$lang['Current_password'] = 'Parola curenta';
$lang['New_password'] = 'Parola noua';
$lang['Confirm_password'] = 'Confirmati parola';
$lang['Confirm_password_explain'] = 'Trebuie sa confirmati parola curenta daca vreti sa o schimbati sau vreti sa aveti alta adresa de email';
$lang['password_if_changed'] = 'Este necesar sa specificati parola daca vreti sa o schimbati';
$lang['password_confirm_if_changed'] = 'Este necesar sa confirmati parola daca ati schimbat-o anterior';

$lang['Avatar'] = 'Imagine asociata (Avatar)';
$lang['Avatar_explain'] = 'Afiseaza o imagine micuta sub detaliile dumneavoastra din mesaje. Doar o imagine poate fi afisata in acelasi timp, marimea ei nu poate fi mai mare de %d pixeli ca inaltime si %d ca latime si marimea fisierului poate fi cel mult de %dko.';
$lang['Upload_Avatar_file'] = 'Incarcati de pe calculatorul dumneavoastra imaginea asociata';
$lang['Upload_Avatar_URL'] = 'Incarcati cu un URL imaginea asociata';
$lang['Upload_Avatar_URL_explain'] = 'Introduceti URL-ul locului unde este imaginea asociatar pentru a fi copiata pe acest site.';
$lang['Pick_local_Avatar'] = 'Alegeti o imagine asociata din galerie';
$lang['Link_remote_Avatar'] = 'Legatura spre un alt site ce contine imagini asociate';
$lang['Link_remote_Avatar_explain'] = 'Introduceti URL-ul locului unde este imaginea asociata pentru a face o legatura la ea.';
$lang['Avatar_URL'] = 'URL-ul imaginii asociate';
$lang['Select_from_gallery'] = 'Alegeti o imagine asociata din galerie';
$lang['View_avatar_gallery'] = 'Arata galeria de imagini asociate';

$lang['Select_avatar'] = 'Alegeti o imagine asociata';
$lang['Return_profile'] = 'Renuntati la imaginea asociata';
$lang['Select_category'] = 'Alegeti o categorie';

$lang['Delete_Image'] = 'Stergeti imaginea';
$lang['Current_Image'] = 'Imaginea curenta';

$lang['Notify_on_privmsg'] = 'Atentioneaza-ma cand primesc un mesaj privat nou';
$lang['Popup_on_privmsg'] = 'Deschide o fereastra cand primesc un mesaj privat nou';
$lang['Popup_on_privmsg_explain'] = 'Unele sabloane pot deschide o fereastra noua pentru a va informa de faptul ca ati primit un mesaj privat nou';
$lang['Hide_user'] = 'Ascundeti indicatorul de conectare';

$lang['Profile_updated'] = 'Profilul dumneavoastra a fost actualizat';
$lang['Profile_updated_inactive'] = 'Profilul dumneavoastra a fost actualizat, dar deoarece au fost modificate detalii importante contul este momentan inactiv. Verificati-va email-ul pentru a afla cum iti va fi reactivat contul sau daca este necesara interventia administratorului asteptati pana ce acesta va va reactiva contul.';

$lang['Password_mismatch'] = 'Parolele introduse nu sunt valide';
$lang['Current_password_mismatch'] = 'Parola furnizata de dumneavoastra nu este gasita in baza de date';
$lang['Password_long'] = 'Parola nu trebuie sa depaseasca 32 de caractere';
$lang['Username_taken'] = 'Scuze, dar numele de utilizator introdus, exista deja';
$lang['Username_invalid'] = 'Scuze, dar numele de utilizator introdus contine caractere gresite, ca de exemplu: \'';
$lang['Username_disallowed'] = 'Scuze, dar acest nume de utilizator a fost interzis';
$lang['Email_taken'] = 'Scuze, dar adresa de email introdusa este deja folosita de un alt utilizator';
$lang['Email_banned'] = 'Scuze, dar aceasta adresa de email a fost interzisa';
$lang['Email_invalid'] = 'Scuze, dar aceasta adresa de email nu este corecta';
$lang['Signature_too_long'] = 'Semnatura dumneavoastra este prea lunga';
$lang['Fields_empty'] = 'Trebuie sa completati campurile obligatorii';
$lang['Avatar_filetype'] = 'Imaginile asociater trebuie sa fie de tipul: .jpg, .gif sau .png';
$lang['Avatar_filesize'] = 'Imaginile asociate trebuie sa fie mai mici de: %d kB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Imaginile asociate trebuie sa fie mai mici de %d pixeli pe latime si %d pixeli pe inaltime';

$lang['Welcome_subject'] = 'Bine ati venit pe forumul %s '; // Welcome to my.com forums
$lang['New_account_subject'] = 'Cont nou de utilizator';
$lang['Account_activated_subject'] = 'Contul a fost activat';

$lang['Account_added'] = 'Va multumim pentru inregistrare, contul a fost creat. Puteti sa va autentificati cu numele de utilizator si parola';
$lang['Account_inactive'] = 'Contul a fost creat. Acest forum necesita activarea contului, o cheie de activare a fost trimisa pe adresa de email furnizata de dumneavoastra. Va rugam sa va verificati casuta de email pentru mai multe informatii.';
$lang['Account_inactive_admin'] = 'Contul a fost creat. Acest forum necesita activarea contului de catre administrator. Veti fi informat prin email cand contul va fi activat.';
$lang['Account_active'] = 'Contul a fost activat. Multumim pentru inregistrare.';
$lang['Account_active_admin'] = 'Contul a fost activat';
$lang['Reactivate'] = 'Reactivati-va contul!';
$lang['Already_activated'] = 'Contul a fost deja activat';
$lang['COPPA'] = 'Contul a fost creat dar trebuie sa fie aprobat, verificati-va, va rugam, casuta de email.';

$lang['Registration'] = 'Termenii acordului de inregistrare';
$lang['Reg_agreement'] = 'Intotdeauna administratorii si moderatorii acestui forum vor incerca sa indeparteze sau sa modifice orice material deranjant cat mai repede posibil; este imposibil sa parcurga fiecare mesaj in parte. Din acest motiv trebuie sa stiti ca toate mesajele exprima punctul de vedere si opiniile autorilor si nu ale administratorilor,
moderatorilor sau a web master-ului (exceptie facand mesajele scrise chiar de catre ei) si de aceea ei nu pot fi facuti responsabili.<br /><br />Trebuie sa fiti de acord sa nu publicati mesaje cu continut abuziv, obscen, vulgar, calomnios, de ura, amenintator, sexual sau orice alt material ce poate viola legile aflate in vigoare. Daca publicati astfel de materiale puteti fi imediat si pentru totdeauna indepartat din forum (si firma care va ofera accesul la Internet va fi anuntata). Adresele IP ale tuturor mesajelor trimise sunt stocate pentru a fi de ajutor in rezolvarea unor astfel de incalcari ale regulilor. Trebuie sa fiti de acord ca webmaster-ul, administratorul si moderatorii acestui forum au dreptul de a sterge, modifica sau inchide orice subiect, oricand cred ei ca acest lucru se impune. Ca utilizator, trebuie sa fiti de acord ca orice informatie introdusa de dumneavoastra sa fie stocata in baza de date. Aceste informatii nu vor fi aratate unei terte persoane fara consimtamantul webmaster-ului, administratorului si moderatorilor care nu pot fi facuti responsabili de atacurile de furt sau de vandalism care pot sa duca la compromiterea datelor.<br /><br />Acest forum utilizeaza fisierele tip cookie pentru a stoca informatiile pe calculatorul dumneavoastra. Aceste fisiere cookie nu contin informatii despre alte aplicatii ci ele sunt folosite doar pentru usurarea navigarii pe forum. Adresele de email sunt utilizate doar pentru confirmarea inregistrarii dumneavoastra ca utilizator si pentru parola (si pentru trimiterea unei noi parole daca ati uitat-o pe cea curenta).<br /><br />Prin apasarea pe butonul de inregistrare se considera ca sunteti de acord cu aceste conditii.';

$lang['Agree_under_13'] = 'Sunt de acord cu aceste conditii si declar ca am <b>sub</b> 13 ani';
$lang['Agree_over_13'] = 'Sunt de acord cu aceste conditii si declar ca am <b>peste</b> 13 ani';
$lang['Agree_not'] = 'Nu sunt de acord cu aceste conditii';

$lang['Wrong_activation'] = 'Cheia de activare furnizata nu se regaseste in baza de date';
$lang['Send_password'] = 'Trimiteti-mi o parola noua';
$lang['Password_updated'] = 'O parola noua a fost creata, va rugam verificati-va casuta de email pentru informatiile de activare';
$lang['No_email_match'] = 'Adresa de email furnizata nu corespunde celei asociate acestui utilizator';
$lang['New_password_activation'] = 'Activarea parolei noi';
$lang['Password_activated'] = 'Contul dumneavoastra a fost reactivat. La autentificare utilizati parola trimisa in la adresa de email primita';

$lang['Send_email_msg'] = "Trimite un email";
$lang['No_user_specified'] = "Nu a fost specificat utilizatorul";
$lang['User_prevent_email'] = "Acest utilizator nu doreste sa primeasca mesaje. Incearcati sa-i trimiteti un mesaj privat";
$lang['User_not_exist'] = "Acest utilizator nu exista";
$lang['CC_email'] = "Trimiteti-va o copie";
$lang['Email_message_desc'] = "Acest mesaj va fi trimis in mod text, nu include cod HTML sau cod BB. Adresa de intoarcere pentru acest mesaj va fi setata catre adresa dumneavoastra de email.";
$lang['Flood_email_limit'] = "Nu puteti trimite inca un email in acest moment, incearcati mai tarziu.";
$lang['Recipient'] = "Recipient";
$lang['Email_sent'] = "Mesajul a fost trimis";
$lang['Send_email'] = "Trimite un mesaj";
$lang['Empty_subject_email'] = "Trebuie specificat un subiect pentru mesaj";
$lang['Empty_message_email'] = "Trebuie introdus continut in mesaj";


//
// Memberslist
//
$lang['Select_sort_method'] = 'Selectati metoda de sortare';
$lang['Sort'] = 'Sorteaza';
$lang['Sort_Top_Ten'] = 'Top 10 utilizatori';
$lang['Sort_Joined'] = 'Data inregistrarii';
$lang['Sort_Username'] = 'Nume utilizator';
$lang['Sort_Location'] = 'Locatia';
$lang['Sort_Posts'] = 'Numar total de mesaje';
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
$lang['Group_member_join'] = 'Aderati la un grup';

$lang['Group_Information'] = 'Informatii despre grup';
$lang['Group_name'] = 'Numele grupului';
$lang['Group_description'] = 'Descrierea grupului';
$lang['Group_membership'] = 'Membrii grupului';
$lang['Group_Members'] = 'Membrii grupului';
$lang['Group_Moderator'] = 'Moderatorul grupului';
$lang['Pending_members'] = 'Membrii in asteptare';

$lang['Group_type'] = 'Tipul grupului';
$lang['Group_open'] = 'Grup deschis';
$lang['Group_closed'] = 'Grup inchis';
$lang['Group_hidden'] = 'Grup ascuns';

$lang['Current_memberships'] = 'Membrii curenti ai grupului';
$lang['Non_member_groups'] = 'Membrii care nu fac parte din grupuri';
$lang['Memberships_pending'] = 'Membrii in asteptare';

$lang['No_groups_exist'] = 'Nu exista grupuri';
$lang['Group_not_exist'] = 'Acest grup de utilizatori nu exista';

$lang['Join_group'] = 'Adera la grup';
$lang['No_group_members'] = 'Acest grup nu are membrii';
$lang['Group_hidden_members'] = 'Acest grup este ascuns, nu-i puteti vedea membrii';
$lang['No_pending_group_members'] = 'Acest grup nu are membrii in asteptare';
$lang['Group_joined'] = 'Inscrierea la acest grup a fost facuta cu succes.<br />Veti fi anuntat cand cererea dumneavoastra va fi aprobata de moderatorul grupului';
$lang['Group_request'] = 'A fost depusa o cerere de aderare la grupul dumneavoastra';
$lang['Group_approved'] = 'Cererea dumneavoastra de aderare la grup a fost aprobata';
$lang['Group_added'] = 'Ati fost acceptat la acest grup de utilizatori';
$lang['Already_member_group'] = 'Sunteti deja membru al acestui grup';
$lang['User_is_member_group'] = 'Utilizatorul este deja membru al acestui grup';
$lang['Group_type_updated'] = 'Modificarea tipului de grup s-a realizat cu succes';

$lang['Could_not_add_user'] = 'Utilizatorul selectat nu exista';
$lang['Could_not_anon_user'] = 'Un Anonim nu poate fi facut membru de grup';

$lang['Confirm_unsub'] = 'Sunteti sigur ca vreti sa parasiti acest grup?';
$lang['Confirm_unsub_pending'] = 'Cererea dumneavoastra de aderare la acest grup nu a fost inca aprobata, sunteti sigur ca vreti sa-l parasiti?';

$lang['Unsub_success'] = 'Dorinta dumneavoastra de parasire a grupului a fost indeplinita.';

$lang['Approve_selected'] = 'Aproba selectiile';
$lang['Deny_selected'] = 'Respinge selectiile';
$lang['Not_logged_in'] = 'Trebuie sa fiti autentificat pentru a adera la grup.';
$lang['Remove_selected'] = 'Sterge selectiile';
$lang['Add_member'] = 'Adauga membru';
$lang['Not_group_moderator'] = 'Nu sunteti moderator in acest grup; prin urmare nu puteti efectua aceste actiuni.';

$lang['Login_to_join'] = 'Autentificati-va pentru a adera la grup sau pentru a organiza membrii';
$lang['This_open_group'] = 'Acesta este un grup deschis, apasati aici pentru a deveni membru';
$lang['This_closed_group'] = 'Acesta este un grup inchis, nu mai accepta noi membrii';
$lang['This_hidden_group'] = 'Acesta este un grup ascuns, cererile de aderare automate nu sunt acceptate';
$lang['Member_this_group'] = 'Sunteti membru al acestui grup';
$lang['Pending_this_group'] = 'Cererea de membru al acestui grup este in asteptare';
$lang['Are_group_moderator'] = 'Sunteti moderatorul grupului';
$lang['None'] = 'Nu';

$lang['Subscribe'] = "Inscriere";
$lang['Unsubscribe'] = "Parasire";
$lang['View_Information'] = "Vizualizare informatii";

//
// Search
//
$lang['Search_query'] = 'Interogare de cautare';
$lang['Search_options'] = 'Optiuni de cautare';

$lang['Search_keywords'] = 'Cauta dupa cuvintele cheie';
$lang['Search_keywords_explain'] = 'Puteti folosi <u>AND</u> pentru a defini cuvintele ce trebuie sa fie in rezultate, <u>OR</u> pentru a defini cuvintele care pot sa fie in rezultat, si <u>NOT</u> pentru a defini cuvintele care nu trebuie sa fie in rezultate. Se poate utiliza * pentru parti de cuvinte.';
$lang['Search_author'] = 'Cauta dupa autor';
$lang['Search_author_explain'] = 'Utilizati * pentru parti de cuvinte';

$lang['Search_for_any'] = "Cauta dupa oricare dintre termeni sau utilizeaza o interogare ca intrare";
$lang['Search_for_all'] = "Cauta dupa toti termenii";
$lang['Search_title_msg'] = "Cauta in titlul subiectelor si in textele mesajelor";
$lang['Search_msg_only'] = "Cauta doar in textele mesajelor";

$lang['Return_first'] = 'Intoarce primele'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'de caractere ale mesajelor';

$lang['Search_previous'] = 'Cauta in urma'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Sorteaza dupa';
$lang['Sort_Time'] = 'Data mesajului';
$lang['Sort_Post_Subject'] = 'Subiectul mesajului';
$lang['Sort_Topic_Title'] = 'Titlul subiectului';
$lang['Sort_Author'] = 'Autor';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'Afiseaza rezultatele ca';
$lang['All_available'] = 'Disponibile toate';
$lang['No_searchable_forums'] = 'nu aveti drepturi de cautare in nici un forum de pe acest site';

$lang['No_search_match'] = 'Nici un subiect sau mesaj nu indeplineste criteriul introdus la cautare';
$lang['Found_search_match'] = 'Cautarea a gasit %d rezultat'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Cautarea a gasit %d rezultate'; // eg. Search found 24 matches

$lang['Close_window'] = 'Inchide fereastra';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Ne pare rau dar doar %s poate pune anunturi in acest forum';
$lang['Sorry_auth_sticky'] = 'Ne pare rau dar doar %s poate pune mesaje lipicioase (sticky) in acest forum';
$lang['Sorry_auth_read'] = 'Ne pare rau dar doar %s poate citi subiectele din acest forum';
$lang['Sorry_auth_post'] = 'Ne pare rau dar doar %s poate scrie subiecte in acest forum';
$lang['Sorry_auth_reply'] = 'Ne pare rau dar doar %s poate replica in acest forum';
$lang['Sorry_auth_edit'] = 'Ne pare rau dar doar %s poate modifica un mesaj in acest forum';
$lang['Sorry_auth_delete'] = 'Ne pare rau dar doar %s poate sterge un mesaj din acest forum';
$lang['Sorry_auth_vote'] = 'Ne pare rau dar doar %s poate vota in chestionarele din acest forum';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>utilizator anonim</b>';
$lang['Auth_Registered_Users'] = '<b>utilizator inregistrat</b>';
$lang['Auth_Users_granted_access'] = '<b>utilizatori cu drepturi speciale de acces</b>';
$lang['Auth_Moderators'] = '<b>moderatori</b>';
$lang['Auth_Administrators'] = '<b>administratori</b>';

$lang['Not_Moderator'] = 'Dumneavoastra nu sunteti moderator in acest forum';
$lang['Not_Authorised'] = 'Nu sunteti autorizat';

$lang['You_been_banned'] = 'Accesul dumneavoastra in acest forum este blocat<br />Va rugam sa contactati webmaster-ul sau administratorul pentru mai multe informatii';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Sunt 0 utilizatori inregistrati si '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Sunt %d utilizatori inregistrati si '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Sunt %d utilizatori inregistrati si '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = '0 utilizatori ascunsi conectati'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d utilizatori ascunsi conectati'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d utilizatori ascunsi conectati'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Sunt %d utilizatori vizitatori conectati'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Sunt 0 utilizatori vizitatori conectati'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Este %d utilizator vizitator conectat'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Nici un utilizator nu navigheaza acum in acest forum';

$lang['Online_explain'] = 'Aceste date se bazeaza pe utilizatorii activi de peste 5 minute';

$lang['Forum_Location'] = 'Situatia forumului';
$lang['Last_updated'] = 'Ultima imbunatatire';

$lang['Forum_index'] = 'Pagina de start a forumului';
$lang['Logging_on'] = 'Autentificare';
$lang['Posting_message'] = 'Scrie un mesaj';
$lang['Searching_forums'] = 'Cauta in forumuri';
$lang['Viewing_profile'] = 'Vezi profilul';
$lang['Viewing_online'] = 'Vezi cine este conectat';
$lang['Viewing_member_list'] = 'Vezi lista cu membri';
$lang['Viewing_priv_msgs'] = 'Vezi mesajele private';
$lang['Viewing_FAQ'] = 'Vezi lista cu intrebari/raspunsuri (FAQ)';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Panoul de control al moderatorului';
$lang['Mod_CP_explain'] = 'Utilizand formularul de mai jos puteti efectua operatii de moderare masiva in forum. Puteti inchide, deschide, muta sau sterge orice numar de subiecte.';

$lang['Select'] = 'Selecteaza';
$lang['Delete'] = 'Sterge';
$lang['Move'] = 'Muta';
$lang['Lock'] = 'Inchide';
$lang['Unlock'] = 'Deschide';

$lang['Topics_Removed'] = 'Subiectele selectate au fost cu succes sterse din baza de date.';
$lang['Topics_Locked'] = 'Subiectele selectate au fost inchise';
$lang['Topics_Moved'] = 'Subiectele selectate au fost mutate';
$lang['Topics_Unlocked'] = 'Subiectele selectate au fost deschise';
$lang['No_Topics_Moved'] = 'Nici un subiect nu a fost mutat';

$lang['Confirm_delete_topic'] = "Sunteti sigur ca vreti sa stergeti subiectul/subiectele selectate?";
$lang['Confirm_lock_topic'] = "Sunteti sigur ca vreti sa inchideti subiectul/subiectele selectate?";
$lang['Confirm_unlock_topic'] = "Sunteti sigur ca vreti sa deschideti subiectul/subiectele selectate?";
$lang['Confirm_move_topic'] = "Sunteti sigur ca vreti sa mutati subiectul/subiectele selectate?";

$lang['Move_to_forum'] = 'Muta forumul';
$lang['Leave_shadow_topic'] = 'Pastreaza o umbra a subiectului in vechiul forum.';

$lang['Split_Topic'] = 'Panoul de control a impartirii subiectelor';
$lang['Split_Topic_explain'] = 'Utilizand formularul de mai jos puteti imparti un subiect in doua, pe rand sau incepand de la cel deja selectat';
$lang['Split_title'] = 'Titlul noului subiect';
$lang['Split_forum'] = 'Forum pentru un subiect nou';
$lang['Split_posts'] = 'Imparte mesajele alese';
$lang['Split_after'] = 'Imparte mesajul ales';
$lang['Topic_split'] = 'Subiectul selectat a fost impartit cu succes';

$lang['Too_many_error'] = 'Ati selectat prea multe mesaje. Puteti sa selectati doar un mesaj la care sa impartiti subiectul!';

$lang['None_selected'] = 'Nu ati selectat nici un subiect pentru a efectua aceasta operatie. Va rugam intoarceti-va si selectati cel putin un subiect.';
$lang['New_forum'] = 'Forum nou';

$lang['This_posts_IP'] = 'IP-ul mesajului';
$lang['Other_IP_this_user'] = 'Alte adrese IP de la care acest utilizator a trimis mesaje';
$lang['Users_this_IP'] = 'Utilizatori care au trimis mesaje de la acest IP';
$lang['IP_info'] = 'Informatii IP';
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

$lang['datetime']['Sunday'] = 'Duminica';
$lang['datetime']['Monday'] = 'Luni';
$lang['datetime']['Tuesday'] = 'Marti';
$lang['datetime']['Wednesday'] = 'Miercuri';
$lang['datetime']['Thursday'] = 'Joi';
$lang['datetime']['Friday'] = 'Vineri';
$lang['datetime']['Saturday'] = 'Sambata';
$lang['datetime']['Sun'] = 'Dum';
$lang['datetime']['Mon'] = 'Lun';
$lang['datetime']['Tue'] = 'Mar';
$lang['datetime']['Wed'] = 'Mie';
$lang['datetime']['Thu'] = 'Joi';
$lang['datetime']['Fri'] = 'Vin';
$lang['datetime']['Sat'] = 'Sam';
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
$lang['Information'] = 'Informatii';
$lang['Critical_Information'] = 'Informatii primejdioase';

$lang['General_Error'] = 'Eroare generala';
$lang['Critical_Error'] = 'Eroare primejdioasa';
$lang['An_error_occured'] = 'A aparut o eroare';
$lang['A_critical_error'] = 'A aparut o eroare primejdioasa';

//
// That's all Folks!
// -------------------------------------------------

?>
