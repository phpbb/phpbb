<?php
/***************************************************************************
 *                            lang_admin.php [Asturian]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     tradución al asturinu: Mikel González (mikelglez@iespana.es)
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
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = "Alministración Xeneral";
$lang['Users'] = "Alministración d'Usuarios";
$lang['Groups'] = "Alministración de Grupos";
$lang['Forums'] = "Alministración de Foros";
$lang['Styles'] = "Alministración d'Estilos";

$lang['Configuration'] = "Configuración";
$lang['Permissions'] = "Permisos";
$lang['Manage'] = "Xestión";
$lang['Disallow'] = "Deshabilitar nomes d'usuariu";
$lang['Prune'] = "Purgar";
$lang['Mass_Email'] = "Correu Masivu";
$lang['Ranks'] = "Rangos";
$lang['Smilies'] = "Smilies";
$lang['Ban_Management'] = "Control d'Exclusión";
$lang['Word_Censor'] = "Pallabres Censuraes";
$lang['Export'] = "Exportar";
$lang['Create_new'] = "Crear";
$lang['Add_new'] = "Añadir";
$lang['Backup_DB'] = "Copia de la Base Datos";
$lang['Restore_DB'] = "Restaurar la Base Datos";


//
// Index
//
$lang['Admin'] = "Alministración";
$lang['Not_admin'] = "Usté nun tá autorizau pa alministrar esti Foru";
$lang['Welcome_phpBB'] = "Bienveniu a phpBB";
$lang['Admin_intro'] = "Gracies por elexir phpBB comu solución pa el su foru. Esta pantalla dará-y un resumen de les principales estadístiques del su foru. Pue tornar a esta páxina calcando el enllace de <u>Índiz del Alministraor</u> nel panel de la izquierda. Pa tornar al índiz del su foru, claque el llogu de phpBB tamén ubicau nel panel izquierdu. Los otros enllaces ubicaos a la izquierda d'esta pantalla dexarán-y controlar tolos aspeutus d'esti foru, cada pantalla tendrá instrucciones de comu usar les ferramientes.";
$lang['Main_index'] = "Índiz del Foru";
$lang['Forum_stats'] = "Estadístiques del Foru";
$lang['Admin_Index'] = "Índiz del Alministraor";
$lang['Preview_forum'] = "Vista previa del Foru";

$lang['Click_return_admin_index'] = "Calque %sEquí%s pa tornar al Índiz del Alministraor";

$lang['Statistic'] = "Estadística";
$lang['Value'] = "Valor";
$lang['Number_posts'] = "Númberu d'unvíos";
$lang['Posts_per_day'] = "Unvíos por día";
$lang['Number_topics'] = "Cantidá de tópicos";
$lang['Topics_per_day'] = "Tópicos per día";
$lang['Number_users'] = "Cantidá d'usuarios";
$lang['Users_per_day'] = "Usuarios per día";
$lang['Board_started'] = "Fecha d'entamu del Foru";
$lang['Avatar_dir_size'] = "Tamañu del direutoriu d'Imáxenes";
$lang['Database_size'] = "Tamañu de la Base Datos";
$lang['Gzip_compression'] ="Tipu de Compresión Gzip";
$lang['Not_available'] = "Nun ta disponible";

$lang['ON'] = "PRENDIU"; // This is for GZip compression
$lang['OFF'] = "APAGAU"; 


//
// DB Utils
//
$lang['Database_Utilities'] = "Utilidaes de la Base Datos";

$lang['Restore'] = "Restaurar";
$lang['Backup'] = "Copia seguridá";
$lang['Restore_explain'] = "Esto restaurará toles tables de phpBB dende un archivu previamente guardau. Si el su servior soportalo, usté puede subir un archivu de textu comprimiu col gzip y ésti descomprimirase automaticamente. <b>ATENCIÓN</b> Esto sobre-escribirá la información existente. La restauración pue durar unos minutos, por favor quédese n'esta páxina hasta que'l procesu fine.";
$lang['Backup_explain'] = "Dende equi usté pue facer una copia de seguridá de toa la información relacioná con phpBB. Si usted tiene tables adicionales ena misma Base Datos de les que quisiera realizar una copia enxerte los sus nomes separtaos por comes nel campu de Tables Adicionales. Si el su servior soportalu pue utilizar el gzip pa comprimir el archivu y reducir el su tamañu enantes de descargarlu.";

$lang['Backup_options'] = "Opciones de la Copia";
$lang['Start_backup'] = "Entamar la Copia";
$lang['Full_backup'] = "Copia completá";
$lang['Structure_backup'] = "Namás la Estructura";
$lang['Data_backup'] = "Námas los Datos";
$lang['Additional_tables'] = "Tables adicionales";
$lang['Gzip_compress'] = "Compresión nun archivu Gzip";
$lang['Select_file'] = "Selecionar un archivu";
$lang['Start_Restore'] = "Entamar la Restauración";

$lang['Restore_success'] = "La Base Datos Restaurose.<br /><br />El su Foru deberia volver a la normalidá una vez fechu el procesu.";
$lang['Backup_download'] = "La descarga entamará darreu, por favor espere un momentín";
$lang['Backups_not_supported'] = "Disculpe pero la opción de copia de la su Base Datos nun ta soportá po'l su sistema";

$lang['Restore_Error_uploading'] = "Error subiendo la copia seguridá";
$lang['Restore_Error_filename'] = "Error nel nome d'archivu, por favor intente con un archivu diferente";
$lang['Restore_Error_decompress'] = "Nun puese descomprimir un archivu gzip, por favor subalo n'una version de textu";
$lang['Restore_Error_no_file'] = "Nun subiose nengún archivu";


//
// Auth pages
//
$lang['Select_a_User'] = "Selecionar un Usuariu";
$lang['Select_a_Group'] = "Selecionar un Grupu";
$lang['Select_a_Forum'] = "Selecionar un Foru";
$lang['Auth_Control_User'] = "Control de Permisos a los Usuarios"; 
$lang['Auth_Control_Group'] = "Control de Permisos a los Grupos"; 
$lang['Auth_Control_Forum'] = "Control de Permisos a los Foros"; 
$lang['Look_up_User'] = "Gueyar un Usuariu";
$lang['Look_up_Group'] = "Gueyar un Grupu"; 
$lang['Look_up_Forum'] = "Gueyar un Foru"; 

$lang['Group_auth_explain'] = "Dende equí podrá camudar los permisos y l'estau del moderaor asignau a cada grupu d'usuarios. Recuerde que en camudando los permisos del Grupu, que los permisos individuales camudarán en cuantes la persona entre al foru. Usted será alvertiu n'esti casu.";
$lang['User_auth_explain'] = "Dende equí usté pue camudar los permisos y estau del moderaor asignau a cada usuariu. Tea presente que camudando los permisos d'un usuariu los permisos del grupu permaneceran ensin camudar hasta qu'el usuariu entre a los foros. Usté será alvertiu n'esti casu.";
$lang['Forum_auth_explain'] = "Dende equí pue camudar los niveles d'autorización de cada foru. Pa facer esto tien dos menes; una simple y otra avanza. Esta última dará-y mayor control pa la operación y el funcionamientu de cada foru. Tenga en cuenta que al camudar los niveles de cada foru pue afectar el funcionamientu de cada usuariu según el foru y los permisos que tenga l'usuario.";



$lang['Simple_mode'] = "Mou Simple";
$lang['Advanced_mode'] = "Mou Avanzau";
$lang['Moderator_status'] = "Estau del Moderaor";

$lang['Allowed_Access'] = "Acesu Permitiu";
$lang['Disallowed_Access'] = "Acesu nun Permitiu";
$lang['Is_Moderator'] = "con Moderaor";
$lang['Not_Moderator'] = "ensin Moderaor";

$lang['Conflict_warning'] = "Advertencia de Conflictu ena Autorización";
$lang['Conflict_access_userauth'] = "Esti usuariu entá nun tien acesu a esti foru debio al Grupu al cual pertenez. Usté tien que camudar los permisos del grupu o esborriar al usuariu del Grupu pa preveer que l'usuariu no tea acesu a esti foru. Los drechos del Grupu y l'Usuariu explicanse abaxu.";
$lang['Conflict_mod_userauth'] = "Esti usuariu entá tien drechos de Moderaor a traviés d'un Grupu al cual pertenez. Usté deberá camudar los permisos del grupu o esborriar al usuariu de dicho Grupu pa tar seguru que nun tea acesu al foru con permisos de Moderar. Los drechos explicanse abasu.";

$lang['Conflict_access_groupauth'] = "El siguiente usuariu (o usuarios) entá tien acesu a esti foru debio a los permisos que tien como Usuariu. Pa que nun tea acesu usté tien que camudar los sus permisos. Los drechos d'Usuarios explicanse abaxu.";
$lang['Conflict_mod_groupauth'] = "El siguiente usuariu (o usuarios) entá tien drechos de Moderaor n'esti foru. Pa que nun tea acesu a esti foru usté tien que camudar los sus permisos d'usuariu. Los drechos d'Usuarios explicanse abaxu.";

$lang['Public'] = "Públicu";
$lang['Private'] = "Privau";
$lang['Registered'] = "Rexistrau";
$lang['Administrators'] = "Alministraor";
$lang['Hidden'] = "Ocultu";

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = "TOOS";
$lang['Forum_REG'] = "REX";
$lang['Forum_PRIVATE'] = "PRIVAOS";
$lang['Forum_MOD'] = "MOD";
$lang['Forum_ADMIN'] = "ALMIN";

$lang['View'] = "Güeyar";
$lang['Read'] = "Lleer";
$lang['Post'] = "Unvio";
$lang['Reply'] = "Rempuesta";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Esborriar";
$lang['Sticky'] = "PostIt";
$lang['Announce'] = "Anunciu";
$lang['Vote'] = "Votar";
$lang['Pollcreate'] = "Facer una encuesta";

$lang['Permissions'] = "Permisos";
$lang['Simple_Permission'] = "Permisu Simple";

$lang['User_Level'] = "Nivel d'Usuariu";
$lang['Auth_User'] = "Usuariu";
$lang['Auth_Admin'] = "Alministraor";
$lang['Group_memberships'] = "Grupu d'Usuarios";
$lang['Usergroup_members'] = "Esti Grupu tien los siguientes Usuarios";

$lang['Forum_auth_updated'] = "Permisu del Foru actualizau";
$lang['User_auth_updated'] = "Permisu del usuariu actualizau";
$lang['Group_auth_updated'] = "Permisu del Grupu actualizau";

$lang['Auth_updated'] = "los permisos camudaronse";
$lang['Click_return_userauth'] = "Calque %sequi%s pa tornar a los Permisos de los Usuarios";
$lang['Click_return_groupauth'] = "Calque %sequi%s pa tornar a los Permisos del Grupu";
$lang['Click_return_forumauth'] = "Calque %sequi%s pa tornar a los Permisos del Foru";


//
// Banning
//
$lang['Ban_control'] = "Control d'Exclusión";
$lang['Ban_explain'] = "Dende equí usté pue banear a un usuariu. Pue banear a los usuarios pol su nome, pola so direción IP, o pol su Nome de Dominiu. Esti métodu previen que un usuariu tea acesu a la páxina principal del foru. Pa evitar que l'Usuariu se rexistre con una nuea cuenta tamién pue bannear la su direción de correu. Tea en cuenta que baneau una direción de correu nun evitará que'l usuariu puea ingresar al foru nin que publique mensaxes. Para facer eso tien que utilizar un de los dos métodos esplicaos anteriormente.";
$lang['Ban_explain_warn'] = "Tea en cuenta que colocando un RANGU de direciones IP usté banea a toles direciones que s'alcuentren dientru del Rangu de la llista de bans.  Si realmente tien que utilizar un rangu intente utilizar unu pequeñu pa asi nun banear a otros usuarios.";

$lang['Select_username'] = "Selecione un Nome d'Usuariu";
$lang['Select_ip'] = "Selecione una direción IP";
$lang['Select_email'] = "Selecione una direción de Correu";

$lang['Ban_username'] = "Banear a un o varios Usuarios";


$lang['Ban_IP'] = "Banear una o varies direciones IP o Nomes de Dominiu";
$lang['IP_hostname'] = "Direciones IP o HOSTNAMES";
$lang['Ban_IP_explain'] = "Pa especificar diferentes IPs o Nomes de Dominiu, sepárelos con comes. Pa especificar un rangu de direciones IP separe l'entamu y el final utilizando un guión (-), pa especificar un comodín utilice el *";

$lang['Ban_email'] = "Banear una o varies direciones de correu";
$lang['Ban_email_explain'] = "Pa especificar más d'un correu, colóquelos separaos per comes. Pa especificar un comodín d'usarios utilece *, por exemplu *@hotmail.com";

$lang['Unban_username'] = "Quitar ban d'un o varios Usuarios";
$lang['Unban_username_explain'] = "Usté pue quitar el ban de múltiples Usuarios usando la correuta combinación de ratón y teclau del so ordenaor y navegaor";

$lang['Unban_IP'] = "Quitar ban d'una o varies Direciones IP";
$lang['Unban_IP_explain'] = "Usté pue quitar el ban a múltiples direciones IP usando la correuta combinación de ratón y teclau del so ordenaor y navegaor";

$lang['Unban_email'] = "Quitar ban d'una o varies direciones de correu";
$lang['Unban_email_explain'] = "Usté pue quitar el ban de varies direciones de correu usando la correuta combinación del ratóm y teclau del so ordenaor y navegaor";

$lang['No_banned_users'] = "Nun hay Usuarios baneaos";
$lang['No_banned_ip'] = "Nun hay direciones IP baneaes";
$lang['No_banned_email'] = "Nun hay direciones de correu baneaes";

$lang['Ban_update_sucessful'] = "La llista de BAN foy actualizá correutamente";
$lang['Click_return_banadmin'] = "Calque %sEquí%s pa tornar al Panel de Control de BANS";


//
// Configuration
//
$lang['General_Config'] = "Configuración Xeneral";
$lang['Config_explain'] = "El siguiente formulariu, permitirá-y camudar les opciones del su foru. Pa la configuración d'Usuarios y Foros use los enllaces de la izquierda.";

$lang['Click_return_config'] = "Calque %sEquí%s pa tornar a la Configuración Xeneral";

$lang['General_settings'] = "Configuración Xeneral del Foru";
$lang['Server_name'] = "Nome de Dominiu";
$lang['Server_name_explain'] = "El nome de dominiu nel que corre esti Foru";
$lang['Script_path'] = "Direutoriu del Script";
$lang['Script_path_explain'] = "El direutoriu au ta phpBB2, relativu al nome de dominiu";
$lang['Server_port'] = "Puertu del Servior";
$lang['Server_port_explain'] = "El puertu nel que corre el servior, xeneralmente 80. Camudar namás si ye distintu.";
$lang['Site_name'] = "Nome del Sitiu";
$lang['Site_desc'] = "Descrición del Situ";
$lang['Board_disable'] = "Desautivar Foru";
$lang['Board_disable_explain'] = "Esto fadrá que los Usuarios nun tean acesu al Foru. Nu se desconeute mientres desautiva'l Foru, ya que nun podriá volver a loguease nueamente";
$lang['Acct_activation'] = "Autivar cuenta";
$lang['Acc_None'] = "Nenguna"; // These three entries are the type of activation
$lang['Acc_User'] = "Usuariu";
$lang['Acc_Admin'] = "Alministraor";

$lang['Abilities_settings'] = "Configuración Básica d'Usuariu y del Foru";
$lang['Max_poll_options'] = "Númberu máximu d'entrugues en Encuestes";
$lang['Flood_Interval'] = "Intervalu de Flood";
$lang['Flood_Interval_explain'] = "Cantidá de segundos que'l usuariu tien que esperar pa publicar temes";
$lang['Board_email_form'] = "Correu d'Usuariu a traviés del Foru";
$lang['Board_email_form_explain'] = "Los usuarios podrán unviase correos mediante'l Foru";
$lang['Topics_per_page'] = "Temes por Páxina";
$lang['Posts_per_page'] = "Rempuestes por Páxina";
$lang['Hot_threshold'] = "Cantidá de rempuestes pa ser considerau Popular";
$lang['Default_style'] = "Estilu por defeutu";
$lang['Override_style'] = "Ignorar l'estilu del Usuariu";
$lang['Override_style_explain'] = "Utilizarase'l estilu selecionado por defeutu ensin importar la eleción del usuariu";
$lang['Default_language'] = "Llingua por Defeutu";
$lang['Date_format'] = "Formatu de la Fecha";
$lang['System_timezone'] = "Zona Horaria";
$lang['Enable_gzip'] = "Activar la Compresion GZip";
$lang['Enable_prune'] = "Habilitar Pruning nel Foru";
$lang['Allow_HTML'] = "Permitir HTML";
$lang['Allow_BBCode'] = "Permitir BBCode";
$lang['Allowed_tags'] = "Permitir HTML tags";
$lang['Allowed_tags_explain'] = "Separare tags con comes";
$lang['Allow_smilies'] = "Permitir Emoticons";
$lang['Smilies_path'] = "Almacenaxe de la ruta de los Iconos xestuales (emoticons)";
$lang['Smilies_path_explain'] = "Ruta dende'l directoriu phpBB , exemplu images/smilies";
$lang['Allow_sig'] = "Permitir Firmes";
$lang['Max_sig_length'] = "Llargu máximu de la Firma";
$lang['Max_sig_length_explain'] = "Máximu numberu de caracteres ena Firma";
$lang['Allow_name_change'] = "Permitir caudar el Nome d'Usuariu";

$lang['Avatar_settings'] = "Configuración de los Avatars";
$lang['Allow_local'] = "Habilitar galeríes d'Avatars";
$lang['Allow_remote'] = "Habilitar Avatars Remotos";
$lang['Allow_remote_explain'] = "Amosar Avatars guardaos n'otros sitios web";
$lang['Allow_upload'] = "Habilitar upload d'Avatars";
$lang['Max_filesize'] = "Tamañu máximu pa les imáxenes";
$lang['Max_filesize_explain'] = "Llimita la cantidá de bytes que pue tener un Avatar";
$lang['Max_avatar_size'] = "Máximu Tamañu del Avatar";
$lang['Max_avatar_size_explain'] = "(Altura x Anchu en pixels)";
$lang['Avatar_storage_path'] = "Ruta del Avatar";
$lang['Avatar_storage_path_explain'] = "Ruta dientru de phpBB au s'alcuentren los Avatars, exemplu images/avatars";
$lang['Avatar_gallery_path'] = "Ruta de la Galería d'Avatars";
$lang['Avatar_gallery_path_explain'] = "Ruta dientru de phpBB de la galería, ex. images/avatars/gallery";

$lang['COPPA_settings'] = "Configuraciones COPPA";
$lang['COPPA_fax'] = "Númberu de Fax COPPA";
$lang['COPPA_mail'] = "Direción de Correu COPPA";
$lang['COPPA_mail_explain'] = "Esta ye la direción de correu pa que los padres unvien el formulariu COPPA";

$lang['Email_settings'] = "Configuración del Correu";
$lang['Admin_email'] = "Direción de correu del Alministraor";
$lang['Email_sig'] = "Firma";
$lang['Email_sig_explain'] = "Esti textu añadiráse al final de cada correu";
$lang['Use_SMTP'] = "Usar servior SMTP pa Correu";
$lang['Use_SMTP_explain'] = "Diga si usté pue y/o debe unviar los correos por un servior SMTP";
$lang['SMTP_server'] = "Direción SMTP del Servior";
$lang['SMTP_username'] = "Nome d'usuariu del SMTP";
$lang['SMTP_username_explain'] = "Enxerte un nome d'usuariu namás si'l su servior SMTP requiere-ylo";
$lang['SMTP_password'] = "Contraseña del SMTP";
$lang['SMTP_password_explain'] = "Enxerte una contraseña namás si'l su servior SMTP requiere-ylo";

$lang['Disable_privmsg'] = "Mensaxe Privau";
$lang['Inbox_limits'] = "Máxima cantidá de mensaxes ena Bandexa d'Entrada";
$lang['Sentbox_limits'] = "Máxima cantidá de mensaxes ena Bandexa de Salida";
$lang['Savebox_limits'] = "Máxima cantidá de mensaxes ena Carpeta pa Guardar";

$lang['Cookie_settings'] = "Configuración de les Cookies"; 
$lang['Cookie_settings_explain'] = "Esto controla comu s'unvíen les cookies al Navegaor, ena mayoría de los casos la configuración preestablecia sedra más que suficiente. Si necesita camudar esto tea cudiau, ya que si fora mal configurau pue que los sus Usuarios nun puean Coneutase al Foru";
$lang['Cookie_domain'] = "Dominiu de la Cookie";
$lang['Cookie_name'] = "Nome de la Cookie";
$lang['Cookie_path'] = "Ruta de la Cookie";
$lang['Cookie_secure'] = "Cookie segura [ https ]";
$lang['Cookie_secure_explain'] = "Si el so servior ta corriendu via SSL escueya esta opción d'otra mena déxelu deshabilitau";
$lang['Session_length'] = "Duración de la sesión [ segundos ]";

//
// Forum Management
//
$lang['Forum_admin'] = "Alministración del Foru";
$lang['Forum_admin_explain'] = "Dende este panel usté pue añadir, esborriar, editar, y re-ordenar categoríes y Foros";
$lang['Edit_forum'] = "Editar el Foru";
$lang['Create_forum'] = "Facer un nueu Foru";
$lang['Create_category'] = "Crear una nuea Categoría";
$lang['Remove'] = "Quitar";
$lang['Action'] = "Acción";
$lang['Update_order'] = "Autualizar Orden";
$lang['Config_updated'] = "La configuración del Foru actualizose";
$lang['Edit'] = "Editar";
$lang['Delete'] = "Esborriar";
$lang['Move_up'] = "P'arriba";
$lang['Move_down'] = "P'abaxu";
$lang['Resync'] = "sincronizar";
$lang['No_mode'] = "Nengún mou fou selecionau";
$lang['Forum_edit_delete_explain'] = "El siguiente formulariu permitirá-y personalizar les opciones del Foru. Pa la configuración d'usuarios y Foros utilice los enllaces de la izquierda.";

$lang['Move_contents'] = "Mover tolos contenios";
$lang['Forum_delete'] = "Esborriar el Foru";
$lang['Forum_delete_explain'] = "El siguiente formulariu permitirá-y Esborriar dalgún foru (o categoría) y dicir au quier poner tolos Temes y Categoríes.";

$lang['Forum_settings'] = "Configuración Xeneral del Foru";
$lang['Forum_name'] = "Nome del Foru";
$lang['Forum_desc'] = "Descripción";
$lang['Forum_status'] = "Estau del Foru";
$lang['Forum_pruning'] = "Auto-pruning";

$lang['prune_freq'] = 'Revisar temes y edá';//Ver Pruning!
$lang['prune_days'] = "Esborriar temes que nun tienen rempuesta";
$lang['Set_prune_data'] = "Ustá selecionara la opción Auto-pruning pa esti foru pero nun selecionara la frecuencia o cantidá de díes pa'l PRUNE. Por favor torne y faiga los camudaminetos";

$lang['Move_and_Delete'] = "Mover y Esborirar";

$lang['Delete_all_posts'] = "Esborriar tolos Temes";
$lang['Nowhere_to_move'] = "Nun hay sitiu au mover tou";

$lang['Edit_Category'] = "Editar Categoría";
$lang['Edit_Category_explain'] = "Utilice esti formulariu pa Editar categoríes";

$lang['Forums_updated'] = "La información del Foru y les sus categoríes actulizaronse";

$lang['Must_delete_forums'] = "Tien qu'esborriar tolos foros enantes d'esborriar una Categoría";

$lang['Click_return_forumadmin'] = "Calque %sEquí%s pa tornar a l'Alministración del Foru";


//
// Smiley Management
//
$lang['smiley_title'] = "Edición de Smilies";
$lang['smile_desc'] = "Dende esta páxina usté pue añadir, quitar o editar dalgún emoticon pa que los Usuarios utilicenlos nel foru y enos mensaxes Privaos";

$lang['smiley_config'] = "Configuración de Smiley";
$lang['smiley_code'] = "Códigu de Smiley";
$lang['smiley_url'] = "Archivu d'Imaxen del Smiley";
$lang['smiley_emot'] = "Emoción o sentimiento del Smiley";
$lang['smile_add'] = "Añadir un nueu Smiley";
$lang['Smile'] = "Smile";
$lang['Emotion'] = "Emoción";

$lang['Select_pak'] = "Selecione l'archivu .pak";
$lang['replace_existing'] = "Reemplazar Smiles Existentes";
$lang['keep_existing'] = "Mantener Smiles Existentes";
$lang['smiley_import_inst'] = "Usté tien que descomprimir el paquete de Smiles y subir tolos archivos nel directoriu de Smiles pa instalalos correutamente. Llueo selecione la información correuta dende este formulariu pa así poer importar los Smiles";
$lang['smiley_import'] = "Importar paquete de Smiles";
$lang['choose_smile_pak'] = "Escoyer archivu de paquete (.pak)";
$lang['import'] = "Importar Smileys";
$lang['smile_conflicts'] = "Que debería facese en casu de conflictu";
$lang['del_existing_smileys'] = "Esborriar los smiles existentes enantes d'importarlos";
$lang['import_smile_pack'] = "Importar Paquete de Smileys";
$lang['export_smile_pack'] = "Facer un paquete de Smileys";
$lang['export_smiles'] = "Pa crear un paquete de Smiles, de los sus smileys instalaos, calque %sEquí%s pa baxar l'archivu smiles.pak. Nome este archivu de forma correuta pero asegúrese de mantener la extensión .pak. Lluego faiga un archivu zip que contenga tolos smileys amás del archivu .pak.";

$lang['smiley_add_success'] = "Los Smileys foron añadios";
$lang['smiley_edit_success'] = "Los Smileys foron autualizados";
$lang['smiley_import_success'] = "El paquete de Smileys importose.";
$lang['smiley_del_success'] = "Los Smileys esborriaronse.";
$lang['Click_return_smileadmin'] = "Calque %sEquí%s pa tornar al Panel de Smiles";

//
// User Management
//
$lang['User_admin'] = "Alministración d'Usuarios";
$lang['User_admin_explain'] = "Dende equí usté pué camudar la información del usuariu. Pa modificar los permisos d'un Usuariu por favor utilice el Sistema de Permisos d'usuarios y Grupos.";

$lang['Look_up_user'] = "Güeyar Usuariu";

$lang['Admin_user_fail'] = "No se llograra autualizar el perfil del Usuariu";
$lang['Admin_user_updated'] = "El perfil del Usuariu autualizose";
$lang['Click_return_useradmin'] = "Calque %sEquí%s pa tornar al Panel d'Alministración d'Usuarios";

$lang['User_delete'] = "Esborriar Usuariu";
$lang['User_delete_explain'] = "Calque equí pa esborriar esti Usuariu. Tea en cuenta que llueu nun podrá restauralo.";
$lang['User_deleted'] = "L'Usuariu esborriose.";

$lang['User_status'] = "Usuariu Autivu";
$lang['User_allowpm'] = "Pué unviar mensaxes privaos";
$lang['User_allowavatar'] = "Pué amosar el so Avatar";

$lang['Admin_avatar_explain'] = "Dende equí pué güeyar y esborriar l'Avatar del Usuariu";

$lang['User_special'] = "Campos especiales pa Alministraores";
$lang['User_special_explain'] = "Estos campos nun tan disponibles pa que los Usuarios puean camudalos. Dende equí usté pue configurar el status y otres opciones que los Usuarios nun puen camudar.";


//
// Group Management
//
$lang['Group_administration'] = "Alministración de Grupos";
$lang['Group_admin_explain'] = "Dende esti panel pué modificar los Grupos, usté pué esborriar, crear y editar los Grupos existentes. Tamién pué escoyer los Moderaores y camudar el nome del Grupu y la so descripción";
$lang['Error_updating_groups'] = "Ocurriera un error autualizando el Grupu";
$lang['Updated_group'] = "El Grupu autualizose";
$lang['Added_new_group'] = "Fexose'l Nueu Grupu";
$lang['Deleted_group'] = "El Grupu esborriose";
$lang['New_group'] = "Facer Nueu Grupu";
$lang['Edit_group'] = "Editar Grupu";
$lang['group_name'] = "Nome del Grupu";
$lang['group_description'] = "Descrición del Grupu";
$lang['group_moderator'] = "Moderaor del Grupu";
$lang['group_status'] = "Status del Grupu";
$lang['group_open'] = "Grupu Abiertu";
$lang['group_closed'] = "Grupu Trancau";
$lang['group_hidden'] = "Grupu Ocultu";
$lang['group_delete'] = "Esborriar Grupu";
$lang['group_delete_check'] = "Esborriar esti Grupu";
$lang['submit_group_changes'] = "Aceutar Camudamientos";
$lang['reset_group_changes'] = "Anular Camudamientos";
$lang['No_group_name'] = "Tien qu'especificar un Nome pa esti Grupu";
$lang['No_group_moderator'] = "Tien qu'especificar un Moderaor pa esti Grupu";
$lang['No_group_mode'] = "Tien qu'especificar el mou d'esti Grupu, Abiertu o Trancau";
$lang['delete_group_moderator'] = "¿Esborriar l'antigü moderaor del Grupu?";
$lang['delete_moderator_explain'] = "Si ta camudando'l moderaor del Grupu, marque esta casilla para esborriar l'antigü Moderaor del Grupu. Sinon l'Usuariu convertiráse nun miembru regular.";
$lang['Click_return_groupsadmin'] = "Calque %sEquí%s pa tornar al Panel d'Alministración de Grupos.";
$lang['Select_group'] = "Selecione un Grupu";
$lang['Look_up_group'] = "Güeyar un Grupu";
$lang['No_group_action'] = 'Nun s\'especificara una ación'; 


//
// Prune Administration
//
$lang['Forum_Prune'] = "Purga de Foros";
$lang['Forum_Prune_explain'] = "Esto esborriará tolos temes enos que nun se publicaran nueos mensaxes enos díes qu'usté selecionara. Si nun enxerta un númberu entos esborriaranse tolos temes. Nun s'esborriarán temes enos qu'heba encuestes que ten furrulando nin anuncios. Tendrá qu'esborriar estos temes de forma manual.";
$lang['Do_Prune'] = "Facer la purga";
$lang['All_Forums'] = "Tolos Foros";
$lang['Prune_topics_not_posted'] = "Esborriar temes ensin rempuestes d'una antigüedá d'estos díes";
$lang['Topics_pruned'] = "Temes esborriaos";
$lang['Posts_pruned'] = "Mensaxes esborriaos";
$lang['Prune_success'] = "Fexose la purga de los foros";


//
// Word censor
//
$lang['Words_title'] = "Control de Pallabres Censuraes";
$lang['Words_explain'] = "Dende equí usté pué añadir, editar, y quitar pallabres qu'automáticamente sedrán censuraes de sus foros. Estes pallabras nun podrán ser escoxies como nomes d'usuarios. Los Asteriscos (*) son aceutaos enos campos de les pallabres, exemplu *prueba* , prueba* (acapararía pruebalu), *prueba (acapararía enprueba).";
$lang['Word'] = "Pallabra";
$lang['Edit_word_censor'] = "Editar el pallabreru prohibiu";
$lang['Replacement'] = "Reemplazar";
$lang['Add_new_word'] = "Agregar nuea pallabra";
$lang['Update_word'] = "Autualizar el pallabreru prohibiu";

$lang['Must_enter_word'] = "Tien que plumiar una pallabra y su otra pallabra pa'l reemplazu";
$lang['No_word_selected'] = "Nun selecionara una pallabra pa'l reemplazu";

$lang['Word_updated'] = "Camudamientos fechos";
$lang['Word_added'] = "Añadiose la nuea pallabra";
$lang['Word_removed'] = "La pallabra esborriose del pallabreru prohibiu";

$lang['Click_return_wordadmin'] = "Calque %sEquí%s pa tornar al Alministraor de Pallabres Censuraes";


//
// Mass Email
//
$lang['Mass_email_explain'] = "Dende equí usté pué unviar mensaxes de correu a tolos sus Usuarios y Grupos. Al facer esto un correu unviarase dende'l correu alministrativu inidicau previamente. Si unvía esti correu a un Grupu numberosu por favor seya paciente y espere a que termine de cargar la páxina. Ye normal que tarde unos cuantos minutos, notificaraise encuantes fine'l unvio.";
$lang['Compose'] = "Plumiar"; 

$lang['Recipients'] = "Correos"; 
$lang['All_users'] = "A tolos Usuarios";

$lang['Email_successfull'] = "El so correu unviose";
$lang['Click_return_massemail'] = "Calque %sEquí%s pa tornar al Panel pa Unviar correos Masivos";


//
// Ranks admin
//
$lang['Ranks_title'] = "Alministración de Rangos";
$lang['Ranks_explain'] = "Usando esti formulariu usté pue añadir, editar, ver y esborriar rangos. Usté tamién pué facer rangos";

$lang['Add_new_rank'] = "Facer Rangu";

$lang['Rank_title'] = "Títulu del Rangu";
$lang['Rank_special'] = "Selecionar como Rangu Especial";
$lang['Rank_minimum'] = "Mínima cantidá de Mensaxes";
$lang['Rank_maximum'] = "Máxima cantidá de Mensaxes";
$lang['Rank_image'] = "Imáxen del rangu (tea en cuenta la ruta del foru phpBB2)";
$lang['Rank_image_explain'] = "Faiga esto pa definir una pequeña imaxen pa esti rangu";

$lang['Must_select_rank'] = "Tien que selecionar un Rangu";
$lang['No_assigned_rank'] = "Nun selecionara un Rangu";

$lang['Rank_updated'] = "El Rangu autualizose";
$lang['Rank_added'] = "Fexose'l nueu Rangu";
$lang['Rank_removed'] = "El Rangu esborriose";

$lang['Click_return_rankadmin'] = "Calque %sEquí%s pa tornar al Panel d'Alminstración de Rangos";


//
// Disallow Username Admin
//
$lang['Disallow_control'] = "Control d'Almisión d'Usuariu";
$lang['Disallow_explain'] = "Dende equí pué controlar los nomes d'usuario que nun quier que s'empleguen. Pa llograr esto tien q'utilizar comodines con asteriscos (*). Recuerde que nun pué prohibir nomes d'usuario que ya tean coyios. Enantes de prohibir dichos nomes tien qu'esborrialos.";

$lang['Delete_disallow'] = "Esborriar";
$lang['Delete_disallow_title'] = "Esborriar un nome d'usuariu nun permitiu";
$lang['Delete_disallow_explain'] = "Usté pué esborriar nomes d'usuariu nun permitios selecionando'l nome d'usuariu de la llista y calcando en Aceutar";

$lang['Add_disallow'] = "Añadir";
$lang['Add_disallow_title'] = "Añadir un nome d'usuariu nun permitiu";
$lang['Add_disallow_explain'] = "Usté pué nun permitir un nome d'usuariu utilizando máscares con asteriscos(*)";

$lang['No_disallowed'] = "Nomes d'usuarios nun permitios";

$lang['Disallowed_deleted'] = "El nome d'usuariu nun permitiu esborriose";
$lang['Disallow_successful'] = "El nome d'usuariu nun permitiu agregose";
$lang['Disallowed_already'] = "El nome d'usuariu nun permitiu qu'elixera nun pué selecionase. Debiu a que ya ta ena llista, o ta nel Pallabreru Prohibiu, o bien ya ta ena llista d'usuarios nun permitios";

$lang['Click_return_disallowadmin'] = "Calque %sEqui%s pa tornar al Control d'Almisión d'Usuariu";


//
// Styles Admin
//
$lang['Styles_admin'] = "Alministración d'Estilos";
$lang['Styles_explain'] = "Dende equí usté pué fácilmente añadir, quitar y alministrar los estilos (plantilles y temes) disponibles pa los sus usuarios";
$lang['Styles_addnew_explain'] = "La siguiente llista contien tolos temes que tán disponibles pa les plantilles. Los items de la llista nun s'instalaren ena base de los foros phpBB. Pa facer esu namás calque nel enllace que figura al llau de cada opción";

$lang['Select_template'] = "Selecione una Plantilla";

$lang['Style'] = "Estilu";
$lang['Template'] = "Plantilla";
$lang['Install'] = "Instalar";
$lang['Download'] = "Descargar";

$lang['Edit_theme'] = "Editar Tema";
$lang['Edit_theme_explain'] = "Nel siguiente formulariu pué editar la configuración del tema selecionau";

$lang['Create_theme'] = "Facer Tema";
$lang['Create_theme_explain'] = "Utilice'l siguiente formulariu para facer un tema nueu pa la plantilla selecioná. Cuandu enxerte los collores (tien qu'ingresalos n'hexadecimal) nun debe incluir el # . Exemplu: CCCCCC ye válidu, pero #CCCCCC taria mal";

$lang['Export_themes'] = "Exportar Tema";
$lang['Export_explain'] = "Dende esti panel usté podrá exportar el tema pa la plantilla selecioná. Selecione la plantilla de la llista d'abaxu y el programa fairá l'archivu de configuración del tema y asina podrá guardalo. Si nun se pueé grabar l'archivu pué Descargalu. Pa que'l programa puea guardalu usté tien que dar permisu d'escritura a la carpeta de plantilles (template). Pa más información utilice la guia del foru phpBB 2";

$lang['Theme_installed'] = "El tema selecionau instalose";
$lang['Style_removed'] = "L'estilu selecionau quitose de la base datos. Pa quitalo del too tien qu'esborriar el direutoriu apropiau de la carpeta de plantilles (template)";
$lang['Theme_info_saved'] = "La información pa la plantilla selecioná guardose. Agora tien que camudar los permisos en theme_info.cgf y poner el direutoriu de plantilles (template) en mou de namás-llectura";
$lang['Theme_updated'] = "El tema selecionau autualizose. Agora tien qu'exportar la configuración del nueu tema";
$lang['Theme_created'] = "Tema Creau. Agora tien qu'exportar el tema nel archivu de configuración de temes pa asi llograr mantenelu seguru";

$lang['Confirm_delete_style'] = "Ta seguru que quier esborriar esti estilu";

$lang['Download_theme_cfg'] = "Nun pudose exportar l'archivu ya que nun se pudiera escribir nel archivu. Presione el botón d'abaxu pa descargar esti archivu col su navegaor. Una vegá que lo tea descargau pué movelu al direutoriu de les plantilles (template).";
$lang['No_themes'] = "La plantilla que selecionara nun tien temes adxuntos. Pa facer un nueu tema calque en Facer Nueu Tema ena izquierda del panel";
$lang['No_template_dir'] = "No pudo abrise la carpeta de plantilles. Esto pué ser porque te con permisos ensin llectura, o que nun exista.";
$lang['Cannot_remove_style'] = "Nun pué quitase l'estilu selecionau porque ye el que ta por defeuto nel foru. Por favor camude'l que utilizase por defeuto e inténtelu nueamente";
$lang['Style_exists'] = "El nome d'estilu selecionau ya existe, por favor torne patrás y selecione otru distintu";

$lang['Click_return_styleadmin'] = "Calque %sEquí%s pa tornar a l'Alministración d'Estilos";

$lang['Theme_settings'] = "Configuración de Temes";
$lang['Theme_element'] = "Ellementos de Temes";
$lang['Simple_name'] = "Nome simple";
$lang['Value'] = "Valor";
$lang['Save_Settings'] = "Guardar Configuración";

$lang['Stylesheet'] = "CSS Stylesheet";
$lang['Background_image'] = "Imaxen de Fondu";
$lang['Background_color'] = "Collor de Fondu";
$lang['Theme_name'] = "Nome de Tema";
$lang['Link_color'] = "Collor d'Enllace";
$lang['Text_color'] = "Collor de Testu";
$lang['VLink_color'] = "Collor d'Enllace Visitau";
$lang['ALink_color'] = "Collor d'Enllace Autivu";
$lang['HLink_color'] = "Collor d'Enllace Enriba";
$lang['Tr_color1'] = "Tabla Fila Collor 1";
$lang['Tr_color2'] = "Tabla Fila Collor 2";
$lang['Tr_color3'] = "Tabla Fila Collor 3";
$lang['Tr_class1'] = "Tabla Fila Clase 1";
$lang['Tr_class2'] = "Tabla Fila Clase 2";
$lang['Tr_class3'] = "Tabla Fila Clase 3";
$lang['Th_color1'] = "Tabla Encabezau Collor 1";
$lang['Th_color2'] = "Tabla Encabezau Collor 2";
$lang['Th_color3'] = "Tabla Encabezau Collor 3";
$lang['Th_class1'] = "Tabla Encabezau Clase 1";
$lang['Th_class2'] = "Tabla Encabezau Clase 2";
$lang['Th_class3'] = "Tabla Encabezau Clase 3";
$lang['Td_color1'] = "Tabla Celda Collor 1";
$lang['Td_color2'] = "Tabla Celda Collor 2";
$lang['Td_color3'] = "Tabla Celda Collor 3";
$lang['Td_class1'] = "Tabla Celda Clase 1";
$lang['Td_class2'] = "Tabla Celda Clase 2";
$lang['Td_class3'] = "Tabla Celda Clase 3";
$lang['fontface1'] = "Fuente 1";
$lang['fontface2'] = "Fuente 2";
$lang['fontface3'] = "Fuente 3";
$lang['fontsize1'] = "Fuente 1";
$lang['fontsize2'] = "Fuente 2";
$lang['fontsize3'] = "Fuente 3";
$lang['fontcolor1'] = "Fuente Collor 1";
$lang['fontcolor2'] = "Fuente Collor 2";
$lang['fontcolor3'] = "Fuente Collor 3";
$lang['span_class1'] = "Espaciu Clase 1";
$lang['span_class2'] = "Espaciu Clase 2";
$lang['span_class3'] = "Espaciu Clase 3";
$lang['img_poll_size'] = "Imaxen de la Encuesta [px]";
$lang['img_pm_size'] = "Tamañu de Mensaxes Privaos [px]";


//
// Install Process
//
$lang['Welcome_install'] = "Bienvenios a l'Instalación de los foros phpBB 2";
$lang['Initial_config'] = "Configuración Basica";
$lang['DB_config'] = "Configuración de la Base Datos";
$lang['Admin_config'] = "Configuración del Alministraor";
$lang['continue_upgrade'] = "Una vegá descargau l'archivu de configuración calque sobre \"Siguir Autualización\" pa continuar col procesu. Por favor espere que se suba l'archivu de configuración hasta que'l procesu d'autualización fine";
$lang['upgrade_submit'] = "Siguir Autualización";

$lang['Installer_Error'] = "Hebo un ERROR ena Instalación";
$lang['Previous_Install'] = "Nun detectose una Instalación previa";
$lang['Install_db_error'] = "Hebo un ERROR autualizando la Base Datos";

$lang['Re_install'] = "La su instalación previa entá ta autiva. <br /><br />Si quier reinstalar los foros phpBB 2 calque Si nel botón d'abaxu. Por favor tea en cuenta que al realizar esto destruiráse la información existente, nun fairanse copies de seguridá. L'usuariu alministrador y la contraseña qu'usté usaba anteriormente serán creaos nueamente, pero nun otru tipo d'información <br /><br />¡Piense cuidadosamente enantes de calcar SI!";

$lang['Inst_Step_0'] = "Gracies por ellexir phpBB 2. Pa finar la instalación por favor complete los datos requerios abaxu. Tena en cuenta que la Base Datos destiná a los foros ya debería existir. Si ta instalando en una Base Datos q'utiliza OBDC, por exemplu MS Access primeru tien que crear un DNS y depués continuar.";

$lang['Start_Install'] = "Entamar Instalación";
$lang['Finish_Install'] = "Finar Inatalación";

$lang['Default_lang'] = "Llingua por defeutu";
$lang['DB_Host'] = "Nome de Dominiu de la Base Datos / DSN";
$lang['DB_Name'] = "Nome de la base Datos";
$lang['DB_Username'] = "Nome d'usuariu de la base datos";
$lang['DB_Password'] = "Contraseña de la base datos";
$lang['Database'] = "Su Base Datos";
$lang['Install_lang'] = "Elixa la Llingua d'Instalación";
$lang['dbms'] = "Tipu de Base Datos";
$lang['Table_Prefix'] = "Prefixu pa les tables ena Base datos";
$lang['Admin_Username'] = "Nome d'Usuariu del Alministraor";
$lang['Admin_Password'] = "Contraseña del Alministraor";
$lang['Admin_Password_confirm'] = "Contraseña d'accesu del Alministraor [ Confirma ]";

$lang['Inst_Step_2'] = "El su usuariu alministraor y contraseña foron creaos. N'esti puntu el procesu d'Instalación Básica completose. Agora se-y unviará a una pantalla que-y permitirá alministrar la nuea instalación. Por favor asegurese de verificar la Configuración Xeneral y de facer los camudamientos requerios. Gracies por escoyer phpBB 2";

$lang['Unwriteable_config'] = "El su archivu de configuración ta nun mou de non-escritura. Una copia del archivu de configuración podrá descargase cuandu calque el botón siguiente. Usté tien que subir esti ficheru nel mesmu direutoriu au s'atope'l foru phpBB 2. Una vegá que faiga esto tien qu'ingresar usando'l usuariu d'alministraor y contraseña q'usté escoyió nel formulariu anterior y así visitar el Control d'Alministración pa ver la configuración xeneral. Gracies por escoyer phpBB 2";
$lang['Download_config'] = "Descargar Configuración";

$lang['ftp_choose'] = "Escoyer Métodu de Descarga";
$lang['ftp_option'] = "<br />Ya que les extensiones FTP tan disponibles n'esta versión de PHP usté podrá escoyer si quier, mediante FTP, colocar l'archivu nel so llugar automáticamente.";
$lang['ftp_instructs'] = "Usté selecionara subir automáticamente por ftp l'archivu ena cuenta que contien el phpBB 2. Por favor ingrese l'información solicitá pa facilitar el procesu. Tea en cuenta que la ruta FTP tien que ser la ruta exacta al PHPBB 2 comu si fora a subir los archivos usando cualisquier cliente de ftp.";
$lang['ftp_info'] = "Enxerte l'Información del su FTP";
$lang['Attempt_ftp'] = "Intentar subir archivu mediante ftp en forma automática";
$lang['Send_file'] = "Unvíenme l'archivu a min y yo subirelu personalmente por FTP";
$lang['ftp_path'] = "Ruta FTP al phpBB 2";
$lang['ftp_username'] = "Nome d'Usuariu FTP";
$lang['ftp_password'] = "Contraseña FTP";
$lang['Transfer_config'] = "Entamar Transferencia";
$lang['NoFTP_config'] = "L'intentu de subir por ftp l'archivu de configuración fallara. Por favor descárgue l'archivu de configuración y súbalo por FTP en forma manual.";

$lang['Install'] = "Instalar";
$lang['Upgrade'] = "Autualizar";


$lang['Install_Method'] = "Elixa'l so métodu d'Instalación";

$lang['Install_No_Ext'] = "La configuración de PHP nel so servior nun soporta el tipu de base datos selecionau";

$lang['Install_No_PCRE'] = "phpBB2 requier el módulu d'expresiones regulares compatible con Perl pa php, que nun figura comu soportau ens su configuración de php!";

//
// FIIIIIIIIIIIIIIIIIIN!!!!
// -------------------------------------------------

?>