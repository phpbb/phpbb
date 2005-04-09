<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
*/
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
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require('pagestart.' . $phpEx);

// Do we have forum admin permissions?
if (!$auth->acl_get('a_words'))
{
	trigger_error($user->lang['NO_ADMIN']);
}

$mode = request_var('mode', '');
$mode = (isset($_POST['add'])) ? 'add' : ((isset($_POST['save'])) ? 'save' : $mode);

$s_hidden_fields = '';
$word_info = array();

switch ($mode)
{
	case 'edit':
		$word_id = request_var('id', 0);
		
		if (!$word_id)
		{
			trigger_error($user->lang['NO_WORD']);
		}

		$sql = 'SELECT *
			FROM ' . WORDS_TABLE . "
			WHERE word_id = $word_id";
		$result = $db->sql_query_limit($sql, 1);

		$word_info = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '" />';

	case 'add':

		adm_page_header($user->lang['WORDS_TITLE']);
?>

<h1><?php echo $user->lang['WORDS_TITLE']; ?></h1>

<p><?php echo $user->lang['WORDS_EXPLAIN']; ?></p>

<form method="post" action="<?php echo "admin_words.$phpEx$SID"; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2"><?php echo $user->lang['EDIT_WORD']; ?></th>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['WORD']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="word" value="<?php echo $word_info['word']; ?>" /></td>
	</tr>
	<tr>
		<td class="row1"><b><?php echo $user->lang['REPLACEMENT']; ?>: </b></td>
		<td class="row2"><input class="post" type="text" name="replacement" value="<?php echo $word_info['replacement']; ?>" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center"><?php echo $s_hidden_fields; ?><input class="btnmain" type="submit" name="save" value="<?php echo $user->lang['SUBMIT']; ?>" /></td>
	</tr>
</table></form>

<?php

			adm_page_footer();
			break;

		case 'save':
			$word_id = request_var('id', 0);
			$word = request_var('word', '');
			$replacement = request_var('replacement', '');

			if (!$word || !$replacement)
			{
				trigger_error($user->lang['ENTER_WORD']);
			}

			$sql = ($word_id) ? "UPDATE " . WORDS_TABLE . " SET word = '" . $db->sql_escape($word) . "', replacement = '" . $db->sql_escape($replacement) . "' WHERE word_id = $word_id" : "INSERT INTO " . WORDS_TABLE . " (word, replacement) VALUES ('" . $db->sql_escape($word) . "', '" . $db->sql_escape($replacement) . "')";
			$db->sql_query($sql);

			$cache->destroy('word_censors');

			$log_action = ($word_id) ? 'LOG_EDIT_WORD' : 'LOG_ADD_WORD';
			add_log('admin', $log_action, $word);

			$message = ($word_id) ? $user->lang['WORD_UPDATED'] : $user->lang['WORD_ADDED'];
			trigger_error($message);
			break;

		case 'delete':

			$word_id = request_var('id', 0);

			if (!$word_id)
			{
				trigger_error($user->lang['NO_WORD']);
			}

			$sql = 'SELECT word
				FROM ' . WORDS_TABLE . "
				WHERE word_id = $word_id";
			$result = $db->sql_query($sql);
			$deleted_word = $db->sql_fetchfield('word', 0, $result);
			$db->sql_freeresult($result);

			$sql = 'DELETE FROM ' . WORDS_TABLE . "
				WHERE word_id = $word_id";
			$db->sql_query($sql);

			$cache->destroy('word_censors');

			add_log('admin', 'LOG_DELETE_WORD', $deleted_word);

			$message = $user->lang['WORD_REMOVE'];
			trigger_error($message);
		
			break;

		default:

			adm_page_header($user->lang['WORDS_TITLE']);
?>

<h1><?php echo $user->lang['WORDS_TITLE']; ?></h1>

<p><?php echo $user->lang['WORDS_EXPLAIN']; ?></p>

<form method="post" action="admin_words.<?php echo $phpEx . $SID; ?>"><table class="bg" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th><?php echo $user->lang['WORD']; ?></th>
		<th><?php echo $user->lang['REPLACEMENT']; ?></th>
		<th colspan="2"><?php echo $user->lang['ACTION']; ?></th>
	</tr>

<?php

		$sql = 'SELECT *
			FROM ' . WORDS_TABLE . '
			ORDER BY word';
		$result = $db->sql_query($sql);

		$row_class = '';
		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$row_class = ($row_class == 'row1') ? 'row2' : 'row1';

?>
	<tr>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['word']; ?></td>
		<td class="<?php echo $row_class; ?>" align="center"><?php echo $row['replacement']; ?></td>
		<td class="<?php echo $row_class; ?>">&nbsp;<a href="<?php echo "admin_words.$phpEx$SID&amp;mode=edit&amp;id=" . $row['word_id']; ?>"><?php echo $user->lang['EDIT']; ?></a>&nbsp;</td>
		<td class="<?php echo $row_class; ?>">&nbsp;<a href="<?php echo "admin_words.$phpEx$SID&amp;mode=delete&amp;id=" . $row['word_id']; ?>"><?php echo $user->lang['DELETE']; ?></a>&nbsp;</td>
	</tr>
<?php

			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);

?>
	<tr>
		<td class="cat" colspan="5" height="28" align="center"><?php echo $s_hidden_fields; ?><input class="btnmain" type="submit" name="add" value="<?php echo $user->lang['ADD_WORD']; ?>" /></td>
	</tr>
</table></form>

<?php

		adm_page_footer();
		break;
}

?>