<?php
/***************************************************************************
 *                            lang_main.php [English]
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
//	Translation produced by tesno
//	http://www.snowbox.it/
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
$lang['LEFT'] = "SINISTRA";
$lang['RIGHT'] = "DESTRA";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Forum";
$lang['Category'] = "Categoria";
$lang['Topic'] = "Argomento";
$lang['Topics'] = "Argomenti";
$lang['Replies'] = "Risposte";
$lang['Views'] = "Consultazioni";
$lang['Post'] = "Messaggio";
$lang['Posts'] = "Messaggi";
$lang['Posted'] = "Inviato";
$lang['Username'] = "Username";
$lang['Password'] = "Password";
$lang['Email'] = "Email";
$lang['Poster'] = "Autore del messaggio";
$lang['Author'] = "Autore";
$lang['Time'] = "Data";
$lang['Hours'] = "Ora";
$lang['Message'] = "Messaggio";

$lang['1_Day'] = "1 Giorno";
$lang['7_Days'] = "7 Giorni";
$lang['2_Weeks'] = "2 Settimane";
$lang['1_Month'] = "1 Mese";
$lang['3_Months'] = "3 Mesi";
$lang['6_Months'] = "6 Mesi";
$lang['1_Year'] = "1 Anno";

$lang['Go'] = "Vai";
$lang['Jump_to'] = "Vai a";
$lang['Submit'] = "Invia";
$lang['Reset'] = "Cancella";
$lang['Cancel'] = "Annulla";
$lang['Preview'] = "Anteprima";
$lang['Confirm'] = "Conferma";
$lang['Spellcheck'] = "Controllo Ortografico";
$lang['Yes'] = "Sì";
$lang['No'] = "No";
$lang['Enabled'] = "Abilitato";
$lang['Disabled'] = "Disabilitato";
$lang['Error'] = "Errore";

$lang['Next'] = "Successivo";
$lang['Previous'] = "Precedente";
$lang['Goto_page'] = "Vai alla pagina";
$lang['Joined'] = "Registrato";
$lang['IP_Address'] = "Indirizzo IP";

$lang['Select_forum'] = "Seleziona un forum";
$lang['View_latest_post'] = "Guarda gli ultimi messaggi";
$lang['View_newest_post'] = "Guarda i nuovi messaggi";
$lang['Page_of'] = "Pagina <b>%d</b> di <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "Numero ICQ";
$lang['AIM'] = "Indirizzo AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "Indice del forum";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Inserisci un nuovo argomento";
$lang['Reply_to_topic'] = "Rispondi all'argomento";
$lang['Reply_with_quote'] = "Rispondi con citazione";

$lang['Click_return_topic'] = "Clicca %squi%s per tornare all'argomento"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Clicca %squi%s per riprovare";
$lang['Click_return_forum'] = "Clicca %squi%s per tornare al forum";
$lang['Click_view_message'] = "Clicca %squi%s per vedere il tuo messaggio";
$lang['Click_return_modcp'] = "Clicca %squi%s per tornare al Pannello di Controllo dei Moderatori";
$lang['Click_return_group'] = "Clicca %squi%s per tornare alle informazioni sul gruppo";

$lang['Admin_panel'] = "Vai al Pannello di Amministrazione";

$lang['Board_disable'] = "Spiacenti ma questo forum non è al momento disponibile, per favore prova più tardi";


//
// Global Header strings
//
$lang['Registered_users'] = "Utenti registrati:";
$lang['Browsing_forum'] = "Utenti che stanno navigando nel forum:";
$lang['Online_users_total'] = "In totale ci sono <b>%d</b> utenti in linea :: ";
$lang['Online_user_total'] = "In totale c'è <b>%d</b> utente in linea :: ";
$lang['Reg_users_total'] = "%d Registrati, ";
$lang['Reg_user_total'] = "%d Registrato, ";
$lang['Hidden_users_total'] = "%d Nascosti e ";
$lang['Hidden_user_total'] = "%d Nascosto e ";
$lang['Guest_users_total'] = "%d Ospiti";
$lang['Guest_user_total'] = "%d Ospite";
$lang['Record_online_users'] = "Il massimo numero di utenti in linea è stato <b>%s</b> il %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sAmministratore%s"; 
$lang['Mod_online_color'] = "%sModeratore%s";

$lang['You_last_visit'] = "La tua ultima visita è stata %s"; // %s replaced by date/time
$lang['Current_time'] = "La data di oggi è %s"; // %s replaced by time

$lang['Search_new'] = "Guarda i messaggi dall'ultima visita";
$lang['Admin_online_color'] = "%sAmministratore%s"; 
$lang['Mod_online_color'] = "%sModeratore%s"; 
$lang['Search_your_posts'] = "Guarda i tuoi messaggi";
$lang['Search_unanswered'] = "Guarda i messaggi senza risposta";

$lang['Register'] = "Registrati";
$lang['Profile'] = "Profilo";
$lang['Edit_profile'] = "Modifica il tuo profilo";
$lang['Search'] = "Cerca";
$lang['Memberlist'] = "Lista degli utenti";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Guida per i BBCode";
$lang['Usergroups'] = "Gruppi utenti";
$lang['Last_Post'] = "Ultimo Messaggio";
$lang['Moderator'] = "Moderatore";
$lang['Moderators'] = "Moderatori";


//
// Stats block text
//
$lang['Posted_article_total'] = "I nostri utenti hanno inviato un totale di <b>%d</b> messaggio"; // Number of posts
$lang['Posted_articles_total'] = "I nostri utenti hanno inviato un totale di <b>%d</b> messaggi"; // Number of posts
$lang['Registered_user_total'] = "Abbiamo <b>%d</b> utente registrato"; // # registered users
$lang['Registered_users_total'] = "Abbiamo <b>%d</b> utenti registrati"; // # registered users
$lang['Newest_user'] = "L'ultimo utente registrato è <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Nessun nuovo messaggio dalla tua ultima visita";
$lang['No_new_posts'] = "Nessun nuovo messaggio";
$lang['New_posts'] = "Nuovi messaggi";
$lang['New_post'] = "Nuovo messaggio";
$lang['No_new_posts_hot'] = "Nessun nuovo messaggio [ Popolare ]";
$lang['New_posts_hot'] = "Nuovi messaggi [ Popolare ]";
$lang['No_new_posts_locked'] = "Nessun nuovo messaggio [ Bloccato ]";
$lang['New_posts_locked'] = "Nuovi messaggi [ Bloccato ]";
$lang['Forum_is_locked'] = "Il Forum è bloccato";


//
// Login
//
$lang['Enter_password'] = "Per favore inserisci il tuo username e la password per entrare";
$lang['Login'] = "Entra";
$lang['Logout'] = "Esci";

$lang['Forgotten_password'] = "Ho dimenticato la password";

$lang['Log_me_in'] = "Entra automaticamente ad ogni visita";

$lang['Error_login'] = "Hai inserito uno username sbagliato o inattivo o una password non valida";


//
// Index page
//
$lang['Index'] = "Indice";
$lang['No_Posts'] = "Nessun Messaggio";
$lang['No_forums'] = "Questo forum è vuoto";

$lang['Private_Message'] = "Messaggio Privato";
$lang['Private_Messages'] = "Messaggi Privati";
$lang['Who_is_Online'] = "Chi c'è in linea";

$lang['Mark_all_forums'] = "Segna tutti i forum come già letti";
$lang['Forums_marked_read'] = "Tutti i forum sono stati segnati come già letti";


//
// Viewforum
//
$lang['View_forum'] = "Guarda il Forum";

$lang['Forum_not_exist'] = "Il forum che hai selezionato non esiste";
$lang['Reached_on_error'] = "Sei arrivato in questa pagina per errore";

$lang['Display_topics'] = "Mostra prima gli argomenti di";
$lang['All_Topics'] = "Tutti gli argomenti";

$lang['Topic_Announcement'] = "<b>Annuncio:</b>";
$lang['Topic_Sticky'] = "<b>Importante:</b>";
$lang['Topic_Moved'] = "<b>Spostato:</b>";
$lang['Topic_Poll'] = "<b>[ Sondaggio ]</b>";

$lang['Mark_all_topics'] = "Segna tutti gli argomenti come già letti";
$lang['Topics_marked_read'] = "Gli argomenti per questo forum sono stati segnati come già letti";

$lang['Rules_post_can'] = "<b>Puoi</b> inserire nuovi argomenti in questo forum";
$lang['Rules_post_cannot'] = "<b>Non puoi</b> inserire nuovi argomenti in questo forum";
$lang['Rules_reply_can'] = "<b>Puoi</b> rispondere agli argomenti in questo forum";
$lang['Rules_reply_cannot'] = "<b>Non puoi</b> rispondere agli argomenti in questo forum";
$lang['Rules_edit_can'] = "<b>Puoi</b> modificare i tuoi messaggi in questo forum";
$lang['Rules_edit_cannot'] = "<b>Non puoi</b> modificare i tuoi messaggi in questo forum";
$lang['Rules_delete_can'] = "<b>Puoi</b> cancellare i tuoi messaggi in questo forum";
$lang['Rules_delete_cannot'] = "<b>Non puoi</b> cancellare i tuoi messaggi in questo forum";
$lang['Rules_vote_can'] = "<b>Puoi</b> votare nei sondaggi in questo forum";
$lang['Rules_vote_cannot'] = "<b>Non puoi</b> votare nei sondaggi in questo forum";
$lang['Rules_moderate'] = "<b>Puoi</b> %smoderare questo forum%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Non ci sono argomenti in questo forum<br />Clicca su <b>Inserisci un Nuovo Argomento</b> per inserirne uno";


//
// Viewtopic
//
$lang['View_topic'] = "Guarda l'argomento";

$lang['Guest'] = 'Ospite';
$lang['Post_subject'] = "Soggetto";
$lang['View_next_topic'] = "Argomento successivo";
$lang['View_previous_topic'] = "Argomento precedente";
$lang['Submit_vote'] = "Invia voto";
$lang['View_results'] = "Guarda i risultati";

$lang['No_newer_topics'] = "Non ci sono nuovi argomenti in questo forum";
$lang['No_older_topics'] = "Non ci sono vecchi argomenti in questo forum";
$lang['Topic_post_not_exist'] = "L'argomento o il messaggio che hai richiesto non esiste";
$lang['No_posts_topic'] = "Non ci sono messaggi in questo argomento";

$lang['Display_posts'] = "Mostra prima i messaggi di";
$lang['All_Posts'] = "Tutti i messaggi";
$lang['Newest_First'] = "Prima i nuovi";
$lang['Oldest_First'] = "Prima i vecchi";

$lang['Back_to_top'] = "Torna in cima";

$lang['Read_profile'] = "Guarda il profilo dell'utente"; 
$lang['Send_email'] = "Manda una e-mail all'utente";
$lang['Visit_website'] = "Visita il sito dell'autore del messaggio";
$lang['ICQ_status'] = "Stato ICQ";
$lang['Edit_delete_post'] = "Modifica/Cancella questo messaggio";
$lang['View_IP'] = "Guarda l'indirizzo IP dell'autore del messaggio";
$lang['Delete_post'] = "Cancella questo messaggio";

$lang['wrote'] = "ha scritto"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Citazione"; // comes before bbcode quote output.
$lang['Code'] = "Codice"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Ultima modifica di %s il %s, modificato %d volta in totale"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Ultima modifica di %s il %s, modificato %d volte in totale"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Blocca questo argomento";
$lang['Unlock_topic'] = "Sblocca questo argomento";
$lang['Move_topic'] = "Sposta questo argomento";
$lang['Delete_topic'] = "Cancella questo argomento";
$lang['Split_topic'] = "Spezza questo argomento";

$lang['Stop_watching_topic'] = "Smetti di controllare questo argomento";
$lang['Start_watching_topic'] = "Controlla questo argomento";
$lang['No_longer_watching'] = "Non stai più controllando questo argomento";
$lang['You_are_watching'] = "Adesso stai controllando questo argomento";

$lang['Total_votes'] = "Voti Totali";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Corpo del messaggio";
$lang['Topic_review'] = "Revisione argomento";

$lang['No_post_mode'] = "Modo di risposta non specificato"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Inserisci un nuovo argomento";
$lang['Post_a_reply'] = "Invia una risposta";
$lang['Post_topic_as'] = "Inserisci l'argomento come";
$lang['Edit_Post'] = "Modifica il messaggio";
$lang['Options'] = "Opzioni";

$lang['Post_Announcement'] = "Annuncio";
$lang['Post_Sticky'] = "Importante";
$lang['Post_Normal'] = "Normale";

$lang['Confirm_delete'] = "Sei sicuro di voler cancellare questo messaggio?";
$lang['Confirm_delete_poll'] = "Sei sicuro di voler cancellare questo sondaggio?";

$lang['Flood_Error'] = "Non puoi inviare un nuovo messaggio così presto dopo l'ultimo inserito, per favore prova di nuovo tra poco";
$lang['Empty_subject'] = "Devi specificare un soggetto quando inserisci un nuovo argomento";
$lang['Empty_message'] = "Devi scrivere un messaggio per inserirlo";
$lang['Forum_locked'] = "Questo forum è bloccato. Non puoi inserire, rispondere o modificare gli argomenti";
$lang['Topic_locked'] = "Questo argomento è bloccato. Non puoi modificare i messaggi o inserire una risposta";
$lang['No_post_id'] = "Devi selezionare un messaggio da modificare";
$lang['No_topic_id'] = "Devi selezionare un argomento a cui rispondere";
$lang['No_valid_mode'] = "Puoi solo inviare, rispondere, modificare o citare messaggi, per favore torna indietro e prova di nuovo";
$lang['No_such_post'] = "Questo messaggio non esiste, per favore torna indietro e prova di nuovo";
$lang['Edit_own_posts'] = "Spiacenti ma puoi modificare solo i tuoi messaggi";
$lang['Delete_own_posts'] = "Spiacenti ma puoi cancellare solo i tuoi messaggi";
$lang['Cannot_delete_replied'] = "Spiacenti ma non puoi cancellare i messaggi che hanno una risposta";
$lang['Cannot_delete_poll'] = "Spiacenti ma non puoi cancellare un sondaggio attivo";
$lang['Empty_poll_title'] = "Devi inserire un titolo per il tuo sondaggio";
$lang['To_few_poll_options'] = "Devi inserire almeno due opzioni per il sondaggio";
$lang['To_many_poll_options'] = "Hai cercato di inserire troppe opzioni per il sondaggio";
$lang['Post_has_no_poll'] = "Questo messaggio non ha nessun sondaggio";

$lang['Add_poll'] = "Aggiungi un sondaggio";
$lang['Add_poll_explain'] = "Se non vuoi aggiungere un sondaggio al tuo argomento lascia vuoti i campi";
$lang['Poll_question'] = "Domanda del sondaggio";
$lang['Poll_option'] = "Opzione del sondaggio";
$lang['Add_option'] = "Aggiungi un'opzione";
$lang['Update'] = "Aggiorna";
$lang['Delete'] = "Cancella";
$lang['Poll_for'] = "Attiva il sondaggio per";
$lang['Days'] = "Giorni"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Scrivi 0 o lascia vuoto per un sondaggio senza fine ]";
$lang['Delete_poll'] = "Cancella il sondaggio";

$lang['Disable_HTML_post'] = "Disabilita HTML in questo messaggio";
$lang['Disable_BBCode_post'] = "Disabilita il BBCode in questo messaggio";
$lang['Disable_Smilies_post'] = "Disabilita gli Smilies in questo messaggio";

$lang['HTML_is_ON'] = "HTML è <u>ATTIVATO</u>";
$lang['HTML_is_OFF'] = "HTML è <u>DISATTIVATO</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s è <u>ATTIVATO</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s è <u>DISATTIVATO</u>";
$lang['Smilies_are_ON'] = "Gli Smilies sono <u>ATTIVATI</u>";
$lang['Smilies_are_OFF'] = "Gli Smilies sono <u>DISATTIVATI</u>";

$lang['Attach_signature'] = "Aggiungi la firma (puoi cambiare la firma nel profilo)";
$lang['Notify'] = "Avvisami quando viene inviata una risposta";
$lang['Delete_post'] = "Cancella questo messaggio";

$lang['Stored'] = "Il tuo messaggio è stato inserito con successo";
$lang['Deleted'] = "Il tuo messaggio è stato cancellato con successo";
$lang['Poll_delete'] = "Il tuo sondaggio è stato cancellato con successo";
$lang['Vote_cast'] = "Il tuo voto è stato aggiunto";

$lang['Topic_reply_notification'] = "Notifica di risposta all'argomento";

$lang['bbcode_b_help'] = "Grassetto: [b]testo[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Corsivo: [i]testo[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Sottolineato: [u]testo[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Citazione: [quote]testo[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Codice: [code]codice[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Lista: [list]testo[/list] (alt+l)";
$lang['bbcode_o_help'] = "Lista ordinata: [list=]testo[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Inserisci un'immagine: [img]http://image_url[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Inserisci URL: [url]http://url[/url] o [url=http://url]testo URL[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Chiudi tutti i bbCode tags aperti";
$lang['bbcode_s_help'] = "Colore carattere: [color=red]testo[/color]  Suggerimento: puoi anche usare color=#FF0000";
$lang['bbcode_f_help'] = "Dimensione carattere: [size=x-small]testo piccolo[/size]";

$lang['Emoticons'] = "Emoticons";
$lang['More_emoticons'] = "Guarda altre Emoticons";

$lang['Font_color'] = "Colore carattere";
$lang['color_default'] = "Default";
$lang['color_dark_red'] = "Rosso scuro";
$lang['color_red'] = "Rosso";
$lang['color_orange'] = "Arancione";
$lang['color_brown'] = "Marrone";
$lang['color_yellow'] = "Giallo";
$lang['color_green'] = "Verde";
$lang['color_olive'] = "Oliva";
$lang['color_cyan'] = "Ciano";
$lang['color_blue'] = "Blu";
$lang['color_dark_blue'] = "Blu scuro";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Viola";
$lang['color_white'] = "Bianco";
$lang['color_black'] = "Nero";

$lang['Font_size'] = "Dimensione carattere";
$lang['font_tiny'] = "Minuscolo";
$lang['font_small'] = "Piccolo";
$lang['font_normal'] = "Normale";
$lang['font_large'] = "Largo";
$lang['font_huge'] = "Enorme";

$lang['Close_Tags'] = "Chiudi i Tags";
$lang['Styles_tip'] = "Suggerimento: gli Stili possono essere applicati velocemente al testo selezionato";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Messaggi Privati";

$lang['Login_check_pm'] = "Entra per controllare i messaggi privati";
$lang['New_pms'] = "%d nuovi messaggi"; // You have 2 new messages
$lang['New_pm'] = "%d nuovo messagggio"; // You have 1 new message
$lang['No_new_pm'] = "Non ci sono nuovi messaggi";
$lang['Unread_pms'] = "%d messaggi non letti";
$lang['Unread_pm'] = "%d messaggio non letto";
$lang['No_unread_pm'] = "Tutti i messaggi sono stati letti";
$lang['You_new_pm'] = "Hai un nuovo messaggio in Posta in Arrivo";
$lang['You_new_pms'] = "Ci sono nuovi messaggi in Posta in Arrivo";
$lang['You_no_new_pm'] = "Non ci sono nuovi messaggi";

$lang['Inbox'] = "Posta in Arrivo";
$lang['Outbox'] = "Posta in Uscita";
$lang['Savebox'] = "Posta Salvata";
$lang['Sentbox'] = "Posta Inviata";
$lang['Flag'] = "Flag";
$lang['Subject'] = "Soggetto";
$lang['From'] = "Da";
$lang['To'] = "A";
$lang['Date'] = "Data";
$lang['Mark'] = "Seleziona";
$lang['Sent'] = "Inviato";
$lang['Saved'] = "Salvato";
$lang['Delete_marked'] = "Cancella Selezionati";
$lang['Delete_all'] = "Cancella Tutti";
$lang['Save_marked'] = "Salva Selezionati"; 
$lang['Save_message'] = "Salva Messaggio";
$lang['Delete_message'] = "Cancella Messaggio";

$lang['Display_messages'] = "Mostra i messaggi di"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Tutti i messaggi";

$lang['No_messages_folder'] = "Non ci sono messaggi in questa cartella";

$lang['PM_disabled'] = "I messaggi privati sono stati disabilitati dal forum";
$lang['Cannot_send_privmsg'] = "Spiacenti, ma l'amministratore del forum ti ha vietato di mandare messaggi privati";
$lang['No_to_user'] = "Devi specificare uno username per inviare il messaggio";
$lang['No_such_user'] = "Spiacenti, ma questo utente non esiste";

$lang['Disable_HTML_pm'] = "Disabilita HTML in questo messaggio";
$lang['Disable_BBCode_pm'] = "Disabilita BBCode in questo messaggio";
$lang['Disable_Smilies_pm'] = "Disabilita Smilies in questo messaggio";

$lang['Message_sent'] = "Il tuo messaggio è stato spedito";

$lang['Click_return_inbox'] = "Clicca %squi%s per tornare alla cartella di Posta in Arrivo";
$lang['Click_return_index'] = "Clicca %squi%s per tornare all'indice";

$lang['Send_a_new_message'] = "Invia un nuovo messaggio privato";
$lang['Send_a_reply'] = "Rispondi ad un messaggio privato";
$lang['Edit_message'] = "Modifica un messaggio privato";

$lang['Notification_subject'] = "Hai un nuovo messaggio privato";

$lang['Find_username'] = "Trova uno username";
$lang['Find'] = "Trova";
$lang['No_match'] = "Nessun risultato";

$lang['No_post_id'] = "Non è stato specificato nessun ID";
$lang['No_such_folder'] = "Questa cartella non esiste";
$lang['No_folder'] = "Nessuna cartella specificata";

$lang['Mark_all'] = "Seleziona tutti";
$lang['Unmark_all'] = "Deseleziona tutto";

$lang['Confirm_delete_pm'] = "Sei sicuro di voler cancellare questo messaggio?";
$lang['Confirm_delete_pms'] = "Sei sicuro di voler cancellare questi messaggi?";

$lang['Inbox_size'] = "Cartella di Posta in Arrivo piena per il %d%%"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "Cartella di Posta in Uscita piena per il %d%%";
$lang['Savebox_size'] = "Cartella di Posta Salvata piena per il %d%%"; 

$lang['Click_view_privmsg'] = "Clicca %squi%s per andare alla cartella di Posta in Arrivo";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Guarda il profilo :: %s"; // %s is username 
$lang['About_user'] = "Tutto su %s"; // %s is username

$lang['Preferences'] = "Preferenze";
$lang['Items_required'] = "Le voci contrassegnate con * sono richieste";
$lang['Registration_info'] = "Informazioni sulla Registrazione";
$lang['Profile_info'] = "Informazioni sul Profilo";
$lang['Profile_info_warn'] = "Queste informazioni saranno visibili da tutti gli utenti";
$lang['Avatar_panel'] = "Pannello di controllo Avatar";
$lang['Avatar_gallery'] = "Galleria Avatar";

$lang['Website'] = "Sito web";
$lang['Location'] = "Località";
$lang['Contact'] = "Contatto";
$lang['Email_address'] = "Indirizzo Email";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Manda un messaggio privato";
$lang['Hidden_email'] = "[ Nascosto ]";
$lang['Search_user_posts'] = "Cerca i messaggi di questo utente";
$lang['Interests'] = "Interessi";
$lang['Occupation'] = "Occupazione"; 
$lang['Poster_rank'] = "Graduatoria Utente";

$lang['Total_posts'] = "Messaggi totali";
$lang['User_post_pct_stats'] = "%.2f%% del totale"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f messaggi al giorno"; // 1.5 posts per day
$lang['Search_user_posts'] = "Trova tutti i messaggi di %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Spiacenti, ma questo utente non esiste";
$lang['Wrong_Profile'] = "Non puoi modificare un profilo che non è il tuo";
$lang['Only_one_avatar'] = "Può essere specificato un solo tipo di avatar";
$lang['File_no_data'] = "Il file all'URL che hai fornito non contiene dati";
$lang['No_connection_URL'] = "Non è possibile connettersi all'URL che hai fornito";
$lang['Incomplete_URL'] = "L'URL che hai fornito è incompleto";
$lang['Wrong_remote_avatar_format'] = "L'URL dell'avatar remoto non è valido";
$lang['No_send_account_inactive'] = "Spiacenti, ma la tua password non può essere recuperata perchè il tuo account è al momento inattivo. Per favore contatta l'amministratore del forum per ulteriori informazioni";

$lang['Always_smile'] = "Abilita sempre gli Smilies";
$lang['Always_html'] = "Abilita sempre i codici HTML";
$lang['Always_bbcode'] = "Abilita sempre il BBCode";
$lang['Always_add_sig'] = "Aggiungi sempre la mia firma";
$lang['Always_notify'] = "Avvisami sempre delle risposte";
$lang['Always_notify_explain'] = "Manda una Email quando qualcuno risponde ad un argomento a cui hai risposto. Questo può essere cambiato ogni volta che inserisci un nuovo messaggio.";

$lang['Board_style'] = "Stile del forum";
$lang['Board_lang'] = "Linguaggio del forum";
$lang['No_themes'] = "Non ci sono temi nel database";
$lang['Timezone'] = "Fuso orario";
$lang['Date_format'] = "Formato della data";
$lang['Date_format_explain'] = "La sintassi usata è identica alla funzione PHP <a href=\"http://www.php.net/date\" target=\"_other\">data()</a>";
$lang['Signature'] = "Firma";
$lang['Signature_explain'] = "Questo è un blocco di testo che può essere aggiunto ai tuoi messaggi. C'è un limite di %d caratteri";
$lang['Public_view_email'] = "Mostra sempre il mio indirizzo Email";

$lang['Current_password'] = "Password attuale";
$lang['New_password'] = "Nuova password";
$lang['Confirm_password'] = "Conferma password";
$lang['Confirm_password_explain'] = "Devi confermare la tua password attuale se vuoi cambiarla o modificare il tuo indirizzo email";
$lang['password_if_changed'] = "Devi inserire la password solo se vuoi cambiarla";
$lang['password_confirm_if_changed'] = "Devi confermare la tua password solo se ne hai inserita una nuova qui sopra";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Mostra una piccola immagine grafica sotto i tuoi dettagli nel messaggio. Può essere mostrata una sola immagine alla volta, la sua larghezza massima è di %d pixels, l'altezza di %d pixels e il file deve essere più piccolo di %dkB.";
$lang['Upload_Avatar_file'] = "Carica l'Avatar dal tuo computer";
$lang['Upload_Avatar_URL'] = "Carica l'Avatar da un URL";
$lang['Upload_Avatar_URL_explain'] = "Inserisci l'URL dell'Avatar, verrà copiato in questo sito.";
$lang['Pick_local_Avatar'] = "Seleziona l'Avatar dalla gallery";
$lang['Link_remote_Avatar'] = "Link ad un Avatar off-site";
$lang['Link_remote_Avatar_explain'] = "Inserisci l'URL dell'Avatar che vuoi linkare";
$lang['Avatar_URL'] = "URL dell'Avatar";
$lang['Select_from_gallery'] = "Seleziona l'Avatar dalla gallery";
$lang['View_avatar_gallery'] = "Mostra la gallery";

$lang['Select_avatar'] = "Seleziona l'avatar";
$lang['Return_profile'] = "Cancella l'avatar";
$lang['Select_category'] = "Seleziona la categoria";

$lang['Delete_Image'] = "Cancella l'immagine";
$lang['Current_Image'] = "Immagine attuale";

$lang['Notify_on_privmsg'] = "Notifica sui nuovi Messaggi Privati";
$lang['Popup_on_privmsg'] = "Finestra di Popup sul nuovo Messaggio Privato";
$lang['Popup_on_privmsg_explain'] = "Alcuni modelli possono aprire una nuova finestra per informarti quando un nuovo messaggio arriva";
$lang['Hide_user'] = "Nascondi il tuo stato online";

$lang['Profile_updated'] = "Il tuo profilo è stato aggiornato";
$lang['Profile_updated_inactive'] = "Il tuo profilo è stato aggiornato. Hai modificato dettagli importanti anche se il tuo account non è ancora attivo. Controlla la tua email per riattivare il tuo account, o, se richiesta, aspetta la riattivazione da parte dell'amministratore";

$lang['Password_mismatch'] = "La password che hai inserito non corrisponde";
$lang['Current_password_mismatch'] = "La password attuale che hai fornito non corrisponde a quella inserita nel database";
$lang['Password_long'] = "La tua password non deve essere più lunga di 32 caratteri";
$lang['Username_taken'] = "Spiacenti, ma questo username esiste già";
$lang['Username_invalid'] = "Spiacenti, ma questo username contiene un carattere invalido come \"";
$lang['Username_disallowed'] = "Spiacenti, ma questo username è stato disabilitato";
$lang['Email_taken'] = "Spiacenti, ma questo indirizzo email è già stato reigstrato da un utente";
$lang['Email_banned'] = "Spiacenti, ma questo indirizzo email è stato escluso";
$lang['Email_invalid'] = "Spiacenti, ma questo indirizzo email non è valido";
$lang['Signature_too_long'] = "La tua firma è troppo lunga";
$lang['Fields_empty'] = "Devi riempire i campi richiesti";
$lang['Avatar_filetype'] = "Il file dell'avatar deve essere .jpg, .gif o .png";
$lang['Avatar_filesize'] = "La grandezza del file dell'avatar deve essere inferiore a %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "L'avatar deve essere più piccolo di %d pixels di larghezza e di %d pixels di altezza"; 
$lang['Welcome_subject'] = "Benvenuto nel forum di %s"; // Welcome to my.com forums
$lang['New_account_subject'] = "Account Nuovo Utente";
$lang['Account_activated_subject'] = "Account Attivato";

$lang['Account_added'] = "Grazie per esserti registrato, il tuo account è stato creato. Adesso puoi entrare con il tuo username e password";
$lang['Account_inactive'] = "Il tuo account è stato creato. Comunque, questo forum richiede l'attivaione dell'account. La chiave per l'attivazione è stata mandata all'indirizzo email che hai inserito. Per favore controlla la tua email per ulteriori informazioni.";
$lang['Account_inactive_admin'] = "Il tuo account è stato creato. Comunque questo forum richiede l'attivazione dell'account da parte dell'amministratore. Una email è stata spedita all'amministratore e sarai informato quando il tuo account sarà attivato";
$lang['Account_active'] = "Il tuo account è stato attivato. Grazie per esserti registrato.";
$lang['Account_active_admin'] = "L'account è stato attivato.";
$lang['Reactivate'] = "Riattiva il tuo account!";
$lang['COPPA'] = "Il tuo account è stato creato, ma deve essere approvato. Per favore controlla la tua email per ulteriori dettagli.";

$lang['Registration'] = "Termini per la registrazione";
$lang['Reg_agreement'] = "Poichè gli amministratori e i moderatori di questo forum cercheranno di rimuovere o modificare tutto il materiale contestabile il più velocemente possibile, è comunque impossibile verificare ogni messaggio. Tuttavia sei consapevole che tutti i messaggi di questo forum esprimono il punto di vista e le opinioni dell'autore e non quelle degli amministratori, dei moderatori o del webmaster (eccetto i messaggi degli stessi) e per questo non sono perseguibili.<br /><br />L'utente concorda di non inviare messaggi abusivi, osceni, volgari, diffamatori, di odio, minatori, sessuali o qualunque altro materiale che possa violare qualunque legge applicabile. Inserendo messaggi di questo tipo l'utente verrà immediatamente e permanentemente escluso (e il tuo provider verrà informato). L'indirizzo IP di tutti i messaggi viene registrato per aiutare a rinforzare queste condizioni. L'utente concorda che il webmaster, l'amministratore e i moderatori di questo forum hanno il diritto di rimuovere, modificare, o chiudere ogni argomento ogni volta che lo ritengano necessario. Come utente concordi che ogni informazione che è stata inserita verrà conservata in un database. Poichè queste informazioni non verranno cedute a terzi senza il tuo consenso, il webmaster, l'amministratore e i moderatori non possono essere ritenuti responsabili per gli attacchi da parte degli hackers che possano compromettere i dati.<br /><br />Questo forum usa i cookies per conservare informazioni sul tuo computer locale. Questi cookies non contengono le informazioni che hai inserito in prcedenza, servono soltanto per migliorare il piacere della tua visita. L'indirizzo email viene utilizzato solo per confermare i dettagli della tua registrazione e per la password (e per inviare una nuova password nel caso dovessi perdere quella attuale).<br /><br />Cliccando Registra qui sotto accetti queste condizioni.";

$lang['Agree_under_13'] = "Accetto queste condizioni e ho <b>meno</b> di 13 anni";
$lang['Agree_over_13'] = "Accetto queste condizioni";
$lang['Agree_not'] = "Non accetto queste condizioni";

$lang['Wrong_activation'] = "La chiave di attivazione che hai fornito non corrisponde a nessuna nel database";
$lang['Send_password'] = "Inviami una nuova password"; 
$lang['Password_updated'] = "Una nuova password è stata creata, per favore controlla la tua email per maggiori dettagli su come attivarla";
$lang['No_email_match'] = "L'indirizzo email che hai fornito non corrisponde a quello inserito per questo username";
$lang['New_password_activation'] = "Attivazione nuova password";
$lang['Password_activated'] = "Il tuo account è stato riattivato. Per entrare usa la password che hai ricevuto nella email";

$lang['Send_email_msg'] = "Invia un messaggio email";
$lang['No_user_specified'] = "Non è stato specificato nessun utente";
$lang['User_prevent_email'] = "Questo utente non gradisce ricevere email. Prova ad inviare un messaggio privato";
$lang['User_not_exist'] = "Questo utente non esiste";
$lang['CC_email'] = "Invia una copia di questa email a te stesso";
$lang['Email_message_desc'] = "Questo messaggio verrà inviato come testo, non includere nessun codice HTML o BBCode. L'indirizzo per la risposta per questo messaggio è il tuo indirizzo email.";
$lang['Flood_email_limit'] = "Non puoi inviare un'altra email al momento. Prova più tardi.";
$lang['Recipient'] = "Cestino";
$lang['Email_sent'] = "Questa email è stata inviata";
$lang['Send_email'] = "Invia email";
$lang['Empty_subject_email'] = "Devi specificare un soggetto per l'email";
$lang['Empty_message_email'] = "Devi inserire un messaggio da inviare";


//
// Memberslist
//
$lang['Select_sort_method'] = "Seleziona un ordine";
$lang['Sort'] = "Ordina";
$lang['Sort_Top_Ten'] = "I migliori 10 autori";
$lang['Sort_Joined'] = "Data di registrazione";
$lang['Sort_Username'] = "Username";
$lang['Sort_Location'] = "Località";
$lang['Sort_Posts'] = "Messaggi totali";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Sito Web";
$lang['Sort_Ascending'] = "Crescente";
$lang['Sort_Descending'] = "Decrescente";
$lang['Order'] = "Ordina";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Pannello di Controllo Gruppo";
$lang['Group_member_details'] = "Dettagli Membri Gruppo";
$lang['Group_member_join'] = "Iscrivi un Gruppo Utenti";

$lang['Group_Information'] = "Informazioni sul Gruppo";
$lang['Group_name'] = "Nome Gruppo";
$lang['Group_description'] = "Descrizione Gruppo";
$lang['Group_membership'] = "Appartenenza al Gruppo";
$lang['Group_Members'] = "Membri del Gruppo";
$lang['Group_Moderator'] = "Moderatore del Gruppo";
$lang['Pending_members'] = "Membri in attesa";

$lang['Group_type'] = "Tipo di Gruppo";
$lang['Group_open'] = "Gruppo aperto";
$lang['Group_closed'] = "Gruppo chiuso";
$lang['Group_hidden'] = "Gruppo nascosto";

$lang['Current_memberships'] = "Membri attuali";
$lang['Non_member_groups'] = "Gruppi non membri";
$lang['Memberships_pending'] = "Membri in attesa";

$lang['No_groups_exist'] = "Non esistono gruppi";
$lang['Group_not_exist'] = "Quel gruppo di utenti non esiste";

$lang['Join_group'] = "Iscriviti ad un Gruppo";
$lang['No_group_members'] = "Questo gruppo non ha membri";
$lang['Group_hidden_members'] = "Questo gruppo è nascosto, non puoi vedere i suoi membri";
$lang['No_pending_group_members'] = "Questo gruppo non ha membri in attesa";
$lang["Group_joined"] = "Ti sei iscritto a questo gruppo con succeso.<br />Sarai avvisato quando la tua iscrizione verrà approvata dal moderatore del gruppo.";
$lang['Group_request'] = "C'è una richiesta di iscrizione al tuo gruppo";
$lang['Group_approved'] = "La tua richiesta è stata approvata";
$lang['Group_added'] = "Sei stato aggiunto a questa gruppo utenti";
$lang['Already_member_group'] = "Sei già un membro di questo gruppo";
$lang['User_is_member_group'] = "L'utente è già un membro di questo gruppo";
$lang['Group_type_updated'] = "Tipo di Gruppo aggiornato con successo";

$lang['Could_not_add_user'] = "L'utente che hai selezionato non esiste";

$lang['Confirm_unsub'] = "Sei sicuro di volerti cancellare da questo gruppo?";
$lang['Confirm_unsub_pending'] = "La tua iscrizione a questo gruppo non è ancora stata approvata, sei sicuro di volerti cancellare?";

$lang['Unsub_success'] = "Sei stato cancellato da questo gruppo";

$lang['Approve_selected'] = "Approvazione selezionata";
$lang['Deny_selected'] = "Rifiuto selezionato";
$lang['Not_logged_in'] = "Per iscriverti ad un gruppo devi essere registrato.";
$lang['Remove_selected'] = "Rimuovi selezionati";
$lang['Add_member'] = "Aggiungi Membro";
$lang['Not_group_moderator'] = "Non sei il moderatore di questo gruppo, quindi non puoi eseguire questa azione.";

$lang['Login_to_join'] = "Entra per iscriverti o gestire un gruppo di utenti";
$lang['This_open_group'] = "Questo è un gruppo aperto, clicca per richiedere l'adesione";
$lang['This_closed_group'] = "Questo è un gruppo chiuso, non si accettano altri membri";
$lang['This_hidden_group'] = "Questo è un gruppo nascosto, non è permesso aggiungere nuovi utenti automaticamente";
$lang['Member_this_group'] = "Sei un membro di questo gruppo";
$lang['Pending_this_group'] = "La tua iscrizione a questo gruppo è in attesa";
$lang['Are_group_moderator'] = "Sei il moderatore di questo gruppo";
$lang['None'] = "Niente";

$lang['Subscribe'] = "Iscriviti";
$lang['Unsubscribe'] = "Cancella";
$lang['View_Information'] = "Guarda Informazioni";


//
// Search
//
$lang['Search_query'] = "Frase di ricerca";
$lang['Search_options'] = "Opzioni di ricerca";

$lang['Search_keywords'] = "Ricerca per parole chiave";
$lang['Search_keywords_explain'] = "Puoi usare <u>AND</u> per definire le parole che devono essere nel risultato della ricerca, <u>OR</u> per definire le parole che potrebbero essere nel risultato e <u>NOT</u> per definire le parole che non devono essere nel risultato. Usa * come abbrevazione per parole parziali";
$lang['Search_author'] = "Cerca per autore";
$lang['Search_author_explain'] = "Usa * come abbreviazione per parole parziali";

$lang['Search_for_any'] = "Cerca per ogni parola oppure usa la frase esatta";
$lang['Search_for_all'] = "Cerca tutti i termini";

$lang['Search_title_msg'] = "Cerca nel titolo e nel testo"; 
$lang['Search_msg_only'] = "Cerca solo nel testo"; 
$lang['Return_first'] = "Dai i primi"; // followed by xxx characters in a select box
$lang['characters_posts'] = "caratteri del messaggio";

$lang['Search_previous'] = "Cerca i messaggi di"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Ordina per";
$lang['Sort_Time'] = "Data messaggio";
$lang['Search_title_msg'] = "Cerca nel titolo dell'argomento e nel testo del messaggio"; 
$lang['Search_msg_only'] = "Cerca solo nel testo del messaggio"; 
$lang['Sort_Post_Subject'] = "Soggetto messaggio";
$lang['Sort_Topic_Title'] = "Titolo argomento";
$lang['Sort_Author'] = "Autore";
$lang['Sort_Forum'] = "Forum";

$lang['Display_results'] = "Mostra i risultati come";
$lang['All_available'] = "Tutto disponibile";
$lang['No_searchable_forums'] = "Non hai il permesso di cercare nel forum di questo sito";

$lang['No_search_match'] = "Nessun argomento o messaggio con questo criterio di ricerca";
$lang['Found_search_match'] = "La ricerca ha trovato %d risultato"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Laricerca ha trovato %d risultati"; // eg. Search found 24 matches

$lang['Close_window'] = "Chiudi finestra";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Spiacenti, ma solo %s possono inviare annunci in questo forum";
$lang['Sorry_auth_sticky'] = "Spiacenti, ma solo %s possono inviare messaggi importanti in questo forum";
$lang['Sorry_auth_read'] = "Spiacenti, ma solo %s possono leggere gli argomenti in questo forum";
$lang['Sorry_auth_post'] = "Spiacenti, ma solo %s possono inserire argomenti in questo forum"; 
$lang['Sorry_auth_reply'] = "Spiacenti, ma solo %s possono rispondere ai messaggi in questo forum"; 
$lang['Sorry_auth_edit'] = "Spiacenti, ma solo %s possono modificare i messaggi in questo forum";
$lang['Sorry_auth_delete'] = "Spiacenti, ma solo %s possono cancellare i messaggi in questo forum";
$lang['Sorry_auth_vote'] = "Spiacenti, ma solo %s possono votare nei sondaggi in questo forum";

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>gli utenti anonimi</b>";
$lang['Auth_Registered_Users'] = "<b>gli utenti registrati</b>";
$lang['Auth_Users_granted_access'] = "<b>gli utenti con accesso speciale</b>";
$lang['Auth_Moderators'] = "<b>i moderatori</b>";
$lang['Auth_Administrators'] = "<b>gli amministratori</b>";

$lang['Not_Moderator'] = "Non sei un moderatore di questo forum";
$lang['Not_Authorised'] = "Non Autorizzato";

$lang['You_been_banned'] = "Sei stato escluso da questo forum<br />per favore contatta il webmaster o l'amministratore per ulteriori informazioni";


//
// Viewonline
//
$lang['Reg_user_online'] = "C'è %d Utente Registrato e "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Ci sono %d Utenti Registrati e "; // There ae 5 Registered and
$lang['Hidden_user_online'] = "%d Utente Nascosto in linea"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d Utenti Nascosti in linea"; // 6 Hidden users online
$lang['Guest_users_online'] = "Ci sono %d Ospiti in linea"; // There are 10 Guest users online
$lang['Guest_user_online'] = "C'è %d Ospite in linea"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Al momento non ci sono utenti nel forum";

$lang['Online_explain'] = "Questi dati si basano sugli utenti in linea negli ultimi cinque minuti";

$lang['Forum_Location'] = "Località del Forum";
$lang['Last_updated'] = "Ultimo aggiornamento";

$lang['Forum_index'] = "Indice Forum";
$lang['Logging_on'] = "Sta entrando";
$lang['Posting_message'] = "Sta inviando un messaggio";
$lang['Searching_forums'] = "Sta cercando nei forum";
$lang['Viewing_profile'] = "Sta guardando il profilo";
$lang['Viewing_online'] = "Sta guardando chi c'è in linea";
$lang['Viewing_member_list'] = "Sta guardando la lista degli utenti";
$lang['Viewing_priv_msgs'] = "Sta guardando i messaggi privati";
$lang['Viewing_FAQ'] = "Sta guardando le FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Pannello di Controllo Moderatori";
$lang['Mod_CP_explain'] = "Utilizzando il modulo qui sotto puoi eseguire operazioni di massa su questo forum. Puoi bloccare, sbloccare, spostare o cancellare qualunque numero di argomenti.";

$lang['Select'] = "Seleziona";
$lang['Delete'] = "Cancella";
$lang['Move'] = "Sposta";
$lang['Lock'] = "Blocca";
$lang['Unlock'] = "Sblocca";

$lang['Topics_Removed'] = "Gli argomenti selezionati sono stati rimossi dal database con successo.";
$lang['Topics_Locked'] = "Gli argomenti selezionati sono stati bloccati";
$lang['Topics_Moved'] = "Gli argomenti selezionati sono stati spostati";
$lang['Topics_Unlocked'] = "Gli argomenti selezionati sono stati sbloccati";
$lang['No_Topics_Moved'] = "Non è stato spostato nessun argomento";

$lang['Confirm_delete_topic'] = "Sei sicuro di voler eliminare gli argomenti selezionati?";
$lang['Confirm_lock_topic'] = "Sei sicuro di voler bloccare gli argomenti selezionati?";
$lang['Confirm_unlock_topic'] = "Sei sicuro di voler sbloccare gli argomenti selezionati?";
$lang['Confirm_move_topic'] = "Sei sicuro di voler spostare gli argomenti selezionati?";

$lang['Move_to_forum'] = "Vai al forum";
$lang['Leave_shadow_topic'] = "Lascia un argomento ombra nel vechio forum";

$lang['Split_Topic'] = "Pannello di Controllo per la Divisione degli Argomenti";
$lang['Split_Topic_explain'] = "Utilizzando il modulo qui sotto puoi dividere un argomento in due, sia selezionando i messaggi individualmente, sia dividendo l'argomento da un messaggio selezionato in poi";
$lang['Split_title'] = "Titolo nuovo argomento";
$lang['Split_forum'] = "Forum per il nuove argomento";
$lang['Split_posts'] = "Dividi i messaggi selezionati";
$lang['Split_after'] = "Dividi dal messaggio selezionato";
$lang['Topic_split'] = "L'argomento selezionato è stato diviso con successo";

$lang['Too_many_error'] = "Hai selezionato troppi messaggi. Puoi selezionare solo il messaggio da cui dividere l'argomento";

$lang['None_selected'] = "Non hai selezionato nessun argomento nel quale eseguire questa operazione. Per favore torna indietro e selezionane almeno uno.";
$lang['New_forum'] = "Nuovo forum";

$lang['This_posts_IP'] = "Indirizzo IP per questo messaggio";
$lang['Other_IP_this_user'] = "Altri indirizzo IP utilizzati da questo utente";
$lang['Users_this_IP'] = "Utenti che utilizzano questo indirizzo IP";
$lang['IP_info'] = "Informazioni sull'indirizzo IP";
$lang['Lookup_IP'] = "Cerca l'indirizzo IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Tutti i fusi orari sono %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "GMT - 12 Ore";
$lang['-11'] = "GMT - 11 ore";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Ore";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (U.S./Canada)";
$lang['-4'] = "GMT - 4 Ore";
$lang['-3.5'] = "GMT - 3.5 Ore";
$lang['-3'] = "GMT - 3 Ore";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 Ora";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europa)";
$lang['2'] = "EET (Europa)";
$lang['3'] = "GMT + 3 Ore";
$lang['3.5'] = "GMT + 3.5 Ore";
$lang['4'] = "GMT + 4 Ore";
$lang['4.5'] = "GMT + 4.5 Ore";
$lang['5'] = "GMT + 5 Ore";
$lang['5.5'] = "GMT + 5.5 Ore";
$lang['6'] = "GMT + 6 Ore";
$lang['7'] = "GMT + 7 Ore";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9 Ore";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11 Ore";
$lang['12'] = "GMT + 12 Ore";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(GMT -12:00 ore) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 ore) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 ore) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 ore) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 ore) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 ore) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 ore) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 ore) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(GMT -4:00 ore) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 ore) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 ore) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 ore) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 ora) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 ora) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 ore) Cairo, Helsinki, Kaliningrad, South Africa";
$lang['tz']['3'] = "(GMT +3:00 ore) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 ore) Tehran";
$lang['tz']['4'] = "(GMT +4:00 ore) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 ore) Kabul";
$lang['tz']['5'] = "(GMT +5:00 ore) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 ore) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 ore) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 ore) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 ore) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 ore) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 ore) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 ore) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 ore) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 ore) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 ore) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "Domenica";
$lang['days_long'][1] = "Lunedì";
$lang['days_long'][2] = "Martedì";
$lang['days_long'][3] = "Mercoledì";
$lang['days_long'][4] = "Giovedì";
$lang['days_long'][5] = "Venerdì";
$lang['days_long'][6] = "Sabato";

$lang['days_short'][0] = "Dom";
$lang['days_short'][1] = "Lun";
$lang['days_short'][2] = "Mar";
$lang['days_short'][3] = "Mer";
$lang['days_short'][4] = "Gio";
$lang['days_short'][5] = "Ven";
$lang['days_short'][6] = "Sab";

$lang['months_long'][0] = "Gennaio";
$lang['months_long'][1] = "Febbraio";
$lang['months_long'][2] = "Marzo";
$lang['months_long'][3] = "Aprile";
$lang['months_long'][4] = "Maggio";
$lang['months_long'][5] = "Giugno";
$lang['months_long'][6] = "Luglio";
$lang['months_long'][7] = "Agosto";
$lang['months_long'][8] = "Settembre";
$lang['months_long'][9] = "Ottobre";
$lang['months_long'][10] = "Novembre";
$lang['months_long'][11] = "Dicembre";

$lang['months_short'][0] = "Gen";
$lang['months_short'][1] = "Feb";
$lang['months_short'][2] = "Mar";
$lang['months_short'][3] = "Apr";
$lang['months_short'][4] = "Mag";
$lang['months_short'][5] = "Giu";
$lang['months_short'][6] = "Lug";
$lang['months_short'][7] = "Ago";
$lang['months_short'][8] = "Set";
$lang['months_short'][9] = "Ott";
$lang['months_short'][10] = "Nov";
$lang['months_short'][11] = "Dic";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Informazione";
$lang['Critical_Information'] = "Informazione Critica";

$lang['General_Error'] = "Errore Generale";
$lang['Critical_Error'] = "Errore Critico";
$lang['An_error_occured'] = "Si è verificato un errore";
$lang['A_critical_error'] = "Si è verificato un errore critico";

//
// That's all Folks!
// -------------------------------------------------

?>