<?php
/***************************************************************************
 *
 *	language/lang_icelandic/lang_main_attach.php   [icelandic]
 *	------------------------------------------------------------------------
 *
 *	Created     Mon,  9 Sep 2002 00:06:29 +0200
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
// Attachment Mod Main Language Variables
//

// Viewforum
$lang['Rules_attach_can'] = 'Þú <b>getur</b> sett viðhengi inn á þessar umræður';
$lang['Rules_attach_cannot'] = 'Þú getur <b>ekki</b> sett viðhengi inn á þessar umræður';
$lang['Rules_download_can'] = 'Þú <b>getur</b> hlaðið niður skrám á þessum umræðum';
$lang['Rules_download_cannot'] = 'Þú getur <b>ekki</b> hlaðið niður skrám á þessum umræðum';

// Viewtopic
$lang['Mime_type_disallowed_post'] = 'mime gerð %s var gerð óvirk af umsjónarmanni umræðu borðsins og þess vegna er þetta viðhengi ekki sýnt'; // used in Posts, replace %s with mime type

// Posting/Replying (Not private messaging!)
$lang['Disallowed_extension'] = 'Endingin %s er ekki heimil'; // replace %s with extension (e.g. .php) 
$lang['Disallowed_Mime_Type'] = 'Ekki heimil gerð Mime: %s<p>Heimilar gerðir eru:<br />%s'; // mime type, allowed types 
$lang['Attachment_too_big'] = 'Þetta viðhengi er of stórt.<br />Mesta stærð er: %d %s'; // replace %d with maximum file size, %s with size var
$lang['Attachment_php_size_overrun'] = 'Þetta viðhengi er of stórt.<br />Mesta stærð uppgefin í PHP: %d MB'; // replace %d with ini_get('upload_max_filesize')
$lang['Attachment_php_size_na'] = 'Þetta viðhengi er of stórt.<br />Gat ekki fengið upp mestu uppgefna stærð frá PHP.'; 
$lang['Invalid_filename'] = '%s er ógilt skráarnafn'; // replace %s with given filename
$lang['General_upload_error'] = 'Villa við upphal: Gat ekki halað upp viðhengi við %s'; // replace %s with local path 
   
$lang['Add_attachment'] = 'Bæta við viðhengi';
$lang['Add_attachment_title'] = 'Bæta við viðhengi';
$lang['Add_attachment_explain'] = 'Ef þú vilt ekki bæta við viðhengi þá við innlegg þitt þá áttu að hafa þessa reiti tóma';
$lang['File_name'] = 'Skráarnafn';
$lang['File_comment'] = 'Athugasemd með skrá';
$lang['Delete_attachments'] = 'Eyða viðhengjum';
$lang['Delete_attachment'] = 'Eyða viðhengi';
$lang['Posted_attachments'] = 'Innsend viðhengi';
$lang['Update_comment'] = 'Uppfæra athugasemd';

// Auth related entries
$lang['Sorry_auth_attach'] = 'Því miður þá getur bara %s sent inn viðhengi í þessum umræðum';

// Download Count functionality
$lang['Download_number'] = 'Skrá hlaðið niður eða skoðuð %d sinnum/sinni'; // replace %d with count

// Errors
$lang['Sorry_auth_view_attach'] = 'Því miður þá hefur þú ekki heimild til að skoða eða hlaða niður þetta viðhengi';
$lang['No_file_comment_available'] = 'Engin athugasemd';
$lang['Too_many_attachments'] = 'Ekki hægt að bæta við viðhengi, þar sem það eru komin %d viðhengi við þetta innlegg'; // replace %d with maximum number of attachments
$lang['Attach_quota_reached'] = 'Því miður þá er mestu skráarstærð náð fyrir öll viðhengi. Hafðu samband umsjónarmann umræðuborðsins ef þú vilt meiri upplýsingar';

$lang['Error_no_attachment'] = 'Valið viðhengi er ekki lengur til';
$lang['No_attachment_selected'] = 'Þú hefur ekki valið neitt viðhengi til að hlaða niður eða skoða';
$lang['Attachment_feature_disabled'] = 'Viðhengja möguleiki er óvirkur';

$lang['Directory_does_not_exist'] = 'Skráarmappan \'%s\' er ekki til eða fannst ekki.'; // replace %s with directory
$lang['Directory_is_not_a_dir'] = 'Athugaðu hvort \'%s\' er skráarmappa.'; // replace %s with directory
$lang['Directory_not_writeable'] = 'Mappa \'%s\' er ekki skrifanleg. Þú verður að búa til upphleðslu slóð og gera chmode 777 á henni (eða breyta eiganda á httpd-vefþjóninum) til að hlaða upp skrám.<br />Ef þú hefur bara venjulegan ftp-aðgang þá getur þú breytt \'Attribute\' á möppunni í rwxrwxrwx.'; // replace %s with directory

$lang['Ftp_error_connect'] = 'Gat ekki tengt við  FTP  vefþjón: \'%s\'. Athugaðu stillingarnar á FTP.';
$lang['Ftp_error_login'] = 'Gat ekki skráð mig inn á FTP vefþjóninn. Notendanafn \'%s\' eða aðgangsorðið er rangt. Athugaðu stillingarnar á FTP.';
$lang['Ftp_error_path'] = 'Gat ekki tengt við ftp möppu: \'%s\'. Athugaður stillingar á FTP.';
$lang['Ftp_error_upload'] = 'Gat ekki hlaðið upp skrám í ftp möppu: \'%s\'. Athugaður stillingar á FTP.';
$lang['Ftp_error_delete'] = 'Gat ekki eytt skrám í ftp möppu: \'%s\'. Athugaður stillingar á FTP.<br />Önnur ástæð getur verið fyrir þessari villu eða það að þetta viðhengi er ekki til, athugðu þetta fyrst í skugga viðhengjum.';

$lang['Attach_quota_sender_pm_reached'] = 'Því miður þá hefur mestu skráar stærð verið náð fyrir öll viðhengi í einkapóst hólfi þínu. Eyddu nokkrum af sendum/mótteknum viðhengjum.';
$lang['Attach_quota_receiver_pm_reached'] = 'Því miður þá hefur mestu skráar stærð verið náð fyrir öll viðhengi í einkapóst hólfi \'%s\' verið náð. Hafðu samband við viðkomandi eða prófaðu aftur seinna þegar hann/hún hefur eytt einhverju af viðhengjum sínum.';

// Size related Variables
$lang['Bytes'] = 'Bytes';
$lang['KB'] = 'KB';
$lang['MB'] = 'MB';

$lang['Attach_search_query'] = 'Leita að viðhengjum';

// Private Messaging
$lang['Pm_delete_attachments'] = 'Eyða viðhengjum';

?>