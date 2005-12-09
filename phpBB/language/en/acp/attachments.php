<?php
/** 
*
* acp_attachments [English]
*
* @package language
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
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
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang += array(
	'ACP_ATTACHMENT_SETTINGS_EXPLAIN'	=> 'Here you can configure the Main Settings for Attachments and the associated Special Categories.',
	'ACP_EXTENSION_GROUPS_EXPLAIN'		=> 'Here you can add, delete and modify your Extension Groups, you can disable Extension Groups, assign a special Category to them, change the download mechanism and you can define an Upload Icon which will be displayed in front of an Attachment belonging to the Group.',
	'ACP_MANAGE_EXTENSIONS_EXPLAIN'		=> 'Here you can manage your allowed extensions. To activate your Extensions, please refer to the extension groups management panel. We strongly recommend not to allow scripting extensions (such as php, php3, php4, phtml, pl, cgi, asp, aspx...)',
	'ACP_ORPHAN_ATTACHMENTS_EXPLAIN'	=> 'Here you are able to see files within the Attachments upload directory but not assigned to posts. This happens mostly if users are attaching files but not submitting the post. You are able to delete the files or attach them to existing posts. Attaching to posts requires a valid post id, you have to determine this id by yourself, this feature is mainly for those people wanting to upload files with another program and assigning those (mostly large) files to an existing post.',
	'ADD_EXTENSION'						=> 'Add extension',
	'ADD_EXTENSION_GROUP'				=> 'Add Extension Group',
	'ADMIN_UPLOAD_ERROR'				=> 'Errors while trying to attach file: %s',
	'ALLOWED_FORUMS'					=> 'Allowed Forums',
	'ALLOWED_FORUMS_EXPLAIN'			=> 'Able to post the assigned extensions at the selected (or all if selected) forums',
	'ALLOW_ALL_FORUMS'					=> 'Allow All Forums',
	'ALLOW_IN_PM'						=> 'Allowed in private messaging',
	'ALLOW_SELECTED_FORUMS'				=> 'Only Forums selected below',
	'ASSIGNED_EXTENSIONS'				=> 'Assigned Extensions',
	'ASSIGNED_GROUP'					=> 'Assigned Group',
	'ATTACHMENTS'						=> 'Attachments',
	'ATTACH_EXTENSIONS_URL'				=> 'Extensions',
	'ATTACH_EXT_GROUPS_URL'				=> 'Extension Groups',
	'ATTACH_MAX_FILESIZE'				=> 'Maximum filesize',
	'ATTACH_MAX_FILESIZE_EXPLAIN'		=> 'Maximum size of each file, 0 is unlimited.',
	'ATTACH_MAX_PM_FILESIZE'			=> 'Maximum filesize messaging',
	'ATTACH_MAX_PM_FILESIZE_EXPLAIN'	=> 'Maximum drive space available per user for private message attachments, 0 is unlimited.',
	'ATTACH_ORPHAN_URL'					=> 'Orphan Attachments',
	'ATTACH_POST_ID'					=> 'Post ID',
	'ATTACH_QUOTA'						=> 'Total attachment quota',
	'ATTACH_QUOTA_EXPLAIN'				=> 'Maximum drive space available for attachments in total, 0 is unlimited.',
	'ATTACH_TO_POST'					=> 'Attach file to post',

	'CAT_IMAGES'				=> 'Images',
	'CAT_RM_FILES'				=> 'Real Media Streams',
	'CAT_WM_FILES'				=> 'Win Media Streams',
	'CREATE_GROUP'				=> 'Create new group',
	'CREATE_THUMBNAIL'			=> 'Create thumbnail',
	'CREATE_THUMBNAIL_EXPLAIN'	=> 'Create a thumbnail in all possible situations.',

	'DEFINE_ALLOWED_IPS'			=> 'Define allowed IPs/Hostnames',
	'DEFINE_DISALLOWED_IPS'			=> 'Define disallowed IPs/Hostnames',
	'DOWNLOAD_ADD_IPS_EXPLAIN'		=> 'To specify several different IP\'s or hostnames enter each on a new line. To specify a range of IP addresses separate the start and end with a hyphen (-), to specify a wildcard use *',
	'DOWNLOAD_MODE'					=> 'Download Mode',
	'DOWNLOAD_MODE_EXPLAIN'			=> 'If you experience problems downloading files, set this to "physical", the user will be directed to the file directly. Do not set it to physical if not really needed, it discloses the filename.',
	'DOWNLOAD_REMOVE_IPS_EXPLAIN'	=> 'You can remove (or un-exclude) multiple IP addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser. Excluded IP\'s have a blue background.',
	'DISPLAY_INLINED'				=> 'Display images inline',
	'DISPLAY_INLINED_EXPLAIN'		=> 'If set to No image attachments will show as a link.',
	'DISPLAY_ORDER'					=> 'Attachment Display Order',
	'DISPLAY_ORDER_EXPLAIN'			=> 'Display attachments ordered by time.',
	
	'EDIT_EXTENSION_GROUP'			=> 'Edit Extension Group',
	'EXCLUDE_ENTERED_IP'			=> 'Enable this to exclude the entered IP/Hostname.',
	'EXCLUDE_FROM_ALLOWED_IP'		=> 'Exclude IP from allowed IPs/Hostnames',
	'EXCLUDE_FROM_DISALLOWED_IP'	=> 'Exclude IP from disallowed IPs/Hostnames',
	'EXTENSIONS_UPDATED'			=> 'Extensions successfully updated',
	'EXTENSION_EXIST'				=> 'The Extension %s already exist',
	'EXTENSION_GROUP'				=> 'Extension Group',
	'EXTENSION_GROUPS'				=> 'Extension Groups',
	'EXTENSION_GROUP_DELETED'		=> 'Extension Group successfully deleted',
	'EXTENSION_GROUP_EXIST'			=> 'The Extension Group %s already exist',

	'GO_TO_EXTENSIONS'		=> 'Go to Extension Management Screen',
	'GROUP_NAME'			=> 'Group name',

	'IMAGE_LINK_SIZE'			=> 'Image Link Dimensions',
	'IMAGE_LINK_SIZE_EXPLAIN'	=> 'Display image attachment as link if image is larger than this, set to 0px by 0px to disable.',
	'IMAGICK_PATH'				=> 'Imagemagick path',
	'IMAGICK_PATH_EXPLAIN'		=> 'Full path to the imagemagick convert application, e.g. /usr/bin/',

	'MAX_ATTACHMENTS'				=> 'Max attachments per post',
	'MAX_ATTACHMENTS_PM'			=> 'Max attachments per message',
	'MAX_EXTGROUP_FILESIZE'			=> 'Maximum Filesize',
	'MAX_IMAGE_SIZE'				=> 'Maximum Image Dimensions',
	'MAX_IMAGE_SIZE_EXPLAIN'		=> 'Maximum size of image attachments, 0px by 0px disables image attachments.',
	'MIN_THUMB_FILESIZE'			=> 'Minimum thumbnail filesize',
	'MIN_THUMB_FILESIZE_EXPLAIN'	=> 'Do not create a thumbnail for images smaller than this.',
	'MODE_INLINE'					=> 'Inline',
	'MODE_PHYSICAL'					=> 'Physical',

	'NOT_ASSIGNED'				=> 'Not assigned',
	'NO_EXT_GROUP_NAME'			=> 'No Group Name entered',
	'NO_EXT_GROUP_SPECIFIED'	=> 'No Extension Group specified',
	'NO_IMAGE'					=> 'No Image',
	'NO_UPLOAD_DIR'				=> 'The upload directory you specified does not exist.',
	'NO_WRITE_UPLOAD'			=> 'The upload directory you specified cannot be written to. Please alter the permissions to allow the webserver to write to it.',

	'ORDER_ALLOW_DENY'		=> 'Allow',
	'ORDER_DENY_ALLOW'		=> 'Deny',

	'REMOVE_ALLOWED_IPS'		=> 'Remove or Un-exclude allowed IPs/Hostnames',
	'REMOVE_DISALLOWED_IPS'		=> 'Remove or Un-exclude disallowed IPs/Hostnames',

	'SEARCH_IMAGICK'				=> 'Search for Imagemagick',
	'SECURE_ALLOW_DENY'				=> 'Allow/Deny List',
	'SECURE_ALLOW_DENY_EXPLAIN'		=> 'Allow or Deny the list of addresses, this setting only applies to downloading files',
	'SECURE_DOWNLOADS'				=> 'Enable secure downloads',
	'SECURE_DOWNLOADS_EXPLAIN'		=> 'With this option enabled, downloads are limited to IP\'s/hostnames you defined.',
	'SECURE_DOWNLOAD_NOTICE'		=> 'Secure Downloads are not enabled. The settings below will be applied after enabling secure downloads.',
	'SECURE_DOWNLOAD_UPDATE_SUCCESS'=> 'The IP list has been updated successfully',
	'SECURE_EMPTY_REFERER'			=> 'Allow empty referer',
	'SECURE_EMPTY_REFERER_EXPLAIN'	=> 'Secure downloads are based on referers. Do you want to allow downloads for those ommitting the referer?',
	'SETTINGS_CAT_IMAGES'			=> 'Image category settings',
	'SPECIAL_CATEGORY'				=> 'Special Category',
	'SPECIAL_CATEGORY_EXPLAIN'		=> 'Special Categories differ between the way presented within posts.',
	'SUCCESSFULLY_UPLOADED'			=> 'Succeessfully uploaded',
	'SUCCESS_EXTENSION_GROUP_ADD'	=> 'Extension Group successfully added',
	'SUCCESS_EXTENSION_GROUP_EDIT'	=> 'Extension Group successfully updated',

	'UPLOADING_FILES'				=> 'Uploading Files',
	'UPLOADING_FILE_TO'				=> 'Uploading File "%1$s" to Post Number %2$d...',
	'UPLOAD_DENIED_FORUM'			=> 'You do not have the permission to upload files to forum "%s"',
	'UPLOAD_DIR'					=> 'Upload Directory',
	'UPLOAD_DIR_EXPLAIN'			=> 'Storage Path for Attachments.',
	'UPLOAD_ICON'					=> 'Upload Icon',
	'UPLOAD_NOT_DIR'				=> 'The upload location you specified does not appear to be a directory.',

);

?>