<?php
/***************************************************************************
 *                            lang_admin.php [Galician]
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
 
/*****************************************************************************
 * Translation by:
 * Sergio Ares Chao :: sergio@ciberagendas.com
 ****************************************************************************/

//
// Mesmo formato que lang_main
//

//
// Módulos, reemprazan as claves usadas en modules[][] en cada arquivo de módulo
//
$lang['General'] = "Administración Xeral";
$lang['Users'] = "Administración dos Usuarios";
$lang['Groups'] = "Administración dos Grupos";
$lang['Forums'] = "Administración dos Foros";
$lang['Styles'] = "Administración dos Estilos";

$lang['Configuration'] = "Configuración";
$lang['Permissions'] = "Permisos";
$lang['Manage'] = "Xestión";
$lang['Disallow'] = "Deshabilitar nomes de usuario";
$lang['Prune'] = "Purgar";
$lang['Mass_Email'] = "Correo Masivo";
$lang['Ranks'] = "Rangos";
$lang['Smilies'] = "Emoticonos";
$lang['Ban_Management'] = "Control de Exclusión";
$lang['Word_Censor'] = "Palabras Censuradas";
$lang['Export'] = "Exportar";
$lang['Create_new'] = "Crear";
$lang['Add_new'] = "Engadir";
$lang['Backup_DB'] = "Backup da Base de Datos";
$lang['Restore_DB'] = "Restaura-la Base de Datos";


//
// Índice
//
$lang['Admin'] = "Administración";
$lang['Not_admin'] = "Vostede non está autorizado para administrar este Foro";
$lang['Welcome_phpBB'] = "Benvido a phpBB";
$lang['Admin_intro'] = "Gracias por elixir phpBB como a súa solución para o seu foro. Esta pantalla daralle unha síntese das principais estatísticas do seu foro. Pode regresar a esta páxina clickeando no link de <u>Índice do Administrador</u> no panel da esquerda. Para regresar ó índice do seu foro, clickee o logo de phpBB tamén ubicado no panel esquerdo. Os outros links ubicados á esquerda desta pantalla permitiranlle controlar tódolos aspectos deste foro, cada pantalla terá instruccións de cómo utiliza-las ferramentas.";
$lang['Main_index'] = "Índice do Foro";
$lang['Forum_stats'] = "Estatísticas do Foro";
$lang['Admin_Index'] = "Índice do Administrador";
$lang['Preview_forum'] = "Vista previa do Foro";

$lang['Click_return_admin_index'] = "Prema %saquí%s para regresar ó Indice do Administrador";

$lang['Statistic'] = "Estatística";
$lang['Value'] = "Valor";
$lang['Number_posts'] = "Cantidade de envíos";
$lang['Posts_per_day'] = "Envíos por día";
$lang['Number_topics'] = "Cantidade de temas";
$lang['Topics_per_day'] = "Temas por día";
$lang['Number_users'] = "Cantidade de usuarios";
$lang['Users_per_day'] = "Usuarios por día";
$lang['Board_started'] = "Data de inicio do Foro";
$lang['Avatar_dir_size'] = "Tamaño do directorio de Imaxes";
$lang['Database_size'] = "Tamaño da Base de Datos";
$lang['Gzip_compression'] ="Tipo de Compresión Gzip";
$lang['Not_available'] = "Non está dispoñible";

$lang['ON'] = "ON"; // Para a compresión GZip
$lang['OFF'] = "OFF"; 


//
// Utilidades da Base de Datos
//
$lang['Database_Utilities'] = "Utilidades da Base de Datos";

$lang['Restore'] = "Restaurar";
$lang['Backup'] = "Backup";
$lang['Restore_explain'] = "Isto restaurará tódalas táboas de phpBB desde un arquivo previamente gardado. Se o seu servidor o soporta vostede pode subir un arquivo de texto comprimido mediante o gzip e este será automaticamente descomprimido. <b>ATENCION</b> Isto sobre-escribirá a información existente. A restauración pode durar uns minutos, por favor fique nesta páxina ata que o proceso sexa completado.";
$lang['Backup_explain'] = "Desde aquí vostede pode facer unha copia de seguridade (backup) de toda a información relacionada con phpBB. Se vostede ten táboas adicionais na mesma Base de Datos das que quixera realizar un backup ingrese os seus nomes separados por comas no campo de Táboas Adicionais. Se o seu servidor o soporta pode utilizar o gzip para comprimi-lo arquivo e reduci-lo seu tamaño antes de descargalo.";

$lang['Backup_options'] = "Opcións do Backup";
$lang['Start_backup'] = "Comenza-lo Backup";
$lang['Full_backup'] = "Backup completo";
$lang['Structure_backup'] = "Só a Estructura";
$lang['Data_backup'] = "Só os Datos";
$lang['Additional_tables'] = "Táboas adicionais";
$lang['Gzip_compress'] = "Compresión nun arquivo Gzip";
$lang['Select_file'] = "Seleccionar un arquivo";
$lang['Start_Restore'] = "Comezar a Restauración";

$lang['Restore_success'] = "A Base de Datos foi Restaurada.<br /><br />O seu Foro debería voltar á normalidade unha vez realizado o proceso.";
$lang['Backup_download'] = "A descarga comezará deseguio, por favor espere un intre";
$lang['Backups_not_supported'] = "Desculpe pero a opción de backup da súa Base de Datos non está soportada polo seu sistema";

$lang['Restore_Error_uploading'] = "Erro subindo o arquivo backup";
$lang['Restore_Error_filename'] = "Erro no nome de arquivo, por favor ténteo cun arquivo diferente";
$lang['Restore_Error_decompress'] = "Non se pode descomprimir un arquivo gzip, por favor súbao nunha version de texto";
$lang['Restore_Error_no_file'] = "Ningún arquivo foi subido";


//
// Páxinas Auth
//
$lang['Select_a_User'] = "Seleccionar un Usuario";
$lang['Select_a_Group'] = "Seleccionar un Grupo";
$lang['Select_a_Forum'] = "Seleccionar un Foro";
$lang['Auth_Control_User'] = "Control de Permisos ós Usuarios";
$lang['Auth_Control_Group'] = "Control de Permisos ós Grupos"; 
$lang['Auth_Control_Forum'] = "Control de Permisos ós Foros"; 
$lang['Look_up_User'] = "Observar un Usuario";
$lang['Look_up_Group'] = "Observar un Grupo"; 
$lang['Look_up_Forum'] = "Observar un Foro"; 

$lang['Group_auth_explain'] = "Desde aquí poderá cambia-los permisos e o estado do moderador asignado a cada grupo de usuarios. Lembre que cambiando os permisos do Grupo, que os permisos individuais cambiarán unha vez que a persona ingrese ó foro. Vostede será advertido neste caso.";
$lang['User_auth_explain'] = "Desde aquí vostede pode cambia-los permisos e estado do moderador asignado a cada usuario. Teña presente que cambiando os permisos dun usuario os permisos do grupo ficarán sen cambiar ata que o usuario entre ós foros. Vostede será advertido neste caso.";
$lang['Forum_auth_explain'] = "Desde aquí pode cambia-los niveis de autorización de cada foro. Para realizar isto ten dous modos; un simple e outro avanzado. Este último brindaralle maior control para a operación e o funcionamento de cada foro. Teña en conta que ó cambia-los niveis de cada foro pode afectar ó funcionamiento de cada usuario segundo o foro e os permisos que teña o usuario.";

$lang['Simple_mode'] = "Modalidade Simple";
$lang['Advanced_mode'] = "Modalidade Avanzada";
$lang['Moderator_status'] = "Estado do Moderador";

$lang['Allowed_Access'] = "Acceso Permitido";
$lang['Disallowed_Access'] = "Acseso non Permitido";
$lang['Is_Moderator'] = "É Moderador";
$lang['Not_Moderator'] = "Non é Moderador";

$lang['Conflict_warning'] = "Advertencia de Conflicto en Autorización";
$lang['Conflict_access_userauth'] = "Este usuario aínda posúe acceso a este foro debido ó Grupo ó cal pertence. Vostede deberá cambia-los permisos do grupo ou borra-lo usuario do Grupo para facer que o usuario non teña acceso a este foro. Os dereitos do Grupo e o Usuario explicanse abaixo.";
$lang['Conflict_mod_userauth'] = "Este usuario aínda posúe dereitos de Moderador debido ó Grupo ó cal pertence. Vostede deberá cambia-los permisos do grupo ou borra-lo usuario do Grupo para facer que o usuario non teña acceso a este foro con permisos de Moderador. Os dereitos de moderador explícanse abaixo.";

$lang['Conflict_access_groupauth'] = "O seguinte usuario (ou usuarios) aínda ten acceso a este foro debido ós permisos que ten como Usuario. Para que non teña acceso a este foro vostede deberá cambia-los seus permisos. Os dereitos de Usuarios explícanse abaixo.";
$lang['Conflict_mod_groupauth'] = "O seguinte usuario (ou usuarios) aínda posúe dereitos de Moderador neste foro. Para que non teña acceso a este foro con dereitos de Moderador vostede deberá cambia-los seus permisos. Os dereitos de Usuarios explícanse abaixo.";

$lang['Public'] = "Público";
$lang['Private'] = "Privado";
$lang['Registered'] = "Rexistrado";
$lang['Administrators'] = "Administrador";
$lang['Hidden'] = "Oculto";

// Estes amósanse nas táboas do modo avanzado de auth.
// Tenteente mantelos curtos
$lang['Forum_ALL'] = "TODOS";
$lang['Forum_REG'] = "REX";
$lang['Forum_PRIVATE'] = "PRIVADOS";
$lang['Forum_MOD'] = "MOD";
$lang['Forum_ADMIN'] = "ADMIN";

$lang['View'] = "Ver";
$lang['Read'] = "Ler";
$lang['Post'] = "Envío";
$lang['Reply'] = "Resposta";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Borrar";
$lang['Sticky'] = "PostIt";
$lang['Announce'] = "Anuncio";
$lang['Vote'] = "Votar";
$lang['Pollcreate'] = "Crear unha enquisa";

$lang['Permissions'] = "Permisos";
$lang['Simple_Permission'] = "Permiso Simple";

$lang['User_Level'] = "Nivel de Usuario";
$lang['Auth_User'] = "Usuario";
$lang['Auth_Admin'] = "Administrador";
$lang['Group_memberships'] = "Grupo de Usuarios";
$lang['Usergroup_members'] = "Este Grupo contén os siguientes Usuarios";

$lang['Forum_auth_updated'] = "Permiso do Foro actualizado";
$lang['User_auth_updated'] = "Permiso do usuario actualizado";
$lang['Group_auth_updated'] = "Permiso do Grupo actualizado";

$lang['Auth_updated'] = "Os permisos foron cambiados";
$lang['Click_return_userauth'] = "Prema %saquí%s para volver ve-los Permisos dos Usuarios";
$lang['Click_return_groupauth'] = "Prema %saquí%s para volver ve-los Permisos do Grupo";
$lang['Click_return_forumauth'] = "Prema %saquí%s para volver ve-los Permisos do Foro";


//
// Baneo
//
$lang['Ban_control'] = "Control de Exclusión";
$lang['Ban_explain'] = "Desde aquí vostede pode banear a un usuario. Pode banear ós usuarios polo seu nome, pola súa dirección IP, ou polo seu Hostname. Este método prevén que un usuario teña acceso á páxina principal do foro. Para previr que o Usuario se rexistre cunha nova conta tamén pode bannear o seu enderezo de email. Teña en conta que baneando unha dirección de email non evitará que o usuario poda ingresar ó foro nin que publique mensaxes. Para iso deberá utilizar un dos dous métodos explicados anteriormente.";
$lang['Ban_explain_warn'] = "Teña en conta que colocando un RANGO de direccións IP vostede banea a tódalas direccións que se atopan dentro do Rango da lista de bans.  Se realmente debe utilizar un rango tente utilizar un pequeno para así non banear a outros usuarios.";

$lang['Select_username'] = "Selecione un Nome de Usuario";
$lang['Select_ip'] = "Seleccione unha dirección IP";
$lang['Select_email'] = "Seleccione un enderezo de Email";

$lang['Ban_username'] = "Banear a un ou varios Usuarios";
$lang['Ban_username_explain'] = "Pode banear a múltiples usuarios dunha soa vez usando a combinación apropiada de rato e teclado para o seu ordenador e navegador.";


$lang['Ban_IP'] = "Banear unha ou varias direccións IP o HOSTNAMES";
$lang['IP_hostname'] = "Direccións IP o HOSTNAMES";
$lang['Ban_IP_explain'] = "Para especificar diferentes IPs ou Nomes de Dominio, sepáreos con comas. Para especificar un rango de direccións IP separe o comezo e o final utilizando un guión (-), para especificar un comodín utilize o *";

$lang['Ban_email'] = "Banear un ou varios enderezos de email";
$lang['Ban_email_explain'] = "Para especificar máis dun email, colóqueos separados por comas. Para especificar un comodín de usarios utilece *, por exemplo *@hotmail.com";

$lang['Unban_username'] = "Quitar ban dun ou varios Usuarios";
$lang['Unban_username_explain'] = "Pode quita-lo ban a múltiples usuarios dunha soa vez usando a combinación apropiada de rato e teclado para o seu ordenador e navegador.";

$lang['Unban_IP'] = "Quitar ban dunha ou varias Direccións IP";
$lang['Unban_IP_explain'] = "Vostede pode quita-lo ban a múltiples direccións IP usando a correcta combinación de mouse e teclado da súa computadora e navegador";

$lang['Unban_email'] = "Quitar ban dun ou varios enderezos de email";
$lang['Unban_email_explain'] = "Vostede pode quita-lo ban a múltiples enderezos de email usando a correcta combinación de mouse e teclado da súa computadora e navegador";

$lang['No_banned_users'] = "Non hai Usuarios baneados";
$lang['No_banned_ip'] = "Non hai direccións de IP baneadas";
$lang['No_banned_email'] = "Non hai enderezos de email baneados";

$lang['Ban_update_sucessful'] = "A lista de BAN foi actualizada correctamente";
$lang['Click_return_banadmin'] = "Prema %sAquí%s para volver ó Panel de Control de BANS";


//
// Configuración
//
$lang['General_Config'] = "Configuración Xeral";
$lang['Config_explain'] = "O seguiente formulario, permitiralle cambia-las opcións do seu foro. Para a configuración de Usuarios e Foros use os enlaces da esquerda.";

$lang['Click_return_config'] = "Prema %sAquí%s para volver á Configuración Xeral";

$lang['General_settings'] = "Configuración Xeral do Foro";
$lang['Server_name'] = "Nome de Dominio";
$lang['Server_name_explain'] = "O nome de dominio no que corre este Foro";
$lang['Script_path'] = "Ruta do Script";
$lang['Script_path_explain'] = "A ruta onde phpBB2 está ubicado, relativo ó nome de dominio";
$lang['Server_port'] = "Porto do Servidor";
$lang['Server_port_explain'] = "O porto no que corre o servidor, xeralmente 80. Só cambiar se difire deste valor.";
$lang['Site_name'] = "Nome do Sitio";
$lang['Site_desc'] = "Descrición do Sito";
$lang['Board_disable'] = "Desactivar Foro";
$lang['Board_disable_explain'] = "Esto fará que os Usuarios non teñan acceso ó Foro. Non se desloguee mentres desactiva o Foro, xa que no poderá volver loguearse novamente";
$lang['Acct_activation'] = "Debe activa-las contas";
$lang['Acc_None'] = "Ninguén"; // Os tres tipos de activación
$lang['Acc_User'] = "O Usuario";
$lang['Acc_Admin'] = "O Administrador";

$lang['Abilities_settings'] = "Configuración Básica de Usuario e do Foro";
$lang['Max_poll_options'] = "Número máximo de items en Enquisas";
$lang['Flood_Interval'] = "Intervalo de Flood";
$lang['Flood_Interval_explain'] = "Cantidade de segundos que o usuario debe agardar para publicar temas";
$lang['Board_email_form'] = "Email de Usuarios a través do Foro";
$lang['Board_email_form_explain'] = "Os usuarios envíanse emails mediante o Foro";
$lang['Topics_per_page'] = "Temas por Páxina";
$lang['Posts_per_page'] = "Respostas por Páxina";
$lang['Hot_threshold'] = "Cantidade de respostas para ser considerado Popular";
$lang['Default_style'] = "Estilo por defecto";
$lang['Override_style'] = "Ignora-lo estilo do Usuario";
$lang['Override_style_explain'] = "Utilizarase o estilo seleccionado por defecto sen importa-la elección do usuario";
$lang['Default_language'] = "Linguaxe por Defecto";
$lang['Date_format'] = "Formato da Data";
$lang['System_timezone'] = "Zona Horaria";
$lang['Enable_gzip'] = "Activa-la Compresión GZip";
$lang['Enable_prune'] = "Habilitar Purgado no Foro";
$lang['Allow_HTML'] = "Permitir HTML";
$lang['Allow_BBCode'] = "Permitir BBCode";
$lang['Allowed_tags'] = "Permitir HTML tags";
$lang['Allowed_tags_explain'] = "Separar tags con comas";
$lang['Allow_smilies'] = "Permitir Emoticonos";
$lang['Smilies_path'] = "Ruta de almacenaxe dos Emoticonos";
$lang['Smilies_path_explain'] = "Ruta desde o directorio phpBB, por exemplo images/smiles";
$lang['Allow_sig'] = "Permitir Sinaturas";
$lang['Max_sig_length'] = "Máxima lonxitude da Sinatura";
$lang['Max_sig_length_explain'] = "Máxima cantidade de caracteres da Sinatura";
$lang['Allow_name_change'] = "Permiti-lo Cambio do Nome de Usuario";

$lang['Avatar_settings'] = "Configuración dos Avatares";
$lang['Allow_local'] = "Habilitar galerías Avatares";
$lang['Allow_remote'] = "Habilitar Avatares Remotos";
$lang['Allow_remote_explain'] = "Permitir amosar Avatares gardados noutros sitios web";
$lang['Allow_upload'] = "Habilitar upload de Avatares";
$lang['Max_filesize'] = "Tamaño máximo para as imaxes";
$lang['Max_filesize_explain'] = "Limita-la cantidade de bytes que pode ter un Avatar";
$lang['Max_avatar_size'] = "Máximo Tamaño do Avatar";
$lang['Max_avatar_size_explain'] = "(Altura x Ancho en pixels)";
$lang['Avatar_storage_path'] = "Ruta do Avatar";
$lang['Avatar_storage_path_explain'] = "Ruta dentro de phpBB onde se atopan os Avatares, exemplo images/avatars";
$lang['Avatar_gallery_path'] = "Ruta da Galería de Avatares";
$lang['Avatar_gallery_path_explain'] = "Ruta dentro de phpBB da galería, ex: images/avatars/gallery";

$lang['COPPA_settings'] = "Configuracións COPPA";
$lang['COPPA_fax'] = "Número de Fax COPPA";
$lang['COPPA_mail'] = "Enderezo de Correo COPPA";
$lang['COPPA_mail_explain'] = "Este é o enderezo de correo onde os pais deben envia-lo formulario COPPA";

$lang['Email_settings'] = "Configuración do Email";
$lang['Admin_email'] = "Enderezo do email do Administrador";
$lang['Email_sig'] = "Sinatura";
$lang['Email_sig_explain'] = "Este texto engadirase ó final de cada email";
$lang['Use_SMTP'] = "Usar servidor SMTP para Email";
$lang['Use_SMTP_explain'] = "Diga se vostede pode e/ou debe envia-los emails por un servidor SMTP";
$lang['SMTP_server'] = "Dirección SMTP do Servidor";
$lang['SMTP_username'] = "Nome de usuario do SMTP";
$lang['SMTP_username_explain'] = "Ingrese un nome de usuario só se o seu servidor SMTP o require";
$lang['SMTP_password'] = "Contrasinal do SMTP";
$lang['SMTP_password_explain'] = "Ingrese un contrasinal só se o seu servidor SMTP o require";

$lang['Disable_privmsg'] = "Mensaxe Privada";
$lang['Inbox_limits'] = "Máxima cantidade de mensaxes na Bandexa de Entrada";
$lang['Sentbox_limits'] = "Máxima cantidade de mensaxes na Bandexa de Saída";
$lang['Savebox_limits'] = "Máxima cantidade de mensaxes na Carpeta para Gardar";

$lang['Cookie_settings'] = "Configuración das Cookies"; 
$lang['Cookie_settings_explain'] = "Isto controla como se envían as cookies ó Navegador, na meirande parte dos casos a configuración preestablecida será máis que suficiente. Se necesita cambiar isto teña coidado, xa que en caso de configuralo mal podería facer que os seus Usuarios non podan Ingresar ó Foro";
$lang['Cookie_domain'] = "Dominio da Cookie";
$lang['Cookie_name'] = "Nome da Cookie";
$lang['Cookie_path'] = "Ruta da Cookie";
$lang['Cookie_secure'] = "Cookie segura [ https ]";
$lang['Cookie_secure_explain'] = "Se o seu servidor está correndo vía SSL marque esta opción, se non déixeo deshabilitado";
$lang['Session_length'] = "Duración da sesión [ segundos ]";


//
// Xestión do Foro
//
$lang['Forum_admin'] = "Administración do Foro";
$lang['Forum_admin_explain'] = "Desde este panel vostede pode engadir, borrar, editar, e re-ordenar categorías e Foros";
$lang['Edit_forum'] = "Edita-lo Foro";
$lang['Create_forum'] = "Crear un novo Foro";
$lang['Create_category'] = "Crear unha nova Categoría";
$lang['Remove'] = "Quitar";
$lang['Action'] = "Acción";
$lang['Update_order'] = "Actualizar Orde";
$lang['Config_updated'] = "Configuración do Foro actualizada satisfactoriamente";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Borrar";
$lang['Move_up'] = "Cara arriba";
$lang['Move_down'] = "Cara abaixo";
$lang['Resync'] = "Sincronizar";
$lang['No_mode'] = "Ningún modo foi seleccionado";
$lang['Forum_edit_delete_explain'] = "O seguinte formulario permitiralle personalizar as opcións do Foro. Para a configuración de usuarios e Foros utilice os enlaces da esquerda.";

$lang['Move_contents'] = "Mover tódolos contidos";
$lang['Forum_delete'] = "Borra-lo Foro";
$lang['Forum_delete_explain'] = "O seguinte formulario permitiralle Borrar algún foro (ou categoría) e dicir donde desexa colocar tódolos Tópicos e Categorías.";
$lang['Status_locked'] = 'Pechado';
$lang['Status_unlocked'] = 'Aberto';

$lang['Forum_settings'] = "Configuración Xeral do Foro";
$lang['Forum_name'] = "Nome do Foro";
$lang['Forum_desc'] = "Descrición";
$lang['Forum_status'] = "Estado do Foro";
$lang['Forum_pruning'] = "Auto-purgado";

$lang['prune_freq'] = 'Chequear temas e idade';//Ver Pruning!
$lang['prune_days'] = "Quitar Temas que non teñen resposta";
$lang['Set_prune_data'] = "Vostede seleccionou a opción Auto-purgado para este foro pero non seleccionou a frecuencia ou cantidade de días para o PURGADO. Por favor regrese e efectúe os cambios";

$lang['Move_and_Delete'] = "Mover e Borrar";

$lang['Delete_all_posts'] = "Borrar tódolos Temas";
$lang['Nowhere_to_move'] = "Ningún sitio a onde mover";

$lang['Edit_Category'] = "Editar Categoría";
$lang['Edit_Category_explain'] = "Utilice este formulario para Editar categorías";

$lang['Forums_updated'] = "A información do Foro e as súas categorías de foros actualizadas satisfactoriamente";

$lang['Must_delete_forums'] = "Necesita Borrar tódolos foros antes de borrar unha Categoría";

$lang['Click_return_forumadmin'] = "Prema %sAquí%s para volver á Administración do Foro";


//
// Xestión de Smileis
//
$lang['smiley_title'] = "Utilidade para a edición de Smileis";
$lang['smile_desc'] = "Desde esta páxina vostede pode engadir, quitar ou editar algún emoticono para que os Usuarios utilicen no foro e nas mensaxes Privadas";

$lang['smiley_config'] = "Configuración de Smilei";
$lang['smiley_code'] = "Código de Smilei";
$lang['smiley_url'] = "Arquivo de Imaxe do Smilei";
$lang['smiley_emot'] = "Emoción do Smilei";
$lang['smile_add'] = "Engadir un novo Smilei";
$lang['Smile'] = "Smilei";
$lang['Emotion'] = "Emoción";

$lang['Select_pak'] = "Seleccione o arquivo .pak";
$lang['replace_existing'] = "Reemprazar Smileis Existentes";
$lang['keep_existing'] = "Manter Smileis Existentes";
$lang['smiley_import_inst'] = "Vostede debe descomprimi-lo paquete de Smileis e subir tódolos arquivos no directorio de Smileis para así lograr a súa correcta instalación. Despois seleccione a información correcta desde este formulario para así poder importa-los Smileis";
$lang['smiley_import'] = "Importar paquete de Smileis";
$lang['choose_smile_pak'] = "Escoller arquivo de paquete (.pak)";
$lang['import'] = "Importar Smileis";
$lang['smile_conflicts'] = "Que se debería realizar en caso de conflicto";
$lang['del_existing_smileys'] = "Borra-los smileis existentes antes de importalos";
$lang['import_smile_pack'] = "Importar Paquete de Smileis";
$lang['export_smile_pack'] = "Crear un paquete de Smileis";
$lang['export_smiles'] = "Para crear un paquete de Smileis dos seus smileis instalados, prema %sAquí%s para baixa-lo arquivo smiles.pak. Nomee este arquivo de forma apropiada pero asegúrese de manter a extension .pak. Despois cree un arquivo zip que conteña tódolos smileis mailo arquivo .pak.";

$lang['smiley_add_success'] = "Os Smileis foron engadidos satisfactoriamente";
$lang['smiley_edit_success'] = "Os Smileis foron actualizados satisfactoriamente ";
$lang['smiley_import_success'] = "O paquete de Smileis foi importado correctamente.";
$lang['smiley_del_success'] = "Os Smileis foron eliminados satisfactoriamente.";
$lang['Click_return_smileadmin'] = "Prema %sAquí%s para volver ó Panel de Smiles";


//
// Xestión de Usuarios
//
$lang['User_admin'] = "Administración de Usuarios";
$lang['User_admin_explain'] = "Desde aquí vostede pode cambia-la información do usuario. Para modifica-los permisos dun Usuario por favor utilice o Sistema de Permisos de usuarios e Grupos.";

$lang['Look_up_user'] = "Observar Usuario";

$lang['Admin_user_fail'] = "Non se logrou actualiza-lo perfil do Usuario";
$lang['Admin_user_updated'] = "O perfil do Usuario foi actualizado satisfactoriamente";
$lang['Click_return_useradmin'] = "Prema %sAquí%s para voltar ó Panel de Administración de Usuarios";

$lang['User_delete'] = "Borrar Usuario";
$lang['User_delete_explain'] = "Prema aquí para borrar este Usuario. Teña en conta que logo non poderá restauralo.";
$lang['User_deleted'] = "O Usuario foi borrado satisfactoriamente.";

$lang['User_status'] = "Usuario Activo";
$lang['User_allowpm'] = "Pode enviar mensaxes privadas";
$lang['User_allowavatar'] = "Pode mostra-lo seu Avatar";

$lang['Admin_avatar_explain'] = "Desde aquí pode ver e borrar-lo Avatar do Usuario";

$lang['User_special'] = "Campos especiais para Administradores";
$lang['User_special_explain'] = "Estos campos non están dispoñibles para que os Usuarios os modifiquen. Desde aquí vostede pode configura-lo status e outras opcións que os Usuarios non poden modificar.";


//
// Xestión de Grupos
//
$lang['Group_administration'] = "Administración de Grupos";
$lang['Group_admin_explain'] = "Desde este panel pode modifica-los Grupos, vostede pode borrar, crear e edita-los Grupos existentes. Tamén pode escolle-los Moderadores e cambia-lo nome do Grupo e a sua descrición";
$lang['Error_updating_groups'] = "Ocurreu un erro actualizando o Grupo";
$lang['Updated_group'] = "O Grupo foi actualizado correctamente";
$lang['Added_new_group'] = "O Novo Grupo foi creado";
$lang['Deleted_group'] = "O Grupo foi borrado";
$lang['New_group'] = "Crear Novo Grupo";
$lang['Edit_group'] = "Editar Grupo";
$lang['group_name'] = "Nome do Grupo";
$lang['group_description'] = "Descrición do Grupo";
$lang['group_moderator'] = "Moderador do Grupo";
$lang['group_status'] = "Estado do Grupo";
$lang['group_open'] = "Grupo Aberto";
$lang['group_closed'] = "Grupo Pechado";
$lang['group_hidden'] = "Grupo Oculto";
$lang['group_delete'] = "Borrar Grupo";
$lang['group_delete_check'] = "Borrar este Grupo";
$lang['submit_group_changes'] = "Aceptar Cambios";
$lang['reset_group_changes'] = "Anular Cambios";
$lang['No_group_name'] = "Debe especificar un Nome para este Grupo";
$lang['No_group_moderator'] = "Debe especificar un Moderador para este Grupo";
$lang['No_group_mode'] = "Debe especifica-lo modo deste Grupo, Aberto ou Pechado";
$lang['delete_group_moderator'] = "¿Borrar-lo antigo moderador do Grupo?";
$lang['delete_moderator_explain'] = "Se está cambiando o moderador do Grupo, seleccione esta cela para quita-lo antigo Moderador do Grupo. Se nón o Usuario convertirase nun membro regular.";
$lang['Click_return_groupsadmin'] = "Prema %sAquí%s para volver ó Panel de Administración de Grupos.";
$lang['Select_group'] = "Seleccione un Grupo";
$lang['Look_up_group'] = "Observar un Grupo";
$lang['No_group_action'] = 'Non se especificou unha acción'; 


//
// Administración do Purgado
//
$lang['Forum_Prune'] = "Purgado de Foros";
$lang['Forum_Prune_explain'] = "Isto borrará tódolos tópicos nos que non se teñan publicado novas mensaxes nos días que vostede seleccionou. Se non ingresa un número entón tódolos temas serán borrados. No se borrarán temas nos que haxa enquisas que estean activas nin anuncios. Terá que quitar estes temas de xeito manual.";
$lang['Do_Prune'] = "Realiza-lo Purgado";
$lang['All_Forums'] = "Tódolos Foros";
$lang['Prune_topics_not_posted'] = "Borrar temas sen respostas dunha antigüidade destes días";
$lang['Topics_pruned'] = "Tópicos borrados";
$lang['Posts_pruned'] = "Mensaxes borradas";
$lang['Prune_success'] = "O Purgado dos foros foi exitoso";


//
// Censor de palabras
//
$lang['Words_title'] = "Control de Palabras Censuradas";
$lang['Words_explain'] = "Desde aquí vostede pode engadir, editar, e quitar palabras que automaticamente serán censuradas dos seus foros. Estas palabras non poderán ser escollidas como nomes de usuarios. Os asteriscos (*) son aceptados nos campos das palabras, exemplo *proba* , proba* (collería probao), *proba (collería aproba).";
$lang['Word'] = "Palabra";
$lang['Edit_word_censor'] = "Edita-lo Censor de Palabras";
$lang['Replacement'] = "Reemprazar";
$lang['Add_new_word'] = "Engadir nova palabra";
$lang['Update_word'] = "Actualizar censor de palabras";

$lang['Must_enter_word'] = "Debe colocar unha palabra e o seu reemprazo";
$lang['No_word_selected'] = "Non se seleccionou unha palabra para o reemprazo";

$lang['Word_updated'] = "Realizáronse os cambios satisfactoriamente";
$lang['Word_added'] = "A nova palabra foi engadida con éxito";
$lang['Word_removed'] = "A palabra foi retirada do Censor de Palabras";

$lang['Click_return_wordadmin'] = "Prema %sAquí%s para volver ó Administrador de Palabras Censuradas";


//
// Email Masivo
//
$lang['Mass_email_explain'] = "Desde aquí vostede pode enviar mensaxes de email a tódolos seus Usuarios e Grupos. Ó facer isto enviarase un email desde o email administrativo inidicado previamente. Se envía este email a un Grupo numeroso por favor sexa paciente e agarde a que remate de carga-la páxina. É normal que tarde uns cantos minutos, vostede será notificado unha vez finalizado o envío.";
$lang['Compose'] = "Escribir"; 

$lang['Recipients'] = "Emails"; 
$lang['All_users'] = "A tódolos Usuarios";

$lang['Email_successfull'] = "O seu email foi enviado";
$lang['Click_return_massemail'] = "Prema %sAquí%s para volver ó Panel para Enviar emails Masivos";


//
// Administración de Rangos
//
$lang['Ranks_title'] = "Administración de Rangos";
$lang['Ranks_explain'] = "Usando este formulario vostede pode engadir, editar, ver e borrar rangos. Vostede tamén pode crear rangos especiais que poden ser aplicados a un usuario a traves do panel de Administración de Usuarios";

$lang['Add_new_rank'] = "Engadir Rango";

$lang['Rank_title'] = "Título do Rango";
$lang['Rank_special'] = "Seleccionar como Rango Especial";
$lang['Rank_minimum'] = "Mínima cantidade de Mensaxes";
$lang['Rank_maximum'] = "Máxima cantidade de Mensaxes";
$lang['Rank_image'] = "Imáxe do rango (teña en conta a ruta do foro phpBB2)";
$lang['Rank_image_explain'] = "Utilice isto para definir unha pequena imaxe para este rango";

$lang['Must_select_rank'] = "Debe seleccionar un Rango";
$lang['No_assigned_rank'] = "Non seleccionou un Rango";

$lang['Rank_updated'] = "O Rango foi actualizado";
$lang['Rank_added'] = "O novo Rango foi engadido";
$lang['Rank_removed'] = "O Rango foi borrado";
$lang['No_update_ranks'] = "O Rango foi borrado. Sin embargo, as contas de usuario que estivesen usando este rango non foron actualizadas. Necesitará actualizar manualmente esas contas";


$lang['Click_return_rankadmin'] = "Prema %sAquí%s para volver ó Panel de Administración de Rangos";


//
// Prohibir nome de usuario
//
$lang['Disallow_control'] = "Control de Admisión de Usuario";
$lang['Disallow_explain'] = "Desde aquí pode controla-los nomes de usuario que desexa que non sexan utilizados. Para lograr isto debe utilizar asteriscos coma comodíns (*). Lembre que non pode prohibir nomes de usuario que xa se estean a utilizar. Antes de prohibir eses nomes debe borra-los usuarios que os usen.";

$lang['Delete_disallow'] = "Borrar";
$lang['Delete_disallow_title'] = "Borrar un nome de usuario non permitido";
$lang['Delete_disallow_explain'] = "Vostede pode quitar nomes de usuario non permitidos seleccionando o nome de usuario da lista e facendo click en Aceptar";

$lang['Add_disallow'] = "Engadir";
$lang['Add_disallow_title'] = "Engadir un nome de usuario non permitido";
$lang['Add_disallow_explain'] = "Vostede pode non permitir un nome de usuario utilizando máscaras con asteriscos(*)";

$lang['No_disallowed'] = "Nomes de usuarios non permitidos";

$lang['Disallowed_deleted'] = "O nome de usuario non permitido foi borrado";
$lang['Disallow_successful'] = "O nome de usuario non permitido foi engadido";
$lang['Disallowed_already'] = "O nome de usuario non permitido que seleccionou non pode ser seleccionado. Debido a que xa existe na lista, ou existe na Lista de Palabras Censuradas, ou ben xa se encontra na lista de usuarios non permitidos";

$lang['Click_return_disallowadmin'] = "Prema %saquí%s para volver ó Control de Admisión de Usuario";


//
// Administración de Estilo
//
$lang['Styles_admin'] = "Administración de Estilos";
$lang['Styles_explain'] = "Desde aquí vostede pode doadamente engadir, quitar e administra-los estilos (plantillas e temas) dispoñibles para os seus usuarios";
$lang['Styles_addnew_explain'] = "A seguinte lista contén tódolos temas que están dispoñibles para as plantillas. Os items da lista non foron instalados na base dos foros phpBB. Para facelo simplemente faga clic no enlace que figura ó lado de cada opción";

$lang['Select_template'] = "Seleccione unha Plantilla";

$lang['Style'] = "Estilo";
$lang['Template'] = "Plantilla";
$lang['Install'] = "Instalar";
$lang['Download'] = "Descargar";

$lang['Edit_theme'] = "Editar Tema";
$lang['Edit_theme_explain'] = "No seguinte formulario pode edita-la configuración do tema seleccionado";

$lang['Create_theme'] = "Crear Tema";
$lang['Create_theme_explain'] = "Utilice o seguinte formulario para crear un tema novo para a plantilla seleccionada. Cando ingrese as cores (as cales debe ingresar de forma hexadecimal) non debe inclui-la # . Exemplo: CCCCCC é válido, non así #CCCCCC";

$lang['Export_themes'] = "Exportar Tema";
$lang['Export_explain'] = "Desde este panel vostede poderá exporta-lo tema para a plantilla seleccionada. Seleccione a plantilla da lista de abaixo e o programa creará o arquivo de configuración do tema e así poderá gardalo. Se non se pode grava-lo arquivo brindaráselle a vostede a oportunidad de Descargalo. Para que o programa poda gardalo vostede debe dar permiso de escritura á carpeta de plantillas (template). Para máis información utilice a guía do foro phpBB 2";

$lang['Theme_installed'] = "O tema seleccionado foi instalado correctamente";
$lang['Style_removed'] = "O estilo seleccionado foi borrado da base de datos. Para borralo completamente debe borra-lo directorio apropiado do directorio de plantillas (template)";
$lang['Theme_info_saved'] = "A información para a plantilla seleccionada foi gardada. Agora debe restablece-los permisos en theme_info.cgf e poñe-lo directorio de plantillas (template) en modo de só-lectura";
$lang['Theme_updated'] = "O tema seleccionado foi actualizado. Agora debe exporta-la configuración do novo tema";
$lang['Theme_created'] = "Tema Creado. Agora debe exporta-lo tema no arquivo de configuración de temas para así lograr mantelo seguro";

$lang['Confirm_delete_style'] = "Está seguro que desexa borrar este estilo";

$lang['Download_theme_cfg'] = "Non se puido exportar-lo arquivo xa que non se puido escribir no arquivo. Prema o botón de embaixo para descargar este arquivo co seu navegador. Unha vez que o descargue pode movelo ó directorio das plantillas (template).";
$lang['No_themes'] = "A plantilla que seleccionou non ten temas adxuntos. Para crear un novo tema prema en 'Crear Novo Tema' na esquerda do panel";
$lang['No_template_dir'] = "Non se logrou abri-la carpeta de plantillas. Isto pode deberse a que estea con permisos sen lectura, ou que esta non exista.";
$lang['Cannot_remove_style'] = "Non pode quita-lo estilo seleccionado xa que é o que está por defecto no foro. Por favor cambie o que se utiliza por defecto e ténteo novamente";
$lang['Style_exists'] = "O nome de estilo seleccionado xa existe, por favor volte atrás e seleccione outro distinto";

$lang['Click_return_styleadmin'] = "Prema %sAquí%s para volver á Administración de Estilos";

$lang['Theme_settings'] = "Configuración de Temas";
$lang['Theme_element'] = "Elementos de Temas";
$lang['Simple_name'] = "Nome simple";
$lang['Value'] = "Valor";
$lang['Save_Settings'] = "Gardar Configuración";

$lang['Stylesheet'] = "Folla de estilos CSS";
$lang['Background_image'] = "Imaxe de Fondo";
$lang['Background_color'] = "Cor de Fondo";
$lang['Theme_name'] = "Nome de Tema";
$lang['Link_color'] = "Cor de Link";
$lang['Text_color'] = "Cor de Texto";
$lang['VLink_color'] = "Cor de Link Visitado";
$lang['ALink_color'] = "Cor de Link Activo";
$lang['HLink_color'] = "Cor de Link Hover";
$lang['Tr_color1'] = "Táboa Fila Cor 1";
$lang['Tr_color2'] = "Táboa Fila Cor 2";
$lang['Tr_color3'] = "Táboa Fila Cor 3";
$lang['Tr_class1'] = "Táboa Fila Clase 1";
$lang['Tr_class2'] = "Táboa Fila Clase 2";
$lang['Tr_class3'] = "Táboa Fila Clase 3";
$lang['Th_color1'] = "Táboa Encabezado Cor 1";
$lang['Th_color2'] = "Táboa Encabezado Cor 2";
$lang['Th_color3'] = "Táboa Encabezado Cor 3";
$lang['Th_class1'] = "Táboa Encabezado Clase 1";
$lang['Th_class2'] = "Táboa Encabezado Clase 2";
$lang['Th_class3'] = "Táboa Encabezado Clase 3";
$lang['Td_color1'] = "Táboa Cela Cor 1";
$lang['Td_color2'] = "Táboa Cela Cor 2";
$lang['Td_color3'] = "Táboa Cela Cor 3";
$lang['Td_class1'] = "Táboa Cela Clase 1";
$lang['Td_class2'] = "Táboa Cela Clase 2";
$lang['Td_class3'] = "Táboa Cela Clase 3";
$lang['fontface1'] = "Fonte 1";
$lang['fontface2'] = "Fonte 2";
$lang['fontface3'] = "Fonte 3";
$lang['fontsize1'] = "Fonte Tamaño 1";
$lang['fontsize2'] = "Fonte Tamaño 2";
$lang['fontsize3'] = "Fonte Tamaño 3";
$lang['fontcolor1'] = "Fonte Cor 1";
$lang['fontcolor2'] = "Fonte Cor 2";
$lang['fontcolor3'] = "Fonte Cor 3";
$lang['span_class1'] = "Espacio Clase 1";
$lang['span_class2'] = "Espacio Clase 2";
$lang['span_class3'] = "Espacio Clase 3";
$lang['img_poll_size'] = "Imaxe da Enquisa [px]";
$lang['img_pm_size'] = "Tamaño de imaxe de Mensaxes Privadas [px]";


//
// Proceso de Instalación
//
$lang['Welcome_install'] = "Benvido á Instalación dos foros phpBB 2";
$lang['Initial_config'] = "Configuración Básica";
$lang['DB_config'] = "Configuración da Base de Datos";
$lang['Admin_config'] = "Configuración do Administrador";
$lang['continue_upgrade'] = "Unha vez que descargue o arquivo de configuración prema sobre \"Continuar Actualización\" para continuar co proceso. Por favor agarde para subi-lo arquivo de configuración ata que o proceso de actualización finalice";
$lang['upgrade_submit'] = "Continuar Actualización";

$lang['Installer_Error'] = "Ocurreu un erro durante a Instalación";
$lang['Previous_Install'] = "Non se detectou unha Instalación previa";
$lang['Install_db_error'] = "Ocurreu un erro actualizando a Base de Datos";

$lang['Re_install'] = "A súa instalación previa aínda se atopa activa. <br /><br />Se desexa reinstala-los foros phpBB 2 prema Si no boton de abaixo. Por favor teña en conta que ó realizar isto destruirase a información existente, non se farán copias de seguridade. O usuario administrador e o contrasinal que vostede usaba anteriormente serán creados novamente, mais non outro tipo de información <br /><br />¡Pénseo coidadosamente antes de premer SI!";

$lang['Inst_Step_0'] = "Gracias por elixir phpBB 2. Para finaliza-la instalación por favor complete os datos requiridos embaixo. Teña en conta que a Base de Datos destinada ós foros xa debería existir. Se está instalando nunha Base de Datos que utiliza OBDC, por exemplo MS Access primeiro deberá crear un DNS e despois continuar.";

$lang['Start_Install'] = "Comezar Instalación";
$lang['Finish_Install'] = "Finalizar Instalación";

$lang['Default_lang'] = "Linguaxe por defecto";
$lang['DB_Host'] = "Nome de Dominio da Base de Datos / DSN";
$lang['DB_Name'] = "Nome da súa base de Datos";
$lang['DB_Username'] = "Nome de usuario da base de datos";
$lang['DB_Password'] = "Contrasinal da base de datos";
$lang['Database'] = "A súa Base de Datos";
$lang['Install_lang'] = "Elixa a Linguaxe de Instalación";
$lang['dbms'] = "Tipo de Base de Datos";
$lang['Table_Prefix'] = "Prefixo para táboas na Base de datos";
$lang['Admin_Username'] = "Nome de Usuario do Administrador";
$lang['Admin_Password'] = "Contrasinal do Administrador";
$lang['Admin_Password_confirm'] = "Contrasinal de acceso do Administrador [ Confirma ]";

$lang['Inst_Step_2'] = "O seu usuario administrador e contrasinal foron creados. Neste punto o proceso de Instalación Básica foi completado. Agora será enviado a unha pantalla que lle permitirá administra-la nova instalación. Por favor asegúrese de verifica-la Configuración Xeral e de ter realizado os cambios requiridos. Gracias por escoller phpBB 2";

$lang['Unwriteable_config'] = "O seu arquivo de configuración esta nun modo de non-escritura. Unha copia do arquivo de configuración poderá ser descargada cando prema o botón seguinte. Vostede debe subir este arquivo no mesmo directorio onde se atope o foro phpBB 2. Unha vez que isto se realice debe ingresar usando o usuario de administrador e contrasinal que vostede escolleu no formulario anterior e así visita-lo Control de Administración para ve-la configuración xeral. Gracias por escoller phpBB 2";
$lang['Download_config'] = "Descargar Configuración";

$lang['ftp_choose'] = "Escoller Método de Descarga";
$lang['ftp_option'] = "<br />Xa que as extensións FTP están dispoñibles nesta versión de PHP vostede poderá escoller se quere, mediante FTP, colocar o arquivo no seu lugar automaticamente.";
$lang['ftp_instructs'] = "Vostede seleccionou subir automaticamente por ftp o arquivo na conta que contén o phpBB 2. Por favor ingrese a información solicitada para facilita-lo proceso. Teña en conta que a ruta FTP debe se-la ruta exacta ó PHPBB 2 como se fose a subi-los arquivos usando calquera cliente de ftp.";
$lang['ftp_info'] = "Ingrese a Información do su FTP";
$lang['Attempt_ftp'] = "Tentar subi-lo arquivo mediante ftp de forma automática";
$lang['Send_file'] = "Envíenme o arquivo a min e eu o subireino persoalmente por FTP";
$lang['ftp_path'] = "Ruta FTP ó phpBB 2";
$lang['ftp_username'] = "Nome de Usuario FTP";
$lang['ftp_password'] = "Contrasinal FTP";
$lang['Transfer_config'] = "Iniciar Transferencia";
$lang['NoFTP_config'] = "O intento de subir por ftp o arquivo de configuración errou. Por favor descargue o arquivo de configuración e súbao por FTP de forma manual.";

$lang['Install'] = "Instalar";
$lang['Upgrade'] = "Actualizar";


$lang['Install_Method'] = "Escolla o seu método de Instalacion";

$lang['Install_No_Ext'] = "A configuración de PHP no seu servidor non soporta o tipo de base de datos seleccionado";

$lang['Install_No_PCRE'] = "phpBB2 require o módulo de expresións regulares compatible con Perl para php, que non figura como soportado na súa configuración de php!";

//
// Xa chegou eh!!
// -------------------------------------------------

?>