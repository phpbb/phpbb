<?php
/***************************************************************************
 *                            lang_main.php [polish]
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
 *   Translation by: Mike Paluchowski, Radek Kmiecicki
 *   See website: www.phpbb.pl
 *
 ***************************************************************************/
 
/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/


//setlocale(LC_ALL, "pl");
$lang['ENCODING'] = "iso-8859-2";
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Category'] = "Kategoria";
$lang['Topic'] = "Temat";
$lang['Topics'] = "Tematy";
$lang['Replies'] = "Odpowiedzi";
$lang['Views'] = "Wy¶wietleñ";
$lang['Post'] = "Post";
$lang['Posts'] = "Posty";
$lang['Posted'] = "Wys³any";
$lang['Username'] = "U¿ytkownik";
$lang['Password'] = "Has³o";
$lang['Email'] = "Email";
$lang['Poster'] = "Wys³a³";
$lang['Author'] = "Autor";
$lang['Time'] = "Czas";
$lang['Hours'] = "Godzin";
$lang['Message'] = "Wiadomo¶æ";

$lang['1_Day'] = "1 Dzieñ";
$lang['7_Days'] = "7 Dni";
$lang['2_Weeks'] = "2 Tygodnie";
$lang['1_Month'] = "1 Miesi±c";
$lang['3_Months'] = "3 Miesi±ce";
$lang['6_Months'] = "6 Miesiêcy";
$lang['1_Year'] = "1 Rok";

$lang['Go'] = "Id¼";
$lang['Jump_to'] = "Skocz do";
$lang['Submit'] = "Wy¶lij";
$lang['Reset'] = "Wyczy¶æ";
$lang['Cancel'] = "Anuluj";
$lang['Preview'] = "Podgl±d";
$lang['Confirm'] = "Zatwierd¼";
$lang['Spellcheck'] = "Sprawd¼ pisowniê";
$lang['Yes'] = "Tak";
$lang['No'] = "Nie";
$lang['Enabled'] = "W³±czony";
$lang['Disabled'] = "Wy³±czony";
$lang['Error'] = "B³±d";

$lang['Next'] = "Nastêpny";
$lang['Previous'] = "Poprzedni";
$lang['Goto_page'] = "Id¼ do strony";
$lang['Joined'] = "Do³±czy³";
$lang['IP_Address'] = "Adres IP";

$lang['Select_forum'] = "Wybierz forum";
$lang['View_latest_post'] = "Zobacz ostatni post";
$lang['View_newest_post'] = "Zobacz najnowszy post";
$lang['Page_of'] = "Strona <b>%d</b> z <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "Numer ICQ";
$lang['AIM'] = "Adres AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "Forum %s Strona G³ówna";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Napisz nowy temat";
$lang['Reply_to_topic'] = "Odpowiedz do tematu";
$lang['Reply_with_quote'] = "Odpowiedz z cytatem";

$lang['Click_return_topic'] = "Kliknij %sTutaj%s aby powróciæ do tematu"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Kliknij %sTutaj%s aby spróbowaæ ponownie";
$lang['Click_return_forum'] = "Kliknij %sTutaj%s aby powróciæ na forum";
$lang['Click_view_message'] = "Kliknij %sTutaj%s aby zobaczyæ swoj± wiadomo¶æ";
$lang['Click_return_modcp'] = "Kliknij %sTutaj%s aby powróciæ do Panelu Kontrolnego Moderacji";
$lang['Click_return_group'] = "Kliknij %sTutaj%s aby powróciæ do informacji o grupach";

$lang['Admin_panel'] = "Panel Administracyjny";

$lang['Board_disable'] = "Przepraszamy, ale to forum jest obecnie niedostêpne. Zapraszamy pó¼niej";


//
// Global Header strings
//
$lang['Registered_users'] = "Zarejestrowani U¿ytkownicy:";
$lang['Online_users_zero_total'] = "Na Forum jest <b>0</b> u¿ytkowników :: ";
$lang['Online_users_total'] = "Na Forum jest <b>%d</b> u¿ytkowników :: ";
$lang['Online_user_total'] = "Na Forum jest <b>%d</b> u¿ytkownik :: ";
$lang['Reg_users_zero_total'] = "0 Zarejestrowanych, ";
$lang['Reg_users_total'] = "%d Zarejestrowanych, ";
$lang['Reg_user_total'] = "%d Zarejestrowany, ";
$lang['Hidden_users_zero_total'] = "0 Ukrytych i ";
$lang['Hidden_users_total'] = "%d Ukrytych i ";
$lang['Hidden_user_total'] = "%d Ukrytych i ";
$lang['Guest_users_zero_total'] = "0 Go¶ci";
$lang['Guest_users_total'] = "%d Go¶ci";
$lang['Guest_user_total'] = "%d Go¶æ";

$lang['Admin_online_color'] = "%sAdministrator%s"; 
$lang['Mod_online_color'] = "%sModerator%s"; 

$lang['You_last_visit'] = "Ostatnio odwiedzi³e¶ nas %s"; // %s replaced by date/time
$lang['Current_time'] = "Obecny czas to %s"; // %s replaced by time

$lang['Search_new'] = "Zobacz posty od ostatniej wizyty";
$lang['Search_your_posts'] = "Zobacz swoje posty";
$lang['Search_unanswered'] = "Zobacz posty bez odpowiedzi";

$lang['Register'] = "Rejestracja";
$lang['Profile'] = "Profil";
$lang['Edit_profile'] = "Zmieñ swój profil";
$lang['Search'] = "Szukaj";
$lang['Memberlist'] = "U¿ytkownicy";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Przewodnik BBCode";
$lang['Usergroups'] = "Grupy";
$lang['Last_Post'] = "Ostatni Post";
$lang['Moderator'] = "Moderator";
$lang['Moderators'] = "Moderatorzy";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Nasi u¿ytkownicy napisali <b>0</b> wiadomo¶ci"; // Number of posts
$lang['Posted_articles_total'] = "Nasi u¿ytkownicy napisali <b>%d</b> wiadomo¶ci"; // Number of posts
$lang['Posted_article_total'] = "Nasi u¿ytkownicy napisali <b>%d</b> wiadomo¶æ"; // Number of posts
$lang['Registered_users_zero_total'] = "Mamy <b>0</b> zarejestrowanych u¿ytkowników"; // # registered users
$lang['Registered_users_total'] = "Mamy <b>%d</b> zarejestrowanych u¿ytkowników"; // # registered users
$lang['Registered_user_total'] = "Mamy <b>%d</b> zarejestrowanego u¿ytkownika"; // # registered users
$lang['Newest_user'] = "Ostatnio zarejestrowa³ siê <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Brak nowych postów od twojej ostatniej wizyty";
$lang['No_new_posts'] = "Brak nowych postów";
$lang['New_posts'] = "Nowe posty";
$lang['New_post'] = "Nowy post";
$lang['No_new_posts_hot'] = "Brak nowych postów [ Popularny ]";
$lang['New_posts_hot'] = "Nowe posty [ Popularny ]";
$lang['No_new_posts_locked'] = "Brak nowych postów [ Zablokowany ]";
$lang['New_posts_locked'] = "Nowe posty [ Zablokowany ]";
$lang['Forum_is_locked'] = "Forum Zablokowane";


//
// Login
//
$lang['Enter_password'] = "Wpisz nazwê u¿ytkownika i has³o by siê zalogowaæ";
$lang['Login'] = "Zaloguj";
$lang['Logout'] = "Wyloguj";

$lang['Forgotten_password'] = "Zapomnia³em has³a";

$lang['Log_me_in'] = "Zaloguj mnie automatycznie przy ka¿dej wizycie";

$lang['Error_login'] = "Poda³e¶ nieprawid³owe lub nieaktywne dane u¿ytkownika";


//
// Index page
//
$lang['Index'] = "Indeks";
$lang['No_Posts'] = "Brak Postów";
$lang['No_forums'] = "Brak For";

$lang['Private_Message'] = "Prywatna Wiadomo¶æ";
$lang['Private_Messages'] = "Prywatne Wiadomo¶ci";
$lang['Who_is_Online'] = "Kto jest na Forum";

$lang['Mark_all_forums'] = "Oznacz wszystkie fora jako przeczytane";
$lang['Forums_marked_read'] = "Wszystkie fora oznaczono jako przeczytane";


//
// Viewforum
//
$lang['View_forum'] = "Zobacz Forum";

$lang['Forum_not_exist'] = "Wybrane przez Ciebie forum nie istnieje";
$lang['Reached_on_error'] = "Dotar³e¶ na t± stronê w wyniku b³êdu";

$lang['Display_topics'] = "Wy¶wietl tematy z ostatnich";
$lang['All_Topics'] = "Wszystkie Tematy";

$lang['Topic_Announcement'] = "<b>Og³oszenie:</b>";
$lang['Topic_Sticky'] = "<b>Przyklejony:</b>";
$lang['Topic_Moved'] = "<b>Przesuniêty:</b>";
$lang['Topic_Poll'] = "<b>[ Ankieta ]</b>";

$lang['Mark_all_topics'] = "Oznacz wszystkie tematy jako przeczytane";
$lang['Topics_marked_read'] = "Tematy na tym forum zosta³y oznaczone jako przeczytane";

$lang['Rules_post_can'] = "<b>Mo¿esz</b> pisaæ nowe tematy";
$lang['Rules_post_cannot'] = "<b>Nie mo¿esz</b> pisaæ nowych tematów";
$lang['Rules_reply_can'] = "<b>Mo¿esz</b> odpowiadaæ w tematach";
$lang['Rules_reply_cannot'] = "<b>Nie mo¿esz</b> odpowiadaæ w tematach";
$lang['Rules_edit_can'] = "<b>Mo¿esz</b> zmieniaæ swoje posty";
$lang['Rules_edit_cannot'] = "<b>Nie mo¿esz</b> zmieniaæ swoich postów";
$lang['Rules_delete_can'] = "<b>Mo¿esz</b> usuwaæ swoje posty";
$lang['Rules_delete_cannot'] = "<b>Nie mo¿esz</b> usuwaæ swoich postów";
$lang['Rules_vote_can'] = "<b>Mo¿esz</b> g³osowaæ w ankietach";
$lang['Rules_vote_cannot'] = "<b>Nie mo¿esz</b> g³osowaæ w ankietach";
$lang['Rules_moderate'] = "<b>Mo¿esz</b> %smoderowaæ to forum%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Nie ma ¿adnych postów na tym forum<br />Kliknij na przycisk <b>Nowy Temat</b> aby co¶ napisaæ";


//
// Viewtopic
//
$lang['View_topic'] = "Zobacz temat";

$lang['Guest'] = 'Go¶æ';
$lang['Post_subject'] = "Temat postu";
$lang['View_next_topic'] = "Zobacz nastêpny temat";
$lang['View_previous_topic'] = "Zobacz poprzedni temat";
$lang['Submit_vote'] = "Wy¶lij G³os";
$lang['View_results'] = "Zobacz Wyniki";

$lang['No_newer_topics'] = "Nie ma nowszych tematów na tym forum";
$lang['No_older_topics'] = "Nie ma starszych tematów na tym forum";
$lang['Topic_post_not_exist'] = "Wybrany przez Ciebie temat lub post nie istnieje";
$lang['No_posts_topic'] = "Nie istniej± ¿adne posty dla tego tematu";

$lang['Display_posts'] = "Wy¶wietl posty z ostatnich";
$lang['All_Posts'] = "Wszystkie Posty";
$lang['Newest_First'] = "Najpierw Nowsze";
$lang['Oldest_First'] = "Najpierw Starsze";

$lang['Back_to_top'] = "Powrót do góry";

$lang['Read_profile'] = "Zobacz profil autora"; 
$lang['Send_email'] = "Wy¶lij email do autora";
$lang['Visit_website'] = "Odwied¼ stronê autora";
$lang['ICQ_status'] = "Status ICQ";
$lang['Edit_delete_post'] = "Zmieñ/Usuñ ten post";
$lang['View_IP'] = "Zobacz IP autora";
$lang['Delete_post'] = "Usuñ ten post";

$lang['wrote'] = "napisa³"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Cytat"; // comes before bbcode quote output.
$lang['Code'] = "Kod"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Ostatnio zmieniony przez %s dnia %s, w ca³o¶ci zmieniany %d raz"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Ostatnio zmieniony przez %s dnia %s, w ca³o¶ci zmieniany %d razy"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Zablokuj ten temat";
$lang['Unlock_topic'] = "Odblokuj ten temat";
$lang['Move_topic'] = "Przesuñ ten temat";
$lang['Delete_topic'] = "Usuñ ten temat";
$lang['Split_topic'] = "Podziel ten temat";

$lang['Stop_watching_topic'] = "Przestañ ¶ledziæ ten temat";
$lang['Start_watching_topic'] = "¦led¼ odpowiedzi w tym temacie";
$lang['No_longer_watching'] = "Przesta³e¶ ¶ledziæ ten temat";
$lang['You_are_watching'] = "Rozpocz±³e¶ ¶ledzenie tego tematu";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Tre¶æ wiadomo¶ci";
$lang['Topic_review'] = "Przegl±d tematu";

$lang['No_post_mode'] = "Nie okre¶lono typu postu";

$lang['Post_a_new_topic'] = "Napisz nowy temat";
$lang['Post_a_reply'] = "Napisz odpowied¼";
$lang['Post_topic_as'] = "Napisz temat jako";
$lang['Edit_Post'] = "Zmieñ post";
$lang['Options'] = "Opcje";

$lang['Post_Announcement'] = "Og³oszenie";
$lang['Post_Sticky'] = "Przyklejony";
$lang['Post_Normal'] = "Normalny";

$lang['Confirm_delete'] = "Czy na pewno chcesz usun±æ ten post?";
$lang['Confirm_delete_poll'] = "Czy na pewno chcesz usun±æ t± ankietê?";

$lang['Flood_Error'] = "Nie mo¿esz wys³aæ nowego postu tak szybko po poprzednim, zaczekaj chwilê i spróbuj ponownie";
$lang['Empty_subject'] = "Musisz wpisaæ temat je¶li wysy³asz nowy w±tek";
$lang['Empty_message'] = "Musisz wpisaæ wiadomo¶æ przed wys³aniem";
$lang['Forum_locked'] = "To forum jest zablokowane, nie mo¿esz pisaæ dodawaæ ani zmieniaæ na nim czegokolwiek";
$lang['Topic_locked'] = "Ten temat jest zablokowany bez mo¿liwo¶ci zmiany postów lub pisania odpowiedzi";
$lang['No_post_id'] = "Musisz wybraæ post do edycji";
$lang['No_topic_id'] = "Musisz wybraæ temat do wys³ania odpowiedzi";
$lang['No_valid_mode'] = "Mo¿esz jedynie pisaæ nowe, odpowiadaæ, zmieniaæ lub cytowaæ wiadomo¶ci, wróæ i spróbuj ponownie";
$lang['No_such_post'] = "Taki post nie istnieje, wróæ i spróbuj ponownie";
$lang['Edit_own_posts'] = "Przepraszamy, ale mo¿esz zmieniaæ jedynie swoje posty";
$lang['Delete_own_posts'] = "Przepraszamy, ale mo¿esz usuwaæ jedynie swoje posty";
$lang['Cannot_delete_replied'] = "Przepraszamy, ale nie mo¿esz usuwaæ postów, które uzyska³y odpowied¶";
$lang['Cannot_delete_poll'] = "Przepraszamy, ale nie mo¿esz usun±æ aktywnej ankiety";
$lang['Empty_poll_title'] = "Musisz wpisaæ tytu³ dla ankiety";
$lang['To_few_poll_options'] = "Musisz wpisaæ przynajmniej dwie opcje ankiety";
$lang['To_many_poll_options'] = "Poda³e¶ zbyt wiele opcji dla ankiety";
$lang['Post_has_no_poll'] = "Ten post nie ma ankiety";

$lang['Add_poll'] = "Dodaj Ankietê";
$lang['Add_poll_explain'] = "Je¿eli nie chcesz dodawaæ ankiety do tego tematu, pozostaw pola puste";
$lang['Poll_question'] = "Pytanie do ankiety";
$lang['Poll_option'] = "Opcja ankiety";
$lang['Add_option'] = "Dodaj opcjê";
$lang['Update'] = "Aktualizuj";
$lang['Delete'] = "Usuñ";
$lang['Poll_for'] = "Czas trwania";
$lang['Days'] = "Dni"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Wpisz 0 lub pozostaw puste dla niekoñcz±cej siê ankiety ]";
$lang['Delete_poll'] = "Usuñ Ankietê";

$lang['Disable_HTML_post'] = "Wy³±cz HTML w tym po¶cie";
$lang['Disable_BBCode_post'] = "Wy³±cz BBCode w tym po¶cie";
$lang['Disable_Smilies_post'] = "Wy³±cz U¶mieszki w tym po¶cie";

$lang['HTML_is_ON'] = "HTML: <u>TAK</u>";
$lang['HTML_is_OFF'] = "HTML: <u>NIE</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s: <u>TAK</u>";
$lang['BBCode_is_OFF'] = "%sBBCode%s: <u>NIE</u>";
$lang['Smilies_are_ON'] = "U¶mieszki: <u>TAK</u>";
$lang['Smilies_are_OFF'] = "U¶mieszki: <u>NIE</u>";

$lang['Attach_signature'] = "Dodaj podpis (mo¿e byæ zmieniony w profilu)";
$lang['Notify'] = "Powiadom mnie gdy kto¶ odpowie";
$lang['Delete_post'] = "Usuñ ten post";

$lang['Stored'] = "Twoja wiadomo¶æ zosta³a zapisana";
$lang['Deleted'] = "Twoja wiadomo¶æ zosta³a usuniêta";
$lang['Poll_delete'] = "Twoja ankieta zosta³a usuniêta";
$lang['Vote_cast'] = "Twój g³os zosta³ zapisany";

$lang['Topic_reply_notification'] = "Powiadomienie o Odpowiedzi";

$lang['bbcode_b_help'] = "Tekst pogrubiony: [b]tekst[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Tekst kursyw±: [i]tekst[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Tekst podkre¶lony: [u]tekst[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Cytat: [quote]tekst[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Poka¿ kod: [code]kod[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Lista: [list]tekst[/list] (alt+l)";
$lang['bbcode_o_help'] = "Lista uporz±dkowana: [list=]tekst[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Wstaw obrazek: [img]http://adres_obrazka[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Wstaw adres: [url]http://adres[/url] or [url=http://adres]Tekst adresu[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Zamknij wszystkie otwarte tagi bbCode";
$lang['bbcode_s_help'] = "Kolor czcionki: [color=red]tekst[/color]  Rada: mo¿esz tak¿e podaæ color=#FF0000";
$lang['bbcode_f_help'] = "Rozmiar czcionki: [size=x-small]ma³y tekst[/size]";

$lang['Emoticons'] = "Ikony Emocji";
$lang['More_emoticons'] = "Wiêcej Ikon";

$lang['Font_color'] = "Kolor";
$lang['color_default'] = "Domy¶lny";
$lang['color_dark_red'] = "Ciemnoczerwony";
$lang['color_red'] = "Czerwony";
$lang['color_orange'] = "Pomarañæzowy";
$lang['color_brown'] = "Br±zowy";
$lang['color_yellow'] = "¯ó³ty";
$lang['color_green'] = "Zielony";
$lang['color_olive'] = "Oliwkowy";
$lang['color_cyan'] = "B³êkitny";
$lang['color_blue'] = "Niebieski";
$lang['color_dark_blue'] = "Ciemnoniebieski";
$lang['color_indigo'] = "Purpurowy";
$lang['color_violet'] = "Fioletowy";
$lang['color_white'] = "Bia³y";
$lang['color_black'] = "Czarny";

$lang['Font_size'] = "Rozmiar";
$lang['font_tiny'] = "Minimalny";
$lang['font_small'] = "Ma³y";
$lang['font_normal'] = "Normalny";
$lang['font_large'] = "Du¿y";
$lang['font_huge'] = "Ogromny";

$lang['Close_Tags'] = "Zamknij Tagi";
$lang['Styles_tip'] = "Rada: Style mog± byæ stosowane szybko do zaznaczonego tekstu";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Prywatne Wiadomo¶ci";

$lang['Login_check_pm'] = "Zaloguj siê, by sprawdziæ wiadomo¶ci";
$lang['New_pms'] = "Masz %d nowych wiadomo¶ci"; // You have 2 new messages
$lang['New_pm'] = "Masz %d now± wiadomo¶æ"; // You have 1 new message
$lang['No_new_pm'] = "Nie masz nowych wiadomo¶ci";
$lang['Unread_pms'] = "Masz %d nieprzeczytanych wiadomo¶ci";
$lang['Unread_pm'] = "Masz %d nieprzeczytan± wiadomo¶æ";
$lang['No_unread_pm'] = "Nie masz nieprzeczytanych wiadomo¶ci";
$lang['You_new_pm'] = "Nowa prywatna wiadomo¶æ czeka na Ciebie w Skrzynce";
$lang['You_new_pms'] = "Nowe prywatne wiadomo¶ci czekaj± na Ciebie w Skrzynce";
$lang['You_no_new_pm'] = "Nie ma dla Ciebie ¿adnych nowych prywatnych wiadomo¶ci";

$lang['Inbox'] = "Skrzynka";
$lang['Outbox'] = "Do Wys³ania";
$lang['Savebox'] = "Zapisane";
$lang['Sentbox'] = "Wys³ane";
$lang['Flag'] = "Flaga";
$lang['Subject'] = "Temat";
$lang['From'] = "Od";
$lang['To'] = "Do";
$lang['Date'] = "Data";
$lang['Mark'] = "Zaznacz";
$lang['Sent'] = "Wys³ana";
$lang['Saved'] = "Zapisana";
$lang['Delete_marked'] = "Usuñ Zaznaczone";
$lang['Delete_all'] = "Usuñ Wszystkie";
$lang['Save_marked'] = "Zapisz Zaznaczone"; 
$lang['Save_message'] = "Zapisz Wiadomo¶æ";
$lang['Delete_message'] = "Usuñ Wiadomo¶æ";

$lang['Display_messages'] = "Wy¶wietl wiadomo¶ci z ostatnich"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Wszystkie Wiadomo¶ci";

$lang['No_messages_folder'] = "Nie masz wiadomo¶ci w tym folderze";

$lang['PM_disabled'] = "Prywatne Wiadomo¶ci zosta³y wy³±czone na tym forum";
$lang['Cannot_send_privmsg'] = "Przepraszam, ale administrator zabroni³ Ci wysy³aæ prywatnych wiadomo¶ci";
$lang['No_to_user'] = "Musisz wpisaæ nazwê u¿ytkownika aby wys³aæ t± wiadomo¶æ";
$lang['No_such_user'] = "Taki u¿ytkownik nie istnieje";

$lang['Message_sent'] = "Twoja wiadomo¶æ zosta³a wys³ana";

$lang['Click_return_inbox'] = "Kliknij %sTutaj%s aby powróciæ do Skrzynki";
$lang['Click_return_index'] = "Kliknij %sTutaj%s aby powróciæ do Strony G³ównej";

$lang['Send_a_new_message'] = "Wy¶lij now± prywatn± wiadomo¶æ";
$lang['Send_a_reply'] = "Odpowiedz na prywatn± wiadomo¶æ";
$lang['Edit_message'] = "Zmieñ prywatn± wiadomo¶æ";

$lang['Notification_subject'] = "Nadesz³a nowa Prywatna Wiadomo¶æ";

$lang['Find_username'] = "Znajd¼ u¿ytkownika";
$lang['Find'] = "Znajd¼";
$lang['No_match'] = "Nie znaleziono pasuj±cych";

$lang['No_post_id'] = "Nie okre¶lono ID postu";
$lang['No_such_folder'] = "Nie istnieje taki folder";
$lang['No_folder'] = "Nie okre¶lono folderu";

$lang['Mark_all'] = "Zaznacz wszystkie";
$lang['Unmark_all'] = "Odznacz wszystkie";

$lang['Confirm_delete_pm'] = "Czy na pewno chcesz usun±æ t± wiadomo¶æ?";
$lang['Confirm_delete_pms'] = "Czy na pewno chcesz usun±æ te wiadomo¶ci?";

$lang['Inbox_size'] = "Wiadomo¶ci w Skrzynce zajmuj± %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Wys³ane wiadomo¶ci zajmuj± %d%%";
$lang['Savebox_size'] = "Zapisane wiadomo¶ci zajmuj± %d%%";

$lang['Click_view_privmsg'] = "Kliknij %sTutaj%s aby odwiedziæ twoj± Skrzynkê";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Przedstawiamy profil :: %s"; // %s is username 
$lang['About_user'] = "Wszystko o %s";

$lang['Preferences'] = "Preferencje";
$lang['Items_required'] = "Pola oznaczone * s± wymagane, chyba ¿e napisano inaczej";
$lang['Registration_info'] = "Infomacje Rejestracji";
$lang['Profile_info'] = "Informacje Profilu";
$lang['Profile_info_warn'] = "Te informacje bêd± widoczne publicznie";
$lang['Avatar_panel'] = "Panel kontrolny emblematów";
$lang['Avatar_gallery'] = "Galeria Emblematów";

$lang['Website'] = "Strona WWW";
$lang['Location'] = "Sk±d";
$lang['Contact'] = "Kontakt z";
$lang['Email_address'] = "Adres email";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Wy¶lij prywatn± wiadomo¶æ";
$lang['Hidden_email'] = "[ Ukryty ]";
$lang['Search_user_posts'] = "Szukaj postów tego u¿ytkownika";
$lang['Interests'] = "Zainteresowania";
$lang['Occupation'] = "Zawód"; 
$lang['Poster_rank'] = "Ranga";

$lang['Total_posts'] = "Postów";
$lang['User_post_pct_stats'] = "%d%% z ca³o¶ci"; // 15% of total
$lang['User_post_day_stats'] = "%.2f postów dziennie"; // 1.5 posts per day
$lang['Search_user_posts'] = "Znajd¼ wszystkie posty %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Przepraszamy, ale ten u¿ytkownik nie istnieje";
$lang['Wrong_Profile'] = "Nie mo¿esz zmieniaæ cudzego profilu.";
$lang['Sorry_banned_or_taken_email'] = "Przepraszamy, ale adres email, który poda³e¶ zosta³ zbanowany, jest ju¿ zarejestrowany lub nieprawid³owy. Spróbuj wpisaæ inny adres, a w przypadku ponownego wyst±pienia problemów skontaktuj siê z administratorem";
$lang['Only_one_avatar'] = "Mo¿na okre¶liæ tylko jeden typ emblematu";
$lang['File_no_data'] = "Plik pod podanym adresem nie zawiera ¿adnych danych";
$lang['No_connection_URL'] = "Nie mo¿na by³o po³±czyæ siê z podanym przez Ciebie adresem";
$lang['Incomplete_URL'] = "Wpisany adres jest niekompletny";
$lang['Wrong_remote_avatar_format'] = "Podany adres emblematu nie jest prawid³owy";
$lang['No_send_account_inactive'] = "Przepraszamy, ale Twoje has³o nie mo¿e byæ odzyskane gdy¿ Twoje konto jest obecnie nieaktywne. Skontaktuj siê z administratorem aby uzyskaæ wiêcej informacji";

$lang['Always_smile'] = "Zawsze w³±czaj U¶mieszki";
$lang['Always_html'] = "Zawsze w³±czaj HTML";
$lang['Always_bbcode'] = "Zawsze w³±czaj BBCode";
$lang['Always_add_sig'] = "Zawsze dodawaj mój podpis";
$lang['Always_notify'] = "Zawsze powiadamiaj o odpowiedziach";
$lang['Always_notify_explain'] = "Wysy³a email gdy kto¶ odpowie w temacie, w którym napisa³e¶ wiadomo¶æ. Mo¿esz to zmieniæ przy ka¿dej napisanej wiadomo¶ci";

$lang['Board_style'] = "Styl Forum";
$lang['Board_lang'] = "Jêzyk Forum";
$lang['No_themes'] = "Brak Szablonów w bazie danych";
$lang['Timezone'] = "Strefa Czasowa";
$lang['Date_format'] = "Format Daty";
$lang['Date_format_explain'] = "Sk³adnia jest identyczna z funkcj± <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> w PHP";
$lang['Signature'] = "Podpis";
$lang['Signature_explain'] = "To jest blok tekstu, który mo¿e byæ dodawany do Twoich postów. Ma limit %d znaków";
$lang['Public_view_email'] = "Zawsze pokazuj mój Adres Email";

$lang['Current_password'] = "Obecne Has³o";
$lang['New_password'] = "Nowe Has³o";
$lang['Confirm_password'] = "Potwierd¼ Has³o";
$lang['password_if_changed'] = "Musisz podawaæ has³o tylko je¶li chcesz je zmieniæ";
$lang['password_confirm_if_changed'] = "Musisz potwierdzaæ has³o tylko je¶li chcesz je zmieniæ";

$lang['Avatar'] = "Emblemat";
$lang['Avatar_explain'] = "Wy¶wietla ma³y obrazek pod informacjami o Tobie przy ka¿dym po¶cie. Tylko jeden obrazek mo¿e byæ wy¶wietlany, jego szeroko¶æ nie mo¿e byæ wiêksza ni¿ %d pikseli, wysoko¶æ wiêksza ni¿ %d pikseli, a rozmiar wiêkszy ni¿ %dkB.";
$lang['Upload_Avatar_file'] = "Wy¶lij Emblemat z twojego komputera";
$lang['Upload_Avatar_URL'] = "Wy¶lij Emblemat z adresu";
$lang['Upload_Avatar_URL_explain'] = "Wpisz adres, pod którym zlokalizowany jest Twój Emblemat, zostanie on skopiowany na tê stronê.";
$lang['Pick_local_Avatar'] = "Wybierz Emblemat z galerii";
$lang['Link_remote_Avatar'] = "Odno¶nik do zewnêtrznego Emblematu";
$lang['Link_remote_Avatar_explain'] = "Wpisz adres Emblematu, który chcesz wykorzystaæ.";
$lang['Avatar_URL'] = "Adres Obrazu Emblematu";
$lang['Select_from_gallery'] = "Wybierz Emblemat z galerii";
$lang['View_avatar_gallery'] = "Poka¿ galeriê";

$lang['Select_avatar'] = "Wybierz Emblemat";
$lang['Return_profile'] = "Anuluj Wybór";
$lang['Select_category'] = "Wybierz Kategoriê";

$lang['Delete_Image'] = "Usuñ Obraz";
$lang['Current_Image'] = "Obecny Obraz";

$lang['Notify_on_privmsg'] = "Powiadom o Prywatnej Wiadomo¶ci";
$lang['Popup_on_privmsg'] = "Otwórz okno przy nadej¶ciu Wiadomo¶ci"; 
$lang['Popup_on_privmsg_explain'] = "Niektóre szablony mog± otwieraæ nowe okno aby poinformowaæ o nadej¶ciu nowej Prywatnej Wiadomo¶ci"; 
$lang['Hide_user'] = "Ukryj moj± obecno¶æ na forum";

$lang['Profile_updated'] = "Twój profil zosta³ zaktualizowany";
$lang['Profile_updated_inactive'] = "Twój profil zosta³ zmieniony jednak¿e zmodyfikowa³e¶ istotne dane i Twoje konto zosta³o deaktywowane. Otrzymasz wiadomo¶æ email z instrukcjami jak reaktywowaæ Twoje konto, lub bêdziesz musia³ poczekaæ a¿ administrator dokona reaktywacji";

$lang['Password_mismatch'] = "Wpisane has³a nie pasuj± do siebie";
$lang['Current_password_mismatch'] = "Wpisane przez Ciebie has³o nie pasuje do zapisanego w bazie danych";
$lang['Invalid_username'] = "Podana nazwa u¿ytkownika jest zajêta lub zakazana";
$lang['Signature_too_long'] = "Twój podpis jest za d³ugi";
$lang['Fields_empty'] = "Musisz wype³niæ wymagane pola";
$lang['Avatar_filetype'] = "Emblemat musi byæ typu .jpg, .gif lub .png";
$lang['Avatar_filesize'] = "Rozmiar emblematu musi byæ wiêkszy ni¿ 0 kB i mniejszy ni¿"; // followed by xx kB, xx being the size
$lang['Avatar_imagesize'] = "Emblemat musi byæ mniejszy ni¿ " . $board_config['avatar_max_width'] . " pikseli szeroko¶ci i " . $board_config['avatar_max_height'] . " pikseli wysoko¶ci"; 

$lang['Welcome_subject'] = "Witamy na Forum " . $board_config['sitename'];
$lang['New_account_subject'] = "Nowe Konto";
$lang['Account_activated_subject'] = "Konto Aktywowane";

$lang['Account_added'] = "Dziêkujemy za rejestracjê, Twoje konto zosta³o utworzone. Mo¿esz zalogowaæ siê korzystaj±c z podanych wcze¶niej nazwy u¿ytkownika i has³a.";
$lang['Account_inactive'] = "Twoje konto zosta³o utworzone. To Forum jednak¿e wymaga aktywacji kont, poprzez podanie klucza aktywuj±cego, który otrzymasz w specjalnej wiadomo¶ci email. W niej te¿ znajdziesz dalsze instrukcje postêpowania.";
$lang['Account_inactive_admin'] = "Twoje konto zosta³o utworzone. To Forum jednak¿e wymaga aktywacji kont przez administratora. Zosta³ ju¿ do niego wys³any email powiadamiaj±cy o utworzeniu nowego konta i wkrótce zostanie ono aktywowane";
$lang['Account_active'] = "Twoje konto zosta³o niniejszym aktywowane. Dziêkujemy za rejestracjê";
$lang['Account_active_admin'] = "Konto zosta³o aktywowane";
$lang['Reactivate'] = "Reaktywuj soje konto!";
$lang['COPPA'] = "Twoje konto zosta³o utworzone ale musi jeszcze zostaæ zaakceptowane. Otrzymasz specjalny email z instrukcjami.";

$lang['Registration'] = "Warunki Rejestracji";
$lang['Reg_agreement'] = "Administratorzy i moderatorzy podejm± starania maj±ce na celu usuwanie wszelkich uznawanych za obra¼liwe materia³ów jak najszybciej, jednak¿e nie jest mo¿liwe przeczytanie ka¿dej wiadomo¶ci. Zgadzasz siê wiêc, ¿e zawarto¶æ ka¿dego postu na tym forum wyra¿a pogl±dy i opinie jego autora a nie administratorów, moderatorów czy webmasterów (poza wiadomo¶ciami pisanymi przez nich) i nie ponosz± oni za te tre¶ci odpowiedzialno¶ci.<br /><br />Zgadzasz siê nie pisaæ ¿adnych obra¼liwych, obscenicznych, wulgarnych, oszczerczych, nienawistnych, zawieraj±cych gro¼by i innych materia³ów, które mog± byæ sprzeczne z prawem. Z³amanie tej zasady mo¿e byæ przyczyn± natychmiastowego i trwa³ego usuniêcia z listy u¿ytkowników (wraz z powiadomieniem odpowiednich w³adz). Aby wspomóc te dzia³ania rejestrowane s± adresy IP autorów. Przyjmujesz do wiadomo¶ci, ¿e webmaster, administrator i moderatorzy tego forum maj± prawo do do usuwania, zmiany lub zamykania ka¿dego w±tku w ka¿dej chwili je¶li zajdzie taka potrzeba. Jako u¿ytkownik zgadzasz siê, ¿e wszystkie informacje, które wpiszesz bêd± przechowywane w bazie danych. Informacje te nie bêd± podawane bez twojej zgody ¿adnym osobom ani podmiotom trzecim, jednak¿e webmaster, administrator i moderatorzy nie bêd± obarczeni odpowiedzialno¶ci± za w³amania hackerskie prowadz±ce do pozyskania tych danych.<br /><br />Skrypt tego forum wykorzystuje cookies do przechowywania informacji na twoim komputerze. Te cookies nie zawieraj± ¿adnych informacji, które poda³e¶ i s³u¿± jedynie u³atwieniu korzystania z forum. Adres email jest wykorzystywany jedynie dla potwierdzenia podanych informacji oraz has³a (i dla przes³ania nowego has³a, gdyby¶ zapomnia³ stare).<br /><br />Klikaj±c odno¶nik Rejestracja na dole zgadzasz siê na te warunki.";

$lang['Agree_under_13'] = "Zgadzam Siê na te warunki i mam <b>poni¿ej</b> 13 lat";
$lang['Agree_over_13'] = "Zgadzam Siê na te warunki i mam <b>powy¿ej</b> 13 lat";
$lang['Agree_not'] = "Nie zgadzam siê na te warunki";

$lang['Wrong_activation'] = "Podany przez ciebie klucz aktywacyjny nie pasuje do ¿adnego w bazie danych";
$lang['Send_password'] = "Wy¶lij mi nowe has³o";
$lang['Password_updated'] = "Nowe has³o zosta³o utworzone. Otrzymasz email z informacjami jak je aktywowaæ";
$lang['No_email_match'] = "Adres email, który poda³e¶ nie pasuje do zapisanego razem z baz± danych";
$lang['New_password_activation'] = "Aktywacja nowego has³a";
$lang['Password_activated'] = "Twoje konto zosta³o reaktywowane. Aby siê zalogowaæ u¿yj has³a podanego w email'u, który otrzyma³e¶";

$lang['Send_email_msg'] = "Wy¶lij mi email";
$lang['No_user_specified'] = "Nie okre¶lono ¿adnego u¿ytkownika";
$lang['User_prevent_email'] = "Ten u¿ytkownik nie ¿yczy sobie otrzymywaæ email'i. Spróbuj wys³aæ mu prywatn± wiadomo¶æ.";
$lang['User_not_exist'] = "Ten u¿ytkownik nie istnieje";
$lang['CC_email'] = "Wy¶lij kopiê tego email'a do siebie";
$lang['Email_message_desc'] = "Wiadomo¶æ zostanie wys³ana jako zwyk³y tekst, nie wstawiaj znaczników HTML ani BBCode. Jako adres zwrotny zostanie wstawiony twój adres email.";
$lang['Flood_email_limit'] = "Nie mo¿esz teraz wys³aæ kolejnego email'a. Spróbuj ponownie za jaki¶ czas.";
$lang['Recipient'] = "Odbiorca";
$lang['Email_sent'] = "Email zosta³ wys³any";
$lang['Send_email'] = "Wy¶lij email";
$lang['Empty_subject_email'] = "Musisz okre¶liæ temat dla email'a";
$lang['Empty_message_email'] = "Musisz wpisaæ wiadomo¶æ do wys³ania";


//
// Memberslist
//
$lang['Select_sort_method'] = "Metoda sortowania";
$lang['Sort'] = "Sortuj";
$lang['Sort_Top_Ten'] = "10 Najaktywniejszych";
$lang['Sort_Joined'] = "Data przy³±czenia";
$lang['Sort_Username'] = "Nazwa U¿ytkownika";
$lang['Sort_Location'] = "Sk±d";
$lang['Sort_Posts'] = "Wszystkich Postów";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Strona";
$lang['Sort_Ascending'] = "Rosn±co";
$lang['Sort_Descending'] = "Malej±co";
$lang['Order'] = "Porz±dek";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Panel Kontrolny Grupy";
$lang['Group_member_details'] = "Szczegó³y Cz³onkostwa w Grupie";
$lang['Group_member_join'] = "Do³±cz do Grupy";

$lang['Group_Information'] = "Informacje o Grupie";
$lang['Group_name'] = "Nazwa Grupy";
$lang['Group_description'] = "Opis Grupy";
$lang['Group_membership'] = "Twoje cz³onkostwo";
$lang['Group_Members'] = "Cz³onkowie Grupy";
$lang['Group_Moderator'] = "Moderator Grupy";
$lang['Pending_members'] = "Cz³onkowie Oczekuj±cy";

$lang['Group_type'] = "Typ grupy";
$lang['Group_open'] = "Grupa otwarta";
$lang['Group_closed'] = "Grupa zamkniêta";
$lang['Group_hidden'] = "Grupa ukryta";

$lang['Current_memberships'] = "Obecni cz³onkowie";
$lang['Non_member_groups'] = "Grupy bez cz³onkostw";
$lang['Memberships_pending'] = "Oczekuj±ce Cz³onkostwa";

$lang['No_groups_exist'] = "¯adna Grupa nie Istnieje";
$lang['Group_not_exist'] = "Taka grupa nie istnieje";

$lang['Join_group'] = "Do³±cz";
$lang['No_group_members'] = "Ta grupa nie ma cz³onków";
$lang['Group_hidden_members'] = "Tak grupa jest ukryta, nie mo¿esz zobaczyæ listy jej cz³onków";
$lang['No_pending_group_members'] = "Ta grupa nie ma cz³onków oczekuj±cych";
$lang["Group_joined"] = "Zosta³e¶ do³±czony do tej grupy<br />Zostaniesz powiadomionu kiedy Twoje cz³onkostwo zostanie zaakceptowane przez moderatora";
$lang['Group_request'] = "Twoja pro¶ba o przy³±czenie do grupy zosta³a wys³ana";
$lang['Group_approved'] = "Twoja pro¶ba zosta³a zaakceptowana";
$lang['Group_added'] = "Zosta³e¶ dodany do tej grupy"; 
$lang['Already_member_group'] = "Jeste¶ ju¿ cz³onkiem tej grupy";
$lang['User_is_member_group'] = "U¿ytkownik jest ju¿ cz³onkiem tej grupy";
$lang['Group_type_updated'] = "Zaktualizowano typ grupy";

$lang['Could_not_add_user'] = "Wybrany u¿ytkownik nie istnieje";
$lang['Could_not_anon_user'] = "Anonimowy nie mo¿e byæ cz³onkiem grupy";

$lang['Confirm_unsub'] = "Czy na pewno chcesz opu¶ciæ t± grupê?";
$lang['Confirm_unsub_pending'] = "Twoje cz³onkostwo w tej grupie nie zosta³o jeszcze zaakceptowane, czy na pewno chcesz je zakoñczyæ?";

$lang['Unsub_success'] = "Przesta³e¶ byæ cz³onkiem tej grupy.";

$lang['Approve_selected'] = "Zaakceptuj Wybrane";
$lang['Deny_selected'] = "Odrzuæ Wybrane";
$lang['Not_logged_in'] = "Musisz siê zalogowaæ by do³±czyæ do grupy.";
$lang['Remove_selected'] = "Usuñ Wybrane";
$lang['Add_member'] = "Dodaj Cz³onka";
$lang['Not_group_moderator'] = "Nie jeste¶ moderatorem tej grupy i nie mo¿esz wykonaæ tego dzia³ania.";

$lang['Login_to_join'] = "Zaloguj siê aby do³±czyæ do grupy lub zarz±dzaæ jej cz³onkostwem";
$lang['This_open_group'] = "To jest grupa otwarta, kliknij aby poprosiæ o cz³onkostwo";
$lang['This_closed_group'] = "To jest grupa zamkniêta, nowi cz³onkowie nie bêd± przyjmowani";
$lang['This_hidden_group'] = "To jest grupa ukryta, automatyczne dodawanie cz³onków nie jest dozwolone";
$lang['Member_this_group'] = "Jeste¶ cz³onkiem tej grupy";
$lang['Pending_this_group'] = "Twoje cz³onkowstwo w tej grupie czeka na akceptacjê";
$lang['Are_group_moderator'] = "Jeste¶ moderatorem tej grupy";
$lang['None'] = "Brak";

$lang['Subscribe'] = "Do³±cz";
$lang['Unsubscribe'] = "Opu¶æ";
$lang['View_Information'] = "Zobacz Informacje";


//
// Search
//
$lang['Search_query'] = "Poszukiwane Zapytanie";
$lang['Search_options'] = "Opcje Wyszukiwania";

$lang['Search_keywords'] = "Szukaj S³ów Kluczowych";
$lang['Search_keywords_explain'] = "Mo¿esz u¿ywaæ <u>AND</u> aby okre¶laæ, które s³owa musz± znale¼æ siê w wynikach, <u>OR</u> dla tych, które mog± siê tam znale¶æ i <u>NOT</u> dla tych, które nie mog± wyst±piæ. Znak * zastêpuje dowolny ci±g znaków. Aby szukaæ zwrotu umie¶æ go wewn±trz &quot;&quot;";
$lang['Search_author'] = "Szukaj Autora";
$lang['Search_author_explain'] = "U¿yj * jako zamiennika dowolnego ci±gu znaków";

$lang['Search_for_any'] = "Szukaj któregokolwiek s³owa lub wyra¿enia jak je wpisano";
$lang['Search_for_all'] = "Szukaj wszystkich s³ów";
$lang['Search_title_msg'] = "Przeszukaj tytu³ i tekst wiadomo¶ci"; 
$lang['Search_msg_only'] = "Przeszukaj tylko tekst wiadomo¶ci"; 

$lang['Return_first'] = "Poka¿ pierwsze"; // followed by xxx characters in a select box
$lang['characters_posts'] = "znaków z postu";

$lang['Search_previous'] = "Przeszukaj ostanie"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Sortuj wed³ug";
$lang['Sort_Time'] = "Czas Wys³ania";
$lang['Sort_Post_Subject'] = "Temat Postu";
$lang['Sort_Topic_Title'] = "Tytu³ Tematu";
$lang['Sort_Author'] = "Autor";
$lang['Sort_Forum'] = "Forum";

$lang['Display_results'] = "Poka¿ wyniki jako";
$lang['All_available'] = "Wszystkie dostêpne";
$lang['No_searchable_forums'] = "Nie masz uprawnieñ do przeszukiwania któegokolwiek forum na tej stronie";

$lang['No_search_match'] = "Nie znaleziono tematów ani postów pasuj±cych do Twoich kryteriów";
$lang['Found_search_match'] = "Znaleziono %d wynik"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Znaleziono %d wyników"; // eg. Search found 24 matches

$lang['Close_window'] = "Zamknij Okno";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Przepraszamy, ale tylko %s mog± pisaæ og³oszenia na tym forum";
$lang['Sorry_auth_sticky'] = "Przepraszamy, ale tylko %s mog± pisaæ tematy przyklejone na tym forum"; 
$lang['Sorry_auth_read'] = "Przepraszamy, ale tylko %s mog± czytaæ tematy na tym forum"; 
$lang['Sorry_auth_post'] = "Przepraszamy, ale tylko %s mog± pisaæ tematy na tym forum"; 
$lang['Sorry_auth_reply'] = "Przepraszamy, ale tylko %s mog± odpowiadaæ na posty na tym forum"; 
$lang['Sorry_auth_edit'] = "Przepraszamy, ale tylko %s mog± zmieniaæ posty na tym forum"; 
$lang['Sorry_auth_delete'] = "Przepraszamy, ale tylko %s mog± usuwaæ posty na tym forum"; 
$lang['Sorry_auth_vote'] = "Przepraszamy, ale tylko %s mog± g³osowaæ w ankietach na tym forum"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>anonimowi u¿ytkownicy</b>";
$lang['Auth_Registered_Users'] = "<b>zarejestrowani u¿ytkownicy</b>";
$lang['Auth_Users_granted_access'] = "<b>u¿ytkownicy z uprawnieniami dostêpu</b>";
$lang['Auth_Moderators'] = "<b>moderatorzy</b>";
$lang['Auth_Administrators'] = "<b>administratorzy</b>";

$lang['Not_Moderator'] = "Nie jeste¶ moderatorem tego forum";
$lang['Not_Authorised'] = "Nieautoryzowany";

$lang['You_been_banned'] = "Zosta³e¶ wyrzucony z tego forum<br />Skontaktuj siê z webmasterem lub administratorem forum w celu uzyskania dalszych informacji";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Na Forum jest 0 Zarejestrowanych i "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Na forum jest %d Zarejestrowanych i ";
$lang['Reg_user_online'] = "Na forum jest %d Zarejestrowany u¿ytkownik i "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 Ukrytych u¿ytkowników"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d Ukrytych u¿ytkowników";
$lang['Hidden_user_online'] = "%d Ukryty u¿ytkownik"; // 6 Hidden users online
$lang['Guest_users_zero_online'] = "Na Forum jest 0 Go¶ci"; // There are 10 Guest users online
$lang['Guest_users_online'] = "Na Forum jest %d Go¶ci";
$lang['Guest_user_online'] = "Na Forum jest %d Go¶æ";
$lang['No_users_browsing'] = "Obecnie nie ma ¿adnych u¿ytkowników na tym forum";

$lang['Online_explain'] = "Te dane pokazuj± u¿ytkowników aktywnych przez ostatnie 5 minut";

$lang['Forum_Location'] = "Lokalizacja";
$lang['Last_updated'] = "Ostatnia Aktualizacja";

$lang['Forum_index'] = "Strona G³ówna";
$lang['Logging_on'] = "Loguje siê";
$lang['Posting_message'] = "Pisze wiadomo¶æ";
$lang['Searching_forums'] = "Przeszukuje fora";
$lang['Viewing_profile'] = "Ogl±da profil";
$lang['Viewing_online'] = "Przegl±da listê obecnych na forum";
$lang['Viewing_member_list'] = "Ogl±da listê u¿ytkowników";
$lang['Viewing_priv_msgs'] = "Ogl±da Prywatne Wiadomo¶ci";
$lang['Viewing_FAQ'] = "Ogl±da FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Panel Kontrolny Moderacji";
$lang['Mod_CP_explain'] = "Korzystaj±c z poni¿szego formularza mo¿esz przeprowadziæ zbiorow± moderacjê na tym forum. Mo¿esz blokowaæ, odblokowywaæ, przenosiæ i usuwaæ dowoln± ilo¶æ tematów. Je¿eli to forum jest ustawione jako prywatne mo¿esz tak¿e czê¶ciowo decydowaæ, którzy u¿ytkownicy mog± mieæ do niego dostêp.";

$lang['Select'] = "Wybierz";
$lang['Delete'] = "Usuñ";
$lang['Move'] = "Przenie¶";
$lang['Lock'] = "Zablokuj";
$lang['Unlock'] = "Odblokuj";

$lang['Topics_Removed'] = "Wybrane tematy zosta³y usuniête z bazy danych.";
$lang['Topics_Locked'] = "Wybrane tematy zosta³y zablokowane";
$lang['Topics_Moved'] = "Wybrane tematy zosta³y przeniesione";
$lang['Topics_Unlocked'] = "Wybrane tematy zosta³y odblokowane";
$lang['No_Topics_Moved'] = "Nie przeniesiono ¿adnego tematu";

$lang['Confirm_delete_topic'] = "Czy na pewno chcesz usun±æ wybrane tematy?";
$lang['Confirm_lock_topic'] = "Czy na pewno chcesz zablokowaæ wybrane tematy?";
$lang['Confirm_unlock_topic'] = "Czy na pewno chcesz odblokowaæ wybrane tematy?";
$lang['Confirm_move_topic'] = "Czy na pewno chcesz przenie¶æ wybrane tematy?";

$lang['Move_to_forum'] = "Przenie¶ do forum";
$lang['Leave_shadow_topic'] = "Pozostaw odno¶nik na starym forum.";

$lang['Split_Topic'] = "Panel Kontrolny Dzielenia Tematów";
$lang['Split_Topic_explain'] = "U¿ywaj±c poni¿szego formularza mo¿esz podzieliæ temat na dwa, wybieraj±c posty, które maj± zostaæ wydzielone lub dziel±c od jednego zaznaczonego postu";
$lang['Split_title'] = "Tytu³ nowego tematu";
$lang['Split_forum'] = "Forum dla nowego tematu";
$lang['Split_posts'] = "Wydziel wybrane posty";
$lang['Split_after'] = "Wydziel od wybranego postu";
$lang['Topic_split'] = "Wybrany temat zosta³ podzielony";

$lang['Too_many_error'] = "Wybra³e¶ zbyt wiele postów. Mo¿esz wybraæ tylko jeden, od którego chcesz dzieliæ temat!";

$lang['None_selected'] = "Nie wybra³e¶ ¿adnych tematów do wykonania tej operacji. Proszê wróæ i wybierz przynajmniej jeden.";
$lang['New_forum'] = "Nowe forum";

$lang['This_posts_IP'] = "IP dla tego postu";
$lang['Other_IP_this_user'] = "Inne IP, z których pisa³ ten u¿ytkownik";
$lang['Users_this_IP'] = "U¿ytkownicy pisz±cy z tego IP";
$lang['IP_info'] = "Informacja o IP";
$lang['Lookup_IP'] = "Szukaj IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Wszystkie czasy w strefie %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Godzin";
$lang['-11'] = "GMT - 11 Godzin";
$lang['-10'] = "HST (Hawaje)";
$lang['-9'] = "GMT - 9 Godzin";
$lang['-8'] = "PST (U.S./Kanada)";
$lang['-7'] = "MST (U.S./Kanada)";
$lang['-6'] = "CST (U.S./Kanada)";
$lang['-5'] = "EST (U.S./Kanada)";
$lang['-4'] = "GMT - 4 Godziny";
$lang['-3.5'] = "GMT - 3.5 Godziny";
$lang['-3'] = "GMT - 3 Godziny";
$lang['-2'] = "¦rodkowy Atlantyk";
$lang['-1'] = "GMT - 1 Godzina";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europa)";
$lang['2'] = "EET (Europa)";
$lang['3'] = "GMT + 3 Godziny";
$lang['3.5'] = "GMT + 3.5 Godziny";
$lang['4'] = "GMT + 4 Godziny";
$lang['4.5'] = "GMT + 4.5 Godziny";
$lang['5'] = "GMT + 5 Godzin";
$lang['5.5'] = "GMT + 5.5 Godzin";
$lang['6'] = "GMT + 6 Godzin";
$lang['7'] = "GMT + 7 Godzin";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9 Godzin";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11 Godzin";
$lang['12'] = "GMT + 12 Godzin";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 hours) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 hours) Wyspa Midway, Samoa";
$lang['tz']['10'] = "(GMT -10:00 hours) Hawaje";
$lang['tz']['-9'] = "(GMT -9:00 hours) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 hours) Pacific Time (US &amp; Kanada)";
$lang['tz']['-7'] = "(GMT -7:00 hours) Mountain Time (US &amp; Kanada)";
$lang['tz']['-6'] = "(GMT -6:00 hours) Central Time (US &amp; Kanada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 hours) Eastern Time (US &amp; Kanada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 hours) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 hours) Nowa Funflandia";
$lang['tz']['-3'] = "(GMT -3:00 hours) Brazylia, Buenos Aires, Georgetown, Falklandy";
$lang['tz']['-2'] = "(GMT -2:00 hours) ¦r-Atlantyk, Wyspa Ascension, ¦w. Helena";
$lang['tz']['-1'] = "(GMT -1:00 hours) Azory, Wyspy Cape Verde";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edynburg, Londyn, Lisbona, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 hours) Berlin, Bruksela, Kopenhaga, Madryd, Pary¿, Rzym";
$lang['tz']['2'] = "(GMT +2:00 hours) Kaliningrad, Po³. Afryka";
$lang['tz']['3'] = "(GMT +3:00 hours) Bagdad, Riyadh, Moskwa, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 hours) Teheran";
$lang['tz']['4'] = "(GMT +4:00 hours) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 hours) Kabul";
$lang['tz']['5'] = "(GMT +5:00 hours) Ekaterinburg, Islamabad, Karaczi, Taszkent";
$lang['tz']['5.5'] = "(GMT +5:30 hours) Bombaj, Kalkuta, Madras, Nowe Delhi";
$lang['tz']['6'] = "(GMT +6:00 hours) Almaty, Colombo, Dhaka";
$lang['tz']['6.5'] = "(GMT +6:30 hours) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 hours) Bangkok, Hanoi, D¿akarta";
$lang['tz']['8'] = "(GMT +8:00 hours) Pekin, Hong Kong, Perth, Singapur, Taipei";
$lang['tz']['9'] = "(GMT +9:00 hours) Osaka, Sapporo, Seoul, Tokyo, Jakuck";
$lang['tz']['9.5'] = "(GMT +9:30 hours) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 hours) Melbourne, Papua Nowa Gwinea, Sydney, W³adywostok";
$lang['tz']['11'] = "(GMT +11:00 hours) Magadan, Nowa Kaledonia, Wyspy Salomona";
$lang['tz']['12'] = "(GMT +12:00 hours) Auckland, Wellington, Fid¿i, Wyspy Marshalla";

$lang['days_long'][0] = "Niedziela";
$lang['days_long'][1] = "Poniedzia³ek";
$lang['days_long'][2] = "Wtorek";
$lang['days_long'][3] = "¦roda";
$lang['days_long'][4] = "Czwartek";
$lang['days_long'][5] = "Pi±tek";
$lang['days_long'][6] = "Sobota";

$lang['days_short'][0] = "Nie";
$lang['days_short'][1] = "Pon";
$lang['days_short'][2] = "Wto";
$lang['days_short'][3] = "Sro";
$lang['days_short'][4] = "Czw";
$lang['days_short'][5] = "Pi±";
$lang['days_short'][6] = "Sob";

$lang['months_long'][0] = "Styczeñ";
$lang['months_long'][1] = "Luty";
$lang['months_long'][2] = "Marzec";
$lang['months_long'][3] = "Kwiecieñ";
$lang['months_long'][4] = "Maj";
$lang['months_long'][5] = "Czerwiec";
$lang['months_long'][6] = "Lipiec";
$lang['months_long'][7] = "Sierpieñ";
$lang['months_long'][8] = "Wrzesieñ";
$lang['months_long'][9] = "Pa¼dziernik";
$lang['months_long'][10] = "Listopad";
$lang['months_long'][11] = "Grudzieñ";

$lang['months_short'][0] = "Sty";
$lang['months_short'][1] = "Lut";
$lang['months_short'][2] = "Mar";
$lang['months_short'][3] = "Kwi";
$lang['months_short'][4] = "Maj";
$lang['months_short'][5] = "Cze";
$lang['months_short'][6] = "Lip";
$lang['months_short'][7] = "Sie";
$lang['months_short'][8] = "Wrz";
$lang['months_short'][9] = "Pa¼";
$lang['months_short'][10] = "Lis";
$lang['months_short'][11] = "Gru";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Informacja";
$lang['Critical_Information'] = "Istotna Informacja";

$lang['General_Error'] = "B³±d Ogólny";
$lang['Critical_Error'] = "B³±d Krytyczny";
$lang['An_error_occured'] = "Wyst±pi³ B³±d";
$lang['A_critical_error'] = "Wyst±pi³ Krytyczny B³±d";

//
// That's all Folks!
// -------------------------------------------------

?>