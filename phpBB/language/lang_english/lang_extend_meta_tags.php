<?php
/***************************************************************************
 *						lang_extend_meta_tags.php [English]
 *						-----------------------------------------------
 *	begin				: 12/10/2004
 *	copyright		: paperclips
 *	email				: jm.lachance@gmail.com
 *
 *	version				: 1.0.0 - 11/10/2004
 *
 ***************************************************************************/
 
/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die("Hacking attempt");
}

// admin part
if ( $lang_extend_admin )
{
$lang['Click_return_admin_meta_tags'] = 'Click %sHere%s to came back to the Meta Tags managment';
$lang['Lang_extend_meta_tags'] = 'Meta Tags +';
$lang['Meta_tags_title'] = 'Meta Tags +';
$lang['Meta_tags_title_explain'] = 'Welcome to the Meta Tags managment.  These tags allow you to give a description of your website to the search engine to allow them indexing your site.<br/ >It is why, you should take attention.<br/ >Furthermore of referencing, these tags allow other options such as automatic redirect to another URL.  ';

$lang['Meta_parameters'] = 'Complete list of meta tags';
$lang['Meta_parameters_explain'] = ' Summary of the principals meta tags, their syntax is : <<b>meta name="xxx" content="xxx"</b>>';
$lang['Meta_keywords']  = 'META Keywords';
$lang['Meta_keywords_explain']  = '- Function: Indicate to the search engines the key words related with your site.<br />- Maximum number of characters: 1000 or 100 key words.<br/ >- In the number of characters, do not forget to count the <a href="accent.htm">accentuated letters</a> once coded in HTML. For example the letter "à", coded &amp&agrave; in HTML counts for eight characters.<br />- You should not repeat several times the same key word (the search engines do not like it).<br />- The key words are separate by a comma, a space or a comma and a space, it is your choice.';
$lang['Meta_description'] = 'META Description';
$lang['Meta_description_explain'] = '- Description of your site.<br />- Maximum number of characters: 200<br />- Avoid the accents, on certain engines it are not taken into account.';
$lang['Meta_author']  = 'META Author';
$lang['Meta_author_explain']  = '- Allows to identifier the author of the site.<br/ >- Put the first name into tiny, followed by the family name in capital letter.<br/ >- If you wish, you can put several authors separated by a comma.';
$lang['Meta_identifier_url']  = 'META Identifier-url';
$lang['Meta_identifier_url_explain']  = ' - Makes possible to specify the URL.<br />- Enter the URL of your home page.<br />- You must specify only one URL.';
$lang['Meta_reply_to']  = 'META Reply-to';
$lang['Meta_reply_to_explain']  = ' - Allows to specify the email of the webmaster.<br/ > It is preferable to put only one address.';
$lang['Meta_revisit_after']  = 'META Revisit-after';
$lang['Meta_revisit_after_explain']  = ' - Allows to specify with the spider (robot of the engine) of indexing your site according to the number of days entered. - 15 days" or "30 days" are the best compromises.';
$lang['Meta_category']  = 'META Category';
$lang['Meta_category_explain']  = ' - Allows to specify the category of your site. Used by certain engines which give a classification by category.';
$lang['Meta_generator']  = 'META Generator';
$lang['Meta_generator_explain']  = '  - Typically the name and version number of a publishing tool used to create the page.<br/ >- Could be used by tool vendors to assess market penetration. <br / >- Same tags as meta publisher.';
$lang['Meta_copyright']  = 'META Copyright';
$lang['Meta_copyright_explain']  = '- Typically an unqualified copyright statement.<br /> - You can include copyright, trademarks, patents, or other information here pertaining to your intellectual property.';
$lang['Meta_robots']  = 'META Robots';
$lang['Meta_robots_explain']  = '- Controls search engine robots on a per-page basis.<br/ >- all = The bot index all the site (by défault)<br />- none = The bot do not index your site at all<br />- index = your page is indexed<br />- noindex = Your page is\'nt indexed but the bot will follow the link of your page<br />- follow = The bot take note of your the link on your page for indexing them after.<br />- nofollow = The bot do not index the link on your page';
$lang['Meta_distribution']  = 'META Distribution';
$lang['Meta_distribution_explain']  = '- There are three classifications of distribution of your web content:<br/ >- Global (the entire web)<br/ >- Local (reserved for the local IP block of your site)<br/ >- IU (Internal Use, not for public distribution)';
$lang['Meta_date_creation']  = 'META Date-creation-yyyymmdd';
$lang['Meta_date_creation_explain']  = '- Creation date of your site';
$lang['Meta_date_revision']  = 'META Date-revision-yyyymmdd';
$lang['Meta_date_revision_explain']  = '- Date of the last modification';
$lang['Meta_day'] = 'Day :';
$lang['Meta_month'] = 'Month :';
$lang['Meta_year'] = 'Year :';

$lang['Meta_http_equiv_parameters'] = 'Others tags';
$lang['Meta_http_equiv_parameters_explain'] = ' The general syntax of these tags is : <<b>meta http-equiv="xxx" CONTENT="xxx"</b>> If you do not want to use one or several tags, leave empty spaces.';
$lang['Meta_refresh']  = 'META Refresh 1';
$lang['Meta_refresh_explain']  = '- Specifies a delay in seconds before the browser automatically reloads the document. The number is the delay in seconds which the browser will "pause" before the refresh is performed. Enter a number in seconds.';
$lang['Meta_redirect_url']  = 'META Refresh 2';
$lang['Meta_redirect_url_explain']  = '- Specifies a delay in seconds before the browser automatically redirect the document to an URL specified.<br/ > The number before the URL is the delay in seconds which the browser will "pause" before the redirect is performed.';
$lang['Meta_redirect_url_time']  = 'Times (sec):';
$lang['Meta_redirect_url_adress']  = 'Adress (URL):';
$lang['Meta_pragma']  = 'META Pragma';
$lang['Meta_pragma_explain']  = '- Prohibit the record of the page in the cache memory of the browser.<br/ >- To use this tag, enter <i>no-cache</i>, if not, left empty.';
$lang['Meta_language']  = 'META Language';
$lang['Meta_language_explain']  = '- fr : French<br/ >- en : English ou Américain<br/ >- de : Dutch<br/ >- es : Spanish<br/ >- it : Italian<br/ >- pt : portuguese<br/ >- If your site is in several languages, it is not recommend to use this tag.';
}
?>