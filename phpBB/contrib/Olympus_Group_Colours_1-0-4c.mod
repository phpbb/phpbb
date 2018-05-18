##############################################################
## MOD Title: Olympus Group Colours
## MOD Author: smithy_dll < n/a > (David Smith) http://phpbbstuff.ddblog.org
## MOD Description: This MOD for phpBB2.0 implements user group colours simillar to phpBB olympus (3.0).
## MOD Version: 1.0.4c
## 
## Installation Level: Moderate
## Installation Time: 47 minutes
## Files To Edit: groupcp.php
##                index.php
##                viewtopic.php
##                admin/admin_groups.php
##                admin/admin_users.php
##                includes/page_header.php
##                includes/usercp_viewprofile.php
##                language/lang_english/lang_admin.php
##                language/lang_english/lang_main.php
##                templates/subSilver/index_body.tpl
##                templates/subSilver/admin/group_edit_body.tpl
##                templates/subSilver/admin/user_edit_body.tpl
## Included Files: 
## Generator: MOD Studio 3.0 Beta 2 [mod functions 0.4.1818.26949]
##############################################################
## For Security Purposes, Please Check: http://www.phpbb.com/mods/ for the 
## latest version of this MOD. Downloading this MOD from other sites could cause malicious code 
## to enter into your phpBB Forum. As such, phpBB will not offer support for MOD's not offered 
## in our MOD-Database, located at: http://www.phpbb.com/mods/ 
##############################################################
## Author Notes: 
## 
## First assign a group a colour in the group administration panel, then edit the default 
## group a user belongs to in the administration panel for the user's name to appear 
## coloured on the index, viewtopic, viewprofile and groupcp.
## 
## A user can belong two multiple coloured groups, but can only belong to one default 
## group of which they inherit the groups colour.
## 
## You can also set wether or not a group shows up in the Legend on the forum index.
##
## Hidden groups will be shown if you tell them to, however it is up to the admin wether
## they give a hidden group a colour and tell it to display in the legend.
## 
##############################################################
## MOD History:
## 
##   2005-03-06 - Version 1.0.4
## 
##      - Fixed groupcp display bug, and legend display bug
##
##   2005-02-27 - Version 1.0.3
## 
##      - Removed some Olympus specific code and cleaned up an SQL edit
## 
##   2005-02-26 - Version 1.0.2
## 
##      - Added missing ALTER TABLE column group_id on phpbb_users
## 
##   2005-02-26 - Version 1.0.1
## 
##      - Fixed a few bugs in the MOD Template file
## 
##   2005-02-26 - Version 1.0.0
## 
##      - The first release of the Olympus style group colour MOD for phpBB2.0.
## 
##############################################################
## Before Adding This MOD To Your Forum, You Should Back Up All Files Related To This MOD 
##############################################################

#
#-----[ SQL ]------------------------------------------
#
ALTER TABLE phpbb_groups ADD group_colour VARCHAR(6) NOT NULL;
ALTER TABLE phpbb_groups ADD group_legend TINYINT(1) DEFAULT '0' NOT NULL;
ALTER TABLE phpbb_users ADD user_colour VARCHAR(6) NOT NULL;
ALTER TABLE phpbb_users ADD group_id MEDIUMINT(8) NOT NULL;

#
#-----[ OPEN ]------------------------------------------
#
groupcp.php
#
#-----[ FIND ]------------------------------------------
#
	$sql = "SELECT username, user_id, user_viewemail, user_posts, user_regdate, user_from, user_website, user_email, user_icq, user_aim, user_yim, user_msnm  

#
#-----[ IN-LINE FIND ]------------------------------------------
#
user_msnm
#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
, user_colour
#
#-----[ FIND ]------------------------------------------
#
	$sql = "SELECT u.username, u.user_id, u.user_viewemail, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm, ug.user_pending 

#
#-----[ IN-LINE FIND ]------------------------------------------
#
user_msnm
#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
, u.user_colour
#
#-----[ FIND ]------------------------------------------
#
	$sql = "SELECT u.username, u.user_id, u.user_viewemail, u.user_posts, u.user_regdate, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_msnm

#
#-----[ IN-LINE FIND ]------------------------------------------
#
user_msnm
#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
, u.user_colour 
#
#-----[ FIND ]------------------------------------------
#
	$s_hidden_fields .= '';

#
#-----[ AFTER, ADD ]------------------------------------------
#
	
	$group_name = $group_info['group_name'];
	
	if ($group_info['group_colour'])
	{
		$group_name = '<span style="color: #' . $group_info['group_colour'] . '">' . $group_name . '</span>';
	}
	
	if ($group_moderator['user_colour'])
	{
		$username = '<b style="color: #' . $group_moderator['user_colour'] . ';">' . $username . '</b>';
	}

#
#-----[ FIND ]------------------------------------------
#
		'GROUP_NAME' => $group_info['group_name'],

#
#-----[ REPLACE WITH ]------------------------------------------
#
		'GROUP_NAME' => $group_name,

#
#-----[ FIND ]------------------------------------------
#
			$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];
			

#
#-----[ AFTER, ADD ]------------------------------------------
#
			if ($group_members[$i]['user_colour'])
			{
				$username = '<b style="color: #' . $group_members[$i]['user_colour'] . ';">' . $username . '</b>';
			}


#
#-----[ FIND ]------------------------------------------
#
				$row_class = ( !($i % 2) ) ? $theme['td_class1'] : $theme['td_class2'];

#
#-----[ AFTER, ADD ]------------------------------------------
#
				
				if ($modgroup_pending_list[$i]['user_colour'])
				{
					$username = '<b style="color: #' . $modgroup_pending_list[$i]['user_colour'] . ';">' . $username . '</b>';
				}

#
#-----[ OPEN ]------------------------------------------
#
index.php
#
#-----[ FIND ]------------------------------------------
#
else
{
	$mark_read = '';
}


#
#-----[ AFTER, ADD ]------------------------------------------
#
// Grab group details for legend display
$sql = 'SELECT group_id, group_name, group_colour, group_type
	FROM ' . GROUPS_TABLE . '
	WHERE group_legend = 1 AND group_single_user = 0';
$result = $db->sql_query($sql);

$legend = '';
while ($row = $db->sql_fetchrow($result))
{
	$legend .= ', <a style="color:#' . $row['group_colour'] . '" href="groupcp.' . $phpEx . '?sid=' . $userdata['session_id'] . '&amp;' . POST_GROUPS_URL . '=' . $row['group_id'] . '">' . $row['group_name'] . '</a>';
}
$db->sql_freeresult($result);


#
#-----[ FIND ]------------------------------------------
#
		'NEWEST_USER' => sprintf($lang['Newest_user'], '<a href="' . append_sid("profile.$phpEx?mode=viewprofile&amp;" . POST_USERS_URL . "=$newest_uid") . '">', $newest_user, '</a>'), 

#
#-----[ AFTER, ADD ]------------------------------------------
#
		'LEGEND' => $legend,

#
#-----[ FIND ]------------------------------------------
#
		'L_ONLINE_EXPLAIN' => $lang['Online_explain'], 

#
#-----[ AFTER, ADD ]------------------------------------------
#
		'L_LEGEND' => $lang['Legend'],

#
#-----[ OPEN ]------------------------------------------
#
viewtopic.php
#
#-----[ FIND ]------------------------------------------
#
$sql = "SELECT u.username, u.user_id, u.user_posts, u.user_from, u.user_website, u.user_email, u.user_icq, u.user_aim, u.user_yim, u.user_regdate, u.user_msnm, u.user_viewemail, u.user_rank, u.user_sig, u.user_sig_bbcode_uid, u.user_avatar, u.user_avatar_type, u.user_allowavatar, u.user_allowsmile, p.*,  pt.post_text, pt.post_subject, pt.bbcode_uid

#
#-----[ IN-LINE FIND ]------------------------------------------
#
user_allowsmile, 
#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
u.user_colour, 
#
#-----[ FIND ]------------------------------------------
#
	$poster = ( $poster_id == ANONYMOUS ) ? $lang['Guest'] : $postrow[$i]['username'];

#
#-----[ AFTER, ADD ]------------------------------------------
#
	
	if ($postrow[$i]['user_colour'])
	{
		$poster = '<b style="color: #' . $postrow[$i]['user_colour'] . ';">' . $poster . '</b>';
	}

#
#-----[ OPEN ]------------------------------------------
#
admin/admin_groups.php
#
#-----[ FIND ]------------------------------------------
#
	$group_hidden = ( $group_info['group_type'] == GROUP_HIDDEN ) ? ' checked="checked"' : '';

#
#-----[ AFTER, ADD ]------------------------------------------
#
	
	$group_legend_yes = ( $group_info['group_legend'] == 1 ) ? ' checked="checked"' : '';
	$group_legend_no = ( $group_info['group_legend'] == 0 ) ? ' checked="checked"' : '';

#
#-----[ FIND ]------------------------------------------
#
		'GROUP_MODERATOR' => $group_moderator, 

#
#-----[ AFTER, ADD ]------------------------------------------
#
	'GROUP_COLOUR' => $group_info['group_colour'],

#
#-----[ FIND ]------------------------------------------
#
		'L_YES' => $lang['Yes'],

#
#-----[ AFTER, ADD ]------------------------------------------
#
		'L_NO' => $lang['No'],
		'L_GROUP_COLOUR' => $lang['group_colour'],
		'L_GROUP_SHOW_LEGEND' => $lang['group_show_legend'],

#
#-----[ FIND ]------------------------------------------
#
		'S_GROUP_HIDDEN_CHECKED' => $group_hidden,

#
#-----[ AFTER, ADD ]------------------------------------------
#
		'S_GROUP_LEGEND_YES' => $group_legend_yes,
		'S_GROUP_LEGEND_NO' => $group_legend_no,

#
#-----[ FIND ]------------------------------------------
#
		$group_name = isset($HTTP_POST_VARS['group_name']) ? trim($HTTP_POST_VARS['group_name']) : '';

#
#-----[ AFTER, ADD ]------------------------------------------
#
		$group_colour = isset($HTTP_POST_VARS['group_colour']) ? trim($HTTP_POST_VARS['group_colour']) : '';
		$group_legend = isset($HTTP_POST_VARS['group_legend']) ? intval($HTTP_POST_VARS['group_legend']) :0;

#
#-----[ FIND ]------------------------------------------
#
				SET group_type = $group_type, group_name = '" . str_replace("\'", "''", $group_name) . "', group_description = '" . str_replace("\'", "''", $group_description) . "', group_moderator = $group_moderator 

#
#-----[ IN-LINE FIND ]------------------------------------------
#
group_moderator = $group_moderator 

#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
, group_colour = '" . str_replace("\'", "''", $group_colour) . "', group_legend = $group_legend 

#
#-----[ FIND ]------------------------------------------
#
			$sql = "INSERT INTO " . GROUPS_TABLE . " (group_type, group_name, group_description, group_moderator, group_single_user) 
				VALUES ($group_type, '" . str_replace("\'", "''", $group_name) . "', '" . str_replace("\'", "''", $group_description) . "', $group_moderator,	'0')";

#
#-----[ IN-LINE FIND ]------------------------------------------
#
group_single_user

#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
, group_colour, group_legend

#
#-----[ IN-LINE FIND ]------------------------------------------
#
'0'

#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
, '" . str_replace("\'", "''", $group_colour) . "', $group_legend

#
#-----[ OPEN ]------------------------------------------
#
admin/admin_users.php
#
#-----[ FIND ]------------------------------------------
#
		$user_rank = ( !empty($HTTP_POST_VARS['user_rank']) ) ? intval( $HTTP_POST_VARS['user_rank'] ) : 0;

#
#-----[ AFTER, ADD ]------------------------------------------
#
		$user_group = ( !empty($HTTP_POST_VARS['user_group']) ) ? intval( $HTTP_POST_VARS['user_group'] ) : 0;

#
#-----[ FIND ]------------------------------------------
#
		// Update entry in DB
		//
		if( !$error )
		{

#
#-----[ AFTER, ADD ]------------------------------------------
#
			$group_colour = '';
			$sql = "SELECT group_colour FROM " . GROUPS_TABLE . " WHERE group_id = $user_group;";
			if ( !($result = $db->sql_query($sql)) )
			{
				message_die(GENERAL_ERROR, 'Could not obtain groups data', '', __LINE__, __FILE__, $sql);
			}
		
			if( $row = $db->sql_fetchrow($result) )
			{
				$group_colour = $row['group_colour'];
			}
			

#
#-----[ FIND ]------------------------------------------
#
				SET " . $username_sql . $passwd_sql . "user_email = '" . str_replace("\'", "''", $email) . "', user_icq = '" . str_replace("\'", "''", $icq) . "', user_website = '" . str_replace("\'", "''", $website) . "', user_occ = '" . str_replace("\'", "''", $occupation) . "', user_from = '" . str_replace("\'", "''", $location) . "', user_interests = '" . str_replace("\'", "''", $interests) . "', user_sig = '" . str_replace("\'", "''", $signature) . "', user_viewemail = $viewemail, user_aim = '" . str_replace("\'", "''", $aim) . "', user_yim = '" . str_replace("\'", "''", $yim) . "', user_msnm = '" . str_replace("\'", "''", $msn) . "', user_attachsig = $attachsig, user_sig_bbcode_uid = '$signature_bbcode_uid', user_allowsmile = $allowsmilies, user_allowhtml = $allowhtml, user_allowavatar = $user_allowavatar, user_allowbbcode = $allowbbcode, user_allow_viewonline = $allowviewonline, user_notify = $notifyreply, user_allow_pm = $user_allowpm, user_notify_pm = $notifypm, user_popup_pm = $popuppm, user_lang = '" . str_replace("\'", "''", $user_lang) . "', user_style = $user_style, user_timezone = $user_timezone, user_dateformat = '" . str_replace("\'", "''", $user_dateformat) . "', user_active = $user_status, user_rank = $user_rank" . $avatar_sql . "

#
#-----[ IN-LINE FIND ]------------------------------------------
#
$user_rank

#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
, group_id = $user_group, user_colour = '$group_colour' 

#
#-----[ FIND ]------------------------------------------
#
			$rank_select_box .= '<option value="' . $rank_id . '"' . $selected . '>' . $rank . '</option>';
		}

#
#-----[ AFTER, ADD ]------------------------------------------
#
		
		$sql = "SELECT g.* FROM " . USER_GROUP_TABLE . " ug, " . GROUPS_TABLE . " g
			WHERE ug.group_id = g.group_id AND user_id = $user_id AND group_single_user = 0;";
		if ( !($result = $db->sql_query($sql)) )
		{
			message_die(GENERAL_ERROR, 'Could not obtain groups data', '', __LINE__, __FILE__, $sql);
		}
		
		$group_select_box = '<option value="0">' . $lang['No_assigned_group'] . '</option>';
		while( $row = $db->sql_fetchrow($result) )
		{
			$group_name = $row['group_name'];
			$group_id = $row['group_id'];
			
			$selected = ( $this_userdata['group_id'] == $group_id ) ? ' selected="selected"' : '';
			if ($row['group_colour'])
			{
				$group_colour = $row['group_colour'];
				$group_select_box .= '<option style="color: #' . $group_colour . '" value="' . $group_id . '"' . $selected . '>' . $group_name . '</option>';
			}
		}

#
#-----[ FIND ]------------------------------------------
#
			'RANK_SELECT_BOX' => $rank_select_box,

#
#-----[ AFTER, ADD ]------------------------------------------
#
			'GROUP_SELECT_BOX' => $group_select_box,

#
#-----[ FIND ]------------------------------------------
#
			'L_SELECT_RANK' => $lang['Rank_title'],

#
#-----[ AFTER, ADD ]------------------------------------------
#
			'L_MAIN_GROUP' => $lang['main_group'],

#
#-----[ OPEN ]------------------------------------------
#
includes/page_header.php
#
#-----[ FIND ]------------------------------------------
#
	$sql = "SELECT u.username, u.user_id, u.user_allow_viewonline, u.user_level, s.session_logged_in, s.session_ip

#
#-----[ IN-LINE FIND ]------------------------------------------
#
user_level, 
#
#-----[ IN-LINE AFTER, ADD ]------------------------------------------
#
u.user_colour, 
#
#-----[ FIND ]------------------------------------------
#
				if ( $row['user_level'] == ADMIN )

#
#-----[ REPLACE WITH ]------------------------------------------
#
				if ( $row['user_colour'] )
				{
					$row['username'] = '<b>' . $row['username'] . '</b>';
					$style_color = 'style="color:#' . $row['user_colour'] . '"';
				}
				elseif ( $row['user_level'] == ADMIN )

#
#-----[ OPEN ]------------------------------------------
#
includes/usercp_viewprofile.php
#
#-----[ FIND ]------------------------------------------
#
	$u_search_author = urlencode(str_replace(array('&amp;', '&#039;', '&quot;', '&lt;', '&gt;'), array('&', "'", '"', '<', '>'), $profiledata['username']));
}


#
#-----[ AFTER, ADD ]------------------------------------------
#
$username = $profiledata['username'];

if ($profiledata['user_colour'])
{
	$username = '<b style="color: #' . $profiledata['user_colour'] . ';">' . $username . '</b>';
}


#
#-----[ FIND ]------------------------------------------
#
	'USERNAME' => $profiledata['username'],

#
#-----[ REPLACE WITH ]------------------------------------------
#
	'USERNAME' => $username,

#
#-----[ OPEN ]------------------------------------------
#
language/lang_english/lang_admin.php
#
#-----[ FIND ]------------------------------------------
#
//
// That's all Folks!

#
#-----[ BEFORE, ADD ]------------------------------------------
#
//
// Olympus Group Colour MOD
//
$lang['group_colour'] = 'Group colour';
$lang['group_show_legend'] = 'Show group in Legend';
$lang['main_group'] = 'Main Group Membership';
$lang['No_assigned_group'] = 'No main group assigned';

#
#-----[ OPEN ]------------------------------------------
#
language/lang_english/lang_main.php
#
#-----[ FIND ]------------------------------------------
#
//
// That's all, Folks!

#
#-----[ BEFORE, ADD ]------------------------------------------
#
//
// Olympus Group Colour MOD
//
$lang['Legend'] = 'Legend';

#
#-----[ OPEN ]------------------------------------------
#
templates/subSilver/index_body.tpl
#
#-----[ FIND ]------------------------------------------
#
	<td class="row1" align="center" valign="middle" rowspan="2"><img src="templates/subSilver/images/whosonline.gif" alt="{L_WHO_IS_ONLINE}" /></td>

#
#-----[ IN-LINE FIND ]------------------------------------------
#
rowspan="2"
#
#-----[ IN-LINE REPLACE WITH ]------------------------------------------
#
rowspan="3"
#
#-----[ FIND ]------------------------------------------
#
{LOGGED_IN_USER_LIST}</span></td>
  </tr>

#
#-----[ REPLACE WITH ]------------------------------------------
#
	<td class="row1" align="left"><span class="gensmall">{TOTAL_USERS_ONLINE}<br />{RECORD_USERS}<br />{LOGGED_IN_USER_LIST}</span></td>
  </tr>
  <tr>
    <td class="row1"><b class="gensmall">{L_LEGEND} :: {L_WHOSONLINE_ADMIN}, {L_WHOSONLINE_MOD}{LEGEND}</b></td>
  </tr>

#
#-----[ OPEN ]------------------------------------------
#
templates/subSilver/admin/group_edit_body.tpl
#
#-----[ FIND ]------------------------------------------
#
		<input type="radio" name="group_type" value="{S_GROUP_OPEN_TYPE}" {S_GROUP_OPEN_CHECKED} /> {L_GROUP_OPEN} &nbsp;&nbsp;<input type="radio" name="group_type" value="{S_GROUP_CLOSED_TYPE}" {S_GROUP_CLOSED_CHECKED} />	{L_GROUP_CLOSED} &nbsp;&nbsp;<input type="radio" name="group_type" value="{S_GROUP_HIDDEN_TYPE}" {S_GROUP_HIDDEN_CHECKED} />	{L_GROUP_HIDDEN}</td> 
	</tr>

#
#-----[ AFTER, ADD ]------------------------------------------
#
	<tr>
	  <td class="row1" width="38%"><span class="gen">{L_GROUP_COLOUR}:</span></td>
	  <td class="row2" width="62%"> 
	    <input type="text" class="post" name="group_colour" maxlength="6" size="6" value="{GROUP_COLOUR}" /></td>
	</tr>
	<tr>
	  <td class="row1" width="38%"><span class="gen">{L_GROUP_SHOW_LEGEND}:</span></td>
	  <td class="row2" width="62%"> 
	  <input type="radio" class="post" name="group_legend" value="1" {S_GROUP_LEGEND_YES} /> {L_YES} &nbsp; <input type="radio" class="post" name="group_legend" value="0" {S_GROUP_LEGEND_NO} /> {L_NO}</td>
	</tr>

#
#-----[ OPEN ]------------------------------------------
#
templates/subSilver/admin/user_edit_body.tpl
#
#-----[ FIND ]------------------------------------------
#
		<td class="row2"><select name="user_rank">{RANK_SELECT_BOX}</select></td>
	</tr>

#
#-----[ AFTER, ADD ]------------------------------------------
#
	<tr>
		<td class="row1"><span class="gen">{L_MAIN_GROUP}</span></td>
		<td class="row2"><select name="user_group">{GROUP_SELECT_BOX}</select></td>
	</tr>

#
#-----[ SAVE/CLOSE ALL FILES ]------------------------------------------
#
# EoM