<?php
/***************************************************************************
 *                            lang_main.php [Catalan]
 *                              -------------------
 *     begin                : Wed Jul 10 2002
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
// Traducció                : Isaac Garcia Abrodos
// Nom d'usuari             : xEik

//
// El format del fitxer és ---> $lang['message'] = "text";
//
// També hauries d'intentar configurar locale i la codificació de caràcters (més direcció). La codificació i direcció 
// seran enviats a la plantilla. El locale pot ser que funcioni o no, depèn del suport del Sistema Operatiu i la sintaxi
// varia ... esculli com millor li sembli!
//

$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "d M Y"; // Això s'hauria de canviar al format per defecte del seu idioma, format com el de php date()

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
 $lang['TRANSLATION_INFO'] = 'Traducció: Isaac Garcia Abrodos';
 
//
// Comuns, aquests termes s'utilitzen de
// forma extensiva en diverses pàgines
//
$lang['Forum'] = "Fòrum";
$lang['Category'] = "Categoria";
$lang['Topic'] = "Tema";
$lang['Topics'] = "Temes";
$lang['Replies'] = "Respostes";
$lang['Views'] = "Lectures";
$lang['Post'] = "Missatge";
$lang['Posts'] = "Missatges";
$lang['Posted'] = "Publicat";
$lang['Username'] = "Nom d'usuari";
$lang['Password'] = "Contrasenya";
$lang['Email'] = "Email";
$lang['Poster'] = "Autor";
$lang['Author'] = "Autor";
$lang['Time'] = "Hora";
$lang['Hours'] = "Hores";
$lang['Message'] = "Missatge";

$lang['1_Day'] = "1 Dia";
$lang['7_Days'] = "7 Dies";
$lang['2_Weeks'] = "2 Setmanes";
$lang['1_Month'] = "1 Mes";
$lang['3_Months'] = "3 Mesos";
$lang['6_Months'] = "6 Mesos";
$lang['1_Year'] = "1 Any";

$lang['Go'] = "Anar";
$lang['Jump_to'] = "Canviar a";
$lang['Submit'] = "Trametre";
$lang['Reset'] = "Desfer canvis";
$lang['Cancel'] = "Cancel·lar";
$lang['Preview'] = "Vista Preliminar";
$lang['Confirm'] = "Confirmar";
$lang['Spellcheck'] = "Ortografia";
$lang['Yes'] = "Sí";
$lang['No'] = "No";
$lang['Enabled'] = "Habilitat";
$lang['Disabled'] = "Deshabilitat";
$lang['Error'] = "Error";

$lang['Next'] = "Següent";
$lang['Previous'] = "Anterior";
$lang['Goto_page'] = "Anar a pàgina";
$lang['Joined'] = "Registrat";
$lang['IP_Address'] = "Direcció IP";

$lang['Select_forum'] = "Selecciona un fòrum";
$lang['View_latest_post'] = "Veure l'últim missatge";
$lang['View_newest_post'] = "Veure el missatge més recent";
$lang['Page_of'] = "Pàgina <b>%d</b> de <b>%d</b>"; // És substituït per : Pàgina 1 de 2 per exemple

$lang['ICQ'] = "Número ICQ";
$lang['AIM'] = "Direcció AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "Índex del Fòrum de %s";  // eg. Índex del Fòrum de Nom de la Pàgina Web, %s es pot treure si ho prefereixes

$lang['Post_new_topic'] = "Publicar un tema nou";
$lang['Reply_to_topic'] = "Respondre al tema";
$lang['Reply_with_quote'] = "Respondre citant";

$lang['Click_return_topic'] = "Clica %saquí%s per tornar al tema"; // %s's són pe als url, no els tregui!
$lang['Click_return_login'] = "Clica %saquí%s per intentar-ho de nou";
$lang['Click_return_forum'] = "Clica %saquí%s per tornar al fòrum";
$lang['Click_view_message'] = "Clica %saquí%s per veure el teu missatge";
$lang['Click_return_modcp'] = "Clica %saquí%s per tornar al Quadre de Control del Moderador";
$lang['Click_return_group'] = "Clica %saquí%s per tornar a la Informació del Grup";

$lang['Admin_panel'] = "Anar al Quadre d'Administració";

$lang['Board_disable'] = "Ens sap greu però aquest fòrum ara mateix no es troba disponible, si us plau intenti-ho de nou més tard";


//
// Cadenes de Capçaleres Globals
//
$lang['Registered_users'] = "Usuaris Registrats:";
$lang['Browsing_forum'] = "Usuaris navegant aquest fòrum:";
$lang['Online_users_zero_total'] = "En total hi ha <b>0</b> usuaris connectats :: ";
$lang['Online_users_total'] = "En total hi ha <b>%d</b> usuaris connectats :: ";
$lang['Online_user_total'] = "En total hi ha <b>%d</b> usuari connectat :: ";
$lang['Reg_users_zero_total'] = "0 Registrats, ";
$lang['Reg_users_total'] = "%d Registrats, ";
$lang['Reg_user_total'] = "%d Registrat, ";
$lang['Hidden_users_zero_total'] = "0 Ocults i ";
$lang['Hidden_user_total'] = "%d Ocults i ";
$lang['Hidden_users_total'] = "%d Ocult i ";
$lang['Guest_users_zero_total'] = "0 Invitats";
$lang['Guest_users_total'] = "%d Invitats";
$lang['Guest_user_total'] = "%d Invitat";
$lang['Record_online_users'] = "El major nombre d'usuaris connectats fou <b>%s</b> el %s"; // primer %s = nombre d'usuaris, segon %s és la data.

$lang['Admin_online_color'] = "%sAdministrador%s";
$lang['Mod_online_color'] = "%sModerador%s";

$lang['You_last_visit'] = "La teva darrera visita va ser el: %s"; // %s substituït per data i hora
$lang['Current_time'] = "Data i hora actual: %s"; // %s substituït per hora

$lang['Search_new'] = "Veure els missatges des de la teva darrera visita";
$lang['Search_your_posts'] = "Veure els teus missatges";
$lang['Search_unanswered'] = "Veure els missatges sense resposta";

$lang['Register'] = "Registrar-se";
$lang['Profile'] = "Perfil";
$lang['Edit_profile'] = "Editar el teu perfil";
$lang['Search'] = "Buscar";
$lang['Memberlist'] = "Llista de Membres";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Guia BBCode";
$lang['Usergroups'] = "Grups d'Usuaris";
$lang['Last_Post'] = "Darrer Missatge";
$lang['Moderator'] = "Moderador";
$lang['Moderators'] = "Moderadors";


//
// Bloc de text d'estadístiques
//
$lang['Posted_articles_zero_total'] = "Els nostres usuaris han publicat un total de <b>0</b> missatges"; // Nombre de missatges
$lang['Posted_articles_total'] = "Els nostres usuaris han publicat un total de <b>%d</b> missatges"; // Nombre de missatges
$lang['Posted_article_total'] = "Els nostres usuaris han publicat un total de <b>%d</b> missatge"; // Nombre de missatges
$lang['Registered_users_zero_total'] = "Tenim <b>0</b> usuaris registrats"; // # usuaris registrats
$lang['Registered_users_total'] = "Tenim <b>%d</b> usuaris registrats"; // # usuaris registrats
$lang['Registered_user_total'] = "Tenim <b>%d</b> usuari registrat"; // # usuaris registrats
$lang['Newest_user'] = "El darrer usuari registrat és <b>%s%s%s</b>"; // un enllaç al nom d'usuari, /a 

$lang['No_new_posts_last_visit'] = "No hi ha missatges nous des de la teva darrera visita";
$lang['No_new_posts'] = "No hi ha missatges nous";
$lang['New_posts'] = "Hi ha missatges nous";
$lang['New_post'] = "Missatge nou";
$lang['No_new_posts_hot'] = "No hi ha missatges nous [ Popular ]";
$lang['New_posts_hot'] = "Hi ha missatges nous [ Popular ]";
$lang['No_new_posts_locked'] = "No hi ha missatges nous [ Bloquejat ]";
$lang['New_posts_locked'] = "Hi ha missatges nous [ Bloquejat ]";
$lang['Forum_is_locked'] = "El fòrum està bloquejat";


//
// Inici de Sessió
//
$lang['Enter_password'] = "Si us plau introdueix el teu nom d'usuari i contrasenya per iniciar la sessió";
$lang['Login'] = "Iniciar Sessió";
$lang['Logout'] = "Sortir";

$lang['Forgotten_password'] = "He oblidat la meva contrasenya";

$lang['Log_me_in'] = "Iniciar sessió automàticament a cada visita";

$lang['Error_login'] = "Has escrit un nom d'usuari incorrecte o inactiu o bé una contrasenya incorrecta";


//
// Pàgina d'Índex
//
$lang['Index'] = "Índex";
$lang['No_Posts'] = "No hi ha missatges";
$lang['No_forums'] = "Aquest tauler no té fòrums";

$lang['Private_Message'] = "Missatge Privat";
$lang['Private_Messages'] = "Missatges Privats";
$lang['Who_is_Online'] = "Qui està Connectat";

$lang['Mark_all_forums'] = "Marcar com a llegits tots els fòrums";
$lang['Forums_marked_read'] = "Tots els fòrums s'han marcat com a llegits";


//
// Veure fòrum
//
$lang['View_forum'] = "Veure Fòrum";

$lang['Forum_not_exist'] = "El fòrum seleccionat no existeix";
$lang['Reached_on_error'] = "Has arribat per error a aquesta pàgina";

$lang['Display_topics'] = "Mostrar temes anteriors";
$lang['All_Topics'] = "Tots els Temes";

$lang['Topic_Announcement'] = "<b>Anunci:</b>";
$lang['Topic_Sticky'] = "<b>Permanent:</b>";
$lang['Topic_Moved'] = "<b>Mogut:</b>";
$lang['Topic_Poll'] = "<b>[ Enquesta ]</b>";

$lang['Mark_all_topics'] = "Marcar com a llegits tots els temes";
$lang['Topics_marked_read'] = "Els temes d'aquest fòrum s'han marcat com a llegits";

$lang['Rules_post_can'] = "<b>Pots</b> publicar nous temes en aquest fòrum";
$lang['Rules_post_cannot'] = "<b>No pots</b> publicar nous temes en aquest fòrum";
$lang['Rules_reply_can'] = "<b>Pots</b> respondre a temes en aquest fòrum";
$lang['Rules_reply_cannot'] = "<b>No pots</b> respondre a temes en aquest fòrum";
$lang['Rules_edit_can'] = "<b>Pots</b> editar els teus missatges en aquest fòrum";
$lang['Rules_edit_cannot'] = "<b>No pots</b> editar els teus missatges en aquest fòrum";
$lang['Rules_delete_can'] = "<b>Pots</b> esborrar els teus missatges en aquest fòrum";
$lang['Rules_delete_cannot'] = "<b>No pots</b> esborrar els teus missatges aquest fòrum";
$lang['Rules_vote_can'] = "<b>Pots</b> votar a les enquestes en aquest fòrum";
$lang['Rules_vote_cannot'] = "<b>No pots</b> votar a les enquestes en aquest fòrum";
$lang['Rules_moderate'] = "<b>Pots</b> %smoderar aquest fòrum%s"; // %s substituït per links a href, no treure! 

$lang['No_topics_post_one'] = "No hi ha temes en aquest fòrum<br />Clica sobre <b>Nou Tema</b> per publicar-ne un";


//
// Veure tema
//
$lang['View_topic'] = "Veure tema";

$lang['Guest'] = 'Invitat';
$lang['Post_subject'] = "Assumpte";
$lang['View_next_topic'] = "Veure tema següent";
$lang['View_previous_topic'] = "Veure tema anterior";
$lang['Submit_vote'] = "Votar";
$lang['View_results'] = "Veure resultats";

$lang['No_newer_topics'] = "No hi ha temes posteriors en aquest fòrum";
$lang['No_older_topics'] = "No hi ha temes anteriors en aquest fòrum";
$lang['Topic_post_not_exist'] = "El tema o missatge sol·licitat no existeix";
$lang['No_posts_topic'] = "No existeix cap missatge per a aquest tema";

$lang['Display_posts'] = "Mostrar missatges d'anteriors";
$lang['All_Posts'] = "Tots els missatges";
$lang['Newest_First'] = "El més recent primer";
$lang['Oldest_First'] = "El més antic primer";

$lang['Back_to_top'] = "Tornar a dalt";

$lang['Read_profile'] = "Veure perfil de l'usuari"; 
$lang['Send_email'] = "Enviar email a l'usuari";
$lang['Visit_website'] = "Visitar pàgina web de l'autor";
$lang['ICQ_status'] = "Status ICQ";
$lang['Edit_delete_post'] = "Editar/Esborrar aquest missatge";
$lang['View_IP'] = "Veure IP de l'autor";
$lang['Delete_post'] = "Esborrar aquest missatge";

$lang['wrote'] = "escrigué"; // precedeix al nom d'usuari i és seguit pel text citat
$lang['Quote'] = "Cita"; // ve abans de la sortida de bbcode citar
$lang['Code'] = "Codi"; // ve abans de la sortida de bbcode codi

$lang['Edited_time_total'] = "Editat per darrera vegada per %s el %s, editat %d cop en total"; // Editat per darrera vegada per mi el 12 Oct 2001, editat 1 cop en total
$lang['Edited_times_total'] = "Editat per darrera vegada per %s el %s, editat %d cops en total"; // Editat per darrera vegada per mi el 12 Oct 2001, editat 2 cops en total

$lang['Lock_topic'] = "Bloquejar aquest tema";
$lang['Unlock_topic'] = "Desbloquejar aquest tema";
$lang['Move_topic'] = "Moure aquest tema";
$lang['Delete_topic'] = "Esborrar aquest tema";
$lang['Split_topic'] = "Separar aquest tema";

$lang['Stop_watching_topic'] = "Deixar d'observar aquest tema";
$lang['Start_watching_topic'] = "Observar aquest tema per respostes";
$lang['No_longer_watching'] = "Ja no estàs observant aquest tema";
$lang['You_are_watching'] = "Ara estàs observant aquest tema";

$lang['Total_votes'] = "Vots Totals";

//
// Publicar/Respondre (No missatges privats!)
//
$lang['Message_body'] = "Cos del missatge";
$lang['Topic_review'] = "Revisar tema";

$lang['No_post_mode'] = "No s'ha especificat mode de missatge"; // Si es crida posting.php sense un mode (newtopic/reply/delete/etc, no hauria de mostrar-se normalment)

$lang['Post_a_new_topic'] = "Publicar un nou tema";
$lang['Post_a_reply'] = "Publicar una resposta";
$lang['Post_topic_as'] = "Publicar tema com";
$lang['Edit_Post'] = "Editar missatge";
$lang['Options'] = "Opcions";

$lang['Post_Announcement'] = "Anunci";
$lang['Post_Sticky'] = "Permanent";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "Estàs segur que vols esborrar aquest missatge?";
$lang['Confirm_delete_poll'] = "Estàs segur que vols esborrar aquesta enquesta?";

$lang['Flood_Error'] = "No pots publicar un altre missatge tan aviat després del darrer, si us plau intenta-ho de nou en uns instants";
$lang['Empty_subject'] = "Has d'especificar un assumpte quan publiques un nou tema";
$lang['Empty_message'] = "Has d'escriure un missatge quan publiques";
$lang['Forum_locked'] = "Aquest fòrum està bloquejat i no hi pots publicar, respondre o editar temes";
$lang['Topic_locked'] = "Aquest tema està bloquejat i no hi pots editar temes ni publicar respostes";
$lang['No_post_id'] = "Has de seleccionar un missatge per editar";
$lang['No_topic_id'] = "Has de seleccionar un tema al qual respondre";
$lang['No_valid_mode'] = "Només pots publicar, respondre editar o citar missatges, si us plau torni i intenti-ho de nou";
$lang['No_such_post'] = "No existeix tal missatge, si us plau torna i intenta-ho de nou";
$lang['Edit_own_posts'] = "Ho sentim però només pots editar els teus propis missatges";
$lang['Delete_own_posts'] = "Ho sentim però només pots esborrar els teus propis missatges";
$lang['Cannot_delete_replied'] = "Ho sentim però no pots esborrar missatges als quals ja s'ahgi respost";
$lang['Cannot_delete_poll'] = "Ho sentim però no pots esborrar una enquesta activa";
$lang['Empty_poll_title'] = "Has d'escriure un títol por a l'enquesta";
$lang['To_few_poll_options'] = "Has d'introduir almenys dues opcions per a l'enquesta";
$lang['To_many_poll_options'] = "Has provat d'introduir massa opcions a l'enquesta";
$lang['Post_has_no_poll'] = "Aquest missatge no té enquesta";
$lang['Already_voted'] = "Ja has votat en aquesta enquesta";
$lang['No_vote_option'] = "Quan votes has d'especificar una opció";

$lang['Add_poll'] = "Afegir una enquesta";
$lang['Add_poll_explain'] = "Si no vols afegir una enquesta al tema deixa els camps en blanc";
$lang['Poll_question'] = "Pregunta de l'enquesta";
$lang['Poll_option'] = "Opció de l'enquesta";
$lang['Add_option'] = "Afegir opció";
$lang['Update'] = "Actualitzar";
$lang['Delete'] = "Esborrar";
$lang['Poll_for'] = "Fer anar enquesta durant";
$lang['Days'] = "Dies"; // Això s'utilitza per Fer anar enquesta durant ... Dies + a admin_forums per netejar la taula
$lang['Poll_for_explain'] = "[ Escriu 0 o deixa-ho en blanc per que l'enquesta no acabi ]";
$lang['Delete_poll'] = "Esborrar Enquesta";

$lang['Disable_HTML_post'] = "Deshabilitar HTML en aquest missatge";
$lang['Disable_BBCode_post'] = "Deshabilitar BBCode en aquest missatge";
$lang['Disable_Smilies_post'] = "Deshabilitar Smilies en aquest missatge";

$lang['HTML_is_ON'] = "l'HTML està <u>ACTIU</u>";
$lang['HTML_is_OFF'] = "l'HTML està <u>INACTIU</u>";
$lang['BBCode_is_ON'] = "el %sBBCode%s està <u>ACTIU</u>"; // %s són substituïts per URL's que apunten al FAQ
$lang['BBCode_is_OFF'] = "el %sBBCode%s està <u>INACTIU</u>";
$lang['Smilies_are_ON'] = "els Smileys estan <u>ACTIUS</u>";
$lang['Smilies_are_OFF'] = "els Smileys estan <u>INACTIUS</u>";

$lang['Attach_signature'] = "Adjuntar signatura (la signatura pot ser canviada al perfil)";
$lang['Notify'] = "Notifiqueu-me quan es publiqui una resposta";
$lang['Delete_post'] = "Esborrar aquest missatge";

$lang['Stored'] = "El teu missatge s'ha publicat correctament";
$lang['Deleted'] = "El teu missatge s'ha esborrat correctament";
$lang['Poll_delete'] = "La teva enquesta s'ha esborrat correctament";
$lang['Vote_cast'] = "El teu vot ha estat emès";

$lang['Topic_reply_notification'] = "Notificació de Resposta al Tema";

$lang['bbcode_b_help'] = "Negreta: [b]text[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Cursiva: [i]text[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Subratllat: [u]text[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Cita: [quote]text[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Codi: [code]codi[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Llista: [list]text[/list] (alt+l)";
$lang['bbcode_o_help'] = "Llista ordenada: [list=]text[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Inserir imatge: [img]http://url_imatge[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Inserir URL: [url]http://url[/url] o [url=http://url]text URL[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Tancar tots els marcadors de bbCode oberts";
$lang['bbcode_s_help'] = "Color: [color=red]text[/color]  Nota: També pots utilitzar color=#FF0000";
$lang['bbcode_f_help'] = "Mida: [size=x-small]text petit[/size]";

$lang['Emoticons'] = "Emoticons";
$lang['More_emoticons'] = "Veure més Emoticons";

$lang['Font_color'] = "Color de font";
$lang['color_default'] = "Predeterminat";
$lang['color_dark_red'] = "Roig Fosc";
$lang['color_red'] = "Roig";
$lang['color_orange'] = "Taronja";
$lang['color_brown'] = "Marró";
$lang['color_yellow'] = "Groc";
$lang['color_green'] = "Verd";
$lang['color_olive'] = "Oliva";
$lang['color_cyan'] = "Cian";
$lang['color_blue'] = "Blau";
$lang['color_dark_blue'] = "Blau Fosc";
$lang['color_indigo'] = "Índigo";
$lang['color_violet'] = "Violeta";
$lang['color_white'] = "Blanc";
$lang['color_black'] = "Negre";

$lang['Font_size'] = "Mida";
$lang['font_tiny'] = "Miniatura";
$lang['font_small'] = "Petita";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Gran";
$lang['font_huge'] = "Enorme";

$lang['Close_Tags'] = "Tancar marcadors";
$lang['Styles_tip'] = "Nota: Es poden aplicar estils ràpidament al text seleccionat";


//
// Missatgeria Privada
//
$lang['Private_Messaging'] = "Missatges Privats";

$lang['Login_check_pm'] = "Inicia una sessió per veure els teus missatges privats";
$lang['New_pms'] = "Tens %d missatges nous"; // Tens 2 missatges nous
$lang['New_pm'] = "Tens %d missatge nou"; // Tens un missatge nou
$lang['No_new_pm'] = "No tens missatges nous";
$lang['Unread_pms'] = "Tens %d missatges sense llegir";
$lang['Unread_pm'] = "Tens %d missatge sense llegir";
$lang['No_unread_pm'] = "No tens missatges sense llegir";
$lang['You_new_pm'] = "Tens un missatge privat nou a la bústia d'entrada";
$lang['You_new_pms'] = "Tens missatges nous a la bústia d'entrada";
$lang['You_no_new_pm'] = "No tens missatges privats nous";

$lang['Unread_message'] = "Missatge no llegit";
$lang['Read_message'] = "Missatge llegit";

$lang['Read_pm'] = "Llegir missatge";
$lang['Post_new_pm'] = "Escriure missatge";
$lang['Post_reply_pm'] = "Respondre el missatge";
$lang['Post_quote_pm'] = "Citar el missatge";
$lang['Edit_pm'] = "Editar el missatge";

$lang['Inbox'] = "Bústia d'Entrada";
$lang['Outbox'] = "Bústia de Sortida";
$lang['Savebox'] = "Elements Guardats";
$lang['Sentbox'] = "Elements Enviats";
$lang['Flag'] = "Marca";
$lang['Subject'] = "Assumpte";
$lang['From'] = "De";
$lang['To'] = "Per";
$lang['Date'] = "Data";
$lang['Mark'] = "Marcar";
$lang['Sent'] = "Enviat";
$lang['Saved'] = "Guardat";
$lang['Delete_marked'] = "Esborrar Marcats";
$lang['Delete_all'] = "Esborrar Tots";
$lang['Save_marked'] = "Guardar Marcats"; 
$lang['Save_message'] = "Guardar Missatge";
$lang['Delete_message'] = "Esborrar Missatge";

$lang['Display_messages'] = "Mostrar missatges dels últims"; // Seguit pel # de dies/setmanes/mesos
$lang['All_Messages'] = "Tots els missatges";

$lang['No_messages_folder'] = "No tens missatges en aquesta carpeta";

$lang['PM_disabled'] = "Els Missatges Privats estan desactivats en aquest fòrum";
$lang['Cannot_send_privmsg'] = "Ho sentim però l'administrador t'ha prohibit enviar missatges privats";
$lang['No_to_user'] = "Has d'especificar un nom d'usuari per enviar aquest missatge";
$lang['No_such_user'] = "Ho sentim però aquest usuari no existeix";

$lang['Disable_HTML_pm'] = "Deshabilitar HTML en aquest missatge";
$lang['Disable_BBCode_pm'] = "Deshabilitar BBCode en aquest missatge";
$lang['Disable_Smilies_pm'] = "Deshabilitar Smilies en aquest missatge";

$lang['Message_sent'] = "El teu missatge ha estat enviat";

$lang['Click_return_inbox'] = "Clica %saquí%s per tornar a la teva Bústia d'Entrada";
$lang['Click_return_index'] = "Clica %saquí%s per tornar a l'Índex";

$lang['Send_a_new_message'] = "Enviar un nou missatge privat";
$lang['Send_a_reply'] = "Respondre a un missatge privat";
$lang['Edit_message'] = "Editar missatge privat";

$lang['Notification_subject'] = "Ha arribat un missatge privat nou";

$lang['Find_username'] = "Trobar nom d'usuari";
$lang['Find'] = "Trobar";
$lang['No_match'] = "No s'ha trobat cap coincidència";

$lang['No_post_id'] = "No s'ha especificat cap ID de missatge";
$lang['No_such_folder'] = "No existeix tal carpeta";
$lang['No_folder'] = "No s'ha especificat cap carpeta";

$lang['Mark_all'] = "Marcar tots";
$lang['Unmark_all'] = "Desmarcar tots";

$lang['Confirm_delete_pm'] = "Estàs segur que vols esborrar aquest missatge?";
$lang['Confirm_delete_pms'] = "Estàs segur que vols esborrar aquests missatges?";

$lang['Inbox_size'] = "La teva Bústia d'Entrada està plena al %d%%"; // eg. La teva Bústia d'Entrada està plena al 50%
$lang['Sentbox_size'] = "La teva Bústia d'Elements Enviats està plena al %d%%"; 
$lang['Savebox_size'] = "La teva Bústia d'Elements Guardats està plena al %d%%"; 

$lang['Click_view_privmsg'] = "Clica %saquí%s per visitar la teva Bústia d'Entrada";


//
// Perfils/Registre
//
$lang['Viewing_user_profile'] = "Veient perfil :: %s"; // %s és nom d'usuari 
$lang['About_user'] = "Tot sobre %s"; // %s és nom d'usuari

$lang['Preferences'] = "Preferències";
$lang['Items_required'] = "Els camps marcats amb * són obligatoris si no és que s'indica el contrari";
$lang['Registration_info'] = "Informació de Registre";
$lang['Profile_info'] = "Informació de Perfil";
$lang['Profile_info_warn'] = "Aquesta informació serà pública";
$lang['Avatar_panel'] = "Quadre de Control de l'Avatar";
$lang['Avatar_gallery'] = "Galeria d'Avatars";

$lang['Website'] = "Pàgina Web";
$lang['Location'] = "Ubicació";
$lang['Contact'] = "Contactar";
$lang['Email_address'] = "Adreça electrònica";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Enviar missatge privat";
$lang['Hidden_email'] = "[ Ocult ]";
$lang['Search_user_posts'] = "Buscar missatges d'aquest usuari";
$lang['Interests'] = "Interessos";
$lang['Occupation'] = "Ocupació"; 
$lang['Poster_rank'] = "Rang de l'Autor";

$lang['Total_posts'] = "Nombre total de missatges";
$lang['User_post_pct_stats'] = "%.2f%% del total"; // 1.25% del total
$lang['User_post_day_stats'] = "%.2f missatges per dia"; // 1.5 missatges per dia
$lang['Search_user_posts'] = "Trobar tots el missatges de %s"; // Trobar tots els missatges de nom d'usuari

$lang['No_user_id_specified'] = "Ho sentim però aquest usuari no existeix";
$lang['Wrong_Profile'] = "No pots modificar un perfil que no sigui el teu";

$lang['Only_one_avatar'] = "Només es pot especificar un tipus d'avatar";
$lang['File_no_data'] = "L'arxiu a l'URL proporcionat no conté dades";
$lang['No_connection_URL'] = "No s'ha pogut establir connexió amb l'URL proporcionat";
$lang['Incomplete_URL'] = "L'URL introduït està incomplet";
$lang['Wrong_remote_avatar_format'] = "L'URL de l'avatar remot no és vàlid";
$lang['No_send_account_inactive'] = "Ho sentim però la teva contrasenya no pot ser recuperada perque el teu compte actualment està desactivat. Si us plau contacta amb l'administrador del fòrum";

$lang['Always_smile'] = "Activar sempre els Smileys";
$lang['Always_html'] = "Permetre sempre l'HTML";
$lang['Always_bbcode'] = "Permetre sempre el BBCode";
$lang['Always_add_sig'] = "Adjuntar sempre la meva signatura";
$lang['Always_notify'] = "Ser avisat sempre de les respostes";
$lang['Always_notify_explain'] = "Envia un email quan algú respon a un tema que tu has publicat. Això es pot canviar sempre que publiquis un tema";

$lang['Board_style'] = "Estil del Fòrum";
$lang['Board_lang'] = "Idioma del Fòrum";
$lang['No_themes'] = "No hi ha temes a la base de dades";
$lang['Timezone'] = "Zona horària";
$lang['Date_format'] = "Format de la Data";
$lang['Date_format_explain'] = "La sintaxi usada és idèntica a la funció <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> de PHP";
$lang['Signature'] = "Signatura";
$lang['Signature_explain'] = "Aquest és un bloc de text que pot ser afegit als missatges que publiquis. Té un límit de %d caràcters";
$lang['Public_view_email'] = "Mostrar sempre el meu email";

$lang['Current_password'] = "Contrasenya actual";
$lang['New_password'] = "Nova contrasenya";
$lang['Confirm_password'] = "Confirmar contrasenya";
$lang['Confirm_password_explain'] = "Has de confirmar la teva contrasenya actual si desitges canviar-la o cambiar la direcció de correu electrònic";
$lang['password_if_changed'] = "Només cal que escriguis una contrasenya si vols canviar-la";
$lang['password_confirm_if_changed'] = "Només cal que confirmis la contrasenya si l'has canviat a dalt";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Mostra una petita imatge sota els teus detalls als missatges. Només es pot mostrar una imatge cada vegada, la seva amplada no pot ser major que %d pixels, la seva alçada no major que %d pixels i la mida de l'arxiu no més de %d kB"; $lang['Upload_Avatar_file'] = "Pujar Avatar des del teu PC";
$lang['Upload_Avatar_URL'] = "Pujar Avatar des d'un URL";
$lang['Upload_Avatar_URL_explain'] = "Introdueix l'URL on es troba l'arxiu d'imatge del teu Avatar i serà copiat a aquesta pàgina web";
$lang['Pick_local_Avatar'] = "Seleccionar Avatar de la galeria";
$lang['Link_remote_Avatar'] = "Vincular a un Avatar fora de la pàgina web";
$lang['Link_remote_Avatar_explain'] = "Introdueix l'URL on es troba l'arxiu d'imatge del teu Avatar";
$lang['Avatar_URL'] = "URL de la imatge d'Avatar";
$lang['Select_from_gallery'] = "Seleccionar Avatar de la nostra galeria";
$lang['View_avatar_gallery'] = "Mostrar galeria";

$lang['Select_avatar'] = "Seleccionar avatar";
$lang['Return_profile'] = "Cancel·lar avatar";
$lang['Select_category'] = "Seleccionar categoria";

$lang['Delete_Image'] = "Esborrar imatge";
$lang['Current_Image'] = "Imatge Actual";

$lang['Notify_on_privmsg'] = "Ser notificat quan tingui Missatges Privats nous";
$lang['Popup_on_privmsg'] = "Desplegar una finestra nova quan tingui Missatges Privats nous";
$lang['Popup_on_privmsg_explain'] = "Algunes plantilles poden obrir una finestra nova per tal d'informar-te quan arriben Missatges Privats nous"; 
$lang['Hide_user'] = "Ocultar el meu estat de connexió";

$lang['Profile_updated'] = "El teu perfil s'ha actualitzat";
$lang['Profile_updated_inactive'] = "El teu perfil s'ha actualitzat, no obstant, has canviat detalls importants i per això s'ha desactivat el teu compte. Revisa el teu email per esbrinar com reactivar el teu compte, o si és necessària l'activació de l'administrador espera a que aquest reactivi el teu compte";

$lang['Password_mismatch'] = "Les contrasenyes que has introduït no coincideixen";
$lang['Current_password_mismatch'] = "La contrasenya que has escrit no coincideix amb la que està emmagatzemada a la base de dades";
$lang['Password_long'] = "La teva contrasenya no ha de contenir més de 32 caràcters";
$lang['Username_taken'] = "Ho sentim però aquest nom d'usuari ja està agafat";
$lang['Username_invalid'] = "El nom d'usuari conté un caràcter invàlid com \"";
$lang['Username_disallowed'] = "Ho sentim però aquest nom d'usuari ha estat deshabilitat";
$lang['Email_taken'] = "Ho sentim però aquesta adreça de correu electrònic ja està registrada per un usuari";
$lang['Email_banned'] = "Ho sentim però aquesta direcció de correu ha estat prohibida";
$lang['Email_invalid'] = "La direcció de correu electrònic és invàlida";
$lang['Signature_too_long'] = "La signatura és massa llarga";
$lang['Fields_empty'] = "Has de completar els camps obligatoris";
$lang['Avatar_filetype'] = "El tipus d'imatge de l'avatar ha de ser .jpg, .gif o .png";
$lang['Avatar_filesize'] = "La mida de l'arxiu de l'avatar ha de ser menor de %d kB"; // El tamany de l'arxiu de l'avatar ha de ser menor de 6 kB
$lang['Avatar_imagesize'] = "L'avatar ha de ser de menys de %d pixels d'amplada per %d pixels d'alçada"; 

$lang['Welcome_subject'] = "Benvingut als Fòrums de %s"; // Benvingut als Fòrums de Nom de la pàgina web
$lang['New_account_subject'] = "Nou compte d'usuari";
$lang['Account_activated_subject'] = "Compte Activat";

$lang['Account_added'] = "Gràcies per registrar-te, el teu compte ha estat creat. Ara pots iniciar una sessió amb el teu nom d'usuari i contrasenya";
$lang['Account_inactive'] = "El teu compte ha esta creat. No obstant, aquest fòrum requereix l'activació del compte una clau d'activació s'ha enviat a l'adreça electrònica que ens has proporcionat. Si us plau revisa el teu email per més informació";
$lang['Account_inactive_admin'] = "El teu compte ha esta creat. No obstant, aquest fòrum requereix l'activació de l'administrador. S'ha enviat un email a l'administrador i seràs informat quan el teu compte sigui activat";
$lang['Account_active'] = "El teu compte ha estat activat. Gràcies per registrar-te";
$lang['Account_active_admin'] = "El compte ha estat activat";
$lang['Reactivate'] = "Reactiva el teu compte!";
$lang['Already_activated'] = "ja has reactivat el teu compte";
$lang['COPPA'] = "El teu compte ha estat creat però ha de ser aprovat, si us plau revisa el teu email per més detalls";

$lang['Registration'] = "Condicions de Registre";
$lang['Reg_agreement'] = "Tot i que els administradors i moderadors d'aquest fòrum faran tot el que sigui possible per eliminar o editar qualsevol material qüestionable tan ràpidament com sigui possible, és impossible revisar cada missatge. Per tant et dones per assabentat que tots els missatges publicats en aquests fòrums expressen els punts de vista i opinions dels seus respectius autors i no la dels administradors, moderadors o el webmaster (excepte els missatges publicats por ells mateixos) per la qual cosa no se'ls considerarà responsables.<br /><br />Estàs d'acord en no publicar material insultant, obscè, vulgar, d'odi, amenaçant, orientat sexualment, o cap altre que d'alguna manera violi lleis vigents. Si publiques material d'aquesta índole el teu compte serà cancel·lat (i el teu proveïdor d'accés a internet avisat). La direcció IP de tots els missatges es guardada per ajudar a complir aquestes normes. Estàs d'acord amb que el webmaster, administrador y moderadores d'aquest Fòrum tenen dret a esborrar, editar, moure o tancar qualsevol tema en qualsevol moment si ho consideren convenient. Com a usuari acceptes que tota la informació que has introduït sigui emmagatzemada en una base de dades. Tot i que aquesta informació no serà proporcionada a tercers sense el teu consentiment, el webmaster, l'administrador y els moderadores no poden responsabilitzar-se per intents de hackers que puguin portar a que aquesta informació es vegi compromesa.<br /><br />Aquest sistema de fòrums utilitza cookies per emmagatzemar informació a la teva computadora local. Aquestes cookies no contenen la informació que has introduït, només s'utilitzen millorar la visualització dels fòrums. L'email només s'utilitza per confirmar els detalls del teu registre i contrasenya (i per enviar noves contrasenyes si oblides la actual).<br /><br />En registrar-te acceptes totes aquestes condicions.";

$lang['Agree_under_13'] = "Estic d'acord amb aquestes condicions i tinc <b>menys</b> de 13 anys d'edat";
$lang['Agree_over_13'] = "Estic d'acord amb aquestes condicions i tinc <b>exactament</b> o <b>més</b> de 13 anys d'edat";
$lang['Agree_not'] = "No estic d'acord amb aquestes condicions";

$lang['Wrong_activation'] = "La clau d'activació subministrada no coincideix amb cap de les de la base de dades";
$lang['Send_password'] = "Envieu-me una nova contrasenya"; 
$lang['Password_updated'] = "S'ha creat una nova contrasenya, si us plau revisa el teu email pels detalles sobre com activar-la";
$lang['No_email_match'] = "L'email subministrat no coincideix amb el del teu nom d'usuari";
$lang['New_password_activation'] = "Activació de nova contrasenya";
$lang['Password_activated'] = "El teu compte ha esta reactivat. Per iniciar una sessió utilitza la contrasenya proporcionada a l'email que has rebut";

$lang['Send_email_msg'] = "Enviar un email";
$lang['No_user_specified'] = "No s'ha especificat usuari";
$lang['User_prevent_email'] = "Aquest usuari no desitja rebre email. Intenta enviar-li un missatge privat";
$lang['User_not_exist'] = "Aquest usuari no existeix";
$lang['CC_email'] = "Enviar-te una còpia d'aquest missatge a tu mateix";
$lang['Email_message_desc'] = "Aquest missatge serà enviat com a text simple, no hi incloguis HTML ni BBCode. La direcció de resposta per aquest missatge serà el teu email";
$lang['Flood_email_limit'] = "No pots enviar un altre email en aquest moment, intenta-ho més tard";
$lang['Recipient'] = "Destinatari";
$lang['Email_sent'] = "L'email ha estat enviat";
$lang['Send_email'] = "Enviar email";
$lang['Empty_subject_email'] = "Has d'especificar un assumpte per a l'email";
$lang['Empty_message_email'] = "Has d'escriure un missatge per que sigui enviat";


//
// Llistat de membres
//
$lang['Select_sort_method'] = "Ordenar per";
$lang['Sort'] = "Ordenar";
$lang['Sort_Top_Ten'] = "Els 10 autors que més escriuen";
$lang['Sort_Joined'] = "Data de Registre";
$lang['Sort_Username'] = "Nom d'usuari";
$lang['Sort_Location'] = "Ubicació";
$lang['Sort_Posts'] = "Quantitat de missatges";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Pàgina Web";
$lang['Sort_Ascending'] = "Ascendent";
$lang['Sort_Descending'] = "Descendent";
$lang['Order'] = "Ordre";


//
// Quadre de control de grups
//
$lang['Group_Control_Panel'] = "Quadre de Control de Grups";
$lang['Group_member_details'] = "Detalls d'Afiliació a Grups";
$lang['Group_member_join'] = "Unir-se al Grup";

$lang['Group_Information'] = "Informació del Grup";
$lang['Group_name'] = "Nom del Grup";
$lang['Group_description'] = "Descripció del Grup";
$lang['Group_membership'] = "Afiliació del Grup";
$lang['Group_Members'] = "Membres del Grup";
$lang['Group_Moderator'] = "Moderador del Grup";
$lang['Pending_members'] = "Membres Pendents";

$lang['Group_type'] = "Tipus de Grup";
$lang['Group_open'] = "Grup Obert";
$lang['Group_closed'] = "Grup Tancat";
$lang['Group_hidden'] = "Grup Ocult";

$lang['Current_memberships'] = "Afiliacions actuals";
$lang['Non_member_groups'] = "Grups dels que no ets membre";
$lang['Memberships_pending'] = "Afiliacions pendents";

$lang['No_groups_exist'] = "No existeix cap Grup";
$lang['Group_not_exist'] = "Aquest grup no existeix";

$lang['Join_group'] = "Unir-se al Grup";
$lang['No_group_members'] = "Aquest grup no té membres";
$lang['Group_hidden_members'] = "Aquest grup està ocult, no en pots veure el membres";
$lang['No_pending_group_members'] = "Aquest grup no té membres pendents";
$lang["Group_joined"] = "T'has subscrit amb èxit a aquest grup<br />Se't notificarà quan la teva subscripció sigui aprovada pel moderador del grup";
$lang['Group_request'] = "Se ha realitzat una petició per unir-se al grup";
$lang['Group_approved'] = "La teva petició ha estat aprovada";
$lang['Group_added'] = "Se t'ha afegit a aquest grup d'usuaris"; 
$lang['Already_member_group'] = "Ja ets membre d'aquest grup";
$lang['User_is_member_group'] = "L'usuari ja és membre d'aquest grup";
$lang['Group_type_updated'] = "El tipus de grup s'ha actualitzat correctament";

$lang['Could_not_add_user'] = "L'usuari seleccionat no existeix";
$lang['Could_not_anon_user'] = "No pots fer Anònim membre d'aquest grup";

$lang['Confirm_unsub'] = "Estàs segur que vols cancel·lar la subscripció a aquest grup?";
$lang['Confirm_unsub_pending'] = "La teva subscripció a aquest grup encara no ha estat aprovada, estàs segur que vols cancel·lar la subscripció?";

$lang['Unsub_success'] = "La teva subscripció a aquest grup ha estat cancel·lada";

$lang['Approve_selected'] = "Aprovar els Seleccionats";
$lang['Deny_selected'] = "Denegar els Seleccionats";
$lang['Not_logged_in'] = "Has d'haver iniciat una sessió per unir-te al grup";
$lang['Remove_selected'] = "Esborrar els Seleccionats";
$lang['Add_member'] = "Afegir Membre";
$lang['Not_group_moderator'] = "No ets el moderador d'aquest grup i per tant no pots realitzar aquesta acció";

$lang['Login_to_join'] = "Inicia una sessió per unir-te a un grup o administrar les afiliacions de grup";
$lang['This_open_group'] = "Aquest és un grup obert, clica per sol·licitar-ne l'afiliació";
$lang['This_closed_group'] = "Aquest és un grup tancat, no s'accepten més usuaris";
$lang['This_hidden_group'] = "Aquest és un grup ocult, no es permet l'addició automàtica d'usuaris";
$lang['Member_this_group'] = "Ets membre d'aquest grup";
$lang['Pending_this_group'] = "La teva afiliació a aquest grup està pendent";
$lang['Are_group_moderator'] = "Ets el moderador del grup";
$lang['None'] = "Cap";

$lang['Subscribe'] = "Subscriure's";
$lang['Unsubscribe'] = "Cancel·lar Subscripció";
$lang['View_Information'] = "Veure Informació";


//
// Cerca
//
$lang['Search_query'] = "Consulta de Cerca";
$lang['Search_options'] = "Opcions de Cerca";

$lang['Search_keywords'] = "Buscar per paraules clau";
$lang['Search_keywords_explain'] = "Pots utilitzar <u>AND</u> per definir paraules que han de ser als resultats, <u>OR</u> per definir paraules que poden ser als resultats i <u>NOT</u> per definir paraules que no han de ser als resultats. Utilitza * com a comodí per a coincidències parcials";
$lang['Search_author'] = "Buscar per Autor";
$lang['Search_author_explain'] = "Utilitza * com a comodí per a coincidències parcials";

$lang['Search_for_any'] = "Buscar qualsevol de les paraules o utilitzar consulta tal com s'ha escrit";
$lang['Search_for_all'] = "Buscar totes les paraules";
$lang['Search_title_msg'] = "Buscar  títols i text dels missatges";
$lang['Search_msg_only'] = "Buscar només al text dels missatges";

$lang['Return_first'] = "Mostrar els primers"; // seguit de xxx caràcters en una llista desplegable
$lang['characters_posts'] = "caràcters dels missatges";

$lang['Search_previous'] = "Buscar en els anteriors"; // seguit per dies, setmanes, mesos, anys, en una llista desplegable

$lang['Sort_by'] = "Ordenar per";
$lang['Sort_Time'] = "Data de Publicació";
$lang['Sort_Post_Subject'] = "Assumpte del Missatge";
$lang['Sort_Topic_Title'] = "Títol del Tema";
$lang['Sort_Author'] = "Autor";
$lang['Sort_Forum'] = "Fòrum";

$lang['Display_results'] = "Mostrar resultats com";
$lang['All_available'] = "Tots els disponibles";
$lang['No_searchable_forums'] = "No tens permís per buscar en cap dels fòrums d'aquesta pàgina web";

$lang['No_search_match'] = "No hi ha temes o missatges que coincideixin amb els criteris de cerca";
$lang['Found_search_match'] = "S'ha trobat %d coincidència"; // eg. S'ha trobat 1 coincidència
$lang['Found_search_matches'] = "S'han trobat %d coincidències"; // eg. S'han trobat 24 coincidències

$lang['Close_window'] = "Tancar finestra";


//
// Entrades relacionades amb autoritzacions
//
// Els %s seran substituïts amb un dels següents arrays d'usuari
$lang['Sorry_auth_announce'] = "Ho sentim però només els %s poden publicar anuncis en aquest fòrum";
$lang['Sorry_auth_sticky'] = "Ho sentim però només els %s poden publicar missatges permanents en aquest fòrum";
$lang['Sorry_auth_read'] = "Ho sentim però només els %s poden llegir temes en aquest fòrum";
$lang['Sorry_auth_post'] = "Ho sentim però només els %s poden publicar temes en aquest fòrum";
$lang['Sorry_auth_reply'] = "Ho sentim però només els %s poden respondre missatges en aquest fòrum";
$lang['Sorry_auth_edit'] = "Ho sentim però només els %s poden editar missatges en aquest fòrum";
$lang['Sorry_auth_delete'] = "Ho sentim però només els %s poden esborrar missatges en aquest fòrum";
$lang['Sorry_auth_vote'] = "Ho sentim però només els %s votar a les enquestes en aquest fòrum";

// Aquests substitueixen els %s en les cadenes de dalt
$lang['Auth_Anonymous_Users'] = "<b>usuaris anònims</b>";
$lang['Auth_Registered_Users'] = "<b>usuaris registrats</b>";
$lang['Auth_Users_granted_access'] = "<b>usuaris amb accés especial</b>";
$lang['Auth_Moderators'] = "<b>moderadors</b>";
$lang['Auth_Administrators'] = "<b>administradors</b>";

$lang['Not_Moderator'] = "No ets moderador d'aquest fòrum";
$lang['Not_Authorised'] = "No Autoritzat";

$lang['You_been_banned'] = "Has estat exclòs d'aquest fòrum<br />Si us plau contacta amb el webmaster o l'administrador del fòrum per més informació";


//
// Veure usuaris connectats
//
$lang['Reg_users_zero_online'] = "Hi ha 0 usuaris Registrats i "; // Hi ha 0 usuaris Registrats i
$lang['Reg_users_online'] = "Hi ha %d usuaris Registrats i "; // Hi ha 5 usuaris Registrats i
$lang['Reg_user_online'] = "Hi ha 1 usuari Registrat i "; // Hi ha 1 usuari Registrat i
$lang['Hidden_users_zero_online'] = "0 usuaris Ocults connectats"; // 0 usuaris Ocults connectats
$lang['Hidden_users_online'] = "%d usuaris Ocults connectats"; // 6 usuaris Ocults connectats
$lang['Hidden_user_online'] = "%d usuari Ocult connectat"; // 1 usuari Ocult connectat
$lang['Guest_users_online'] = "Hi ha %d usuaris Invitats connectats"; // Hi ha 10 usuaris Invitats connectats
$lang['Guest_users_zero_online'] = "Hi ha 0 usuaris Invitats connectats"; // Hi ha 0 usuaris Invitats connectats
$lang['Guest_user_online'] = "Hi ha %d usuari Invitat connectat"; // Hi ha 1 usuari Invitat connectat
$lang['No_users_browsing'] = "No hi ha usuaris explorant aquest fòrum";

$lang['Online_explain'] = "Aquestes dades estan basades en els usuaris actius durant els darrers cinc minuts";

$lang['Forum_Location'] = "Ubicació del Fòrum";
$lang['Last_updated'] = "Darrera Actualització";

$lang['Forum_index'] = "Índex del Fòrum";
$lang['Logging_on'] = "Iniciant Sessió";
$lang['Posting_message'] = "Publicant missatge";
$lang['Searching_forums'] = "Buscant fòrums";
$lang['Viewing_profile'] = "Veient Perfil";
$lang['Viewing_online'] = "Veient qui està connectat";
$lang['Viewing_member_list'] = "Veient llista de membres";
$lang['Viewing_priv_msgs'] = "Veient missatges privats";
$lang['Viewing_FAQ'] = "Veient FAQ";


//
// Quadre de Control del Moderador
//
$lang['Mod_CP'] = "Quadre de Control del Moderador";
$lang['Mod_CP_explain'] = "Utilitzant el següent formulari pots realitzar operacions de moderació en aquest fòrum. Pots tancar, desbloquejar, moure, o esborrar qualsevol nombre de temes";

$lang['Select'] = "Seleccionar";
$lang['Delete'] = "Esborrar";
$lang['Move'] = "Moure";
$lang['Lock'] = "Bloquejar";
$lang['Unlock'] = "Desbloquejar";

$lang['Topics_Removed'] = "Els temes seleccionats s'han esborrat correctament de la base de dades";
$lang['Topics_Locked'] = "S'han bloquejat els temes seleccionats";
$lang['Topics_Moved'] = "S'han mogut els temes seleccionats";
$lang['Topics_Unlocked'] = "S'han desbloquejat els temes seleccionats";
$lang['No_Topics_Moved'] = "No s'ha mogut cap tema";

$lang['Confirm_delete_topic'] = "Estàs segur que vols eliminar els temes seleccionats?";
$lang['Confirm_lock_topic'] = "Estàs segur que vols bloquejar els temes seleccionats?";
$lang['Confirm_unlock_topic'] = "Estàs segur que vols desbloquejar els temes seleccionats?";
$lang['Confirm_move_topic'] = "Estàs segur que vols moure els temes seleccionats?";

$lang['Move_to_forum'] = "Moure al fòrum";
$lang['Leave_shadow_topic'] = "Deixar el tema sombrejat a l'antic fòrum";

$lang['Split_Topic'] = "Quadre de Control  de Divisió de Temes";
$lang['Split_Topic_explain'] = "Utilitzant el següent formulari pots dividir un tema en dos, ja sigui seleccionant els missatges individualment o dividint-lo  a partir d'un missatge determinat";
$lang['Split_title'] = "Títol del nou tema";
$lang['Split_forum'] = "Fòrum per al nou tema";
$lang['Split_posts'] = "Dividir missatges seleccionats";
$lang['Split_after'] = "Dividir des del missatge seleccionat";
$lang['Topic_split'] = "El tema seleccionat s'ha dividit correctament";

$lang['Too_many_error'] = "Has seleccionat massa missatges. Només pots triar un missatge per dividir un tema a partir d'ell";

$lang['None_selected'] = "No has seleccionat cap tema para fer aquesta operació. Si us plau torna i selecciona'n almenys un";
$lang['New_forum'] = "Nou Fòrum";

$lang['This_posts_IP'] = "IP per aquest missatge";
$lang['Other_IP_this_user'] = "Altres IP's des de les quals aquest usuari ha publicat missatges";
$lang['Users_this_IP'] = "Usuaris publicant des d'aquesta IP";
$lang['IP_info'] = "Informació IP";
$lang['Lookup_IP'] = "Buscar IP";


//
// Zones horàries ... per mostrar a cada pàgina
//
$lang['All_times'] = "Totes les hores són %s"; // eg. Totes les hores són GMT - 12 Hores 

$lang['-12'] = "GMT - 12 Hores";
$lang['-11'] = "GMT - 11 Hores";
$lang['-10'] = "GMT - 10 Hores";
$lang['-9'] = "GMT - 9 Hores";
$lang['-8'] = "GMT - 8 Hores";
$lang['-7'] = "GMT - 7 Hores";
$lang['-6'] = "GMT - 6 Hores";
$lang['-5'] = "GMT - 5 Hores";
$lang['-4'] = "GMT - 4 Hores";
$lang['-3.5'] = "GMT - 3.5 Hores";
$lang['-3'] = "GMT - 3 Hores";
$lang['-2'] = "GMT - 2 Hores";
$lang['-1'] = "GMT - 1 Hora";
$lang['0'] = "GMT";
$lang['1'] = "GMT + 1 Hora";
$lang['2'] = "GMT + 2 Hores";
$lang['3'] = "GMT + 3 Hores";
$lang['3.5'] = "GMT + 3.5 Hores";
$lang['4'] = "GMT + 4 Hores";
$lang['4.5'] = "GMT + 4.5 Hores";
$lang['5'] = "GMT + 5 Hores";
$lang['5.5'] = "GMT + 5.5 Hores";
$lang['6'] = "GMT + 6 Hores";
$lang['6.5'] = "GMT + 6.5 Hores";
$lang['7'] = "GMT + 7 Hores";
$lang['8'] = "GMT + 8 Hores";
$lang['9'] = "GMT + 9 Hores";
$lang['9.5'] = "GMT + 9.5 Hores";
$lang['10'] = "GMT + 10 Hores";
$lang['11'] = "GMT + 11 Hores";
$lang['12'] = "GMT + 12 Hores";

// Aquests es mostren al quadre de selecció de zona horària
$lang['tz']['-12'] = "GMT -12 Hores";
$lang['tz']['-11'] = "GMT -11 Hores";
$lang['tz']['-10'] = "GMT -10 Hores";
$lang['tz']['-9'] = "GMT -9 Hores";
$lang['tz']['-8'] = "GMT -8 Hores";
$lang['tz']['-7'] = "GMT -7 Hores";
$lang['tz']['-6'] = "GMT -6 Hores";
$lang['tz']['-5'] = "GMT -5 Hores";
$lang['tz']['-4'] = "GMT -4 Hores";
$lang['tz']['-3.5'] = "GMT -3.5 Hores";
$lang['tz']['-3'] = "GMT -3 Hores";
$lang['tz']['-2'] = "GMT -2 Hores";
$lang['tz']['-1'] = "GMT -1 Hora";
$lang['tz']['0'] = "GMT";
$lang['tz']['1'] = "GMT +1 Hora";
$lang['tz']['2'] = "GMT +2 Hores";
$lang['tz']['3'] = "GMT +3 Hores";
$lang['tz']['3.5'] = "GMT +3.5 Hores";
$lang['tz']['4'] = "GMT +4 Hores";
$lang['tz']['4.5'] = "GMT +4.5 Hores";
$lang['tz']['5'] = "GMT +5 Hores";
$lang['tz']['5.5'] = "GMT +5.5 Hores";
$lang['tz']['6'] = "GMT +6 Hores";
$lang['tz']['6.5'] = "GMT +6.5 Hores";
$lang['tz']['7'] = "GMT +7 Hores";
$lang['tz']['8'] = "GMT +8 Hores";
$lang['tz']['9'] = "GMT +9 Hores";
$lang['tz']['9.5'] = "GMT +9.5 Hores";
$lang['tz']['10'] = "GMT +10 Hores";
$lang['tz']['11'] = "GMT +11 Hores";
$lang['tz']['12'] = "GMT +12 Hores";

$lang['datetime']['Sunday'] = "Diumenge";
$lang['datetime']['Monday'] = "Dilluns";
$lang['datetime']['Tuesday'] = "Dimarts";
$lang['datetime']['Wednesday'] = "Dimecres";
$lang['datetime']['Thursday'] = "Dijous";
$lang['datetime']['Friday'] = "Divendres";
$lang['datetime']['Saturday'] = "Dissabte";
$lang['datetime']['Sun'] = "Dg";
$lang['datetime']['Mon'] = "Dl";
$lang['datetime']['Tue'] = "Dt";
$lang['datetime']['Wed'] = "Dc";
$lang['datetime']['Thu'] = "Dj";
$lang['datetime']['Fri'] = "Dv";
$lang['datetime']['Sat'] = "Ds";
$lang['datetime']['January'] = "Gener";
$lang['datetime']['February'] = "Febrer";
$lang['datetime']['March'] = "Març";
$lang['datetime']['April'] = "Abril";
$lang['datetime']['May'] = "Maig";
$lang['datetime']['June'] = "Juny";
$lang['datetime']['July'] = "Juliol";
$lang['datetime']['August'] = "Agost";
$lang['datetime']['September'] = "Setembre";
$lang['datetime']['October'] = "Octubre";
$lang['datetime']['November'] = "Novembre";
$lang['datetime']['December'] = "Desembre";
$lang['datetime']['Jan'] = "Gen";
$lang['datetime']['Feb'] = "Feb";
$lang['datetime']['Mar'] = "Mar";
$lang['datetime']['Apr'] = "Abr";
$lang['datetime']['May'] = "Mai";
$lang['datetime']['Jun'] = "Jun";
$lang['datetime']['Jul'] = "Jul";
$lang['datetime']['Aug'] = "Ago";
$lang['datetime']['Sep'] = "Set";
$lang['datetime']['Oct'] = "Oct";
$lang['datetime']['Nov'] = "Nov";
$lang['datetime']['Dec'] = "Des";

//
// Errors (no relacionats amb una fallada 
// específica en una pàgina)
//
$lang['Information'] = "Informació";
$lang['Critical_Information'] = "Informació Crítica";

$lang['General_Error'] = "Error General";
$lang['Critical_Error'] = "Error Crític";
$lang['An_error_occured'] = "Un Error ha tingut lloc";
$lang['A_critical_error'] = "Un Error Crític ha tingut lloc";

//
// Això és tot amics!
// -------------------------------------------------

?>