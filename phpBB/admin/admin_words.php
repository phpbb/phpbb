<?php
/***************************************************************************
 *                              admin_words.php
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
	if (!$auth->acl_get('a_words'))
	{
		return;
	}

	$module['POST']['WORD_CENSOR'] = basename(__FILE__) . $SID;
	return;
}

define('IN_PHPBB', 1);
// Include files
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);
require_once($phpbb_root_path . 'includes/functions_admin.' . $phpEx);

// Do we have forum admin permissions?
if (!$auth->acl_get('a_words'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

// What do we want to do?
if (isset($_REQUEST['mode']))
{
	$mode = $_REQUEST['mode'];
}
else
{
	// These could be entered via a form button
	if (isset($_POST['add']))
	{
		$mode = 'add';
	}
	else if (isset($_POST['save']))
	{
		$mode = 'save';
	}
	else
	{
		$mode = '';
	}
}

if ($mode != '')
{
	switch ($mode)
	{
		case 'edit':
		case 'add':
			$word_id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;

			$s_hidden_fields = '';
			if ($mode == 'edit')
			{
				if (!$word_id)
				{
					trigger_error($user->lang['No_word_selected']);
				}

				$sql = "SELECT *
					FROM " . WORDS_TABLE . "
					WHERE word_id = $word_id";
				$result = $db->sql_query($sql);

				$word_info = $db->sql_fetchrow($result);
				$s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '" />';
			}

			page_header($user->lang['Words_title']);

?>

<h1><?php echo $user->lang['Words_title']; ?></h1>

<p><?php echo $user->lang['Words_explain']; ?></p>

<form method="post" action="<?php echo "admin_words.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['Edit_word_censor']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Word']; ?></td>
		<td class="row2"><input type="text" name="word" value="<?php echo $word_info['word']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $user->lang['Replacement']; ?></td>
		<td class="row2"><input type="text" name="replacement" value="<?php echo $word_info['replacement']; ?>" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $s_hidden_fields; ?><input class="mainoption" type="submit" name="save" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table></form>

<?php

			page_footer();
			break;

		case 'save':
			$word_id = (isset($_POST['id'])) ? intval($_POST['id']) : 0;
			$word = (isset($_POST['word'])) ? trim($_POST['word']) : '';
			$replacement = (isset($_POST['replacement'])) ? trim($_POST['replacement']) : '';

			if ($word == '' || $replacement == '')
			{
				trigger_error($user->lang['Must_enter_word']);
			}

			$sql = ($word_id) ? "UPDATE " . WORDS_TABLE . " SET word = '" . sql_quote($word) . "', replacement = '" . sql_quote($replacement) . "' WHERE word_id = $word_id" : "INSERT INTO " . WORDS_TABLE . " (word, replacement) VALUES ('" . sql_quote($word) . "', '" . sql_quote($replacement) . "')";
			$db->sql_query($sql);
			$cache->destroy('word_censors');

			$log_action = ($word_id) ? 'log_edit_word' : 'log_add_word';
			add_admin_log($log_action, stripslashes($word));

			$message = ($word_id) ? $user->lang['Word_updated'] : $user->lang['Word_added'];
			break;

		case 'delete':

			if (isset($_POST['id']) || isset($_GET['id']))
			{
				$word_id = (isset($_POST['id'])) ? intval($_POST['id']) : intval($_GET['id']);
			}
			else
			{
				trigger_error($user->lang['Must_specify_word']);
			}

			$sql = "DELETE FROM " . WORDS_TABLE . "
				WHERE word_id = $word_id";
			$db->sql_query($sql);
			$cache->destroy('word_censors');

			add_admin_log('log_delete_word');

			$message = $user->lang['Word_remove'];
			break;

	}

	$sql = "SELECT *
		FROM " . WORDS_TABLE . "
		ORDER BY word";
	$result = $db->sql_query($sql);

	$cache_str = "\$word_censors = array(\n";
	$cache_str_match = $cache_str_replace = '';
	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			$cache_str_match .= "\t\t'" . addslashes('#\b' . str_replace('\*', '.*?', preg_quote($row['word'], '#')) . '\b#i') . "',\n";
			$cache_str_replace .= "\t\t'" . addslashes($row['replacement']) . "',\n";
		}
		while ($row = $db->sql_fetchrow($result));

		$cache_str .= "\t'match' => array(\n$cache_str_match\t),\n\t'replace' => array(\n$cache_str_replace\t)\n);";
	}
	$db->sql_freeresult($result);

	config_cache_write('\$word_censors = array\(.*?\);', $cache_str);
	trigger_error($message);

}
else
{

	page_header($user->lang['Words_title']);

?>

<h1><?php echo $user->lang['Words_title']; ?></h1>

<p><?php echo $user->lang['Words_explain']; ?></p>

<form method="post" action="<?php echo "admin_words.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $user->lang['Word']; ?></th>
		<th><?php echo $user->lang['Replacement']; ?></th>
		<th colspan="2"><?php echo $user->lang['Action']; ?></th>
	</tr>

<?php

	$sql = "SELECT *
		FROM " . WORDS_TABLE . "
		ORDER BY word";
	$result = $db->sql_query($sql);

	if ($row = $db->sql_fetchrow($result))
	{
		do
		{
			$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['word']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['replacement']; ?></td>
		<td class="<?php echo $row_class; ?>">&nbsp;<a href="<?php echo "admin_words.$phpEx$SID&amp;mode=edit&amp;id=" . $row['word_id']; ?>"><?php echo $user->lang['Edit']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>">&nbsp;<a href="<?php echo "admin_words.$phpEx$SID&amp;mode=delete&amp;id=" . $row['word_id']; ?>"><?php echo $user->lang['DELETE']; ?></a>&nbsp;</td>
	</tr>
<?php

		}
		while ($row = $db->sql_fetchrow($result));
	}
	$db->sql_freeresult($result);

?>
	<tr>
		<td class="cat" colspan="5" height="28" align="center"><?php echo $s_hidden_fields; ?><input class="mainoption" type="submit" name="add" value="<?php echo $user->lang['Add_new_word']; ?>" /></td>
	</tr>
</table></form>

<?php

	page_footer();

}

?>