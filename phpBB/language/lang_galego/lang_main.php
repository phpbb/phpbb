<?php
/***************************************************************************
 *                            lang_main.php [Galician]
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
 
/****************************************************************************
 * Translation by:
 * Sergio Ares Chao :: sergio@ciberagendas.com
 ****************************************************************************/
     

//
// O formato deste arquivo é:
//
// ---> $lang['message'] = "text";
//
// Debería tamén tentar configurar a codificación local e de caracteres (ademais da dirección). A codificación e dirección serán
// enviados á plantilla. O locale pode que funcione
// ou non, depende do soporte do Sistema Operativo e a 
// sintaxe varía ... escolla como mellor lle pareza!
//

$lang['ENCODING'] = "iso-8859-1";
$lang['DIRECTION'] = "ltr";
$lang['LEFT'] = "left";
$lang['RIGHT'] = "right";
$lang['DATE_FORMAT'] =  "d M Y"; // Esto debería cambia-lo formato predeterminado para o seu idioma, formato coma php date()

// Esto é opcional, se queres incluír un PEQUENO texto
// co teu copyright indicando que eres o traductor
// engádeo aqui.
$lang['TRANSLATION_INFO'] = "&copy; 2002 Traducción ó Galego por <a href='mailto:sergio@ciberagendas.com' class='copyright'>Sergio Ares Chao</a>";

//
// Comúns, estes termos úsanse bastante
// en varias páxinas
//
$lang['Forum'] = "Foro";
$lang['Category'] = "Categoría";
$lang['Topic'] = "Tema";
$lang['Topics'] = "Temas";
$lang['Replies'] = "Respostas";
$lang['Views'] = "Lecturas";
$lang['Post'] = "Mensaxe";
$lang['Posts'] = "Mensaxes";
$lang['Posted'] = "Publicado";
$lang['Username'] = "Nome de Usuario";
$lang['Password'] = "Contrasinal";
$lang['Email'] = "Email";
$lang['Poster'] = "Autor";
$lang['Author'] = "Autor";
$lang['Time'] = "Hora";
$lang['Hours'] = "Horas";
$lang['Message'] = "Mensaxe";

$lang['1_Day'] = "1 Día";
$lang['7_Days'] = "7 Días";
$lang['2_Weeks'] = "2 Semanas";
$lang['1_Month'] = "1 Mes";
$lang['3_Months'] = "3 Meses";
$lang['6_Months'] = "6 Meses";
$lang['1_Year'] = "1 Ano";

$lang['Go'] = "Ir";
$lang['Jump_to'] = "Cambiar a";
$lang['Submit'] = "Enviar";
$lang['Reset'] = "Resetear";
$lang['Cancel'] = "Cancelar";
$lang['Preview'] = "Vista Preliminar";
$lang['Confirm'] = "Confirmar";
$lang['Spellcheck'] = "Ortografía";
$lang['Yes'] = "Si";
$lang['No'] = "Non";
$lang['Enabled'] = "Habilitado";
$lang['Disabled'] = "Deshabilitado";
$lang['Error'] = "Erro";

$lang['Next'] = "Seguinte";
$lang['Previous'] = "Anterior";
$lang['Goto_page'] = "Ir a páxina";
$lang['Joined'] = "Rexistrado";
$lang['IP_Address'] = "Enderezo IP";

$lang['Select_forum'] = "Seleccione un foro";
$lang['View_latest_post'] = "Ver última mensaxe";
$lang['View_newest_post'] = "Ver a mensaxe máis recente";
$lang['Page_of'] = "Páxina <b>%d</b> de <b>%d</b>"; // Será reemprazado por : Páxina 1 de 2 por exemplo

$lang['ICQ'] = "Número ICQ";
$lang['AIM'] = "Enderezo AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

//$lang['Forum_Index'] = "%s Índice de Foros";  // eg. Nome de Sitio Índice de Foros, %s pódese quitar se o desexa
$lang['Forum_Index'] = "Foros de discusión";  // eg. Nome de Sitio Índice de Foros, %s pódese quitar se o desexa

$lang['Post_new_topic'] = "Publicar novo tema";
$lang['Reply_to_topic'] = "Responde-lo tema";
$lang['Reply_with_quote'] = "Responder citando";

$lang['Click_return_topic'] = "Click %saquí%s para volver ó tema"; // %s's son para os url, non quitar!
$lang['Click_return_login'] = "Click %saquí%s para tentar de novo";
$lang['Click_return_forum'] = "Click %saquí%s para volver ó foro";
$lang['Click_view_message'] = "Click %saquí%s para ve-la súa mensaxe";
$lang['Click_return_modcp'] = "Click %saquí%s para volver ó Panel de Control do Moderador";
$lang['Click_return_group'] = "Click %saquí%s para volver á Información do Grupo";

$lang['Admin_panel'] = "Ir a Panel de Administración";

$lang['Board_disable'] = "Sentímolo pero momentaneamente este foro non se atopa dispoñible. Por favor tente ingresar máis tarde";


//
// Cadeas do encabezado Global
//
$lang['Registered_users'] = "Usuarios Rexistrados:";
$lang['Browsing_forum'] = "Usuarios navengando neste foro:";
$lang['Online_users_zero_total'] = "En total hai <b>0</b> usuarios online :: ";
$lang['Online_users_total'] = "En total hai <b>%d</b> usuarios online :: ";
$lang['Online_user_total'] = "En total hai <b>%d</b> usuario online :: ";
$lang['Reg_users_zero_total'] = "0 Rexistrados, ";
$lang['Reg_users_total'] = "%d Rexistrados, ";
$lang['Reg_user_total'] = "%d Rexistrado, ";
$lang['Hidden_users_zero_total'] = "0 Ocultos e ";
$lang['Hidden_user_total'] = "%d Oculto e ";
$lang['Hidden_users_total'] = "%d Ocultos e ";
$lang['Guest_users_zero_total'] = "0 Convidados";
$lang['Guest_users_total'] = "%d Convidados";
$lang['Guest_user_total'] = "%d Convidado";
$lang['Record_online_users'] = "A maior cantidade de usuarios online foi <b>%s</b> o %s"; // primeiro %s = número de usuarios, segundo %s é a data.

$lang['Admin_online_color'] = "%sAdministrador%s";
$lang['Mod_online_color'] = "%sModerador%s";

$lang['You_last_visit'] = "A súa última visita foi: %s"; // %s reemprazado por data e hora
$lang['Current_time'] = "Data e hora actual: %s"; // %s reemprazado por data e hora

$lang['Search_new'] = "Ver mensaxes desde última visita";
$lang['Search_your_posts'] = "Ver as súas mensaxes";
$lang['Search_unanswered'] = "Ver mensaxes sen resposta";

$lang['Register'] = "Rexistrarse";
$lang['Profile'] = "Perfil";
$lang['Edit_profile'] = "Edita-lo seu perfil";
$lang['Search'] = "Buscar";
$lang['Memberlist'] = "Membros";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Guía BBCode";
$lang['Usergroups'] = "Grupos de Usuarios";
$lang['Last_Post'] = "Última Mensaxe";
$lang['Moderator'] = "Moderador";
$lang['Moderators'] = "Moderadores";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Os nosos usuarios publicaron <b>0</b> mensaxes"; // Número de mensaxes = 0
$lang['Posted_articles_total'] = "Os nosos usuarios publicaron un total de <b>%d</b> mensaxes"; // Número de mensaxes
$lang['Posted_article_total'] = "Os nosos usuarios publicaron un total de <b>%d</b> mensaxe"; // Número de mensaxes
$lang['Registered_users_zero_total'] = "Temos <b>0</b> usuarios rexistrados"; // numero de usuarios rexistrados
$lang['Registered_users_total'] = "Temos <b>%d</b> usuarios rexistrados"; // numero de usuarios rexistrados
$lang['Registered_user_total'] = "Temos <b>%d</b> usuario rexistrado"; // numero de usuarios rexistrados
$lang['Newest_user'] = "O último usuario rexistrado é <b>%s%s%s</b>"; // un enlace ó nome de usuario, /a 

$lang['No_new_posts_last_visit'] = "Non hai mensaxes novas desde a súa última visita";
$lang['No_new_posts'] = "Non hai mensaxes novas";
$lang['New_posts'] = "Mensaxes novas";
$lang['New_post'] = "Mensaxe nova";
$lang['No_new_posts_hot'] = "Non hai mensaxes novas [ Popular ]";
$lang['New_posts_hot'] = "Mensaxes novas [ Popular ]";
$lang['No_new_posts_locked'] = "Non hai mensaxes novas [ Pechada ]";
$lang['New_posts_locked'] = "Mensaxes novas [ Pechada ]";
$lang['Forum_is_locked'] = "Foro pechado";


//
// Ingreso
//
$lang['Enter_password'] = "Por favor introduza o seu nome de usuario e contrasinal para entrar";
$lang['Login'] = "Login";
$lang['Logout'] = "Logout";

$lang['Forgotten_password'] = "Esquecín o meu contrasinal";

$lang['Log_me_in'] = "Entrar automáticamente en cada visita";

$lang['Error_login'] = "Ingresou un nome de usuario incorrecto ou inactivo, ou ben un contrasinal incorrecto";


//
// Index page
//
$lang['Index'] = "Índice";
$lang['No_Posts'] = "Non hai mensaxes";
$lang['No_forums'] = "Non hai foros";

$lang['Private_Message'] = "Mensaxe Privada";
$lang['Private_Messages'] = "Mensaxes Privadas";
$lang['Who_is_Online'] = "Quen está Online";

$lang['Mark_all_forums'] = "Marcar tódolos foros como lidos";
$lang['Forums_marked_read'] = "Tódolos foros marcáronse como lidos";


//
// Viewforum
//
$lang['View_forum'] = "Ver Foro";

$lang['Forum_not_exist'] = "O foro seleccionado non existe.";
$lang['Reached_on_error'] = "Chegou por erro a esta páxina.";

$lang['Display_topics'] = "Mostrar temas anteriores";
$lang['All_Topics'] = "Tódolos Temas";

$lang['Topic_Announcement'] = "<b>Anuncio:</b>";
$lang['Topic_Sticky'] = "<b>PostIt:</b>";
$lang['Topic_Moved'] = "<b>Movido:</b>";
$lang['Topic_Poll'] = "<b>[ Enquisa ]</b>";

$lang['Mark_all_topics'] = "Marcar tódolos temas como lidos";
$lang['Topics_marked_read'] = "Os temas deste foro foron marcados como lidos";

$lang['Rules_post_can'] = "<b>Pode</b> publicar novos temas neste foro";
$lang['Rules_post_cannot'] = "<b>Pode</b> publicar novos temas neste foro";
$lang['Rules_reply_can'] = "<b>Pode</b> responder a temas neste foro";
$lang['Rules_reply_cannot'] = "<b>Non pode</b> responder a temas neste foro";
$lang['Rules_edit_can'] = "<b>Pode</b> edita-las súas mensaxes neste foro";
$lang['Rules_edit_cannot'] = "<b>Non pode</b> edita-las súas mensaxes neste foro";
$lang['Rules_delete_can'] = "<b>Pode</b> borra-las súas mensaxes neste foro";
$lang['Rules_delete_cannot'] = "<b>Non pode</b> borra-las súas mensaxes neste foro";
$lang['Rules_vote_can'] = "<b>Pode</b> votar en enquisas neste foro";
$lang['Rules_vote_cannot'] = "<b>Non pode</b> votar en enquisas neste foro";
$lang['Rules_moderate'] = "<b>Pode</b> %smoderar este foro%s"; // %s reemprazado por enlaces, non quitar!

$lang['No_topics_post_one'] = "Non hai temas neste foro.<br />Click en <b>Novo Tema</b> para publicar un novo tema.";

       
//
// Viewtopic
//
$lang['View_topic'] = "Ver tema";

$lang['Guest'] = 'Convidado';
$lang['Post_subject'] = "<b>Asunto</b>";
$lang['View_next_topic'] = "Ver tema seguinte";
$lang['View_previous_topic'] = "Ver tema anterior";
$lang['Submit_vote'] = "Votar";
$lang['View_results'] = "Ver resultados";

$lang['No_newer_topics'] = "Non hai temas novos neste foro";
$lang['No_older_topics'] = "Non hai temas anteriores neste foro";
$lang['Topic_post_not_exist'] = "O tema ou mensaxe solicitado non existe";
$lang['No_posts_topic'] = "Non existen mensaxes para este tema";

$lang['Display_posts'] = "Mostrar mensaxes dos últimos";
$lang['All_Posts'] = "Tódalas mensaxes";
$lang['Newest_First'] = "A máis recente primeiro";
$lang['Oldest_First'] = "A máis antiga primeiro";

$lang['Back_to_top'] = "Volver arriba";

$lang['Read_profile'] = "Ver perfil de usuario";
$lang['Send_email'] = "Enviar email a usuario";
$lang['Visit_website'] = "Visitar sitio web do autor";
$lang['ICQ_status'] = "Estado ICQ";
$lang['Edit_delete_post'] = "Editar/Borrar esta mensaxe";
$lang['View_IP'] = "Ver enderezo IP do autor";
$lang['Delete_post'] = "Borrar esta mensaxe";

$lang['wrote'] = "escribiu"; // precede ó nome de usuario e é seguido polo texto citado
$lang['Quote'] = "Cita"; // vén antes da saída de bbcode citar
$lang['Code'] = "Código"; // vén antes da saída de bbcode código

$lang['Edited_time_total'] = "Última edición por %s o %s; editado %d vez"; // Última edición por min o 3 Out 2001, editado 1 vez
$lang['Edited_times_total'] = "Última edición por %s o %s; editado %d veces"; // Última edición por min 0 3 Out 2001, editado 2 veces

$lang['Lock_topic'] = "Pechar este tema";
$lang['Unlock_topic'] = "Desbloquear este tema";
$lang['Move_topic'] = "Mover este tema";
$lang['Delete_topic'] = "Borrar este tema";
$lang['Split_topic'] = "Separar este tema";

$lang['Stop_watching_topic'] = "Deixar de seguir este tema";
$lang['Start_watching_topic'] = "Seguir as respostas deste tema";
$lang['No_longer_watching'] = "Xa non está seguindo este tema";
$lang['You_are_watching'] = "Agora está seguindo este tema";

$lang['Total_votes'] = "Votos Totais";

//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Corpo da mensaxe";
$lang['Topic_review'] = "Revisar tema";

$lang['No_post_mode'] = "Non se especificou modo de mensaxe"; // Se chamase posting.php sen un modo (newtopic/reply/delete/etc, non debería mostrarse normalmente)

$lang['Post_a_new_topic'] = "Publicar un novo tema";
$lang['Post_a_reply'] = "Publicar unha resposta";
$lang['Post_topic_as'] = "Publicar tema como";
$lang['Edit_Post'] = "Editar mensaxe";
$lang['Options'] = "Opcións";

$lang['Post_Announcement'] = "Anuncio";
$lang['Post_Sticky'] = "PostIt";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "¿Está seguro de querer borrar esta mensaxe?";
$lang['Confirm_delete_poll'] = "¿Está seguro de querer borrar esta enquisa?";

$lang['Flood_Error'] = "Non se pode publicar outro tema tan rápido despois do último; por favor ténteo de novo nuns intres";
$lang['Empty_subject'] = "Debe especificar un asunto cando publique un novo tema.";
$lang['Empty_message'] = "Debe escribir unha mensaxe para publicar.";
$lang['Forum_locked'] = "Este foro está pechado: non pode publicar, responder ou editar temas.";
$lang['Topic_locked'] = "Este tema está pechado: non pode editar mensaxes ou responder.";
$lang['No_post_id'] = "Debe seleccionar unha mensaxe para editar.";
$lang['No_topic_id'] = "Debe seleccionar un tema ó cal responder.";
$lang['No_valid_mode'] = "Só pode publicar, responder, editar ou citar mensaxes; por favor volva e ténteo de novo.";
$lang['No_such_post'] = "Non existe esa mensaxe, volva e intente de novo.";
$lang['Edit_own_posts'] = "Sentímolo pero só pode edita-las súas propias mensaxes.";
$lang['Delete_own_posts'] = "Sentímolo pero só pode borra-las súas propias mensaxes.";
$lang['Cannot_delete_replied'] = "Sentímolo pero non pode borrar mensaxes que foron respondidas.";
$lang['Cannot_delete_poll'] = "Sentímolo pero non pode borrar unha enquisa activa.";
$lang['Empty_poll_title'] = "Debe escribir un título para a súa mensaxe.";
$lang['To_few_poll_options'] = "Debe introducir polo menos dúas opcións para a enquisa.";
$lang['To_many_poll_options'] = "Introduciu demasiadas opcións para a enquisa.";
$lang['Post_has_no_poll'] = "Esta mensaxe non ten enquisa.";

$lang['Add_poll'] = "Engadir unha enquisa";
$lang['Add_poll_explain'] = "Se non desexa engadir unha enquisa ó seu tema deixe os campos en branco.";
$lang['Poll_question'] = "Pregunta da Enquisa";
$lang['Poll_option'] = "Opción da Enquisa";
$lang['Add_option'] = "Engadir Opción";
$lang['Update'] = "Actualizar";
$lang['Delete'] = "Borrar";
$lang['Poll_for'] = "Deixar enquisa durante";
$lang['Days'] = "Días"; // Úsase en Deixar enquisa durante ... Días + en admin_forums para pruning
$lang['Poll_for_explain'] = "[ Escriba 0 ou deixe en branco para que a enquisa non termine ]";
$lang['Delete_poll'] = "Borrar Enquisa";
$lang['Already_voted'] = 'Vostede xa votou nesta enquisa'; 
$lang['No_vote_option'] = 'Debe especificar unha opción ó votar'; 

$lang['Disable_HTML_post'] = "Deshabilitar HTML nesta mensaxe";
$lang['Disable_BBCode_post'] = "Deshabilitar BBCode nesta mensaxe";
$lang['Disable_Smilies_post'] = "Deshabilitar Smileis nesta mensaxe";

$lang['HTML_is_ON'] = "HTML está <u>ON</u>";
$lang['HTML_is_OFF'] = "HTML está <u>OFF</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s está <u>ON</u>"; // %s se reemprazan pola URI apuntando á FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s está <u>OFF</u>";
$lang['Smilies_are_ON'] = "Smileis están <u>ON</u>";
$lang['Smilies_are_OFF'] = "Smileis están <u>OFF</u>";

$lang['Attach_signature'] = "Axuntar sinatura (a sinatura pode ser cambiada no perfil)";
$lang['Notify'] = "Notificarme cando se publique unha resposta";
$lang['Delete_post'] = "Borrar esta mensaxe";

$lang['Stored'] = "A súa mensaxe foi publicada con éxito.";
$lang['Deleted'] = "A súa mensaxe foi borrada con éxito.";
$lang['Poll_delete'] = "A súa enquisa foi borrada con éxito.";
$lang['Vote_cast'] = "O seu voto foi publicado.";

$lang['Topic_reply_notification'] = "Notificación de Resposta a Tema";

$lang['bbcode_b_help'] = "Negriña: [b]texto[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Cursiva: [i]texto[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Subraiado: [u]texto[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Cita: [quote]texto[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Código: [code]código[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Lista: [list]texto[/list] (alt+l)";
$lang['bbcode_o_help'] = "Lista ordenada: [list=]texto[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Insertar imaxe: [img]http://url_imaxe[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Insertar URL: [url]http://url[/url] o [url=http://url]texto URL[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Pechar tódolos marcadores de bbCode abertos";
$lang['bbcode_s_help'] = "Cor: [color=red]texto[/color]  Nota: Puede usar color=#FF0000";
$lang['bbcode_f_help'] = "Tamaño: [size=x-small]texto pequeno[/size]";

$lang['Emoticons'] = "Emoticonos";
$lang['More_emoticons'] = "Ver máis Emoticonos";

$lang['Font_color'] = "Cor";
$lang['color_default'] = "Predeterminado";
$lang['color_dark_red'] = "Vermello Escuro";
$lang['color_red'] = "Vermello";
$lang['color_orange'] = "Laranxa";
$lang['color_brown'] = "Marrón";
$lang['color_yellow'] = "Amarelo";
$lang['color_green'] = "Verde";
$lang['color_olive'] = "Oliva";
$lang['color_cyan'] = "Cian";
$lang['color_blue'] = "Azul";
$lang['color_dark_blue'] = "Azul Escuro";
$lang['color_indigo'] = "Índigo";
$lang['color_violet'] = "Violeta";
$lang['color_white'] = "Branco";
$lang['color_black'] = "Negro";

$lang['Font_size'] = "Tamaño";
$lang['font_tiny'] = "Miniatura";
$lang['font_small'] = "Pequena";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Grande";
$lang['font_huge'] = "Enorme";

$lang['Close_Tags'] = "Pechar marcadores";
$lang['Styles_tip'] = "Nota: Pódense aplicar estilos rapidamente ó texto seleccionado.";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Mensaxes Privadas";

$lang['Login_check_pm'] = "Entre para ve-las súas mensaxes privadas";
$lang['New_pms'] = "Vostede ten %d mensaxes novas"; // Vostede ten 2 mensaxes novas
$lang['New_pm'] = "Vostede ten %d mensaxe nova"; // Vostede ten 1 mensaxe nova
$lang['No_new_pm'] = "Non ten mensaxes novas";
$lang['Unread_pms'] = "Vostede ten %d mensaxes sen ler";
$lang['Unread_pm'] = "Vostede ten %d mensaxe sen ler";
$lang['No_unread_pm'] = "Vostede non ten mensaxes sen ler";
$lang['You_new_pm'] = "Ten unha nova mensaxe privada na bandexa de entrada";
$lang['You_new_pms'] = "Ten novas mensaxes privadas na bandexa de entrada";
$lang['You_no_new_pm'] = "Non ten mensaxes privadas novas";

$lang['Inbox'] = "Bandexa de Entrada";
$lang['Outbox'] = "Bandexa de Salida";
$lang['Savebox'] = "Elementos Gardados";
$lang['Sentbox'] = "Elementos Enviados";
$lang['Flag'] = "Marca";
$lang['Subject'] = "Asunto";
$lang['From'] = "De";
$lang['To'] = "Para";
$lang['Date'] = "Data";
$lang['Mark'] = "Marcar";
$lang['Sent'] = "Enviado";
$lang['Saved'] = "Gardado";
$lang['Delete_marked'] = "Borrar Marcados";
$lang['Delete_all'] = "Borrar Todos";
$lang['Save_marked'] = "Gardar Marcados"; 
$lang['Save_message'] = "Gardar Mensaxe";
$lang['Delete_message'] = "Borrar Mensaxe";

$lang['Display_messages'] = "Mostrar mensaxes dos anteriores"; // Seguido polo numero de días/semanas/meses
$lang['All_Messages'] = "Tódalas mensaxes";

$lang['No_messages_folder'] = "Non ten mensaxes nesta carpeta";

$lang['PM_disabled'] = "Desactiváronse as Mensaxes Privadas neste Foro.";
$lang['Cannot_send_privmsg'] = "Sentímolo pero o administrador desactivoulle a opción de enviar mensaxes privadas.";
$lang['No_to_user'] = "Debe especificar un nome de usuario para enviar esta mensaxe.";
$lang['No_such_user'] = "Sentímolo pero ese usuario non existe.";

$lang['Disable_HTML_pm'] = "Deshabilitar HTML nesta mensaxe";
$lang['Disable_BBCode_pm'] = "Deshabilitar BBCode nesta mensaxe";
$lang['Disable_Smilies_pm'] = "Deshabilitar Smileis nesta mensaxe";

$lang['Message_sent'] = "A súa mensaxe foi enviada.";

$lang['Click_return_inbox'] = "Click %saquí%s para volver á súa Bandexa de Entrada";
$lang['Click_return_index'] = "Click %saquí%s para volver ó Indice";

$lang['Send_a_new_message'] = "Enviar unha nova mensaxe privada";
$lang['Send_a_reply'] = "Responder á mensaxe privada";
$lang['Edit_message'] = "Editar mensaxe privada";

$lang['Notification_subject'] = "Chegou unha nova mensaxe privada";

$lang['Find_username'] = "Atopar un usuario";
$lang['Find'] = "Atopar";
$lang['No_match'] = "Non se atoparon coincidencias.";

$lang['No_post_id'] = "Non se identificou un ID de mensaxe";
$lang['No_such_folder'] = "Non existe esa carpeta";
$lang['No_folder'] = "Non se especificou unha carpeta";

$lang['Mark_all'] = "Marcar todas";
$lang['Unmark_all'] = "Desmarcar todas";

$lang['Confirm_delete_pm'] = "¿Está seguro de querer borrar esta mensaxe?";
$lang['Confirm_delete_pms'] = "¿Está seguro de querer borrar estas mensaxes?";

$lang['Inbox_size'] = "A súa Bandexa de Entrada está %d%% chea"; // ex. A Súa Bandexa de Entrada esta 50% chea
$lang['Sentbox_size'] = "A súa Bandexa Elementos Enviados está %d%% chea";
$lang['Savebox_size'] = "A súa Bandexa de Elementos Gardados está %d%% chea"; 

$lang['Click_view_privmsg'] = "Click %saquí%s para visita-la súa Bandexa de Entrada";

$lang['Read_pm'] = 'Ler mensaxe'; 
$lang['Post_new_pm'] = 'Enviar mensaxe'; 
$lang['Post_reply_pm'] = 'Contestar mensaxe'; 
$lang['Post_quote_pm'] = 'Citar mensaxe'; 
$lang['Edit_pm'] = 'Editar mensaxe'; 

$lang['Unread_message'] = 'Mensaxe non lida'; 
$lang['Read_message'] = 'Mensaxe lida'; 


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Vendo perfil :: %s"; // %s é nome de usuario 
$lang['About_user'] = "Todo sobre %s"; // %s é nome de usuario

$lang['Preferences'] = "Preferencias";
$lang['Items_required'] = "Os campos marcados con * son obligatorios agás que se indique o contrario.";
$lang['Registration_info'] = "Información de Rexistro";
$lang['Profile_info'] = "Información de Perfil";
$lang['Profile_info_warn'] = "Esta información estará publicamente dispoñible";
$lang['Avatar_panel'] = "Panel de Control de Avatar";
$lang['Avatar_gallery'] = "Galería de Avatares";

$lang['Website'] = "Sitio Web";
$lang['Location'] = "Ubicación";
$lang['Contact'] = "Contactar con";
$lang['Email_address'] = "Enderezo Email";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Enviar mensaxe privada";
$lang['Hidden_email'] = "[ Oculto ]";
$lang['Search_user_posts'] = "Buscar mensaxes deste usuario";
$lang['Interests'] = "Intereses";
$lang['Occupation'] = "Ocupación"; 
$lang['Poster_rank'] = "Rango do Autor";

$lang['Total_posts'] = "Cantidade de Mensaxes";
$lang['User_post_pct_stats'] = "%.2f%% do total"; // 1.25% do total
$lang['User_post_day_stats'] = "%.2f mensaxes por día"; // 1.5 mensaxes por dia
$lang['Search_user_posts'] = "Buscar tódalas mensaxes de %s"; // Encontrar tódalas mensaxes do usuario

$lang['No_user_id_specified'] = "Sentímolo, pero ese usuario non existe.";
$lang['Wrong_Profile'] = "Non se pode modificar un perfil que non sexa o seu propio.";

$lang['Only_one_avatar'] = "Só se pode especificar un tipo de avatar";
$lang['File_no_data'] = "O arquivo no URL proporcionado non contén datos";
$lang['No_connection_URL'] = "Non se puido establecer conexión co URL proporcionado";
$lang['Incomplete_URL'] = "O URL está incompleto";
$lang['Wrong_remote_avatar_format'] = "O URL do avatar remoto non é válido";
$lang['No_send_account_inactive'] = "Sentímolo, pero o seu contrasinal non pode ser recuparado porque a súa conta atópase actualmente desactivada. Por favor contacte ó Administrador do Foro.";

$lang['Always_smile'] = "Sempre activar Smileis";
$lang['Always_html'] = "Sempre permitir HTML";
$lang['Always_bbcode'] = "Sempre permitir BBCode";
$lang['Always_add_sig'] = "Sempre axunta-la miña Sinatura";
$lang['Always_notify'] = "Sempre avisarme cando hai respostas";
$lang['Always_notify_explain'] = "Envía un email cando alguén responde a un tema que Vostede publicou. Esto pode ser cambiado sempre que Vostede publica unha mensaxe";

$lang['Board_style'] = "Estilo do Foro";
$lang['Board_lang'] = "Idioma do Foro";
$lang['No_themes'] = "Non hai temas na base de datos";
$lang['Timezone'] = "Zona horaria";
$lang['Date_format'] = "Formato de Data";
$lang['Date_format_explain'] = "A sintaxe usada é idéntica á función <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> de PHP.";
$lang['Signature'] = "Sinatura";
$lang['Signature_explain'] = "Este é un bloque de texto que se pode engadir ás mensaxes que publique. Existe un límite de %d caracteres";
$lang['Public_view_email'] = "Mostrar sempre o meu Email";

$lang['Current_password'] = "Contrasinal actual";
$lang['New_password'] = "Novo contrasinal";
$lang['Confirm_password'] = "Confirmar contrasinal";
$lang['Confirm_password_explain'] = "Debe confirmar o seu actual contrasinal se desexa cambiar este ou o seu enderezo de correo electrónico";
$lang['password_if_changed'] = "Só debe ingresar un contrasinal se desexa cambiala";
$lang['password_confirm_if_changed'] = "Só necesita confirma-lo seu contrasinal se o cambio enriba";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Mostra unha pequena imaxe baixo os seus detalles nas mensaxes. Só se pode mostrar unha imaxe á vez, o seu ancho non pode ser maior de %d pixels, e a súa altura non maior de %d pixels e o tamaño de arquivo non máis de %dkB."; 
$lang['Upload_Avatar_file'] = "Enviar Avatar desde o seu PC";
$lang['Upload_Avatar_URL'] = "Enviar Avatar desde un URL";
$lang['Upload_Avatar_URL_explain'] = "Escriba o URL onde se atopa o arquivo de imaxe do seu Avatar, será copiado a este sitio.";
$lang['Pick_local_Avatar'] = "Seleccionar Avatar da galería";
$lang['Link_remote_Avatar'] = "Vincular a un Avatar fóra deste sitio";
$lang['Link_remote_Avatar_explain'] = "Escriba o URL onde se atopa o arquivo de imaxe do seu Avatar.";
$lang['Avatar_URL'] = "URL da imaxe de Avatar";
$lang['Select_from_gallery'] = "Seleccionar Avatar da nosa galería";
$lang['View_avatar_gallery'] = "Amosar Galería";

$lang['Select_avatar'] = "Seleccionar avatar";
$lang['Return_profile'] = "Cancelar avatar";
$lang['Select_category'] = "Seleccionar categoría";

$lang['Delete_Image'] = "Borrar Imaxe";
$lang['Current_Image'] = "Imaxe Actual";

$lang['Notify_on_privmsg'] = "Notificarme as novas Mensaxes Privadas";
$lang['Popup_on_privmsg'] = "Desplegar nova fiestra cando hai Mensaxes Privadas";
$lang['Popup_on_privmsg_explain'] = "Algunhas plantillas poden abrir unha nova fiestra para informarlle cando recibiu novas mensaxes privadas.";
$lang['Hide_user'] = "Ocultar o seu status online";

$lang['Profile_updated'] = "O seu perfil foi actualizado";
$lang['Profile_updated_inactive'] = "O seu perfil foi actualizado. Sen embargo, cambiou detalles importantes e a súa conta foi desactivada. Revise o seu email para averiguar como reactiva-la súa conta, ou se é necesaria a activación do Administrador agarde a que este reactive a súa conta";

$lang['Password_mismatch'] = "Os contrasinais que ingresou non coinciden.";
$lang['Current_password_mismatch'] = "O contrasinal que ingresou non coincide co almacenado na base de datos.";
$lang['Password_long'] = "O seu contrasinal non debe ter máis de 32 caracteres.";
$lang['Username_taken'] = "Lamentámolo pero este nome de usuario xa está en uso.";
$lang['Username_invalid'] = "O nome de usuario contén un caracter inválido como \".";
$lang['Username_disallowed'] = "Desculpe, este nome de usuario está restrinxido.";
$lang['Email_taken'] = "Lamentámolo pero este enderezo de correo electrónico xa foi rexistrado por un usuario.";
$lang['Email_banned'] = "Desculpe, este enderezo de correo electrónico foi baneado.";
$lang['Email_invalid'] = "O enderezo de correo electrónico ingresado é inválido.";
$lang['Signature_too_long'] = "A sinatura é moi longa.";
$lang['Fields_empty'] = "Debe completa-los campos obrigatorios.";
$lang['Avatar_filetype'] = "O tipo de imaxe do avatar debe ser .jpg, .gif ou .png";
$lang['Avatar_filesize'] = "O tamaño de arquivo do avatar debe ser menor de %d kB"; // O tamaño de arquivo do avatar debe ser menor de 6 kB
$lang['Avatar_imagesize'] = "O avatar debe tener menos de %d pixels de ancho por %d pixels de alto"; 

$lang['Welcome_subject'] = "Benvid@ ós Foros de %s"; // Benvid@ ós Foros de Nome de Sitio
$lang['New_account_subject'] = "Nova conta de usuario";
$lang['Account_activated_subject'] = "Conta Activada";

$lang['Account_added'] = "Gracias por rexistrarse, a súa conta foi creada. Agora pode entrar co seu nome de usuario e contrasinal";
$lang['Account_inactive'] = "A súa conta foi creada. Sen embargo, este foro require activación da conta. Enviouse unha clave de activación ó seu email. Por favor revise o seu email para máis información";
$lang['Account_inactive_admin'] = "A súa conta foi creada. Sen embargo, este foro require activación do Administrador. Un email foi enviado ó Administrador e Vostede será informado cando a súa conta sexa activada";
$lang['Account_active'] = "A súa conta foi creada. Gracias por rexistrarse";
$lang['Already_activated'] = 'Vostede xa activou a súa conta';
$lang['Account_active_admin'] = "A conta foi activada";
$lang['Reactivate'] = "¡Reactive a súa conta!";
$lang['COPPA'] = "A súa conta foi creada pero debe ser aprobada, por favor revise o seu email para máis información.";

$lang['Registration'] = "Condicións de Rexistro";
$lang['Reg_agreement'] = "Ainda cando os administradores ou moderadores destes foros farán todo o posible por eliminar calquera material cuestionable tan pronto como sexa posible, é imposible revisar tódalas mensaxes. Polo tanto Vostede acepta que tódalas mensaxes publicadas nestes foros expresan as opinións dos seus autores e non a dos administradores, moderadores ou o webmaster (agás en mensaxes publicadas por eles mesmos) polo cal non se lles considerará responsables.<br /><br />Vostede está de acordo en non publicar material abusivo, obsceno, vulgar, de odio, ameazante, orientado sexualmente, ou ningún outro que dalgunha forma viole leis vixentes. Se publicase material desa índole a súa conta de acceso ó foro será cancelada e o seu proveedor de Acceso a Internet será informado. A dirección IP de tódalas mensaxes é gardada para axudar a cumplir estas normas. Vostede está de acordo en que o webmaster, administrador e moderadores deste Foro teñen o dereito de borrar, editar, mover ou pechar cualquera tema en cualquera intre se o consideran adecuado. Como usuario Vostede acepta que toda a información que ingrese sexa almacenada nunha base de datos. Ainda cando esta información non será proporcionada a terceiros sen o seu consentimento, o webmaster, administrador e moderadores non poden responsabilizarse por intentos de hackers que podan levar a que esta información se vexa comprometida.<br /><br />Este sistema de foros utiliza cookies para almacear información na súa computadora local. Estes cookies non conteñen a información que Vostede ingresou, só se utilizan para mellorar a visualización dos foros. O email só é usado para confirmar os seus detalles de rexistro e contrasinal (e para enviar novas contrasinais se esquece a actual).<br /><br />Ó rexistrarse Vostede aceptará todas estas condicións.";

$lang['Agree_under_13'] = "Estou de acordo con estas condicións e son <b>menor</b> de 13 anos de idade";
$lang['Agree_over_13'] = "Estou de acordo con estas condicións e son <b>maior de</b> ou teño <b>exactamente</b> 13 anos de idade";
$lang['Agree_not'] = "Non estou de acordo con estas condicións";

$lang['Wrong_activation'] = "A clave de activación subministrada non coincide con ningunha na base de datos.";
$lang['Send_password'] = "Enviarme unha nova contrasinal";
$lang['Password_updated'] = "Creouse unha nova contrasinal, por favor revise o seu email para detalles sobre como activala.";
$lang['No_email_match'] = "O email subministrado non coincide co dese nome de usuario.";
$lang['New_password_activation'] = "Activación de novo contrasinal";
$lang['Password_activated'] = "A súa conta foi re-activada. Para entrar use o contrasinal provisto no email que recibiu.";

$lang['Send_email_msg'] = "Enviar un email";
$lang['No_user_specified'] = "Non se especificou usuario";
$lang['User_prevent_email'] = "Este usuario non desexa recibir email. Tente enviarlle unha mensaxe privada";
$lang['User_not_exist'] = "Ese usuario non existe";
$lang['CC_email'] = "Enviar unha copia desta mensaxe a Vostede";
$lang['Email_message_desc'] = "Esta mensaxe será enviada como texto simple, non inclúa HTML nin BBCode. A dirección de resposta para esta mensaxa será o seu email.";
$lang['Flood_email_limit'] = "Non pode enviar outro email neste momento, ténteo máis tarde";
$lang['Recipient'] = "Destinatario";
$lang['Email_sent'] = "O email foi enviado";
$lang['Send_email'] = "Enviar email";
$lang['Empty_subject_email'] = "Debe especificar un asunto para o email";
$lang['Empty_message_email'] = "Debe ingresar unha mensaxe para ser enviada";


//
// Memberslist
//
$lang['Select_sort_method'] = "Ordenar por";
$lang['Sort'] = "Ordenar";
$lang['Sort_Top_Ten'] = "Os 10 autores que máis escriben";
$lang['Sort_Joined'] = "Data de Rexistro";
$lang['Sort_Username'] = "Nome de usuario";
$lang['Sort_Location'] = "Ubicación";
$lang['Sort_Posts'] = "Cantidad de mensaxes";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Sitio Web";
$lang['Sort_Ascending'] = "Ascendente";
$lang['Sort_Descending'] = "Descendente";
$lang['Order'] = "Orde";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Panel de Control de Grupo";
$lang['Group_member_details'] = "Detalles de Membros do Grupo";
$lang['Group_member_join'] = "Unirse a Grupo";

$lang['Group_Information'] = "Información do Grupo";
$lang['Group_name'] = "Nome do Grupo";
$lang['Group_description'] = "Descrición do Grupo";
$lang['Group_membership'] = "Afiliación ó Grupo";
$lang['Group_Members'] = "Membros do Grupo";
$lang['Group_Moderator'] = "Moderador do Grupo";
$lang['Pending_members'] = "Membros Pendentes";

$lang['Group_type'] = "Tipo de Grupo";
$lang['Group_open'] = "Grupo Aberto";
$lang['Group_closed'] = "Grupo Pechado";
$lang['Group_hidden'] = "Grupo Oculto";

$lang['Current_memberships'] = "Afiliacións actuais";
$lang['Non_member_groups'] = "Grupos onde non é membro";
$lang['Memberships_pending'] = "Afiliacións pendentes";

$lang['No_groups_exist'] = "Non existen Grupos";
$lang['Group_not_exist'] = "Ese grupo non existe";

$lang['Join_group'] = "Unirse a Grupo";
$lang['No_group_members'] = "Este grupo non ten membros";
$lang['Group_hidden_members'] = "Este grupo está oculto; non pode ve-la súa afiliación";
$lang['No_pending_group_members'] = "Este grupo non ten membros pendentes";
$lang["Group_joined"] = "Subscrición a grupo exitosa <br />Vostede será notificado cando a súa subscrición sexa aprobada polo moderador do grupo.";
$lang['Group_request'] = "Fíxose unha petición para unirse ó grupo.";
$lang['Group_approved'] = "A súa petición foi aprobada.";
$lang['Group_added'] = "Vostede foi engadido a este grupo.";
$lang['Already_member_group'] = "Vostede xa é membro deste grupo.";
$lang['User_is_member_group'] = "O usuario xa é membro deste grupo.";
$lang['Group_type_updated'] = "Tipo de grupo actualizado con éxito.";

$lang['Could_not_add_user'] = "O usuario que seleccionou non existe.";
$lang['Could_not_anon_user'] = "Non pode facer a Anónimo membro deste grupo.";

$lang['Confirm_unsub'] = "¿Está seguro que quere cancelar a subscrición a este grupo?";
$lang['Confirm_unsub_pending'] = "A súa subscrición a este grupo aínda non foi aprobada, ¿Esta seguro que desexa cancelar a subscrición?";

$lang['Unsub_success'] = "A súa subscrición a este grupo foi cancelada.";

$lang['Approve_selected'] = "Aprobar seleccionados";
$lang['Deny_selected'] = "Denegar seleccionados";
$lang['Not_logged_in'] = "Debe entrar ó Foro para unirse a un Grupo.";
$lang['Remove_selected'] = "Borrar Seleccionados";
$lang['Add_member'] = "Engadir Membro";
$lang['Not_group_moderator'] = "Vostede non é moderador deste grupo polo que non pode realizar esta acción.";

$lang['Login_to_join'] = "Entre para unirse a un grupo ou administrar as afiliacións dun grupo.";
$lang['This_open_group'] = "Este é un grupo aberto: click para solicitar afiliación.";
$lang['This_closed_group'] = "Este é un grupo pechado: non se aceptan máis usuarios.";
$lang['This_hidden_group'] = "Este é un grupo oculto: non se permite a adición automática de usuarios.";
$lang['Member_this_group'] = "Vostede é membro deste grupo";
$lang['Pending_this_group'] = "A súa afiliación a este grupo está pendente";
$lang['Are_group_moderator'] = "Vostede é o moderador do grupo";
$lang['None'] = "Ningún";

$lang['Subscribe'] = "Subscribirse";
$lang['Unsubscribe'] = "Cancelar Subscrición";
$lang['View_Information'] = "Ver Información";


//
// Search
//
$lang['Search_query'] = "Consulta de Búsqueda";
$lang['Search_options'] = "Opcións de Búsqueda";

$lang['Search_keywords'] = "Buscar por palabras clave";
$lang['Search_keywords_explain'] = "Pode usar <u>AND</u> para definir palabras que deben estar nos resultados, <u>OR</u> para definir palabras que poden estar nos resultados e <u>NOT</u> para definir palabras que non deben estar nos resultados. Use * como un comodín para as buscas parciais";
$lang['Search_author'] = "Buscar por Autor";
$lang['Search_author_explain'] = "Use * como un comodín para buscas parciais";

$lang['Search_for_any'] = "Buscar calquera das palabras ou usar consulta tal como se escribiu";
$lang['Search_for_all'] = "Buscar tódalas palabras";
$lang['Search_title_msg'] = "Buscar en títulos e texto das mensaxes";
$lang['Search_msg_only'] = "Buscar só no texto das mensaxes";

$lang['Return_first'] = "Mostrar os primeiros"; // seguido por xxx caracteres en cadro de texto
$lang['characters_posts'] = "caracteres das mensaxes";

$lang['Search_previous'] = "Buscar nos anteriores"; // seguido por dias, semanas, meses, anos, nunha lista despregable

$lang['Sort_by'] = "Ordenar por";
$lang['Sort_Time'] = "Data de Publicación";
$lang['Sort_Post_Subject'] = "Asunto da Mensaxe";
$lang['Sort_Topic_Title'] = "Título do Tema";
$lang['Sort_Author'] = "Autor";
$lang['Sort_Forum'] = "Foro";

$lang['Display_results'] = "Mostrar resultados como";
$lang['All_available'] = "Tódolos dispoñibles";
$lang['No_searchable_forums'] = "Non ten permiso para buscar nos foros deste sitio web.";

$lang['No_search_match'] = "Non hai temas ou mensaxes que coincidan cos seus criterios de búsqueda";
$lang['Found_search_match'] = "Atopouse %d coincidencia"; // eg. Atopouse 1 coincidencia
$lang['Found_search_matches'] = "Atopáronse %d coincidencias"; // eg. Atopáronse 24 coincidencias

$lang['Close_window'] = "Pechar fiestra";


//
// Entradas relacionadas con autorizacións
//
// Os %s serán reemprazados cun dos seguientes arrays
$lang['Sorry_auth_announce'] = "Sentímolo pero só %s poden publicar anuncios neste foro.";
$lang['Sorry_auth_sticky'] = "Sentímolo pero só %s poden publicar PostIt neste foro.";
$lang['Sorry_auth_read'] = "Sentímolo pero só %s poden ler temas neste foro.";
$lang['Sorry_auth_post'] = "Sentímolo pero só %s poden publicar temas neste foro.";
$lang['Sorry_auth_reply'] = "Sentímolo pero só %s poden responder a mensaxes neste foro.";
$lang['Sorry_auth_edit'] = "Sentímolo pero só %s poden editar mensaxes neste foro.";
$lang['Sorry_auth_delete'] = "Sentímolo pero só %s poden borrar mensaxes neste foro.";
$lang['Sorry_auth_vote'] = "Sentímolo pero só %s poden votar en enquisas neste foro.";

// Estes remprazan os %s nas cadnas de arriba
$lang['Auth_Anonymous_Users'] = "<b>usuarios anónimos</b>";
$lang['Auth_Registered_Users'] = "<b>usuarios rexistrados</b>";
$lang['Auth_Users_granted_access'] = "<b>usuarios con acceso especial</b>";
$lang['Auth_Moderators'] = "<b>moderadores</b>";
$lang['Auth_Administrators'] = "<b>administradores</b>";

$lang['Not_Moderator'] = "Vostede non é moderador neste foro.";
$lang['Not_Authorised'] = "Non Autorizado";

$lang['You_been_banned'] = "Foille restrixido o acceso a este foro.<br />Por favor contacte ó webmaster ou ó administrador do foro para máis información.";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Hai 0 usuarios rexistrados e ";
$lang['Reg_users_online'] = "Hai %d usuarios rexistrados e "; // Hai 5 usuarios rexistrados e
$lang['Reg_user_online'] = "Hai %d usuario rexistrado e "; // Hai 1 usuario rexistrado e
$lang['Hidden_users_zero_online'] = "0 usuarios ocultos online"; // 0 usuarios ocultos online
$lang['Hidden_users_online'] = "%d usuarios ocultos online"; // 6 usuarios ocultos online
$lang['Hidden_user_online'] = "%d usuario oculto online"; // 1 usuario oculto online
$lang['Guest_users_online'] = "Hai %d usuarios convidados online"; // Hai 10 usuarios convidados online
$lang['Guest_users_zero_online'] = "Hai 0 convidados online"; // Hai 0 usuarios convidados online
$lang['Guest_user_online'] = "Hai %d usuario convidado online"; // Hay 1 usuario convidado online
$lang['No_users_browsing'] = "Non hai usuarios explorando este foro";

$lang['Online_explain'] = "Estes datos estan baseados en usuarios activos durante os últimos 5 minutos";

$lang['Forum_Location'] = "Ubicación do Foro";
$lang['Last_updated'] = "Ultima Actualización";

$lang['Forum_index'] = "Índice do Foro";
$lang['Logging_on'] = "Entrando";
$lang['Posting_message'] = "Publicando mensaxe";
$lang['Searching_forums'] = "Buscando foros";
$lang['Viewing_profile'] = "Vendo Perfil";
$lang['Viewing_online'] = "Vendo quen está online";
$lang['Viewing_member_list'] = "Vendo lista de membros";
$lang['Viewing_priv_msgs'] = "Vendo mensaxes privadas";
$lang['Viewing_FAQ'] = "Vendo FAQ";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Panel de Control do Moderador";
$lang['Mod_CP_explain'] = "Usando o seguinte formulario pode realizar operacións de moderación neste foro. Pode pechar, desbloquear, mover ou borrar calquera número de temas.";

$lang['Select'] = "Seleccionar";
$lang['Delete'] = "Borrar";
$lang['Move'] = "Mover";
$lang['Lock'] = "Pechar";
$lang['Unlock'] = "Desbloquear";

$lang['Topics_Removed'] = "Os temas seleccionados foron quitados con éxito da base de datos.";
$lang['Topics_Locked'] = "Os temas seleccionados foron pechados.";
$lang['Topics_Moved'] = "Os temas seleccionados foron movidos.";
$lang['Topics_Unlocked'] = "Os temas seleccionados foron desbloqueados.";
$lang['No_Topics_Moved'] = "Non se moveron temas.";

$lang['Confirm_delete_topic'] = "¿Está seguro que quere elimina-lo/s tema/s seleccionado/s?";
$lang['Confirm_lock_topic'] = "¿Está seguro que quere pecha-lo/s tema/s seleccionado/s?";
$lang['Confirm_unlock_topic'] = "¿Está seguro que quere desbloquea-lo/s tema/s seleccionado/s?";
$lang['Confirm_move_topic'] = "¿Está seguro que quere move-lo/s tema/s seleccionado/s?";

$lang['Move_to_forum'] = "Mover ó foro";
$lang['Leave_shadow_topic'] = "Deixar tema sombreado en antigo foro.";

$lang['Split_Topic'] = "Panel de Control para División de Temas";
$lang['Split_Topic_explain'] = "Usando o seguinte formulario pode dividir un tema en dous, xa sexa seleccionando as mensaxes individualmente ou dividíndoo nunha mensaxe determinada";
$lang['Split_title'] = "Título do novo tema";
$lang['Split_forum'] = "Foro para novo tema";
$lang['Split_posts'] = "Dividir mensaxes seleccionadas";
$lang['Split_after'] = "Dividir desde a mensaxe seleccionada";
$lang['Topic_split'] = "O tema seleccionado foi dividido con éxito";

$lang['Too_many_error'] = "Seleccionou moitas mensaxes. Só pode escoller unha mensaxe a partir da cal dividir un tema ";

$lang['None_selected'] = "Non seleccionou temas para esta operación. Por favor regrese e seleccione cando menos un.";
$lang['New_forum'] = "Novo Foro";

$lang['This_posts_IP'] = "Enderezo IP para esta mensaxe";
$lang['Other_IP_this_user'] = "Outros enderezeos IP desde os que este usuario publicou mensaxes";
$lang['Users_this_IP'] = "Usuarios publicando deste enderezo IP";
$lang['IP_info'] = "Información IP";
$lang['Lookup_IP'] = "Buscar por enderezo IP";


//
// Zonas horarias ... para amosar en cada páxina
//
$lang['All_times'] = "Tódalas horas son %s"; // ej. Tódalas horas son GMT - 12 Horas

// Estes amósanse na lista despregable de zona horaria
$lang['-12'] = 'GMT - 12 Horas'; 
$lang['-11'] = 'GMT - 11 Horas'; 
$lang['-10'] = 'GMT - 10 Horas'; 
$lang['-9'] = 'GMT - 9 Horas'; 
$lang['-8'] = 'GMT - 8 Horas'; 
$lang['-7'] = 'GMT - 7 Horas'; 
$lang['-6'] = 'GMT - 6 Horas'; 
$lang['-5'] = 'GMT - 5 Horas'; 
$lang['-4'] = 'GMT - 4 Horas'; 
$lang['-3.5'] = 'GMT - 3.5 Horas'; 
$lang['-3'] = 'GMT - 3 Horas'; 
$lang['-2'] = 'GMT - 2 Horas'; 
$lang['-1'] = 'GMT - 1 Horas'; 
$lang['0'] = 'GMT'; 
$lang['1'] = 'GMT + 1 Hora'; 
$lang['2'] = 'GMT + 2 Horas'; 
$lang['3'] = 'GMT + 3 Horas'; 
$lang['3.5'] = 'GMT + 3.5 Horas'; 
$lang['4'] = 'GMT + 4 Horas'; 
$lang['4.5'] = 'GMT + 4.5 Horas'; 
$lang['5'] = 'GMT + 5 Horas'; 
$lang['5.5'] = 'GMT + 5.5 Horas'; 
$lang['6'] = 'GMT + 6 Horas'; 
$lang['6.5'] = 'GMT + 6.5 Horas'; 
$lang['7'] = 'GMT + 7 Horas'; 
$lang['8'] = 'GMT + 8 Horas'; 
$lang['9'] = 'GMT + 9 Horas'; 
$lang['9.5'] = 'GMT + 9.5 Horas'; 
$lang['10'] = 'GMT + 10 Horas'; 
$lang['11'] = 'GMT + 11 Horas'; 
$lang['12'] = 'GMT + 12 Horas'; 

// Estes amósanse na lista despregable de zona horaria
$lang['tz']['-12'] = 'GMT - 12 Horas'; 
$lang['tz']['-11'] = 'GMT - 11 Horas'; 
$lang['tz']['-10'] = 'GMT - 10 Horas'; 
$lang['tz']['-9'] = 'GMT - 9 Horas'; 
$lang['tz']['-8'] = 'GMT - 8 Horas'; 
$lang['tz']['-7'] = 'GMT - 7 Horas'; 
$lang['tz']['-6'] = 'GMT - 6 Horas'; 
$lang['tz']['-5'] = 'GMT - 5 Horas'; 
$lang['tz']['-4'] = 'GMT - 4 Horas'; 
$lang['tz']['-3.5'] = 'GMT - 3.5 Horas'; 
$lang['tz']['-3'] = 'GMT - 3 Horas'; 
$lang['tz']['-2'] = 'GMT - 2 Horas'; 
$lang['tz']['-1'] = 'GMT - 1 Horas'; 
$lang['tz']['0'] = 'GMT'; 
$lang['tz']['1'] = 'GMT + 1 Hora'; 
$lang['tz']['2'] = 'GMT + 2 Horas'; 
$lang['tz']['3'] = 'GMT + 3 Horas'; 
$lang['tz']['3.5'] = 'GMT + 3.5 Horas'; 
$lang['tz']['4'] = 'GMT + 4 Horas'; 
$lang['tz']['4.5'] = 'GMT + 4.5 Horas'; 
$lang['tz']['5'] = 'GMT + 5 Horas'; 
$lang['tz']['5.5'] = 'GMT + 5.5 Horas'; 
$lang['tz']['6'] = 'GMT + 6 Horas'; 
$lang['tz']['6.5'] = 'GMT + 6.5 Horas'; 
$lang['tz']['7'] = 'GMT + 7 Horas'; 
$lang['tz']['8'] = 'GMT + 8 Horas'; 
$lang['tz']['9'] = 'GMT + 9 Horas'; 
$lang['tz']['9.5'] = 'GMT + 9.5 Horas'; 
$lang['tz']['10'] = 'GMT + 10 Horas'; 
$lang['tz']['11'] = 'GMT + 11 Horas'; 
$lang['tz']['12'] = 'GMT + 12 Horas';
+$lang['tz']['13'] = 'GMT + 13 Hours';

$lang['datetime']['Sunday'] = "Domingo";
$lang['datetime']['Monday'] = "Luns";
$lang['datetime']['Tuesday'] = "Martes";
$lang['datetime']['Wednesday'] = "Mércores";
$lang['datetime']['Thursday'] = "Xoves";
$lang['datetime']['Friday'] = "Venres";
$lang['datetime']['Saturday'] = "Sábado";
$lang['datetime']['Sun'] = "Dom";
$lang['datetime']['Mon'] = "Lun";
$lang['datetime']['Tue'] = "Mar";
$lang['datetime']['Wed'] = "Mer";
$lang['datetime']['Thu'] = "Xov";
$lang['datetime']['Fri'] = "Ven";
$lang['datetime']['Sat'] = "Sab";
$lang['datetime']['January'] = "Xaneiro";
$lang['datetime']['February'] = "Febreiro";
$lang['datetime']['March'] = "Marzo";
$lang['datetime']['April'] = "Abril";
$lang['datetime']['May'] = "Maio";
$lang['datetime']['June'] = "Xuño";
$lang['datetime']['July'] = "Xullo";
$lang['datetime']['August'] = "Agosto";
$lang['datetime']['September'] = "Setembro";
$lang['datetime']['October'] = "Outubro";
$lang['datetime']['November'] = "Novembro";
$lang['datetime']['December'] = "Decembro";
$lang['datetime']['Jan'] = "Xan";
$lang['datetime']['Feb'] = "Feb";
$lang['datetime']['Mar'] = "Mar";
$lang['datetime']['Apr'] = "Abr";
$lang['datetime']['May'] = "Mai";
$lang['datetime']['Jun'] = "Xuñ";
$lang['datetime']['Jul'] = "Xul";
$lang['datetime']['Aug'] = "Ago";
$lang['datetime']['Sep'] = "Set";
$lang['datetime']['Oct'] = "Out";
$lang['datetime']['Nov'] = "Nov";
$lang['datetime']['Dec'] = "Dec";

//
// Erros (non relacionados cunha falla específica
// nunha páxina)
//
$lang['Information'] = "Información";
$lang['Critical_Information'] = "Información Crítica";

$lang['General_Error'] = "Erro Xeral";
$lang['Critical_Error'] = "Erro Crítico";
$lang['An_error_occured'] = "Ocorreu un Erro";
$lang['A_critical_error'] = "Ocorreu un Erro Crítico";

//
// Xa vale por hoxe ¿non?
// -------------------------------------------------

?>