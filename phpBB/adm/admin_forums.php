<?php
/***************************************************************************
 *								admin_forums.php
 *                            -------------------
 *   begin                : Thursday, Jul 12, 2001
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

if (!empty($setmodules))
{
	if (!$auth->acl_gets('a_forum', 'a_forumadd', 'a_forumdel'))
	{
		return;
	}

	$module['FORUM']['MANAGE'] = basename(__FILE__) . $SID;
	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

// Get mode
$mode = (isset($_REQUEST['mode'])) ? htmlspecialchars($_REQUEST['mode']) : '';

// Do we have permissions?
switch ($mode)
{
	case 'add':
		$acl = 'a_forumadd';
		break;
	case 'delete':
		$acl = 'a_forumdel';
		break;
	default:
		$acl = 'a_forum';
}

if (!$auth->acl_get($acl))
{
	trigger_error($user->lang['NO_ADMIN']);
}


// Major routines
switch ($mode)
{
	case 'add':
	case 'edit':

		$action = (isset($_POST['action'])) ? htmlspecialchars($_POST['action']) : '';
		$forum_id = (isset($_REQUEST['this_f'])) ? intval($_REQUEST['this_f']) : ((isset($_REQUEST['f'])) ? intval($_REQUEST['f']) : 0);
		$parent_id = (isset($_REQUEST['parent_id'])) ? intval($_REQUEST['parent_id']) : 0;

		$forum_type = (isset($_POST['forum_type'])) ? intval($_POST['forum_type']) : FORUM_POST;
		$forum_status = (isset($_POST['forum_status'])) ? intval($_POST['forum_status']) : ITEM_UNLOCKED;
		$forum_name = (isset($_POST['forum_name'])) ? htmlspecialchars(stripslashes($_POST['forum_name'])) : '';
		$forum_link = (isset($_POST['forum_link'])) ? htmlspecialchars(stripslashes($_POST['forum_link'])) : ''; 
		$forum_link_track = (!empty($_POST['forum_link_track'])) ? 1 : 0;
		$forum_desc = (isset($_POST['forum_desc'])) ? str_replace("\n", '<br />', stripslashes($_POST['forum_desc'])) : '';
		$forum_image = (isset($_POST['forum_image'])) ? htmlspecialchars(stripslashes($_POST['forum_image'])) : '';
		$forum_style = (isset($_POST['forum_style'])) ? intval($_POST['forum_style']) : 0;
		$display_on_index = (!empty($_POST['display_on_index'])) ? 1 : 0;
		$forum_topics_per_page = (isset($_POST['topics_per_page'])) ? intval($_POST['topics_per_page']) : 0;
		$enable_icons = (!empty($_POST['enable_icons'])) ? 1 : 0;
		$enable_prune = (!empty($_POST['enable_prune'])) ? 1 : 0;
		$prune_days = (isset($_POST['prune_days'])) ? intval($_POST['prune_days']) : 7;
		$prune_freq = (isset($_POST['prune_freq'])) ? intval($_POST['prune_freq']) : 1;
		$prune_old_polls = (!empty($_POST['prune_old_polls'])) ? 1 : 0;
		$prune_announce = (!empty($_POST['prune_announce'])) ? 1 : 0;
		$prune_sticky = (!empty($_POST['prune_sticky'])) ? 1 : 0;
		$forum_password = (isset($_POST['forum_password'])) ? htmlspecialchars(stripslashes($_POST['forum_password'])) : '';
		$forum_password_confirm = (isset($_POST['forum_password_confirm'])) ? htmlspecialchars(stripslashes($_POST['forum_password_confirm'])) : '';

		if (isset($_POST['update']))
		{
			$error = array();
			if (!trim($_POST['forum_name']))
			{
				$error[] = $user->lang['FORUM_NAME_EMPTY'];
			}

			if (!empty($password) || !empty($password_confirm))
			{
				if ($password != $password_confirm)
				{
					$error[] = $user->lang['FORUM_PASSWORD_MISMATCH'];
				}
			}

			if ($prune_days < 0 || $prune_freq < 0)
			{
				$error[] = $user->lang['FORUM_DATA_NEGATIVE'];
			}

			// Set forum flags
			// 1 = link tracking
			// 2 = prune old polls
			// 4 = prune announcements
			// 8 = prune stickies
			$forum_flags = 0;
			$forum_flags += ($forum_link_track) ? 1 : 0;
			$forum_flags += ($prune_old_polls) ? 2 : 0;
			$forum_flags += ($prune_announce) ? 4 : 0;
			$forum_flags += ($prune_sticky) ? 8 : 0;

			// What are we going to do tonight Brain? The same thing we do everynight,
			// try to take over the world ... or decide whether to continue update
			// and if so, whether it's a new forum/cat/link or an existing one
			if (sizeof($error))
			{
				$error = implode('<br />', $error);
			}
			else if ($mode == 'add')
			{
				if ($parent_id)
				{
					$sql = 'SELECT left_id, right_id 
						FROM ' . FORUMS_TABLE . " 
						WHERE forum_id = $parent_id";
					$result = $db->sql_query($sql);

					if (!($row = $db->sql_fetchrow($result)))
					{
						trigger_error('Parent does not exist', E_USER_ERROR);
					}
					$db->sql_freeresult($result);

					extract($row);
					unset($row);

					$sql = 'UPDATE ' . FORUMS_TABLE . " 
						SET left_id = left_id + 2, right_id = right_id + 2 
						WHERE left_id > $right_id";
					$db->sql_query($sql);

					$sql = 'UPDATE ' . FORUMS_TABLE . " 
						SET right_id = right_id + 2 
						WHERE $left_id BETWEEN left_id AND right_id";
					$db->sql_query($sql);

					$left_id = $right_id;
					++$right_id;
				}
				else
				{
					$sql = 'SELECT MAX(right_id) AS right_id 
						FROM ' . FORUMS_TABLE;
					$result = $db->sql_query($sql);

					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					$left_id = $row['right_id'] + 1;
					$right_id = $left_id + 1;
				}

				$sql = array(
					'parent_id'				=> (int) $parent_id,
					'left_id'				=> (int) $left_id,
					'right_id'				=> (int) $right_id, 
					'forum_name'			=> (string) $forum_name,
					'forum_desc'			=> (string) $forum_desc, 
					'forum_type'			=> (int) $forum_type, 
					'forum_status'			=> (int) $forum_status, 
					'forum_link'			=> (string) $forum_link, 
					'forum_password'		=> (string) $forum_password, 
					'forum_topics_per_page'	=> (int) $forum_topics_per_page, 
					'forum_style'			=> (int) $forum_style, 
					'forum_image'			=> (string) $forum_image, 
					'display_on_index'		=> (int) $display_on_index,
					'forum_flags'			=> (int) $forum_flags, 
					'enable_icons'			=> (int) $enable_icons, 
					'enable_prune'			=> (int) $enable_prune,
					'prune_days'			=> (int) $prune_days,
					'prune_freq'			=> (int) $prune_freq,
				);

				$sql = 'INSERT INTO ' . FORUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql);
				$db->sql_query($sql);
					
				$forum_id = $db->sql_nextid();

				add_log('admin', 'LOG_FORUM_ADD', $forum_name);

				// Redirect to permissions
				$message = $user->lang['FORUM_UPDATED'] . '<br /><br />' . sprintf($user->lang['REDIRECT_ACL'], "<a href=\"admin_permissions.$phpEx$SID&amp;mode=forum&amp;submit_usergroups=true&amp;ug_type=forum&amp;action=usergroups&amp;f[forum][]=$forum_id\">", '</a>');
				trigger_error($message);

			}
			else if ($mode == 'edit')
			{
				$row = get_forum_info($forum_id);

				if ($row['forum_type'] != $forum_type && $action)
				{
					if ($action == 'move' && $_POST['to_forum_id'])
					{
						move_forum_content($forum_id, $_POST['to_forum_id']);
					}
					elseif ($action == 'delete')
					{
						delete_forum_content($forum_id);
					}

					$sql['forum_posts'] = 0;
					$sql['forum_topics'] = 0;
					$sql['forum_topics_real'] = 0;
				}

				if ($row['parent_id'] != $parent_id)
				{
					move_forum($forum_id, $parent_id);
				}
				elseif ($row['forum_name'] != $forum_name)
				{
					$sql = 'UPDATE ' . FORUMS_TABLE . "
						SET forum_parents = '' 
						WHERE left_id > " . $row['left_id'] . ' 
							AND right_id < ' . $row['right_id'];
					$db->sql_query($sql);
				}

				$sql = array(
					'parent_id'				=> (int) $parent_id,
					'forum_name'			=> (string) $forum_name,
					'forum_desc'			=> (string) $forum_desc, 
					'forum_type'			=> (int) $forum_type, 
					'forum_status'			=> (int) $forum_status, 
					'forum_link'			=> (string) $forum_link, 
					'forum_topics_per_page'	=> (int) $forum_topics_per_page, 
					'forum_password'		=> (string) $forum_password, 
					'forum_style'			=> (int) $forum_style, 
					'forum_image'			=> (string) $forum_image, 
					'display_on_index'		=> (int) $display_on_index,
					'forum_flags'			=> (int) $forum_flags, 
					'enable_icons'			=> (int) $enable_icons,
					'enable_prune'			=> (int) $enable_prune,
					'prune_days'			=> (int) $prune_days,
					'prune_freq'			=> (int) $prune_freq,
				);

				$sql = 'UPDATE ' . FORUMS_TABLE . ' 
					SET ' . $db->sql_build_array('UPDATE', $sql) . " 
					WHERE forum_id = $forum_id";
				$db->sql_query($sql);

				add_log('admin', 'LOG_FORUM_EDIT', $forum_name);

				trigger_error($user->lang['FORUM_UPDATED']);
			}
		}

		// Show form to create/modify a forum
		if ($mode == 'edit')
		{
			$l_title = $user->lang['EDIT_FORUM'];
	
			$forum_data = get_forum_info($forum_id);
			if (!isset($_POST['forum_type']))
			{
				extract($forum_data);
			}
			else
			{
				$old_forum_type = $forum_data['forum_type'];
			}
			unset($forum_data);

			$parents_list = make_forum_select($parent_id, $forum_id, false, false, false);
			$forums_list = make_forum_select($parent_id, $forum_id, false, true, false);

			$forum_password_confirm = $forum_password;
		}
		else
		{
			$l_title = $user->lang['CREATE_FORUM'];

			$forum_id = $parent_id;
			$parents_list = make_forum_select($parent_id, false, false, false, false);

			if ($parent_id && !isset($_POST['update']))
			{
				$temp_forum_desc = $forum_desc;
				$temp_forum_name = $forum_name;
				$temp_forum_type = $forum_type;

				extract(get_forum_info($parent_id));
				$forum_type = $temp_forum_type;
				$forum_name = $temp_forum_name;
				$forum_desc = $temp_forum_desc;
				$forum_password_confirm = $forum_password;
			}
		}

		$forum_type_options = '';
		$forum_type_ary = array(FORUM_CAT => 'CAT', FORUM_POST => 'FORUM', FORUM_LINK => 'LINK');
		foreach ($forum_type_ary as $value => $lang)
		{
			$forum_type_options .= '<option value="' . $value . '"' . (($value == $forum_type) ? ' selected="selected"' : '') . '>' . $user->lang['TYPE_' . $lang] . '</option>';
		}

		$styles_list = style_select($forum_style);

		$statuslist = '<option value="' . ITEM_UNLOCKED . '"' . (($forum_status == ITEM_UNLOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['UNLOCKED'] . '</option><option value="' . ITEM_LOCKED . '"' . (($forum_status == ITEM_LOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['LOCKED'] . '</option>';

		$topic_icons_yes = ($enable_icons) ? ' checked="checked"' : '';
		$topic_icons_no = (!$enable_icons) ? ' checked="checked"' : '';

		$display_index_yes = ($display_on_index) ? ' checked="checked"' : '';
		$display_index_no = (!$display_on_index) ? ' checked="checked"' : '';

		$prune_enable_yes = ($enable_prune) ? ' checked="checked"' : '';
		$prune_enable_no = (!$enable_prune) ? ' checked="checked"' : '';

		$prune_old_polls_yes = ($forum_flags & 2) ? ' checked="checked"' : '';
		$prune_old_polls_no = (!($forum_flags & 2)) ? ' checked="checked"' : '';
		$prune_announce_yes = ($forum_flags & 4) ? ' checked="checked"' : '';
		$prune_announce_no = (!($forum_flags & 4)) ? ' checked="checked"' : '';
		$prune_sticky_yes = ($forum_flags & 8) ? ' checked="checked"' : '';
		$prune_sticky_no = (!($forum_flags & 8)) ? ' checked="checked"' : '';

		$forum_link_track_yes = ($forum_flags & 1) ? ' checked="checked"' : '';
		$forum_link_track_no = (!($forum_flags & 1)) ? ' checked="checked"' : '';

		$navigation = '<a href="admin_forums.' . $phpEx . $SID . '">' . $user->lang['FORUM_INDEX'] . '</a>';

		$forums_nav = get_forum_branch($forum_id, 'parents', 'descending');
		foreach ($forums_nav as $row)
		{
			$navigation .= ($row['forum_id'] == $forum_id) ? ' -&gt; ' . $row['forum_name'] : ' -&gt; <a href="admin_forums.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>';
		}

		adm_page_header($l_title);

?>

<p><?php echo $user->lang['FORUM_ADMIN_EXPLAIN'] ?></p>

<h1><?php echo $l_title ?></h1>

<p><?php echo $user->lang['FORUM_EDIT_EXPLAIN'] ?></p>

<form method="post" name="edit" action="<?php echo "admin_forums.$phpEx$SID&amp;mode=$mode" . (($forum_id) ? "&amp;f=$forum_id" : ''); ?>"><table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr>
		<td class="nav"><?php echo $navigation ?></td>
	</tr>
</table>

<table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['FORUM_SETTINGS'] ?></th>
	</tr>
<?php

		if (!empty($error))
		{

?>
	<tr>
		<td class="row3" colspan="2" align="center"><span style="color:red"><?php echo $error; ?></span></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="row1" width="33%"><?php echo $user->lang['FORUM_TYPE'] ?>: </td>
		<td class="row2"><select name="forum_type" onchange="this.form.submit();"><?php echo $forum_type_options; ?></select><?php
	
		if ($old_forum_type == FORUM_POST && $forum_type == FORUM_CAT)
		{

?><br /><input type="radio" name="action" value="delete" checked="checked" /> <?php echo $user->lang['Delete_all_posts'] ?> &nbsp;<input type="radio" name="action" value="move" /> <?php echo $user->lang['Move_posts_to'] ?> <select name="to_forum_id"><?php echo $forums_list ?></select><?php

		}

?></td>
	</tr>
<?php

		if ($forum_type == FORUM_POST)
		{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_STATUS'] ?>: </td>
		<td class="row2"><select name="forum_status"><?php echo $statuslist ?></select></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="row1" width="40%"><?php echo $user->lang['FORUM_PARENT'] ?>: </td>
		<td class="row2"><select name="parent_id"><option value="0"><?php echo $user->lang['NO_PARENT'] ?></option><?php echo $parents_list ?></select></td>
	</tr>
<?php

		if ($forum_type == FORUM_LINK)
		{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_LINK'] ?>: <br /><span class="gensmall"><?php echo $user->lang['FORUM_LINK_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" name="forum_link" value="<?php echo $forum_link; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_LINK_TRACK'] ?>: <br /><span class="gensmall"><?php echo $user->lang['FORUM_LINK_TRACK_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="forum_link_track" value="1"<?php echo $forum_link_track_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="forum_link_track" value="0"<?php echo $forum_link_track_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_NAME']; ?>: </td>
		<td class="row2"><input class="post" type="text" size="25" name="forum_name" value="<?php echo $forum_name ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_DESC'] ?>: <br /><span class="gensmall"><?php echo $user->lang['FORUM_DESC_EXPLAIN']; ?></span> </td>
		<td class="row2"><textarea class="post" rows="5" cols="45" wrap="virtual" name="forum_desc"><?php echo htmlspecialchars(str_replace('<br />', "\n", $forum_desc)); ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_IMAGE']; ?>: <br /><span class="gensmall"><?php echo $user->lang['FORUM_IMAGE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" name="forum_image" value="<?php echo $forum_image ?>" /><br /><?php 
	
		if ($forum_image != '')
		{

			echo '<img src="../' . $forum_image . '" alt="" />';

		}
		
?></td>
	</tr>
<?php

		if ($forum_type == FORUM_POST)
		{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_STYLE'] ?>: </td>
		<td class="row2"><select name="forum_style"><option value="0"><?php echo $user->lang['DEFAULT_STYLE'] ?></option><?php echo $styles_list ?></select></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['ENABLE_TOPIC_ICONS'] ?>: </td>
		<td class="row2"><input type="radio" name="enable_icons" value="1"<?php echo $topic_icons_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="enable_icons" value="0"<?php echo $topic_icons_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

			if ($mode == 'edit' && $parent_id > 0)
			{
				// if this forum is a subforum put the "display on index" checkbox
				if ($parent_info = get_forum_info($parent_id))
				{
					if ($parent_info['parent_id'] > 0 || $parent_info['forum_type'] == FORUM_CAT)
					{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['LIST_INDEX'] ?>: <br /><span class="gensmall"><?php echo $user->lang['LIST_INDEX_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="display_on_index" value="1"<?php echo $display_index_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="display_on_index" value="0"<?php echo $display_index_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

					}
				}
			}

?>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_AUTO_PRUNE'] ?>: <br /><span class="gensmall"><?php echo $user->lang['FORUM_AUTO_PRUNE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="enable_prune" value="1"<?php echo $prune_enable_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="enable_prune" value="0"<?php echo $prune_enable_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['AUTO_PRUNE_FREQ'] ?>: <br /><span class="gensmall"><?php echo $user->lang['AUTO_PRUNE_FREQ_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="prune_freq" value="<?php echo $prune_freq ?>" size="5" /> <?php echo $user->lang['DAYS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['AUTO_PRUNE_DAYS'] ?>: <br /><span class="gensmall"><?php echo $user->lang['AUTO_PRUNE_DAYS_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="prune_days" value="<?php echo $prune_days ?>" size="5" /> <?php echo $user->lang['DAYS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['PRUNE_OLD_POLLS'] ?>: <br /><span class="gensmall"><?php echo $user->lang['PRUNE_OLD_POLLS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="prune_old_polls" value="1"<?php echo $prune_old_polls_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_old_polls" value="0"<?php echo $prune_old_polls_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['PRUNE_ANNOUNCEMENTS'] ?>: </td>
		<td class="row2"><input type="radio" name="prune_announce" value="1"<?php echo $prune_announce_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_announce" value="0"<?php echo $prune_announce_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['PRUNE_STICKY'] ?>: </td>
		<td class="row2"><input type="radio" name="prune_sticky" value="1"<?php echo $prune_sticky_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_sticky" value="0"<?php echo $prune_sticky_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_TOPICS_PAGE'] ?>: <br /><span class="gensmall"><?php echo $user->lang['FORUM_TOPICS_PAGE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="text" name="topics_per_page" value="<?php echo $forum_topics_per_page; ?>" size="3" maxlength="3" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_PASSWORD'] ?>: <br /><span class="gensmall"><?php echo $user->lang['FORUM_PASSWORD_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="password" name="forum_password" value="<?php echo $forum_password; ?>" size="25" maxlength="25" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_PASSWORD_CONFIRM'] ?>: <br /><span class="gensmall"><?php echo $user->lang['FORUM_PASSWORD_CONFIRM_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="password" name="forum_password_confirm" value="<?php echo $forum_password_confirm; ?>" size="25" maxlength="25" /></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="mainoption" name="update" type="submit" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp;<input class="liteoption" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<br clear="all" />

<?php

		adm_page_footer();
		break;

	case 'delete':

		$forum_id = (isset($_REQUEST['this_f'])) ? intval($_REQUEST['this_f']) : ((isset($_REQUEST['f'])) ? intval($_REQUEST['f']) : 0);

		if (isset($_POST['update']))
		{

			$action_subforums = (!empty($_POST['action_subforums'])) ? $_POST['action_subforums'] : '';
			$action_posts = (!empty($_POST['action_posts'])) ? $_POST['action_posts'] : '';

			$row = get_forum_info($forum_id);
			extract($row);

			$log_action_posts = $log_action_forums = '';
			if ($action_posts == 'delete')
			{
				$log_action_posts = 'POSTS';
				delete_forum_content($forum_id);
			}
			elseif ($action_posts == 'move')
			{
				if (empty($_POST['posts_to_id']))
				{
					trigger_error($user->lang['NO_DESTINATION_FORUM']);
				}

				$log_action_posts = 'MOVE_POSTS';

				$sql = 'SELECT forum_name  
					FROM ' . FORUMS_TABLE . " 
					WHERE forum_id = " . intval($_POST['posts_to_id']);
				$result = $db->sql_query($sql);

				if (!($row = $db->sql_fetchrow($result)))
				{
					trigger_error($user->lang['NO_FORUM']);
				}
				$db->sql_freeresult($result);

				$posts_to_name = $row['forum_name'];
				unset($row);

				move_forum_content($forum_id, intval($_POST['posts_to_id']));
			}

			if ($action_subforums == 'delete')
			{
				$log_action_forums = 'FORUMS';

				$forum_ids = array($forum_id);
				$rows = get_forum_branch($forum_id, 'children', 'descending', FALSE);

				foreach ($rows as $row)
				{
					$forum_ids[] = $row['forum_id'];
					delete_forum_content($row['forum_id']);
				}

				$diff = count($forum_ids) * 2;

				$sql = 'DELETE FROM ' . FORUMS_TABLE . ' 
					WHERE forum_id IN (' . implode(', ', $forum_ids) . ')';
				$db->sql_query($sql);
			}
			elseif ($action_subforums == 'move')
			{
				if (empty($_POST['subforums_to_id']))
				{
					trigger_error($user->lang['NO_DESTINATION_FORUM']);
				}

				$log_action_forums = 'MOVE_FORUMS';

				$sql = 'SELECT forum_name  
					FROM ' . FORUMS_TABLE . " 
					WHERE forum_id = " . intval($_POST['subforums_to_id']);
				$result = $db->sql_query($sql);

				if (!($row = $db->sql_fetchrow($result)))
				{
					trigger_error($user->lang['NO_FORUM']);
				}
				$db->sql_freeresult($result);

				$subforums_to_name = $row['forum_name'];
				unset($row);

				$sql = 'SELECT forum_id 
					FROM ' . FORUMS_TABLE . " 
					WHERE parent_id = $forum_id";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					move_forum($row['forum_id'], intval($_POST['subforums_to_id']));
				}
				$db->sql_freeresult($result);

				$sql = 'UPDATE ' . FORUMS_TABLE . ' 
					SET parent_id = ' . $_POST['subforums_to_id'] . " 
					WHERE parent_id = $forum_id";
				$db->sql_query($sql);

				$diff = 2;

				$sql = 'DELETE FROM ' . FORUMS_TABLE . " 
					WHERE forum_id = $forum_id";
				$db->sql_query($sql);
			}
			else
			{
				$diff = 2;
				$sql = 'DELETE FROM ' . FORUMS_TABLE . " 
					WHERE forum_id = $forum_id";
				$db->sql_query($sql);
			}

			// Resync tree
			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET right_id = right_id - $diff
				WHERE left_id < $right_id AND right_id > $right_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET left_id = left_id - $diff, right_id = right_id - $diff
				WHERE left_id > $right_id";
			$db->sql_query($sql);

			$log_action = implode('_', array($log_action_posts, $log_action_forums));

			switch ($log_action)
			{
				case 'MOVE_POSTS_MOVE_FORUMS':
					add_log('admin', 'LOG_FORUM_DEL_MOVE_POSTS_MOVE_FORUMS', $posts_to_name, $subforums_to_name, $forum_name);
					break;
				case 'MOVE_POSTS_FORUMS':
					add_log('admin', 'LOG_FORUM_DEL_MOVE_POSTS_FORUMS', $posts_to_name, $forum_name);
					break;
				case 'POSTS_MOVE_FORUMS':
					add_log('admin', 'LOG_FORUM_DEL_POSTS_MOVE_FORUMS',$subforums_to_name, $forum_name);
					break;
				case '_MOVE_FORUMS':
					add_log('admin', 'LOG_FORUM_DEL_MOVE_FORUMS', $subforums_to_name, $forum_name);
					break;
				case 'MOVE_POSTS_':
					add_log('admin', 'LOG_FORUM_DEL_MOVE_POSTS', $posts_to_name, $forum_name);
					break;
				case 'POSTS_FORUMS':
					add_log('admin', 'LOG_FORUM_DEL_POSTS_FORUMS', $forum_name);
					break;
				case '_FORUMS':
					add_log('admin', 'LOG_FORUM_DEL_FORUMS', $forum_name);
					break;
				case 'POSTS_':
					add_log('admin', 'LOG_FORUM_DEL_POSTS', $forum_name);
					break;
			}

			trigger_error($user->lang['FORUM_DELETED']);
		}
	

		adm_page_header($user->lang['MANAGE']);
		extract(get_forum_info($forum_id));

		$subforums_id = array();
		$subforums = get_forum_branch($forum_id, 'children');
		foreach ($subforums as $row)
		{
			$subforums_id[] = $row['forum_id'];
		}

		$forums_list = make_forum_select($parent_id, $subforums_id);
		$move_posts_list = make_forum_select($parent_id, $subforums_id);

?>

<p><?php echo $user->lang['FORUM_ADMIN_EXPLAIN']; ?></p>

<h1><?php echo $user->lang['FORUM_DELETE'] ?></h1>

<p><?php echo $user->lang['FORUM_DELETE_EXPLAIN'] ?></p>

<form action="admin_forums.<?php echo $phpEx . $SID ?>&mode=delete&amp;f=<?php echo $forum_id ?>" method="post"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['FORUM_DELETE'] ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['FORUM_NAME']; ?>: </td>
		<td class="row1"><b><?php echo $forum_name ?></b></td>
	</tr>
<?php

	if ($forum_type == FORUM_POST)
	{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['ACTION'] ?>: </td>
		<td class="row1"><table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><input type="radio" name="action_posts" value="delete" checked="checked" /> <?php echo $user->lang['DELETE_ALL_POSTS'] ?></td>
			</tr>
			<tr>
				<td><input type="radio" name="action_posts" value="move" /> <?php echo $user->lang['MOVE_POSTS_TO'] ?> <select name="posts_to_id" ?><?php echo $move_posts_list ?></select></td>
			</tr>
		</table></td>
	</tr>
<?php

	}

	if ($right_id - $left_id > 1)
	{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['ACTION'] ?>:</td>
		<td class="row1"><table cellspacing="0" cellpadding="2" border="0">
			<tr>
				<td><input type="radio" name="action_subforums" value="delete" checked="checked" /> <?php echo $user->lang['DELETE_SUBFORUMS'] ?></td>
			</tr>
			<tr>
				<td><input type="radio" name="action_subforums" value="move" /> <?php echo $user->lang['MOVE_SUBFORUMS_TO'] ?> <select name="subforums_to_id" ?><?php echo $forums_list ?></select></td>
			</tr>
		</table></td>
	</tr>
<?php

	}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="submit" name="update" value="<?php echo $user->lang['SUBMIT'] ?>" class="mainoption" /></td>
	</tr>
</table></form>
<?php

		adm_page_footer();
		break;

	case 'move_up':
	case 'move_down':
		$forum_id = intval($_GET['this_f']);

		$sql = 'SELECT parent_id, left_id, right_id 
			FROM ' . FORUMS_TABLE . " 
			WHERE forum_id = $forum_id";
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_FORUM']);
		}
		$db->sql_freeresult($result);

		extract($row);

		$forum_info = array($forum_id => $row);

		// Get the adjacent forum
		$sql = 'SELECT forum_id, forum_name, left_id, right_id 
			FROM ' . FORUMS_TABLE . " 
			WHERE parent_id = $parent_id 
				AND " . (($mode == 'move_up') ? "right_id < $right_id ORDER BY right_id DESC" : "left_id > $left_id ORDER BY left_id ASC");
		$result = $db->sql_query_limit($sql, 1);

		if (!($row = $db->sql_fetchrow($result)))
		{
			// already on top or at bottom
			break;
		}
		$db->sql_freeresult($result);

		if ($mode == 'move_up')
		{
			$log_action = 'UP';
			$up_id = $forum_id;
			$down_id = $row['forum_id'];
		}
		else
		{
			$log_action = 'DOWN';
			$up_id = $row['forum_id'];
			$down_id = $forum_id;
		}

		$move_forum_name = $row['forum_name'];
		$forum_info[$row['forum_id']] = $row;
		$diff_up = $forum_info[$up_id]['right_id'] - $forum_info[$up_id]['left_id'];
		$diff_down = $forum_info[$down_id]['right_id'] - $forum_info[$down_id]['left_id'];

		$forum_ids = array();
		$sql = 'SELECT forum_id
			FROM ' . FORUMS_TABLE . '
			WHERE left_id > ' . $forum_info[$up_id]['left_id'] . ' 
				AND right_id < ' . $forum_info[$up_id]['right_id'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$forum_ids[] = $row['forum_id'];
		}
		$db->sql_freeresult($result);

		// Start transaction
		$db->sql_transaction();

		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET left_id = left_id + ' . ($diff_up + 1) . ', right_id = right_id + ' . ($diff_up + 1) . '
			WHERE left_id > ' . $forum_info[$down_id]['left_id'] . ' 
				AND right_id < ' . $forum_info[$down_id]['right_id'];
		$db->sql_query($sql);

		if (count($forum_ids))
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET left_id = left_id - ' . ($diff_down + 1) . ', right_id = right_id - ' . ($diff_down + 1) . '
				WHERE forum_id IN (' . implode(', ', $forum_ids) . ')';
			$db->sql_query($sql);
		}

		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET left_id = ' . $forum_info[$down_id]['left_id'] . ', right_id = ' . ($forum_info[$down_id]['left_id'] + $diff_up) . '
			WHERE forum_id = ' . $up_id;
		$db->sql_query($sql);

		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET left_id = ' . ($forum_info[$up_id]['right_id'] - $diff_down) . ', right_id = ' . $forum_info[$up_id]['right_id'] . '
			WHERE forum_id = ' . $down_id;
		$db->sql_query($sql);

		$db->sql_transaction('commit');

		$forum_data = get_forum_info($forum_id);
		add_log('admin', 'LOG_FORUM_MOVE_' . $log_action, $forum_data['forum_name'], $move_forum_name);
		unset($forum_data);
		break;

	case 'sync':
		$forum_id = (isset($_REQUEST['this_f'])) ? intval($_REQUEST['this_f']) : ((isset($_REQUEST['f'])) ? intval($_REQUEST['f']) : 0);

		if (!$forum_id)
		{
			trigger_error($user->lang['NO_FORUM']);
		}

		$sql = "SELECT forum_name 
			FROM " . FORUMS_TABLE . " 
			WHERE forum_id = $forum_id";
		$result = $db->sql_query($sql);

		if (!($row = $db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_FORUM']);
		}
		$db->sql_freeresult($result);

		add_log('admin', 'LOG_FORUM_SYNC', $row['forum_name']);

		sync('forum', 'forum_id', $forum_id);
		break;
}

// Default management page

$forum_id = (!empty($_GET['f'])) ? intval($_GET['f']) : 0;

if (!$forum_id)
{
	$navigation = $user->lang['FORUM_INDEX'];
}
else
{
	$navigation = '<a href="admin_forums.' . $phpEx . $SID . '">' . $user->lang['FORUM_INDEX'] . '</a>';

	$forums_nav = get_forum_branch($forum_id, 'parents', 'descending');
	foreach ($forums_nav as $row)
	{
		if ($row['forum_id'] == $forum_id)
		{
			$navigation .= ' -&gt; ' . $row['forum_name'];
		}
		else
		{
			$navigation .= ' -&gt; <a href="admin_forums.' . $phpEx . $SID . '&amp;f=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>';
		}
	}
}

// Jumpbox
$forum_box = make_forum_select($forum_id);

// Front end
adm_page_header($user->lang['MANAGE']);

?>

<h1><?php echo $user->lang['MANAGE']; ?></h1>

<p><?php echo $user->lang['FORUM_ADMIN_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_forums.$phpEx$SID" ?>"><table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr>
		<td class="nav"><?php echo $navigation ?></td>
	</tr>
</table>
		
<table class="bg" width="100%" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="6"><?php echo $user->lang['FORUM_ADMIN'] ?></th>
	</tr>
<?php

$sql = 'SELECT * 
	FROM ' . FORUMS_TABLE . " 
	WHERE parent_id = $forum_id 
	ORDER BY left_id";
$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
	$forum_type = $row['forum_type'];

	if ($row['forum_status'] == ITEM_LOCKED)
	{
		$folder_image = '<img src="images/icon_folder_lock.gif" width="46" height="25" alt="' . $user->lang['LOCKED'] . '" alt="' . $user->lang['LOCKED'] . '" />'; 
	}
	else
	{
		switch ($forum_type)
		{
			case FORUM_LINK:
				$folder_image = '<img src="images/icon_folder_link.gif" width="46" height="25" alt="' . $user->lang['LINK'] . '" alt="' . $user->lang['LINK'] . '" />'; 
				break;

			default:
				$folder_image = ($row['left_id'] + 1 != $row['right_id']) ? '<img src="images/icon_subfolder.gif" width="46" height="25" alt="' . $user->lang['SUBFORUM'] . '" alt="' . $user->lang['SUBFORUM'] . '" />' : '<img src="images/icon_folder.gif" width="46" height="25" alt="' . $user->lang['FOLDER'] . '" alt="' . $user->lang['FOLDER'] . '" />'; 
		}
	}

	$forum_title = ($forum_type != FORUM_LINK) ? "<a href=\"admin_forums.$phpEx$SID&amp;f=" . $row['forum_id'] . '">' : '';
	$forum_title .= $row['forum_name'];
	$forum_title .= ($forum_type != FORUM_LINK) ? '</a>' : '';
	$url = "$phpEx$SID&amp;f=$forum_id&amp;this_f=" . $row['forum_id'];

?>
	<tr>
		<td class="row1" width="5%"><?php echo $folder_image; ?></td>
		<td class="row1" width="50%"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr>
				<td><span class="forumlink"><?php echo $forum_title ?></span></td><?php

	if ($forum_type == FORUM_POST)
	{

?>
				<td class="gensmall" align="right">&nbsp;<?php echo $user->lang['TOPICS']; ?>: <b><?php echo $row['forum_topics'] ?></b> / <?php echo $user->lang['POSTS']; ?>: <b><?php echo $row['forum_posts'] ?></b></td><?php

	}

?>
			</tr>
			</table>
			<table cellspacing="5" cellpadding="0" border="0">
				<tr>
					<td class="gensmall"><?php echo $row['forum_desc'] ?></td>
				</tr>
			</table></td>
		<td class="row2" width="15%" align="center" valign="middle" nowrap="nowrap"><a href="admin_forums.<?php echo $url ?>&amp;mode=move_up"><?php echo $user->lang['MOVE_UP'] ?></a><br /><a href="admin_forums.<?php echo $url ?>&amp;mode=move_down"><?php echo $user->lang['MOVE_DOWN'] ?></a></td>
		<td class="row2" width="20%" align="center" valign="middle" nowrap="nowrap">&nbsp;<a href="admin_forums.<?php echo $url ?>&amp;mode=edit"><?php echo $user->lang['EDIT'] ?></a> | <a href="admin_forums.<?php echo $url ?>&amp;mode=delete"><?php echo $user->lang['DELETE'] ?></a><?php
			
	if ($forum_type != FORUM_LINK)
	{

?> | <a href="admin_forums.<?php echo $url ?>&amp;mode=sync"><?php echo $user->lang['RESYNC'] ?></a><?php
	

	}
	
?>&nbsp;</td>
	</tr>
<?php

}

?>
	<tr>
		<td width="100%" colspan="6" class="cat"><input type="hidden" name="mode" value="add" /><input type="hidden" name="parent_id" value="<? echo $forum_id ?>" /><input type="text" name="forum_name" /> <input class="liteoption" type="submit" value="<?php echo $user->lang['CREATE_FORUM'] ?>" /></td>
	</tr>
</table></form>

<form method="get" action="admin_forums.<?php echo $phpEx,$SID ?>"><table width="100%" cellpadding="1" cellspacing="1" border="0">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_FORUM']; ?>: <select name="f" onchange="if(this.options[this.selectedIndex].value != -1){ this.form.submit(); }"><?php echo $forum_box; ?></select> <input class="liteoption" type="submit" value="<?php echo $user->lang['GO']; ?>" /><input type="hidden" name="sid" value="<?php echo $user->session_id; ?>" /></td>
	</tr>
</table></form>
<?php

adm_page_footer();

//
// END
//














// ------------------
// Begin function block
//

function get_forum_info($forum_id)
{
	global $db;

	$sql = 'SELECT *
		FROM ' . FORUMS_TABLE . "
		WHERE forum_id = $forum_id";
	$result = $db->sql_query($sql);

	if (!$row = $db->sql_fetchrow($result))
	{
		trigger_error("Forum #$forum_id does not exist", E_USER_ERROR);
	}

	return $row;
}

function move_forum($from_id, $to_id)
{
	global $db;

	$moved_forums = get_forum_branch($from_id, 'children', 'descending');
	$from_data = $moved_forums[0];
	$diff = count($moved_forums) * 2;

	$moved_ids = array();
	for ($i = 0; $i < count($moved_forums); ++$i)
	{
		$moved_ids[] = $moved_forums[$i]['forum_id'];
	}

	// Resync parents
	$sql = 'UPDATE ' . FORUMS_TABLE . "
		SET right_id = right_id - $diff, forum_parents = ''
		WHERE left_id < " . $from_data['right_id'] . " 
			AND right_id > " . $from_data['right_id'];
	$db->sql_query($sql);

	// Resync righthand side of tree
	$sql = 'UPDATE ' . FORUMS_TABLE . "
		SET left_id = left_id - $diff, right_id = right_id - $diff, forum_parents = ''
		WHERE left_id > " . $from_data['right_id'];
	$db->sql_query($sql);

	if ($to_id > 0)
	{
		$to_data = get_forum_info($to_id);

		// Resync new parents
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET right_id = right_id + $diff, forum_parents = ''
			WHERE " . $to_data['right_id'] . ' BETWEEN left_id AND right_id
				AND forum_id NOT IN (' . implode(', ', $moved_ids) . ')';
		$db->sql_query($sql);

		// Resync the righthand side of the tree
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET left_id = left_id + $diff, right_id = right_id + $diff, forum_parents = ''
			WHERE left_id > " . $to_data['right_id'] . '
				AND forum_id NOT IN (' . implode(', ', $moved_ids) . ')';
		$db->sql_query($sql);

		// Resync moved branch
		$to_data['right_id'] += $diff;
		if ($to_data['right_id'] > $from_data['right_id'])
		{
			$diff = '+ ' . ($to_data['right_id'] - $from_data['right_id'] - 1);
		}
		else
		{
			$diff = '- ' . abs($to_data['right_id'] - $from_data['right_id'] - 1);
		}
	}
	else
	{
		$sql = 'SELECT MAX(right_id) AS right_id 
			FROM ' . FORUMS_TABLE . ' 
			WHERE forum_id NOT IN (' . implode(', ', $moved_ids) . ')';
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$diff = '+ ' . ($row['right_id'] - $from_data['left_id'] + 1);
	}

	$sql = 'UPDATE ' . FORUMS_TABLE . "
		SET left_id = left_id $diff, right_id = right_id $diff, forum_parents = ''
		WHERE forum_id IN (" . implode(', ', $moved_ids) . ')';
	$db->sql_query($sql);
}

function move_forum_content($from_id, $to_id)
{
	global $db;

	$table_ary = array(LOG_MOD_TABLE, POSTS_TABLE, TOPICS_TABLE);
	foreach ($sql_ary as $table)
	{
		$sql = "UPDATE $table 
			SET forum_id = $to_id
			WHERE forum_id = $from_id";
		$db->sql_query($sql);
	}
	unset($table_ary);

	$sql = 'SELECT t1.topic_id
		FROM ' .TOPICS_TABLE . ' t1, ' . TOPICS_TABLE . " t2 
		WHERE t2.forum_id = $to_id 
			AND t1.topic_moved_id = t2.topic_id 
			AND t1.forum_id = t2.forum_id";
	$result = $db->sql_query($result);

	if ($row = $db->sql_fetchrow($result))
	{
		$topic_id_ary = array();
		do
		{
			$topic_id_ary[] = $row['topic_id'];
		}
		while ($row = $db->sql_fetchrow($result));

		$sql = 'DELETE FROM ' . TOPICS_TABLE . ' 
			WHERE topic_id IN (' . implode(', ', $topic_id_ary) . ')';
		$db->sql_query($sql);
		unset($topic_id_ary);
	}
	$db->sql_freeresult($result);

	sync('forum', 'forum_id', $to_id);
}

function delete_forum_content($forum_id)
{
	global $db;

	$db->sql_transaction();

	$sql = 'SELECT post_id 
		FROM ' . POSTS_TABLE . " 
		WHERE forum_id = $forum_id";
	$result = $db->sql_query();

	if ($row = $db->sql_fetchrow($result))
	{
		$id_ary = array();

		do
		{
			$id_ary[] = $row['post_id'];
		}
		while ($row = $db->sql_fetchrow($result));

		// TODO
		// Could be problematical with large forums ... should split array
		// if large
		$sql = 'DELETE FROM ' . SEARCH_MATCH_TABLE . ' 
			WHERE post_id IN (' . implode(', ', $id_ary) . ')';
		$db->sql_query($sql);

		// Remove attachments
		delete_attachment($id_ary);
		unset($id_ary);
	}
	$db->sql_freeresult();

	$sql = 'SELECT topic_id 
		FROM ' . TOPICS_TABLE . " 
		WHERE forum_id = $forum_id";
	$result = $db->sql_query();

	if ($row = $db->sql_fetchrow($result))
	{
		$id_ary = array();
		do
		{
			$id_ary[] = $row['topic_id'];
		}
		while ($row = $db->sql_fetchrow($result));

		$sql_in = implode(', ', $id_ary);
		unset($id_ary);

		$table_ary = array(TOPICS_WATCH_TABLE, POLL_OPTIONS_TABLE, POLL_VOTES_TABLE);
		foreach ($sql_ary as $table)
		{
			$sql = "DELETE FROM $table 
				WHERE topic_id IN ($sql_in)";
			$db->sql_query($sql);
		}
		unset($table_ary);

		$sql = 'DELETE FROM ' . TOPICS_TABLE . " 
			WHERE topic_moved_id IN ($sql_in)";
		$db->sql_query($sql);

		unset($sql_in);
	}
	$db->sql_freeresult();

	$table_ary = array(TOPICS_TABLE, POSTS_TABLE, ACL_GROUPS_TABLE, ACL_USERS_TABLE, MODERATOR_TABLE, LOG_MOD_TABLE, FORUMS_WATCH_TABLE);
	foreach ($sql_ary as $table)
	{
		$sql = "DELETE FROM $table 
			WHERE forum_id = $forum_id";
		$db->sql_query($sql);
	}
	unset($table_ary);

	switch (SQL_LAYER)
	{
		case 'mysql':
		case 'mysql4':
/*			$sql = 'SHOW TABLES';
			$result = $db->sql_query($sql);

			$field_name = 'Tables_in_' . $db->dbname;
			$table_ary = array();
			while ($row = $db->sql_fetchrow($result))
			{
				if (preg_match('#^' . preg_quote($phpEx, '#') . '#', $row[$field_name]))
				{
					$table_ary[] = $row[$field_name];
				}
			}
			$db->sql_freeresult($result);

			if (sizeof($table_ary))
			{*/
				$table_ary = array(TOPICS_TABLE, POSTS_TABLE, ACL_GROUPS_TABLE, ACL_USERS_TABLE, MODERATOR_TABLE, LOG_MOD_TABLE, FORUMS_WATCH_TABLE, TOPICS_WATCH_TABLE, POLL_OPTIONS_TABLE, POLL_VOTES_TABLE, SEARCH_MATCH_TABLE);
				$sql = 'OPTIMIZE TABLE ' . implode(', ', $table_ary);
				$db->sql_query($sql);
//			}
			unset($table_ary);

			break;

		case 'postgresql':
			$db->sql_query('VACUUM');
			break;
	}

	$db->sql_transaction('commit');
}

//
// End function block
// ------------------

?>