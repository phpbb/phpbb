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
include($phpbb_root_path.'includes/functions_profile_fields.'.$phpEx);

$user->add_lang(array('posting', 'ucp'));

//
// Get and set basic vars
//
$mode		= request_var('mode', 'overview');
$action		= request_var('action', '');

$username	= request_var('username', '');
$user_id	= request_var('u', 0);
$gid		= request_var('g', 0);

$start		= request_var('start', 0);
$ip			= request_var('ip', '');
$start		= request_var('start', 0);
$delete		= request_var('delete', '');
$deletetype	= request_var('deletetype', '');
$marked		= request_var('mark', 0);
$quicktools	= request_var('quicktools', '');

$st			= request_var('st', 0);
$sk			= request_var('sk', 'a');
$sd			= request_var('sd', 'd');

$submit		= (isset($_POST['update'])) ? true : false;
$confirm	= (isset($_POST['confirm'])) ? true : false;
$cancel		= (isset($_POST['cancel'])) ? true : false;
$preview	= (isset($_POST['preview'])) ? true : false;
$deletemark = (isset($_POST['delmarked'])) ? true : false;
$deleteall	= (isset($_POST['delall'])) ? true : false;

$error = array();
$colspan = 0;

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

		if ($ipwhois = user_ipwhois($ip))
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
	$sql = ($action == 'overview') ? 'SELECT u.*, s.session_time, s.session_page, s.session_ip FROM (' . USERS_TABLE . ' u LEFT JOIN ' . SESSIONS_TABLE . " s ON s.session_user_id = u.user_id) WHERE u.$sql_where ORDER BY s.session_time DESC" : 'SELECT * FROM ' . USERS_TABLE . " WHERE $sql_where";
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
// Output forms
//

// Begin program
if ($username || $user_id)
{
	// Generate overall "header" for user admin
	$form_options = '';
	$forms_ary = array('overview' => 'OVERVIEW', 'feedback' => 'FEEDBACK', 'profile' => 'PROFILE', 'prefs' => 'PREFS', 'avatar' => 'AVATAR', 'sig' => 'SIG', 'groups' => 'GROUP', 'perm' => 'PERM', 'attach' => 'ATTACH');

	foreach ($forms_ary as $value => $lang)
	{
		$selected = ($mode == $value) ? ' selected="selected"' : '';
		$form_options .= '<option value="' . $value . '"' . $selected . '>' . $user->lang['USER_ADMIN_' . $lang]  . '</option>';
	}

	$pagination = '';

?>

<script language="javascript" type="text/javascript">
<!--

var form_name = 'admin';
var text_name = 'signature';

// Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[quote]','[/quote]','[code]','[/code]','[list]','[/list]','[list=]','[/list]','[img]','[/img]','[url]','[/url]');
imageTag = false;

// Helpline messages
b_help = "<?php echo $user->lang['BBCODE_B_HELP']; ?>";
i_help = "<?php echo $user->lang['BBCODE_I_HELP']; ?>";
u_help = "<?php echo $user->lang['BBCODE_U_HELP']; ?>";
q_help = "<?php echo $user->lang['BBCODE_Q_HELP']; ?>";
c_help = "<?php echo $user->lang['BBCODE_C_HELP']; ?>";
l_help = "<?php echo $user->lang['BBCODE_L_HELP']; ?>";
o_help = "<?php echo $user->lang['BBCODE_O_HELP']; ?>";
p_help = "<?php echo $user->lang['BBCODE_P_HELP']; ?>";
w_help = "<?php echo $user->lang['BBCODE_W_HELP']; ?>";
a_help = "<?php echo $user->lang['BBCODE_A_HELP']; ?>";
s_help = "<?php echo $user->lang['BBCODE_S_HELP']; ?>";
f_help = "<?php echo $user->lang['BBCODE_F_HELP']; ?>";
e_help = "<?php echo $user->lang['BBCODE_E_HELP']; ?>";

//-->
</script>
<script language="javascript" type="text/javascript" src="editor.js"></script>

<h1><?php echo $user->lang['USER_ADMIN']; ?></h1>

<p><?php echo $user->lang['USER_ADMIN_EXPLAIN']; ?></p>

<form method="post" name="admin" action="<?php echo "admin_users.$phpEx$SID&amp;mode=$mode&amp;u=$user_id"; ?>"<?php echo ($file_uploads) ? ' enctype="multipart/form-data"' : ''; ?>><table width="100%" cellspacing="2" cellpadding="0" border="0" align="center">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_FORM']; ?>: <select name="mode" onchange="if (this.options[this.selectedIndex].value != '') this.form.submit();"><?php echo $form_options; ?></select></td>
	</tr>
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
<?php

	if (sizeof($error))
	{

?>
			<tr>
				<td class="row3" colspan="" align="center"><span class="error"><?php echo implode('<br />', $error); ?></span></td>
			</tr>
<?php

	}


	switch ($mode)
	{
		case 'overview':

			if ($submit)
			{
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
						user_delete($deletetype, $user_id);

						add_log('admin', 'LOG_USER_DELETED', $username);
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
									$log = 'LOG_BAN_USERNAME_USER';
									break;

								case 'banemail':
									$ban[] = $user_email;
									$reason = 'USER_ADMIN_BAN_EMAIL_REASON';
									$log = 'LOG_BAN_EMAIL_USER';
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
									$log = 'LOG_BAN_IP_USER';
									break;
							}

							user_ban(substr($quicktools, 3), $ban, 0, 0, 0, $user->lang[$reason]);

							add_log('user', $user_id, $log);

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

								add_log('admin', 'LOG_USER_REACTIVATE', $username);
								add_log('user', $user_id, 'LOG_USER_REACTIVATE_USER');

								trigger_error($user->lang['USER_ADMIN_REACTIVATE']);
							}

							break;

						case 'active':

							user_active_flip($user_id, $user_type, false, $username);

							$message = ($user_type == USER_NORMAL) ? 'USER_ADMIN_INACTIVE' : 'USER_ADMIN_ACTIVE';
							$log = ($user_type == USER_NORMAL) ? 'LOG_USER_INACTIVE' : 'LOG_USER_ACTIVE';

							add_log('admin', $log, $username);
							add_log('user', $user_id, $log . '_USER');

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

					$sql = 'SELECT forum_name
						FROM ' . TOPICS_TABLE . " 
						WHERE topic_id = $new_forum_id";
					$result = $db->sql_query($sql);

					extract($db->sql_fetchrow($result));
					$db->sql_freeresult($result);

					add_log('admin', 'LOG_USER_MOVE_POSTS', $forum_name, $username);
					add_log('user', $user_id, 'LOG_USER_MOVE_POSTS_USER', $forum_name);

					trigger_error($user->lang['USER_ADMIN_MOVE']);
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

					// TODO
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
			}

			$colspan = 2;

			$user_char_ary = array('.*' => 'USERNAME_CHARS_ANY', '[\w]+' => 'USERNAME_ALPHA_ONLY', '[\w_\+\. \-\[\]]+' => 'USERNAME_ALPHA_SPACERS');
			$quick_tool_ary = array('banuser' => 'BAN_USER', 'banemail' => 'BAN_EMAIL', 'banip' => 'BAN_IP', 'active' => (($user_type == USER_INACTIVE) ? 'ACTIVATE' : 'DEACTIVATE'), 'delsig' => 'DEL_SIG', 'delavatar' => 'DEL_AVATAR', 'moveposts' => 'MOVE_POSTS', 'delposts' => 'DEL_POSTS', 'delattach' => 'DEL_ATTACH');
			if ($config['email_enable']) 
			{
				$quick_tool_ary['reactivate'] = 'FORCE';
			}

			$options = '<option class="sep" value="">' . $user->lang['SELECT_OPTION'] . '</option>';
			foreach ($quick_tool_ary as $value => $lang)
			{
				$options .= '<option value="' . $value . '">' . $user->lang['USER_ADMIN_' . $lang]  . '</option>';
			}

			$user_founder_yes = ($user_type == USER_FOUNDER) ? ' checked="checked"' : '';
			$user_founder_no = ($user_type != USER_FOUNDER) ? ' checked="checked"' : (($user->data['user_type'] != USER_FOUNDER) ? ' disabled="disabled"' : '');

?>	
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_ADMIN_OVERVIEW']; ?></th>
			</tr>
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

?>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
<?php

			break;

		case 'feedback':

			if ($submit)
			{
				if (($deletemark || $deleteall) && $auth->acl_get('a_clearlogs'))
				{
					$where_sql = '';
					if ($deletemark && $marked)
					{
						$sql_in = array();
						foreach ($marked as $mark)
						{
							$sql_in[] =  $mark;
						}
						$where_sql = ' AND log_id IN (' . implode(', ', $sql_in) . ')';
						unset($sql_in);
					}

					$sql = 'DELETE FROM ' . LOG_TABLE . '
						WHERE log_type = ' . LOG_USERS . " 
							$where_sql";
					$db->sql_query($sql);

					add_log('admin', 'LOG_USERS_CLEAR');
					trigger_error("");
				}

				if ($message = request_var('message', ''))
				{
					add_log('admin', 'LOG_USER_FEEDBACK', $username);
					add_log('user', $user_id, 'LOG_USER_GENERAL', $message);

					trigger_error($user->lang['USER_FEEDBACK_ADDED']);
				}
			}

			$colspan = 2;

			$limit_days = array(0 => $user->lang['ALL_ENTRIES'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);
			$sort_by_text = array('a' => $user->lang['SORT_USERNAME'], 'b' => $user->lang['SORT_DATE'], 'c' => $user->lang['SORT_IP'], 'd' => $user->lang['SORT_ACTION']);
			$sort_by_sql = array('a' => 'l.user_id', 'b' => 'l.log_time', 'c' => 'l.log_ip', 'd' => 'l.log_operation');

			$s_limit_days = $s_sort_key = $s_sort_dir = '';
			gen_sort_selects($limit_days, $sort_by_text, $st, $sk, $sd, $s_limit_days, $s_sort_key, $s_sort_dir);

			// Define where and sort sql for use in displaying logs
			$sql_where = ($st) ? (time() - ($st * 86400)) : 0;
			$sql_sort = $sort_by_sql[$sk] . ' ' . (($sd == 'd') ? 'DESC' : 'ASC');

?>
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_ADMIN_FEEDBACK']; ?></th>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><?php echo $user->lang['DISPLAY_LOG']; ?>: &nbsp;<?php echo $s_limit_days; ?>&nbsp;<?php echo $user->lang['SORT_BY']; ?>: <?php echo $s_sort_key; ?> <?php echo $s_sort_dir; ?>&nbsp;<input class="btnlite" type="submit" value="<?php echo $user->lang['GO']; ?>" name="sort" /></td>
			</tr>
<?php

			$log_data = array();
			$log_count = 0;
			view_log('user', $log_data, $log_count, $config['posts_per_page'], $start, 0, 0, $user_id, $sql_where, $sql_sort);

			if ($log_count)
			{
				for($i = 0; $i < sizeof($log_data); $i++)
				{
					$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
				<tr>
					<td class="<?php echo $row_class; ?>"><span class="gensmall">Report by: <b><?php echo $log_data[$i]['username']; ?></b> on <?php echo $user->format_date($log_data[$i]['time']); ?></span><hr /><?php echo $log_data[$i]['action']; ?></td>
					<td class="<?php echo $row_class; ?>" width="5%" align="center"><input type="checkbox" name="mark[]" value="<?php echo $log_data[$i]['id']; ?>" /></td>
				</tr>
<?php

				}
			}
			else
			{

?>
				<tr>
					<td class="row1" colspan="2" align="center">No reports exist for this user</td>
				</tr>
<?php

			}


?>
			<tr>
				<td class="cat" colspan="2" align="right"><?php
	
			if ($auth->acl_get('a_clearlogs'))
			{

?><input class="btnlite" type="submit" name="delmarked" value="<?php echo $user->lang['DELETE_MARKED']; ?>" />&nbsp; <input class="btnlite" type="submit" name="delall" value="<?php echo $user->lang['DELETE_ALL']; ?>" /><?php
	
			}
			
?>&nbsp;</td>
			</tr>
		</table></td>
	</tr>
	<tr>
		<td class="nav"><div style="float:left;"><?php echo on_page($log_count, $config['topics_per_page'], $start); ?></div><div  style="float:right;"><b><a href="javascript:marklist('admin', true);"><?php echo $user->lang['MARK_ALL']; ?></a> :: <a href="javascript:marklist('admin', false);"><?php echo $user->lang['UNMARK_ALL']; ?></a></b>&nbsp;<br /><br /><?php

			echo generate_pagination("admin_users.$phpEx$SID&amp;action=$action&amp;u=$user_id&amp;st=$st&amp;sk=$sk&amp;sd=$sd", $log_count, $config['posts_per_page'], $start); 
	
?></div></td>
	</tr>
</table>

<script language="Javascript" type="text/javascript">
<!--
function marklist(match, status)
{
	len = eval('document.' + match + '.length');
	for (i = 0; i < len; i++)
	{
		eval('document.' + match + '.elements[i].checked = ' + status);
	}
}
//-->
</script>

<h1><?php echo $user->lang['ADD_FEEDBACK']; ?></h1>

<p><?php echo $user->lang['ADD_FEEDBACK_EXPLAIN']; ?></p>

<table width="100%" cellspacing="2" cellpadding="0" border="0" align="center">
	<tr>
		<td><table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_ADMIN_' . strtoupper($action)]; ?></th>
			</tr>
			<tr>
				<td class="row1" colspan="2" align="center"><textarea name="message" rows="10" cols="76"></textarea></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
<?php


			break;


		case 'profile':

			if ($submit)
			{
				$var_ary = array(
					'icq'			=> (string) '', 
					'aim'			=> (string) '', 
					'msn'			=> (string) '', 
					'yim'			=> (string) '', 
					'jabber'		=> (string) '', 
					'website'		=> (string) '', 
					'location'		=> (string) '',
					'occupation'	=> (string) '',
					'interests'		=> (string) '',
					'bday_day'		=> 0,
					'bday_month'	=> 0,
					'bday_year'		=> 0,
				);

				foreach ($var_ary as $var => $default)
				{
					$data[$var] = request_var($var, $default);
				}

				$var_ary = array(
					'icq'			=> array(
						array('string', true, 3, 15), 
						array('match', true, '#^[0-9]+$#i')), 
					'aim'			=> array('string', true, 5, 255), 
					'msn'			=> array('string', true, 5, 255), 
					'jabber'		=> array(
						array('string', true, 5, 255), 
						array('match', true, '#^[a-z0-9\.\-_\+]+?@(.*?\.)*?[a-z0-9\-_]+?\.[a-z]{2,4}(/.*)?$#i')),
					'yim'			=> array('string', true, 5, 255), 
					'website'		=> array(
						array('string', true, 12, 255), 
						array('match', true, '#^http[s]?://(.*?\.)*?[a-z0-9\-]+\.[a-z]{2,4}#i')), 
					'location'		=> array('string', true, 2, 255), 
					'occupation'	=> array('string', true, 2, 500), 
					'interests'		=> array('string', true, 2, 500), 
					'bday_day'		=> array('num', true, 1, 31),
					'bday_month'	=> array('num', true, 1, 12),
					'bday_year'		=> array('num', true, 1901, gmdate('Y', time())),
				);

				$error = validate_data($data, $var_ary);
				extract($data);
				unset($data);

				// validate custom profile fields
	//			$cp->submit_cp_field('profile', $cp_data, $cp_error);

				if (!sizeof($error) && !sizeof($cp_error))
				{
					$sql_ary = array(
						'user_icq'		=> $icq,
						'user_aim'		=> $aim,
						'user_msnm'		=> $msn,
						'user_yim'		=> $yim,
						'user_jabber'	=> $jabber,
						'user_website'	=> $website,
						'user_from'		=> $location,
						'user_occ'		=> $occupation,
						'user_interests'=> $interests,
						'user_birthday'	=> sprintf('%2d-%2d-%4d', $bday_day, $bday_month, $bday_year),
					);

					$sql = 'UPDATE ' . USERS_TABLE . ' 
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE user_id = $user_id";
					$db->sql_query($sql);

	/*
					// Update Custom Fields
					if (sizeof($cp_data))
					{
						$sql = 'UPDATE ' . PROFILE_DATA_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $cp_data) . "
							WHERE user_id = $user_id";
						$db->sql_query($sql);

						if (!$db->sql_affectedrows())
						{
							$cp_data['user_id'] = $user_id;

							$db->return_on_error = true;

							$sql = 'INSERT INTO ' . PROFILE_DATA_TABLE . ' ' . $db->sql_build_array('INSERT', $cp_data);
							$db->sql_query();

							$db->return_on_error = false;
						}
					}
	*/
					trigger_error($user->lang['USER_PROFILE_UPDATED']);
				}
			}

			$colspan = 2;

			$cp = new custom_profile();

			$cp_data = $cp_error = array();

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

			// Get additional profile fields and assign them to the template block var 'profile_fields'
//			$user->get_profile_fields($user->data['user_id']);
//			$cp->generate_profile_fields('profile', $user->get_iso_lang_id(), $cp_error);


?>
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_ADMIN_SIG']; ?></th>
			</tr>
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
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
<?php

			break;


		case 'prefs':

			if ($submit)
			{
				$var_ary = array(
					'user_dateformat'		=> (string) $config['default_dateformat'], 
					'user_lang'				=> (string) $config['default_lang'], 
					'user_tz'				=> (float) $config['board_timezone'],
					'user_style'			=> (int) $config['default_style'], 
					'user_dst'				=> (bool) $config['board_dst'], 
					'user_allow_viewemail'	=> false, 
					'user_allow_massemail'	=> true, 
					'user_allow_viewonline'	=> true, 
					'user_notify_type'		=> 0, 
					'user_notify_pm'		=> true, 
					'user_allow_pm'			=> true, 
					'user_notify'			=> false, 

					'sk'		=> (string) 't', 
					'sd'		=> (string) 'd', 
					'st'		=> 0,

					'popuppm'		=> false, 
					'viewimg'		=> true, 
					'viewflash'		=> false, 
					'viewsmilies'	=> true, 
					'viewsigs'		=> true, 
					'viewavatars'	=> true, 
					'viewcensors'	=> false, 
					'bbcode'		=> true, 
					'html'			=> false, 
					'smile'			=> true,
					'attachsig'		=> true, 
				);

				foreach ($var_ary as $var => $default)
				{
					$data[$var] = request_var($var, $default);
				}

				$var_ary = array(
					'user_dateformat'	=> array('string', false, 3, 15), 
					'user_lang'			=> array('match', false, '#^[a-z_]{2,}$#i'),
					'user_tz'			=> array('num', false, -13, 13),

					'sk'	=> array('string', false, 1, 1), 
					'sd'	=> array('string', false, 1, 1), 
				);

				$error = validate_data($data, $var_ary);
				extract($data);
				unset($data);

				// Set the popuppm option
				$option_ary = array('popuppm', 'viewimg', 'viewflash', 'viewsmilies', 'viewsigs', 'viewavatars', 'viewcensors', 'bbcode', 'html', 'smile', 'attachsig');

				foreach ($option_ary as $option)
				{
					$user_options = $user->optionset($option, $$option, $user_options);
				}

				if (!sizeof($error))
				{
					$sql_ary = array(
						'user_allow_pm'			=> $user_allow_pm, 
						'user_allow_viewemail'	=> $user_allow_viewemail, 
						'user_allow_massemail'	=> $user_allow_massemail, 
						'user_allow_viewonline'	=> $user_allow_viewonline, 
						'user_notify_type'		=> $user_notify_type, 
						'user_notify_pm'		=> $user_notify_pm,
						'user_options'			=> $user_options, 
						'user_notify'			=> $user_notify,
						'user_dst'				=> $user_dst,
						'user_dateformat'		=> $user_dateformat,
						'user_lang'				=> $user_lang,
						'user_timezone'			=> $user_tz,
						'user_style'			=> $user_style,
						'user_sortby_type'		=> $sk,
						'user_sortby_dir'		=> $sd,
						'user_show_days'		=> $st, 
					);

					$sql = 'UPDATE ' . USERS_TABLE . ' 
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
						WHERE user_id = $user_id";
					$db->sql_query($sql);

					trigger_error($user->lang['USER_PREFS_UPDATED']);
				}

				$user_sortby_type = $sk;
				$user_sortby_dir = $sd;
				$user_show_days = $st;
			}

			$colspan = 2;

			$option_ary = array('user_allow_viewemail', 'user_allow_massemail', 'user_allow_pm', 'user_allow_viewonline', 'user_notify_pm', 'user_dst', 'user_notify');

			foreach ($option_ary as $option)
			{
				${$option . '_yes'} = ($$option) ? ' checked="checked"' : '';
				${$option . '_no'} = (!$$option) ? ' checked="checked"' : '';
			}
			unset($option_ary);

			$option_ary = array('popuppm', 'viewimg', 'viewflash', 'viewsmilies', 'viewsigs', 'viewavatars', 'viewcensors', 'bbcode', 'html', 'smile', 'attachsig');

			foreach ($option_ary as $option)
			{
				${$option . '_yes'} = ($user->optionget($option, $user_options)) ? ' checked="checked"' : '';
				${$option . '_no'} = (!$user->optionget($option, $user_options)) ? ' checked="checked"' : '';
			}

			$notify_email	= ($user_notify_type == NOTIFY_EMAIL) ? ' checked="checked"' : '';
			$notify_im		= ($user_notify_type == NOTIFY_IM) ? ' checked="checked"' : '';
			$notify_both	= ($user_notify_type == NOTIFY_BOTH) ? ' checked="checked"' : '';

			// Topic ordering display
			$limit_days = array(0 => $user->lang['ALL_TOPICS'], 0 => $user->lang['ALL_TOPICS'], 1 => $user->lang['1_DAY'], 7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'], 90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 364 => $user->lang['1_YEAR']);

			$sort_by_text = array('a' => $user->lang['AUTHOR'], 't' => $user->lang['POST_TIME'], 'r' => $user->lang['REPLIES'], 's' => $user->lang['SUBJECT'], 'v' => $user->lang['VIEWS']);
			$sort_by_sql = array('a' => 't.topic_first_poster_name', 't' => 't.topic_last_post_time', 'r' => 't.topic_replies', 's' => 't.topic_title', 'v' => 't.topic_views');

			$s_limit_days = $s_sort_key = $s_sort_dir = '';
			gen_sort_selects($limit_days, $sort_by_text, $user_show_days, $user_sortby_type, $user_sortby_dir, $s_limit_days, $s_sort_key, $s_sort_dir);

?>
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_ADMIN_PREFS']; ?></th>
			</tr>
			<tr> 
				<td class="row1" width="40%"><b><?php echo $user->lang['VIEW_IMAGES']; ?>:</b></td>
				<td class="row2"><input type="radio" name="viewimg" value="1"<?php echo $viewimg_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="viewimg" value="0"<?php echo $viewimg_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['VIEW_FLASH']; ?>:</b></td>
				<td class="row2"><input type="radio" name="viewflash" value="1"<?php echo $viewflash_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="viewflash" value="0"<?php echo $viewflash_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['VIEW_SMILIES']; ?>:</b></td>
				<td class="row2"><input type="radio" name="viewsmilies" value="1"<?php echo $viewsmilies_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="viewsmilies" value="0"<?php echo $viewsmilies_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['VIEW_SIGS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="viewsigs" value="1"<?php echo $viewsigs_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="viewsigs" value="0"<?php echo $viewsigs_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['VIEW_AVATARS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="viewavatars" value="1"<?php echo $viewavatars_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="viewavatars" value="0"<?php echo $viewavatars_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['DISABLE_CENSORS']; ?>:</b></td>
				<td class="row2"><input type="radio" name="viewcensors" value="1"<?php echo $viewcensors_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="viewcensors" value="0"<?php echo $viewcensors_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<!-- tr>
				<td class="row1"><b><?php echo $user->lang['MINIMUM_KARMA']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['MINIMUM_KARMA_EXPLAIN']; ?></span></td>
				<td class="row2"><select name="user_min_karma">{S_MIN_KARMA_OPTIONS}</select></td>
			</tr-->
			<tr> 
				<td class="row1"><b><?php echo $user->lang['VIEW_TOPICS_DAYS']; ?>:</b></td>
				<td class="row2"><?php echo $s_limit_days; ?></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['VIEW_TOPICS_KEY']; ?>:</b></td>
				<td class="row2"><?php echo $s_sort_key; ?></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['VIEW_TOPICS_DIR']; ?>:</b></td>
				<td class="row2"><?php echo $s_sort_dir; ?></td>
			</tr>
			<tr>
				<th colspan="2"><?php echo $user->lang['USER_POSTING_PREFS']; ?></th>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['DEFAULT_BBCODE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="bbcode" value="1"<?php echo $bbcode_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="bbcode" value="0"<?php echo $bbcode_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['DEFAULT_HTML']; ?>:</b></td>
				<td class="row2"><input type="radio" name="html" value="1"<?php echo $html_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="html" value="0"<?php echo $html_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['DEFAULT_SMILE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="smile" value="1"<?php echo $smile_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="smile" value="0"<?php echo $smile_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['DEFAULT_ADD_SIG']; ?>:</b></td>
				<td class="row2"><input type="radio" name="attachsig" value="1"<?php echo $attachsig_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="attachsig" value="0"<?php echo $attachsig_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['DEFAULT_NOTIFY']; ?>:</b></td>
				<td class="row2"><input type="radio" name="user_notify" value="1"<?php echo $user_notify_yes; ?> /><span class="gen"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_notify" value="0"<?php echo $user_notify_no; ?> /><span class="gen"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr>
				<th colspan="2"></th>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['SHOW_EMAIL']; ?>:</b></td>
				<td class="row2"><input type="radio" name="user_allow_viewemail" value="1"<?php echo $user_allow_viewemail_yes; ?> /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_allow_viewemail" value="0"<?php echo $user_allow_viewemail_no; ?> /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['ADMIN_EMAIL']; ?>:</b></td>
				<td class="row2"><input type="radio" name="user_allow_massemail" value="1"<?php echo $user_allow_massemail_yes; ?> /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_allow_massemail" value="0"<?php echo $user_allow_massemail_no; ?> /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['ALLOW_PM']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['ALLOW_PM_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="radio" name="user_allow_pm" value="1"<?php echo $user_allow_pm_yes; ?> /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_allow_pm" value="0"<?php echo $user_allow_pm_no; ?> /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['HIDE_ONLINE']; ?>:</b></td>
				<td class="row2"><input type="radio" name="user_allow_viewonline" value="0"<?php echo $user_allow_viewonline_no; ?> /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_allow_viewonline" value="1"<?php echo $user_allow_viewonline_yes; ?> /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['NOTIFY_METHOD']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['NOTIFY_METHOD_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="radio" name="user_notify_type" value="0"<?php echo $notify_email; ?> /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_EMAIL']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_notify_type" value="1"<?php echo $notify_im; ?> /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_IM']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_notify_type" value="2"<?php echo $notify_both; ?> /><span class="genmed"><?php echo $user->lang['NOTIFY_METHOD_BOTH']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['NOTIFY_ON_PM']; ?>:</b></td>
				<td class="row2"><input type="radio" name="user_notify_pm" value="1"<?php echo $user_notify_pm_yes; ?> /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_notify_pm" value="0"<?php echo $user_notify_pm_no; ?> /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['POPUP_ON_PM']; ?>:</b></td>
				<td class="row2"><input type="radio" name="popuppm" value="1"<?php echo $popuppm_yes; ?> /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="popuppm" value="0"<?php echo $popuppm_no; ?> /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['BOARD_LANGUAGE']; ?>:</b></td>
				<td class="row2"><select name="user_lang"><?php echo language_select($user_lang); ?></select></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['BOARD_STYLE']; ?>:</b></td>
				<td class="row2"><select name="user_style"><?php echo style_select($user_style); ?></select></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['BOARD_TIMEZONE']; ?>:</b></td>
				<td class="row2"><select name="user_tz"><?php echo tz_select($user_timezone); ?></select></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['BOARD_DST']; ?>:</b></td>
				<td class="row2"><input type="radio" name="user_dst" value="1"<?php echo $user_dst_yes; ?> /><span class="genmed"><?php echo $user->lang['YES']; ?></span>&nbsp;&nbsp;<input type="radio" name="user_dst" value="0"<?php echo $user_dst_no; ?> /><span class="genmed"><?php echo $user->lang['NO']; ?></span></td>
			</tr>
			<tr> 
				<td class="row1"><b><?php echo $user->lang['BOARD_DATE_FORMAT']; ?>:</b><br /><span class="gensmall"><?php echo $user->lang['BOARD_DATE_FORMAT_EXPLAIN']; ?></span></td>
				<td class="row2"><input type="text" name="user_dateformat" value="<?php echo $user_dateformat; ?>" maxlength="14" class="post" /></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
<?php

			break;

		case 'avatar':

			$can_upload = (file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && $file_uploads) ? true : false;

			if ($submit)
			{
				$var_ary = array(
					'uploadurl'		=> (string) '', 
					'remotelink'	=> (string) '', 
					'width'			=> (string) '',
					'height'		=> (string) '', 
				);

				foreach ($var_ary as $var => $default)
				{
					$data[$var] = request_var($var, $default);
				}

				$var_ary = array(
					'uploadurl'		=> array('string', true, 5, 255), 
					'remotelink'	=> array('string', true, 5, 255), 
					'width'			=> array('string', true, 1, 3), 
					'height'		=> array('string', true, 1, 3), 
				);

				$error = validate_data($data, $var_ary);

				if (!sizeof($error))
				{
					$data['user_id'] = $user_id;

					if ((!empty($_FILES['uploadfile']['tmp_name']) || $data['uploadurl']) && $can_upload)
					{
						list($type, $filename, $width, $height) = avatar_upload($data, $error);
					}
					else if ($data['remotelink'])
					{
						list($type, $filename, $width, $height) = avatar_remote($data, $error);
					}
					else if ($delete)
					{
						$type = $filename = $width = $height = '';
					}
				}

				if (!sizeof($error))
				{
					// Do we actually have any data to update?
					if (sizeof($data))
					{
						$sql_ary = array(
							'user_avatar'			=> $filename, 
							'user_avatar_type'		=> $type, 
							'user_avatar_width'		=> $width, 
							'user_avatar_height'	=> $height, 
						);

						$sql = 'UPDATE ' . USERS_TABLE . ' 
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " 
							WHERE user_id = $user_id";
						$db->sql_query($sql);

						// Delete old avatar if present
						if ($user_avatar && $filename != $user_avatar)
						{
							avatar_delete($user_avatar);
						}
					}

					trigger_error($message);
				}

				extract($data);
				unset($data);
			}

			$colspan = 2;

			$display_gallery = (isset($_POST['displaygallery'])) ? true : false;
			$avatar_category = request_var('category', '');

			// Generate users avatar
			$avatar_img = '';
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
				<th colspan="<?php echo $colspan; ?>"><?php echo $user->lang['USER_ADMIN_AVATAR']; ?></th>
			</tr>
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

?>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
<?php

			break;


		case 'sig':

			if ($submit || $preview)
			{
				$var_ary = array(
					'enable_html'		=> (bool) $config['allow_html'], 
					'enable_bbcode'		=> (bool) $config['allow_bbcode'], 
					'enable_smilies'	=> (bool) $config['allow_smilies'],
					'enable_urls'		=> true,  
					'signature'			=> (string) $user_sig, 

				);

				foreach ($var_ary as $var => $default)
				{
					$$var = request_var($var, $default);
				}

				// NOTE: allow_img and allow_flash do not exist in config table
				$img_status = ($config['allow_img']) ? true : false; 
				$flash_status = ($config['allow_flash']) ? true : false; 

				include($phpbb_root_path . 'includes/message_parser.'.$phpEx);
				$message_parser = new parse_message($signature);

				// Allowing Quote BBCode
				$message_parser->parse($enable_html, $enable_bbcode, $enable_urls, $enable_smilies, $img_status, $flash_status, true);

				if ($submit)
				{
					$sql_ary = array(
						'user_sig'					=> (string) $message_parser->message, 
						'user_sig_bbcode_uid'		=> (string) $message_parser->bbcode_uid, 
						'user_sig_bbcode_bitfield'	=> (int) $message_parser->bbcode_bitfield
					);

					$sql = 'UPDATE ' . USERS_TABLE . ' 
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " 
						WHERE user_id = $user_id";
					$db->sql_query($sql);

					unset($message_parser);
					trigger_error($user->lang['PROFILE_UPDATED']);
				}
			}

			$colspan = 2;

			include($phpbb_root_path . 'includes/functions_posting.'.$phpEx);

			$signature_preview = '';
			if ($preview)
			{
				// Now parse it for displaying
				$signature_preview = $message_parser->format_display($enable_html, $enable_bbcode, $enable_urls, $enable_smilies, false);
				unset($message_parser);
			}

			decode_message($user_sig, $user_sig_bbcode_uid);

?>
			<tr>
				<th colspan="<?php echo $colspan; ?>"><?php echo $user->lang['USER_ADMIN_SIG']; ?></th>
			</tr>
			<tr> 
				<td class="row1" width="40%"><b class="genmed"><?php echo $user->lang['SIGNATURE']; ?>: </b></td>
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
								<td><span class="genmed"> &nbsp;<?php echo $user->lang['FONT_SIZE']; ?>:</span> <select name="addbbcode20" onchange="bbfontstyle('[size=' + this.form.addbbcode20.options[this.form.addbbcode20.selectedIndex].value + ']', '[/size]');this.form.addbbcode20.selectedIndex = 2;" onmouseover="helpline('f')">
									<option value="7"><?php echo $user->lang['FONT_TINY']; ?></option>
									<option value="9"><?php echo $user->lang['FONT_SMALL']; ?></option>
									<option value="12" selected="selected"><?php echo $user->lang['FONT_NORMAL']; ?></option>
									<option value="18"><?php echo $user->lang['FONT_LARGE']; ?></option>
									<option  value="24"><?php echo $user->lang['FONT_HUGE']; ?></option>
								</select></td>
								<td class="gensmall" nowrap="nowrap" align="right"><a href="javascript:bbstyle(-1)" onmouseover="helpline('a')"><?php echo $user->lang['CLOSE_TAGS']; ?></a></td>
							</tr>
						</table></td>
					</tr>
					<tr>
						<td colspan="9"><input class="helpline" type="text" name="helpbox" size="45" maxlength="100" value="<?php echo $user->lang['STYLES_TIP']; ?>" /></td>
					</tr>
					<tr>
						<td colspan="9"><textarea name="signature" rows="6" cols="60" tabindex="3" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);"><?php echo $user_sig; ?></textarea></td>
					</tr>
					<tr>
						<td colspan="9"><table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td bgcolor="black"><script language="javascript" type="text/javascript"><!--

								colorPalette('h', 14, 5)

								//--></script></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row1" valign="top"><b class="genmed"><?php echo $user->lang['OPTIONS']; ?></b><br /><table cellspacing="2" cellpadding="0" border="0">
					<tr>
						<td class="gensmall"><?php echo ($config['allow_html']) ? $user->lang['HTML_IS_ON'] : $user->lang['HTML_IS_OFF']; ?></td>
					</tr>
					<tr>
						<td class="gensmall"><?php echo ($config['allow_bbcode']) ? sprintf($user->lang['BBCODE_IS_ON'], "<a href=\"../faq.$phpEx$SID&amp;mode=bbcode\" target=\"_blank\">", '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], "<a href=\"../faq.$phpEx$SID&amp;mode=bbcode\" target=\"_blank\">", '</a>'); ?></td>
					</tr>
					<tr>
						<td class="gensmall"><?php echo ($config['allow_img']) ? $user->lang['IMAGES_ARE_ON'] : $user->lang['IMAGES_ARE_OFF']; ?></td>
					</tr>
					<tr>
						<td class="gensmall"><?php echo ($config['allow_flash']) ? $user->lang['FLASH_IS_ON'] : $user->lang['FLASH_IS_OFF']; ?></td>
					</tr>
					<tr>
						<td class="gensmall"><?php echo ($config['allow_smilies']) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF']; ?></td>
					</tr>
				</table></td>
				<td class="row2" valign="top"><table cellspacing="0" cellpadding="1" border="0">
<?php

			if ($config['allow_html'])
			{
			
?>
					<tr>
						<td><input type="checkbox" name="disable_html" /></td>
						<td class="gen"><?php echo $user->lang['DISABLE_HTML']; ?></td>
					</tr>
<?php

			}

			if ($config['allow_bbcode'])
			{
			
?>
					<tr>
						<td><input type="checkbox" name="disable_bbcode" /></td>
						<td class="gen"><?php echo $user->lang['DISABLE_BBCODE']; ?></td>
					</tr>
<?php

			}

			if ($config['allow_smilies'])
			{
			
?>
					<tr>
						<td><input type="checkbox" name="disable_smilies" /></td>
						<td class="gen"><?php echo $user->lang['DISABLE_SMILIES']; ?></td>
					</tr>
<?php

			}
			
?>
					<tr>
						<td><input type="checkbox" name="disable_magic_url" /></td>
						<td class="gen"><?php echo $user->lang['DISABLE_MAGIC_URL']; ?></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><input class="btnlite" type="submit" name="preview" value="<?php echo $user->lang['PREVIEW']; ?>" />&nbsp;&nbsp;<input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;&nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
			</tr>
<?php

			if ($signature_preview)
			{
			
?>
			<tr>
				<th colspan="2" valign="middle"><?php echo $user->lang['ADMIN_SIGNATURE_PREVIEW']; ?></th>
			</tr>
			<tr> 
				<td class="row1" colspan="2"><div class="postdetails" style="padding: 6px;"><?php echo $signature_preview; ?></div></td>
			</tr>
<?php

			}
			
?>
<?php

			break;

		case 'groups':

			switch ($action)
			{
				case 'demote':
				case 'promote':
				case 'default':
					group_user_attributes($action, $gid, $user_id);

					if ($action == 'default')
					{
						$group_id = $gid;
					}
					break;

				case 'delete':
					if (!$cancel && !$confirm)
					{
						adm_page_confirm($user->lang['CONFIRM'], $user->lang['CONFIRM_OPERATION']);
					}
					else if (!$cancel) 
					{
						if (!$gid)
						{
							trigger_error($user->lang['NO_GROUP']);
						}

						if ($error = group_user_del($gid, $user_id))
						{
							trigger_error($user->lang[$error]);
						}
					}
				break;
			}

			// Add user to group?
			if ($submit)
			{
				if (!$gid)
				{
					trigger_error($user->lang['NO_GROUP']);
				}

				// Add user/s to group
				if ($error = group_user_add($gid, $user_id))
				{
					trigger_error($user->lang[$error]);
				}
			}

			$colspan = 4;

?>
			<tr>
				<th colspan="4"><?php echo $user->lang['USER_ADMIN_GROUPS']; ?></th>
			</tr>
<?php

			$sql = 'SELECT ug.group_leader, g.* 
				FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . " ug 
				WHERE ug.user_id = $user_id
					AND g.group_id = ug.group_id
				ORDER BY g.group_type DESC, ug.user_pending ASC, g.group_name";
			$result = $db->sql_query($sql);
	
			$i = 0;
			$group_data = $id_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$type = ($row['group_type'] == GROUP_SPECIAL) ? 'special' : (($row['user_pending']) ? 'pending' : 'normal');

				$group_data[$type][$i]['group_id']		= $row['group_id'];
				$group_data[$type][$i]['group_name']	= $row['group_name'];
				$group_data[$type][$i]['group_leader']	= ($row['group_leader']) ? 1 : 0;

				$id_ary[] = $row['group_id'];

				$i++;
			}
			$db->sql_freeresult($result);

			// Select box for other groups
			$sql = 'SELECT group_id, group_name, group_type 
				FROM ' . GROUPS_TABLE . ' 
				WHERE group_id NOT IN (' . implode(', ', $id_ary) . ')
				ORDER BY group_type DESC, group_name ASC';
			$result = $db->sql_query($sql);

			$group_options = '';
			while ($row = $db->sql_fetchrow($result))
			{
				$group_options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="blue"' : '') . ' value="' . $row['group_id'] . '">' . (($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name']) . '</option>';
			}
			$db->sql_freeresult($result);

			$current_type = '';
			foreach ($group_data as $group_type => $data_ary)
			{
				if ($current_type != $group_type)
				{

?>
			<tr>
				<td class="row3" colspan="4"><strong><?php echo $user->lang['USER_GROUP_' . strtoupper($group_type)]; ?></strong></td>
			</tr>
<?php

				}

				foreach ($data_ary as $data)
				{
					$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
			<tr>
				<td class="<?php echo $row_class; ?>"><a href="<?php echo "admin_groups.$phpEx$SID&amp;mode=manage&amp;action=edit&amp;g=" . $data['group_id']; ?>"><?php echo ($group_type == 'special') ? $user->lang['G_' . $data['group_name']] : $data['group_name']; ?></a></td>
				<td class="<?php echo $row_class; ?>" width="10%" nowrap="nowrap">&nbsp;<?php 

					if ($group_id != $data['group_id'])
					{

?><a href="<?php echo "admin_users.$phpEx$SID&amp;mode=$mode&amp;action=default&amp;u=$user_id&amp;g=" . $data['group_id']; ?>"><?php echo $user->lang['GROUP_DEFAULT']; ?></a><?php
	
					}
					else
					{
						echo $user->lang['GROUP_DEFAULT'];
					}
					
?>&nbsp;</td>
				<td class="<?php echo $row_class; ?>" width="10%" nowrap="nowrap">&nbsp;<?php
	
					if ($group_type != 'special')
					{

?><a href="<?php echo "admin_users.$phpEx$SID&amp;mode=$mode&amp;action=" . (($data['group_leader']) ? 'demote' : 'promote') . "&amp;u=$user_id&amp;g=" . $data['group_id']; ?>"><?php echo ($data['group_leader']) ? $user->lang['GROUP_DEMOTE'] : $user->lang['GROUP_PROMOTE']; ?></a>&nbsp;<?php
	
					}
					
?></td>
				<td class="<?php echo $row_class; ?>" width="10%" nowrap="nowrap">&nbsp;<a href="<?php echo "admin_users.$phpEx$SID&amp;mode=$mode&amp;action=delete&amp;u=$user_id&amp;g=" . $data['group_id']; ?>"><?php echo $user->lang['GROUP_DELETE']; ?></a>&nbsp;</td>
			</tr>
<?php

				}
			}

?>
			<tr>
				<td class="cat" colspan="4" align="right"><?php echo $user->lang['USER_GROUP_ADD']; ?>: <select name="g"><?php echo $group_options; ?></select> <input class="btnmain" type="submit" name="update" value="<?php echo $user->lang['SUBMIT']; ?>" />&nbsp;</td>
			</tr>
<?php

			break;


		case 'perm':
			break;


		case 'attach':

			if ($deletemark && $marked)
			{
				if (!$cancel && !$confirm)
				{
					adm_page_confirm($user->lang['CONFIRM'], $user->lang['CONFIRM_OPERATION']);
				}
				else if (!$cancel) 
				{
					$sql = 'SELECT real_filename
						FROM ' . ATTACHMENTS_TABLE . '
						WHERE attach_id IN (' . implode(', ', $marked) . ')';
					$result = $db->sql_query($sql);

					$log_attachments = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$log_attachments[] = $row['real_filename'];
					}
					$db->sql_freeresult($result);

					delete_attachments('attach', $marked);

					$log = (sizeof($delete_ids) == 1) ? 'ATTACHMENT_DELETED' : 'ATTACHMENTS_DELETED';
					$meesage = (sizeof($delete_ids) == 1) ? $user->lang['ATTACHMENT_DELETED'] : $user->lang['ATTACHMENTS_DELETED'];

					add_log('admin', $log, implode(', ', $log_attachments));
					trigger_error($message);
				}
			}

			$colspan = 6;
	
			$uri = "admin_users.$phpEx$SID&amp;mode=$mode&amp;action=$action&amp;u=$user_id";

			$sk_text = array('a' => $user->lang['SORT_FILENAME'], 'b' => $user->lang['SORT_COMMENT'], 'c' => $user->lang['SORT_EXTENSION'], 'd' => $user->lang['SORT_SIZE'], 'e' => $user->lang['SORT_DOWNLOADS'], 'f' => $user->lang['SORT_POST_TIME'], 'g' => $user->lang['SORT_TOPIC_TITLE']);
			$sk_sql = array('a' => 'a.real_filename', 'b' => 'a.comment', 'c' => 'a.extension', 'd' => 'a.filesize', 'e' => 'a.download_count', 'f' => 'a.filetime', 'g' => 't.topic_title');

			$sd_text = array('a' => $user->lang['ASCENDING'], 'd' => $user->lang['DESCENDING']);
	
			$s_sort_key = '';
			foreach ($sk_text as $key => $value)
			{
				$selected = ($sk == $key) ? ' selected="selected"' : '';
				$s_sort_key .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			}

			$s_sort_dir = '';
			foreach ($sd_text as $key => $value)
			{
				$selected = ($sd == $key) ? ' selected="selected"' : '';
				$s_sort_dir .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			}

			$order_by = $sk_sql[$sk] . '  ' . (($sd == 'a') ? 'ASC' : 'DESC');
	
			$sql = 'SELECT COUNT(*) as num_attachments
				FROM ' . ATTACHMENTS_TABLE . "
				WHERE poster_id = $user_id";
			$result = $db->sql_query_limit($sql, 1);

			$num_attachments = $db->sql_fetchfield('num_attachments', 0, $result);
			$db->sql_freeresult($result);

			$sql = 'SELECT a.*, t.topic_title
				FROM ' . ATTACHMENTS_TABLE . ' a, ' . TOPICS_TABLE . " t
				WHERE a.topic_id = t.topic_id
					AND a.poster_id = $user_id
				ORDER BY $order_by";
			$result = $db->sql_query_limit($sql, $config['posts_per_page'], $start);

			$row_count = 0;
			if ($row = $db->sql_fetchrow($result))
			{
				$class = 'row2';

?>
				<tr>
					<th nowrap="nowrap">#</th>
					<th nowrap="nowrap" width="15%"><a class="th" href="<?php echo "$uri&amp;sk=a&amp;sd=" . (($sk == 'a' && $sd == 'a') ? 'd' : 'a'); ?>"><?php echo $user->lang['FILENAME']; ?></a></th>
					<th nowrap="nowrap" width="5%"><a class="th" href="<?php echo "$uri&amp;sk=f&amp;sd=" . (($sk == 'f' && $sd == 'a') ? 'd' : 'a'); ?>"><?php echo $user->lang['POST_TIME']; ?></a></th>
					<th nowrap="nowrap" width="5%"><a class="th" href="<?php echo "$uri&amp;sk=d&amp;sd=" . (($sk == 'd' && $sd == 'a') ? 'd' : 'a'); ?>"><?php echo $user->lang['FILESIZE']; ?></a></th>
					<th nowrap="nowrap" width="5%"><a class="th" href="<?php echo "$uri&amp;sk=e&amp;sd=" . (($sk == 'e' && $sd == 'a') ? 'd' : 'a'); ?>"><?php echo $user->lang['DOWNLOADS']; ?></a></th>
					<th width="2%" nowrap="nowrap"><?php echo $user->lang['DELETE']; ?></th>
				</tr>
<?php

				do
				{
					$view_topic = "{$phpbb_root_path}viewtopic.$phpEx$SID&amp;t=" . $row['topic_id'] . '&amp;p=' . $row['post_id'] . '#' . $row['post_id'];

					$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
					<tr>
						<td class="<?php echo $row_class; ?>" style="padding: 4px;" width="2%" align="center"><span class="gen">&nbsp;<?php echo $row_count + ($start + 1); ?>&nbsp;</span></td>
						<td class="<?php echo $row_class; ?>" style="padding: 4px;"><a class="gen" href="<?php echo "{$phpbb_root_path}download.$phpEx$SID&amp;id=" . $row['attach_id']; ?>" target="_blank"><?php echo $row['real_filename']; ?></a><br /><span class="gensmall"><?php echo $user->lang['TOPIC']; ?>: <a href="<?php echo $view_topic; ?>" target="_blank"><?php echo $row['topic_title']; ?></a></span></td>
						<td class="<?php echo $row_class; ?>" class="gensmall" style="padding: 4px;" align="center" nowrap="nowrap">&nbsp;<?php echo $user->format_date($row['filetime'], $user->lang['DATE_FORMAT']); ?>&nbsp;</td>
						<td class="<?php echo $row_class; ?>" style="padding: 4px;" align="center" nowrap="nowrap"><span class="gen"><?php echo ($row['filesize'] >= 1048576) ? (round($row['filesize'] / 1048576 * 100) / 100) . ' ' . $user->lang['MB'] : (($row['filesize'] >= 1024) ? (round($row['filesize'] / 1024 * 100) / 100) . ' ' . $user->lang['KB'] : $row['filesize'] . ' ' . $user->lang['BYTES']); ?></span></td>
						<td class="<?php echo $row_class; ?>" style="padding: 4px;" align="center"><span class="gen"><?php echo $row['download_count']; ?></span></td>
						<td class="<?php echo $row_class; ?>" style="padding: 4px;" align="center"><input type="checkbox" name="mark[]" value="<?php echo $row['attach_id']; ?>" /></td>
					</tr>
<?php

					$row_count++;
				} 
				while ($row = $db->sql_fetchrow($result));
			}
			$db->sql_freeresult($result);
			
			$pagination = generate_pagination("$uri&amp;sk=$sk&amp;sd=$sd", $num_attachments, $config['topics_per_page'], $start);

?>
					<tr>
						<td class="cat" colspan="<?php echo $colspan; ?>"><table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td width="100%" align="center"><span class="gensmall"><?php echo $user->lang['SORT_BY']; ?>: </span><select name="sk"><?php echo $s_sort_key; ?></select> <select name="sd"><?php echo $s_sort_dir; ?></select>&nbsp;<input class="btnlite" type="submit" name="sort" value="<?php echo $user->lang['SORT']; ?>" /></td>
								<td align="right"><input class="btnlite" type="submit" name="delmarked" value="<?php echo $user->lang['DELETE_MARKED']; ?>" />&nbsp;</td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
<?php

			break;
	}


?>
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
		<td class="row1" width="40%"><b><?php echo $user->lang['FIND_USERNAME']; ?>: </b><br /><span class="gensmall">[ <a href="<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username"; ?>" onclick="window.open('<?php echo "../memberlist.$phpEx$SID&amp;mode=searchuser&amp;field=username"?>', '_phpbbsearch', 'HEIGHT=500,resizable=yes,scrollbars=yes,WIDTH=740');return false;"><?php echo $user->lang['FIND_USERNAME']; ?></a> ]</span></td>
		<td class="row2"><input type="text" class="post" name="username" maxlength="50" size="20" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="submituser" value="<?php echo $user->lang['SUBMIT']; ?>" class="btnmain" /></td>
	</tr>
</table></form>

<?php

adm_page_footer();


// Module class
class acp_admin_users extends module
{




}

?>