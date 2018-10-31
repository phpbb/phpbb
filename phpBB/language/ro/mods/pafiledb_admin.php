<?php
/**
*
* pafiledb [Română]
*
* @package MX-Publisher Module - mx_pafiledb
* @version $Id: pafiledb_admin.php,v 1.2 2008/10/26 08:50:23 orynider Exp $
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

$lang = array_merge($lang, array(
	'ACP_PAFILEDB_MANAGEMENT'	=> 'PafileDB Admin',
	'ACP_PA_SETTINGS'			=> 'General Settings',
	'ACP_PA_CAT'				=> 'Category Management',
	'ACP_PA_CAT_AUTH'			=> 'File Management',
	'ACP_PA_UG_AUTH'			=> 'Permissions',
	'ACP_PA_LICENCE'			=> 'License',
	'ACP_PA_CUSTOM'				=> 'Custom fields',
	'ACP_PA_FCHECKER'			=> 'File checker',
	
	'Create_category'			=> 'Create category',
	
	'ParType_pa_mapping'		=> 'pafileDB category mapping',
	'ParType_pa_mapping_info'		=> '',

	'ParType_pa_quick_cat'		=> 'pafileDB default category',
	'ParType_pa_quick_cat_info'		=> '',

	'pa_mapping'		=> 'pafileDB category mapping',
	'pa_mapping_explain'		=> 'pafileDB categories and portal dynamic blocks mapping',

	'pa_quick_cat'		=> 'pafileDB default category',
	'pa_quick_cat_explain'		=> 'This category is used if no matching mapping is found',

	'Panel_config_title'		=> 'Download Configuration',
	'Panel_config_explain'		=> 'The form below will allow you to customize all the general download options.',

	'General_title'		=> 'General',

	'Module_name'		=> 'Database Name',
	'Module_name_explain'		=> 'This is the name of the database, such as \'Download Index\'',

	'Enable_module'		=> 'Enable this module',
	'Enable_module_explain'		=> 'This will make the download section unavailable to users. This is a good option to use when making modifications to your database. Only Admins will be able to view the database.',

	'Wysiwyg_path'		=> 'Path to WYSIWYG software',
	'Wysiwyg_path_explain'		=> 'This is the path (from MX-Publisher/phpBB root) to the WYSIWYG software folder, eg \'modules/mx_shared/\' if you have uploaded, for example, TinyMCE in modules/mx_shared/tinymce.',

	'Upload_directory'		=> 'Upload Directory',
	'Upload_directory_explain'		=> 'Enter the relative path from your root installation (where phpBB or MX-Publisher is located) to the files upload directory. If you stick to the file structure provided in the shipped package, enter \'pafiledb/uploads/\'.',

	'Screenshots_directory'		=> 'Screenshots Directory',
	'Screenshots_directory_explain'		=> 'Enter the relative path from your root installation (where phpBB or MX-Publisher is located) to the Screenshots upload directory. If you stick to the file structure provided in the shipped package, enter \'pafiledb/images/screenshots/\'.',

	'File_title'		=> 'File',

	'Hotlink_prevent'		=> 'Hotlink Prevention',
	'Hotlinl_prevent_info'		=> 'Set this to yes if you don\'t want to allow hotlinks to the files',

	'Hotlink_allowed'		=> 'Allowed domains for hotlink',
	'Hotlink_allowed_info'		=> 'Allowed domains for hotlink (separated by a comma), for example, www.phpbb.com, www.forumimages.com',

	'Php_template'		=> 'PHP in template',
	'Php_template_info'		=> 'This will allow you to use php directly in the template files',

	'Max_filesize'		=> 'Maximum Filesize',
	'Max_filesize_explain'		=> 'Maximum filesize for Files. A value of 0 means \'unlimited\'. This Setting is restricted by your Server Configuration. For example, if your php Configuration only allows a maximum of 2 MB uploads, this cannot be overwritten by the Mod.',

	'Forbidden_extensions'		=> 'Forbidden Extensions',
	'Forbidden_extensions_explain'		=> 'Here you can add or delete the forbidden extensions. Seprate each extenstion with comma.',

	'Bytes'		=> 'Bytes',
	'KB'		=> 'KB',
	'MB'		=> 'MB',


	'Appearance_title'		=> 'Appearance',

	'File_pagination'		=> 'File pagination',
	'File_pagination_explain'		=> 'The number of files to show in a category before pagination.',

	'Sort_method'		=> 'Sorting method',
	'Sort_method_explain'		=> 'Define how files are sorted within its category.',

	'Sort_order'		=> 'ASC or DESC sorting',
	'Sort_order_explain'		=> '',

	'Topnum'		=> 'Top Number',
	'Topnuminfo'		=> 'This is how many files will be displayed on the Top X Downloaded files list',

	'Showva'		=> 'Show \'View All Files\'',
	'Showvainfo'		=> 'Choose whether or not you wish to have the \'View All Files\' category displayed with the other categories on the main page',

	'Use_simple_navigation'		=> 'Simple Category Navigation',
	'Use_simple_navigation_explain'		=> 'If you prefer, this will generate more simple categories and other navigation',

	'Cat_col'		=> 'How many column of categories are to be listed (only used for \'Simple Category Navigation\')',

	'Nfdays'		=> 'New File Days',
	'Nfdaysinfo'		=> 'How many days a new file is to be listed with a \'New File\' icon. If this is set to 5, then all files added within the past 5 days will have the \'New File\' icon',


	'Comments_title'		=> 'Comments',
	'Comments_title_explain'		=> 'Some comments settings are default settings, and can be overridden per category',

	'Use_comments'		=> 'Comments',
	'Use_comments_explain'		=> 'Enable comments for files, to be inserted in the forum',

	'Internal_comments'		=> 'Internal or phpBB Comments',
	'Internal_comments_explain'		=> 'Use internal comments, or phpBB comments',

	'Select_topic_id'		=> 'Select phpBB Comments Topic!',

	'Internal_comments_phpBB'		=> 'phpBB Comments',
	'Internal_comments_internal'		=> 'Internal Comments',

	'Forum_id'		=> 'phpBB Forum ID',
	'Forum_id_explain'		=> 'If phpBB comments are used, this is the forum where the comments will be kept',

	'Autogenerate_comments'		=> 'Autogenerate comments when fil are managed',
	'Autogenerate_comments_explain'		=> 'When editing/adding a file, a notifying reply is posted in the file topic.',

	'Del_topic'		=> 'Delete Topic',
	'Del_topic_explain'		=> 'When you delete a file, do you want its comments topic to be deleted also?',

	'Comments_pag'		=> 'Comments pagination',
	'Comments_pag_explain'		=> 'The number of comments to show for the file before pagination.',

	'Allow_Wysiwyg'		=> 'Use WYSIWYG editor',
	'Allow_Wysiwyg_explain'		=> 'If enabled, the standard BBCode/HTML/Smilies input dialog is replaced by a WYSIWYG editor.',

	'Allow_links'		=> 'Allow Links',
	'Allow_links_message'		=> 'Default \'No Links\' Message',
	'Allow_links_explain'		=> 'If links are not allowed this text will be displayed instead',

	'Allow_images'		=> 'Allow Images',
	'Allow_images_message'		=> 'Default \'No Images\' Message',
	'Allow_images_explain'		=> 'If images are not allowed this text will be displayed instead',

	'Max_subject_char'		=> 'Maximum Number of charcters in subject',
	'Max_subject_char_explain'		=> 'If to big, you get an error message (Limit the subject).',

	'Max_desc_char'		=> 'Maximum Number of charcters in description',
	'Max_desc_char_explain'		=> 'If to big, you get an error message (Limit the subject).',

	'Max_char'		=> 'Maximum Number of charcters in text',
	'Max_char_explain'		=> 'If to big, you get an error message (Limit the comment).',

	'Format_wordwrap'		=> 'Word wrapping',
	'Format_wordwrap_explain'		=> 'Text control filter',

	'Format_truncate_links'		=> 'Truncate Links',
	'Format_truncate_links_explain'		=> 'Links are shortened, eg t ex \'www.mxp-portal...\'',

	'Format_image_resize'		=> 'Image resize',
	'Format_image_resize_explain'		=> 'Resize images to this width (pixels)',

	'Ratings_title'		=> 'Ratings',
	'Ratings_title_explain'		=> 'Some ratings settings are default settings, and can be overridden per category',

	'Use_ratings'		=> 'Ratings',
	'Use_ratings_explain'		=> 'Enable ratings',

	'Votes_check_ip'		=> 'Validate ratings - IP',
	'Votes_check_ip_explain'		=> 'Only one vote per IP address is permitted.',

	'Votes_check_userid'		=> 'Validate ratings - User',
	'Votes_check_userid_explain'		=> 'Users may only vote once.',


	'Instructions_title'		=> 'User Instructions',

	'Pre_text_name'		=> 'File Submission Instructions',
	'Pre_text_explain'		=> 'Activate Submission Instructions displayed to users at the top of the submission forum.',

	'Pre_text_header'		=> 'File Submission Instructions Header',
	'Pre_text_body'		=> 'File Submission Instructions Body',

	'Show'		=> 'Show',
	'Hide'		=> 'Hide',


	'Notifications_title'		=> 'Notification',

	'Notify'		=> 'Notify admin by',
	'Notify_explain'		=> 'Choose which way to receive notices that new files have been uploaded',
	'PM'		=> 'PM',

	'Notify_group'		=> 'and groupmembers ',
	'Notify_group_explain'		=> 'Also send notification to members in this group',


	'Permission_settings'		=> 'Permission settings',

	'Auth_search'		=> 'Search Permission',
	'Auth_search_explain'		=> 'Allow search for specific type of users',

	'Auth_stats'		=> 'Stats Permission',
	'Auth_stats_explain'		=> 'Allow stats for specific type of users',

	'Auth_toplist'		=> 'Toplist Permission',
	'Auth_toplist_explain'		=> 'Allow toplist for specific type of users',

	'Auth_viewall'		=> 'Viewall Permission',
	'Auth_viewall_explain'		=> 'Allow viewall for specific type of users',

	'Settings'		=> 'Configuration',
	'Settings_changed'		=> 'Your settings have been successfully updated',


	'Panel_cat_title'		=> 'Category administration',
	'Panel_cat_explain'		=> 'You can use the Category Management section to add, edit, delete and reorder categories. In order to add files to your database, you must have at least one category created. You can select a link below to manage your categories.',

	'Use_default'		=> 'Use default setting',

	'Maintenance'		=> 'File Maintenance',
	'Acat'		=> 'Category: Add',
	'Ecat'		=> 'Category: Edit',
	'Dcat'		=> 'Category: Delete',
	'Rcat'		=> 'Category: Reorder',
	'Cat_Permissions'		=> 'Category Permissions',
	'User_Permissions'		=> 'User Permissions',
	'Group_Permissions'		=> 'Group Permissions',
	'User_Global_Permissions'		=> 'User Global Permissions',
	'Group_Global_Permissions'		=> 'Group Global Permissions',
	'Acattitle'		=> 'Add Category',
	'Ecattitle'		=> 'Edit Category',
	'Dcattitle'		=> 'Delete Category',
	'Rcattitle'		=> 'Reorder Categories',
	'Catexplain'		=> 'You can use the Category Management section to add, edit, delete and reorder categories. In order to add files to your database, you must have at least one category created. You can select a link below to manage your categories.',
	'Rcatexplain'		=> 'You can reorder categories to change the position they are displayed in on the main page. To reorder the categories, change the numbers to the order you want them shown in. 1 will be showed first, 2 will be shown second, etc. This does not affect sub-categories.',
	'Catadded'		=> 'The new category has been successfully added',
	'Catname'		=> 'Category Name',
	'Catnameinfo'		=> 'This will become the name of the category.',
	'Catdesc'		=> 'Category Description',
	'Catdescinfo'		=> 'This is a description of the files in the category',
	'Catparent'		=> 'Parent Category',
	'Catparentinfo'		=> 'If you want this category to be a sub-category, select the category you want it to be a sub-category of.',
	'Allow_file'		=> 'Allow Adding file',
	'Allow_file_info'		=> 'If you are not allowed to add files in this category it will be a higher level category.',
	'None'		=> 'None',
	'Catedited'		=> 'The category you selected has been successfully edited',
	'Delfiles'		=> 'What do you want to do with the files in this category?',
	'Do_cat'		=> 'What do you want to do with the sub category in this category?',
	'Move_to'		=> 'Move to',
	'Catsdeleted'		=> 'The categories you selected have been successfully deleted',
	'Cdelerror'		=> 'You didn\'t select any categories to delete',
	'Rcatdone'		=> 'The categories have been successfully re-ordered',


	'Fchecker'		=> 'File: Maintenance',
	'File_checker'		=> 'File Maintenance',
	'File_checker_explain'		=> 'Here you can perform a checking for all file in database and the file in the download directory.',
	'File_saftey'		=> 'File maintenance will attempt to delete all files and screenshots that are currently not needed and will remove any file records where the file has been deleted and will clear all screenshots that are not found.<br /><br />If the files do not start with <FONT COLOR="#FF0000">{html_path}</FONT> then the files will be skipped for security reasons.<br /><br />Please make sure that <FONT COLOR="#FF0000">{html_path}</FONT> is the path that you use for your files.<br /><br />.',

	'File_checker_perform'		=> 'Perform Checking',
	'Checker_saved'		=> 'Total Saved Space',
	'Checker_sp1'		=> 'Checking for records with missing files...',
	'Checker_sp2'		=> 'Checking for records with missing screenshots...',
	'Checker_sp3'		=> 'Deleting unused Files...',


	'View'		=> 'View',
	'Read'		=> 'Read',
	'View_file'		=> 'View File',
	'Delete_file'		=> 'Delete File',
	'Edit_file'		=> 'Edit File',
	'Upload'		=> 'Upload File',
	'Approval'		=> 'Approval',
	'Approval_edit'		=> 'Approval Edit',
	'Download_file'		=> 'Download File',
	'Rate'		=> 'Rate',
	'View_comment'		=> 'View Comments',
	'Post_comment'		=> 'Post Comments',
	'Edit_comment'		=> 'Edit Comments',
	'Delete_comment'		=> 'Delete Comments',
	'Category_auth_updated'		=> 'Category permissions updated',
	'Click_return_catauth'		=> 'Click %sHere%s to return to Category Permissions',
	'Auth_Control_Category'		=> 'Category Permissions Control',
	'Category_auth_explain'		=> 'Here you can alter the authorisation levels of each category. Remember that changing the permission level of category will affect which users can carry out the various operations within them.',
	'Select_a_Category'		=> 'Select a Category',
	'Look_up_Category'		=> 'Look Up Category',
	'Category'		=> 'Category',

	'Category_NONE'		=> 'NONE',
	'Category_ALL'		=> 'ALL',
	'Category_REG'		=> 'REG',
	'Category_PRIVATE'		=> 'PRIVATE',
	'Category_MOD'		=> 'MOD',
	'Category_ADMIN'		=> 'ADMIN',


	'Fieldselecttitle'		=> 'Select what to do',
	'Afield'		=> 'Custom Field: Add',
	'Efield'		=> 'Custom Field: Edit',
	'Dfield'		=> 'Custom Field: Delete',
	'Mfieldtitle'		=> 'Custom Fields',
	'Afieldtitle'		=> 'Add Field',
	'Efieldtitle'		=> 'Edit Field',
	'Dfieldtitle'		=> 'Delete Field',
	'Fieldexplain'		=> 'You can use the custom fields management section to add, edit, and delete custom fields. You can use custom fields to add more information about a file. For example, if you want an information field to put the file\'s size in, you can create the custom field and then you can add the file size on the Add/Edit file page.',
	'Fieldname'		=> 'Field Name',
	'Fieldnameinfo'		=> 'This is the name of the field, for example \'File Size\'',
	'Fielddesc'		=> 'Field Description',
	'Fielddescinfo'		=> 'This is a description of the field, for example \'File Size in Megabytes\'',
	'Fieldadded'		=> 'The custom field has been successfully added',
	'Fieldedited'		=> 'The custom field you selected has been successfully edited',
	'Dfielderror'		=> 'You didn\'t select any fields to delete',
	'Fieldsdel'		=> 'The custom fields you selected have been successfully deleted',

	'Field_data'		=> 'Options',
	'Field_data_info'		=> 'Enter the options that the user can choose from. Separate each option with a new-line (carriage return).',
	'Field_regex'		=> 'Regular Expression',
	'Field_regex_info'		=> 'You may require the input field to match a regular expression %s(PCRE)%s.',
	'Field_order'		=> 'Display Order',


	'License_title'		=> 'License',
	'Alicense'		=> 'License: Add',
	'Elicense'		=> 'License: Edit',
	'Dlicense'		=> 'License: Delete',
	'Alicensetitle'		=> 'Add License',
	'Elicensetitle'		=> 'Edit License',
	'Dlicensetitle'		=> 'Delete License',
	'Licenseexplain'		=> 'You can use the license management section to add, edit, and delete license agreements. You can select a license for a file on the file add or edit page. If a file has a license agreement, a user will have to agree to it before downloading the file.',
	'Lname'		=> 'License Name',
	'Ltext'		=> 'License Text',
	'Licenseadded'		=> 'The new license agreement has been successfully added',
	'Licenseedited'		=> 'The license agreement you selected has been successfully edited',
	'Lderror'		=> 'You did not select any licenses to delete',
	'Ldeleted'		=> 'The license agreements you selected have been successfully deleted',

	'Click_return'		=> 'Click %sHere%s to return to the previous page',
	'Click_edit_permissions'		=> 'Click %sHere%s to edit the permissions for this category',


	'Cat_name_missing'		=> 'Please fill the category name field',
	'Cat_conflict'		=> 'You can\'t have a category with no file in side a category that doesn\'t allow files',
	'Cat_id_missing'		=> 'Please select a category',
	'Missing_field'		=> 'Please complete all the required fields',


	'Field_Input'		=> 'Single-Line Text Box',
	'Field_Textarea'		=> 'Multiple-Line Text Box',
	'Field_Radio'		=> 'Single-Selection Radio Buttons',
	'Field_Select'		=> 'Single-Selection Menu',
	'Field_Select_multiple'		=> 'Multiple-Selection Menu',
	'Field_Checkbox'		=> 'Multiple-Selection Checkbox',

	'Com_settings'		=> 'Comment Settings',
	'Validation_settings'		=> 'Approval Settings',
	'Ratings_settings'		=> 'Ratings Settings',
	'PM_notify'		=> 'PM Notification (to admin)',

	'Use_comments'		=> 'Enable comments',
	'Allow_comments'		=> 'Enable comments',
	'Allow_comments_info'		=> 'Enable/disable comments in this category.',

	'Use_ratings'		=> 'Enable ratings',
	'Allow_ratings'		=> 'Enable ratings',
	'Allow_ratings_info'		=> 'Enable/disable ratings in this category.',

	'Fileadded_not_validated'		=> 'The new file has been successfully added, but a moderator (admin) need to validate the file before approval.',


	'toplist_sort_method'		=> 'Toplist type',
	'toplist_display_options'		=> 'Display options',
	'toplist_use_pagination'		=> 'Use Pagination (Previous/Next \'Number of rows\')',
	'toplist_pagination'		=> 'Number of rows',
	'toplist_filter_date'		=> "Filter by time",
	'toplist_filter_date_explain'		=> "- Show posts from last week, month, year...",
	'toplist_cat_id'		=> 'Limit to category',
	'target_block'		=>'Associated (target) pafileDB Block',


	'mini_display_options'		=> 'Display options',
	'mini_pagination'		=>'Number of rows',
	'mini_default_cat_id'		=>'Limit to category',


	'Panel_title'		=> 'pafileDB Mapping',
	'Panel_title_explain'		=> 'Here you can associate portal dynamic blocks and pafileDB categories. The quickdl block will show the pafiledb category when the dynamic block is active.',

	'Map_pafiledb'		=> 'Select a pafileDB category...',
	'Map_mxbb'		=> '...to be mapped to this dynamic portal block',
	
	'Permissions'		=> 'Permisiuni',
	'Simple_Permission'		=> 'Permisiune simplă',
	
	'auth_guests' => 'Guests',
	'auth_members' => 'Members',
	'auth_mods' => 'Moderators',
	'auth_admins' => 'Admins',	

	'User_Level'		=> 'Nivelul utilizatorului',
	'Auth_User'		=> 'Utilizator',
	'Auth_Admin'		=> 'Administrator',
	'Group_memberships'		=> 'Membru al grupurilor',
	'Usergroup_members'		=> 'Acest grup conţine următorii membrii',	

	'Username' => 'Utilizator',
	'Look_up_User' => 'Caută utilizator',
	'SELECT_USER'			=> 'Selectează utilizator',
	'SELECT_GROUP'			=> 'Selectează grup',		
	'Look_up_Group' => 'Caută Grup',
	'Group_name' => 'Nume Grup',	
	'No_such_user' => 'Sorry, but no such user exists.',	
	'User_admin' => 'Administrare utilizatori',
	'User_admin_explain' => 'Aici puteţi schimba informaţiile despre utilizatorii dumneavoastră şi opţiunile specifice. Ca să modificaţi drepturile utilizatorilor, folosiţi drepturile din sistem ale utilizatorilor şi grupurilor.',	
));

?>