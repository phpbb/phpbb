<?php
/***************************************************************************
 *						lang_extend_announces.php [English]
 *						-------------------------
 *	begin				: 28/09/2003
 *	copyright			: Ptirhiik
 *	email				: ptirhiik@clanmckeen.com
 *
 *	version				: 1.0.0 - 28/09/2003
 *
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
	$lang['Lang_extend_announces'] = 'Announces Suite';
}

$lang['Board_announcement']						= 'Board Announcements';
$lang['announcement_duration']					= 'Announcement duration';
$lang['announcement_duration_explain']			= 'This is the number of days an announcement remains. Use -1 in order to set it permanent';
$lang['Announce_settings']						= 'Announcements';
$lang['announcement_date_display']				= 'Display announcement dates';
$lang['announcement_display']					= 'Display board announcements on index';
$lang['announcement_display_forum']				= 'Display board announcements on forums';
$lang['announcement_split']						= 'Split announcement type in the board announcement box';
$lang['announcement_forum']						= 'Display the forum name under the announcement title in the board announcement box';
$lang['announcement_prune_strategy']			= 'Announcement prune strategy';
$lang['announcement_prune_strategy_explain']	= 'This is what will be the type of the announcement topic after being pruned';

$lang['Global_announce']						= 'Global announce';
$lang['Sorry_auth_global_announce']				= 'Sorry, but only %s can post global announcements in this forum.';
$lang['Post_Global_Announcement']				= 'Global Announcement';
$lang['Topic_Global_Announcement']				= '<b>Global Announcement:</b>';

$lang['Announces_from_to']						= '(from %s to %s)';

?>