<?php
/***************************************************************************
 *                            lang_main.php [Croatian]
 *                              -------------------
 *     begin                : Monday Dec 01 2002
 *     copyright            : (C) 2002 Hrvoje Stankov
 *     email                : hrvoje@spirit.hr
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

$lang['ENCODING'] = 'windows-1250';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'left';
$lang['RIGHT'] = 'right';
$lang['DATE_FORMAT'] = 'd M Y'; // This should be changed to the default date format for your language, php date() format

$lang['TRANSLATION'] = 'Preveo spirit team';

$lang['Forum'] = 'Forum';
$lang['Category'] = 'Kategorija';
$lang['Topic'] = 'Tema';
$lang['Topics'] = 'Teme';
$lang['Replies'] = 'Odgovori';
$lang['Views'] = 'Pregledano';
$lang['Post'] = 'Poruka';
$lang['Posts'] = 'Poruke';
$lang['Posted'] = 'Poslao';
$lang['Username'] = 'Korisnièko ime';
$lang['Password'] = 'Lozinka';
$lang['Email'] = 'Email';
$lang['Poster'] = 'Poslao';
$lang['Author'] = 'Autor';
$lang['Time'] = 'Vrijeme';
$lang['Hours'] = 'Sati';
$lang['Message'] = 'Poruka';

$lang['1_Day'] = '1 Dan';
$lang['7_Days'] = '7 Dana';
$lang['2_Weeks'] = '2 Tjedna';
$lang['1_Month'] = '1 Mjesec';
$lang['3_Months'] = '3 Mjeseca';
$lang['6_Months'] = '6 Mjeseci';
$lang['1_Year'] = '1 Godina';

$lang['Go'] = 'Kreni';
$lang['Jump_to'] = 'Skoèi na';
$lang['Submit'] = 'Pošalji';
$lang['Reset'] = 'Resetiraj';
$lang['Cancel'] = 'Poništi';
$lang['Preview'] = 'Pregled poruke';
$lang['Confirm'] = 'Potvrdi';
$lang['Spellcheck'] = 'Gramatièka provjera';
$lang['Yes'] = 'Da';
$lang['No'] = 'Ne';
$lang['Enabled'] = 'Omoguæeno';
$lang['Disabled'] = 'Onemoguæeno';
$lang['Error'] = 'Greška';

$lang['Next'] = 'Sljedeci';
$lang['Previous'] = 'Prethodni';
$lang['Goto_page'] = 'Idi na stranicu';
$lang['Joined'] = 'Pridružen';
$lang['IP_Address'] = 'IP adresa';

$lang['Select_forum'] = 'Izaberi forum';
$lang['View_latest_post'] = 'Vidi zadnje poruke';
$lang['View_newest_post'] = 'Vidi najnovije poruke';
$lang['Page_of'] = 'Stranica <b>%d</b> od <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ broj';
$lang['AIM'] = 'AIM Adresa';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo Messenger';

$lang['Forum_Index'] = '%s forum';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Postavi novu temu';
$lang['Reply_to_topic'] = 'Odgovori na poruku';
$lang['Reply_with_quote'] = 'Odgovoriti sa citatom';

$lang['Click_return_topic'] = 'Klikni %sOvdje%s za povratak na temu'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = 'Klikni %sOvdje%s za ponovni pokušaj';
$lang['Click_return_forum'] = 'Klikni %sOvdje%s za povratak na forum';
$lang['Click_view_message'] = 'Klikni %sOvdje%s za pregled svoju poruku';
$lang['Click_return_modcp'] = 'Klikni %sOvdje%s za povratak na urednièki kontrolni panel';
$lang['Click_return_group'] = 'Klikni %sOvdje%s za povratak na informacije o grupi';

$lang['Admin_panel'] = 'Idi na administracijski panel';

$lang['Board_disable'] = 'Oprostite ali ovaj forum trenutno nije dostupan, pokušajte ponovno kasnije';


//
// Global Header strings
//
$lang['Registered_users'] = 'Registriranih korisnika:';
$lang['Browsing_forum'] = 'Korisnici trenutno na forumu:';
$lang['Online_users_zero_total'] = 'Ukupno je <b>0</b> korisnika na forumu ::';
$lang['Online_users_total'] = 'Ukupno su <b>%d</b> korisnika na forumu ::';
$lang['Online_user_total'] = 'Ukupno je <b>%d</b> korisnik na forumu ::';
$lang['Reg_users_zero_total'] = '0 Registriranih,';
$lang['Reg_users_total'] = '%d Registriranih,';
$lang['Reg_user_total'] = '%d Registriran,';
$lang['Hidden_users_zero_total'] = '0 Skrivenih i';
$lang['Hidden_user_total'] = '%d Skrivenih i';
$lang['Hidden_users_total'] = '%d Skriven i';
$lang['Guest_users_zero_total'] = '0 Gostiju';
$lang['Guest_users_total'] = '%d Gosta';
$lang['Guest_user_total'] = '%d Gost';
$lang['Record_online_users'] = 'Najviše korisnika na forumu ikad bilo je <b>%s</b> dana %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAdministrator%s';
$lang['Mod_online_color'] = '%sUrednik%s';

$lang['You_last_visit'] = 'Posljednji put posjetili ste forum %s'; // %s replaced by date/time
$lang['Current_time'] = 'Sada je %s'; // %s replaced by time

$lang['Search_new'] = 'Pregledaj poruke od svoje posljednje posjete';
$lang['Search_your_posts'] = 'Pregledaj svoje poruke';
$lang['Search_unanswered'] = 'Pregledaj neodgovorene poruke';

$lang['Register'] = 'Registriraj se';
$lang['Profile'] = 'Profil';
$lang['Edit_profile'] = 'Izmijeni svoj profil';
$lang['Search'] = 'Traži';
$lang['Memberlist'] = 'Lista èlanova';
$lang['FAQ'] = 'FAQ - Èesto Postavljana Pitanja';
$lang['BBCode_guide'] = 'BBCode vodiè';
$lang['Usergroups'] = 'Korisnièke grupe';
$lang['Last_Post'] = 'Posljednja poruka';
$lang['Moderator'] = 'Urednik';
$lang['Moderators'] = 'Urednici';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Naši korisnici su poslali ukupno <b>0</b> èlanaka'; // Number of posts
$lang['Posted_articles_total'] = 'Naši korisnici su poslali ukupno <b>%d</b> èlanaka'; // Number of posts
$lang['Posted_article_total'] = 'Naši korisnici su poslali ukupno <b>%d</b> èlanak'; // Number of posts
$lang['Registered_users_zero_total'] = 'Imamo <b>0</b> registriranih korisnika'; // # registered users
$lang['Registered_users_total'] = 'Imamo <b>%d</b> registriranih korisnika'; // # registered users
$lang['Registered_user_total'] = 'Imamo <b>%d</b> registriranog korisnika'; // # registered users
$lang['Newest_user'] = 'Najnoviji registrirani èlan je <b>%s%s%s</b>'; // a href, username, /a

$lang['No_new_posts_last_visit'] = 'Nema novih poruka od Vaše posljednje posjete';
$lang['No_new_posts'] = 'Nema novih poruka';
$lang['New_posts'] = 'Nove poruke';
$lang['New_post'] = 'Nova poruka';
$lang['No_new_posts_hot'] = 'Nema novih poruka [ Popularne ]';
$lang['New_posts_hot'] = 'Nove poruke [ Popularne ]';
$lang['No_new_posts_locked'] = 'Nema novih poruka [ Zakljuèane ]';
$lang['New_posts_locked'] = 'Nove poruke [ Zakljuèane ]';
$lang['Forum_is_locked'] = 'Forum je zakljuèan';


//
// Login
//
$lang['Enter_password'] = 'Unesite vaše korisnièko ime i lozinku za pristup';
$lang['Login'] = 'Pristupi';
$lang['Logout'] = 'Odjavi se';

$lang['Forgotten_password'] = 'Zaboravio sam lozinku';

$lang['Log_me_in'] = 'Pristupi automatski pri svakoj posjeti';

$lang['Error_login'] = 'Unijeli ste pogrešno ili neaktivirano korisnièko ime ili pogrešnu lozinku';


//
// Index page
//
$lang['Index'] = 'Indeks';
$lang['No_Posts'] = 'Nema poruka';
$lang['No_forums'] = 'Ovaj board nema nijedan forum';

$lang['Private_Message'] = 'Privatna Poruka';
$lang['Private_Messages'] = 'Privatne Poruke';
$lang['Who_is_Online'] = 'Tko je trenutno na forumu';

$lang['Mark_all_forums'] = 'Oznaèi sve forume kao proèitane';
$lang['Forums_marked_read'] = 'Svi forumi su oznaèeni kao proèitani';


//
// Viewforum
//
$lang['View_forum'] = 'Pregledaj Forum';

$lang['Forum_not_exist'] = 'Forum koji ste izabrali ne postoji';
$lang['Reached_on_error'] = 'Greška kojom ste došli do ove stranice';

$lang['Display_topics'] = 'Pokaži teme iz prijašnjih';
$lang['All_Topics'] = 'Sve teme';

$lang['Topic_Announcement'] = '<b>Obavijest:</b>';
$lang['Topic_Sticky'] = '<b>Ljepljiva:</b>';
$lang['Topic_Moved'] = '<b>Pomaknuta:</b>';
$lang['Topic_Poll'] = '<b>[ Glasanje ]</b>';

$lang['Mark_all_topics'] = 'Oznaèi sve teme kao proèitane';
$lang['Topics_marked_read'] = 'Tema ovog foruma je oznaèena kao proèitana';

$lang['Rules_post_can'] = '<b>Možete</b> pisati nove teme u ovom forumu';
$lang['Rules_post_cannot'] = '<b>Ne možete</b> pisati nove teme u ovom forumu';
$lang['Rules_reply_can'] = '<b>Možete</b> odgovarati na teme u ovom forumu';
$lang['Rules_reply_cannot'] = '<b>Ne možete</b> odgovarati na teme u ovom forumu';
$lang['Rules_edit_can'] = '<b>Možete</b> mijenjati vaše poruke u ovom forumu';
$lang['Rules_edit_cannot'] = '<b>Ne možete</b> mijenjati vaše poruke u ovom forumu';
$lang['Rules_delete_can'] = '<b>Možete</b> brisati vaše poruke u ovom forumu';
$lang['Rules_delete_cannot'] = '<b>Ne možete</b> brisati vaše poruke u ovom forumu';
$lang['Rules_vote_can'] = '<b>Možete</b> glasati u ovom forumu';
$lang['Rules_vote_cannot'] = '<b>Ne možete</b> glasati u ovom forumu';
$lang['Rules_moderate'] = '<b>Možete</b> %suredivati ovaj forum%s'; // %s replaced by a href links, do not remove!

$lang['No_topics_post_one'] = 'Nema poruka u ovom forumu<br />
Klikni na <b>Otvori novu temu</b> link na ovoj stranici za napisati poruku';


//
// Viewtopic
//
$lang['View_topic'] = 'Pregledaj teme';

$lang['Guest'] = 'Gost';
$lang['Post_subject'] = 'Naslov';
$lang['View_next_topic'] = 'Vidi sljedeæu temu';
$lang['View_previous_topic'] = 'Vidi prethodnu temu';
$lang['Submit_vote'] = 'Glasaj';
$lang['View_results'] = 'Vidi rezultate';

$lang['No_newer_topics'] = 'Nema novih tema u ovom forumu';
$lang['No_older_topics'] = 'Nema starih tema u ovom forumu';
$lang['Topic_post_not_exist'] = 'Tema ili poruka koju ste tražili ne postoji';
$lang['No_posts_topic'] = 'Nema poruka u ovoj temi';

$lang['Display_posts'] = 'Prikaži poruke iz posljednjih';
$lang['All_Posts'] = 'Sve Poruke';
$lang['Newest_First'] = 'Prvo Najnovije';
$lang['Oldest_First'] = 'Prvo Najstarije';

$lang['Back_to_top'] = 'Povratak na vrh';

$lang['Read_profile'] = 'Prikaži profil korisnika';
$lang['Send_email'] = 'Pošalji email';
$lang['Visit_website'] = 'Posjeti web stranice autora';
$lang['ICQ_status'] = 'ICQ status';
$lang['Edit_delete_post'] = 'Izmijeni/Izbriši poruku';
$lang['View_IP'] = 'Pogledaj IP autora';
$lang['Delete_post'] = 'Obriši ovu poruku';

$lang['wrote'] = ':'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Citat'; // comes before bbcode quote output.
$lang['Code'] = 'Kod'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Posljednji izmijenio %s dana %s, izmijenjeno ukupno %d puta'; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = 'Posljednji put izmijenio %s dana %s, izmijenio ukupno %d puta'; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = 'Zakljuèaj ovu temu';
$lang['Unlock_topic'] = 'Otkljuèaj ovu temu';
$lang['Move_topic'] = 'Pomakni ovu temu';
$lang['Delete_topic'] = 'Obriši ovu temu';
$lang['Split_topic'] = 'Podijeli ovu temu';

$lang['Stop_watching_topic'] = 'Iskljuèi nadgledanje za ovu temu';
$lang['Start_watching_topic'] = 'Nadgledaj ovu temu za odgovore';
$lang['No_longer_watching'] = 'Više ne nadgledate ovu temu';
$lang['You_are_watching'] = 'Od sada nadgledate ovu temu';

$lang['Total_votes'] = 'Ukupno Glasova';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Tijelo poruke';
$lang['Topic_review'] = 'Prikaz teme';

$lang['No_post_mode'] = 'Nije odreðen naèin za pisanje poruke'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Otvori novu temu';
$lang['Post_a_reply'] = 'Odgovori';
$lang['Post_topic_as'] = 'Odgovori na temu kao';
$lang['Edit_Post'] = 'Izmijeni poruku';
$lang['Options'] = 'Opcije';

$lang['Post_Announcement'] = 'Obavijest';
$lang['Post_Sticky'] = 'Ljepljiva';
$lang['Post_Normal'] = 'Normalna';

$lang['Confirm_delete'] = 'Da li sigurno želite izbrisati ovu poruku?';
$lang['Confirm_delete_poll'] = 'Da li sigurno želite izbrisati ovo glasanje?';

$lang['Flood_Error'] = 'Ne možete napisati poruku odmah poslije vaše posljednje, pokušajte ponovno malo kasnije';
$lang['Empty_subject'] = 'Morate odrediti naslov kada postavljate novu temu';
$lang['Empty_message'] = 'Morate upisati poruku';
$lang['Forum_locked'] = 'Ovaj forum je zakljuèan i ne možete pisati, odgovoriti ili mijenjati teme';
$lang['Topic_locked'] = 'Ovaj forum je zakljuèan i ne možete mijenjati teme ili odgovarati';
$lang['No_post_id'] = 'Niste izabrali poruku';
$lang['No_topic_id'] = 'Morate izabrati temu da bi odgovorili';
$lang['No_valid_mode'] = 'Možete samo pisati, mijenjati ili citirati poruke, vratite se i pokušajte ponovo';
$lang['No_such_post'] = 'Ne postoji takva poruka';
$lang['Edit_own_posts'] = 'Izmijeni vlastite poruke';
$lang['Delete_own_posts'] = 'Izbriši vlastite poruke';
$lang['Cannot_delete_replied'] = 'Nije moguæe izbrisati poruku na koju je odgovoreno';
$lang['Cannot_delete_poll'] = 'Nije moguæe izbrisati glasanje';
$lang['Empty_poll_title'] = 'Morate unijeti naslov za glasanje';
$lang['To_few_poll_options'] = 'Morate upisati nekoliko opcija za glasanje';
$lang['To_many_poll_options'] = 'Previše opcija za glasanje';
$lang['Post_has_no_poll'] = 'Poruka nema glasanje';
$lang['Already_voted'] = 'Veæ glasano';
$lang['No_vote_option'] = 'Nema opcije za glasanje';

$lang['Add_poll'] = 'Dodaj glasanje';
$lang['Add_poll_explain'] = 'Dodaj objašnjenje glasanja';
$lang['Poll_question'] = 'Glasaèko pitanje';
$lang['Poll_option'] = 'Glasaèka opcija';
$lang['Add_option'] = 'Dodaj opciju';
$lang['Update'] = 'Osviježi';
$lang['Delete'] = 'Obriši';
$lang['Poll_for'] = 'Glasaj za';
$lang['Days'] = 'dana'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = 'Glasaj za objašnjenje';
$lang['Delete_poll'] = 'Briši glas';

$lang['Disable_HTML_post'] = 'Iskljuèi HTML u poruci';
$lang['Disable_BBCode_post'] = 'Iskljuèi BBCode u poruci';
$lang['Disable_Smilies_post'] = 'Iskljuèi smajlije u poruci';

$lang['HTML_is_ON'] = 'HTML je ukljuèen';
$lang['HTML_is_OFF'] = 'HTML je iskljuèen';
$lang['BBCode_is_ON'] = '%sBBCode%s je ukljuèen'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s je iskljuèen';
$lang['Smilies_are_ON'] = 'Smiley-ji su ukljuèeni';
$lang['Smilies_are_OFF'] = 'Smiley-ji su iskljuèeni';

$lang['Attach_signature'] = 'Zalijepi potpis';
$lang['Notify'] = 'Obavijesti me';
$lang['Delete_post'] = 'Obriši ovu poruku';

$lang['Stored'] = 'Vaša poruka je upisana';
$lang['Deleted'] = 'Vaša poruka je izbrisana';
$lang['Poll_delete'] = 'Izbriši glasanje';
$lang['Vote_cast'] = 'Glasali ste';

$lang['Topic_reply_notification'] = 'Obavijesti me kad neko odgovori na temu';

$lang['bbcode_b_help'] = 'Podebljan(bold) tekst: [b]tekst[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Kurziv(italic) tekst: [i]tekst[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Podvuèen(underline) tekst: [u]tekst[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Citat: [quote]tekst[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Prikaz koda: [code]code[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Lista: [list]tekst[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Poreðana lista: [list=]tekst[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Ubacivanje slike: [img]http://url_slike[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Ubacivanje URLa: [url]http://url[/url] ili [url=http://url]URL tekst[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Zatvori sve otvorene BBCode tagove';
$lang['bbcode_s_help'] = 'Boja fonta: [color=red]tekst[/color]  Napomena: možete da koristite i color=#FF0000';
$lang['bbcode_f_help'] = 'Velièina fonta: [size=x-small]mali tekst[/size]';

$lang['Emoticons'] = 'Smiley-i';
$lang['More_emoticons'] = 'Još smiley-a';

$lang['Font_color'] = 'Boja fonta';
$lang['color_default'] = 'Standardna';
$lang['color_dark_red'] = 'Tamno crvena';
$lang['color_red'] = 'Crvena';
$lang['color_orange'] = 'Narandžasta';
$lang['color_brown'] = 'Smeða';
$lang['color_yellow'] = 'Žuta';
$lang['color_green'] = 'Zelena';
$lang['color_olive'] = 'Maslinasto zeleno';
$lang['color_cyan'] = 'zeleno plava';
$lang['color_blue'] = 'Plava';
$lang['color_dark_blue'] = 'Tamno plava';
$lang['color_indigo'] = 'Indigo';
$lang['color_violet'] = 'Ljubièasta';
$lang['color_white'] = 'Bijela';
$lang['color_black'] = 'Crna';

$lang['Font_size'] = 'Velièina fonta';
$lang['font_tiny'] = 'Siæušni';
$lang['font_small'] = 'Mali';
$lang['font_normal'] = 'Normalan';
$lang['font_large'] = 'Veliki';
$lang['font_huge'] = 'Ogromni';

$lang['Close_Tags'] = 'Zatvori tagove';
$lang['Styles_tip'] = 'Da li znate: Stilovi se mogu lako dodati na izabrani tekst';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Privatne poruke';

$lang['Login_check_pm'] = 'Provjeri privatne poruke';
$lang['New_pms'] = 'Broj novih poruka:<b>%d</b>'; // You have 2 new messages
$lang['New_pm'] = 'Imate <b>%d</b> novu poruku'; // You have 1 new message
$lang['No_new_pm'] = 'Nemate novih poruka';
$lang['Unread_pms'] = 'Imate %d neproèitane poruka';
$lang['Unread_pm'] = 'Imate %d neproèitanu poruku';
$lang['No_unread_pm'] = 'Nemate neproèitanih poruka';
$lang['You_new_pm'] = 'Nova privatna poruka vas èeka u sanduèiæu';
$lang['You_new_pms'] = 'Nove privatne poruke vas èekaju u sanduèiæu';
$lang['You_no_new_pm'] = 'Nemate nove privatne poruke';

$lang['Unread_message'] = 'Neproèitana poruka';
$lang['Read_message'] = 'Proèitana poruka';

$lang['Read_pm'] = 'Proèitaj privatnu poruku';
$lang['Post_new_pm'] = 'Pošalji novu privatnu poruku';
$lang['Post_reply_pm'] = 'Odgovori na privatnu poruku';
$lang['Post_quote_pm'] = 'Citiraj privatnu poruku';
$lang['Edit_pm'] = 'Izmijeni privatnu poruku';

$lang['Inbox'] = 'Sanduèiæ';
$lang['Outbox'] = 'Za slanje';
$lang['Savebox'] = 'Snimljeno';
$lang['Sentbox'] = 'Poslano';
$lang['Flag'] = 'Zastavica';
$lang['Subject'] = 'Naslov';
$lang['From'] = 'Od';
$lang['To'] = 'Za';
$lang['Date'] = 'Datum';
$lang['Mark'] = 'Obilježi';
$lang['Sent'] = 'Poslano';
$lang['Saved'] = 'Snimljeno';
$lang['Delete_marked'] = 'Izbriši obilježeno';
$lang['Delete_all'] = 'Izbriši sve';
$lang['Save_marked'] = 'Snimi obilježene';
$lang['Save_message'] = 'Snimi poruku';
$lang['Delete_message'] = 'Obriši poruku';

$lang['Display_messages'] = 'Prikaži poruke u zadnjih'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Sve poruke';

$lang['No_messages_folder'] = 'Nema poruka u ovom direktoriju';

$lang['PM_disabled'] = 'Privatne poruke iskljuèene';
$lang['Cannot_send_privmsg'] = 'Ne možete slati privatne poruke';
$lang['No_to_user'] = 'Morate upisati korisnièko ime da pošaljete ovu poruku';
$lang['No_such_user'] = 'Korisnik ne postoji';

$lang['Disable_HTML_pm'] = 'Iskljuèi HTML u privatnim porukama';
$lang['Disable_BBCode_pm'] = 'Iskljuèi BBCode u privatnim porukama';
$lang['Disable_Smilies_pm'] = 'Iskljuèi smiley-je u privatnim porukama';

$lang['Message_sent'] = 'Poruka poslana';

$lang['Click_return_inbox'] = 'Klikni %sovdje%s za povratak u sanduèiæ';
$lang['Click_return_index'] = 'Klikni %sovdje%s za povratak na indeks';

$lang['Send_a_new_message'] = 'Pošalji novu privatnu poruku';
$lang['Send_a_reply'] = 'Odgovori na privatnu poruku';
$lang['Edit_message'] = 'Izmijeni privatnu poruku';

$lang['Notification_subject'] = 'Nova privatna poruka je stigla';

$lang['Find_username'] = 'Naði korisnièko ime';
$lang['Find'] = 'Naði';
$lang['No_match'] = 'Ništa nije naðeno';

$lang['No_post_id'] = 'Nije upisan ID poruke';
$lang['No_such_folder'] = 'Ne postoji takav direktorij';
$lang['No_folder'] = 'Nije upisan direktorij';

$lang['Mark_all'] = 'Obilježi sve';
$lang['Unmark_all'] = 'Poništi sve';

$lang['Confirm_delete_pm'] = 'Da li sigurno želite obrisati ovu poruku?';
$lang['Confirm_delete_pms'] = 'Da li sigurno želite obrisati ove poruke?';

$lang['Inbox_size'] = 'Vaš sanduèiæ je %d%% pun'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Vaš direktorij za slanje je %d%% pun';
$lang['Savebox_size'] = 'Vaš direktorij snimljenih poruka je %d%% pun';

$lang['Click_view_privmsg'] = 'Klikni %sovdje%s za ulazak u sanduèiæ';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Pregled profila :: %s'; // %s is username
$lang['About_user'] = 'Informacije o èlanu %s'; // %s is username

$lang['Preferences'] = 'Opcije';
$lang['Items_required'] = 'Stavke obilježene sa * (zvjezdicom) su obavezne';
$lang['Registration_info'] = 'Informacije o registraciji';
$lang['Profile_info'] = 'Informacije o profilu';
$lang['Profile_info_warn'] = 'Ova informacija æe biti javno dostupna svima';
$lang['Avatar_panel'] = 'Kontrolni panel avatara';
$lang['Avatar_gallery'] = 'Galerija avatara';

$lang['Website'] = 'Web site';
$lang['Location'] = 'Lokacija';
$lang['Contact'] = 'Kontakt';
$lang['Email_address'] = 'Email adresa';
$lang['Email'] = 'Email';
$lang['Send_private_message'] = 'Pošalji privatnu poruku';
$lang['Hidden_email'] = '[ Sakriven ]';
$lang['Search_user_posts'] = 'Pretraži poruke ovog èlana';
$lang['Interests'] = 'Interesi';
$lang['Occupation'] = 'Zanimanje';
$lang['Poster_rank'] = 'Rang autora';

$lang['Total_posts'] = 'Ukupno poruka';
$lang['User_post_pct_stats'] = '%.2f%% od ukupnog broja'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f poruka na dan'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Pronaði sve poruke autora %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'Žao nam je, ali èlan sa tim imenom ne postoji';
$lang['Wrong_Profile'] = 'Ne možete mijenjati profil koji nije vaš.';

$lang['Only_one_avatar'] = 'Samo jedna vrsta avatara može biti izabrana';
$lang['File_no_data'] = 'Datoteka na adresi koju ste upisali ne sadrži nikakve podatke';
$lang['No_connection_URL'] = 'Nije moguæe ostvariti vezu sa adresom koju ste upisali';
$lang['Incomplete_URL'] = 'Adresa koju ste upisali nije kompletna';
$lang['Wrong_remote_avatar_format'] = 'Adresa udaljenog avatara nije toèna';
$lang['No_send_account_inactive'] = 'Žao nam je, ali vaša šifra nije dostupna jer vaš nalog nije aktivan. Kontaktirajte admina foruma za još informacija';

$lang['Always_smile'] = 'Uvijek ukljuèi smajlije';
$lang['Always_html'] = 'Uvijek ukljuèi HTML';
$lang['Always_bbcode'] = 'Uvijek ukljuèi BBCode';
$lang['Always_add_sig'] = 'Uvijek dodaj moj potpis';
$lang['Always_notify'] = 'Uvijek me obavesti na odgovore';
$lang['Always_notify_explain'] = 'Email æe vam biti poslan svaki put kada netko odgovori na temu u kojoj ste vi pisali. Ovo može biti promijenjeno prilikom svakog pisanja.';

$lang['Board_style'] = 'Stil (izgled) foruma';
$lang['Board_lang'] = 'Jezik foruma';
$lang['No_themes'] = 'Ne postoji tema u bazi podataka';
$lang['Timezone'] = 'Vremenska zona';
$lang['Date_format'] = 'Format datuma';
$lang['Date_format_explain'] = 'Korištena sintaksa identièna je PHP <a href=\'http://www.php.net/date\' target=\'_other\'>date()</a> funkciji';
$lang['Signature'] = 'Potpis';
$lang['Signature_explain'] = 'Ovo je kratak tekst koji možete dodati vašim porukama. Ogranièenje dužine je na %d slova';
$lang['Public_view_email'] = 'Uvijek prikaži moj email';

$lang['Current_password'] = 'Trenutna lozinka';
$lang['New_password'] = 'Nova lozinka';
$lang['Confirm_password'] = 'Potvrdi lozinku';
$lang['Confirm_password_explain'] = 'Morate potvrditi vašu trenutnu lozinku ukoliko ju želite promijeniti, ili ako želite promijeniti vašu email adresu';
$lang['password_if_changed'] = 'Samo trebate da upisati lozinku ukoliko ju želite promeniti';
$lang['password_confirm_if_changed'] = 'Samo trebate potvrditi vašu lozinku ako ste je gore promijenili';

$lang['Avatar'] = 'Avatar';
$lang['Avatar_explain'] = 'Prikazuje malu sliku ispod vaših detalja u poruci. Samo jedna slika može biti prikazana istovremeno. Širina slike ne smije biti veæa od %d piksela, a visina ne smije biti veæa od %d piksela. Maksimalna velièina datoteke je %dKB.';
$lang['Upload_Avatar_file'] = 'Upload avatara sa vašeg raèunala';
$lang['Upload_Avatar_URL'] = 'Upload avatara sa URL adrese';
$lang['Upload_Avatar_URL_explain'] = 'Upišite URL adresu koja sadrži sliku avatara. Biti æe prenesena na ovaj site.';
$lang['Pick_local_Avatar'] = 'Izaberite avatar iz galerije';
$lang['Link_remote_Avatar'] = 'Link prema avataru na drugoj stranici';
$lang['Link_remote_Avatar_explain'] = 'Upišite URL adresu koja sadrži sliku avatara koji hoæete linkati.';
$lang['Avatar_URL'] = 'URL slike avatara';
$lang['Select_from_gallery'] = 'Izaberite avatar iz galerije';
$lang['View_avatar_gallery'] = 'Prikaži galeriju';

$lang['Select_avatar'] = 'Izaberi avatar';
$lang['Return_profile'] = 'Poništi izbor';
$lang['Select_category'] = 'Izberi kategoriju';

$lang['Delete_Image'] = 'Obriši sliku';
$lang['Current_Image'] = 'Trenutna slika';

$lang['Notify_on_privmsg'] = 'Obavijesti me kada dobijem novu privatnu poruku';
$lang['Popup_on_privmsg'] = 'Otvori novi prozor kada dobijem novu privatnu poruku';
$lang['Popup_on_privmsg_explain'] = 'Otvorit æe se novi pop-up prozor da vas obavijesti kada primite novu privatnu poruku';
$lang['Hide_user'] = 'Sakrij se od "Tko je na forumu?" ';

$lang['Profile_updated'] = 'Vaš profil je ažuriran';
$lang['Profile_updated_inactive'] = 'Vaš profil je ažuriran, iako vaš nalog nije aktivan. Provjerite vaš email da saznate kako reaktivirati vaš nalog';

$lang['Password_mismatch'] = 'Lozinke koje ste upisali nisu jednake';
$lang['Current_password_mismatch'] = 'Trenutna lozinka koju ste upisali nije ista kao lozinka u bazi podataka';
$lang['Password_long'] = 'Vaša lozinka ne smije biti duža od 32 znaka';
$lang['Username_taken'] = 'Žao nam je, ali ovo korisnièko ime je zauzeto';
$lang['Username_invalid'] = 'Žao nam je, ali ovo korisnièko ime koristi neupotrebljive znakove, kao što je \'';
$lang['Username_disallowed'] = 'Žao nam je, ali ovo korisnièko ime nije dopušteno';
$lang['Email_taken'] = 'Žao nam je, ali tu email adresu je iskoristio drugi èlan za registraciju';
$lang['Email_banned'] = 'Žao nam je, ali ova email adresa je zabranjena';
$lang['Email_invalid'] = 'Žao nam je, ali ova email adresa nije ispravna';
$lang['Signature_too_long'] = 'Vaš potpis je predugaèak';
$lang['Fields_empty'] = 'Morate ispuniti zahtjevana polja';
$lang['Avatar_filetype'] = 'Ekstenzija datoteke mora biti .jpg, .gif ili .png';
$lang['Avatar_filesize'] = 'Velièina datoteke avatara ne smije biti veæa od %d KB'; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = 'Avatar mora biti manji od %d piksela širine i %d piksela visine';

$lang['Welcome_subject'] = 'Dobrodošli na %s Forum'; // Welcome to my.com forums
$lang['New_account_subject'] = 'Novi korisnièki nalog';
$lang['Account_activated_subject'] = 'Nalog aktiviran';

$lang['Account_added'] = 'Hvala vam što ste se registrirali, vaš nalog je kreiran. Sada se možete prijaviti sa vašim korisnièkim imenom i lozinkom.';
$lang['Account_inactive'] = 'Vaš nalog je kreiran. Ipak, ovaj forum zahtjeva aktivaciju naloga. Kljuè za aktivaciju je poslat na email adresu koju ste unijeli prilikom registracije.';
$lang['Account_inactive_admin'] = 'Vaš nalog je kreiran. Ipak, ovaj forum zahtjeva aktivaciju naloga od strane admina. Bit æete obaviješteni kada se aktivira vaš nalog.';
$lang['Account_active'] = 'Vaš nalog je sada aktiviran. Hvala na registraciji.';
$lang['Account_active_admin'] = 'Vaš nalog je aktiviran';
$lang['Reactivate'] = 'Reaktivirajte vaš nalog!';
$lang['Already_activated'] = 'Veæ ste aktivirali vaš nalog';
$lang['COPPA'] = 'Vaš nalog je kreiran, ali treba biti dozvoljen, pogledajte email za detalje.';

$lang['Registration'] = 'Uvijeti registracije';
$lang['Reg_agreement'] = 'Dok admini i urednici ovog foruma nastoje obrisati ili izmijeniti bilo koji dostupni materijal što je brže moguæe, nemoguæe je pregledati svaku poruku. Prema tome, Vi trebate znati da sve poruke poslane na ove forume iskazuju stavove i opredjeljenja autora, nikako admina, urednika ili webmastera (osim, naravno, poruka poslanih osobno od ovih ljudi) i od sada se obavezujete uvijetima.<br /><br />Slažete se da ne šaljete nikakve pogrdne, nepristojne, vulgarne, klevetnièke, odvratne, prijeteæe, seksualno-orjentirane poruke ili bilo kakav materijal koji može narušiti bilo koje od korisnièkih pravila. Postupajuæi drugaèije, možete biti odmah i trajno iskljuèeni (i Vaš internet provider (ISP) æe biti obaviješten). IP adrese svih poruka su saèuvane za pomoæ u slucaju nepridržavanja ovih uvijeta. Slažete se da webmaster, admin i urednici ovog foruma imaju pravo obrisati, izmijeniti, pomaknuti ili zatvoriti svaku temu u svakom trenutku ako to smatraju potrebnim. Kao korisnik slažete se sa svim informacijama koje ste prethodno unijeli i one ce biti saèuvane u bazi podataka. Da ove informacije ne budu objavljene ni jednoj treæoj osobi bez Vašeg dopuštenja webmasteru, adminu i urednicima, oni se ne mogu se držati odgovornima za bilo koji od hakerskih napada koji mogu preuzeti podatke koji mogu dovesti do neprilika.<br /><br />Ovaj forum koristi cookies da bi saèuvao informacije na Vašem raèunalu. Ove cookies datoteke ne sadrže ni jednu od informacija koju ste dosad unijeli, oni služe samo da upotpune bolji izgled stranica. Email adresa se koristi samo za potvrdu vaših podataka registracije i lozinke.<br /><br />Kada kliknete na gumb za registraciju ispod, slažete se i obavezujete se sa svim ovim uslovima.';

$lang['Agree_under_13'] = 'Slažem se sa ovim uvijetima i ja sam <b>mlaði/a</b> od 13 godina';
$lang['Agree_over_13'] = 'Slažem se sa ovim uvijetima i ja sam <b>stariji/a od</b> ili imam taèno 13 godina';
$lang['Agree_not'] = 'Ne slažem se sa ovim uvijetima';

$lang['Wrong_activation'] = 'Kljuè za aktivaciju koji ste unijeli ne poklapa se sa onim u našoj bazi podataka';
$lang['Send_password'] = 'Pošalji mi novu šifru';
$lang['Password_updated'] = 'Nova šifra je napravljena, provjerite vaš email sa detaljima aktivacije';
$lang['No_email_match'] = 'Email adresa koju ste unijeli ne poklapa se sa onom u vašem nalogu';
$lang['New_password_activation'] = 'Aktivacija nove lozinke';
$lang['Password_activated'] = 'Vaš nalog je reaktiviran. Da se prijavite, koristite lozinku koju ste dobili u emailu';

$lang['Send_email_msg'] = 'Pošalji email';
$lang['No_user_specified'] = 'Nije izabran èlan';
$lang['User_prevent_email'] = 'Ovaj èlan ne želi primati email. Pokušajte sa slanjem privatne poruke';
$lang['User_not_exist'] = 'Takav èlan ne postoji';
$lang['CC_email'] = 'Pošaljite kopiju ovog emaila sebi';
$lang['Email_message_desc'] = 'Ova poruka æe biti poslana kao obièan tekst, nemojte unositi HTML ili BBCode. Povratna adresa za ovu poruku biti æe vaša email adresa.';
$lang['Flood_email_limit'] = 'Ne možete upisati drugi email sada, pokušajte kasnije.';
$lang['Recipient'] = 'Primatelj';
$lang['Email_sent'] = 'Email je poslan';
$lang['Send_email'] = 'Pošalji email';
$lang['Empty_subject_email'] = 'Morate upisati naslov emaila';
$lang['Empty_message_email'] = 'Morate upisati poruku';


//
// Memberslist
//
$lang['Select_sort_method'] = 'Izaberite naèin soritranja';
$lang['Sort'] = 'Sortiraj';
$lang['Sort_Top_Ten'] = 'Glavnih 10 autora';
$lang['Sort_Joined'] = 'Datum registracije';
$lang['Sort_Username'] = 'Korisnièko ime';
$lang['Sort_Location'] = 'Lokacija';
$lang['Sort_Posts'] = 'Ukupne poruke';
$lang['Sort_Email'] = 'Email';
$lang['Sort_Website'] = 'Web site';
$lang['Sort_Ascending'] = 'Rastuæem';
$lang['Sort_Descending'] = 'Opadajuæem';
$lang['Order'] = 'Niz';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Kontrolni panel grupe';
$lang['Group_member_details'] = 'Detalji èlanova';
$lang['Group_member_join'] = 'Pridruži se grupi';

$lang['Group_Information'] = 'Informacije o grupi';
$lang['Group_name'] = 'Ime grupe';
$lang['Group_description'] = 'Opis grupe';
$lang['Group_membership'] = 'Èlanstvo';
$lang['Group_Members'] = 'Èlanovi grupe';
$lang['Group_Moderator'] = 'Urednici grupe';
$lang['Pending_members'] = 'Èlanovi koji èekaju';

$lang['Group_type'] = 'Vrsta grupe';
$lang['Group_open'] = 'Otvorena grupa';
$lang['Group_closed'] = 'Zatvorena grupa';
$lang['Group_hidden'] = 'Skrivena grupa';

$lang['Current_memberships'] = 'Trenutna èlanstva';
$lang['Non_member_groups'] = 'Grupe bez èlanstava';
$lang['Memberships_pending'] = 'èlanstva koja èekaju';

$lang['No_groups_exist'] = 'Ne postoji ni jedna grupa';
$lang['Group_not_exist'] = 'Ta grupa èlanova ne postoji';

$lang['Join_group'] = 'Pridruži se';
$lang['No_group_members'] = 'Ova glupa nema èlanova';
$lang['Group_hidden_members'] = 'Ova grupa je skrivena, ne možete vidjeti èlanstvo';
$lang['No_pending_group_members'] = 'Ova grupa nema èlanova koji èekaju';
$lang['Group_joined'] = 'Uspješno ste se upisali u ovu grupu<br />Biti æete obaviješteni kada vaš upis dopusti urednik grupe';
$lang['Group_request'] = 'Zahtjev za pridruživanje vašoj grupi je napravljen';
$lang['Group_approved'] = 'Vaš zahtjev je dopušten';
$lang['Group_added'] = 'Dodani ste u ovu grupu èlanova';
$lang['Already_member_group'] = 'Veæ ste èlan ove grupe';
$lang['User_is_member_group'] = 'Korisnik je veæ èlan ove grupe';
$lang['Group_type_updated'] = 'Uspješno ažurirana vrsta grupe';

$lang['Could_not_add_user'] = 'Korisnièko ime koje ste izabrali ne postoji';
$lang['Could_not_anon_user'] = 'Ne možete dodati anonimca za èlana';

$lang['Confirm_unsub'] = 'Da li ste sigurni da se želite ispisati iz ove grupe?';
$lang['Confirm_unsub_pending'] = 'Vaš zahtjev za upis u ovu grupu još nije pregledan, da li ste sigurni da se želite ispisati?';

$lang['Unsub_success'] = 'Uspješno ste se ispisali iz ove grupe.';

$lang['Approve_selected'] = 'Dopusti';
$lang['Deny_selected'] = 'Ne dopuštaj';
$lang['Not_logged_in'] = 'Morate biti prijavljeni da se pridružite grupi.';
$lang['Remove_selected'] = 'Obriši iz grupe';
$lang['Add_member'] = 'Dodaj èlana';
$lang['Not_group_moderator'] = 'Vi niste urednik ove grupe, i ne možete izvršiti takve akcije.';

$lang['Login_to_join'] = 'Morate biti prijavljeni da možete mijenjati èlanstvo';
$lang['This_open_group'] = 'Ovo je otvorena grupa, kliknite za slanje zahtjeva za èlanstvo';
$lang['This_closed_group'] = 'Ovo je zatvorena grupa, nije dopušteno èlanstvo';
$lang['This_hidden_group'] = 'Ovo je skrivena grupa, automatsko dodavanje èlanova nije dopušteno';
$lang['Member_this_group'] = 'Vi ste èlan ove grupe';
$lang['Pending_this_group'] = 'Vaš zahtjev za èlanstvo je na èekanju';
$lang['Are_group_moderator'] = 'Vi ste urednik grupe';
$lang['None'] = 'Nema';

$lang['Subscribe'] = 'Upišite se';
$lang['Unsubscribe'] = 'Ispišite se';
$lang['View_Information'] = 'Pogledaj informacije';


//
// Search
//
$lang['Search_query'] = 'Upit za pretraživanje';
$lang['Search_options'] = 'Opcije pretraživanja';

$lang['Search_keywords'] = 'Traži kljuène rijeèi';
$lang['Search_keywords_explain'] = 'Možete koristiti <u>AND</u> da definirate rijeèi koje moraju biti u rezultatu, <u>OR</u> da definirate rijeèi koje mogu biti u rezultatu i <u>NOT</u> da definirate rijeèi koje ne smiju biti u rezultatu. Koristite * (zvjezdicu) za pojedine pogodke.';
$lang['Search_author'] = 'Traži autora';
$lang['Search_author_explain'] = 'Koristite * (zvjezdicu) za pojedine pogodke';

$lang['Search_for_any'] = 'Pretraži svaki izraz ili koristi upit onako kako je unijet';
$lang['Search_for_all'] = 'Pretraži sve izraze';
$lang['Search_title_msg'] = 'Pretraži naslove teme i tekst poruke';
$lang['Search_msg_only'] = 'Pretraži samo tekst poruke';

$lang['Return_first'] = 'Pretraži prvih'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'znakova poruke';

$lang['Search_previous'] = 'Pretraži od prije'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Sortiraj po';
$lang['Sort_Time'] = 'Vremenu postavljanja';
$lang['Sort_Post_Subject'] = 'Naslovu poruke';
$lang['Sort_Topic_Title'] = 'Naslovu teme';
$lang['Sort_Author'] = 'Autoru';
$lang['Sort_Forum'] = 'Forumu';

$lang['Display_results'] = 'Prikaži rezultat kao';
$lang['All_available'] = 'Svi dostupni';
$lang['No_searchable_forums'] = 'Nemate dozvolu za pretraživanjem bilo kojeg foruma na ovom site-u';

$lang['No_search_match'] = 'Ni jedna tema i ni jedna poruka ne sadrži Vaš kriterij.';
$lang['Found_search_match'] = 'Naðen %d pogodak'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'Naðeno %d pogodaka'; // eg. Search found 24 matches

$lang['Close_window'] = 'Zatvori prozor';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Žao nam je, ali samo %s mogu postavljati obavijesti u ovom forumu';
$lang['Sorry_auth_sticky'] = 'Žao nam je ali samo %s mogu postavljati ljepljive poruke u ovom forumu';
$lang['Sorry_auth_read'] = 'Žao nam je, ali samo %s mogu èitati teme u ovom forumu';
$lang['Sorry_auth_post'] = 'Žao nam je, ali samo %s mogu postavljati teme u ovom forumu';
$lang['Sorry_auth_reply'] = 'Žao nam je, ali samo %s mogu odgovarati na teme u ovom forumu';
$lang['Sorry_auth_edit'] = 'Å½ao nam je, ali samo %s mogu mijenjati poruke u ovom forumu';
$lang['Sorry_auth_delete'] = 'Žao nam je, ali samo %s mogu brisati poruke u ovom forumu';
$lang['Sorry_auth_vote'] = 'Žao nam je, ali samo %s mogu glasati u ovom forumu';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>anonimni korisnici</b>';
$lang['Auth_Registered_Users'] = '<b>registrirani èlanovi</b>';
$lang['Auth_Users_granted_access'] = '<b>korisnici sa posebnom dozvolom</b>';
$lang['Auth_Moderators'] = '<b>urednici</b>';
$lang['Auth_Administrators'] = '<b>administratori</b>';

$lang['Not_Moderator'] = 'Vi niste urednik ovog foruma.';
$lang['Not_Authorised'] = 'Nemate ovlasti';

$lang['You_been_banned'] = 'Zabranjem Vam je pristup na ovaj forum<br />Kontaktirajte admina za više informacija';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Trenutno je 0 èlanova i '; // There ae 5 Registered and
$lang['Reg_users_online'] = 'Trenutno je %d èlanova i '; // There ae 5 Registered and
$lang['Reg_user_online'] = 'Trenutno je %d èlan i '; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = '0 skrivenih èlanova na forumu'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d skrivenih èlanova na forumu'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d skriven èlan na forumu'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Broj gostiju: %d'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Nema gostiju.'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'Broj gostiju: %d'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Trenutno nema èlanova na ovom forumu';

$lang['Online_explain'] = 'Ovi podaci su bazirani na èlanovima aktivnim u posljednjih 5 minuta';

$lang['Forum_Location'] = 'Lokacija';
$lang['Last_updated'] = 'Poslednji put ažurirano';

$lang['Forum_index'] = 'Indeks foruma';
$lang['Logging_on'] = 'Prijavljuje se';
$lang['Posting_message'] = 'Piše poruku';
$lang['Searching_forums'] = 'Pretražuje forume';
$lang['Viewing_profile'] = 'Pregled profila';
$lang['Viewing_online'] = 'Pregled - tko je na forumu';
$lang['Viewing_member_list'] = 'Pregled liste èlanova';
$lang['Viewing_priv_msgs'] = 'Pregled privatnih poruka';
$lang['Viewing_FAQ'] = 'Pomoæ';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Kontrolni panel urednika';
$lang['Mod_CP_explain'] = 'Korištenjem formulara ispod možete napraviti masovne operacije urednika na ovom forumu. Možete zakljuèati, otkljuèati, premjestiti ili obrišeti bilo koji broj tema.';

$lang['Select'] = 'Izaberi';
$lang['Delete'] = 'Obriši';
$lang['Move'] = 'Pomakni';
$lang['Lock'] = 'Zakljuèaj';
$lang['Unlock'] = 'Otkljuèaj';

$lang['Topics_Removed'] = 'Izabrane teme su uspješno obrisane iz baze podataka.';
$lang['Topics_Locked'] = 'Izabrane teme su zakljuèane';
$lang['Topics_Moved'] = 'Izabrane teme su premještene';
$lang['Topics_Unlocked'] = 'Izabrane teme su otkljuèane';
$lang['No_Topics_Moved'] = 'Nijedna tema nije premještena';

$lang['Confirm_delete_topic'] = 'Da li sigurno želite obrisati izabrane teme?';
$lang['Confirm_lock_topic'] = 'Da li sigurno želite zakljuèati izabrane teme?';
$lang['Confirm_unlock_topic'] = 'Da li sigurno želite otkljuèati izabrane teme?';
$lang['Confirm_move_topic'] = 'Da li sigurno želite premjestiti izabrane teme?';

$lang['Move_to_forum'] = 'Premjesti u forum';
$lang['Leave_shadow_topic'] = 'Ostavi sjenku teme u starom forumu.';

$lang['Split_Topic'] = 'Podjela teme';
$lang['Split_Topic_explain'] = 'Korištenjem formulara ispod možete podijeliti jednu temu u dvije.';
$lang['Split_title'] = 'Novi naslov teme';
$lang['Split_forum'] = 'Forum za novu temu';
$lang['Split_posts'] = 'Podijeli izabrane poruke';
$lang['Split_after'] = 'Podijeli OD izabranih poruka';
$lang['Topic_split'] = 'Izabrana tema je uspešno podjeljena';

$lang['Too_many_error'] = 'Izabrali ste previše poruka. Možete izabrati samo jednu poruku da po njoj podijelite temu!';

$lang['None_selected'] = 'Niste izabrali ni jednu temu za ovu operaciju. Vratite se nazad i izaberite bar jednu.';
$lang['New_forum'] = 'Novi forum';

$lang['This_posts_IP'] = 'IP za ovu poruku';
$lang['Other_IP_this_user'] = 'Ostale IP adrese sa kojih je ovaj èlan pisao';
$lang['Users_this_IP'] = 'èlanovi koji pišu sa ovog IPa';
$lang['IP_info'] = 'IP Informacija';
$lang['Lookup_IP'] = 'Pogledaj IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Sva vremena su %s'; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = 'GMT - 12 sati';
$lang['-11'] = 'GMT - 11 sati';
$lang['-10'] = 'GMT - 10 sati';
$lang['-9'] = 'GMT - 9 sati';
$lang['-8'] = 'GMT - 8 sati';
$lang['-7'] = 'GMT - 7 sati';
$lang['-6'] = 'GMT - 6 sati';
$lang['-5'] = 'GMT - 5 sati';
$lang['-4'] = 'GMT - 4 sata';
$lang['-3.5'] = 'GMT - 3.5 sata';
$lang['-3'] = 'GMT - 3 sata';
$lang['-2'] = 'GMT - 2 sata';
$lang['-1'] = 'GMT - 1 sat';
$lang['0'] = 'GMT';
$lang['1'] = 'GMT + 1 sat';
$lang['2'] = 'GMT + 2 sata';
$lang['3'] = 'GMT + 3 sata';
$lang['3.5'] = 'GMT + 3.5 sati';
$lang['4'] = 'GMT + 4 sata';
$lang['4.5'] = 'GMT + 4.5 sata';
$lang['5'] = 'GMT + 5 sati';
$lang['5.5'] = 'GMT + 5.5 sati';
$lang['6'] = 'GMT + 6 sati';
$lang['6.5'] = 'GMT + 6.5 sati';
$lang['7'] = 'GMT + 7 sati';
$lang['8'] = 'GMT + 8 sati';
$lang['9'] = 'GMT + 9 sati';
$lang['9.5'] = 'GMT + 9.5 sati';
$lang['10'] = 'GMT + 10 sati';
$lang['11'] = 'GMT + 11 sati';
$lang['12'] = 'GMT + 12 sati';

// These are displayed in the timezone select box
$lang['tz']['-12'] = 'GMT - 12 sati';
$lang['tz']['-11'] = 'GMT - 11 sati';
$lang['tz']['-10'] = 'GMT - 10 sati';
$lang['tz']['-9'] = 'GMT - 9 sati';
$lang['tz']['-8'] = 'GMT - 8 sati';
$lang['tz']['-7'] = 'GMT - 7 sati';
$lang['tz']['-6'] = 'GMT - 6 sati';
$lang['tz']['-5'] = 'GMT - 5 sati';
$lang['tz']['-4'] = 'GMT - 4 sata';
$lang['tz']['-3.5'] = 'GMT - 3.5 sata';
$lang['tz']['-3'] = 'GMT - 3 sata';
$lang['tz']['-2'] = 'GMT - 2 sata';
$lang['tz']['-1'] = 'GMT - 1 sata';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT + 1 sat';
$lang['tz']['2'] = 'GMT + 2 sata';
$lang['tz']['3'] = 'GMT + 3 sata';
$lang['tz']['3.5'] = 'GMT + 3.5 sata';
$lang['tz']['4'] = 'GMT + 4 sata';
$lang['tz']['4.5'] = 'GMT + 4.5 sati';
$lang['tz']['5'] = 'GMT + 5 sati';
$lang['tz']['5.5'] = 'GMT + 5.5 sati';
$lang['tz']['6'] = 'GMT + 6 sati';
$lang['tz']['6.5'] = 'GMT + 6.5 sati';
$lang['tz']['7'] = 'GMT + 7 sati';
$lang['tz']['8'] = 'GMT + 8 sati';
$lang['tz']['9'] = 'GMT + 9 sati';
$lang['tz']['9.5'] = 'GMT + 9.5 sati';
$lang['tz']['10'] = 'GMT + 10 sati';
$lang['tz']['11'] = 'GMT + 11 sati';
$lang['tz']['12'] = 'GMT + 12 sati';

$lang['datetime']['Sunday'] = 'Nedjelja';
$lang['datetime']['Monday'] = 'Ponedjeljak';
$lang['datetime']['Tuesday'] = 'Utorak';
$lang['datetime']['Wednesday'] = 'Srijeda';
$lang['datetime']['Thursday'] = 'Èetvrtak';
$lang['datetime']['Friday'] = 'Petak';
$lang['datetime']['Saturday'] = 'Subota';
$lang['datetime']['Sun'] = 'Ned';
$lang['datetime']['Mon'] = 'Pon';
$lang['datetime']['Tue'] = 'Uto';
$lang['datetime']['Wed'] = 'Sri';
$lang['datetime']['Thu'] = 'Èet';
$lang['datetime']['Fri'] = 'Pet';
$lang['datetime']['Sat'] = 'Sub';
$lang['datetime']['January'] = 'Sijeèanj';
$lang['datetime']['February'] = 'Veljaèa';
$lang['datetime']['March'] = 'Ožujak';
$lang['datetime']['April'] = 'Travanj';
$lang['datetime']['May'] = 'Svibanj';
$lang['datetime']['June'] = 'Lipanj';
$lang['datetime']['July'] = 'Srpanj';
$lang['datetime']['August'] = 'Kolovoz';
$lang['datetime']['September'] = 'Rujan';
$lang['datetime']['October'] = 'Listopad';
$lang['datetime']['November'] = 'Studeni';
$lang['datetime']['December'] = 'Prosinac';
$lang['datetime']['Jan'] = 'Sij';
$lang['datetime']['Feb'] = 'Velj';
$lang['datetime']['Mar'] = 'Ožu';
$lang['datetime']['Apr'] = 'Tra';
$lang['datetime']['May'] = 'Svi';
$lang['datetime']['Jun'] = 'Lip';
$lang['datetime']['Jul'] = 'Srp';
$lang['datetime']['Aug'] = 'Kol';
$lang['datetime']['Sep'] = 'Ruj';
$lang['datetime']['Oct'] = 'Lis';
$lang['datetime']['Nov'] = 'Stu';
$lang['datetime']['Dec'] = 'Pro';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Informacije';
$lang['Critical_Information'] = 'Kritiène informacije';

$lang['General_Error'] = 'Generalna greška';
$lang['Critical_Error'] = 'Kritièna greška';
$lang['An_error_occured'] = 'Nastupila je greška';
$lang['A_critical_error'] = 'Nastupila je kritièna greška';

//
// Ako nisam sad umro,nikad neæu ;=))))
// -------------------------------------------------

?>
