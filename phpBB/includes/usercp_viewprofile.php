<?php
/***************************************************************************
 *                           usercp_viewprofile.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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
 *
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
	exit;
}

if ( empty($_GET['u']) || $_GET['u'] == ANONYMOUS )
{
	message_die(MESSAGE, $user->lang['No_user_id_specified']);
}
$profiledata = get_userdata(intval($_GET['u']));

$sql = "SELECT *
	FROM " . RANKS_TABLE . "
	ORDER BY rank_special, rank_min";
$result = $db->sql_query($sql);

while ( $row = $db->sql_fetchrow($result) )
{
	$ranksrow[] = $row;
}
$db->sql_freeresult($result);

//
// Output page header and profile_view template
//
$template->set_filenames(array(
	'body' => 'profile_view_body.html')
);
make_jumpbox('viewforum.'.$phpEx);

//
// Calculate the number of days this user has been a member ($memberdays)
// Then calculate their posts per day
//
$regdate = $profiledata['user_regdate'];
$memberdays = max(1, round( ( time() - $regdate ) / 86400 ));
$posts_per_day = $profiledata['user_posts'] / $memberdays;

// Get the users percentage of total posts
if ( $profiledata['user_posts'] )
{
	$sql = "SELECT SUM(forum_posts) AS total
		FROM " . FORUMS_TABLE;
	$result = $db->sql_query($sql);

	$total_posts = ($row = $db->sql_fetchrow($result)) ? $row['total'] : 0;
	$db->sql_freeresult($result);

	$percentage = ( $total_posts ) ? min(100, ($profiledata['user_posts'] / $total_posts) * 100) : 0;
}
else
{
	$percentage = 0;
}

$avatar_img = '';
if ( $profiledata['user_avatar_type'] && $profiledata['user_allowavatar'] )
{
	switch( $profiledata['user_avatar_type'] )
	{
		case USER_AVATAR_UPLOAD:
			$avatar_img = ( $config['allow_avatar_upload'] ) ? '<img src="' . $config['avatar_path'] . '/' . $profiledata['user_avatar'] . '" alt="" border="0" />' : '';
			break;
		case USER_AVATAR_REMOTE:
			$avatar_img = ( $config['allow_avatar_remote'] ) ? '<img src="' . $profiledata['user_avatar'] . '" alt="" border="0" />' : '';
			break;
		case USER_AVATAR_GALLERY:
			$avatar_img = ( $config['allow_avatar_local'] ) ? '<img src="' . $config['avatar_gallery_path'] . '/' . $profiledata['user_avatar'] . '" alt="" border="0" />' : '';
			break;
	}
}

$poster_rank = '';
$rank_image = '';
if ( $profiledata['user_rank'] )
{
	for($i = 0; $i < count($ranksrow); $i++)
	{
		if ( $profiledata['user_rank'] == $ranksrow[$i]['rank_id'] && $ranksrow[$i]['rank_special'] )
		{
			$poster_rank = $ranksrow[$i]['rank_title'];
			$rank_image = ( $ranksrow[$i]['rank_image'] ) ? '<img src="' . $ranksrow[$i]['rank_image'] . '" alt="' . $poster_rank . '" title="' . $poster_rank . '" border="0" /><br />' : '';
		}
	}
}
else
{
	for($i = 0; $i < count($ranksrow); $i++)
	{
		if ( $profiledata['user_posts'] >= $ranksrow[$i]['rank_min'] && !$ranksrow[$i]['rank_special'] )
		{
			$poster_rank = $ranksrow[$i]['rank_title'];
			$rank_image = ( $ranksrow[$i]['rank_image'] ) ? '<img src="' . $ranksrow[$i]['rank_image'] . '" alt="' . $poster_rank . '" title="' . $poster_rank . '" border="0" /><br />' : '';
		}
	}
}

if ( $profiledata['user_viewemail'] || $auth->acl_get('a_') )
{
	$email_uri = ( $config['board_email_form'] ) ? "profile.$phpEx$SID&amp;mode=email&amp;u=" . $profiledata['user_id'] : 'mailto:' . $profiledata['user_email'];

	$email_img = '<a href="' . $email_uri . '">' . $user->img('icon_email', $user->lang['Send_email']) . '</a>';
	$email = '<a href="' . $email_uri . '">' . $user->lang['Send_email'] . '</a>';
}
else
{
	$email_img = '&nbsp;';
	$email = '&nbsp;';
}

$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id";
$profile_img = '<a href="' . $temp_url . '">' . $user->img('icon_profile', $user->lang['Read_profile']) . '</a>';
$profile = '<a href="' . $temp_url . '">' . $user->lang['Read_profile'] . '</a>';

$temp_url = "privmsg.$phpEx$SID&amp;mode=post&amp;u=$user_id";
$pm_img = '<a href="' . $temp_url . '">' . $user->img('icon_pm', $user->lang['Send_private_message']) . '</a>';
$pm = '<a href="' . $temp_url . '">' . $user->lang['Send_private_message'] . '</a>';

$www_img = ( $profiledata['user_website'] ) ? '<a href="' . $profiledata['user_website'] . '" target="_userwww">' . $user->img('icon_www', $user->lang['Visit_website']) . '</a>' : '';
$www = ( $profiledata['user_website'] ) ? '<a href="' . $profiledata['user_website'] . '" target="_userwww">' . $user->lang['Visit_website'] . '</a>' : '';

if ( !empty($profiledata['user_icq']) )
{
	$icq_status_img = '<a href="http://wwp.icq.com/' . $profiledata['user_icq'] . '#pager"><img src="http://web.icq.com/whitepages/online?icq=' . $profiledata['user_icq'] . '&img=5" width="18" height="18" border="0" /></a>';
	$icq_img = '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $profiledata['user_icq'] . '">' . $user->img('icon_icq', $user->lang['ICQ']) . '</a>';
	$icq =  '<a href="http://wwp.icq.com/scripts/search.dll?to=' . $profiledata['user_icq'] . '">' . $user->lang['ICQ'] . '</a>';
}
else
{
	$icq_status_img = '';
	$icq_img = '';
	$icq = '';
}

$aim_img = ( $profiledata['user_aim'] ) ? '<a href="aim:goim?screenname=' . $profiledata['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $user->img('icon_aim', $user->lang['AIM']) . '</a>' : '';
$aim = ( $profiledata['user_aim'] ) ? '<a href="aim:goim?screenname=' . $profiledata['user_aim'] . '&amp;message=Hello+Are+you+there?">' . $user->lang['AIM'] . '</a>' : '';

$temp_url = "profile.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id";
$msn_img = ( $profiledata['user_msnm'] ) ? '<a href="' . $temp_url . '">' . $user->img('icon_msnm', $user->lang['MSNM']) . '</a>' : '';
$msn = ( $profiledata['user_msnm'] ) ? '<a href="' . $temp_url . '">' . $user->lang['MSNM'] . '</a>' : '';

$yim_img = ( $profiledata['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $profiledata['user_yim'] . '&amp;.src=pg">' . $user->img('icon_yim', $user->lang['YIM']) . '</a>' : '';
$yim = ( $profiledata['user_yim'] ) ? '<a href="http://edit.yahoo.com/config/send_webmesg?.target=' . $profiledata['user_yim'] . '&amp;.src=pg">' . $user->lang['YIM'] . '</a>' : '';

$temp_url = "search.$phpEx$SID&amp;search_author=" . urlencode($profiledata['username']) . "&amp;showresults=posts";
$search_img = '<a href="' . $temp_url . '">' . $user->img('icon_search', $user->lang['Search_user_posts']) . '</a>';
$search = '<a href="' . $temp_url . '">' . $user->lang['Search_user_posts'] . '</a>';

//
// Generate page
//
$page_title = $user->lang['Viewing_profile'];
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->assign_vars(array(
	'USERNAME' => $profiledata['username'],
	'JOINED' => $user->format_date($profiledata['user_regdate'], $user->lang['DATE_FORMAT']),
	'POSTER_RANK' => $poster_rank,
	'RANK_IMAGE' => $rank_image,
	'POSTS_PER_DAY' => $posts_per_day,
	'POSTS' => $profiledata['user_posts'],
	'PERCENTAGE' => $percentage . '%',
	'POST_DAY_STATS' => sprintf($user->lang['User_post_day_stats'], $posts_per_day),
	'POST_PERCENT_STATS' => sprintf($user->lang['User_post_pct_stats'], $percentage),

	'SEARCH_IMG' => $search_img,
	'SEARCH' => $search,
	'PM_IMG' => $pm_img,
	'PM' => $pm,
	'EMAIL_IMG' => $email_img,
	'EMAIL' => $email,
	'WWW_IMG' => $www_img,
	'WWW' => $www,
	'ICQ_STATUS_IMG' => $icq_status_img,
	'ICQ_IMG' => $icq_img,
	'ICQ' => $icq,
	'AIM_IMG' => $aim_img,
	'AIM' => $aim,
	'MSN_IMG' => $msn_img,
	'MSN' => $msn,
	'YIM_IMG' => $yim_img,
	'YIM' => $yim,

	'LOCATION' => ( $profiledata['user_from'] ) ? $profiledata['user_from'] : '&nbsp;',
	'OCCUPATION' => ( $profiledata['user_occ'] ) ? $profiledata['user_occ'] : '&nbsp;',
	'INTERESTS' => ( $profiledata['user_interests'] ) ? $profiledata['user_interests'] : '&nbsp;',
	'AVATAR_IMG' => $avatar_img,

	'L_VIEWING_PROFILE' => sprintf($user->lang['Viewing_user_profile'], $profiledata['username']),
	'L_ABOUT_USER' => sprintf($user->lang['About_user'], $profiledata['username']),
	'L_AVATAR' => $user->lang['Avatar'],
	'L_POSTER_RANK' => $user->lang['Poster_rank'],
	'L_JOINED' => $user->lang['Joined'],
	'L_TOTAL_POSTS' => $user->lang['Total_posts'],
	'L_SEARCH_USER_POSTS' => sprintf($user->lang['Search_user_posts'], $profiledata['username']),
	'L_CONTACT' => $user->lang['Contact'],
	'L_EMAIL_ADDRESS' => $user->lang['Email_address'],
	'L_EMAIL' => $user->lang['Email'],
	'L_PM' => $user->lang['Private_Message'],
	'L_ICQ_NUMBER' => $user->lang['ICQ'],
	'L_YAHOO' => $user->lang['YIM'],
	'L_AIM' => $user->lang['AIM'],
	'L_MESSENGER' => $user->lang['MSNM'],
	'L_WEBSITE' => $user->lang['Website'],
	'L_LOCATION' => $user->lang['Location'],
	'L_OCCUPATION' => $user->lang['Occupation'],
	'L_INTERESTS' => $user->lang['Interests'],

	'U_SEARCH_USER' => "search.$phpEx$SID&amp;search_author=" . urlencode($profiledata['username']),

	'S_PROFILE_ACTION' => "profile.$phpEx$SID")
);

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>