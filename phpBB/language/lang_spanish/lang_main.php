<?php
/***************************************************************************
 *                            lang_main.php [English]
 *                              -------------------
 *     begin                : Wed Dec 12 2001
 *     copyright            : Alexis Bellido Medina (alexis@ventanazul.com)
 *                            Mariano Martene (pacha@maestrosdelweb.com)
 *                            Angelika Lautz (alautz@promis.net)
 *                            Patricio Marin (pmarin@hotmail.com)
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
//
//

//setlocale(LC_ALL, "es");
$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "d M Y"; // Esto se debería cambiar al formato predeterminado para su idioma, formato como php date()

//
// Comunes, estos términos se usan bastante
// en varias páginas
//
$lang['Forum'] = "Foro";
$lang['Category'] = "Categoría";
$lang['Topic'] = "Tema";
$lang['Topics'] = "Temas";
$lang['Replies'] = "Respuestas";
$lang['Views'] = "Lecturas";
$lang['Post'] = "Mensaje";
$lang['Posts'] = "Mensajes";
$lang['Posted'] = "Publicado";
$lang['Username'] = "Nombre de Usuario";
$lang['Password'] = "Contraseña";
$lang['Email'] = "Email";
$lang['Poster'] = "Autor";
$lang['Author'] = "Autor";
$lang['Time'] = "Horas";
$lang['Hours'] = "Horas";
$lang['Message'] = "Mensaje";

$lang['1_Day'] = "1 Día";
$lang['7_Days'] = "7 Días";
$lang['2_Weeks'] = "2 Semanas";
$lang['1_Month'] = "1 Mes";
$lang['3_Months'] = "3 Meses";
$lang['6_Months'] = "6 Meses";
$lang['1_Year'] = "1 Año";

$lang['Go'] = "Ir";
$lang['Jump_to'] = "Saltar a";
$lang['Submit'] = "Enviar";
$lang['Reset'] = "Resetear";
$lang['Cancel'] = "Cancelar";
$lang['Preview'] = "Vista Preliminar";
$lang['Confirm'] = "Confirmar";
$lang['Spellcheck'] = "Ortografía";
$lang['Yes'] = "Si";
$lang['No'] = "No";
$lang['Enabled'] = "Habilitado";
$lang['Disabled'] = "Deshabilitado";
$lang['Error'] = "Error";

$lang['Next'] = "Siguiente";
$lang['Previous'] = "Anterior";
$lang['Goto_page'] = "Ir a página";
$lang['Joined'] = "Registrado";
$lang['IP_Address'] = "Dirección IP";

$lang['Select_forum'] = "Seleccione un foro";
$lang['View_latest_post'] = "Ver último mensaje";
$lang['View_newest_post'] = "Ver el mensaje mas reciente";
$lang['Page_of'] = "Página <b>%d</b> de <b>%d</b>"; // Será reemplazado con : Página 1 de 2 por ejemplo

$lang['ICQ'] = "Número ICQ";
$lang['AIM'] = "Dirección AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "%s Indice de Foros";  // eg. Nombre de Sitio Indice de Foros, %s se puede quitar si lo desea

$lang['Post_new_topic'] = "Publicar nuevo tema";
$lang['Reply_to_topic'] = "Responder al tema";
$lang['Reply_with_quote'] = "Responder citando";

$lang['Click_return_topic'] = "Click %saquí%s para volver al tema"; // %s's son para los url, no quitar!
$lang['Click_return_login'] = "Click %saquí%s para intentar de nuevo";
$lang['Click_return_forum'] = "Click %saquí%s para volver al foro";
$lang['Click_view_message'] = "Click %saquí%s para ver su mensaje";
$lang['Click_return_modcp'] = "Click %saquí%s para volver al Panel de Control del Moderador";
$lang['Click_return_group'] = "Click %saquí%s para volver a la Información del Grupo";

$lang['Admin_panel'] = "Ir a Panel de Administración";

$lang['Board_disable'] = "Lo sentimos pero este foro no se encuentra disponible, por favor intente mas tarde";


//
// Global Header strings
//
$lang['Registered_users'] = "Usuarios Registrados:";
$lang['Browsing_forum'] = "Usuarios navengando este foro:";
$lang['Online_users_zero_total'] = "En total hay <b>0</b> usuarios online :: ";
$lang['Online_users_total'] = "En total hay <b>%d</b> usuarios online :: ";
$lang['Online_user_total'] = "En total hay <b>%d</b> usuario online :: ";
$lang['Reg_users_zero_total'] = "0 Registrados, ";
$lang['Reg_users_total'] = "%d Registrados, ";
$lang['Reg_user_total'] = "%d Registrado, ";
$lang['Hidden_users_zero_total'] = "0 Ocultos y ";
$lang['Hidden_user_total'] = "%d Ocultos y ";
$lang['Hidden_users_total'] = "%d Ocultos y ";
$lang['Guest_users_zero_total'] = "0 Invitados";
$lang['Guest_users_total'] = "%d Invitados";
$lang['Guest_user_total'] = "%d Invitado";
$lang['Record_online_users'] = "La mayor cantidad de usuarios online fue <b>%s</b> el %s"; // first %s = number of users, second %s is the date.

$lang['Admin_online_color'] = "%sAdministrador%s";
$lang['Mod_online_color'] = "%sModerador%s";

$lang['You_last_visit'] = "Su última visita fué: %s"; // %s reemplazado por fecha y hora
$lang['Current_time'] = "Fecha y hora actual: %s"; // %s reemplazado por hora

$lang['Search_new'] = "Ver mensajes desde última visita";
$lang['Search_your_posts'] = "Ver sus mensajes";
$lang['Search_unanswered'] = "Ver mensajes sin respuesta";

$lang['Register'] = "Registrarse";
$lang['Profile'] = "Perfil";
$lang['Edit_profile'] = "Editar su perfil";
$lang['Search'] = "Buscar";
$lang['Memberlist'] = "Miembros";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Guía BBCode";
$lang['Usergroups'] = "Grupos de Usuarios";
$lang['Last_Post'] = "Ultimo Mensaje";
$lang['Moderator'] = "Moderador";
$lang['Moderators'] = "Moderadores";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Nuestros usuarios han publicado <b>0</b> mensajes"; // Number of posts
$lang['Posted_articles_total'] = "Nuestros usuarios han publicado un total de <b>%d</b> mensajes"; // Número de mensajes
$lang['Posted_article_total'] = "Nuestros usuarios han publicado un total de <b>%d</b> mensajes"; // Número de mensajes
$lang['Registered_users_zero_total'] = "Tenemos <b>0</b> usuarios registrados"; // # registered users
$lang['Registered_users_total'] = "Tenemos <b>%d</b> usuarios registrados"; // # usuarios registrados
$lang['Registered_user_total'] = "Tenemos <b>%d</b> usuario registrado"; // # usuarios registrados
$lang['Newest_user'] = "El usuario registrado mas reciente es <b>%s%s%s</b>"; // un enlace a nombre de usuario, /a 

$lang['No_new_posts_last_visit'] = "No hay mensajes nuevos desde su última visita";
$lang['No_new_posts'] = "No hay mensajes nuevos";
$lang['New_posts'] = "Mensajes nuevos";
$lang['New_post'] = "Mensaje nuevo";
$lang['No_new_posts_hot'] = "No hay mensajes nuevos [ Popular ]";
$lang['New_posts_hot'] = "Mensajes nuevos [ Popular ]";
$lang['No_new_posts_locked'] = "No hay mensajes nuevos [ Cerrado ]";
$lang['New_posts_locked'] = "Mensajes nuevos [ Cerrado ]";
$lang['Forum_is_locked'] = "Foro cerrado";


//
// Login
//
$lang['Enter_password'] = "Por favor ingrese su nombre de usuario y contraseña para entrar";
$lang['Login'] = "Entrar";
$lang['Logout'] = "Salir";

$lang['Forgotten_password'] = "Olvidé mi contraseña";

$lang['Log_me_in'] = "Entrar automáticamente en cada visita";

$lang['Error_login'] = "Ha ingresado un nombre de usuario incorrecto o inactivo o una contraseña incorrecta";


//
// Index page
//
$lang['Index'] = "Indice";
$lang['No_Posts'] = "No hay mensajes";
$lang['No_forums'] = "No hay foros";

$lang['Private_Message'] = "Mensaje Privado";
$lang['Private_Messages'] = "Mensajes Privados";
$lang['Who_is_Online'] = "Quien está Online";

$lang['Mark_all_forums'] = "Marcar todos los foros como leidos";
$lang['Forums_marked_read'] = "Todos los foros se han marcado como leidos";


//
// Viewforum
//
$lang['View_forum'] = "Ver Foro";

$lang['Forum_not_exist'] = "El foro seleccionado no existe";
$lang['Reached_on_error'] = "Ha llegado por error a esta página";

$lang['Display_topics'] = "Mostrar temas anteriores";
$lang['All_Topics'] = "Todos los Temas";

$lang['Topic_Announcement'] = "<b>Anuncio:</b>";
$lang['Topic_Sticky'] = "<b>PostIt:</b>";
$lang['Topic_Moved'] = "<b>Movido:</b>";
$lang['Topic_Poll'] = "<b>[ Encuesta ]</b>";

$lang['Mark_all_topics'] = "Marcar todos los temas como leidos";
$lang['Topics_marked_read'] = "Los temas de este foro han sido marcados como leidos";

$lang['Rules_post_can'] = "<b>Puede</b> publicar nuevos temas en este foro";
$lang['Rules_post_cannot'] = "<b>Puede</b> publicar nuevos temas en este foro";
$lang['Rules_reply_can'] = "<b>Puede</b> responder a temas en este foro";
$lang['Rules_reply_cannot'] = "<b>No puede</b> responder a temas en este foro";
$lang['Rules_edit_can'] = "<b>Puede</b> editar sus mensajes en este foro";
$lang['Rules_edit_cannot'] = "<b>No puede</b> editar sus mensajes en este foro";
$lang['Rules_delete_can'] = "<b>Puede</b> borrar sus mensajes en este foro";
$lang['Rules_delete_cannot'] = "<b>No puede</b> borrar sus mensajes en este foro";
$lang['Rules_vote_can'] = "<b>Puede</b> votar en encuestas en este foro";
$lang['Rules_vote_cannot'] = "<b>No puede</b> votar en encuestas en este foro";
$lang['Rules_moderate'] = "<b>Puede</b> %smoderar este foro%s"; // %s reemplazado por enlaces, no quitar! 

$lang['No_topics_post_one'] = "No hay temas en este foro<br />Click en <b>Nuevo Tema</b> para publicar un nuevo tema";


//
// Viewtopic
//
$lang['View_topic'] = "Ver tema";

$lang['Guest'] = 'Invitado';
$lang['Post_subject'] = "<b>Asunto</b>";
$lang['View_next_topic'] = "Ver tema siguiente";
$lang['View_previous_topic'] = "Ver tema anterior";
$lang['Submit_vote'] = "Enviar voto";
$lang['View_results'] = "Ver resultados";

$lang['No_newer_topics'] = "No hay temas nuevos en este foro";
$lang['No_older_topics'] = "No hay temas anteriores en este foro";
$lang['Topic_post_not_exist'] = "El tema o mensaje solicitado no existe";
$lang['No_posts_topic'] = "No existen mensajes para este tema";

$lang['Display_posts'] = "Mostrar mensajes de anteriores";
$lang['All_Posts'] = "Todos los mensajes";
$lang['Newest_First'] = "El mas reciente primero";
$lang['Oldest_First'] = "El mas antiguo primero";

$lang['Back_to_top'] = "Volver arriba";

$lang['Read_profile'] = "Ver perfil de usuario"; 
$lang['Send_email'] = "Enviar email a usuario";
$lang['Visit_website'] = "Visitar sitio web del autor";
$lang['ICQ_status'] = "Estatus ICQ";
$lang['Edit_delete_post'] = "Editar/Borrar este mensaje";
$lang['View_IP'] = "Ver IP del autor";
$lang['Delete_post'] = "Borrar este mensaje";

$lang['wrote'] = "escribió"; // precede al nombre de usuario y es seguido por el texto citado
$lang['Quote'] = "Cita"; // viene antes de la salida de bbcode citar
$lang['Code'] = "Código"; // viene antes de la salida de bbcode código

$lang['Edited_time_total'] = "Ultima edición por %s el %s, editado %d vez"; // Ultima edición por mi el Oct 2001, editado 1 vez
$lang['Edited_times_total'] = "Ultima edición por %s el %s, editado %d veces"; // Ultima edición por mi el Oct 2001, editado 2 veces

$lang['Lock_topic'] = "Cerrar este tema";
$lang['Unlock_topic'] = "Desbloquear este tema";
$lang['Move_topic'] = "Mover este tema";
$lang['Delete_topic'] = "Borrar este tema";
$lang['Split_topic'] = "Separar este tema";

$lang['Stop_watching_topic'] = "Dejar de observar este tema";
$lang['Start_watching_topic'] = "Observar este tema por respuestas";
$lang['No_longer_watching'] = "Ya no está observando este tema";
$lang['You_are_watching'] = "Ahora está observando este tema";

$lang['Total_votes'] = "Votos Totales";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Cuerpo del mensaje";
$lang['Topic_review'] = "Revisar tema";

$lang['No_post_mode'] = "No se especificó modo de mensaje"; // Si se llama posting.php sin un modo (newtopic/reply/delete/etc, no debería mostrarse normalmente)

$lang['Post_a_new_topic'] = "Publicar un nuevo tema";
$lang['Post_a_reply'] = "Publicar una respuesta";
$lang['Post_topic_as'] = "Publicar tema como";
$lang['Edit_Post'] = "Editar mensaje";
$lang['Options'] = "Opciones";

$lang['Post_Announcement'] = "Anuncio";
$lang['Post_Sticky'] = "PostIt";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "¿Está seguro que quiere borrar este mensaje?";
$lang['Confirm_delete_poll'] = "¿Está seguro que quiere borrar esta encuesta?";

$lang['Flood_Error'] = "No puede publicar otro tema tan rápido después del último, por favor intente nuevamente en unos momentos";
$lang['Empty_subject'] = "Debe especificar un asunto cuando publique un nuevo tema";
$lang['Empty_message'] = "Debe escribir un mensaje para publicar";
$lang['Forum_locked'] = "Este foro está cerrado y no puede publicar, responder o editar temas";
$lang['Topic_locked'] = "Este tema está cerrado y no puede editar mensajes o responder";
$lang['No_post_id'] = "Debe seleccionar un mensaje para editar";
$lang['No_topic_id'] = "Debe seleccionar un tema al cual responder";
$lang['No_valid_mode'] = "Solo puede publicar, responder, editar o citar mensajes, por favor regrese e intente nuevamente";
$lang['No_such_post'] = "No existe ese mensaje, regrese e intente nuevamente";
$lang['Edit_own_posts'] = "Lo sentimos pero solo puede editar sus propios mensajes";
$lang['Delete_own_posts'] = "Lo sentimos pero solo puede borrar sus propios mensajes";
$lang['Cannot_delete_replied'] = "Lo sentimos pero no puede borrar mensajes que han sido respondidos";
$lang['Cannot_delete_poll'] = "Lo sentimos pero no puede borrar una encuesta activa";
$lang['Empty_poll_title'] = "Debe escribir un título para su mensaje";
$lang['To_few_poll_options'] = "Debe ingresar al menos dos opciones para la encuesta";
$lang['To_many_poll_options'] = "Ha ingresado demasiadas opciones para la encuesta";
$lang['Post_has_no_poll'] = "Este mensaje no tiene encuesta";

$lang['Add_poll'] = "Agregar una encuesta";
$lang['Add_poll_explain'] = "Si no desea agregar una encuesta a su tema deje los campos en blanco";
$lang['Poll_question'] = "Pregunta de la Encuesta";
$lang['Poll_option'] = "Opción de Encuesta";
$lang['Add_option'] = "Agregar Opción";
$lang['Update'] = "Actualizar";
$lang['Delete'] = "Borrar";
$lang['Poll_for'] = "Correr encuesta por";
$lang['Days'] = "Días"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Escriba 0 o deje en blanco para que la encuesta no termine ]";
$lang['Delete_poll'] = "Borrar Encuesta";

$lang['Disable_HTML_post'] = "Deshabilitar HTML en este mensaje";
$lang['Disable_BBCode_post'] = "Deshabilitar BBCode en este mensaje";
$lang['Disable_Smilies_post'] = "Deshabilitar Smilies en este mensaje";

$lang['HTML_is_ON'] = "HTML está <u>ON</u>";
$lang['HTML_is_OFF'] = "HTML está <u>OFF</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s está <u>ON</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s está <u>OFF</u>";
$lang['Smilies_are_ON'] = "Smilies están <u>ON</u>";
$lang['Smilies_are_OFF'] = "Smilies están <u>OFF</u>";

$lang['Attach_signature'] = "Adjuntar firma (la firma puede ser cambiada en el perfil)";
$lang['Notify'] = "Notificarme cuando se publique una respuesta";
$lang['Delete_post'] = "Borrar este mensaje";

$lang['Stored'] = "Su mensaje ha sido publicado con éxito";
$lang['Deleted'] = "Su mensaje ha sido borrado con éxito";
$lang['Poll_delete'] = "Su encuesta ha sido borrada con éxito";
$lang['Vote_cast'] = "Su voto ha sido publicado";

$lang['Topic_reply_notification'] = "Notificación de Respuesta a Tema";

$lang['bbcode_b_help'] = "Negrita: [b]texto[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Cursiva: [i]texto[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Subrayado: [u]texto[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Cita: [quote]texto[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Código: [code]código[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Lista: [list]texto[/list] (alt+l)";
$lang['bbcode_o_help'] = "Lista ordenada: [list=]texto[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Insertar imagen: [img]http://url_imagen[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Insertar URL: [url]http://url[/url] o [url=http://url]texto URL[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Cerrar todos los marcadores de bbCode abiertos";
$lang['bbcode_s_help'] = "Color: [color=red]texto[/color]  Nota: Puede usar color=#FF0000";
$lang['bbcode_f_help'] = "Tamaño: [size=x-small]texto pequeño[/size]";

$lang['Emoticons'] = "Emoticons";
$lang['More_emoticons'] = "Ver mas Emoticons";

$lang['Font_color'] = "Color";
$lang['color_default'] = "Predeterminado";
$lang['color_dark_red'] = "Rojo oscuro";
$lang['color_red'] = "Rojo";
$lang['color_orange'] = "Naranja";
$lang['color_brown'] = "Marrón";
$lang['color_yellow'] = "Amarillo";
$lang['color_green'] = "Verde";
$lang['color_olive'] = "Oliva";
$lang['color_cyan'] = "Cyan";
$lang['color_blue'] = "Azul";
$lang['color_dark_blue'] = "Azul Oscuro";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violeta";
$lang['color_white'] = "Blanco";
$lang['color_black'] = "Negro";

$lang['Font_size'] = "Tamaño";
$lang['font_tiny'] = "Miniatura";
$lang['font_small'] = "Pequeña";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Grande";
$lang['font_huge'] = "Super Grande";

$lang['Close_Tags'] = "Cerrar marcadores";
$lang['Styles_tip'] = "Nota: Se pueden aplicar estilos rápidamente al texto seleccionado";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Mensajes Privados";

$lang['Login_check_pm'] = "Entre para ver sus mensajes privados";
$lang['New_pms'] = "Usted tiene %d mensajes nuevos"; // Usted tiene 2 mensajes nuevos
$lang['New_pm'] = "Usted tiene %d mensaje nuevo"; // Usted tiene 1 mensaje nuevo
$lang['No_new_pm'] = "Usted no tiene mensajes nuevos";
$lang['Unread_pms'] = "Usted tiene %d mensajes sin leer";
$lang['Unread_pm'] = "Usted tiene %d mensaje sin leer";
$lang['No_unread_pm'] = "Usted no tiene mensajes sin leer";
$lang['You_new_pm'] = "Tiene un nuevo mensaje privado en la bandeja de entrada";
$lang['You_new_pms'] = "Tiene nuevos mensajes privados en la bandeja de entrada";
$lang['You_no_new_pm'] = "No tiene mensajes privados nuevos";

$lang['Inbox'] = "Bandeja de Entrada";
$lang['Outbox'] = "Bandeja de Salida";
$lang['Savebox'] = "Elementos Guardados";
$lang['Sentbox'] = "Elementos Enviados";
$lang['Flag'] = "Marca";
$lang['Subject'] = "Asunto";
$lang['From'] = "De";
$lang['To'] = "Para";
$lang['Date'] = "Fecha";
$lang['Mark'] = "Marcar";
$lang['Sent'] = "Enviado";
$lang['Saved'] = "Guardado";
$lang['Delete_marked'] = "Borrar Marcados";
$lang['Delete_all'] = "Borrar Todos";
$lang['Save_marked'] = "Grabar Marcados"; 
$lang['Save_message'] = "Grabar Mensaje";
$lang['Delete_message'] = "Borrar Mensaje";

$lang['Display_messages'] = "Mostrar mensajes de los anteriores"; // Seguido por # de dias/semanas/meses
$lang['All_Messages'] = "Todos los mensajes";

$lang['No_messages_folder'] = "No tiene mensajes en esta carpeta";

$lang['PM_disabled'] = "Se han desactivado los Mensajes Privados en este Foro";
$lang['Cannot_send_privmsg'] = "Lo sentimos pero el administrador le ha desactivado la opción de enviar mensajes privados";
$lang['No_to_user'] = "Debe especificar un nombre de usuario para enviar este mensaje";
$lang['No_such_user'] = "Lo sentimos pero ese usuario no existe";

$lang['Disable_HTML_pm'] = "Deshabilitar HTML en este mensaje";
$lang['Disable_BBCode_pm'] = "Deshabilitar BBCode en este mensaje";
$lang['Disable_Smilies_pm'] = "Deshabilitar en este mensaje";

$lang['Message_sent'] = "Su mensaje ha sido enviado";

$lang['Click_return_inbox'] = "Click %saquí%s para volver a su Bandeja de Entrada";
$lang['Click_return_index'] = "Click %saquí%s para volver al Indice";

$lang['Send_a_new_message'] = "Enviar un nuevo mensaje privado";
$lang['Send_a_reply'] = "Responder a mensaje privado";
$lang['Edit_message'] = "Editar mensaje privado";

$lang['Notification_subject'] = "Ha llegado un nuevo mensaje privado";

$lang['Find_username'] = "Encontrar un usuario";
$lang['Find'] = "Encontrar";
$lang['No_match'] = "No se hallaron coincidencias";

$lang['No_post_id'] = "No se identificó un ID de mensaje";
$lang['No_such_folder'] = "No existe ese folder";
$lang['No_folder'] = "No se especificó folder";

$lang['Mark_all'] = "Marcar todos";
$lang['Unmark_all'] = "Desmarcar todos";

$lang['Confirm_delete_pm'] = "¿Está seguro que desea borrar este mensaje?";
$lang['Confirm_delete_pms'] = "¿Está seguro que desea borrar estos mensajes?";

$lang['Inbox_size'] = "Su Bandeja de Entrada está %d%% llena"; // eg. Su Bandeja de Entrada esta 50% llena
$lang['Sentbox_size'] = "Su Bandeja Elementos Enviados está %d%% llena"; 
$lang['Savebox_size'] = "Su Bandeja de Elementos Guardados está %d%% llena"; 

$lang['Click_view_privmsg'] = "Click %saquí%s para visitar su Bandeja de Entrada";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Viendo perfil :: %s"; // %s es nombre de usuario 
$lang['About_user'] = "Todo sobre %s"; // %s es nombre de usuario

$lang['Preferences'] = "Preferencias";
$lang['Items_required'] = "Los campos marcados con * son obligatorios a menos que se indique lo contrario";
$lang['Registration_info'] = "Información de Registro";
$lang['Profile_info'] = "Información de Perfil";
$lang['Profile_info_warn'] = "Esta información estará públicamente disponible";
$lang['Avatar_panel'] = "Panel de Control de Avatar";
$lang['Avatar_gallery'] = "Galería Avatar";

$lang['Website'] = "Sitio Web";
$lang['Location'] = "Ubicación";
$lang['Contact'] = "Contacto";
$lang['Email_address'] = "Email";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Enviar mensaje privado";
$lang['Hidden_email'] = "[ Oculto ]";
$lang['Search_user_posts'] = "Buscar mensajes de este usuario";
$lang['Interests'] = "Intereses";
$lang['Occupation'] = "Ocupación"; 
$lang['Poster_rank'] = "Ranking de Autor";

$lang['Total_posts'] = "Cantidad de Mensajes";
$lang['User_post_pct_stats'] = "%.2f%% del total"; // 1.25% del total
$lang['User_post_day_stats'] = "%.2f mensajes por día"; // 1.5 mensajes por dia
$lang['Search_user_posts'] = "Encontrar todos los mensajes de %s"; // Encontrar todos los mensajes del usuario

$lang['No_user_id_specified'] = "Lo sentimos pero ese usuario no existe";
$lang['Wrong_Profile'] = "No puede modificar un perfil que no sea el suyo propio.";

$lang['Only_one_avatar'] = "Solo se puede especificar un tipo de avatar";
$lang['File_no_data'] = "El archivo en el URL proporcionado no contiene datos";
$lang['No_connection_URL'] = "No se pudo establecer conexión con el URL proporcionado";
$lang['Incomplete_URL'] = "El URL está incompleto";
$lang['Wrong_remote_avatar_format'] = "El URL del avatar remoto no es válido";
$lang['No_send_account_inactive'] = "Lo sentimos pero su contraseña no puede ser recuparada porque su cuenta se encuentra actualmente desactivada. Por favor contacte al Administrador del Foro";

$lang['Always_smile'] = "Siempre activar Smilies";
$lang['Always_html'] = "Siempre permitir HTML";
$lang['Always_bbcode'] = "Siempre permitir BBCode";
$lang['Always_add_sig'] = "Siempre adjuntar mi Firma";
$lang['Always_notify'] = "Siempre avisarme cuando hay respuestas";
$lang['Always_notify_explain'] = "Envía email cuando alguien responde a un tema que Usted ha publicado. Esto puede ser cambiado siempre que Usted publica un mensaje";

$lang['Board_style'] = "Estilo de Foro";
$lang['Board_lang'] = "Idioma de Foro";
$lang['No_themes'] = "No hay temas en la base de datos";
$lang['Timezone'] = "Zona horaria";
$lang['Date_format'] = "Formato de Fecha";
$lang['Date_format_explain'] = "La sintaxis usada es idéntica a la función <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> de PHP";
$lang['Signature'] = "Firma";
$lang['Signature_explain'] = "Este es un bloque de texto que se puede agregar a los mensajes que publique. Existe un límite de %d caracteres";
$lang['Public_view_email'] = "Mostrar siempre mi Email";

$lang['Current_password'] = "Contraseña actual";
$lang['New_password'] = "Nueva contraseña";
$lang['Confirm_password'] = "Confirmar contraseña";
$lang['Confirm_password_explain'] = "Usted debe confirmar su actual contraseña si desea cambiarla o cambiar su dirección de correo electrónico";
$lang['password_if_changed'] = "Solo debe ingresar una contraseña si desea cambiarla";
$lang['password_confirm_if_changed'] = "Solo necesita confirmar su contraseña si la cambió arriba";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Muestra una pequeña imagen bajo sus detalles en los mensajes. Solo una imagen puede ser mostrada a la vez, su ancho no puede ser mayor que %d pixels, y su altura no mayor que %d pixels y el tamaño de archivo no mas de %dkB."; $lang['Upload_Avatar_file'] = "Enviar Avatar desde su PC";
$lang['Upload_Avatar_URL'] = "Enviar Avatar desde un URL";
$lang['Upload_Avatar_URL_explain'] = "Escriba el URL donde se encuentra el archivo de imagen de su Avatar, será copiado a este sitio.";
$lang['Pick_local_Avatar'] = "Seleccionar Avatar de la galería";
$lang['Link_remote_Avatar'] = "Vincular a un Avatar fuera de este sitio";
$lang['Link_remote_Avatar_explain'] = "Escriba el URL donde se encuentra el archivo de imagen de su Avatar.";
$lang['Avatar_URL'] = "URL de imagen de Avatar";
$lang['Select_from_gallery'] = "Seleccionar Avatar de galería";
$lang['View_avatar_gallery'] = "Mostrar Galería";

$lang['Select_avatar'] = "Seleccionar avatar";
$lang['Return_profile'] = "Cancelar avatar";
$lang['Select_category'] = "Seleccionar categoría";

$lang['Delete_Image'] = "Borrar Imagen";
$lang['Current_Image'] = "Imagen Actual";

$lang['Notify_on_privmsg'] = "Notificar de nuevos Mensajes Privados";
$lang['Popup_on_privmsg'] = "Desplegar nueva ventana cuando hay Mensajes Privados"; 
$lang['Popup_on_privmsg_explain'] = "Algunas plantillas pueden abrir una nueva ventana para informarle cuando ha recibido nuevos mensajes privados"; 
$lang['Hide_user'] = "Ocultar su estatus online";

$lang['Profile_updated'] = "Su perfil ha sido actualizado";
$lang['Profile_updated_inactive'] = "Su perfil ha sido actualizado, sin embargo, ha cambiado detalles importantes y su cuenta ha sido desactivada. Revise su email para averiguar como reactivar su cuenta, o si es necesaria la activación del Administrador espere a que este reactive su cuenta";

$lang['Password_mismatch'] = "Las contraseñas que ingresó no coinciden";
$lang['Current_password_mismatch'] = "La contraseña que ingresó no coincide con la que está almacenada en la base de datos";
$lang['Password_long'] = "Su contraseña no debe contener más de 32 caracteres";
$lang['Username_taken'] = "Lo lamentamos pero este nombre de usuario ya está en uso";
$lang['Username_invalid'] = "El nombre de usuario contiene un caracter inválido como \"";
$lang['Username_disallowed'] = "Disculpe, este nombre de usuario está restringido";
$lang['Email_taken'] = "Lo lamentamos pero esta dirección de correo electrónico ya ha sido registrada por un usuario";
$lang['Email_banned'] = "Disculpe, esta dirección de correo electrónico ha sido baneada";
$lang['Email_invalid'] = "La dirección de correo electrónico ingresada es inválida";
$lang['Signature_too_long'] = "La firma es muy larga";
$lang['Fields_empty'] = "Debe completar los campos obligatorios";
$lang['Avatar_filetype'] = "El tipo de imagen del avatar debe ser .jpg, .gif o .png";
$lang['Avatar_filesize'] = "El tamaño de archivo del avatar debe ser menor de %d kB"; // El tamaño de archivo del avatar debe ser menor de 6 kB
$lang['Avatar_imagesize'] = "El avatar debe tener menos de %d pixels de ancho por %d pixels de alto"; 

$lang['Welcome_subject'] = "Bienvenido a los Foros de %s"; // Bienvenido a los Foros de Nombre de Sitio
$lang['New_account_subject'] = "Nueva cuenta de usuario";
$lang['Account_activated_subject'] = "Cuenta Activada";

$lang['Account_added'] = "Gracias por registrarse, su cuenta ha sido creada. Ahora puede entrar con su nombre de usuario y contraseña";
$lang['Account_inactive'] = "Su cuenta ha sido creada. Sin embargo, este foro requiere activación de la cuenta, una clave de activación se ha enviado a su email. Por favor revise su email para mas información";
$lang['Account_inactive_admin'] = "Su cuenta ha sido creada. Sin embargo, este foro requiere activación del Administrador. Un email ha sido enviado al Administrador y Usted será informado cuando su cuenta haya sido activada";
$lang['Account_active'] = "Su cuenta ha sido activada. Gracias por registrarse";
$lang['Account_active_admin'] = "La cuenta ha sido activada";
$lang['Reactivate'] = "¡Reactive su cuenta!";
$lang['COPPA'] = "Su cuenta ha sido creada pero debe ser aprobada, por favor revise su email por mayores detalles.";

$lang['Registration'] = "Condiciones de Registro";
$lang['Reg_agreement'] = "Aun cuando los administradores y moderadores de estos foros harán todo lo posible por remover  cualquier material cuestionable tan pronto como sea posible, es imposible revisar todos los mensajes. Por lo tanto Usted acepta que todos los mensajes publicados en estos foros expresan las opiniones de sus autores y no la de los administradores, moderadores o el webmaster (excepto en mensajes publicados por ellos mismos) por lo cual no se les considerará responsables.<br /><br />Usted está de acuerdo en no publicar material abusivo, obsceno, vulgar, de odio, amenazante, orientado sexualmente, o ningun otro que de alguna forma viole leyes vigentes. Si publicase material de esa índole su cuenta de acceso al foro será cancelada y su proveedor de Acceso a Internet será informado. La dirección IP de todos los mensajes es guardada para ayudar a cumplir estas normas. Usted está de acuerdo en que el webmaster, administrador y moderadores de este Foro tienen el derecho de borrar, editar, mover o cerrar cualquier tema en cualquier momento si lo consideran conveniente. Como usuario Usted acepta que toda la información que ingrese sea almacenada en una base de datos. Aun cuando esta información no será proporcionada a terceros sin su consentimiento, el webmaster, administrador y moderadores no pueden responsabilizarse por intentos de hackers que puedan llevar a que esta información se vea comprometida.<br /><br />Este sistema de foros utiliza cookies para almacenar información en su computadora local. Estos cookies no contienen la información que Usted ha ingresado, solamente se utilizan para mejorar la visualización de los foros. El email solamente es usado para confirmar sus detalles de registro y contraseña (y para enviar nuevas contraseñas si olvida la actual).<br /><br />Al registrarse Usted aceptará todas estas condiciones.";

$lang['Agree_under_13'] = "Estoy de acuerdo con estas condiciones y soy <b>menor</b> de 13 años de edad";
$lang['Agree_over_13'] = "Estoy de acuerdo con estas condiciones y soy <b>mayor</b> de 13 años de edad";
$lang['Agree_not'] = "No estoy de acuerdo con estas condiciones";

$lang['Wrong_activation'] = "La clave de activación suministrada no coincide con ninguna en la base de datos";
$lang['Send_password'] = "Enviarme una nueva contraseña"; 
$lang['Password_updated'] = "Se ha creado una nueva contraseña, por favor revise su email por detalles sobre como activarla";
$lang['No_email_match'] = "El email suministrado no coincide con el de ese nombre de usuario";
$lang['New_password_activation'] = "Activación de nueva contraseña";
$lang['Password_activated'] = "Su cuenta ha sido re-activada. Para entrar use la contraseña provista en el email que recibió";

$lang['Send_email_msg'] = "Enviar un email";
$lang['No_user_specified'] = "No se especificó usuario";
$lang['User_prevent_email'] = "Este usuario no desea recibir email. Intente enviarle un mensaje privado";
$lang['User_not_exist'] = "Ese usuario no existe";
$lang['CC_email'] = "Enviar una copia de este mensaje a Usted";
$lang['Email_message_desc'] = "Este mensaje será enviado como texto simple, no incluya HTML ni BBCode. La dirección de respuesta para este mensaje será su email.";
$lang['Flood_email_limit'] = "No puede enviar otro email en este momento, intentelo mas tarde";
$lang['Recipient'] = "Destinatario";
$lang['Email_sent'] = "El email ha sido enviado";
$lang['Send_email'] = "Enviar email";
$lang['Empty_subject_email'] = "Debe especificar un asunto para el email";
$lang['Empty_message_email'] = "Debe ingresar un mensaje para ser enviado";


//
// Memberslist
//
$lang['Select_sort_method'] = "Ordenar por";
$lang['Sort'] = "Ordenar";
$lang['Sort_Top_Ten'] = "Los 10 autores que mas escriben";
$lang['Sort_Joined'] = "Fecha de Registro";
$lang['Sort_Username'] = "Nombre de usuario";
$lang['Sort_Location'] = "Ubicación";
$lang['Sort_Posts'] = "Cantidad de mensajes";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Sitio Web";
$lang['Sort_Ascending'] = "Ascendente";
$lang['Sort_Descending'] = "Descendente";
$lang['Order'] = "Orden";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Panel de Control de Grupo";
$lang['Group_member_details'] = "Detalles de Membresía de Grupo";
$lang['Group_member_join'] = "Unirse a Grupo";

$lang['Group_Information'] = "Información de Grupo";
$lang['Group_name'] = "Nombre de Grupo";
$lang['Group_description'] = "Descripción de Grupo";
$lang['Group_membership'] = "Membresía de Grupo";
$lang['Group_Members'] = "Miembros de Grupo";
$lang['Group_Moderator'] = "Moderador de Grupo";
$lang['Pending_members'] = "Miembros Pendientes";

$lang['Group_type'] = "Tipo de Grupo";
$lang['Group_open'] = "Grupo Abierto";
$lang['Group_closed'] = "Grupo Cerrado";
$lang['Group_hidden'] = "Grupo Oculto";

$lang['Current_memberships'] = "Membresías actuales";
$lang['Non_member_groups'] = "Grupos donde no es miembro";
$lang['Memberships_pending'] = "Membresías pendientes";

$lang['No_groups_exist'] = "No existen Grupos";
$lang['Group_not_exist'] = "Ese grupo no existe";

$lang['Join_group'] = "Unirse a Grupo";
$lang['No_group_members'] = "Este grupo no tiene miembros";
$lang['Group_hidden_members'] = "Esta grupo está oculto, no puede ver su membresía";
$lang['No_pending_group_members'] = "Este grupo no tiene miembros pendientes";
$lang["Group_joined"] = "Subscripción a grupo exitosa <br />Usted será notificado cuando su subscripción sea aprobada por el moderador del grupo";
$lang['Group_request'] = "Se ha realizado un pedido para unirse al grupo";
$lang['Group_approved'] = "Su pedido ha sido aprobado";
$lang['Group_added'] = "Usted ha sido agregado a este grupo"; 
$lang['Already_member_group'] = "Usted ya es miembro de este grupo";
$lang['User_is_member_group'] = "El usuario ya es miembro de este grupo";
$lang['Group_type_updated'] = "Tipo de grupo actualizado con éxito";

$lang['Could_not_add_user'] = "El usuario que seleccionó no existe";
$lang['Could_not_anon_user'] = "No puede hacer a Anónimo un miembro de este grupo";

$lang['Confirm_unsub'] = "¿Esta seguro que quiere cancelar la subscripción a este grupo?";
$lang['Confirm_unsub_pending'] = "Su subscripción a este grupo aun no ha sido aprobada, ¿Esta seguro que desea cancelar la subscripción?";

$lang['Unsub_success'] = "Su subscripción a este grupo ha sido cancelada.";

$lang['Approve_selected'] = "Aprobar seleccionados";
$lang['Deny_selected'] = "Denegar seleccionados";
$lang['Not_logged_in'] = "Debe entrar al Foro para unirse a un Grupo.";
$lang['Remove_selected'] = "Borrar Seleccionados";
$lang['Add_member'] = "Agregar Miembro";
$lang['Not_group_moderator'] = "Usted no es moderador de este grupo por lo que no puede realizar esta acción.";

$lang['Login_to_join'] = "Entre para unirse a un grupo o administrar las membresías de un grupo";
$lang['This_open_group'] = "Este es un grupo abierto, click para solicitar membresía";
$lang['This_closed_group'] = "Este es un grupo cerrado, no se aceptan mas usuarios";
$lang['This_hidden_group'] = "Este es un grupo oculto, no se permite la adición automática de usuarios";
$lang['Member_this_group'] = "Usted es miembro de este grupo";
$lang['Pending_this_group'] = "Su membresía en este grupo está pendiente";
$lang['Are_group_moderator'] = "Usted es el moderador de grupo";
$lang['None'] = "Ninguno";

$lang['Subscribe'] = "Subscribirse";
$lang['Unsubscribe'] = "Cancelar Subscripción";
$lang['View_Information'] = "Ver Información";


//
// Search
//
$lang['Search_query'] = "Consulta de Búsqueda";
$lang['Search_options'] = "Opciones de Búsqueda";

$lang['Search_keywords'] = "Buscar por palabras clave";
$lang['Search_keywords_explain'] = "Puede usar <u>AND</u> para definir palabras que deben estar en los resultados, <u>OR</u> para definir palabras que pueden estar en los resultados y <u>NOT</u> para definir palabras que no deben estar en los resultados. Use * como un comodín para las búsqueda parciales";
$lang['Search_author'] = "Buscar por Autor";
$lang['Search_author_explain'] = "Use * como un comodín para búsquedas parciales";

$lang['Search_for_any'] = "Buscar cualquiera de las palabras o usar consulta tal como se escribió";
$lang['Search_for_all'] = "Buscar todas las palabras";
$lang['Search_title_msg'] = "Buscar en títulos y texto de los mensjaes";
$lang['Search_msg_only'] = "Buscar solamente en el texto de los mensajes";

$lang['Return_first'] = "Mostrar los primeros"; // seguido por xxx caracteres en cuadro de texto
$lang['characters_posts'] = "caracteres de los mensajes";

$lang['Search_previous'] = "Buscar en los anteriores"; // seguido por dias, semanas, meses, años, en una lista desplegable

$lang['Sort_by'] = "Ordenar por";
$lang['Sort_Time'] = "Fecha Publicación";
$lang['Sort_Post_Subject'] = "Asunto de Mensaje";
$lang['Sort_Topic_Title'] = "Título del Tema";
$lang['Sort_Author'] = "Autor";
$lang['Sort_Forum'] = "Foro";

$lang['Display_results'] = "Mostrar resultados como";
$lang['All_available'] = "Todos disponibles";
$lang['No_searchable_forums'] = "No tiene permiso para buscar en los foros de este sitio web";

$lang['No_search_match'] = "No hay temas o mensajes que coincidan con sus criterios de búsqueda";
$lang['Found_search_match'] = "Se encontró %d coincidencia"; // eg. Se encontró 1 coincidencia
$lang['Found_search_matches'] = "Se encontraron %d coincidencias"; // eg. Se encontraron 24 coincidencias

$lang['Close_window'] = "Cerrar Ventana";


//
// Entradas relacionadas con autorizaciones
//
// Los %s will serán reemplazados con uno de los siguientes arrays
$lang['Sorry_auth_announce'] = "Lo sentimos pero solo %s pueden publicar anuncios en este foro";
$lang['Sorry_auth_sticky'] = "Lo sentimos pero solo %s pueden publicar PostIt en este foro"; 
$lang['Sorry_auth_read'] = "Lo sentimos pero solo %s pueden leer temas en este foro"; 
$lang['Sorry_auth_post'] = "Lo sentimos pero solo %s pueden publicar temas en este foro"; 
$lang['Sorry_auth_reply'] = "Lo sentimos pero solo %s pueden responder a mensajes en este foro"; 
$lang['Sorry_auth_edit'] = "Lo sentimos pero solo %s pueden editar mensajes en este foro"; 
$lang['Sorry_auth_delete'] = "Lo sentimos pero solo %s pueden borrar mensajes en este foro"; 
$lang['Sorry_auth_vote'] = "Lo sentimos pero solo %s pueden votar en encuestas en este foro"; 

// Estos remplazan los %s en las cadenas de arriba
$lang['Auth_Anonymous_Users'] = "<b>usuarios anónimos</b>";
$lang['Auth_Registered_Users'] = "<b>usuarios registrados</b>";
$lang['Auth_Users_granted_access'] = "<b>usuarios con acceso especial</b>";
$lang['Auth_Moderators'] = "<b>moderadores</b>";
$lang['Auth_Administrators'] = "<b>administradores</b>";

$lang['Not_Moderator'] = "Usted no es moderador en este foro";
$lang['Not_Authorised'] = "No Autorizado";

$lang['You_been_banned'] = "Se le ha restringido el acceso a este foro<br />Por favor contacte al webmaster o al administrador del foro para mayor información";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Hay 0 usuarios registrados y "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Hay %d usuarios registrados y "; // Hay 5 usuarios registrados y
$lang['Reg_user_online'] = "Hay %d usuario registrado y "; // Hay 1 usuario registrado y
$lang['Hidden_users_zero_online'] = "0 usuarios ocultos online"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d usuarios ocultos online"; // 6 usuarios ocultos online
$lang['Hidden_user_online'] = "%d usuario oculto online"; // 1 usuario oculto online
$lang['Guest_users_online'] = "Hay %d usuarios invitados online"; // Hay 10 usuarios invitados online
$lang['Guest_users_zero_online'] = "Hay 0 invitados online"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Hay %d usuario invitado online"; // Hay 1 usuario invitado online
$lang['No_users_browsing'] = "No hay usuarios explorando este foro";

$lang['Online_explain'] = "Estos datos estan basados en usuarios activos durante los últimos 5 minutos";

$lang['Forum_Location'] = "Ubicación del Foro";
$lang['Last_updated'] = "Ultima Actualización";

$lang['Forum_index'] = "Indice de Foro";
$lang['Logging_on'] = "Entrando";
$lang['Posting_message'] = "Publicando mensaje";
$lang['Searching_forums'] = "Buscando foros";
$lang['Viewing_profile'] = "Viendo Perfil";
$lang['Viewing_online'] = "Viendo quien está online";
$lang['Viewing_member_list'] = "Viendo lista de miembros";
$lang['Viewing_priv_msgs'] = "Viendo mensajes privados";
$lang['Viewing_FAQ'] = "Viendo FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Panel de Control del Moderador";
$lang['Mod_CP_explain'] = "Usando el siguiente formulario puede realizar operaciones de moderación en este foro. Puede cerrar, desbloquear, mover o borrar cualquier número de temas.";

$lang['Select'] = "Seleccionar";
$lang['Delete'] = "Borrar";
$lang['Move'] = "Mover";
$lang['Lock'] = "Cerrar";
$lang['Unlock'] = "Desbloquear";

$lang['Topics_Removed'] = "Los temas seleccionados han sido removidos con éxito de la base de datos.";
$lang['Topics_Locked'] = "Los temas seleccionados han sido cerrados";
$lang['Topics_Moved'] = "Los temas seleccionados han sido movidos";
$lang['Topics_Unlocked'] = "Los temas seleccionados han sido desbloqueados";
$lang['No_Topics_Moved'] = "No se movieron temas";

$lang['Confirm_delete_topic'] = "¿Está seguro que quiere eliminar el/los tema/s seleccionado/s?";
$lang['Confirm_lock_topic'] = "¿Está seguro que quiere cerrar el/los tema/s seleccionado/s?";
$lang['Confirm_unlock_topic'] = "¿Está seguro que quiere desbloquear el/los tema/s seleccionado/s?";
$lang['Confirm_move_topic'] = "¿Está seguro que quiere mover el/los tema/s seleccionado/s?";

$lang['Move_to_forum'] = "Mover a foro";
$lang['Leave_shadow_topic'] = "Dejar tema sombreado en antiguo foro.";

$lang['Split_Topic'] = "Panel de Control para División de Temas";
$lang['Split_Topic_explain'] = "Usando el siguiente formulario puede dividir un tema en dos, ya sea seleccionando los mensajes individualmente o dividiendolo en un mensaje determinado";
$lang['Split_title'] = "Título del nuevo tema";
$lang['Split_forum'] = "Foro para nuevo tema";
$lang['Split_posts'] = "Dividir mensajes seleccionados";
$lang['Split_after'] = "Dividir desde el mensaje seleccionado";
$lang['Topic_split'] = "El tema seleccionado ha sido dividido con éxito";

$lang['Too_many_error'] = "Ha seleccionado muchos mensajes. Solo puede escoger un mensaje para dividir un tema a partir de él";

$lang['None_selected'] = "No ha seleccionado temas para esta operación. Por favor regrese y seleccione al menos uno.";
$lang['New_forum'] = "Nuevo Foro";

$lang['This_posts_IP'] = "IP para este mensaje";
$lang['Other_IP_this_user'] = "Otros IP's desde los que este usuario ha publicado mensajes";
$lang['Users_this_IP'] = "Usuarios publicando de este IP";
$lang['IP_info'] = "Información IP";
$lang['Lookup_IP'] = "Buscar por IP";


//
// Zonas horarias ... para mostrar en cada página
//
$lang['All_times'] = "Todas las horas son %s"; // ej. Todas las horas son GMT - 12 Horas 

$lang['-12'] = "GMT - 12 Horas";
$lang['-11'] = "GMT - 11 Horas";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "GMT - 9 Horas";
$lang['-8'] = "PST (U.S./Canada)";
$lang['-7'] = "MST (U.S./Canada)";
$lang['-6'] = "CST (U.S./Canada)";
$lang['-5'] = "EST (Lima /U.S./Canada)";
$lang['-4'] = "GMT - 4 Horas";
$lang['-3.5'] = "GMT - 3.5 Horas";
$lang['-3'] = "GMT - 3 Horas";
$lang['-2'] = "Mid-Atlantic";
$lang['-1'] = "GMT - 1 Hora";
$lang['0'] = "GMT";
$lang['1'] = "CET (Europa)";
$lang['2'] = "EET (Europa)";
$lang['3'] = "GMT + 3 Horas";
$lang['3.5'] = "GMT + 3.5 Horas";
$lang['4'] = "GMT + 4 Horas";
$lang['4.5'] = "GMT + 4.5 Horas";
$lang['5'] = "GMT + 5 Horas";
$lang['5.5'] = "GMT + 5.5 Horas";
$lang['6'] = "GMT + 6 Horas";
$lang['7'] = "GMT + 7 Horas";
$lang['8'] = "WST (Australia)";
$lang['9'] = "GMT + 9 Horas";
$lang['9.5'] = "CST (Australia)";
$lang['10'] = "EST (Australia)";
$lang['11'] = "GMT + 11 Horas";
$lang['12'] = "GMT + 12 Horas";

// Estos se muestran en la lista desplegable de zona horaria
$lang['tz']['-12'] = "(GMT -12:00 horas) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(GMT -11:00 horas) Midway Island, Samoa";
$lang['tz']['-10'] = "(GMT -10:00 horas) Hawaii";
$lang['tz']['-9'] = "(GMT -9:00 horas) Alaska";
$lang['tz']['-8'] = "(GMT -8:00 horas) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(GMT -7:00 horas) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(GMT -6:00 horas) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(GMT -5:00 horas) Lima, Bogotá, Quito, Eastern Time (US &amp; Canada)";
$lang['tz']['-4'] = "(GMT -4:00 horas) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(GMT -3:30 horas) Newfoundland";
$lang['tz']['-3'] = "(GMT -3:00 horas) Brassila, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(GMT -2:00 horas) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(GMT -1:00 horas) Azores, Cape Verde Islands";
$lang['tz']['0'] = "(GMT) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia";
$lang['tz']['1'] = "(GMT +1:00 horas) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome";
$lang['tz']['2'] = "(GMT +2:00 horas) Cairo, Helsinki, Kaliningrad, South Africa, Warsaw";
$lang['tz']['3'] = "(GMT +3:00 horas) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(GMT +3:30 horas) Tehran";
$lang['tz']['4'] = "(GMT +4:00 horas) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(GMT +4:30 horas) Kabul";
$lang['tz']['5'] = "(GMT +5:00 horas) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(GMT +5:30 horas) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(GMT +6:00 horas) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(GMT +6:30 horas) Rangoon";
$lang['tz']['7'] = "(GMT +7:00 horas) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(GMT +8:00 horas) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(GMT +9:00 horas) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(GMT +9:30 horas) Adelaide, Darwin";
$lang['tz']['10'] = "(GMT +10:00 horas) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(GMT +11:00 horas) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(GMT +12:00 horas) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "Domingo";
$lang['days_long'][1] = "Lunes";
$lang['days_long'][2] = "Martes";
$lang['days_long'][3] = "Miércoles";
$lang['days_long'][4] = "Jueves";
$lang['days_long'][5] = "Viernes";
$lang['days_long'][6] = "Sábado";

$lang['days_short'][0] = "Dom";
$lang['days_short'][1] = "Lun";
$lang['days_short'][2] = "Mar";
$lang['days_short'][3] = "Mie";
$lang['days_short'][4] = "Jue";
$lang['days_short'][5] = "Vie";
$lang['days_short'][6] = "Sab";

$lang['months_long'][0] = "Enero";
$lang['months_long'][1] = "Febrero";
$lang['months_long'][2] = "Marzo";
$lang['months_long'][3] = "Abril";
$lang['months_long'][4] = "Mayo";
$lang['months_long'][5] = "Junio";
$lang['months_long'][6] = "Julio";
$lang['months_long'][7] = "Agosto";
$lang['months_long'][8] = "Setiembre";
$lang['months_long'][9] = "Octubre";
$lang['months_long'][10] = "Noviembre";
$lang['months_long'][11] = "Diciembre";

$lang['months_short'][0] = "Ene";
$lang['months_short'][1] = "Feb";
$lang['months_short'][2] = "Mar";
$lang['months_short'][3] = "Abr";
$lang['months_short'][4] = "May";
$lang['months_short'][5] = "Jun";
$lang['months_short'][6] = "Jul";
$lang['months_short'][7] = "Ago";
$lang['months_short'][8] = "Set";
$lang['months_short'][9] = "Oct";
$lang['months_short'][10] = "Nov";
$lang['months_short'][11] = "Dic";

//
// Errores (no relacionados con una falla específica en 
// una página)
//
$lang['Information'] = "Información";
$lang['Critical_Information'] = "Información Crítica";

$lang['General_Error'] = "Error General";
$lang['Critical_Error'] = "Error Crítico";
$lang['An_error_occured'] = "Ocurrió un Error";
$lang['A_critical_error'] = "Ocurrió un Error Crítico";

//
// ¡Eso es todo amigos!
// -------------------------------------------------

?>