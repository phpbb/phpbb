<?php
/***************************************************************************
 *                            lang_admin.php [Argentinean Spanish]
 *                              -------------------
 *     begin                : Wed Jul 24 2002
 *     copyright            : Angel Olivera
 *     e-mail               : aolivera@softhome.net
 *     location             : Mendoza, Argentina
 *
 *
 *     modified from Spanish language by:
 *
 *                          Daniel González Cuellar (webmaster@ba-k.com)
 *   			            Mariano Martene (correo@webfactory.com)
 *                          Patricio Marín (pmarin@hotmail.com)
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


// Gracias a los traductores al idioma Español, éste es un archivo modificado para
// adecuar el lenguaje al Español utilizado en la República Argentina.
// Creí conveniente crear el lenguaje ya que phpBB es muy utilizado en mi país.
//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Administración General";
$lang['Users'] = "Administración de los Usuarios";
$lang['Groups'] = "Administración de los Grupos";
$lang['Forums'] = "Administración de los Foros";
$lang['Styles'] = "Administración de los Estilos";

$lang['Configuration'] = "Configuración";
$lang['Permissions'] = "Permisos";
$lang['Manage'] = "Management";
$lang['Disallow'] = "Deshabilitar nombres de usuario";
$lang['Prune'] = "Purgar";
$lang['Mass_Email'] = "Correo Masivo";
$lang['Ranks'] = "Rangos";
$lang['Smilies'] = "Emoticons";
$lang['Ban_Management'] = "Control de Exclusion";
$lang['Word_Censor'] = "Palabras Censuradas";
$lang['Export'] = "Exportar";
$lang['Create_new'] = "Crear";
$lang['Add_new'] = "Agregar";
$lang['Backup_DB'] = "Backup de la Base de Datos";
$lang['Restore_DB'] = "Restaurar la Base de Datos";


//
// Index
//
$lang['Admin'] = "Administración";
$lang['Not_admin'] = "No estás autorizado para administrar este Foro";
$lang['Welcome_phpBB'] = "Bienvenido a phpBB";
$lang['Admin_intro'] = "Gracias por elegir phpBB como tu solución para tu foro. Esta pantalla te dará una síntesis de las principales estadísticas de tu foro. Podés volver a esta página clickeando en el link de <u>Índice del Administrador</u> en el panel de la izquierda. Para regresar al índice de tu foro, clickeá el logo de phpBB también ubicado en el panel izquierdo. Los otros links ubicados a la izquierda de esta pantalla te permitirán controlar todos los aspectos de este foro, cada pantalla tendrá instrucciones sobre cómo utilizar las herramientas.";
$lang['Main_index'] = "Índice del Foro";
$lang['Forum_stats'] = "Estadísticas del Foro";
$lang['Admin_Index'] = "Índice del Administrador";
$lang['Preview_forum'] = "Vista previa del Foro";

$lang['Click_return_admin_index'] = "click %sacá%s para volver al Índice del Administrador";

$lang['Statistic'] = "Estadísticas";
$lang['Value'] = "Valor";
$lang['Number_posts'] = "Cantidad de envíos";
$lang['Posts_per_day'] = "Envíos por día";
$lang['Number_topics'] = "Cantidad de topics";
$lang['Topics_per_day'] = "Topics por día";
$lang['Number_users'] = "Cantidad de usuarios";
$lang['Users_per_day'] = "Usuarios por día";
$lang['Board_started'] = "Fecha de inicio del Foro";
$lang['Avatar_dir_size'] = "Tamaño del directorio de Imágenes";
$lang['Database_size'] = "Tamaño de la Base de Datos";
$lang['Gzip_compression'] ="Tipo de Compresión Gzip";
$lang['Not_available'] = "No está disponible";

$lang['ON'] = "ON"; // This is for GZip compression
$lang['OFF'] = "OFF"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Utilitarios de la Base de Datos";

$lang['Restore'] = "Restaurar";
$lang['Backup'] = "Backup";
$lang['Restore_explain'] = "Esto restaurará todas las tablas de phpBB desde un archivo previamente guardado. Si tu servidor lo soporta, podés subir un archivo de texto comprimido mediante gzip y éste será automáticamente descomprimido. <b>ATENCION</b> Esto sobre-escribirá la información existente. La restauración puede durar unos minutos, por favor quedáte en esta página hasta que el proceso se haya completado.";
$lang['Backup_explain'] = "Desde aquí podés hacer una copia de seguridad (backup) de toda la información relacionada con phpBB. Si tenés tablas adicionales en la misma Base de Datos de las que quisieras realizar un backup ingresá sus nombres separados por comas en el campo de Tablas Adicionales. Si tu servidor lo soporta podés utilizar gzip para comprimir el archivo y reducir su tamaño antes de descargarlo.";

$lang['Backup_options'] = "Opciones del Backup";
$lang['Start_backup'] = "Comenzar el Backup";
$lang['Full_backup'] = "Backup completo";
$lang['Structure_backup'] = "Sólo la Estructura";
$lang['Data_backup'] = "Sólo los Datos";
$lang['Additional_tables'] = "Tablas adicionales";
$lang['Gzip_compress'] = "Compresión en un archivo Gzip";
$lang['Select_file'] = "Seleccionar un archivo";
$lang['Start_Restore'] = "Comenzar la Restauración";

$lang['Restore_success'] = "La Base de Datos ha sido Restaurada.<br /><br />Tu Foro debería volver a la normalidad una vez realizado el proceso.";
$lang['Backup_download'] = "Por favor esperá un momento hasta que empiece la descarga";
$lang['Backups_not_supported'] = "La opcion de backup de tu Base de Datos no está soportada por tu sistema";

$lang['Restore_Error_uploading'] = "Error subiendo el archivo backup";
$lang['Restore_Error_filename'] = "Error en el nombre de archivo, por favor intentálo con un archivo diferente";
$lang['Restore_Error_decompress'] = "No se puede descomprimir un archivo gzip, subílo en una version de texto";
$lang['Restore_Error_no_file'] = "Ningún archivo ha sido subido";


//
// Auth pages
//
$lang['Select_a_User'] = "Seleccionar un Usuario";
$lang['Select_a_Group'] = "Seleccionar un Grupo";
$lang['Select_a_Forum'] = "Seleccionar un Foro";
$lang['Auth_Control_User'] = "Control de Permisos a los Usuario"; 
$lang['Auth_Control_Group'] = "Control de Permisos a los Grupos"; 
$lang['Auth_Control_Forum'] = "Control de Permisos a los Foros"; 
$lang['Look_up_User'] = "Observar un Usuario";
$lang['Look_up_Group'] = "Observar un Grupo"; 
$lang['Look_up_Forum'] = "Observar un Foro"; 

$lang['Group_auth_explain'] = "Desde acá podés cambiar los permisos y el estado del moderador asignado a cada grupo de usuarios. Acordáte que cambiando los permisos del Grupo, los permisos individuales cambiarán una vez que la persona ingrese al foro. Serás advertido en este caso.";
$lang['User_auth_explain'] = "Desde acá podés cambiar los permisos y estado del moderador asignado a cada usuario. Tené presente que cambiando los permisos de un usuario los permisos del grupo permanecerán sin cambiar hasta que el usuario entre a los foros. Serás advertido en este caso.";
$lang['Forum_auth_explain'] = "Desde acá podés cambiar los niveles de autorización de cada foro. Para realizar esto tenés dos modos; uno simple y otro avanzado. Este último te brindará mayor control para la operación y el funcionamiento de cada foro. Tené en cuenta que al cambiar los niveles de cada foro podés afectar el funcionamiento de cada usuario según el foro y los permisos que tenga el usuario.";

$lang['Simple_mode'] = "Modalidad Simple";
$lang['Advanced_mode'] = "Modalidad Avanzada";
$lang['Moderator_status'] = "Estado del Moderador";

$lang['Allowed_Access'] = "Acseso Permitido";
$lang['Disallowed_Access'] = "Acseso no Permitido";
$lang['Is_Moderator'] = "con Moderador";
$lang['Not_Moderator'] = "sin Moderador";

$lang['Conflict_warning'] = "Advertencia de Conflicto en Autorización";
$lang['Conflict_access_userauth'] = "Este usuario todavía tiene acceso a este foro debido al Grupo al cual pertenece. Tenés que cambiar los permisos del grupo o borrar al usuario del Grupo para preveer que el usuario no tenga acceso a este foro. Los derechos del Grupo y el Usuario se explican abajo.";
$lang['Conflict_mod_userauth'] = "Este usuario todavía tiene derechos de Moderador a través de un Grupo al cual pertence. Tenés que cambiar los permisos del grupo o borrar al usuario de dicho Grupo para estar seguro que este usuario no tenga acceso al foro con permisos de Moderar. Los derechos se explican abajo.";

$lang['Conflict_access_groupauth'] = "El siguiente usuario(s) todavía tiene(n) acceso a este foro debido a los permisos que tiene(n) como Usuario. Para que no tenga(n) acceso a este foro tenés que cambiar sus permisos. Los derechos de Usuarios se explican abajo.";
$lang['Conflict_mod_groupauth'] = "El siguiente usuario(s) todavía tiene derechos de Moderador en este foro. Para que no tenga(n) acceso a este foro tenés que cambiar sus permisos de usuario. Los derechos de Usuarios se explican abajo.";

$lang['Public'] = "Público";
$lang['Private'] = "Privado";
$lang['Registered'] = "Registrado";
$lang['Administrators'] = "Administrador";
$lang['Hidden'] = "Oculto";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "TODOS";
$lang['Forum_REG'] = "REG";
$lang['Forum_PRIVATE'] = "PRIVADOS";
$lang['Forum_MOD'] = "MOD";
$lang['Forum_ADMIN'] = "ADMIN";

$lang['View'] = "Ver";
$lang['Read'] = "Leer";
$lang['Post'] = "Envio";
$lang['Reply'] = "Respuesta";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Borrar";
$lang['Sticky'] = "PostIt";
$lang['Announce'] = "Anuncio";
$lang['Vote'] = "Votar";
$lang['Pollcreate'] = "Crear una encuesta";

$lang['Permissions'] = "Permisos";
$lang['Simple_Permission'] = "Permiso Simple";

$lang['User_Level'] = "Nivel de Usuario";
$lang['Auth_User'] = "Usuario";
$lang['Auth_Admin'] = "Administrador";
$lang['Group_memberships'] = "Grupo de Usuarios";
$lang['Usergroup_members'] = "Este Grupo contiene a los siguientes Usuarios";

$lang['Forum_auth_updated'] = "Permiso del Foro actualizado";
$lang['User_auth_updated'] = "Permiso del usuario actualizado";
$lang['Group_auth_updated'] = "Permiso del Grupo actualizado";

$lang['Auth_updated'] = "los permisos han sido cambiados";
$lang['Click_return_userauth'] = "Click %sacá%s para vovler a ver los Permisos de los Usuarios";
$lang['Click_return_groupauth'] = "Click %sacá%s para vovler a ver los Permisos del Grupo";
$lang['Click_return_forumauth'] = "Click %sacá%s para vovler a ver los Permisos del Foro";


//
// Banning
//
$lang['Ban_control'] = "Control de Exclusión";
$lang['Ban_explain'] = "Desde acá podés banear a un usuario. Podés banear a los usuarios por su nombre, por su dirección IP, o por su Hostname. Este método previene que un usuario tenga acceso a la página principal del foro. Para prevenir que el Usuario se registre con una nueva cuenta también podés bannear su dirección de e-mail. Tené en cuenta que baneando una dirección de email no evitarás que el usuario pueda ingresar al foro ni que publique mensajes. Para hacer esto eso tenés que usar uno de los dos métodos explicados anteriormente.";
$lang['Ban_explain_warn'] = "Tené en cuenta que colocando un RANGO de direcciones IP. baneás a todas las direcciones que se encuentran dentro del Rango de la lista de bans.  Si realmente tenés que usar un rango intentá usar uno pequeño para así no banear a otros usuarios.";

$lang['Select_username'] = "Selecioná un Nombre de Usuario";
$lang['Select_ip'] = "Seleccioná una dirección IP";
$lang['Select_email'] = "Seleccioná una dirección de E-mail";

$lang['Ban_username'] = "Banear a uno o varios Usuarios";


$lang['Ban_IP'] = "Banear una o varias direcciones IP o HOSTNAMES";
$lang['IP_hostname'] = "Direcciones IP o HOSTNAMES";
$lang['Ban_IP_explain'] = "Para especificar diferentes IPs o Nombres de Dominio, separálos con comas. Para especificar un rango de direcciones IP separá el comienzo y el final utilizando un guión (-), para especificar un comodín utilizá *";

$lang['Ban_email'] = "Banear una o varias direcciones de email";
$lang['Ban_email_explain'] = "Para especificar más de un email, colocálos separados por comas. Para especificar un comodín de usarios usá *, por ejemplo *@hotmail.com";

$lang['Unban_username'] = "Quitar ban de uno o varios Usuarios";
$lang['Unban_username_explain'] = "Podés quitar el ban de múltiples Usuarios usando la correcta combinación de mouse y teclado de su computadora y navegador";

$lang['Unban_IP'] = "Quitar ban de una o varias Direcciones IP";
$lang['Unban_IP_explain'] = "Podés quitar el ban a múltiples direcciones IP usando la correcta combinación de mouse y teclado de su computadora y navegador";

$lang['Unban_email'] = "Quitar ban de una o varias direcciones de email";
$lang['Unban_email_explain'] = "Podés quitar el ban de múltiples direcciones de email usando la correcta combinación del mouse y teclado de su computadora y navegador";

$lang['No_banned_users'] = "No hay Usuarios baneados";
$lang['No_banned_ip'] = "No hay direcciones de IP baneadas";
$lang['No_banned_email'] = "No hay direcciones de email baneadas";

$lang['Ban_update_sucessful'] = "La lista de BAN ha sido actualizada correctamente";
$lang['Click_return_banadmin'] = "Click %sacá%s para volver al Panel de Control de BANS";


//
// Configuration
//
$lang['General_Config'] = "Configuración General";
$lang['Config_explain'] = "El siguiente formulario, te permite cambiar las opciones de tu foro. Para la configuración de Usuarios y Foros usá los links de la izquierda.";

$lang['Click_return_config'] = "Click %sacá%s para volver a la Configuración General";

$lang['General_settings'] = "Configuración General del Foro";
$lang['Server_name'] = "Nombre de Dominio";
$lang['Server_name_explain'] = "El nombre de dominio en el que corre este Foro";
$lang['Script_path'] = "Path del Script";
$lang['Script_path_explain'] = "La ruta en donde phpBB2 está ubicado, relativo al nombre de dominio";
$lang['Server_port'] = "Puerto del Servidor";
$lang['Server_port_explain'] = "El puerto en el que corre el servidor, generalmente 80. Sólo cambiar si difiere de este valor.";
$lang['Site_name'] = "Nombre del Sitio";
$lang['Site_desc'] = "Descripción del Sito";
$lang['Board_disable'] = "Desactivar Foro";
$lang['Board_disable_explain'] = "Esto hará que el los Usuarios no tengan acceso al Foro. No te desloguees mientras desactiva el Foro, ya que no podrás volver a loguearte";
$lang['Acct_activation'] = "Activar cuenta";
$lang['Acc_None'] = "Ninguna"; // These three entries are the type of activation
$lang['Acc_User'] = "Usuario";
$lang['Acc_Admin'] = "Administrador";

$lang['Abilities_settings'] = "Configuración Básica de Usuario y del Foro";
$lang['Max_poll_options'] = "Número máximo de ítems en Encuentas";
$lang['Flood_Interval'] = "Intervalo de Flood";
$lang['Flood_Interval_explain'] = "Cantidad de segundos que el usuario debe esperar para publicar topicos";
$lang['Board_email_form'] = "E-mail de Usuarios a través del Foro";
$lang['Board_email_form_explain'] = "Los usuarios se envían emails mediante el Foro";
$lang['Topics_per_page'] = "Topics por Pagina";
$lang['Posts_per_page'] = "Respuestas por Página";
$lang['Hot_threshold'] = "Cantidad de respuestas para ser considerado Popular";
$lang['Default_style'] = "Estilo por defecto";
$lang['Override_style'] = "Ignorar el estilo del Usuario";
$lang['Override_style_explain'] = "Se utilizará el estilo seleccionado por defecto sin importar la elección del usuario";
$lang['Default_language'] = "Lenguaje por Defecto";
$lang['Date_format'] = "Formato de la Fecha";
$lang['System_timezone'] = "Zona Horaria";
$lang['Enable_gzip'] = "Activar la Compresion GZip";
$lang['Enable_prune'] = "Habilitar Pruning en el Foro";
$lang['Allow_HTML'] = "Permitir HTML";
$lang['Allow_BBCode'] = "Permitir BBCode";
$lang['Allowed_tags'] = "Permitir HTML tags";
$lang['Allowed_tags_explain'] = "Separare tags con comas";
$lang['Allow_smilies'] = "Permitir Emoticons";
$lang['Smilies_path'] = "Almacenaje de la ruta de los Emoticons";
$lang['Smilies_path_explain'] = "Ruta desde el directorio phpBB , ejemplo images/smilies";
$lang['Allow_sig'] = "Permitir Firmas";
$lang['Max_sig_length'] = "Máxima longitud de la Firma";
$lang['Max_sig_length_explain'] = "Máxima cantidad de caracteres de la Firma";
$lang['Allow_name_change'] = "Permitir cambio de Nombre de Usuario";

$lang['Avatar_settings'] = "Configuración de los Avatars";
$lang['Allow_local'] = "Habilitar galerías de Avatars";
$lang['Allow_remote'] = "Habilitar Avatars Remotos";
$lang['Allow_remote_explain'] = "Permitir mostrar Avatars guardados en otros sitios web";
$lang['Allow_upload'] = "Habilitar upload de Avatars";
$lang['Max_filesize'] = "Tamaño máximo para las imágenes";
$lang['Max_filesize_explain'] = "Limita la cantidad de bytes que puede tener un Avatar";
$lang['Max_avatar_size'] = "Máximo Tamaño del Avatar";
$lang['Max_avatar_size_explain'] = "(Altura x Ancho en píxeles)";
$lang['Avatar_storage_path'] = "Ruta del Avatar";
$lang['Avatar_storage_path_explain'] = "Ruta dentro de phpBB donde se encuentran los Avatars, ejemplo images/avatars";
$lang['Avatar_gallery_path'] = "Ruta de la Galería de Avatars";
$lang['Avatar_gallery_path_explain'] = "Ruta dentro de phpBB de la galería, e.g. images/avatars/gallery";

$lang['COPPA_settings'] = "Configuraciones COPPA";
$lang['COPPA_fax'] = "Número de Fax COPPA";
$lang['COPPA_mail'] = "Dirección de Correo COPPA";
$lang['COPPA_mail_explain'] = "Esta es la dirección de correo donde los padres deben enviar el formulario COPPA";

$lang['Email_settings'] = "Configuración del Email";
$lang['Admin_email'] = "Dirección de email del Administrador";
$lang['Email_sig'] = "Firma";
$lang['Email_sig_explain'] = "Este texto se añadirá al final de cada email";
$lang['Use_SMTP'] = "Usar servidor SMTP para Email";
$lang['Use_SMTP_explain'] = "Decí si podés y/o tenés que enviar los e-mails por un servidor SMTP";
$lang['SMTP_server'] = "Dirección SMTP del Servidor";
$lang['SMTP_username'] = "Nombre de usuario del SMTP";
$lang['SMTP_username_explain'] = "Ingresá un nombre de usuario solamente si su servidor SMTP lo requiere";
$lang['SMTP_password'] = "Contraseña del SMTP";
$lang['SMTP_password_explain'] = "Ingresá una contraseña solamente si su servidor SMTP lo requiere";

$lang['Disable_privmsg'] = "Mensaje Privado";
$lang['Inbox_limits'] = "Máxima cantidad de mensajes en la Bandeja de Entrada";
$lang['Sentbox_limits'] = "Máxima cantidad de mensajes en la Bandeja de Salida";
$lang['Savebox_limits'] = "Máxima cantidad de mensajes en La Carpeta para Guardar";

$lang['Cookie_settings'] = "Configuración de las Cookies"; 
$lang['Cookie_settings_explain'] = "Esto controla como se envían las cookies al Navegador, en la mayoría de los casos la configuración preestablecida sera más que suficiente. Si necesitás cambiar esto tené cuidado, ya que en caso de configurarlo mal podrías hacer que tus Usuarios no puedan Ingresar al Foro";
$lang['Cookie_domain'] = "Dominio de la Cookie";
$lang['Cookie_name'] = "Nombre de la Cookie";
$lang['Cookie_path'] = "Ruta de la Cookie";
$lang['Cookie_secure'] = "Cookie segura [ https ]";
$lang['Cookie_secure_explain'] = "Si tu servidor está funcionando vía SSL marcá esta opción, de otra manera dejálo deshabilitado";
$lang['Session_length'] = "Duración de la sesión [ segundos ]";


//
// Forum Management
//
$lang['Forum_admin'] = "Administración del Foro";
$lang['Forum_admin_explain'] = "Desde este panel podés añadir, borrar, editar, y re-ordenar categorías y Foros";
$lang['Edit_forum'] = "Editar el Foro";
$lang['Create_forum'] = "Crear un nuevo Foro";
$lang['Create_category'] = "Crear una nueva Categoría";
$lang['Remove'] = "Quitar";
$lang['Action'] = "Acción";
$lang['Update_order'] = "Actualizar Orden";
$lang['Config_updated'] = "Configuración del Foro actualizada satisfactoriamente";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Borrar";
$lang['Move_up'] = "Hacia arriba";
$lang['Move_down'] = "Hacia abajo";
$lang['Resync'] = "sincronizar";
$lang['No_mode'] = "Ningún modo ha sido seleccionado";
$lang['Forum_edit_delete_explain'] = "El siguiente formulario te permite personalizar las opciones del Foro. Para la configuración de usuarios y Foros usá los links de la izquierda.";

$lang['Move_contents'] = "Mover todos los contenidos";
$lang['Forum_delete'] = "Borrar el Foro";
$lang['Forum_delete_explain'] = "El siguiente formulario te permite Borrar algún foro (o categoría) y decir dónde querés colocar todos los Topics y Categorías.";

$lang['Forum_settings'] = "Configuración General del Foro";
$lang['Forum_name'] = "Nombre del Foro";
$lang['Forum_desc'] = "Descripción";
$lang['Forum_status'] = "Estado del Foro";
$lang['Forum_pruning'] = "Auto-pruning";

$lang['prune_freq'] = 'Chequear topics y edad';//Ver Pruning!
$lang['prune_days'] = "Remover Topics que no tienen respuesta";
$lang['Set_prune_data'] = "Elegiste la opción de Auto-pruning para este foro pero no has seleccionado la frecuencia o cantidad de días para el PRUNE. Volvé y efectuá los cambios";

$lang['Move_and_Delete'] = "Mover y Borrar";

$lang['Delete_all_posts'] = "Borrar todos los Temas";
$lang['Nowhere_to_move'] = "Nowhere to move too";

$lang['Edit_Category'] = "Editar Categoría";
$lang['Edit_Category_explain'] = "Usá este formulario para Editar categorías";

$lang['Forums_updated'] = "La información del Foro y sus categorías han sido actualizadas satisfactoriamente";

$lang['Must_delete_forums'] = "Necesitás Borrar todos los foros antes de borrar una Categoría";

$lang['Click_return_forumadmin'] = "Click %sacá%s para volver a la Administración del Foro";


//
// Smiley Management
//
$lang['smiley_title'] = "Utilitario para la edición de Smilies";
$lang['smile_desc'] = "Desde esta página usted podés añadir, quitar o editar algún emoticon para que los Usuarios utilicen en el foro y en los mensajes Privados";

$lang['smiley_config'] = "Configuración de Smiley";
$lang['smiley_code'] = "Código de Smiley";
$lang['smiley_url'] = "Archivo de Imagen del Smiley";
$lang['smiley_emot'] = "Smiley Emotion";
$lang['smile_add'] = "Añadir un nuevo Smiley";
$lang['Smile'] = "Smiley";
$lang['Emotion'] = "Emoción";

$lang['Select_pak'] = "Seleccione el archivo .pak";
$lang['replace_existing'] = "Reemplazar Smileys Existentes";
$lang['keep_existing'] = "Mantener Smileys Existentes";
$lang['smiley_import_inst'] = "Tenés que descomprimir el paquete de Smileys y subir todos los archivos en el directorio de Smileys para así lograr su correcta instalación. Luego seleccioná la información correcta desde este formulario para así poder importar los Smileys";
$lang['smiley_import'] = "Importar paquete de Smileys";
$lang['choose_smile_pak'] = "Escoger archivo de paquete (.pak)";
$lang['import'] = "Importar Smileys";
$lang['smile_conflicts'] = "Qué se debería realizar en caso de conflicto";
$lang['del_existing_smileys'] = "Borrar los smileys existentes antes de importarlos";
$lang['import_smile_pack'] = "Importar Paquete de Smileys";
$lang['export_smile_pack'] = "Crear un paquete de Smileys";
$lang['export_smiles'] = "Para crear un paquete de Smilets desde sus smileys instalados, hacé click %sacá%s para bajar el archivo smiles.pak. Renombrá este archivo de forma apropiada pero aseguráte de mantener la extension .pak. Luego creá un archivo zip que contenga todos los smileys más el archivo .pak.";

$lang['smiley_add_success'] = "Los Smileys han sido añadidos satisfactoriamente";
$lang['smiley_edit_success'] = "Los Smileys han sido actualizados satisfactoriamente ";
$lang['smiley_import_success'] = "El paquete de Smileys ha sido importado correctamente.";
$lang['smiley_del_success'] = "Los Smileys han sido removidos satisfactoriamente.";
$lang['Click_return_smileadmin'] = "Click %sacá%s para volver al Panel de Smileys";


//
// User Management
//
$lang['User_admin'] = "Administración de Usuarios";
$lang['User_admin_explain'] = "Desde acá podés cambiar la información del usuario. Para modificar los permisos de un Usuario usá el Sistema de Permisos de usuarios y Grupos.";

$lang['Look_up_user'] = "Observar Usuario";

$lang['Admin_user_fail'] = "No se ha logrado actualizar el perfil del Usuario";
$lang['Admin_user_updated'] = "El perfil del Usuario ha sido actualizado satisfactoriamente";
$lang['Click_return_useradmin'] = "Click %sacá%s para volver al Panel de Administración de Usuarios";

$lang['User_delete'] = "Borrar Usuario";
$lang['User_delete_explain'] = "Click acá para borrar este Usuario. Tené en cuenta que no vas a poder restaurarlo.";
$lang['User_deleted'] = "El Usuario ha sido borrado satisfactoriamente.";

$lang['User_status'] = "Usuario Activo";
$lang['User_allowpm'] = "Podés enviar mensajes privados";
$lang['User_allowavatar'] = "Podés mostrar tu Avatar";

$lang['Admin_avatar_explain'] = "Desde acá podés ver y borrar el Avatar del Usuario";

$lang['User_special'] = "Campos especiales para Administradores";
$lang['User_special_explain'] = "Estos campos no están disponibles para que los Usuarios lo modifiquen. Desde acá podés configurar el status y otras opciones que los Usuarios no pueden modificar.";


//
// Group Management
//
$lang['Group_administration'] = "Administración de Grupos";
$lang['Group_admin_explain'] = "Desde este panel podés modificar los Grupos: borrar, crear y editar los Grupos existentes. También podés elegir los Moderadores y cambiar el nombre del Grupo y su descripción";
$lang['Error_updating_groups'] = "Ha ocurrido un error actualizando el Grupo";
$lang['Updated_group'] = "El Grupo ha sido actualizado correctamente";
$lang['Added_new_group'] = "El Nuevo Grupo ha sido creado";
$lang['Deleted_group'] = "El Grupo ha sido borrado";
$lang['New_group'] = "Crear Nuevo Grupo";
$lang['Edit_group'] = "Editar Grupo";
$lang['group_name'] = "Nombre del Grupo";
$lang['group_description'] = "Descripción del Grupo";
$lang['group_moderator'] = "Moderador del Grupo";
$lang['group_status'] = "Status del Grupo";
$lang['group_open'] = "Grupo Abierto";
$lang['group_closed'] = "Grupo Cerrado";
$lang['group_hidden'] = "Grupo Oculto";
$lang['group_delete'] = "Borrar Grupo";
$lang['group_delete_check'] = "Borrar este Grupo";
$lang['submit_group_changes'] = "Aceptar Cambios";
$lang['reset_group_changes'] = "Anular Cambios";
$lang['No_group_name'] = "Tenés que especificar un Nombre para este Grupo";
$lang['No_group_moderator'] = "Tenés que especificar un Moderador para este Grupo";
$lang['No_group_mode'] = "Tenés que especificar el modo de este Grupo, Abrierto o Cerrado";
$lang['delete_group_moderator'] = "¿Borrar el antiguo moderador del Grupo?";
$lang['delete_moderator_explain'] = "Si estás cambiando el moderador del Grupo, marcá esta casilla para eliminar al antiguo Moderador del Grupo. Si no, el Usuario se convertirá en un miembro regular.";
$lang['Click_return_groupsadmin'] = "Click %sacá%s para volver al Panel de Administración de Grupos.";
$lang['Select_group'] = "Seleccioná un Grupo";
$lang['Look_up_group'] = "Observar un Grupo";
$lang['No_group_action'] = 'No se especificó una acción'; 


//
// Prune Administration
//
$lang['Forum_Prune'] = "Prune de Foros";
$lang['Forum_Prune_explain'] = "Esto borrará todos los topics en los que no se hayan publicado nuevos men	sajes en los días que usted seleccionó. Si no ingresás un número entonces todos los topics serán borrados. No se borrarán los topics en los que hay encuestas que estén corriendo ni anuncios. Tendrás que eliminar estos topics en forma manual.";
$lang['Do_Prune'] = "Realizar el Prune";
$lang['All_Forums'] = "Todos los Foros";
$lang['Prune_topics_not_posted'] = "Borrar topics sin respuestas de una antigüedad de estos días";
$lang['Topics_pruned'] = "topics borrados";
$lang['Posts_pruned'] = "Mensajes borrados";
$lang['Prune_success'] = "El Pruning de los foros fue exitoso";


//
// Word censor
//
$lang['Words_title'] = "Control de Palabras Censuradas";
$lang['Words_explain'] = "Desde acá podés agregar, editar, y quitar palabras que automáticamente serán censuradas de tus foros. Estas palabras no podrán ser elegidas como nombres de usuario. Los Asteriscos (*) son aceptados en los campos de las palabras, ejemplo *prueba* , prueba* (acapararía pruebalo), *test (acapararía enprueba).";
$lang['Word'] = "Palabra";
$lang['Edit_word_censor'] = "Editar el Censor de Palabras";
$lang['Replacement'] = "Reemplazar";
$lang['Add_new_word'] = "Agregar nueva palabra";
$lang['Update_word'] = "Actualizar censor de palabras";

$lang['Must_enter_word'] = "Tenés que colocar una palabra y su otra palabra para el reemplazo";
$lang['No_word_selected'] = "No se ha seleccionado una palabra para el reemplazo";

$lang['Word_updated'] = "Se han realizado los cambios satisfactoriamente";
$lang['Word_added'] = "La nueva palabra ha sido añadida con éxito";
$lang['Word_removed'] = "La palabra ha sido removida del Censurador de Palabras";

$lang['Click_return_wordadmin'] = "Click %sacá%s para volver al Administrador de Palabras Censuradas";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Desde acá podés enviar mensajes de e-mail a todos tus Usuarios y Grupos. Al hacer esto, un e-mail será enviado desde el email administrativo inidicado previamente. Si enviás este e-mail a un Grupo numeroso, sé paciente y esperá a que termine de cargar la página. Es normal que tarde unos cuantos minutos, serás notificado una vez finalizado el envío.";
$lang['Compose'] = "Escribir"; 

$lang['Recipients'] = "E-mails"; 
$lang['All_users'] = "A todos los Usuarios";

$lang['Email_successfull'] = "Su e-mail ha sido enviado";
$lang['Click_return_massemail'] = "Click %sacá%s para volver al Panel para Enviar e-mails Masivos";


//
// Ranks admin
//
$lang['Ranks_title'] = "Administración de Rangos";
$lang['Ranks_explain'] = "Usando este formulario podés añadir, editar, ver y borrar rangos. También podés crear rangos";

$lang['Add_new_rank'] = "Añadir Rango";

$lang['Rank_title'] = "Título del Rango";
$lang['Rank_special'] = "Seleccionar como Rangp Especial";
$lang['Rank_minimum'] = "Mínima cantidad de Mensajes";
$lang['Rank_maximum'] = "Máxima cantidad de Mensajes";
$lang['Rank_image'] = "Imágen del rango (tenga en cuenta la ruta del foro phpBB2)";
$lang['Rank_image_explain'] = "Usá esto para definir una pequeña imagen para este rango";

$lang['Must_select_rank'] = "Tenés que seleccionar un Rango";
$lang['No_assigned_rank'] = "No se ha seleccionado un Rango";

$lang['Rank_updated'] = "El Rango ha sido actualizado";
$lang['Rank_added'] = "El nuevo Rango se ha añadido";
$lang['Rank_removed'] = "El Rango ha sido borrado";

$lang['Click_return_rankadmin'] = "Click %sacá%s para volver al Panel de Adminstración de Rangos";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Control de Admisión de Usuario";
$lang['Disallow_explain'] = "Desde acá podés controlar los nombres de usuario que no querés que sean utilizados. Para lograr esto tenés que usar comodines (*). Acordáte que no podés prohibir nombres de usuario que ya estén siendo utilizados. Antes de prohibir dichos nombres tenés que borrar a los usuarios que los usen.";

$lang['Delete_disallow'] = "Borrar";
$lang['Delete_disallow_title'] = "Borrar un nombre de usuario no permitido";
$lang['Delete_disallow_explain'] = "Podés eliminar nombres de usuario no permitidos seleccionando el nombre de usuario de la lista y clickeando en Aceptar";

$lang['Add_disallow'] = "Agregar";
$lang['Add_disallow_title'] = "Agregar un nombre de usuario no permitido";
$lang['Add_disallow_explain'] = "Podés no permitir un nombre de usuario utilizando máscaras con asteriscos(*)";

$lang['No_disallowed'] = "Nombres de usuarios no permitidos";

$lang['Disallowed_deleted'] = "El nombre de usuario no permitido ha sido borrado";
$lang['Disallow_successful'] = "El nombre de usuario no permitido ha sido agregado";
$lang['Disallowed_already'] = "El nombre de usuario no permitido que has seleccionado no puede ser agregado debido a que ya existe en la lista, o existe en la Lista de Palabras Censuradas, o bien ya se encuentra en la lista de usuarios no permitidos";

$lang['Click_return_disallowadmin'] = "Click %sacá%s para volver al Control de Admisión de Usuario";


//
// Styles Admin
//
$lang['Styles_admin'] = "Administración de Estilos";
$lang['Styles_explain'] = "Desde acá podés fácilmente añadir, quitar y administrar los estilos (plantillas y temas) disponibles para tus usuarios";
$lang['Styles_addnew_explain'] = "La siguiente lista contiene todos los temas que están disponibles para las plantillas. Los items de la lista no han sido instalados en la base de los foros phpBB. Para hacer eso simplemente hacé click en el link que figura al lado de cada opción";

$lang['Select_template'] = "Seleccioná una Plantilla";

$lang['Style'] = "Estilo";
$lang['Template'] = "Plantilla";
$lang['Install'] = "Instalar";
$lang['Download'] = "Descargar";

$lang['Edit_theme'] = "Editar Tema";
$lang['Edit_theme_explain'] = "En el siguiente formulario podés editar la configuración del tema seleccionado";

$lang['Create_theme'] = "Crear Tema";
$lang['Create_theme_explain'] = "Usá este formulario para crear un tema nuevo para la plantilla seleccionada. Cuando ingreses los colores (los cuales tenés ingresar de forma hexadecimal) no debe incluir el # . Ejemplo: CCCCCC es válido, no asi lo seria colocar #CCCCCC";

$lang['Export_themes'] = "Exportar Tema";
$lang['Export_explain'] = "Desde este panel podés exportar el tema para la plantilla seleccionada. Seleccioná la plantilla de la lista debajo y el programa creará el archivo de configuración del tema y asi podrás guardarlo. Si no se puede grabar el archivo te va a preguntar si querés bajarlo. Para que el programa pueda guardarlo tenés que dar permiso de escritura a la carpeta de plantillas (template). Para más información consultá la guia del foro phpBB 2";

$lang['Theme_installed'] = "El tema seleccionado ha sido instalado correctamente";
$lang['Style_removed'] = "El estilo seleccionado ha sido quitado de la base de datos. Para removerlo completamente tenés borrar el directorio apropiado del directorio de plantillas (template)";
$lang['Theme_info_saved'] = "La información para la plantilla seleccionada ha sido guardada. Ahora tenés que devolver los permisos en theme_info.cgf y poner el directorio de plantillas (template) en modo de sólo lectura";
$lang['Theme_updated'] = "El tema seleccionado ha sido actualizado. Ahora tenés que exportar la configuración del nuevo tema";
$lang['Theme_created'] = "Tema Creado. Ahora tenés que exportar el tema en el archivo de configuración de temas para asi lograr mantenerlo seguro";

$lang['Confirm_delete_style'] = "Estás seguro querer borrar este estilo";

$lang['Download_theme_cfg'] = "No se ha podido exportar el archivo ya que no se ha podido escribir en el archivo. Presioná el botón debajo para descargar este archivo con su navegador. Una vez que lo hayas bajado podés moverlo al directorio de las plantillas (template).";
$lang['No_themes'] = "La plantilla que has seleccionado no tiene temas adjuntos. Para crear un nuevo tema presioná en Crear Nuevo Tema en la izquierda del panel";
$lang['No_template_dir'] = "No se ha podido abrir la carpeta de plantillas. Esto puede deberse a que esté con permisos sin lectura, o que ésta no exista.";
$lang['Cannot_remove_style'] = "No podés quitar el estilo seleccionado ya que es el que está por defecto en el foro. Cambiá el que se utiliza por defecto e intentálo nuevamente";
$lang['Style_exists'] = "El nombre de estilo seleccionado ya existe, volvé atrás y seleccioná otro distinto";

$lang['Click_return_styleadmin'] = "Click %sacá%s para volver a la Administración de Estilos";

$lang['Theme_settings'] = "Configuración de Temas";
$lang['Theme_element'] = "Elementos de Temas";
$lang['Simple_name'] = "Nombre simple";
$lang['Value'] = "Valor";
$lang['Save_Settings'] = "Guardar Configuración";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Background_image'] = "Imagen de Fondo";
$lang['Background_color'] = "Color de Fondo";
$lang['Theme_name'] = "Nombre de Tema";
$lang['Link_color'] = "Color de Link";
$lang['Text_color'] = "Color de Texto";
$lang['VLink_color'] = "Color de Link Visitado";
$lang['ALink_color'] = "Color de Link Activo";
$lang['HLink_color'] = "Color de Link Hoover";
$lang['Tr_color1'] = "Tabla Fila Color 1";
$lang['Tr_color2'] = "Tabla Fila Color 2";
$lang['Tr_color3'] = "Tabla Fila Color 3";
$lang['Tr_class1'] = "Tabla Fila Clase 1";
$lang['Tr_class2'] = "Tabla Fila Clase 2";
$lang['Tr_class3'] = "Tabla Fila Clase 3";
$lang['Th_color1'] = "Tabla Encabezado Color 1";
$lang['Th_color2'] = "Tabla Encabezado Color 2";
$lang['Th_color3'] = "Tabla Encabezado Color 3";
$lang['Th_class1'] = "Tabla Encabezado Clase 1";
$lang['Th_class2'] = "Tabla Encabezado Clase 2";
$lang['Th_class3'] = "Tabla Encabezado Clase 3";
$lang['Td_color1'] = "Tabla Celda Color 1";
$lang['Td_color2'] = "Tabla Celda Color 2";
$lang['Td_color3'] = "Tabla Celda Color 3";
$lang['Td_class1'] = "Tabla Celda Clase 1";
$lang['Td_class2'] = "Tabla Celda Clase 2";
$lang['Td_class3'] = "Tabla Celda Clase 3";
$lang['fontface1'] = "Fuente 1";
$lang['fontface2'] = "Fuente 2";
$lang['fontface3'] = "Fuente 3";
$lang['fontsize1'] = "Fuente 1";
$lang['fontsize2'] = "Fuente 2";
$lang['fontsize3'] = "Fuente 3";
$lang['fontcolor1'] = "Fuente Color 1";
$lang['fontcolor2'] = "Fuente Color 2";
$lang['fontcolor3'] = "Fuente Color 3";
$lang['span_class1'] = "Espacio Clase 1";
$lang['span_class2'] = "Espacio Clase 2";
$lang['span_class3'] = "Espacio Clase 3";
$lang['img_poll_size'] = "Imagen de la Encuesta [px]";
$lang['img_pm_size'] = "Tamaño de Mensajes Privados [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Bienvenidos a la Instalación de los foros phpBB 2";
$lang['Initial_config'] = "Configuración Basica";
$lang['DB_config'] = "Configuración de la Base de Datos";
$lang['Admin_config'] = "Configuración del Administrador";
$lang['continue_upgrade'] = "Una vez que hayas bajado el archivo de configuración hacé click en \"Continuar Actualización\" para continuar con el proceso. Esperá a que se suba el archivo de configuración hasta que el proceso de actualización finalice";
$lang['upgrade_submit'] = "Continuar Actualización";

$lang['Installer_Error'] = "Un error ha ocurrido durante la Instalación";
$lang['Previous_Install'] = "No se ha detectado una Instalación previa";
$lang['Install_db_error'] = "Un error ha ocurrido actualizando la Base de Datos";

$lang['Re_install'] = "Tu instalación previa todavía está activa. <br /><br />Si querés reinstalar los foros phpBB 2 hacé click en Sí en el botón debajo. Por favor tené en cuenta que al realizar esto se destruirá la información existente, no se harán copias de seguridad. El usuario administrador y la contraseña que usabas anteriormente serán creados nuevamente. <br /><br />¡Pensá cuidadosamente ántes de presionar SI!";

$lang['Inst_Step_0'] = "Gracias por elegir phpBB 2. Para finalizar la instalación por favor completá los datos requeridos debajo. Tené en cuenta que la Base de Datos destinada a los foros ya debería existir. Si estás instalando en una Base de Datos que utiliza OBDC, por ejemplo MS Access primero deberás crear un DNS y después continuar.";

$lang['Start_Install'] = "Comenzar Instalación";
$lang['Finish_Install'] = "Finalizar Inatalación";

$lang['Default_lang'] = "Lenguaje por defecto";
$lang['DB_Host'] = "Nombre de Dominio de la Base de Datos / DSN";
$lang['DB_Name'] = "Nombre de su base de Datos";
$lang['DB_Username'] = "Nombre de usuario de la base de datos";
$lang['DB_Password'] = "Contraseña de la base de datos";
$lang['Database'] = "Su Base de Datos";
$lang['Install_lang'] = "Elija el Lenguaje de Instalación";
$lang['dbms'] = "Tipo de Base de Datos";
$lang['Table_Prefix'] = "Prefijo para tablas en la Base de datos";
$lang['Admin_Username'] = "Nombre de Usuario del Administrador";
$lang['Admin_Password'] = "Contraseña del Administrador";
$lang['Admin_Password_confirm'] = "Contraseña de acceso del Administrador [ Confirma ]";

$lang['Inst_Step_2'] = "Tu usuario administrador y contraseña han sido creados. En este punto el proceso de Instalación Básica ha sido completado. Ahora serás enviado a una pantalla que te permite administrar la nueva instalación. Aseguráte de verificar la Configuración General y de haber realizado los cambios requeridos. Gracias por elegir phpBB 2";

$lang['Unwriteable_config'] = "Tu archivo de configuración está en un modo de no-escritura. Una copia del archivo de configuración podrá ser bajado cuando hagas click el botón siguiente. Tenés que subir este fichero en el mismo directorio donde se encuentre el foro phpBB 2. Una vez que esto se haya realizado tenés que ingresar usando el usuario de administrador y contraseña que elegiste en el formulario anterior y así visitar el Control de Administración para ver la configuración general. Gracias por escoger phpBB 2";
$lang['Download_config'] = "Bajar Configuración";

$lang['ftp_choose'] = "Escoger Método de Descarga";
$lang['ftp_option'] = "<br />Ya que las extensiones FTP están disponibles en esta versión de PHP podés elegir si querés, mediante FTP, colocar el archivo en su lugar automáticamente.";
$lang['ftp_instructs'] = "Has seleccionado subir automáticamente por FTP el archivo en la cuenta que contiene el phpBB 2. Ingresá la información solicitada para facilitar el proceso. Tené en cuenta que la ruta FTP debe ser la ruta exacta al PHPBB 2 como si fueras a subir los archivos usando cualquier cliente de ftp.";
$lang['ftp_info'] = "Ingresá la Información de su FTP";
$lang['Attempt_ftp'] = "Intentar subir archivo mediante ftp en forma automática";
$lang['Send_file'] = "Envíenme el archivo a mí y yo lo subiré personalmente por FTP";
$lang['ftp_path'] = "Ruta FTP al phpBB 2";
$lang['ftp_username'] = "Nombre de Usuario FTP";
$lang['ftp_password'] = "Contraseña FTP";
$lang['Transfer_config'] = "Iniciar Transferencia";
$lang['NoFTP_config'] = "El intento se subir por ftp el archivo de configuración ha fallado. Por favor bajá el archivo de configuración y subílo por FTP en forma manual.";

$lang['Install'] = "Instalar";
$lang['Upgrade'] = "Actualizar";


$lang['Install_Method'] = "Elegí tu método de Instalacion";

$lang['Install_No_Ext'] = "La configuración de PHP en tu servidor no soporta el tipo de base de datos seleccionado";

$lang['Install_No_PCRE'] = "phpBB2 requiere el módulo de expresiones regulares compatible con Perl para php, que no figura como soportado en tu configuración de php!";

//
// Eso es todo amigos!!!!
// -------------------------------------------------

?>