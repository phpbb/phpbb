<?php
/***************************************************************************
 *                                posting.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
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

define('IN_PHPBB', true);
$phpbb_root_path = './';
include($phpbb_root_path . 'extension.inc');
include($phpbb_root_path . 'common.'.$phpEx);
include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);

// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);
// End session management

// Grab all data
extract($_GET);
extract($_POST);

// Some vars need their names changing and type imposing
$int_vars = array(
	'f' => 'forum_id',
	'p' => 'post_id',
	't' => 'topic_id',
);

foreach ( $int_vars as $in_var => $out_var)
{
	$$out_var = ( isset($$in_var) ) ? intval($$in_var) : false;
}

// Was cancel pressed? If so then redirect to the appropriate page
if ( !empty($cancel) )
{
	$redirect = ( $p ) ? "viewtopic.$phpEx$SID&p=$p#$p" : ( ( $t ) ? "viewtopic.$phpEx$SID&t=$t" : ( ( $f ) ? "viewforum.$phpEx$SID&f=$f" : "index.$phpEx$SID" ) );
	redirect($redirect);
}

// If the mode is set to topic review then output that review ...
switch ($mode)
{
	case 'topicreview':
//		require($phpbb_root_path . 'includes/topic_review.'.$phpEx);
//		topic_review($topic_id, false);
		break;

	case 'smilies':
		generate_smilies('window');
		break;
}


// Set toggles for various options
if ( !$board_config['allow_html'] )
{
	$html_on = 0;
}
else
{
	$html_on = ( $post || $refresh ) ? ( ( !empty($disable_html) ) ? 0 : TRUE ) : ( ( !$user->data['user_id'] ) ? $board_config['allow_html'] : $user->data['user_allowhtml'] );
}

if ( !$board_config['allow_bbcode'] )
{
	$bbcode_on = 0;
}
else
{
	$bbcode_on = ( $post || $refresh ) ? ( ( !empty($disable_bbcode) ) ? 0 : TRUE ) : ( ( !$user->data['user_id'] ) ? $board_config['allow_bbcode'] : $user->data['user_allowbbcode'] );
}

$magic_urls_on = ( $post || $refresh ) ? ( ( !empty($disable_magic_url) ) ? 0 : TRUE ) : TRUE;

if ( !$board_config['allow_smilies'] )
{
	$smilies_on = 0;
}
else
{
	$smilies_on = ( $post || $refresh ) ? ( ( !empty($disable_smilies) ) ? 0 : TRUE ) : ( ( !$user->data['user_id'] ) ? $board_config['allow_smilies'] : $user->data['user_allowsmile'] );
}

$attach_sig = ( $post || $refresh ) ? ( ( !empty($attach_sig) ) ? TRUE : 0 ) : ( ( !$user->data['user_id'] ) ? 0 : $user->data['user_attachsig'] );
//
// FLAGS
// -----


// ---------
// POST INFO
//

// What is all this following SQL for? Well, we need to know
// some basic information in all cases before we do anything.
switch ($mode)
{
	case 'post':
		break;
	case 'reply':
		if ( empty($topic_id) )
		{
			trigger_error($user->lang['No_topic_id']);
		}

		$sql = "SELECT *
			FROM " . TOPICS_TABLE . "
			WHERE topic_id = $topic_id";
		break;
	case 'quote':
	case 'edit':
	case 'delete':
		if ( empty($post_id) )
		{
			trigger_error($user->lang['No_post_id']);
		}

		$sql = "SELECT t.*, p.*, pt.*
			FROM " . POSTS_TABLE . " p, " . POSTS_TEXT_TABLE . " pt, " . TOPICS_TABLE . " t, " . USERS_TABLE . " u
			WHERE p.post_id = $post_id
				AND t.topic_id = p.topic_id
				AND pt.post_id = p.post_id
				AND u.user_id = p.poster_id";
		break;

	default:
		trigger_error($user->lang['No_valid_mode']);
}

if ( $sql != '' )
{
	$result = $db->sql_query($sql);

	extract($db->sql_fetchrow($result));
	$db->sql_freeresult($result);
}
//
// POST INFO
// ---------


// ACL CHECK
if (!$auth->acl_get('f_' . $mode, $forum_id))
{
	trigger_error($user->lang['User_cannot_' . $mode]);
}

// EDIT TIME CHECK
if (($mode == 'edit' || $mode == 'delete') && !empty($board_config['edit_time']) && $post_time < time() - $board_config['edit_time'])
{
	trigger_error($user->lang['Cannot_edit_time']);
}



// --------------
// PROCESS SUBMIT
//
if ( isset($post) )
{

	// First check if message has changed (if editing), if not
	// don't parse at all else ...
	//
	// Need to parse message, parse search words, parse polls, parse attachments,
	// check whether forum is moderated or if msg is being saved (and if it is
	// whether user has run out of save quota) if not topic/forum needs syncing,
	// if replying notifications need sending as appropriate.

//	$mtime = explode(' ', microtime());
//	$starttime = $mtime[1] + $mtime[0];

	$err_msg = '';
	$current_time = time();
	$message_md5 = md5($message);

	// Check checksum
	if ($mode != 'edit' || $message_md5 != $post_checksum)
	{
		// Parse message
		$parse_msg = new parse_message();

		if(($result = $parse_msg->parse($message, $html_on, $bbcode_on, $bbcode_uid, $magic_urls_on, $smilies_on)) != '')
		{
			$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result;
		}
	}

	if ($mode != 'edit')
	{
		// Flood check
		$where_sql = ($user->data['user_id'] == ANONYMOUS) ? "poster_ip = '$user->ip'" : 'poster_id = ' . $user->data['user_id'];
		$sql = "SELECT MAX(post_time) AS last_post_time
			FROM " . POSTS_TABLE . "
			WHERE $where_sql";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			if (intval($row['last_post_time']) && ($current_time - intval($row['last_post_time'])) < intval($board_config['flood_interval']))
			{
				$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['Flood_Error'];
			}
		}
	}

	// Validate username
	if (($username != '' && $user->data['user_id'] == ANONYMOUS) || ($mode == 'edit' && $post_username != ''))
	{
		require_once($phpbb_root_path . 'includes/functions_validate.'.$phpEx);

		$username = strip_tags(htmlspecialchars($username));
		$result = validate_username($username);
		if ( $result['error'] )
		{
			$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $result['error_msg'];
		}

	}

	// Parse subject
	if (($subject = htmlspecialchars($subject)) == '' && ($mode == 'post' || ($mode == 'edit' && $topic_first_post_id == $post_id)))
	{
		$err_msg .= ((!empty($err_msg)) ? '<br />' : '') . $user->lang['Empty_subject'];
	}

	if ($err_msg == '')
	{
		$db->sql_transaction();

		if ($mode == 'post' || ($mode == 'edit' && $topic_first_post_id == $post_id))
		{
			$sql = ($mode == 'post') ? 'INSERT INTO ' . TOPICS_TABLE : 'UPDATE ' . TOPICS_TABLE . ' SET WHERE topic_id = ' . intval($topic_id);
			$topic_sql = array(
				'topic_title' 	=> $subject,
				'topic_poster' 	=> intval($user->data['user_id']),
				'topic_time' 	=> $current_time,
				'forum_id' 		=> intval($forum_id),
				'topic_type' 	=> intval($type),
				'topic_icon'	=> intval($icon),
				'topic_approved'=> ($forum_moderated) ? 0 : 1,
			);
			$db->sql_query_array($sql, $topic_sql);

			$topic_id = ($mode == 'post') ? $db->sql_nextid() : $topic_id;
		}

		$enable_sig = $enable_bbcode = $enable_html = $enable_smilies = $enable_magic_url = $bbcode_uid = 1;

		$sql = ($mode == 'edit') ? 'UPDATE ' . POSTS_TABLE . ' SET WHERE post_id = ' . $post_id : 'INSERT INTO ' . POSTS_TABLE;
		$post_sql = array(
			'topic_id' 			=> intval($topic_id),
			'forum_id' 			=> intval($forum_id),
			'poster_id' 		=> ($mode == 'edit') ? intval($poster_id) : intval($user->data['user_id']),
			'post_username'		=> ($username != '') ? $username : '',
			'poster_ip' 			=> $user->ip,
			'post_time' 		=> $current_time,
			'post_approved' 	=> ($forum_moderated) ? 0 : 1,
			'post_edit_time' 	=> ($mode == 'edit') ? $current_time : 0,
			'post_edit_count' 	=> ($mode == 'edit') ? 'post_edit_count + 1' : 0,
			'enable_sig' 		=> $enable_sig,
			'enable_bbcode' 	=> $enable_bbcode,
			'enable_html' 		=> $enable_html,
			'enable_smilies' 	=> $enable_smilies,
			'enable_magic_url' 	=> $enable_magic_url,
		);
		$db->sql_query_array($sql, $post_sql);

		$post_id = ($mode == 'edit') ? $post_id : $db->sql_nextid();

		$sql = ($mode == 'edit') ? 'UPDATE ' . POSTS_TEXT_TABLE . ' SET WHERE post_id = ' . $post_id : 'INSERT INTO ' . POSTS_TEXT_TABLE;
		$post_text_sql = array(
			'post_subject'	=> htmlspecialchars($subject),
			'bbcode_uid'	=> $bbcode_uid,
			'post_id' 		=> intval($post_id),
		);

		if ($mode != 'edit' || $message_md5 != $post_checksum)
		{
			$post_text_sql = array_merge($post_text_sql, array(
				'post_checksum' => $message_md5,
				'post_text' 	=> $message,
			));
		}
		$db->sql_query_array($sql, $post_text_sql);

		// Fulltext parse
		if ($mode != 'edit' || $message_md5 != $post_checksum)
		{
//			$search = new fulltext_search();
//			$result = $search->add($p, $message, $subject, $post_text, $post_subject);
		}

		// Sync forums, topics and users ...
		if ($mode != 'edit')
		{
			$forum_topics_sql = ($mode == 'post') ? ', forum_topics = forum_topics + 1' : '';
			$forum_sql = array(
				'forum_last_post_id' => intval($post_id),
				'forum_last_post_time' => $current_time,
				'forum_last_poster_id' => intval($user->data['user_id']),
				'forum_last_poster_name' => ($username != '') ? $username : '',
			);
			$db->sql_query_array('UPDATE ' . FORUMS_TABLE . ' SET , forum_posts = forum_posts + 1' . $forum_topics_sql . ' WHERE forum_id = ' . intval($forum_id), $forum_sql);

			$topic_sql = array(
				'topic_last_post_id' => intval($post_id),
				'topic_last_post_time' => $current_time,
				'topic_last_poster_id' => intval($user->data['user_id']),
				'topic_last_poster_name' => ($username != '') ? $username : '',
			);

			if ($mode == 'post')
			{
				$topic_sql = array_merge($topic_sql, array(
					'topic_first_post_id' => intval($post_id),
					'topic_time' => $current_time,
					'topic_poster' => intval($user->data['user_id']),
					'topic_first_poster_name' => ($username != '') ? $username : '',
				));
			}
			$db->sql_query_array('UPDATE ' . TOPICS_TABLE . ' SET WHERE topic_id = ' . intval($topic_id), $topic_sql);

			if ($post_count_inc)
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_posts = user_posts + 1
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);
			}
		}

		$db->sql_transaction('commit');

//			$mtime = explode(' ', microtime());
//			echo "<br />\nParsed [ '$result' :: " . ( $mtime[1] + $mtime[0] - $starttime ) . " ] >> ";
//			print_r(htmlentities($message));

		trigger_error($user->lang['Stored']);
	}

	// Houston, we have an error ...
	$post_text = &$message;
	$post_subject = $topic_title = &$subject;
	$topic_icon = &$icon;
	$topic_type = &$type;

}
//
// PROCESS SUBMIT
// --------------



// -----------------
// TEMPORARY SECTION!
//

// Remove encoded bbcode, urls, etc.
$match = array(
	'#<!\-\- b \-\-><b>(.*?)</b><!\-\- b \-\->#s',
	'#<!\-\- u \-\-><u>(.*?)</u><!\-\- u \-\->#s',
	'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
	'#<!\-\- m \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- m \-\->#',
);

$replace = array(
	'[b]\1[/b]',
	'[u]\1[/u]',
	'\1',
	'\1',
);

$post_text = preg_replace($match, $replace, $post_text);
//
// TEMPORARY SECTION!
// -----------------



// -----------------------------
// MAIN POSTING PAGE BEGINS HERE
//

// Notify user checkbox
if ($post || $refresh)
{
	$notify_user = (!empty($notify)) ? TRUE : 0;
}
else
{
	if ($mode != 'post' && $user->data['user_id'] != ANONYMOUS)
	{
		$sql = "SELECT topic_id
			FROM " . TOPICS_WATCH_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id = " . $user->data['user_id'];
		$result = $db->sql_query($sql);

		$notify_user = ($db->sql_fetchrow($result)) ? TRUE : $user->data['user_notify'];
	}
	else
	{
		$notify_user = ($user_id['user_id']) ? $user->data['user_notify'] : 0;
	}
}

// Generate smilies and topic icon listings
generate_smilies('inline');

// Topic icons
$sql = "SELECT *
	FROM " . ICONS_TABLE . "
	WHERE icons_id > 1";
$result = $db->sql_query($sql);

$s_topic_icons = false;
if ($row = $db->sql_fetchrow($result))
{
	$s_topic_icons = true;

	do
	{
		$template->assign_block_vars('topic_icon', array(
			'ICON_ID'		=> $row['icons_id'],
			'ICON_IMG'		=> $board_config['icons_path'] . '/' . $row['icons_url'],
			'ICON_WIDTH'	=> $row['icons_width'],
			'ICON_HEIGHT' 	=> $row['icons_height'],

			'S_ICON_CHECKED' => ($row['icons_id'] == $topic_icon) ? ' checked="checked"' : '')
		);
	}
	while ($row = $db->sql_fetchrow($result));
}

// Topic type selection ... only for first post in topic?
$topic_type_toggle = '';
if ($mode == 'post' || $mode == 'edit')
{
	if ( $auth->acl_get('f_sticky', $forum_id) )
	{
		$topic_type_toggle .= '<input type="radio" name="type" value="' . POST_STICKY . '"';
		if ($topic_type == POST_STICKY)
		{
			$topic_type_toggle .= ' checked="checked"';
		}
		$topic_type_toggle .= ' /> ' . $user->lang['Post_Sticky'] . '&nbsp;&nbsp;';
	}

	if ( $auth->acl_get('f_announce', $forum_id) )
	{
		$topic_type_toggle .= '<input type="radio" name="type" value="' . POST_ANNOUNCE . '"';
		if ($topic_type == POST_ANNOUNCE)
		{
			$topic_type_toggle .= ' checked="checked"';
		}
		$topic_type_toggle .= ' /> ' . $user->lang['Post_Announcement'] . '&nbsp;&nbsp;';
	}

	if ( $topic_type_toggle != '' )
	{
		$topic_type_toggle = $user->lang['Post_topic_as'] . ': <input type="radio" name="type" value="' . POST_NORMAL .'"' . ( ($topic_type == POST_NORMAL) ? ' checked="checked"' : '' ) . ' /> ' . $user->lang['Post_Normal'] . '&nbsp;&nbsp;' . $topic_type_toggle;
	}
}

// HTML, BBCode, Smilies, Images and Flash status
$html_status = ($board_config['allow_html'] && $auth->acl_get('f_html', $forum_id)) ? true : false;
$bbcode_status = ($board_config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id)) ? true : false;
$smilies_status = ($board_config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id)) ? true : false;
$img_status = ($board_config['allow_img'] && $auth->acl_get('f_img', $forum_id)) ? true : false;
$flash_status = ($board_config['allow_flash'] && $auth->acl_get('f_flash', $forum_id)) ? true : false;

// Page title/hidden fields
$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';

switch( $mode )
{
	case 'post':
		$page_title = $user->lang['Post_a_new_topic'];
		$s_hidden_fields .= '<input type="hidden" name="f" value="' . $forum_id . '" />';
		break;

	case 'reply':
		$page_title = $user->lang['Post_a_reply'];
		$s_hidden_fields .= '<input type="hidden" name="t" value="' . $topic_id . '" />';
		break;

	case 'edit':
		$page_title = $user->lang['Edit_Post'];
		$s_hidden_fields .= '<input type="hidden" name="p" value="' . $post_id . '" />';
		break;
}

// Nav links for forum
forum_nav_links($forum_id, $forum_name);

// Start assigning vars for main posting page ...
$template->assign_vars(array(
	'FORUM_NAME' 		=> $forum_name,
	'TOPIC_TITLE' 		=> ($mode != 'post') ? $topic_title : '',
	'USERNAME' 			=> $post_username,
	'SUBJECT' 			=> (!empty($topic_title)) ? $topic_title : $post_subject,
	'MESSAGE' 			=> $post_text,
	'HTML_STATUS' 		=> ($html_status) ? $user->lang['HTML_is_ON'] : $user->lang['HTML_is_OFF'],
	'BBCODE_STATUS' 	=> ($bbcode_status) ? sprintf($user->lang['BBCode_is_ON'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>') : sprintf($user->lang['BBCode_is_OFF'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>'),
	'SMILIES_STATUS' 	=> ($smilies_status) ? $user->lang['Smilies_are_ON'] : $user->lang['Smilies_are_OFF'],
	'IMG_STATUS' 		=> ($img_status) ? $user->lang['Images_are_ON'] : $user->lang['Images_are_OFF'],
	'FLASH_STATUS' 		=> ($flash_status) ? $user->lang['Flash_is_ON'] : $user->lang['Flash_is_OFF'],

	'L_POST_A' 				=> $page_title,
	'L_POST_SUBJECT' 		=> $user->lang['Post_subject'],
	'L_VIEW_MODERATORS' 	=> $user->lang['View_moderators'],
	'L_TOPIC_ICON' 			=> $user->lang['Topic_icon'],
	'L_SUBJECT' 			=> $user->lang['Subject'],
	'L_MESSAGE_BODY' 		=> $user->lang['Message_body'],
	'L_OPTIONS' 			=> $user->lang['Options'],
	'L_PREVIEW' 			=> $user->lang['Preview'],
	'L_SPELLCHECK' 			=> $user->lang['Spellcheck'],
	'L_SUBMIT' 				=> $user->lang['Submit'],
	'L_SAVE' 				=> $user->lang['Save'],
	'L_CANCEL' 				=> $user->lang['Cancel'],
	'L_CONFIRM_DELETE' 		=> $user->lang['Confirm_delete'],
	'L_DISABLE_HTML' 		=> $user->lang['Disable_HTML_post'],
	'L_DISABLE_BBCODE' 		=> $user->lang['Disable_BBCode_post'],
	'L_DISABLE_SMILIES' 	=> $user->lang['Disable_Smilies_post'],
	'L_DISABLE_MAGIC_URL' 	=> $user->lang['Disable_magic_url'],
	'L_ATTACH_SIGNATURE' 	=> $user->lang['Attach_signature'],
	'L_NOTIFY_ON_REPLY' 	=> $user->lang['Notify'],
	'L_DELETE_POST' 		=> $user->lang['Delete_post'],
	'L_NONE' 				=> $user->lang['None'],
	'L_EMPTY_MESSAGE' 		=> $user->lang['Empty_message'],
	'L_BBCODE_CLOSE_TAGS' 	=> $user->lang['Close_Tags'],
	'L_STYLES_TIP' 			=> $user->lang['Styles_tip'],
	'L_BBCODE_B_HELP' 		=> $user->lang['bbcode_b_help'],
	'L_BBCODE_I_HELP' 		=> $user->lang['bbcode_i_help'],
	'L_BBCODE_U_HELP' 		=> $user->lang['bbcode_u_help'],
	'L_BBCODE_Q_HELP' 		=> $user->lang['bbcode_q_help'],
	'L_BBCODE_C_HELP' 		=> $user->lang['bbcode_c_help'],
	'L_BBCODE_L_HELP' 		=> $user->lang['bbcode_l_help'],
	'L_BBCODE_O_HELP' 		=> $user->lang['bbcode_o_help'],
	'L_BBCODE_P_HELP' 		=> $user->lang['bbcode_p_help'],
	'L_BBCODE_W_HELP' 		=> $user->lang['bbcode_w_help'],
	'L_BBCODE_A_HELP' 		=> $user->lang['bbcode_a_help'],
	'L_BBCODE_S_HELP' 		=> $user->lang['bbcode_s_help'],
	'L_BBCODE_F_HELP' 		=> $user->lang['bbcode_f_help'],
	'L_FONT_COLOR' 			=> $user->lang['Font_color'],
	'L_FONT_SIZE' 			=> $user->lang['Font_size'],
	'L_FONT_TINY' 			=> $user->lang['font_tiny'],
	'L_FONT_SMALL' 			=> $user->lang['font_small'],
	'L_FONT_NORMAL' 		=> $user->lang['font_normal'],
	'L_FONT_LARGE' 			=> $user->lang['font_large'],
	'L_FONT_HUGE' 			=> $user->lang['font_huge'],

	'U_VIEW_FORUM' 		=> "viewforum.$phpEx$SID&amp;f=$forum_id",
	'U_VIEWTOPIC' 		=> ($mode != 'post') ? "viewtopic.$phpEx$SID&amp;t=$topic_id" : '',
	'U_REVIEW_TOPIC' 	=> ($mode != 'post') ? "posting.$phpEx$SID&amp;mmode=topicreview&amp;t=$topic_id" : '',
	'U_VIEW_MODERATORS' => 'memberslist.' . $phpEx . $SID . '&amp;mode=moderators&amp;f=' . $forum_id,

	'S_SHOW_TOPIC_ICONS' 	=> $s_topic_icons,
	'S_HTML_CHECKED' 		=> (!$html_on ) ? 'checked="checked"' : '',
	'S_BBCODE_CHECKED' 		=> (!$bbcode_on ) ? 'checked="checked"' : '',
	'S_SMILIES_CHECKED' 	=> (!$smilies_on ) ? 'checked="checked"' : '',
	'S_MAGIC_URL_CHECKED' 	=> (!$magic_urls_on ) ? 'checked="checked"' : '',
	'S_SIGNATURE_CHECKED' 	=> ($attach_sig ) ? 'checked="checked"' : '',
	'S_NOTIFY_CHECKED' 		=> ($notify_user ) ? 'checked="checked"' : '',
	'S_DISPLAY_USERNAME' 	=> ($user->data['user_id'] == ANONYMOUS || ($mode == 'edit' && $post_username)) ? true : false,

	'S_SAVE_ALLOWED' 	=> ($auth->acl_get('f_save', $forum_id)) ? true : false,
	'S_HTML_ALLOWED' 	=> $html_status,
	'S_BBCODE_ALLOWED' 	=> $bbcode_status,
	'S_SMILIES_ALLOWED' => $smilies_status,
	'S_SIG_ALLOWED' 	=> ($auth->acl_get('f_sigs', $forum_id)) ? true : false,
	'S_NOTIFY_ALLOWED' 	=> ($user->data['user_id'] != ANONYMOUS) ? true : false,
	'S_DELETE_ALLOWED' 	=> ($mode == 'edit' && (($auth->acl_get('f_delete', $forum_id) && $post_data['last_post']) || $auth->acl_get('m_', $forum_id))) ? true : false,
	'S_TYPE_TOGGLE' 	=> $topic_type_toggle,

	'S_TOPIC_ID' 		=> $topic_id,
	'S_POST_ACTION' 	=> "posting.$phpEx$SID",
	'S_HIDDEN_FIELDS' 	=> $s_hidden_fields)
);

// Poll entry
if ( ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) ) && $auth->acl_get('f_poll', $forum_id) )
{
	$template->assign_vars(array(
		'S_SHOW_POLL_BOX' 	=> true,
		'S_POLL_DELETE' 	=> ($mode == 'edit' && $edit_poll) ? true : false,

		'L_ADD_A_POLL' 			=> $user->lang['Add_poll'],
		'L_ADD_POLL_EXPLAIN' 	=> $user->lang['Add_poll_explain'],
		'L_POLL_QUESTION' 		=> $user->lang['Poll_question'],
		'L_POLL_OPTION' 		=> $user->lang['Poll_option'],
		'L_ADD_OPTION' 			=> $user->lang['Add_option'],
		'L_UPDATE_OPTION' 		=> $user->lang['Update'],
		'L_DELETE_OPTION' 		=> $user->lang['Delete'],
		'L_POLL_LENGTH' 		=> $user->lang['Poll_for'],
		'L_DAYS' 				=> $user->lang['Days'],
		'L_POLL_LENGTH_EXPLAIN' => $user->lang['Poll_for_explain'],
		'L_POLL_DELETE' 		=> $user->lang['Delete_poll'],

		'POLL_TITLE' 	=> $poll_title,
		'POLL_LENGTH' 	=> $poll_length)
	);

	if ( !empty($poll_options) )
	{
		foreach ( $poll_options as $option_id => $option_text )
		{
			$template->assign_block_vars('poll_options', array(
				'POLL_OPTION' => htmlspecialchars($option_text),

				'S_POLL_OPTION_NUM' => $option_id)
			);
		}
	}
}

// Attachment entry
if ( $auth->acl_get('f_attach', $forum_id) )
{
	$template->assign_vars(array(
		'S_SHOW_ATTACH_BOX' 		=> true,
		'L_ADD_ATTACHMENT' 			=> $user->lang['Add_attach'],
		'L_ADD_ATTACHMENT_EXPLAIN' 	=> $user->lang['Add_attach_explain'],

		'L_ADD_FILE' 	=> $user->lang['Add_file'],
		'L_FILE_NAME' 	=> $user->lang['Filename'],
		'L_FILE_COMMENT'=> $user->lang['File_comment'],)
	);
}

// Output page ...
include($phpbb_root_path . 'includes/page_header.'.$phpEx);

$template->set_filenames(array(
	'body' => 'posting_body.html',
	'reviewbody' => 'posting_topic_review.html')
);
make_jumpbox('viewforum.'.$phpEx);

// Topic review
if ( $mode == 'reply' )
{
//	require($phpbb_root_path . 'includes/topic_review.'.$phpEx);
//	topic_review($topic_id, true);

//	$template->assign_var_from_handle('TOPIC_REVIEW_BOX', 'reviewbody');
}

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

function forum_nav_links(&$forum_id, &$forum_name)
{
	global $SID, $template, $phpEx, $auth;

	$type = 'parent';
	$forum_rows = array();

	if (!($forum_branch = get_forum_branch($forum_id)))
	{
		trigger_error($user->lang['Forum_not_exist']);
	}

	$s_has_subforums = FALSE;
	foreach ($forum_branch as $row)
	{
		if ($type == 'parent')
		{
			$link = ($row['forum_status'] == ITEM_CATEGORY) ? 'index.' . $phpEx . $SID . '&amp;c=' . $row['forum_id'] : 'viewforum.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'];

			$template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=>	$row['forum_name'],
				'U_VIEW_FORUM'	=>	$link
			));

			if ($row['forum_id'] == $forum_id)
			{
				$branch_root_id = 0;
				$forum_data = $row;
				$type = 'child';

				$forum_name = $row['forum_name'];
			}
		}
		else
		{
			if ($row['parent_id'] == $forum_data['forum_id'])
			{
				// Root-level forum
				$forum_rows[] = $row;
				$parent_id = $row['forum_id'];

				if ($row['forum_status'] == ITEM_CATEGORY)
				{
					$branch_root_id = $row['forum_id'];
				}
				else
				{
					$s_has_subforums = TRUE;
				}
			}
			elseif ($row['parent_id'] == $branch_root_id)
			{
				// Forum directly under a category
				$forum_rows[] = $row;
				$parent_id = $row['forum_id'];

				if ($row['forum_status'] != ITEM_CATEGORY)
				{
					$s_has_subforums = TRUE;
				}
			}
			elseif ($row['forum_status'] != ITEM_CATEGORY)
			{
				// Subforum
				if ($auth->acl_get('f_list', $row['forum_id']))
				{
					$subforums[$parent_id][] = $row;
				}
			}
		}
	}

	return $s_has_subforums;
}

?>