<?php
/***************************************************************************
 *                           lang_main.php [Portuguese]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id$ 
 *
 ****************************************************************************/

 /****************************************************************************
 * Translation by:
 * LuizCB (Pincel) LuizCB@pincel.net || http://pincel.net
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
$lang['DIRECTION'] = "LTR";
$lang['LEFT'] = "LEFT";
$lang['RIGHT'] = "RIGHT";
$lang['DATE_FORMAT'] =  "d M Y"; // This should be changed to the default date format for your language, php date() format

//
// Common, these terms are used
// extensively on several pages
//
$lang['Forum'] = "Fórum";
$lang['Category'] = "Categoria";
$lang['Topic'] = "Tópico";
$lang['Topics'] = "Tópicos";
$lang['Replies'] = "Respostas";
$lang['Views'] = "Vistos";
$lang['Post'] = "Mensagem";
$lang['Posts'] = "Mensagens";
$lang['Posted'] = "Colocada";
$lang['Username'] = "Utilizador";
$lang['Password'] = "Senha";
$lang['Email'] = "Email";
$lang['Poster'] = "Autor";
$lang['Author'] = "Autor";
$lang['Time'] = "Data";
$lang['Hours'] = "Horas";
$lang['Message'] = "Mensagem";

$lang['1_Day'] = "1 Dia";
$lang['7_Days'] = "7 Dias";
$lang['2_Weeks'] = "2 Semanas";
$lang['1_Month'] = "1 Mês";
$lang['3_Months'] = "3 Meses";
$lang['6_Months'] = "6 Meses";
$lang['1_Year'] = "1 Ano";

$lang['Go'] = "Premir";
$lang['Jump_to'] = "Ir para";
$lang['Submit'] = "Submeter";
$lang['Reset'] = "Restaurar";
$lang['Cancel'] = "Cancelar";
$lang['Preview'] = "Prever";
$lang['Confirm'] = "Confirmar";
$lang['Spellcheck'] = "Corrigir";
$lang['Yes'] = "Sim";
$lang['No'] = "Não";
$lang['Enabled'] = "Activo";
$lang['Disabled'] = "Inactivo";
$lang['Error'] = "Erro";

$lang['Next'] = "Seguinte";
$lang['Previous'] = "Anterior";
$lang['Goto_page'] = "Ir á página";
$lang['Joined'] = "Registo";
$lang['IP_Address'] = "Endereço de IP";

$lang['Select_forum'] = "Seleccione um Fórum";
$lang['View_latest_post'] = "Ver a última mensagem";
$lang['View_newest_post'] = "Ver a mensagem mais recente";
$lang['Page_of'] = "Página <b>%d</b> de <b>%d</b>"; // Replaces with: Page 1 of 2 for example

$lang['ICQ'] = "Número de ICQ";
$lang['AIM'] = "Endereço de AIM";
$lang['MSNM'] = "MSN Messenger";
$lang['YIM'] = "Yahoo Messenger";

$lang['Forum_Index'] = "Índice do Fórum %s";  // eg. sitename Forum Index, %s can be removed if you prefer

$lang['Post_new_topic'] = "Novo Tópico";
$lang['Reply_to_topic'] = "Responder a Mensagem";
$lang['Reply_with_quote'] = "Responder com Citação";

$lang['Click_return_topic'] = "Premir %sAqui%s para voltar ao Tópico"; // %s's here are for uris, do not remove!
$lang['Click_return_login'] = "Premir %sAqui%s para tentar novamente";
$lang['Click_return_forum'] = "Premir %sAqui%s para voltar ao Fórum";
$lang['Click_view_message'] = "Premir %sAqui%s para ver a sua mensagem";
$lang['Click_return_modcp'] = "Premir %sAqui%s para voltar ao Painel de Controle de Moderador";
$lang['Click_return_group'] = "Premir %sAqui%s para voltar á informação do grupo";

$lang['Admin_panel'] = "Ir ao Painel de Administração";

$lang['Board_disable'] = "Este painel não se encontra disponível de momento. Tente novamente mais tarde";


//
// Global Header strings
//
$lang['Registered_users'] = "Utilizadores ligados";
$lang['Online_users_zero_total'] = "Não há <b>0</b> utilizadores ligados :: ";
$lang['Online_users_total'] = "Há <b>%d</b> utilizadores ligados :: ";
$lang['Online_user_total'] = "Há <b>%d</b> utilizador ligado :: ";
$lang['Reg_users_zero_total'] = "Nenhum Registado, ";
$lang['Reg_users_total'] = "%d Registados, ";
$lang['Reg_user_total'] = "%d Registado, ";
$lang['Hidden_users_zero_total'] = "Nenhum Invisível e ";
$lang['Hidden_user_total'] = "%d Invisível e ";
$lang['Hidden_users_total'] = "%d Invisíveis e ";
$lang['Guest_users_zero_total'] = "Nenhum Visitante";
$lang['Guest_users_total'] = "%d Visitantes";
$lang['Guest_user_total'] = "%d Visitante";

$lang['Admin_online_color'] = "%sAdministrador%s";
$lang['Mod_online_color'] = "%sModerador%s";      

$lang['You_last_visit'] = "A sua última visita foi a %s"; // %s replaced by date/time
$lang['Current_time'] = "Data: %s"; // %s replaced by time

$lang['Search_new'] = "Ler mensagens desde a última visita";
$lang['Search_your_posts'] = "Verificar as suas mensagens";
$lang['Search_unanswered'] = "Ler mensagens sem resposta";

$lang['Register'] = "Registar";
$lang['Profile'] = "Perfil";
$lang['Edit_profile'] = "Editar o seu Perfil";
$lang['Search'] = "Pesquisar";
$lang['Memberlist'] = "Membros";
$lang['FAQ'] = "FAQ";
$lang['BBCode_guide'] = "Guia do BBcode";
$lang['Usergroups'] = "Grupos";
$lang['Last_Post'] = "Última Mensagem";
$lang['Moderator'] = "Moderador";
$lang['Moderators'] = "Moderadores";


//
// Stats block text
//
$lang['Posted_articles_zero_total'] = "Há presentemente um total de <b>0</b> mensagens"; // Number of posts
$lang['Posted_articles_total'] = "Os nossos utilizadores colocaram um total de <b>%d</b> mensagens"; // Number of posts
$lang['Posted_article_total'] = "Os nossos utilizadores colocaram um total de <b>%d</b> mensagens"; // Number of posts
$lang['Registered_users_zero_total'] = "Temos <b>0</b> utilizadores registados"; // # registered users
$lang['Registered_users_total'] = "Temos <b>%d</b> utilizadores registados"; // # registered users
$lang['Registered_user_total'] = "Temos <b>%d</b> utilizadores registados"; // # registered users
$lang['Newest_user'] = "O registo mais recente é de <b>%s%s%s</b>"; // a href, username, /a 

$lang['No_new_posts_last_visit'] = "Não á novas mensagens desde a sua última visita";
$lang['No_new_posts'] = "Não há mensagens novas";
$lang['New_posts'] = "Mensagens novas";
$lang['New_post'] = "Mensagem nova";
$lang['No_new_posts_hot'] = "Não há mensagens novas [ Popular ]";
$lang['New_posts_hot'] = "Mensagens novas [ Popular ]";
$lang['No_new_posts_locked'] = "Não há mensagens novas [ Bloqueadas ]";
$lang['New_posts_locked'] = "New posts [ Bloqueadas ]";
$lang['Forum_is_locked'] = "Fórum Bloqueado";


//
// Login
//
$lang['Enter_password'] = "Por favor escrever o seu nome de utilizador e senha para entrar";
$lang['Login'] = "Entrar";
$lang['Logout'] = "Sair";

$lang['Forgotten_password'] = "Esqueci-me da senha";

$lang['Log_me_in'] = "Ligar-me automaticamente em cada visita";

$lang['Error_login'] = "Especificou um nome de utilizador incorrecto ou inactivo ou uma senha inválida";


//
// Index page
//
$lang['Index'] = "Índice";
$lang['No_Posts'] = "Não há mensagens";
$lang['No_forums'] = "Este painel não possui foruns";

$lang['Private_Message'] = "Mensagem Privada";
$lang['Private_Messages'] = "Mensagens Privadas";
$lang['Who_is_Online'] = "Quem está ligado";

$lang['Mark_all_forums'] = "Assinalar todos os fóruns como lidos";
$lang['Forums_marked_read'] = "Todos os fóruns foram seleccionados como lidos";


//
// Viewforum
//
$lang['View_forum'] = "Ver o Fórum";

$lang['Forum_not_exist'] = "O fórum seleccionado não existe";
$lang['Reached_on_error'] = "Alcançou esta página por erro";

$lang['Display_topics'] = "Mostrar tópicos anteriores";
$lang['All_Topics'] = "Totos os tópicos";

$lang['Topic_Announcement'] = "<b>Anúncio:</b>";
$lang['Topic_Sticky'] = "<b>Inamovível:</b>";
$lang['Topic_Moved'] = "<b>Mudado:</b>";
$lang['Topic_Poll'] = "<b>[ Votação ]</b>";

$lang['Mark_all_topics'] = "Seleccionar todos os tópicos como lidos";
$lang['Topics_marked_read'] = "Todos os tópicos neste fórum estão agora seleccionados como lidos";

$lang['Rules_post_can'] = "Neste fórum, você <b>Pode</b> colocar mensagens novas";
$lang['Rules_post_cannot'] = "Neste fórum, você <b>Não pode</b> colocar mensagens novas";
$lang['Rules_reply_can'] = "<b>Pode</b> responder a mensagens";
$lang['Rules_reply_cannot'] = "<b>Não pode</b> responder a mensagens";
$lang['Rules_edit_can'] = "<b>Pode</b> editar as suas mensagens";
$lang['Rules_edit_cannot'] = "<b>Não pode</b> editar as suas mensagens";
$lang['Rules_delete_can'] = "<b>Pode</b> remover as suas mensagens";
$lang['Rules_delete_cannot'] = "<b>Não pode</b> remover as suas mensagens";
$lang['Rules_vote_can'] = "Você <b>Pode</b> votar neste fórum";
$lang['Rules_vote_cannot'] = "Você <b>Não pode</b> votar neste fórum";
$lang['Rules_moderate'] = "Você <b>Pode ser</b> %smoderador neste fórum%s"; // %s replaced by a href links, do not remove! 

$lang['No_topics_post_one'] = "Não há mensagens neste fórum<br />Premir em <b>Novo Tópico</b> nesta página para registar uma";


//
// Viewtopic
//
$lang['View_topic'] = "Verificar tópico";

$lang['Guest'] = 'Visitante';
$lang['Post_subject'] = "Assunto";
$lang['View_next_topic'] = "Ver mensagem seguinte";
$lang['View_previous_topic'] = "Ver mensagem anterior";
$lang['Submit_vote'] = "Submeter voto";
$lang['View_results'] = "Ver resultados";

$lang['No_newer_topics'] = "Não há tópicos novos neste fórum";
$lang['No_older_topics'] = "Não há tópicos antigos neste fórum";
$lang['Topic_post_not_exist'] = "O tópico ou mensagem que pretende não existes";
$lang['No_posts_topic'] = "Não há mensagens para este tópico";

$lang['Display_posts'] = "Mostrar os tópicos anteriores";
$lang['All_Posts'] = "Todas as mensagens";
$lang['Newest_First'] = "Recentes primeiro";
$lang['Oldest_First'] = "Antigas primeiro";

$lang['Back_to_top'] = "Voltar acima";

$lang['Read_profile'] = "Ver o perfil de utilizadores"; 
$lang['Send_email'] = "Enviar email ao utilizador";
$lang['Visit_website'] = "Visitar a página na web do utilizador";
$lang['ICQ_status'] = "Estado do ICQ";
$lang['Edit_delete_post'] = "Editar/Remover esta mensagem";
$lang['View_IP'] = "Ver o IP do utilizador";
$lang['Delete_post'] = "Remover esta mensagem";

$lang['wrote'] = "escreveu"; // proceeds the username and is followed by the quoted text
$lang['Quote'] = "Citação"; // comes before bbcode quote output.
$lang['Code'] = "Código"; // comes before bbcode code output.

$lang['Edited_time_total'] = "Editado pela última vez por %s em %s, num total de %d vez"; // Last edited by me on 12 Oct 2001, edited 1 time in total
$lang['Edited_times_total'] = "Editado pela última vez por %s em %s, num total de %d vezes"; // Last edited by me on 12 Oct 2001, edited 2 times in total

$lang['Lock_topic'] = "Bloquear este tópico";
$lang['Unlock_topic'] = "Desbloquear este tópico";
$lang['Move_topic'] = "Mover este tópico";
$lang['Delete_topic'] = "Remover este tópico";
$lang['Split_topic'] = "Subdividir este tópico";

$lang['Stop_watching_topic'] = "Parar de observar este tópico";
$lang['Start_watching_topic'] = "Observar as respostas a este tópico";
$lang['No_longer_watching'] = "Não se encontra mais a observar este tópico";
$lang['You_are_watching'] = "Está agora a observar este tópico";


//
// Posting/Replying (Not private messaging!)
//
$lang['Message_body'] = "Corpo da messagem";
$lang['Topic_review'] = "Rever o tópico";

$lang['No_post_mode'] = "Não foi especificado a acção para esta mensagem"; // If posting.php is called without a mode (newtopic/reply/delete/etc, shouldn't be shown normaly)

$lang['Post_a_new_topic'] = "Novo Tópico";
$lang['Post_a_reply'] = "Responder";
$lang['Post_topic_as'] = "Colocar o tópico como";
$lang['Edit_Post'] = "Editar a mensagem";
$lang['Options'] = "Opções";

$lang['Post_Announcement'] = "Anúncio";
$lang['Post_Sticky'] = "Inamovível";
$lang['Post_Normal'] = "Normal";

$lang['Confirm_delete'] = "Tem a certeza que quer remover esta mensagem?";
$lang['Confirm_delete_poll'] = "Tem a certeza que quer remover esta votação?";

$lang['Flood_Error'] = "Não pode colocar nova mensagem tão rapidamente, por favor tentar novamente daqui a pouco";
$lang['Empty_subject'] = "Deve ser especificado um assunto quando se coloca uma mensagem";
$lang['Empty_message'] = "Deve ser escrita a mensagem";
$lang['Forum_locked'] = "Este fórum está Bloqueado. Não pode colocar, responder ou editar mensagens";
$lang['Topic_locked'] = "Este tópico está Bloqueado. Não pode editar mensagens ou responder";
$lang['No_post_id'] = "Deve ser seleccionado a mensagem a ser editada";
$lang['No_topic_id'] = "Deve ser seleccionado o tópico a responder";
$lang['No_valid_mode'] = "Apenas pode colocar, responder, editar ou citar mensagens, pr favor voltar e tentar novamente";
$lang['No_such_post'] = "Não existe essa mensagem, por favor voltar e tentar novamente";
$lang['Edit_own_posts'] = "Apenas pode editar as suas próprias mensagens";
$lang['Delete_own_posts'] = "Apenas pode remover as suas próprias mensagens";
$lang['Cannot_delete_replied'] = "Não pode remover mensagens que possuam respostas";
$lang['Cannot_delete_poll'] = "Não pode remover uma votação em curso";
$lang['Empty_poll_title'] = "Deve escrever o título ou questão para votação";
$lang['To_few_poll_options'] = "Deverá mencionar pelo menos duas opções de escolha para a votação";
$lang['To_many_poll_options'] = "Tentou seleccionar opções a mais na votação";
$lang['Post_has_no_poll'] = "Esta mensagem não possui votação";

$lang['Add_poll'] = "Adicionar Votação";
$lang['Add_poll_explain'] = "Se não pretende adicionar uma votação ao seu tópico deixe os espaços abaixo em branco";
$lang['Poll_question'] = "Questão ou título para votação";
$lang['Poll_option'] = "Opção de escolha";
$lang['Add_option'] = "Adicionar opção de escolha para a votação";
$lang['Update'] = "Actualizar";
$lang['Delete'] = "Remover";
$lang['Poll_for'] = "Activar a votação para";
$lang['Days'] = "Dias"; // This is used for the Run poll for ... Days + in admin_forums for pruning
$lang['Poll_for_explain'] = "[ Escrever 0 ou deixar a branco para uma votação sem tempo limite]";
$lang['Delete_poll'] = "Remover Votação";

$lang['Disable_HTML_post'] = "Desactivar HTML nesta mensagem";
$lang['Disable_BBCode_post'] = "Desactivar BBCode nesta mensagem";
$lang['Disable_Smilies_post'] = "Desactivar Smileys nesta mensagem";

$lang['HTML_is_ON'] = "HTML está <u>Activo</u>";
$lang['HTML_is_OFF'] = "HTML está <u>Inactivo</u>";
$lang['BBCode_is_ON'] = "%sBBCode%s está <u>Activo</u>"; // %s are replaced with URI pointing to FAQ
$lang['BBCode_is_OFF'] = "%sBBCode%s está <u>Inactivo</u>";
$lang['Smilies_are_ON'] = "Smileys estão <u>Activos</u>";
$lang['Smilies_are_OFF'] = "Smileys estão <u>Inactivos</u>";

$lang['Attach_signature'] = "Adicionar Assinatura (as assinaturas podem ser mudadas em Perfil)";
$lang['Notify'] = "Notificar-me quando for colocada uma resposta";
$lang['Delete_post'] = "Remover esta mensagem";

$lang['Stored'] = "A sua mensagem foi afixada com sucesso";
$lang['Deleted'] = "A sua mensagem foi removida com sucesso";
$lang['Poll_delete'] = "A sua votação foi removida com sucesso";
$lang['Vote_cast'] = "O seu voto foi registado";

$lang['Topic_reply_notification'] = "Notificação de Resposta a Tópico";

$lang['bbcode_b_help'] = "Texto sobrecarregado: [b]texto[/b]  (alt+b)";
$lang['bbcode_i_help'] = "Texto itálico: [i]texto[/i]  (alt+i)";
$lang['bbcode_u_help'] = "Texto sublinhado: [u]texto[/u]  (alt+u)";
$lang['bbcode_q_help'] = "Texto citado: [quote]texto[/quote]  (alt+q)";
$lang['bbcode_c_help'] = "Marca de código: [code]código[/code]  (alt+c)";
$lang['bbcode_l_help'] = "Lista: [list]texto[/list] (alt+l)";
$lang['bbcode_o_help'] = "Lista ordenada: [list=]texto[/list]  (alt+o)";
$lang['bbcode_p_help'] = "Inserir imagem: [img]http://url_da_imagem[/img]  (alt+p)";
$lang['bbcode_w_help'] = "Inserir URL: [url]http://url[/url] ou [url=http://url]URL texto[/url]  (alt+w)";
$lang['bbcode_a_help'] = "Fechar todas as marcas de bbCode";
$lang['bbcode_s_help'] = "Cor: [color=red]texto[/color]  Tip: pode também usar color=#FF0000";
$lang['bbcode_f_help'] = "Fonte: [size=x-small]texto pequeno[/size]";

$lang['Emoticons'] = "Emoções";
$lang['More_emoticons'] = "Ver mais icones de emoções";

$lang['Font_color'] = "Cor do texto";
$lang['color_default'] = "Definido";
$lang['color_dark_red'] = "Vermelho Escuro";
$lang['color_red'] = "Vermelho";
$lang['color_orange'] = "Laranja";
$lang['color_brown'] = "Castanho";
$lang['color_yellow'] = "Amarelo";
$lang['color_green'] = "Verde";
$lang['color_olive'] = "Azeitona";
$lang['color_cyan'] = "Ciano";
$lang['color_blue'] = "Azul";
$lang['color_dark_blue'] = "Azul escuro";
$lang['color_indigo'] = "Indigo";
$lang['color_violet'] = "Violeta";
$lang['color_white'] = "Branco";
$lang['color_black'] = "Preto";

$lang['Font_size'] = "Fonte";
$lang['font_tiny'] = "Minúscula";
$lang['font_small'] = "Pequena";
$lang['font_normal'] = "Normal";
$lang['font_large'] = "Grande";
$lang['font_huge'] = "Enorme";

$lang['Close_Tags'] = "Fechar marcas";
$lang['Styles_tip'] = "Idéia: Estilos podem ser aplicados rapidamente a texto seleccionado";


//
// Private Messaging
//
$lang['Private_Messaging'] = "Mensagem Privada";

$lang['Login_check_pm'] = "Ligar para ler as Mensagens Privadas";
$lang['New_pms'] = "Tem %d mensagens privadas novas"; // You have 2 new messages
$lang['New_pm'] = "Tem %d mensagem privada nova"; // You have 1 new message
$lang['No_new_pm'] = "Não possui mensagens privadas novas";
$lang['Unread_pms'] = "Possui %d mensagens privadas não lidas";
$lang['Unread_pm'] = "Possui %d mensagem privada não lida";
$lang['No_unread_pm'] = "Não possui mensagens privadas não lidas";
$lang['You_new_pm'] = "Possui uma mensagem privada nova na sua Caixa de Entrada";
$lang['You_new_pms'] = "Possui novas mensagens privadas na sua Caixa de Entrada";
$lang['You_no_new_pm'] = "Não há mensagens privadas novas";

$lang['Inbox'] = "Caixa de Entrada";
$lang['Outbox'] = "Caixa de Saída";
$lang['Savebox'] = "Caixa de Reserva";
$lang['Sentbox'] = "Caixa de Enviados";
$lang['Flag'] = "Bandeira";
$lang['Subject'] = "Assunto";
$lang['From'] = "De";
$lang['To'] = "Para";
$lang['Date'] = "Data";
$lang['Mark'] = "Marca";
$lang['Sent'] = "Enviado";
$lang['Saved'] = "Guardado";
$lang['Delete_marked'] = "Remover os assinalados";
$lang['Delete_all'] = "Remover Tudo";
$lang['Save_marked'] = "Guardar os assinalados"; 
$lang['Save_message'] = "Guardar a Mensagem";
$lang['Delete_message'] = "Remover a Mensagem";

$lang['Display_messages'] = "Período"; // Followed by number of days/weeks/months
$lang['All_Messages'] = "Todas";

$lang['No_messages_folder'] = "Não possui mensagens nesta pasta";

$lang['PM_disabled'] = "As Mensagens Privadas foram desactivadas neste painel";
$lang['Cannot_send_privmsg'] = "O administrador suspendeu-lhe o envio de mensagens privadas";
$lang['No_to_user'] = "Deve especificar um utilizador quando envia uma mensagem";
$lang['No_such_user'] = "Esse utilizador não existe";

$lang['Message_sent'] = "A sua mensagem foi enviada";

$lang['Click_return_inbox'] = "Premir %sAqui%s para voltar á sua Caixa de Entrada";
$lang['Click_return_index'] = "Premir %sAqui%s para voltar ao índice";

$lang['Send_a_new_message'] = "Enviar nova Mensagem Privada";
$lang['Send_a_reply'] = "Responder a uma Mensagem Privada";
$lang['Edit_message'] = "Editar Mensagem Privada";

$lang['Notification_subject'] = "Chegou uma Mensagem Privada nova";

$lang['Find_username'] = "Encontrar um utilizador";
$lang['Find'] = "Encontrar";
$lang['No_match'] = "Nada encontrado";

$lang['No_post_id'] = "Não foi especificado o ID da mensagem";
$lang['No_such_folder'] = "Não existe essa pasta";
$lang['No_folder'] = "Não foi especificada a pasta";

$lang['Mark_all'] = "Assinalar todas";
$lang['Unmark_all'] = "Desmarcar todas";

$lang['Confirm_delete_pm'] = "Tem a certeza que quer remover esta mensagem?";
$lang['Confirm_delete_pms'] = "Tem a certeza que quer remover estas mensagens?";

$lang['Inbox_size'] = "A sua Caixa de Entrada está %d%% cheia"; // eg. Your Inbox is 50% full
$lang['Sentbox_size'] = "A sua Caixa de Envio está %d%% cheia"; 
$lang['Savebox_size'] = "A sua Caixa de Reserva está %d%% cheia"; 

$lang['Click_view_privmsg'] = "Premir %sAqui%s para ir á Caixa de Entrada";


//
// Profiles/Registration
//
$lang['Viewing_user_profile'] = "Vendo perfil :: %s"; // %s is username 
$lang['About_user'] = "Tudo acerca de %s"; // %s is username

$lang['Preferences'] = "Preferências";
$lang['Items_required'] = "Itens marcados com um * são necessários excepto quando manifestado o contrário";
$lang['Registration_info'] = "Informação de Registo";
$lang['Profile_info'] = "Informação de Perfil";
$lang['Profile_info_warn'] = "Esta informação irá estar publicamente visível";
$lang['Avatar_panel'] = "Painel de controle de Avatar";
$lang['Avatar_gallery'] = "Galeria de Avatar";

$lang['Website'] = "Página/WWW";
$lang['Location'] = "Local/Origem";
$lang['Contact'] = "Contacto";
$lang['Email_address'] = "Endereço de Email";
$lang['Email'] = "Email";
$lang['Send_private_message'] = "Enviar Mensagem Privada";
$lang['Hidden_email'] = "[ Invisível ]";
$lang['Search_user_posts'] = "Procurar mensagens deste utilizador";
$lang['Interests'] = "Interesses";
$lang['Occupation'] = "Ocupação"; 
$lang['Poster_rank'] = "Escalão de Afixação de Mensagens";

$lang['Total_posts'] = "Total de Mensagens";
$lang['User_post_pct_stats'] = "%.2f%% do total"; // 1.25% of total
$lang['User_post_day_stats'] = "%.2f mensagens por dia"; // 1.5 posts per day
$lang['Search_user_posts'] = "Encontrar todas as mensagens de %s"; // Find all posts by username

$lang['No_user_id_specified'] = "Esse utilizador não existe";
$lang['Wrong_Profile'] = "Não pode modificar um perfil que não lhe pertence.";
$lang['Sorry_banned_or_taken_email'] = "O endereço de email que forneceu encontra-se banido, já se encontra registado por outro utilizador ou é inválido. Por favor tentar outro endereço alternativo e emm caso de também se encontrar banido contactar o administrador para informação";
$lang['Only_one_avatar'] = "Apenas pode ser especificado um tipo de avatar";
$lang['File_no_data'] = "O ficheiro do URL que deu não possui dados";
$lang['No_connection_URL'] = "Não se pode fazer ligação ao URL que forneceu";
$lang['Incomplete_URL'] = "O URL que forneceu está incompleto";
$lang['Wrong_remote_avatar_format'] = "O URL do avatar remoto não é válido";
$lang['No_send_account_inactive'] = "A sua senha não pode ser recuperada porque o seu registo encontra-se presentemente inactivo. Por favor contactar o administrador do fórum para mais informações";

$lang['Always_smile'] = "Activar sempre os Smileys";
$lang['Always_html'] = "Permitir sempre HTML";
$lang['Always_bbcode'] = "Permitir sempre BBCode";
$lang['Always_add_sig'] = "Anexar sempre a minha assinatura";
$lang['Always_notify'] = "Notificar-me sempre que haja respostas";
$lang['Always_notify_explain'] = "Envia um email quando alguém responda a uma mensagem que tenha colocado. Isto pode ser alterado sempre que escreva uma mensagem.";

$lang['Board_style'] = "Estilo do Painel";
$lang['Board_lang'] = "Língua do Painel";
$lang['No_themes'] = "Não há temas na base de dados";
$lang['Timezone'] = "Fuso Horário";
$lang['Date_format'] = "Formato da Data";
$lang['Date_format_explain'] = "O sintaxe usado é idêntico á função PHP <a href=\"http://www.php.net/date\" target=\"_other\">date()</a> ";
$lang['Signature'] = "Assinatura";
$lang['Signature_explain'] = "Isto é um bloco de texto que pode ser adicionado ás mensagens que faça. Há um limite de %d caracteres";
$lang['Public_view_email'] = "Mostrar sempre o meu endereço de Email";

$lang['Current_password'] = "Senha em uso";
$lang['New_password'] = "Senha nova";
$lang['Confirm_password'] = "Confirmar senha";
$lang['password_if_changed'] = "Apenas necessita fornecer uma senha caso a pretenda mudar";
$lang['password_confirm_if_changed'] = "Apenas necessita confirmar a sua senha caso a tenha mudado acima";

$lang['Avatar'] = "Avatar";
$lang['Avatar_explain'] = "Mostra uma pequena imagem gráfica por baixo dos seus detalhes nas mensagens. Apenas pode ser mostrada uma imagem de cada vez, a largura não exceder %d pixels, a altura não ser superior a %d pixels e o tamanho do ficheiro não ser superior a %dkB."; $lang['Upload_Avatar_file'] = "Carregar o Avatar a partir do seu computador";
$lang['Upload_Avatar_URL'] = "carregar o Avatar a partir de um URL";
$lang['Upload_Avatar_URL_explain'] = "Escrever o URL do local contendo o Avatar, para ser copiado para a página.";
$lang['Pick_local_Avatar'] = "Seleccionar um Avatar da galeria";
$lang['Link_remote_Avatar'] = "Ligar a um Avatar fora desta página";
$lang['Link_remote_Avatar_explain'] = "Escrever o URL do local contendo o Avatar que pretende que seja mostrado.";
$lang['Avatar_URL'] = "URL da imagem Avatar";
$lang['Select_from_gallery'] = "Seleccionar um Avatar da galeria";
$lang['View_avatar_gallery'] = "Mostrar a galeria";

$lang['Select_avatar'] = "Seleccionar um avatar";
$lang['Return_profile'] = "Cancelar o avatar";
$lang['Select_category'] = "Seleccionar uma categoria";

$lang['Delete_Image'] = "Remover a imagem";
$lang['Current_Image'] = "Imagem corrente";

$lang['Notify_on_privmsg'] = "Notificar-me por email quando haja Mensagens Privadas novas";
$lang['Popup_on_privmsg'] = "Avisar-me em janela destacada quando haja Mensagens Privadas novas"; 
$lang['Popup_on_privmsg_explain'] = "Surgirá uma pequena janela a avisar caso uma Mensagem Privada lhe seja enviada."; 
$lang['Hide_user'] = "Esconder o meu indicador de Ligado";

$lang['Profile_updated'] = "O seu perfil foi actualizado";
$lang['Profile_updated_inactive'] = "O seu perfil foi actualizado, contudo alterou detalhes vitais e como tal o oseu registo está inactivo. verificar o seu email para saber como reactivar o registo, ou se é necessária reactivação pelo administrador aguarde que tal seja feito";

$lang['Password_mismatch'] = "As senhas que escreveu não são iguais";
$lang['Current_password_mismatch'] = "A senha que forneceu não é igual á registada na base de dados";
$lang['Invalid_username'] = "O nome de utilizador que pediu já foi tomado, não é permitido, ou contém caracteres inválidos como \" ";
$lang['Signature_too_long'] = "A sua assinatura é muito extensa";
$lang['Fields_empty'] = "Deve preencher os espaços solicitados";
$lang['Avatar_filetype'] = "O tipo de ficheiro do avatar deverá ser .jpg, .gif ou .png";
$lang['Avatar_filesize'] = "O tamanho do ficheiro do avatar tem que ser inferior a %d kB"; // The avatar image file size must be less than 6 kB
$lang['Avatar_imagesize'] = "O avatar tem que ser inferior a %d pixels de largura e %d pixels de altura"; 

$lang['Welcome_subject'] = "Bem-vindo ao Fórum %s"; // Welcome to my.com forums
$lang['New_account_subject'] = "Novo registo de utilizador";
$lang['Account_activated_subject'] = "Registo activado";

$lang['Account_added'] = "Obrigado por ter registado, o seu registo foi criado. Pode ligar-se com o seu nome de utilizador e respectiva senha";
$lang['Account_inactive'] = "O seu registo foi criado. Contudo este fórum requer que o mesmo seja activado, uma senha para o efeito foi enviada para o endereço de email que forneceu. Por favor verificar o seu email para mais informações";
$lang['Account_inactive_admin'] = "O seu registo foi criado. Contudo este fórum requer que o mesmo seja activado pelo administrador. Foi-lhes enviado e você será informado quando o seu registo for activado";
$lang['Account_active'] = "O seu registo foi activado. Obrigado por se ter registado";
$lang['Account_active_admin'] = "O seu registo foi agora activado";
$lang['Reactivate'] = "Reactivar o seu registo!";
$lang['COPPA'] = "O seu registo foi criado mas tem que ser aprovado, por favor verificar o seu email para detalhes.";

$lang['Registration'] = "Condições de Aceitação de Registo";
$lang['Reg_agreement'] = "Apesar dos administradores e moderadores deste fórum tentarem remover ou editar qualquer material indesejável logo que detectado, é impossível rever todas as mensagens. Como tal você reconhece que todas as mensagens efectuadas nos fóruns expressam os pontos de vista e opiniões dos seus respectivos autores e não dos administradores, moderadores ou o encarregado das páginas (excepto menasgens colocadas por essas pessoas) não sendo por tal responsáveis.<br /><br />Você aceita não colocar qualquer mensagem abusiva, obscena, invulgar, insultuosa, de ódio, ameaçadora, sexualmente tendenciosa ou qualquer outro material que possa violar qualquer lei aplicável. Tal acontecendo conduz á sua expulsão imediata e permanente (além de ser notificado o seu provedor de Internet). Os endereços de IP de todas as mensagens são registados para ajudar a implementar essas condições. Você concorda que quem faz e mantém estas páginas, administradores e moderadores deste fórum tem o direito de remover, editar, mover ou encerrar qualquer tópico em qualquer altura que eles assim o decidam e seja implícito. Como utilizador você aceita que qualquer informação que forneceu acima seja guardada numa base de dados. Apesar dessa informação não ser fornecida a terceiros sem a sua autorização, o encarregado das páginas, administradores ou moderadores não podem assumir a responsabilidade por qualquer tentativa de acto de 'hacking', intromissão forçada e ilegal que conduza a essa informação ser exposta.<br /><br />Este sistema de fóruns usa 'cookies' para guardar informação no seu computador. Esses 'cookies' não possúem nenhuma da informação acima fornecida, apenas servem para melhorar o seu prazer aquando e enquanto visita estes fóruns. O endereço de email é apenas usado para confirmar a informação do seu registo e a senha (bem como para enviar novas senhas caso se esqueça da que acabou de submeter).<br /><br />Ao carregar abaixo para prosseguir com o registo você concorda em seguir estas condições.";

$lang['Agree_under_13'] = "Aceito estes termos e tenho  <b>menos que</b> 13 anos de idade";
$lang['Agree_over_13'] = "Aceito estes termos e tenho <b>mais que</b> 13 anos de idade";
$lang['Agree_not'] = "Não aceito estes termos";

$lang['Wrong_activation'] = "A senha de activação que forneceu não é igual nenhuma que se encontra na base de dados";
$lang['Send_password'] = "Envie-me uma nova senha"; 
$lang['Password_updated'] = "Uma senha nova foi criada, por favor verifique o seu email para pormenores em como a activar";
$lang['No_email_match'] = "O endereço de email que forneceu não é igual ao que se encontra designado para esse nome de usuário";
$lang['New_password_activation'] = "Activação de Senha Nova";
$lang['Password_activated'] = "O seu registo foi reactivado. Para se ligar por favor use a senha que lhe foi fornecida no email que recebeu";

$lang['Send_email_msg'] = "Enviar uma mensagem de email";
$lang['No_user_specified'] = "Não foi especificado um utilizador";
$lang['User_prevent_email'] = "Este utilizador não pretende receber email. Tente enviar-lhe uma Mensagem Privada";
$lang['User_not_exist'] = "Esse utilizador não existe";
$lang['CC_email'] = "Enviar uma cópia deste email a si proprio";
$lang['Email_message_desc'] = "Esta mensagem será enviada em texto, por favor não incluir  qualquer HTML ou BBCode. Para o endereço de devolução será colocado o seu endereço de email.";
$lang['Flood_email_limit'] = "Não pode enviar outro email neste momento, tente novamente mais tarde";
$lang['Recipient'] = "Recipiente";
$lang['Email_sent'] = "O email foi enviado";
$lang['Send_email'] = "Enviar email";
$lang['Empty_subject_email'] = "Deve especificar um assunto para o email";
$lang['Empty_message_email'] = "Deve escrever uma mensagem a ser enviada no email";


//
// Memberslist
//
$lang['Select_sort_method'] = "Forma de listagem";
$lang['Sort'] = "Seleccionar";
$lang['Sort_Top_Ten'] = "Top 10 autores";
$lang['Sort_Joined'] = "Data de registo";
$lang['Sort_Username'] = "Utilizador";
$lang['Sort_Location'] = "Local";
$lang['Sort_Posts'] = "Total de mensagens";
$lang['Sort_Email'] = "Email";
$lang['Sort_Website'] = "Página na WWW";
$lang['Sort_Ascending'] = "crescente";
$lang['Sort_Descending'] = "decrescente";
$lang['Order'] = "Ordem";


//
// Group control panel
//
$lang['Group_Control_Panel'] = "Painel de controle de Grupos";
$lang['Group_member_details'] = "Detalhes de Membros de Grupos";
$lang['Group_member_join'] = "Juntar-se a Grupo";

$lang['Group_Information'] = "Informação de Grupo";
$lang['Group_name'] = "Nome do Grupo";
$lang['Group_description'] = "Descrição do Grupo";
$lang['Group_membership'] = "Registo de Membros";
$lang['Group_Members'] = "Membros do Grupo";
$lang['Group_Moderator'] = "Moderador do Grupo";
$lang['Pending_members'] = "Registos pendentes";

$lang['Group_type'] = "Tipo de Grupo";
$lang['Group_open'] = "Grupo aberto";
$lang['Group_closed'] = "Grupo fechado";
$lang['Group_hidden'] = "Grupo invisível";

$lang['Current_memberships'] = "Grupos existentes";
$lang['Non_member_groups'] = "Grupos de não-membros";
$lang['Memberships_pending'] = "Registo de membro pendente";

$lang['No_groups_exist'] = "Não existem Grupos";
$lang['Group_not_exist'] = "Esse Grupo de utilizadores não existe";

$lang['Join_group'] = "Juntar-se a Grupo";
$lang['No_group_members'] = "Este Grupo não possui membros";
$lang['Group_hidden_members'] = "Este Grupo encontra-se invisível, não pode ver os seus membros";
$lang['No_pending_group_members'] = "Este Grupo não possui membros pendentes";
$lang["Group_joined"] = "Você subscreveu com sucesso a este Grupo<br />Será notificado quando a sua subscripção for aprovada pelo Moderador de Grupo";
$lang['Group_request'] = "Foi feito um pedido para se juntar ao seu Grupo";
$lang['Group_approved'] = "O seu pedido foi aceite";
$lang['Group_added'] = "Você foi adicionado a este Grupo de utilizadores"; 
$lang['Already_member_group'] = "Você é já membro deste Grupo";
$lang['User_is_member_group'] = "O utilizador é já membro deste grupo";
$lang['Group_type_updated'] = "Tipo de Grupo actualizado com sucesso";

$lang['Could_not_add_user'] = "O utilizador que seleccionou não existe";
$lang['Could_not_anon_user'] = "Não pode tornar Anónimo um membro de Grupo";

$lang['Confirm_unsub'] = "Tem a certeza que quer remover a sua subscrição deste Grupo?";
$lang['Confirm_unsub_pending'] = "A sua subscrição a este Grupo não foi ainda aprovada, tem a certeza que quer remover a sua subscrição?";

$lang['Unsub_success'] = "Foi retirada a sua subscrição deste grupo.";

$lang['Approve_selected'] = "Aprovar os assinalados";
$lang['Deny_selected'] = "Recusar os assinalados";
$lang['Not_logged_in'] = "Deverá estar ligado para entrar no grupo.";
$lang['Remove_selected'] = "Remover os assinalados";
$lang['Add_member'] = "Adicionar um membro";
$lang['Not_group_moderator'] = "Você não é moderador deste Grupo e como tal não pode efectuar essa função.";

$lang['Login_to_join'] = "Ligar-se para entrar ou dar manutenção á lista de membros do Grupo";
$lang['This_open_group'] = "Este Grupo está aberto, prima para solicitar ser membro";
$lang['This_closed_group'] = "Este Grupo está fechado, não são aceites mais utilizadores.";
$lang['This_hidden_group'] = "Este Grupo está invisível, não são permitidas adições automáticas.";
$lang['Member_this_group'] = "Você é membro deste Grupo";
$lang['Pending_this_group'] = "O seu registo de membro neste Grupo está pendente";
$lang['Are_group_moderator'] = "Você é moderador deste Grupo";
$lang['None'] = "Nenhum";

$lang['Subscribe'] = "Subscrever";
$lang['Unsubscribe'] = "Remover Subscrição";
$lang['View_Information'] = "Ver Informação";


//
// Search
//
$lang['Search_query'] = "Termos de Pesquisa";
$lang['Search_options'] = "Opções de Pesquisa";

$lang['Search_keywords'] = "Pesquisar por palavras-chave";
$lang['Search_keywords_explain'] = "Pode usar os operadores boleanos <u>AND</u> para definir palavras que tenham que constar no resultado, <u>OR</u> para definir palavras que possam constar no resultado e <u>NOT</u> para definir palavras que não devam constar no resultado. Pode usar asteriscos '*' para obter palavras por aproximação";
$lang['Search_author'] = "Pesquisar por Autor";
$lang['Search_author_explain'] = "Pode usar asteriscos '*' para obter palavras por aproximação";

$lang['Search_for_any'] = "Pesquisar por qualquer dos termos ou como está descrito";
$lang['Search_for_all'] = "Pesquisar por todos termos";
$lang['Search_title_msg'] = "Pesquisar em títulos de tópicos e texto de mensagens"; 
$lang['Search_msg_only'] = "Pesquisar apenas em texto de mensagens";             

$lang['Return_first'] = "Mostrar os primeiros"; // followed by xxx characters in a select box
$lang['characters_posts'] = "caracteres de mensagens";

$lang['Search_previous'] = "Período"; // followed by days, weeks, months, year, all in a select box

$lang['Sort_by'] = "Seleccionar por";
$lang['Sort_Time'] = "Data da Mensagem";
$lang['Sort_Post_Subject'] = "Assunto";
$lang['Sort_Topic_Title'] = "Título do Tópico";
$lang['Sort_Author'] = "Autor";
$lang['Sort_Forum'] = "Fórum";

$lang['Display_results'] = "Mostrar resultados como";
$lang['All_available'] = "Todos os disponíveis";
$lang['No_searchable_forums'] = "Você não possui autorização para fazer pesquiza nestas páginas";

$lang['No_search_match'] = "Não há tópicos ou mensagens englobados nos seus parâmetros de pesquisa";
$lang['Found_search_match'] = "Encontrado %d item"; // eg. Search found 1 match
$lang['Found_search_matches'] = "Encontrados %d itens"; // eg. Search found 24 matches

$lang['Close_window'] = "Fechar a janela";


//
// Auth related entries
//
// Note the %s will be replaced with one of the following 'user' arrays
$lang['Sorry_auth_announce'] = "Apenas %s podem anunciar neste fórum";
$lang['Sorry_auth_sticky'] = "Apenas %s podem colocar mensagens amovíveis neste fórum"; 
$lang['Sorry_auth_read'] = "Apenas %s podem ler tópicos neste fórum"; 
$lang['Sorry_auth_post'] = "Apenas %s podem colocar tópicos neste fórum"; 
$lang['Sorry_auth_reply'] = "Apenas %s podem responder a mensagens neste fórum"; 
$lang['Sorry_auth_edit'] = "Apenas %s podem editar mensagens neste fórum"; 
$lang['Sorry_auth_delete'] = "Apenas %s podem remover mensagens neste fórum"; 
$lang['Sorry_auth_vote'] = "Apenas %s podem votar neste fórum"; 

// These replace the %s in the above strings
$lang['Auth_Anonymous_Users'] = "<b>utilizadores anónimos</b>";
$lang['Auth_Registered_Users'] = "<b>utilizadores registados</b>";
$lang['Auth_Users_granted_access'] = "<b>utilizadores com acesso especial</b>";
$lang['Auth_Moderators'] = "<b>moderadores</b>";
$lang['Auth_Administrators'] = "<b>administradores</b>";

$lang['Not_Moderator'] = "Você não é moderador neste fórum";
$lang['Not_Authorised'] = "Não autorizado";

$lang['You_been_banned'] = "Você foi expulso deste fórum<br />Contacte o gerente de páginas ou o administrador para mais informação";


//
// Viewonline
//
$lang['Reg_users_zero_online'] = "Não há utilizadores ligados e "; // There ae 5 Registered and
$lang['Reg_users_online'] = "Há %d utilizadores ligados e "; // There ae 5 Registered and
$lang['Reg_user_online'] = "Há %d utilizador ligado e "; // There ae 5 Registered and
$lang['Hidden_users_zero_online'] = "não há utilizadores em modo invisível"; // 6 Hidden users online
$lang['Hidden_users_online'] = "%d utilizadores ligados em modo invisível"; // 6 Hidden users online
$lang['Hidden_user_online'] = "%d utilizador ligado em modo invisível"; // 6 Hidden users online
$lang['Guest_users_online'] = "Há %d visitantes ligados"; // There are 10 Guest users online
$lang['Guest_users_zero_online'] = "Não há visitantes ligados"; // There are 10 Guest users online
$lang['Guest_user_online'] = "Há %d visitante ligado"; // There is 1 Guest user online
$lang['No_users_browsing'] = "Não há presentemente qualquer utilizador a verificar este fórum";

$lang['Online_explain'] = "Esta informação é baseada em utilizadores activos nos últimos cinco minutos";

$lang['Forum_Location'] = "Local do Fórum";
$lang['Last_updated'] = "Actualizado pela última vez";

$lang['Forum_index'] = "Índice do Fórum";
$lang['Logging_on'] = "Ligados";
$lang['Posting_message'] = "Colocando mensagens";
$lang['Searching_forums'] = "Pesquisando os Fóruns";
$lang['Viewing_profile'] = "Verificando Perfil";
$lang['Viewing_online'] = "Vendo quem se encontra ligado";
$lang['Viewing_member_list'] = "Vendo a lista de membros";
$lang['Viewing_priv_msgs'] = "Vendo Mensagens Privadas";
$lang['Viewing_FAQ'] = "Vendo FAQ - Questões Mais Frequentes";


//
// Moderator Control Panel
//
$lang['Mod_CP'] = "Painel de Controle de Moderador";
$lang['Mod_CP_explain'] = "Usando o formulário abaixo pode efectuar operações de moderaçáo maciças neste fórum. Pode bloquear, desbloquear, mover ou remover qualquer quantidade de tópicos.";

$lang['Select'] = "Seleccionar";
$lang['Delete'] = "Remover";
$lang['Move'] = "Mover";
$lang['Lock'] = "Bloquear";
$lang['Unlock'] = "Desbloquear";

$lang['Topics_Removed'] = "Os tópicos seleccionados foram removidos da base de dados com sucesso.";
$lang['Topics_Locked'] = "Os tópicos seleccionados foram bloqueados";
$lang['Topics_Moved'] = "Os tópicos seleccionados foram movidos";
$lang['Topics_Unlocked'] = "Os tópicos seleccionados foram desbloqueados";
$lang['No_Topics_Moved'] = "Nenhum tópico foi movido";

$lang['Confirm_delete_topic'] = "Tem a certeza que quer remover o/s tópico/s seleccionado/s?";
$lang['Confirm_lock_topic'] = "Tem a certeza que quer bloquear o/s tópico/s seleccionado/s?";
$lang['Confirm_unlock_topic'] = "Tem a certeza que quer desbloquear o/s tópico/s seleccionado/s?";
$lang['Confirm_move_topic'] = "Tem a certeza que quer mover o/s tópico/s seleccionado/s?";

$lang['Move_to_forum'] = "Mover para fórum";
$lang['Leave_shadow_topic'] = "Deixar uma imagem do tópico no fórum anterior.";

$lang['Split_Topic'] = "Subdividor o painel do tópico";
$lang['Split_Topic_explain'] = "Usando o formulário abaixo pode subdividir um tópico em dois, tanto seleccionando as mensagens individualmente como dividindo uma mensagem seleccionada";
$lang['Split_title'] = "Título de Tópico Novo";
$lang['Split_forum'] = "Fórum para Novo Tópico";
$lang['Split_posts'] = "Subdividir as mensagens seleccionadas";
$lang['Split_after'] = "Subdividir pela mensagem seleccionada";
$lang['Topic_split'] = "O tópico seleccionado foi subdividido com sucesso";

$lang['Too_many_error'] = "Você seleccionou demasiadas mensagens. Apenas pode seleccionar uma mensagem para depois subdividir um tópico!";

$lang['None_selected'] = "Você não seleccionou qualquer tópico para efectuar esta operação. Por favor voltar atrás e escolha pelo menos um.";
$lang['New_forum'] = "Fórum Novo";

$lang['This_posts_IP'] = "IP para esta mensagem";
$lang['Other_IP_this_user'] = "Outros IP's que este utilizador usou para colocar mensagens";
$lang['Users_this_IP'] = "Utilizadores a colocar mensagens a partir deste IP";
$lang['IP_info'] = "Informação de IP";
$lang['Lookup_IP'] = "Verificar IP";


//
// Timezones ... for display on each page
//
$lang['All_times'] = "Todos os tempos estão em %s"; // eg. All times are GMT - 12 Hours (times from next block)

$lang['-12'] = "TMG - 12 Horas";
$lang['-11'] = "TMG - 11 Horas";
$lang['-10'] = "HST (Hawaii)";
$lang['-9'] = "TMG - 9 Horas";
$lang['-8'] = "PST (U.S./Canadá)";
$lang['-7'] = "MST (U.S./Canadá)";
$lang['-6'] = "CST (U.S./Canadá)";
$lang['-5'] = "EST (U.S./Canadá)";
$lang['-4'] = "TMG - 4 Horas";
$lang['-3.5'] = "TMG - 3.5 Horas";
$lang['-3'] = "TMG - 3 Horas";
$lang['-2'] = "Mid-Atlantico";
$lang['-1'] = "TMG - 1 Horas";
$lang['0'] = "TMG";
$lang['1'] = "CET (Europa)";
$lang['2'] = "EET (Europa)";
$lang['3'] = "TMG + 3 Horas";
$lang['3.5'] = "TMG + 3.5 Horas";
$lang['4'] = "TMG + 4 Horas";
$lang['4.5'] = "TMG + 4.5 Horas";
$lang['5'] = "TMG + 5 Horas";
$lang['5.5'] = "TMG + 5.5 Horas";
$lang['6'] = "TMG + 6 Horas";
$lang['7'] = "TMG + 7 Horas";
$lang['8'] = "WST (Austrália)";
$lang['9'] = "TMG + 9 Horas";
$lang['9.5'] = "CST (Austrália)";
$lang['10'] = "EST (Austrália)";
$lang['11'] = "TMG + 11 Horas";
$lang['12'] = "TMG + 12 Horas";

// These are displayed in the timezone select box
$lang['tz']['-12'] = "(TMG -12:00 Horas) Eniwetok, Kwajalein";
$lang['tz']['-11'] = "(TMG -11:00 Horas) Midway Island, Samoa";
$lang['tz']['-10'] = "(TMG -10:00 Horas) Hawaii";
$lang['tz']['-9'] = "(TMG -9:00 Horas) Alasca";
$lang['tz']['-8'] = "(TMG -8:00 Horas) Pacific Time (US &amp; Canada), Tijuana";
$lang['tz']['-7'] = "(TMG -7:00 Horas) Mountain Time (US &amp; Canada), Arizona";
$lang['tz']['-6'] = "(TMG -6:00 Horas) Central Time (US &amp; Canada), Mexico City";
$lang['tz']['-5'] = "(TMG -5:00 Horas) Eastern Time (US &amp; Canada), Bogota, Lima, Quito";
$lang['tz']['-4'] = "(TMG -4:00 Horas) Atlantic Time (Canada), Caracas, La Paz";
$lang['tz']['-3.5'] = "(TMG -3:30 Horas) Newfoundland";
$lang['tz']['-3'] = "(TMG -3:00 Horas) Brasíla, Buenos Aires, Georgetown, Falkland Is";
$lang['tz']['-2'] = "(TMG -2:00 Horas) Mid-Atlantic, Ascension Is., St. Helena";
$lang['tz']['-1'] = "(TMG -1:00 Horas) Açores, Cabo Verde";
$lang['tz']['0'] = "(TMG) Casablanca, Dublin, Edinburgh, London, Lisboa, Monrovia";
$lang['tz']['1'] = "(TMG +1:00 Horas) Amsterdam, Berlin, Brussels, Madrid, Paris, Roma";
$lang['tz']['2'] = "(TMG +2:00 Horas) Cairo, Helsinki, Kaliningrad, South Africa, Warsaw";
$lang['tz']['3'] = "(TMG +3:00 Horas) Baghdad, Riyadh, Moscow, Nairobi";
$lang['tz']['3.5'] = "(TMG +3:30 Horas) Tehran";
$lang['tz']['4'] = "(TMG +4:00 Horas) Abu Dhabi, Baku, Muscat, Tbilisi";
$lang['tz']['4.5'] = "(TMG +4:30 Horas) Kabul";
$lang['tz']['5'] = "(TMG +5:00 Horas) Ekaterinburg, Islamabad, Karachi, Tashkent";
$lang['tz']['5.5'] = "(TMG +5:30 Horas) Bombay, Calcutta, Madras, New Delhi";
$lang['tz']['6'] = "(TMG +6:00 Horas) Almaty, Colombo, Dhaka, Novosibirsk";
$lang['tz']['6.5'] = "(TMG +6:30 Horas) Rangoon";
$lang['tz']['7'] = "(TMG +7:00 Horas) Bangkok, Hanoi, Jakarta";
$lang['tz']['8'] = "(TMG +8:00 Horas) Beijing, Hong Kong, Perth, Singapore, Taipei";
$lang['tz']['9'] = "(TMG +9:00 Horas) Osaka, Sapporo, Seoul, Tokyo, Yakutsk";
$lang['tz']['9.5'] = "(TMG +9:30 Horas) Adelaide, Darwin";
$lang['tz']['10'] = "(TMG +10:00 Horas) Canberra, Guam, Melbourne, Sydney, Vladivostok";
$lang['tz']['11'] = "(TMG +11:00 Horas) Magadan, New Caledonia, Solomon Islands";
$lang['tz']['12'] = "(TMG +12:00 Horas) Auckland, Wellington, Fiji, Marshall Island";

$lang['days_long'][0] = "Domingo";
$lang['days_long'][1] = "Segunda-Feira";
$lang['days_long'][2] = "Terça-Feira";
$lang['days_long'][3] = "Quarta-Feira";
$lang['days_long'][4] = "Quinta-Feira";
$lang['days_long'][5] = "Sexta-Feira";
$lang['days_long'][6] = "Sábado";

$lang['days_short'][0] = "Dom";
$lang['days_short'][1] = "Seg";
$lang['days_short'][2] = "Ter";
$lang['days_short'][3] = "Qua";
$lang['days_short'][4] = "Qui";
$lang['days_short'][5] = "Sex";
$lang['days_short'][6] = "Sab";

$lang['months_long'][0] = "Janeiro";
$lang['months_long'][1] = "Fevereiro";
$lang['months_long'][2] = "Março";
$lang['months_long'][3] = "Abril";
$lang['months_long'][4] = "Maio";
$lang['months_long'][5] = "Junho";
$lang['months_long'][6] = "Julho";
$lang['months_long'][7] = "Agosto";
$lang['months_long'][8] = "Setembro";
$lang['months_long'][9] = "Outubro";
$lang['months_long'][10] = "Novembro";
$lang['months_long'][11] = "Dezembro";

$lang['months_short'][0] = "Jan";
$lang['months_short'][1] = "Fev";
$lang['months_short'][2] = "Mar";
$lang['months_short'][3] = "Abr";
$lang['months_short'][4] = "Mai";
$lang['months_short'][5] = "Jun";
$lang['months_short'][6] = "Jul";
$lang['months_short'][7] = "Ago";
$lang['months_short'][8] = "Set";
$lang['months_short'][9] = "Out";
$lang['months_short'][10] = "Nov";
$lang['months_short'][11] = "Dez";

//
// Errors (not related to a
// specific failure on a page)
//
$lang['Information'] = "Informação";
$lang['Critical_Information'] = "Informação Crítica";

$lang['General_Error'] = "Erro Geral";
$lang['Critical_Error'] = "Erro Crítico";
$lang['An_error_occured'] = "Ocorreu um Erro";
$lang['A_critical_error'] = "Ocorreu um Erro Crítico";

//
// That's all Folks!
// -------------------------------------------------

?>