<?php
/***************************************************************************
 *						lang_extend_topic_calendar.php [English]
 *						------------------------------
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
	$lang['Lang_extend_topic_calendar'] = 'Topic Calendar';
}

$lang['Calendar']				= 'Calendar';
$lang['Calendar_scheduler']		= 'Scheduler';
$lang['Calendar_event']			= 'Calendar event';
$lang['Calendar_from_to']		= 'From %s to %s (included)';
$lang['Calendar_time']			= '%s';
$lang['Calendar_duration']		= 'During';

$lang['Calendar_settings']		= 'Calendar settings';
$lang['Calendar_week_start']	= 'First day of the week';
$lang['Calendar_header_cells']	= 'Number of cells to display on the board header (0 for no display)';
$lang['Calendar_title_length']	= 'Length of the title displayed in the calendar cells';
$lang['Calendar_text_length']	= 'Length of the text displayed in the overview windows';
$lang['Calendar_display_open']	= 'Display the calendar row on the board header opened';
$lang['Calendar_nb_row']		= 'Number of row per day on the board header';
$lang['Calendar_birthday']		= 'Display birthday in the calendar';
$lang['Calendar_forum']			= 'Display the forum name under the topic title in the scheduler';

$lang['Sorry_auth_cal']			= 'Sorry, but only %s can post calendar events in this forum.';
$lang['Date_error']				= 'day %d, month %d, year %d is not a valid date';

$lang['Event_time']				= 'Event time';
$lang['Minutes']				= 'Minutes';
$lang['Today']					= 'Today';
$lang['All_events']				= 'All events';

$lang['Rules_calendar_can']		= 'You <b>can</b> post calendar events in this forum';
$lang['Rules_calendar_cannot']	= 'You <b>cannot</b> post calendar events in this forum';
?>