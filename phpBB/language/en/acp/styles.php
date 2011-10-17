<?php
/**
*
* acp_styles [English]
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
if (!defined('IN_PHPBB'))
{
	exit;
}

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
	'ACP_STYLES_EXPLAIN'	=> 'Here you can manage the available styles on your board. A style consists of a template and theme. You may alter existing styles, delete, deactivate, reactivate, create or import new ones. You can also see what a style will look like using the preview function. The current default style is noted by the presence of an asterisk (*). Also listed is the total user count for each style, note that overriding user styles will not be reflected here.',
	'ACP_TEMPLATES_EXPLAIN'	=> 'A template set comprises all the markup used to generate the layout of your board. Here you can edit existing template sets, delete, export, import and preview sets. You can also modify the templating code used to generate BBCode.',
	'ACP_THEMES_EXPLAIN'	=> 'From here you can create, install, edit, delete and export themes. A theme is the combination of colours and images that are applied to your templates to define the basic look of your board. The range of options open to you depends on the configuration of your server and phpBB installation, see the manual for further details. Please note that when creating new themes the use of an existing theme as a basis is optional.',
	'ADD_STYLE'				=> 'Create style',
	'ADD_STYLE_EXPLAIN'		=> 'Here you can create a new style. Depending on your server configuration and file permissions you may have additional options. For example you may be able to base this style on an existing one. You may also be able to upload or import (from the store directory) a style archive. If you upload or import an archive the style name will be determined automatically.',
	'ADD_TEMPLATE'			=> 'Create template',
	'ADD_TEMPLATE_EXPLAIN'	=> 'Here you can add a new template. Depending on your server configuration and file permissions you may have additional options here. For example you may be able to base this template set on an existing one. You may also be able to upload or import (from the store directory) a template archive. If you upload or import an archive the template name can be optionally taken from the archive name (to do this leave the template name blank).',
	'ADD_THEME'				=> 'Create theme',
	'ADD_THEME_EXPLAIN'		=> 'Here you can add a new theme. Depending on your server configuration and file permissions you may have additional options here. For example you may be able to base this theme on an existing one. You may also be able to upload or import (from the store directory) a theme archive. If you upload or import an archive the theme name can be optionally taken from the archive name (to do this leave the theme name blank).',
	'ARCHIVE_FORMAT'		=> 'Archive file type',
	'AUTOMATIC_EXPLAIN'		=> 'Leave blank to attempt automatic detection.',

	'BACKGROUND'			=> 'Background',
	'BACKGROUND_COLOUR'		=> 'Background colour',
	'BACKGROUND_IMAGE'		=> 'Background image',
	'BACKGROUND_REPEAT'		=> 'Background repeat',
	'BOLD'					=> 'Bold',

	'CACHE'							=> 'Cache',
	'CACHE_CACHED'					=> 'Cached',
	'CACHE_FILENAME'				=> 'Template file',
	'CACHE_FILESIZE'				=> 'File size',
	'CACHE_MODIFIED'				=> 'Modified',
	'CONFIRM_TEMPLATE_CLEAR_CACHE'	=> 'Are you sure you wish to clear all cached versions of your template files?',
	'COPYRIGHT'						=> 'Copyright',
	'CREATE_STYLE'					=> 'Create new style',
	'CREATE_TEMPLATE'				=> 'Create new template set',
	'CREATE_THEME'					=> 'Create new theme',
	'CURRENT_IMAGE'					=> 'Current image',

	'DEACTIVATE_DEFAULT'		=> 'You cannot deactivate the default style.',
	'DELETE_FROM_FS'			=> 'Delete from filesystem',
	'DELETE_STYLE'				=> 'Delete style',
	'DELETE_STYLE_EXPLAIN'		=> 'Here you can remove the selected style. Take care in deleting styles, there is no undo capability.',
	'DELETE_TEMPLATE'			=> 'Delete template',
	'DELETE_TEMPLATE_EXPLAIN'	=> 'Here you can remove the selected template set from the database. Please note that there is no undo capability. It is recommended that you first export your set for possible future use.',
	'DELETE_THEME'				=> 'Delete theme',
	'DELETE_THEME_EXPLAIN'		=> 'Here you can remove the selected theme from the database. Please note that there is no undo capability. It is recommended that you first export your theme for possible future use.',
	'DETAILS'					=> 'Details',
	'DIMENSIONS_EXPLAIN'		=> 'Selecting yes here will include width/height parameters.',


	'EDIT_DETAILS_STYLE'				=> 'Edit style',
	'EDIT_DETAILS_STYLE_EXPLAIN'		=> 'Using the form below you can modify this existing style. You may alter the combination of template and theme which define the style itself. You may also make the style the default one.',
	'EDIT_DETAILS_TEMPLATE'				=> 'Edit template details',
	'EDIT_DETAILS_TEMPLATE_EXPLAIN'		=> 'Here you can edit certain template details such as its name.',
	'EDIT_DETAILS_THEME'				=> 'Edit theme details',
	'EDIT_DETAILS_THEME_EXPLAIN'		=> 'Here you can edit certain theme details such as its name.',
	'EDIT_TEMPLATE'						=> 'Edit template',
	'EDIT_TEMPLATE_EXPLAIN'				=> 'Here you can edit your template set directly. Please remember that these edits are permanent and cannot be undone once submitted. Please take care when editing your template set, remember to close all replacement variable terms {XXXX} and conditional statements.',
	'EDIT_THEME'						=> 'Edit theme',
	'EDIT_THEME_EXPLAIN'				=> 'Here you can edit the selected theme, changing colours, images, etc.',
	'EDITOR_DISABLED'					=> 'The template editor is disabled.',
	'EXPORT'							=> 'Export',

	'FOREGROUND'			=> 'Foreground',
	'FONT_COLOUR'			=> 'Font colour',
	'FONT_FACE'				=> 'Font face',
	'FONT_FACE_EXPLAIN'		=> 'You can specify multiple fonts separated by commas. If a user doesn’t have the first font installed the first other working font will be chosen.',
	'FONT_SIZE'				=> 'Font size',

	'GLOBAL_IMAGES'			=> 'Global',

	'HIDE_CSS'				=> 'Hide raw CSS',

	'IMAGE_WIDTH'				=> 'Image width',
	'IMAGE_HEIGHT'				=> 'Image height',
	'IMAGE'						=> 'Image',
	'IMAGE_NAME'				=> 'Image name',
	'IMAGE_PARAMETER'			=> 'Parameter',
	'IMAGE_VALUE'				=> 'Value',

	'ITALIC'					=> 'Italic',

	'IMG_CAT_BUTTONS'		=> 'Localised buttons',
	'IMG_CAT_CUSTOM'		=> 'Custom images',
	'IMG_CAT_FOLDERS'		=> 'Topic icons',
	'IMG_CAT_FORUMS'		=> 'Forum icons',
	'IMG_CAT_ICONS'			=> 'General icons',
	'IMG_CAT_LOGOS'			=> 'Logos',
	'IMG_CAT_POLLS'			=> 'Polling images',
	'IMG_CAT_UI'			=> 'General user interface elements',
	'IMG_CAT_USER'			=> 'Additional images',

	'IMG_SITE_LOGO'			=> 'Main logo',
	'IMG_UPLOAD_BAR'		=> 'Upload progress bar',
	'IMG_POLL_LEFT'			=> 'Poll left end',
	'IMG_POLL_CENTER'		=> 'Poll centre',
	'IMG_POLL_RIGHT'		=> 'Poll right end',
	'IMG_ICON_FRIEND'		=> 'Add as friend',
	'IMG_ICON_FOE'			=> 'Add as foe',

	'IMG_FORUM_LINK'			=> 'Forum link',
	'IMG_FORUM_READ'			=> 'Forum',
	'IMG_FORUM_READ_LOCKED'		=> 'Forum locked',
	'IMG_FORUM_READ_SUBFORUM'	=> 'Subforum',
	'IMG_FORUM_UNREAD'			=> 'Forum unread posts',
	'IMG_FORUM_UNREAD_LOCKED'	=> 'Forum unread posts locked',
	'IMG_FORUM_UNREAD_SUBFORUM'	=> 'Subforum unread posts',
	'IMG_SUBFORUM_READ'			=> 'Legend subforum',
	'IMG_SUBFORUM_UNREAD'		=> 'Legend subforum unread posts',

	'IMG_TOPIC_MOVED'			=> 'Topic moved',

	'IMG_TOPIC_READ'				=> 'Topic',
	'IMG_TOPIC_READ_MINE'			=> 'Topic posted to',
	'IMG_TOPIC_READ_HOT'			=> 'Topic popular',
	'IMG_TOPIC_READ_HOT_MINE'		=> 'Topic popular posted to',
	'IMG_TOPIC_READ_LOCKED'			=> 'Topic locked',
	'IMG_TOPIC_READ_LOCKED_MINE'	=> 'Topic locked posted to',

	'IMG_TOPIC_UNREAD'				=> 'Topic unread posts',
	'IMG_TOPIC_UNREAD_MINE'			=> 'Topic posted to unread',
	'IMG_TOPIC_UNREAD_HOT'			=> 'Topic popular unread posts',
	'IMG_TOPIC_UNREAD_HOT_MINE'		=> 'Topic popular posted to unread',
	'IMG_TOPIC_UNREAD_LOCKED'		=> 'Topic locked unread',
	'IMG_TOPIC_UNREAD_LOCKED_MINE'	=> 'Topic locked posted to unread',

	'IMG_STICKY_READ'				=> 'Sticky topic',
	'IMG_STICKY_READ_MINE'			=> 'Sticky topic posted to',
	'IMG_STICKY_READ_LOCKED'		=> 'Sticky topic locked',
	'IMG_STICKY_READ_LOCKED_MINE'	=> 'Sticky topic locked posted to',
	'IMG_STICKY_UNREAD'				=> 'Sticky topic unread posts',
	'IMG_STICKY_UNREAD_MINE'		=> 'Sticky topic posted to unread',
	'IMG_STICKY_UNREAD_LOCKED'		=> 'Sticky topic locked unread posts',
	'IMG_STICKY_UNREAD_LOCKED_MINE'	=> 'Sticky topic locked posted to unread',

	'IMG_ANNOUNCE_READ'					=> 'Announcement',
	'IMG_ANNOUNCE_READ_MINE'			=> 'Announcement posted to',
	'IMG_ANNOUNCE_READ_LOCKED'			=> 'Announcement locked',
	'IMG_ANNOUNCE_READ_LOCKED_MINE'		=> 'Announcement locked posted to',
	'IMG_ANNOUNCE_UNREAD'				=> 'Announcement unread posts',
	'IMG_ANNOUNCE_UNREAD_MINE'			=> 'Announcement posted to unread',
	'IMG_ANNOUNCE_UNREAD_LOCKED'		=> 'Announcement locked unread posts',
	'IMG_ANNOUNCE_UNREAD_LOCKED_MINE'	=> 'Announcement locked posted to unread',

	'IMG_GLOBAL_READ'					=> 'Global',
	'IMG_GLOBAL_READ_MINE'				=> 'Global posted to',
	'IMG_GLOBAL_READ_LOCKED'			=> 'Global locked',
	'IMG_GLOBAL_READ_LOCKED_MINE'		=> 'Global locked posted to',
	'IMG_GLOBAL_UNREAD'					=> 'Global unread posts',
	'IMG_GLOBAL_UNREAD_MINE'			=> 'Global posted to unread',
	'IMG_GLOBAL_UNREAD_LOCKED'			=> 'Global locked unread posts',
	'IMG_GLOBAL_UNREAD_LOCKED_MINE'		=> 'Global locked posted to unread',

	'IMG_PM_READ'		=> 'Read private message',
	'IMG_PM_UNREAD'		=> 'Unread private message',

	'IMG_ICON_BACK_TOP'		=> 'Top',

	'IMG_ICON_CONTACT_AIM'		=> 'AIM',
	'IMG_ICON_CONTACT_EMAIL'	=> 'Send e-mail',
	'IMG_ICON_CONTACT_ICQ'		=> 'ICQ',
	'IMG_ICON_CONTACT_JABBER'	=> 'Jabber',
	'IMG_ICON_CONTACT_MSNM'		=> 'WLM',
	'IMG_ICON_CONTACT_PM'		=> 'Send message',
	'IMG_ICON_CONTACT_YAHOO'	=> 'YIM',
	'IMG_ICON_CONTACT_WWW'		=> 'Website',

	'IMG_ICON_POST_DELETE'			=> 'Delete post',
	'IMG_ICON_POST_EDIT'			=> 'Edit post',
	'IMG_ICON_POST_INFO'			=> 'Show post details',
	'IMG_ICON_POST_QUOTE'			=> 'Quote post',
	'IMG_ICON_POST_REPORT'			=> 'Report post',
	'IMG_ICON_POST_TARGET'			=> 'Minipost',
	'IMG_ICON_POST_TARGET_UNREAD'	=> 'New minipost',


	'IMG_ICON_TOPIC_ATTACH'			=> 'Attachment',
	'IMG_ICON_TOPIC_LATEST'			=> 'Last post',
	'IMG_ICON_TOPIC_NEWEST'			=> 'Last unread post',
	'IMG_ICON_TOPIC_REPORTED'		=> 'Post reported',
	'IMG_ICON_TOPIC_UNAPPROVED'		=> 'Post unapproved',

	'IMG_ICON_USER_ONLINE'		=> 'User online',
	'IMG_ICON_USER_OFFLINE'		=> 'User offline',
	'IMG_ICON_USER_PROFILE'		=> 'Show profile',
	'IMG_ICON_USER_SEARCH'		=> 'Search posts',
	'IMG_ICON_USER_WARN'		=> 'Warn user',

	'IMG_BUTTON_PM_FORWARD'		=> 'Forward private message',
	'IMG_BUTTON_PM_NEW'			=> 'New private message',
	'IMG_BUTTON_PM_REPLY'		=> 'Reply private message',
	'IMG_BUTTON_TOPIC_LOCKED'	=> 'Topic locked',
	'IMG_BUTTON_TOPIC_NEW'		=> 'New topic',
	'IMG_BUTTON_TOPIC_REPLY'	=> 'Reply topic',

	'IMG_USER_ICON1'		=> 'User defined image 1',
	'IMG_USER_ICON2'		=> 'User defined image 2',
	'IMG_USER_ICON3'		=> 'User defined image 3',
	'IMG_USER_ICON4'		=> 'User defined image 4',
	'IMG_USER_ICON5'		=> 'User defined image 5',
	'IMG_USER_ICON6'		=> 'User defined image 6',
	'IMG_USER_ICON7'		=> 'User defined image 7',
	'IMG_USER_ICON8'		=> 'User defined image 8',
	'IMG_USER_ICON9'		=> 'User defined image 9',
	'IMG_USER_ICON10'		=> 'User defined image 10',

	'INCLUDE_DIMENSIONS'		=> 'Include dimensions',
	'INCLUDE_TEMPLATE'			=> 'Include template',
	'INCLUDE_THEME'				=> 'Include theme',
	'INHERITING_FROM'			=> 'Inherits from',
	'INSTALL_STYLE'				=> 'Install style',
	'INSTALL_STYLE_EXPLAIN'		=> 'Here you can install a new style and if appropriate the corresponding style elements. If you already have the relevant style elements installed they will not be overwritten. Some styles require existing style elements to already be installed. If you try installing such a style and do not have the required elements you will be notified.',
	'INSTALL_TEMPLATE'			=> 'Install Template',
	'INSTALL_TEMPLATE_EXPLAIN'	=> 'Here you can install a new template set. Depending on your server configuration you may have a number of options here.',
	'INSTALL_THEME'				=> 'Install theme',
	'INSTALL_THEME_EXPLAIN'		=> 'Here you can install your selected theme. You can edit certain details if you wish or use the installation defaults.',
	'INSTALLED_STYLE'			=> 'Installed styles',
	'INSTALLED_TEMPLATE'		=> 'Installed templates',
	'INSTALLED_THEME'			=> 'Installed themes',

	'KEEP_TEMPLATE'				=> 'Keep “%s” template',
	'KEEP_THEME'				=> 'Keep “%s” theme',

	'LINE_SPACING'				=> 'Line spacing',
	'LOCALISED_IMAGES'			=> 'Localised',

	'NO_CLASS'					=> 'Cannot find class in stylesheet.',
	'NO_IMAGE'					=> 'No image',
	'NO_IMAGE_ERROR'			=> 'Cannot find image on filesystem.',
	'NO_STYLE'					=> 'Cannot find style on filesystem.',
	'NO_TEMPLATE'				=> 'Cannot find template on filesystem.',
	'NO_THEME'					=> 'Cannot find theme on filesystem.',
	'NO_UNINSTALLED_STYLE'		=> 'No uninstalled styles detected.',
	'NO_UNINSTALLED_TEMPLATE'	=> 'No uninstalled templates detected.',
	'NO_UNINSTALLED_THEME'		=> 'No uninstalled themes detected.',
	'NO_UNIT'					=> 'None',

	'ONLY_STYLE'			=> 'This is the only remaining style, you cannot delete it.',
	'ONLY_TEMPLATE'			=> 'This is the only remaining template set, you cannot delete it.',
	'ONLY_THEME'			=> 'This is the only remaining theme, you cannot delete it.',
	'OPTIONAL_BASIS'		=> 'Optional basis',

	'REFRESH'					=> 'Refresh',
	'REPEAT_NO'					=> 'None',
	'REPEAT_X'					=> 'Only horizontally',
	'REPEAT_Y'					=> 'Only vertically',
	'REPEAT_ALL'				=> 'Both directions',
	'REPLACE_STYLE'				=> 'Replace style with',
	'REPLACE_STYLE_EXPLAIN'		=> 'This style will replace the one being deleted for members that use it.',
	'REPLACE_TEMPLATE'			=> 'Replace template with',
	'REPLACE_TEMPLATE_EXPLAIN'	=> 'This template set will replace the one you are deleting in any styles that use it.',
	'REPLACE_THEME'				=> 'Replace theme with',
	'REPLACE_THEME_EXPLAIN'		=> 'This theme will replace the one you are deleting in any styles that use it.',
	'REPLACE_WITH_OPTION'		=> 'Replace with “%s”',
	'REQUIRES_TEMPLATE'			=> 'This style requires the %s template set to be installed.',
	'REQUIRES_THEME'			=> 'This style requires the %s theme to be installed.',

	'SELECT_IMAGE'				=> 'Select image',
	'SELECT_TEMPLATE'			=> 'Select template file',
	'SELECT_THEME'				=> 'Select theme file',
	'SELECTED_IMAGE'			=> 'Selected image',
	'SELECTED_TEMPLATE'			=> 'Selected template',
	'SELECTED_TEMPLATE_FILE'	=> 'Selected template file',
	'SELECTED_THEME'			=> 'Selected theme',
	'SELECTED_THEME_FILE'		=> 'Selected theme file',
	'STORE_FILESYSTEM'			=> 'Filesystem',
	'STYLE_ACTIVATE'			=> 'Activate',
	'STYLE_ACTIVE'				=> 'Active',
	'STYLE_ADDED'				=> 'Style added successfully.',
	'STYLE_DEACTIVATE'			=> 'Deactivate',
	'STYLE_DEFAULT'				=> 'Make default style',
	'STYLE_DELETED'				=> 'Style deleted successfully.',
	'STYLE_DETAILS_UPDATED'		=> 'Style edited successfully.',
	'STYLE_ERR_ARCHIVE'			=> 'Please select an archive method.',
	'STYLE_ERR_COPY_LONG'		=> 'The copyright can be no longer than 60 characters.',
	'STYLE_ERR_MORE_ELEMENTS'	=> 'You must select at least one style element.',
	'STYLE_ERR_NAME_CHARS'		=> 'The style name can only contain alphanumeric characters, -, +, _ and space.',
	'STYLE_ERR_NAME_EXIST'		=> 'A style with that name already exists.',
	'STYLE_ERR_NAME_LONG'		=> 'The style name can be no longer than 30 characters.',
	'STYLE_ERR_NO_IDS'			=> 'You must select a template and theme for this style.',
	'STYLE_ERR_NOT_STYLE'		=> 'The imported or uploaded file did not contain a valid style archive.',
	'STYLE_ERR_STYLE_NAME'		=> 'You must supply a name for this style.',
	'STYLE_EXPORT'				=> 'Export style',
	'STYLE_EXPORT_EXPLAIN'		=> 'Here you can export a style in the form of an archive. A style does not need to contain all elements but it must contain at least one. For example if you have created a new theme for a commonly used template you could simply export the theme and omit the template. You may select whether to download the file directly or to place it in your store folder for download later or via FTP.',
	'STYLE_EXPORTED'			=> 'Style exported successfully and stored in %s.',
	'STYLE_NAME'				=> 'Style name',
	'STYLE_TEMPLATE'			=> 'Template',
	'STYLE_THEME'				=> 'Theme',
	'STYLE_USED_BY'				=> 'Used by (including robots)',

	'TEMPLATE_ADDED'			=> 'Template set added.',
	'TEMPLATE_CACHE'			=> 'Template cache',
	'TEMPLATE_CACHE_EXPLAIN'	=> 'By default phpBB caches the compiled version of its templates. This decreases the load on the server each time a page is viewed and thus may reduce the page generation time. Here you can view the cache status of each file and delete individual files or the entire cache.',
	'TEMPLATE_CACHE_CLEARED'	=> 'Template cache cleared successfully.',
	'TEMPLATE_CACHE_EMPTY'		=> 'There are no cached templates.',
	'TEMPLATE_DELETED'			=> 'Template set deleted successfully.',
	'TEMPLATE_DELETE_DEPENDENT'	=> 'The template set cannot be deleted as there are one or more other template sets inheriting from it:',
	'TEMPLATE_DELETED_FS'		=> 'Template set removed from database but files remain on the filesystem.',
	'TEMPLATE_DETAILS_UPDATED'	=> 'Template details successfully updated.',
	'TEMPLATE_EDITOR'			=> 'Raw HTML template editor',
	'TEMPLATE_EDITOR_HEIGHT'	=> 'Template editor height',
	'TEMPLATE_ERR_ARCHIVE'		=> 'Please select an archive method.',
	'TEMPLATE_ERR_CACHE_READ'	=> 'The cache directory used to store cached versions of template files could not be opened.',
	'TEMPLATE_ERR_COPY_LONG'	=> 'The copyright can be no longer than 60 characters.',
	'TEMPLATE_ERR_NAME_CHARS'	=> 'The template name can only contain alphanumeric characters, -, +, _ and space.',
	'TEMPLATE_ERR_NAME_EXIST'	=> 'A template set with that name already exists.',
	'TEMPLATE_ERR_NAME_LONG'	=> 'The template name can be no longer than 30 characters.',
	'TEMPLATE_ERR_NOT_TEMPLATE'	=> 'The archive you specified does not contain a valid template set.',
	'TEMPLATE_ERR_REQUIRED_OR_INCOMPLETE' => 'The new template set requires the template %s to be installed and not inheriting itself.',
	'TEMPLATE_ERR_STYLE_NAME'	=> 'You must supply a name for this template.',
	'TEMPLATE_EXPORT'			=> 'Export templates',
	'TEMPLATE_EXPORT_EXPLAIN'	=> 'Here you can export a template set in the form of an archive. This archive will contain all the files necessary to install the templates on another board. You may select whether to download the file directly or to place it in your store folder for download later or via FTP.',
	'TEMPLATE_EXPORTED'			=> 'Templates exported successfully and stored in %s.',
	'TEMPLATE_FILE'				=> 'Template file',
	'TEMPLATE_FILE_UPDATED'		=> 'Template file updated successfully.',
	'TEMPLATE_INHERITS'			=> 'This template sets inherits from %s and thus cannot have a different storage setting than its super template.',
	'TEMPLATE_NAME'				=> 'Template name',
	'TEMPLATE_FILE_NOT_WRITABLE'=> 'Unable to write to template file %s. Please check the permissions for the directory and the files.',

	'THEME_ADDED'				=> 'New theme added.',
	'THEME_CLASS_ADDED'			=> 'Custom class added successfully.',
	'THEME_DELETED'				=> 'Theme deleted successfully.',
	'THEME_DELETED_FS'			=> 'Theme removed from database but files remain on the filesystem.',
	'THEME_DETAILS_UPDATED'		=> 'Theme details successfully updated.',
	'THEME_EDITOR'				=> 'Theme editor',
	'THEME_EDITOR_HEIGHT'		=> 'Theme editor height',
	'THEME_ERR_ARCHIVE'			=> 'Please select an archive method.',
	'THEME_ERR_CLASS_CHARS'		=> 'Only alphanumeric characters plus ., :, -, _ and # are valid in class names.',
	'THEME_ERR_COPY_LONG'		=> 'The copyright can be no longer than 60 characters.',
	'THEME_ERR_NAME_CHARS'		=> 'The theme name can only contain alphanumeric characters, -, +, _ and space.',
	'THEME_ERR_NAME_EXIST'		=> 'A theme with that name already exists.',
	'THEME_ERR_NAME_LONG'		=> 'The theme name can be no longer than 30 characters.',
	'THEME_ERR_NOT_THEME'		=> 'The archive you specified does not contain a valid theme.',
	'THEME_ERR_STYLE_NAME'		=> 'You must supply a name for this theme.',
	'THEME_FILE'				=> 'Theme file',
	'THEME_FILE_NOT_WRITABLE'	=> 'Unable to write to theme file %s. Please check the permissions for the directory and the files.',
	'THEME_EXPORT'				=> 'Export Theme',
	'THEME_EXPORT_EXPLAIN'		=> 'Here you can export a theme in the form of an archive. This archive will contain all the data necessary to install the theme on another board. You may select whether to download the file directly or to place it in your store folder for download later or via FTP.',
	'THEME_EXPORTED'			=> 'Theme exported successfully and stored in %s.',
	'THEME_NAME'				=> 'Theme name',
	'THEME_UPDATED'				=> 'Theme updated successfully.',

	'UNDERLINE'				=> 'Underline',
	'UNINSTALLED_STYLE'		=> 'Uninstalled styles',
	'UNINSTALLED_TEMPLATE'	=> 'Uninstalled templates',
	'UNINSTALLED_THEME'		=> 'Uninstalled themes',
	'UNSET'					=> 'Undefined',

));
