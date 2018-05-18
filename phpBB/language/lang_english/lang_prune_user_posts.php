<?php

/*******************************************************************

 Name		: Prune User Posts [English Language]
 Copyright	: 2003, Adam Alkins
 Website	: http://www.rasadam.com
 email		: phpbb at rasadam dot com

 $Id: lang_prune_user_posts.php,v 1.1 2010/10/10 15:09:19 orynider Exp $: 

*******************************************************************/

/*******************************************************************

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the 
Free Software Foundation Inc., 59 Temple Place, Suite 330,
Boston, MA  02111-1307  USA

*******************************************************************/

$lang['Prune_user_posts'] = 'Prune User Posts';
$lang['Prune_explain'] = 'Welcome to the prune user posts Admin module addon for phpBB. This script allows you to prune posts based on a wide range of criteria.';
$lang['Forums_to_prune'] = 'Forums to Prune';
$lang['Forums_to_prune_explain'] = 'Check the box to prune posts in that forum. You can check multiple forums. (Note for large forums: You should only do a couple of forums at a time if you\'re pruning many posts)';
$lang['Users_to_prune'] = 'Users to Prune';
$lang['Username_explain'] = 'Prune posts made by this specific user';
$lang['All_users_explain'] = 'Prune posts by all users';
$lang['Banned_users'] = 'Banned users';
$lang['Banned_users_explain'] = 'Prune posts made by all users that have been banned (as per the banlist)';
$lang['Group'] = 'Group';
$lang['Group_explain'] = 'Prune posts made by users in this specific group';
$lang['IP_explain'] = 'Prune posts made by a specific ip address (xxx.xxx.xxx.xxx), wildcard (xxx.xxx.xxx.*) or range (xxx.xxx.xxx.xxx-yyy.yyy.yyy.yyy). Note: the last quad .255 is considered the range of all the IPs in that quad. If you enter 10.0.0.255, it is just like entering 10.0.0.* (No IP is assigned .255 for that matter, it is reserved). Where you may encounter this is in ranges, 10.0.0.5-10.0.0.255 is the same as "10.0.0.*" . You should really enter 10.0.0.5-10.0.0.254 .';
$lang['Banned_IPs'] = 'Banned IP Addresses';
$lang['Banned_IPs_explain'] = 'Prune posts made by all IPs in the banned list.';
$lang['Guest_posters'] = 'Guest Posters';
$lang['Guest_posters_explain'] = 'Prune posts made by guest posters only (Users not logged in).';
$lang['Date_criteria'] = 'Date Criteria';
$lang['Before'] = 'Before';
$lang['On'] = 'On';
$lang['After'] = 'After';
$lang['the_last'] = 'the last';
$lang['Seconds'] = 'Seconds';
$lang['Minutes'] = 'Minutes';
$lang['By_time_explain'] = 'Prune posts based on the above time. Please note only whole numbers are accepted, there is no reason to use decimals. (If you need .5 days, input 12 and select hours).';
$lang['ddmmyyyy'] = '(dd/mm/yyyy)';
$lang['Date_explain'] = 'Prune posts on above date criteria. Note dates are limited to roughly 1970 - 2038 (4 Bit unix timestamp limit)';
$lang['to'] = 'to';
$lang['Range_explain'] = 'Prune posts between both dates. Dates are subject to above limits.';
$lang['All_posts_explain'] = 'Prune all posts regardless of time.';
$lang['Pruning_options'] = 'Pruning Options';
$lang['Prune_remove_topics'] = 'Remove Topics by User(s)?';
$lang['Prune_remove_topics_explain'] = 'If the user(s) you selected started the topic and others have replied to the topic, would you like the entire topic removed?';
$lang['Exempt_stickies'] = 'Exempt Stickies?';
$lang['Exempt_stickies_explain'] = 'Do not prune posts in stickied topics.';
$lang['Exempt_announcements'] = 'Exempt Announcements?';
$lang['Exempt_announcements_explain'] = 'Do not prune posts in Announcements.';
$lang['Exempt_open'] = 'Exempt Open Topics?';
$lang['Exempt_open_explain'] = 'Do not prune posts in topics that are still open. (e.g. Select this as yes to prune locked topics only)';
$lang['Exempt_polls'] = 'Exempt Polls?';
$lang['Exempt_polls_explain'] = 'Do not prune posts in topics with Polls.';
$lang['Adjust_post_counts'] = 'Adjust Post Counts?';
$lang['Adjust_post_counts_explain'] = 'Update user\'s post counts to reflect posts that were deleted.';
$lang['Update_search'] = 'Update Search Tables?';
$lang['Update_search_explain'] = 'Whether posts should be removed from the search tables. If you select No, you will need to manually rebuild the search tables. You should only select No if you have a very large board on a very slow server pruning many posts.';

$lang['Prune_invalid_mode'] = 'Unable to Prune - Invalid mode';
$lang['Prune_invalid_IP'] = 'Invalid IP Address entered';
$lang['Prune_invalid_date'] = 'Invalid date entered.';
$lang['Prune_invalid_range'] = 'Invalid IP Range entered';
$lang['No_banned_IPs'] = 'There are no banned IP Addresses';
$lang['No_forums_selected'] = 'Unable to start pruning - No forums were selected';
$lang['Prune_no_posts'] = 'Unable to start pruning - No posts were found to prune';

$lang['Prune_finished'] = 'Pruning successfully completed.<br /><br />Return to the <a href="%s">Prune User Posts</a> page.<br /><br />Return to the <a href="%s">Admin Index</a>.';


?>