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

if ( !empty($setmodules) )
{
	if ( !$auth->acl_get('a_general') )
	{
		return;
	}

	$file = basename(__FILE__);
	$module['General']['Word_Censor'] = "$file$SID";
	return;
}

define('IN_PHPBB', 1);
//
// Include files
//
$phpbb_root_path = '../';
require($phpbb_root_path . 'extension.inc');
require('pagestart.' . $phpEx);

//
// Do we have forum admin permissions?
//
if ( !$auth->acl_get('a_general') )
{
	return;
}

//
//
//
if ( isset($_GET['mode']) || isset($_POST['mode']) )
{
	$mode = ( isset($_GET['mode']) ) ? $_GET['mode'] : $_POST['mode'];
}
else
{
	//
	// These could be entered via a form button
	//
	if ( isset($_POST['add']) )
	{
		$mode = 'add';
	}
	else if ( isset($_POST['save']) )
	{
		$mode = 'save';
	}
	else
	{
		$mode = '';
	}
}

if( $mode != '' )
{
	switch ( $mode )
	{
		case 'edit':
		case 'add':
			$word_id = ( isset($_GET['id']) ) ? intval($_GET['id']) : 0;

			$s_hidden_fields = '';
			if ( $mode == 'edit' )
			{
				if ( !$word_id )
				{
					message_die(MESSAGE, $lang['No_word_selected']);
				}

				$sql = "SELECT *
					FROM " . WORDS_TABLE . "
					WHERE word_id = $word_id";
				$result = $db->sql_query($sql);

				$word_info = $db->sql_fetchrow($result);
				$s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '" />';
			}

			page_header($lang['Words_title']);

?>

<h1><?php echo $lang['Words_title']; ?></h1>

<p><?php echo $lang['Words_explain']; ?></p>

<form method="post" action="<?php echo "admin_words.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $lang['Edit_word_censor']; ?></th>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Word']; ?></td>
		<td class="row2"><input type="text" name="word" value="<?php echo $word_info['word']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><?php echo $lang['Replacement']; ?></td>
		<td class="row2"><input type="text" name="replacement" value="<?php echo $word_info['replacement']; ?>" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $s_hidden_fields; ?><input class="mainoption" type="submit" name="save" value="<?php echo $lang['Submit']; ?>" /></td>
	</tr>
</table></form>

<?php

			break;

		case 'save':
			$word_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : 0;
			$word = ( isset($_POST['word']) ) ? trim($_POST['word']) : '';
			$replacement = ( isset($_POST['replacement']) ) ? trim($_POST['replacement']) : '';

			if ( $word == '' || $replacement == '' )
			{
				message_die(MESSAGE, $lang['Must_enter_word']);
			}

			$sql = ( $word_id ) ? "UPDATE " . WORDS_TABLE . " SET word = '" . str_replace("\'", "''", $word) . "', replacement = '" . str_replace("\'", "''", $replacement) . "' WHERE word_id = $word_id" : "INSERT INTO " . WORDS_TABLE . " (word, replacement) VALUES ('" . str_replace("\'", "''", $word) . "', '" . str_replace("\'", "''", $replacement) . "')";
			$db->sql_query($sql);

			$log_action = ( $word_id ) ? 'log_edit_word' : 'log_add_word';
			add_admin_log($log_action, stripslashes($word));

			$message = ( $word_id ) ? $lang['Word_updated'] : $lang['Word_added'];
			message_die(MESSAGE, $message);
			break;

		case 'delete':

			if ( isset($_POST['id']) || isset($_GET['id']) )
			{
				$word_id = ( isset($_POST['id']) ) ? intval($_POST['id']) : intval($_GET['id']);
			}
			else
			{
				message_die(MESSAGE, $lang['Must_specify_word']);
			}

			$sql = "DELETE FROM " . WORDS_TABLE . "
				WHERE word_id = $word_id";
			$db->sql_query($sql);

			add_admin_log('log_delete_word');

			message_die(MESSAGE, $lang['Word_removed']);
			break;
	}

}
else
{

	page_header($lang['Words_title']);

?>

<h1><?php echo $lang['Words_title']; ?></h1>

<p><?php echo $lang['Words_explain']; ?></p>

<form method="post" action="<?php echo "admin_words.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $lang['Word']; ?></th>
		<th><?php echo $lang['Replacement']; ?></th>
		<th colspan="2"><?php echo $lang['Action']; ?></th>
	</tr>

<?php

	$sql = "SELECT *
		FROM " . WORDS_TABLE . "
		ORDER BY word";
	$result = $db->sql_query($sql);

	if ( $row = $db->sql_fetchrow($result) )
	{
		do
		{
			$row_class = ( $row_class == 'row1' ) ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['word']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['replacement']; ?></td>
		<td class="<?php echo $row_class; ?>">&nbsp;<a href="<?php echo "admin_words.$phpEx$SID&amp;mode=edit&amp;id=" . $row['word_id']; ?>"><?php echo $lang['Edit']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>">&nbsp;<a href="<?php echo "admin_words.$phpEx$SID&amp;mode=delete&amp;id=" . $row['word_id']; ?>"><?php echo $lang['Delete']; ?></a>&nbsp;</td>
	</tr>
<?php

		}
		while ( $row = $db->sql_fetchrow($result) );
	}

?>
	<tr>
		<td class="cat" colspan="5" height="28" align="center"><?php echo $s_hidden_fields; ?><input class="mainoption" type="submit" name="add" value="<?php echo $lang['Add_new_word']; ?>" /></td>
	</tr>
</table></form>

<?php

}

page_footer()

?>