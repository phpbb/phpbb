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
	if (!$auth->acl_get('a_forum'))
	{
		return;
	}

	$file = basename(__FILE__);
	$module['Forums']['Manage'] = $file . $SID;
	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);
include($phpbb_root_path . 'includes/functions_admin.'.$phpEx);

//
// Do we have forum admin permissions?
//
if (!$auth->acl_get('a_forum'))
{
	message_die(MESSAGE, $user->lang['No_admin']);
}

//
// Mode setting
//
if (isset($_POST['mode']) || isset($_GET['mode']))
{
	$mode = (!empty($_POST['mode'])) ? $_POST['mode'] : $_GET['mode'];
}
else
{
	$mode = '';
}

switch ($mode)
{
	case 'move_up':
	case 'move_down':
		$show_index = TRUE;
		$forum_id = intval($_GET['f']);

		$result = $db->sql_query('SELECT parent_id, left_id, right_id FROM ' . FORUMS_TABLE . " WHERE forum_id = $forum_id");
		if (!$row = $db->sql_fetchrow($result))
		{
			message_die(ERROR, 'Forum does not exist');
		}
		extract($row);
		$forum_info = array($forum_id => $row);

		//
		// Get the adjacent forum
		//
		if ($mode == 'move_up')
		{
			$sql = 'SELECT forum_id, left_id, right_id
					FROM ' . FORUMS_TABLE . "
					WHERE parent_id = $parent_id AND right_id < $right_id
					ORDER BY right_id DESC";
		}
		else
		{
			$sql = 'SELECT forum_id, left_id, right_id
					FROM ' . FORUMS_TABLE . "
					WHERE parent_id = $parent_id AND left_id > $left_id
					ORDER BY left_id ASC";
		}
		$result = $db->sql_query_limit($sql, 1);

		if (!$row = $db->sql_fetchrow($result))
		{
			//
			// already on top or at bottom
			//
			break;
		}

		if ($mode == 'move_up')
		{
			$up_id = $forum_id;
			$down_id = $row['forum_id'];
		}
		else
		{
			$up_id = $row['forum_id'];
			$down_id = $forum_id;
		}

		$forum_info[$row['forum_id']] = $row;
		$diff_up = $forum_info[$up_id]['right_id'] - $forum_info[$up_id]['left_id'];
		$diff_down = $forum_info[$down_id]['right_id'] - $forum_info[$down_id]['left_id'];

		//
		// I should consider using transactions here
		//
		$forum_ids = array();
		$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE left_id > ' . $forum_info[$up_id]['left_id'] . ' AND right_id < ' . $forum_info[$up_id]['right_id'];

		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$forum_ids[] = $row['forum_id'];
		}

		$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET left_id = left_id + ' . ($diff_up + 1) . ', right_id = right_id + ' . ($diff_up + 1) . '
				WHERE left_id > ' . $forum_info[$down_id]['left_id'] . ' AND right_id < ' . $forum_info[$down_id]['right_id'];
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
	break;

	case 'create':
		if (!trim($_POST['forum_name']))
		{
			message_die(ERROR, 'Cannot create a forum without a name');
		}

		$parent_id = (!empty($_POST['parent_id'])) ? $_POST['parent_id'] : 0;
		$forum_status = (!empty($_POST['is_category'])) ? ITEM_CATEGORY : $_POST['forum_status'];
		$forum_name = str_replace("\'", "''", $_POST['forum_name']);
		$forum_desc = str_replace("\'", "''", $_POST['forum_desc']);

		$forum_style = (!empty($_POST['forum_style'])) ? intval($_POST['forum_style']) : 'NULL';
		$post_count_inc = (!empty($_POST['disable_post_count'])) ? 0 : 1;
		$moderated = (!empty($_POST['moderated'])) ? 1 : 0;

		$prune_enable = (!empty($_POST['prune_enable'])) ? 1 : 0;
		$prune_days = intval($_POST['prune_days']);
		$prune_freq = intval($_POST['prune_freq']);

		$result = $db->sql_query('SELECT MAX(forum_id) AS max_id FROM ' . FORUMS_TABLE);
		$forum_id = $db->sql_fetchfield('max_id', 0, $result) + 1;

		if ($parent_id)
		{
			$result = $db->sql_query('SELECT left_id, right_id FROM ' . FORUMS_TABLE . " WHERE forum_id = $parent_id");
			if (!$row = $db->sql_fetchrow($result))
			{
				message_die(ERROR, 'Parent does not exist');
			}
			extract($row);

			$db->sql_query('UPDATE ' . FORUMS_TABLE . " SET left_id = left_id + 2, right_id = right_id + 2 WHERE left_id > $right_id");
			$db->sql_query('UPDATE ' . FORUMS_TABLE . " SET right_id = right_id + 2 WHERE $left_id BETWEEN left_id AND right_id");

			$left_id = $right_id;
			++$right_id;
		}
		else
		{
			$result = $db->sql_query('SELECT MAX(right_id) AS right_id FROM ' . FORUMS_TABLE);
			$left_id = $db->sql_fetchfield('right_id', 0, $result) + 1;
			$right_id = $left_id + 1;
		}

		$sql = 'INSERT INTO ' . FORUMS_TABLE . " (forum_id, forum_name, forum_desc, parent_id, left_id, right_id, forum_status, forum_style, post_count_inc, moderated, prune_enable, prune_days, prune_freq)
				VALUES ($forum_id, '$forum_name', '$forum_desc', $parent_id, $left_id, $right_id, $forum_status, $forum_style, $post_count_inc, $moderated, $prune_enable, $prune_days, $prune_freq)";
		$db->sql_query($sql);

		$message = $user->lang['Forums_updated'] . "<br /><br />" . sprintf($user->lang['Click_return_forumadmin'], '<a href="admin_forums.' . $phpEx . $SID . '&parent_id=' . $parent_id . '">', '</a>') . '<br /><br />' . sprintf($user->lang['Click_return_admin_index'], '<a href="index.' . $phpEx . $SID . '?pane=right' . '">', '</a>');
		message_die(MESSAGE, $message);

	break;

	case 'modify':
		if (!$forum_id = intval($_POST['forum_id']))
		{
			message_die(ERROR, 'No forum specified');
		}

		$row = get_forum_info($forum_id);
		$parent_id = intval($_POST['parent_id']);
		$action = (!empty($_POST['action'])) ? $_POST['action'] : '';

		if (($row['parent_id'] != $parent_id) && ($parent_id != -1))
		{
			move_forum($forum_id, $parent_id);
		}
		elseif ($row['forum_name'] != $_POST['forum_name'])
		{
			$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET forum_parents = ""
					WHERE left_id > ' . $row['left_id'] . ' AND right_id < ' . $row['right_id'];
			$db->sql_query($sql);
		}

		$sql = array(
			'forum_name'		=>	(!empty($_POST['forum_name'])) ? $_POST['forum_name'] : $row['forum_name'],
			'forum_desc'		=>	(!empty($_POST['forum_desc'])) ? $_POST['forum_desc'] : $row['forum_desc'],
			'forum_status'		=>	(!empty($_POST['set_category']) && $action) ? ITEM_CATEGORY : intval($_POST['forum_status']),
			'forum_style'		=>	(!empty($_POST['forum_style'])) ? $_POST['forum_style'] : NULL,
			'parent_id'			=>	$parent_id,
			'prune_enable'		=>	(!empty($_POST['prune_enable'])) ? 1 : 0,
			'prune_days'		=>	intval($_POST['prune_days']),
			'prune_freq'		=>	intval($_POST['prune_freq']),
			'display_on_index'	=>	(!empty($_POST['display_on_index'])) ? 1 : 0,
			'post_count_inc'	=>	(!empty($_POST['disable_post_count'])) ? 0 : 1,
			'moderated'			=>	(!empty($_POST['moderated'])) ? 1 : 0,
		);

		if (!empty($_POST['set_category']) && $action)
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
		}

		$db->sql_query('UPDATE ' . FORUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql) . " WHERE forum_id = $forum_id");

		$message = $user->lang['Forums_updated'] . "<br /><br />" . sprintf($user->lang['Click_return_forumadmin'], '<a href="admin_forums.' . $phpEx . $SID . '&parent_id=' . $parent_id . '">', '</a>') . '<br /><br />' . sprintf($user->lang['Click_return_admin_index'], '<a href="index.' . $phpEx . $SID . '?pane=right' . '">', '</a>');
		message_die(MESSAGE, $message);

	break;

	case 'remove':
		if (empty($_POST['submit']))
		{
			//
			// wasn't this form submitted? is anyone trying to remotely delete forums
			//
			message_die(ERROR, 'Did not submit');
		}

		$action_subforums = (!empty($_POST['action_subforums'])) ? $_POST['action_subforums'] : '';
		$action_posts = (!empty($_POST['action_posts'])) ? $_POST['action_posts'] : '';

		$row = get_forum_info($_GET['f']);
		extract($row);

		if ($action_posts == 'delete')
		{
			delete_forum_content($forum_id);
		}
		elseif ($action_posts == 'move')
		{
			if (empty($_POST['posts_to_id']))
			{
				$message = $user->lang['No_destination_forum'] . '<br /><br />' . sprintf($user->lang['Click_return_forumadmin'], '<a href="admin_forums.' . $phpEx . $SID . '&mode=delete&f=' . $forum_id. '">', '</a>');

				message_die(ERROR, $message);
			}

			move_forum_content($forum_id, $_POST['posts_to_id']);
		}

		if ($action_subforums == 'delete')
		{
			$forum_ids = array($forum_id);
			$rows = get_forum_branch($forum_id, 'children', 'descending', FALSE);
			foreach ($rows as $row)
			{
				$forum_ids[] = $row['forum_id'];
				delete_forum_content($row['forum_id']);
			}

			$diff = count($forum_ids) * 2;
			$db->sql_query('DELETE FROM ' . FORUMS_TABLE . ' WHERE forum_id IN (' . implode(', ', $forum_ids) . ')');
		}
		elseif ($action_subforums == 'move')
		{
			if (empty($_POST['subforums_to_id']))
			{
				$message = $user->lang['No_destination_forum'] . '<br /><br />' . sprintf($user->lang['Click_return_forumadmin'], '<a href="admin_forums.' . $phpEx . $SID . '&mode=delete&f=' . $forum_id. '">', '</a>');

				message_die(ERROR, $message);
			}

			$result = $db->sql_query('SELECT forum_id FROM ' . FORUMS_TABLE . " WHERE parent_id = $forum_id");
			while ($row = $db->sql_fetchrow($result))
			{
				move_forum($row['forum_id'], $_POST['subforums_to_id']);
			}
			$db->sql_query('UPDATE ' . FORUMS_TABLE . ' SET parent_id = ' . $_POST['subforums_to_id'] . " WHERE parent_id = $forum_id");

			$diff = 2;
			$db->sql_query('DELETE FROM ' . FORUMS_TABLE . " WHERE forum_id = $forum_id");
		}
		else
		{
			$diff = 2;
			$db->sql_query('DELETE FROM ' . FORUMS_TABLE . " WHERE forum_id = $forum_id");
		}

		//
		// Resync tree
		//
		$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET right_id = right_id - $diff
				WHERE left_id < $right_id AND right_id > $right_id";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET left_id = left_id - $diff, right_id = right_id - $diff
				WHERE left_id > $right_id";
		$db->sql_query($sql);

		$return_id = (!empty($_POST['subforums_to_id'])) ? $_POST['subforums_to_id'] : $parent_id;
		$message = $user->lang['Forum_deleted'] . '<br /><br />' . sprintf($user->lang['Click_return_forumadmin'], '<a href="admin_forums.' . $phpEx . $SID . '&parent_id=' . $return_id. '">', '</a>');

		message_die(MESSAGE, $message);
	break;

	case 'forum_sync':
		sync('forum', intval($_GET[POST_FORUM_URL]));
		$show_index = TRUE;
	break;

	case 'add':
	case 'edit':
		//
		// Show form to create/modify a forum
		//
		if ($mode == 'edit')
		{
			$forum_id = intval($_GET['f']);

			$row = get_forum_info($forum_id);
			extract($row);

			$subforums_id = array();
			$subforums = get_forum_branch($forum_id, 'children');
			foreach ($subforums as $row)
			{
				$subforums_id[] = $row['forum_id'];
			}

			$parents_list = make_forums_list('all', $parent_id, $subforums_id);

			$l_title = ($forum_status != ITEM_CATEGORY) ? $user->lang['Edit_forum'] : $user->lang['Edit_category'];
			$newmode = 'modify';
			$buttonvalue = $user->lang['Update'];
			$prune_enabled = ($prune_enable) ? 'checked="checked" ' : '';

			if ($forum_status != ITEM_CATEGORY)
			{
				$forums_list = make_forums_list('forums', 0, $forum_id);
			}
		}
		else
		{
			$parent_id = 0;
			if (!empty($_POST['parent_id']))
			{
				list($parent_id) = each($_POST['parent_id']);
			}
			$parents_list = make_forums_list('all', $parent_id);

			$l_title = $user->lang['Create_forum'];
			$newmode = 'create';
			$buttonvalue = $user->lang['Create_forum'];

			$forum_desc = '';
			$forum_style = '';
			$forum_status = ITEM_UNLOCKED;
			$forum_name = (!empty($_POST['forum_name'][$parent_id])) ? htmlspecialchars($_POST['forum_name'][$parent_id]) : '';

			$post_count_inc = TRUE;
			$moderated = FALSE;

			$prune_enabled = '';
			$prune_days = 7;
			$prune_freq = 1;
		}

		$styles_list = make_styles_list($forum_style);

		$forumlocked = ($forum_status == ITEM_LOCKED) ? ' selected="selected"' : '';
		$forumunlocked = ($forum_status == ITEM_UNLOCKED || $forum_status == ITEM_CATEGORY) ? ' selected="selected"' : '';

		$forum_checked = ($forum_status != ITEM_CATEGORY) ? 'checked="checked" ' : '';
		$category_checked = ($forum_status == ITEM_CATEGORY) ? 'checked="checked" ' : '';

		$statuslist = '<option value="' . ITEM_UNLOCKED . '"' . $forumunlocked . '>' . $user->lang['Unlocked'] . "</option>\n";
		$statuslist .= '<option value="' . ITEM_LOCKED . '"' . $forumlocked . '>' . $user->lang['Locked'] . "</option>\n";

		page_header($l_title);

?>
<h1><?php echo $l_title ?></h1>

<p><?php echo $user->lang['Forum_edit_delete_explain'] ?></p>

<form action="<?php echo "admin_forums.$phpEx$SID" ?>" method="post"><table class="bg" width="100%" cellpadding="4" cellspacing="1" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['General_settings'] ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo ($forum_status != ITEM_CATEGORY) ? $user->lang['Forum_name'] : $user->lang['Category_name'] ?></td>
		<td class="row2"><input type="text" size="25" name="forum_name" value="<?php echo $forum_name ?>" class="post" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Forum_desc'] ?></td>
		<td class="row2"><textarea rows="5" cols="45" wrap="virtual" name="forum_desc" class="post"><?php echo $forum_desc ?></textarea></td>
</tr>
<?php

	if ($mode == 'add' || $forum_status == ITEM_CATEGORY)
	{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['Forum_type'] ?></td>
		<td class="row2"><input type="radio" name="is_category" value="0" <?php echo $forum_checked ?>/><?php echo $user->lang['Forum'] ?> &nbsp; <input type="radio" name="is_category" value="1" <?php echo $category_checked ?>/><?php echo $user->lang['Category'] ?></td>
	</tr>
<?php

	}

?>
	<tr>
		<td class="row1"><?php echo $user->lang['Parent'] ?></td>
		<td class="row2"><select name="parent_id"><option value="0"><?php echo $user->lang['No_parent'] ?></option><?php echo $parents_list ?></select></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Style'] ?></td>
		<td class="row2"><select name="forum_style"><option value="0"><?php echo $user->lang['Default_style'] ?></option><?php echo $styles_list ?></select></td>
	</tr>
<?php

	if ($forum_status != ITEM_CATEGORY)
	{

?>
	<tr>
		<th colspan="2"><?php echo $user->lang['Forum_settings'] ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Forum_status'] ?></td>
		<td class="row2"><select name="forum_status"><?php echo $statuslist ?></select></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Options'] ?></td>
		<td class="row2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
		  	<tr>
  				<td><input type="checkbox" name="disable_post_count"<?php echo ((!empty($post_count_inc)) ? ' ' : 'checked="checked" ') ?>/> <?php echo $user->lang['Disable_post_count'] ?></td>
		  	</tr>
		  	<tr>
  				<td><input type="checkbox" name="moderated"<?php echo ((!empty($moderated)) ? 'checked="checked" ' : ' ') ?>/> <?php echo $user->lang['Forum_is_moderated']; ?></td>
		  	</tr>
<?php

		if ($mode == 'edit' && $parent_id > 0)
		{
			// if this forum is a subforum put the "display on index" checkbox
			if ($parent_info = get_forum_info($parent_id))
			{
				if ($parent_info['parent_id'] > 0 || $parent_info['forum_status'] != ITEM_CATEGORY)
				{

?>
			<tr>
				<td><input type="checkbox" name="display_on_index"<?php echo ((!empty($display_on_index)) ? 'checked="checked" ' : ' ') ?>/> <?php echo $user->lang['Display_on_index'] ?></td>
			</tr>
<?php

				}
			}
		}
?>
		</td></table>
	</tr>
<?php

		if ($mode == 'edit')
		{

?>
	<tr>
		<td class="row1"><?php echo $user->lang['Forum_type'] ?></td>
		<td class="row2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
		  	<tr>
  				<td><input type="checkbox" name="set_category" /> <?php echo $user->lang['Set_as_category'] ?></td>
  			</tr>
  			<tr>
  				<td>&nbsp; &nbsp; &nbsp;<input type="radio" name="action" value="delete" checked="checked" /> <?php echo $user->lang['Delete_all_posts'] ?></td>
  			</tr>
  			<tr>
  				<td>&nbsp; &nbsp; &nbsp;<input type="radio" name="action" value="move" /> <?php echo $user->lang['Move_posts_to'] ?> <select name="to_forum_id"><?php echo $forums_list ?></select></td>
  			</tr>
  		</table></td>
	</tr>
<?php

		}

?>
	<tr>
		<td class="row1"><?php echo $user->lang['Forum_pruning'] ?></td>
		<td class="row2"><table cellspacing="0" cellpadding="1" border="0">
			<tr>
				<td align="right" valign="middle"><?php echo $user->lang['Enabled'] ?></td>
				<td align="left" valign="middle"><input type="checkbox" name="prune_enable" value="1" <?php echo $prune_enabled ?>/></td>
			</tr>
			<tr>
				<td align="right" valign="middle"><?php echo $user->lang['prune_days'] ?></td>
				<td align="left" valign="middle">&nbsp;<input class="post" type="text" name="prune_days" value="<?php echo $prune_days ?>" size="5" />&nbsp;<?php echo $user->lang['Days'] ?></td>
			</tr>
			<tr>
				<td align="right" valign="middle"><?php echo $user->lang['prune_freq'] ?></td>
				<td align="left" valign="middle">&nbsp;<input class="post" type="text" name="prune_freq" value="<?php echo $prune_freq ?>" size="5" />&nbsp;<?php echo $user->lang['Days'] ?></td>
			</tr>
		</table></td>
	</tr>
<?php

	}

?>
	<tr>
		<td class="cat" colspan="2" align="center"><input type="hidden" name="mode" value="<?php echo $newmode ?>" /><input type="hidden" name="forum_id" value="<?php echo $forum_id ?>" /><input class="mainoption" type="submit" name="submit" value="<?php echo $buttonvalue ?>" /></td>
	</tr>
</table></form>

<br clear="all" />
<?php

	break;

	case 'delete':
		page_header($user->lang['Forum_delete']);
		extract(get_forum_info($_GET['f']));

		$subforums_id = array();
		$subforums = get_forum_branch($forum_id, 'children');
		foreach ($subforums as $row)
		{
			$subforums_id[] = $row['forum_id'];
		}

		$forums_list = make_forums_list('all', $parent_id, $subforums_id);
		$move_posts_list = make_forums_list('forums', $parent_id, $subforums_id);

?>
<h1><?php echo $user->lang['Forum_delete'] ?></h1>

<p><?php echo $user->lang['Forum_delete_explain'] ?></p>

<form action="admin_forums.<?php echo $phpEx . $SID ?>&mode=remove&f=<?php echo $forum_id ?>" method="post">
  <table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
	<tr>
	  <th colspan="2" class="thHead"><?php echo $user->lang['Forum_delete'] ?></th>
	  </tr>
	<tr>
	  <td class="row1"><?php echo ($forum_status == ITEM_CATEGORY) ? $user->lang['Category_name'] : $user->lang['Forum_name'] ?></td>
	  <td class="row1"><span class="row1"><?php echo $forum_name ?></span></td>
	</tr>
<?php
	if ($forum_status != ITEM_CATEGORY)
	{
?>
	<tr>
	  <td class="row1"><?php echo $user->lang['Action'] ?></td>
	  <td class="row1"><input type="radio" name="action_posts" value="delete" checked="checked" /> <?php echo $user->lang['Delete_all_posts'] ?></td>
	</tr>
	<tr>
	  <td class="row1"></td>
	  <td class="row1"><input type="radio" name="action_posts" value="move" /> <?php echo $user->lang['Move_posts_to'] ?> <select name="posts_to_id" ?><option value="0"></option><?php echo $move_posts_list ?></select></td>
	</tr>
<?php
	}
	if ($right_id - $left_id > 1)
	{
?>
	<tr>
	  <td class="row1"><?php echo $user->lang['Action'] ?></td>
	  <td class="row1"><input type="radio" name="action_subforums" value="delete" checked="checked" /> <?php echo $user->lang['Delete_subforums'] ?></td>
	</tr>
	<tr>
	  <td class="row1"></td>
	  <td class="row1"><input type="radio" name="action_subforums" value="move" /> <?php echo $user->lang['Move_subforums_to'] ?> <select name="subforums_to_id" ?><option value="0"></option><?php echo $forums_list ?></select></td>
	</tr>
<?php
	}
?>
	<tr>
	  <td class="cat" colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $user->lang['Move_and_Delete'] ?>" class="mainoption" /></td>
	</tr>
  </table>
</form>
<?php

	break;

	default:
		$show_index = TRUE;
}

if (empty($show_index))
{
	page_footer();
}

page_header($user->lang['Manage']);

$parent_id = (!empty($_GET['parent_id'])) ? $_GET['parent_id'] : 0;

if (!$parent_id)
{
	$navigation = 'Forum Index';
}
else
{
	$navigation = '<a href="admin_forums.' . $phpEx . $SID . '">Forum Index</a>';

	$forums_nav = get_forum_branch($parent_id, 'parents', 'descending');
	foreach ($forums_nav as $row)
	{
		if ($row['forum_id'] == $parent_id)
		{
			$navigation .= ' -&gt; ' . htmlspecialchars($row['forum_name']);
		}
		else
		{
			$navigation .= ' -&gt; <a href="admin_forums.' . $phpEx . $SID . '&parent_id=' . $row['forum_id'] . '">' . htmlspecialchars($row['forum_name']) . '</a>';
		}
	}
}
?>
<h1>Manage</h1>

<p>Here you can add, edit, delete, lock, unlock individual forums as well as set certain additional controls. If your posts and topics have got out of sync you can also resynchronise a forum. In phpBB 2.2 there are no categories, everything is forum based. Each forum can have an unlimited number of sub-forums and you can determine whether each may be posted to or not (i.e. whether it acts like an old category).</p>

<?php echo $navigation ?><br />
<form method="post" action="<?php echo "admin_forums.$phpEx$SID&mode=add" ?>"><table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
	<tr>
		<th class="thHead" colspan="9"><?php echo $user->lang['Forum_admin'] ?></th>
	</tr>
<?php

$result = $db->sql_query('SELECT * FROM ' . FORUMS_TABLE . " WHERE parent_id = $parent_id ORDER BY left_id");
while ($row = $db->sql_fetchrow($result))
{
	//
	// DEBUG
	//
	$forum_title = htmlspecialchars($row['forum_name']);
	$forum_desc = htmlspecialchars($row['forum_desc']);
	$url = $phpEx . $SID . '&f=' . $row['forum_id'];
	$subforums = ($row['right_id'] - $row['left_id'] - 1) / 2;

	if ($row['forum_status'] != ITEM_CATEGORY)
	{
		$colspan = ' colspan="2"';
		$forum_stats = '
			<td class="row1" align="center" valign="middle"><span class="gen">' . $row['forum_topics'] . '</span></td>
			<td class="row2" align="center" valign="middle"><span class="gen">' . $row['forum_posts'] . '</span></td>
			<td class="row3" align="center" valign="middle"><span class="gen">' . $subforums . '</span></td>
		';
	}
	else
	{
		$colspan = ' colspan="4"';
		$forum_stats = '
			<td class="row3" align="center" valign="middle"><span class="gen">' . $subforums . '</span></td>
		';
	}

	if ($row['left_id'] + 1 != $row['right_id'])
	{
		$forum_title = '<a href="admin_forums.' . $phpEx . $SID . '&parent_id=' . $row['forum_id'] . '">' . $forum_title . '</a>';
	}

	if ($row['forum_status'] == ITEM_CATEGORY)
	{
?>
	<tr>
		<td class="cat" colspan="5"><span class="cattitle"><b><?php echo $forum_title ?></b></span></td>
		<td class="cat" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=edit"><?php echo $user->lang['Edit'] ?></a></span></td>
		<td class="cat" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=delete"><?php echo $user->lang['Delete'] ?></a></span></td>
		<td class="cat" align="center" valign="middle" nowrap="nowrap"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=move_up&parent_id=<?php echo $parent_id ?>"><?php echo $user->lang['Move_up'] ?></a> <a href="admin_forums.<?php echo $url ?>&mode=move_down&parent_id=<?php echo $parent_id ?>"><?php echo $user->lang['Move_down'] ?></a></span></td>
		<td class="cat" align="center" valign="middle"><span class="gen">&nbsp</span></td>
	</tr>

<?php

		$sub_result = $db->sql_query('SELECT * FROM ' . FORUMS_TABLE . ' WHERE parent_id = ' . $row['forum_id'] . ' ORDER BY left_id');
		while ($sub_row = $db->sql_fetchrow($sub_result))
		{
			$url = $phpEx . $SID . '&f=' . $sub_row['forum_id'];
			$forum_title = htmlspecialchars($sub_row['forum_name']);
			$forum_desc = htmlspecialchars($sub_row['forum_desc']);
			$subforums = ($sub_row['right_id'] - $sub_row['left_id'] - 1) / 2;

			if ($sub_row['forum_status'] != ITEM_CATEGORY)
			{
				$colspan = '';
				$forum_stats = '
					<td class="row1" align="center" valign="middle"><span class="gen">' . $sub_row['forum_topics'] . '</span></td>
					<td class="row2" align="center" valign="middle"><span class="gen">' . $sub_row['forum_posts'] . '</span></td>
					<td class="row3" align="center" valign="middle"><span class="gen">' . $subforums . '</span></td>
				';
			}
			else
			{
				$colspan = ' colspan="3"';
				$forum_stats = '
					<td class="row3" align="center" valign="middle"><span class="gen">' . $subforums . '</span></td>
				';
			}

			if ($sub_row['left_id'] + 1 != $sub_row['right_id'])
			{
				$forum_title = '<a href="admin_forums.' . $phpEx . $SID . '&parent_id=' . $sub_row['forum_id'] . '">' . $forum_title . '</a>';
			}
?>
	<tr>
		<td width="1" class="row3"></td>
		<td width="40%" class="row2"<?php echo $colspan ?>><span class="gen"><?php echo $forum_title ?></span><br /><span class="gensmall"><?php echo $forum_desc ?></span></td>
		<?php echo $forum_stats ?>
		<td class="row2" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=edit"><?php echo $user->lang['Edit'] ?></a></span></td>
		<td class="row1" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=delete"><?php echo $user->lang['Delete'] ?></a></span></td>
		<td class="row2" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=move_up&parent_id=<?php echo $parent_id ?>"><?php echo $user->lang['Move_up'] ?></a> <br /> <a href="admin_forums.<?php echo $url ?>&mode=move_down&parent_id=<?php echo $parent_id ?>"><?php echo $user->lang['Move_down'] ?></a></span></td>
		<td class="row1" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=resync&parent_id=<?php echo $parent_id ?>"><?php echo ($sub_row['forum_status'] != ITEM_CATEGORY) ? $user->lang['Resync'] : '&nbsp;' ?></a></span></td>
	</tr>
<?php
		}
?>
	<tr>
		<td width="1" class="row3"></td>
		<td width="100%" colspan="8" class="row2"><input type="text" name="forum_name[<? echo $row['forum_id'] ?>]" /> <input type="submit" class="liteoption"  name="parent_id[<? echo $row['forum_id'] ?>]" value="<?php echo $user->lang['Create_forum'] ?>" /></td>
	</tr>
<?php
	}
	else
	{
?>
	<tr>
		<td width="40%" class="row2"<?php echo $colspan ?>><span class="gen"><?php echo $forum_title ?></span><br /><span class="gensmall"><?php echo $forum_desc ?></span></td>
		<?php echo $forum_stats ?>
		<td class="row2" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=edit"><?php echo $user->lang['Edit'] ?></a></span></td>
		<td class="row1" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=delete"><?php echo $user->lang['Delete'] ?></a></span></td>
		<td class="row2" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=move_up&parent_id=<?php echo $parent_id ?>"><?php echo $user->lang['Move_up'] ?></a> <br /> <a href="admin_forums.<?php echo $url ?>&mode=move_down&parent_id=<?php echo $parent_id ?>"><?php echo $user->lang['Move_down'] ?></a></span></td>
		<td class="row1" align="center" valign="middle"><span class="gen"><a href="admin_forums.<?php echo $url ?>&mode=resync&parent_id=<?php echo $parent_id ?>"><?php echo $user->lang['Resync'] ?></a></span></td>
	</tr>
<?php
	}
}

?>
	<tr>
		<td width="100%" colspan="9" class="cat"><input type="text" name="forum_name[<? echo $parent_id ?>]" /> <input type="submit" class="liteoption"  name="parent_id[<? echo $parent_id ?>]" value="<?php echo $user->lang['Create_forum'] ?>" /></td>
	</tr>
</table></form>

<?php


page_footer();

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
		message_die(ERROR, "Forum #$forum_id does not exist");
	}

	return $row;
}

function make_forums_list($mode='all', $selected_id=0, $exclude_id=array())
{
	global $db;
	if (!is_array($exclude_id))
	{
		$exclude_id = array($exclude_id);
	}

	$sql = 'SELECT f2.*
			FROM ' . FORUMS_TABLE . ' f1, ' . FORUMS_TABLE . ' f2
			WHERE f1.parent_id = 0
			  AND f2.left_id BETWEEN f1.left_id AND f1.right_id
			ORDER BY f2.left_id';
	$result = $db->sql_query($sql);

	$list = '';
	$indent = array();
	$current_indent = 0;

	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['parent_id'] == 0)
		{
			$current_indent = 0;
		}
		elseif (!isset($indent[$row['parent_id']]))
		{
			++$current_indent;
			$indent[$row['parent_id']] = $current_indent;
		}
		else
		{
			$current_indent = $indent[$row['parent_id']];
		}

		if (($mode == 'forums' && $row['forum_status'] == ITEM_CATEGORY)
		 || ($mode == 'categories' && $row['forum_status'] != ITEM_CATEGORY)
		 || (in_array($row['forum_id'], $exclude_id)))
		{
			continue;
		}

		if ($mode == 'all' && !$row['parent_id'])
		{
			$list .= "<option value=\"-1\">&nbsp;</option>\n";
		}

		$list .= '<option value="' . $row['forum_id'] . '"';
		$list .= ($row['forum_id'] == $selected_id) ? ' selected="selected">' : '>';
		$list .= str_repeat('--', $current_indent) . (($indent) ? ' ' : '') . htmlspecialchars($row['forum_name']) . "</option>\n";
	}

	return $list;
}

function make_styles_list($selected_id=0)
{
	global $db;

	$list = '';
	$result = $db->sql_query('SELECT style_id, style_name FROM ' . STYLES_TABLE . ' ORDER BY style_name');

	while ($row = $db->sql_fetchrow($result))
	{
		$list .= '<option value="' . $row['style_id'] . '"' . (($row['style_id'] == $selected_id) ? ' selected="selected">' : '>') . htmlspecialchars($row['style_name']) . "</option>\n";
	}
	return $list;
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

	//
	// Resync parents
	//
	$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET right_id = right_id - $diff, forum_parents = ''
			WHERE left_id < " . $from_data['right_id'] . " AND right_id > " . $from_data['right_id'];
	$db->sql_query($sql);

	//
	// Resync righthand side of tree
	//
	$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET left_id = left_id - $diff, right_id = right_id - $diff, forum_parents = ''
			WHERE left_id > " . $from_data['right_id'];
	$db->sql_query($sql);

	if ($to_id > 0)
	{
		$to_data = get_forum_info($to_id);

		//
		// Resync new parents
		//
		$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET right_id = right_id + $diff, forum_parents = ''
				WHERE " . $to_data['right_id'] . ' BETWEEN left_id AND right_id
				  AND forum_id NOT IN (' . implode(', ', $moved_ids) . ')';
		$db->sql_query($sql);

		//
		// Resync the righthand side of the tree
		//
		$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET left_id = left_id + $diff, right_id = right_id + $diff, forum_parents = ''
				WHERE left_id > " . $to_data['right_id'] . '
				  AND forum_id NOT IN (' . implode(', ', $moved_ids) . ')';
		$db->sql_query($sql);

		//
		// Resync moved branch
		//
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
		$result = $db->sql_query('SELECT MAX(right_id) AS right_id FROM ' . FORUMS_TABLE . ' WHERE forum_id NOT IN (' . implode(', ', $moved_ids) . ')');
		$right_id = $db->sql_fetchfield('right_id', 0, $result);

		$diff = '+ ' . ($right_id - $from_data['left_id'] + 1);
	}

	$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET left_id = left_id $diff, right_id = right_id $diff, forum_parents = ''
			WHERE forum_id IN (" . implode(', ', $moved_ids) . ')';
	$db->sql_query($sql);
}

function move_forum_content($from_id, $to_id)
{
	global $db;

	$db->sql_query('UPDATE ' . ACL_GROUPS_TABLE . " SET forum_id = $to_id WHERE forum_id = $from_id");
	$db->sql_query('UPDATE ' . ACL_PREFETCH_TABLE . " SET forum_id = $to_id WHERE forum_id = $from_id");
	$db->sql_query('UPDATE ' . LOG_MOD_TABLE . " SET forum_id = $to_id WHERE forum_id = $from_id");
	$db->sql_query('UPDATE ' . POSTS_TABLE . " SET forum_id = $to_id WHERE forum_id = $from_id");
	$db->sql_query('UPDATE ' . TOPICS_TABLE . " SET forum_id = $to_id WHERE forum_id = $from_id");

	//
	// TODO: untested yet
	//
	$sql = 'SELECT t1.topic_id
			FROM ' .TOPICS_TABLE . ' t1
			LEFT JOIN ' . TOPICS_TABLE . " t2 ON t1.topic_moved_id = t2.topic_id AND t1.forum_id = t2.forum_id
			WHERE t1.forum_id = $to_id";
	$result = $db->sql_query($result);

	$topic_ids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$topic_ids[] = $row['topic_id'];
	}
	if (count($topic_ids))
	{
		$db->sql_query('DELETE FROM ' . TOPICS_TABLE . ' WHERE topic_id IN (' . implode(', ', $topic_ids) . ')');
	}
	sync('forum', $to_id);

	//
	// TODO: there might be conflicts in ACL tables =\
	//		 make sure that the query that retrieves shadow topics uses the correct index (topic_type or topic_moved_id)
	//
}

function delete_forum_content($forum_id)
{
	global $db;

	$db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . " WHERE forum_id = $forum_id");
	$db->sql_query('DELETE FROM ' . ACL_PREFETCH_TABLE . " WHERE forum_id = $forum_id");
	$db->sql_query('DELETE FROM ' . LOG_MOD_TABLE . " WHERE forum_id = $forum_id");
	$db->sql_query('DELETE FROM ' . FORUMS_WATCH_TABLE . " WHERE forum_id = $forum_id");

	$ids = array();
	$result = $db->sql_query('SELECT post_id FROM ' . POSTS_TABLE . " WHERE forum_id = $forum_id");
	while ($row = $db->sql_fetchrow($result))
	{
		$ids[] = $row['post_id'];
	}
	$ids = implode(',', $ids);
	$db->sql_freeresult();

	if ($ids)
	{
		$db->sql_query('DELETE FROM ' . SEARCH_MATCH_TABLE . " WHERE post_id IN ($ids)");
		$db->sql_query('DELETE FROM ' . POSTS_TABLE . " WHERE forum_id = $forum_id");
		$db->sql_query('DELETE FROM ' . POSTS_TEXT_TABLE . " WHERE post_id IN ($ids)");
	}

	$ids = array();
	$result = $db->sql_query('SELECT topic_id FROM ' . TOPICS_TABLE . " WHERE forum_id = $forum_id");
	while ($row = $db->sql_fetchrow($result))
	{
		$ids[] = $row['topic_id'];
	}
	$ids = implode(',', $ids);
	$db->sql_freeresult();

	if ($ids)
	{
		$db->sql_query('DELETE FROM ' . TOPICS_RATING_TABLE . " WHERE topic_id IN ($ids)");
		$db->sql_query('DELETE FROM ' . TOPICS_WATCH_TABLE . " WHERE topic_id IN ($ids)");
		$db->sql_query('DELETE FROM ' . TOPICS_TABLE . " WHERE forum_id = $forum_id");
		$db->sql_query('DELETE FROM ' . TOPICS_TABLE . " WHERE topic_moved_id IN ($ids)");
	}

	//
	// TODO: delete attachments
	//		 delete polls
	//		 OPTIMIZE / VACUUM table ?
	//
}

//
// End function block
// ------------------

?>