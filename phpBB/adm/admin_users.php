<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_users.php
// STARTED   : Sat Feb 13, 2001
// COPYRIGHT : © 2001,2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

if (!empty($setmodules))
{
	if (!$auth->acl_gets('a_user', 'a_useradd', 'a_userdel'))
	{
		return;
	}

	$module['USER']['MANAGE_USERS'] = basename(__FILE__) . $SID;

	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);
include($phpbb_root_path.'includes/functions_user.'.$phpEx);

//
// Get and set basic vars
//
$mode		= request_var('mode', '');
$action		= request_var('action', 'overview');
$username	= request_var('username', '');
$user_id	= request_var('u', 0);
$ip			= request_var('ip', '');
$start		= request_var('start', 0);
$delete		= request_var('delete', '');
$quicktools	= request_var('quicktools', '');
$submit		= (isset($_POST['update'])) ? true : false;
$confirm	= (isset($_POST['confirm'])) ? true : false;
$cancel		= (isset($_POST['cancel'])) ? true : false;

$error = array();

//
// Whois output
//
if ($action == 'whois')
{
	// Output relevant page
	adm_page_header($user->lang['WHOIS']);

	if ($ip && $domain = gethostbyaddr($ip))
	{
?>

<table class="bg" width="90%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>IP whois for <?php echo $domain; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php

		if ($ipwhois = ipwhois($ip))
		{
			$ipwhois = preg_replace('#(\s+?)([\w\-\._\+]+?@[\w\-\.]+?)(\s+?)#s', '\1<a href="mailto:\2">\2</a>\3', $ipwhois);
			echo '<br /><pre align="left">' . trim($ipwhois) . '</pre>';
		}

?></td>
	</tr>
</table>

<br clear="all" />

<?php

	}

	adm_page_footer();
}

//
// Obtain user information if appropriate
//
if ($username || $user_id)
{
	$session_time = 0;
	$sql_where = ($user_id) ? "user_id = $user_id" : "username = '" . $db->sql_escape($username) . "'";
	$sql = ($action == 'overview') ? 'SELECT u.*, s.session_time, s.session_page, s.session_ip FROM (' . USERS_TABLE . ' u LEFT JOIN ' . SESSIONS_TABLE . " s ON s.session_user_id = u.user_id) WHERE u.$sql_where ORDER BY s.session_time DESC LIMIT 1" : 'SELECT * FROM ' . USERS_TABLE . " WHERE $sql_where";
	$result = $db->sql_query($sql);

	if (!extract($db->sql_fetchrow($result)))
	{
		trigger_error($user->lang['NO_USER']);
	}
	$db->sql_freeresult($result);

	if ($session_time > $user_lastvisit)
	{
		$user_lastvisit = $session_time;
		$user_lastpage = $session_page;
	}
	
	$user_password = '';
}

// Output page
adm_page_header($user->lang['MANAGE']);

//
// User has submitted a form, process it
//
if ($submit)
{
	switch ($action)
	{
		case 'overview':

			if ($delete && $user_type != USER_FOUNDER)
			{
				if (!$auth->acl_get('a_userdel'))
				{
					trigger_error($user->lang['NO_ADMIN']);
				}

				if (!$cancel && !$confirm)
				{
					adm_page_confirm($user->lang['CONFIRM'], $user->lang['CONFIRM_OPERATION']);
				}
				else if (!$cancel) 
				{
					$db->sql_transaction();

					switch ($deletetype)
					{
						case 'retain':
							$sql = 'UPDATE ' . FORUMS_TABLE . '
								SET forum_last_poster_id = ' . ANONYMOUS . " 
								WHERE forum_last_poster_id = $user_id";
				//			$db->sql_query($sql);

							$sql = 'UPDATE ' . POSTS_TABLE . '
								SET poster_id = ' . ANONYMOUS . " 
								WHERE poster_id = $user_id";
				//			$db->sql_query($sql);

							$sql = 'UPDATE ' . TOPICS_TABLE . '
								SET topic_poster = ' . ANONYMOUS . "
								WHERE topic_poster = $user_id";
				//			$db->sql_query($sql);
							break;

						case 'remove':
							break;
					}

					$table_ary = array(USERS_TABLE, USER_GROUP_TABLE, TOPICS_WATCH_TABLE, FORUMS_WATCH_TABLE, ACL_USERS_TABLE, TOPICS_TRACK_TABLE, FORUMS_TRACK_TABLE);

					foreach ($table_ary as $table)
					{
						$sql = "DELETE FROM $table 
							WHERE user_id = $user_id";
		//				$db->sql_query($sql);
					}

					// Reset newest user info if appropriate
					if ($config['newest_user_id'] == $user_id)
					{
						$sql = 'SELECT user_id, username 
							FROM ' . USERS_TABLE . ' 
							ORDER BY user_id DESC
							LIMIT 1';
						$result = $db->sql_query($sql);

						if ($row = $db->sql_fetchrow($result))
						{
							set_config('newest_user_id', $row['user_id']);
							set_config('newest_username', $row['username']);
						}
						$db->freeresult($result);
					}

					set_config('num_users', $config['num_users'] - 1, TRUE);

					$db->sql_transaction('commit');

					trigger_error($user->lang['USER_DELETED']);
				}
			}

			// Handle quicktool actions
			if ($quicktools && $user_type != USER_FOUNDER)
			{
				switch ($quicktools)
				{
					case 'banuser':
					case 'banemail':
					case 'banip':
						$ban = array();

						switch ($quicktools)
						{
							case 'banuser':
								$ban[] = $username;
								$reason = 'USER_ADMIN_BAN_NAME_REASON';
								break;

							case 'banemail':
								$ban[] = $user_email;
								$reason = 'USER_ADMIN_BAN_EMAIL_REASON';
								break;

							case 'banip':
								$ban[] = $user_ip;

								$sql = 'SELECT DISTINCT poster_ip 
									FROM ' . POSTS_TABLE . " 
									WHERE poster_id = $user_id";
								$result = $db->sql_query($sql);

								while ($row = $db->sql_fetchrow($result))
								{
									$ban[] = $row['poster_ip'];
								}
								$db->sql_freeresult($result);

								$reason = 'USER_ADMIN_BAN_IP_REASON';
								break;
						}

						user_ban(substr($quicktools, 3), $ban, 0, 0, 0, $user->lang[$reason]);

						trigger_error($user->lang['BAN_UPDATE_SUCESSFUL']);

						break;

					case 'reactivate':

						if ($config['email_enable'])
						{
							include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);

							$user_actkey = gen_rand_string(10);
							$key_len = 54 - (strlen($server_url));
							$key_len = ($key_len > 6) ? $key_len : 6;
							$user_actkey = substr($user_actkey, 0, $key_len);

							user_active_flip($user_id, $user_type, $user_actkey, $username);

							$messenger = new messenger();

							$messenger->template('user_welcome_inactive', $user_lang);
							$messenger->subject();

							$messenger->replyto($config['board_contact']);
							$messenger->to($user_email, $username);

							$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
							$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
							$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
							$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);

							$messenger->assign_vars(array(
								'SITENAME'		=> $config['sitename'],
								'WELCOME_MSG'	=> sprintf($user->lang['WELCOME_SUBJECT'], $config['sitename']),
								'USERNAME'		=> $username,
								'PASSWORD'		=> $password_confirm,
								'EMAIL_SIG'		=> str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']),

								'U_ACTIVATE'	=> generate_board_url() . "/ucp.$phpEx?mode=activate&u=$user_id&k=$user_actkey")
							);

							$messenger->send(NOTIFY_EMAIL);
							$messenger->queue->save();
						}

						break;

					case 'active':

						user_active_type($user_id, $user_type, false, $username);

						$message = ($user_type == USER_NORMAL) ? 'USER_ADMIN_INACTIVE' : 'USER_ADMIN_ACTIVE';
						trigger_error($user->lang[$message]);
						break;

					case 'moveposts':

						if (!($new_forum_id = request_var('new_f', 0)))
						{

?>

<h1><?php echo $user->lang['USER_ADMIN']; ?></h1>

<p><?php echo $user->lang['USER_ADMIN_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_users.$phpEx$SID&amp;action=$action&amp;quicktools=moveposts&amp;u=$user_id"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th align="center"><?php echo $user->lang['USER_ADMIN_MOVE_POSTS']; ?></th>
	</tr>
	<tr>
		<td class="row2" align="center" valign="middle"><?php echo $user->lang['MOVE_POSTS_EXPLAIN']; ?><br /><br /><select name="new_f"><?php 
	
			echo make_forum_select(false, false, false, true);
			
?></select>&nbsp;</td>
	</tr>
	<tr>
		<td class="cat" align="center"><input type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" /></td>
	</tr>
</table>
<?php

							adm_page_footer();
						}
						else
						{
							// Two stage?
							// Move topics comprising only posts from this user
							$topic_id_ary = array();
							$forum_id_ary = array($new_forum_id);

							$sql = 'SELECT topic_id, COUNT(post_id) AS total_posts 
								FROM ' . POSTS_TABLE . " 
								WHERE poster_id = $user_id
									AND forum_id <> $new_forum_id
								GROUP BY topic_id";
							$result = $db->sql_query($sql);

							while ($row = $db->sql_fetchrow($result))
							{
								$topic_id_ary[$row['topic_id']] = $row['total_posts'];
							}
							$db->sql_freeresult($result);

							$sql = 'SELECT topic_id, forum_id, topic_title, topic_replies, topic_replies_real 
								FROM ' . TOPICS_TABLE . ' 
								WHERE topic_id IN (' . implode(', ', array_keys($topic_id_ary)) . ')';
							$result = $db->sql_query($sql);

							$move_topic_ary = $move_post_ary = array();
							while ($row = $db->sql_fetchrow($result))
							{
								if (max($row['topic_replies'], $row['topic_replies_real']) + 1 == $topic_id_ary[$row['topic_id']])
								{
									$move_topic_ary[] = $row['topic_id'];
								}
								else
								{
									$move_post_ary[$row['topic_id']]['title'] = $row['topic_title'];
									$move_post_ary[$row['topic_id']]['attach'] = ($row['attach']) ? 1 : 0;
								}

								$forum_id_ary[] = $row['forum_id'];
							}
							$db->sql_freeresult($result);

							// Entire topic comprises posts by this user, move these topics
							if (sizeof($move_topic_ary))
							{
								move_topics($move_topic_ary, $new_forum_id, false);
							}

							if (sizeof($move_post_ary))
							{
								// Create new topic
								// Update post_ids, report_ids, attachment_ids
								foreach ($move_post_ary as $topic_id => $post_ary)
								{
									// Create new topic
									$sql = 'INSERT INTO ' . TOPICS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
										'topic_poster'				=> $user_id,
										'topic_time'				=> time(),
										'forum_id' 					=> $new_forum_id,
										'icon_id'					=> 0,
										'topic_approved'			=> 1, 
										'topic_title' 				=> $post_ary['title'],
										'topic_first_poster_name'	=> $username,
										'topic_type'				=> POST_NORMAL,
										'topic_time_limit'			=> 0,
										'topic_attachment'			=> $post_ary['attach'],)
									);
									$db->sql_query($sql);

									$new_topic_id = $db->sql_nextid();

									// Move posts
									$sql = 'UPDATE ' . POSTS_TABLE . "
										SET forum_id = $new_forum_id, topic_id = $new_topic_id 
										WHERE topic_id = $topic_id
											AND poster_id = $user_id";
									$db->sql_query($sql);

									if ($post_ary['attach'])
									{
										$sql = 'UPDATE ' . ATTACHMENTS_TABLE . "
											SET topic_id = $new_topic_id
											WHERE topic_id = $topic_id
												AND poster_id = $user_id";
										$db->sql_query($sql);
									}

									$new_topic_id_ary[] = $new_topic_id;
								}
							}

							$forum_id_ary = array_unique($forum_id_ary);
							$topic_id_ary = array_unique(array_merge($topic_id_ary, $new_topic_id_ary));

							sync('reported', 'topic_id', $topic_id_ary);
							sync('topic', 'topic_id', $topic_id_ary);
							sync('forum', 'forum_id', $forum_id_ary);
						}

						break;
				}

				trigger_error($message);
			}

			// Handle registration info updates
			$var_ary = array(
				'username'			=> (string) $username, 
				'user_founder'		=> (int) $user_founder, 
				'user_type'			=> (int) $user_type, 
				'user_email'		=> (string) $user_email, 
				'email_confirm'		=> (string) '',
				'user_password'		=> (string) '', 
				'password_confirm'	=> (string) '', 
				'user_warnings'		=> (int) $user_warnings, 
			);

			foreach ($var_ary as $var => $default)
			{
				$data[$var] = request_var($var, $default);
			}

			$var_ary = array(
				'password_confirm'	=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']), 
				'user_password'		=> array('string', true, $config['min_pass_chars'], $config['max_pass_chars']), 
				'user_email'		=> array(
					array('string', false, 6, 60), 
					array('email', $email)), 
				'email_confirm'		=> array('string', true, 6, 60), 
				'user_warnings'		=> array('num', 0, $config['max_warnings']), 
			);

			// Check username if altered
			if ($username != $data['username'])
			{
				$var_ary += array(
					'username'			=> array(
						array('string', false, $config['min_name_chars'], $config['max_name_chars']), 
						array('username', $username)),
				);
			}

			$error = validate_data($data, $var_ary);

			if ($data['user_password'] && $data['password_confirm'] != $data['user_password'])
			{
				$error[] = 'NEW_PASSWORD_ERROR';
			}

			if ($user_email != $data['user_email'] && $data['email_confirm'] != $data['user_email'])
			{
				$error[] = 'NEW_EMAIL_ERROR';
			}

			// Which updates do we need to do?
			$update_warning = ($user_warnings != $data['user_warnings']) ? true : false;
			$update_username = ($username != $data['username']) ? $username : false;
			$update_password = ($user_password != $data['user_password']) ? true : false;

			extract($data);
			unset($data);

			if (!sizeof($error))
			{
				$sql_ary = array(
					'username'			=> $username, 
					'user_founder'		=> $user_founder, 
					'user_email'		=> $user_email, 
					'user_email_hash'	=> crc32(strtolower($user_email)) . strlen($user_email), 
					'user_warnings'		=> $user_warnings, 
				);

				if ($update_password)
				{
					$sql_ary += array(
						'user_password' => md5($user_password),
						'user_passchg'	=> time(),
					);
				}

				$sql = 'UPDATE ' . USERS_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . ' 
					WHERE user_id = ' . $user->data['user_id'];
				$db->sql_query($sql);

				if ($update_warning)
				{
				}

				if ($update_username)
				{
					user_update_name($update_username, $username);
				}

				trigger_error($user->lang['USER_OVERVIEW_UPDATED']);
			}

			// Replace "error" strings with their real, localised form
			$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);

			break;

	}
}

//
// Output forms
//

// Begin program
if ($username || $user_id)
{
	// Generate overall "header" for user admin
	$form_options = '';
	$forms_ary = array('overview' => 'OVERVIEW', 'feedback' => 'FEEDBACK', 'profile' => 'PROFILE', 'prefs' => 'PREFS', 'avatar' => 'AVATAR', 'sig' => 'SIG', 'groups' => 'GROUP', 'perm' => 'PERM');

	foreach ($forms_ary as $value => $lang)
	{
		$selected = ($action == $value) ? ' selected="selected"' : '';
		$form_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang['USER_ADMIN_' . $lang]  . '</option>';
	}

	$pagination = '';

?>

<h1><?php echo $user->lang['USER_ADMIN']; ?></h1>

<p><?php echo $user->lang['USER_ADMIN_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_users.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;u=$user_id"; ?>"<?php echo ($can_upload) ? ' enctype="multipart/form-data"' : ''; ?>><table width="100%" cellspacing="2" cellpadding="0" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_FORM']; ?>: <select name="action" onchange="if (this.options[this.selectedIndex].value != '') this.form.submit();"><?php echo $form_options; ?></select></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_ADMIN_' . strtoupper($action)]; ?></th>
			</tr>
<?php

	if (sizeof($error))
	{

?>
			<tr>
				<td class="row3" colspan="2" align="center"><span class="error"><?php echo implode('<br />', $error); ?></span></td>
			</tr>
<?php

	}


	switch ($action)
	{
		case 'overview':

			$user_char_ary = array('.*' => 'USERNAME_CHARS_ANY', '[\w]+' => 'USERNAME_ALPHA_ONLY', '[\w_\+\. \-\[\]]+' => 'USERNAME_ALPHA_SPACERS');
			$quick_tool_ary = array('banuser' => 'BAN_USER', 'banemail' => 'BAN_EMAIL', 'banip' => 'BAN_IP', 'active' => (($user_type == USER_INACTIVE) ? 'ACTIVATE' : 'DEACTIVATE'), 'moveposts' => 'MOVE_POSTS');
			if ($config['email_enable']) 
			{
				$quick_tool_ary['reactivate'] = 'FORCE';
			}

			asort($quick_tool_ary);

			$options = '<option class="sep" value="">' . $user->lang['SELECT_OPTION'] . '</option>';
			foreach ($quick_tool_ary as $value => $lang)
			{
				$options .= '<option value="' . $value . '">' . $user->lang['USER_ADMIN_' . $lang]  . '</option>';
			}

			$user_founder_yes = ($user_type == USER_FOUNDER) ? ' checked="checked"' : '';
			$user_founder_no = ($user_type != USER_FOUNDER) ? ' checked="checked"' : (($user->data['user_type'] != USER_FOUNDER) ? ' disabled="disabled"' : '');

?>	
			<tr>
				<td class="row1" width="40%"><?php echo $user->lang['USERNAME']; ?>: <br /><span class="gensmall"><?php echo sprintf($user->lang[$user_char_ary[str_replace('\\\\', '\\', $config['allow_name_chars'])] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']); ?></span></td>
				<td class="row2"><input class="post" type="text" name="username" value="<?php echo $username; ?>" maxlength="60" /></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['REGISTERED']; ?>: </td>
				<td class="row2"><strong><?php echo $user->format_date($user_regdate); ?></strong></td>
			</tr>
<?php

			if ($user_ip)
			{

?>
			<tr>
				<td class="row1"><?php echo $user->lang['REGISTERED_IP']; ?>: </td>
				<td class="row2"><strong><?php echo "<a href=\"admin_users.$phpEx$SID&amp;action=$action&amp;u=$user_id&amp;ip=" . ((!$ip || $ip == 'ip') ? 'hostname' : 'ip') . '">' . (($ip == 'hostname') ? gethostbyaddr($user_ip) : $user_ip) . "</a> [ <a href=\"admin_users.$phpEx$SID&amp;action=whois&amp;ip=$user_ip\" onclick=\"window.open('admin_users.$phpEx$SID&amp;action=whois&amp;ip=$user_ip', '', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=600');return false;\">" . $user->lang['WHOIS'] . '</a> ]'; ?></strong></td>
			</tr>
<?php
						
			}
			
?>
			<tr>
				<td class="row1" width="40%"><?php echo $user->lang['LAST_ACTIVE']; ?>: </td>
				<td class="row2"><strong><?php echo $user->format_date($user_lastvisit); ?></strong></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['FOUNDER']; ?>: <br /><span class="gensmall"><?php echo $user->lang['FOUNDER_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="radio" name="user_founder" value="1"<?php echo $user_founder_yes; ?> /><?php echo $user->lang['YES']; ?>&nbsp;<input type="radio" name="user_founder" value="0"<?php echo $user_founder_no; ?> /><?php echo $user->lang['NO']; ?></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['EMAIL']; ?>: </td>
				<td class="row2"><input class="post" type="text" name="user_email" value="<?php echo $user_email; ?>" maxlength="60" /></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['CONFIRM_EMAIL']; ?>: <br /><span class="gensmall"><?php echo $user->lang['CONFIRM_EMAIL_EXPLAIN']; ?></span></td>
				<td class="row2"><input class="post" type="text" name="email_confirm" value="<?php echo $email_confirm; ?>" maxlength="60" /></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['NEW_PASSWORD']; ?>: <br /><span class="gensmall"><?php echo sprintf($user->lang['CHANGE_PASSWORD_EXPLAIN'], $config['min_pass_chars'], $config['max_pass_chars']) ?></span></td>
				<td class="row2"><input class="post" type="password" name="user_password" value="<?php echo ($submit) ? $user_password : ''; ?>" maxlength="60" /></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['CONFIRM_PASSWORD']; ?>: <br /><span class="gensmall"><?php echo $user->lang['CONFIRM_PASSWORD_EXPLAIN']; ?></span></td>
				<td class="row2"><input class="post" type="password" name="password_confirm" value="<?php echo ($submit) ? $user_password_confirm : ''; ?>" maxlength="60" /></td>
			</tr>
<?php

			if ($user_type != USER_FOUNDER)
			{

?>
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_TOOLS']; ?></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['WARNINGS']; ?>: <br /><span class="gensmall"><?php echo $user->lang['WARNINGS_EXPLAIN']; ?></span></td>
				<td class="row2"><input class="post" type="text" name="warnings" size="2" maxlength="2" value="<?php echo $user->data['user_warnings']; ?>" /></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['QUICK_TOOLS']; ?>: </td>
				<td class="row2"><select name="quicktools"><?php echo $options; ?></select></td>
			</tr>
			<tr>
				<td class="row1"><?php echo $user->lang['DELETE_USER']; ?>: <br /><span class="gensmall"><?php echo $user->lang['DELETE_USER_EXPLAIN']; ?></span></td>
				<td class="row2"><select name="deletetype"><option value="retain"><?php echo $user->lang['RETAIN_POSTS']; ?></option><option value="remove"><?php echo $user->lang['DELETE_POSTS']; ?></option></select> <input type="checkbox" name="delete" value="1" /> </td>
			</tr>
<?php

			}

			break;






		case 'feedback':

			if ($submit)
			{

			}

?>



<?php

			$sql = 'SELECT COUNT(user_id) AS total_reports
				FROM ' . USERS_NOTES_TABLE . "
				WHERE user_id = $user_id";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			$total_reports = $row['total_reports'];

			if ($total_reports)
			{
				$pagination = generate_pagination("admin_users.$phpEx$SID&amp;action=$action&amp;u=$user_id&amp;st=$sort_days&amp;sk=$sort_key&amp;sd=$sort_dir", $total_reports, $config['posts_per_page'], $start);

				$sql = 'SELECT u.username, n.* 
					FROM ' . USERS_NOTES_TABLE . ' n, ' . USERS_TABLE . " u  
					WHERE n.user_id = $user_id 
						AND u.user_id = n.reporter_id 
					ORDER BY n.report_log DESC, n.report_date DESC";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
				<tr>
					<td class="<?php echo $row_class; ?>"><span class="gensmall">Report by: <b><?php echo $row['username']; ?></b> on <?php echo $user->format_date($row['report_date']); ?></span><hr /><?php echo $row['report_text']; ?></td>
				</tr>
<?php

				}
				$db->sql_freeresult($result);
			}
			else
			{

?>
				<tr>
					<td class="row1" align="center">No reports exist for this user</td>
				</tr>
<?php

			}
			break;


		case 'profile':

			if (!isset($bday_day))
			{
				list($bday_day, $bday_month, $bday_year) = explode('-', $user_birthday);
			}

			$s_birthday_day_options = '<option value="0"' . ((!$bday_day) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = 1; $i < 32; $i++)
			{
				$selected = ($i == $bday_day) ? ' selected="selected"' : '';
				$s_birthday_day_options .= "<option value=\"$i\"$selected>$i</option>";
			}

			$s_birthday_month_options = '<option value="0"' . ((!$bday_month) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = 1; $i < 13; $i++)
			{
				$selected = ($i == $bday_month) ? ' selected="selected"' : '';
				$s_birthday_month_options .= "<option value=\"$i\"$selected>$i</option>";
			}
			$s_birthday_year_options = '';

			$now = getdate();
			$s_birthday_year_options = '<option value="0"' . ((!$bday_year) ? ' selected="selected"' : '') . '>--</option>';
			for ($i = $now['year'] - 100; $i < $now['year']; $i++)
			{
				$selected = ($i == $bday_year) ? ' selected="selected"' : '';
				$s_birthday_year_options .= "<option value=\"$i\"$selected>$i</option>";
			}
			unset($now);

?>
			<tr> 
				<td class="row1" width="40%"><b><?php echo $user->lang['UCP_ICQ']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="icq" size="30" maxlength="15" value="<?php echo $user_icq; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['UCP_AIM']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="aim" size="30" maxlength="255" value="<?php echo $user_aim; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['UCP_MSNM']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="msn" size="30" maxlength="255" value="<?php echo $user_msnm; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['UCP_YIM']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="yim" size="30" maxlength="255" value="<?php echo $user_yim; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['UCP_JABBER']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="jabber" size="30" maxlength="255" value="<?php echo $user_jabber; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['WEBSITE']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="website" size="30" maxlength="255" value="<?php echo $user_website; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['LOCATION']; ?>: </b></td>
				<td class="row2"><input class="post" type="text" name="location" size="30" maxlength="100" value="<?php echo $user_location; ?>" /></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['OCCUPATION']; ?>: </b></td>
				<td class="row2"><textarea class="post" name="occ" rows="3" cols="30"><?php echo $user_occ; ?></textarea></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['INTERESTS']; ?>: </b></td>
				<td class="row2"><textarea class="post" name="interests" rows="3" cols="30"><?php echo $user_interests; ?></textarea></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['BIRTHDAY']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['BIRTHDAY_EXPLAIN']; ?></span></td>
				<td class="row2"><span class="genmed"><?php echo $user->lang['DAY']; ?>:</span> <select name="bday_day"><?php echo $s_birthday_day_options; ?></select> <span class="genmed"><?php echo $user->lang['MONTH']; ?>:</span> <select name="bday_month"><?php echo $s_birthday_month_options; ?></select> <span class="genmed"><?php echo $user->lang['YEAR']; ?>:</span> <select name="bday_year"><?php echo $s_birthday_year_options; ?></select></td>
			</tr>
<?php

			break;


		case 'prefs':

?>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_IMAGES']; ?>:</b></td>
				<td class="row2"><input type="radio" name="images" value="1"{VIEW_IMAGES_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="images" value="0"{VIEW_IMAGES_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_FLASH']; ?>:</b></td>
				<td class="row2"><input type="radio" name="flash" value="1"{VIEW_FLASH_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="flash" value="0"{VIEW_FLASH_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_SMILIES']; ?>:</b></td>
				<td class="row2"><input type="radio" name="smilies" value="1"{VIEW_SMILIES_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="smilies" value="0"{VIEW_SMILIES_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_SIGS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="sigs" value="1"{VIEW_SIGS_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="sigs" value="0"{VIEW_SIGS_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_AVATARS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="avatars" value="1"{VIEW_AVATARS_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="avatars" value="0"{VIEW_AVATARS_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- IF S_CHANGE_CENSORS -->
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DISABLE_CENSORS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="wordcensor" value="1"{DISABLE_CENSORS_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="wordcensor" value="0"{DISABLE_CENSORS_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- ENDIF -->
			<tr>
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['MINIMUM_KARMA']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['MINIMUM_KARMA_EXPLAIN']; ?></span></td>
				<td class="row2"><select name="minkarma">{S_MIN_KARMA_OPTIONS}</select></td>

			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_TOPICS_DAYS']; ?>:</b></td>
				<td class="row2">{S_SELECT_SORT_DAYS}</td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_TOPICS_KEY']; ?>:</b></td>
				<td class="row2">{S_SELECT_SORT_KEY}</td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['VIEW_TOPICS_DIR']; ?>:</b></td>
				<td class="row2">{S_SELECT_SORT_DIR}</td>
			</tr>
			<tr>
				<th colspan="2">Posting preferences</th>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_BBCODE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="bbcode" value="1"{DEFAULT_BBCODE_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="bbcode" value="0"{DEFAULT_BBCODE_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_HTML']; ?>:</b></td>
				<td class="row2"><input type="radio" name="html" value="1"{DEFAULT_HTML_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="html" value="0"{DEFAULT_HTML_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_SMILE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="smilies" value="1"{DEFAULT_SMILIES_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="smilies" value="0"{DEFAULT_SMILIES_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_ADD_SIG']; ?>:</b></td>
				<td class="row2"><input type="radio" name="sig" value="1"{DEFAULT_SIG_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="sig" value="0"{DEFAULT_SIG_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['DEFAULT_NOTIFY']; ?>:</b></td>
				<td class="row2"><input type="radio" name="notify" value="1"{DEFAULT_NOTIFY_YES} /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp; &nbsp;<input type="radio" name="notify" value="0"{DEFAULT_NOTIFY_NO} /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr>
				<th colspan="2"></th>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['SHOW_EMAIL']; ?>:</b></td>
				<td class="row2"><input type="radio" name="viewemail" value="1"{VIEW_EMAIL_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="viewemail" value="0"{VIEW_EMAIL_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['ADMIN_EMAIL']; ?>:</b></td>
				<td class="row2"><input type="radio" name="massemail" value="1"{ADMIN_EMAIL_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="massemail" value="0"{ADMIN_EMAIL_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['ALLOW_PM']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_PM_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="radio" name="allowpm" value="1"{ALLOW_PM_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="allowpm" value="0"{ALLOW_PM_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- IF S_CAN_HIDE_ONLINE -->
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['HIDE_ONLINE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="hideonline" value="1"{HIDE_ONLINE_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="hideonline" value="0"{HIDE_ONLINE_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- ENDIF -->
			<!-- IF S_SELECT_NOTIFY -->
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['NOTIFY_METHOD']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['NOTIFY_METHOD_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="radio" name="notifymethod" value="0"{NOTIFY_EMAIL} /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_EMAIL']; ?></span>&nbsp;&nbsp;<input type="radio" name="notifymethod" value="1"{NOTIFY_IM} /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_IM']; ?></span>&nbsp;&nbsp;<input type="radio" name="notifymethod" value="2"{NOTIFY_BOTH} /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_BOTH']; ?></span></td>
			</tr>
			<!-- ENDIF -->
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['NOTIFY_ON_PM']; ?>:</b></td>
				<td class="row2"><input type="radio" name="notifypm" value="1"{NOTIFY_PM_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="notifypm" value="0"{NOTIFY_PM_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['POPUP_ON_PM']; ?>:</b></td>
				<td class="row2"><input type="radio" name="popuppm" value="1"{POPUP_PM_YES} /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="popuppm" value="0"{POPUP_PM_NO} /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_LANGUAGE']; ?>:</b></td>
				<td class="row2"><select name="lang">{S_LANG_OPTIONS}</select></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_STYLE']; ?>:</b></td>
				<td class="row2"><select name="style">{S_STYLE_OPTIONS}</select></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_TIMEZONE']; ?>:</b></td>
				<td class="row2"><select name="tz">{S_TZ_OPTIONS}</select></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_DST']; ?>:</b></td>
				<td class="row2"><input type="radio" name="dst" value="1"{DST_YES} /> <span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="dst" value="0"{DST_NO} /> <span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1" width="50%"><b class="genmed"><?php echo $user->lang['BOARD_DATE_FORMAT']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['BOARD_DATE_FORMAT_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="text" name="dateformat" value="{DATE_FORMAT}" maxlength="14" class="post" /></td>
			</tr>
<?php

			break;


		case 'avatar':
			$can_upload = (file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on')) ? true : false;

			if ($user_avatar)
			{
				switch ($user_avatar_type)
				{
					case AVATAR_UPLOAD:
						$avatar_img = $phpbb_root_path . $config['avatar_path'] . '/';
						break;
					case AVATAR_GALLERY:
						$avatar_img = $phpbb_root_path . $config['avatar_gallery_path'] . '/';
						break;
				}
				$avatar_img .= $user_avatar;

				$avatar_img = '<img src="' . $avatar_img . '" width="' . $user_avatar_width . '" height="' . $user_avatar_height . '" border="0" alt="" />';
			}
			else
			{
				$avatar_img = '<img src="images/no_avatar.gif" alt="" />';
			}

?>
			<tr> 
				<td class="row2" width="35%"><b><?php echo $user->lang['CURRENT_IMAGE']; ?>: </b><br /><span class="gensmall"><?php echo sprintf($user->lang['AVATAR_EXPLAIN'], $config['avatar_max_width'], $config['avatar_max_height'], round($config['avatar_filesize'] / 1024)); ?></span></td>
				<td class="row1" align="center"><br /><?php echo $avatar_img; ?><br /><br /><input type="checkbox" name="delete" />&nbsp;<span class="gensmall"><?php echo $user->lang['DELETE_AVATAR']; ?></span></td>
			</tr>
<?php

			// Can we upload?
			if ($can_upload)
			{

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['UPLOAD_AVATAR_FILE']; ?>: </b></td>
		<td class="row1"><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $config['avatar_max_filesize']; ?>" /><input class="post" type="file" name="uploadfile" /></td>
	</tr>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['UPLOAD_AVATAR_URL']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['UPLOAD_AVATAR_URL_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="uploadurl" size="40" value="<?php echo $avatar_url; ?>" /></td>
	</tr>
<?php

			}

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['LINK_REMOTE_AVATAR']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LINK_REMOTE_AVATAR_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="remotelink" size="40" value="<?php echo $avatar_url; ?>" /></td>
	</tr>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['LINK_REMOTE_SIZE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LINK_REMOTE_SIZE_EXPLAIN']; ?></span></td>
		<td class="row1"><input class="post" type="text" name="width" size="3" value="<?php echo $user_avatar_width; ?>" /> <span class="gen">px X </span> <input class="post" type="text" name="height" size="3" value="<?php echo $user_avatar_height; ?>" /> <span class="gen">px</span></td>
	</tr>
<?php

			// Do we have a gallery?
			if ($config['null'] && !$display_gallery)
			{

?>
	<tr> 
		<td class="row2" width="35%"><b><?php echo $user->lang['AVATAR_GALLERY']; ?>: </b></td>
		<td class="row1"><input class="btnlite" type="submit" name="displaygallery" value="<?php echo $user->lang['DISPLAY_GALLERY']; ?>" /></td>
	</tr>
<?php
			}

			// Do we want to display it?
			if ($config['null'] && $display_gallery)
			{

?>
	<tr> 
		<th colspan="2"><?php echo $user->lang['AVATAR_GALLERY']; ?></th>
	</tr>
	<tr> 
		<td class="cat" colspan="2" align="center" valign="middle"><span class="genmed"><?php echo $user->lang['AVATAR_CATEGORY']; ?>: </span><select name="avatarcat">{S_CAT_OPTIONS}</select>&nbsp; <span class="genmed"><?php echo $user->lang['AVATAR_PAGE']; ?>: </span><select name="avatarpage">{S_PAGE_OPTIONS}</select>&nbsp;<input class="btnlite" type="submit" value="<?php echo $user->lang['GO']; ?>" name="avatargallery" /></td>
	</tr>
	<tr> 
		<td class="row1" colspan="2" align="center"><table cellspacing="1" cellpadding="4" border="0">
		
			<!-- BEGIN avatar_row -->
			<tr> 
				<!-- BEGIN avatar_column -->
				<td class="row1" align="center"><img src="{avatar_row.avatar_column.AVATAR_IMAGE}" alt="{avatar_row.avatar_column.AVATAR_NAME}" title="{avatar_row.avatar_column.AVATAR_NAME}" /></td>
				<!-- END avatar_column -->
			</tr>
			<tr>
				<!-- BEGIN avatar_option_column -->
				<td class="row2" align="center"><input type="radio" name="avatarselect" value="{avatar_row.avatar_option_column.S_OPTIONS_AVATAR}" /></td>
				<!-- END avatar_option_column -->
			</tr>
			<!-- END avatar_row -->

		</table></td>
	</tr>
<?php

			}

			break;


		case 'sig':
			include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);

			decode_text($user_sig, $user_sig_bbcode_uid);


?>
	<tr> 
		<td class="row2"><table cellspacing="0" cellpadding="2" border="0">
			<tr align="center" valign="middle">
				<td><input class="btnlite" type="button" accesskey="b" name="addbbcode0" value=" B " style="font-weight:bold; width: 30px" onclick="bbstyle(0)" onmouseover="helpline('b')" /></td>
				<td><input class="btnlite" type="button" accesskey="i" name="addbbcode2" value=" i " style="font-style:italic; width: 30px" onclick="bbstyle(2)" onmouseover="helpline('i')" /></td>
				<td><input class="btnlite" type="button" accesskey="u" name="addbbcode4" value=" u " style="text-decoration: underline; width: 30px" onclick="bbstyle(4)" onmouseover="helpline('u')" /></td>
				<td><input class="btnlite" type="button" accesskey="q" name="addbbcode6" value="Quote" style="width: 50px" onclick="bbstyle(6)" onmouseover="helpline('q')" /></td>
				<td><input class="btnlite" type="button" accesskey="c" name="addbbcode8" value="Code" style="width: 40px" onclick="bbstyle(8)" onmouseover="helpline('c')" /></td>
				<td><input class="btnlite" type="button" accesskey="l" name="addbbcode10" value="List" style="width: 40px" onclick="bbstyle(10)" onmouseover="helpline('l')" /></td>
				<td><input class="btnlite" type="button" accesskey="o" name="addbbcode12" value="List=" style="width: 40px" onclick="bbstyle(12)" onmouseover="helpline('o')" /></td>
				<td><input class="btnlite" type="button" accesskey="p" name="addbbcode14" value="Img" style="width: 40px"  onclick="bbstyle(14)" onmouseover="helpline('p')" /></td>
				<td><input class="btnlite" type="button" accesskey="w" name="addbbcode18" value="URL" style="text-decoration: underline; width: 40px" onclick="bbstyle(18)" onmouseover="helpline('w')" /></td>
			</tr>
			<tr>
				<td colspan="9"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><span class="genmed"> &nbsp;{L_FONT_SIZE}:</span> <select name="addbbcode20" onchange="bbfontstyle('[size=' + this.form.addbbcode20.options[this.form.addbbcode20.selectedIndex].value + ']', '[/size]');this.form.addbbcode20.selectedIndex = 2;" onmouseover="helpline('f')">
							<option value="7">{L_FONT_TINY}</option>
							<option value="9">{L_FONT_SMALL}</option>
							<option value="12" selected="selected">{L_FONT_NORMAL}</option>
							<option value="18">{L_FONT_LARGE}</option>
							<option  value="24">{L_FONT_HUGE}</option>
						</select></td>
						<td class="gensmall" nowrap="nowrap" align="right"><a href="javascript:bbstyle(-1)" onmouseover="helpline('a')">{L_CLOSE_TAGS}</a></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td colspan="9"><input class="helpline" type="text" name="helpbox" size="45" maxlength="100" value="{L_STYLES_TIP}" /></td>
			</tr>
			<tr>
				<td colspan="9"><textarea class="post" name="signature" rows="6" cols="60" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);"><?php echo $user_sig; ?></textarea></td>
			</tr>
		</table></td>
	</tr>
<?php

			break;


		case 'groups':
			break;


		case 'perm':
			break;

	}

?>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
		</table></td>
	</tr>
<?php

	if ($pagination)
	{

?>
	<tr>
		<td align="right"><?php echo $pagination; ?></td>
	</tr>
<?php

	}

?>
</table></form>

<?php

	adm_page_footer();

}

// Do we have permission?
if (!$auth->acl_get('a_user'))
{
	trigger_error($user->lang['No_admin']);
}

?>

<h1><?php echo $user->lang['USER_ADMIN']; ?></h1>

<p><?php echo $user->lang['USER_ADMIN_EXPLAIN']; ?></p>

<form method="post" name="post" action="<?php echo "admin_users.$phpEx$SID"; ?>"><table class="bg" width="75%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"align="center"><?php echo $user->lang['SELECT_USER']; ?></th>
	</tr>
	<tr> 
		<td class="row1" width="40%"><b>Lookup existing user: </b><br /><span class="gensmall">[ <a href="<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username"; ?>" onclick="window.open('<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username"?>', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;"><?php echo $user->lang['FIND_USERNAME']; ?></a> ]</span></td>
		<td class="row2"><input type="text" class="post" name="username" maxlength="50" size="20" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submituser" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();

?>