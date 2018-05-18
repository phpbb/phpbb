<?php
/***************************************************************************
 *                            lang_google_sitemap.php [English]
 *                              -------------------
 *     begin                : Feb 22, 2006
 *     copyright            : (C) 2006 dcz
 *     email                : support@mx-system.com
 *
 *   $Id: lang_google_sitemap.php,v 1.1 2009/10/18 04:16:00 orynider Exp $
 *
 *
 ****************************************************************************/
/* Translation info : Feb 28, 2006
 Ver. 1.0.1
 copyright : (C) 2006 dcz
 This is the first version, please repport any errors.
*/
// 
// The format of this file is: 
// 
// ---> $lang["message"] = "text"; 
// 
// Specify your language character encoding... [optional]
// 
// setlocale(LC_ALL, "en"); 

// ACP
$lang['Sitemap_conf_title'] = 'Google Sitemaps';
$lang['Sitemap_conf_explain'] = "The Google sitemap system allows GoogleBot to find pages far away from the Home page easyer. This system generates a sitemapp index pointing to the different sitemaps available.<br /> You must register your sitemapIndex @ %sGoogle%s if you want to access some interesting stats.";
$lang['Sitemap_conf_explain2'] = "You can proceed %sanonymous%s though";

$lang['Sitemap_settings'] = 'Google Sitemaps Settings';

$lang['Sql_limit'] = 'SQL cycle';
$lang['Sql_limit_explain'] = 'Major queries are sparated into several cycles in order not to overload the SQL server. This is the maximum number of topics to fetch within a single query';

$lang['Default_limit'] = 'Url Limit';
$lang['Default_limit_explain'] = 'Maximum number of url outputed in each sitemap.<br /> This limit being checked in every SQL cycle, the actual outputed number of url is this limit +- 1 SQL cycle +- number of paginated topics (limited or not) in the last cycle.<br />Limited by default to 40 000, knowing Google will go up to 50 000 per sitemap file. ';

$lang['Sort_order'] = 'Sort Order';
$lang['New_first'] = 'DESC';
$lang['Old_first'] = 'ASC';
$lang['Sort_order_explain'] = 'All outputed links are sorted in the same way topics are sorted by default in phpbb (last activity DESC). <br /> You can set this to DESC for example if you whish to make it easyer for Google to find again links to archeological or locked threads (eg inactive for a looong time).';

$lang['Mod_rewrite_S'] = "Mod Rewrite Sitemaps";
$lang['Mod_rewrite_S_explain'] = "If activated, the sitemap's url will be rewrited.<br />CAUTION : you MUST run Apache server with mod rewrite activated and set up the .htaccess located in this release's contrib/ folder properly. <br /> NOTE : This will only affect the sitemaps url provided in the sitemap index. There is no problem for google to visit non url rewrited sitemaps.";

$lang['Sitemap_Forum_set'] = 'Forums Sitemaps Settings';

$lang['Announce_priority'] = 'Announcement Priority';
$lang['Announce_priority_explain'] = 'Announcement Priority (must be a number between 0.0 &amp; 1.0 inclusive)';

$lang['Sticky_priority'] = 'Sticky Priority';
$lang['Sticky_priority_explain'] = 'Sticky Priority (must be a number between 0.0 &amp; 1.0 inclusive)';

$lang['Default_priority'] = 'Default Priority';
$lang['Default_priority_explain'] = 'Priority for regular topics (must be a number between 0.0 &amp; 1.0 inclusive)';

$lang['Pagination_limit1'] = "Topic Pagination: Low Limit";
$lang['Pagination_limit_explain1'] = "Paginated topic link output is handeled. Enter here how many paginated topic pages, from the begining, are to be outputed.<br /> If set to 0, it won't output paginated links after the first topic page.";

$lang['Pagination_limit2'] = "Topic Pagination: Upper Limit";
$lang['Pagination_limit_explain2'] = "Enter here how many paginated topic pages, starting from the last one,  are to be outputed.<br /> If set to 0, it won't output paginated links before the last topic page.";

$lang['KB_mx_page'] = "Kb mx page Id";
$lang['KB_mx_page_explain'] = "This only get used if kb is installed in a %smxBB PORTAL%s . If running phpbb stand alone, just not bother about this, it's just ment to know on which mx pages kb is installed.<br />CAUTION : If you don't set this to the right ID while using KB and mxBB you could end up pointing to 404!!";


$lang['Google_Config_updated'] = "Google sitemaps Configuration Updated Successfully";
$lang['Click_return_ggsitemap_config'] = "Click %sHere%s to return to the Google sitemaps Configuration";

// INSTALL
$lang['Google_install'] = "<b>Installation mx Google Sitemaps : Default Param.</b><br/><br/>";
$lang['Google_install_ok'] = "Building required dB tables";
$lang['Google_error'] = "[Error or Already added]</font></b> line: ";
$lang['Google_sql_ok'] = "[Added/Updated]</font></b> line: ";
$lang['Google_general'] = "If you get some Errors, Already Added or Updated messages, relax, this is normal when updating modules";
$lang['Google_uninstall'] = "<b>This list is a result of the SQL queries needed for mx Google Sitemap module</b><br /><br />";
$lang['Google_uninstall_ok'] = "Sql : Ok.";

$lang['Google_unerror'] = "[Error, Already deleted or updated]</font></b> line: ";
$lang['Google_unsql_ok'] = "[Deleted/Updated]</font></b> line: ";
$lang['Google_uninstal_info'] = "Module Uninstallation Information";
$lang['Google_instal_info'] = "Module Installation Information";
//
// That's all Folks!
// -------------------------------------------------
?>
