<?php

/***************************************************************************
 *                            lang_admin.php [English]
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
$lang['General'] = 'Gweinyddu Cyffredinol';
$lang['Users'] = 'Gweinyddu Defnyddwyr';
$lang['Groups'] = 'Gweinyddu Grwpiau';
$lang['Forums'] = 'Gweinyddu Seiadau';
$lang['Styles'] = 'Gweinyddu Arddull';

$lang['Configuration'] = 'Ffurfwedd';
$lang['Permissions'] = 'Hawliau';
$lang['Manage'] = 'Rheoli';
$lang['Disallow'] = 'Gwrthod enwau';
$lang['Prune'] = 'Tocio';
$lang['Mass_Email'] = 'E-bost at Bawb';
$lang['Ranks'] = 'Graddau';
$lang['Smilies'] = 'Gwenogluniau';
$lang['Ban_Management'] = 'Rheolu Gwaharddiad';
$lang['Word_Censor'] = 'Sensor Geiriau';
$lang['Export'] = 'Allfudo';
$lang['Create_new'] = 'Creu';
$lang['Add_new'] = 'Ychwanegu';
$lang['Backup_DB'] = 'Ategu Cronfa-ddata';
$lang['Restore_DB'] = 'Adfer Cronfa-ddata';


//
// Index
//
$lang['Admin'] = 'Gweinyddu';
$lang['Not_admin'] = 'Nid oes ganddych hawl i weinyddu';
$lang['Welcome_phpBB'] = 'Croeso i phpBB';
$lang['Admin_intro'] = 'Diolch am ddewis phpBB. Bydd y tudalen yma yn rhoi amlinelliad o ystadegau amrywiol eich negesfwrdd. Gallwch ddod yn ol i\'r tudalen yma drwy rhoi clec ar y ddolen <u>Hafan Gweinyddu</u> ar y chwith. I ddychwelyd i fyngegai eich negesfwrdd, rhowch glec ar y logo phpBB ar y chwith. Mae\'r dolennau eraill ar y chwith yn eich galluogi i reoli pob rhan o\'ch negesfwrdd. Bydd cyfarwyddiadau ar bob tudalen.';
$lang['Main_index'] = 'Hafan\'r Negesfwrdd';
$lang['Forum_stats'] = 'Ystadegau\'r Negesfwrdd';
$lang['Admin_Index'] = 'Hafan Gweinyddu';
$lang['Preview_forum'] = 'Rhagolwg Negesfwrdd';

$lang['Click_return_admin_index'] = 'Rhowch glec %sYma%s i ddychwelyd i Fynegai Gweinyddu';

$lang['Statistic'] = 'Ystadegau';
$lang['Value'] = 'Gwerth';
$lang['Number_posts'] = 'Nifer o negesuon';
$lang['Posts_per_day'] = 'Negesuon y diwrnod';
$lang['Number_topics'] = 'Nifer o bynciau';
$lang['Topics_per_day'] = 'Pynciau\'r diwrnod';
$lang['Number_users'] = 'Nifer o ddefnyddwyr';
$lang['Users_per_day'] = 'Defnyddwyr y diwrnod';
$lang['Board_started'] = 'Dechreuwyd y bwrdd';
$lang['Avatar_dir_size'] = 'Maint cyfeiriadur rhithffurfiau';
$lang['Database_size'] = 'Maint Cronfa-ddata';
$lang['Gzip_compression'] ='Cywasgiad Gzip';
$lang['Not_available'] = 'Dim ar gael';

$lang['ON'] = 'Ie'; // This is for GZip compression
$lang['OFF'] = 'Nage'; 


//
// DB Utils
//
$lang['Database_Utilities'] = 'Gwasanaethau Cronfa-ddata';

$lang['Restore'] = 'Adfer';
$lang['Backup'] = 'Ategu';
$lang['Restore_explain'] = 'Bydd hwn yn rhoi adferiad llawn o dablau phpBB o ffeil. Os yw eich gweinydd yn gadael i chi wneud, gallwch fynylwytho ffeil testun ‰ chywasgiad Gzip. <b>RHYBUDD</b>: Bydd hwn yn ail-osod data blaenorol. Gall yr adferiad gymryd dipyn o amser, felly peidiwch ‰ phori oddi wrth y tudalen yma tan iddo orffen.';

$lang['Backup_explain'] = 'Yma gallwch ategu holl ddata phpBB. Os oes ganddych tablau ychwanegol yn yr un gronfa-ddata ‰ phpBB y hoffech ei ategu hefyd, bwydwch eu henwau, wedi eu gwahanu gan atalnod, yn y bocs Tablau Ychwanegol isod. Os yw eich gweinydd yn gadael i chi wneud, gallwch hefyd cywasgu\'r ffeil yn y modd Gzip i leihau ei faint cyn ei lawrlwytho.';

$lang['Backup_options'] = 'Dewisiadau ategu';
$lang['Start_backup'] = 'Cychwyn Ategiad';
$lang['Full_backup'] = 'Ategiad llawn';
$lang['Structure_backup'] = 'Ategiad strwythyr';
$lang['Data_backup'] = 'Ategiad data';
$lang['Additional_tables'] = 'Tablau Ychwanegol';
$lang['Gzip_compress'] = 'Cywasgu ffeil yn y modd Gzip';
$lang['Select_file'] = 'Dewisiwch ffeil';
$lang['Start_Restore'] = 'Cychwyn Adferiad';

$lang['Restore_success'] = 'Mae eich cronfa-ddata wedi ei adferu\'n llwyddiannus.<br /><br />Dylai eich negesfwrdd fod yn ei gyflwr cychwynol.';
$lang['Backup_download'] = 'Bydd eich lawrlwythiad yn cychwyn mewn eiliad; arhoswch tan y cychwynir.';
$lang['Backups_not_supported'] = 'Mae\'n ddrwg gennym, ond nid yw\'n bosib i ategu eich math chi o gronfa-ddata ar hyn o bryd.';

$lang['Restore_Error_uploading'] = 'Gwall wrth fynylwytho\'r ffeil ategol';
$lang['Restore_Error_filename'] = 'Problem enw-ffeil; ceisiwch ddefnyddiwch ffeil amgen';
$lang['Restore_Error_decompress'] = 'Ffaelu dad-gywasgu ffeil Gzip; fynylwythwch ffeil testun plaen';
$lang['Restore_Error_no_file'] = 'Dim ffeil wedi ei fynylwytho';


//
// Auth pages
//
$lang['Select_a_User'] = 'Dewiswch Ddefnyddiwr';
$lang['Select_a_Group'] = 'Dewiswch Gylch Defnyddwyr';
$lang['Select_a_Forum'] = 'Dewiswch Negesfwrdd';
$lang['Auth_Control_User'] = 'Rheolu Hawliau Defnyddiwr'; 
$lang['Auth_Control_Group'] = 'Rheolu Hawliau Cylch'; 
$lang['Auth_Control_Forum'] = 'Rheolu Hawliau Negesfwrdd'; 
$lang['Look_up_User'] = 'Chwilio am ddefnyddiwr'; 
$lang['Look_up_Group'] = 'Chwilio am gylch'; 
$lang['Look_up_Forum'] = 'Chwilio am seiat'; 

$lang['Group_auth_explain'] = 'Gallwch newid hawliau a statws cymedrolwr sydd wedi ei neilltuo i bob cylch defnyddwyr. Peidiwch ag anghofio pan yr ydych yn newid hawliau cylch, bod hawliau defnyddwyr unigol yn gallu rhoi mynediad i rhai seiadau. Byddwch yn cael eich rhybuddio os ydy hyn yn digwydd.';

$lang['User_auth_explain'] = 'Gallwch newid hawliau a statws cymedrolwr sydd wedi ei neilltuo i bob defnyddiwr. Peidiwch ag anghofio pan yr ydych yn newid hawliau defnyddiwr unigol, bod hawliau cylchoedd yn gallu rhoi mynediad i rhai seiadau. Byddwch yn cael eich rhybuddio os ydy hyn yn digwydd.';

$lang['Forum_auth_explain'] = 'Gallwch newid lefelau awdurdod pob seiat. Bydd ganddych ddull syml ac uwch i wneud hyn, ble mae\'r dull uwch yn rhoi mwy o reolaeth ar bob seiat. Cofiwch bod newid y lefelau hawliau seiadau, yn gallu cael effaith ar ba defnyddwyr sy\'n gallu gweithgareddu ynddyn nhw.';

$lang['Simple_mode'] = 'Dull Syml';
$lang['Advanced_mode'] = 'Dull Uwch';
$lang['Moderator_status'] = 'Statws cymedrolwr';

$lang['Allowed_Access'] = 'Mynediad Caniataol';
$lang['Disallowed_Access'] = 'Mynediad Diganiataol';
$lang['Is_Moderator'] = 'Cymedrolwr';
$lang['Not_Moderator'] = 'Nid yn gymedrolwr';

$lang['Conflict_warning'] = 'Rhybydd Gwrthdaro Awdurdod';
$lang['Conflict_access_userauth'] = 'Mae\'r defnyddiwr yma dal efo hawl mynediad i\'r seiat yma trwy aeoldaeth cylch defnyddwyr. Dichon y byddwch eisiau newid hawliau\'r cylch neu symud y defnyddiwr yma o\'r cyclh i\'w hatal nhw rhag cael hawliau mynediad. Mae hawliau\'r cylch (a\'r seiadau gysylltiedig) wedi eu nodi isod.';
$lang['Conflict_mod_userauth'] = 'Mae\'r defnyddiwr yma dal efo hawliau cymedrolwr i\'r seiat yma trwy aeoldaeth cylch defnyddwyr. Dichon y byddwch eisiau newid hawliau\'r cylch neu symud y defnyddiwr yma o\'r grwp i\'w hatal nhw rhag cael hawliau cymedrolwr. Mae hawliau\'r cyclh (a\'r seiadau cysylltiedig) wedi eu nodi isod.';

$lang['Conflict_access_groupauth'] = 'Mae\'r defnyddiwr/wyr canlynol dal efo hawliad mynediad i\'r seiat yma trwy eu gosodiadau hawliau. Dichon y byddwch eisiau newid hawliau defnyddiwr i\'w hatal nhw rhag cael hawliau mynediad. Mae hawliau\'r defnyddiwr (a\'r seiadau cysylltiedig) wedi eu nodi isod.';

$lang['Conflict_mod_groupauth'] = 'Mae\'r defnyddiwr/wyr canlynol dal efo hawliad cymedrolwr i\'r seiat yma trwy eu gosodiadau hawliau. Dichon y byddwch eisiau newid hawliau defnyddiwr/wyr i\'w atal nhw rhag cael hawliau cymedrolwr. Mae hawliau\'r defnyddiwr/wyr (a\'r seiadau cysylltiedig) wedi eu nodi isod.';

$lang['Public'] = 'Cyhoeddus';
$lang['Private'] = 'Preifat';
$lang['Registered'] = 'Cofrestredig';
$lang['Administrators'] = 'Gweinyddwyr';
$lang['Hidden'] = 'Cuddiedig';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'Pawb';
$lang['Forum_REG'] = 'Cofr';
$lang['Forum_PRIVATE'] = 'Preifat';
$lang['Forum_MOD'] = 'Cymed';
$lang['Forum_ADMIN'] = 'Gwein';

$lang['View'] = 'Gweld';
$lang['Read'] = 'Darllen';
$lang['Post'] = 'Postio';
$lang['Reply'] = 'Ymateb';
$lang['Edit'] = 'Golygu';
$lang['Delete'] = 'Dileu';
$lang['Sticky'] = 'Gludog';
$lang['Announce'] = 'Datganiad'; 
$lang['Vote'] = 'Pleidlas';
$lang['Pollcreate'] = 'Creu P™l';

$lang['Permissions'] = 'Hawliau';
$lang['Simple_Permission'] = 'Hawliau Syml';

$lang['User_Level'] = 'Lefel Defnyddiwr'; 
$lang['Auth_User'] = 'Defnyddiwr';
$lang['Auth_Admin'] = 'Gweinydd';
$lang['Group_memberships'] = 'Aelodaeth cylchoed defnyddwyr';
$lang['Usergroup_members'] = 'Mae gan y cylch yma\'r aelodau canlynol';

$lang['Forum_auth_updated'] = 'Hawliau seiat wedi eu diweddaru';
$lang['User_auth_updated'] = 'Hawliau defnyddiwr wedi eu diweddaru';
$lang['Group_auth_updated'] = 'Hawliau cylch wedi eu diweddaru';

$lang['Auth_updated'] = 'Hawliau wedi eu diweddaru';
$lang['Click_return_userauth'] = 'Cliciwch %sYma%s i ddychwelyd i Hawliau Defnyddiwr';
$lang['Click_return_groupauth'] = 'Cliciwch %sYma%s i ddychwelyd i Hawliau Cylch';
$lang['Click_return_forumauth'] = 'Cliciwch %sYma%s i ddychwelyd i  Hawliau Seiat';


//
// Banning
//
$lang['Ban_control'] = 'Rheoli Gwahardd';
$lang['Ban_explain'] = 'Gallwch rheoli gwaharddi defnyddwyr yma. Gallwch wneud hyn, trwy unai gwahardd defnyddiwr neu unigolyn, neu o gyfres o gyferiadau IP ac enwau cyfrifiadur. Mae\'r modd yma\'n atal defnyddiwr rhag cyrraedd y prif dudalen. I osgoi defnyddiwr rhag cofrestru o dan enw gwahanol, gallwch hefyd ddewis cyfeiriad e-bost i wahardd. Nodwch nad yw gwahardd cyfeiriadiau e-bost yn unig yn atal defnyddiwr rhag mewn-gofnodi neu anfon negeseuon. Dylech ddefnyddio un o\'r ddau ddull cyntaf i wneud hyn.';
$lang['Ban_explain_warn'] = 'Nodwch fod bwydo cyfres o gyfeiriadau IP yn gallu arwain at cyfeiriadau rhwng y dechrau a\'r diwedd yn cael eu hychwanegu i\'r rhestr gwahardd. Bydd ceisiadau yn cael eu gwneud yn ddi-ofyn i leihau y nifer o gyfeiriadau sy\'n cael eu hadio drwy gyflwyno \'wildcard\' ble mae angen. Os oes rhaid bwydo amrediad, ceisiwch ei gadw\'n fyr neu\'n well fyth bwydwch gyfeiriad penodol.';

$lang['Select_username'] = 'Dewiswch enw defnyddiwr';
$lang['Select_ip'] = 'Dewiswch cyfeiriad IP';
$lang['Select_email'] = 'Dewiswch cyfeiriad e-bost';

$lang['Ban_username'] = 'Gwahardd un neu fwy o ddefnyddwyr penodol';
$lang['Ban_username_explain'] = 'Gallwch wahardd mwy nag un defnyddiwr ar y tro, drwy ddefnyddio gweithredoedd addas efo\'ch llygoden ac allweddell.';

$lang['Ban_IP'] = 'Gwahardd un neu fwy o gyfeiriadau IP neu enwau cyfrifiadur';
$lang['IP_hostname'] = 'Cyfeiriadau IP neu enwau cyfrifiadur';
$lang['Ban_IP_explain'] = 'I benodi sawl cyfeiriad IP neu enw cyfrifiadur gwahanol, gwahanwch nhw efo atalnod. I benodi amrediad o gyfeiriadau IP, gwahanwch y dechrau a\'r diwedd gyda gwant (-); i benodi \'wildcard\', defnyddiwch asterisc (*).';

$lang['Ban_email'] = 'Gwahardd un neu fwy o gyfeiriadau e-bost';
$lang['Ban_email_explain'] = 'I benodi mwy nag un cyfeiriad e-bost, gwahanwch nhw gyda atalnodau. I benodi enw-defnyddiwr \'wildcard\', defnyddiwch * yn y modd *@hotmail.com';

$lang['Unban_username'] = 'Di-wahardd un neu fwy o ddefnyddwyr penodol';
$lang['Unban_username_explain'] = 'Gallwch di-wahardd nifer o ddefnyddwyr ar y tro, drwy ddefnyddio gweithredoedd addas efo\'ch llygoden ac allweddell';

$lang['Unban_IP'] = 'Di-wahardd un neu fwy o gyfeiriadau IP';
$lang['Unban_IP_explain'] = 'Gallwch di-wahardd nifer o gyfeiriadau IP ar y tro, drwy ddefnyddio gweithredoedd addas efo\'ch llygoden ac allweddell';

$lang['Unban_email'] = 'Di-wahardd un neu fwy o gyfeiriadau post-e';
$lang['Unban_email_explain'] = 'Gallwch di-wahardd nifer o cyfeiriadau e-byst ar y tro, drwy ddefnyddio gweithredoedd addas efo\'ch llygoden ac allweddell';

$lang['No_banned_users'] = 'Dim enwau defnyddwyr gwaharddiedig';
$lang['No_banned_ip'] = 'Dim cyfeiriadau IP gwaharddiedig';
$lang['No_banned_email'] = 'Dim cyfeiriadau e-byst gwaharddiedig';

$lang['Ban_update_sucessful'] = 'Cafodd y rhestr gwahardd ei ddiweddaru\'n llwyddiannus';
$lang['Click_return_banadmin'] = 'Cliciwch %syma%s i ddychwelyd i Rheoli Gwaharddiad';


//
// Configuration
//
$lang['General_Config'] = 'Cyfluniad Cyffredinol';
$lang['Config_explain'] = 'Gyda\'r ffurflen isod gallwch chi newid pob opsiwn cyffredinol ar y negesfwrdd. I newid cyfluniadau defnyddwyr a seiadau, defnyddiwch y dolennau penodol ar y chwith.';

$lang['Click_return_config'] = 'Cliciwch %syma%s i ddychwelyd i Gyfluniad Cyffredinol';

$lang['General_settings'] = 'Gosodiadau Cyffredinol y Negesfwrdd';
$lang['Server_name'] = 'Enw Parth';
$lang['Server_name_explain'] = 'Enw y parth lle mae\'r negesfwrdd yn byw, e.e. http://www.yparthhwn.com';
$lang['Script_path'] = 'Llwybr sgript';
$lang['Script_path_explain'] = 'Lle mae phpBB2 yn byw, mewn cymhariaeth ag enw y parth, e.e. /phpbb';
$lang['Server_port'] = 'Agorfa\'r gweinydd';
$lang['Server_port_explain'] = 'Rhif yr agorfa lle mae\'ch gweinydd yn rhedeg, fel arfer 80. Newidiwch dim ond os ydy e\'n wahanol';
$lang['Site_name'] = 'Enw safle';
$lang['Site_desc'] = 'Disgrifiad safle';
$lang['Board_disable'] = 'Analluogi\'r negesfwrdd';
$lang['Board_disable_explain'] = 'Bydd hyn yn cau\'r negesfwrdd i ddefnyddwyr. Peidiwch ag allgofnodi ar ™l analluogi\'r bwrdd - fyddwch chi ddim yn gallu mewngofnodi eto!';
$lang['Acct_activation'] = 'Galluogi bywiogi cyfrif';
$lang['Acc_None'] = 'Dim un'; // These three entries are the type of activation
$lang['Acc_User'] = 'Defnyddiwr';
$lang['Acc_Admin'] = 'Gweinydd';

$lang['Abilities_settings'] = 'Gosodiadau Sylfaenol Defnyddwyr a Seiadau';
$lang['Max_poll_options'] = 'Mwyafswm dewisiadau mewn p™l piniwn';
$lang['Flood_Interval'] = 'Adeg gorlifo';
$lang['Flood_Interval_explain'] = 'Sawl eiliad mae rhaid i ddefnyddiwr aros rhwng negeseuon'; 
$lang['Board_email_form'] = 'E-bostio defnyddwyr trwy\'r negesfwrdd';
$lang['Board_email_form_explain'] = 'Gall defnyddwyr anfon e-byst at ei gilydd trwy\'r negesfwrdd hwn';
$lang['Topics_per_page'] = 'Nifer o bynciau i\'r tudalene';
$lang['Posts_per_page'] = 'Negesuon y diwrnod';
$lang['Hot_threshold'] = 'Trothwy Pwnc Llosg';
$lang['Default_style'] = 'Arddull arferol';
$lang['Override_style'] = 'Anwybyddu arddull defnyddiwr';
$lang['Override_style_explain'] = 'Defnyddio\'r arddull arferol yn lle arddull o ddewis y defnyddwyr';
$lang['Default_language'] = 'Iaith arferol';
$lang['Date_format'] = 'Arddull Dyddiad';
$lang['System_timezone'] = 'Ardal amser arferol';
$lang['Enable_gzip'] = 'Galluogi cywasgu GZip';
$lang['Enable_prune'] = 'Galluogi tocio seiadau';
$lang['Allow_HTML'] = 'Galluogi HTML';
$lang['Allow_BBCode'] = 'Galluogi BBCode';
$lang['Allowed_tags'] = 'Tagiau HTML';
$lang['Allowed_tags_explain'] = 'Gwahanu tagiau gyda atalnodau';
$lang['Allow_smilies'] = 'Galluogi Gwenogluniau';
$lang['Smilies_path'] = 'Llwybr i\'r gwenogluniau';
$lang['Smilies_path_explain'] = 'Llwybr o dan eich ffolder phpBB, e.e. lluniau/gwenogluniau';
$lang['Allow_sig'] = 'Galluogi llofnodion';
$lang['Max_sig_length'] = 'Hyd eithaf llofnodion';
$lang['Max_sig_length_explain'] = 'Mwyafswm o nodiadau mewn llofnod defnyddwyr';
$lang['Allow_name_change'] = 'Galluogi newidiadau i enwau defnyddwyr';

$lang['Avatar_settings'] = 'Gosodiadau rhithffurfiau';
$lang['Allow_local'] = 'Galluogi oriel rhithffurfiau';
$lang['Allow_remote'] = 'Galluogi rhithffurfiau o bell';
$lang['Allow_remote_explain'] = 'Cysylltu ‰ rhithffurfiau o wefannau eraill';
$lang['Allow_upload'] = 'Galluogi fynylwtho rhithffurfiau';
$lang['Max_filesize'] = 'Maint ffeil mwyaf rhithffurfiau';
$lang['Max_filesize_explain'] = 'Ar gyfer ffeiliau sy wedi\'u fynylwtho';
$lang['Max_avatar_size'] = 'Maint llun mwyaf rhithffurfiau';
$lang['Max_avatar_size_explain'] = '(Hyd x Lled mewn picselau)';
$lang['Avatar_storage_path'] = 'Llwybr storfa rhithffurfiau';
$lang['Avatar_storage_path_explain'] = 'Llwybr o dan eich ffolder phpBB, lle mae\'ch rhithffurfiau yn byw, e.e. lluniau/rhithffurfiau';
$lang['Avatar_gallery_path'] = 'Llwybr Oriel Rhithffurfiau';
$lang['Avatar_gallery_path_explain'] = 'Llwybr o dan eich ffolder phpBB, lle mae\'ch oriel rhithffurfiau yn byw, e.e. lluniau/rhithffurfiau/oriel';

$lang['COPPA_settings'] = 'Gosodiadau COPPA';
$lang['COPPA_fax'] = 'Rhif ffacs COPPA';
$lang['COPPA_mail'] = 'Cyfeiriad post COPPA';
$lang['COPPA_mail_explain'] = 'Dyma\'r cyfeiriad i rieni anfon eu ffurflenni cofrestri COPPA';

$lang['Email_settings'] = 'Gosodiadau e-bost';
$lang['Admin_email'] = 'Cyfeiriad e-bost gweinyddol';
$lang['Email_sig'] = 'Llofnod e-bost';
$lang['Email_sig_explain'] = 'Bydd y testun yma yn cael ei atodi i bob e-bost gweinyddol sy\'n dod o\'r negesfwrdd';
$lang['Use_SMTP'] = 'Defnyddio gweinydd SMTP ar gyfer e-bost?';
$lang['Use_SMTP_explain'] = 'Dwedwch \'ie\' os dych chi am ddefnyddio gweinydd penodol yn lle\'r teclyn e-byst lleol';
$lang['SMTP_server'] = 'Cyfeiriad Gweinydd SMTP';
$lang['SMTP_username'] = 'Enw denyddiwr SMTP';
$lang['SMTP_username_explain'] = 'Rhowch enw defnyddiwr ar gyfer eich gweinydd SMTP dim ond os oes angen';
$lang['SMTP_password'] = 'Cyfrinair SMTP';
$lang['SMTP_password_explain'] = 'Rhowch cyfrinair ar gyfer eich gweinydd SMTP dim ond os oes angen';

$lang['Disable_privmsg'] = 'Negeseuon Preifat';
$lang['Inbox_limits'] = 'Uchafswm o negeseuon yn y Blwch Derbyn';
$lang['Sentbox_limits'] = 'Uchafswm o negeseuon yn y Blwch Gyrrwyd';
$lang['Savebox_limits'] = 'Uchafswm o negeseuon yn y Blwch Cadw';

$lang['Cookie_settings'] = 'Gosodiadau Cwcis'; 
$lang['Cookie_settings_explain'] = 'Mae\'r manylion yma yn rheoli sut mae cwcis yn cael eu hanfon i borwyr eich defnyddwyr. Fel arfer, bydd y gosodiadau arferol yn iawn - os oes rhaid i chi eu newid, byddwch yn ofalus -- gall gosodiadau anghywir wneud mewngofnodi yn amhosibl';
$lang['Cookie_domain'] = 'Parth cwci';
$lang['Cookie_name'] = 'Enw cwci';
$lang['Cookie_path'] = 'Llwybr cwci';
$lang['Cookie_secure'] = 'Cwci diogel';
$lang['Cookie_secure_explain'] = 'Os ydy\'ch gweinydd yn rhedeg trwy SSL, gosodwch hwn i \'ie\' - fel arall, gadewch yn \'nage\'';
$lang['Session_length'] = 'Hyd sesiwn [ eiliadau ]';


//
// Forum Management
//
$lang['Forum_admin'] = 'Gweinyddu Seiadau';
$lang['Forum_admin_explain'] = 'O\'r panel hwn, gallwch chi ychwanegu, dileu, golygu, ail-drefnu ac ail-gydamseru categoriau a seiadau';
$lang['Edit_forum'] = 'Golygu seiat';
$lang['Create_forum'] = 'Creu seiat newydd';
$lang['Create_category'] = 'Creu categori newydd';
$lang['Remove'] = 'Dileu';
$lang['Action'] = 'Gweithred';
$lang['Update_order'] = 'Diweddaru trefn';
$lang['Config_updated'] = 'Diweddarwyd cyfluniad y seiadau yn llwyddianus';
$lang['Edit'] = 'Golygu';
$lang['Delete'] = 'Dileu';
$lang['Move_up'] = 'Symud i fyny';
$lang['Move_down'] = 'Symud i lawr';
$lang['Resync'] = 'Cydamseru';
$lang['No_mode'] = 'Ni osodwyd modd';
$lang['Forum_edit_delete_explain'] = 'Gyda\'r ffurflen isod cewch chi addasu opsiynau cyffredinol ar y negesfwrdd. Ar gyfer cyfluniadau defnyddwyr a seiadau, defnyddiwch y dolenni perthnasol ar y chwith';

$lang['Move_contents'] = 'Symud popeth';
$lang['Forum_delete'] = 'Dileu seiad';
$lang['Forum_delete_explain'] = 'Mae\'r ffurflen isod yn eich galluogi chi i ddileu seiat (neu gategori) a phenderfynu ble dych chi eisiau rhoi\'r trafodaethau (neu seiadau) yr oedd ynddo fe.';

$lang['Status_locked'] = 'Dan glo';
$lang['Status_unlocked'] = 'Agored';
$lang['Forum_settings'] = 'Gosodiadau Cyffredinol y Fforwm';
$lang['Forum_name'] = 'Enw seiat';
$lang['Forum_desc'] = 'Disgrifiad';
$lang['Forum_status'] = 'Statws seiat';
$lang['Forum_pruning'] = 'Tocio di-ofyn';

$lang['prune_freq'] = 'Sieco oedran trafodaeth bob';
$lang['prune_days'] = 'Dileu trafodaethau sydd heb gael cyfraniad o fewn';
$lang['Set_prune_data'] = 'Dych chi wedi dewis tocio di-ofyn ar gyfer y seiat hwn, ond heb osod y manylion am y gweithred. Cerwch yn ™l i wneud hynny, os gwelwch yn dda.';

$lang['Move_and_Delete'] = 'Symud a Dileu';

$lang['Delete_all_posts'] = 'Dileu\'r holl negeseuon';
$lang['Nowhere_to_move'] = 'Does dim lle i\'w symud';

$lang['Edit_Category'] = 'Golygu categori';
$lang['Edit_Category_explain'] = 'Defnyddiwch y ffurflen hon i newid enw categori.';

$lang['Forums_updated'] = 'Diweddarwyd gwybodaeth seiadau a chategoriau yn llwyddianus';

$lang['Must_delete_forums'] = 'Mae rhiad i chi ddileu bob seiat cyn i chi ddileu\'r categori yma';

$lang['Click_return_forumadmin'] = 'Rhowch glec Click %syma%s i ddychwelyd i Weinyddu Seiadau';


//
// Smiley Management
//
$lang['smiley_title'] = 'Tudalen golyfu gwenogluniau';
$lang['smile_desc'] = 'O\'r tudalen yma cewch chi ychwanegu, dileu a golygu\'r gwenogluniau sydd ar gael i\'ch defnyddwyr.';

$lang['smiley_config'] = 'Cyfluniad gwenogluniau';
$lang['smiley_code'] = 'Cod gwenoglun';
$lang['smiley_url'] = 'Ffeil llun gwenoglun';
$lang['smiley_emot'] = 'Emosiwn y gwenoglun';
$lang['smile_add'] = 'Ychwanegu gwenoglun newydd';
$lang['Smile'] = 'Gwenoglun';
$lang['Emotion'] = 'Emosiwn';

$lang['Select_pak'] = 'Dewiswch ffeil pecyn (.pak)';
$lang['replace_existing'] = 'Cymryd lle hen wenoglun';
$lang['keep_existing'] = 'Cadw hen wenoglun';
$lang['smiley_import_inst'] = 'Dylech dat-sipo pecyn y gwenogluniau a fynylwtho\'r ffeiliau i gyd i\'r ffolder perthnasol. Wedyn, dewiswch y gwybodaeth perthnasol ar y ffurflen hon i fewnfudo\'r pecyn gwenogluniau.';
$lang['smiley_import'] = 'Mewnfudo Pecyn Gwenogluniau';
$lang['choose_smile_pak'] = 'Dewiswch ffeil pecyn gwenogluniau .pak';
$lang['import'] = 'Mewnfudo gwenogluniau';
$lang['smile_conflicts'] = 'Beth ddylai ddigwydd mewn achos gwrthdaro rhwng ffeiliau';
$lang['del_existing_smileys'] = 'Dileu pob gwenoglun cyfredol cyn mewnfudo';
$lang['import_smile_pack'] = 'Mewnfudo pecyn gwenogluniau';
$lang['export_smile_pack'] = 'Creu pecyn gwenogluniau';
$lang['export_smiles'] = 'I greu pecyn gwenogluniau o\'r gwenogluniau sy gyda chi nawr, rhowch glec %syma%s i lawrlwytho\'r ffeil gwenog.pak. Enwch y ffeil yma, tgan gadw\'r estyniad .pak. Wedyn, crewch ffeil .zip sy\'n cynnwys y lluniau gwenogluniau i gyd, a\'r ffeil cyfluniad .pak hwn.';

$lang['smiley_add_success'] = 'Ychwanegwyd y gwenoglun yn llwyddiannus';
$lang['smiley_edit_success'] = 'Diweddarwyd y gwenoglun yn llwyddiannus';
$lang['smiley_import_success'] = 'Mewnfudwyd y pecyn gwenogluniau yn llwyddiannus!';
$lang['smiley_del_success'] = 'Dileuwyd y gwenoglun yn llwyddiannus';
$lang['Click_return_smileadmin'] = 'Rhowch glec %syma%s i ddychwelyd i Weinyddu Gwenogluniau';


//
// User Management
//
$lang['User_admin'] = 'Gweinyddu Defnyddwyr';
$lang['User_admin_explain'] = 'Gallwch chi newid gwybodaeth eich defnyddwyr a sawl opsiwn yma. I newid hawliau defnyddwyr, defnyddiwch y gyfundrefn ar gyfer hawliau defnyddwyr a chylchoedd.';

$lang['Look_up_user'] = 'Chwilio am ddefnyddiwr';

$lang['Admin_user_fail'] = 'Methodd diweddaru proffeil y defnyddiwr.';
$lang['Admin_user_updated'] = 'Diweddarwyd proffeil y defnyddiwr yn llwyddiannus.';
$lang['Click_return_useradmin'] = 'Rhowch glec %sYma%s i ddychwelyd i Weinyddu Defnyddwyr';

$lang['User_delete'] = 'Dileu\'r defnyddiwr yma';
$lang['User_delete_explain'] = 'Click here to delete this user; this cannot be undone.';
$lang['User_deleted'] = 'Defnyddiwr wedi ei ddileu\'n llwyddiannus.';

$lang['User_status'] = 'Defnyddiwr yn weithredol';
$lang['User_allowpm'] = 'Gallu anfon negeseuon preifat';
$lang['User_allowavatar'] = 'Gallu ymddangos rhithffurf';

$lang['Admin_avatar_explain'] = 'Yma gallwch ddileu rhithffurf presennol defnyddwyr.';

$lang['User_special'] = 'Gosodiadau y gweinydd yn unig';
$lang['User_special_explain'] = 'Dyw hi ddim yn bosibl i\'r defnyddwyr newid y gosodiadau yma. Cewch chi osod gradd y defnyddwr a dewisiadau eraill sy ddim ar gael i\'r defnyddwwyr eu hunain.';


//
// Group Management
//
$lang['Group_administration'] = 'Gweinyddu Cylchoedd';
$lang['Group_admin_explain'] = 'O\'r panel hwn gallwch weinyddu\'ch cylchoedd defnyddwyr i gyd. Cewch chi ddileu, creu a golygu cylchoedd cyfredol. Cewch chi ddewis cymedrolwyr, toglo statws agored/cae‘dig y cylch a gosod enw a disgrifiad i\'r cylch.';  
$lang['Error_updating_groups'] = 'Bu gamgymeriad wrth diweddaru\'r cylchoedd';
$lang['Updated_group'] = 'Mae\'r cylch wedi\'i ddiweddaru yn llwyddiannus';
$lang['Added_new_group'] = 'Mae\'r cylch newydd wedi\'i greu\'n llwyddiannus';
$lang['Deleted_group'] = 'Mae\'r cylch wedi\'i ddileu\'n llwyddiannus';
$lang['New_group'] = 'Creu grwp newydd';
$lang['Edit_group'] = 'Golygu grwp';
$lang['group_name'] = 'Enw grwp';
$lang['group_description'] = 'Descrifiad y cylch';
$lang['group_moderator'] = 'Cymedrolwr y cylch';
$lang['group_status'] = 'Statws y cylch';
$lang['group_open'] = 'Cylch agored';
$lang['group_closed'] = 'Cylch cae‘dig';
$lang['group_hidden'] = 'Cyclh cuddiedig';
$lang['group_delete'] = 'Dileu\'r cylch';
$lang['group_delete_check'] = 'Dileu\'r cylch hwn';
$lang['submit_group_changes'] = 'Anfon newidiadau';
$lang['reset_group_changes'] = 'Ail-osod newidiadau';
$lang['No_group_name'] = 'Mae rhaid i chi roi enw i\'r cylch';
$lang['No_group_moderator'] = 'Mae rhaid i chi ddewis cymedrolwr i\'r cylch';
$lang['No_group_mode'] = 'Mae rhaid i chi ddewis statws i\'r cylch, agored ynteu gae‘dig';
$lang['No_group_action'] = 'Dim gweithred wedi\'i ddewis';
$lang['delete_group_moderator'] = 'Dileu\'r hen gymedrolwr?';
$lang['delete_moderator_explain'] = 'Os dych chi\'n newid cymedrolwr y cylch, ticiwch y blwch hwn i ddileu\'r hen gymedrolwr o\'r cylch. Fel arall, peidiwch ‰\'i dicio a bydd yr hen gymedrolwr yn dod yn aelod cyffredin y cylch.';
$lang['Click_return_groupsadmin'] = 'Rhowch glec %sYma%s i ddychwelyd i Weinyddu Cylchoedd.';
$lang['Select_group'] = 'Dewisiwch cylch defnyddwyr';
$lang['Look_up_group'] = 'Chwilio cylch defnyddwyr';


//
// Prune Administration
//
$lang['Forum_Prune'] = 'Tocio Seiadau';
$lang['Forum_Prune_explain'] = 'Bydd hyn yn dileu unrhyw pwnc trafod sy ddim wedi cael negeseuon newydd o fewn y nifer o ddyddiau ydych chi\'n dewis. Os nad ydych chi\'n dodi rhif ceith bob pwnc trafod ei ddileu. Fydd hi ddim yn dileu unryw pwnc sy gyda p™l piniwn cyfredol, ynteu datganiadau. Mae rhaid i chi dileu nhwthau gan law.';
$lang['Do_Prune'] = 'Tocio';
$lang['All_Forums'] = 'Pob seiat';
$lang['Prune_topics_not_posted'] = 'Tocio pynciau heb ymateb o fewn ';
$lang['Topics_pruned'] = 'Pynciau trafod wedi\'u tocio';
$lang['Posts_pruned'] = 'Negesueon wedi eu tocio';
$lang['Prune_success'] = 'Tocwyd pynciau trafod yn llwwyddiannus';


//
// Word censor
//
$lang['Words_title'] = 'Sensor Geiriau';
$lang['Words_explain'] = 'O\'r panel hwn gallwch chi ychwanegu, golygu a dileu geiriau a g‰n nhw eu sensro ar eich negesfwrdd. Hefyd, na fydd pobl yn gallu cofrestru gyda enwau defnyddwyr sy\'n cynnwys y geiriau yma. Derbynir cardiau gwyllt (*) ym maes y gair. Er enghraifft, byddai Cymr* yn matsio Cymraeg, Cymru a Cymro, a byddai *ymru yn matsio Cymru, Gymru a Nghymru.';
$lang['Word'] = 'Gair';
$lang['Edit_word_censor'] = 'Golygu sensor geiriau';
$lang['Replacement'] = 'Newid i';
$lang['Add_new_word'] = 'Ychwanegu gair newydd';
$lang['Update_word'] = 'Diweddaru sensor geiriau';

$lang['Must_enter_word'] = 'Mae rhiad i chi ddodi gair a\'i amnewidiad';
$lang['No_word_selected'] = 'Does dim gair wedi\'i ddewis i\'w olygu';

$lang['Word_updated'] = 'Mae sensor gair dewisiedig wedi\'i ddiweddaru\'n llwyddiannus';
$lang['Word_added'] = 'Mae sensor gair dewisiedig wedi\'i ychwanegu\'n llwyddiannus';
$lang['Word_removed'] = 'Mae sensor gair dewisiedig wedi\'i ddileu\'n llwyddiannus';

$lang['Click_return_wordadmin'] = 'Rhowch glec %sYma%s i ddychwelyd i Weinyddu Sensor Geiriau.';


//
// Mass Email
//
$lang['Mass_email_explain'] = 'O\'r adran hon, cewch chi anfon ebost at naill ai bob un o\'ch defnyddwyr neu at bob aelod o gylch defnyddwyr penodol. Wrth wneud hyn, ceith ebost ei anfon at gyfeiriad gweinyddol y negesfwrdd, gyda chopi carbon dall yn mynd i bob derbynnydd. Os dych chi\'n ebostio grwp mawr o bobl, byddwch yn amynddgar ar ™l anfon y neges a pheidiwch ‰ stopio\'r broses hanner ffordd trwyddi. Mae\'n normal i\'r broses cymryd sbel a chewch chi neges unwaith mae\'r sgript wedi gorffen.';
$lang['Compose'] = 'Cyfansoddi'; 

$lang['Recipients'] = 'Derbynwyr'; 
$lang['All_users'] = 'Defnyddwyr i gyd';

$lang['Email_successfull'] = 'Mae eich neges wedi\'i hanfon';
$lang['Click_return_massemail'] = 'Cliciwch %sYma%s i ddychwelyd i\'r ffurflen Ebost at Bawb';


//
// Ranks admin
//
$lang['Ranks_title'] = 'Gweinyddu Graddau';
$lang['Ranks_explain'] = 'Drwy ddefnyddio\'r ffurflen yma gallwch adio, golygu, gweld a dileu graddau. Gallwch hefyd greu graddau personnol, sy\'n gallu cael eu gosod yn adran rheoli defnyddwyr.';

$lang['Add_new_rank'] = 'Ychwanegu gradd newydd';

$lang['Rank_title'] = 'Teitl y radd';
$lang['Rank_special'] = 'Gosod fel Gradd Arbennig';
$lang['Rank_minimum'] = 'Isafrif Negesuon';
$lang['Rank_maximum'] = 'Uchafrif Negesuon';
$lang['Rank_image'] = 'Delwedd Gradd (Perthnasol i lwybyr gwraidd phpBB2)';
$lang['Rank_image_explain'] = 'Defnyddiwch hwn i ddifinio\'r delwedd fach sy\'n berthnasol i\'r radd';

$lang['Must_select_rank'] = 'Rhaid i chi ddewis gradd';
$lang['No_assigned_rank'] = 'Dim gradd arbennig wedi ei neilltuo';

$lang['Rank_updated'] = 'Cafodd y radd ei ddiweddaru\'n lwyddiannus';
$lang['Rank_added'] = 'Cafodd y radd ei adio\'n lwyddiannus';
$lang['Rank_removed'] = 'Cafodd y radd ei ddileu\'n lwyddiannus';
$lang['No_update_ranks'] = 'Cafodd y radd ei ddileu\'n lwyddiannus. Ond, mae cyfrifion defnyddwyr sy\'n defnyddio\'r radd yma ddim wedi eu diweddaru. Mae angen i chi ail-osod y radd ar y cyfrifion yma';

$lang['Click_return_rankadmin'] = 'Cliciwch %sYma%s i ddychwelyd i Weinyddu Graddau';


//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Gwahardd Enwau Defnyddwyr';
$lang['Disallow_explain'] = 'Yn yr adran hon cewch chi reoli pa enwau defnyddwyr sy ddim yn cael eu defnyddio. Cewch chi gynnwys nodyn cerdyn wyllt * yn yr enwau gwaharddiedig. Sylwch nad yw\'n bosib i wahardd unrhyw enw sy\'n cael ei ddefnyddio yn barod - mae rhaid i chi ddileu\'r enw hwnna\'n gyntaf, wedyn ei wahardd.';

$lang['Delete_disallow'] = 'Dileu';
$lang['Delete_disallow_title'] = 'Dileu enw defnyddiwr wedi\'i wahardd';
$lang['Delete_disallow_explain'] = 'Gallwch chi stopio gwahardd enw denyddiwr gan ei ddewis o\'r rhestr a rhoi clec ar Anfon';

$lang['Add_disallow'] = 'Ychwanegu';
$lang['Add_disallow_title'] = 'Ychwanegu enw defnyddiwr i\'r rhestr o enwau gwaharddiedig';
$lang['Add_disallow_explain'] = 'Ychwanegu enw defnyddiwr i\'r rhestr o enwau gwaharddiedig, gan ddefnyddio cerdyn wyllt * i fatsio unrhyw llythyren(nau)';

$lang['No_disallowed'] = 'Dim enwau defnyddwyr wedi\'u gwahardd';

$lang['Disallowed_deleted'] = 'Mae\'r enw wedi\'i ddileu o\'r rhestr o enwau gwaharddiedig';
$lang['Disallow_successful'] = 'Mae\'r enw wedi\'i ychwanegu i\'r rhestr o enwau gwaharddiedig';
$lang['Disallowed_already'] = 'Na allwch gwahardd yr enw hwwna. Mae\'n naill ai yn y rhestr yn barod, neu yn rhestr geiriau wedi\'u sensro, neu mae rhywun yn defnyddio\'r enw ar y negesfwrdd.';

$lang['Click_return_disallowadmin'] = 'Cliciwch %sYma%s i ddychwelyd i Wahardd Enwau Defnyddwyr';


//
// Styles Admin
//
$lang['Styles_admin'] = 'Gweinyddu Arddull';
$lang['Styles_explain'] = 'Yn yr adran hon gallwch ychwanegu, symud a rheoli arddulliau (patrymluniau a them‰u) sydd ar gael i\'ch defnyddwyr';
$lang['Styles_addnew_explain'] = 'Dyma rhestr o\'r themau sydd ar gael i\'r patrymluniau sy gennych chi ar hyn o bryd. Dydy\'r eitemau yma ddim wedi\'i gosod yn y gronfa ddata phpBB. I osod thema, rhowch glec ar y ddolen wrth ei ochr.';

$lang['Select_template'] = 'Dewiswch Arddull';

$lang['Style'] = 'Arddull';
$lang['Template'] = 'Patrymlun';
$lang['Install'] = 'Gosod';
$lang['Download'] = 'Lawrlwytho';

$lang['Edit_theme'] = 'Golygu Thema';
$lang['Edit_theme_explain'] = 'Yn y ffurflen isod gallwch chi olygu gosodiadau y thema dewisiedig';

$lang['Create_theme'] = 'Creu Thema';
$lang['Create_theme_explain'] = 'Defnyddiwch y ffurflen isod i greu thema newydd ar gyfer patrymlun dewisiedig. Dylech ddefnyddio c™d hecsadegol am y lliwiau, ond peidiwch ‰ chynnwys y # blaenorol, h.y. mae <b>CCCCCC</b> yn iawn, ond nid <b>#CCCCCC</b>';

$lang['Export_themes'] = 'Allfudo Themau';
$lang['Export_explain'] = 'O\'r panel hwn cewch chi allfudo data\'r thema dewisiedig. Dewiswch patrymlun o\'r rhestr isod a bydd y sgript yn creu ffeil cyfluniadau ar gyfer y thema a cheisio i\'w arbed yn ffolder y patrymlun dewisiedig. Os dydy e ddim yn gallu arbed y ffeil bydd e\'n rhoi\'r opsiwn i chi lawrlwytho. Er mwyn i\'r sgript arbed y ffeil mae rhaid i chi roi caniat‰d ysgrifennu i\'r gwe-weinydd ar y ffolder priodol. Am fwy o fanylion am hyn, gweler canllawiau defnyddwyr phpBB2. ';

$lang['Theme_installed'] = 'Mae\'r thema wedi\'i osod yn llwyddiannus';
$lang['Style_removed'] = 'Mae\'r thema wedi\'i ddileu o\'r gronfa ddata. Dylech chi hefyd ddileu\'r arddull priodol o\'ch ffolder patrymluniau.';
$lang['Theme_info_saved'] = 'Mae gwybodaeth thema ar gyfer y patrymlun dewisiedig wedi\'i arbed. Dylech chi ddychwelyd y caniat‰d ar y ffeil theme_info.cfg (ac ar ffolder y patrymlun dewisiedig, os bo angen) i ddarllen-yn-unig.';
$lang['Theme_updated'] = 'Mae\'r thema wedi\'i ddiweddaru. Dylech chi allfudo\'r gosodiadau newydd nawr.';
$lang['Theme_created'] = 'Thema wedi ei greu. Dylech chi allfudo\'r thema i\'r ffeil cyfluniad thema i\'w gadw yn ddiogel neu i\'w ddefnyddio fe rhywle arall.';

$lang['Confirm_delete_style'] = 'Ydych chi\'n siwr eich bod eisiau dileu\'r ardull yma?';

$lang['Download_theme_cfg'] = 'Doedd yr allforiwr ddim yn gallu ysgrifennu\'r ffeil gwybodaeth thema. Rhowch glec ar y botwm isod i lawrlwytho\'r ffeil gyda\'ch porwr. Ar ™l i chi lawrlwytho, gallwch chi ei drosglwyddo i\'r ffolder sy\'n cadw \'r ffeiliau patrymlun. Gallwch chi wedyn bacio\'r ffeiliau er mwyn eu dosbarthu neu eu defnyddio rhywle arall.';

$lang['No_themes'] = 'Does gan y patrymlun a ddewsoch chi ddim thema cysylltiedig. I greu thema newydd rhowch glec ar y ddolen Creu Newydd ar y panel ar y  chwith.';

$lang['No_template_dir'] = 'Ddim yn gallu agor ffolder y patrymluniau. Efallai nad yw\'n ddarllenadwy, neu nad yw\'n bodoli.';

$lang['Cannot_remove_style'] = 'Na allwch chi ddileu yr ardull yma; hwnna yw\'r arddull di-ofyn ar gyfer y negesfwrdd. Newidwch yr arddull di-ofyn a triwch eto.';
$lang['Style_exists'] = 'Mae\'r enw hwnna yn bodoli\'n barod. Cerwch yn ™l a dewis eto.';

$lang['Click_return_styleadmin'] = 'Cliciwch %sYma%s i ddychwelyd i Weinyddu Arddull';

$lang['Theme_settings'] = 'Gosodiadau Thema';
$lang['Theme_element'] = 'Elfen Thema';
$lang['Simple_name'] = 'Enw Syml';
$lang['Value'] = 'Gwerth';
$lang['Save_Settings'] = 'Arbed Gosodiadau';

$lang['Stylesheet'] = 'Taflenarddull CSS';
$lang['Background_image'] = 'Delwedd Cefndirol';
$lang['Background_color'] = 'Lliw Cefndirol';
$lang['Theme_name'] = 'Enw Thema';
$lang['Link_color'] = 'Lliw Dolen';
$lang['Text_color'] = 'Lliw Ysgrifen';
$lang['VLink_color'] = 'Lliw dolen wedi\'i Ymweld';
$lang['ALink_color'] = 'Lliw Dolen Gweithredol';
$lang['HLink_color'] = 'Lliw Dolen Ehedfan';
$lang['Tr_color1'] = 'Lliw Rhes Tabl 1';
$lang['Tr_color2'] = 'Lliw Rhes Tabl 2';
$lang['Tr_color3'] = 'Lliw Rhes Tabl 3';
$lang['Tr_class1'] = 'Dosbarth Rhes Tabl 1';
$lang['Tr_class2'] = 'Dosbarth Rhes Tabl 2';
$lang['Tr_class3'] = 'Dosbarth Rhes Tabl 3';
$lang['Th_color1'] = 'Lliw Peniad Tabl 1';
$lang['Th_color2'] = 'Lliw Peniad Tabl 2';
$lang['Th_color3'] = 'Lliw Peniad Tabl 3';
$lang['Th_class1'] = 'Dosbarth Peniad Tabl 1';
$lang['Th_class2'] = 'Dosbarth Peniad Tabl 2';
$lang['Th_class3'] = 'Dosbarth Peniad Tabl 3';
$lang['Td_color1'] = 'Lliw Cell Tabl 1';
$lang['Td_color2'] = 'Lliw Cell Tabl 2';
$lang['Td_color3'] = 'Lliw Cell Tabl 3';
$lang['Td_class1'] = 'Dosbarth Cell Tabl 1';
$lang['Td_class2'] = 'Dosbarth Cell Tabl 2';
$lang['Td_class3'] = 'Dosbarth Cell Tabl 3';
$lang['fontface1'] = 'Gwyneb Ffont 1';
$lang['fontface2'] = 'Gwyneb Ffont 2';
$lang['fontface3'] = 'Gwyneb Ffont 3';
$lang['fontsize1'] = 'Maint Ffont 1';
$lang['fontsize2'] = 'Maint Ffont 2';
$lang['fontsize3'] = 'Maint Ffont 3';
$lang['fontcolor1'] = 'Lliw Ffont 1';
$lang['fontcolor2'] = 'Lliw Ffont 2';
$lang['fontcolor3'] = 'Lliw Ffont 3';
$lang['span_class1'] = 'Dosbarth Dyrnfedd 1';
$lang['span_class2'] = 'Dosbarth Dyrnfedd 2';
$lang['span_class3'] = 'Dosbarth Dyrnfedd 3';
$lang['img_poll_size'] = 'Maint Delwedd Pleidlais [px]';
$lang['img_pm_size'] = 'Maint Statws Neges Ddirgel [px]';

//yma
//
// Install Process
//
$lang['Welcome_install'] = 'Croeso i Arsefydliad phpBB 2';
$lang['Initial_config'] = 'Ffurfwedd Sylfaenol';
$lang['DB_config'] = 'Ffurfwedd Cronfa-ddata';
$lang['Admin_config'] = 'Gweinyddu Ffurfwedd';
$lang['continue_upgrade'] = 'Ar ™l i chi lawrlwytho eich ffeil ffurfwedd i\'ch cyfrifiadur lleol, cewch chi roi clec ar y botwm \'Parhau uwchraddio\' isod i fynd ymlaen gyda\'r broses uwchraddio. Peidiwch llwytho i fyny\'r ffeil ffurfwedd nes i\'r broses uwchraddio wedi\'i chwblhau.';
$lang['upgrade_submit'] = 'Parhau uwchraddio';

$lang['Installer_Error'] = 'Roedd gwall yn ystod yr arsefydliad';
$lang['Previous_Install'] = 'Mae arsefydliad hwn yn bodoli\'n barod';
$lang['Install_db_error'] = 'Roedd gwall wrth ddiweddaru\'r gronfa ddata';

$lang['Re_install'] = 'Mae\'ch arsefydliad blaenorol yn weithredol o hyd.<br /><br />Os dych chi am ail-osod phpBB 2 dylech roi clec ar y botwm \'Ie\' isod. Cofiwch bydd hyn yn dinistrio\'r holl ddata sy\'n bodoli nawr a cheith ategiadau mo\'u gwneud! Ceith enw defnyddiwr a chyfrinair arweinydd y negesfwrdd eu hailgreu ar ™l yr ailosodiad ond na fydd unrhyw gosiadau eraill yn cael eu cadw.<br /><br />Pwyll biau hi cyn gwasgu\'r botwm \'ma!';

$lang['Inst_Step_0'] = 'Diolch am ddewis phpBB 2. I gwblhau\'r arsefydliad, llenwch y manylion isod. Sylwch bod rhaid creu\'r gronfa-ddata\'n gyntaf. Os dych chi\'n arsefydlu cronfa-ddata sy\'n defnyddio ODBC, e.e. MS Access, dylech chi greu DSN iddi hi cyn mynd ymlaen.';

$lang['Start_Install'] = 'Cychwyn Arsefydliad';
$lang['Finish_Install'] = 'Gorffen Arsefydliad';

$lang['Default_lang'] = 'Iaith di-ofyn y negesfwrdd';
$lang['DB_Host'] = 'Enw Gweinydd Cronfa-ddata / DSN';
$lang['DB_Name'] = 'Enw Eich Cronfa-ddata';
$lang['DB_Username'] = 'Enw-defnyddiwr Cronfa-ddata';
$lang['DB_Password'] = 'Cyfrinair Cronfa-ddata';
$lang['Database'] = 'Eich Cronfa-ddata';
$lang['Install_lang'] = 'Dewisiwch iaith ar gyfer yr arsefydliad';
$lang['dbms'] = 'Math cronfa-ddata';
$lang['Table_Prefix'] = 'Rhagddodiad i\'r tablau yn y cronfa-ddata';
$lang['Admin_Username'] = 'Enw-defnyddiwr Gweinyddwr';
$lang['Admin_Password'] = 'Cyfrinair Gweinyddwr';
$lang['Admin_Password_confirm'] = 'Cyfrinair Gweinyddwr [ Cadarnhau ]';

$lang['Inst_Step_2'] = 'Mae\'ch enw defnyddiwr fel gweinyddwr wedi\'i greu. Mae\'r arsefydliad sylfaenol wedi\'i gwblhau. Ar y sgr”n nesaf gallwch chi gweinyddu\'ch arsefydliad newydd. Gwnewch yn siwr eich bod chi\'n sieco manylion Ffurfwedd Cyffredinol a gwneud unrhyw newidiadau anghenrheidiol. Diolch yn fawr am ddewis phpBB 2.';

$lang['Unwriteable_config'] = 'Dyw hi ddim yn bosibl i ysgrifennu eich ffeil ffurfwedd ar hyn o bryd. Ceith copi o\'r ffeil ei lawrlwytho i\'ch cyfrifiadur ar ™l i chi roi clec ar y botwm isod. Dylech chi lwytho-i-fyny\'r ffeil hwn i\'r un ffolder ‰ phpBB. Ar ™l i chi wneud hyn, mewngofnodwch (gan ddefnyddio\'r enw defnyddiwr a chyfrinair a greuwyd ar y ffurflen blaenorol) ac ymweld ‰\'r ganolfan weinyddu - bydd dolen ar waelod sgr”n eich hafan tudalen - a sieco\'r ffurfwedd cyffredinol. Diolch yn fawr am ddewis phpBB 2.';
$lang['Download_config'] = 'Ffurfwedd lawrlwytho';

$lang['ftp_choose'] = 'Dewis modd lawrlwytho';
$lang['ftp_option'] = '<br />Gan fod estyniadau FTP wedi\'u galluogi yn y ffersiwn hwn o PHP, gallwch chi ddewis i FTPio y ffeil ffurfwedd i\'w le yn awtomatig hefyd.';
$lang['ftp_instructs'] = 'Dych chi wedi dewis i FTPio\'r ffeil i\'r cyfrif sy\'n cynnwys phpBB 2 yn awtomatig. Dodwch y gwybodaeth isod i hwyluso\'r broses hon. Sylwch bod rhaid dodi\'r union llwybr trwy FTP i\'ch arseydliad phpBB2, fel fyddech chi petasech chi\'n ei FTPio gan ddefnyddio cleient arferol.';
$lang['ftp_info'] = 'Dodwch eich manylion FTP';
$lang['Attempt_ftp'] = 'Ceisio FTPio ffeil ffurfwedd i\'w le';
$lang['Send_file'] = 'Anfon y ffeil ataf a wna i FTPio fe gan law';
$lang['ftp_path'] = 'Llwybyr FTP i phpBB 2';
$lang['ftp_username'] = 'Eich enw defnyddiwr FTP';
$lang['ftp_password'] = 'Eich Cyfrinair FTP';
$lang['Transfer_config'] = 'Cychwyn Trosglwyddiad';
$lang['NoFTP_config'] = 'Bu methiant wrth geisio anfon y ffeil ffurfwedd drwy FTP.  Lawrlwythwch y ffeil ffurfwedd a\'i osod mewn lle eich hun.';

$lang['Install'] = 'Arsefydlu';
$lang['Upgrade'] = 'Diweddaru';


$lang['Install_Method'] = 'Dewisiwch eich dull arsefydlu';

$lang['Install_No_Ext'] = 'Dydy\'r ffurfwedd PHP ar eich gweinydd ddim yn cefnogi\'r fathy cronfa-ddata a ddewisoch';

$lang['Install_No_PCRE'] = 'I arsefydlu phpBB2 mae angen y modiwl PHP \'Perl-Compatible Regular Expressions\' - sy ddim yn bodoli ar y fersiwn o PHP ar eich gweinydd!';

//
// A dyna i gyd!
// -------------------------------------------------

?>