<?php
/***************************************************************************
 *
 *	language/lang_icelandic/lang_admin_attach.php   [icelandic]
 *	------------------------------------------------------------------------
 *
 *	Created     Sat,  7 Sep 2002 01:47:14 +0200
 *
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
// Attachment Mod Admin Language Variables
//

// Modules, this replaces the keys used
$lang['Attachments'] = 'Viðhengi';
$lang['Attachment'] = 'Viðhengi';

$lang['Extension_control'] = 'Viðauka stjórnun';

$lang['Extensions'] = 'Viðaukar';
$lang['Extension'] = 'Viðauki';
$lang['Mimetypes'] = 'MIME Gerðir'; 
$lang['Mimetype'] = 'MIME Gerð'; 
$lang['Mimegroups'] = 'MIME Hópar';
$lang['Mimegroup'] = 'MIME Hópur';

// Auth pages
$lang['Auth_attach'] = 'Senda inn skrár';
$lang['Auth_download'] = 'Hlaða niður';

// Attachments
$lang['Select_action'] = 'Hvað á að gera';

// Attachments -> Management
$lang['Attach_settings'] = 'Stilling viðhengis';
$lang['Manage_attachments_explain'] = 'Hérna getur þú stillt stillingar fyrir viðhengi';
$lang['Attach_filesize_settings'] = 'Stærð á viðhengi';
$lang['Attach_number_settings'] = 'Fjöldi viðhengja';
$lang['Attach_options_settings'] = 'Aukastillingar fyrir viðhengi';

$lang['Upload_directory'] = 'Mappa fyrir viðhengi';
$lang['Upload_directory_explain'] = 'Settu inn slóð á möppu til að vista viðhengi í séð frá phpBB rótarmöppu. Til dæmis, settu \'files\' ef phpBB2 forritið er: http://www.yourdomain.com/phpBB2 og viðhengja mappan er hér:  http://www.yourdomain.com/phpBB2/files.';
$lang['Attach_img_path'] = 'Smámynd fyrir innlegg';
$lang['Attach_img_path_explain'] = 'Þessi mynd kemur næst við tengil á viðhengi í viðkomandi innleggi.  Ekki setja neitt ef þú vilt ekki að það sé nein mynd sýnd.';
$lang['Attach_topic_icon'] = 'Mynd fyrir spjallþráð sem er með viðhengi';
$lang['Attach_topic_icon_explain'] = 'Þessi mynd er sett við spjallþráð sem inniheldur viðhengi.  Ekki setja neitt ef þú vilt ekki að það sé nein mynd sýnd.';

$lang['Max_filesize_attach'] = 'Skráarstærð';
$lang['Max_filesize_attach_explain'] = 'Mesta skráarstærð fyrir viðhengi (í bytes). 0 þýðir ótakmörkuð stærð.';
$lang['Attach_quota'] = 'Heildar stærð viðhengja ';
$lang['Attach_quota_explain'] = 'Mesta diskpláss sem ÖLL viðhengi mega taka.';
$lang['Max_filesize_pm'] = 'Mesta skráarstærð á möppu fyrir einkapóst';
$lang['Max_filesize_pm_explain'] = 'Mesta diskpláss sem viðhengi mega taka með einkapósti fyrir hvern og einn notanda.'; 

$lang['Max_attachments'] = 'Mesti fjöldi viðhengja';
$lang['Max_attachments_explain'] = 'Mesti fjöldi viðhengja sem heimill er í einu innleggi.';
$lang['Max_attachments_pm'] = 'Mesti fjöldi viðhengja í einni einkapóst sendingu';
$lang['Max_attachments_pm_explain'] = 'Hér setur þú fjölda viðhengja sem notandi má setja með inn með einkapósti.';

$lang['Disable_mod'] = 'Taka viðhengja kerfi úr sambandi';
$lang['Disable_mod_explain'] = 'Þetta er aðallega til að prófa ný snið eða þema á umræðuborðinu, þá hættir að virka viðhengja möguleikinn en Stjórnborðið virkar samt áfram.';
$lang['PM_Attachments'] = 'Heimila viðhengi í einkapósti';
$lang['PM_Attachments_explain'] = 'Heimilar/Bannar viðhengi í einkapóstsendingum';
$lang['Ftp_upload'] = 'Gera FTP upphal virkt';
$lang['Ftp_upload_explain'] = 'Virkt/óvirkt FTP upphals möguleiki. Ef þú setur Já, þá verður þú að stilla FTP stillingar fyrir viðhengi og þá er mappan fyrir viðhengi ekki lengur notuð.';

$lang['Attach_ftp_path'] = 'Slóð FTP að upphals möppu';
$lang['Attach_ftp_path_explain'] = 'Þetta er mappan sem viðhengi verða vistuð. Þessi mappa þarf ekki að vera chmodded. Ekki setja inn IP eða FTP-Netfang hér, sjálfgefin IP tala er localhost, þessi kassi er bara fyrir FTP slóð.<br />Til dæmis: /home/web/uploads';
$lang['Ftp_download_path'] = 'Niðurhals tengill að FTP slóð';
$lang['Ftp_download_path_explain'] = 'Settu inn slóð fyrir FTP frá phpBB2 uppsetningunni þinni. Til dæmis, settu in \'ftpfiles\' ef phpBB2 uppsetningin er: http://www.yourdomain.com/phpBB2 og viðhengja mappan er: http://www.yourdomain.com/phpBB2/ftpfiles.<br />Hafðu þennan kassa tóman er þú ert með slóð utan vef-rótar. Með þessum kassa tómum þá virkar ekki niðurhalið með FTP.';

$lang['Attach_config_updated'] = 'Viðhengja stillingar tókust';
$lang['Click_return_attach_config'] = 'Ýtið %sHér%s til að fara til baka til Viðhengja stillingar';

// Attachments -> Extension Control
$lang['Manage_forbidden_extensions'] = 'Stjórnun á bönnuðum endingum';
$lang['Manage_forbidden_extensions_explain'] = 'Hér er hægt að bæta við eða eyða endingum sem heimilaðar eru í viðhengjum. Endingarnar php, php3 og php4 eru bannaðar, þú getur ekki eytt þeim.';
$lang['Extension_exist'] = 'Endingin %s er þegar til'; // replace %s with the extension

// Attachments -> Mime Types
$lang['Manage_mime_types'] = 'Stjórnun MIME tegunda';
$lang['Manage_mime_types_explain'] = 'Hér er hægt að stjórna MIME gerðum. Ef þú vilt heimila/banna ákveðnar gerðir af MIME, notaðu þá MIME hóp stjórnun.';
$lang['Explanation'] = 'Skýring';
$lang['Invalid_mimetype'] = 'Bönnuð MIME gerð';
$lang['Mimetype_exist'] = 'MIME gerð %s þegar til'; // replace %s with the mimetype

// Attachments -> Mime Groups
$lang['Manage_mime_groups'] = 'Stjórnun MIME hópa';
$lang['Manage_mime_groups_explain'] = 'Hér getur þú bætt við, eytt eða breytt MIME hópum, þú getur bannað MIME hópa og gert mynda hóp.';
$lang['Image_group'] = 'Mynda hópur';
$lang['Allowed'] = 'Heimill';
$lang['Mimegroup_exist'] = 'MIME hópur %s er þegar til'; // replace %s with the mimetype
$lang['Special_category'] = 'Sérstakur flokkur';
$lang['Category_images'] = 'myndir';
$lang['Category_wma_files'] = 'wma skrár';
$lang['Category_swf_files'] = 'flash skrár';

$lang['Download_mode'] = 'Niðurhals hamur';
$lang['Upload_image'] = 'Hlaða upp mynd';
$lang['Max_groups_filesize'] = 'Mesta skráarstærð';

$lang['Collapse'] = 'Fella saman';
$lang['Decollapse'] = 'Taka sundur';

// Attachments -> Shadow Attachments
$lang['Shadow_attachments'] = 'Skugga viðhengi';		// used in modules-list
$lang['Shadow_attachments_title'] = 'Skugga viðhengi';
$lang['Shadow_attachments_explain'] = 'Hér getur þú eytt tenglum úr innleggjum sem vísa að viðhengjum þegar viðhengin vantar á diskinn og einnig getur þú eytt viðhengjum þegar engin innlegg vísa á þau. Þú getur hlaðið niður skrám eða skoðað skrá sem þú klikkar á; ef enginn tengill er til að klikka á þá er skráin ekki til.';
$lang['Shadow_attachments_file_explain'] = 'Eyða öllum viðhengjum sem eru í skráarsafni þínu og eru ekki með tengil í neinu innleggi.';
$lang['Shadow_attachments_row_explain'] = 'Eyða tenglum úr innleggjum sem vísa í viðhengi sem ekki eru til í skráarsafninu.';

// Attachments -> Control Panel
$lang['Control_Panel'] = 'Stjórnborð';
$lang['Control_panel_title'] = 'Stjórnborð fyrir viðhengi';
$lang['Control_panel_explain'] = 'Hér getur þú skoðað og stjórnað viðhengjum háð Notendum, Viðhengjum, hversu oft skoðað o.fl....';
$lang['File_comment_cp'] = 'Athugasemd með skrá';

// Sort Types
$lang['Sort_Attachments'] = 'Viðhengi';
$lang['Sort_Size'] = 'Stærð';
$lang['Sort_Filename'] = 'Skráarnafn';
$lang['Sort_Comment'] = 'Athugasemd';
$lang['Sort_Mimegroup'] = 'MIME hópur';
$lang['Sort_Mimetype'] = 'MIME gerð';
$lang['Sort_Downloads'] = 'Hlaða niður';
$lang['Sort_Posttime'] = 'Innlegg dags/kl.';
$lang['Sort_Posts'] = 'Innlegg';

// View Types
$lang['View_Statistic'] = 'Tölfræði';
$lang['View_Search'] = 'Leita';
$lang['View_Username'] = 'Notendanafn';
$lang['View_Attachments'] = 'Viðhengi';

// Control Panel -> Statistics
$lang['Number_of_attachments'] = 'Fjöldi viðhengja';
$lang['Total_filesize'] = 'Samtals skráarstærð';
$lang['Number_posts_attach'] = 'Fjöldi innleggja með viðhengi';
$lang['Number_topics_attach'] = 'Fjöldi spjallþráða með viðhengi';
$lang['Number_users_attach'] = 'Innlegg með viðhengi hvers notanda ';
$lang['Number_pms_attach'] = 'Samtals fjöldi viðhengja í einkapósti';

// Control Panel -> Search
$lang['Search_wildcard_explain'] = 'Notaðu * sem einhvern staf';
$lang['Size_smaller_than'] = 'Viðhengi minna en (bytes)';
$lang['Size_greater_than'] = 'Viðhengi stærra en (bytes)';
$lang['Count_smaller_than'] = 'Fjöldi niðurhala minni en';
$lang['Count_greater_than'] = 'Fjöldi niðurhala meiri en';
$lang['More_days_old'] = 'Meira en þetta margra daga gömul';
$lang['No_attach_search_match'] = 'Engin viðhengi passa við leitarforsendur';

// Control Panel -> Attachments
$lang['Statistics_for_user'] = 'Tölfræði viðhengja fyrir %s'; // replace %s with username
$lang['Size_in_kb'] = 'Stærð (KB)';
$lang['Downloads'] = 'Niðurhöl';
$lang['Post_time'] = 'Innlegg dags/kl';
$lang['Posted_in_topic'] = 'Innlegg í spjallþræði';
$lang['Confirm_delete_attachments'] = 'Ertu viss um að þú viljir eyða völdum viðhengjum?';
$lang['Deleted_attachments'] = 'Völdum viðhengjum hefur verið eytt.';
$lang['Error_deleted_attachments'] = 'Get ekki eytt viðhengjum.';

?>