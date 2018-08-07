<?php
/**
*
* @package MX-Publisher Core
* @version $Id: lang_main.php,v 1.42 2013/06/28 15:34:31 orynider Exp $
* @copyright (c) 2002-2008 MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License v2
* @link http://mxpcms.sourceforge.net/
*
*/

//
// The format of this file is:
//
// ---> $lang['message'] = 'text';
//
// Specify your language character encoding... [optional]
//
// setlocale(LC_ALL, 'en');

$lang['ENCODING'] = 'UTF-8';
$lang['DIRECTION'] = 'ltr';
$lang['USER_LANG']	= 'es-x-tu';
$lang['DATE_FORMAT'] =  '|d M Y| H:i'; // This should be changed to the default date format for your language, php date() format

//
// General
//
$lang['Page_Not_Authorised'] = 'Sorry, but you are not authorized to access this page.';
$lang['Execution_Stats'] = 'Page generated %s queries - Generation time: %s seconds';
$lang['Redirect_login'] = 'Click %shere%s to login.';
$lang['Show_admin_options'] = 'Show/Hide Page Admin Options: ';
$lang['Block_updated_date'] = 'Updated ';
$lang['Block_updated_by'] = 'by ';
$lang['Page_updated_date'] = 'This page was updated ';
$lang['Page_updated_by'] = 'by ';
$lang['Powered_by'] = 'Powered by ';

$lang['mx_spacer'] = 'Spacer';
$lang['Yes'] = 'Si';
$lang['No'] = 'No';

$lang['Link'] = 'Link';

$lang['Hidden_block'] = 'Hidden block';
$lang['Hidden_block_explain'] = 'This block is \'hidden\', but visible to you since you have the proper permissions set.';

//
// Overall Navigation Navigation
//
$lang['MX_home'] = 'Home';
$lang['MX_forum'] = 'Forum';

//
// Core Blocks - Language
//
$lang['Change_default_lang'] = 'Set the Board\'s Default Language';
$lang['Change_user_lang'] = 'Set Your Language';
$lang['Portal_lang'] = 'LanguageCP';
$lang['Select_lang'] = 'Select Language:';

//
// Core Blocks - Theme
//
$lang['Change'] = 'Change Now';
$lang['Change_default_style'] = 'Set the Board\'s Default Style';
$lang['Change_user_style'] = 'Set Your Style';
$lang['Theme'] = 'ThemeCP/StyleCP';
$lang['Select_theme'] = 'Select Theme/Style:';

//
// Core Blocks - Search
//
$lang['Mx_Page'] = 'Page';
$lang['Mx_Block'] = 'Section';

//
// Core Blocks - Virtual
//
$lang['Virtual_Create_new'] = 'Create new ';
$lang['Virtual_Create_new_user'] = 'User Page';
$lang['Virtual_Create_new_group'] = 'Group Page';
$lang['Virtual_Create_new_project'] = 'Project Page';
$lang['Virtual_Create'] = 'Create now';
$lang['Virtual_Edit'] = 'Update page name';
$lang['Virtual_Delete'] = 'Delete this page';

$lang['Virtual_Welcome'] = 'Welcome ';
$lang['Virtual_Info'] = 'Here you can control your private web page.';
$lang['Virtual_CP'] = 'Page Control Panel';
$lang['Virtual_Go'] = 'Go';
$lang['Virtual_Select'] = 'Select:';

//
// Core Blocks - Site Log (and many last 'item' blocks)
//
$lang['No_items_found'] = 'Nothing new to report. ';

//
// BlockCP
//
$lang['Block_Title'] = 'Title';
$lang['Block_Info'] = 'Information';

$lang['Block_Config_updated'] = 'Block configuration updated successfully.';
$lang['Block_Edit'] = 'Edit Block';
$lang['Block_Edit_dyn'] = 'Edit parent dynamic block';
$lang['Block_Edit_sub'] = 'Edit parent split block';

$lang['General_updated_return_settings'] = 'Configuration updated successfully.<br /><br />Click %shere%s to continue.'; // %s's for URI params - DO NOT REMOVE
$lang['General_update_error'] = 'Couldn\'t update configuration.';

//
// Header
//
$lang['Mx_search_site'] = 'Site';
$lang['Mx_search_forum'] = 'Forum';
$lang['Mx_search_kb'] = 'Articles';
$lang['Mx_search_pafiledb'] = 'Downloads';
$lang['Mx_search_google'] = 'Google';
$lang['Mx_new_search'] = 'New Search';

//
// Copyrights page
//
$lang['mx_about_title'] = 'About MX-Publisher';
$lang['mx_copy_title'] = 'MX-Publisher Information';
$lang['mx_copy_modules_title'] = 'Installed MX-Publisher Modules';
$lang['mx_copy_template_title'] = 'About the style';
$lang['mx_copy_translation_title'] = 'About the translation';

// This is optional, if you would like a _SHORT_ message output
// along with our copyright message indicating you are the translator
// please add it here.
//$lang['TRANSLATION_INFO_MXBB'] = 'English Language by <a href="http://mxpcms.sourceforge.net/" target="_blank">MX-Publisher Development Team</a>';

//
// Installation
//
$lang['Please_remove_install_contrib'] = 'Please ensure both the install/ and contrib/ directories are deleted.';

//
// Multilangual page titles
// - To have multilangual page titles, add lang keys 'pagetitle_PAGE_TITLE' below
// - This lang key replaces the page title (PAGE_TITLE) for the page given in the adminCP
//
//$lang['pagetitle_NameOfFirstPage'] = 'Whatever one';
//$lang['pagetitle_NameOfSecondPage'] = 'Whatever two';

//
// Multilangual block titles
// - To have multilangual block titles, add lang keys 'blocktitle_BLOCK_TITLE' below
// - This lang key replaces the block title (BLOCK_TITLE) for the block given in the adminCP/blockCP
//
//$lang['blocktitle_NameOfFirstPage'] = 'Whatever one';
//$lang['blocktitle_NameOfSecondPage'] = 'Whatever two';

//
// That's all Folks!
// -------------------------------------------------
?>