<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : memberlist.php 
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

// TODO
// Add permission check for IM clients
// Combine Jabber and email contact capabilities?
// When registering a new jabber user the message doesn't get sent first time

define('IN_PHPBB', true);
$phpbb_root_path = './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.'.$phpEx);

// Start session management
$user->start();
$auth->acl($user->data);
$user->setup();

// Grab data
$mode		= request_var('mode', '');
$action		= request_var('action', '');
$user_id	= request_var('u', ANONYMOUS);
$topic_id	= request_var('t', 0);

switch ($mode)
{
	case 'email':
		break;

	default:
		// Can this user view profiles/memberslist?
		if (!$auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
		{
			if ($user->data['user_id'] != ANONYMOUS)
			{
				trigger_error($user->lang['NO_VIEW_USERS']);
			}

			login_box(preg_replace('#.*?([a-z]+?\.' . $phpEx . '.*?)$#i', '\1', htmlspecialchars($_SERVER['REQUEST_URI'])));
		}
		break;
}


$start	= request_var('start', 0);
$submit = (isset($_POST['submit'])) ? true : false;

$sort_key = request_var('sk', 'c');
$sort_dir = request_var('sd', 'a');


// Grab rank information for later
$ranks = array();
obtain_ranks($ranks);


// What do you want to do today? ... oops, I think that line is taken ...
switch ($mode)
{
	case 'leaders':
		// Display a listing of board admins, moderators?
		$user_ary = auth::acl_get_list(false, array('a_', 'm_'), false);

		$user_id_ary = array();
		foreach ($user_ary as $forum_id => $forum_ary)
		{
			foreach ($forum_ary as $auth_option => $id_ary)
			{
				$user_id_ary += $id_ary;
			}
		}

		$sql = 'SELECT user_id, username 
			FROM ' . USERS_TABLE . ' 
			WHERE user_id IN (' . implode(', ', $user_id_ary) . ')';
		$result = $db->sql_query($sql);

		$db->sql_freeresult($result);

		foreach ($user_ary[0]['u_'] as $user_id)
		{
		}

		break;

	case 'contact':
		$page_title = $user->lang['IM_USER'];
		$template_html = 'memberlist_im.html';

		$presence_img = '';
		switch ($action)
		{
			case 'icq':
				$lang = 'ICQ';
				$sql_field = 'user_icq';
				$s_select = 'S_SEND_ICQ';
				$s_action = 'http://wwp.icq.com/scripts/WWPMsg.dll';
				break;

			case 'aim':
				$lang = 'AIM';
				$sql_field = 'user_aim';
				$s_select = 'S_SEND_AIM';
				$s_action = '';
				break;
			
			case 'msnm':
				$lang = 'MSNM';
				$sql_field = 'user_msnm';
				$s_select = 'S_SEND_MSNM';
				$s_action = '';
				break;

			case 'jabber':
				$lang = 'JABBER';
				$sql_field = 'user_jabber';
				$s_select = (@extension_loaded('xml')) ? 'S_SEND_JABBER' : 'S_NO_SEND_JABBER';
				$s_action = "memberlist.$phpEx$SID&amp;mode=contact&amp;action=$action&amp;u=$user_id";
				break;
		}

		// Grab relevant data
		$sql = "SELECT user_id, username, user_email, user_lang, $sql_field 
			FROM " . USERS_TABLE . " 
			WHERE user_id = $user_id";
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_USER_DATA']);
		}
		$db->sql_freeresult($result);

		// Post data grab actions
		switch ($action)
		{
			case 'icq':
				$presence_img = '<img src="http://web.icq.com/whitepages/online?icq=' . $row[$sql_field] . '&amp;img=5" width="18" height="18" border="0" alt="" />';
				break;

			case 'jabber':
				if ($submit && @extension_loaded('xml'))
				{
					include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

					$subject = sprintf($user->lang['IM_JABBER_SUBJECT'], $user->data['username'], $config['server_name']);
					$message = $_POST['message'];

					$messenger = new messenger();

					$messenger->template('profile_send_email', $row['user_lang']);
					$messenger->subject($subject);

					$messenger->replyto($user->data['user_email']);
					$messenger->im($row['user_jabber'], $row['username']);

					$messenger->assign_vars(array(
						'SITENAME'		=> $config['sitename'],
						'BOARD_EMAIL'	=> $config['board_contact'],
						'FROM_USERNAME' => $user->data['username'],
						'TO_USERNAME'	=> $row['username'],
						'MESSAGE'		=> $message)
					);

					$messenger->send(NOTIFY_IM);
					$messenger->queue->save();

					$s_select = 'S_SENT_JABBER';
				}
				break;
		}

		// Send vars to the template
		$template->assign_vars(array(
			'IM_CONTACT'	=> $row[$sql_field], 
			'USERNAME'		=> addslashes($row['username']), 
			'EMAIL'			=> $row['user_email'], 
			'CONTACT_NAME'	=> $row[$sql_field], 
			'SITENAME'		=> addslashes($config['sitename']),

			'PRESENCE_IMG'		=> $presence_img, 

			'L_SEND_IM_EXPLAIN'	=> $user->lang['IM_' . $lang], 
			'L_IM_SENT_JABBER'	=> sprintf($user->lang['IM_SENT_JABBER'], $row['username']), 

			$s_select			=> true, 
			'S_IM_ACTION'		=> $s_action)
		);

		break;

	case 'viewprofile':
		// Display a profile
		$page_title = sprintf($user->lang['VIEWING_PROFILE'], $row['username']);
		$template_html = 'memberlist_view.html';
		
		if ($user_id == ANONYMOUS)
		{
			trigger_error($user->lang['NO_USER']);
		}

		// Do the SQL thang
		$sql = 'SELECT g.group_id, g.group_name, g.group_type 
			FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . " ug 
			WHERE ug.user_id = $user_id 
				AND g.group_id = ug.group_id" . (($auth->acl_get('a_groups'))? ' AND g.group_type <> ' . GROUP_HIDDEN : '') . '  
			ORDER BY group_type, group_name';
		$result = $db->sql_query($sql);

		$group_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$group_options .= '<option value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
		}

		// We left join on the session table to see if the user is currently online
		$sql = 'SELECT username, user_id, user_colour, user_permissions, user_karma, user_sig, user_sig_bbcode_uid, user_sig_bbcode_bitfield, user_allow_viewemail, user_posts, user_regdate, user_rank, user_from, user_occ, user_interests, user_website, user_email, user_icq, user_aim, user_yim, user_msnm, user_jabber, user_avatar, user_avatar_width, user_avatar_height, user_avatar_type, user_lastvisit   
			FROM ' . USERS_TABLE . " 
			WHERE user_id = $user_id";
		$result = $db->sql_query($sql);

		if (!($member = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_USER']);
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT MAX(session_time) AS session_time 
			FROM ' . SESSIONS_TABLE . "
			WHERE session_user_id = $user_id";
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$member['session_time'] = (isset($row['session_time'])) ? $row['session_time'] : 0;
		unset($row);

		// Obtain list of forums where this users post count is incremented
		$auth2 = new auth();
		$auth2->acl($member);
		$f_postcount_ary = $auth2->acl_getf('f_postcount');
		
		$sql_forums = array();
		foreach ($f_postcount_ary as $forum_id => $allow)
		{
			if ($allow)
			{
				$sql_forums[] = $forum_id;
			}
		}

		$post_count_sql = (sizeof($sql_forums)) ? 'AND f.forum_id IN (' . implode(', ', $sql_forums) . ')' : '';
		unset($sql_forums);
		unset($f_postcount_ary);
		unset($auth2);

		// Grab all the relevant data
		$sql = 'SELECT COUNT(p.post_id) AS num_posts   
			FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . " f
			WHERE p.poster_id = $user_id 
				AND f.forum_id = p.forum_id 
				$post_count_sql";
		$result = $db->sql_query($sql);

		$num_real_posts = min($user->data['user_posts'], $db->sql_fetchfield('num_posts', 0, $result));
		$db->sql_freeresult($result);

		$sql = 'SELECT f.forum_id, f.forum_name, COUNT(post_id) AS num_posts   
			FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . " f 
			WHERE p.poster_id = $user_id 
				AND f.forum_id = p.forum_id 
				$post_count_sql
			GROUP BY f.forum_id, f.forum_name  
			ORDER BY num_posts DESC"; 
		$result = $db->sql_query_limit($sql, 1);

		$active_f_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$sql = 'SELECT t.topic_id, t.topic_title, COUNT(p.post_id) AS num_posts   
			FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . " f  
			WHERE p.poster_id = $user_id 
				AND t.topic_id = p.topic_id  
				AND f.forum_id = t.forum_id 
				$post_count_sql
			GROUP BY t.topic_id, t.topic_title  
			ORDER BY num_posts DESC";
		$result = $db->sql_query_limit($sql, 1);

		$active_t_row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		// Do the relevant calculations 
		$memberdays = max(1, round((time() - $member['user_regdate']) / 86400));
		$posts_per_day = $member['user_posts'] / $memberdays;
		$percentage = ($config['num_posts']) ? min(100, ($num_real_posts / $config['num_posts']) * 100) : 0;

		$active_f_name = $active_f_id = $active_f_count = $active_f_pct = '';
		if (!empty($active_f_row['num_posts']))
		{
			$active_f_name = $active_f_row['forum_name'];
			$active_f_id = $active_f_row['forum_id'];
			$active_f_count = $active_f_row['num_posts'];
			$active_f_pct = ($active_f_count / $member['user_posts']) * 100;
		}
		unset($active_f_row);

		$active_t_name = $active_t_id = $active_t_count = $active_t_pct = '';
		if (!empty($active_t_row['num_posts']))
		{
			$active_t_name = $active_t_row['topic_title'];
			$active_t_id = $active_t_row['topic_id'];
			$active_t_count = $active_t_row['num_posts'];
			$active_t_pct = ($active_t_count / $member['user_posts']) * 100;
		}
		unset($active_t_row);

		if ($member['user_sig_bbcode_bitfield'] && $member['user_sig'])
		{
			include_once($phpbb_root_path . 'includes/bbcode.'.$phpEx);
			$bbcode = new bbcode();
			$bbcode->bbcode_second_pass($member['user_sig'], $member['user_sig_bbcode_uid'], $member['user_sig_bbcode_bitfield']);
		}

		if ($member['user_sig'])
		{
			$member['user_sig'] = ($config['allow_smilies']) ? preg_replace('#<!\-\- s(.*?) \-\-><img src="\{SMILE_PATH\}\/.*? \/><!\-\- s\1 \-\->#', '\1', $member['user_sig']) : str_replace('<img src="{SMILE_PATH}', '<img src="' . $config['smilies_path'], $member['user_sig']);
		}

		$poster_avatar = '';
		if (!empty($member['user_avatar']))
		{
			switch ($member['user_avatar_type'])
			{
				case AVATAR_UPLOAD:
					$poster_avatar = $config['avatar_path'] . '/';
					break;
				case AVATAR_GALLERY:
					$poster_avatar = $config['avatar_gallery_path'] . '/';
					break;
			}
			$poster_avatar .= $member['user_avatar'];

			$poster_avatar = '<img src="' . $poster_avatar . '" width="' . $member['user_avatar_width'] . '" height="' . $member['user_avatar_height'] . '" border="0" alt="" />';
		}

		$template->assign_vars(show_profile($member));

		$template->assign_vars(array(
			'POSTS_DAY'			=> sprintf($user->lang['POST_DAY'], $posts_per_day),
			'POSTS_PCT'			=> sprintf($user->lang['POST_PCT'], $percentage),
			'ACTIVE_FORUM'		=> $active_f_name, 
			'ACTIVE_FORUM_POSTS'=> ($active_f_count == 1) ? sprintf($user->lang['USER_POST'], 1) : sprintf($user->lang['USER_POSTS'], $active_f_count), 
			'ACTIVE_FORUM_PCT'	=> sprintf($user->lang['POST_PCT'], $active_f_pct), 
			'ACTIVE_TOPIC'		=> $active_t_name,
			'ACTIVE_TOPIC_POSTS'=> ($active_t_count == 1) ? sprintf($user->lang['USER_POST'], 1) : sprintf($user->lang['USER_POSTS'], $active_t_count), 
			'ACTIVE_TOPIC_PCT'	=> sprintf($user->lang['POST_PCT'], $active_t_pct), 

			'OCCUPATION'	=> (!empty($member['user_occ'])) ? $member['user_occ'] : '',
			'INTERESTS'		=> (!empty($member['user_interests'])) ? $member['user_interests'] : '',
			'SIGNATURE'		=> (!empty($member['user_sig'])) ? str_replace("\n", '<br />', $member['user_sig']) : '', 

			'AVATAR_IMG'	=> $poster_avatar,
			'PM_IMG'		=> $user->img('btn_pm', $user->lang['MESSAGE']),
			'EMAIL_IMG'		=> $user->img('btn_email', $user->lang['EMAIL']),
			'WWW_IMG'		=> $user->img('btn_www', $user->lang['WWW']),
			'ICQ_IMG'		=> $user->img('btn_icq', $user->lang['ICQ']),
			'AIM_IMG'		=> $user->img('btn_aim', $user->lang['AIM']),
			'MSN_IMG'		=> $user->img('btn_msnm', $user->lang['MSNM']),
			'YIM_IMG'		=> $user->img('btn_yim', $user->lang['YIM']),
			'JABBER_IMG'	=> $user->img('btn_jabber', $user->lang['JABBER']), 
			'SEARCH_IMG'	=> $user->img('btn_search', $user->lang['SEARCH']), 

			'S_PROFILE_ACTION'	=> "groupcp.$phpEx$SID", 
			'S_GROUP_OPTIONS'	=> $group_options, 

			'U_ADD_FRIEND'		=> "ucp.$phpEx$SID&amp;i=zebra&amp;add=" . urlencode($member['username']), 
			'U_ACTIVE_FORUM'	=> "viewforum.$phpEx$SID&amp;f=$active_f_id",
			'U_ACTIVE_TOPIC'	=> "viewtopic.$phpEx$SID&amp;t=$active_t_id",)
		);
		break;

	case 'email':
		// Send an email
		$page_title = $user->lang['SEND_EMAIL'];
		$template_html = 'memberlist_email.html';

		if (empty($config['email_enable']))
		{
			trigger_error($user->lang['EMAIL_DISABLED']);
		}

		if (($user_id == ANONYMOUS || empty($config['board_email_form'])) && !$topic_id)
		{
			trigger_error($user->lang['NO_EMAIL']);
		}

		if (!$auth->acl_get('u_sendemail'))
		{
			trigger_error($user->lang['NO_EMAIL']);
		}

		// Are we trying to abuse the facility?
		if (time() - $user->data['user_emailtime'] < $config['flood_interval'])
		{
			trigger_error($lang['FLOOD_EMAIL_LIMIT']);
		}

		$name		= strip_tags(request_var('name', ''));
		$email		= strip_tags(request_var('email', ''));
		$email_lang = request_var('lang', '');
		$subject	= request_var('subject', '');
		$message	= request_var('message', '');
		$cc			= (!empty($_POST['cc_email'])) ? true : false;

		// Are we sending an email to a user on this board? Or are we sending a
		// topic heads-up message?
		if (!$topic_id)
		{
			// Get the appropriate username, etc.
			$sql = 'SELECT username, user_email, user_allow_viewemail, user_lang, user_jabber, user_notify_type 
				FROM ' . USERS_TABLE . "
				WHERE user_id = $user_id
					AND user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
			$result = $db->sql_query($sql);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_USER']);
			}
			$db->sql_freeresult($result);

			// Can we send email to this user?
			if (empty($row['user_allow_viewemail']) && !$auth->acl_get('a_user'))
			{
				trigger_error($user->lang['NO_EMAIL']);
			}
		}
		else
		{
			$sql = 'SELECT forum_id, topic_title 
				FROM ' . TOPICS_TABLE . "
				WHERE topic_id = $topic_id";
			$result = $db->sql_query($sql);

			if (!($row = $db->sql_fetchrow($result)))
			{
				trigger_error($user->lang['NO_TOPIC']);
			}
			$db->sql_freeresult($result);

			if (!$auth->acl_get('f_read', $row['forum_id']))
			{
				trigger_error($user->lang['NO_FORUM_READ']);
			}

			if (!$auth->acl_get('f_email', $row['forum_id']))
			{
				trigger_error($user->lang['NO_EMAIL']);
			}
		}

		// User has submitted a message, handle it
		$error = array();
		if ($submit)
		{
			if (!$topic_id)
			{
				if (!$subject) 
				{
					$error[] = $user->lang['EMPTY_SUBJECT_EMAIL'];
				}

				if (!$message) 
				{
					$error[] = $user->lang['EMPTY_MESSAGE_EMAIL'];
				}
			}
			else
			{
				if (!$email || !preg_match('#^.*?@(.*?\.)?[a-z0-9\-]+\.[a-z]{2,4}$#i', $email)) 
				{
					$error[] = $user->lang['EMPTY_ADDRESS_EMAIL'];
				}

				if (!$name) 
				{
					$error[] = $user->lang['EMPTY_NAME_EMAIL'];
				}
			}

			if (!sizeof($error))
			{
				$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_emailtime = ' . time() . '
					WHERE user_id = ' . $user->data['user_id'];
				$result = $db->sql_query($sql);

				include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

				$email_tpl	= (!$topic_id) ? 'profile_send_email' : 'email_notify';
				$email_lang = (!$topic_id) ? $row['user_lang'] : $email_lang;
				$email		= (!$topic_id) ? $row['user_email'] : $email;

				$messenger = new messenger();

				$messenger->template($email_tpl, $email_lang);
				$messenger->subject($subject);

				$messenger->replyto($user->data['user_email']);
				$messenger->to($email, $row['username']);

				if (!$topic_id)
				{
					$messenger->im($row['user_jabber'], $row['username']);
				}

				if ($cc)
				{
					$messenger->cc($user->data['user_email'], $user->data['username']);
				}

				$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

				$messenger->assign_vars(array(
					'SITENAME'		=> $config['sitename'],
					'BOARD_EMAIL'	=> $config['board_contact'],
					'FROM_USERNAME' => $user->data['username'],
					'TO_USERNAME'	=> ($topic_id) ? $name : $row['username'],
					'MESSAGE'		=> $message, 
					'TOPIC_NAME'	=> ($topic_id) ? strtr($row['topic_title'], array_flip(get_html_translation_table(HTML_ENTITIES))) : '', 
					
					'U_TOPIC'	=> ($topic_id) ? generate_board_url() . "/viewtopic.$phpEx?f=" . $row['forum_id'] . "&t=$topic_id" : '')
				);

				$messenger->send($row['user_notify_type']);
				$messenger->queue->save();

				meta_refresh(3, "index.$phpEx$SID");
				$message = (!$topic_id) ? sprintf($user->lang['RETURN_INDEX'],  '<a href="' . "index.$phpEx$SID" . '">', '</a>') : sprintf($user->lang['RETURN_TOPIC'],  "<a href=\"viewtopic.$phpEx$SID&amp;f=$forum_id&amp;t=" . $row['topic_id'] . '">', '</a>'); 
				trigger_error($user->lang['EMAIL_SENT'] . '<br /><br />' . $message);
			}
		}

		if ($topic_id)
		{
			$template->assign_vars(array(
				'EMAIL'			=> htmlspecialchars($email), 
				'NAME'			=> htmlspecialchars($name), 
				'TOPIC_TITLE'	=> $row['topic_title'], 

				'U_TOPIC'	=> "viewtopic.$phpEx$SID&amp;f=" . $row['forum_id'] . "&amp;t=topic_id", 

				'S_LANG_OPTIONS'=> ($topic_id) ? language_select($email_lang) : '')
			);
		}
		$template->assign_vars(array(
			'USERNAME'		=> (!$topic_id) ? addslashes($row['username']) : '',  
			'ERROR_MESSAGE'	=> (sizeof($error)) ? implode('<br />', $error) : '', 

			'L_EMAIL_BODY_EXPLAIN'	=> (!$topic_id) ? $user->lang['EMAIL_BODY_EXPLAIN'] : $user->lang['EMAIL_TOPIC_EXPLAIN'], 

			'S_POST_ACTION' => (!$topic_id) ? "memberlist.$phpEx$SID&amp;mode=email&amp;u=$user_id" : "memberlist.$phpEx$SID&amp;mode=email&amp;f=$forum_id&amp;t=$topic_id",
			'S_SEND_USER'	=> (!$topic_id) ? true : false)
		);
		break;

	default:
		// The basic memberlist
		$page_title = $user->lang['MEMBERLIST'];
		$template_html = 'memberlist_body.html';

		// Sorting
		$sort_key_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_LOCATION'], 'c' => $user->lang['SORT_JOINED'], 'd' => $user->lang['SORT_POST_COUNT'], 'e' => $user->lang['SORT_EMAIL'], 'f' => $user->lang['WEBSITE'], 'g' => $user->lang['ICQ'], 'h' => $user->lang['AIM'], 'i' => $user->lang['MSNM'], 'j' => $user->lang['YIM'], 'k' => $user->lang['SORT_LAST_ACTIVE'], 'l' => $user->lang['SORT_RANK']);
		$sort_key_sql = array('a' => 'username', 'b' => 'user_from', 'c' => 'user_regdate', 'd' => 'user_posts', 'e' => 'user_email', 'f' => 'user_website', 'g' => 'user_icq', 'h' => 'user_aim', 'i' => 'user_msnm', 'j' => 'user_yim', 'k' => 'user_lastvisit', 'l' => 'user_rank DESC, user_posts');

		$sort_dir_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);

		$s_sort_key = '';
		foreach ($sort_key_text as $key => $value)
		{
			$selected = ($sort_key == $key) ? ' selected="selected"' : '';
			$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		$s_sort_dir = '';
		foreach ($sort_dir_text as $key => $value)
		{
			$selected = ($sort_dir == $key) ? ' selected="selected"' : '';
			$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		// Additional sorting options for user search ... if search is enabled, if not
		// then only admins can make use of this (for ACP functionality)
		$where_sql = '';
		if ($mode == 'searchuser' && ($config['load_search'] || $auth->acl_get('a_')))
		{
			$form	= request_var('form', '');
			$field	= request_var('field', 'username');

			$username	= request_var('username', '');
			$email		= request_var('email', '');
			$icq		= request_var('icq', '');
			$aim		= request_var('aim', '');
			$yahoo		= request_var('yahoo', '');
			$msn		= request_var('msn', '');

			$joined_select	= request_var('joined_select', 'lt');
			$active_select	= request_var('active_select', 'lt');
			$count_select	= request_var('count_select', 'eq');
			$joined			= explode('-', request_var('joined', ''));
			$active			= explode('-', request_var('active', ''));
			$count			= request_var('count', 0);
			$ipdomain		= request_var('ip', '');

			$find_key_match = array('lt' => '<', 'gt' => '>', 'eq' => '=');

			$find_count = array('lt' => $user->lang['LESS_THAN'], 'eq' => $user->lang['EQUAL_TO'], 'gt' => $user->lang['MORE_THAN']);
			$s_find_count = '';
			foreach ($find_count as $key => $value)
			{
				$selected = ($count_select == $key) ? ' selected="selected"' : '';
				$s_find_count .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			}

			$find_time = array('lt' => $user->lang['BEFORE'], 'gt' => $user->lang['AFTER']);
			$s_find_join_time = '';
			foreach ($find_time as $key => $value)
			{
				$selected = ($joined_select == $key) ? ' selected="selected"' : '';
				$s_find_join_time .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			}

			$s_find_active_time = '';
			foreach ($find_time as $key => $value)
			{
				$selected = ($active_select == $key) ? ' selected="selected"' : '';
				$s_find_active_time .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			}

			$where_sql .= ($username) ? " AND username LIKE '" . str_replace('*', '%', $db->sql_escape($username)) ."'" : '';
			$where_sql .= ($email) ? " AND user_email LIKE '" . str_replace('*', '%', $db->sql_escape($email)) ."' " : '';
			$where_sql .= ($icq) ? " AND user_icq LIKE '" . str_replace('*', '%', $db->sql_escape($icq)) ."' " : '';
			$where_sql .= ($aim) ? " AND user_aim LIKE '" . str_replace('*', '%', $db->sql_escape($aim)) ."' " : '';
			$where_sql .= ($yahoo) ? " AND user_yim LIKE '" . str_replace('*', '%', $db->sql_escape($yahoo)) ."' " : '';
			$where_sql .= ($msn) ? " AND user_msnm LIKE '" . str_replace('*', '%', $db->sql_escape($msn)) ."' " : '';
			$where_sql .= ($count) ? " AND user_posts " . $find_key_match[$count_select] . " $count " : '';
			$where_sql .= (sizeof($joined) > 1) ? " AND user_regdate " . $find_key_match[$joined_select] . ' ' . gmmktime(0, 0, 0, intval($joined[1]), intval($joined[2]), intval($joined[0])) : '';
			$where_sql .= (sizeof($active) > 1) ? " AND user_lastvisit " . $find_key_match[$active_select] . ' ' . gmmktime(0, 0, 0, $active[1], intval($active[2]), intval($active[0])) : '';

			if ($ipdomain)
			{
				$ips = (preg_match('#[a-z]#', $ipdomain)) ? implode(', ', preg_replace('#([0-9]{1,3}\.[0-9]{1,3}[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})#', "'\\1'", gethostbynamel($ipdomain))) : "'" . str_replace('*', '%', $ipdomain) . "'";

				$sql = 'SELECT DISTINCT poster_id 
					FROM ' . POSTS_TABLE . ' 
					WHERE poster_ip ' . ((preg_match('#%#', $ips)) ? 'LIKE' : 'IN') . " ($ips)";
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					$ip_sql = array();
					do
					{
						$ip_sql[] = $row['poster_id'];
					}
					while ($row = $db->sql_fetchrow($result));

					$where_sql .= ' AND user_id IN (' . implode(', ', $ip_sql) . ')';
				}
				else
				{
					// A minor fudge but it does the job :D
					$where_sql .= " AND user_id IN ('-1')";
				}
			}
		}

		// Sorting and order
		$order_by = $sort_key_sql[$sort_key] . '  ' . (($sort_dir == 'a') ? 'ASC' : 'DESC');

		// Count the users ...
		if ($where_sql)
		{
			$sql = 'SELECT COUNT(user_id) AS total_users
				FROM ' . USERS_TABLE . '
				WHERE user_type <> ' . USER_IGNORE . "
				$where_sql";
			$result = $db->sql_query($sql);

			$total_users = ($row = $db->sql_fetchrow($result)) ? $row['total_users'] : 0;
		}
		else
		{
			$total_users = $config['num_users'];
		}

		// Pagination string
		$pagination_url = "memberlist.$phpEx$SID&amp;mode=$mode";

		// Build a relevant pagination_url
		$global_var = ($submit) ? '_POST' : '_GET';
		foreach ($$global_var as $key => $var)
		{
			if (in_array($key, array('submit', 'start', 'mode')) || !$var)
			{
				continue;
			}
			$pagination_url .= '&amp;' . $key . '=' . urlencode(htmlspecialchars($var));
		}

		// Some search user specific data
		if ($mode == 'searchuser' && ($config['load_search'] || $auth->acl_get('a_')))
		{
			$template->assign_vars(array(
				'USERNAME'	=> $username,
				'EMAIL'		=> $email,
				'ICQ'		=> $icq,
				'AIM'		=> $aim,
				'YAHOO'		=> $yahoo,
				'MSNM'		=> $msn,
				'JOINED'	=> implode('-', $joined),
				'ACTIVE'	=> implode('-', $active),
				'COUNT'		=> $count,  
				'IP'		=> $ipdomain, 

				'S_SEARCH_USER' 		=> true,
				'S_FORM_NAME' 			=> $form,
				'S_FIELD_NAME' 			=> $field,
				'S_COUNT_OPTIONS' 		=> $s_find_count,
				'S_SORT_OPTIONS' 		=> $s_sort_key,
				'S_USERNAME_OPTIONS'	=> $username_list,
				'S_JOINED_TIME_OPTIONS' => $s_find_join_time,
				'S_ACTIVE_TIME_OPTIONS' => $s_find_active_time,
				'S_SEARCH_ACTION' 		=> "memberslist.$phpEx$SID&amp;mode=searchuser&amp;field=$field")
			);
		}

		// TODO
		// ?????????
		$sql = 'SELECT session_user_id, MAX(session_time) AS session_time 
			FROM ' . SESSIONS_TABLE . ' 
			WHERE session_time >= ' . (time() - 300) . '
				AND session_user_id <> ' . ANONYMOUS . '
			GROUP BY session_user_id';
		$result = $db->sql_query($sql);

		$session_times = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$session_times[$row['session_user_id']] = $row['session_time'];
		}
		$db->sql_freeresult($result);

		// Do the SQL thang
		$sql = 'SELECT username, user_id, user_colour, user_allow_viewemail, user_posts, user_regdate, user_rank, user_from, user_website, user_email, user_icq, user_aim, user_yim, user_msnm, user_avatar, user_avatar_type, user_lastvisit
			FROM ' . USERS_TABLE . ' 
			WHERE user_type IN (' . USER_NORMAL . ', ' . USER_FOUNDER . ")
				$where_sql 
			ORDER BY $order_by";
		$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

		if ($row = $db->sql_fetchrow($result))
		{
			$i = 0;
			do
			{
				$row['session_time'] = (!empty($session_times[$row['user_id']])) ? $session_times[$row['user_id']] : '';

				$template->assign_block_vars('memberrow', array_merge(show_profile($row), array(
					'ROW_NUMBER'	=> $i + ($start + 1),

					'S_ROW_COUNT'	=> $i,

					'U_VIEWPROFILE'		=> "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=" . $row['user_id']))
				);

				$i++;
			}
			while ($row = $db->sql_fetchrow($result));
	}

	// Generate page
	$template->assign_vars(array(
		'PAGINATION' 	=> generate_pagination($pagination_url, $total_users, $config['topics_per_page'], $start),
		'PAGE_NUMBER' 	=> on_page($total_users, $config['topics_per_page'], $start), 
		'TOTAL_USERS'	=> ($total_users == 1) ? $user->lang['LIST_USER'] : sprintf($user->lang['LIST_USERS'], $total_users), 

		'PROFILE_IMG'	=> $user->img('btn_profile', $user->lang['PROFILE']), 
		'PM_IMG'		=> $user->img('btn_pm', $user->lang['MESSAGE']),
		'EMAIL_IMG'		=> $user->img('btn_email', $user->lang['EMAIL']),
		'WWW_IMG'		=> $user->img('btn_www', $user->lang['WWW']),
		'ICQ_IMG'		=> $user->img('btn_icq', $user->lang['ICQ']),
		'AIM_IMG'		=> $user->img('btn_aim', $user->lang['AIM']),
		'MSN_IMG'		=> $user->img('btn_msnm', $user->lang['MSNM']),
		'YIM_IMG'		=> $user->img('btn_yim', $user->lang['YIM']),
		'JABBER_IMG'	=> $user->img('btn_jabber', $user->lang['JABBER']), 
		'SEARCH_IMG'	=> $user->img('btn_search', $user->lang['SEARCH']), 

		'U_FIND_MEMBER'		=> (!empty($config['load_search']) || $auth->acl_get('a_')) ? "memberlist.$phpEx$SID&amp;mode=searchuser" : '', 
		'U_SORT_USERNAME'	=> "memberlist.$phpEx$SID&amp;sk=a&amp;sd=" . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_FROM'		=> "memberlist.$phpEx$SID&amp;sk=b&amp;sd=" . (($sort_key == 'b' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_JOINED'		=> "memberlist.$phpEx$SID&amp;sk=c&amp;sd=" . (($sort_key == 'c' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_POSTS'		=> "memberlist.$phpEx$SID&amp;sk=d&amp;sd=" . (($sort_key == 'd' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_EMAIL'		=> "memberlist.$phpEx$SID&amp;sk=e&amp;sd=" . (($sort_key == 'e' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_WEBSITE'	=> "memberlist.$phpEx$SID&amp;sk=f&amp;sd=" . (($sort_key == 'f' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_ICQ'		=> "memberlist.$phpEx$SID&amp;sk=g&amp;sd=" . (($sort_key == 'g' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_AIM'		=> "memberlist.$phpEx$SID&amp;sk=h&amp;sd=" . (($sort_key == 'h' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_MSN'		=> "memberlist.$phpEx$SID&amp;sk=i&amp;sd=" . (($sort_key == 'i' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_YIM'		=> "memberlist.$phpEx$SID&amp;sk=j&amp;sd=" . (($sort_key == 'j' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_ACTIVE'		=> "memberlist.$phpEx$SID&amp;sk=k&amp;sd=" . (($sort_key == 'k' && $sort_dir == 'a') ? 'd' : 'a'), 
		'U_SORT_RANK'		=> "memberlist.$phpEx$SID&amp;sk=l&amp;sd=" . (($sort_key == 'l' && $sort_dir == 'a') ? 'd' : 'a'), 

		'S_MODE_SELECT' => $s_sort_key,
		'S_ORDER_SELECT'=> $s_sort_dir,
		'S_MODE_ACTION' => "memberlist.$phpEx$SID&amp;mode=$mode&amp;form=$form")
	);
}


// Output the page
page_header($page_title);

$template->set_filenames(array(
	'body' => $template_html)
);
make_jumpbox('viewforum.'.$phpEx);

page_footer();


// ---------
// FUNCTIONS 
//
function show_profile($data)
{
	global $config, $auth, $template, $user, $ranks, $SID, $phpEx;

	$username = $data['username'];
	$user_id = $data['user_id'];

	$rank_title = $rank_img = '';
	if (!empty($data['user_rank']))
	{
		$rank_title = $ranks['special'][$data['user_rank']]['rank_title'];
		$rank_img = (!empty($ranks['special'][$data['user_rank']]['rank_image'])) ? '<img src="' . $config['ranks_path'] . '/' . $ranks['special'][$data['user_rank']]['rank_image'] . '" border="0" alt="' . $ranks['special'][$data['user_rank']]['rank_title'] . '" title="' . $ranks['special'][$data['user_rank']]['rank_title'] . '" /><br />' : '';
	}
	else
	{
		foreach ($ranks['normal'] as $rank)
		{
			if ($data['user_posts'] >= $rank['rank_min'])
			{
				$rank_title = $rank['rank_title'];
				$rank_img = (!empty($rank['rank_image'])) ? '<img src="' . $config['ranks_path'] . '/' . $rank['rank_image'] . '" border="0" alt="' . $rank['rank_title'] . '" title="' . $rank['rank_title'] . '" /><br />' : '';
				break;
			}
		}
	}

	$email = (!empty($data['user_allow_viewemail']) || $auth->acl_get('a_email')) ? ((!empty($config['board_email_form'])) ? "memberlist.$phpEx$SID&amp;mode=email&amp;u=$user_id" : 'mailto:' . $row['user_email']) : '';

	$last_visit = (!empty($data['session_time'])) ? $data['session_time'] : $data['user_lastvisit'];

	// Dump it out to the template
	// TODO 
	// Add permission check for IM clients
	return array(
		'USERNAME'		=> $username, 
		'USER_COLOR'	=> (!empty($data['user_colour'])) ? $data['user_colour'] : '', 
		'RANK_TITLE'	=> $rank_title, 
		'KARMA'			=> ($config['enable_karma']) ? $user->lang['KARMA'][$data['user_karma']] : '',  
		'JOINED'		=> $user->format_date($data['user_regdate'], $user->lang['DATE_FORMAT']),
		'VISITED'		=> (empty($last_visit)) ? ' - ' : $user->format_date($last_visit, $user->lang['DATE_FORMAT']),
		'POSTS'			=> ($data['user_posts']) ? $data['user_posts'] : 0,

		'KARMA_IMG'		=>($config['enable_karma']) ? $user->img('karma_center', $user->lang['KARMA'][$data['user_karma']], false, (int) $data['user_karma']) : '',  
		'ONLINE_IMG'	=> (intval($data['session_time']) >= time() - ($config['load_online_time'] * 60)) ? $user->img('btn_online', $user->lang['USER_ONLINE']) : $user->img('btn_offline', $user->lang['USER_ONLINE']), 
		'RANK_IMG'		=> $rank_img,
		'ICQ_STATUS_IMG'=> (!empty($data['user_icq'])) ? '<img src="http://web.icq.com/whitepages/online?icq=' . $data['user_icq'] . '&img=5" width="18" height="18" border="0" />' : '',

		'U_PROFILE'		=> "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u=$user_id", 
		'U_SEARCH_USER'	=> ($auth->acl_get('u_search')) ? "search.$phpEx$SID&amp;search_author=" . urlencode($username) . "&amp;show_results=posts" : '', 
		'U_PM'			=> ($auth->acl_get('u_sendpm')) ? "ucp.$phpEx$SID&amp;mode=pm&amp;action=send&amp;u=$user_id" : '',
		'U_EMAIL'		=> $email,
		'U_WWW'			=> (!empty($data['user_website'])) ? $data['user_website'] : '',
		'U_ICQ'			=> ($data['user_icq']) ? "memberlist.$phpEx$SID&amp;mode=contact&amp;action=icq&amp;u=$user_id" : '',
		'U_AIM'			=> ($data['user_aim']) ? "memberlist.$phpEx$SID&amp;mode=contact&amp;action=aim&amp;u=$user_id" : '',
		'U_YIM'			=> ($data['user_yim']) ? 'http://edit.yahoo.com/config/send_webmesg?.target=' . $row['user_yim'] . '&.src=pg' : '',
		'U_MSN'			=> ($data['user_msn']) ? "memberlist.$phpEx$SID&amp;mode=contact&amp;action=msn&amp;u=$user_id" : '',
		'U_JABBER'		=> ($data['user_jabber']) ? "memberlist.$phpEx$SID&amp;mode=contact&amp;action=jabber&amp;u=$user_id" : '',

		'S_ONLINE'	=> (intval($data['session_time']) >= time() - 300) ? true : false
	);
}
//
// FUNCTIONS 
// ---------

?>