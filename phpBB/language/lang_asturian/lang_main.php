<?php
/***************************************************************************
 *                            lang_main.php [Asturian]
 *                              -------------------
 *     begin                : Wed Dec 12 2001
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *                            
 *     tradución al asturianu: Mikel González (mikelglez@iespana.es)
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
// Deberia tamén intentar configurar local y codificación de caracteres
// (mas direción). La codificación y direción
// unviaranse a la plantilla. El local pue que funcione
// o non, depende del soporte del Sistema Operativu y la
// sintaxis varia ... escueya como meyor-y paeza!
//

$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "d M Y"; // Esto deberíase camudar al formatu predeterminau pa'l su idioma, formatu comu php date()

// Estu ye ocional, si quies incluyir un POQUIÑIN testu
// co'l to copyright indicando que yes el traductor
// engadeo aqui.
$lang['TRANSLATION_INFO'] = "Torna'l asturianu por <a href='http://www.mikel.tk' class='copyright' target=_blank'>Mikel González</a> pal proyeutu <a href='http://phpbb.softastur.levillage.org' class='copyright' target=_blank'>phpBB</a> de <a href='http://www.cpu-inside.com/~mikel/softastur' class='copyright' target=_blank'>SoftAstur</a>";

//
// Comunes, estos términos usanse bastante
// en varies páxines
//
$lang['Forum'] = "Foru";
$lang['Category'] = "Categoría";
$lang['Topic'] = "Tema";
$lang['Topics'] = "Temes";
$lang['Replies'] = "Rempuestes";
$lang['Views'] = "Llectures";
$lang['Post'] = "Mensaxe";
$lang['Posts'] = "Mensaxes";
$lang['Posted'] = "Publicao";
$lang['Username'] = "Nome d'Usuario";
$lang['Password'] = "Contraseña";
$lang['Email'] = "Correu";
$lang['Poster'] = "Autor";
$lang['Author'] = "Autor";
$lang['Time'] = "Hores";
$lang['Horas'] = "Hores";
$lang['Message'] = "Mensaxe";

$lang['1_Day'] = "1 Día";
$lang['7_Days'] = "7 Díes";
$lang['2_Weeks'] = "2 Semanes";
$lang['1_Month'] = "1 Mes";
$lang['3_Months'] = "3 Meses";
$lang['6_Months'] = "6 Meses";
$lang['1_Year'] = "1 Añu";

$lang['Go'] = "Dir";
$lang['Jump_to'] = "Dir a";
$lang['Submit'] = "Unviar";
$lang['Reset'] = "Anular";
$lang['Cancel'] = "Cancelar";
$lang['Preview'] = "Vista Previa";
$lang['Confirm'] = "Confirmar";
$lang['Spellcheck'] = "Ortografía";
$lang['Yes'] = "Si";
$lang['No'] = "Non";
$lang['Enabled'] = "Habilitau";
$lang['Disabled'] = "Deshabilitau";
$lang['Error'] = "Error";

$lang['Next'] = "Siguiente";
$lang['Previous'] = "Anterior";
$lang['Goto_page'] = "Dir a páxina";
$lang['Joined'] = "Rexistrau";
$lang['IP_Address'] = "Direción IP";

$lang['Select_forum'] = "Seleccione un foru";
$lang['View_latest_post'] = "Lleer últimu mensaxe";
$lang['View_newest_post'] = "Lleer el mensaxe más reciente";
$lang['Page_of'] = "Páxina <b>%d</b> de <b>%d</b>"; // Será reemplazau con : Páxina 1 de 2 por exemplu

$lang['ICQ'] = "Númberu ICQ";
$lang['AIM'] = "Direción AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

//$lang['Forum_Index'] = "%s Índiz de Foros";  // ex. Nome de Sitiu Índiz de Foros, %s puese quitar si se quier
$lang['Forum_Index'] = "Foros de discusión";  // ex. Nome de Sitiu Índiz de Foros, %s puese quitar si se quier

$lang['Post_new_topic'] = "Publicar nueu tema";
$lang['Reply_to_topic'] = "Responder al tema";
$lang['Reply_with_quote'] = "Responder citando";

$lang['Click_return_topic'] = "Calque %sequí%s pa tornar al tema"; // %s's son pa los url, nun quitalos!
$lang['Click_return_login'] = "Calque %sequí%s pa intentalu de nueu";
$lang['Click_return_forum'] = "Calque %sequí%s pa tornar al foru";
$lang['Click_view_message'] = "Calque %sequí%s pa lleer el su mensaxe";
$lang['Click_return_modcp'] = "Calque %sequí%s pa tornar al Panel de Control del Moderaor";
$lang['Click_return_group'] = "Calque %sequí%s pa tornar a la Información del Grupu";

$lang['Admin_panel'] = "Dir a Panel d'Alministración";

$lang['Board_disable'] = "Sentimoslo pero momentaneamente esti foru nun ta disponible, por favor intente conectase llueu";


//
// Global Header strings
//
$lang['Registered_users'] = "Usuarios Rexistraos:";
$lang['Browsing_forum'] = "Usuarios navengando esti foru:";
$lang['Online_users_zero_total'] = "En total hay <b>0</b> usuarios en llinia :: ";
$lang['Online_users_total'] = "En total hay <b>%d</b> usuarios en llinia :: ";
$lang['Online_user_total'] = "En total hay <b>%d</b> usuariu en llinia :: ";
$lang['Reg_users_zero_total'] = "0 Rexistraos, ";
$lang['Reg_users_total'] = "%d Rexistraos, ";
$lang['Reg_user_total'] = "%d Rexistrao, ";
$lang['Hidden_users_zero_total'] = "0 Ocultos y ";
$lang['Hidden_user_total'] = "%d Ocultos y ";
$lang['Hidden_users_total'] = "%d Ocultos y ";
$lang['Guest_users_zero_total'] = "0 Invitaos";
$lang['Guest_users_total'] = "%d Invitaos";
$lang['Guest_user_total'] = "%d Invitao";
$lang['Record_online_users'] = "La mayor cantidá d'usuarios en llinia foy <b>%s</b> el %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sAdministraor%s";
$lang['Mod_online_color'] = "%sModeraor%s";

$lang['You_last_visit'] = "La so última visita foy: %s"; // %s reemplazau por fecha y hora
$lang['Current_time'] = "Fecha y hora d'agora: %s"; // %s reemplazau por hora

$lang['Search_new'] = "Lleer mensaxes dende última visita";
$lang['Search_your_posts'] = "Lleer sus mensaxes";
$lang['Search_unanswered'] = "Lleer mensaxes ensin rempuesta";

$lang['Register'] = "Rexistrase";
$lang['Profile'] = "Perfil";
$lang['Edit_profile'] = "Editar el su perfil";
$lang['Search'] = "Buscar";
$lang['Memberlist'] = "Miembros";
$lang['FAQ'] = "Entrugues frecuentes";
$lang['BBCode_guide'] = "Guía BBCode";
$lang['Usergroups'] = "Grupos d'Usuarios";
$lang['Last_Post'] = "Último Mensaxe";
$lang['Moderator'] = "Moderaor";
$lang['Moderators'] = "Moderaores";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Los nuesos usuarios publicaron <b>0</b> mensaxes"; // Number of posts
$lang['Posted_articles_total'] = "Los Nuesos usuarios publicaron un total de <b>%d</b> mensaxes"; // Númberu de mensaxes
$lang['Posted_article_total'] = "Los Nuesos usuarios publicaron un total de <b>%d</b> mensaxes"; // Númberu de mensaxes
$lang['Registered_users_zero_total'] = "Tenemos <b>0</b> usuarios rexistraos"; // # registered users
$lang['Registered_users_total'] = "Tenemos <b>%d</b> usuarios rexistraos"; // # usuarios rexistraos
$lang['Registered_user_total'] = "Tenemos <b>%d</b> usuariu rexistrau"; // # usuarios rexistraos
$lang['Newest_user'] = "L'últimu usuariu rexistrau ye <b>%s%s%s</b>"; // un enllace a nome d'usuariu, /a 

$lang['No_new_posts_last_visit'] = "Nun hay mensaxes nueos dende la so última visita";
$lang['No_new_posts'] = "Nun hay mensaxes nueos";
$lang['New_posts'] = "Mensaxes nueos";
$lang['New_post'] = "Mensaxe nueu";
$lang['No_new_posts_hot'] = "Nun hay mensaxes nueos [ Popular ]";
$lang['New_posts_hot'] = "Mensaxes nueos [ Popular ]";
$lang['No_new_posts_locked'] = "Nun hay mensaxes nueos [ Trancau ]";
$lang['New_posts_locked'] = "Mensaxes nueos [ Trancau ]";
$lang['Forum_is_locked'] = "Foru trancau";


//
// Login
//
$lang['Enter_password'] = "Por favor enxerte el su nome d'usuario y contraseña pa entrar";
$lang['Login'] = "Conectase";
$lang['Logout'] = "Desconectase";

$lang['Forgotten_password'] = "Olvidé la mio contraseña";

$lang['Log_me_in'] = "Entrar automáticamente en cada visita";

$lang['Error_login'] = "Ingresara un nome d'usuariu incorreutu o inactivu o una contraseña incorreuta";


//
// Index page
//
$lang['Index'] = "Índiz";
$lang['No_Posts'] = "Nun hay mensaxes";
$lang['No_forums'] = "Nun hay foros";

$lang['Private_Message'] = "Mensaxe Privau";
$lang['Private_Messages'] = "Mensaxes Privaos";
$lang['Who_is_Online'] = "Quien ta conectau";

$lang['Mark_all_forums'] = "Marcar tolos foros como lleidos";
$lang['Forums_marked_read'] = "Tolos foros marcaronse comu lleidos";


//
// Viewforum
//
$lang['View_forum'] = "Foru";

$lang['Forum_not_exist'] = "El foru seleccionau nun existe";
$lang['Reached_on_error'] = "Llegara por error a esta páxina";

$lang['Display_topics'] = "Mostrar temes anteriores";
$lang['All_Topics'] = "Tolos Temes";

$lang['Topic_Announcement'] = "<b>Anunciu:</b>";
$lang['Topic_Sticky'] = "<b>PostIt:</b>";
$lang['Topic_Moved'] = "<b>Moviu:</b>";
$lang['Topic_Poll'] = "<b>[ Encuesta ]</b>";

$lang['Mark_all_topics'] = "Marcar tolos temes como lleidos";
$lang['Topics_marked_read'] = "Los temes d'esti foru marcaronse como lleidos";

$lang['Rules_post_can'] = "<b>Pue</b> publicar nueos temes n'esti foru";
$lang['Rules_post_cannot'] = "<b>Pue</b> publicar nueos temes n'esti foru";
$lang['Rules_reply_can'] = "<b>Pue</b> responder a temes n'esti foru";
$lang['Rules_reply_cannot'] = "<b>Nun pue</b> responder a temes n'esti foru";
$lang['Rules_edit_can'] = "<b>Pue</b> editar los sus mensaxes n'esti foru";
$lang['Rules_edit_cannot'] = "<b>Nun pue</b> editar los sus mensaxes n'esti foru";
$lang['Rules_delete_can'] = "<b>Pue</b> esborriar los sus mensaxes n'esti foru";
$lang['Rules_delete_cannot'] = "<b>Nun pue</b> esborriar los sus mensaxes n'esti foru";
$lang['Rules_vote_can'] = "<b>Pue</b> votar en encuestes n'esti foru";
$lang['Rules_vote_cannot'] = "<b>Nun pue</b> votar en encuestes n'esti foru";
$lang['Rules_moderate'] = "<b>Pue</b> %smoderar esti foru%s"; // %s reemplazau por enllaces, nun quitalu! 

$lang['No_topics_post_one'] = "Nun hay temes n'esti foru<br />Calque en <b>Nueu Tema</b> pa publicar un tema nueu";


//
// Viewtopic
//
$lang['View_topic'] = "Lleendu tema";

$lang['Guest'] = 'Invitau';
$lang['Post_subject'] = "<b>Asuntu</b>";
$lang['View_next_topic'] = "Ver tema siguiente";
$lang['View_previous_topic'] = "Ver tema anterior";
$lang['Submit_vote'] = "Votar";
$lang['View_results'] = "Ver resultaos";

$lang['No_newer_topics'] = "Nun hay temes nueos n'esti foru";
$lang['No_older_topics'] = "Nun hay temes anteriores n'esti foru";
$lang['Topic_post_not_exist'] = "El tema o mensaxe solicitau nun existe";
$lang['No_posts_topic'] = "Nun existen mensaxes pa esti tema";

$lang['Display_posts'] = "Mostrar mensaxes de anteriores";
$lang['All_Posts'] = "Tolos mensaxes";
$lang['Newest_First'] = "El más reciente primeru";
$lang['Oldest_First'] = "El más vieyu primeru";

$lang['Back_to_top'] = "Tornar arriba";

$lang['Read_profile'] = "Ver perfil d'usuariu"; 
$lang['Send_email'] = "Unviar correu a usuariu";
$lang['Visit_website'] = "Visitar sitiu web del autor";
$lang['ICQ_status'] = "Status ICQ";
$lang['Edit_delete_post'] = "Editar/Esborriar esti mensaxe";
$lang['View_IP'] = "Ver IP del autor";
$lang['Delete_post'] = "Esborriar esti mensaxe";

$lang['wrote'] = "Plumió"; // precede al nome d'usuariu y siguelu el textu citau
$lang['Quote'] = "Cita"; // vien antes de la salia de bbcode citar
$lang['Code'] = "Códigu"; // vien antes de la salia de bbcode códigu

$lang['Edited_time_total'] = "Ultima edición por %s el %s, editau %d vegá"; // Ultima edición por mi el Dic 2002, editau 1 vegá
$lang['Edited_times_total'] = "Ultima edición por %s el %s, editau %d vegaes"; // Ultima edición por mi el Dic 2002, editau 2 vegaes

$lang['Lock_topic'] = "Trancar esti tema";
$lang['Unlock_topic'] = "Desbloquear esti tema";
$lang['Move_topic'] = "Mover esti tema";
$lang['Delete_topic'] = "Esborriar esti tema";
$lang['Split_topic'] = "Separtar esti tema";

$lang['Stop_watching_topic'] = "Dexar de lleer esti tema";
$lang['Start_watching_topic'] = "Lleer esti tema por rempuestes";
$lang['No_longer_watching'] = "Ya nun ta n'esti tema";
$lang['You_are_watching'] = "Agora ta n'esti tema";

$lang['Total_votes'] = "Votos Totales";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Cuerpu del mensaxe";
$lang['Topic_review'] = "Revisar tema";

$lang['No_post_mode'] = "Nun especificose'l modu de mensaxe"; // Si llamase posting.php ensin un modu (newtopic/reply/delete/etc, nun debería mostrase normalmente)

$lang['Post_a_new_topic'] = "Publicar un nueu tema";
$lang['Post_a_reply'] = "Publicar una rempuesta";
$lang['Post_topic_as'] = "Publicar tema comu";
$lang['Edit_Post'] = "Editar mensaxe";
$lang['Options'] = "Opciones";

$lang['Post_Announcement'] = "Anunciu";
$lang['Post_Sticky'] = "PostIt";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "¿Ta seguru que quier esborriar esti mensaxe?";
$lang['Confirm_delete_poll'] = "¿Ta seguru que quier esborriar esta encuesta?";

$lang['Flood_Error'] = "Nun pue publicar otru tema tan rápidu dempués del últimu, faiga el favor d'intentalu más tarde";
$lang['Empty_subject'] = "Debe especificar un asuntu cuando publique un tema nueu";
$lang['Empty_message'] = "Debe plumiar un mensaxe pa publicar";
$lang['Forum_locked'] = "Esti foru ta trancau y nun pue publicar, responder o editar temes";
$lang['Topic_locked'] = "Esti tema ta trancau y nun pue editar mensaxes o responder";
$lang['No_post_id'] = "Debe selecionar un mensaxe pa editar";
$lang['No_topic_id'] = "Debe selecionar un tema pa respondelu";
$lang['No_valid_mode'] = "Namás pue publicar, responder, editar o citar mensaxes, por favor torne y intente de nueu";
$lang['No_such_post'] = "Nun existe ese mensaxe, torne y intente de nueu";
$lang['Edit_own_posts'] = "Sentimoslo pero namás pue editar los sus propios mensaxes";
$lang['Delete_own_posts'] = "Sentimoslo pero namás pue esborriar los sus propios mensaxes";
$lang['Cannot_delete_replied'] = "Sentimoslo pero nun pue esborriar mensaxes que foron respondios";
$lang['Cannot_delete_poll'] = "Sentimoslo pero nun pue esborriar una encuesta activa";
$lang['Empty_poll_title'] = "Debe plumiar un títulu pa'l su mensaxe";
$lang['To_few_poll_options'] = "Debe enxertar al menos dos opciones pa la encuesta";
$lang['To_many_poll_options'] = "Enxertara demasiaes opciones pa la encuesta";
$lang['Post_has_no_poll'] = "Esti mensaxe nun tien encuesta";

$lang['Add_poll'] = "Facer una encuesta";
$lang['Add_poll_explain'] = "Si nun quier facer una encuesta nel su tema dexe los campos en blancu";
$lang['Poll_question'] = "Entrugaa de la Encuesta";
$lang['Poll_option'] = "Opción de Encuesta";
$lang['Add_option'] = "Añadir Opción";
$lang['Update'] = "Actualizar";
$lang['Delete'] = "Esborriar";
$lang['Poll_for'] = "Correr encuesta por";
$lang['Days'] = "Díes"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Plumie 0 o dexe en blancu pa que la encuesta nun fine ]";
$lang['Delete_poll'] = "Esborriar Encuesta";
$lang['Already_voted'] = "Usté ya votara n'esta encuesta"; 
$lang['No_vote_option'] = 'Debe elexir una opción al votar'; 

$lang['Disable_HTML_post'] = "Deshabilitar HTML n'esti mensaxe";
$lang['Disable_BBCode_post'] = "Deshabilitar BBCode n'esti mensaxe";
$lang['Disable_Smilies_post'] = "Deshabilitar Smilies n'esti mensaxe";

$lang['HTML_is_ON'] = "HTML ta <u>ON</u>";
$lang['HTML_is_OFF'] = "HTML ta <u>OFF</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s ta <u>ON</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s ta <u>OFF</u>";
$lang['Smilies_are_ON'] = "Smilies tan <u>ON</u>";
$lang['Smilies_are_OFF'] = "Smilies tan <u>OFF</u>";

$lang['Attach_signature'] = "Axuntar firma (la firma pue camudala nel perfil)";
$lang['Notify'] = "Avisaime cuandu publiquese una rempuesta";
$lang['Delete_post'] = "Esborriar esti mensaxe";

$lang['Stored'] = "El su mensaxe publicose con éxitu";
$lang['Deleted'] = "El su mensaxe borrose con éxitu";
$lang['Poll_delete'] = "La su encuesta borrose con éxitu";
$lang['Vote_cast'] = "El su votu foy publicau";

$lang['Topic_reply_notification'] = "Avisu de Rempuesta a Tema";

$lang['bbcode_b_help'] = "Negrita: [b]texto[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Cursiva: [i]texto[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Subrayau: [u]texto[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Cita: [quote]texto[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Códigu: [code]código[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Llista: [list]texto[/list] (alt+l)";
$lang['bbcode_o_help'] = "Llista ordená: [list=]texto[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Enxertar imaxen: [img]http://url_imagen[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Enxertar URL: [url]http://url[/url] o [url=http://url]texto URL[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Trancar tolos marcaores de bbCode abiertos";
$lang['bbcode_s_help'] = "Color: [color=red]texto[/color]  Nota: Puede usar color=#FF0000";
$lang['bbcode_f_help'] = "Tamañu: [size=x-small]texto pequeño[/size]";

$lang['Emoticons'] = "Emoticons";
$lang['More_emoticons'] = "Gueyar mas Emoticons";

$lang['Font_color'] = "Color";
$lang['color_default'] = "Predeterminau";
$lang['color_dark_red'] = "Roxu oscuru";
$lang['color_red'] = "Roxu";
$lang['color_orange'] = "Naranxa";
$lang['color_brown'] = "Marrón";
$lang['color_yellow'] = "Mariellu";
$lang['color_green'] = "Verde";
$lang['color_olive'] = "Aceituna";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Azul";
$lang['color_dark_blue'] = "Azul Oscuru";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violeta";
$lang['color_white'] = "Blancu";
$lang['color_black'] = "Negru";

$lang['Font_size'] = "Tamañu";
$lang['font_tiny'] = "Piquiñina";
$lang['font_small'] = "Piquiña";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Grande";
$lang['font_huge'] = "Grandona";

$lang['Close_Tags'] = "Trancar marcaores";
$lang['Styles_tip'] = "Nota: Puese aplicar estilos rápidamente al textu selecionau";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Mensaxes Privaos";

$lang['Login_check_pm'] = "Entre pa ver los sus mensaxes privaos";
$lang['New_pms'] = "Usté tien %d mensaxes nueos"; // Usté tien 2 mensaxes nueos
$lang['New_pm'] = "Usté tien %d mensaxe nueu"; // Usté tien 1 mensaxe nueu
$lang['No_new_pm'] = "Usté nun tien mensaxes nueos";
$lang['Unread_pms'] = "Usté tien %d mensaxes ensin lleer";
$lang['Unread_pm'] = "Usté tien %d mensaxe ensin lleer";
$lang['No_unread_pm'] = "Usté nun tien mensaxes ensin lleer";
$lang['You_new_pm'] = "Tien un nueu mensaxe privau ena bandexa d'entrada";
$lang['You_new_pms'] = "Tien nueos mensaxes privaos ena bandexa d'entrada";
$lang['You_no_new_pm'] = "Nun tien mensaxes privaos nueos";

$lang['Inbox'] = "Bandexa d'Entrada";
$lang['Outbox'] = "Bandexa de Salida";
$lang['Savebox'] = "Mensaxes Guardaos";
$lang['Sentbox'] = "Mensaxes Unviaos";
$lang['Flag'] = "Marca";
$lang['Subject'] = "Asuntu";
$lang['From'] = "De";
$lang['To'] = "Pa";
$lang['Date'] = "Fecha";
$lang['Mark'] = "Marcar";
$lang['Sent'] = "Unviau";
$lang['Saved'] = "Guardau";
$lang['Delete_marked'] = "Esborriar Marcaos";
$lang['Delete_all'] = "Esborriar Toos";
$lang['Save_marked'] = "Guardar Marcaos"; 
$lang['Save_message'] = "Guardar Mensaxe";
$lang['Delete_message'] = "Esborriar Mensaxe";

$lang['Display_messages'] = "Mostrar mensaxes de los anteriores"; // Seguiu por # de dies/selmanes/meses
$lang['All_Messages'] = "Tolos mensaxes";

$lang['No_messages_folder'] = "Nun tien mensaxes n'esta carpeta";

$lang['PM_disabled'] = "Desactivaronse los Mensaxes Privaos n'esti Foru";
$lang['Cannot_send_privmsg'] = "Sentimoslu pero l'alministraor desactivo-y la opción d'unviar mensaxes privaos";
$lang['No_to_user'] = "Debe especificar un nome d'usuariu pa unviar esti mensaxe";
$lang['No_such_user'] = "Sentimoslu pero esi usuariu nun existe";

$lang['Disable_HTML_pm'] = "Deshabilitar HTML n'esti mensaxe";
$lang['Disable_BBCode_pm'] = "Deshabilitar BBCode n'esti mensaxe";
$lang['Disable_Smilies_pm'] = "Deshabilitar n'esti mensaxe";

$lang['Message_sent'] = "El su mensaxe enviose";

$lang['Click_return_inbox'] = "Calque %sequí%s pa tornar a la su Bandexa d'Entrada";
$lang['Click_return_index'] = "Calque %sequí%s pa tornar al Índiz";

$lang['Send_a_new_message'] = "Unviar un nueu mensaxe privau";
$lang['Send_a_reply'] = "Responder a mensaxe privau";
$lang['Edit_message'] = "Editar mensaxe privau";

$lang['Notification_subject'] = "Llegara un nueu mensaxe privau";

$lang['Find_username'] = "Buscar un usuariu";
$lang['Find'] = "Buscar";
$lang['No_match'] = "Nun s'atoparon coincidencies";

$lang['No_post_id'] = "Nun identificose un ID de mensaxe";
$lang['No_such_folder'] = "Nun existe esa carpeta";
$lang['No_folder'] = "Nun especificose una carpeta";

$lang['Mark_all'] = "Marcar toos";
$lang['Unmark_all'] = "Desmarcar toos";

$lang['Confirm_delete_pm'] = "¿Ta seguru d'esborriar esti mensaxe?";
$lang['Confirm_delete_pms'] = "¿Ta seguru d'esborriar estos mensaxes?";

$lang['Inbox_size'] = "La su Bandexa d'Entrada ta %d%% llena"; // ex. La su Bandexa d'Entrada ta 50% llena
$lang['Sentbox_size'] = "La su Bandexa de Mensaxes Enviaos ta %d%% llena"; 
$lang['Savebox_size'] = "La su Bandexa de Mensaxes Guardaos ta %d%% llena"; 

$lang['Click_view_privmsg'] = "Calque %sequí%s pa dir a la su Bandexa d'Entrada";

$lang['Read_pm'] = 'Lleer mensaxe'; 
$lang['Post_new_pm'] = 'Unviar mensaxe'; 
$lang['Post_reply_pm'] = 'Contestar mensaxe'; 
$lang['Post_quote_pm'] = 'Citar mensaxe'; 
$lang['Edit_pm'] = 'Editar mensaxe'; 

$lang['Unread_message'] = 'Mensaxe nun lleiu'; 
$lang['Read_message'] = 'Mensaxe lleiu'; 


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Perfil :: %s"; // %s ye'l nome d'usuariu 
$lang['About_user'] = "Too sobre %s"; // %s ye'l nome d'usuariu

$lang['Preferences'] = "Preferencies";
$lang['Items_required'] = "Los campos marcaos con * son obligatorios a non ser qu'indiquese lo contrariu";
$lang['Registration_info'] = "Información de Rexistración";
$lang['Profile_info'] = "Información de Perfil";
$lang['Profile_info_warn'] = "Esta información tará públicamente disponible";
$lang['Avatar_panel'] = "Panel de Control d'Avatar";
$lang['Avatar_gallery'] = "Galería d'Avatars";

$lang['Website'] = "Sitiu Web";
$lang['Location'] = "Llugar";
$lang['Contact'] = "Contautar a";
$lang['Email_address'] = "Correu";
$lang['Email'] = "Correu";
$lang['Send_private_message'] = "Unviar mensaxe privau";
$lang['Hidden_email'] = "[ Ocultu ]";
$lang['Search_user_posts'] = "Buscar mensaxes d'esti usuariu";
$lang['Interests'] = "Intereses";
$lang['Occupation'] = "Ocupación"; 
$lang['Poster_rank'] = "Rangu del Autor";

$lang['Total_posts'] = "Cantidá de Mensaxes";
$lang['User_post_pct_stats'] = "%.2f%% del total"; // 1.25% del total
$lang['User_post_day_stats'] = "%.2f mensaxes per día"; // 1.5 mensaxes per dia
$lang['Search_user_posts'] = "Buscar tolos mensaxes de %s"; // Buscar tolos mensaxes de usuariu

$lang['No_user_id_specified'] = "Sentimolo pero esi usuariu nun existe";
$lang['Wrong_Profile'] = "Nun puede modificar un perfil que nun seya el suyo propiu.";

$lang['Only_one_avatar'] = "Namás puese especificar un tipu d'avatar";
$lang['File_no_data'] = "L'archivu nel URL proporcionau nun contien datos";
$lang['No_connection_URL'] = "Nun pudose establecer conexión col URL proporcionau";
$lang['Incomplete_URL'] = "El URL ta incompletu";
$lang['Wrong_remote_avatar_format'] = "El URL del avatar remotu nun ye válidu";
$lang['No_send_account_inactive'] = "Sentimoslu pero la su contraseña nun pue recuparase porque la so cuenta ta desautivada. Por favor contaute col Alministraor del Foru";

$lang['Always_smile'] = "Activar siempre los Smilies";
$lang['Always_html'] = "Permitir siempre l'usu de HTML";
$lang['Always_bbcode'] = "Permitir siempre l'usu de BBCode";
$lang['Always_add_sig'] = "Axuntar siempre la mio Firma";
$lang['Always_notify'] = "Avisaime siempre q'haya rempuestes";
$lang['Always_notify_explain'] = "Unvía un correu cuandu alguien respuenda a dalgún tema qu'Usté publicara. Esto puede camudase siempre qu'Usté publique un mensaxe";

$lang['Board_style'] = "Estilu del Foru";
$lang['Board_lang'] = "Idioma del Foru";
$lang['No_themes'] = "Nun hay temes ena base datos";
$lang['Timezone'] = "Zona horaria";
$lang['Date_format'] = "Formatu de Fecha";
$lang['Date_format_explain'] = "La sintaxis usada ye idéntica a la función <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> de PHP";
$lang['Signature'] = "Firma";
$lang['Signature_explain'] = "Este ye un bloque de testu que puese axuntar a los mensaxes qu'usté publique. Hay un llímite de %d caracteres";
$lang['Public_view_email'] = "Mostrar siempre el mio Correu";

$lang['Current_password'] = "Contraseña actual";
$lang['New_password'] = "Nuea contraseña";
$lang['Confirm_password'] = "Confirmar contraseña";
$lang['Confirm_password_explain'] = "Ha confirmar la so actual contraseña si quier camudar esta o la su direción de correu llectrónicu";
$lang['password_if_changed'] = "Namás tien q'enxertar contraseña si quier camudala";
$lang['password_confirm_if_changed'] = "Namás tien de confirmar la so contraseña si camudola arriba";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Muesa una piquiña imaxen enbaxu los sus detalles enos mensaxes. Namás una imaxen pue mostrase al mesmu tiempu, el su anchu nun pue ser mayor de %d pixels, y la so altura nun pue ser mayor de %d pixels y l'archivu nun pue pesar más de %dkB."; $lang['Upload_Avatar_file'] = "Unviar Avatar dende la so máquina";
$lang['Upload_Avatar_URL'] = "Unviar Avatar dende URL";
$lang['Upload_Avatar_URL_explain'] = "Escriba'l URL au s'atopa l'archivu d'imaxen del so Avatar, será copiau a esti sitiu.";
$lang['Pick_local_Avatar'] = "Selecionar Avatar de la galería";
$lang['Link_remote_Avatar'] = "Vincular a un Avatar fuera d'esti sitiu";
$lang['Link_remote_Avatar_explain'] = "Plumie'l URL au s'atopa l'archivu d'imaxen del su Avatar.";
$lang['Avatar_URL'] = "URL d'imaxen d'Avatar";
$lang['Select_from_gallery'] = "Selecionar Avatar de la nuesa galería";
$lang['View_avatar_gallery'] = "Güeyar Galería";

$lang['Select_avatar'] = "Selecionar avatar";
$lang['Return_profile'] = "Cancelar avatar";
$lang['Select_category'] = "Selecionar categoría";

$lang['Delete_Image'] = "Esborriar Imaxen";
$lang['Current_Image'] = "Imaxen Actual";

$lang['Notify_on_privmsg'] = "Notificame los nueos Mensaxes Privaos";
$lang['Popup_on_privmsg'] = "Abrir una ventana cuandu tea Mensaxes Privaos";
$lang['Popup_on_privmsg_explain'] = "Dalgunes plantilles puen abrir una ventana muea pa informa-y cuandu recibiere nueos mensaxes privaos"; 
$lang['Hide_user'] = "Ocultar el su estau en llínia";

$lang['Profile_updated'] = "El su perfil foy actualizau";
$lang['Profile_updated_inactive'] = "El su perfil foy actualizau, pero, camudara detalles importantes y la so cuenta foy desactivada. Revise el su correu pa pescanciar comu reactivar la su cuenta, o si ye necesaria l'activación del Alministraor espere a qu'esti reactive la su cuenta";

$lang['Password_mismatch'] = "Les contraseñas qu'enxertara nun coinciden";
$lang['Current_password_mismatch'] = "La contraseña qu'enxertara nun coincide cola que ta na nuesa base datos";
$lang['Password_long'] = "La su contraseña nun debe contener más de 32 caracteres";
$lang['Username_taken'] = "Llamentamoslo pero esti nome d'usuariu ya ta garrau";
$lang['Username_invalid'] = "El nome d'usuariu contien dalgún caracter inváliu comu \"";
$lang['Username_disallowed'] = "Llamentamoslo, esti nome d'usuariu ta restrinxiu";
$lang['Email_taken'] = "Llamentamoslo pero esta direción de correu lletróniuo ya ta rexistrada por un usuariu";
$lang['Email_banned'] = "Llamentamoslo, esta direción de correu lletrónicu ta baneada";
$lang['Email_invalid'] = "La direción de correu lletrónicu enxertá nun ye válida";
$lang['Signature_too_long'] = "La firma ye mui llarga";
$lang['Fields_empty'] = "Tien que completar los campos obligatorios";
$lang['Avatar_filetype'] = "El tipu d'imaxen del avatar tien que ser .jpg, .gif o .png";
$lang['Avatar_filesize'] = "El tamañu de archivu del avatar tien que pesar menos de %d kB"; // El tamañu de archivu del avatar tien que pesar menos de 6 kB
$lang['Avatar_imagesize'] = "L'avatar tien que tener menos de %d pixels d'anchu por %d pixels d'altu"; 

$lang['Welcome_subject'] = "Bienveniu a los Foros de %s"; // Bienveniu a los Foros de Nome del Sitiu
$lang['New_account_subject'] = "Nuea cuenta d'usuariu";
$lang['Account_activated_subject'] = "Cuenta Activada";

$lang['Account_added'] = "Gracies por rexistrase, la su cuenta foy creada. Agora pue entrar col su nombre d'usuariu y contraseña";
$lang['Account_inactive'] = "La su cuenta foy creada. Pero, esti foru requier activación de la cuenta, una clave de activación enviose-y al su correu. Por favor revise'l su correu pa mas información";
$lang['Account_inactive_admin'] = "La su cuenta foy creada. Pero, esti foru requier activación del Alministraor. Un email foy enviau al Alministrador y Usté será informau cuando la so cuenta seya activada";
$lang['Account_active'] = "La su cuenta foy activada. Gracies per rexistrase";
$lang['Already_activated'] = 'Usté ya activara la su cuenta'; 
$lang['Account_active_admin'] = "La cuenta foy activada";
$lang['Reactivate'] = "¡Reactive la su cuenta!";
$lang['COPPA'] = "Su cuenta foy creada pero tien que ser aprobada, por favor revise el su correu pa más detalles.";

$lang['Registration'] = "Condiciones de Rexistru";
$lang['Reg_agreement'] = "Aun cuandu los alministraores y moderaores d'estos foros fairán tolo posible pa esborriar cualisquier material cuestionable tan prontu comu yos sea posible, ye imposible revisar tolos mensaxes. Polo tanto Usté acepta que tolos mensaxes publicaos n'estos foros expresen les opiniones de los propios autores y non la de los alministraores, moderaores o el webmaster (exceutu en mensaxes publicaos por ellos mesmos) polo cual nun se yos considerará responsables.<br /><br />Usté ta d'alcuerdu en non publicar material abusivo, obscenu, vulgar, d'odiu, amenazante, orientau sexualmente, o nengun otru que d'alguna forma viole leyes vixentes. Si publicase material d'esta índole la su cuenta d'accesu al foru será trancada y el su proveor d'Accesu a Internet será informau. La direción IP de tolos mensaxes ye guardada pa ayudar a cumplir estes normes. Usté ta d'acuerdu en qu'el webmaster, alministraor y moderaores d'esti Foru tienen el drechu d'esborriar, editar, mover o trancar cualisquier tema en cualisquier momentu si lo consideren conveniente. Com'usuariu Usté aceuta que tola información qu'enxerte seya almacenada nuna base datos. Esta información nun será proporcionada a terceros, el webmaster, alministraor y moderaores nun pueden responsabilizase por intentos de hackers que puean llevar a qu'esta información se vea comprometia.<br /><br />Esti sistema de foros utiliza cookies pa almacenar información ena su computaora llocal. Estos cookies no contienen la información qu'Usté ingresara, namás utilizanse pa meyorar la visualización de los foros. El correu namás ye usau pa confirmar los sus detalles de rexistru y contraseña (y pa enviar nueves contraseñes si olvida l'autual).<br /><br />Al rexistrase Usté aceutará toes estes condiciones.";

$lang['Agree_under_13'] = "Toy d'acuerdu con estes condiciones y soi <b>menor</b> de 13 años d'edá";
$lang['Agree_over_13'] = "Toy d'acuerdu con estes condiciones y soi <b>mayor</b> de 13 años de edá";
$lang['Agree_not'] = "Nun toy d'acuerdu con estes condiciones";

$lang['Wrong_activation'] = "La clave d'activación facilitá nun coincide con nenguna ena base datos";
$lang['Send_password'] = "Envia-yme una nuea contraseña"; 
$lang['Password_updated'] = "Creose una nuea contraseña, por favor revise'l so correu pa saber como activala";
$lang['No_email_match'] = "El correu facilitau nun coincide col d'esi nome d'usuariu";
$lang['New_password_activation'] = "Activación de nuea contraseña";
$lang['Password_activated'] = "La su cuenta foy re-activada. Pa entrar use la contraseña q'apaez nel correu que recibió";

$lang['Send_email_msg'] = "Unviar un correu";
$lang['No_user_specified'] = "Nun especificose usuariu";
$lang['User_prevent_email'] = "Esti usuariu no desea recibir correu. Intente envia-y un mensaxe privau";
$lang['User_not_exist'] = "Esi usuariu nun existe";
$lang['CC_email'] = "Unviar una copia d'esti mensaxe a Usté";
$lang['Email_message_desc'] = "Esti mensaxe será unviao comu testu simple, nun incluya HTML ni BBCode. La direción de rempuesta pa esti mensaxe será'l so correu.";
$lang['Flood_email_limit'] = "Nun pue unviar otru correu n'esti momentu, intentelu más tarde";
$lang['Recipient'] = "Destinatariu";
$lang['Email_sent'] = "El correu unviose";
$lang['Send_email'] = "Unviar correu";
$lang['Empty_subject_email'] = "Tien q'enxertar un asuntu pa'l correu";
$lang['Empty_message_email'] = "Tien 1'enxertar un mensaxe pa ser enviau";


//
// Memberslist
//
$lang['Select_sort_method'] = "Ordenar por";
$lang['Sort'] = "Ordenar";
$lang['Sort_Top_Ten'] = "Los 10 autores que más plumien";
$lang['Sort_Joined'] = "Fecha de Rexistru";
$lang['Sort_Username'] = "Nome d'usuariu";
$lang['Sort_Location'] = "Ubicación";
$lang['Sort_Posts'] = "Cantidá de mensaxes";
$lang['Sort_Email'] = "Correu";
$lang['Sort_Website'] = "Sitiu Web";
$lang['Sort_Ascending'] = "Ascendente";
$lang['Sort_Descending'] = "Descendente";
$lang['Order'] = "Orden";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Panel de Control de Grupu";
$lang['Group_member_details'] = "Detalles de Miembros de Grupu";
$lang['Group_member_join'] = "Unise a Grupu";

$lang['Group_Information'] = "Información de Grupu";
$lang['Group_name'] = "Nome de Grupu";
$lang['Group_description'] = "Descrición de Grupu";
$lang['Group_membership'] = "Membresía de Grupu";
$lang['Group_Members'] = "Miembros de Grupu";
$lang['Group_Moderator'] = "Moderaor de Grupu";
$lang['Pending_members'] = "Miembros Pendientes";

$lang['Group_type'] = "Tipu de Grupu";
$lang['Group_open'] = "Grupu Abiertu";
$lang['Group_closed'] = "Grupu Trancau";
$lang['Group_hidden'] = "Grupu Ocultu";

$lang['Current_memberships'] = "Membresíes autuales";
$lang['Non_member_groups'] = "Grupos au nun ye miembru";
$lang['Memberships_pending'] = "Membresíes pendientes";

$lang['No_groups_exist'] = "Nun hay Grupos";
$lang['Group_not_exist'] = "Esi grupu nun existe";

$lang['Join_group'] = "Unise a Grupu";
$lang['No_group_members'] = "Esti grupu nun tien miembros";
$lang['Group_hidden_members'] = "Esti grupu ta ocultu, nun pue ver la su membresía";
$lang['No_pending_group_members'] = "Esti grupu nun tien miembros pendientes";
$lang["Group_joined"] = "Subscripción a grupu exitosa <br />Avisarase-y cuandu la so subscripción seya aprobada por el moderaor del grupu";
$lang['Group_request'] = "Realizose un pedio pa xuntase al grupu";
$lang['Group_approved'] = "El su pedio foy aprobau";
$lang['Group_added'] = "Usté agregose a esti grupu"; 
$lang['Already_member_group'] = "Usté ya ye miembru d'esti grupu";
$lang['User_is_member_group'] = "L'usuariu ya ye miembru d'esti grupu";
$lang['Group_type_updated'] = "Tipu de grupu actualizau con éxitu";

$lang['Could_not_add_user'] = "L'usuariu selecionau nun existe";
$lang['Could_not_anon_user'] = "Nun pue facer a Anónimu un miembru d'esti grupu";

$lang['Confirm_unsub'] = "¿Ta seguru que quier cancelar la suscrición a esti grupu?";
$lang['Confirm_unsub_pending'] = "La so suscrición a esti grupu entá nun foy aprobá, ¿Ta seguru de cancelar la suscrición?";

$lang['Unsub_success'] = "La so suscrición a esti grupu foy cancelá.";

$lang['Approve_selected'] = "Aprobar selecionaos";
$lang['Deny_selected'] = "Denegar selecionaos";
$lang['Not_logged_in'] = "Tien qu'entrar al Foru pa xunise a un Grupu.";
$lang['Remove_selected'] = "Esborriar Selecionaos";
$lang['Add_member'] = "Añadirr Miembru";
$lang['Not_group_moderator'] = "Usté nun ye moderaor d'esti grupu polo que nun pue facer esta acción.";

$lang['Login_to_join'] = "Entre pa unise a un grupu o alministrar les membresíes d'un grupu";
$lang['This_open_group'] = "Esti ye un grupu abiertu, calque pa solicitar membresía";
$lang['This_closed_group'] = "Esti ye un grupu cerrau, nun s'aceuten más usuarios";
$lang['This_hidden_group'] = "Esti ye un grupu ocultu, nun ta permitio axuntar usuarios automáticamente";
$lang['Member_this_group'] = "Usté ye miembru d'esti grupu";
$lang['Pending_this_group'] = "La su membresía n'esti grupu ta pendiente";
$lang['Are_group_moderator'] = "Usté ye'l moderaor de grupu";
$lang['None'] = "Nengunu";

$lang['Subscribe'] = "Suscribise";
$lang['Unsubscribe'] = "Cancelar Suscrición";
$lang['View_Information'] = "Güeyar Información";


//
// Search
//
$lang['Search_query'] = "Consulta de Búsquea";
$lang['Search_options'] = "Opciones de Búsquea";

$lang['Search_keywords'] = "Buscar per pallabras clave";
$lang['Search_keywords_explain'] = "Pue usar <u>AND</u> pa definir pallabras que tienen que tar enos resultaos, <u>OR</u> pa definir pallabras que puen tar enos resultados y <u>NOT</u> para definir pallabres que nun han de tar enos resultaos. Use * comu comodín pa les búsques parciales";
$lang['Search_author'] = "Buscar per Autor";
$lang['Search_author_explain'] = "Use * comu comodín pa búsques parciales";

$lang['Search_for_any'] = "Buscar cualisquier de les pallabras o usar consulta tal comu escribiose";
$lang['Search_for_all'] = "Buscar toles pallabres";
$lang['Search_title_msg'] = "Buscar en títulos y testu de los mensaxes";
$lang['Search_msg_only'] = "Buscar namás nel testu de los mensaxes";

$lang['Return_first'] = "Mostrar los primeros"; // seguiu por xxx caracteres en cuadru testu
$lang['characters_posts'] = "caracteres de los mensaxes";

$lang['Search_previous'] = "Buscar enos anteriores"; // seguiu per dies, selmanes, meses, años, nuna llista desplegable

$lang['Sort_by'] = "Ordenar por";
$lang['Sort_Time'] = "Fecha Publicación";
$lang['Sort_Post_Subject'] = "Asuntu Mensaxe";
$lang['Sort_Topic_Title'] = "Títulu del Tema";
$lang['Sort_Author'] = "Autor";
$lang['Sort_Forum'] = "Foru";

$lang['Display_results'] = "Mostrar resultaos comu";
$lang['All_available'] = "Tolos disponibles";
$lang['No_searchable_forums'] = "Nun tien permisu pa buscar enos foros d'esti sitiu web";

$lang['No_search_match'] = "Nun hay temes o mensaxes que coincidan colos sus criterios de búsquea";
$lang['Found_search_match'] = "Atopose %d coincidencia"; // ex. Atopose 1 coincidencia
$lang['Found_search_matches'] = "Atopáronse %d coincidencies"; // ex. Atoparonse 24 coincidencies

$lang['Close_window'] = "Piesllar Ventana";


//
// Entraes relacionaes con autorizaciones
//
// Los %s will serán reemplazaos con un de los siguientes arrays
$lang['Sorry_auth_announce'] = "Sentimoslo pero namás %s puen publicar anuncios n'esti foru";
$lang['Sorry_auth_sticky'] = "Sentimoslo pero namás %s puen publicar PostIt n'esti foru"; 
$lang['Sorry_auth_read'] = "Sentimoslo pero namás %s puen lleer temes n'esti foru"; 
$lang['Sorry_auth_post'] = "Sentimoslo pero namás %s puen publicar temes n'esti foru"; 
$lang['Sorry_auth_reply'] = "Sentimoslo pero namás %s puen responder a mensaxes n'esti foru"; 
$lang['Sorry_auth_edit'] = "Sentimoslo pero namás %s puen editar mensaxes n'esti foru"; 
$lang['Sorry_auth_delete'] = "Sentimoslo pero namás %s puen esborriar mensaxes n'esti foru"; 
$lang['Sorry_auth_vote'] = "Sentimoslo pero namás %s puen votar en encuestes n'esti foru"; 

// Estos remplacen los %s enes cadenes d'arriba
$lang['Auth_Anonymous_Users'] = "<b>usuarios anónimos</b>";
$lang['Auth_Registered_Users'] = "<b>usuarios rexistraos</b>";
$lang['Auth_Users_granted_access'] = "<b>usuarios con accesu especial</b>";
$lang['Auth_Moderators'] = "<b>moderaores</b>";
$lang['Auth_Administrators'] = "<b>alministraores</b>";

$lang['Not_Moderator'] = "Usté nun ye moderaor n'esti foru";
$lang['Not_Authorised'] = "Nun Autorizau";

$lang['You_been_banned'] = "Restringiose-y l'accesu a esti foru<br />Por favor contaute co'l webmaster o co'l alministraor del foru pa mayor información";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Hay 0 usuarios rexistraos y "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Hay %d usuarios rexistraos y "; // Hay 5 usuarios rexistraos y
$lang['Reg_user_online'] = "Hay %d usuariu rexistrau y "; // Hay 1 usuariu rexistrau y
$lang['Hidden_users_zero_online'] = "0 usuarios ocultos en llinia"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d usuarios ocultos en llinia"; // 6 usuarios ocultos en llinia
$lang['Hidden_user_online'] = "%d usuariu ocultu en llinia"; // 1 usuariu ocultu en llinia
$lang['Guest_users_online'] = "Hay %d usuarios invitaos en llinia"; // Hay 10 usuarios invitaos en llinia
$lang['Guest_users_zero_online'] = "Hay 0 invitaos en llinia"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Hay %d usuariu invitau en llinia"; // Hay 1 usuariu invitau en llinia
$lang['No_users_browsing'] = "Nun hay usuarios explorando esti foru";

$lang['Online_explain'] = "Estos datos tan basaos n'usuarios activos durante los últimos 5 minutos";

$lang['Forum_Location'] = "Ubicación del Foru";
$lang['Last_updated'] = "Ultima Autualización";

$lang['Forum_index'] = "Índiz del Foru";
$lang['Logging_on'] = "Entrando";
$lang['Posting_message'] = "Publicando mensaxe";
$lang['Searching_forums'] = "Buscando foros";
$lang['Viewing_profile'] = "Güeyandu Perfil";
$lang['Viewing_online'] = "Güeyandu quien ta en llinia";
$lang['Viewing_member_list'] = "Güeyandu llista de miembros";
$lang['Viewing_priv_msgs'] = "Güeyandu mensaxes privaos";
$lang['Viewing_FAQ'] = "Güeyandu entrugues frecuentes";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Panel de Control del Moderaor";
$lang['Mod_CP_explain'] = "Usando el siguiente formulariu pue facer operaciones de moderación n'esti foru. Puede trancar, desbloquear, mover o esborriar cualisquier númberu de temes.";

$lang['Select'] = "Selecionar";
$lang['Delete'] = "Esborriar";
$lang['Move'] = "Mover";
$lang['Lock'] = "Trancarr";
$lang['Unlock'] = "Desbloquear";

$lang['Topics_Removed'] = "Los temes selecionaos foron quitaos con éxitu de la base datos.";
$lang['Topics_Locked'] = "Los temes selecionaos foron cerraos";
$lang['Topics_Moved'] = "Los temes selecionaos foron movios";
$lang['Topics_Unlocked'] = "Los temes selecionaos foron desbloqueaos";
$lang['No_Topics_Moved'] = "Nun se movieron temes";

$lang['Confirm_delete_topic'] = "¿Ta seguru que quier elliminar el/los tema/es selecionau/os?";
$lang['Confirm_lock_topic'] = "¿Ta seguru que quier trancar el/los tema/es selecionau/os?";
$lang['Confirm_unlock_topic'] = "¿Ta seguru que quier desbloquear el/los tema/es selecionau/os?";
$lang['Confirm_move_topic'] = "¿Ta seguru que quier mover el/los tema/es selecionau/os?";

$lang['Move_to_forum'] = "Mover al foru";
$lang['Leave_shadow_topic'] = "Dexar tema sombreau nel foro vieyu.";

$lang['Split_Topic'] = "Panel de Control pa División de Temes";
$lang['Split_Topic_explain'] = "Usando el siguiente formulariu pue dividir un tema en dos, ya seya selecionando los mensaxes individualmente o dividiéndolu nun mensaxe determinau";
$lang['Split_title'] = "Títulu del nueu tema";
$lang['Split_forum'] = "Foru pal nueu tema";
$lang['Split_posts'] = "Dividir mensaxes selecionaos";
$lang['Split_after'] = "Dividir dende'l mensaxe selecionau";
$lang['Topic_split'] = "El tema selecionau foy dividiu con éxitu";

$lang['Too_many_error'] = "Slecionara munchos mensaxes. Namás pue escoyer un mensaxe pa dividir un tema a partir delli";

$lang['None_selected'] = "Nun selecionara temes pa esta operación. Por favor torne y selecione al menos un d'ellos.";
$lang['New_forum'] = "Nueu Foru";

$lang['This_posts_IP'] = "IP pa esti mensaxe";
$lang['Other_IP_this_user'] = "Otros IP's dende los qu'esti usuariu publicara mensaxes";
$lang['Users_this_IP'] = "Usuarios publicando dend'esti IP";
$lang['IP_info'] = "Información IP";
$lang['Lookup_IP'] = "Buscar per IP";


//
// Zones horaries ... pa mostrar en cada páxina
//
$lang['All_times'] = "Toles hores son %s"; // ex. Toles hores son GMT - 12 Hores 

// Estos muestranse ena llista desplegable de zona horaria
$lang['-12'] = 'GMT - 12 Hores'; 
$lang['-11'] = 'GMT - 11 Hores'; 
$lang['-10'] = 'GMT - 10 Hores'; 
$lang['-9'] = 'GMT - 9 Hores'; 
$lang['-8'] = 'GMT - 8 Hores'; 
$lang['-7'] = 'GMT - 7 Hores'; 
$lang['-6'] = 'GMT - 6 Hores'; 
$lang['-5'] = 'GMT - 5 Hores'; 
$lang['-4'] = 'GMT - 4 Hores'; 
$lang['-3.5'] = 'GMT - 3.5 Hores'; 
$lang['-3'] = 'GMT - 3 Hores'; 
$lang['-2'] = 'GMT - 2 Hores'; 
$lang['-1'] = 'GMT - 1 Hores'; 
$lang['0'] = 'GMT'; 
$lang['1'] = 'GMT + 1 Hora'; 
$lang['2'] = 'GMT + 2 Hores'; 
$lang['3'] = 'GMT + 3 Hores'; 
$lang['3.5'] = 'GMT + 3.5 Hores'; 
$lang['4'] = 'GMT + 4 Hores'; 
$lang['4.5'] = 'GMT + 4.5 Hores'; 
$lang['5'] = 'GMT + 5 Hores'; 
$lang['5.5'] = 'GMT + 5.5 Hores'; 
$lang['6'] = 'GMT + 6 Hores'; 
$lang['6.5'] = 'GMT + 6.5 Hores'; 
$lang['7'] = 'GMT + 7 Hores'; 
$lang['8'] = 'GMT + 8 Hores'; 
$lang['9'] = 'GMT + 9 Hores'; 
$lang['9.5'] = 'GMT + 9.5 Hores'; 
$lang['10'] = 'GMT + 10 Hores'; 
$lang['11'] = 'GMT + 11 Hores'; 
$lang['12'] = 'GMT + 12 Hores'; 

// These are displayed in the timezone select box 
$lang['tz']['-12'] = 'GMT - 12 Hores'; 
$lang['tz']['-11'] = 'GMT - 11 Hores'; 
$lang['tz']['-10'] = 'GMT - 10 Hores'; 
$lang['tz']['-9'] = 'GMT - 9 Hores'; 
$lang['tz']['-8'] = 'GMT - 8 Hores'; 
$lang['tz']['-7'] = 'GMT - 7 Hores'; 
$lang['tz']['-6'] = 'GMT - 6 Hores'; 
$lang['tz']['-5'] = 'GMT - 5 Hores'; 
$lang['tz']['-4'] = 'GMT - 4 Hores'; 
$lang['tz']['-3.5'] = 'GMT - 3.5 Hores'; 
$lang['tz']['-3'] = 'GMT - 3 Hores'; 
$lang['tz']['-2'] = 'GMT - 2 Hores'; 
$lang['tz']['-1'] = 'GMT - 1 Hores'; 
$lang['tz']['0'] = 'GMT'; 
$lang['tz']['1'] = 'GMT + 1 Hora'; 
$lang['tz']['2'] = 'GMT + 2 Hores'; 
$lang['tz']['3'] = 'GMT + 3 Hores'; 
$lang['tz']['3.5'] = 'GMT + 3.5 Hores'; 
$lang['tz']['4'] = 'GMT + 4 Hores'; 
$lang['tz']['4.5'] = 'GMT + 4.5 Hores'; 
$lang['tz']['5'] = 'GMT + 5 Hores'; 
$lang['tz']['5.5'] = 'GMT + 5.5 Hores'; 
$lang['tz']['6'] = 'GMT + 6 Hores'; 
$lang['tz']['6.5'] = 'GMT + 6.5 Hores'; 
$lang['tz']['7'] = 'GMT + 7 Hores'; 
$lang['tz']['8'] = 'GMT + 8 Hores'; 
$lang['tz']['9'] = 'GMT + 9 Hores'; 
$lang['tz']['9.5'] = 'GMT + 9.5 Hores'; 
$lang['tz']['10'] = 'GMT + 10 Hores'; 
$lang['tz']['11'] = 'GMT + 11 Hores'; 
$lang['tz']['12'] = 'GMT + 12 Hores'; 

$lang['datetime']['Sunday'] = "Domingu";
$lang['datetime']['Monday'] = "Llunes";
$lang['datetime']['Tuesday'] = "Martes";
$lang['datetime']['Wednesday'] = "Miércoles";
$lang['datetime']['Thursday'] = "Xueves";
$lang['datetime']['Friday'] = "Vienres";
$lang['datetime']['Saturday'] = "Sábadu";
$lang['datetime']['Sun'] = "Dom";
$lang['datetime']['Mon'] = "Llu";
$lang['datetime']['Tue'] = "Mar";
$lang['datetime']['Wed'] = "Mie";
$lang['datetime']['Thu'] = "Xue";
$lang['datetime']['Fri'] = "Vie";
$lang['datetime']['Sat'] = "Sab";
$lang['datetime']['January'] = "Xineru";
$lang['datetime']['February'] = "Febreru";
$lang['datetime']['March'] = "Marzu";
$lang['datetime']['April'] = "Abril";
$lang['datetime']['May'] = "Mayu";
$lang['datetime']['June'] = "Xunu";
$lang['datetime']['July'] = "Xunetu";
$lang['datetime']['August'] = "Augostu";
$lang['datetime']['September'] = "Setiembre";
$lang['datetime']['October'] = "Ochobre";
$lang['datetime']['November'] = "Payares";
$lang['datetime']['December'] = "Avientu";
$lang['datetime']['Jan'] = "Xin";
$lang['datetime']['Feb'] = "Feb";
$lang['datetime']['Mar'] = "Mar";
$lang['datetime']['Apr'] = "Abr";
$lang['datetime']['May'] = "May";
$lang['datetime']['Jun'] = "Xun";
$lang['datetime']['Jul'] = "Xut";
$lang['datetime']['Aug'] = "Ago";
$lang['datetime']['Sep'] = "Set";
$lang['datetime']['Oct'] = "Och";
$lang['datetime']['Nov'] = "Pay";
$lang['datetime']['Dec'] = "Avi";

//
// Errores (nun relacionaos con un fallu específicu nuna
// páxina)
//
$lang['Information'] = "Información";
$lang['Critical_Information'] = "Información Crítica";

$lang['General_Error'] = "Error Xeneral";
$lang['Critical_Error'] = "Error Críticu";
$lang['An_error_occured'] = "Ocurriera un Error";
$lang['A_critical_error'] = "Ocurriera un Error Críticu";

//
// ¡Y POR FIN FINAMOOOOOOSSSSSSS!
// -------------------------------------------------

?>