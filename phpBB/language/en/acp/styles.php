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

$lang = array_merge($lang, array(
	'ACP_IMAGESETS_EXPLAIN'	=> 'Imagesets comprise all the button, forum, folder, etc. and other non-style specific images used by the board. Here you can edit, export or delete existing imagesets and import or activate new sets.',
	'ACP_STYLES_EXPLAIN'	=> 'Here you can manage the available styles on your board. A style consists off a template, theme and imageset. You may alter existing styles, delete, deactivate, reactivate, create or import new ones. You can also see what a style will look like using the preview function. The current default style is noted by the presence of an asterix (*). Also listed is the total user count for each style, note that overriding user styles will not be reflected here.',
	'ACP_TEMPLATES_EXPLAIN'	=> 'A Template set comprises all the markup used to generate the layout of your board. Here you can edit existing template sets, delete, export, import and preview sets. You can also modify the templating code used to generate BBCode.',
	'ACP_THEMES_EXPLAIN'	=> 'From here you can create, install, edit, delete and export themes. A theme is the combination of colours and images that are applied to your templates to define the basic look of your forum. The range of options open to you depends on the configuration of your server and phpBB installation, see the Manual for further details. Please note that when creating new themes the use of an existing theme as a basis is optional.',
	'ADD_IMAGESET'			=> 'Create Imageset',
	'ADD_IMAGESET_EXPLAIN'	=> 'Here you can create a new imageset. Depending on your server configuration and file permissions you may have additional options here. For example you may be able to base this imageset on an existing one. You may also be able to upload or import (from the store directory) a imageset archive. If you upload or import an archive the imageset name can be optionally taken from the archive name (to do this leave the imageset name blank).',
	'ADD_STYLE'				=> 'Create Style',
	'ADD_STYLE_EXPLAIN'		=> 'Here you can create a new style. Depending on your server configuration and file permissions you may have additional options. For example you may be able to base this style on an existing one. You may also be able to upload or import (from the store directory) a style archive. If you upload or import an archive the style name will be determined automatically.',
	'ADD_TEMPLATE'			=> 'Create Template',
	'ADD_TEMPLATE_EXPLAIN'	=> 'Here you can add a new template. Depending on your server configuration and file permissions you may have additional options here. For example you may be able to base this template set on an existing one. You may also be able to upload or import (from the store directory) a template archive. If you upload or import an archive the template name can be optionally taken from the archive name (to do this leave the template name blank).',
	'ADD_THEME'				=> 'Create Theme',
	'ADD_THEME_EXPLAIN'		=> 'Here you can add a new theme. Depending on your server configuration and file permissions you may have additional options here. For example you may be able to base this theme on an existing one. You may also be able to upload or import (from the store directory) a theme archive. If you upload or import an archive the theme name can be optionally taken from the archive name (to do this leave the theme name blank).',
	'ARCHIVE_FORMAT'		=> 'Archive file type',

	'CACHE'					=> 'Cache',
	'COPYRIGHT'				=> 'Copyright',
	'CREATE_IMAGESET'		=> 'Create new imageset',
	'CREATE_STYLE'			=> 'Create new style',
	'CREATE_TEMPLATE'		=> 'Create new template set',
	'CREATE_THEME'			=> 'Create new theme',

	'DEACTIVATE_DEFAULT'		=> 'You cannot deactivate the default style.',
	'DELETE_FROM_FS'			=> 'Delete from filesystem',
	'DELETE_IMAGESET'			=> 'Delete Imageset',
	'DELETE_IMAGESET_EXPLAIN'	=> 'Here you can remove the selected imageset from the database. Additionally, if you have permission you can elect to remove the set from the filesystem. Please note that there is no undo capability. When the imageset is deleted it is gone for good. It is recommended that you first export your set for possible future use.',
	'DELETE_STYLE'				=> 'Delete style',
	'DELETE_STYLE_EXPLAIN'		=> 'Here you can remove the selected style. You cannot remove all the style elements from here. These must be deleted individually via their respective forms. Take care in deleting styles there is no undo facility.',
	'DELETE_TEMPLATE'			=> 'Delete Template',
	'DELETE_TEMPLATE_EXPLAIN'	=> 'Here you can remove the selected template set from the database. Additionally, if you have permission you can elect to remove the set from the filesystem. Please note that there is no undo capability. When the templates are deleted they are gone for good. It is recommended that you first export your set for possible future use.',
	'DELETE_THEME'				=> 'Delete theme',
	'DELETE_THEME_EXPLAIN'		=> 'Here you can remove the selected theme from the database. Additionally, if you have permission you can elect to remove the theme from the filesystem. Please note that there is no undo capability. When the theme is deleted it is gone for good. It is recommended that you first export your theme for possible future use.',
	'DETAILS'					=> 'Details',

	'EDIT_DETAILS_IMAGESET'				=> 'Edit imageset details',
	'EDIT_DETAILS_IMAGESET_EXPLAIN'		=> 'Here you can edit certain imageset details such as its name.',
	'EDIT_DETAILS_STYLE'				=> 'Edit Style',
	'EDIT_DETAILS_STYLE_EXPLAIN'		=> 'Using the form below you can modify this existing style. You may alter the combination of template, theme and imageset which define the style itself. You may also make the style the default one.',
	'EDIT_DETAILS_TEMPLATE'				=> 'Edit template details',
	'EDIT_DETAILS_TEMPLATE_EXPLAIN'		=> 'Here you can edit certain templates details such as its name. You may also have the option to switch storage of the stylesheet from the filesystem to the database and vice versa. This option depends on your PHP configuration and whether your template set can be written to by the webserver.',
	'EDIT_DETAILS_THEME'				=> 'Edit theme details',
	'EDIT_DETAILS_THEME_EXPLAIN'		=> 'Here you can edit certain theme details such as its name. You may also have the option to switch storage of the stylesheet from the filesystem to the database and vice versa. This option depends on your PHP configuration and whether your stylesheet can be written to by the webserver.',
	'EXPORT'							=> 'Export',

	'FROM'					=> 'from', // "Create new style .... from ..."

	'IMAGESET_ADDED'			=> 'New imageset added on filesystem',
	'IMAGESET_ADDED_DB'			=> 'New imageset added to database',
	'IMAGESET_DELETED'			=> 'Imageset deleted successfully',
	'IMAGESET_DELETED_FS'		=> 'Imageset removed from database but some files may remain on the filesystem',
	'IMAGESET_DETAILS_UPDATED'	=> 'Imageset details successfully updated',
	'IMAGESET_ERR_ARCHIVE'		=> 'Please select an archive method',
	'IMAGESET_ERR_COPY_LONG'	=> 'The copyright can be no longer than 60 characters',
	'IMAGESET_ERR_NAME_CHARS'	=> 'The imageset name can only contain alphanumeric characters, -, +, _ and space',
	'IMAGESET_ERR_NAME_EXIST'	=> 'A imageset with that name already exists',
	'IMAGESET_ERR_NAME_LONG'	=> 'The imageset name can be no longer than 30 characters',
	'IMAGESET_ERR_NOT_IMAGESET'	=> 'The archive you specified does not contain a valid imageset.',
	'IMAGESET_ERR_STYLE_NAME'	=> 'You must supply a name for this imageset',
	'IMAGESET_EXPORT'			=> 'Export Imageset',
	'IMAGESET_EXPORT_EXPLAIN'	=> 'Here you can export an imageset in the form of an archive. This archive will contain all the data necessary to install the set of images on another board. You may select whether to download the file directly or to place it in your store folder for download later or via FTP.',
	'IMAGESET_EXPORTED'			=> 'Imageset exported succesfully and stored in %s',
	'IMAGESET_NAME'				=> 'Imageset Name',
	'INCLUDE_IMAGESET'			=> 'Include imageset',
	'INCLUDE_TEMPLATE'			=> 'Include template',
	'INCLUDE_THEME'				=> 'Include theme',
	'INSTALL_IMAGESET'			=> 'Install Imageset',
	'INSTALL_IMAGESET_EXPLAIN'	=> 'Here you can install your selected imageset. You can edit certain details if you wish or use the installation defaults.',
	'INSTALL_STYLE'				=> 'Install Style',
	'INSTALL_STYLE_EXPLAIN'		=> 'Here you can install a new style and if appropriate the corresponding style elements. If you already have the relevant style elements installed they will not be overwritten. Some styles require existing style elements to already be installed. If you try installing such a style and do not have the required elements you will be notified.',
	'INSTALL_TEMPLATE'			=> 'Install Template',
	'INSTALL_TEMPLATE_EXPLAIN'	=> 'Here you can install a new template set. Depending on your server configuration you may have a number of options here.',
	'INSTALL_THEME'				=> 'Install Theme',
	'INSTALL_THEME_EXPLAIN'		=> 'Here you can install your selected theme. You can edit certain details if you wish or use the installation defaults.',
	'INSTALLED_IMAGESET'		=> 'Installed imagesets',
	'INSTALLED_STYLE'			=> 'Installed styles',
	'INSTALLED_TEMPLATE'		=> 'Installed templates',
	'INSTALLED_THEME'			=> 'Installed themes',

	'NO_IMAGESET'				=> 'Cannot find imageset on filesystem',
	'NO_STYLE'					=> 'Cannot find style on filesystem',
	'NO_TEMPLATE'				=> 'Cannot find template on filesystem',
	'NO_THEME'					=> 'Cannot find theme on filesystem',
	'NO_UNINSTALLED_IMAGESET'	=> 'No uninstalled imagesets detected',
	'NO_UNINSTALLED_STYLE'		=> 'No uninstalled styles detected',
	'NO_UNINSTALLED_TEMPLATE'	=> 'No uninstalled templates detected',
	'NO_UNINSTALLED_THEME'		=> 'No uninstalled themes detected',

	'ONLY_IMAGESET'			=> 'This is the only remaining imageset, you cannot delete it',
	'ONLY_STYLE'			=> 'This is the only remaining style, you cannot delete it',
	'ONLY_TEMPLATE'			=> 'This is the only remaining template set, you cannot delete it',
	'ONLY_THEME'			=> 'This is the only remaining theme, you cannot delete it',
	'OPTIONAL_BASIS'		=> 'Optional basis',

	'REFRESH'					=> 'Refresh',
	'REPLACE_IMAGESET'			=> 'Replace imageset with',
	'REPLACE_IMAGESET_EXPLAIN'	=> 'This imageset will replace the one you are deleting in any styles that use it.',
	'REPLACE_STYLE'				=> 'Replace style with',
	'REPLACE_STYLE_EXPLAIN'		=> 'This style will replace the one being deleted for members that use it.',
	'REPLACE_TEMPLATE'			=> 'Replace template with',
	'REPLACE_TEMPLATE_EXPLAIN'	=> 'This template set will replace the one you are deleting in any styles that use it.',
	'REPLACE_THEME'				=> 'Replace theme with',
	'REPLACE_THEME_EXPLAIN'		=> 'This theme will replace the one you are deleting in any styles that use it.',
	'REQUIRES_IMAGESET'			=> 'This style requires the %s imageset to be installed.',
	'REQUIRES_TEMPLATE'			=> 'This style requires the %s template set to be installed.',
	'REQUIRES_THEME'			=> 'This style requires the %s theme to be installed.',

	'STORE_DATABASE'			=> 'Database',
	'STORE_FILESYSTEM'			=> 'Filesystem',

	'STYLE_ACTIVATE'			=> 'Activate',
	'STYLE_ACTIVE'				=> 'Active',
	'STYLE_ADDED'				=> 'Style added successfully',
	'STYLE_DEACTIVATE'			=> 'Deactivate',
	'STYLE_DEFAULT'				=> 'Make default style',
	'STYLE_DELETED'				=> 'Style deleted successfully',
	'STYLE_DETAILS_UPDATED'		=> 'Style edited successfully',
	'STYLE_ERR_ARCHIVE'			=> 'Please select an archive method',
	'STYLE_ERR_COPY_LONG'		=> 'The copyright can be no longer than 60 characters',
	'STYLE_ERR_MORE_ELEMENTS'	=> 'You must select at least one style element.',
	'STYLE_ERR_NAME_CHARS'		=> 'The style name can only contain alphanumeric characters, -, +, _ and space',
	'STYLE_ERR_NAME_EXIST'		=> 'A style with that name already exists',
	'STYLE_ERR_NAME_LONG'		=> 'The style name can be no longer than 30 characters',
	'STYLE_ERR_NO_IDS'			=> 'You must select a template, theme and imageset for this style',
	'STYLE_ERR_NOT_STYLE'		=> 'The imported or uploaded file did not contain a valid style archive.',
	'STYLE_ERR_STYLE_NAME'		=> 'You must supply a name for this style',
	'STYLE_EXPORT'				=> 'Export Style',
	'STYLE_EXPORT_EXPLAIN'		=> 'Here you can export a style in the form of an archive. A style does not need to contain all elements but it must contain at least one. For example if you have created a new theme and imageset for a commonly used template you could simply export the theme and imageset and ommit the template. You may select whether to download the file directly or to place it in your store folder for download later or via FTP.',
	'STYLE_EXPORTED'			=> 'Style exported succesfully and stored in %s',
	'STYLE_IMAGESET'			=> 'Imageset',
	'STYLE_NAME'				=> 'Style name',
	'STYLE_TEMPLATE'			=> 'Template',
	'STYLE_THEME'				=> 'Theme',
	'STYLE_USED_BY'				=> 'Used by',

	'TEMPLATE_ADDED'			=> 'Template set added and stored on filesystem',
	'TEMPLATE_ADDED_DB'			=> 'Template set added and stored in database',
	'TEMPLATE_DELETED'			=> 'Template set deleted successfully',
	'TEMPLATE_DELETED_FS'		=> 'Template set removed from database but some files may remain on the filesystem',
	'TEMPLATE_DETAILS_UPDATED'	=> 'Template details successfully updated',
	'TEMPLATE_ERR_ARCHIVE'		=> 'Please select an archive method',
	'TEMPLATE_ERR_COPY_LONG'	=> 'The copyright can be no longer than 60 characters',
	'TEMPLATE_ERR_NAME_CHARS'	=> 'The template name can only contain alphanumeric characters, -, +, _ and space',
	'TEMPLATE_ERR_NAME_EXIST'	=> 'A template set with that name already exists',
	'TEMPLATE_ERR_NAME_LONG'	=> 'The template name can be no longer than 30 characters',
	'TEMPLATE_ERR_NOT_TEMPLATE'	=> 'The archive you specified does not contain a valid template set.',
	'TEMPLATE_ERR_STYLE_NAME'	=> 'You must supply a name for this templates',
	'TEMPLATE_EXPORT'			=> 'Export Templates',
	'TEMPLATE_EXPORT_EXPLAIN'	=> 'Here you can export a template set in the form of an archive. This archive will contain all the files necessary to install the templates on another board. You may select whether to download the file directly or to place it in your store folder for download later or via FTP.',
	'TEMPLATE_EXPORTED'			=> 'Templates exported succesfully and stored in %s',
	'TEMPLATE_LOCATION'			=> 'Store templates in',
	'TEMPLATE_LOCATION_EXPLAIN'	=> 'Images are always stored on the filesystem.',
	'TEMPLATE_NAME'				=> 'Template name',

	'THEME_ADDED'				=> 'New theme added on filesystem',
	'THEME_ADDED_DB'			=> 'New theme added to database',
	'THEME_DELETED'				=> 'Theme deleted successfully',
	'THEME_DELETED_FS'			=> 'Theme removed from database but files remain on the filesystem',
	'THEME_DETAILS_UPDATED'		=> 'Theme details successfully updated',
	'THEME_ERR_ARCHIVE'			=> 'Please select an archive method',
	'THEME_ERR_COPY_LONG'		=> 'The copyright can be no longer than 60 characters',
	'THEME_ERR_NAME_CHARS'		=> 'The theme name can only contain alphanumeric characters, -, +, _ and space',
	'THEME_ERR_NAME_EXIST'		=> 'A theme with that name already exists',
	'THEME_ERR_NAME_LONG'		=> 'The theme name can be no longer than 30 characters',
	'THEME_ERR_NOT_THEME'		=> 'The archive you specified does not contain a valid theme.',
	'THEME_ERR_STYLE_NAME'		=> 'You must supply a name for this theme',
	'THEME_EXPORT'				=> 'Export Theme',
	'THEME_EXPORT_EXPLAIN'		=> 'Here you can export a theme in the form of an archive. This archive will contain all the data necessary to install the theme on another board. You may select whether to download the file directly or to place it in your store folder for download later or via FTP.',
	'THEME_EXPORTED'			=> 'Theme exported succesfully and stored in %s',
	'THEME_LOCATION'			=> 'Store stylesheet in',
	'THEME_LOCATION_EXPLAIN'	=> 'Images are always stored on the filesystem.',
	'THEME_NAME'				=> 'Theme Name',

	'UNINSTALLED_IMAGESET'	=> 'Uninstalled imagesets',
	'UNINSTALLED_STYLE'		=> 'Uninstalled styles',
	'UNINSTALLED_TEMPLATE'	=> 'Uninstalled templates',
	'UNINSTALLED_THEME'		=> 'Uninstalled themes',

));

?>