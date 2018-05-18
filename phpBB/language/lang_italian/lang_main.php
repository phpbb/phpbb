<?php
/***************************************************************************
 *                            lang_main.php [Italian]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_main.php,v 1.1 2012/10/21 00:03:48 orynider Exp $
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

/* CONTRIBUTORS
	2005-03-15	phpBB.it (info@phpbb.it)
		Fixed many minor grammatical mistakes
*/

//
// The format of this file is ---> $lang['message'] = 'text';
//
// You should also try to set a locale and a character encoding (plus direction). The encoding and direction
// will be sent to the template. The locale may or may not work, it's dependent on OS support and the syntax
// varies ... give it your best guess!
//

$lang['ENCODING'] = 'iso-8859-1';
$lang['DIRECTION'] = 'ltr';
$lang['LEFT'] = 'sinistra';
$lang['RIGHT'] = 'destra';
$lang['DATE_FORMAT'] =  'd/m/y H:i'; // This should be changed to the default date format for your language, php date() format

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
$lang['TRANSLATION_INFO'] = 'Traduzione Italiana  V-0.13 <a href="http://www.phpbb.it" class="copyright" target="_blank">phpBB.it</a>';
$lang['TRANSLATION'] = 'Traduzione Italiana V-0.13 <a href="http://www.phpbb.it" class="copyright" target="_blank">phpBB.it</a>';

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = 'Forum';
$lang['Category'] = 'Categoria';
$lang['Topic'] = 'Argomento';
$lang['Topics'] = 'Argomenti';
$lang['Replies'] = 'Risposte';
$lang['Views'] = 'Consultazioni';
$lang['Post'] = 'Messaggio';
$lang['Posts'] = 'Messaggi';
$lang['Posted'] = 'Inviato';
$lang['Username'] = 'Username';
$lang['Password'] = 'Password';
$lang['Email'] = 'Email';
$lang['Poster'] = 'Scritto da';
$lang['Author'] = 'Autore';
$lang['Time'] = 'Data';
$lang['Hours'] = 'Ore';
$lang['Message'] = 'Messaggio';

$lang['1_Day'] = '1 Giorno';
$lang['7_Days'] = '7 Giorni';
$lang['2_Weeks'] = '2 Settimane';
$lang['1_Month'] = '1 Mese';
$lang['3_Months'] = '3 Mesi';
$lang['6_Months'] = '6 Mesi';
$lang['1_Year'] = '1 Anno';

$lang['Go'] = 'Vai';
$lang['Jump_to'] = 'Vai a';
$lang['Submit'] = 'Invia';
$lang['Reset'] = 'Azzera';
$lang['Cancel'] = 'Cancella';
$lang['Preview'] = 'Anteprima';
$lang['Confirm'] = 'Conferma';
$lang['Spellcheck'] = 'Controllo Ortografico';
$lang['Yes'] = 'Si';
$lang['No'] = 'No';
$lang['Enabled'] = 'Abilitato';
$lang['Disabled'] = 'Disabilitato';
$lang['Error'] = 'Errore';

$lang['Next'] = 'Successivo';
$lang['Previous'] = 'Precedente';
$lang['Goto_page'] = 'Vai a';
$lang['Joined'] = 'Registrato';
$lang['IP_Address'] = 'Indirizzo IP';

$lang['Select_forum'] = 'Seleziona Forum';
$lang['View_latest_post'] = 'Leggi gli ultimi Messaggi';
$lang['View_newest_post'] = 'Leggi i nuovi Messaggi';
$lang['Page_of'] = 'Pagina <b>%d</b> di <b>%d</b>'; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = 'ICQ';
$lang['AIM'] = 'Indirizzo AIM';
$lang['MSNM'] = 'MSN Messenger';
$lang['YIM'] = 'Yahoo Messenger';

$lang['Forum_Index'] = 'Indice del Forum';  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = 'Nuovo Argomento';
$lang['Reply_to_topic'] = 'Rispondi';
$lang['Reply_with_quote'] = 'Rispondi Citando';

$lang['Click_return_topic'] = '%sTorna a Argomento%s'; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = '%sRitenta Login%s';
$lang['Click_return_forum'] = '%sTorna a Forum%s';
$lang['Click_view_message'] = '%sGuarda il tuo Messaggio%s';
$lang['Click_return_modcp'] = '%sPannello di Controllo Moderatori%s';
$lang['Click_return_group'] = '%sTorna a Informazioni Gruppo%s';

$lang['Admin_panel'] = 'Amministrazione';

$lang['Board_disable'] = 'Spiacenti ma il Forum al momento non è disponibile, prova più tardi.';


//
// Global Header strings
//
$lang['Registered_users'] = 'Utenti Registrati:';
$lang['Browsing_forum'] = 'Utenti presenti in questo Forum:';
$lang['Online_users_zero_total'] = 'In totale ci sono <b>0</b> Utenti in linea :: ';
$lang['Online_users_total'] = 'In totale ci sono <b>%d</b> Utenti in linea :: ';
$lang['Online_user_total'] = 'In totale c\'è <b>%d</b> Utente in linea :: ';
$lang['Reg_users_zero_total'] = '0 Registrati, ';
$lang['Reg_users_total'] = '%d Registrati, ';
$lang['Reg_user_total'] = '%d Registrato, ';
$lang['Hidden_users_zero_total'] = '0 Nascosti e ';
$lang['Hidden_user_total'] = '%d Nascosto e ';
$lang['Hidden_users_total'] = '%d Nascosti e ';
$lang['Guest_users_zero_total'] = '0 Ospiti';
$lang['Guest_users_total'] = '%d Ospiti';
$lang['Guest_user_total'] = '%d Ospite';
$lang['Record_online_users'] = 'Record Utenti in linea <b>%s</b> in data %s'; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = '%sAmministratore%s';
$lang['Mod_online_color'] = '%sModeratore%s';

$lang['You_last_visit'] = 'La tua ultima visita è stata %s'; // %s replaced by date/time
$lang['Current_time'] = 'La data di oggi è %s'; // %s replaced by time

$lang['Search_new'] = 'Messaggi non Letti';
$lang['Search_your_posts'] = 'I tuoi Messaggi';
$lang['Search_unanswered'] = 'Messaggi senza Risposta';

$lang['Register'] = 'Registrati';
$lang['Profile'] = 'Profilo';
$lang['Edit_profile'] = 'Modifica il tuo profilo';
$lang['Search'] = 'Cerca';
$lang['Memberlist'] = 'Lista Utenti';
$lang['FAQ'] = 'FAQ';
$lang['BBCode_guide'] = 'Guida BBCode';
$lang['Usergroups'] = 'Gruppi';
$lang['Last_Post'] = 'Ultimo Messaggio';
$lang['Moderator'] = 'Moderatore';
$lang['Moderators'] = 'Moderatori';


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = 'Non ci sono Messaggi nel forum'; // Number of posts
$lang['Posted_articles_total'] = 'Ci sono <b>%d</b> Messaggi nel Forum'; // Number of posts
$lang['Posted_article_total'] = 'C\'è <b>%d</b> Messaggio nel Forum'; // Number of posts
$lang['Registered_users_zero_total'] = 'Abbiamo <b>0</b> Utenti Registrati'; // # registered users
$lang['Registered_users_total'] = 'Abbiamo <b>%d</b> Utenti Registrati'; // # registered users
$lang['Registered_user_total'] = 'Abbiamo <b>%d</b> Utente Registrato'; // # registered users
$lang['Newest_user'] = 'Nuovo Utente <b>%s%s%s</b>'; // a href, username, /a 

$lang['No_new_posts_last_visit'] = 'Non ci sono nuovi Messaggi dall\'ultima tua visita';
$lang['No_new_posts'] = 'No Nuovi Messaggi';
$lang['New_posts'] = 'Nuovi Messaggi';
$lang['New_post'] = 'Nuovo Messaggo';
$lang['No_new_posts_hot'] = 'No Nuovi Messaggi [ Popolare ]';
$lang['New_posts_hot'] = 'Nuovi Messaggi [ Popolare ]';
$lang['No_new_posts_locked'] = 'No Nuovi Messaggi [ Chiuso ]';
$lang['New_posts_locked'] = 'Nuovi Messaggi [ Chiuso ]';
$lang['Forum_is_locked'] = 'Il Forum è chiuso';


//
// Login
//
$lang['Enter_password'] = 'Inserisci username e password per entrare.';
$lang['Login'] = 'Login';
$lang['Logout'] = 'Logout';

$lang['Forgotten_password'] = 'Ho dimenticato la password';

$lang['Log_me_in'] = 'Login automatico ad ogni visita';

$lang['Error_login'] = 'I dati inseritti non sono corretti.';


//
// Index page
//
$lang['Index'] = 'Indice';
$lang['No_Posts'] = 'Nessun Messaggio';
$lang['No_forums'] = 'Questo Forum è vuoto';

$lang['Private_Message'] = 'Messaggio Privato';
$lang['Private_Messages'] = 'Messaggi Privati';
$lang['Who_is_Online'] = 'Chi c\'è in Linea';

$lang['Mark_all_forums'] = 'Segna come già letti';
$lang['Forums_marked_read'] = 'Tutti i Forum sono stati segnati come già letti';


//
// Viewforum
//
$lang['View_forum'] = 'Guarda Forum';

$lang['Forum_not_exist'] = 'Il Forum selezionato non esiste.';
$lang['Reached_on_error'] = 'Sei arrivato in questa pagina per errore.';

$lang['Display_topics'] = 'Mostra prima gli Argomenti di';
$lang['All_Topics'] = 'Seleziona';

$lang['Topic_Announcement'] = '<b>Annuncio:</b>';
$lang['Topic_Sticky'] = '<b>Importante:</b>';
$lang['Topic_Moved'] = '<b>Spostato:</b>';
$lang['Topic_Poll'] = '<b>[ Sondaggio ]</b>';

$lang['Mark_all_topics'] = 'Segna come già letti';
$lang['Topics_marked_read'] = 'Gli Argomenti di questo Forum sono stati segnati come già letti';

$lang['Rules_post_can'] = '<b>Puoi</b> inserire nuovi Argomenti';
$lang['Rules_post_cannot'] = '<b>Non puoi</b> inserire nuovi Argomenti';
$lang['Rules_reply_can'] = '<b>Puoi</b> rispondere a tutti gli Argomenti';
$lang['Rules_reply_cannot'] = '<b>Non puoi</b> rispondere a nessun Argomento';
$lang['Rules_edit_can'] = '<b>Puoi</b> modificare i tuoi Messaggi';
$lang['Rules_edit_cannot'] = '<b>Non puoi</b> modificare i tuoi Messaggi';
$lang['Rules_delete_can'] = '<b>Puoi</b> cancellare i tuoi Messaggi';
$lang['Rules_delete_cannot'] = '<b>Non puoi</b> cancellare i tuoi Messaggi';
$lang['Rules_vote_can'] = '<b>Puoi</b> votare nei Sondaggi';
$lang['Rules_vote_cannot'] = '<b>Non puoi</b> votare nei Sondaggi';
$lang['Rules_moderate'] = '<b>Puoi</b> %sModerare questo Forum%s'; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = 'Non ci sono Argomenti in questo Forum.<br />Clicca <b>Inserisci Nuovo Argomento</b> per crearne uno.';


//
// Viewtopic
//
$lang['View_topic'] = 'Leggi Argomento';

$lang['Guest'] = 'Ospite';
$lang['Post_subject'] = 'Oggetto';
$lang['View_next_topic'] = 'Successivo';
$lang['View_previous_topic'] = 'Precedente';
$lang['Submit_vote'] = 'Vota';
$lang['View_results'] = 'Guarda i Risultati';

$lang['No_newer_topics'] = 'Non ci sono nuovi Argomenti in questo Forum';
$lang['No_older_topics'] = 'Non ci sono vecchi Argomenti in questo Forum';
$lang['Topic_post_not_exist'] = 'l\'Argomento o il Messaggio che hai richiesto non esiste';
$lang['No_posts_topic'] = 'Non ci sono Messaggi in questo Argomento';

$lang['Display_posts'] = 'Mostra prima i Messaggi di';
$lang['All_Posts'] = 'Tutti i Messaggi';
$lang['Newest_First'] = 'Nuovi';
$lang['Oldest_First'] = 'Vecchi';

$lang['Back_to_top'] = 'Top';

$lang['Read_profile'] = 'Profilo'; 
$lang['Send_email'] = 'Invia Email';
$lang['Visit_website'] = 'HomePage';
$lang['ICQ_status'] = 'Stato ICQ';
$lang['Edit_delete_post'] = 'Modifica';
$lang['View_IP'] = 'Mostra indirizzo IP';
$lang['Delete_post'] = 'Cancella Messaggio';

$lang['wrote'] = 'ha scritto'; // proceeds the username and is followed by the quoted text
$lang['Quote'] = 'Citazione'; // comes before bbcode quote output.
$lang['Code'] = 'Codice'; // comes before bbcode code output.

$lang['Edited_time_total'] = 'Ultima modifica di %s il %s, modificato %d volta in totale'; // Last edited by me on 12 Oct 2001; edited 1 time in total
$lang['Edited_times_total'] = 'Ultima modifica di %s il %s, modificato %d volte in totale'; // Last edited by me on 12 Oct 2001; edited 2 times in total

$lang['Lock_topic'] = 'Chiudi Argomento';
$lang['Unlock_topic'] = 'Apri Argomento';
$lang['Move_topic'] = 'Sposta Argomento';
$lang['Delete_topic'] = 'Cancella Argomento';
$lang['Split_topic'] = 'Dividi Argomento';

$lang['Stop_watching_topic'] = 'Smetti di controllare questo Argomento';
$lang['Start_watching_topic'] = 'Controlla questo Argomento';
$lang['No_longer_watching'] = 'Non stai più controllando questo Argomento';
$lang['You_are_watching'] = 'Adesso stai controllando questo Argomento';

$lang['Total_votes'] = 'Voti Totali';

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = 'Struttura Messaggio';
$lang['Topic_review'] = 'Revisione Argomento';

$lang['No_post_mode'] = 'Modo di risposta non specificato'; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = 'Nuovo Argomento';
$lang['Post_a_reply'] = 'Rispondi';
$lang['Post_topic_as'] = 'Tipo di Argomento';
$lang['Edit_Post'] = 'Modifica Messaggio';
$lang['Options'] = 'Opzioni';

$lang['Post_Announcement'] = 'Annuncio';
$lang['Post_Sticky'] = 'Importante';
$lang['Post_Normal'] = 'Normale';

$lang['Confirm_delete'] = 'Sei sicuro di voler cancellare questo Messaggio?';
$lang['Confirm_delete_poll'] = 'Sei sicuro di voler cancellare questo Sondaggio?';

$lang['Flood_Error'] = 'Non puoi inviare un nuovo Messaggio così presto dopo l\'ultimo inserito, attendi un istante e riprova.';
$lang['Empty_subject'] = 'Devi specificare un oggetto quando inserisci un nuovo Argomento.';
$lang['Empty_message'] = 'Devi scrivere un Messaggio per inserirlo.';
$lang['Forum_locked'] = 'Questo Forum è chiuso: Non puoi inserire, rispondere o modificare gli Argomenti.';
$lang['Topic_locked'] = 'Quest\'Argomento è chiuso: Non puoi inserire, rispondere o modificare i Messaggi.';
$lang['No_post_id'] = 'Non è stato specificato nessun ID';
$lang['No_topic_id'] = 'Devi selezionare un Argomento a cui rispondere';
$lang['No_valid_mode'] = 'Puoi solo inviare, rispondere, modificare o citare Messaggi. Torna indietro e prova di nuovo.';
$lang['No_such_post'] = 'Questo Messaggio non esiste. Torna indietro e prova di nuovo.';
$lang['Edit_own_posts'] = 'Puoi modificare solo i tuoi Messaggi.';
$lang['Delete_own_posts'] = 'Puoi cancellare solo i tuoi Messaggi.';
$lang['Cannot_delete_replied'] = 'Non puoi cancellare i Messaggi che hanno una risposta.';
$lang['Cannot_delete_poll'] = 'Non puoi cancellare un Sondaggio attivo.';
$lang['Empty_poll_title'] = 'Devi inserire un titolo per il tuo Sondaggio.';
$lang['To_few_poll_options'] = 'Devi inserire almeno due opzioni per il Sondaggio.';
$lang['To_many_poll_options'] = 'Ci sono troppe opzioni per il Sondaggio.';
$lang['Post_has_no_poll'] = 'Questo Messaggio non ha Sondaggi.';
$lang['Already_voted'] = 'Hai già votato questo Sondaggio.';
$lang['No_vote_option'] = 'Devi specificare un\'opzione quando voti.';

$lang['Add_poll'] = 'Aggiungi Sondaggio';
$lang['Add_poll_explain'] = 'Se non vuoi aggiungere un sondaggio al tuo Argomento lascia vuoti i campi.';
$lang['Poll_question'] = 'Domanda del Sondaggio';
$lang['Poll_option'] = 'Opzione del Sondaggio';
$lang['Add_option'] = 'Aggiungi un\'opzione';
$lang['Update'] = 'Aggiorna';
$lang['Delete'] = 'Cancella';
$lang['Poll_for'] = 'Attiva il sondaggio per';
$lang['Days'] = 'Giorni'; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = '[ Scrivi 0 o lascia vuoto per un Sondaggio senza fine ]';
$lang['Delete_poll'] = 'Cancella Sondaggio';

$lang['Disable_HTML_post'] = 'Disabilita HTML in questo Messaggio';
$lang['Disable_BBCode_post'] = 'Disabilita BBCode in questo Messaggio';
$lang['Disable_Smilies_post'] = 'Disabilita Smilies in questo Messaggio';

$lang['HTML_is_ON'] = 'HTML <u>ATTIVO</u>';
$lang['HTML_is_OFF'] = 'HTML <u>DISATTIVATO</u>';
$lang['BBCode_is_ON'] = '%sBBCode%s <u>ATTIVO</u>'; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = '%sBBCode%s <u>DISATTIVATO</u>';
$lang['Smilies_are_ON'] = 'Smilies <u>ATTIVI</u>';
$lang['Smilies_are_OFF'] = 'Smilies <u>DISATTIVATI</u>';

$lang['Attach_signature'] = 'Aggiungi firma (puoi cambiare la firma nel profilo)';
$lang['Notify'] = 'Avvisami quando viene inviata una risposta';
$lang['Delete_post'] = 'Cancella Messaggio';

$lang['Stored'] = 'Il tuo Messaggio è stato inserito.';
$lang['Deleted'] = 'Il tuo Messaggio è stato cancellato.';
$lang['Poll_delete'] = 'Il tuo Sondaggio è stato cancellato.';
$lang['Vote_cast'] = 'Il tuo voto è stato aggiunto.';

$lang['Topic_reply_notification'] = 'Notifica risposta all\'Argomento';

$lang['bbcode_b_help'] = 'Grassetto: [b]testo[/b]  (alt+b)';
$lang['bbcode_i_help'] = 'Corsivo: [i]testo[/i]  (alt+i)';
$lang['bbcode_u_help'] = 'Sottolineato: [u]testo[/u]  (alt+u)';
$lang['bbcode_q_help'] = 'Citazione: [quote]testo[/quote]  (alt+q)';
$lang['bbcode_c_help'] = 'Codice: [code]codice[/code]  (alt+c)';
$lang['bbcode_l_help'] = 'Lista: [list]testo[/list] (alt+l)';
$lang['bbcode_o_help'] = 'Lista ordinata: [list=]testo[/list]  (alt+o)';
$lang['bbcode_p_help'] = 'Inserisci immagine: [img]http://image_url[/img]  (alt+p)';
$lang['bbcode_w_help'] = 'Inserisci URL: [url]http://url[/url] o [url=http://url]testo URL[/url]  (alt+w)';
$lang['bbcode_a_help'] = 'Chiudi tutti i bbCode tags aperti';
$lang['bbcode_s_help'] = 'Colore font: [color=red]testo[/color]  Suggerimento: puoi anche usare color=#FF0000';
$lang['bbcode_f_help'] = 'Dimensione font: [size=x-small]testo piccolo[/size]';

$lang['Emoticons'] = 'Emoticons';
$lang['More_emoticons'] = 'Altre Emoticons';

$lang['Font_color'] = 'Colore font';
$lang['color_default'] = 'Default';
$lang['color_dark_red'] = 'Rosso scuro';
$lang['color_red'] = 'Rosso';
$lang['color_orange'] = 'Arancione';
$lang['color_brown'] = 'Marrone';
$lang['color_yellow'] = 'Giallo';
$lang['color_green'] = 'Verde';
$lang['color_olive'] = 'Oliva';
$lang['color_cyan'] = 'Ciano';
$lang['color_blue'] = 'Blu';
$lang['color_dark_blue'] = 'Blu scuro';
$lang['color_indigo'] = 'Indigo';
$lang['color_violet'] = 'Viola';
$lang['color_white'] = 'Bianco';
$lang['color_black'] = 'Nero';

$lang['Font_size'] = 'Dimensione font';
$lang['font_tiny'] = 'Minuscolo';
$lang['font_small'] = 'Piccolo';
$lang['font_normal'] = 'Normale';
$lang['font_large'] = 'Grande';
$lang['font_huge'] = 'Enorme';

$lang['Close_Tags'] = 'Chiudi i Tags';
$lang['Styles_tip'] = 'Suggerimento: gli Stili possono essere applicati velocemente al testo selezionato';


//
// Private Messaging
//
$lang['Private_Messaging'] = 'Messaggi Privati';

$lang['Login_check_pm'] = 'Messaggi Privati';
$lang['New_pms'] = '%d nuovi Messaggi'; // You have 2 new messages
$lang['New_pm'] = '%d nuovo Messaggio'; // You have 1 new message
$lang['No_new_pm'] = 'Non ci sono nuovi messaggi';
$lang['Unread_pms'] = '%d Messaggi non letti';
$lang['Unread_pm'] = '%d Messaggio non letto';
$lang['No_unread_pm'] = 'Hai letto tutti i Messaggi';
$lang['You_new_pm'] = 'Hai un nuovo Messaggio in Posta in Arrivo';
$lang['You_new_pms'] = 'Ci sono nuovi Messaggi in Posta in Arrivo';
$lang['You_no_new_pm'] = 'Non ci sono nuovi Messaggi';

$lang['Unread_message'] = 'Messaggio da leggere';
$lang['Read_message'] = 'Messaggio letto';

$lang['Read_pm'] = 'Leggi Messaggio';
$lang['Post_new_pm'] = 'Nuovo Messaggio';
$lang['Post_reply_pm'] = 'Rispondi';
$lang['Post_quote_pm'] = 'Cita Messaggio';
$lang['Edit_pm'] = 'Modifica Messaggio';

$lang['Inbox'] = 'Posta in Arrivo';
$lang['Outbox'] = 'Posta in Uscita';
$lang['Savebox'] = 'Posta Salvata';
$lang['Sentbox'] = 'Posta Inviata';
$lang['Flag'] = 'Stato';
$lang['Subject'] = 'Oggetto';
$lang['From'] = 'Da';
$lang['To'] = 'A';
$lang['Date'] = 'Data';
$lang['Mark'] = 'Seleziona';
$lang['Sent'] = 'Inviato';
$lang['Saved'] = 'Salvato';
$lang['Delete_marked'] = 'Cancella Selezionati';
$lang['Delete_all'] = 'Cancella Tutti';
$lang['Save_marked'] = 'Salva Selezionati'; 
$lang['Save_message'] = 'Salva Messaggio';
$lang['Delete_message'] = 'Cancella Messaggio';

$lang['Display_messages'] = 'Mostra i Messaggi di'; // Followed by number of days/weeks/months
$lang['All_Messages'] = 'Tutti i Messaggi';

$lang['No_messages_folder'] = 'Non ci sono Messaggi in questa cartella';

$lang['PM_disabled'] = 'I Messaggi privati sono stati disabilitati dal Forum.';
$lang['Cannot_send_privmsg'] = 'L\'Amministratore del forum ti ha revocato i permessi di inviare Messaggi Privati.';
$lang['No_to_user'] = 'Devi specificare un username per inviare il Messaggio.';
$lang['No_such_user'] = 'L\'Utente non esiste.';

$lang['Disable_HTML_pm'] = 'Disabilita HTML in questo Messaggio';
$lang['Disable_BBCode_pm'] = 'Disabilita BBCode in questo Messaggio';
$lang['Disable_Smilies_pm'] = 'Disabilita Smilies in questo Messaggio';

$lang['Message_sent'] = 'Il tuo Messaggio è stato spedito.';

$lang['Click_return_inbox'] = 'Torna alla cartella %sPosta in Arrivo%s';
$lang['Click_return_index'] = 'Torna %sall\'Indice%s';

$lang['Send_a_new_message'] = 'Invia nuovo Messaggio Privato';
$lang['Send_a_reply'] = 'Rispondi a Messaggio Privato';
$lang['Edit_message'] = 'Modifica Messaggio Privato';

$lang['Notification_subject'] = 'Hai un nuovo Messaggio Privato!';

$lang['Find_username'] = 'Trova un Username';
$lang['Find'] = 'Trova';
$lang['No_match'] = 'Nessun Risultato.';

$lang['No_post_id'] = 'Non è stato specificato nessun ID';
$lang['No_such_folder'] = 'Questa cartella non esiste';
$lang['No_folder'] = 'Nessuna cartella specificata';

$lang['Mark_all'] = 'Seleziona tutti';
$lang['Unmark_all'] = 'Deseleziona tutti';

$lang['Confirm_delete_pm'] = 'Sei sicuro di voler cancellare questo Messaggio?';
$lang['Confirm_delete_pms'] = 'Sei sicuro di voler cancellare questi Messaggi?';

$lang['Inbox_size'] = 'Utilizzo Posta in Arrivo'; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = 'Utilizzo Posta in Uscita'; 
$lang['Savebox_size'] = 'Utilizzo Posta Salvata'; 

$lang['Click_view_privmsg'] = 'Clicca %squi%s per andare alla cartella di Posta in Arrivo';


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = 'Guarda il profilo :: %s'; // %s is username 
$lang['About_user'] = 'Tutto su %s'; // %s is username

$lang['Preferences'] = 'Preferenze';
$lang['Items_required'] = 'Le voci contrassegnate con * sono richieste.';
$lang['Registration_info'] = 'Dettagli Registrazione';
$lang['Profile_info'] = 'Dettagli Profilo';
$lang['Profile_info_warn'] = 'Queste informazioni saranno visibili da tutti gli Utenti';
$lang['Avatar_panel'] = 'Pannello di Controllo Avatar';
$lang['Avatar_gallery'] = 'Galleria Avatar';

$lang['Website'] = 'Sito web';
$lang['Location'] = 'Residenza';
$lang['Contact'] = 'Contatto';
$lang['Email_address'] = 'Indirizzo Email';
$lang['Email'] = 'Email';
$lang['Send_private_message'] = 'Invia Messaggio Privato';
$lang['Hidden_email'] = '[ Nascosto ]';
$lang['Search_user_posts'] = 'Cerca tutti i Messaggi di %s';
$lang['Interests'] = 'Interessi';
$lang['Occupation'] = 'Impiego'; 
$lang['Poster_rank'] = 'Livello Utente';

$lang['Total_posts'] = 'Messaggi totali';
$lang['User_post_pct_stats'] = '%.2f%% del totale'; // 1.25% of total
$lang['User_post_day_stats'] = '%.2f messaggi al giorno'; // 1.5 posts per day
$lang['Search_user_posts'] = 'Guarda tutti i Messaggi scritti da %s'; // Find all posts by username

$lang['No_user_id_specified'] = 'L\'Utente non esiste.';
$lang['Wrong_Profile'] = 'Non puoi modificare questo Profilo.';

$lang['Only_one_avatar'] = 'Può essere specificato un solo tipo di Avatar';
$lang['File_no_data'] = 'Il file all\'URL che hai fornito non contiene dati';
$lang['No_connection_URL'] = 'Non è possibile connettersi all\'URL che hai fornito';
$lang['Incomplete_URL'] = 'L\'URL che hai fornito è incompleto';
$lang['Wrong_remote_avatar_format'] = 'L\'URL dell\'Avatar remoto non è valido';
$lang['No_send_account_inactive'] = 'Spiacenti, ma la tua password non può essere recuperata perchè il tuo account al momento è inattivo. Contatta l\'Amministratore per ulteriori informazioni.';

$lang['Always_smile'] = 'Abilita sempre gli Smilies';
$lang['Always_html'] = 'Abilita sempre i codici HTML';
$lang['Always_bbcode'] = 'Abilita sempre il BBCode';
$lang['Always_add_sig'] = 'Aggiungi sempre la mia firma';
$lang['Always_notify'] = 'Avvisami sempre delle risposte';
$lang['Always_notify_explain'] = 'Vieni avvisato con un\'Email quando un Utente risponde ad un Argomento a cui hai risposto. Questo può essere cambiato ogni volta che inserisci un nuovo Messaggio.';

$lang['Board_style'] = 'Stile Forum';
$lang['Board_lang'] = 'Linguaggio del Forum';
$lang['No_themes'] = 'Non ci sono Stili presenti nel Database';
$lang['Timezone'] = 'Fuso orario';
$lang['Date_format'] = 'Formato data';
$lang['Date_format_explain'] = 'La sintassi utilizzata e\' la funzione <a href=\'http://www.php.net/manual/it/html/function.date.html\' target=\'_other\'>data()</a> del PHP.';
$lang['Signature'] = 'Firma';
$lang['Signature_explain'] = 'Testo che verrà visualizzato come firma a tutti i tuoi Messaggi. C\'è un limite di %d caratteri';
$lang['Public_view_email'] = 'Mostra sempre il mio indirizzo Email';

$lang['Current_password'] = 'Password attuale';
$lang['New_password'] = 'Nuova password';
$lang['Confirm_password'] = 'Conferma password';
$lang['Confirm_password_explain'] = 'Devi confermare la tua password attuale se vuoi cambiarla o modificare il tuo indirizzo email';
$lang['password_if_changed'] = 'Devi inserire la password solo se vuoi cambiarla';
$lang['password_confirm_if_changed'] = 'Devi confermare la tua password solo se ne hai inserita una nuova qui sopra';

$lang['Avatar'] = 'Avatar';
$lang['Avatar_explain'] = 'Mostra una piccola immagine grafica sotto i tuoi dettagli nel Messaggio. Può essere mostrata una sola immagine alla volta, la sua larghezza massima è di %d pixels, l\'altezza di %d pixels e il file deve essere più piccolo di %dkB.';
$lang['Upload_Avatar_file'] = 'Carica Avatar da PC';
$lang['Upload_Avatar_URL'] = 'Carica Avatar da un URL';
$lang['Upload_Avatar_URL_explain'] = 'Inserisci URL dell\'Avatar, che verrà copiato in questo Sito.';
$lang['Pick_local_Avatar'] = 'Seleziona Avatar dalla Galleria';
$lang['Link_remote_Avatar'] = 'Link esterno Avatar';
$lang['Link_remote_Avatar_explain'] = 'Inserisci URL dell\'Avatar che vuoi linkare.';
$lang['Avatar_URL'] = 'URL Avatar';
$lang['Select_from_gallery'] = 'Seleziona Avatar dalla Galleria';
$lang['View_avatar_gallery'] = 'Mostra Galleria';

$lang['Select_avatar'] = 'Seleziona Avatar';
$lang['Return_profile'] = 'Cancella Avatar';
$lang['Select_category'] = 'Seleziona categoria';

$lang['Delete_Image'] = 'Cancella Immagine';
$lang['Current_Image'] = 'Immagine attuale';

$lang['Notify_on_privmsg'] = 'Notifica sui nuovi Messaggi Privati';
$lang['Popup_on_privmsg'] = 'Popup nuovo Messaggio Privato'; 
$lang['Popup_on_privmsg_explain'] = 'Apre una piccola nuova finestra per informarti quando arriva un nuovo Messaggio Privato.';
$lang['Hide_user'] = 'Nascondi il tuo stato online';

$lang['Profile_updated'] = 'Il tuo profilo è stato aggiornato';
$lang['Profile_updated_inactive'] = 'Il tuo profilo è stato aggiornato. Hai modificato dettagli importanti anche se il tuo account non è ancora attivo. Controlla la tua email per riattivare il tuo account, o, se richiesta, attendi la riattivazione da parte dell\'Amministratore.';

$lang['Password_mismatch'] = 'La password inserita non corrisponde.';
$lang['Current_password_mismatch'] = 'La password inserita non corrisponde a quella presente nel Database.';
$lang['Password_long'] = 'La password non deve essere più lunga di 32 caratteri.';
$lang['Too_many_registers'] = 'Hai fatto troppi tentativi di Registrazione. Prova più tardi.';
$lang['Username_taken'] = 'Username in uso da un\'altro utente.';
$lang['Username_invalid'] = 'Errore, l\'Username contiene un carattere non valido come \'.';
$lang['Username_disallowed'] = 'Username disabilitato dall\'Amministratore.';
$lang['Email_taken'] = 'L\'indirizzo Email è già presente nel nostro Database.';
$lang['Email_banned'] = 'L\'indirizzo Email stato escluso dall\'Amministratore.';
$lang['Email_invalid'] = 'Indirizzo Email non valido.';
$lang['Signature_too_long'] = 'La firma è troppo lunga.';
$lang['Fields_empty'] = 'Devi riempire tutti i campi richiesti.';
$lang['Avatar_filetype'] = 'Il file Avatar deve essere .jpg, .gif o .png';
$lang['Avatar_filesize'] = 'La grandezza del file dell\'Avatar deve essere inferiore a %d kB'; // The avatar image file size must be less than 6 KB
$lang['Avatar_imagesize'] = 'L\'Avatar non può superare le dimensioni di %d pixels di larghezza e di %d pixels di altezza'; 

$lang['Welcome_subject'] = 'Benvenuto nel Forum di %s '; // Welcome to my.com forums
$lang['New_account_subject'] = 'Account Nuovo Utente';
$lang['Account_activated_subject'] = 'Account Attivato';

$lang['Account_added'] = 'Grazie per esserti registrato, il tuo account è stato creato. Adesso puoi entrare con il tuo username e password';
$lang['Account_inactive'] = 'Il tuo account è stato creato. Questo forum richiede l\'attivazione dell\'account. La chiave per l\'attivazione è stata inviata all\'indirizzo email che hai inserito. Controlla la tua Email per ulteriori informazioni';
$lang['Account_inactive_admin'] = 'Il tuo account è stato creato. Questo forum richiede l\'attivazione dell\'account da parte dell\'Amministratore. Ti verrà inviata un\'Email dall\'Amministratore e sarai informato sullo stato di attivazione del tuo account';
$lang['Account_active'] = 'Il tuo account è stato attivato. Grazie per esserti registrato.';
$lang['Account_active_admin'] = 'The account has now been activated';
$lang['Reactivate'] = 'Riattiva il tuo account!';
$lang['Already_activated'] = 'Questo account è già stato attivato';
$lang['COPPA'] = 'Il tuo account è stato creato, ma deve essere approvato. Controlla la tua Email per ulteriori dettagli.';

$lang['Registration'] = 'Condizioni per la Registrazione';
$lang['Reg_agreement'] = 'Anche se gli Amministratori e i Moderatori di questo forum cercheranno di rimuovere o modificare tutto il materiale contestabile il più velocemente possibile, è comunque impossibile verificare ogni Messaggio. Tuttavia sei consapevole che tutti i Messaggi di questo forum esprimono il punto di vista e le opinioni dell\'autore e non quelle degli Amministratori, dei Moderatori o del Webmaster (eccetto i messaggi degli stessi) e per questo non sono perseguibili.<br /><br />L\'utente concorda di non inviare Messaggi abusivi, osceni, volgari, diffamatori, di odio, minatori, sessuali o qualunque altro materiale che possa violare qualunque legge applicabile. Inserendo Messaggi di questo tipo l\'utente verrà immediatamente e permanentemente escluso (e il tuo provider verrà informato). L\'indirizzo IP di tutti i Messaggi viene registrato per aiutare a rinforzare queste condizioni. L\'Utente concorda che l\'Amministratore i Moderatori e Webmaster di questo forum hanno il diritto di rimuovere, modificare, o chiudere Argomenti qualora si ritengana necessario. Come Utente concordi che ogni informazione che è stata inserita verrà conservata in un database. Poichè queste informazioni non verranno cedute a terzi senza il tuo consenso, Webmaster, Amministratore e i Moderatori non sono ritenuti responsabili per gli attacchi da parte degli hackers che possano compromettere i dati.<br /><br />Questo Forum usa i cookies per conservare informazioni sul tuo computer locale. Questi cookies non contengono le informazioni che hai inserirai, servono soltanto per velocizzarne il processo. L\'indirizzo Email viene utilizzato solo per confermare i dettagli della tua registrazione e per la password (e per inviare una nuova password nel caso dovessi perdere quella attuale).<br /><br />Cliccando Registra qui sotto accetti queste condizioni.';

$lang['Agree_under_13'] = 'Accetto queste condizioni e ho <b>meno</b> di 13 anni';
$lang['Agree_over_13'] = 'Accetto queste condizioni e ho <b>più</b> di 13 anni';
$lang['Agree_not'] = 'Non accetto queste condizioni';

$lang['Wrong_activation'] = 'La chiave di attivazione che hai fornito non corrisponde a nessuna presente nel database.';
$lang['Send_password'] = 'Inviami una nuova password'; 
$lang['Password_updated'] = 'Una nuova password è stata creata, controlla la tua email per maggiori dettagli su come attivarla.';
$lang['No_email_match'] = 'L\'indirizzo Email inserita non corrisponde a quella attuale per questo Username.';
$lang['New_password_activation'] = 'Attivazione nuova password';
$lang['Password_activated'] = 'Il tuo account è stato riattivato. Per entrare usa la password ricevuta via Email.';

$lang['Send_email_msg'] = 'Invia un Messaggio Email';
$lang['No_user_specified'] = 'Non è stato specificato nessun Utente';
$lang['User_prevent_email'] = 'L\'Utente non gradisce ricevere Email. Prova ad inviare un Messaggio Privato.';
$lang['User_not_exist'] = 'Questo Utente non esiste';
$lang['CC_email'] = 'Invia una copia di questa Email a te stesso';
$lang['Email_message_desc'] = 'Questo messaggio verrà inviato come testo, non includere nessun codice HTML o BBCode. L\'indirizzo per la risposta di questo Messaggio è il tuo indirizzo Email.';
$lang['Flood_email_limit'] = 'Non puoi inviare un\'altra Email al momento. Prova più tardi.';
$lang['Recipient'] = 'Cestino';
$lang['Email_sent'] = 'Questa Email è stata inviata.';
$lang['Send_email'] = 'Invia Email';
$lang['Empty_subject_email'] = 'Devi specificare un oggetto per l\'Email.';
$lang['Empty_message_email'] = 'Devi inserire un Messaggio da inviare.';


//
// Visual confirmation system strings
//
$lang['Confirm_code_wrong'] = 'Il codice di conferma inserito non è corretto';
$lang['Too_many_registers'] = 'Hai superato il numero massimo di tentativi per questa sessione. Ritenta più tardi.';
$lang['Confirm_code_impaired'] = 'Se non riesci a visualizzare il codice di registrazione contattate l\'%sAmministratore%s.';
$lang['Confirm_code'] = 'Codice di conferma';
$lang['Confirm_code_explain'] = 'Inserisci il codice di conferma  visuale come indicato nell\'immagine. Il sistema riconosce la differenza tra maiuscole e minuscole, lo zero ha una barra diagonale per distinguerlo dalla lettera O.';



//
// Memberslist
//
$lang['Select_sort_method'] = 'Seleziona un ordine';
$lang['Sort'] = 'Ordina';
$lang['Sort_Top_Ten'] = 'I Migliori 10 Autori';
$lang['Sort_Joined'] = 'Data di Registrazione';
$lang['Sort_Username'] = 'Username';
$lang['Sort_Location'] = 'Località';
$lang['Sort_Posts'] = 'Messaggi totali';
$lang['Sort_Email'] = 'Email';
$lang['Sort_Website'] = 'Sito Web';
$lang['Sort_Ascending'] = 'Crescente';
$lang['Sort_Descending'] = 'Decrescente';
$lang['Order'] = 'Ordina';


//
// Group control panel
//
$lang['Group_Control_Panel'] = 'Pannello di Controllo Gruppo';
$lang['Group_member_details'] = 'Dettagli Utenti Gruppo';
$lang['Group_member_join'] = 'Iscrivi un Gruppo';

$lang['Group_Information'] = 'Informazioni Gruppo';
$lang['Group_name'] = 'Nome Gruppo';
$lang['Group_description'] = 'Descrizione Gruppo';
$lang['Group_membership'] = 'Appartenenza al Gruppo';
$lang['Group_Members'] = 'Utenti del Gruppo';
$lang['Group_Moderator'] = 'Moderatore Gruppo';
$lang['Pending_members'] = 'Nuovi iscritti in Attesa';

$lang['Group_type'] = 'Tipo di Gruppo';
$lang['Group_open'] = 'Gruppo Aperto';
$lang['Group_closed'] = 'Gruppo Chiuso';
$lang['Group_hidden'] = 'Gruppo Nascosto';

$lang['Current_memberships'] = 'Utenti Gruppo attuali';
$lang['Non_member_groups'] = 'Non sei iscritto al Gruppo';
$lang['Memberships_pending'] = 'Nuovi iscritti al Gruppo in Attesa';

$lang['No_groups_exist'] = 'Non esistono Gruppi';
$lang['Group_not_exist'] = 'Gruppo non esistente';

$lang['Join_group'] = 'Iscriviti al Gruppo';
$lang['No_group_members'] = 'Questo Gruppo non ha Utenti iscritti';
$lang['Group_hidden_members'] = 'Gruppo nascosto, non puoi vedere i suoi membri';
$lang['No_pending_group_members'] = 'Questo Gruppo non ha Utenti in attesa';
$lang['Group_joined'] = 'Ti sei iscritto a questo Gruppo con succeso.<br />Sarai avvisato quando la tua iscrizione verrà approvata dal moderatore del Gruppo.';
$lang['Group_request'] = 'C\'è una richiesta di iscrizione al tuo Gruppo.';
$lang['Group_approved'] = 'La tua richiesta è stata approvata.';
$lang['Group_added'] = 'Sei stato aggiunto a questo Gruppo.'; 
$lang['Already_member_group'] = 'Sei già iscritto a questo Gruppo';
$lang['User_is_member_group'] = 'L\'Utente è già iscritto a questo Gruppo';
$lang['Group_type_updated'] = 'Tipo di Gruppo aggiornato.';

$lang['Could_not_add_user'] = 'L\'Utente selezionato non esiste.';
$lang['Could_not_anon_user'] = 'L\'Utente Anonimo non può essere iscritto ad un Gruppo.';

$lang['Confirm_unsub'] = 'Sei sicuro di volerti cancellare da questo Gruppo?';
$lang['Confirm_unsub_pending'] = 'La tua iscrizione a questo Gruppo non è ancora stata approvata, sei sicuro di volerti cancellare?';

$lang['Unsub_success'] = 'Sei stato cancellato da questo Gruppo.';

$lang['Approve_selected'] = 'Approvazione Selezionata';
$lang['Deny_selected'] = 'Rifiuto Selezionato';
$lang['Not_logged_in'] = 'Per iscriverti ad un Gruppo devi essere Registrato.';
$lang['Remove_selected'] = 'Rimuovi Selezionati';
$lang['Add_member'] = 'Aggiungi Utente';
$lang['Not_group_moderator'] = 'Non sei Moderatore di questo Gruppo, non puoi eseguire questa azione.';

$lang['Login_to_join'] = 'Entra per iscriverti o gestire un Gruppo di Utenti';
$lang['This_open_group'] = 'Gruppo aperto, clicca per richiedere l\'adesione';
$lang['This_closed_group'] = 'Gruppo chiuso, non si accettano altri membri';
$lang['This_hidden_group'] = 'Gruppo nascosto, non è permesso aggiungere nuovi utenti automaticamente';
$lang['Member_this_group'] = 'Sei iscritto a questo Gruppo';
$lang['Pending_this_group'] = 'La tua iscrizione a questo Gruppo è in attesa di approvazione';
$lang['Are_group_moderator'] = 'Sei Moderatore di questo Gruppo';
$lang['None'] = 'Nessuno';

$lang['Subscribe'] = 'Iscriviti';
$lang['Unsubscribe'] = 'Cancella';
$lang['View_Information'] = 'Guarda Informazioni';


//
// Search
//
$lang['Search_query'] = 'Motore di Ricerca';
$lang['Search_options'] = 'Opzioni di Ricerca';

$lang['Search_keywords'] = 'Cerca per parole chiave';
$lang['Search_keywords_explain'] = 'Puoi usare <u>AND</u> per definire le parole che devono essere nel risultato della ricerca, <u>OR</u> per definire le parole che potrebbero essere nel risultato e <u>NOT</u> per definire le parole che non devono essere nel risultato. Usa * come abbrevazione per parole parziali';
$lang['Search_author'] = 'Cerca per Autore';
$lang['Search_author_explain'] = 'Usa * come abbreviazione per parole parziali';

$lang['Search_for_any'] = 'Cerca per parola o usa frase esatta';
$lang['Search_for_all'] = 'Cerca tutte le parole';
$lang['Search_title_msg'] = 'Cerca nel titolo o nel testo';
$lang['Search_msg_only'] = 'Cerca solo nel testo';

$lang['Return_first'] = 'Mostra i primi'; // followed by xxx characters in a select box
$lang['characters_posts'] = 'caratteri del messaggio';

$lang['Search_previous'] = 'Cerca i Messaggi di'; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = 'Ordina per';
$lang['Sort_Time'] = 'Data Messaggio';
$lang['Sort_Post_Subject'] = 'Oggetto Messaggio';
$lang['Sort_Topic_Title'] = 'Titolo Argomento';
$lang['Sort_Author'] = 'Autore';
$lang['Sort_Forum'] = 'Forum';

$lang['Display_results'] = 'Mostra i risultati come';
$lang['All_available'] = 'Tutto disponibile';
$lang['No_searchable_forums'] = 'Non hai i permessi per utilizzare il motore di ricerca del Forum.';

$lang['No_search_match'] = 'Nessun Argomento o Messaggio con questo criterio di ricerca';
$lang['Found_search_match'] = 'La ricerca ha trovato %d risultato'; // eg. Search found 1 match
$lang['Found_search_matches'] = 'La ricerca ha trovato %d risultati'; // eg. Search found 24 matches

$lang['Close_window'] = 'Chiudi Finestra';


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = 'Solo %s possono inviare Annunci.';
$lang['Sorry_auth_sticky'] = 'Solo %s possono inviare Messaggi importanti.'; 
$lang['Sorry_auth_read'] = 'Solo %s possono leggere gli Argomenti.'; 
$lang['Sorry_auth_post'] = 'Solo %s possono inserire Argomenti.'; 
$lang['Sorry_auth_reply'] = 'Solo %s possono rispondere ai Messaggi.';
$lang['Sorry_auth_edit'] = 'Solo %s possono modificare i Messaggi.'; 
$lang['Sorry_auth_delete'] = 'Solo %s possono cancellare i Messaggi.';
$lang['Sorry_auth_vote'] = 'Solo %s possono votare nei Sondaggi.';

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = '<b>gli Utenti Anonimi</b>';
$lang['Auth_Registered_Users'] = '<b>gli Utenti Registrati</b>';
$lang['Auth_Users_granted_access'] = '<b>gli Utenti con accesso speciale</b>';
$lang['Auth_Moderators'] = '<b>i Moderatori</b>';
$lang['Auth_Administrators'] = '<b>gli Amministratori</b>';

$lang['Not_Moderator'] = 'Non sei Moderatore di questo Forum.';
$lang['Not_Authorised'] = 'Non Autorizzato';

$lang['You_been_banned'] = 'Sei stato escluso da questo Forum<br />contatta l\'Amministratore o Webmasterper del Sito per ulteriori informazioni.';


//
// Viewonline
//
$lang['Reg_users_zero_online'] = 'Ci sono 0 Utenti Registrati e '; // There are 5 Registered and
$lang['Reg_users_online'] = 'Ci sono %d Utenti Registrati e '; // There are 5 Registered and
$lang['Reg_user_online'] = 'C\'è %d Utente Registrato e '; // There is 1 Registered and
$lang['Hidden_users_zero_online'] = '0 Utenti Nascosti in linea'; // 6 Hidden users online
$lang['Hidden_users_online'] = '%d Utenti Nascosti in linea'; // 6 Hidden users online
$lang['Hidden_user_online'] = '%d Utente Nascosto in linea'; // 6 Hidden users online
$lang['Guest_users_online'] = 'Ci sono %d Ospiti in linea'; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = 'Ci sono 0 Ospiti in linea'; // There are 10 Guest users online
$lang['Guest_user_online'] = 'C\'è %d Ospite in linea'; // There is 1 Guest user online
$lang['No_users_browsing'] = 'Al momento non ci sono Utenti nel Forum';

$lang['Online_explain'] = 'Questi dati si basano sugli Utenti in linea negli ultimi cinque minuti';

$lang['Forum_Location'] = 'Località del Forum';
$lang['Last_updated'] = 'Ultimo Aggiornamento';

$lang['Forum_index'] = 'Indice Forum';
$lang['Logging_on'] = 'Sta entrando';
$lang['Posting_message'] = 'Sta inviando un Messaggio';
$lang['Searching_forums'] = 'Sta cercando nei Forum';
$lang['Viewing_profile'] = 'Sta guardando il Profilo';
$lang['Viewing_online'] = 'Sta guardando chi c\'è in linea';
$lang['Viewing_member_list'] = 'Sta guardando la Lista Utenti';
$lang['Viewing_priv_msgs'] = 'Sta guardando i Messaggi Privati';
$lang['Viewing_FAQ'] = 'Sta guardando le FAQ';


//
// Moderator Control Panel
//
$lang['Mod_CP'] = 'Pannello di Controllo Moderatori';
$lang['Mod_CP_explain'] = 'Utilizzando il modulo qui sotto puoi eseguire operazioni di massa su questo Forum. Puoi bloccare, sbloccare, spostare o cancellare.';

$lang['Select'] = 'Seleziona';
$lang['Delete'] = 'Cancella';
$lang['Move'] = 'Sposta';
$lang['Lock'] = 'Chiudi';
$lang['Unlock'] = 'Apri';

$lang['Topics_Removed'] = 'Gli Argomenti selezionati sono stati rimossi dal Database.';
$lang['Topics_Locked'] = 'Gli Argomenti selezionati sono stati chiusi.';
$lang['Topics_Moved'] = 'Gli Argomenti selezionati sono stati spostati.';
$lang['Topics_Unlocked'] = 'Gli Argomenti selezionati sono stati ri-aperti.';
$lang['No_Topics_Moved'] = 'Non è stato spostato nessun Argomenti.';

$lang['Confirm_delete_topic'] = 'Sei sicuro di voler eliminare gli Argomenti selezionati?';
$lang['Confirm_lock_topic'] = 'Sei sicuro di voler chiudere gli Argomenti selezionati?';
$lang['Confirm_unlock_topic'] = 'Sei sicuro di voler ri-aprire gli Argomenti selezionati?';
$lang['Confirm_move_topic'] = 'Sei sicuro di voler spostare gli Argomenti selezionati?';

$lang['Move_to_forum'] = 'Vai al Forum';
$lang['Leave_shadow_topic'] = 'Lascia una traccia nel Forum di creazione.';

$lang['Split_Topic'] = 'Divisione Argomenti';
$lang['Split_Topic_explain'] = 'Utilizzando il modulo qui sotto puoi dividere un Argomenti in due, sia selezionando i Messaggi individualmente, sia dividendo l\'Argomento da una parte di selezionato Messaggio in poi';
$lang['Split_title'] = 'Titolo nuovo Argomento';
$lang['Split_forum'] = 'Forum per il nuovo Argomento';
$lang['Split_posts'] = 'Dividi i Messaggi selezionati';
$lang['Split_after'] = 'Dividi partendo dal Messaggio selezionato';
$lang['Topic_split'] = 'L\'Argomento selezionato è stato diviso';

$lang['Too_many_error'] = 'Hai selezionato troppi Messaggi. Puoi selezionare solo il Messaggio da cui dividere l\'Argomento!';

$lang['None_selected'] = 'Nessun Argomento selezionato nel quale eseguire questa operazione. Torna indietro e selezionane almeno uno.';
$lang['New_forum'] = 'Nuovo Forum';

$lang['This_posts_IP'] = 'Indirizzo IP per questo Messaggio';
$lang['Other_IP_this_user'] = 'Altri indirizzi IP utilizzati da questo Utente';
$lang['Users_this_IP'] = 'Utenti che utilizzano questo indirizzo IP';
$lang['IP_info'] = 'Informazioni indirizzo IP';
$lang['Lookup_IP'] = 'Cerca indirizzo IP';


//
// Timezones ... for display on each page
//
$lang['All_times'] = 'Tutti i fusi orari sono %s'; // eg. All times are GMT - 12 Hours (times from next block)

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
$lang['-1'] = 'GMT - 1 ore';
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
$lang['tz']['-12'] = 'GMT -12:00 ore';
$lang['tz']['-11'] = 'GMT -11:00 ore';
$lang['tz']['-10'] = 'GMT -10:00 ore';
$lang['tz']['-9'] = 'GMT -9:00 ore';
$lang['tz']['-8'] = 'GMT -8:00 ore';
$lang['tz']['-7'] = 'GMT -7:00 ore';
$lang['tz']['-6'] = 'GMT -6:00 ore';
$lang['tz']['-5'] = 'GMT -5:00 ore';
$lang['tz']['-4'] = 'GMT -4:00 ore';
$lang['tz']['-3.5'] = 'GMT -3:30 ore';
$lang['tz']['-3'] = 'GMT -3:00 ore';
$lang['tz']['-2'] = 'GMT -2:00 ore';
$lang['tz']['-1'] = 'GMT -1:00 ora';
$lang['tz']['0'] = 'GMT';
$lang['tz']['1'] = 'GMT +1:00 ora';
$lang['tz']['2'] = 'GMT +2:00 ore';
$lang['tz']['3'] = 'GMT +3:00 ore';
$lang['tz']['3.5'] = 'GMT +3:30 ore';
$lang['tz']['4'] = 'GMT +4:00 ore';
$lang['tz']['4.5'] = 'GMT +4:30 ore';
$lang['tz']['5'] = 'GMT +5:00 ore';
$lang['tz']['5.5'] = 'GMT +5:30 ore';
$lang['tz']['6'] = 'GMT +6:00 ore';
$lang['tz']['6.5'] = 'GMT +6:30 ore';
$lang['tz']['7'] = 'GMT +7:00 ore';
$lang['tz']['8'] = 'GMT +8:00 ore';
$lang['tz']['9'] = 'GMT +9:00 ore';
$lang['tz']['9.5'] = 'GMT +9:30 ore';
$lang['tz']['10'] = 'GMT + 10 ore';
$lang['tz']['11'] = 'GMT + 11 ore';
$lang['tz']['12'] = 'GMT + 12 ore';
$lang['tz']['13'] = 'GMT + 13 ore';

$lang['datetime']['Sunday'] = 'Domenica';
$lang['datetime']['Monday'] = 'Lunedì';
$lang['datetime']['Tuesday'] = 'Martedì';
$lang['datetime']['Wednesday'] = 'Mercoledì';
$lang['datetime']['Thursday'] = 'Giovedì';
$lang['datetime']['Friday'] = 'Venerdì';
$lang['datetime']['Saturday'] = 'Sabato';
$lang['datetime']['Sun'] = 'Dom';
$lang['datetime']['Mon'] = 'Lun';
$lang['datetime']['Tue'] = 'Mar';
$lang['datetime']['Wed'] = 'Mer';
$lang['datetime']['Thu'] = 'Gio';
$lang['datetime']['Fri'] = 'Ven';
$lang['datetime']['Sat'] = 'Sab';
$lang['datetime']['January'] = 'Gennaio';
$lang['datetime']['February'] = 'Febbraio';
$lang['datetime']['March'] = 'Marzo';
$lang['datetime']['April'] = 'Aprile';
$lang['datetime']['May'] = 'Maggio';
$lang['datetime']['June'] = 'Giugno';
$lang['datetime']['July'] = 'Luglio';
$lang['datetime']['August'] = 'Agosto';
$lang['datetime']['September'] = 'Settembre';
$lang['datetime']['October'] = 'Ottobre';
$lang['datetime']['November'] = 'Novembre';
$lang['datetime']['December'] = 'Dicembre';
$lang['datetime']['Jan'] = 'Gen';
$lang['datetime']['Feb'] = 'Feb';
$lang['datetime']['Mar'] = 'Mar';
$lang['datetime']['Apr'] = 'Apr';
$lang['datetime']['May'] = 'Mag';
$lang['datetime']['Jun'] = 'Giu';
$lang['datetime']['Jul'] = 'Lug';
$lang['datetime']['Aug'] = 'Ago';
$lang['datetime']['Sep'] = 'Set';
$lang['datetime']['Oct'] = 'Ott';
$lang['datetime']['Nov'] = 'Nov';
$lang['datetime']['Dec'] = 'Dic';

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = 'Informazione';
$lang['Critical_Information'] = 'Informazione Critica';

$lang['General_Error'] = 'Errore Generale';
$lang['Critical_Error'] = 'Errore Critico';
$lang['An_error_occured'] = 'Si è verificato un errore';
$lang['A_critical_error'] = 'Si è verificato un errore critico';

//
// That's all, Folks!
// -------------------------------------------------

?>