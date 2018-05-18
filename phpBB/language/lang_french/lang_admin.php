<?php
/***************************************************************************
 *                            lang_admin.php [French]
 *                              -------------------
 *     begin                : Sat Dec 16 2000
 *     copyright            : (C) 2001 The phpBB Group
 *     email                : support@phpbb.com
 *
 *     $Id: lang_admin.php,v 1.1 2013/10/03 08:32:41 orynider Exp $
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

/***************************************************************************
 *                         Translation: Informations
 *
 *   Version: 1.0.2
 *   Date: 07/03/2008 19:04:16
 *   Author: Xaphos (Maël Soucaze)
 *   Website: http://www.phpbb.fr/
 *
 ***************************************************************************/

/* CONTRIBUTORS
	2002-12-15	Philip M. White (pwhite@mailhaven.com)
		Fixed many minor grammatical mistakes
*/

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = 'Administration générale';
$lang['Users'] = 'Administration des utilisateurs';
$lang['Groups'] = 'Administration des groupes';
$lang['Forums'] = 'Administration des forums';
$lang['Styles'] = 'Administration des styles';

$lang['Configuration'] = 'Configuration';
$lang['Permissions'] = 'Permissions';
$lang['Manage'] = 'Gestion';
$lang['Disallow'] = 'Interdire des noms';
$lang['Prune'] = 'Délestage';
$lang['Mass_Email'] = 'E-mail de masse';
$lang['Ranks'] = 'Rangs';
$lang['Smilies'] = 'Émoticônes';
$lang['Ban_Management'] = 'Contrôle des bannissements';
$lang['Word_Censor'] = 'Censure de mots';
$lang['Export'] = 'Exporter';
$lang['Create_new'] = 'Créer';
$lang['Add_new'] = 'Ajouter';
$lang['Backup_DB'] = 'Sauvegarder la base de données';
$lang['Restore_DB'] = 'Restaurer la base de données';


//
// Index
//
$lang['Admin'] = 'Administration';
$lang['Not_admin'] = 'Vous n’êtes pas autorisé à administrer ce forum';
$lang['Welcome_phpBB'] = 'Bienvenue sur phpBB';
$lang['Admin_intro'] = 'Nous vous remercions d’avoir sélectionné phpBB comme solution pour votre forum. Cet écran vous donne un aperçu rapide des diverses statistiques de votre forum. Vous pouvez retourner sur cette page en cliquant sur le lien <u>Index de l’administration</u> dans le panneau de gauche. Pour retourner à l’index de votre forum, cliquez sur le logo phpBB qui est également situé dans le panneau de gauche. Les autres liens situés sur le volet à gauche de cet écran vous permettront de contrôler tous les aspects de votre forum. Chaque page contiendra des instructions sur l’utilisation des outils disponibles.';
$lang['Main_index'] = 'Index du forum';
$lang['Forum_stats'] = 'Statistiques du forum';
$lang['Admin_Index'] = 'Index de l’administration';
$lang['Preview_forum'] = 'Aperçu du forum';

$lang['Click_return_admin_index'] = 'Cliquez %sici%s afin de retourner à l’index de l’administration';

$lang['Statistic'] = 'Statistique';
$lang['Value'] = 'Valeur';
$lang['Number_posts'] = 'Nombre de messages';
$lang['Posts_per_day'] = 'Messages par jour';
$lang['Number_topics'] = 'Nombre de sujets';
$lang['Topics_per_day'] = 'Sujets par jour';
$lang['Number_users'] = 'Nombre d’utilisateurs';
$lang['Users_per_day'] = 'Utilisateurs par jour';
$lang['Board_started'] = 'Date d’ouverture du forum';
$lang['Avatar_dir_size'] = 'Taille du répertoire des avatars';
$lang['Database_size'] = 'Taille de la base de données';
$lang['Gzip_compression'] ='Compression GZip';
$lang['Not_available'] = 'Indisponible';

$lang['ON'] = 'Activée'; // This is for GZip compression
$lang['OFF'] = 'Désactivée'; 


//
// DB Utils
//
$lang['Database_Utilities'] = 'Utilitaires de la base de données';

$lang['Restore'] = 'Restaurer';
$lang['Backup'] = 'Sauvegarder';
$lang['Restore_explain'] = 'Cela exécutera une restauration complète de toutes les tables de phpBB à partir d’un fichier de sauvegarde. Si votre serveur le supporte, vous pouvez utiliser un fichier texte compressé en GZip qui sera automatiquement décompressé. <b>ATTENTION :</b> cela écrasera toutes les données existantes. La restauration est un processus pouvant prendre beaucoup de temps, veuillez ne pas vous déplacer de la page avant que l’opération soit terminée.';
$lang['Backup_explain'] = 'Vous pouvez sauvegarder ici toutes les données relatives à votre forum phpBB. Si vous avez créé des tables additionnelles personnalisées dans la même base de données et que vous souhaitez les sauvegarder, veuillez saisir leurs noms, séparés par une virgule, dans la boîte de texte <u>Tables additionnelles</u> ci-dessous. Si votre serveur le supporte, vous pouvez également compresser votre fichier au format GZip afin de réduire sa taille avant de le télécharger.';

$lang['Backup_options'] = 'Options de la sauvegarde';
$lang['Start_backup'] = 'Démarrer la sauvegarde';
$lang['Full_backup'] = 'Sauvegarde complète';
$lang['Structure_backup'] = 'Sauvegarde de la structure uniquement';
$lang['Data_backup'] = 'Sauvegarde des données uniquement';
$lang['Additional_tables'] = 'Tables additionnelles';
$lang['Gzip_compress'] = 'Fichier compressé GZip';
$lang['Select_file'] = 'Sélectionner un fichier';
$lang['Start_Restore'] = 'Démarrer la restauration';

$lang['Restore_success'] = 'La base de données a été restaurée avec succès.<br /><br />Votre forum devrait être tel qu’il l’était lorsque la sauvegarde a été faite.';
$lang['Backup_download'] = 'Votre téléchargement va démarrer sous peu ; veuillez patienter jusqu’à ce qu’il démarre.';
$lang['Backups_not_supported'] = 'Désolé, mais les sauvegardes ne sont actuellement pas supportées par votre système de base de données.';

$lang['Restore_Error_uploading'] = 'Erreur lors du transfert du fichier de sauvegarde';
$lang['Restore_Error_filename'] = 'Problème avec le nom du fichier ; veuillez essayer avec un autre fichier';
$lang['Restore_Error_decompress'] = 'Impossible de décompresser le fichier GZip ; veuillez transférer un fichier texte';
$lang['Restore_Error_no_file'] = 'Aucun fichier n’a été transféré';


//
// Auth pages
//
$lang['Select_a_User'] = 'Sélectionner un utilisateur';
$lang['Select_a_Group'] = 'Sélectionner un groupe';
$lang['Select_a_Forum'] = 'Sélectionner un forum';
$lang['Auth_Control_User'] = 'Contrôle des permissions des utilisateurs'; 
$lang['Auth_Control_Group'] = 'Contrôle des permissions des groupes'; 
$lang['Auth_Control_Forum'] = 'Contrôle des permissions des forums'; 
$lang['Look_up_User'] = 'Rechercher un utilisateur'; 
$lang['Look_up_Group'] = 'Rechercher un groupe'; 
$lang['Look_up_Forum'] = 'Rechercher un forum'; 

$lang['Group_auth_explain'] = 'Vous pouvez modifier ici les permissions et le statut de modérateur assignés à chaque groupe d’utilisateurs. N’oubliez pas qu’en modifiant les permissions des groupes, certaines permissions individuelles peuvent toutefois permettre à un utilisateur d’accéder à un forum, etc. Vous serez averti si tel était le cas.';
$lang['User_auth_explain'] = 'Vous pouvez modifier ici les permissions et le statut de modérateur assignés à chaque utilisateur individuel. N’oubliez pas qu’en modifiant les permissions des groupes, certaines permissions individuelles peuvent toutefois permettre à un utilisateur d’accéder à un forum, etc. Vous serez averti si tel était le cas.';
$lang['Forum_auth_explain'] = 'Vous pouvez modifier ici les niveaux de permissions de chaque forum. Vous disposerez du mode simple et avancé pour réaliser cela, où le mode avancé offre un plus grand contrôle de chaque opération du forum. Rappelez-vous qu’en modifiant le niveau de permissions des forums, chaque utilisateur sera affecté des diverses opérations faites dans celui-ci.';

$lang['Simple_mode'] = 'Mode simple';
$lang['Advanced_mode'] = 'Mode avancé';
$lang['Moderator_status'] = 'Statut de modérateur';

$lang['Allowed_Access'] = 'Accès autorisé';
$lang['Disallowed_Access'] = 'Accès interdit';
$lang['Is_Moderator'] = 'est modérateur';
$lang['Not_Moderator'] = 'n’est pas modérateur';

$lang['Conflict_warning'] = 'Avertissement de conflit d’autorisations';
$lang['Conflict_access_userauth'] = 'Cet utilisateur disposera toujours des droits d’accès sur ce forum à cause de son appartenance à un groupe. Vous devriez modifier les permissions du groupe ou supprimer cet utilisateur du groupe afin de l’empêcher complètement de disposer des droits d’accès. Les groupes (et les forums impliqués) accordant des droits sont indiqués ci-dessous.';
$lang['Conflict_mod_userauth'] = 'Cet utilisateur disposera toujours des droits de modérateur sur ce forum à cause de son appartenance à un groupe. Vous devriez modifier les permissions du groupe ou supprimer cet utilisateur du groupe afin de l’empêcher complètement de disposer des droits de modérateur. Les groupes (et les forums impliqués) accordant des droits sont indiqués ci-dessous.';

$lang['Conflict_access_groupauth'] = 'L’utilisateur (ou les utilisateurs) suivant dispose toujours des droits d’accès sur ce forum à cause des réglages des permissions de l’utilisateur. Vous devriez modifier les permissions de l’utilisateur afin de l’empêcher complètement de disposer des droits d’accès. Les utilisateurs (et les forums impliqués) accordant des droits sont indiqués ci-dessous.';
$lang['Conflict_mod_groupauth'] = 'L’utilisateur (ou les utilisateurs) suivant dispose toujours des droits de modérateur sur ce forum à cause des réglages des permissions de l’utilisateur. Vous devriez modifier les permissions de l’utilisateur afin de l’empêcher complètement de disposer des droits de modérateur. Les utilisateurs (et les forums impliqués) accordant des droits sont indiqués ci-dessous.';

$lang['Public'] = 'Public';
$lang['Private'] = 'Privé';
$lang['Registered'] = 'Inscrit';
$lang['Administrators'] = 'Administrateurs';
$lang['Hidden'] = 'Invisible';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'TOUS';
$lang['Forum_REG'] = 'INSCRITS';
$lang['Forum_PRIVATE'] = 'PRIVÉ';
$lang['Forum_MOD'] = 'MODÉRATEURS';
$lang['Forum_ADMIN'] = 'ADMINISTRATEURS';

$lang['View'] = 'Voir';
$lang['Read'] = 'Lire';
$lang['Post'] = 'Publier';
$lang['Reply'] = 'Répondre';
$lang['Edit'] = 'Éditer';
$lang['Delete'] = 'Supprimer';
$lang['Sticky'] = 'Note';
$lang['Announce'] = 'Annonce'; 
$lang['Vote'] = 'Voter';
$lang['Pollcreate'] = 'Créer un sondage';

$lang['Permissions'] = 'Permissions';
$lang['Simple_Permission'] = 'Permissions simples';

$lang['User_Level'] = 'Niveau de l’utilisateur'; 
$lang['Auth_User'] = 'Utilisateur';
$lang['Auth_Admin'] = 'Administrateur';
$lang['Group_memberships'] = 'Adhérents du groupe d’utilisateurs';
$lang['Usergroup_members'] = 'Ce groupe est composé des membres suivants';

$lang['Forum_auth_updated'] = 'Les permissions des forums ont été mises à jour avec succès';
$lang['User_auth_updated'] = 'Les permissions des utilisateurs ont été mises à jour avec succès';
$lang['Group_auth_updated'] = 'Les permissions des groupes ont été mises à jour avec succès';

$lang['Auth_updated'] = 'Les permissions ont été mises à jour avec succès';
$lang['Click_return_userauth'] = 'Cliquez %sici%s pour retourner aux permissions des utilisateurs';
$lang['Click_return_groupauth'] = 'Cliquez %sici%s pour retourner aux permissions des groupes';
$lang['Click_return_forumauth'] = 'Cliquez %sici%s pour retourner aux permissions des forums';


//
// Banning
//
$lang['Ban_control'] = 'Contrôle des bannissements';
$lang['Ban_explain'] = 'Vous pouvez contrôler ici le bannissement d’utilisateurs. Vous pouvez faire cela en bannissant un ou des utilisateurs spécifiques ou un ou des intervalles d’adresses IP ou de noms d’hôtes. Ces méthodes empêcheront un utilisateur d’atteindre les pages votre forum. Afin d’empêcher un utilisateur de s’inscrire sous un nom d’utilisateur différent, vous pouvez également bannir les adresses e-mail. Veuillez noter que bannir uniquement une adresse e-mail n’empêchera pas l’utilisateur concerné de se connecter ou de publier des messages sur votre forum. Vous devrez utiliser une des deux premières méthodes citées afin de réaliser cela.';
$lang['Ban_explain_warn'] = 'Veuillez noter que la saisie d’un intervalle d’adresses IP ne prendra en compte que les adresses situées entre la première et la dernière adresse IP. Des essais seront effectués afin de réduire le nombre d’adresses ajoutées à la base de données en ajoutant automatiquement des jokers où cela est approprié. Si vous souhaitez tout de même saisir un intervalle, essayez de le rendre court ou au mieux, saisissez des adresses spécifiques.';

$lang['Select_username'] = 'Sélectionner un nom d’utilisateur';
$lang['Select_ip'] = 'Sélectionner une adresse IP';
$lang['Select_email'] = 'Sélectionner une adresse e-mail';

$lang['Ban_username'] = 'Bannir un ou plusieurs utilisateurs spécifiques';
$lang['Ban_username_explain'] = 'Vous pouvez bannir plusieurs utilisateurs en une fois en utilisant la combinaison appropriée de la souris et du clavier de votre ordinateur et de votre navigateur Internet';

$lang['Ban_IP'] = 'Bannir une ou plusieurs adresses IP ou noms d’hôtes';
$lang['IP_hostname'] = 'Adresses IP ou noms d’hôtes';
$lang['Ban_IP_explain'] = 'Afin de spécifier plusieurs adresses IP ou plusieurs noms d’hôtes différents, veuillez les séparer par une virgule. Afin de spécifier un intervalle d’adresses IP, veuillez séparer le début et la fin par un tiret (-). Afin de spécifier un joker, veuillez utiliser un astérisque (*).';

$lang['Ban_email'] = 'Bannir une ou plusieurs adresses e-mail';
$lang['Ban_email_explain'] = 'Afin de spécifier plusieurs adresses e-mail, veuillez les séparer par une virgule. Afin de spécifier un nom d’utilisateur joker, veuillez utiliser un astérisque (*), comme *@hotmail.com';

$lang['Unban_username'] = 'Débannir un ou plusieurs utilisateurs spécifiques';
$lang['Unban_username_explain'] = 'Vous pouvez débannir plusieurs utilisateurs en une fois en utilisant la combinaison appropriée de la souris et du clavier de votre ordinateur et de votre navigateur Internet';

$lang['Unban_IP'] = 'Débannir une ou plusieurs adresses IP ou noms d’hôtes';
$lang['Unban_IP_explain'] = 'Vous pouvez débannir plusieurs adresses IP ou plusieurs noms d’hôtes en une fois en utilisant la combinaison appropriée de la souris et du clavier de votre ordinateur et de votre navigateur Internet';

$lang['Unban_email'] = 'Débannir une ou plusieurs adresses e-mail';
$lang['Unban_email_explain'] = 'Vous pouvez débannir plusieurs adresses e-mail en une fois en utilisant la combinaison appropriée de la souris et du clavier de votre ordinateur et de votre navigateur Internet';

$lang['No_banned_users'] = 'Aucun nom d’utilisateur n’a été banni';
$lang['No_banned_ip'] = 'Aucune adresse IP n’a été bannie';
$lang['No_banned_email'] = 'Aucune adresse e-mail n’a été bannie';

$lang['Ban_update_sucessful'] = 'La lise des bannissements a été mise à jour avec succès';
$lang['Click_return_banadmin'] = 'Cliquez %sici%s afin de retourner au contrôle des bannissements';


//
// Configuration
//
$lang['General_Config'] = 'Configuration générale';
$lang['Config_explain'] = 'Le formulaire ci-dessous vous permet de personnaliser toutes les options générales de votre forum. Pour les configurations des utilisateurs et des forums, veuillez utiliser les liens appropriés situés sur le volet de gauche.';

$lang['Click_return_config'] = 'Cliquez %sici%s afin de retourner à la configuration générale';

$lang['General_settings'] = 'Réglages généraux du forum';
$lang['Server_name'] = 'Nom de domaine';
$lang['Server_name_explain'] = 'Le nom de domaine à partir duquel ce forum fonctionne';
$lang['Script_path'] = 'Chemin du script';
$lang['Script_path_explain'] = 'Le chemin où phpBB2 est installé par rapport au nom de domaine';
$lang['Server_port'] = 'Port du serveur';
$lang['Server_port_explain'] = 'Le port sous lequel fonctionne votre serveur, souvent 80. Ne le modifiez que s’il est différent';
$lang['Site_name'] = 'Nom du site';
$lang['Site_desc'] = 'Description du site';
$lang['Board_disable'] = 'Désactiver le forum';
$lang['Board_disable_explain'] = 'Cela rendra le forum indisponible aux utilisateurs. Les administrateurs pourront toutefois accéder au panneau de contrôle de l’administrateur.';
$lang['Acct_activation'] = 'Activation du compte';
$lang['Acc_None'] = 'Aucune'; // These three entries are the type of activation
$lang['Acc_User'] = 'Utilisateur';
$lang['Acc_Admin'] = 'Administrateur';

$lang['Abilities_settings'] = 'Réglages de base des utilisateurs et des forums';
$lang['Max_poll_options'] = 'Nombre maximal d’options des sondages';
$lang['Flood_Interval'] = 'Intervalle de flood';
$lang['Flood_Interval_explain'] = 'Nombre de secondes durant lequel un utilisateur doit patienter avant de pouvoir publier de nouveau'; 
$lang['Board_email_form'] = 'Envoi d’e-mail par le forum';
$lang['Board_email_form_explain'] = 'Les utilisateurs pourront envoyer des e-mail aux autres utilisateurs par ce forum';
$lang['Topics_per_page'] = 'Sujets par page';
$lang['Posts_per_page'] = 'Messages par page';
$lang['Hot_threshold'] = 'Seuil de popularité des messages';
$lang['Default_style'] = 'Style par défaut';
$lang['Override_style'] = 'Remplacer le style des utilisateurs';
$lang['Override_style_explain'] = 'Remplace le style des utilisateurs avec celui par défaut';
$lang['Default_language'] = 'Langue par défaut';
$lang['Date_format'] = 'Format de la date';
$lang['System_timezone'] = 'Fuseau horaire';
$lang['Enable_gzip'] = 'Activer la compression GZip';
$lang['Enable_prune'] = 'Activer le délestage du forum';
$lang['Allow_HTML'] = 'Autoriser l’HTML';
$lang['Allow_BBCode'] = 'Autoriser le BBCode';
$lang['Allowed_tags'] = 'Balises HTML autorisées';
$lang['Allowed_tags_explain'] = 'Séparez les balises par une virgule';
$lang['Allow_smilies'] = 'Autoriser les émoticônes';
$lang['Smilies_path'] = 'Chemin de stockage des émoticônes';
$lang['Smilies_path_explain'] = 'Le chemin depuis la racine de votre répertoire phpBB, ex : images/smiles';
$lang['Allow_sig'] = 'Autoriser les signatures';
$lang['Max_sig_length'] = 'Longueur maximale de la signature';
$lang['Max_sig_length_explain'] = 'Nombre de caractères maximum dans les signatures des utilisateurs';
$lang['Allow_name_change'] = 'Autoriser la modification du nom d’utilisateur';

$lang['Avatar_settings'] = 'Réglages des avatars';
$lang['Allow_local'] = 'Activer la galerie des avatars';
$lang['Allow_remote'] = 'Activer les avatars à distance';
$lang['Allow_remote_explain'] = 'Les avatars situés sur un autre site Internet';
$lang['Allow_upload'] = 'Activer le transfert des avatars';
$lang['Max_filesize'] = 'Taille maximale de l’avatar';
$lang['Max_filesize_explain'] = 'Valable pour les avatars transférés';
$lang['Max_avatar_size'] = 'Dimensions maximales de l’avatar';
$lang['Max_avatar_size_explain'] = '(hauteur x largeur en pixels)';
$lang['Avatar_storage_path'] = 'Chemin de stockage des avatars';
$lang['Avatar_storage_path_explain'] = 'Le chemin depuis la racine de votre répertoire phpBB, ex : images/avatars';
$lang['Avatar_gallery_path'] = 'Chemin de la galerie des avatars';
$lang['Avatar_gallery_path_explain'] = 'Le chemin depuis la racine de votre répertoire phpBB, ex : images/avatars/gallery';

$lang['COPPA_settings'] = 'Réglages de la COPPA';
$lang['COPPA_fax'] = 'Numéro de fax de la COPPA';
$lang['COPPA_mail'] = 'Adresse postale de la COPPA';
$lang['COPPA_mail_explain'] = 'Ceci est l’adresse postale où les parents doivent envoyer le formulaire d’inscription de la COPPA';

$lang['Email_settings'] = 'Réglages des e-mail';
$lang['Admin_email'] = 'Adresse e-mail de l’administrateur';
$lang['Email_sig'] = 'Signature de l’e-mail';
$lang['Email_sig_explain'] = 'Ce texte sera inséré dans tous les e-mail que le forum enverra';
$lang['Use_SMTP'] = 'Utiliser un serveur SMTP pour l’envoi d’e-mail';
$lang['Use_SMTP_explain'] = 'Envoi les e-mail par l’intermédiaire de ce serveur au lieu d’utiliser la fonction e-mail locale';
$lang['SMTP_server'] = 'Adresse du serveur SMTP';
$lang['SMTP_username'] = 'Nom d’utilisateur SMTP';
$lang['SMTP_username_explain'] = 'Ne saisir le nom d’utilisateur que si votre serveur SMTP le demande';
$lang['SMTP_password'] = 'Mot de passe SMTP';
$lang['SMTP_password_explain'] = 'Ne saisir le mot de passe que si votre serveur SMTP le demande';

$lang['Disable_privmsg'] = 'Messagerie privée';
$lang['Inbox_limits'] = 'Messages maximum dans la boîte de réception';
$lang['Sentbox_limits'] = 'Messages maximum dans la boîte d’envoi';
$lang['Savebox_limits'] = 'Messages maximum dans les archives';

$lang['Cookie_settings'] = 'Réglages du cookie'; 
$lang['Cookie_settings_explain'] = 'Ces informations définissent la méthode d’envoi aux navigateurs des utilisateurs. Dans la plupart des cas, les valeurs par défaut sont suffisantes, mais il se peut que vous ayez besoin de les modifier. Des réglages incorrects pourraient provoquer des déconnexions chez vos utilisateurs';
$lang['Cookie_domain'] = 'Domaine du cookie';
$lang['Cookie_name'] = 'Nom du cookie';
$lang['Cookie_path'] = 'Chemin du cookie';
$lang['Cookie_secure'] = 'Cookie sécurisé';
$lang['Cookie_secure_explain'] = 'Si votre serveur fonctionne par l’intermédiaire d’SSL, activez cette fonctionnalité';
$lang['Session_length'] = 'Durée de la session [ secondes ]';

// Visual Confirmation
$lang['Visual_confirm'] = 'Activer la confirmation visuelle';
$lang['Visual_confirm_explain'] = 'Les utilisateurs devront saisir un code situé dans une image lors de leur inscription.';

// Autologin Keys - added 2.0.18
$lang['Allow_autologin'] = 'Autoriser les connexions automatiques';
$lang['Allow_autologin_explain'] = 'Autorise les utilisateurs à se connecter automatiquement lorsqu’ils visitent le forum';
$lang['Autologin_time'] = 'Expiration de la clé de la connexion automatique';
$lang['Autologin_time_explain'] = 'Durée en jours de validité de la clé de la connexion automatique si les utilisateurs ne visitent pas le forum. Si cela est réglé sur zéro, elle n’expirera jamais.';

// Search Flood Control - added 2.0.20
$lang['Search_Flood_Interval'] = 'Intervalle de flood de la recherche';
$lang['Search_Flood_Interval_explain'] = 'Nombre de secondes durant lequel un utilisateur devra patienter entre chaque recherche'; 

//
// Forum Management
//
$lang['Forum_admin'] = 'Administration des forums';
$lang['Forum_admin_explain'] = 'De ce panneau, vous pouvez ajouter, supprimer, éditer, réorganiser et resynchroniser les catégories et les forums.';
$lang['Edit_forum'] = 'Éditer le forum';
$lang['Create_forum'] = 'Créer un nouveau forum';
$lang['Create_category'] = 'Créer une nouvelle catégorie';
$lang['Remove'] = 'Supprimer';
$lang['Action'] = 'Action';
$lang['Update_order'] = 'Mettre à jour l’ordre';
$lang['Config_updated'] = 'La configuration du forum a été mise à jour avec succès';
$lang['Edit'] = 'Éditer';
$lang['Delete'] = 'Supprimer';
$lang['Move_up'] = 'Monter';
$lang['Move_down'] = 'Descendre';
$lang['Resync'] = 'Resynchroniser';
$lang['No_mode'] = 'Aucun mode n’a été réglé';
$lang['Forum_edit_delete_explain'] = 'Le formulaire ci-dessous vous permettra de personnaliser toutes les options générales du forum. Pour ce qui concerne les configurations des utilisateurs ou des forums, veuillez utiliser les liens situés sur le volet de gauche.';

$lang['Move_contents'] = 'Déplacer tout le contenu';
$lang['Forum_delete'] = 'Supprimer un forum';
$lang['Forum_delete_explain'] = 'Le formulaire ci-dessous vous permettra de supprimer un forum ou une catégorie et de déplacer tous les sujets ou les forums qu’il contient où vous souhaitez.';

$lang['Status_locked'] = 'Verrouillé';
$lang['Status_unlocked'] = 'Déverrouillé';
$lang['Forum_settings'] = 'Réglages généraux des forums';
$lang['Forum_name'] = 'Nom du forum';
$lang['Forum_desc'] = 'Description';
$lang['Forum_status'] = 'Statut du forum';
$lang['Forum_pruning'] = 'Délestage automatique';

$lang['prune_freq'] = 'Vérifier l’âge des sujets tous les';
$lang['prune_days'] = 'Supprimer les sujet n’ayant obtenus aucune réponse depuis';
$lang['Set_prune_data'] = 'Vous souhaitez activer le délestage automatique dans ce forum mais nous n’avez pas réglé sa fréquence ou son nombre de jours. Veuillez apporter ces réglages.';

$lang['Move_and_Delete'] = 'Déplacer et supprimer';

$lang['Delete_all_posts'] = 'Supprimer tous les messages';
$lang['Nowhere_to_move'] = 'Nulle part à déplacer';

$lang['Edit_Category'] = 'Éditer la catégorie';
$lang['Edit_Category_explain'] = 'Utilisez ce formulaire afin de modifier le nom de la catégorie.';

$lang['Forums_updated'] = 'Les informations sur le forum ou sur la catégorie ont été mises à jours avec succès';

$lang['Must_delete_forums'] = 'Vous devez supprimer tous les forums de cette catégorie avant de pouvoir la supprimer';

$lang['Click_return_forumadmin'] = 'Cliquez %sici%s afin de retourner à l’administration des forums';


//
// Smiley Management
//
$lang['smiley_title'] = 'Utilitaire d’édition des émoticônes';
$lang['smile_desc'] = 'De cette page vous pouvez ajouter, supprimer et éditer les émoticônes que vous utilisateurs utilisent dans leurs messages et messages privés.';

$lang['smiley_config'] = 'Configuration des émoticônes';
$lang['smiley_code'] = 'Code de l’émoticône';
$lang['smiley_url'] = 'Image de l’émoticône';
$lang['smiley_emot'] = 'Émotion';
$lang['smile_add'] = 'Ajouter une nouvelle émoticône';
$lang['Smile'] = 'Émoticône';
$lang['Emotion'] = 'Émotion';

$lang['Select_pak'] = 'Sélectionner une archive d’émoticônes .pak';
$lang['replace_existing'] = 'Remplacer l’émoticône existante';
$lang['keep_existing'] = 'Préserver l’émoticône existante';
$lang['smiley_import_inst'] = 'Vous devez extraire l’archive d’émoticônes et transférer tous les fichiers dans le répertoire propre aux émoticônes pour votre installation. Cela sélectionnera l’information correcte dans ce formulaire afin d’importer l’archive d’émoticônes.';
$lang['smiley_import'] = 'Importer l’archive d’émoticônes';
$lang['choose_smile_pak'] = 'Sélectionner une archive d’émoticônes .pak';
$lang['import'] = 'Importer les émoticônes';
$lang['smile_conflicts'] = 'Que doit-il être fait en cas de conflits ?';
$lang['del_existing_smileys'] = 'Supprimer les émoticônes existantes avant l’importation';
$lang['import_smile_pack'] = 'Importer l’archive d’émoticônes';
$lang['export_smile_pack'] = 'Créer une archive d’émoticônes';
$lang['export_smiles'] = 'Pour créer une archive d’émoticônes de vos émoticônes installées existantes, veuillez cliquer %sici%s afin de télécharger le fichier smiles.pak. Renommez le fichier correctement en préservant l’extension .pak. Cela créera un fichier .zip qui contiendra toutes les images et les configurations de vos émoticônes.';

$lang['smiley_add_success'] = 'L’émoticône a été ajoutée avec succès';
$lang['smiley_edit_success'] = 'L’émoticône a été mise à jour avec succès';
$lang['smiley_import_success'] = 'L’archive d’émoticônes a été importée avec succès !';
$lang['smiley_del_success'] = 'L’émoticône a été supprimée avec succès';
$lang['Click_return_smileadmin'] = 'Cliquez %sici%s afin de retourner à la configuration des émoticônes';

$lang['Confirm_delete_smiley'] = 'Êtes-vous sûr de vouloir supprimer cette émoticône ?';

//
// User Management
//
$lang['User_admin'] = 'Administration des utilisateurs';
$lang['User_admin_explain'] = 'Vous pouvez modifier ici les informations et certaines options de vos utilisateurs. Pour modifier les permissions des utilisateurs, veuillez utiliser le système de permissions des utilisateurs et des groupes d’utilisateurs.';

$lang['Look_up_user'] = 'Rechercher un utilisateur';

$lang['Admin_user_fail'] = 'Il n’a pas été possible de mettre à jour le profil de l’utilisateur.';
$lang['Admin_user_updated'] = 'Le profil de l’utilisateur a été mis à jour avec succès.';
$lang['Click_return_useradmin'] = 'Cliquez %sici%s afin de retourner à l’administration des utilisateurs';

$lang['User_delete'] = 'Supprimer cet utilisateur';
$lang['User_delete_explain'] = 'Cliquez ici afin de supprimer cet utilisateur. Cette opération est irréversible.';
$lang['User_deleted'] = 'L’utilisateur a été supprimé avec succès.';

$lang['User_status'] = 'L’utilisateur est actif';
$lang['User_allowpm'] = 'Peut envoyer des messages privés';
$lang['User_allowavatar'] = 'Peut afficher un avatar';

$lang['Admin_avatar_explain'] = 'Vous pouvez consulter et supprimer ici l’avatar actuel de l’utilisateur.';

$lang['User_special'] = 'Champs spéciaux réservés à l’administrateur';
$lang['User_special_explain'] = 'Ces champs ne peuvent pas être modifiés par les utilisateurs. Vous pouvez régler ici leurs statuts et les autres options qui ne sont pas fournies aux utilisateurs.';


//
// Group Management
//
$lang['Group_administration'] = 'Administration des groupes';
$lang['Group_admin_explain'] = 'De ce panneau vous pouvez administrer tous les groupes d’utilisateurs. Vous pouvez supprimer, créer et éditer les groupes d’utilisateurs existants. Vous pouvez choisir des responsables, ouvrir ou fermer le statut d’un groupe d’utilisateurs et modifier son nom et sa description';
$lang['Error_updating_groups'] = 'Une erreur est survenue lors de la mise à jour des groupes d’utilisateurs';
$lang['Updated_group'] = 'Le groupe d’utilisateurs a été mis à jour avec succès';
$lang['Added_new_group'] = 'Le nouveau groupe d’utilisateurs a été crée avec succès';
$lang['Deleted_group'] = 'Le groupe d’utilisateurs a été supprimé avec succès';
$lang['New_group'] = 'Créer un nouveau groupe';
$lang['Edit_group'] = 'Éditer le groupe';
$lang['group_name'] = 'Nom du groupe';
$lang['group_description'] = 'Description du groupe';
$lang['group_moderator'] = 'Responsable du groupe';
$lang['group_status'] = 'Statut du groupe';
$lang['group_open'] = 'Groupe ouvert';
$lang['group_closed'] = 'Groupe fermé';
$lang['group_hidden'] = 'Groupe invisible';
$lang['group_delete'] = 'Supprimer un groupe';
$lang['group_delete_check'] = 'Supprimer ce groupe';
$lang['submit_group_changes'] = 'Envoyer les modifications';
$lang['reset_group_changes'] = 'Remise à zéro des modifications';
$lang['No_group_name'] = 'Vous devez saisir le nom de ce groupe';
$lang['No_group_moderator'] = 'Vous devez spécifier le responsable du groupe';
$lang['No_group_mode'] = 'Vous devez spécifier le statut du groupe, ouvert ou fermé';
$lang['No_group_action'] = 'Aucune action n’a été spécifiée';
$lang['delete_group_moderator'] = 'Supprimer l’ancien responsable du groupe ?';
$lang['delete_moderator_explain'] = 'Si vous souhaitez modifier le responsable du groupe, cochez cette case afin de supprimer l’ancien responsable du groupe. Dans le cas contraire, ne la cochez pas et l’utilisateur deviendra simplement un membre du groupe.';
$lang['Click_return_groupsadmin'] = 'Cliquez %sici%s afin de retourner à l’administration des groupes.';
$lang['Select_group'] = 'Sélectionner un groupe';
$lang['Look_up_group'] = 'Rechercher un groupe';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Délester un forum';
$lang['Forum_Prune_explain'] = 'Cela supprimera tous les sujets qui n’ont pas été publiés dans le nombre de jours que vous avez sélectionné. Si vous ne souhaitez pas saisir un nombre, tous les sujets seront alors supprimés. Cela ne supprimera ni sujets dans lesquels un sondage est en cours, ni les annonces. Vous devrez supprimer ces sujets manuellement.';
$lang['Do_Prune'] = 'Réaliser le délestage';
$lang['All_Forums'] = 'Tous les forums';
$lang['Prune_topics_not_posted'] = 'Délester les sujets sans réponse à partir de ce nombre de jours';
$lang['Topics_pruned'] = 'Sujets délestés';
$lang['Posts_pruned'] = 'Messages délestés';
$lang['Prune_success'] = 'Les forums ont été délestés avec succès';


//
// Word censor
//
$lang['Words_title'] = 'Censure de mots';
$lang['Words_explain'] = 'De ce panneau de contrôle vous pouvez ajouter, éditer et supprimer les mots qui seront automatiquement censurés sur votre forum. De plus, il ne sera plus possible de s’inscrire avec un nom d’utilisateur contenant un de ces mots. Les jokers (*) sont acceptés dans le champ du mot. Par exemple, *test* censurera détestable, test* censurera testament, *test censurera contest.';
$lang['Word'] = 'Mot';
$lang['Edit_word_censor'] = 'Éditer la censure du mot';
$lang['Replacement'] = 'Remplacement';
$lang['Add_new_word'] = 'Ajouter un nouveau mot';
$lang['Update_word'] = 'Mettre à jour la censure du mot';

$lang['Must_enter_word'] = 'Vous devez saisir un mot et son remplacement';
$lang['No_word_selected'] = 'Aucun mot n’a été sélectionné pour l’édition';

$lang['Word_updated'] = 'La censure du mot que vous avez sélectionnée a été mise à jour avec succès';
$lang['Word_added'] = 'La censure du mot a été ajoutée avec succès';
$lang['Word_removed'] = 'La censure du mot que vous avez sélectionnée a été supprimée avec succès';

$lang['Click_return_wordadmin'] = 'Cliquez %sici%s afin de retourner à la censure de mots';

$lang['Confirm_delete_word'] = 'Êtes-vous sûr de vouloir supprimer la censure du mot sélectionnée ?';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Vous pouvez envoyer ici des messages e-mail à tous les utilisateurs ou groupes d’utilisateurs de votre forum. Pour réaliser cela, un e-mail sera envoyé à partir de l’adresse e-mail que vous avez spécifiée avec une copie envoyée à tous les destinataires. Si vous envoyez un e-mail de masse à de nombreux utilisateurs, merci de patienter et de ne pas quitter la page le temps de l’envoi. Il est normal qu’un e-mail un masse prenne un certain temps, vous serez averti lorsque le script aura terminé';
$lang['Compose'] = 'Composer'; 

$lang['Recipients'] = 'Destinataires'; 
$lang['All_users'] = 'Tous les utilisateurs';

$lang['Email_successfull'] = 'Votre message a été envoyé avec succès';
$lang['Click_return_massemail'] = 'Cliquez %sici%s afin de retourner au formulaire de l’e-mail de masse';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Administration des rangs';
$lang['Ranks_explain'] = 'En utilisant ce formulaire vous pouvez ajouter, éditer, consulter et supprimer des rangs. Vous pouvez également créer des rangs personnalisés qui seront mis en place à des utilisateurs spécifiques par l’intermédiaire de l’outil de gestion des utilisateurs';

$lang['Add_new_rank'] = 'Ajouter un nouveau rang';

$lang['Rank_title'] = 'Titre du rang';
$lang['Rank_special'] = 'Définir comme rang spécial';
$lang['Rank_minimum'] = 'Messages minimum';
$lang['Rank_maximum'] = 'Messages maximum';
$lang['Rank_image'] = 'Image du rang';
$lang['Rank_image_explain'] = 'Utilisez cela afin de définir une petite image associée avec le rang. Elle est relative au chemin à la racine de phpBB';

$lang['Must_select_rank'] = 'Vous devez sélectionner un rang';
$lang['No_assigned_rank'] = 'Aucun rang spécial n’a été défini';

$lang['Rank_updated'] = 'Le rang a été mis à jour avec succès';
$lang['Rank_added'] = 'Le rang a été ajouté avec succès';
$lang['Rank_removed'] = 'Le rang a été supprimé avec succès';
$lang['No_update_ranks'] = 'Le rang a été supprimé avec succès. Cependant, les comptes des utilisateurs utilisant ce rang n’ont pas été mis à jour. Vous devez réinitialiser manuellement le rang sur ces comptes';

$lang['Click_return_rankadmin'] = 'Cliquez %sici%s afin de retourner à l’administration des rangs';

$lang['Confirm_delete_rank'] = 'Êtes-vous sûr de vouloir supprimer ce rang ?';

//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Interdiction de noms d’utilisateurs';
$lang['Disallow_explain'] = 'Vous pouvez contrôler ici les noms d’utilisateurs qui ne sont pas autorisés à être utilisés. Les noms d’utilisateurs interdits peuvent contenir un joker (*). Veuillez noter que vous ne pouvez pas interdire un nom d’utilisateur qui a déjà été enregistré. Vous devez supprimer en premier lieu l’utilisateur, puis interdire son nom d’utilisateur.';

$lang['Delete_disallow'] = 'Supprimer';
$lang['Delete_disallow_title'] = 'Supprimer un nom d’utilisateur interdit';
$lang['Delete_disallow_explain'] = 'Vous pouvez supprimer un nom d’utilisateur interdit en sélectionnant celui-ci dans la liste et en cliquant sur supprimer';

$lang['Add_disallow'] = 'Ajouter';
$lang['Add_disallow_title'] = 'Ajouter un nom d’utilisateur interdit';
$lang['Add_disallow_explain'] = 'Vous pouvez interdire un nom d’utilisateur en utilisant un joker (*) afin de remplacer n’importe quel caractère';

$lang['No_disallowed'] = 'Aucun nom d’utilisateur interdit';

$lang['Disallowed_deleted'] = 'Le nom d’utilisateur interdit a été supprimé avec succès';
$lang['Disallow_successful'] = 'Le nom d’utilisateur interdit a été ajouté avec succès';
$lang['Disallowed_already'] = 'Vous ne pouvez pas interdire ce nom d’utilisateur. Soit il existe déjà dans cette liste, soit il existe dans la liste de la censure de mots, soit le nom d’utilisateur est déjà enregistré.';

$lang['Click_return_disallowadmin'] = 'Cliquez %sici%s afin de retourner à l’interdiction de noms d’utilisateurs';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Administration des styles';
$lang['Styles_explain'] = 'En utilisant cet outil vous pouvez ajouter, supprimer et gérer les styles (templates et thèmes) disponibles à vos utilisateurs';
$lang['Styles_addnew_explain'] = 'La liste suivante contient tous les thèmes qui sont disponibles aux templates que vous avez actuellement. Les objets de cette liste ne sont pas encore installés dans la base de données de phpBB. Pour installer un thème, cliquez tout simplement sur le lien d’installation ci-dessous.';

$lang['Select_template'] = 'Sélectionner un template';

$lang['Style'] = 'Style';
$lang['Template'] = 'Template';
$lang['Install'] = 'Installer';
$lang['Download'] = 'Télécharger';

$lang['Edit_theme'] = 'Éditer un thème';
$lang['Edit_theme_explain'] = 'Dans le formulaire ci-dessous, vous pouvez éditer les réglages du thème sélectionné';

$lang['Create_theme'] = 'Créer un thème';
$lang['Create_theme_explain'] = 'Utilisez le formulaire ci-dessous afin de créer un nouveau thème pour un template sélectionné. Lors de la mise en place des couleurs (pour laquelle vous pouvez utiliser la notation hexadécimale), vous ne devez pas inclure #. Par exemple, CCCCCC est correct alors que #CCCCCC ne l’est pas';

$lang['Export_themes'] = 'Exporter des thèmes';
$lang['Export_explain'] = 'De ce panneau, vous pouvez exporter le thème pour un template sélectionné. Sélectionnez le template à partir de la liste ci-dessous et le script créera le fichier de configuration du thème et essaiera de le sauvegarder vers le répertoire du template. S’il n’arrive pas à sauvegarder le fichier, il vous fournira une option afin de le télécharger. Vous devez en premier lieu attribuer au répertoire du template les droits d’écritures nécessaires à la sauvegarde du fichier. Pour plus d’informations, veuillez consulter le guide des utilisateurs de phpBB2.';

$lang['Theme_installed'] = 'Le thème sélectionné a été installé avec succès';
$lang['Style_removed'] = 'Le style sélectionné a été supprimé de la base de données avec succès. Pour supprimer entièrement ce style de votre système, vous devez supprimer le style approprié du répertoire de vos templates.';
$lang['Theme_info_saved'] = 'L’information du thème du template sélectionné a été sauvegardé avec succès. Vous devriez à présent restaurer les permissions de non-écriture sur le fichier theme_info.cfg, et, si possible, sur le répertoire du template sélectionné également';
$lang['Theme_updated'] = 'Le thème sélectionné a été mis à jour avec succès. Vous devriez à présent exporter les réglages du nouveau thème';
$lang['Theme_created'] = 'Le thème a été crée avec succès. Vous devriez à présent exporter le thème sur le fichier de configuration du thème pour plus de sécurité et afin de l’utiliser n’importe où';

$lang['Confirm_delete_style'] = 'Êtes-vous sûr de vouloir supprimer ce style ?';

$lang['Download_theme_cfg'] = 'L’outil d’exportation n’arrive pas à écrire le fichier d’information du thème. Cliquez sur le bouton ci-dessous afin de télécharger ce fichier avec votre navigateur. Une fois téléchargé, vous pouvez le transférer dans le répertoire contenant les fichiers du template. Si vous le souhaitez, vous pouvez également compresser les fichiers pour les distribuer ou les utiliser n’importe où';
$lang['No_themes'] = 'Le template que vous avez sélectionné n’a aucun thème qui lui est associé. Pour créer un nouveau thème, cliquez sur le lien de création sur le volet de gauche';
$lang['No_template_dir'] = 'Il n’a pas été possible d’ouvrir le répertoire du template. Il n’est peut-pas pas possible d’y écrire ou il n’existe pas';
$lang['Cannot_remove_style'] = 'Vous ne pouvez pas supprimer le style que vous avez sélectionné depuis qu’il est celui par défaut. Veuillez modifier le style par défaut et réessayer.';
$lang['Style_exists'] = 'Le nom du style que vous avez saisi existe déjà, veuillez en sélectionner un autre.';

$lang['Click_return_styleadmin'] = 'Cliquez %sici%s afin de retourner à l’administration des styles';

$lang['Theme_settings'] = 'Réglages du thème';
$lang['Theme_element'] = 'Élément du thème';
$lang['Simple_name'] = 'Nom simple';
$lang['Value'] = 'Valeur';
$lang['Save_Settings'] = 'Sauvegarder les réglages';

$lang['Stylesheet'] = 'Feuille de style CSS';
$lang['Stylesheet_explain'] = 'Nom du fichier pour la feuille de style CSS à utiliser pour ce thème.';
$lang['Background_image'] = 'Image de fond';
$lang['Background_color'] = 'Couleur de fond';
$lang['Theme_name'] = 'Nom du thème';
$lang['Link_color'] = 'Couleur du lien';
$lang['Text_color'] = 'Couleur du texte';
$lang['VLink_color'] = 'Couleur du lien visité';
$lang['ALink_color'] = 'Couleur du lien actif';
$lang['HLink_color'] = 'Couleur du lien survolé';
$lang['Tr_color1'] = 'Couleur 1 de la colonne du tableau';
$lang['Tr_color2'] = 'Couleur 2 de la colonne du tableau';
$lang['Tr_color3'] = 'Couleur 3 de la colonne du tableau';
$lang['Tr_class1'] = 'Classe 1 de la colonne du tableau';
$lang['Tr_class2'] = 'Classe 2 de la colonne du tableau';
$lang['Tr_class3'] = 'Classe 3 de la colonne du tableau';
$lang['Th_color1'] = 'Couleur 1 du haut du tableau';
$lang['Th_color2'] = 'Couleur 2 du haut du tableau';
$lang['Th_color3'] = 'Couleur 3 du haut du tableau';
$lang['Th_class1'] = 'Classe 1 du haut du tableau';
$lang['Th_class2'] = 'Classe 2 du haut du tableau';
$lang['Th_class3'] = 'Classe 3 du haut du tableau';
$lang['Td_color1'] = 'Couleur 1 de la cellule du tableau';
$lang['Td_color2'] = 'Couleur 2 de la cellule du tableau';
$lang['Td_color3'] = 'Couleur 3 de la cellule du tableau';
$lang['Td_class1'] = 'Classe 1 de la cellule du tableau';
$lang['Td_class2'] = 'Classe 2 de la cellule du tableau';
$lang['Td_class3'] = 'Classe 3 de la cellule du tableau';
$lang['fontface1'] = 'Apparence 1 de la police';
$lang['fontface2'] = 'Apparence 2 de la police';
$lang['fontface3'] = 'Apparence 3 de la police';
$lang['fontsize1'] = 'Taille 1 de la police';
$lang['fontsize2'] = 'Taille 2 de la police';
$lang['fontsize3'] = 'Taille 3 de la police';
$lang['fontcolor1'] = 'Couleur 1 de la police';
$lang['fontcolor2'] = 'Couleur 2 de la police';
$lang['fontcolor3'] = 'Couleur 3 de la police';
$lang['span_class1'] = 'Classe 1 de span';
$lang['span_class2'] = 'Classe 2 de span';
$lang['span_class3'] = 'Classe 3 de span';
$lang['img_poll_size'] = 'Taille de l’image du sondage [px]';
$lang['img_pm_size'] = 'Taille du statut des messages privés [px]';


//
// Install Process
//
$lang['Welcome_install'] = 'Bienvenue à l’installation de phpBB2';
$lang['Initial_config'] = 'Configuration de base';
$lang['DB_config'] = 'Configuration de la base de données';
$lang['Admin_config'] = 'Configuration de l’administrateur';
$lang['continue_upgrade'] = 'Une fois que vous avez téléchargé votre fichier de configuration sur votre machine locale, vous devez cliquer sur le bouton\'Continuer la mise à jour\' ci-dessous afin de poursuivre la procédure d’installation. Veuillez patienter le temps de transférer le fichier de configuration afin que la procédure de mise à jour soit terminée.';
$lang['upgrade_submit'] = 'Continuer la mise à jour';

$lang['Installer_Error'] = 'Une erreur est survenue lors de l’installation';
$lang['Previous_Install'] = 'Une installation antérieure a été détectée';
$lang['Install_db_error'] = 'Une erreur est survenue lors de la mise à jour de la base de données';

$lang['Re_install'] = 'Votre installation antérieure est toujours active.<br /><br />Si vous souhaitez réinstaller phpBB2, cliquez sur le bouton <em>Oui</em> ci-dessous. Veuillez faire attention à tout ce que vous faites, une mauvaise manœuvre pourrait détruire toutes les données existantes d’une manière irréversible ! Le nom d’utilisateur et le mot de passe de l’administrateur que vous avez utilisé afin de vous connecter sur le forum sera de nouveau créé après la réinstallation. Tout autre réglage ne sera pas sauvegardé.<br /><br />Soyez sûr de savoir ce que vous faites, et faites-le en tout sécurité !';

$lang['Inst_Step_0'] = 'Nous vous remercions d’avoir sélectionné phpBB2. Pour terminer cette installation, veuillez fournir toutes les informations ci-dessous avant toute chose. Veuillez noter que la base de données qui servira à l’installation doit déjà exister. Si l’installation se fait à partir d’une base de données de type ODBC, comme Microsoft Access par exemple, vous devez en tout premier lieu créer un DSN.';

$lang['Start_Install'] = 'Commencer l’installation';
$lang['Finish_Install'] = 'Terminer l’installation';

$lang['Default_lang'] = 'Langue par défaut du forum';
$lang['DB_Host'] = 'Nom d’hôte du serveur de la base de données';
$lang['DB_Name'] = 'Le nom de votre base de données';
$lang['DB_Username'] = 'Nom d’utilisateur de la base de données';
$lang['DB_Password'] = 'Mot de passe de la base de données';
$lang['Database'] = 'Votre base de données';
$lang['Install_lang'] = 'Sélectionnez une langue pour l’installation';
$lang['dbms'] = 'Type de la base de données';
$lang['Table_Prefix'] = 'Préfixe des tables dans la base de données';
$lang['Admin_Username'] = 'Nom d’utilisateur de l’administrateur';
$lang['Admin_Password'] = 'Mot de passe de l’administrateur';
$lang['Admin_Password_confirm'] = 'Mot de passe de l’administrateur [ Confirmation ]';

$lang['Inst_Step_2'] = 'Le nom d’utilisateur de l’administrateur a été créé avec succès. Votre installation de base est à présent terminée. Vous allez être conduit à une page qui vous permettra d’administrer votre nouveau forum. Veuillez vous assurer de vérifier et d’apporter les modifications nécessaires aux informations de la configuration générale. Nous vous remercions d’avoir sélectionné phpBB2.';

$lang['Unwriteable_config'] = 'Votre fichier de configuration est à présent inaccessible en écriture. Une copie de ce fichier peut être téléchargée sur votre ordinateur en cliquant sur le bouton ci-dessous. Vous devez transférer ce fichier dans la même répertoire que celui de phpBB2. Une fois cela réalisé, vous devez vous connecter sur le forum en utilisant le nom d’utilisateur et le mot de passe de l’administrateur que vous avez sélectionné sur le formulaire précédent afin de vous rendre sur votre panneau de contrôle de l’administrateur (le lien est présent en bas de chaque page du forum) pour vérifier la configuration générale du forum. Nous vous remercions d’avoir sélectionné phpBB2.';
$lang['Download_config'] = 'Télécharger la configuration';

$lang['ftp_choose'] = 'Sélectionnez une méthode de téléchargement';
$lang['ftp_option'] = '<br />Depuis que les extensions FTP sont activés dans cette version de PHP, vous pouvez à présent essayer de transférer automatiquement le fichier de configuration.';
$lang['ftp_instructs'] = 'Vous avez sélectionné de transférer le fichier de configuration sur votre FTP de manière automatique. Veuillez saisir les informations demandées ci-dessous afin de faciliter la procédure. Veuillez noter que le chemin FTP doit être exactement le même chemin que celui de votre installation, comme si vous utilisiez normalement le FTP.';
$lang['ftp_info'] = 'Saisissez vos informations FTP';
$lang['Attempt_ftp'] = 'Transférer le fichier de configuration automatiquement';
$lang['Send_file'] = 'Télécharger le fichier de configuration afin de le transférer manuellement';
$lang['ftp_path'] = 'Chemin FTP vers phpBB2';
$lang['ftp_username'] = 'Votre nom d’utilisateur FTP';
$lang['ftp_password'] = 'Votre mot de passe FTP';
$lang['Transfer_config'] = 'Démarrer le transfert';
$lang['NoFTP_config'] = 'Le transfert automatique du fichier de configuration a échouée. Veuillez télécharger le fichier afin de le transférer manuellement.';

$lang['Install'] = 'Installer';
$lang['Upgrade'] = 'Mettre à jour';


$lang['Install_Method'] = 'Sélectionnez votre méthode d’installation';

$lang['Install_No_Ext'] = 'La configuration PHP de votre serveur ne supporte pas le type de base de données que vous avez sélectionné';

$lang['Install_No_PCRE'] = 'Le logiciel a besoin du module des expressions régulières compatible avec Perl, mais votre configuration de PHP ne le supporte apparemment pas !';

//
// Version Check
//
$lang['Version_up_to_date'] = 'Votre installation est à jour, aucune mise à jour n’est disponible pour votre version de phpBB.';
$lang['Version_not_up_to_date'] = 'Votre installation ne semble <b>pas</b> à jour. Des mises à jour sont disponibles, veuillez vous rendre sur <a href="http://www.phpbb.com/downloads.php" target="_new">http://www.phpbb.com/downloads.php</a> ou sur <a href="http://www.phpbb.fr/" target="_new">http://www.phpbb.fr/</a> afin d’obtenir la dernière version de phpBB.';
$lang['Latest_version_info'] = 'La dernière version disponible est <b>phpBB %s</b>.';
$lang['Current_version_info'] = 'Vous utilisez actuellement <b>phpBB %s</b>.';
$lang['Connect_socket_error'] = 'Impossible d’ouvrir une connexion au serveur de phpBB. L’erreur rapportée est :<br />%s';
$lang['Socket_functions_disabled'] = 'Impossible d’utiliser les fonctions du port.';
$lang['Mailing_list_subscribe_reminder'] = 'Pour obtenir les dernières informations à propos des mises à jour de phpBB, pourquoi ne pas vous <a href="http://www.phpbb.com/support/" target="_new">inscrire sur notre liste de diffusion</a> ?';
$lang['Version_information'] = 'Information sur la version';

//
// Login attempts configuration
//
$lang['Max_login_attempts'] = 'Tentatives de connexions autorisées';
$lang['Max_login_attempts_explain'] = 'Le nombre de tentatives de connexions autorisées sur le forum.';
$lang['Login_reset_time'] = 'Durée de verrouillage de la connexion';
$lang['Login_reset_time_explain'] = 'Durée en minutes que l’utilisateur devra patienter le temps de pouvoir de nouveau se connecter après avoir dépassé le nombre de tentatives de connexions autorisées.';

//
// That's all Folks!
// -------------------------------------------------

?>
