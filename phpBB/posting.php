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

// Start session management
$user->start();
$user->setup();
$auth->acl($user->data);
// End session management

// Was cancel pressed? If so then redirect to the appropriate page
if ( !empty($cancel) )
{
	$redirect = ( $p ) ? "viewtopic.$phpEx$SID&p=$p#$p" : ( ( $t ) ? "viewtopic.$phpEx$SID&t=$t" : ( ( $f ) ? "viewforum.$phpEx$SID&f=$f" : "index.$phpEx$SID" ) );
	redirect($redirect);
}









// If the mode is set to topic review then output that review ...
switch ( $mode )
{
	case 'topicreview':
//		require($phpbb_root_path . 'includes/topic_review.'.$phpEx);
//		topic_review($t, false);
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





// Create appropriate SQL for this mode ...
switch ( $mode )
{
	case 'newtopic':
		if ( empty($f) )
		{
			trigger($user->lang['Forum_not_exist']);
		}

		$sql = "SELECT *
			FROM " . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";
		break;

	case 'reply':
	case 'vote':
		if ( empty( $t) )
		{
			trigger($user->lang['No_topic_id']);
		}

		$sql = "SELECT f.*, t.*
			FROM " . FORUMS_TABLE . " f, " . TOPICS_TABLE . " t
			WHERE t.topic_id = $topic_id
				AND f.forum_id = t.forum_id";
		break;

	case 'quote':
	case 'editpost':
	case 'delete':
	case 'poll_delete':
		if ( empty($p) )
		{
			trigger($user->lang['No_post_id']);
		}

		$select_sql = ( !$submit ) ? ', t.topic_title, p.enable_bbcode, p.enable_html, p.enable_smilies, p.enable_sig, p.post_username, pt.post_subject, pt.post_text, pt.bbcode_uid, u.username, u.user_id, u.user_sig' : ', pt.post_subject, pt.post_text';
		$from_sql = ( !$submit ) ? ', ' . POSTS_TEXT_TABLE . ' pt, ' . USERS_TABLE . ' u' : ', ' . POSTS_TEXT_TABLE . ' pt';
		$where_sql = ( !$submit ) ? 'AND pt.post_id = p.post_id AND u.user_id = p.poster_id' : 'AND pt.post_id = p.post_id';

		$sql = "SELECT f.*, t.*, p.post_id, p.poster_id" . $select_sql . "
			FROM " . POSTS_TABLE . " p, " . TOPICS_TABLE . " t, " . FORUMS_TABLE . " f" . $from_sql . "
			WHERE p.post_id = $p
				AND t.topic_id = p.topic_id
				AND f.forum_id = p.forum_id
				$where_sql";
		break;

	default:
		message_die(MESSAGE, $user->lang['No_valid_mode']);
}

$result = $db->sql_query($sql);

if ( $post_info = $db->sql_fetchrow($result) )
{
	$forum_id = $post_info['forum_id'];
	$forum_name = $post_info['forum_name'];

	$topic_title = $post_info['topic_title'];
	$topic_id = $post_info['topic_id'];
}



// User has submitted a post, process it
if ( isset($post) )
{

	// First check if message has changed (if editing), if not
	// don't parse at all else ...
	//
	// Need to parse message, parse search words, parse polls,
	// parse attachments, check whether forum is moderated or
	// if msg is being saved (and if it is whether user has run
	// out of save quota) if not topic/forum needs syncing, if
	// replying notifications need sending as appropriate.

	echo "\$_POST >> ";
	print_r(htmlentities($message));
	echo "<br /><hr /><br />\n\n";

	// Check checksum
	if ( $mode != 'editpost' || md5($_POST['message']) != $post_info['post_checksum'] )
	{
		$parse_msg = new parse_message();
		$search = new fulltext_search();

		$mtime = explode(' ', microtime());
		$starttime = $mtime[1] + $mtime[0];

		$result = $parse_msg->parse($message, $html_on, $bbcode_on, $post_info['bbcode_uid'], $magic_urls_on, $smilies_on);

		$mtime = explode(' ', microtime());
		echo "<br />\nParsed [ '$result' :: " . ( $mtime[1] + $mtime[0] - $starttime ) . " ] >> ";
//		print_r(htmlentities($message));
		print_r($message);
		echo "<br /><hr /><br />\n\n";

		// Fulltext parser
//		$result = $search->add($p, $message, $post_subject, $post_info['post_text'], $post_info['post_subject']);
	}

	exit;


}




// TEMPORARY :D
$message = $post_info['post_text'];

// Remove encoded bbcode, urls, etc.
$match = array(
	'#<!\-\- b \-\-><b>(.*?)</b><!\-\- b \-\->#s',
	'#<!\-\- u \-\-><u>(.*?)</u><!\-\- u \-\->#s',
	'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
	'#<!\-\- u \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- u \-\->#',
);

$replace = array(
	'[b]\1[/b]',
	'[u]\1[/u]',
	'\1',
	'\1',
);

$message = preg_replace($match, $replace, $message);





// -----------------------------
// MAIN POSTING PAGE BEGINS HERE
//

// Notify user checkbox
if ( $post || $refresh )
{
	$notify_user = ( !empty($notify) ) ? TRUE : 0;
}
else
{
	if ( $mode != 'newtopic' && $user->data['user_id'] )
	{
		$sql = "SELECT topic_id
			FROM " . TOPICS_WATCH_TABLE . "
			WHERE topic_id = $topic_id
				AND user_id = " . $user->data['user_id'];
		$result = $db->sql_query($sql);

		$notify_user = ( $db->sql_fetchrow($result) ) ? TRUE : $user->data['user_notify'];
	}
	else
	{
		$notify_user = ( $user_id['user_id'] ) ? $user->data['user_notify'] : 0;
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
if ( $row = $db->sql_fetchrow($result) )
{
	$s_topic_icons = true;

	do
	{
		$template->assign_block_vars('topic_icon', array(
			'ICON_ID' => $row['icons_id'],
			'ICON_IMG' => $board_config['icons_path'] . '/' . $row['icons_url'],
			'ICON_WIDTH' => $row['icons_width'],
			'ICON_HEIGHT' => $row['icons_height'])
		);
	}
	while ( $row = $db->sql_fetchrow($result) );
}

// Topic type selection
$topic_type_toggle = '';
if ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) )
{
	if ( $auth->acl_get('f_sticky', $forum_id) )
	{
		$topic_type_toggle .= '<input type="radio" name="topictype" value="' . POST_STICKY . '"';
		if ( $post_data['topic_type'] == POST_STICKY || $topic_type == POST_STICKY )
		{
			$topic_type_toggle .= ' checked="checked"';
		}
		$topic_type_toggle .= ' /> ' . $user->lang['Post_Sticky'] . '&nbsp;&nbsp;';
	}

	if ( $auth->acl_get('f_announce', $forum_id) )
	{
		$topic_type_toggle .= '<input type="radio" name="topictype" value="' . POST_ANNOUNCE . '"';
		if ( $post_data['topic_type'] == POST_ANNOUNCE || $topic_type == POST_ANNOUNCE )
		{
			$topic_type_toggle .= ' checked="checked"';
		}
		$topic_type_toggle .= ' /> ' . $user->lang['Post_Announcement'] . '&nbsp;&nbsp;';
	}

	if ( $topic_type_toggle != '' )
	{
		$topic_type_toggle = $user->lang['Post_topic_as'] . ': <input type="radio" name="topictype" value="' . POST_NORMAL .'"' . ( ( $post_data['topic_type'] == POST_NORMAL || $topic_type == POST_NORMAL ) ? ' checked="checked"' : '' ) . ' /> ' . $user->lang['Post_Normal'] . '&nbsp;&nbsp;' . $topic_type_toggle;
	}
}

// HTML, BBCode, Smilies, Images and Flash status
$html_status = ( $board_config['allow_html'] && $auth->acl_get('f_html', $forum_id) ) ? true : false;
$bbcode_status = ( $board_config['allow_bbcode'] && $auth->acl_get('f_bbcode', $forum_id) ) ? true : false;
$smilies_status = ( $board_config['allow_smilies'] && $auth->acl_get('f_smilies', $forum_id) ) ? true : false;
$img_status = ( $board_config['allow_img'] && $auth->acl_get('f_img', $forum_id) ) ? true : false;
$flash_status = ( $board_config['allow_flash'] && $auth->acl_get('f_flash', $forum_id) ) ? true : false;

// Page title/hidden fields
$s_hidden_fields = '<input type="hidden" name="mode" value="' . $mode . '" />';

switch( $mode )
{
	case 'newtopic':
		$page_title = $user->lang['Post_a_new_topic'];
		$s_hidden_fields .= '<input type="hidden" name="f" value="' . $forum_id . '" />';
		break;

	case 'reply':
		$page_title = $user->lang['Post_a_reply'];
		$s_hidden_fields .= '<input type="hidden" name="t" value="' . $topic_id . '" />';
		break;

	case 'editpost':
		$page_title = $user->lang['Edit_Post'];
		$s_hidden_fields .= '<input type="hidden" name="p" value="' . $post_id . '" />';
		break;
}

// Start assigning vars for main posting page ...
$template->assign_vars(array(
	'FORUM_NAME' => $forum_name,
	'TOPIC_TITLE' => ( $mode != 'newtopic' ) ? $topic_title : '',
	'USERNAME' => $username,
	'SUBJECT' => $subject,
	'MESSAGE' => $message,
	'HTML_STATUS' => ( $html_status ) ? $user->lang['HTML_is_ON'] : $user->lang['HTML_is_OFF'],
	'BBCODE_STATUS' => ( $bbcode_status ) ? sprintf($user->lang['BBCode_is_ON'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>') : sprintf($user->lang['BBCode_is_OFF'], '<a href="' . "faq.$phpEx$SID&amp;mode=bbcode" . '" target="_phpbbcode">', '</a>'),
	'SMILIES_STATUS' => ( $smilies_status ) ? $user->lang['Smilies_are_ON'] : $user->lang['Smilies_are_OFF'],
	'IMG_STATUS' => ( $img_status ) ? $user->lang['Images_are_ON'] : $user->lang['Images_are_OFF'],
	'FLASH_STATUS' => ( $flash_status ) ? $user->lang['Flash_is_ON'] : $user->lang['Flash_is_OFF'],

	'L_POST_A' => $page_title,
	'L_POST_SUBJECT' => $user->lang['Post_subject'],
	'L_VIEW_MODERATORS' => $user->lang['View_moderators'],
	'L_TOPIC_ICON' => $user->lang['Topic_icon'],
	'L_SUBJECT' => $user->lang['Subject'],
	'L_MESSAGE_BODY' => $user->lang['Message_body'],
	'L_OPTIONS' => $user->lang['Options'],
	'L_PREVIEW' => $user->lang['Preview'],
	'L_SPELLCHECK' => $user->lang['Spellcheck'],
	'L_SUBMIT' => $user->lang['Submit'],
	'L_SAVE' => $user->lang['Save'],
	'L_CANCEL' => $user->lang['Cancel'],
	'L_CONFIRM_DELETE' => $user->lang['Confirm_delete'],
	'L_DISABLE_HTML' => $user->lang['Disable_HTML_post'],
	'L_DISABLE_BBCODE' => $user->lang['Disable_BBCode_post'],
	'L_DISABLE_SMILIES' => $user->lang['Disable_Smilies_post'],
	'L_DISABLE_MAGIC_URL' => $user->lang['Disable_magic_url'],
	'L_ATTACH_SIGNATURE' => $user->lang['Attach_signature'],
	'L_NOTIFY_ON_REPLY' => $user->lang['Notify'],
	'L_DELETE_POST' => $user->lang['Delete_post'],
	'L_NONE' => $user->lang['None'],
	'L_EMPTY_MESSAGE' => $user->lang['Empty_message'],
	'L_BBCODE_CLOSE_TAGS' => $user->lang['Close_Tags'],
	'L_STYLES_TIP' => $user->lang['Styles_tip'],
	'L_BBCODE_B_HELP' => $user->lang['bbcode_b_help'],
	'L_BBCODE_I_HELP' => $user->lang['bbcode_i_help'],
	'L_BBCODE_U_HELP' => $user->lang['bbcode_u_help'],
	'L_BBCODE_Q_HELP' => $user->lang['bbcode_q_help'],
	'L_BBCODE_C_HELP' => $user->lang['bbcode_c_help'],
	'L_BBCODE_L_HELP' => $user->lang['bbcode_l_help'],
	'L_BBCODE_O_HELP' => $user->lang['bbcode_o_help'],
	'L_BBCODE_P_HELP' => $user->lang['bbcode_p_help'],
	'L_BBCODE_W_HELP' => $user->lang['bbcode_w_help'],
	'L_BBCODE_A_HELP' => $user->lang['bbcode_a_help'],
	'L_BBCODE_S_HELP' => $user->lang['bbcode_s_help'],
	'L_BBCODE_F_HELP' => $user->lang['bbcode_f_help'],
	'L_FONT_COLOR' => $user->lang['Font_color'],
	'L_FONT_SIZE' => $user->lang['Font_size'],
	'L_FONT_TINY' => $user->lang['font_tiny'],
	'L_FONT_SMALL' => $user->lang['font_small'],
	'L_FONT_NORMAL' => $user->lang['font_normal'],
	'L_FONT_LARGE' => $user->lang['font_large'],
	'L_FONT_HUGE' => $user->lang['font_huge'],

	'U_VIEW_FORUM' => "viewforum.$phpEx$SID&amp;f=$forum_id",
	'U_VIEWTOPIC' => ( $mode != 'newtopic' ) ? "viewtopic.$phpEx$SID&amp;t=$topic_id" : '',
	'U_REVIEW_TOPIC' => ( $mode != 'newtopic' ) ? "posting.$phpEx$SID&amp;mmode=topicreview&amp;t=$topic_id" : '',
	'U_VIEW_MODERATORS' => 'memberslist.' . $phpEx . $SID . '&amp;mode=moderators&amp;f=' . $f,

	'S_SHOW_TOPIC_ICONS' => $s_topic_icons,
	'S_HTML_CHECKED' => ( !$html_on ) ? 'checked="checked"' : '',
	'S_BBCODE_CHECKED' => ( !$bbcode_on ) ? 'checked="checked"' : '',
	'S_SMILIES_CHECKED' => ( !$smilies_on ) ? 'checked="checked"' : '',
	'S_MAGIC_URL_CHECKED' => ( !$magic_urls_on ) ? 'checked="checked"' : '',
	'S_SIGNATURE_CHECKED' => ( $attach_sig ) ? 'checked="checked"' : '',
	'S_NOTIFY_CHECKED' => ( $notify_user ) ? 'checked="checked"' : '',
	'S_DISPLAY_USERNAME' => ( !$user->data['user_id'] || ( $mode == 'editpost' && $post_info['post_username'] ) ) ? true : false,

	'S_SAVE_ALLOWED' => ( $auth->acl_get('f_save', $forum_id) ) ? true : false,
	'S_HTML_ALLOWED' => $html_status,
	'S_BBCODE_ALLOWED' => $bbcode_status,
	'S_SMILIES_ALLOWED' => $smilies_status,
	'S_SIG_ALLOWED' => ( $auth->acl_get('f_sigs', $forum_id) ) ? true : false,
	'S_NOTIFY_ALLOWED' => ( $user->data['user_id'] ) ? true : false,
	'S_DELETE_ALLOWED' => ( $mode == 'editpost' && ( ( $auth->acl_get('f_delete', $forum_id) && $post_data['last_post'] && ( !$post_data['has_poll'] || $post_data['edit_poll'] ) ) || $auth->acl_get('m_', $forum_id) ) ) ? true : false,
	'S_TYPE_TOGGLE' => $topic_type_toggle,

	'S_TOPIC_ID' => $t,
	'S_POST_ACTION' => "posting.$phpEx$SID",
	'S_HIDDEN_FIELDS' => $s_hidden_fields)
);

// Poll entry
if ( ( $mode == 'newtopic' || ( $mode == 'editpost' && $post_data['first_post'] ) ) && $auth->acl_get('f_poll', $forum_id) )
{
	$template->assign_vars(array(
		'S_SHOW_POLL_BOX' => true,
		'S_POLL_DELETE' => ( $mode == 'editpost' && $post_data['edit_poll'] ) ? true : false,

		'L_ADD_A_POLL' => $user->lang['Add_poll'],
		'L_ADD_POLL_EXPLAIN' => $user->lang['Add_poll_explain'],
		'L_POLL_QUESTION' => $user->lang['Poll_question'],
		'L_POLL_OPTION' => $user->lang['Poll_option'],
		'L_ADD_OPTION' => $user->lang['Add_option'],
		'L_UPDATE_OPTION' => $user->lang['Update'],
		'L_DELETE_OPTION' => $user->lang['Delete'],
		'L_POLL_LENGTH' => $user->lang['Poll_for'],
		'L_DAYS' => $user->lang['Days'],
		'L_POLL_LENGTH_EXPLAIN' => $user->lang['Poll_for_explain'],
		'L_POLL_DELETE' => $user->lang['Delete_poll'],

		'POLL_TITLE' => $poll_title,
		'POLL_LENGTH' => $poll_length)
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
		'S_SHOW_ATTACH_BOX' => true,
		'L_ADD_ATTACHMENT' => $user->lang['Add_attach'],
		'L_ADD_ATTACHMENT_EXPLAIN' => $user->lang['Add_attach_explain'],

		'L_ADD_FILE' => $user->lang['Add_file'],
		'L_FILE_NAME' => $user->lang['Filename'],
		'L_FILE_COMMENT' => $user->lang['File_comment'],)
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
	require($phpbb_root_path . 'includes/topic_review.'.$phpEx);
	topic_review($t, true);

	$template->assign_var_from_handle('TOPIC_REVIEW_BOX', 'reviewbody');
}

include($phpbb_root_path . 'includes/page_tail.'.$phpEx);

?>