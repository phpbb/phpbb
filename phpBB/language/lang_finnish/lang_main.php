<?php
/***************************************************************************
 *                            lang_main.php [Finnish]
 *                              -------------------
 *     begin                : Tue Dec 18 2001
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
//	Translation produced by Jorma Aaltonen (bullitt)
//	http://www.pitro.com/
//


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
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Foorumi";
$lang['Category'] = "Kategoria";
$lang['Topic'] = "Aihe";
$lang['Topics'] = "Aiheet";
$lang['Replies'] = "Vastaukset";
$lang['Views'] = "Luettu";
$lang['Post'] = "Lähetä";
$lang['Posts'] = "Viestejä";
$lang['Posted'] = "Lähetetty";
$lang['Username'] = "Käyttäjätunnus";
$lang['Password'] = "Salasana";
$lang['Email'] = "Sähköposti";
$lang['Poster'] = "Lähettäjä";
$lang['Author'] = "Kirjoittaja";
$lang['Time'] = "Aika";
$lang['Hours'] = "Tunnit";
$lang['Message'] = "Viesti";

$lang['1_Day'] = "1 päivä";
$lang['7_Days'] = "7 päivää";
$lang['2_Weeks'] = "2 viikkoa";
$lang['1_Month'] = "1 kuukausi";
$lang['3_Months'] = "3 kuukautta";
$lang['6_Months'] = "6 kuukautta";
$lang['1_Year'] = "1 vuosi";

$lang['Go'] = "Siirry";
$lang['Jump_to'] = "Siirry";
$lang['Submit'] = "Lähetä";
$lang['Reset'] = "Resetoi";
$lang['Cancel'] = "Peruuta";
$lang['Preview'] = "Esikatselu";
$lang['Confirm'] = "Vahvista";
$lang['Spellcheck'] = "Oikeinkirjoitus";
$lang['Yes'] = "Kyllä";
$lang['No'] = "Ei";
$lang['Enabled'] = "Aktivoitu";
$lang['Disabled'] = "Ei käytössä";
$lang['Error'] = "Virhe";

$lang['Next'] = "Seuraava";
$lang['Previous'] = "Edellinen";
$lang['Goto_page'] = "Siirry sivulle";
$lang['Joined'] = "Liittynyt";
$lang['IP_Address'] = "IP osoite";

$lang['Select_forum'] = "Valitse foorumi";
$lang['View_latest_post'] = "Katso viimeisin viesti";
$lang['View_newest_post'] = "Katso uusin viesti";
$lang['Page_of'] = "Sivu <b>%d</b> Yht. <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "ICQ Numero";
$lang['AIM'] = "AIM Osoite";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Foorumin päävalikko";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Lähetä uusi viesti";
$lang['Reply_to_topic'] = "Vastaa viestiin";
$lang['Reply_with_quote'] = "Vastaa lainaamalla viestiä";

$lang['Click_return_topic'] = "Klikkaa %stästä%s palataksesi viestiin"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Klikkaa %stästä%s yrittääksesi uudelleen";
$lang['Click_return_forum'] = "Klikkaa %stästä%s palataksesi foorumiin";
$lang['Click_view_message'] = "Klikkaa %stästä%s nähdäksesi viestisi";
$lang['Click_return_modcp'] = "Klikkaa %stästä%s palataksesi Moderaattorin ohjauspaneeliin";
$lang['Click_return_group'] = "Klikkaa %stästä%s palataksesi ryhmän tietoihin";

$lang['Admin_panel'] = "Siirry Hallintapaneeliin";

$lang['Board_disable'] = "Valitettavasti tämä sivusto on juuri nyt pois käytöstä, yritä myöhemmin uudestaan";


//
// Global Header strings
//
$lang['Registered_users'] = "Rekisteröityjä käyttäjiä:";
$lang['Online_users_zero_total'] = "Yhteensä <b>0</b> käyttäjää tällä hetkellä :: ";
$lang['Online_users_total'] = "Yhteensä <b>%d</b> käyttäjää tällä hetkellä :: ";
$lang['Online_user_total'] = "Yhteensä <b>%d</b> käyttäjä tällä hetkellä :: ";
$lang['Reg_users_zero_total'] = "0 Rekisteröityä, ";
$lang['Reg_users_total'] = "%d Rekisteröityjä, ";
$lang['Reg_user_total'] = "%d Rekisteröity, ";
$lang['Hidden_users_zero_total'] = "0 Piilotettua ja ";
$lang['Hidden_users_total'] = "%d Piilotettuja ja ";
$lang['Hidden_user_total'] = "%d Piilotettu ja ";
$lang['Guest_users_zero_total'] = "0 Vierasta";
$lang['Guest_users_total'] = "%d Vieraita";
$lang['Guest_user_total'] = "%d Vieras";

$lang['Admin_online_color'] = "%sYlläpitäjä%s";
$lang['Mod_online_color'] = "%sModeraattori%s";

$lang['You_last_visit'] = "Edellinen käyntisi oli %s"; // %s replaced by date/time
$lang['Current_time'] = "Kellonaika on nyt %s"; // %s replaced by time

$lang['Search_new'] = "Katso viime käyntisi jälkeen tulleet uudet viestit";
$lang['Search_your_posts'] = "Katso omat viestisi";
$lang['Search_unanswered'] = "Katso viestit joihin ei ole vastattu";

$lang['Register'] = "Rekisteröidy";
$lang['Profile'] = "Käyttäjätiedot";
$lang['Edit_profile'] = "Muokkaa käyttäjätietoja";
$lang['Search'] = "Haku";
$lang['Memberlist'] = "Käyttäjälista";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "BBCode ohje";
$lang['Usergroups'] = "Käyttäjäryhmät";
$lang['Last_Post'] = "Viimeinen viesti";
$lang['Moderator'] = "Moderaattori";
$lang['Moderators'] = "Moderaattorit";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Käyttäjämme ovat kirjoittaneet yhteensä <b>0</b> viestiä"; // Number of posts
$lang['Posted_articles_total'] = "Käyttäjämme ovat kirjoittaneet yhteensä <b>%d</b> viestiä"; // Number of posts
$lang['Posted_article_total'] = "Käyttäjämme ovat kirjoittaneet yhteensä <b>%d</b> viestin"; // Number of posts
$lang['Registered_users_zero_total'] = "Meillä on  <b>0</b> rekisteröityä käyttäjää"; // # registered users
$lang['Registered_users_total'] = "Meillä on  <b>%d</b> rekisteröityä käyttäjää"; // # registered users
$lang['Registered_user_total'] = "Meillä on <b>%d</b> rekisteröity käyttäjä"; // # registered users
$lang['Newest_user'] = "Uusin rekisteröitynyt käyttäjä on <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Ei uusia viestejä edellisen käyntisi jälkeen";
$lang['No_new_posts'] = "Ei uusia viestejä";
$lang['New_posts'] = "Uusia viestejä";
$lang['New_post'] = "Uusi viesti";
$lang['No_new_posts_hot'] = "Ei uusia viestejä [ Suosittu ]";
$lang['New_posts_hot'] = "Uusia viestejä [ Suosittu ]";
$lang['No_new_posts_locked'] = "Ei uusia viestejä [ Lukittu ]";
$lang['New_posts_locked'] = "Uusia viestejä [ Lukittu ]";
$lang['Forum_is_locked'] = "Foorumi on lukittu";


//
// Login
//
$lang['Enter_password'] = "Ole hyvä ja anna käyttäjätunnus sekä salasana kirjautumiseen";
$lang['Login'] = "Kirjaudu sisään";
$lang['Logout'] = "Kirjaudu ulos";

$lang['Forgotten_password'] = "Unohdin salasanani";

$lang['Log_me_in'] = "Kirjaa minut aina sisään automaattisesti";

$lang['Error_login'] = "Annoit väärän tai passiivisen käyttäjätunnuksen tai salasana oli väärin";


//
// Index page
//
$lang['Index'] = "Päävalikko";
$lang['No_Posts'] = "Ei viestejä";
$lang['No_forums'] = "Tällä sivustolla ei ole foorumeita";

$lang['Private_Message'] = "Yksityisviesti";
$lang['Private_Messages'] = "Yksityiset viestit";
$lang['Who_is_Online'] = "Ketä on paikalla";

$lang['Mark_all_forums'] = "Merkitse kaikki foorumit luetuiksi";
$lang['Forums_marked_read'] = "Kaikki foorumit on merkitty luetuiksi";


//
// Viewforum
//
$lang['View_forum'] = "Siirry foorumiin";

$lang['Forum_not_exist'] = "Valitsemaasi foorumia ei ole olemassa";
$lang['Reached_on_error'] = "Olet tällä sivulla virhetilanteen vuoksi";

$lang['Display_topics'] = "Näytä edelliset aiheet";
$lang['All_Topics'] = "Kaikki aiheet";

$lang['Topic_Announcement'] = "<b>Ilmoitus:</b>";
$lang['Topic_Sticky'] = "<b>Tiedote:</b>";
$lang['Topic_Moved'] = "<b>Siirretty:</b>";
$lang['Topic_Poll'] = "<b>[ Äänestys ]</b>";

$lang['Mark_all_topics'] = "Merkitse kaikki aiheet luetuiksi";
$lang['Topics_marked_read'] = "Tämän foorumin aiheet on merkitty luetuiksi";

$lang['Rules_post_can'] = "<b>Voit</b> kirjoittaa uusia viestejä tässä foorumissa";
$lang['Rules_post_cannot'] = "<b>Et voi</b> kirjoittaa uusia viestejä tässä foorumissa";
$lang['Rules_reply_can'] = "<b>Voit</b> vastata viesteihin tässä foorumissa";
$lang['Rules_reply_cannot'] = "<b>Et voi</b> vastata viesteihin tässä foorumissa";
$lang['Rules_edit_can'] = "<b>Voit</b> muokata viestejäsi tässä foorumissa";
$lang['Rules_edit_cannot'] = "<b>Et voi</b> muokata viestejäsi tässä foorumissa";
$lang['Rules_delete_can'] = "<b>Voit</b> poistaa viestejäsi tässä foorumissa";
$lang['Rules_delete_cannot'] = "<b>Et voi</b> poistaa viestejäsi tässä foorumissa";
$lang['Rules_vote_can'] = "<b>Voit</b> äänestää tässä foorumissa";
$lang['Rules_vote_cannot'] = "<b>Et voi</b> äänestää tässä foorumissa";
$lang['Rules_moderate'] = "<b>Voit</b> %smoderoida tätä foorumia%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Tässä foorumissa ei ole viestejä<br />Klikkaa <b>Lähetä uusi viesti</b> linkkiä tällä sivulla kirjoittaaksi viestin";


//
// Viewtopic
//
$lang['View_topic'] = "Näytä viesti";

$lang['Guest'] = 'Vieras';
$lang['Post_subject'] = "Kirjoita viesti";
$lang['View_next_topic'] = "Näytä seuraava aihe";
$lang['View_previous_topic'] = "Näytä edellinen aihe";
$lang['Submit_vote'] = "Äänestä";
$lang['View_results'] = "Näytä tulokset";

$lang['No_newer_topics'] = "Tässä foorumissa ei ole uudempia aiheita";
$lang['No_older_topics'] = "Tässä foorumissa ei ole vanhempia aiheita";
$lang['Topic_post_not_exist'] = "Hakemaasi aihetta tai viestiä ei löydy";
$lang['No_posts_topic'] = "Otsikolla ei ole viestejä";

$lang['Display_posts'] = "Näytä edelliset viestit";
$lang['All_Posts'] = "Kaikki viestit";
$lang['Newest_First'] = "Uusin ensin";
$lang['Oldest_First'] = "Vanhin ensin";

$lang['Back_to_top'] = "Takaisin alkuun";

$lang['Read_profile'] = "Näytä käyttäjän tiedot"; 
$lang['Send_email'] = "Lähetä sähköpostia käyttäjälle";
$lang['Visit_website'] = "Käy lähettäjän sivustolla";
$lang['ICQ_status'] = "ICQ Status";
$lang['Edit_delete_post'] = "Muokkaa/Poista viesti";
$lang['View_IP'] = "Näytä lähettäjän IP";
$lang['Delete_post'] = "Poista viesti";

$lang['wrote'] = "kirjoitti"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Lainaus"; // comes before bbcode quote output.
$lang['Code'] = "Koodi"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Viimeinen muokkaaja, %s pvm %s, muokattu %d kertaa"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Viimeinen muokkaaja, %s pvm %s, muokattu %d kertaa"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Lukitse aihe";
$lang['Unlock_topic'] = "Poista aiheen lukitus";
$lang['Move_topic'] = "Siirrä aihe";
$lang['Delete_topic'] = "Poista tämä aihe";
$lang['Split_topic'] = "Jaa tämä aihe";

$lang['Stop_watching_topic'] = "Lopeta aiheen seuraaminen";
$lang['Start_watching_topic'] = "Seuraa aiheen vastauksia";
$lang['No_longer_watching'] = "Aihetta ei enää seurata";
$lang['You_are_watching'] = "Aihe on nyt seurannassa";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Viestin teksti";
$lang['Topic_review'] = "Aiheen tarkistus";

$lang['No_post_mode'] = "Viestin muotoa ei ole määritetty"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Luo uusi aihe";
$lang['Post_a_reply'] = "Vastaa";
$lang['Post_topic_as'] = "Viestin muoto";
$lang['Edit_Post'] = "Muokkaa viestiä";
$lang['Options'] = "Vaihtoehdot";

$lang['Post_Announcement'] = "Ilmoitus";
$lang['Post_Sticky'] = "Tiedote";
$lang['Post_Normal'] = "Normaali";

$lang['Confirm_delete'] = "Oletko varma, että haluat poistaa tämän viestin?";
$lang['Confirm_delete_poll'] = "Oletko varma, että haluat poistaa tämän äänestyksen?";

$lang['Flood_Error'] = "Et voi tehdä uutta viestiä heti edellisen jälkeen, ole hyvä ja yritä hetken kuluttua uudelleen";
$lang['Empty_subject'] = "Sinun on annettava uudelle aiheelle selite";
$lang['Empty_message'] = "Sinun on kirjoitettava jotain viestiin";
$lang['Forum_locked'] = "Tämä foorumi on lukittu, et voi kirjoittaa uusia viestejä, vastata vanhoihin tai muokata viestejä";
$lang['Topic_locked'] = "Tämä aihe on lukittu, et voi muokata vastauksia tai kirjoittaa uusia vastauksia";
$lang['No_post_id'] = "Sinun on valittava viesti jota muokkaat";
$lang['No_topic_id'] = "Sinun on valittava aihe, johon vastaat";
$lang['No_valid_mode'] = "Voit vain luoda viestejä, vastata, muokata tai lainata viestejä, ole hyvä ja yritä uudelleen";
$lang['No_such_post'] = "Haluttua aihetta ei löydy, ole hyvä ja yritä uudelleen";
$lang['Edit_own_posts'] = "Valitettavasti voit muokata vain omia viestejäsi";
$lang['Delete_own_posts'] = "Valitettavasti voit poistaa vain omia viestejäsi";
$lang['Cannot_delete_replied'] = "Valitettavasti et voi poistaa viestejä joihin on vastattu";
$lang['Cannot_delete_poll'] = "Valitettavasti et voi poistaa aktiivista äänestystä";
$lang['Empty_poll_title'] = "Sinun on annettava äänestykselle nimi";
$lang['To_few_poll_options'] = "Äänestykselle on annettava vähintään kaksi vaihtoehtoa";
$lang['To_many_poll_options'] = "Annoit liikaa vaihtoehtoja äänestykseen";
$lang['Post_has_no_poll'] = "Tässä viestissä ei ole äänestystä";

$lang['Add_poll'] = "Lisää äänestys";
$lang['Add_poll_explain'] = "Jos et halua lisätä äänestystä viestiisi jätä tämä kenttä tyhjäksi";
$lang['Poll_question'] = "Äänestyksen aihe";
$lang['Poll_option'] = "Äänestysvaihtoehto";
$lang['Add_option'] = "Lisää vaihtoehto";
$lang['Update'] = "Päivitä";
$lang['Delete'] = "Poista";
$lang['Poll_for'] = "Äänestys on voimassa";
$lang['Days'] = "Päivää"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Anna 0 tai jätä tyhjäksi ikuiselle äänestykselle ]";
$lang['Delete_poll'] = "Poista äänestys";

$lang['Disable_HTML_post'] = "Älä salli HTML muotoiluja tässä viestissä";
$lang['Disable_BBCode_post'] = "Älä salli BBCode muotoiluja tässä viestissä";
$lang['Disable_Smilies_post'] = "Älä salli hymiöitä tässä viestissä";

$lang['HTML_is_ON'] = "HTML on <u>PÄÄLLÄ</u>";
$lang['HTML_is_OFF'] = "HTML on <u>POIS PÄÄLTÄ</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s on <u>PÄÄLLÄ</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s on <u>POIS PÄÄLTÄ</u>";
$lang['Smilies_are_ON'] = "Hymiöt ovat <u>PÄÄLLÄ</u>";
$lang['Smilies_are_OFF'] = "Hymiöt ovat <u>POIS PÄÄLTÄ</u>";

$lang['Attach_signature'] = "Liitä allekirjoitus (allekirjoitusta voidaan vaihtaa käyttäjätiedoissa)";
$lang['Notify'] = "Ilmoita vastauksesta";
$lang['Delete_post'] = "Poista tämä viesti";

$lang['Stored'] = "Viestisi on talletettu";
$lang['Deleted'] = "Viestisi on poistettu";
$lang['Poll_delete'] = "Äänestyksesi on poistettu";
$lang['Vote_cast'] = "Äänesi on rekisteröity";

$lang['Topic_reply_notification'] = "Ilmoitus vastauksesta aiheeseen";

$lang['bbcode_b_help'] = "Lihavointi: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Kursivointi: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Alleviivaus: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Lainaus: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Koodin näyttö: [code]code[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Luettelo: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Järjestetty luettelo: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Lisää kuva: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Lisää URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Sulje kaikki avoimet bbCode tagit";
$lang['bbcode_s_help'] = "Fontin väri: [color=red]text[/color]  Vihje: voit käyttää myös color=#FF0000";
$lang['bbcode_f_help'] = "Fontin koko: [size=x-small]small text[/size]";

$lang['Emoticons'] = "Hymiöt";
$lang['More_emoticons'] = "Lisää hymiöitä";

$lang['Font_color'] = "Fontin väri";
$lang['color_default'] = "Oletus";
$lang['color_dark_red'] = "Tumman punainen";
$lang['color_red'] = "Punainen";
$lang['color_orange'] = "Oranssi";
$lang['color_brown'] = "Ruskea";
$lang['color_yellow'] = "Keltainen";
$lang['color_green'] = "Vihreä";
$lang['color_olive'] = "Oliivi";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Sininen";
$lang['color_dark_blue'] = "Tumman Sininen";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violetti";
$lang['color_white'] = "Valkoinen";
$lang['color_black'] = "Musta";

$lang['Font_size'] = "Fontin koko";
$lang['font_tiny'] = "Erittäin pieni";
$lang['font_small'] = "Pieni";
$lang['font_normal'] = "Normaali";
$lang['font_large'] = "Iso";
$lang['font_huge'] = "Erittäin iso";

$lang['Close_Tags'] = "Sulje tagit";
$lang['Styles_tip'] = "Vihje: Tyylejä voi käyttää valittuun tekstiin nopeasti";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Yksityiset viestit";

$lang['Login_check_pm'] = "Kirjaudu sisään tarkistaaksesi yksityiset viestit";
$lang['New_pms'] = "Sinulla on  %d uutta viestiä"; // You have 2 new messages
$lang['New_pm'] = "Sinulla on %d uusi viesti"; // You have 1 new message
$lang['No_new_pm'] = "Sinulla ei ole uusia viestejä";
$lang['Unread_pms'] = "Sinulla on  %d lukematonta viestiä";
$lang['Unread_pm'] = "Sinulla on  %d lukematon viesti";
$lang['No_unread_pm'] = "Sinulla ei ole lukemattomia viestejä";
$lang['You_new_pm'] = "Uusi yksityinen viesti odottaa postilaatikossasi";
$lang['You_new_pms'] = "Uusia yksityisiä viestejä odottaa postilaatikossasi";
$lang['You_no_new_pm'] = "Sinulla ei ole uusia yksityisiä viestejä";

$lang['Inbox'] = "Tulevat";
$lang['Outbox'] = "Lähtevät";
$lang['Savebox'] = "Tallennetut";
$lang['Sentbox'] = "Lähetetyt";
$lang['Flag'] = "Lippu";
$lang['Subject'] = "Aihe";
$lang['From'] = "Keneltä";
$lang['To'] = "Kenelle";
$lang['Date'] = "Päiväys";
$lang['Mark'] = "Merkki";
$lang['Sent'] = "Lähetetty";
$lang['Saved'] = "Tallennettu";
$lang['Delete_marked'] = "Poista merkityt";
$lang['Delete_all'] = "Poista kaikki";
$lang['Save_marked'] = "Tallenna merkityt"; 
$lang['Save_message'] = "Tallenna viesti";
$lang['Delete_message'] = "Poista viesti";

$lang['Display_messages'] = "Näytä viestit edellisiltä"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Kaikki viestit";

$lang['No_messages_folder'] = "Sinulla ei ole viestejä tässä kansiossa";

$lang['PM_disabled'] = "Yksityisviestit eivät ole käytössä tällä sivustolla";
$lang['Cannot_send_privmsg'] = "Valitettavasti ylläpito estää yksityisviestin lähettämisen";
$lang['No_to_user'] = "Sinun on annettava vastaanottavan henkilön käyttäjätunnus";
$lang['No_such_user'] = "Valitettavasti käyttäjätunnusta ei löydy";

$lang['Message_sent'] = "Viestisi on lähetetty";

$lang['Click_return_inbox'] = "Klikkaa %stästä%s palataksesi saapuvan postin kansioon";
$lang['Click_return_index'] = "Klikkaa %stästä%s palataksesi pääsivulle.";

$lang['Send_a_new_message'] = "Lähetä uusi yksityinen viesti";
$lang['Send_a_reply'] = "Vastaa yksityiseen viestiin";
$lang['Edit_message'] = "Muokkaa yksityistä viestiä";

$lang['Notification_subject'] = "Uusi yksityinen viesti on saapunut";

$lang['Find_username'] = "Etsi käyttäjätunnus";
$lang['Find'] = "Etsi";
$lang['No_match'] = "Tietoja ei löytynyt";

$lang['No_post_id'] = "Viestin ID ei ole määritelty";
$lang['No_such_folder'] = "Kansiota ei ole olemassa";
$lang['No_folder'] = "Kansiota ei ole määritelty";

$lang['Mark_all'] = "Merkitse kaikki";
$lang['Unmark_all'] = "Poista merkintä kaikista";

$lang['Confirm_delete_pm'] = "Oletko varma, että haluat poistaa tämän viestin?";
$lang['Confirm_delete_pms'] = "Oletko varma, että haluat poistaa nämä viestit?";

$lang['Inbox_size'] = "Sisääntulevan postin kansiosta on %d%% käytetty"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Lähtevän postin kansiosta on %d%% käytetty"; 
$lang['Savebox_size'] = "Tallennetun postin kansiosta on  %d%% käytetty"; 

$lang['Click_view_privmsg'] = "Klikkaa %stästä%s siirtyäksesi sisääntulevan postin kansioon";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Käyttäjätiedot :: %s"; // %s is username 
$lang['About_user'] = "Kaikki käyttäjästä %s"; // %s is username

$lang['Preferences'] = "Valinnat";
$lang['Items_required'] = "Kentät, jotka on merkitty * ovat pakollisia ellei muuta mainita";
$lang['Registration_info'] = "Rekisteröintitiedot";
$lang['Profile_info'] = "Käyttäjätiedot";
$lang['Profile_info_warn'] = "Nämä tiedot ovat kaikille näkyvissä";
$lang['Avatar_panel'] = "Avatarien ohjauspaneeli";
$lang['Avatar_gallery'] = "Avatar galleria";

$lang['Website'] = "Kotisivu";
$lang['Location'] = "Paikkakunta";
$lang['Contact'] = "Yhteystiedot";
$lang['Email_address'] = "Sähköpostiosoite";
$lang['Email'] = "Sähköposti";
$lang['Send_private_message'] = "Lähetä yksityinen viesti";
$lang['Hidden_email'] = "[ Piilotettu ]";
$lang['Search_user_posts'] = "Etsi tämän käyttäjän kirjoittamat viestit";
$lang['Interests'] = "Harrastukset";
$lang['Occupation'] = "Ammatti"; 
$lang['Poster_rank'] = "Käyttäjätitteli";

$lang['Total_posts'] = "Viestejä yhteensä";
$lang['User_post_pct_stats'] = "%.2f%% kaikista viesteistä"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f viestiä per päivä"; // 1.5 posts per day
$lang['Search_user_posts'] = "Etsi kaikki viestit, jotka on kirjoittanut %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Valitettavasti käyttäjätunnusta ei ole olemassa";
$lang['Wrong_Profile'] = "Et voi muokata toisen käyttäjätunnuksen tietoja.";
$lang['Sorry_banned_or_taken_email'] = "Valitettavasti sähköpostiosoite jonka annoit on porttikiellossa, rekisteröity jo toiselle käyttäjälle tai muuten epäkelpo. Ole hyvä ja yritä toista osoitetta. Jos myös se on estetty, ota yhteys ylläpitoon";
$lang['Only_one_avatar'] = "Voit määritellä vain yhden Avatarin";
$lang['File_no_data'] = "Antamassasi URL:ssa ei ole dataa";
$lang['No_connection_URL'] = "Yhteyttä antamaasi URL:iin ei saatu";
$lang['Incomplete_URL'] = "Antamasi URL ei ole täydellinen";
$lang['Wrong_remote_avatar_format'] = "URL ulkoiselle Avatarille ei ole kelvollinen";
$lang['No_send_account_inactive'] = "Valitettavasti salasanaasi ei voida selvittää koska käyttäjätunnuksesi ei ole aktiivinen. Ole hyvä ja ota yhteyttä ylläpitoon mikäli haluat lisätietoja";

$lang['Always_smile'] = "Salli aina hymiöt";
$lang['Always_html'] = "Salli aina HTML";
$lang['Always_bbcode'] = "Salli aina BBCode";
$lang['Always_add_sig'] = "Liitä aina allekirjoitus";
$lang['Always_notify'] = "Lähetä aina ilmoitus vastauksista";
$lang['Always_notify_explain'] = "Saat sähköpostia kun joku vastaa luomaasi aiheeseen. Voit muuttaa tätä jokaiselle viestille";

$lang['Board_style'] = "Sivuston tyyli";
$lang['Board_lang'] = "Sivuston kieli";
$lang['No_themes'] = "Ei teemoja tietokannassa";
$lang['Timezone'] = "Aikavyöhyke";
$lang['Date_format'] = "Päiväyksen muoto";
$lang['Date_format_explain'] = "Syntaksin muoto vastaa PHP:n <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> funktiota";
$lang['Signature'] = "Allekirjoitus";
$lang['Signature_explain'] = "Tämä teksti voidaan lisätä kirjoittamiisi viesteihin. Käytössä on %d merkin rajoitus.";
$lang['Public_view_email'] = "Näytä aina sähköpostiosoitteeni";

$lang['Current_password'] = "Nykyinen salasana";
$lang['New_password'] = "Uusi salasana";
$lang['Confirm_password'] = "Vahvista salasana";
$lang['password_if_changed'] = "Anna salasana vain jos haluat vaihtaa sitä";
$lang['password_confirm_if_changed'] = "Vahvistus tarvitaan vain jos muutit salasanaa yläpuolella olevassa kentässä";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Näyttää pienen graafisen kuvan tietojesi alla viestiruudussa. Vain yksi Avatar kerrallaan, jonka leveys ei voi olla suurempi kuin %d pixeliä, ja korkeus ei voi ylittää %d pixeliä ja tiedoston koko korkeintaan %dkB."; $lang['Upload_Avatar_file'] = "Lataa Avatar omalta koneeltasi";
$lang['Upload_Avatar_URL'] = "Lataa Avatar URL:sta";
$lang['Upload_Avatar_URL_explain'] = "Anna URL, Avatar tiedostoon, tiedosto kopioidaan tälle palvelimelle.";
$lang['Pick_local_Avatar'] = "Valitse Avatar galleriasta";
$lang['Link_remote_Avatar'] = "Linkki ulkopuoliseen Avatariin";
$lang['Link_remote_Avatar_explain'] = "Anna URL tiedostoon, jonka Avatar kuvan haluat linkittää.";
$lang['Avatar_URL'] = "Avatar kuvan URL";
$lang['Select_from_gallery'] = "Valitse Avatar galleriasta";
$lang['View_avatar_gallery'] = "Näytä galleria";

$lang['Select_avatar'] = "Valitse avatar";
$lang['Return_profile'] = "Peruuta avatar valinta";
$lang['Select_category'] = "Valitse categoria";

$lang['Delete_Image'] = "Poista kuva";
$lang['Current_Image'] = "Nykyinen kuva";

$lang['Notify_on_privmsg'] = "Ilmoita uudesta yksityisestä viestistä";
$lang['Popup_on_privmsg'] = "Pop up ikkuna uudesta yksityisestä viestistä"; 
$lang['Popup_on_privmsg_explain'] = "Jotkut sivumallit voivat avata uuden ikkunan kun uusi yksityinen viesti saapuu"; 
$lang['Hide_user'] = "Piilota online status";

$lang['Profile_updated'] = "Käyttäjätietosi on päivitetty";
$lang['Profile_updated_inactive'] = "Käyttäjätietosi on päivitetty, Olet kuitenkin muuttanut joitakin tärkeitä tietoja tunnuksesi ei ole aktiivinen. Tarkista sähköpostistasi kuinka saat tunnuksesi taas aktivoitua. Jos sivuston ylläpito suorittaa aktivoinnin odota kunnes tunnuksesi on jälleen aktivoitu";

$lang['Password_mismatch'] = "Antamasi salasanat eivät täsmänneet";
$lang['Current_password_mismatch'] = "Antamasi nykyinen salasana ei täsmää tietokantaan tallennetun kanssa";
$lang['Invalid_username'] = "Antamasi käyttäjätunnus on jo käytössä, ei ole sallittu tai sisältää kiellettyjä merkkejä kuten \" merkin";
$lang['Signature_too_long'] = "Allekirjoituksesi on liian pitkä";
$lang['Fields_empty'] = "Kaikki pakolliset kentät on täytettävä";
$lang['Avatar_filetype'] = "Avatarin tiedostotyypin tulee olla .jpg, .gif tai .png";
$lang['Avatar_filesize'] = "Avatarin tiedoston koko pitää olla vähemmän kuin %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "Avatarin tulee olla vähemmän kuin %d pixeliä leveä ja %d pixeliä korkea"; 

$lang['Welcome_subject'] = "Tervetuloa %s Foorumeihin"; // Welcome to my.com forums
$lang['New_account_subject'] = "Uusi käyttäjätunnus";
$lang['Account_activated_subject'] = "Käyttäjätunnus aktivoitu";

$lang['Account_added'] = "Kiitoksia rekisteröitymisestä, käyttäjätunnuksesi on luotu. Voit nyt kirjautua sisään käyttäjätunnuksellasi ja salasanalla";
$lang['Account_inactive'] = "Käyttäjätunnuksesi on luotu. Tämä sivusto vaatii kuitenkin tunnuksen aktivoinnin, aktivointiavain on lähetetty sähköpostiosoitteeseen jonka annoit. Tarkista sähköpostistasi lisäohjeet.";
$lang['Account_inactive_admin'] = "Käyttäjätunnuksesi on luotu. Tämä sivusto vaatii kuitenkin, että ylläpito aktivoi käyttäjätunnuksen. Heille on lähetetty sähköposti ja saat tiedon kun käyttäjätunnuksesi on aktivoitu";
$lang['Account_active'] = "Käyttäjätunnuksesi on aktivoitu. Kiitoksia rekisteröitymisestä";
$lang['Account_active_admin'] = "Käyttäjätunnus on nyt aktivoitu";
$lang['Reactivate'] = "Aktivoi uudelleen käyttäjätunnuksesi!";
$lang['COPPA'] = "Käyttäjätunnuksesi on luotu mutta vaatii vahvistuksen, Tarkista sähköpostistasi lisäohjeet.";

$lang['Registration'] = "Rekisteröintisopimus";
$lang['Reg_agreement'] = "Vaikka tämän sivuston ylläpitäjät ja moderaattorit pyrkivät poistamaan tai muokkaamaan kaiken yleisesti arvelluttavan sisällön niin nopeasti kuin mahdollista, on mahdotonta tarkistaa jokaista viestiä. Tiedostatte siis, että viestit sivuilla ovat kirjoittajiensa mielipiteitä eivätkä ylläpidon, moderaattoreiden tai webmasterin (lukuunottamatta heidän itsensä kirjoittamia viestejä) ja siksi he eivät ole vastuussa näistä kirjoituksista.<br /><br />Suostut olemaan esittämättä mitään loukkaavaa, vihamielistä, epämoraalista tai muutakaan materiaalia joka voisi loukata voimassa olevia lakeja. Toimimalla tätä vastoin voidaan sinut välittömästi ja lopullisesti poistaa järjestelmän käyttäjistä (tarvittaessa yhteydentarjoajaasi otetaan yhteyttä). Kaikkien viestien IP osoite tallennetaan tämän vuoksi. Suostut siihen, että webmaster, ylläpito ja moderaattorit ovat oikeutettuja poistamaan, muokkaamaan, siirtämään tai sulkemaan minkä tahansa aiheen milloin tahansa. Käyttäjänä suostut siihen, että kaikki yllä annettu tieto tallennetaan tietokantaan. Tätä tietoa ei anneta millekään kolmannelle osapuolelle ilman suostumustasi. Webmaster, ylläpito ja moderaattorit eivät ole vastuullisia jos tietoturva vaarantuu hakkerointiyrityksistä tms. johtuen.<br /><br />Tämä sivusto käyttää avusteita (cookies) tallentamaan tietoa paikalliselle tietokoneelle. Nämä avusteet eivät sisällä mitään yllä annetuista tiedoista, niiden ainoa tarkoitus on helpottaa käyttöä. Sähköpostiosoitetta käytetään vain käyttäjätunnus tietojen lähettämiseen (Sekä salasanan lähettämiseen jos unohdat sen).<br /><br /> Klikkaamalla Hyväksyn hyväksyt nämä ehdot.";

$lang['Agree_under_13'] = "Hyväksyn ehdot ja olen <b>alle</b> 13 vuotias";
$lang['Agree_over_13'] = "Hyväksyn ehdot ja olen <b>yli</b> 13 vuotias";
$lang['Agree_not'] = "En hyväksy ehtoja";

$lang['Wrong_activation'] = "Antamasi aktivointiavain ei täsmää tietokantaan tallennetun kanssa";
$lang['Send_password'] = "Lähetä minulle uusi salasana"; 
$lang['Password_updated'] = "Uusi salasana on luotu, tarkista sähköpostistasi lisäohjeet";
$lang['No_email_match'] = "Antamasi sähköpostiosoite ei täsmää käyttäjätunnuksen tiedoissa annettuun";
$lang['New_password_activation'] = "Uuden salasanan aktivointi";
$lang['Password_activated'] = "Käyttäjätunnuksesi on aktivoitu uudelleen. Kirjaudu sisään käyttäen uutta salasanaa joka lähetettiin sähköpostiisi";

$lang['Send_email_msg'] = "Lähetä sähköpostia";
$lang['No_user_specified'] = "Käyttäjää ei ole määritelty";
$lang['User_prevent_email'] = "Tämä käyttäjä ei halua vastaanottaa sähköpostia. Yritä lähettää yksityinen viesti";
$lang['User_not_exist'] = "Käyttäjätunnusta ei ole olemassa";
$lang['CC_email'] = "Lähetä kopio sähköpostista itsellesi";
$lang['Email_message_desc'] = "Tämä viesti lähetetään pelkkänä tekstinä, älä käytä HTML tai BBCode koodeja. Paluuosoitteeksi tälle viestille asetetaan sähköpostiosoitteesi.";
$lang['Flood_email_limit'] = "Et voi lähettää uutta sähköpostia nyt, yritä myöhemmin uudelleen";
$lang['Recipient'] = "Vastaanottaja";
$lang['Email_sent'] = "Sähköposti on lähetetty";
$lang['Send_email'] = "Lähetä sähköposti";
$lang['Empty_subject_email'] = "Sinun täytyy antaa otsikko sähköpostille";
$lang['Empty_message_email'] = "Sinun täytyy kirjoittaa viesti sähköpostiin";


//
// Memberslist
//
$lang['Select_sort_method'] = "Valitse järjestys";
$lang['Sort'] = "Järjestä";
$lang['Sort_Top_Ten'] = "Top Ten kirjoittajat";
$lang['Sort_Joined'] = "Liittymispäivä";
$lang['Sort_Username'] = "Käyttäjätunnus";
$lang['Sort_Location'] = "Paikkakunta";
$lang['Sort_Posts'] = "Viestejä yhteensä";
$lang['Sort_Email'] = "Sähköposti";
$lang['Sort_Website'] = "Kotisivu";
$lang['Sort_Ascending'] = "Laskeva";
$lang['Sort_Descending'] = "Nouseva";
$lang['Order'] = "Lajittelu";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Ryhmän ohjauspaneeli";
$lang['Group_member_details'] = "Ryhmän jäsentiedot";
$lang['Group_member_join'] = "Liity ryhmään";

$lang['Group_Information'] = "Ryhmän tiedot";
$lang['Group_name'] = "Ryhmän nimi";
$lang['Group_description'] = "Ryhmän kuvaus";
$lang['Group_membership'] = "Ryhmän jäsenyys";
$lang['Group_Members'] = "Ryhmän jäsenet";
$lang['Group_Moderator'] = "Ryhmän moderaattori";
$lang['Pending_members'] = "Odottavat jäsenet";

$lang['Group_type'] = "Ryhmän tyyppi";
$lang['Group_open'] = "Avoin ryhmä";
$lang['Group_closed'] = "Suljettu ryhmä";
$lang['Group_hidden'] = "Piilotettu ryhmä";

$lang['Current_memberships'] = "Nykyiset jäsenyydet";
$lang['Non_member_groups'] = "Ryhmät joissa ei jäsenenä";
$lang['Memberships_pending'] = "Jäsenyys haussa";

$lang['No_groups_exist'] = "Ryhmiä ei ole olemassa";
$lang['Group_not_exist'] = "Ryhmää ei ole olemassa";

$lang['Join_group'] = "Liity ryhmään";
$lang['No_group_members'] = "Tässä ryhmässä ei ole jäseniä";
$lang['Group_hidden_members'] = "Tämä ryhmä on piilotettu, et voi katsoa sen tietoja";
$lang['No_pending_group_members'] = "Tällä ryhmällä ei ole jäseniä odottamassa";
$lang["Group_joined"] = "Hakemuksesi ryhmään on jätetty<br />Ryhmän moderaattori ilmoittaa sinulle kun jäsenyytesi on hyväksytty";
$lang['Group_request'] = "Ryhmään on tehty jäseneksi liittymispyyntö";
$lang['Group_approved'] = "Hakemuksesi on hyväksytty";
$lang['Group_added'] = "Sinut on lisätty tähän käyttäjäryhmään"; 
$lang['Already_member_group'] = "Olet jo jäsen tässä käyttäjäryhmässä";
$lang['User_is_member_group'] = "Käyttäjä on jo tämän ryhmän jäsen";
$lang['Group_type_updated'] = "Ryhmän tyypin päivitys suoritettu";

$lang['Could_not_add_user'] = "Valitsemaasi käyttäjää ei ole olemassa";
$lang['Could_not_anon_user'] = "Anonyymi käyttäjä ei voi olla ryhmän jäsen";

$lang['Confirm_unsub'] = "Oletko varma, että haluat poistua ryhmästä?";
$lang['Confirm_unsub_pending'] = "Jäsenyyttäsi ryhmään ei ole vielä hyväksytty, Oletko varma, että haluat perua jäsenyyden?";

$lang['Unsub_success'] = "Jäsenyytesi tähän ryhmään on poistettu.";

$lang['Approve_selected'] = "Hyväksy valitut";
$lang['Deny_selected'] = "Hylkää valitut";
$lang['Not_logged_in'] = "Sinun täytyy kirjautua sisään voidaksesi liittyä ryhmään.";
$lang['Remove_selected'] = "Poista valitut";
$lang['Add_member'] = "Lisää jäsen";
$lang['Not_group_moderator'] = "Et ole tämän ryhmän moderaattori joten et voi suorittaa tätä toimenpidettä.";

$lang['Login_to_join'] = "Kirjaudu sisään liittyäksesi ryhmään tai ylläpitääksesi ryhmän jäsenyystietoja";
$lang['This_open_group'] = "Tämä on avoin ryhmä, klikkaa hakeaksesi jäsenyyttä";
$lang['This_closed_group'] = "Tämä on suljettu ryhmä, lisää jäseniä ei hyväksytä";
$lang['This_hidden_group'] = "Tämä on piilotettu ryhmä, automaattinen käyttäjien lisäys ei ole sallittua";
$lang['Member_this_group'] = "Olet tämän ryhmän jäsen";
$lang['Pending_this_group'] = "Jäsenyytesi tähän ryhmään on käsittelyssä";
$lang['Are_group_moderator'] = "Olet ryhmän moderaattori";
$lang['None'] = "Ei kukaan";

$lang['Subscribe'] = "Liity";
$lang['Unsubscribe'] = "Eroa";
$lang['View_Information'] = "Näytä tiedot";


//
// Search
//
$lang['Search_query'] = "Haku ";
$lang['Search_options'] = "Haun vaihtoehdot";

$lang['Search_keywords'] = "Etsi avainsanoja";
$lang['Search_keywords_explain'] = "Voit käyttää <u>AND</u> määrittämään sanat joiden täytyy löytyä haussa, <u>OR</u> sanoille jotka voivat olla vaihtoehtoisesti haussa ja <u>NOT</u> määrittämään sanat, joita ei saa olla haussa. Käytä * jokerimerkkinä";
$lang['Search_author'] = "Etsi kirjoittajaa";
$lang['Search_author_explain'] = "Käytä * jokerimerkkinä";

$lang['Search_for_any'] = "Hae millä tahansa ehdolla tai käytä annettua hakujonoa";
$lang['Search_for_all'] = "Etsi kaikilla annetuilla ehdoilla";
$lang['Search_title_msg'] = "Etsi viestin aiheesta ja tekstistä";
$lang['Search_msg_only'] = "Etsi vain viestin tekstistä";

$lang['Return_first'] = "Näytä ensimmäiset"; // followed by xxx characters in a select box
$lang['characters_posts'] = "merkkiä viestistä";

$lang['Search_previous'] = "Hae edelliset"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Järjestä";
$lang['Sort_Time'] = "Viestin aika";
$lang['Sort_Post_Subject'] = "Viestin otsikko";
$lang['Sort_Topic_Title'] = "Aiheen otsikko";
$lang['Sort_Author'] = "Kirjoittaja";
$lang['Sort_Forum'] = "Foorumi";

$lang['Display_results'] = "Näytä tulokset";
$lang['All_available'] = "Kaikki mahdolliset";
$lang['No_searchable_forums'] = "Sinulle ei ole lupaa etsiä foorumeista tällä sivustolla";

$lang['No_search_match'] = "Yksikään aihe tai viesti ei vastannut hakuehtoja";
$lang['Found_search_match'] = "Haussa löytyi %d osuma"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Haussa löytyi %d osumaa"; // eg. Search found 24 matches

$lang['Close_window'] = "Sulje ikkuna";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Valitettavasti vain %s voivat tehdä ilmoituksia tässä foorumissa";
$lang['Sorry_auth_sticky'] = "Valitettavasti vain %s voivat tehdä tiedotuksia tässä foorumissa"; 
$lang['Sorry_auth_read'] = "Valitettavasti vain %s voivat lukea aiheita tässä foorumissa"; 
$lang['Sorry_auth_post'] = "Valitettavasti vain %s voivat luoda aiheita tässä foorumissa"; 
$lang['Sorry_auth_reply'] = "Valitettavasti vain %s voivat kirjoittaa vastauksia tässä foorumissa"; 
$lang['Sorry_auth_edit'] = "Valitettavasti vain %s voivat muokata viestejä tässä foorumissa"; 
$lang['Sorry_auth_delete'] = "Valitettavasti vain %s voivat poistaa viestejä tässä foorumissa"; 
$lang['Sorry_auth_vote'] = "Valitettavasti vain %s voivat äänestää tässä foorumissa"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>rekisteröitymättömät käyttäjät</b>";
$lang['Auth_Registered_Users'] = "<b>rekisteröidyt käyttäjät</b>";
$lang['Auth_Users_granted_access'] = "<b>käyttäjät, joille on erikoisoikeuksia</b>";
$lang['Auth_Moderators'] = "<b>moderaattorit</b>";
$lang['Auth_Administrators'] = "<b>ylläpitäjät</b>";

$lang['Not_Moderator'] = "Et ole tämän foorumin moderaattori";
$lang['Not_Authorised'] = "Sinulla ei ole lupaa";

$lang['You_been_banned'] = "Sinulla on porttikielto tähän foorumiin<br />Ole hyvä ja ota yhteyttä webmasteriin tai ylläpitoon jos haluat lisätietoja";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Tällä hetkellä on 0 rekisteröityä käyttäjää ja "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Tällä hetkellä on %d rekisteröity käyttäjä ja "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Tällä hetkellä on %d rekisteröityä käyttäjää ja "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "0 piilotettua käyttäjää online"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d piilotettu käyttäjä online"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d piilotettua käyttäjää online"; // 6 Hidden users online
$lang['Guest_users_online'] = "Tällä hetkellä on  %d vierasta online"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "Tällä hetkellä on 0 vierailijaa online"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Tällä hetkellä on  %d vieras online"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Tällä hetkellä foorumeilla ei ole käyttäjiä";

$lang['Online_explain'] = "Tieto perustuu viimeisen viiden minuutin aikana olleisiin aktiiveihin käyttäjiin";

$lang['Forum_Location'] = "Sijainti foorumissa";
$lang['Last_updated'] = "Viimeksi päivitetty";

$lang['Forum_index'] = "Foorumin päävalikko";
$lang['Logging_on'] = "Kirjautuu";
$lang['Posting_message'] = "Lähettää viestiä";
$lang['Searching_forums'] = "Etsii foorumeita";
$lang['Viewing_profile'] = "Katsoo  käyttäjätietoja";
$lang['Viewing_online'] = "Katsoo keitä on online";
$lang['Viewing_member_list'] = "Katsoo käyttäjälistaa";
$lang['Viewing_priv_msgs'] = "Katsoo yksityisiä viestejä";
$lang['Viewing_FAQ'] = "Katsoo FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Moderaattorin ohjauspaneeli";
$lang['Mod_CP_explain'] = "Käyttämällä alla olevaa lomaketta voit suorittaa useita moderointitoimia tässä foorumissa. Voit lukita, vapauttaa, siirtää tai poistaa mitä tahansa aiheita.";

$lang['Select'] = "Valitse";
$lang['Delete'] = "Poista";
$lang['Move'] = "Siirrä";
$lang['Lock'] = "Lukitse";
$lang['Unlock'] = "Vapauta";

$lang['Topics_Removed'] = "Valitut aiheet on onnistuneesti poistettu tietokannasta.";
$lang['Topics_Locked'] = "Valitut aiheet on lukittu";
$lang['Topics_Moved'] = "Valitut aiheet on siirretty";
$lang['Topics_Unlocked'] = "Valitut aiheet on vapautettu";
$lang['No_Topics_Moved'] = "Aiheita ei siirretty";

$lang['Confirm_delete_topic'] = "Oletko varma, että haluat poistaa valitun/valitut aiheen/aiheet?";
$lang['Confirm_lock_topic'] = "Oletko varma, että haluat lukita valitun/valitut aiheen/aiheet?";
$lang['Confirm_unlock_topic'] = "Oletko varma, että haluat vapauttaa valitun/valitut aiheen/aiheet?";
$lang['Confirm_move_topic'] = "Oletko varma, että haluat siirtää valitun/valitut aiheen/aiheet?";

$lang['Move_to_forum'] = "Siirrä foorumiin";
$lang['Leave_shadow_topic'] = "Jätä varjo-otsikko vanhaan foorumiin.";

$lang['Split_Topic'] = "Aiheen jakamisen hallintapaneeli";
$lang['Split_Topic_explain'] = "Käyttämällä alla olevaa lomaketta voit jakaa aiheen kahtia, joko valitsemalla viestit erikseen tai jakamalla valitusta kohdasta";
$lang['Split_title'] = "Uuden aiheen otsikko";
$lang['Split_forum'] = "Uuden aiheen foorumi";
$lang['Split_posts'] = "Jaa valitut viestit";
$lang['Split_after'] = "Valitun viestin jako";
$lang['Topic_split'] = "Valittu aihe on onnistuneesti jaettu";

$lang['Too_many_error'] = "Olet valinnut liikaa viestejä. Voit valita vain yhden viestin, josta aihe jaetaan!";

$lang['None_selected'] = "Et ole valinnut yhtään aihetta. Ole hyvä ja valitse ainakin yksi.";
$lang['New_forum'] = "Uusi foorumi";

$lang['This_posts_IP'] = "Tämän viestin IP";
$lang['Other_IP_this_user'] = "Muut IP't joista käyttäjä on kirjoittanut";
$lang['Users_this_IP'] = "Käyttäjät, jotka kirjoittavat tästä IP:stä";
$lang['IP_info'] = "IP Informaatio";
$lang['Lookup_IP'] = "Tarkista IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Kaikki ajat ovat %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 tuntia";
$lang['-11'] = "GMT - 11 tuntia";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 tuntia";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 tuntia";
$lang['-3.5'] = "GMT - 3.5 tuntia";
$lang['-3'] = "GMT - 3 tuntia";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 tuntia";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europe)";
$lang['2'] = "EET (Europe)";
$lang['3'] = "GMT + 3 tuntia";
$lang['3.5'] = "GMT + 3.5 tuntia";
$lang['4'] = "GMT + 4 tuntia";
$lang['4.5'] = "GMT + 4.5 tuntia";
$lang['5'] = "GMT + 5 tuntia";
$lang['5.5'] = "GMT + 5.5 tuntia";
$lang['6'] = "GMT + 6 tuntia";
$lang['7'] = "GMT + 7 tuntia";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9 tuntia";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11 tuntia";
$lang['12'] = "GMT + 12 tuntia";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 tuntia) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 tuntia) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 tuntia) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 tuntia) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 tuntia) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 tuntia) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 tuntia) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 tuntia) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 tuntia) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 tuntia) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 tuntia) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 tuntia) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 tuntia) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 tuntia) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 tuntia) Cairo, Helsinki, Kaliningrad, South Africa, Warsaw";
$lang['tz']['3'] = "(GMT +3:00 tuntia) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 tuntia) Tehran";
$lang['tz']['4'] = "(GMT +4:00 tuntia) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 tuntia) Kabul";
$lang['tz']['5'] = "(GMT +5:00 tuntia) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 tuntia) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 tuntia) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 tuntia) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 tuntia) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 tuntia) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 tuntia) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 tuntia) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 tuntia) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 tuntia) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 tuntia) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "Sunnuntai";
$lang['days_long'][1] = "Maanantai";
$lang['days_long'][2] = "Tiistai";
$lang['days_long'][3] = "Keskiviikko";
$lang['days_long'][4] = "Torstai";
$lang['days_long'][5] = "Perjantai";
$lang['days_long'][6] = "Lauantai";

$lang['days_short'][0] = "Sun";
$lang['days_short'][1] = "Maa";
$lang['days_short'][2] = "Tii";
$lang['days_short'][3] = "Kes";
$lang['days_short'][4] = "Tor";
$lang['days_short'][5] = "Per";
$lang['days_short'][6] = "Lau";

$lang['months_long'][0] = "Tammikuu";
$lang['months_long'][1] = "Helmikuu";
$lang['months_long'][2] = "Maaliskuu";
$lang['months_long'][3] = "Huhtikuu";
$lang['months_long'][4] = "Toukokuu";
$lang['months_long'][5] = "Kesäkuu";
$lang['months_long'][6] = "Heinäkuu";
$lang['months_long'][7] = "Elokuu";
$lang['months_long'][8] = "Syyskuu";
$lang['months_long'][9] = "Lokakuu";
$lang['months_long'][10] = "Marraskuu";
$lang['months_long'][11] = "Joulukuu";

$lang['months_short'][0] = "Tam";
$lang['months_short'][1] = "Hel";
$lang['months_short'][2] = "Maa";
$lang['months_short'][3] = "Huh";
$lang['months_short'][4] = "Tou";
$lang['months_short'][5] = "Kes";
$lang['months_short'][6] = "Hei";
$lang['months_short'][7] = "Elo";
$lang['months_short'][8] = "Syy";
$lang['months_short'][9] = "Lok";
$lang['months_short'][10] = "Mar";
$lang['months_short'][11] = "Jou";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Informaatio";
$lang['Critical_Information'] = "Tärkeää informaatiota";

$lang['General_Error'] = "Yleinen virhe";
$lang['Critical_Error'] = "Kriittinen virhe";
$lang['An_error_occured'] = "Tapahtui virhe";
$lang['A_critical_error'] = "Tapahtui kriittinen virhe";

//
// That's all Folks!
// -------------------------------------------------

?>