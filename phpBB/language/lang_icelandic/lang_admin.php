<?php
/***************************************************************************
 *
 *	language/lang_icelandic/lang_admin.php   [icelandic]
 *	------------------------------------------------------------------------
 *
 *	Created     Thu, 29 Aug 2002 18:16:51 +0200
 *	Corrected   Mon, 23 dec 2002, baldur@oreind.is
 *	Copyright   (c) 2002 The phpBB Group
 *	Email       support@phpbb.com
 *
 *	Created by  C.O.L.T. v1.4.4 - The Cool Online Language Translation Tool
 *	            Fast like a bullet and available online!
 *	            (c) 2002 Matthias C. Hormann <matthias@hormann-online.net>
 *
 *	Visit       http://www.phpbb.kicks-ass.net/ to find out more!
 *
 ***************************************************************************/

/***************************************************************************
 *
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 2 of the License, or
 *	(at your option) any later version.
 *
 ***************************************************************************/

//
// Format is same as lang_main
//

//
// Modules, this replaces the keys used
// in the modules[][] arrays in each module file
//
$lang['General'] = 'Almenn umsjón';
$lang['Users'] = 'Notenda umsjón';
$lang['Groups'] = 'Hóp umsjón';
$lang['Forums'] = 'Umræðu umsjón';
$lang['Styles'] = 'Þema umsjón';

$lang['Configuration'] = 'Uppsetning';
$lang['Permissions'] = 'Heimildir';
$lang['Manage'] = 'Stjórnun';
$lang['Disallow'] = 'Banna nöfn';
$lang['Prune'] = 'Eyða gömlu';
$lang['Mass_Email'] = 'Magnpóstur';
$lang['Ranks'] = 'Stig';
$lang['Smilies'] = 'Broskallar';
$lang['Ban_Management'] = 'Bann stjórnun';
$lang['Word_Censor'] = 'Orða eftirlit';
$lang['Export'] = 'Flytja út';
$lang['Create_new'] = 'Búa til';
$lang['Add_new'] = 'Bæta við';
$lang['Backup_DB'] = 'Taka afrit';
$lang['Restore_DB'] = 'Setja inn gagnagrunn';

//
// Index
//
$lang['Admin'] = 'Umsýsla';
$lang['Not_admin'] = 'Þú ert ekki með heimild til að stjórna þessu umræðuborði';
$lang['Welcome_phpBB'] = 'Velkomin til phpBB';
$lang['Admin_intro'] = 'Þakka þér fyrir að hafa valið phpBB sem lausn fyrir þínar umræður. Þessi skjár sýnir þér upplýsingar um ýmsa þætti umræðuborðsins. Þú getur farið aftur til baka á þessa síðu með því að ýta á <u>Umsjón, yfirlit</u> tengil efst í vinstri hluta skjásins. Til að fara á forsíðu umræðuborðsins, þá ýtirðu á phpBB myndina líka efst á vinstri hluta skjásins. Aðrir tenglar á vinstri hluta skjásins hjálpa þér við að stjórna öllum atriðum á umræðuborði þínu, hver og einn skjár er með leiðbeiningar um hvað á að gera.';
$lang['Main_index'] = 'Umræður, forsíða';
$lang['Forum_stats'] = 'Tölfræði borðs';
$lang['Admin_Index'] = 'Umsjón, yfirlit';
$lang['Preview_forum'] = 'Skoða umræðuborð';

$lang['Click_return_admin_index'] = 'Ýtið %sHér%s til að fara til baka á Umsjón, yfirlit';

$lang['Statistic'] = 'Tölfræði';
$lang['Value'] = 'Gildi';
$lang['Number_posts'] = 'Fjöldi innleggja';
$lang['Posts_per_day'] = 'Innlegg á dag';
$lang['Number_topics'] = 'Fjöldi spjallþráða';
$lang['Topics_per_day'] = 'Spjallþræðir á dag';
$lang['Number_users'] = 'Fjöldi notenda';
$lang['Users_per_day'] = 'Notendur á dag';
$lang['Board_started'] = 'Umræðuborð byrjar';
$lang['Avatar_dir_size'] = 'Stærð á myndamöppu';
$lang['Database_size'] = 'Stærð á gagnagrunni';
$lang['Gzip_compression'] = 'Gzip gagnaþjöppun';
$lang['Not_available'] = 'Ekki til';

$lang['ON'] = 'Á'; // This is for GZip compression
$lang['OFF'] = 'AF';

//
// DB Utils
//
$lang['Database_Utilities'] = 'Gagnagrunns tól';

$lang['Restore'] = 'Ná í afrit';
$lang['Backup'] = 'Taka afrit';
$lang['Restore_explain'] = 'Hér nærð þú aftur í öll gögn phpBB sem var áður búið að geyma í skrá á þinni tölvu. Ef þjónustuaðili styður þá getur þú sent gzip þjáppaða texta skrá og hún verður þá sjálfvirkt afþjöppuð. <b>VIÐVÖRUN</b> Þetta skrifar yfir öll núverandi gögn. Það getur tekið langan tíma að ná í og setja upp öll gögnin. Ekki fara frá þessari síðu fyrr en það er búið.';
$lang['Backup_explain'] = 'Hér tekur þú afrit af öllum gögnum phpBB umræðuhópsins. Ef þú hefur einhverjar auka sérhannaðar töflur í sama gagnagrunni og phpBB þá verður þú að skrá nöfnin á þeim sérstaklega með kommu á milli í Auka Töflu textahólfi hér neðanvið. Ef vefþjónninn styður það þá getur þú líka gzip þjappað skrána til að minnka plássið sem hún tekur áður en þú sækir hana.';

$lang['Backup_options'] = 'Stillingar afrits';
$lang['Start_backup'] = 'Byrja afritun';
$lang['Full_backup'] = 'Afrita allt';
$lang['Structure_backup'] = 'Afrita bara uppsetningu';
$lang['Data_backup'] = 'Afrita bara gögn';
$lang['Additional_tables'] = 'Auka töflur';
$lang['Gzip_compress'] = 'Gzip þjappa skrá';
$lang['Select_file'] = 'Velja skrá';
$lang['Start_Restore'] = 'Byrja að setja upp afrit';

$lang['Restore_success'] = 'Gagnagrunnurinn hefur verið settur upp að fullu aftur.<br /><br />Umræðuborðið á að vera komið upp eins og það var áður en þú tókst afritið.';
$lang['Backup_download'] = 'Það ætti að byrja fljótlega flutningur á skránni til þín';
$lang['Backups_not_supported'] = 'Því miður er stuðningur við afritun á þessum gagnagrunni ekki fyrir hendi á vefþjóninum.';

$lang['Restore_Error_uploading'] = 'Villa við flutning á skránni';
$lang['Restore_Error_filename'] = 'Villa í nafni, reyndu aðra skrá';
$lang['Restore_Error_decompress'] = 'Get ekki afþjappað gzip skrá, þú þarft að flytja óþjappaða skrá';
$lang['Restore_Error_no_file'] = 'Engin skrá var flutt';

//
// Auth pages
//
$lang['Select_a_User'] = 'Veldu notanda';
$lang['Select_a_Group'] = 'Veldu hóp';
$lang['Select_a_Forum'] = 'Veldu umræður';
$lang['Auth_Control_User'] = 'Heimilda stjórnun'; 
$lang['Auth_Control_Group'] = 'Heimildir fyrir hópa'; 
$lang['Auth_Control_Forum'] = 'Heimildir fyrir umræður'; 
$lang['Look_up_User'] = 'Leita að notanda'; 
$lang['Look_up_Group'] = 'Leita að hóp'; 
$lang['Look_up_Forum'] = 'Leita að umræðum'; 

$lang['Group_auth_explain'] = 'Hér getur þú breytt heimild og umsjónarmanns stöðu þess sem er skráður fyrir hvern hóp. Ekki gleyma að við að breyta heimild hvers hóps þá getur heimild hvers notanda hleypt honum að umræðunum o.s.fv. Þú munt vera varaður við ef það gerist.';
$lang['User_auth_explain'] = 'Hér getur þú breytt heimild og umsjónarmanns stöðu fyrir hvern notanda fyrir sig. Ekki gleyma að við að breyta heimild hvers notanda þá getur heimild hvers hóps hleypt honum að umræðunum o.s.fv. Þú munt vera varaður við ef það gerist.';
$lang['Forum_auth_explain'] = 'Hér getur þú breytt aðgangs stigum fyrir hverjar umræður. Þú færð bæði einfalda og flókna aðferð til að framkvæma þetta, flókin aðferð gefur þér meiri sveigjanleika í stillingum á umræðunum. Mundu að með því að breyta aðgangi að umræðum getur það haft áhrif hvaða notendur geta gert hvað.';

$lang['Simple_mode'] = 'Einföld aðferð';
$lang['Advanced_mode'] = 'Flókin aðferð';
$lang['Moderator_status'] = 'Staða umsjónarmanna';

$lang['Allowed_Access'] = 'Aðgangur heimill';
$lang['Disallowed_Access'] = 'Aðgangur óheimill';
$lang['Is_Moderator'] = 'Er stjórnandi';
$lang['Not_Moderator'] = 'Ekki stjórnandi';

$lang['Conflict_warning'] = 'Heimildir stangast á';
$lang['Conflict_access_userauth'] = 'Þessi notandi hefur ennþá aðgang að þessum umræðum í gegnum hóp aðgang. Þú getur breytt heimild hópsins eða eytt þessum notenda hóp til að útiloka alveg að þeir hafi aðgang. Heimildir hópa (og umræður sem á við) eru listaðar hér neðar.';
$lang['Conflict_mod_userauth'] = 'Þessi notandi hefur enn réttindi stjórnanda í gegnum hóp aðgang. Þú getur breytt heimild hópsins eða þú getur eytt þessum hóp til að útiloka alveg stjórnunar heimild. Heimildir hópanna (og umræðu flokka sem á við) eru listaðar hér neðan við.';

$lang['Conflict_access_groupauth'] = 'Eftirfarandi notandi (eða notendur) hafa enn aðgang að þessum umræðum með stillingum á heimildum sínum. Þú getur breytt heimildum þeirra til að útiloka þá frá því sem við á. Heimildir notanda (notenda) og umræður sem á við eru listaðar hér neðan við.';
$lang['Conflict_mod_groupauth'] = 'Eftirfarandi notandi (eða notendur) hafa enn aðgang stjórnanda að þessum umræðum með stillingum á heimildum þeirra. Þú getur breytt heimildum þeirra til að útiloka þá frá því að hafa réttindi stjórnanda. Heimildir notanda (notenda) og umræður sem á við eru listaðar hér neðan við.';

$lang['Public'] = 'Almennt';
$lang['Private'] = 'Einka';
$lang['Registered'] = 'Skráður';
$lang['Administrators'] = 'Umsjónarmaður';
$lang['Hidden'] = 'Falinn';

// These are displayed in the drop down boxes for advanced
// mode forum auth, try and keep them short!
$lang['Forum_ALL'] = 'ALLIR';
$lang['Forum_REG'] = 'SKRÁÐUR';
$lang['Forum_PRIVATE'] = 'EINKA';
$lang['Forum_MOD'] = 'STJÓRN';
$lang['Forum_ADMIN'] = 'UMSJÓN';

$lang['View'] = 'Skoða';
$lang['Read'] = 'Lesa';
$lang['Post'] = 'Innlegg';
$lang['Reply'] = 'Svara';
$lang['Edit'] = 'Breyta';
$lang['Delete'] = 'Eyða';
$lang['Sticky'] = 'Líma';
$lang['Announce'] = 'Tilkynning'; 
$lang['Vote'] = 'Kjósa';
$lang['Pollcreate'] = 'Búa til könnun';

$lang['Permissions'] = 'Heimildir';
$lang['Simple_Permission'] = 'Einföld heimild';

$lang['User_Level'] = 'Heimild notanda'; 
$lang['Auth_User'] = 'Notandi';
$lang['Auth_Admin'] = 'Umsjónarmaður';
$lang['Group_memberships'] = 'Meðlimir notendahópa';
$lang['Usergroup_members'] = 'Þessi hópur hefur eftirtalda meðlimi';

$lang['Forum_auth_updated'] = 'Heimildir umræða uppfærðar';
$lang['User_auth_updated'] = 'Heimildir notenda uppfærðar';
$lang['Group_auth_updated'] = 'Heimildir hópa uppfærðar';

$lang['Auth_updated'] = 'Heimildir hafa verið uppfærðar';
$lang['Click_return_userauth'] = 'Ýtið %sHér%s til að fara aftur á heimildir notanda';
$lang['Click_return_groupauth'] = 'Ýtið %sHér%s til að fara aftur á hóp heimildir';
$lang['Click_return_forumauth'] = 'Ýtið %sHér%s til að fara aftur á heimildir umræðuborðs';

//
// Banning
//
$lang['Ban_control'] = 'Bann stjórnun';
$lang['Ban_explain'] = 'Hér getur þú útilokað ákveðna notendur. Þú getur náð því með því að banna annaðhvort ákveðinn notanda og/eða röð af IP tölum eða netþjónum. Þessi aðferð útilokar notanda frá því jafnvel að sjá forsíðuna á umræðuborðinu. Til að útiloka notanda frá því að skrá sig undir öðru notendanafni þá getur þú líka útilokað ákveðin netföng. Athugaðu að það eitt að banna netfang útilokar ekki að notandi geti ekki sent inn innlegg og skráð sig inn, þú þarft að nota aðra fyrri aðferðina líka til þess.';
$lang['Ban_explain_warn'] = 'Athugaðu að með því að slá inn röð af IP tölum veldur því að allar tölur frá byrjun að enda eru settar á bannlista. Reynt verður að minnka þann fjölda talna sem bætt er í gagnagrunninn með því að nota * sjálfvirkt eftir því sem við á. Ef þú þarft endilega að setja inn röð af tölum hafðu þær eins fáar og hægt er og enn betra er að tilgreina ákveðnar tölur.';

$lang['Select_username'] = 'Veldu notandanafn';
$lang['Select_ip'] = 'Veldu IP tölu';
$lang['Select_email'] = 'Veldu netfang';

$lang['Ban_username'] = 'Banna fleiri en einn notanda';
$lang['Ban_username_explain'] = 'Þú getur bannað fleiri en einn notanda í einu með því að nota músina og lyklaborðið eins og hentar fyrir þína tölvu og þinn vafra.';

$lang['Ban_IP'] = 'Banna eina eða fleiri IP tölur eða vefþjóna';
$lang['IP_hostname'] = 'IP tölur eða vefþjóna';
$lang['Ban_IP_explain'] = 'Til að tilgreina margar mismunandi IP tölur eða vefþjóna þá þarf að hafa kommu á milli. Til að tilgreina röð af IP tölum hafðu þá mínus (-) á milli þeirra, til að notað sé * í skráningu';

$lang['Ban_email'] = 'Banna eitt eða fleiri netföng';
$lang['Ban_email_explain'] = 'Til að tilgreina fleiri en eitt netfang þá þarf að hafa kommu á milli. Til að tilgreina mörg með sömu stöfum þá notið *, til dæmis *@hotmail.com';

$lang['Unban_username'] = 'Leyfa aftur einn eða fleiri notendur';
$lang['Unban_username_explain'] = 'Þú getur leyft marga notendur í einu með því að halda niðri ctrl takkanum um leið og þú klikkar með músinni';

$lang['Unban_IP'] = 'Leyfa aftur eina eða fleiri IP tölur';
$lang['Unban_IP_explain'] = 'Þú getur leyft margar IP tölur í einu með því að halda niðri ctrl takkanum um leið og þú klikkar með músinni';

$lang['Unban_email'] = 'Leyfa eitt eða fleiri netföng í einu';
$lang['Unban_email_explain'] = 'Þú getur leyft mörg netföng í einu í einu með því að halda niðri ctrl takkanum um leið og þú klikkar með músinni';

$lang['No_banned_users'] = 'Engin bönnuð notendanöfn';
$lang['No_banned_ip'] = 'Engar bannaðar IP tölur';
$lang['No_banned_email'] = 'Engin bönnuð netföng';

$lang['Ban_update_sucessful'] = 'Bannlistinn hefur verið uppfærður';
$lang['Click_return_banadmin'] = 'Ýtið %sHér%s til að fara aftur á Bann stjórnun';

//
// Configuration
//
$lang['General_Config'] = 'Almenn uppsetning';
$lang['Config_explain'] = 'Formið hér neðan við gefur kleift að breyta öllum almennum stillingum á umræðuborðinu. Fyrir Notenda og Umræðu borðs stillingar þá skaltu nota viðeigandi tengla á vinstri skjánum.';

$lang['Click_return_config'] = 'Ýtið %sHér%s til að fara aftur á Almenna uppsetningu';

$lang['General_settings'] = 'Almenn stilling á borði';
$lang['Server_name'] = 'Nafn léns';
$lang['Server_name_explain'] = 'Nafn léns þaðan sem þetta borð er keyrt frá';
$lang['Script_path'] = 'Slóð í umræður';
$lang['Script_path_explain'] = 'Slóðin þar sem phpBB2 er staðsett séð frá léninu, ss. /spjall/';
$lang['Server_port'] = 'Port á vefþjón';
$lang['Server_port_explain'] = 'Portið sem vefþjónn þinn er keyrður á, venjulegas 80, bara breyta ef þetta er annað port';
$lang['Site_name'] = 'Nafn síðu';
$lang['Site_desc'] = 'Lýsing á síðu';
$lang['Board_disable'] = 'Gera borð óvirkt';
$lang['Board_disable_explain'] = 'Þetta er til að gera borðið óvirkt fyrir notendur. Ekki skrá þig út þar sem þú getur þá ekki skráð þig inn aftur!';
$lang['Acct_activation'] = 'Gera aðgangs virkjun virka';
$lang['Acc_None'] = 'Enginn'; // These three entries are the type of activation
$lang['Acc_User'] = 'Notandi';
$lang['Acc_Admin'] = 'Stjórnandi';

$lang['Abilities_settings'] = 'Notanda og Umræðuborðs grunnstillingar';
$lang['Max_poll_options'] = 'Mesti fjöldi möguleika í kosningum';
$lang['Flood_Interval'] = 'Millibil á milli innleggja';
$lang['Flood_Interval_explain'] = 'Fjöldi sekúnda sem notandi verður að bíða á milli þess sem hann sendir inn innlegg'; 
$lang['Board_email_form'] = 'Notandi getur sent Póst um borðið';
$lang['Board_email_form_explain'] = 'Notendur geta sent hvor öðrum Póst um borðið';
$lang['Topics_per_page'] = 'Spjallþræðir á blaðsíðu';
$lang['Posts_per_page'] = 'Innlegg á blaðsíðu';
$lang['Hot_threshold'] = 'Fjöldi innleggja til að verða vinsæl';
$lang['Default_style'] = 'Sjálfvalið þema';
$lang['Override_style'] = 'Taka yfir þema sem notandi velur';
$lang['Override_style_explain'] = 'Tekur yfir þema sem notandi velur';
$lang['Default_language'] = 'Sjálfvalið tungumál';
$lang['Date_format'] = 'Form á dagsetningu';
$lang['System_timezone'] = 'Tímabelti kerfis';
$lang['Enable_gzip'] = 'Virkja GZip gagnaþjöppun';
$lang['Enable_prune'] = 'Virkja eyðingu á eldri innleggjum';
//
// MOD: Birthday/Zodiac v1.0.2
//
$lang['Require_birthday'] = 'Þurfa notendur að gefa upp aldur?';
$lang['Require_birthday_explain'] = 'Hér getur þú valið hvort notendur þurfa eða fá möguleika að gefa upp aldur sinn.';
$lang['Min_user_age'] = 'Minnsti aldur notenda (ár)';
$lang['Max_user_age'] = 'Mesti aldur notenda (ár)';
$lang['Birthday_lookahead'] = '# daga sem á að athuga með afmæli';
$lang['Birthday_lookahead_explain'] = 'Hér getur þú valið hve marga daga fram í tímann á að athuga með afmæli. Núll tekur þetta úr sambandi.';
$lang['Show_birthday_to_guests'] = 'Á að leyfa gestum að sjá afmælisdaga notenda?';
$lang['Show_birthday_to_guests_explain'] = 'Hér getur þú valið hvort gestir á síðunni geta séð afmælisdaga notenda, bara aldur, eða ekkert af þessu.';
$lang['Show_birthday_to_guests_none'] = 'Nei';
$lang['Show_birthday_to_guests_age'] = 'Bara aldur';
$lang['Show_birthday_to_guests_all'] = 'Aldur+Afmælisdagur';
$lang['Show_zodiac_sign'] = 'Sýna stjörnumerki og heiti?';
$lang['Show_zodiac_sign_explain'] = 'Ef aldur og/eða afmælisdagur er sýndur, þá sýnir þetta stjörnumerki og texta, til dæmis rétt hjá upplýsinga dálki höfundar.';
//
// MOD: -END-
//
$lang['Allow_HTML'] = 'Leyfa HTML';
$lang['Allow_BBCode'] = 'Leyfa BB kóða';
$lang['Allowed_tags'] = 'Leyfð HTML tög';
$lang['Allowed_tags_explain'] = 'Hafðu kommu á milli tagga';
$lang['Allow_smilies'] = 'Leyfa broskalla';
$lang['Smilies_path'] = 'Slóð að brosköllum';
$lang['Smilies_path_explain'] = 'Slóð undir phpBB rótar möppur, t.d. images/smilies';
$lang['Allow_sig'] = 'Leyfa undirskrift';
$lang['Max_sig_length'] = 'Mesta lengd á undiskrift';
$lang['Max_sig_length_explain'] = 'Mesti fjöldi á stöfum sem leyfilegur er í undirskrift';
$lang['Allow_name_change'] = 'Leyfa breytingu á notendanafni';

$lang['Avatar_settings'] = 'Stillingar á Myndum';
$lang['Allow_local'] = 'Virkja Mynda gallerý';
$lang['Allow_remote'] = 'Heimila Myndir frá öðrum vefþjón';
$lang['Allow_remote_explain'] = 'Myndir sem eru vísað í á annarri heimasíðu';
$lang['Allow_upload'] = 'Heimila að Myndir eru sóttar og geymdar á vefþjón';
$lang['Max_filesize'] = 'Mesta skráar stærð á Mynd';
$lang['Max_filesize_explain'] = 'Fyrir skrár sem sóttar eru';
$lang['Max_avatar_size'] = 'Mesta punkta stærð Myndar';
$lang['Max_avatar_size_explain'] = '(Hæð x Breidd í punktum)';
$lang['Avatar_storage_path'] = 'Slóð að Myndum';
$lang['Avatar_storage_path_explain'] = 'Slóð undir phpBB rótar möppu, t.d. images/avatars';
$lang['Avatar_gallery_path'] = 'Slóð að Myndasafni';
$lang['Avatar_gallery_path_explain'] = 'Slóð undir phpBB rótar möppu fyrir myndir sem eru settar inn fyrirfram, t.d. images/avatars/gallery';

$lang['COPPA_settings'] = 'COPPA Stilling';
$lang['COPPA_fax'] = 'COPPA Fax Númer';
$lang['COPPA_mail'] = 'COPPA Netfang';
$lang['COPPA_mail_explain'] = 'Þetta er netfang sem foreldrar geta sent inn COPPA skráningar eyðublaðið';

$lang['Email_settings'] = 'Email/póst stillingar';
$lang['Admin_email'] = 'Netfang umsjónarmanns';
$lang['Email_sig'] = 'Undirskrift á póst';
$lang['Email_sig_explain'] = 'Þessi texti er settur aftan við allan póst sem borðið sendir';
$lang['Use_SMTP'] = 'Nota SMTP póstþjón fyrir póstsendingar';
$lang['Use_SMTP_explain'] = 'Virkja ef þú vilt senda póst með öðrum póstþjón en þeim sem er innbyggður í umræðuborðið';
$lang['SMTP_server'] = 'Vistfang SMTP póstþjóns';
$lang['SMTP_username'] = 'SMTP Notandanafn';
$lang['SMTP_username_explain'] = 'Bara setja inn nafn ef póstþjónn krefst þess';
$lang['SMTP_password'] = 'Aðgangsorð póstþjóns';
$lang['SMTP_password_explain'] = 'Bara setja inn aðgangsorð ef póstþjónn krefst þess';

$lang['Disable_privmsg'] = 'Einkapóstur';
$lang['Inbox_limits'] = 'Mesta magn pósts í Innhólfi';
$lang['Sentbox_limits'] = 'Mesta magn pósts í hólfi fyrir Sendan póst';
$lang['Savebox_limits'] = 'Mesta magn pósts í hólfi fyrir Vistaðan póst';

$lang['Cookie_settings'] = 'Stillingar á vefkökum'; 
$lang['Cookie_settings_explain'] = 'Þessar stillingar segja til hvernig vefkökur eru sendar til vefvafra notenda. Í flestum tilfellum eru sjálfgefin gildi best en ef þú þarft að breyta þeim af einhverjum sökum þá gerðu það með varúð, rangar stillingar geta valdið að notendur geti ekki skráð sig inn';
$lang['Cookie_domain'] = 'Lén fyrir vefkökur';
$lang['Cookie_name'] = 'Nafn á vefkökum';
$lang['Cookie_path'] = 'Slóð fyrir vefkökur';
$lang['Cookie_secure'] = 'Vefkökur á SSL';
$lang['Cookie_secure_explain'] = 'Ef vefþjónninn þinn er keyrður yfir SSL þá á að virkja þessa stillingu, annars ekki';
$lang['Session_length'] = 'Lengd innskráningar [ sekúndur ]';

//
// Forum Management
//
$lang['Forum_admin'] = 'Stjórnun Umræðuborðs';
$lang['Forum_admin_explain'] = 'Á þessari síðu getur þú bætt við, eytt, breytt, endur-raðað og endur-stillt umræðu flokka og umræður';
$lang['Edit_forum'] = 'Breyta umræðum';
$lang['Create_forum'] = 'Bæta við umræðum';
$lang['Create_category'] = 'Bæta við umræðuflokk';
$lang['Remove'] = 'Eyða';
$lang['Action'] = 'Aðgerð';
$lang['Update_order'] = 'Uppfæra röð';
$lang['Config_updated'] = 'Tókst að uppfæra stillingar';
$lang['Edit'] = 'Breyta';
$lang['Delete'] = 'Eyða';
$lang['Move_up'] = 'Færa upp';
$lang['Move_down'] = 'Færa niður';
$lang['Resync'] = 'Endurstilla';
$lang['No_mode'] = 'Engin aðgerð stillt';
$lang['Forum_edit_delete_explain'] = 'Formið hér neðanvið er til að breyta grunnstillingum fyrir umræðuborðið. Fyrir "notendur" og "umræður", notið þá viðeigandi tengla á vinstri hluta skjásins';

$lang['Move_contents'] = 'Færa allt innihald';
$lang['Forum_delete'] = 'Eyða umræðum';
$lang['Forum_delete_explain'] = 'Formið hér neðan við gefur kleyft að eyða umræðum (eða umræðu flokka) eða ákveðið hvar þú vilt hafa spjallþræðina (eða umræðurnar).';

$lang['Status_locked'] = 'Lokað';
$lang['Status_unlocked'] = 'Opnar';
$lang['Forum_settings'] = 'Almennar stillingar fyrir umræður';
$lang['Forum_name'] = 'Nafn á umræðuhópnum';
$lang['Forum_desc'] = 'Lýsing';
$lang['Forum_status'] = 'Staða umræðuborðs';
$lang['Forum_pruning'] = 'Sjálfvirk eyðing gagna';

$lang['prune_freq'] = 'Athuga með aldur spjallþráða alla daga';
$lang['prune_days'] = 'Eyða spjallþráðum sem ekki hefur verið sett innlegg';
$lang['Set_prune_data'] = 'Þú hefur sett sjálfvirka eyðingu á en þú settir ekki hversu oft eða með hvað margra daga millibili. Farðu aftur og stilltu það';

$lang['Move_and_Delete'] = 'Færa og eyða';

$lang['Delete_all_posts'] = 'Eyða öllum innleggjum';
$lang['Nowhere_to_move'] = 'Ekki valið hvert á að færa';

$lang['Edit_Category'] = 'Breyta umræðuflokki';
$lang['Edit_Category_explain'] = 'Notið þetta form til að breyta nafni á umræðuhópi.';

$lang['Forums_updated'] = 'Umræður og flokkar uppfærðir';

$lang['Must_delete_forums'] = 'Þú þarft að eyða öllum umræðum áður en þú getur eytt flokki';

$lang['Click_return_forumadmin'] = 'Ýtið %sHér%s til að fara til baka á "Umræðu stjórnun"';

//
// Smiley Management
//
$lang['smiley_title'] = 'Hér er hægt að breyta brosköllum';
$lang['smile_desc'] = 'Á þessari síðu getur þú bætt við, eytt og breytt brosköllum sem notendur geta notað í innleggjum og einkapósti.';

$lang['smiley_config'] = 'Broskalla stillingar';
$lang['smiley_code'] = 'Broskalla kóðar';
$lang['smiley_url'] = 'Skrá fyrir broskalla';
$lang['smiley_emot'] = 'Broskall';
$lang['smile_add'] = 'Bæta við nýjum broskalli';
$lang['Smile'] = 'Broskall';
$lang['Emotion'] = 'Svipbrigði';

$lang['Select_pak'] = 'Veldu pakka (.pak) Skrá';
$lang['replace_existing'] = 'Skipta um núverandi broskall';
$lang['keep_existing'] = 'Halda núverandi broskalli';
$lang['smiley_import_inst'] = 'Þú þarft að afþjappa broskalla skránni og senda hana svo í viðeigandi möppu á vefþjóninum þínum. Svo þarftu að skrá réttar upplýsingar hér á síðuna til að flytja inn broskallana.';
$lang['smiley_import'] = 'Broskalla innflutningur';
$lang['choose_smile_pak'] = 'Veldu broskalla pakka (.pak) skrá';
$lang['import'] = 'Flytja inn broskalla';
$lang['smile_conflicts'] = 'Hvað á að gera ef verður árekstur';
$lang['del_existing_smileys'] = 'Eyða núverandi broskalli fyrir innflutning';
$lang['import_smile_pack'] = 'Flytja inn broskall';
$lang['export_smile_pack'] = 'Búa til broskalla Pakka skrá';
$lang['export_smiles'] = 'Til að búa til broskalla pakka skrá úr núverandi brosköllum, ýttu þá %sHér%s til að sækja smiles.pak skrá. Nefndu þessa skrá með viðeigandi nafni og vertu viss um að halda skráar endingunni (.pak). Þá getur þú útbúið zip skrá sem inniheldur alla broskallana þína ásamt þessa .pak stillinga skrá.';

$lang['smiley_add_success'] = 'Þessum broskalli tókst að bæta við';
$lang['smiley_edit_success'] = 'Það tókst að uppfæra þennan broskall';
$lang['smiley_import_success'] = 'Það tókst að flytja inn Broskalla pakkann';
$lang['smiley_del_success'] = 'Það tókst að eyða þessum broskalli';
$lang['Click_return_smileadmin'] = 'Ýtið %sHér%s til að fara til baka á broskalla umsjón';

//
// User Management
//
$lang['User_admin'] = 'Notenda Umsýsla';
$lang['User_admin_explain'] = 'Hér getur þú breytt notenda uppsetningu og auka uppsetningu varðandi þá. Til að breyta heimildum notenda þá á að nota þar til gerðar valmyndir.';

$lang['Look_up_user'] = 'Leita að notanda';

$lang['Admin_user_fail'] = 'Gat ekki uppfært uppsetningu notanda.';
$lang['Admin_user_updated'] = 'Það tókst að uppfæra uppsetningu notanda.';
$lang['Click_return_useradmin'] = 'Ýtið %sHér%s til að fara til baka á "Notenda Umsýslu"';

$lang['User_delete'] = 'Eyða þessum notanda';
$lang['User_delete_explain'] = 'Ýtið hér til að eyða þessum notanda, ekki er hægt að hætta við.';
$lang['User_deleted'] = 'Tókst að eyða notanda.';

$lang['User_status'] = 'Notandi er virkur';
$lang['User_allowpm'] = 'Getur sent einkapóst';
$lang['User_allowavatar'] = 'Getur verið með myndir';

$lang['Admin_avatar_explain'] = 'Hér getur þú séð og eytt mynd notanda';

$lang['User_special'] = 'Sérstakir reitir, bara fyrir stjórnanda';
$lang['User_special_explain'] = 'Þessum reitum getur notandi ekki breytt. Hér getur þú breytt stöðu og öðrum stillingum sem notandi sér ekki.';

//
// Group Management
//
$lang['Group_administration'] = 'Hóp umsjón';
$lang['Group_admin_explain'] = 'Frá þessu stjórnborði getur þú stjórnað öllum notenda hópum, þú getur; eytt, búið til og breytt hópum sem til eru. Þú getur valið umsjónarmenn, opnað eða lokað hóp, nefnt hóp og sett lýsingu';
$lang['Error_updating_groups'] = 'Það kom villa við að uppfæra hópa';
$lang['Updated_group'] = 'Það tókst að uppfæra hópa';
$lang['Added_new_group'] = 'Það tókst að búa til nýjan hóp';
$lang['Deleted_group'] = 'Það tókst að eyða þessum hóp';
$lang['New_group'] = 'Búa til nýjan hóp';
$lang['Edit_group'] = 'Breyta hóp';
$lang['group_name'] = 'Nafn á hóp';
$lang['group_description'] = 'Lýsing á hóp';
$lang['group_moderator'] = 'Umsjónarmaður hóps';
$lang['group_status'] = 'Staða hóps';
$lang['group_open'] = 'Opinn hópur';
$lang['group_closed'] = 'Lokaður hópur';
$lang['group_hidden'] = 'Falinn hópur';
$lang['group_delete'] = 'Eyða hóp';
$lang['group_delete_check'] = 'Eyða þessum hóp';
$lang['submit_group_changes'] = 'Senda inn breytingar';
$lang['reset_group_changes'] = 'Núllsetja breytingar';
$lang['No_group_name'] = 'Þú verður að nefna þennan hóp';
$lang['No_group_moderator'] = 'Þú verður að hafa umsjónarmann fyrir þennan hóp';
$lang['No_group_mode'] = 'Þú verður að velja hvort hópur á að vera opinn eða lokaður';
$lang['No_group_action'] = 'Ekkert var valið';
$lang['delete_group_moderator'] = 'Eyða umsjónarmanni gamals hóps?';
$lang['delete_moderator_explain'] = 'Ef þú ert að breyta umsjónarmanni hóps, veldu þá þetta box til að eyða eldri umsjónarmanni frá þessum hóp. Ekki velja þetta box annars, þá verður umsjónarmaður sem venjulegur notandi hópsins.';
$lang['Click_return_groupsadmin'] = 'Ýtið %sHér%s til að fara til baka á "Hóp umsjón".';
$lang['Select_group'] = 'Veldu hóp';
$lang['Look_up_group'] = 'Leita að hóp';

//
// Prune Administration
//
$lang['Forum_Prune'] = 'Eyða eldra efni';
$lang['Forum_Prune_explain'] = 'Þetta eyðir gömlum spjallþráðum þar sem ekki hefur komið svar við eftir þann  fjölda daga sem þú velur. Ef þú setur ekki inn tölu þá verður öllum spjallþráðum eytt. Þetta eyðir ekki spjallþráðum sem eru með virkar kosningar eða tilkynningar. Þú verður að eyða þessum spjallþráðum handvirkt.';
$lang['Do_Prune'] = 'Eyða eldra efni';
$lang['All_Forums'] = 'Allir umræðu flokkar';
$lang['Prune_topics_not_posted'] = 'Eyða spjallþráðum sem ekki er svarað eftir ákveðið marga daga';
$lang['Topics_pruned'] = 'Spjallþráðum eytt';
$lang['Posts_pruned'] = 'Innleggjum eytt';
$lang['Prune_success'] = 'Það tókst að eyða gömlum umræðum';

//
// Word censor
//
$lang['Words_title'] = 'Ritskoðun/orða eftirlit';
$lang['Words_explain'] = 'Hér getur þú bætt við orðum sem þarf að ritskoða. Einnig getur þú breytt og eytt orðum úr lista yfir orð sem sjálfvirkt verða fjarlægð úr umræðunum. Að auki geta notendur ekki skráð sig undir nafni sem inniheldur þessi orð. Heimilt er að nota stjörnu (*) til viðbótar við orð, td. *póst* passar við einkapóstur, póst* passar við póstur, *póst passar við einkapóst.';
$lang['Word'] = 'Orð';
$lang['Edit_word_censor'] = 'Breyta ritskoðuðum orðum';
$lang['Replacement'] = 'Orð sem kemur í staðinn';
$lang['Add_new_word'] = 'Bæta við orði';
$lang['Update_word'] = 'Uppfæra orða skrá';

$lang['Must_enter_word'] = 'Þú verður að setja inn orð og annað orð sem kemur í staðinn';
$lang['No_word_selected'] = 'Ekkert orð valið til að breyta';

$lang['Word_updated'] = 'Valið orð hefur verið uppfært í gagnagrunn';
$lang['Word_added'] = 'Orði hefur verið bætt við í ritskoðun';
$lang['Word_removed'] = 'Völdu orði hefur verið eytt';

$lang['Click_return_wordadmin'] = 'Ýtið %sHér%s til að fara til baka á ritskoða/orða eftirlits stjórnun';

//
// Mass Email
//
$lang['Mass_email_explain'] = 'Hér getur þú sent Póst til annað hvort allra notenda þinna eða allra notenda á ákveðnum hóp. Til að gera það þá er sendur póstur til netfangs umsjónarmanns sem slegið er inn og svo er sent afrit til allra valinna notenda. Ef þú ert að senda á stóran hóp þá þarft þú að vera þolinmóð/ur því að það tekur langan tíma að senda mörgum póst og ekki stoppa eftir hálfa síðu. Þú verður látinn vita þegar þetta er búið.';
$lang['Compose'] = 'Semja'; 

$lang['Recipients'] = 'Mótttakendur'; 
$lang['All_users'] = 'Allir notendur';

$lang['Email_successfull'] = 'Skilaboðin hafa verið send';
$lang['Click_return_massemail'] = 'Ýtið %sHér%s til að fara til baka á Fjölpósts síðu';

//
// Ranks admin
//
$lang['Ranks_title'] = 'Umsjón með stigum';
$lang['Ranks_explain'] = 'Hér er hægt að bæta við, breyta, skoða og eyða stigum. Þú getur líka búið til stig sem hægt er að bæta við notendur á umsjónar síðu fyrir notendur';

$lang['Add_new_rank'] = 'Bæta við stigum';

$lang['Rank_title'] = 'Nafn stigs';
$lang['Rank_special'] = 'Hafa sem sérstakt stig';
$lang['Rank_minimum'] = 'Minnsti fjöldi innleggja';
$lang['Rank_maximum'] = 'Mesti fjöldi innleggja';
$lang['Rank_image'] = 'Mynd stigs (Frá rótar möppu phpBB2)';
$lang['Rank_image_explain'] = 'Hér velur þú litla mynd sem fylgir með stiginu';

$lang['Must_select_rank'] = 'Þú verður að velja stig';
$lang['No_assigned_rank'] = 'Ekkert sérstakt stig valið';

$lang['Rank_updated'] = 'Það tókst að uppfæra stig';
$lang['Rank_added'] = 'Það tókst að bæta við stigum';
$lang['Rank_removed'] = 'Það tókst að eyða stigum';
$lang['No_update_ranks'] = 'Það tókst að eyða stigunum, hinsvegar var ekki eytt frá notendum sem nota þessi stig. Þú þarft að stilla það í uppsetningu notenda sem voru að nota þetta stig';

$lang['Click_return_rankadmin'] = 'Ýtið %sHér%s til að fara til baka á "Stiga umsjón"';

//
// Disallow Username Admin
//
$lang['Disallow_control'] = 'Banna notendanöfn';
$lang['Disallow_explain'] = 'Hér er hægt að banna notkun á ákveðnum notendanöfnum. Það er hægt að nota stjörnu *.  Athugaðu að þú getur ekki sett inn orð sem er þegar í notkun, þú verður fyrst að eyða notendum sem nota þau til að geta gert það';

$lang['Delete_disallow'] = 'Eyða';
$lang['Delete_disallow_title'] = 'Fjarlægja bönnuð nöfn';
$lang['Delete_disallow_explain'] = 'Þú getur tekið út nöfn sem eru bönnuð með því að velja úr listanum';

$lang['Add_disallow'] = 'Bæta við';
$lang['Add_disallow_title'] = 'Bæta við nafni sem á að banna';
$lang['Add_disallow_explain'] = 'Þú getur notað * fyrir hvaða staf sem er í orði sem á að banna';

$lang['No_disallowed'] = 'Engin bönnuð notendanöfn';

$lang['Disallowed_deleted'] = 'Það tókst að fjarlægja nafn úr listanum yfir bönnuð nöfn';
$lang['Disallow_successful'] = 'Það tókst að bæta við nafni í listann yfir bönnuð nöfn';
$lang['Disallowed_already'] = 'Það tókst ekki að bæta þessu nafni við listann. Það gæti verið í notkun,  það er fyrir í listanum yfir ritskoðuð orð eða það er þegar til sem notenda nafn';

$lang['Click_return_disallowadmin'] = 'Ýtið %sHér%s til að fara til baka á "Banna notendanöfn"';

//
// Styles Admin
//
$lang['Styles_admin'] = 'Þema stjórnun';
$lang['Styles_explain'] = 'Hér getur þú bætt við, eytt eða haft umsjón með þema (snið og þema) sem notendur geta notað';
$lang['Styles_addnew_explain'] = 'Eftirfarandi listi inniheldur alla þema sem eru til fyrir þitt snið. Það sem er á listanum hefur ekki verið sett inn í gagnagrunn phpBB. Til að setja inn þema þá þarf bara að ýta á "setja inn" tengil hjá nafninu';

$lang['Select_template'] = 'Veldu snið';

$lang['Style'] = 'Þema';
$lang['Template'] = 'Snið';
$lang['Install'] = 'Setja inn';
$lang['Download'] = 'Sækja skrá';

$lang['Edit_theme'] = 'Breyta þema';
$lang['Edit_theme_explain'] = 'Hér neðan við getur þú breytt uppsetningu fyrir valið þema';

$lang['Create_theme'] = 'Búa til þema';
$lang['Create_theme_explain'] = 'Notið formið hér neðanvið til að búa til nýtt þema fyrir valið snið. Þegar þú ert að setja inn liti (þá þarft þú að nota tölur í sextánda kerfinu(hexadecimal notation)) verður þú að passa að nota ekki #, t.d.. er CCCCCC í lagi en #CCCCCC ekki';

$lang['Export_themes'] = 'Flytja út þema';
$lang['Export_explain'] = 'Hér er hægt að flytja út gögn fyrir þema á valið snið. Veldu snið af listanum hér neðan við og þá verður reynt að útbúa þema skrá og hún verður vistuð í valda möppu. Ef ekki er hægt að vista skrána sjálfa þá getur þú valið um að fá skrána senda til þín. Til að hægt sé að vista skrána á vefþjóninum þá þarf að vera ritheimild á möppuna sem er valin. Fyrir meiri upplýsingar þá skaltu skoða notenda leiðbeiningar phpBB 2.';

$lang['Theme_installed'] = 'Það tókst að setja inn valið þema ';
$lang['Style_removed'] = 'Það tókst að fjarlægja valið þema úr gagnagrunninum. Til að fjarlægja það alveg úr kerfinu þá þarf að eyða skránum úr viðeigandi snið (template) möppu.';
$lang['Theme_info_saved'] = 'Þema upplýsingar fyrir valið snið hafa verið vistaðar. Þú ættir að taka af ritheimild á theme_info.cfg skrá (og einnig valda snið (template) möppu)';
$lang['Theme_updated'] = 'Valið þema hefur verið uppfært. Þú ættir nú að flytja út nýja uppsetningu á þema';
$lang['Theme_created'] = 'Þema hefur verið útbúið. Þú ættir að flytja út þema í skrá til að hafa eintak á öruggum stað eða til nota annarsstaðar';

$lang['Confirm_delete_style'] = 'Ertu viss um að þú viljir eyða þessu þema';

$lang['Download_theme_cfg'] = 'Það gekk ekki að skrifa þema upplýsinga skrána. Ýttu á hnapp hér neðan við til að sækja skrána með vafranum þínum. Þegar þú ert búinn að fá skrána til þín þá getur þú sent hana í möppuna með þema skránum á vefþjóninum. Þú getur líka pakkað skránum til að dreifa þeim eða til að geyma á öruggum stað';
$lang['No_themes'] = 'Það snið sem þú valdir inniheldur enga þema upplýsingar. Til að búa til nýtt þema ýtið þá á "Búa til" undir þema á vinstri valmynd';
$lang['No_template_dir'] = 'Gat ekki opnað snið möppu. Mappan gæti verið ólæsileg af vefþjóni eða hún er ekki til';
$lang['Cannot_remove_style'] = 'Þú getur ekki fjarlægt þema þar sem það er sjálfvalið fyrir umræðuhópinn.Skiptu um sjálfvalið þema og reyndi aftur.';
$lang['Style_exists'] = 'Nafn þema sem þú valdir er þegar til, farðu til baka og veldu annað nafn.';

$lang['Click_return_styleadmin'] = 'Ýtið %sHér%s til að fara tilbaka á Þema stjórnun';

$lang['Theme_settings'] = 'Þema stillingar';
$lang['Theme_element'] = 'Þema hlutar';
$lang['Simple_name'] = 'Einfalt nafn';
$lang['Value'] = 'Gildi';
$lang['Save_Settings'] = 'Vista stillingar';

$lang['Stylesheet'] = 'CSS Stylesheet';
$lang['Background_image'] = 'Mynd í bakgrunni';
$lang['Background_color'] = 'Litur á bakgrunni';
$lang['Theme_name'] = 'Nafn á þema';
$lang['Link_color'] = 'Litur á tengli';
$lang['Text_color'] = 'Litur á texta';
$lang['VLink_color'] = 'Litur á heimsóttum tengli';
$lang['ALink_color'] = 'Litur á virkum tengli';
$lang['HLink_color'] = 'Litur á link ef mús er yfir';
$lang['Tr_color1'] = 'Table Row Colour 1';
$lang['Tr_color2'] = 'Table Row Colour 2';
$lang['Tr_color3'] = 'Table Row Colour 3';
$lang['Tr_class1'] = 'Table Row Class 1';
$lang['Tr_class2'] = 'Table Row Class 2';
$lang['Tr_class3'] = 'Table Row Class 3';
$lang['Th_color1'] = 'Table Header Colour 1';
$lang['Th_color2'] = 'Table Header Colour 2';
$lang['Th_color3'] = 'Table Header Colour 3';
$lang['Th_class1'] = 'Table Header Class 1';
$lang['Th_class2'] = 'Table Header Class 2';
$lang['Th_class3'] = 'Table Header Class 3';
$lang['Td_color1'] = 'Table Cell Colour 1';
$lang['Td_color2'] = 'Table Cell Colour 2';
$lang['Td_color3'] = 'Table Cell Colour 3';
$lang['Td_class1'] = 'Table Cell Class 1';
$lang['Td_class2'] = 'Table Cell Class 2';
$lang['Td_class3'] = 'Table Cell Class 3';
$lang['fontface1'] = 'Font Face 1';
$lang['fontface2'] = 'Font Face 2';
$lang['fontface3'] = 'Font Face 3';
$lang['fontsize1'] = 'Font Size 1';
$lang['fontsize2'] = 'Font Size 2';
$lang['fontsize3'] = 'Font Size 3';
$lang['fontcolor1'] = 'Font Colour 1';
$lang['fontcolor2'] = 'Font Colour 2';
$lang['fontcolor3'] = 'Font Colour 3';
$lang['span_class1'] = 'Span Class 1';
$lang['span_class2'] = 'Span Class 2';
$lang['span_class3'] = 'Span Class 3';
$lang['img_poll_size'] = 'Polling Image Size [px]';
$lang['img_pm_size'] = 'Einkapóstur - Staða, stærð [px]';

//
// Install Process
//
$lang['Welcome_install'] = 'Velkomin til uppsetningur á phpBB 2';
$lang['Initial_config'] = 'Grunn stillingar';
$lang['DB_config'] = 'Stillingar á gagnagrunni';
$lang['Admin_config'] = 'Stillingar stjórnanda';
$lang['continue_upgrade'] = 'Þegar þú hefur sótt config skrána á tölvuna þína þá máttu ýta á \\"Halda áfram að uppfæra\\" hnappinn hér neðan við til að halda áfram með uppfærsluna. Þú þarft að bíða með að senda config skrána þar til eftir að uppfærslunni er lokið.';
$lang['upgrade_submit'] = 'Halda áfram að uppfæra';

$lang['Installer_Error'] = 'Villa varð við uppsetningu';
$lang['Previous_Install'] = 'Áður uppsett borð fannst';
$lang['Install_db_error'] = 'Villa varð við að uppfæra gagnagrunn';

$lang['Re_install'] = 'Áður uppsett borð er ennþá virkt. <br /><br />Ef þú vilt setja phpBB 2 inn upp á nýtt þá skaltu ýta á Já takkann hér neðan við. Athugaðu að þá eyðast ÖLL gögn sem til eru, engin afrit eru tekin! Aðgangsorð stjórnanda sem þú hefur notað verður haldið eftir uppsetningu en EKKI öðru. <br /><br />Hugsaðu þig vel um áður en þú ýtir á Já!';

$lang['Inst_Step_0'] = 'Þakka þeér fyrir að velja phpBB 2. Til að ljúka þessari uppsetningu þá þarftu að fylla út upplýsingar sem spurt er um hér fyrir neðan. Athugaðu að gagnagrunnur sem þú kemur til með að nota verður að vera orðinn virkur. Ef þú ert að tengja við gagnagrunn sem notar ODBC, s.s. MS Access þá þarftu fyrst að búa til DSN áður en þú heldur áfram.';

$lang['Start_Install'] = 'Byrja uppsetningu';
$lang['Finish_Install'] = 'Ljúka uppsetningu';

$lang['Default_lang'] = 'Sjálfvalið tungumál borðs';
$lang['DB_Host'] = 'Database Server Hostname / DSN';
$lang['DB_Name'] = 'Nafn á þínum gagnagrunni';
$lang['DB_Username'] = 'Database Username';
$lang['DB_Password'] = 'Database Password';
$lang['Database'] = 'Þinn gagnagrunnur';
$lang['Install_lang'] = 'Veldu tungumál við uppsetningu';
$lang['dbms'] = 'Gerð gagnagrunns';
$lang['Table_Prefix'] = 'Prefix for tables in database';
$lang['Admin_Username'] = 'Notenda nafn stjórnanda';
$lang['Admin_Password'] = 'Aðgangsorð stjórnanda';
$lang['Admin_Password_confirm'] = 'Aðgangsorð stjórnanda [ staðfestið ]';

$lang['Inst_Step_2'] = 'Notenda nafn stjórnanda hefur verið búið til. Núna er grunnuppsetningu lokið. Þú verður nú sendur á skjá þar sem þú getur stillt nýju uppsetninguna þína. Vertu viss um að athuga stillingar í "Almennri uppsetningu". Takk fyrir að velja phpBB 2.';

$lang['Unwriteable_config'] = 'Config skráin er ritvarin sem stendur. Afrit af skránni verður nú sótt þegar þú ýtir á takkann hér fyrir neðan. Þú skalt þá senda skrána aftur í sömu möppu á vefþjóninum sem phpBB 2 er í. Þegar þetta er búið þá áttu að tengja þig með stjórnenda nafninu þínu og aðgangsorðinu sem þú valdir og fara þá á stjórnborð fyrir umræðuborðsstjóra (tengill kemur neðst á skjáinn) og athuga "Almenna uppsetningu". Þakka þér fyrir að velja phpBB 2.';
$lang['Download_config'] = 'Sækja config skrá';

$lang['ftp_choose'] = 'Veldu aðferð við að sækja skrá';
$lang['ftp_option'] = '<br />Þar sem FTP möguleiki er virkur í þessari útgáfu af PHP þá getur verið að þér verði boðið að senda config skrána með ftp sjálfvirkt á réttan stað.';
$lang['ftp_instructs'] = 'Þú hefur valið að senda skrána með ftp sjálfvirkt á vefþjóninn sem er með phpBB 2.  Sláðu inn upplýsingar sem spurt er um hér neðan við til að það sé hægt. Athugið að FTP slóðin verður að vera alveg eins og sú slóð sem þú myndir nota með venjulegum ftp.';
$lang['ftp_info'] = 'Sláðu inn FTP upplýsingar';
$lang['Attempt_ftp'] = 'Reyni að senda config skrá á sinn stað';
$lang['Send_file'] = 'Sendu skrána til mín og ég sendi hana sjálfur';
$lang['ftp_path'] = 'FTP slóð að phpBB 2';
$lang['ftp_username'] = 'Notenda nafn FTP';
$lang['ftp_password'] = 'Aðgangsorð FTP';
$lang['Transfer_config'] = 'Byrja sendingu';
$lang['NoFTP_config'] = 'Það tókst ekki að senda config skrána á sinn stað. Sæktu hana og sendu hana handvirkt á sinn stað á vefþjóninum.';

$lang['Install'] = 'Setja inn';
$lang['Upgrade'] = 'Uppfæra';

$lang['Install_Method'] = 'Veldu aðferð við uppsetningu';

$lang['Install_No_Ext'] = 'Uppsetning php á vefþjóninum styður ekki þá gerð af gagnagrunni sem þú ert búinn að velja.';

$lang['Install_No_PCRE'] = 'phpBB2 þarf Perl-Compatible Regular Expressions Module for php sem uppsetning php á vefþjóni þínum styður ekki!';

//
// MOD: Prune Inactive Users v1.2.0
//
$lang['User_prune_this_user'] = 'Eyða notanda %s'; // ALT/TITLE text for memberlist delete button
$lang['Confirm_delete_users'] = 'Ertu viss um að þú viljir eyða %s? Þetta er ekki aftur tekið.';
$lang['User_prune_none_explain'] = 'Engum notendum var eytt.';
$lang['User_prune_deleted_explain'] = 'Þessum %d notendum var eytt:';
$lang['Click_return_user_prune'] = 'Ýtið %sHér%s til að fara til baka í eyðingar á gömlu efni';
$lang['Click_return_caller'] = 'Ýtið %sHér%s til að fara til baka';

$lang['User_prune'] = 'Eyða notendum';
$lang['User_prune_explain'] = 'Hér getur þú eytt notendum sem eru ekki lengur virkir. Þú getur valið um þrjár aðferðir: Þú getur eytt eldri notendum sem hafa aldrei sent inn innlegg, þú getur eytt notendum sem hafa aldrei skráð sig inn, þú getur eytt notendum sem hafa aldrei gert aðgang sinn virkan.<p/><p>Að auki eru tvær aðrar leiðir að eyða notendum sem eru - að nafninu til - virkir. Það er að eyða þeim sem hafa ekki sent innlegg inn eða skráð sig inn lengi.<p/><p><b>Ath.:</b> Þeim innleggjum sem eru send inn af notendum sem er eytt er <i>ekki</i> breytt. Þau innlegg halda nafninu á höfundi en eru þá merkt sem send inn af \'Gestur\'.<p/><p><b>Viðvörun! Öllum notendum verður eytt endanlega.</b> Þó svo að sá hinn sami skrái sig inn aftur undir sama nafni þá er það ekki tengt við fyrri innlegg!</p>'; 
$lang['User_prune_list'] = 'Notendur sem verður eytt';
$lang['User_prune_scheme'] = 'Aðferð við að eyða';

// $lang['User_prune_action'] = array();
// $lang['User_prune_action_explain'] = array();
// More entries go here if needed. Entries #0 and #1 are 'hard coded';
$lang['User_prune_action']['0'] = 'Eyða einum notanda (user_name)';
$lang['User_prune_action_explain']['0'] = 'Eyða einum notanda eftir notenda nafni: admin/admin_user_prune?mode=delete&type=user_name&user={username}';
$lang['User_prune_action']['1'] = 'Eyða einum notanda (user_id)';
$lang['User_prune_action_explain']['1'] = 'Eyða einum notanda eftir nafni: admin/admin_user_prune?mode=delete&type=user_id&user={user id}';
$lang['User_prune_action']['2'] = 'Eyða notendum sem ekki hafa gert aðgang virkan';
$lang['User_prune_action_explain']['2'] = 'Notendur sem hafa skráð sig en hafa ekki gert aðgang sinn virkan, <b>utanvið</b> nýskráningar síðustu %d daga.';
$lang['User_prune_action']['3'] = 'Eyða óvirkum notendum';
$lang['User_prune_action_explain']['3'] = 'Notendur sem hafa aldrei skráð sig inn, <b>utanvið</b> nýja notendur frá síðustu %d daga.';
$lang['User_prune_action']['4'] = 'Eyða notendum sem ekki hafa sent inn innlegg';
$lang['User_prune_action_explain']['4'] = 'Notendur sem hafa ekki sent inn innlegg, <b>utanvið</b> nýja notendur frá síðustu %d daga.';
$lang['User_prune_action']['5'] = 'Eyða notendum sem senda ekki inn innlegg (virkir notendur)';
$lang['User_prune_action_explain']['5'] = 'Notendur sem eru \'virkir\' en <b>hafa ekki sent inn innlegg síðustu %d daga.</b>';
$lang['User_prune_action']['6'] = 'Eyða notendum sem ekki hafa skráð sig inn lengi';
$lang['User_prune_action_explain']['6'] = 'Notendur sem eru \'virkir\' en <b>hafa ekki skráð sig inn síðustu %d daga.</b>';
//
// MOD: -END-
//

//
// MOD: Rebuild Search Tables v1.1.0
//
$lang['Rebuild_Search'] = 'Endurbyggja leitar skrá'; // replaces Module Name
$lang['Rebuild_Search_Info'] = '<p><b>Athugið:</b> Að endurbyggja leitar skrána tekur langan tíma!</p><p>Ekki klikka á neitt í vafranum þínum á meðan þetta er gert!</p>';
$lang['Rebuild_Search_Start'] = 'Byrja';
$lang['Rebuild_Search_Done'] = 'Endurbygging á leitar skrá er lokið.';
//
// MOD: -END-
//

//
// MOD: MyCalendar v2.1.6
//
$lang['Events_Forum'] = 'Leyfa hátíðisdaga? ';
//
// MOD: -END-
//

//
// That's all Folks!
// -------------------------------------------------

?>