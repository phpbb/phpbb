<?php

/***************************************************************************
 *                            lang_admin.php [French]
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
//	Translation produced by Helix
//	http://www.phpbb-fr.com/
//


//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = 'Administration Générale';
$lang['Users'] = 'Administration des Utilisateurs';
$lang['Groups'] = 'Administration des Groupes';
$lang['Forums'] = 'Administration des Forums';
$lang['Styles'] = 'Administration des Thèmes';

$lang['Configuration'] = 'Configuration';
$lang['Permissions'] = 'Permissions';
$lang['Manage'] = 'Gestion';
$lang['Disallow'] = 'Interdire un nom d\'utilisateur';
$lang['Prune'] = 'Délester';
$lang['Mass_Email'] = 'Email de Masse';
$lang['Ranks'] = 'Rangs';
$lang['Smilies'] = 'Smilies';
$lang['Ban_Management'] = 'Contrôle du bannissement';
$lang['Word_Censor'] = 'Censure';
$lang['Export'] = 'Exporter';
$lang['Create_new'] = 'Créer';
$lang['Add_new'] = 'Ajouter';
$lang['Backup_DB'] = 'Sauvegarder la base de données';
$lang['Restore_DB'] = 'Restaurer la base de données';


//
// Index
//
$lang['Admin'] = 'Administration';
$lang['Not_admin'] = 'Vous n\'êtes pas autorisé à administrer ce forum';
$lang['Welcome_phpBB'] = 'Bienvenue sur phpBB';
$lang['Admin_intro'] = 'Merci d\'avoir choisi phpBB comme solution de forum. Cet écran vous donnera un rapide aperçu des diverses statistiques de votre forum. Vous pouvez revenir sur cette page en cliquant sur le lien <u>Index de l\'Administration</u> dans le volet de gauche. Pour retourner à l\'index de votre forum, cliquez sur le logo phpBB dans le volet de gauche. Les autres liens du volet de gauche vous permettront de contrôler tous les aspects de votre forum, chaque page contient les instructions nécessaires pour leur utilisation.';
$lang['Main_index'] = 'Index du Forum';
$lang['Forum_stats'] = 'Statistiques du Forum';
$lang['Admin_Index'] = 'Index de l\'Administration';
$lang['Preview_forum'] = 'Aperçu du Forum';

$lang['Click_return_admin_index'] = 'Cliquez %sici%s pour revenir à l\'Index d\'Administration';

$lang['Statistic'] = 'Statistique';
$lang['Value'] = 'Valeur';
$lang['Number_posts'] = 'Nombre de messages';
$lang['Posts_per_day'] = 'Messages par jour';
$lang['Number_topics'] = 'Nombre de sujets';
$lang['Topics_per_day'] = 'Sujets par jour';
$lang['Number_users'] = 'Nombre d\'utilisateurs';
$lang['Users_per_day'] = 'Utilisateurs par jour';
$lang['Board_started'] = 'Ouverture du forum';
$lang['Avatar_dir_size'] = 'Taille du répertoire des Avatars';
$lang['Database_size'] = 'Taille de la base de données';
$lang['Gzip_compression'] ='Compression Gzip';
$lang['Not_available'] = 'Non disponible';

$lang['ON'] = 'ON'; // This is for GZip compression
$lang['OFF'] = 'OFF';


//
// DB Utils
//
$lang['Database_Utilities'] = 'Utilitaires de la Base de données';

$lang['Restore'] = 'Restaurer';
$lang['Backup'] = 'Sauvegarder';
$lang['Restore_explain'] = 'Ceci exécutera une restauration complète de toutes les tables de phpBB à partir d\'un fichier sauvegardé. Si votre serveur le supporte, vous pourrez envoyer au serveur un fichier texte compressé au format gzip et il sera automatiquement décompressé. <B>ATTENTION</B> Cette opération effacera toutes les données existantes. La restauration peut prendre un certain temps à s\'effectuer, veuillez ne pas vous déplacer de cette page tant que l\'opération ne sera pas terminée.';
$lang['Backup_explain'] = 'Ici, vous pouvez sauvegarder toutes les données relatives à phpBB. Si vous avez des tables supplémentaires personnalisées dans la même base de données que phpBB et que vous voulez les sauvegarder aussi, veuillez entrer leurs noms, séparés par une virgule dans la zone de texte \'Tables Supplémentaires\' ci-dessous. Si votre serveur le supporte, vous pourrez compresser le fichier-sauvegarde au format gzip afin de réduire sa taille avant de le télécharger.';

$lang['Backup_options'] = 'Options de Sauvegarde';
$lang['Start_backup'] = 'Démarrer la sauvegarde';
$lang['Full_backup'] = 'Sauvegarde complète';
$lang['Structure_backup'] = 'Sauvegarde de la structure seule';
$lang['Data_backup'] = 'Sauvegarde des données seulement';
$lang['Additional_tables'] = 'Tables Supplémentaires';
$lang['Gzip_compress'] = 'Compression Gzip';
$lang['Select_file'] = 'Sélectionner un fichier';
$lang['Start_Restore'] = 'Démarrer la restauration';

$lang['Restore_success'] = 'La Base de données a été restaurée avec succès.<br /><br />Votre forum devrait revenir dans l\'état dans lequel il était lorsque la sauvegarde a été effectuée.';
$lang['Backup_download'] = 'Le téléchargement va débuter sous peu, veuillez patienter jusqu\'à ce qu\'il commence.';
$lang['Backups_not_supported'] = 'Désolé, mais la sauvegarde de base de données n\'est pas supporté actuellement par votre système de base de données.';

$lang['Restore_Error_uploading'] = 'Erreur durant l\'envoi de la sauvegarde.';
$lang['Restore_Error_filename'] = 'Problème de nom de fichier, veuillez essayer avec un autre fichier.';
$lang['Restore_Error_decompress'] = 'Impossible de décompresser le fichier gzip, veuillez renvoyer une version non compressée du fichier.';
$lang['Restore_Error_no_file'] = 'Aucun fichier n\'a été envoyé.';


//
// Auth pages
//
$lang['Select_a_User'] = 'Sélectionner un Utilisateur';
$lang['Select_a_Group'] = 'Sélectionner un Groupe';
$lang['Select_a_Forum'] = 'Sélectionner un Forum';
$lang['Auth_Control_User'] = 'Contrôle des Permissions des Utilisateurs';
$lang['Auth_Control_Group'] = 'Contrôle des Permissions des Groupes';
$lang['Auth_Control_Forum'] = 'Contrôle des Permissions des Forums';
$lang['Look_up_User'] = 'Rechercher l\'Utilisateur';
$lang['Look_up_Group'] = 'Rechercher le Groupe';
$lang['Look_up_Forum'] = 'Rechercher le Forum';

$lang['Group_auth_explain'] = 'Ici, vous pouvez modifier les permissions et les statuts de modérateurs assignés à chaque groupe. N\'oubliez pas qu\'en changeant les permissions de groupe, les permissions individuelles d\'utilisateurs pourront toujours autoriser un utilisateur à entrer sur un forum, etc. Vous serez prévenu le cas échéant.';
$lang['User_auth_explain'] = 'Ici, vous pouvez modifier les permissions et les statuts de modérateurs assignés à chaque utilisateur, individuellement. N\'oubliez pas qu\'en changeant les permissions individuelles d\'utilisateurs, les permissions de groupe pourront toujours autoriser un utilisateur à entrer sur un forum, etc. Vous serez prévenu le cas échéant.';
$lang['Forum_auth_explain'] = 'Ici, vous pouvez modifier les niveaux d\'accès de chaque forum. Vous aurez deux modes pour le faire, un mode simple, et un mode avancé ; le mode avancé offre un plus grand contrôle sur le fonctionnement de chaque forum. Rappelez-vous qu\'en modifiant les niveaux d\'accès d\'un forum, les utilisateurs du forum pourront en être affectés.';

$lang['Simple_mode'] = 'Mode Simple';
$lang['Advanced_mode'] = 'Mode Avancé';
$lang['Moderator_status'] = 'Statut de Modérateur';

$lang['Allowed_Access'] = 'Accès Autorisé';
$lang['Disallowed_Access'] = 'Accès Interdit';
$lang['Is_Moderator'] = 'est modérateur';
$lang['Not_Moderator'] = 'n\'est pas modérateur';

$lang['Conflict_warning'] = 'Avertissement : Conflit des Autorisations';
$lang['Conflict_access_userauth'] = 'Cet utilisateur a toujours les droits d\'accès à ce forum grâce à son appartenance à un groupe. Vous pouvez modifier les permissions du groupe ou retirer cet utilisateur du groupe pour l\'empêcher complètement d\'avoir les droits d\'accès. L\'attribution des droits par les groupes (et les forums concernés) sont notés ci-dessous.';
$lang['Conflict_mod_userauth'] = 'Cet utilisateur a toujours les droits de modération à ce forum grâce à son appartenance à un groupe. Vous pouvez modifier les permissions du groupe ou retirer cet utilisateur du groupe pour l\'empêcher complètement d\'avoir les droits de modération. L\'attribution des droits par les groupes (et les forums concernés) sont notés ci-dessous.';

$lang['Conflict_access_groupauth'] = 'L\'utilisateur suivant (ou les utilisateurs) a toujours les droits d\'accès à ce forum grâce à ses permissions d\'utilisateur. Vous pouvez modifier les permissions d\'utilisateur pour l\'empêcher complètement d\'avoir les droits d\'accès. L\'attribution des droits par les permissions d\'utilisateur (et les forums concernés) sont notés ci-dessous.';
$lang['Conflict_mod_groupauth'] = 'L\'utilisateur suivant (ou les utilisateurs) a toujours les droits de modération à ce forum grâce à ses permissions d\'utilisateur. Vous pouvez modifier les permissions d\'utilisateur pour l\'empêcher complètement d\'avoir les droits de modération. L\'attribution des droits par les permissions d\'utilisateur (et les forums concernés) sont notés ci-dessous.';

$lang['Public'] = 'Public';
$lang['Private'] = 'Privé';
$lang['Registered'] = 'Enregistré';
$lang['Administrators'] = 'Administrateurs';
$lang['Hidden'] = 'Invisible';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'TOUS';
$lang['Forum_REG'] = 'MEMBRES';
$lang['Forum_PRIVATE'] = 'PRIVE';
$lang['Forum_MOD'] = 'MOD';
$lang['Forum_ADMIN'] = 'ADMIN';

$lang['View'] = 'Voir';
$lang['Read'] = 'Lire';
$lang['Post'] = 'Poster';
$lang['Reply'] = 'Répondre';
$lang['Edit'] = 'Editer';
$lang['Delete'] = 'Supprimer';
$lang['Sticky'] = 'Post-it';
$lang['Announce'] = 'Annoncer';
$lang['Vote'] = 'Voter';
$lang['Pollcreate'] = 'Créer un sondage';

$lang['Permissions'] = 'Permissions';
$lang['Simple_Permission'] = 'Permission Simple';

$lang['User_Level'] = 'Niveau de l\'utilisateur';
$lang['Auth_User'] = 'Utilisateur';
$lang['Auth_Admin'] = 'Administrateur';
$lang['Group_memberships'] = 'Effectifs des groupes d\'utilisateurs';
$lang['Usergroup_members'] = 'Ce groupe est composé des membres suivants';

$lang['Forum_auth_updated'] = 'Permissions du forum mises à jour';
$lang['User_auth_updated'] = 'Permissions de l\'utilisateur mises à jour';
$lang['Group_auth_updated'] = 'Permissions du groupe mises à jour';

$lang['Auth_updated'] = 'Les permissions ont été mises à jour';
$lang['Click_return_userauth'] = 'Cliquez %sici%s pour revenir aux Permissions d\'Utilisateurs';
$lang['Click_return_groupauth'] = 'Cliquez %sici%s pour revenir aux Permissions de Groupes';
$lang['Click_return_forumauth'] = 'Cliquez %sici%s pour revenir aux Permissions des Forums';


//
// Banning
//
$lang['Ban_control'] = 'Contrôle du Bannissement';
$lang['Ban_explain'] = 'Ici, vous pouvez contrôler les bannissement des utilisateurs. Vous pouvez accomplir cela en bannissant soit un utilisateur spécifique, soit un intervalle d\'adresses IP ou un nom de serveur. Ces méthodes empêcheront un utilisateur d\'atteindre votre forum. Pour empêcher un utilisateur de s\'enregistrer sous un nom d\'utilisateur différent, vous pouvez également bannir une adresse email spécifique. Veuillez noter que bannir uniquement l\'adresse email n\'empêchera pas l\'utilisateur concerné de se connecter ou poster sur votre forum, vous devrez utiliser l\'une des deux méthodes citées ci-dessus.';
$lang['Ban_explain_warn'] = 'Veuillez noter qu\'entrer un intervalle d\'adresses IP aura pour résultat de prendre en compte toutes les adresses entre l\'IP de départ et l\'IP de fin dans la liste de bannissement. Des essais seront effectués afin de réduire le nombre d\'adresses IP ajoutées à la base de données en introduisant des jokers automatiquement aux endroits appropriés. Si vous devez réellement entrer un intervalle, essayez de le garder réduit ou au mieux, fixez des adresses spécifiques.';

$lang['Select_username'] = 'Sélectionner un Nom d\'utilisateur';
$lang['Select_ip'] = 'Sélectionner une IP';
$lang['Select_email'] = 'Sélectionner une adresse Email';

$lang['Ban_username'] = 'Bannir un ou plusieurs utilisateurs spécifiques';
$lang['Ban_username_explain'] = 'Vous pouvez bannir plusieurs utilisateurs d\'une fois en utilisant la combinaison appropriée de souris et clavier pour votre ordinateur et navigateur internet';

$lang['Ban_IP'] = 'Bannir une ou plusieurs adresses IP ou noms de serveurs';
$lang['IP_hostname'] = 'Adresses IP ou noms de serveurs';
$lang['Ban_IP_explain'] = 'Pour spécifier plusieurs IP ou noms de serveurs différents, séparez-les par des virgules. Pour spécifier un intervalle d\'adresses IP, séparez le début et la fin avec un trait d\'union (-), pour spécifier un joker, utilisez *';

$lang['Ban_email'] = 'Bannir une ou plusieurs adresses email';
$lang['Ban_email_explain'] = 'Pour spécifier plus d\'une adresse email, séparez-les par des virgules. Pour spécifier un joker pour le nom d\'utilisateur, utilisez * ; par exemple *@hotmail.com';

$lang['Unban_username'] = 'Débannir un ou plusieurs utilisateurs spécifiques';
$lang['Unban_username_explain'] = 'Vous pouvez débannir plusieurs utilisateurs en une fois en utilisant la combinaison appropriée de souris et clavier pour votre ordinateur et navigateur internet';

$lang['Unban_IP'] = 'Débannir une ou plusieurs adresses IP';
$lang['Unban_IP_explain'] = 'Vous pouvez débannir plusieurs adresses IP en une fois en utilisant la combinaison appropriée de souris et clavier pour votre ordinateur et navigateur internet';

$lang['Unban_email'] = 'Débannir une ou plusieurs adresses email';
$lang['Unban_email_explain'] = 'Vous pouvez débannir plusieurs adresses email en une fois en utilisant la combinaison appropriée de souris et clavier pour votre ordinateur et navigateur internet';

$lang['No_banned_users'] = 'Aucun noms d\'utilisateurs bannis';
$lang['No_banned_ip'] = 'Aucune adresses IP bannies';
$lang['No_banned_email'] = 'Aucune adresses email bannies';

$lang['Ban_update_sucessful'] = 'La liste de bannissement a été mise à jour avec succès';
$lang['Click_return_banadmin'] = 'Cliquez %sici%s pour revenir au Contrôle du Bannissement';


//
// Configuration
//
$lang['General_Config'] = 'Configuration Générale';
$lang['Config_explain'] = 'Le formulaire ci-dessous vous permettra de personnaliser toutes les options générales du forum. Pour les Utilisateurs et les Forums, utilisez les liens relatifs sur le volet de gauche.';

$lang['Click_return_config'] = 'Cliquez %sici%s pour revenir à Configuration Générale';

$lang['General_settings'] = 'Options Générales du Forum';
$lang['Server_name'] = 'Nom de domaine';
$lang['Server_name_explain'] = 'Le nom de domaine à partir duquel ce forum fonctionne';
$lang['Script_path'] = 'Chemin du script';
$lang['Script_path_explain'] = 'Le chemin relatif de phpBB2 par rapport au nom de domaine';
$lang['Server_port'] = 'Port du serveur';
$lang['Server_port_explain'] = 'Le port utilisé par votre serveur est habituellement le 80, uniquement modifier si différent';
$lang['Site_name'] = 'Nom du site';
$lang['Site_desc'] = 'Description du site';
$lang['Board_disable'] = 'Désactiver le forum';
$lang['Board_disable_explain'] = 'Ceci rendra le forum indisponible aux utilisateurs. Ne vous déconnectez pas lorsque vous désactivez le forum, vous ne pourrez plus vous reconnecter !';
$lang['Acct_activation'] = 'Activation du compte';
$lang['Acc_None'] = 'Aucune'; // These three entries are the type of activation
$lang['Acc_User'] = 'Utilisateur';
$lang['Acc_Admin'] = 'Administrateur';


$lang['Abilities_settings'] = 'Options de Base de l\'Utilisateur et du Forum';
$lang['Max_poll_options'] = 'Nombre maximal d\'options pour les sondages';
$lang['Flood_Interval'] = 'Intervalle de Flood';
$lang['Flood_Interval_explain'] = 'Nombre de secondes durant lequel un utilisateur doit patienter avant de pouvoir reposter.';
$lang['Board_email_form'] = 'Messagerie email via le forum';
$lang['Board_email_form_explain'] = 'Les Utilisateurs s\'envoient des email par ce forum';
$lang['Topics_per_page'] = 'Sujets Par Page';
$lang['Posts_per_page'] = 'Messages Par Page';
$lang['Hot_threshold'] = 'Seuil de Messages pour être Populaire';
$lang['Default_style'] = 'Thème par Défaut';
$lang['Override_style'] = 'Annuler le thème de l\'utilisateur';
$lang['Override_style_explain'] = 'Remplace le thème de l\'utilisateur par le thème par défaut';
$lang['Default_language'] = 'Langue par défaut';
$lang['Date_format'] = 'Format de la Date';
$lang['System_timezone'] = 'Fuseau Horaire';
$lang['Enable_gzip'] = 'Activer la Compression GZip';
$lang['Enable_prune'] = 'Activer le Délestage du Forum';
$lang['Allow_HTML'] = 'Autoriser le HTML';
$lang['Allow_BBCode'] = 'Autoriser le BBCode';
$lang['Allowed_tags'] = 'Balises HTML autorisées';
$lang['Allowed_tags_explain'] = 'Séparez les balises avec des virgules';
$lang['Allow_smilies'] = 'Autoriser les Smilies';
$lang['Smilies_path'] = 'Chemin de stockage des Smilies';
$lang['Smilies_path_explain'] = 'Chemin sous votre répertoire phpBB, ex : images/smiles';
$lang['Allow_sig'] = 'Autoriser les Signatures';
$lang['Max_sig_length'] = 'Longueur Maximale de la signature';
$lang['Max_sig_length_explain'] = 'Nombre maximal de caractères dans la signature de l\'utilisateur';
$lang['Allow_name_change'] = 'Autoriser les changements de Nom d\'utilisateur';

$lang['Avatar_settings'] = 'Option des Avatars';
$lang['Allow_local'] = 'Activer la gallerie des avatars';
$lang['Allow_remote'] = 'Activer les avatars à distance';
$lang['Allow_remote_explain'] = 'Les avatars sont stockés sur un autre site web';
$lang['Allow_upload'] = 'Activer l\'envoi d\'avatar';
$lang['Max_filesize'] = 'Taille Maximale du Fichier Avatar';
$lang['Max_filesize_explain'] = 'Pour les avatars envoyés';
$lang['Max_avatar_size'] = 'Dimensions Maximales de l\'Avatar';
$lang['Max_avatar_size_explain'] = '(Hauteur x Largeur en pixels)';
$lang['Avatar_storage_path'] = 'Chemin de stockage des Avatars';
$lang['Avatar_storage_path_explain'] = 'Chemin sous votre répertoire phpBB, ex : images/avatars';
$lang['Avatar_gallery_path'] = 'Chemin de la Gallerie des Avatars';
$lang['Avatar_gallery_path_explain'] = 'Chemin sous votre répertoire phpBB pour les images pré-chargées, ex : images/avatars/gallery';

$lang['COPPA_settings'] = 'Options COPPA';
$lang['COPPA_fax'] = 'Numéro de Fax COPPA';
$lang['COPPA_mail'] = 'Adresse postale de la COPPA';
$lang['COPPA_mail_explain'] = 'Ceci est l\'adresse postale où les parents enverront le formulaire d\'enregistrement COPPA';

$lang['Email_settings'] = 'Options de l\'Email';
$lang['Admin_email'] = 'Adresse Email de l\'Administrateur';
$lang['Email_sig'] = 'Signature Email';
$lang['Email_sig_explain'] = 'Ce texte sera attaché à tous les emails que le forum enverra';
$lang['Use_SMTP'] = 'Utiliser un serveur SMTP pour l\'email';
$lang['Use_SMTP_explain'] = 'Dites oui si vous voulez ou devez envoyer des emails par un serveur spécifique au lieu de la fonction locale mail()';
$lang['SMTP_server'] = 'Adresse du serveur SMTP';
$lang['SMTP_username'] = 'Nom d\'utilisateur SMTP';
$lang['SMTP_username_explain'] = 'N\'entrez un nom d\'utilisateur pour votre serveur smtp seulement si c\'est nécessaire';
$lang['SMTP_password'] = 'Mot de passe SMTP';
$lang['SMTP_password_explain'] = 'N\'entrez un mot de passe pour votre serveur smtp seulement si c\'est nécessaire';

$lang['Disable_privmsg'] = 'Messagerie Privée';
$lang['Inbox_limits'] = 'Messages Max dans la Boîte de réception';
$lang['Sentbox_limits'] = 'Messages Max dans la Boîte des messages envoyés';
$lang['Savebox_limits'] = 'Message Max dans la Boîte des Archives';

$lang['Cookie_settings'] = 'Options du Cookie';
$lang['Cookie_settings_explain'] = 'Ces détails définissent la manière dont les cookies sont envoyés au navigateur internet des utilisateurs. Dans la majeure partie des cas, les valeurs par défaut devraient être suffisantes. Si vous avez besoin de les modifier, faites le avec précaution, des valeurs incorrectes pourraient empêcher les utilisateurs de se connecter.';
$lang['Cookie_domain'] = 'Domaine du cookie';
$lang['Cookie_name'] = 'Nom du cookie';
$lang['Cookie_path'] = 'Chemin du cookie';
$lang['Cookie_secure'] = 'Cookie sécurisé';
$lang['Cookie_secure_explain'] = 'Si votre serveur fonctionne via SSL, activez cette fonction, sinon laissez là désactivée';
$lang['Session_length'] = 'Durée de la session [ secondes ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Administration des Forums';
$lang['Forum_admin_explain'] = 'Depuis ce panneau de contrôle, vous pouvez ajouter, supprimer, éditer, réordonner et resynchroniser vos catégories et forums.';
$lang['Edit_forum'] = 'Editer un forum';
$lang['Create_forum'] = 'Créer un nouveau forum';
$lang['Create_category'] = 'Créer une nouvelle catégorie';
$lang['Remove'] = 'Enlever';
$lang['Action'] = 'Action';
$lang['Update_order'] = 'Mettre à jour l\'Ordre';
$lang['Config_updated'] = 'Configuration du Forum mise à jour avec succès';
$lang['Edit'] = 'Editer';
$lang['Delete'] = 'Supprimer';
$lang['Move_up'] = 'Monter';
$lang['Move_down'] = 'Descendre';
$lang['Resync'] = 'Resynchroniser';
$lang['No_mode'] = 'Aucun mode n\'a été défini';
$lang['Forum_edit_delete_explain'] = 'Le formulaire ci-dessous vous permettra de personnaliser toutes les options générales du forum. Pour les configurations Utilisateurs et Forums, utilisez les liens relatifs dans le volet de gauche.';

$lang['Move_contents'] = 'Déplacer tout le contenu vers';
$lang['Forum_delete'] = 'Supprimer un Forum';
$lang['Forum_delete_explain'] = 'Le formulaire ci-dessous vous permettra de supprimer un forum (ou une catégorie) et décider où vous voulez mettre les messages (ou les forums) qu\'il contenait.';

$lang['Status_locked'] = 'Verrouillé';
$lang['Status_unlocked'] = 'Déverrouillé';
$lang['Forum_settings'] = 'Options Générales des Forums';
$lang['Forum_name'] = 'Nom du Forum';
$lang['Forum_desc'] = 'Description';
$lang['Forum_status'] = 'Statut du forum';
$lang['Forum_pruning'] = 'Auto-délestage';

$lang['prune_freq'] = 'Vérifier l\'age des sujets tous les ';
$lang['prune_days'] = 'Retirer les sujets n\'ayant pas eu de réponses depuis';
$lang['Set_prune_data'] = 'Vous avez activer l\'auto-délestage pour ce forum mais n\'avez pas défini une fréquence ou un nombre de jours à délester. Veuillez revenir en arrière et le faire';

$lang['Move_and_Delete'] = 'Déplacer et Supprimer';

$lang['Delete_all_posts'] = 'Supprimer tous les messages';
$lang['Nowhere_to_move'] = 'Nulle part où déplacer aussi';

$lang['Edit_Category'] = 'Editer une Catégorie';
$lang['Edit_Category_explain'] = 'Utilisez ce formulaire pour modifer le nom d\'une catégorie.';

$lang['Forums_updated'] = 'Informations du Forum et de la Catégorie mises à jour avec succès';

$lang['Must_delete_forums'] = 'Vous devez supprimer tous vos forums avant de pouvoir supprimer cette catégorie';

$lang['Click_return_forumadmin'] = 'Cliquez %sici%s pour revenir à l\'Administration des Forums';


//
// Smiley Management
//
$lang['smiley_title'] = 'Utilitaire d\'Edition des Smilies';
$lang['smile_desc'] = 'Depuis cette page vous pouvez ajouter, retirer et éditer les émoticônes ou smilies que les utilisateurs utilisent dans leurs messages et messages privés.';

$lang['smiley_config'] = 'Configuration des Smilies';
$lang['smiley_code'] = 'Code du Smiley';
$lang['smiley_url'] = 'Fichier Image du Smiley';
$lang['smiley_emot'] = 'Emoticon du Smiley';
$lang['smile_add'] = 'Ajouter un nouveau Smiley';
$lang['Smile'] = 'Smile';
$lang['Emotion'] = 'Emotion';

$lang['Select_pak'] = 'Selectionner le Fichier Pack (.pak)';
$lang['replace_existing'] = 'Remplacer les Smilies existants';
$lang['keep_existing'] = 'Conserver les Smilies existants';
$lang['smiley_import_inst'] = 'Vous devez dézipper le pack de smilies et envoyer tous les fichiers dans le répertoire de Smilies approprié pour l\'installation. Ensuite sélectionnez les informations correctes dans ce formulaire pour pour importer le pack de smilies.';
$lang['smiley_import'] = 'Importer un Pack de Smilies';
$lang['choose_smile_pak'] = 'Choisir un Pack de Smilies, fichier .pak';
$lang['import'] = 'Importer les Smilies';
$lang['smile_conflicts'] = 'Que doit-il être fait en cas de conflits ?';
$lang['del_existing_smileys'] = 'Supprimer les smilies existants avant l\'importation';
$lang['import_smile_pack'] = 'Importer un Pack de Smilies';
$lang['export_smile_pack'] = 'Créer un Pack de Smilies';
$lang['export_smiles'] = 'Pour créer un pack de smilies à partir de vos smilies actuellement installés, cliquez %sici%s pour télécharger le fichier .pak de smilies. Nommez ce fichier de façon appropriée afin de vous assurer de conserver l\'extension de fichier .pak. Ensuite, créez un fichier zip contenant toutes les images de vos smilies plus le fichier de configuration .pak.';

$lang['smiley_add_success'] = 'Le Smiley a été ajouté avec succès';
$lang['smiley_edit_success'] = 'Le Smiley a été mis à jour avec succès';
$lang['smiley_import_success'] = 'Le Pack de Smilies a été importé avec succès !';
$lang['smiley_del_success'] = 'Le Smiley a été retiré avec succès';
$lang['Click_return_smileadmin'] = 'Cliquez %sici%s pour revenir à l\'Administration des Smilies';


//
// User Management
//
$lang['User_admin'] = 'Administration des Utilisateurs';
$lang['User_admin_explain'] = 'Ici, vous pouvez changer les informations des utilisateurs et certaines options spécifiques. Pour modifier les permissions des utilisateurs, veuillez utiliser le système de permissions d\'utilisateurs et de groupes.';

$lang['Look_up_user'] = 'Rechercher l\'utilisateur';

$lang['Admin_user_fail'] = 'Impossible de mettre à jour le profil de l\'utilisateur.';
$lang['Admin_user_updated'] = 'Le profil de l\'utilisateur a été mis à jour avec succès.';
$lang['Click_return_useradmin'] = 'Cliquez %sici%s pour revenir à l\'Administration des Utilisateurs';

$lang['User_delete'] = 'Supprimer cet utilisateur';
$lang['User_delete_explain'] = 'Cliquez ici pour supprimer cet utilisateur, ceci ne peut pas être rétabli.';
$lang['User_deleted'] = 'L\'utilisateur a été supprimé avec succès.';

$lang['User_status'] = 'L\'utilisateur est actif';
$lang['User_allowpm'] = 'Peut envoyer des Messages Privés';
$lang['User_allowavatar'] = 'Peut afficher un avatar';

$lang['Admin_avatar_explain'] = 'Ici vous pouvez voir et supprimer l\'avatar actuel de l\'utilisateur.';

$lang['User_special'] = 'Champs spéciaux pour administrateurs uniquement';
$lang['User_special_explain'] = 'Ces champs ne peuvent pas être modifées par l\'utilisateur. Ici, vous pouvez définir leur statut et d\'autres options non données aux utilisateurs.';


//
// Group Management
//
$lang['Group_administration'] = 'Administration des Groupes';
$lang['Group_admin_explain'] = 'Depuis ce panneau, vous pouvez administrer tous vos groupes d\'utilisateurs, vous pouvez : supprimer, créer et éditer les groupes existants. Vous pouvez choisir des modérateurs, alterner le statut ouvert/fermé d\'un groupe et définir le nom  et la description d\'un groupe';
$lang['Error_updating_groups'] = 'Il y a eu une erreur durant la mise à jour des groupes';
$lang['Updated_group'] = 'Le groupe a été mis à jour avec succès';
$lang['Added_new_group'] = 'Le nouveau groupe a été créé avec succès';
$lang['Deleted_group'] = 'Le groupe a été supprimé avec succès';
$lang['New_group'] = 'Créer un nouveau groupe';
$lang['Edit_group'] = 'Editer un groupe';
$lang['group_name'] = 'Nom du groupe';
$lang['group_description'] = 'Description du groupe';
$lang['group_moderator'] = 'Modérateur du groupe';
$lang['group_status'] = 'Statut du groupe';
$lang['group_open'] = 'Groupe ouvert';
$lang['group_closed'] = 'Groupe fermé';
$lang['group_hidden'] = 'Groupe invisible';
$lang['group_delete'] = 'Supprimer un groupe';
$lang['group_delete_check'] = 'Supprimer ce groupe';
$lang['submit_group_changes'] = 'Envoyer les modifications';
$lang['reset_group_changes'] = 'Remettre à zero';
$lang['No_group_name'] = 'Vous devez spécifier un nom pour ce groupe';
$lang['No_group_moderator'] = 'Vous devez spécifier un modérateur pour ce groupe';
$lang['No_group_mode'] = 'Vous devez spécifier un mode pour ce groupe, ouvert ou fermé';
$lang['No_group_action'] = 'Aucune action n\'a été spécifiée';
$lang['delete_group_moderator'] = 'Supprimer l\'ancien modérateur du groupe ?';
$lang['delete_moderator_explain'] = 'Si vous changez le modérateur du groupe, cochez cette case pour enlever l\'ancien modérateur de ce groupe. Sinon, vous pouvez ne pas la cocher, et l\'utilisateur deviendra un membre régulier de ce groupe.';
$lang['Click_return_groupsadmin'] = 'Cliquez %sici%s pour revenir à l\'Administration des Groupes.';
$lang['Select_group'] = 'Sélectionner un groupe';
$lang['Look_up_group'] = 'Rechercher le groupe';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Délester un Forum';
$lang['Forum_Prune_explain'] = 'Ceci supprimera tous les sujets n\'ayant pas eu de réponses depuis le nombre de jours que vous aurez choisi. Si vous n\'entrez pas de nombre, tous les sujets seront supprimés. Par contre cela ne supprimera ni les sujets dans lesquels un sondage est encore en cours, ni les annonces. Vous devrez supprimer ces sujets manuellement.';
$lang['Do_Prune'] = 'Faire le Délestage';
$lang['All_Forums'] = 'Tous les Forums';
$lang['Prune_topics_not_posted'] = 'Délester les sujets sans réponses depuis cette période (en jours)';
$lang['Topics_pruned'] = 'Sujets délestés';
$lang['Posts_pruned'] = 'Messages délestés';
$lang['Prune_success'] = 'Le délestage des forums s\'est déroulé avec succès';


//
// Word censor
//
$lang['Words_title'] = 'Censure des Mots';
$lang['Words_explain'] = 'Depuis ce panneau de contrôle, vous pouvez ajouter, éditer, et retirer les mots qui seront automatiquement censurés sur vos forums. De plus, les gens ne seront pas autorisés à s\'inscrire avec des noms d\'utilisateurs contenant ces mots. Les jokers (*) sont acceptés dans le champ \'Mot\', ex : *test* concordera avec detestable, test* concordera avec testing, et *test avec detest.';
$lang['Word'] = 'Mot';
$lang['Edit_word_censor'] = 'Editer la censure du mot';
$lang['Replacement'] = 'Remplacement';
$lang['Add_new_word'] = 'Ajouter un nouveau mot';
$lang['Update_word'] = 'Mettre à jour la censure du mot';

$lang['Must_enter_word'] = 'Vous devez entrer un mot et son remplaçant';
$lang['No_word_selected'] = 'Aucun mot sélectionné pour l\'édition';

$lang['Word_updated'] = 'Le mot censuré sélectionné a été mis à jour avec succès';
$lang['Word_added'] = 'Le mot censuré a été ajouté avec succès';
$lang['Word_removed'] = 'Le mot censuré sélectionné a été retiré avec succès';

$lang['Click_return_wordadmin'] = 'Cliquez %sici%s pour revenir à l\'Administration de la Censure des Mots';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'Ici, vous pouvez envoyer le même email à tous les utilisateurs du forums ou seulement à ceux d\'un groupe donné. Pour ce faire, un email sera envoyé en copie cachée à partir de l\'adresse email d\'administration vers ses destinataires. L\'envoi massif d\'email prend un certain temps, soyez patients après l\'envoi et n\'interrompez pas le chargement de la page, vous serez averti automatiquement de la fin de l\'opération.';
$lang['Compose'] = 'Composer';

$lang['Recipients'] = 'Destinataires';
$lang['All_users'] = 'Tous les Utilisateurs';

$lang['Email_successfull'] = 'Votre message a été envoyé';
$lang['Click_return_massemail'] = 'Cliquez %sici%s pour revenir au formulaire de l\'Email de Masse';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Administration des Rangs';
$lang['Ranks_explain'] = 'En utilisant ce formulaire vous pouvez ajouter, éditer, voir et supprimer des rangs. Vous pouvez également créer des rangs personnalisés qui pourront être assignés à des utilisateurs spécifiques par l\'outil de Gestion des Utilisateurs';

$lang['Add_new_rank'] = 'Ajouter un nouveau rang';

$lang['Rank_title'] = 'Titre du Rang';
$lang['Rank_special'] = 'Définir en tant que Rang Spécial';
$lang['Rank_minimum'] = 'Messages Minimum';
$lang['Rank_maximum'] = 'Messages Maximum';
$lang['Rank_image'] = 'Image du Rang (relatif au chemin de phpBB2)';
$lang['Rank_image_explain'] = 'Utilisez ceci pour associer une petite image avec le rang en question';

$lang['Must_select_rank'] = 'Vous devez sélectionner un rang';
$lang['No_assigned_rank'] = 'Aucun rang spécial assigné';

$lang['Rank_updated'] = 'Le rang a été mis à jour avec succès';
$lang['Rank_added'] = 'Le rang a été ajouté avec succès';
$lang['Rank_removed'] = 'Le rang a été supprimé avec succès';
$lang['No_update_ranks'] = 'Le rang a été supprimé avec succès, toutefois, les comptes des utilisateurs n\'ont pas été mis à jour. Vous devrez remettre à zéro manuellement leur rang.';

$lang['Click_return_rankadmin'] = 'Cliquez %sici%s pour revenir à l\'Administration des Rangs';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Contrôle des Noms d\'utilisateurs Interdits';
$lang['Disallow_explain'] = 'Ici, vous pouvez contrôler les noms d\'utilisateurs qui seront interdits à l\'usage. Les noms d\'utilisateurs interdits peuvent contenir un caractère joker (*). Veuillez noter que vous ne pourrez pas interdire un nom d\'utilisateur déjà enregistré, vous devrez d\'abord supprimer le compte de l\'utilisateur et ensuite interdire le nom d\'utilisateur';

$lang['Delete_disallow'] = 'Supprimer';
$lang['Delete_disallow_title'] = 'Retirer un Nom d\'utilisateur Interdit';
$lang['Delete_disallow_explain'] = 'Vous pouvez retirer un nom d\'utilisateur interdit en sélectionnant le nom d\'utilisateur depuis la liste et en cliquant sur Supprimer';

$lang['Add_disallow'] = 'Ajouter';
$lang['Add_disallow_title'] = 'Ajouter un nom d\'utilisateur interdit';
$lang['Add_disallow_explain'] = 'Vous pouvez interdire un nom d\'utilisateur en utilisant le caractère joker *';

$lang['No_disallowed'] = 'Aucun Nom d\'utilisateur Interdit';

$lang['Disallowed_deleted'] = 'Le nom d\'utilisateur interdit a été retiré avec succès';
$lang['Disallow_successful'] = 'Le nom d\'utilisateur interdit a été ajouté avec succès';
$lang['Disallowed_already'] = 'Le nom que vous avez entré ne peut être interdit. Soit il existe déjà dans la liste, soit il est dans la liste des mots censurés, ou soit il est déjà enregistré';

$lang['Click_return_disallowadmin'] = 'Cliquez %sici%s pour revenir à l\'Administration des Noms d\'utilisateurs Interdits';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Administration des Thèmes';
$lang['Styles_explain'] = 'En utilisant cet outil, vous pouvez ajouter, éditer, supprimer et gérer les thèmes (modèles de documents et thèmes) disponibles auprès des utilisateurs.';
$lang['Styles_addnew_explain'] = 'La liste suivante contient tous les thèmes actuellement disponibles pour le modèle de document courant. Les éléments sur cette liste n\'ont pas encore été installés dans la base de données de phpBB. Pour installer un thème, il suffit de cliquer sur le lien \'Installer\' à côté d\'une entrée';

$lang['Select_template'] = 'Sélectionner un Modèle de document';

$lang['Style'] = 'Thème';
$lang['Template'] = 'Modèle de document';
$lang['Install'] = 'Installer';
$lang['Download'] = 'Télécharger';

$lang['Edit_theme'] = 'Editer un Thème';
$lang['Edit_theme_explain'] = 'Dans le formulaire ci-dessous, vous pouvez éditer les paramètres pour le thème sélectionné';

$lang['Create_theme'] = 'Créer un Thème';
$lang['Create_theme_explain'] = 'Utilisez le formulaire ci-dessous pour créer un nouveau thème pour un modèle de document sélectionné. Lorsque vous entrerez les couleurs (pour lesquelles vous devrez utiliser une notation hexadécimale), vous ne devrez pas inclure le # initial, ex : CCCCCC est valide, #CCCCCC ne l\'est pas';

$lang['Export_themes'] = 'Exporter des Thèmes';
$lang['Export_explain'] = 'Dans ce panneau, vous pourrez exporter les données de ce thème pour un modèle de document sélectionné. Sélectionnez le modèle de document depuis la liste ci-dessous, et le script crééra le fichier de configuration du thème et essaiera de le copier dans le répertoire sélectionné des modèles de documents. S\'il ne peut pas le copier lui-même, il vous proposera de le télécharger. Afin que le script puisse copier le fichier, vous devez donner les droits d\'écriture pour le répertoire sur le serveur. Pour plus d\'informations à propos de cela, allez voir le Guide de l\'utilisateur de phpBB 2.';

$lang['Theme_installed'] = 'Le thème sélectionné a été installé avec succès';
$lang['Style_removed'] = 'Le thème sélectionné a été retiré de la base de données. Pour enlever complètement ce thème de votre système, vous devez supprimer les fichiers appropriés dans le répertoire du modèle de document.';
$lang['Theme_info_saved'] = 'Les informations du thème pour le modèle de document sélectionné ont été sauvegardées. Vous devriez restreindre les permissions du fichier theme_info.cfg (et si possible dans le répertoire du modèle de document sélectionné) à la lecture seule';
$lang['Theme_updated'] = 'Le thème sélectionné a été mis à jour. Vous devriez exporter maintenant les nouveaux paramètres du thème';
$lang['Theme_created'] = 'Thème créé. Vous devriez exporter maintenant le thème vers le fichier de configuration du thème pour le conserver en lieu sûr ou l\'utiliser ailleurs';

$lang['Confirm_delete_style'] = 'Etes-vous sûr de vouloir supprimer ce thème';

$lang['Download_theme_cfg'] = 'L\'exportateur n\'arrive pas à écrire le fichier des informations du thème. Cliquez sur le bouton ci-dessous pour télécharger ce fichier avec votre navigateur internet. Une fois téléchargé, vous pourrez le transférer vers le répertoire contenant les modèles de documents. Vous pourrez ensuite créer un pack des fichiers pour le distribuer ou l\'utiliser ailleurs si vous le désirez';
$lang['No_themes'] = 'Le modèle de document que vous avez sélectionné n\'a pas de thème. Pour créer un nouveau thème, cliquez sur Créer un Nouveau Thème sur le volet de gauche';
$lang['No_template_dir'] = 'Impossible d\'ouvrir le répertoire du modèle de document. Il peut être illisible par le serveur ou ne pas exister';
$lang['Cannot_remove_style'] = 'Vous ne pouvez pas enlever le thème sélectionné tant qu\'il est utilisé par le forum en tant que thème par défaut. Veuillez changer le thème par défaut et réessayer.';
$lang['Style_exists'] = 'Le nom du thème choisi existe déjà, veuillez revenir en arrière et choisir un nom différent.';

$lang['Click_return_styleadmin'] = 'Cliquez %sici%s pour revenir à l\'Administration des Thèmes';

$lang['Theme_settings'] = 'Option du Thème';
$lang['Theme_element'] = 'Elément du Thème';
$lang['Simple_name'] = 'Nom Simple';
$lang['Value'] = 'Valeur';
$lang['Save_Settings'] = 'Sauver les Paramètres';

$lang['Stylesheet'] = 'Feuille de style CSS';
$lang['Background_image'] = 'Image de Fond';
$lang['Background_color'] = 'Couleur de Fond';
$lang['Theme_name'] = 'Nom du Thème';
$lang['Link_color'] = 'Couleur du Lien';
$lang['Text_color'] = 'Couleur du Texte';
$lang['VLink_color'] = 'Couleur du Lien Visité';
$lang['ALink_color'] = 'Couleur du Lien Actif';
$lang['HLink_color'] = 'Couleur du Lien survolé';
$lang['Tr_color1'] = 'Table Rangée Couleur 1';
$lang['Tr_color2'] = 'Table Rangée Couleur 2';
$lang['Tr_color3'] = 'Table Rangée Couleur 3';
$lang['Tr_class1'] = 'Table Rangée Class 1';
$lang['Tr_class2'] = 'Table Rangée Class 2';
$lang['Tr_class3'] = 'Table Rangée Class 3';
$lang['Th_color1'] = 'Table En-tête Couleur 1';
$lang['Th_color2'] = 'Table En-tête Couleur 2';
$lang['Th_color3'] = 'Table En-tête Couleur 3';
$lang['Th_class1'] = 'Table En-tête Class 1';
$lang['Th_class2'] = 'Table En-tête Class 2';
$lang['Th_class3'] = 'Table En-tête Class 3';
$lang['Td_color1'] = 'Table Cellule Couleur 1';
$lang['Td_color2'] = 'Table Cellule Couleur 2';
$lang['Td_color3'] = 'Table Cellule Couleur 3';
$lang['Td_class1'] = 'Table Cellule Class 1';
$lang['Td_class2'] = 'Table Cellule Class 2';
$lang['Td_class3'] = 'Table Cellule Class 3';
$lang['fontface1'] = 'Nom de la Police 1';
$lang['fontface2'] = 'Nom de la Police 2';
$lang['fontface3'] = 'Nom de la Police 3';
$lang['fontsize1'] = 'Taille Police 1';
$lang['fontsize2'] = 'Taille Police 2';
$lang['fontsize3'] = 'Taille Police 3';
$lang['fontcolor1'] = 'Couleur Police 1';
$lang['fontcolor2'] = 'Couleur Police 2';
$lang['fontcolor3'] = 'Couleur Police 3';
$lang['span_class1'] = 'Span Class 1';
$lang['span_class2'] = 'Span Class 2';
$lang['span_class3'] = 'Span Class 3';
$lang['img_poll_size'] = 'Taille Image Sondage [px]';
$lang['img_pm_size'] = 'Tauille Statut Message Privé [px]';

//
// Install Process
//
$lang['Welcome_install'] = 'Bienvenue à l\'Installation de phpBB 2';
$lang['Initial_config'] = 'Configuration de Base';
$lang['DB_config'] = 'Configuration de la Base de données';
$lang['Admin_config'] = 'Configuration du compte Administrateur';
$lang['continue_upgrade'] = 'Une fois que vous avez téléchargé le fichier config vers votre ordinateur, vous pouvez cliquer sur le boutton \'Continuer la Mise à jour\' ci-dessous pour progresser dans le processus de mise à jour. Veuillez attendre la fin du processus de mise à jour avant d\'envoyer le fichier config.';
$lang['upgrade_submit'] = 'Continuer la Mise à jour';

$lang['Installer_Error'] = 'Une erreur s\'est produite durant l\'installation';
$lang['Previous_Install'] = 'Une installation précédente a été détectée';
$lang['Install_db_error'] = 'Une erreur s\'est produite en essayant de mettre à jour la base de données';

$lang['Re_install'] = 'Votre installation précédente est toujours active. <br /><br />Si vous voulez réinstaller phpBB 2, cliquez sur le bouton Oui ci-dessous. Vous êtes conscient qu\'en faisant cela, vous détruirez toutes les données existantes, aucune sauvegarde ne sera faites ! le nom d\'utilisateur de l\'administrateur et le mot de passe que vous utilisez pour vous connecter au forum sera recréé après la réinstallation, rien d\'autre ne sera fait conservé. <br /><br />Réfléchissez bien avant d\'appuyer sur Oui!';

$lang['Inst_Step_0'] = 'Merci d\'avoir choisi phpBB 2. Afin d\'achever cette installation, veuillez remplir les détails demandés ci-dessous. Veuillez noter que la base de données dans laquelle vous installez devrait déjà exister. Si vous êtes en train d\'installer sur une base de données qui utilise ODBC, MS Access par exemple, vous devez d\'abord lui créer un SGBD avant de continuer.';

$lang['Start_Install'] = 'Démarrer l\'Installation';
$lang['Finish_Install'] = 'Finir l\'Installation';

$lang['Default_lang'] = 'Langue par Défaut du Forum';
$lang['DB_Host'] = 'Nom du Serveur de Base de données / SGBD';
$lang['DB_Name'] = 'Nom de votre Base de données';
$lang['DB_Username'] = 'Nom d\'utilisateur';
$lang['DB_Password'] = 'Mot de passe';
$lang['Database'] = 'Votre Base de données';
$lang['Install_lang'] = 'Choisissez la Langue pour l\'Installtion';
$lang['dbms'] = 'Type de la Base de données';
$lang['Table_Prefix'] = 'Préfixe des tables';
$lang['Admin_Username'] = 'Nom d\'utilisateur';
$lang['Admin_Password'] = 'Mot de passe';
$lang['Admin_Password_confirm'] = 'Mot de passe [ Confirmer ]';

$lang['Inst_Step_2'] = 'Votre compte d\'administration a été créé. A ce point, l\'installation de base est terminée. Vous allez être redirigé vers une nouvelle page qui vous permettra d\'administrer votre nouvelle installation. Veuillez vous assurer de vérifier les détails de la Configuration Générale et d\'opérer les changements qui s\'imposent. Merci d\'avoir choisi phpBB 2.';

$lang['Unwriteable_config'] = 'Votre fichier config est en lecture seule actuellement. Une copie du fichier config va vous être proposé en téléchargement après avoir avoir cliqué sur le boutton ci-dessous. Vous devrez envoyer ce fichier dans le même répertoire où est installé phpBB 2. Une fois terminé, vous pourrez vous connecter en utilisant vos nom d\'utilisateur et mot de passe d\'administrateur que vous avez fourni précédemment, et visiter le Panneau d\'Administration (un lien apparaîtra en bas de chaque page une fois connecté) pour vérifier la Configuration Générale. Merci d\'avoir choisi phpBB 2.';
$lang['Download_config'] = 'Télécharger Config';

$lang['ftp_choose'] = 'Choisir le Méthode de Téléchargement';
$lang['ftp_option'] = '<br />Tant que les extensions FTP seront activés dans cette version de PHP, l\'option d\'essayer d\'envoyer automatiquement le fichier config sur un ftp peut vous être donnée.';
$lang['ftp_instructs'] = 'Vous avez choisi de transférer automatiquement via FTP le fichier vers le compte contenant phpBB 2. Veuillez compléter les informtions ci-dessous afin de faciliter cette opération. Notez que le chemin FTP doit être le chemin exact vers le répertoire où est installé phpBB2 comme si vous étiez en train d\'envoyer le fichier avec n\'importe quel client FTP.';
$lang['ftp_info'] = 'Entrez vos informations FTP';
$lang['Attempt_ftp'] = 'Essayer de transférer config vers un serveur ftp';
$lang['Send_file'] = 'Juste m\'envoyer le fichier et je l\'enverrai manuellement sur le serveur ftp';
$lang['ftp_path'] = 'Chemin de phpBB2 FTP';
$lang['ftp_username'] = 'Votre Nom d\'utilisateur FTP';
$lang['ftp_password'] = 'Votre Mot de passe FTP';
$lang['Transfer_config'] = 'Démarrer le Transfert';
$lang['NoFTP_config'] = 'La tentative d\'envoi du fichier config par FTP a échoué. Veuillez télécharger le fichier config et l\'envoyer manuellement sur votre serveur FTP.';

$lang['Install'] = 'Installation';
$lang['Upgrade'] = 'Mise à jour';

$lang['Install_Method'] = 'Choix du type d\'installation';

$lang['Install_No_Ext'] = 'La configuration de php sur votre serveur ne supporte pas le type de base de données que vous avez choisi';

$lang['Install_No_PCRE'] = 'phpBB2 requiert le support des expressions régulières Perl pour php, mais votre configuration de php ne le supporte pas apparemment !';

//
// That's all Folks!
// -------------------------------------------------

?>