<?php
/**
*
* pafiledb [English]
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pafiledb_main.php,v 1.3 2008/10/26 08:50:23 orynider Exp $
* @copyright (c) 2002-2006 [Jon Ohlsson, Mohd Basri, wGEric, PHP Arena, pafileDB, CRLin] MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine


// Translated by Romanian phpBB online community
// Web: http://www.phpbb.ro
// Autor: Bogdan Toma
// Email: bogdan@phpbb.ro
// Date: January 07, 2004
// MOD Web Address: http://mohd.vraag-en-antwoord.nl/main

// Traducere efectuata de Romanian phpBB online community
// Web: http://www.phpbb.ro
// Autor: Bogdan Toma
// Email: bogdan@phpbb.ro
// Data: 07 ianuarie 2004
// Adresa Web MOD: http://mohd.vraag-en-antwoord.nl/main

$lang = array_merge($lang, array(
	'Category'		=> 'Categorie',
	'Error_no_download'		=> 'Fişierul selectat nu mai există',
	'Options'		=> 'Opţiuni',
	'Click_here'		=> 'Apăsaţi aici',
	'never'		=> 'Nu s-a efectuat încă',
	'pafiledb_disable'		=> 'Baza de download-uri este dezactivată',
	'jump'		=> 'Selectaţi o categorie',
	'viewall_disabled'		=> 'Această funcţionalitate este dezactivată de către administrator.',
	'New_file'		=> 'Fişier nou',
	'No_new_file'		=> 'Nici un fişier nou',
	'None'		=> 'Nu are',
	'No_file'		=> 'Nici un fişier',
	'View_latest_file'		=> 'Vizualizarea ultimului fişier',

	'Click_return'		=> 'Apasaţi %saici%s pentru a reveni la pagina anterioară',

	'Files'		=> 'Fişiere',
	'Viewall'		=> 'Toate fişierele',
	'Vainfo'		=> 'Toate fişierele din baza de date',
	'Quick_nav'		=> 'Navigare Rapidă',
	'Quick_jump'		=> 'Selectaţi o categorie',
	'Quick_go'		=> 'Dute',
	'Sub_category'		=> 'Subcategorie',
	'Last_file'		=> 'Ultimul fişier',

	'Sort'		=> 'Sortare',
	'Name'		=> 'Nume',
	'Update_time'		=> 'Ultima actualizare',

	'No_files'		=> 'Nu a fost gasit nici un fişier',
	'No_files_cat'		=> 'Nu există fişier în această categorie.',
	'Cat_not_exist'		=> 'Categoria pe care aţi selectat-o nu există.',
	'File_not_exist'		=> 'Fişierul pe care l-aţi selectat nu există.',
	'License_not_exist'		=> 'Licenţa pe care aţi selectat-o nu există.',

	'File'		=> 'Fişier',
	'Desc'		=> 'Descriere',
	'Creator'		=> 'Creator',
	'Submited'		=> 'Trimis de',
	'Version'		=> 'Versiune',
	'Scrsht'		=> 'Exemplu',
	'Docs'		=> 'Site web',
	'Lastdl'		=> 'Ultima descărcare',
	'Never'		=> 'Niciodată',
	'Votes'		=> ' Voturi',
	'Date'		=> 'Data',
	'Update_time'		=> 'Ultima actualizare',
	'DlRating'		=> 'Votare',
	'Dls'		=> 'Număr descărcări',
	'Downloadfile'		=> 'Descarcă fişier',
	'File_size'		=> 'Dimensiune fişier',
	'Not_available'		=> 'Nu este disponibil !',
	'Bytes'		=> 'Bytes',
	'KB'		=> 'Kilo Byte',
	'MB'		=> 'Mega Byte',
	'Mirrors'		=> 'Mirror-uri',
	'Mirrors_explain'		=> 'Aici puteţi adăuga sau modifica mirror-urile pentru acest fişier; asiguraţi-vă ca aţi verificat toate informaţiile deoarece fişierul va fi trimis către baza de date',
	'Click_here_mirrors'		=> 'Apăsaţi aici pentru a adăuga mirror-uri',
	'Mirror_location'		=> 'Locaţie mirror',
	'Add_new_mirror'		=> 'Adaugă un nou mirror',
	'Save_as'		=> 'Salvează Ca...',

	'File_manage_title'		=> 'File Management',

	'Afile'		=> 'File: Add',
	'Efile'		=> 'File: Edit',
	'Dfile'		=> 'File: Delete',
	'Afiletitle'		=> 'Add File',
	'Efiletitle'		=> 'Edit File',
	'Dfiletitle'		=> 'Delete File',
	'Fileexplain'		=> 'You can use the file management section to add, edit, and delete files.',
	'Upload'		=> 'Upload File',
	'Uploadinfo'		=> 'Upload this file',
	'Uploaderror'		=> 'This file already exists. Please rename the file and try again.',
	'Uploaddone'		=> 'This file has been successfully uploaded. The URL to the file is',
	'Uploaddone2'		=> 'Click Here to place this URL in the Download URL field.',
	'Upload_do_done'		=> 'Uploaded Sucessfully',
	'Upload_do_not'		=> 'Not Uploaded',
	'Upload_do_exist'		=> 'File Exist',
	'Filename'		=> 'File Name',
	'Filenameinfo'		=> 'This is the name of the file you are adding, such as \'My Picture.\'',
	'Filesd'		=> 'Short Description',
	'Filesdinfo'		=> 'This is a short description of the file. This will go on the page that lists all the files in a category, so this description should be short',
	'Fileld'		=> 'Long Description',
	'Fileldinfo'		=> 'This is a longer description of the file. This will go on the file\'s information page so this description can be longer',
	'Filecreator'		=> 'Creator/Author',
	'Filecreatorinfo'		=> 'This is the name of whoever created the file.',
	'Fileversion'		=> 'File Version',
	'Fileversioninfo'		=> 'This is the version of the file, such as 3.0 or 1.3 Beta',
	'Filess'		=> 'Screenshot URL',
	'Filessinfo'		=> 'This is a URL to a screenshot of the file. For example, if you are adding a Winamp skin, this would be a URL to a screenshot of Winamp with this skin. You can manually enter a URL or you can leave it blank and upload a screen shot using "Browse" above.',
	'Filess_upload'		=> 'Upload Screenshot',
	'Filessinfo_upload'		=> 'You can upload a screenshot by clicking on "Browse"',
	'Filess_link'		=> 'Screenshot as a Link',
	'Filess_link_info'		=> 'If you want to show the screenshot as a link, choose "yes".',
	'Filedocs'		=> 'Documentation/Manual URL',
	'Filedocsinfo'		=> 'This is a URL to the documentation or a manual for the file',
	'Fileurl'		=> 'File URL',
	'Fileurlinfo'		=> 'This is a URL to the file that will be downloaded. You can type it in manually or you can click on "Browse" above and upload a file.',
	'File_upload'		=> 'File Upload',
	'Fileinfo_upload'		=> 'You can upload a file by clicking on "Browse"',
	'Uploaded_file'		=> 'Uploaded file',
	'Filepi'		=> 'Post Icon',
	'Filepiinfo'		=> 'You can choose a post icon for the file. The post icon will be shown next to the file in the list of files.',
	'Filecat'		=> 'Category',
	'Filecatinfo'		=> 'This is the category the file belongs in.',
	'Filelicense'		=> 'License',
	'Filelicenseinfo'		=> 'This is the license agreement the user must agree to before downloading the file.',
	'Filepin'		=> 'Pin File',
	'Filepininfo'		=> 'Choose if you want the file pinned or not. Pinned files will always be shown at the top of the file list.',
	'Fileadded'		=> 'The new file has been successfully added',
	'Filedeleted'		=> 'The file has been successfully deleted',
	'Fileedited'		=> 'The file you selected has been successfully edited',
	'Fderror'		=> 'You didn\'t select any files to delete',
	'Filesdeleted'		=> 'The files you selected have been successfully deleted',
	'Filetoobig'		=> 'That file is too big!',
	'Approved'		=> 'Approved',
	'Not_approved'		=> '(Not Approved)',
	'Approved_info'		=> 'Use this option to make the file available for users, and also to approve a file that has been uploaded by the users.',

	'Filedls'		=> 'Download Total',
	'Addtional_field'		=> 'Additional Field',
	'File_not_found'		=> 'The file you specified cannot be found',
	'SS_not_found'		=> 'The screenshot you specified cannot be found',

	'MCP_title'		=> 'Moderator Control Panel',
	'MCP_title_explain'		=> 'Here moderators can approve and manage files',

	'View'		=> 'View',

	'Approve_selected'		=> 'Approve Selected',
	'Unapprove_selected'		=> 'Unapprove Selected',
	'Delete_selected'		=> 'Delete Selected',
	'No_item'		=> 'There is no files',

	'All_items'		=> 'All Files',
	'Approved_items'		=> 'Approved Files',
	'Unapproved_items'		=> 'Unapproved Files',
	'Broken_items'		=> 'Broken Files',
	'Item_cat'		=> 'File in Category',
	'Approve'		=> 'Approve',
	'Unapprove'		=> 'Unapprove',

	'Sorry_auth_delete'		=> 'Sorry, but you cannot delete files in this category.',
	'Sorry_auth_mcp'		=> 'Sorry, but you cannot moderate this category.',
	'Sorry_auth_approve'		=> 'Sorry, but you cannot approve files in this category.',

	'User_upload'		=> 'Upload',

	'License'		=> 'Licenţă',
	'Licensewarn'		=> 'Trebuie să fiţi de acord cu condiţiile licenţei pentru a descărca fisierul',
	'Iagree'		=> 'Sunt de acord',
	'Dontagree'		=> 'Nu sunt de acord',

	'Search'		=> 'Căutare',
	'Search_for'		=> 'Caută pentru',
	'Results'		=> 'Rezultate pentru',
	'No_matches'		=> 'Nu au fost găsite rezultate pentru',
	'Matches'		=> 'rezultate au fost găsite pentru',
	'All'		=> 'Toate categoriile',
	'Choose_cat'		=> 'Alegeţi categoria:',
	'Include_comments'		=> 'Caută şi în comentarii',
	'Submiter'		=> 'Trimis de',
	
	'Search_query'		=> 'Search Query',
	'Search_options'		=> 'Search Options',	
	
	'Search_keywords'		=> 'Search for Keywords',
	'Search_keywords_explain'		=> 'You can use <u>AND</u> to define words which must be in the results, <u>OR</u> to define words which may be in the result and <u>NOT</u> to define words which should not be in the result. Use * as a wildcard for partial matches',
	'Search_author'		=> 'Search for Author',
	'Search_author_explain'		=> 'Use * as a wildcard for partial matches',

	'Search_for_any'		=> 'Search for any terms or use query as entered',
	'Search_for_all'		=> 'Search for all terms',	

	'Statistics'		=> 'Statistici',
	'Stats_text'		=> "Sunt {total_files} fişiere în {total_categories} categorii<br> S-au efectuat în total {total_downloads} descărcări<br><br> Cel mai nou fişier este <a href={u_newest_file}>{newest_file}</a><br> Cel mai vechi fişier este <a href={u_oldest_file}>{oldest_file}</a><br><br> Evaluarea medie este {average}/10<br> Cel mai popular fişier bazat pe aprecierile făcute este <a href={u_popular}>{popular}</a> cu o evaluare de {most}/10<br> Cel mai puţin popular fişier bazat pe aprecierile făcute este <a href={u_lpopular}>{lpopular}</a> cu o evaluare de {least}/10<br><br> Numărul mediu a descărcărilor pe fiecare fişier este {avg_dls}<br> Cel mai popular fişier bazat pe numărul de descărcări este <a href={u_most_dl}>{most_dl}</a> cu {most_no} descărcări<br> Cel mai puţin popular fişier bazat pe numărul de descărcări este <a href={u_least_dl}>{least_dl}</a> cu {least_no} descărcări<br>",
	'Select_chart_type'		=> 'Selectaţi tipul de grafic',
	'Bars'		=> 'Bare',
	'Lines'		=> 'Linii',
	'Area'		=> 'Arie',
	'Linepoints'		=> 'Linii cu puncte',
	'Points'		=> 'Puncte',
	'Chart_header'		=> 'Fişiere statistice - Fişiere adăugate în fiecare lună la baza de date',
	'Chart_legend'		=> 'Fişiere',
	'X_label'		=> 'Luni',
	'Y_label'		=> 'Număr de fişiere',

	'Rate'		=> 'Evaluare fişier',
	'Rerror'		=> 'Aţi evaluat deja acest fişier.',
	'Rateinfo'		=> 'Doriţi să evaluaţi fişierul <i>{filename}</i>.<br>Selectaţi o notă de mai jos. 1 este cel mai slab, 10 este cel mai bun.',
	'Rconf'		=> 'Aţi ales să daţi fişierului <i>{filename}</i> nota {rate}.<br>Astfel nota generală a acestui fişier a devenit {newrating}.',
	'R1'		=> '1',
	'R2'		=> '2',
	'R3'		=> '3',
	'R4'		=> '4',
	'R5'		=> '5',
	'R6'		=> '6',
	'R7'		=> '7',
	'R8'		=> '8',
	'R9'		=> '9',
	'R10'		=> '10',
	'Not_rated'		=> 'Fără notă',

	'Emailfile'		=> 'Trimite un email cu acest fişier la un prieten',
	'Emailinfo'		=> 'Dacă doriţi ca un prieten să ştie mai multe despre acest fişier, trebuie să completaţi şi să trimiteţi un e-mail ce conţine toate informaţiile despre fişier!<br>Câmpurile marcate cu caracterul * sunt obligatorii',
	'Yname'		=> 'Numele dumneavoastră',
	'Yemail'		=> 'Adresa de E-mail',
	'Fname'		=> 'Numele prietenului',
	'Femail'		=> 'Adresa de E-mail a prietenului',
	'Esub'		=> 'Subiectul mesajului',
	'Etext'		=> 'Textul mesajului',
	'Defaultmail'		=> 'Cred că ar putea să te intereseze descărcarea fişierului localizat la adresa',
	'Semail'		=> 'Trimite E-mail',
	'Econf'		=> 'Mesajul a fost trimis cu succes.',

	'Comments'		=> 'Comentarii',
	'Comments_title'		=> 'Titlul comentariului',
	'Comment_subject'		=> 'Subiectul comentariului',
	'Comment'		=> 'Comentariu',
	'Comment_explain'		=> 'Folosiţi căsuţa de text de mai jos pentru a vă face cunoscută opinia vis a vis de acest fişier!',
	'Comment_add'		=> 'Adaugă comentariu',
	'Comment_delete'		=> 'Şterge',
	'Comment_posted'		=> 'Comentariul dumneavoastră  a fost introdus cu succes',
	'Comment_deleted'		=> 'Comentariul pe care l-aţi selectat a fost şters cu succes',
	'Comment_desc'		=> 'Titlu',
	'No_comments'		=> 'Nici un comentariu nu a fost scris.',
	'Links_are_ON'		=> 'Link-urile sunt <u>Activate</u>',
	'Links_are_OFF'		=> 'Link-urile sunt <u>Dezactivate</u>',
	'Images_are_ON'		=> 'Imaginile sunt <u>Activate</u>',
	'Images_are_OFF'		=> 'Imaginile sunt <u>Dezactivate</u>',
	'Check_message_length'		=> 'Verifică lungimea mesajului',
	'Msg_length_1'		=> 'Mesajul dumneavoastră are o lungime de ',
	'Msg_length_2'		=> ' caractere.',
	'Msg_length_3'		=> 'Limita mesajului este de ',
	'Msg_length_4'		=> ' caractere.',
	'Msg_length_5'		=> 'Mai puteţi scrie ',
	'Msg_length_6'		=> ' caractere.',


	'Directly_linked'		=> 'Nu puteţi descărca acest fişier direct de pe alt site!',

	'Sorry_auth_view'		=> 'Doar %s poate să vadă fişierele şi subcategoriile din această categorie.',
	'Sorry_auth_file_view'		=> 'Doar %s poate să vadă fişierele din această categorie.',
	'Sorry_auth_upload'		=> 'Doar %s poate să publice fişiere în această categorie.',
	'Sorry_auth_download'		=> 'Doar %s poate descărca fişiere în această categorie.',
	'Sorry_auth_rate'		=> 'Doar %s poate evalua fişiere în această categorie.',
	'Sorry_auth_view_comments'		=> 'Doar %s poate sa vadă comentariile din această categorie.',
	'Sorry_auth_post_comments'		=> 'Doar %s poate să scrie comentarii în această categorie.',
	'Sorry_auth_edit_comments'		=> 'Doar %s poate să modifice comentariile din această categorie.',
	'Sorry_auth_delete_comments'		=> 'Doar %s poate să şteargă comentariile din această categorie.',

	'Deletefile'		=> 'Şterge Fişier',
	'Editfile'		=> 'Editează fişier',
	'pa_MCP'		=> '[ModeratorCP]',
	'Click_return_not_validated'		=> 'Click %sAici%s pentru a reveni la pagina anterioară',
	'Fileadded_not_validated'		=> 'Fişierul nou a fost adăugat cu success, dar un moderator sau admin trebuie să evelueze fişierul înainte de a fi aprobat.',

	'Quickdl_back'		=> '&laquo; Înapoi',

	'Quickdl'		=> 'Categorie Pa implicită',
	'Quickdl_explain'		=> 'This is the default pafiledb category to display, if no mapping is activated',

	'Pa_updated_return_settings'		=> "Pa quickdl configuration updated successfully.<br /><br />Click %shere%s to return to main page.",
	'Pa_update_error'		=> "Couldn't update Pa quickdl configuration.<br /><br />This mod is designed for MySQL so please contact the author if you have troubles. If you can offer a translation of the SQL into other database formats, please send them to:<br />",

	'Pa_settings'		=> "Pa mapping settings",
	'Pa_settings_short_explain'		=> "Settings for mapping pa cats and dynamic blocks.",
	'Pa_settings_explain'		=> "Here you can edit the configuration for the pa module. This panel lets you associate pa cats and dynamic blocks for the quickdl block.",

	'PA_title'		=> 'Download database',
	'PA_prefix'		=> '[ Fişier ]',

	'PA_goto_file'		=> '<br />Vezi Fişier',
	'PA_notify_subject_new'		=> '<br />Fişier nou!',
	'PA_notify_subject_edited'		=> '<br />Fişier Editat!',
	'PA_notify_subject_approved'		=> '<br />Fişier Apobat!',
	'PA_notify_subject_unapproved'		=> '<br />Fişier Ne-Aprobat!',
	'PA_notify_subject_deleted'		=> '<br />Fişier Şters!',
	'PA_notify_subject_unapproved'		=> '<br />Fişier Ne-Aprobat!',
	'PA_notify_body'		=> '<br />Un fişier a fost uploadat sau actualizat:',
	'PA_no_ratings'		=> '<br />Dezactivat în aceată categorie',

	'PA_notify_new_body'		=> '<br />Un fişier nou a fost urcat în Download Manager.',
	'PA_notify_edited_body'		=> '<br />Un fişier a fost editat în Download Manager.',
	'PA_notify_approved_body'		=> '<br />Un fişier a fost aprobat în Download Manager.',
	'PA_notify_unapproved_body'		=> '<br />Un fişier a fost dez-aprobat în Download Manager.',
	'PA_notify_deleted_body'		=> '<br />Un fişier a fost şters din Download Manager.',
	'Edited_Article_info'		=> '<br />Fişierul a fost actualizat de ',

	'PA_goto'		=> '>>Vezi fişier',


	'PA_Rules_upload_can'		=> 'You <b>can</b> upload new files in this category',
	'PA_Rules_upload_cannot'		=> 'You <b>cannot</b> upload new files in this category',
	'PA_Rules_download_can'		=> 'You <b>can</b> download files in this category',
	'PA_Rules_download_cannot'		=> 'You <b>cannot</b> download files in this category',
	'PA_Rules_post_comment_can'		=> 'You <b>can</b> comment files in this category',
	'PA_Rules_post_comment_cannot'		=> 'You <b>cannot</b> comment files in this category',
	'PA_Rules_view_comment_can'		=> 'You <b>can</b> view comments in this category',
	'PA_Rules_view_comment_cannot'		=> 'You <b>cannot</b> view comments in this category',
	'PA_Rules_view_file_can'		=> 'You <b>can</b> see files in this category',
	'PA_Rules_view_file_cannot'		=> 'You <b>cannot</b> see files in this category',
	'PA_Rules_edit_file_can'		=> 'You <b>can</b> edit your files in this category',
	'PA_Rules_edit_file_cannot'		=> 'You <b>cannot</b> edit your files in this category',
	'PA_Rules_delete_file_can'		=> 'You <b>can</b> delete your files in this category',
	'PA_Rules_delete_file_cannot'		=> 'You <b>cannot</b> delete your files in this category',
	'PA_Rules_rate_can'		=> 'You <b>can</b> rate files in this category',
	'PA_Rules_rate_cannot'		=> 'You <b>cannot</b> rate files in this category',
	'PA_Rules_moderate'		=> 'You <b>can</b> %smoderate this category%s',
	'PA_Rules_moderate_can'		=> 'You <b>can</b> moderate this category',

	'Toplist'		=> 'Top',
	'Select_list'		=> 'Selectaţi tipul listei de consultat',
	'Latest_downloads'		=> 'Cele mai noi fişiere',
	'Most_downloads'		=> 'Cele mai populare fişiere',
	'Rated_downloads'		=> 'Cele mai votate fişiere',
	'Total_new_files'		=> 'Total download-uri noi',
	'Show'		=> 'Arată',
	'One_week'		=> 'O săptămână',
	'Two_week'		=> 'Două săptămâni',
	'30_days'		=> '30 zile',
	'New_Files'		=> 'Total fişiere noi în ultimele %d zile',
	'Last_week'		=>'Ultima săptămână',
	'Last_30_days'		=> 'Ultimele 30 de zile',
	'Show_top'		=> 'Arată top',
	'Or_top'		=> 'sau Top',
	'Popular_num'		=> 'Top %d din %d fişiere din baza de date',
	'Popular_per'		=> 'Top %d %% din toate cele %d fişiere din baza de date',
	'General_Info'		=> 'Informaţii generale',
	'Downloads_stats'		=> 'Statisticile de download ale utilizatorului',
	'Rating_stats'		=> 'Statisticile de votare ale utilizatorului',
	'Os'		=> 'Sisteme de operare',
	'Browsers'		=> 'Browsere',

	'Recent_Public_Files'		=> 'Ultimile dl-uri',
	'Random_Public_Files'		=> 'Random dl-uri',
	'Toprated_Public_Files'		=> 'Topvotate dl-uri',
	'Most_Public_Files'		=> 'Cele mai downloadate',
	'File_Title'		=> 'Titlu',
	'File_Desc'		=> 'Descripţie',
	'Rating'		=> 'Votare',
	'Dls'		=> 'Downloadate',
	
	'Information'		=> 'Information',
	'Critical_Information'		=> 'Critical Information',

	'General_Error'		=> 'General Error',
	'Critical_Error'		=> 'Critical Error',
	'An_error_occured'		=> 'An Error Occurred',
	'A_critical_error'		=> 'A Critical Error Occurred',	

	'Quickdl_back'		=> 'Înapoi',

	'Select_sort_method'		=> 'Select sort method',
	'Sort'		=> 'Sort',
	'Sort_Top_Ten'		=> 'Top Ten Posters',
	'Sort_Joined'		=> 'Joined Date',
	'Sort_Username'		=> 'Username',
	'Sort_Location'		=> 'Location',
	'Sort_Posts'		=> 'Total posts',
	'Sort_Email'		=> 'Email',
	'Sort_Website'		=> 'Website',
	'Sort_Ascending'		=> 'Ascending',
	'Sort_Descending'		=> 'Descending',
	'Order'		=> 'Order',	
));

?>
