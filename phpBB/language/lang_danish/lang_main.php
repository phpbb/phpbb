<?php
/***************************************************************************
 *                            lang_main.php [Danish]
 *                              -------------------
 *     begin                : Sat Jan 03 2002
 *     copyright            : (C) 2002 The phpBB Group
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
// The format of this file is:
//
// ---> $lang['message'] = "text";
//
// You should also try to set a locale and a character
// encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may
// not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

//setlocale(LC_ALL, "en");
$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Category'] = "Kategori";
$lang['Topic'] = "Emner";
$lang['Topics'] = "Emner";
$lang['Replies'] = "Svar";
$lang['Views'] = "Visninger";
$lang['Post'] = "Indlæg";
$lang['Posts'] = "Indlæg";
$lang['Posted'] = "Skrevet";
$lang['Username'] = "Brugernavn";
$lang['Password'] = "Kodeord";
$lang['Email'] = "Email";
$lang['Poster'] = "Poster";
$lang['Author'] = "Forfatter";
$lang['Time'] = "Tid";
$lang['Hours'] = "Timer";
$lang['Message'] = "Besked";

$lang['1_Day'] = "1 Dag";
$lang['7_Days'] = "7 Dage";
$lang['2_Weeks'] = "2 Uger";
$lang['1_Month'] = "1 Måned";
$lang['3_Months'] = "3 Måneder";
$lang['6_Months'] = "6 Måneder";
$lang['1_Year'] = "1 År";

$lang['Go'] = "Opdater";
$lang['Jump_to'] = "Gå til";
$lang['Submit'] = "Udfør";
$lang['Reset'] = "Forfra";
$lang['Cancel'] = "Fortryd";
$lang['Preview'] = "Vis Prøve";
$lang['Confirm'] = "Bekræft";
$lang['Spellcheck'] = "Stavekontrol";
$lang['Yes'] = "Ja";
$lang['No'] = "Nej";
$lang['Enabled'] = "Slået til";
$lang['Disabled'] = "Afbrudt";
$lang['Error'] = "Fejl";

$lang['Next'] = "Næste";
$lang['Previous'] = "Forrige";
$lang['Goto_page'] = "Gå til side";
$lang['Joined'] = "Indmeld";
$lang['IP_Address'] = "IP Adresse";

$lang['Select_forum'] = "Vælg et forum";
$lang['View_latest_post'] = "Vis sidste indlæg";
$lang['View_newest_post'] = "Vis nyeste indlæg";
$lang['Page_of'] = "Side <b>%d</b> af <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ Nummer";
$lang['AIM'] = "AIM Adresse";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Forum Index";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Skriv nyt emne";
$lang['Reply_to_topic'] = "Besvar indlægget";
$lang['Reply_with_quote'] = "Besvar, med citat";

$lang['Click_return_topic'] = "Klik %sHer%s for at returnerer til emnet"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Klik %sHer%s for at forsøge igen";
$lang['Click_return_forum'] = "Klik %sHer%s for at returnerer til forumet";
$lang['Click_view_message'] = "Klik %sHer%s for at se din besked";
$lang['Click_return_modcp'] = "Klik %sHer%s for at returnerer til forumets Redaktør side";
$lang['Click_return_group'] = "Klik %sHer%s for at returnerer til Gruppe information";

$lang['Admin_panel'] = "Gå til Administrations siden";

$lang['Board_disable'] = "Desværre dette forum er i øjeblikket ikke tilgængeligt, Prøv igen senere";


//
// Global Header strings
//
$lang['Registered_users'] = "Hvem er på nu:";
$lang['Browsing_forum'] = "Brugere på systemet nu:";
$lang['Online_users_zero_total'] = "Der er i alt <b>0</b> Brugere på systemmet nu: ";
$lang['Online_users_total'] = "Der er i alt <b>%d</b> Brugere på systemmet nu: ";
$lang['Online_user_total'] = "Der er <b>%d</b> Bruger på systemmet nu: ";
$lang['Reg_users_zero_total'] = "Ingen Tilmeldte, ";
$lang['Reg_users_total'] = "%d Tilmeldte, ";
$lang['Reg_user_total'] = "%d Tilmeldt, ";
$lang['Hidden_users_zero_total'] = "Ingen skjulte og ";
$lang['Hidden_users_total'] = "%d Skjulte og ";
$lang['Hidden_user_total'] = "%d Skjult og ";
$lang['Guest_users_zero_total'] = "Ingen gæster";
$lang['Guest_users_total'] = "%d Gæster";
$lang['Record_online_users'] = "Flest brugere online på samme tid var <b>%s</b> den %s"; // first %s = number of users, second %s is the date.
$lang['Guest_user_total'] = "%d Gæst";
$lang['Admin_online_color'] = "%sAdministrator%s";
$lang['Mod_online_color'] = "%sRedaktør%s";


$lang['You_last_visit'] = "Dit forrige besøg var %s"; // %s replaced by date/time
$lang['Current_time'] = "Tiden lige nu er %s"; // %s replaced by time

$lang['Search_new'] = "Vis indlæg siden sidste besøg";
$lang['Search_your_posts'] = "Vis dine indlæg";
$lang['Search_unanswered'] = "Vis ubesvarede indlæg";

$lang['Register'] = "Tilmeld";
$lang['Profile'] = "Profil";

$lang['Edit_profile'] = "Ret din profil";
$lang['Search'] = "Søg";
$lang['Memberlist'] = "Tilmeldte brugere";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "BBKode Oversigt";
$lang['Usergroups'] = "Grupper";
$lang['Last_Post'] = "Sidste indlæg";
$lang['Moderator'] = "Redaktør";
$lang['Moderators'] = "Redaktører";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Vores brugere har i alt skrevet <b>0</b> indlæg"; // Number of posts
$lang['Posted_articles_total'] = "Vores brugere har i alt skrevet <b>%d</b> indlæg"; // Number of posts
$lang['Posted_article_total'] = "Vores brugere har i alt skrevet <b>%d</b> inlæg"; // Number of posts
$lang['Registered_users_zero_total'] = "Der er i alt <b>0</b> tilmeldte brugere"; // # registered users
$lang['Registered_users_total'] = "Der er i alt <b>%d</b> tilmeldte brugere"; // # registered users
$lang['Registered_user_total'] = "Der er i alt <b>%d</b> tilmeldt bruger"; // # registered users
$lang['Newest_user'] = "Den sidst registrerede bruger er <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Ingen nye indlæg siden dit sidste besøg";
$lang['No_new_posts'] = "Ingen nye indlæg";
$lang['New_posts'] = "Nye indlæg";
$lang['New_post'] = "Nye indlæg";
$lang['No_new_posts_hot'] = "Ingen nye indlæg [ Populære ]";
$lang['New_posts_hot'] = "Nye indlæg [ Populære ]";
$lang['No_new_posts_locked'] = "Ingen nye indlæg [ Låst ]";
$lang['New_posts_locked'] = "Nye indlæg [ Låst ]";
$lang['Forum_is_locked'] = "Forum er låst";


//
// Login
//
$lang['Enter_password'] = "Vær venlig at indtaste brugernavn og kodeord for at logge ind";
$lang['Login'] = "Login";
$lang['Logout'] = "Logout";

$lang['Forgotten_password'] = "Jeg har glemt mit kodeord, Send kodeord, via email";

$lang['Log_me_in'] = "Log mig på automatisk ved hvert besøg";

$lang['Error_login'] = "Du har angivet et forkert eller inaktivt brugernavn eller et forkert kodeord";


//
// Index page
//
$lang['Index'] = "Index";
$lang['No_Posts'] = "Ingen indlæg";
$lang['No_forums'] = "Dette board har ingen forums";

$lang['Private_Message'] = "Privat Besked";
$lang['Private_Messages'] = "Privat Beskeder";
$lang['Who_is_Online'] = "Hvem er på nu";

$lang['Mark_all_forums'] = "Marker alle forums som læs";
$lang['Forums_marked_read'] = "Alle forums er markeret som læst";


//
// Viewforum
//
$lang['View_forum'] = "Vis Forum";

$lang['Forum_not_exist'] = "Det valgte forum eksistere ikke";
$lang['Reached_on_error'] = "Denne side er vist ved en fejl";

$lang['Display_topics'] = "Vis ikke emner ældre end";
$lang['All_Topics'] = "Begyndelsen";

$lang['Topic_Announcement'] = "<b>Bekendtgørelse:</b>";
$lang['Topic_Sticky'] = "<b>Opslag:</b>";
$lang['Topic_Moved'] = "<b>Flyttet:</b>";
$lang['Topic_Poll'] = "<b>[Afstemning]</b>";

$lang['Mark_all_topics'] = "Marker alle indlæg som læst";
$lang['Topics_marked_read'] = "Alle indlæg til dette forum er blevet markeret som læst";

$lang['Rules_post_can'] = "Du <b>kan</b> skrive nye indlæg i dette forum";
$lang['Rules_post_cannot'] = "Du <b>kan ikke</b> skrive nye indlæg i dette forum";
$lang['Rules_reply_can'] = "Du <b>kan</b> besvare indlæg i dette forum";
$lang['Rules_reply_cannot'] = "Du <b>kan ikke</b> besvare indlæg i dette forum";
$lang['Rules_edit_can'] = "Du <b>kan</b> rette dine indlæg i dette forum";
$lang['Rules_edit_cannot'] = "Du <b>kan ikke</b> rette dine indlæg i dette forum";
$lang['Rules_delete_can'] = "Du <b>kan</b> slette dine indlæg i dette forum";
$lang['Rules_delete_cannot'] = "Du <b>kan ikke</b> slette dine indlæg i dette forum";
$lang['Rules_vote_can'] = "Du <b>kan</b> stemme på afstemninger i dette forum";
$lang['Rules_vote_cannot'] = "Du <b>kan ikke</b> stemme på afstemninger i dette forum";
$lang['Rules_moderate'] = "Du <b>kan</b> %s Styre dette forum%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Der er ingen indlæg i dette forum<br />Klik på <b>Nyt Emne</b> for at skrive et";


//
// Viewtopic
//
$lang['View_topic'] = "Vis Emner";

$lang['Guest'] = 'Gæst';
$lang['Post_subject'] = "Emne:";
$lang['View_next_topic'] = "Vis næste Emne";
$lang['View_previous_topic'] = "Vis forrige Emne";
$lang['Submit_vote'] = "Stem";
$lang['View_results'] = "Vis resultater";

$lang['No_newer_topics'] = "Der er ingen nyere Emner i dette forum";
$lang['No_older_topics'] = "Der er ingen ældre Emner i dette forum";
$lang['Topic_post_not_exist'] = "Indlægget eller Emnet som du ønsker at se, eksistere ikke";
$lang['No_posts_topic'] = "Der eksistere ingen indlæg under dette emne";

$lang['Display_posts'] = "Vis ikke emner ældre end";
$lang['All_Posts'] = "Begyndelsen";
$lang['Newest_First'] = "Nyeste først";
$lang['Oldest_First'] = "Ældste først";

$lang['Back_to_top'] = "Tilbage til toppen";

$lang['Read_profile'] = "Vis brugerens profil"; 
$lang['Send_email'] = "Send email til bruger";
$lang['Visit_website'] = "Besøg brugerens webside";
$lang['ICQ_status'] = "ICQ Status";
$lang['Edit_delete_post'] = "Ret/Slet dette indlæg";
$lang['View_IP'] = "Vis forfatterens IP";
$lang['Delete_post'] = "Slet dette indlæg";

$lang['wrote'] = "Skrev"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Citat"; // comes before bbcode quote output.
$lang['Code'] = "Kode"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Sidst rette af %s den %s, rettet %d gang"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Sidst rette af %s den %s, rette i alt %d gange"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Lås dette emne";
$lang['Unlock_topic'] = "Lås dette emne op";
$lang['Move_topic'] = "Flyt dette emne";
$lang['Delete_topic'] = "Slet dette emne";
$lang['Split_topic'] = "Del dette emne";

$lang['Stop_watching_topic'] = "Stop med at overvåge dette emne";
$lang['Start_watching_topic'] = "Overvåg dette emne for svar";
$lang['No_longer_watching'] = "Du overvåger nu ikke længere dette emne for svar";
$lang['You_are_watching'] = "Du overvåger nu dette emne for svar";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Besked";
$lang['Topic_review'] = "Se Emne";

$lang['No_post_mode'] = "Igen post type angivet"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Skriv et nyt Emne";
$lang['Post_a_reply'] = "Svar på indlæg";
$lang['Post_topic_as'] = "Skriv Emne som";
$lang['Edit_Post'] = "Ret indlæg";
$lang['Options'] = "Muligheder";

$lang['Post_Announcement'] = "Bekendtgørelse";
$lang['Post_Sticky'] = "Opslag";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "Er du sikker på at du ønsker at slette dette indlæg?";
$lang['Confirm_delete_poll'] = "Er du sikker på at du ønsker at slette denne afstemning?";

$lang['Flood_Error'] = "Du kan ikke skrive et nyt indlæg nu, for at undgå misbrug skal der gå en lille tid, vær venlig at prøve igen om lidt";
$lang['Empty_subject'] = "Du skal angive en overskrift når du skriver et nyt emne";
$lang['Empty_message'] = "Du skal skrive noget i meddelelses feltet, når du skriver et indlæg";
$lang['Forum_locked'] = "Dette forum er låst, du kan ikke skrive nye, svare eller rette indlæg";
$lang['Topic_locked'] = "Dette emne er låst, du kan ikke rette eller besvare indlæg";
$lang['No_post_id'] = "Du skal vælge et indlæg som du ønsker at rette";
$lang['No_topic_id'] = "Du skal vælge et emne som du ønsker at svare på";
$lang['No_valid_mode'] = "Du kan kun lave nye, besvare, rette eller citere indlæg, prøv venligst igen";
$lang['No_such_post'] = "Indlægget findes ikke, prøv venligst igen";
$lang['Edit_own_posts'] = "Desværre, du kan kun rette i dine egne indlæg";
$lang['Delete_own_posts'] = "Desværre, du kan kun slette dine egne indlæg";
$lang['Cannot_delete_replied'] = "Desværre, du kan ikke slette indlæg som der er besvaret på";
$lang['Cannot_delete_poll'] = "Desværre du kan ikke slette en aktiv afstemning";
$lang['Empty_poll_title'] = "Du skal angive en overskrift til din afstemning";
$lang['To_few_poll_options'] = "Du skal angive mindst 2 afstemnings muligheder";
$lang['To_many_poll_options'] = "Du har prøvet at oprette en afstemning med for mange afstemnings muligheder";
$lang['Post_has_no_poll'] = "Dette indlæg har ingen afstemning";

$lang['Add_poll'] = "Tilføj en afstemning";
$lang['Add_poll_explain'] = "Hvis du ikke ønsker at tilføje en afstemning til dit indlæg, lad da venligst nedenstående felter være tomt";
$lang['Poll_question'] = "Afstemnings spørgsmål";
$lang['Poll_option'] = "Afstemnings mulighed";
$lang['Add_option'] = "Tilføj denne mulighed";
$lang['Update'] = "Opdater";
$lang['Delete'] = "Slet";
$lang['Poll_for'] = "Kør afstemning i";
$lang['Days'] = "Dage"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[Tast 0 eller lad blive blank, hvis ingen afslutnings tid ønskes på afstemningen]";
$lang['Delete_poll'] = "Slet afstemning";

$lang['Disable_HTML_post'] = "Udfør ikke HTML i dette indlæg";
$lang['Disable_BBCode_post'] = "ingen BBKoder i dette indlæg";
$lang['Disable_Smilies_post'] = "ingen Smilies i dette indlæg";

$lang['HTML_is_ON'] = "HTML er <u>Slået til</u>";
$lang['HTML_is_OFF'] = "HTML er <u>Slået fra</u>";
$lang['BBCode_is_ON'] = "%sBBKode%s er <u>Slået til</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBKode%s er <u>Slået fra</u>";
$lang['Smilies_are_ON'] = "Smilies er <u>Slået til</u>";
$lang['Smilies_are_OFF'] = "Smilies er <u>Slået fra</u>";

$lang['Attach_signature'] = "Tilføj underskrift (Underskriften kan ændres i din profil)";
$lang['Notify'] = "Send mail når indlægget besvares";
$lang['Delete_post'] = "Slet dette indlæg";

$lang['Stored'] = "Dit indlæg et blevet tilføjet";
$lang['Deleted'] = "Dit inlæg er blevet slettet";
$lang['Poll_delete'] = "Dit afstemning er blevet slettet";
$lang['Vote_cast'] = "Din afstemning er blevet noteret";

$lang['Topic_reply_notification'] = "Emnet er besvaret";

$lang['bbcode_b_help'] = "Fed skrift: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Kursiv skrift: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Understregning: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Citat: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Kode display: [code]kode[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Liste: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Nummereret liste: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Indsæt billede: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Indsæt URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Luk alle åbne BBkoder";
$lang['bbcode_s_help'] = "Skrift farve: [color=red]text[/color]  p.s. du kan f.eks skrive color=#FF0000";
$lang['bbcode_f_help'] = "Skrift størrelse: [size=x-small]lille text[/size]";

$lang['Emoticons'] = "Smilies";
$lang['More_emoticons'] = "Se flere Smilies";

$lang['Font_color'] = "Skrift farve";
$lang['color_default'] = "Normal";
$lang['color_dark_red'] = "Mørk rød";
$lang['color_red'] = "Rød";
$lang['color_orange'] = "Orange";
$lang['color_brown'] = "Brun";
$lang['color_yellow'] = "Gul";
$lang['color_green'] = "Grøn";
$lang['color_olive'] = "Oliven";
$lang['color_cyan'] = "Turkis";
$lang['color_blue'] = "Blå";
$lang['color_dark_blue'] = "Mørk Blå";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violet";
$lang['color_white'] = "Hvid";
$lang['color_black'] = "Sort";

$lang['Font_size'] = "Skrift størrelse";
$lang['font_tiny'] = "Meget lille";
$lang['font_small'] = "Lille";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Stor";
$lang['font_huge'] = "Meget stor";

$lang['Close_Tags'] = "Luk Tags";
$lang['Styles_tip'] = "p.s. Udseende kan ændres på den markerede tekst";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Privat besked";

$lang['Login_check_pm'] = "Login, for at vise private beskeder";
$lang['New_pms'] = "%d nye beskeder"; // You have 2 new messages
$lang['New_pm'] = "%d ny besked"; // You have 1 new message
$lang['No_new_pm'] = "Ingen nye beskeder";
$lang['Unread_pms'] = "%d ulæste beskeder";
$lang['Unread_pm'] = "%d ulæst besked";
$lang['No_unread_pm'] = "Ingen ulæste beskeder";
$lang['You_new_pm'] = "En privat besked ligger i din postkasse";
$lang['You_new_pms'] = "Flere private beskeder ligger i din postkasse";
$lang['You_no_new_pm'] = "Ingen private beskeder i din postkasse";

$lang['Inbox'] = "Indbakke";
$lang['Outbox'] = "Udbakke";
$lang['Savebox'] = "Gemt post";
$lang['Sentbox'] = "Sendt post";
$lang['Flag'] = "Flag";
$lang['Subject'] = "Emne";
$lang['From'] = "Fra";
$lang['To'] = "Til";
$lang['Date'] = "Dato";
$lang['Mark'] = "Marker";
$lang['Sent'] = "Sendt";
$lang['Saved'] = "Gemt";
$lang['Delete_marked'] = "Slet Markerede";
$lang['Delete_all'] = "Slet Alle";
$lang['Save_marked'] = "Gem Markerede"; 
$lang['Save_message'] = "Gem Besked";
$lang['Delete_message'] = "Slet Besked";

$lang['Display_messages'] = "Vis beskeder fra forrige"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Alle Beskeder";

$lang['No_messages_folder'] = "Du har ingen beskeder i denne folder";

$lang['PM_disabled'] = "Private beskeder er blevet slået fra på dette system";
$lang['Cannot_send_privmsg'] = "Desværre, administrator har låst din adgang til at sende private beskeder";
$lang['No_to_user'] = "Du må angive et brugernavn for at kunne sende denne besked";
$lang['No_such_user'] = "Beklager, kan ikke finde en bruger med det navn";

$lang['Disable_HTML_pm'] = "Slå HTML fra i denne besked";
$lang['Disable_BBCode_pm'] = "Slå BBCode fra i denne besked";
$lang['Disable_Smilies_pm'] = "Slå Smilies fra i denne besked";

$lang['Message_sent'] = "Din besked er blev sendt";

$lang['Click_return_inbox'] = "Klik %sHer%s for at returnerer til din indbakke";
$lang['Click_return_index'] = "Klik %sHer%s for at returnerer til indekset";

$lang['Send_a_new_message'] = "Send en ny privat besked";
$lang['Send_a_reply'] = "Besvar privat besked";
$lang['Edit_message'] = "Ret privat besked";

$lang['Notification_subject'] = "Ny Privat besked er ankommet";

$lang['Find_username'] = "Find en bruger";
$lang['Find'] = "Find";
$lang['No_match'] = "Ingen fundet";

$lang['No_post_id'] = "Ingen besked ID var angivet";
$lang['No_such_folder'] = "folderen eksistere ikke";
$lang['No_folder'] = "Ingen folder specificeret";

$lang['Mark_all'] = "Marker alle";
$lang['Unmark_all'] = "Afmarker alle";

$lang['Confirm_delete_pm'] = "Er du sikker på at du ønsker at slette denne besked?";
$lang['Confirm_delete_pms'] = "Er du sikker på at du ønsker at slette alle markerede beskeder?";

$lang['Inbox_size'] = "Din indbakke er %d%% fuld"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Din Sendt post er %d%% fuld"; 
$lang['Savebox_size'] = "Din Gemt post er %d%% fuld"; 

$lang['Click_view_privmsg'] = "Klik %sHer%s for at besøge din indbakke";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Viser profil :: %s"; // %s is username 
$lang['About_user'] = "Alt om %s"; // %s is username

$lang['Preferences'] = "Indstillinger";
$lang['Items_required'] = "Felter markeret med en * skal udfyldes med mindre andet er angivet";
$lang['Registration_info'] = "Registrerings Information";
$lang['Profile_info'] = "Profil Information";
$lang['Profile_info_warn'] = "Disse informationer vil være offentligt tilgængelige";
$lang['Avatar_panel'] = "Billede kontrol panel";
$lang['Avatar_gallery'] = "Billede galleri";

$lang['Website'] = "Hjemmeside";
$lang['Location'] = "Geografisk sted";
$lang['Contact'] = "Kontakt";
$lang['Email_address'] = "Email adresse";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Send privat besked";
$lang['Hidden_email'] = "[Skjult]";
$lang['Search_user_posts'] = "Søg efter denne brugers post";
$lang['Interests'] = "Interesser";
$lang['Occupation'] = "Beskæftigelse"; 
$lang['Poster_rank'] = "Forfatterens Niveau";

$lang['Total_posts'] = "Indlæg i alt";
$lang['User_post_pct_stats'] = "%.2f%% af alt"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f indlæg per dag"; // 1.5 posts per day
$lang['Search_user_posts'] = "Find alle indlæg skrevet af %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Beklager, den bruger eksistere ikke";
$lang['Wrong_Profile'] = "Du kan kun rette i din egen profil.";

$lang['Only_one_avatar'] = "Kun et slags billede kan angives";
$lang['File_no_data'] = "Filen på den URL som du angav, indeholder ingen data";
$lang['No_connection_URL'] = "Der kunne ikke skabes forbindelse til den angivne URL";
$lang['Incomplete_URL'] = "Den angivne URL er ikke komplet";
$lang['Wrong_remote_avatar_format'] = "Den angivne URL til billedet er ikke korrekt";
$lang['No_send_account_inactive'] = "Desværre, det er ikke muligt at få informationer om dit kodeord, da kontoen ikke er aktiv. Kontakt venligst forum'ets administrator for mere information";

$lang['Always_smile'] = "Aktiver altid Smilies";
$lang['Always_html'] = "Tillad altid HTML";
$lang['Always_bbcode'] = "Tillad altid BBkode";
$lang['Always_add_sig'] = "Tilføj altid min underskrift";
$lang['Always_notify'] = "Send besked, når der svares";
$lang['Always_notify_explain'] = "Sender en email når der bliver svaret på et indlæg du har skrevet i. Dette kan ændres i når du skriver et indlæg";

$lang['Board_style'] = "Sidens Grafiske Tema";
$lang['Board_lang'] = "Sidens Sprog";
$lang['No_themes'] = "Ingen Temaer i databasen";
$lang['Timezone'] = "Tids zone";
$lang['Date_format'] = "Dato format";
$lang['Date_format_explain'] = "Den benyttede syntaks er identisk med PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> funktion";
$lang['Signature'] = "Underskrift";
$lang['Signature_explain'] = "Denne tekst kan blive tilføjet som underskrift til alle de indlæg som du skriver, der er en begrænsning på %d karaktere";
$lang['Public_view_email'] = "Vis altid min Email Adresse";

$lang['Current_password'] = "Nuværende kodeord";
$lang['New_password'] = "Nyt kodeord";
$lang['Confirm_password'] = "Bekræft kodeord";
$lang['Confirm_password_explain'] = "Du skal bekræfte dit nuværende kodeord, hvis du vil ændre det eller ændre din email adresse";
$lang['password_if_changed'] = "Du skal kun angive kodeord hvis du ønsker at skifte til et nyt";
$lang['password_confirm_if_changed'] = "Du skal kun bekræfte dit kodeord, hvis du har ændret det ovenfor";

$lang['Avatar'] = "Personligt Billede";
$lang['Avatar_explain'] = "Viser et lille billede sammen med dine personlige data i dine indlæg. Kun et billede kan være aktivt på et hvilket som helst tidspunkt, formatet må maksimalt være (%d,%d) (hxb) pixels og ikke større end %dkB.";
$lang['Upload_Avatar_file'] = "Hent Billede fra din maskine";
$lang['Upload_Avatar_URL'] = "Hent billede fra URL";
$lang['Upload_Avatar_URL_explain'] = "Indtaste en URL hvor billedet befinder sig, det vil blive kopieret til denne server.";
$lang['Pick_local_Avatar'] = "Vælg billede fra galleri";
$lang['Link_remote_Avatar'] = "Link til et billede på anden server";
$lang['Link_remote_Avatar_explain'] = "Indtast en URL til et billede der befinder sig på en anden server.";
$lang['Avatar_URL'] = "URL til billede";
$lang['Select_from_gallery'] = "Vælg billede fra galleri";
$lang['View_avatar_gallery'] = "Vis galleri";

$lang['Select_avatar'] = "Vælg billede";
$lang['Return_profile'] = "Fortryd billede";
$lang['Select_category'] = "Vælg Kategori";

$lang['Delete_Image'] = "Slet billede";
$lang['Current_Image'] = "Nuværende billede";

$lang['Notify_on_privmsg'] = "Giv besked når der er ny privat besked";
$lang['Popup_on_privmsg'] = "Pop up boks når der er ny privat besked"; 
$lang['Popup_on_privmsg_explain'] = "Nogle browsere åbner muligvis et nyt vindue for at informere dig når der ankommer en ny besked"; 
$lang['Hide_user'] = "Skjul min online status";

$lang['Profile_updated'] = "Din profil er blevet opdateret";
$lang['Profile_updated_inactive'] = "Din profil er blevet opdateret, da der er ændret vitale oplysninger er din konto blevet deaktiveret. Kontroller venligst din email, for oplysninger om hvordan den aktiveres igen, eller hvis administator aktivering er nødvendig vent på at forumets administrator aktivere kontoen";

$lang['Password_mismatch'] = "Det nye kodeord, og det felt der indeholde bekræftelse af det nye kodeord er ikke ens";
$lang['Current_password_mismatch'] = "Det angivne kodeord er ikke det samme som det der er gemt i vores database";
$lang['Password_long'] = "Dit kodeord må ikke være længere end 32 tegn";
$lang['Username_taken'] = "Desværre, det brugernavn er allerede taget";
$lang['Username_invalid'] = "Desværre, det brugernavn indeholder et ulovlig tegn som \"";
$lang['Username_disallowed'] = "Desværre, dette brugernavn er ikke tilladt";
$lang['Email_taken'] = "Desværre, denne email er allerede registreret til en anden bruger";
$lang['Email_banned'] = "Desværre, denne email er blevet bandlyst";
$lang['Email_invalid'] = "Desværre, denne email er ikke brugbar";
$lang['Signature_too_long'] = "Din underskrift er for lang";
$lang['Fields_empty'] = "Du skal udfylde de nødvendige felter";
$lang['Avatar_filetype'] = "Billede filen skal være af fil typen .jpg, .gif or .png";
$lang['Avatar_filesize'] = "Billede filens størrelse må ikke overstige %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Billede filen må ikke være større end (%d,%d) (hxb) pixels"; 

$lang['Welcome_subject'] = "Velkommen til %s Forum"; // Welcome to my.com forums
$lang['New_account_subject'] = "Ny bruger konto";
$lang['Account_activated_subject'] = "Konto aktiveret";

$lang['Account_added'] = "Tak for tilmeldingen, din konto er nu blevet oprettet. Du kan nu logge ind med dit brugernavn og kodeord";
$lang['Account_inactive'] = "Din konto er nu oprettet. Dette forum kræver dog at din konto aktiveres før den kan tages i brug, en aktiverings nøgle er sendt til din email. Kontroller venligst din email for mere information";
$lang['Account_inactive_admin'] = "Din konto er nu oprettet. Dette forum kræver dog at din konto godkendes af en administrator. En email er nu blevet sendt til dem, og du vil blive informeret når din konto er aktiveret";
$lang['Account_active'] = "Din konto er nu aktiveret, til for din tilmelding";
$lang['Account_active_admin'] = "Din konto er nu aktiveret";
$lang['Reactivate'] = "Genaktiver venligst din konto!";
$lang['COPPA'] = "Din konto er nu oprettet, dog skal den først verificeres, kontroller venligst din mail for yderligere instruktioner.";

$lang['Registration'] = "Tilmeldings vilkår";
$lang['Reg_agreement'] = "Administratorer og redaktører af dette forum vil prøve at fjerne eller rette i indlæg der kan være stødende så hurtigt som muligt, dog vil det være umuligt at gennemlæse alle indlæg. Derfor acceptere du at være indforstået med at alle indlæg i dette forum udtrykker holdninger og meninger af forfatterne af indlæggene, og ikke administratorene, redaktører eller webmastere (bortset fra indlæg skrevet af disse) og derfor kan disse heller ikke holdes ansvarlige.<br /><br />Du vedkender også ikke at skrive indlæg der indeholder skældsord, pornografiske , vulgære, bagtalene, hadefulde, truende, seksuelt-orienterede eller andre ord/vendinger som strider mod gældende lovgivning. Såfremt dette konstateres, kan det føre til at du øjeblikkeligt og permanent udelukkes fra forumet (din Internet udbyder vil blive orienteret). Din unike IP adresse logges i alle indlæg der skrive til dette forum, for at forebygge misbrug af en hver slags. Du vedkender også at administrator, redaktører og webmastere tilhørende dette forum har ret til at fjerne, rette eller lukke et hvert emne/indlæg hvornår som helst de måtte ønske dette. Som bruger giver du ret til at alle informationer som du har indtastet gemmes i en database. Disse informationer vil ikke blive videregivet til 3. part uden din viden, webmastere, administratore eller redaktører kan ikke blive holdt ansvarlige for eventuelle hacker angreb der måtte føre til afslørelse af disse data.<br /><br />Dette forum bruger cookies til at gemme information på din lokal harddisk. Disse cookier indeholder ingen af de informationer som du har indtastet om dig selv, De er kun til for at sørge for at du får den nemeste brugerflade. Din email bliver kun brugt til at sende en verificering af dine tilmeldings detaljer og kodeord (og til at sende et nyt kodeord, hvis du skulle glemme dit nuværende).<br /><br />Ved at klikke på nedenstående tilmeldings link acceptere du at være indforstået med disse forhold.";

$lang['Agree_under_13'] = "Jeg er indforstået med disse vilkår og er <b>under</b> 13 år gammel";
$lang['Agree_over_13'] = "Jeg er indforstået med disse vilkår og er <b>over</b> 13 år gammel";
$lang['Agree_not'] = "Jeg er ikke indforstået med disse vilkår";

$lang['Wrong_activation'] = "Aktiverings nøglen som benyttes, passer ikke til den som er gemt i vores database";
$lang['Send_password'] = "Send mig et nyt kodeord"; 
$lang['Password_updated'] = "Et nyt kodeord er blevet genereret, kontroller venligst din email, for information om hvordan du aktivere det";
$lang['No_email_match'] = "Den angivne email passer ikke til det angivne brugernavn";
$lang['New_password_activation'] = "Aktivering af nyt kodeord";
$lang['Password_activated'] = "Din konto er gen-aktiveret. For at logge ind skal du benytte det kodeord som er sendt til dig i en email";

$lang['Send_email_msg'] = "Send en email besked";
$lang['No_user_specified'] = "Der var ikke angivet noget brugernavn";
$lang['User_prevent_email'] = "Denne bruger ønsker ikke at modtage emails. forsøg istedet at sende som privat besked";
$lang['User_not_exist'] = "Den bruger eksistere ikke";
$lang['CC_email'] = "Send en kopi af denne email til dig selv";
$lang['Email_message_desc'] = "Denne besked vil blive sendt som ren tekst, skrive derfor ikke HTML eller BBCode. retur adressen af denne mail vil blive sat til din email addresse.";
$lang['Flood_email_limit'] = "Du kan ikke sende en anden email på nuværende tidspunkt, forsøg venligst igen lidt senere";
$lang['Recipient'] = "Modtager";
$lang['Email_sent'] = "Emailen er nu sendt";
$lang['Send_email'] = "Send email";
$lang['Empty_subject_email'] = "Du skal udfylde en overskrift til emailen";
$lang['Empty_message_email'] = "Du skal udfylde med en besked som du ønsker skal sendes";


//
// Memberslist
//
$lang['Select_sort_method'] = "Vælg sorterings metode";
$lang['Sort'] = "Sorter";
$lang['Sort_Top_Ten'] = "Top Ti Indlæg";
$lang['Sort_Joined'] = "Tilmeldt Dato";
$lang['Sort_Username'] = "Brugernavn";
$lang['Sort_Location'] = "Placering";
$lang['Sort_Posts'] = "Totale Indlæg";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Hjemmeside";
$lang['Sort_Ascending'] = "Stigende";
$lang['Sort_Descending'] = "Faldende";
$lang['Order'] = "Rækkefølge";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Gruppe Administration";
$lang['Group_member_details'] = "Gruppe Medlemskabs detaljer";
$lang['Group_member_join'] = "Tilmeld til en gruppe";

$lang['Group_Information'] = "Gruppe Information";
$lang['Group_name'] = "Gruppe navn";
$lang['Group_description'] = "Gruppe beskrivelse";
$lang['Group_membership'] = "Gruppe medlemskab";
$lang['Group_Members'] = "Gruppe Medlemmer";
$lang['Group_Moderator'] = "Gruppe Redaktør";
$lang['Pending_members'] = "Afventende Brugere";

$lang['Group_type'] = "Gruppe type";
$lang['Group_open'] = "Åben gruppe";
$lang['Group_closed'] = "Lukket gruppe";
$lang['Group_hidden'] = "Skjult gruppe";

$lang['Current_memberships'] = "Medlem af følgende grupper";
$lang['Non_member_groups'] = "Ikke medlem af følgende grupper";
$lang['Memberships_pending'] = "Tilmelding afventer";

$lang['No_groups_exist'] = "Der er ingen grupper oprettet";
$lang['Group_not_exist'] = "Den gruppe eksistere ikke";

$lang['Join_group'] = "Tilføj gruppen";
$lang['No_group_members'] = "Denne gruppe har ingen medlemmer";
$lang['Group_hidden_members'] = "Denne gruppe er skjult, Du kan ikke få vist brugerne";
$lang['No_pending_group_members'] = "Denne gruppe har ingen afventende brugere";
$lang["Group_joined"] = "Du er nu tilmeldt denne gruppe<br />Du vil få besked når din tilmelding er blevet godkendt af gruppens redaktør";
$lang['Group_request'] = "En bruger ønsker at tilmelde sig til din gruppe";
$lang['Group_approved'] = "Din tilmelding er blevet godkendt";
$lang['Group_added'] = "Du er nu tilføjet til denne bruger gruppe"; 
$lang['Already_member_group'] = "Du er allerede medlem af denne gruppe";
$lang['User_is_member_group'] = "Brugeren er allerede medlem af denne gruppe";
$lang['Group_type_updated'] = "Gruppens type er ændret";

$lang['Could_not_add_user'] = "Den valgte bruger findes ikke";
$lang['Could_not_anon_user'] = "Du kan ikke gøre brugeren Gæst til gruppe medlem";

$lang['Confirm_unsub'] = "Er du sikker på at du ønsker at melde dig ud af gruppen?";
$lang['Confirm_unsub_pending'] = "Din tilmelding til denne gruppe er endnu ikke blevet godkendt, er du sikker på du ønsker at framelde dig?";

$lang['Unsub_success'] = "Du er nu meldt ud af denne gruppe.";

$lang['Approve_selected'] = "Godkend Valgte";
$lang['Deny_selected'] = "Afvis valgte";
$lang['Not_logged_in'] = "Du skal være logget ind for at tilmelde dig til en gruppe.";
$lang['Remove_selected'] = "Slet Valgte";
$lang['Add_member'] = "Tilføj Medlem";
$lang['Not_group_moderator'] = "Du er ikke Redaktør for denne gruppe, derfor kan du ikke udføre dette.";

$lang['Login_to_join'] = "Du skal logge ind før du kan melde dig ind i en gruppe";
$lang['This_open_group'] = "Dette er en åben gruppe, klik for at opnå medlemskab";
$lang['This_closed_group'] = "Denne gruppe er lukket, der kan ikke tilføjes nye medlemmer";
$lang['This_hidden_group'] = "Denne gruppe er skjult, automatisk tilføjelse af brugere er ikke tilladt";
$lang['Member_this_group'] = "Du er medlem af denne gruppe";
$lang['Pending_this_group'] = "Dit medlemskab af denne gruppe er afventende";
$lang['Are_group_moderator'] = "Du er Redaktør af gruppen";
$lang['None'] = "Ingen";

$lang['Subscribe'] = "Meld ind";
$lang['Unsubscribe'] = "Meld ud";
$lang['View_Information'] = "Vis Information";


//
// Search
//
$lang['Search_query'] = "Søge ord";
$lang['Search_options'] = "Søge muligheder";

$lang['Search_keywords'] = "Søg efter nøgleord";
$lang['Search_keywords_explain'] = "Du kan bruge <u>AND</u> for af definere ord som skal indgå i resultatet, <u>OR</u> for at definere ord som må indgå i resultatet og  <u>NOT</u> for at definere ord som  ikke må findes i resultatet. Brug * som et wildcard for ukendte bogstaver";
$lang['Search_author'] = "Søg efter en bestemt forfatter";
$lang['Search_author_explain'] = "Brug * som et wildcard for ukendte bogstaver";

$lang['Search_for_any'] = "Søg efter alle udtryk eller brug søgeparameteren som indtastet";
$lang['Search_for_all'] = "Søg efter alle udtryk";
$lang['Search_title_msg'] = "Søg i Emne og besked felterne";
$lang['Search_msg_only'] = "Søg kun i besked feltet";

$lang['Return_first'] = "Returner de første"; // followed by xxx characters in a select box
$lang['characters_posts'] = "karakter af indlægget";

$lang['Search_previous'] = "Søg tilbage i tiden"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Sorter efter";
$lang['Sort_Time'] = "Indlæggets dato";
$lang['Sort_Post_Subject'] = "Indlæggets Overskrift";
$lang['Sort_Topic_Title'] = "Emnets Overskrift";
$lang['Sort_Author'] = "Forfatter";
$lang['Sort_Forum'] = "Forum";

$lang['Display_results'] = "Vis resultatet som";
$lang['All_available'] = "Alle";
$lang['No_searchable_forums'] = "Du har ikke tilladelse til at søge i alle forum på dette site";

$lang['No_search_match'] = "Igen Emner eller indlæg, passer til dine søgekriterier";
$lang['Found_search_match'] = "Søgningen fandt %d hit"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Søgningen resulterede i %d hits"; // eg. Search found 24 matches

$lang['Close_window'] = "Luk Vindue";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Beklager, Kun %s kan skrive bekendtgørelser i dette forum";
$lang['Sorry_auth_sticky'] = "Beklager, Kun %s kan skrive opslag i dette forum"; 
$lang['Sorry_auth_read'] = "Beklager, Kun %s kan læse emner i dette forum"; 
$lang['Sorry_auth_post'] = "Beklager, kun %s kan skrive emner i dette forum"; 
$lang['Sorry_auth_reply'] = "Beklager, kun %s kan besvare indlæg i dette forum"; 
$lang['Sorry_auth_edit'] = "Beklager, kun %s kan rette indlæg i dette forum"; 
$lang['Sorry_auth_delete'] = "Beklager, kun %s kan slette indlæg i dette forum"; 
$lang['Sorry_auth_vote'] = "Beklager, kun %s kan stemme på afstemninger i dette forum"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>Gæste brugere</b>";
$lang['Auth_Registered_Users'] = "<b>Tilmeldte brugere</b>";
$lang['Auth_Users_granted_access'] = "<b>Brugere med specielle rettighedder</b>";
$lang['Auth_Moderators'] = "<b>Redaktører</b>";
$lang['Auth_Administrators'] = "<b>administratorer</b>";

$lang['Not_Moderator'] = "Du er ikke Redaktør af dette forum";
$lang['Not_Authorised'] = "Ej tilladt";

$lang['You_been_banned'] = "Du er blevet låst ude fra dette forum<br />Kontakt venligst webmaster eller forumets administrator for information";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Der er ingen tilmeldte brugere og "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Der er %d tilmeldte brugere og "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Der er %d tilmeldt bruge og "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 skjulte brugere lige nu"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d skjult bruger lige nu"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d skjulte brugere lige nu"; // 6 Hidden users online
$lang['Guest_users_online'] = "Der er %d gæste brugere lige nu"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "Der er ingen gæste brugere lige nu"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Der er %d gæste bruger lige nu"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Der er ingen brugere på forumet lige nu";

$lang['Online_explain'] = "Disse data er baseret på brugernes aktivitet de sidste 5 minutter";

$lang['Forum_Location'] = "Forum Placering";
$lang['Last_updated'] = "Sidst opdateret";

$lang['Forum_index'] = "Forum index";
$lang['Logging_on'] = "Logning tændt";
$lang['Posting_message'] = "Skriver en besked";
$lang['Searching_forums'] = "Søger blandt forums";
$lang['Viewing_profile'] = "Viser profilen";
$lang['Viewing_online'] = "Viser hvem der er på nu";
$lang['Viewing_member_list'] = "Viser Tilmeldte brugere";
$lang['Viewing_priv_msgs'] = "Viser Private beskeder";
$lang['Viewing_FAQ'] = "Viser Generelle Spørgsmål";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Redaktør Administration";
$lang['Mod_CP_explain'] = "På denne side har du mulighed for at ændre dette forum, du kan Slette, flytte og låse emnerne, du kan også låse emnerne op igen.";

$lang['Select'] = "Vælg";
$lang['Delete'] = "Slet";
$lang['Move'] = "Flyt";
$lang['Lock'] = "Lås";
$lang['Unlock'] = "Lås op";

$lang['Topics_Removed'] = "Det valgte emne er nu slettet";
$lang['Topics_Locked'] = "Det valgte emne er nu låst";
$lang['Topics_Moved'] = "Det valgte emne er nu flyttet";
$lang['Topics_Unlocked'] = "Det valgte emne er nu låst op igen";
$lang['No_Topics_Moved'] = "Igen emner er blevet flyttet";

$lang['Confirm_delete_topic'] = "Er du sikker på du ønsker at slette de(t) valgte emne(r)?";
$lang['Confirm_lock_topic'] = "Er du sikker på du ønsker at låse de(t) valgte emne(r)?";
$lang['Confirm_unlock_topic'] = "Er du sikker på du ønsker at låse de(t) valgte emne(r) op?";
$lang['Confirm_move_topic'] = "Er du sikker på du ønsker at flytte de(t) valgte emne(r)?";

$lang['Move_to_forum'] = "Flyt til nyt forum";
$lang['Leave_shadow_topic'] = "Bevar henvisning til emnet i det gamle forum.";

$lang['Split_Topic'] = "Del et Emne";
$lang['Split_Topic_explain'] = "Ved at bruge nedenstående, kan du dele et emne i 2 dele, enten ved at vælge posterne individuelt eller ved at dele ved et bestemt indlæg";
$lang['Split_title'] = "Ny Emne titel";
$lang['Split_forum'] = "Emnets nye Forum";
$lang['Split_posts'] = "Del de valgte indlæg";
$lang['Split_after'] = "Del fra og med det valgte indlæg";
$lang['Topic_split'] = "Det valgte emne er nu delt i 2";

$lang['Too_many_error'] = "Du har valgt mere end 1 indlæg. Et emne kan kun deles efter ét bestemt indlæg!";

$lang['None_selected'] = "Du har ikke valgt et emne at udføre denne handling på. Gå venligst tilbage og vælg et emne.";
$lang['New_forum'] = "Nyt forum";

$lang['This_posts_IP'] = "Dette indlægs IP";
$lang['Other_IP_this_user'] = "Andre IP som denne bruger også har skrevet indlæg fra";
$lang['Users_this_IP'] = "Brugere som har skrevet indlæg fra denne IP";
$lang['IP_info'] = "IP Information";
$lang['Lookup_IP'] = "Slå IP op";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Alle tider er %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 timer";
$lang['-11'] = "GMT - 11 Timer";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Timer";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 Timer";
$lang['-3.5'] = "GMT - 3.5 Timer";
$lang['-3'] = "GMT - 3 Timer";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 Time";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europa)";
$lang['2'] = "EET (Europa)";
$lang['3'] = "GMT + 3 Timer";
$lang['3.5'] = "GMT + 3.5 Timer";
$lang['4'] = "GMT + 4 Timer";
$lang['4.5'] = "GMT + 4.5 Timer";
$lang['5'] = "GMT + 5 Timer";
$lang['5.5'] = "GMT + 5.5 Timer";
$lang['6'] = "GMT + 6 Timer";
$lang['7'] = "GMT + 7 Timer";
$lang['8'] = "WST (Australien)";
$lang['9'] = "GMT + 9 Timer";
$lang['9.5'] = "CST (Australien)";
$lang['10'] = "EST (Australien)";
$lang['11'] = "GMT + 11 Timer";
$lang['12'] = "GMT + 12 Timer";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 Timer) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 Timer) Midway Island, Findland";
$lang['tz']['-10'] = "(GMT -10:00 Timer) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 Timer) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 Timer) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 Timer) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 Timer) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 Timer) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 Timer) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 Timer) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 Timer) Brassilien, Buenos Aires, Georgetown, Falklands ørne";
$lang['tz']['-2'] = "(GMT -2:00 Timer) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 Time) Azorene, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 Time) Amsterdam, Berlin, Brussel, København, Madrid, Paris, Rom";
$lang['tz']['2'] = "(GMT +2:00 Timer) Cairo, Helsinki, Kaliningrad, Syd Afrika";
$lang['tz']['3'] = "(GMT +3:00 Timer) Baghdad, Riyadh, Moskva, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 Timer) Tehran";
$lang['tz']['4'] = "(GMT +4:00 Timer) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 Timer) Kabul";
$lang['tz']['5'] = "(GMT +5:00 Timer) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 Timer) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 Timer) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 Timer) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 Timer) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 Timer) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 Timer) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 Timer) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 Timer) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 Timer) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 Timer) Auckland, Wellington, Fiji, Marshall ørene";

$lang['datetime']['Sunday'] = "Søndag";
$lang['datetime']['Monday'] = "Mandag";
$lang['datetime']['Tuesday'] = "Tirsdag";
$lang['datetime']['Wednesday'] = "Onsdag";
$lang['datetime']['Thursday'] = "Torsdag";
$lang['datetime']['Friday'] = "Fredag";
$lang['datetime']['Saturday'] = "Lørdag";
$lang['datetime']['Sun'] = "Søn";
$lang['datetime']['Mon'] = "Man";
$lang['datetime']['Tue'] = "Tirs";
$lang['datetime']['Wed'] = "Ons";
$lang['datetime']['Thu'] = "Tors";
$lang['datetime']['Fri'] = "Fre";
$lang['datetime']['Sat'] = "Lør";
$lang['datetime']['January'] = "Januar";
$lang['datetime']['February'] = "Februar";
$lang['datetime']['March'] = "Marts";
$lang['datetime']['April'] = "April";
$lang['datetime']['May'] = "Maj";
$lang['datetime']['June'] = "Juni";
$lang['datetime']['July'] = "Juli";
$lang['datetime']['August'] = "August";
$lang['datetime']['September'] = "September";
$lang['datetime']['October'] = "Oktober";
$lang['datetime']['November'] = "November";
$lang['datetime']['December'] = "December";
$lang['datetime']['Jan'] = "Jan";
$lang['datetime']['Feb'] = "Feb";
$lang['datetime']['Mar'] = "Mar";
$lang['datetime']['Apr'] = "Apr";
$lang['datetime']['May'] = "Maj";
$lang['datetime']['Jun'] = "Jun";
$lang['datetime']['Jul'] = "Jul";
$lang['datetime']['Aug'] = "Aug";
$lang['datetime']['Sep'] = "Sep";
$lang['datetime']['Oct'] = "Okt";
$lang['datetime']['Nov'] = "Nov";
$lang['datetime']['Dec'] = "Dec";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Information";
$lang['Critical_Information'] = "Kritisk Information";

$lang['General_Error'] = "Generel Fejl";
$lang['Critical_Error'] = "Kritisk Fejl";
$lang['An_error_occured'] = "Der er opstået en Fejl";
$lang['A_critical_error'] = "Der er opstået en kritisk Fejl";


//
// That's all Folks!
// -------------------------------------------------

?>