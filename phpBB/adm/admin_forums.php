<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : admin_forums.php
// STARTED   : Thu Jul 12, 2001
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ]
//
// -------------------------------------------------------------

/*
	TODO:

	- make a function to verify and/or fix the tree?
*/

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
// Include *files
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Get general vars
$update		= (isset($_POST['update'])) ? true : false;
$mode		= request_var('mode', '');
$action		= request_var('action', '');
$forum_id	= request_var('f', 0);
$parent_id	= request_var('parent_id', 0);

$forum_data = $errors = array();

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
if ($update)
{
	switch ($mode)
	{
		case 'delete':
			$action_subforums	= request_var('action_subforums', '');
			$subforums_to_id	= request_var('subforums_to_id', 0);
			$action_posts		= request_var('action_posts', '');
			$posts_to_id		= request_var('posts_to_id', 0);

			delete_forum($forum_id, $action_posts, $action_subforums, $posts_to_id, $subforums_to_id);

			trigger_error($user->lang['FORUM_DELETED']);
			break;

		case 'edit':
			$forum_data = array(
				'forum_id'		=>	$forum_id
			);

			// No break here

		case 'add':
			$forum_data += array(
				'parent_id'				=> $parent_id,
				'forum_type'			=> request_var('forum_type', FORUM_POST),
				'forum_status'			=> request_var('forum_status', ITEM_UNLOCKED),
				'forum_name'			=> request_var('forum_name', ''),
				'forum_link'			=> request_var('forum_link', ''),
				'forum_link_track'		=> request_var('forum_link_track', FALSE),
				'forum_desc'			=> str_replace("\n", '<br />', request_var('forum_desc', '')),
				'forum_image'			=> request_var('forum_image', ''),
				'forum_style'			=> request_var('forum_style', 0),
				'display_on_index'		=> request_var('display_on_index', FALSE),
				'forum_topics_per_page'	=> request_var('topics_per_page', 0), 
				'enable_indexing'		=> request_var('enable_indexing',true), 
				'enable_icons'			=> request_var('enable_icons', FALSE),
				'enable_prune'			=> request_var('enable_prune', FALSE),
				'prune_days'			=> request_var('prune_days', 7),
				'prune_viewed'			=> request_var('prune_viewed', 7),
				'prune_freq'			=> request_var('prune_freq', 1),
				'prune_old_polls'		=> request_var('prune_old_polls', FALSE),
				'prune_announce'		=> request_var('prune_announce', FALSE),
				'prune_sticky'			=> request_var('prune_sticky', FALSE),
				'forum_password'		=> request_var('forum_password', ''),
				'forum_password_confirm'=> request_var('forum_password_confirm', '')
			);

			$errors = update_forum_data($forum_data);

			if ($errors)
			{
				break;
			}

			// Redirect to permissions
			$message = ($mode == 'add') ? $user->lang['FORUM_CREATED'] : $user->lang['FORUM_UPDATED'];
			$message .= '<br /><br />' . sprintf($user->lang['REDIRECT_ACL'], "<a href=\"admin_permissions.$phpEx$SID&amp;mode=forum&amp;submit_usergroups=true&amp;ug_type=forum&amp;action=usergroups&amp;f[forum][]=" . $forum_data['forum_id'] . '">', '</a>');

			trigger_error($message);
			break;
	}
}

switch ($mode)
{
	case 'add':
	case 'edit':
		if (isset($_POST['update']))
		{
			extract($forum_data);
		}
		else
		{
			$forum_id				= request_var('f', 0);
			$parent_id				= request_var('parent_id', 0);
			$style_id				= request_var('style_id', 0);
			$forum_type				= request_var('forum_type', FORUM_POST);
			$forum_status			= request_var('forum_status', ITEM_UNLOCKED);
			$forum_desc				= request_var('forum_desc', '');
			$forum_name				= request_var('forum_name', '');
			$forum_password			= request_var('forum_password', '');
			$forum_password_confirm	= request_var('forum_password_confirm', '');
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

			if ($parent_id)
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

		$styles_list = style_select($forum_style, true);

		$statuslist = '<option value="' . ITEM_UNLOCKED . '"' . (($forum_status == ITEM_UNLOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['UNLOCKED'] . '</option><option value="' . ITEM_LOCKED . '"' . (($forum_status == ITEM_LOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['LOCKED'] . '</option>';

		$indexing_yes = ($enable_indexing) ? ' checked="checked"' : '';
		$indexing_no = (!$enable_indexing) ? ' checked="checked"' : '';
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

		if (!empty($errors))
		{

?>
	<tr>
		<td class="row3" colspan="2" align="center"><span style="color:red"><?php echo implode('<br />', $errors); ?></span></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="row1" width="33%"><?php echo $user->lang['FORUM_TYPE'] ?>: </td>
		<td class="row2"><select name="forum_type" onchange="this.form.submit();"><?php echo $forum_type_options; ?></select><?php
	
		if ($old_forum_type == FORUM_POST && $forum_type != FORUM_POST)
		{
			// Forum type being changed to a non-postable type, let the user decide between
			// deleting all posts or moving them to another forum (if applicable)

?><br /><input type="radio" name="action" value="delete" checked="checked" /> <?php echo $user->lang['DELETE_ALL_POSTS'];

			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type = ' . FORUM_POST . "
					AND forum_id <> $forum_id";
			$result = $db->sql_query($sql);

			if ($db->sql_fetchrow($result))
			{
?>&nbsp;<input type="radio" name="action" value="move" /> <?php echo $user->lang['MOVE_POSTS_TO'] ?> <select name="to_forum_id"><?php echo $forums_list ?></select><?php

			}
		}

?></td>
	</tr>
<?php

		if ($forum_type == FORUM_POST)
		{

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_STATUS'] ?>: </b></td>
		<td class="row2"><select name="forum_status"><?php echo $statuslist ?></select></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="row1" width="40%"><b><?php echo $user->lang['FORUM_PARENT'] ?>: </b></td>
		<td class="row2"><select name="parent_id"><option value="0"><?php echo $user->lang['NO_PARENT'] ?></option><?php echo $parents_list ?></select></td>
	</tr>
<?php

		if ($forum_type == FORUM_LINK)
		{

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_LINK'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORUM_LINK_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" name="forum_link" value="<?php echo $forum_link; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_LINK_TRACK'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORUM_LINK_TRACK_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="forum_link_track" value="1"<?php echo $forum_link_track_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="forum_link_track" value="0"<?php echo $forum_link_track_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_NAME']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" size="25" name="forum_name" value="<?php echo $forum_name ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_DESC'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORUM_DESC_EXPLAIN']; ?></span> </td>
		<td class="row2"><textarea class="post" rows="5" cols="45" wrap="virtual" name="forum_desc"><?php echo htmlspecialchars(str_replace('<br />', "\n", $forum_desc)); ?></textarea></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_IMAGE']; ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORUM_IMAGE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" size="25" name="forum_image" value="<?php echo $forum_image ?>" /><br /><?php
	
		if ($forum_image != '')
		{
			echo '<img src="../' . $forum_image . '" alt="" />';
		}
		
?></td>
	</tr>
<?php

		if ($forum_type == FORUM_POST || $forum_type == FORUM_CAT)
		{

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_STYLE'] ?>: </b></td>
		<td class="row2"><select name="forum_style"><option value="0"><?php echo $user->lang['DEFAULT_STYLE'] ?></option><?php echo $styles_list ?></select></td>
	</tr>
<?php

		}

		if ($forum_type == FORUM_POST)
		{

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ENABLE_INDEXING'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['ENABLE_INDEXING_EXPLAIN'] ?></span></td>
		<td class="row2"><input type="radio" name="enable_indexing" value="1"<?php echo $indexing_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="enable_indexing" value="0"<?php echo $indexing_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['ENABLE_TOPIC_ICONS'] ?>: </b></td>
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
		<td class="row1"><b><?php echo $user->lang['LIST_INDEX'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['LIST_INDEX_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="display_on_index" value="1"<?php echo $display_index_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="display_on_index" value="0"<?php echo $display_index_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
<?php

					}
				}
			}

?>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_AUTO_PRUNE'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORUM_AUTO_PRUNE_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="enable_prune" value="1"<?php echo $prune_enable_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="enable_prune" value="0"<?php echo $prune_enable_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTO_PRUNE_FREQ'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['AUTO_PRUNE_FREQ_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="prune_freq" value="<?php echo $prune_freq ?>" size="5" /> <?php echo $user->lang['DAYS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTO_PRUNE_DAYS'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['AUTO_PRUNE_DAYS_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="prune_days" value="<?php echo $prune_days ?>" size="5" /> <?php echo $user->lang['DAYS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['AUTO_PRUNE_VIEWED'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['AUTO_PRUNE_VIEWED_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="prune_viewed" value="<?php echo $prune_viewed ?>" size="5" /> <?php echo $user->lang['DAYS']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['PRUNE_OLD_POLLS'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['PRUNE_OLD_POLLS_EXPLAIN']; ?></span></td>
		<td class="row2"><input type="radio" name="prune_old_polls" value="1"<?php echo $prune_old_polls_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_old_polls" value="0"<?php echo $prune_old_polls_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['PRUNE_ANNOUNCEMENTS'] ?>: </b></td>
		<td class="row2"><input type="radio" name="prune_announce" value="1"<?php echo $prune_announce_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_announce" value="0"<?php echo $prune_announce_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['PRUNE_STICKY'] ?>: </b></td>
		<td class="row2"><input type="radio" name="prune_sticky" value="1"<?php echo $prune_sticky_yes; ?> /> <?php echo $user->lang['YES']; ?> &nbsp; <input type="radio" name="prune_sticky" value="0"<?php echo $prune_sticky_no; ?> /> <?php echo $user->lang['NO']; ?></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_TOPICS_PAGE'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORUM_TOPICS_PAGE_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="text" name="topics_per_page" value="<?php echo $forum_topics_per_page; ?>" size="3" maxlength="3" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_PASSWORD'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORUM_PASSWORD_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="password" name="forum_password" value="<?php echo $forum_password; ?>" size="25" maxlength="25" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['FORUM_PASSWORD_CONFIRM'] ?>: </b><br /><span class="gensmall"><?php echo $user->lang['FORUM_PASSWORD_CONFIRM_EXPLAIN']; ?></span></td>
		<td class="row2"><input class="post" type="password" name="forum_password_confirm" value="<?php echo $forum_password_confirm; ?>" size="25" maxlength="25" /></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input class="btnmain" name="update" type="submit" value="<?php echo $user->lang['SUBMIT']; ?>" /> &nbsp;<input class="btnlite" type="reset" value="<?php echo $user->lang['RESET']; ?>" /></td>
	</tr>
</table></form>

<br clear="all" />

<?php

		adm_page_footer();
		break;

	case 'delete':

		$forum_id = request_var('f', 0);

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
<?php

			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_type = ' . FORUM_POST . "
					AND forum_id <> $forum_id";
			$result = $db->sql_query($sql);

			if ($db->sql_fetchrow($result))
			{

?>
			<tr>
				<td><input type="radio" name="action_posts" value="move" /> <?php echo $user->lang['MOVE_POSTS_TO'] ?> <select name="posts_to_id" ?><?php echo $move_posts_list ?></select></td>
			</tr>
<?php

			}

?>
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
		<td class="cat" colspan="2" align="center"><input type="submit" name="update" value="<?php echo $user->lang['SUBMIT'] ?>" class="btnmain" /></td>
	</tr>
</table></form>
<?php

		adm_page_footer();
		break;

	case 'move_up':
	case 'move_down':
		$sql = 'SELECT parent_id, left_id, right_id
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";
		$result = $db->sql_query($sql);

		if (!extract($db->sql_fetchrow($result)))
		{
			trigger_error($user->lang['NO_FORUM']);
		}
		$db->sql_freeresult($result);

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
			$log_action = 'LOG_FORUM_MOVE_UP';
			$up_id = $forum_id;
			$down_id = $row['forum_id'];
		}
		else
		{
			$log_action = 'LOG_FORUM_MOVE_DOWN';
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
		$db->sql_transaction('begin');

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

		add_log('admin', $log_action, $forum_data['forum_name'], $move_forum_name);
		unset($forum_data);
		break;

	case 'sync':
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

		sync('forum', 'forum_id', $forum_id);
		add_log('admin', 'LOG_FORUM_SYNC', $row['forum_name']);

		break;
}

// Default management page

if (!$parent_id)
{
	$navigation = $user->lang['FORUM_INDEX'];
}
else
{
	$navigation = '<a href="admin_forums.' . $phpEx . $SID . '">' . $user->lang['FORUM_INDEX'] . '</a>';

	$forums_nav = get_forum_branch($parent_id, 'parents', 'descending');
	foreach ($forums_nav as $row)
	{
		if ($row['forum_id'] == $parent_id)
		{
			$navigation .= ' -&gt; ' . $row['forum_name'];
		}
		else
		{
			$navigation .= ' -&gt; <a href="admin_forums.' . $phpEx . $SID . '&amp;parent_id=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>';
		}
	}
}

// Jumpbox
$forum_box = make_forum_select($parent_id);

// Front end
adm_page_header($user->lang['MANAGE']);

?>

<h1><?php echo $user->lang['MANAGE']; ?></h1>

<p><?php echo $user->lang['FORUM_ADMIN_EXPLAIN']; ?></p><?php

if ($mode == 'sync')
{
	echo '<br /><div class="gen" align="center"><b>' . $user->lang['FORUM_RESYNCED'] . '</b></div>';
}

?><form method="post" action="<?php echo "admin_forums.$phpEx$SID&amp;parent_id=$parent_id" ?>"><table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
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
	WHERE parent_id = $parent_id
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

	$forum_title = ($forum_type != FORUM_LINK) ? "<a href=\"admin_forums.$phpEx$SID&amp;parent_id=" . $row['forum_id'] . '">' : '';
	$forum_title .= $row['forum_name'];
	$forum_title .= ($forum_type != FORUM_LINK) ? '</a>' : '';
	$url = "$phpEx$SID&amp;parent_id=$parent_id&amp;f=" . $row['forum_id'];

?>
	<tr>
		<td class="row1" width="5%"><?php echo $folder_image; ?></td>
		<td class="row1" width="50%"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td><span class="forumlink"><?php echo $forum_title ?></span></td>
				</tr>
			</table>
			<table cellspacing="5" cellpadding="0" border="0">
				<tr>
					<td class="gensmall"><?php echo $row['forum_desc'] ?></td>
				</tr>
			</table>
<?php

	if ($forum_type == FORUM_POST)
	{

?>
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td class="gensmall">&nbsp;<?php echo $user->lang['TOPICS']; ?>: <b><?php echo $row['forum_topics'] ?></b> / <?php echo $user->lang['POSTS']; ?>: <b><?php echo $row['forum_posts'] ?></b></td>
				</tr>
			</table>
<?php

	}

?></td>
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
		<td width="100%" colspan="6" class="cat"><input type="hidden" name="mode" value="add" /><input class="post" type="text" name="forum_name" /> <input class="btnlite" type="submit" value="<?php echo $user->lang['CREATE_FORUM'] ?>" /></td>
	</tr>
</table></form>

<form method="get" action="admin_forums.<?php echo $phpEx,$SID ?>"><table width="100%" cellpadding="1" cellspacing="1" border="0">
	<tr>
		<td align="right"><?php echo $user->lang['SELECT_FORUM']; ?>: <select name="f" onchange="if(this.options[this.selectedIndex].value != -1){ this.form.submit(); }"><?php echo $forum_box; ?></select> <input class="btnlite" type="submit" value="<?php echo $user->lang['GO']; ?>" /><input type="hidden" name="sid" value="<?php echo $user->session_id; ?>" /></td>
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

	if (!($row = $db->sql_fetchrow($result)))
	{
		trigger_error("Forum #$forum_id does not exist", E_USER_ERROR);
	}

	return $row;
}

function update_forum_data(&$forum_data)
{
	global $db, $user;

	$errors = array();
	if (!trim($forum_data['forum_name']))
	{
		$errors[] = $user->lang['FORUM_NAME_EMPTY'];
	}

	if (!empty($_POST['forum_password']) || !empty($_POST['forum_password_confirm']))
	{
		if ($_POST['forum_password'] != $_POST['forum_password_confirm'])
		{
			$forum_data['forum_password'] = $forum_data['forum_password_confirm'] = '';
			$errors[] = $user->lang['FORUM_PASSWORD_MISMATCH'];
		}
	}

	if ($forum_data['prune_days'] < 0 || $forum_data['prune_viewed'] < 0 || $forum_data['prune_freq'] < 0)
	{
		$forum_data['prune_days'] = $forum_data['prune_viewed'] = $forum_data['prune_freq'] = 0;
		$errors[] = $user->lang['FORUM_DATA_NEGATIVE'];
	}

	// Set forum flags
	// 1 = link tracking
	// 2 = prune old polls
	// 4 = prune announcements
	// 8 = prune stickies
	$forum_data['forum_flags'] = 0;
	$forum_data['forum_flags'] += ($forum_data['forum_link_track']) ? 1 : 0;
	$forum_data['forum_flags'] += ($forum_data['prune_old_polls']) ? 2 : 0;
	$forum_data['forum_flags'] += ($forum_data['prune_announce']) ? 4 : 0;
	$forum_data['forum_flags'] += ($forum_data['prune_sticky']) ? 8 : 0;

	// Unset data that are not database fields
	unset($forum_data['forum_link_track']);
	unset($forum_data['prune_old_polls']);
	unset($forum_data['prune_announce']);
	unset($forum_data['prune_sticky']);
	unset($forum_data['forum_password_confirm']);

	// What are we going to do tonight Brain? The same thing we do everynight,
	// try to take over the world ... or decide whether to continue update
	// and if so, whether it's a new forum/cat/link or an existing one
	if (count($errors))
	{
		return $errors;
	}

	if (empty($forum_data['forum_id']))
	{
		// no forum_id means we're creating a new forum

		$db->sql_transaction('begin');

		if ($forum_data['parent_id'])
		{
			$sql = 'SELECT left_id, right_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $forum_data['parent_id'];
			$result = $db->sql_query($sql);

			if (!$row = $db->sql_fetchrow($result))
			{
				trigger_error('Parent does not exist', E_USER_ERROR);
			}
			$db->sql_freeresult($result);

			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET left_id = left_id + 2, right_id = right_id + 2
				WHERE left_id > ' . $row['right_id'];
			$db->sql_query($sql);

			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET right_id = right_id + 2
				WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
			$db->sql_query($sql);

			$forum_data['left_id'] = $row['right_id'];
			$forum_data['right_id'] = $row['right_id'] + 1;
		}
		else
		{
			$sql = 'SELECT MAX(right_id) AS right_id
				FROM ' . FORUMS_TABLE;
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$forum_data['left_id'] = $row['right_id'] + 1;
			$forum_data['right_id'] = $row['right_id'] + 2;
		}

		$sql = 'INSERT INTO ' . FORUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $forum_data);
		$db->sql_query($sql);
		
		$db->sql_transaction('commit');

		$forum_data['forum_id'] = $db->sql_nextid();
		add_log('admin', 'LOG_FORUM_ADD', $forum_data['forum_name']);
	}
	else
	{
		$row = get_forum_info($forum_data['forum_id']);

		if ($forum_data['forum_type'] != FORUM_POST && $row['forum_type'] != $forum_data['forum_type'])
		{
			// we're turning a postable forum into a non-postable forum

			if (empty($forum_data['action']))
			{
				// TODO: error message if no action is specified

				return array($user->lang['']);
			}
			elseif ($forum_data['action'] == 'move')
			{
				if (!empty($forum_data['to_forum_id']))
				{
					$errors = move_forum_content($forum_data['forum_id'], $forum_data['to_forum_id']);				
				}
				else
				{
					return array($user->lang['SELECT_DESTINATION_FORUM']);
				}
			}
			elseif ($forum_data['action'] == 'delete')
			{
				$errors = delete_forum_content($forum_data['forum_id']);
			}

			$forum_data['forum_posts'] = 0;
			$forum_data['forum_topics'] = 0;
			$forum_data['forum_topics_real'] = 0;
		}

		if ($row['parent_id'] != $forum_data['parent_id'])
		{
			$errors = move_forum($forum_data['forum_id'], $forum_data['parent_id']);
		}
		elseif ($row['forum_name'] != $forum_data['forum_name'])
		{
			// the forum name has changed, clear the parents list of child forums

			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET forum_parents = ''
				WHERE left_id > " . $row['left_id'] . '
					AND right_id < ' . $row['right_id'];
			$db->sql_query($sql);
		}

		if (count($errors))
		{
			return $errors;
		}

		$sql = 'UPDATE ' . FORUMS_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $forum_data) . '
			WHERE forum_id = ' . $forum_data['forum_id'];
		$db->sql_query($sql);

		add_log('admin', 'LOG_FORUM_EDIT', $forum_data['forum_name']);
	}
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

function move_forum_content($from_id, $to_id, $sync = TRUE)
{
	// TODO: empty tables like forum_tracks or forum_access

	global $db;

	$table_ary = array(LOG_TABLE, POSTS_TABLE, TOPICS_TABLE);
	foreach ($sql_ary as $table)
	{
		$sql = "UPDATE $table
			SET forum_id = $to_id
			WHERE forum_id = $from_id";
		$db->sql_query($sql);
	}
	unset($table_ary);

	if ($sync)
	{
		// Delete ghost topics that link back to the same forum
		// then resync counters

		sync('topic_moved');
		sync('forum', 'forum_id', $to_id);
	}
}

function delete_forum($forum_id, $action_posts = 'delete', $action_subforums = 'delete', $posts_to_id = 0, $subforums_to_id = 0)
{
	global $db, $user;

	$row = get_forum_info($forum_id);
	extract($row);

	$errors = array();
	$log_action_posts = $log_action_forums = '';

	if ($action_posts == 'delete')
	{
		$log_action_posts = 'POSTS';
		$errors += delete_forum_content($forum_id);
	}
	elseif ($action_posts == 'move')
	{
		if (!$posts_to_id)
		{
			$errors[] = $user->lang['NO_DESTINATION_FORUM'];
		}
		else
		{
			$log_action_posts = 'MOVE_POSTS';

			$sql = 'SELECT forum_name 
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $posts_to_id;
			$result = $db->sql_query($sql);

			if (!$row = $db->sql_fetchrow($result))
			{
				$errors[] = $user->lang['NO_FORUM'];
			}
			else
			{
				$posts_to_name = $row['forum_name'];
				unset($row);

				$errors += move_forum_content($forum_id, $subforums_to_id);
			}
		}
	}

	if (count($errors))
	{
		return $errors;
	}

	if ($action_subforums == 'delete')
	{
		$log_action_forums = 'FORUMS';

		$forum_ids = array($forum_id);
		$rows = get_forum_branch($forum_id, 'children', 'descending', FALSE);

		foreach ($rows as $row)
		{
			$forum_ids[] = $row['forum_id'];
			$errors += delete_forum_content($row['forum_id']);
		}

		if (count($errors))
		{
			return $errors;
		}

		$diff = count($forum_ids) * 2;

		$sql = 'DELETE FROM ' . FORUMS_TABLE . '
			WHERE forum_id IN (' . implode(', ', $forum_ids) . ')';
		$db->sql_query($sql);
	}
	elseif ($action_subforums == 'move')
	{
		if (!$subforums_to_id)
		{
			$errors[] = $user->lang['NO_DESTINATION_FORUM'];
		}
		else
		{
			$log_action_forums = 'MOVE_FORUMS';

			$sql = 'SELECT forum_name 
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . $subforums_to_id;
			$result = $db->sql_query($sql);

			if (!$row = $db->sql_fetchrow($result))
			{
				$errors[] = $user->lang['NO_FORUM'];
			}
			else
			{
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

				$sql = 'UPDATE ' . FORUMS_TABLE . "
					SET parent_id = $subforums_to_id
					WHERE parent_id = $forum_id";
				$db->sql_query($sql);

				$diff = 2;
				$sql = 'DELETE FROM ' . FORUMS_TABLE . "
					WHERE forum_id = $forum_id";
				$db->sql_query($sql);
			}
		}

		if (count($errors))
		{
			return $errors;
		}
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
			add_log('admin', 'LOG_FORUM_DEL_POSTS_MOVE_FORUMS', $subforums_to_name, $forum_name);
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

	return $errors;
}

function delete_forum_content($forum_id)
{
	global $db, $config, $phpbb_root_path, $phpEx;
	include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

	$db->sql_transaction('begin');

	switch (SQL_LAYER)
	{
		case 'mysql4':
			// Use delete_attachments('topic', $ids, false) here...
		
			// Select then delete all attachments
			$sql = 'SELECT d.physical_filename, d.thumbnail
				FROM ' . POSTS_TABLE . ' p, ' . ATTACHMENTS_DESC_TABLE . ' d, ' . ATTACHMENTS_TABLE . " a
				WHERE p.forum_id = $forum_id
					AND a.post_id = p.post_id
					AND d.attach_id = a.attach_id";
			$result = $db->sql_query($sql);	
		
			while ($row = $db->sql_fetchrow($result))
			{
				phpbb_unlink($row['physical_filename'], 'file');
				if ($row['thumbnail'])
				{
					phpbb_unlink($row['physical_filename'], 'thumbnail');
				}
			}
			$db->sql_freeresult($result);

			// Delete everything else and thank MySQL for offering multi-table deletion
			$tables_ary = array(
				SEARCH_MATCH_TABLE	=>	'wm.post_id',
				RATINGS_TABLE				=>	'ra.post_id',
				REPORTS_TABLE				=>	're.post_id',
				ATTACHMENTS_TABLE		=>	'a.post_id',
				TOPICS_WATCH_TABLE		=>	'tw.topic_id',
				TOPICS_TRACK_TABLE		=>	'tt.topic_id',
				POLL_OPTIONS_TABLE		=>	'po.post_id',
				POLL_VOTES_TABLE			=>	'pv.post_id'
			);

			$sql = 'DELETE QUICK FROM ' . POSTS_TABLE . ', ' . ATTACHMENTS_DESC_TABLE;
			$sql_using = "\nUSING " . POSTS_TABLE . ' p, ' . ATTACHMENTS_DESC_TABLE . ' d';
			$sql_where = "\nWHERE p.forum_id = $forum_id\nAND d.attach_id = a.attach_id";
			$sql_optimise = 'OPTIMIZE TABLE . ' . POSTS_TABLE . ', ' . ATTACHMENTS_DESC_TABLE;

			foreach ($tables_ary as $table => $field)
			{
				$sql .= ", $table";
				$sql_using .= ", $table " . strtok($field, '.');
				$sql_where .= "\nAND $field = p." . strtok('');
				$sql_optimise .= ', ' . $table;
			}

			$db->sql_query($sql . $sql_using . $sql_where);

			$tables_ary = array('phpbb_forum_access', TOPICS_TABLE, FORUMS_TRACK_TABLE, FORUMS_WATCH_TABLE, ACL_GROUPS_TABLE, ACL_USERS_TABLE, MODERATOR_TABLE, LOG_TABLE);
			foreach ($tables_ary as $table)
			{
				$db->sql_query("DELETE QUICK FROM $table WHERE forum_id = $forum_id");
				$sql_optimise .= ', ' . $table;
			}

			// Now optimise a hell lot of tables
			$db->sql_query($sql_optimise);
		break;

		default:
			// Select then delete all attachments
			$sql = 'SELECT d.attach_id, d.physical_filename, d.thumbnail
				FROM ' . POSTS_TABLE . ' p, ' . ATTACHMENTS_TABLE . ' a, ' . ATTACHMENTS_DESC_TABLE . " d
				WHERE p.forum_id = $forum_id
					AND a.post_id = p.post_id
					AND d.attach_id = a.attach_id";
			$result = $db->sql_query($sql);	

			$attach_ids = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$attach_ids[] = $row['attach_id'];

				phpbb_unlink($row['physical_filename'], 'file');
				if ($row['thumbnail'])
				{
					phpbb_unlink($row['physical_filename'], 'thumbnail');
				}
			}
			$db->sql_freeresult($result);

			if (count($attach_ids))
			{
				$attach_id_list = implode(',', array_unique($attach_ids));

				$db->sql_query('DELETE FROM ' . ATTACHMENTS_TABLE . " WHERE attach_id IN ($attach_id_list)");
				$db->sql_query('DELETE FROM ' . ATTACHMENTS_DESC_TABLE . " WHERE attach_id IN ($attach_id_list)");

				unset($attach_ids, $attach_id_list);
			}

			// Delete everything else and curse your DB for not offering multi-table deletion
			$tables_ary = array(
				'post_id'	=>	array(
					SEARCH_MATCH_TABLE,
					RATINGS_TABLE,
					REPORTS_TABLE,
					POLL_OPTIONS_TABLE,
					POLL_VOTES_TABLE
				),
				
				'topic_id'	=>	array(
					TOPICS_WATCH_TABLE,
					TOPICS_TRACK_TABLE
				)
			);

			foreach ($tables_ary as $field => $tables)
			{
				$start = 0;
				do
				{
					$sql = "SELECT $field
						FROM " . POSTS_TABLE . '
						WHERE forum_id = ' . $forum_id;
					$result = $db->sql_query_limit($sql, 500, $start);

					$ids = array();
					while ($row = $db->sql_fetchrow($result))
					{
						$ids[] = $row[$field];
					}
					$db->sql_freeresult($result);

					if (count($ids))
					{
						$start += count($ids);
						$id_list = implode(',', $ids);

						foreach ($tables as $table)
						{
							$db->sql_query("DELETE FROM $table WHERE $field IN ($id_list)");
						}
					}
				}
				while ($row);
			}
			unset($ids, $id_list);

			$table_ary = array('phpbb_forum_access', POSTS_TABLE, TOPICS_TABLE, FORUMS_TRACK_TABLE, FORUMS_WATCH_TABLE, ACL_GROUPS_TABLE, ACL_USERS_TABLE, MODERATOR_TABLE, LOG_TABLE);
			foreach ($table_ary as $table)
			{
				$db->sql_query("DELETE FROM $table WHERE forum_id = $forum_id");
			}

			// NOTE: ideally these queries should be stalled until the page is displayed
			switch (SQL_LAYER)
			{
				case 'mysql':
					$sql = 'OPTIMIZE TABLE ' . POSTS_TABLE . ', ' . ATTACHMENTS_TABLE . ', ' . ATTACHMENTS_DESC_TABLE . ', ' . implode(', ', $tables_ary['post_id']) . ', ' . implode(', ', $tables_ary['topic_id']) . ', ' . implode(', ', $table_ary);

					$db->sql_query($sql);
				break;
			
				case 'postgres':
					$db->sql_query('VACUUM');
			}
	}

	$db->sql_transaction('commit');
}

//
// End function block
// ------------------

?>