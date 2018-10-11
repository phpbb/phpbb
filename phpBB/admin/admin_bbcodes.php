<?php
/**
*
* @package phpBB2 admin
* @version $Id$
* @copyright (c) 2002-2008 MX-Publisher Project Team
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

@define('IN_PHPBB', 1);

if(!empty($setmodules))
{
	$file = basename(__FILE__);
	$module['General']['Custom_BBCodes'] = $file;
	return;
}

//
// Load default header
//
$phpbb_root_path = "./../";
require($phpbb_root_path . 'extension.inc');
if (!defined('PHPBB_ROOT_PATH')) define('PHPBB_ROOT_PATH', './../');
if (!defined('$php_ext')) define('$php_ext', substr(strrchr(__FILE__, '.'), 1));
require('./pagestart.' . $phpEx);

include_once($phpbb_root_path . 'includes/bbcode.' . $phpEx);

define('THIS_PAGE', 'admin_bbcodes.' . $phpEx);

// DB CLASS - BEGIN
include($phpbb_root_path . 'includes/class_db.' . $phpEx);
$class_db = new class_db();
$class_db->main_db_table = BBCODES_TABLE;
$class_db->main_db_item = 'bbcode_id';
// DB CLASS - END

// MODES - BEGIN
$mode_types = array('list', 'add', 'edit', 'save', 'delete');
$mode = request_var('mode', $mode_types[0]);
$mode = (isset($_POST['add']) ? 'add' : (isset($_POST['save']) ? 'save' : $mode));
$mode = (!in_array($mode, $mode_types) ? $mode_types[0] : $mode);
// MODES - END

// VARS - BEGIN
$bbcode_id = request_var('bbcode_id', 0);
$page_action = append_sid(THIS_PAGE . '?mode=' . $mode . (!empty($bbcode_id) ? ('&amp;bbcode=' . $bbcode_id) : ''));
$s_hidden_fields = '';
// VARS - END

if($mode != 'list')
{
	if(($mode == 'edit') || ($mode == 'add'))
	{
		$template->set_filenames(array('body' => ('admin/bbcodes_edit_body.tpl')));

		if($mode == 'edit')
		{
			if($bbcode_id)
			{
				$sql = "SELECT * FROM " . BBCODES_TABLE . " WHERE bbcode_id = " . $bbcode_id;
				$result = $db->sql_query($sql);
				$bbcode_info = $db->sql_fetchrow($result);
				$s_hidden_fields .= '<input type="hidden" name="bbcode_id" value="' . $bbcode_id . '" />';
				$db->sql_freeresult($result);
			}
			else
			{
				message_die(GENERAL_MESSAGE, $lang['BBCODES_NO_BBCODES_SEL']);
			}
		}

		$template->assign_vars(array(
			'BBCODE_TAG' => htmlspecialchars($bbcode_info['bbcode_tag']),
			'BBCODE_HELPLINE' => htmlspecialchars($bbcode_info['bbcode_helpline']),
			'BBCODE_MATCH' => htmlspecialchars($bbcode_info['bbcode_match']),
			'BBCODE_TPL' => htmlspecialchars($bbcode_info['bbcode_tpl']),

			'L_BBCODE_USAGE_EXPLAIN' => sprintf($lang['BBCODE_USAGE_EXPLAIN'], '<a href="#down">', '</a>'),
			'L_SUBMIT' => $lang['Submit'],
			)
		);
	}
	elseif($mode == 'save')
	{
		$page_action = append_sid(THIS_PAGE . (!empty($bbcode_id) ? ('?mode=edit&amp;bbcode=' . $bbcode_id) : ('?mode=add')));
		$inputs_array = array(
			//'bbcode_tag' => '',
			'bbcode_match' => '',
			'bbcode_tpl' => '',
			'bbcode_helpline' => '',
		);
		foreach ($inputs_array as $k => $v)
		{
			$inputs_array[$k] = request_var($k, $v);
			$inputs_array[$k] = htmlspecialchars_decode($inputs_array[$k], ENT_COMPAT);
		}

		$data = $bbcode->build_regexp($inputs_array['bbcode_match'], $inputs_array['bbcode_tpl']);
		foreach ($inputs_array as $k => $v)
		{
			//$data[$k] = addslashes($v);
			$data[$k] = $v;
		}

		if($data['bbcode_tag'] == '')
		{
			trigger_error($lang['BBCODES_NO_BBCODES_INPUT'] . page_back_link($page_action), E_USER_WARNING);
		}

		if (substr($data['bbcode_tag'], -1) === '=')
		{
			$test = substr($data['bbcode_tag'], 0, -1);
		}
		else
		{
			$test = $data['bbcode_tag'];
		}

		if (!preg_match('%\\[' . $test . '[^]]*].*?\\[/' . $test . ']%s', $data['bbcode_match']))
		{
			trigger_error($lang['BBCODE_OPEN_ENDED_TAG'] . page_back_link($page_action), E_USER_WARNING);
		}

		if (strlen($data['bbcode_tag']) > 16)
		{
			trigger_error($lang['BBCODE_TAG_TOO_LONG'] . page_back_link($page_action), E_USER_WARNING);
		}

		if (strlen($data['bbcode_match']) > 4000)
		{
			trigger_error($lang['BBCODE_TAG_DEF_TOO_LONG'] . page_back_link($page_action), E_USER_WARNING);
		}

		if (strlen($data['bbcode_helpline']) > 255)
		{
			trigger_error($lang['BBCODE_HELPLINE_TOO_LONG'] . page_back_link($page_action), E_USER_WARNING);
		}

		if(($data['bbcode_match'] == '') && ($data['bbcode_tpl'] == ''))
		{
			trigger_error($lang['BBCODE_INVALID'], E_USER_WARNING);
		}

		if ($bbcode_id > 0)
		{
			$class_db->update_item($bbcode_id, $data);
			$message = '<br /><br />' . $lang['BBCODES_DB_UPDATED'];
		}
		else
		{
			$class_db->insert_item($data);
			$message = '<br /><br />' . $lang['BBCODES_DB_ADDED'];
		}
		$cache->destroy_datafiles(array('_bbcodes'), MAIN_CACHE_FOLDER, 'data', true);

		$message .= '<br /><br />' . sprintf($lang['BBCODES_DB_CLICK'], '<a href="' . append_sid(THIS_PAGE) . '">', '</a>');
		$message .= '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid('index.' . $phpEx . '?pane=right') . '">', '</a>');
		message_die(GENERAL_MESSAGE, $message);
	}
	elseif($mode == 'delete')
	{
		if($bbcode_id > 0)
		{
			$class_db->delete_item($bbcode_id);
			$message = '<br /><br />' . $lang['BBCODES_DB_DELETED'];
		}
		else
		{
			$message = '<br /><br />' . $lang['BBCODES_NO_BBCODES_SEL'];
		}
		$cache->destroy_datafiles(array('_bbcodes'), MAIN_CACHE_FOLDER, 'data', true);

		$message .= '<br /><br />' . sprintf($lang['BBCODES_DB_CLICK'], '<a href="' . append_sid(THIS_PAGE) . '">', '</a>');
		$message .= '<br /><br />' . sprintf($lang['Click_return_admin_index'], '<a href="' . append_sid('index.' . $phpEx . '?pane=right') . '">', '</a>');
		message_die(GENERAL_MESSAGE, $message);
	}
}
else
{
	$template->set_filenames(array('body' => ('admin/bbcodes_list_body.tpl')));

	$sql = "SELECT * FROM " . BBCODES_TABLE . " ORDER BY bbcode_tag ASC";
	$result = $db->sql_query($sql);
	$bbcode_rows = $db->sql_fetchrowset($result);
	$bbcodes_count = sizeof($bbcode_rows);
	$db->sql_freeresult($result);

	if ($bbcodes_count == 0)
	{
		$template->assign_var('S_NO_BBCODES', true);
	}
	else
	{
		for($i = 0; $i < $bbcodes_count; $i++)
		{
			$row_class = (!($i % 2)) ? $theme['td_class1'] : $theme['td_class2'];

			$template->assign_block_vars('bbcode', array(
				'ROW_CLASS' => $row_class,
				'BBCODE_TAG' => htmlspecialchars($bbcode_rows[$i]['bbcode_tag']),
				'BBCODE_HELPLINE' => htmlspecialchars($bbcode_rows[$i]['bbcode_helpline']),
				'BBCODE_MATCH' => htmlspecialchars($bbcode_rows[$i]['bbcode_match']),
				'BBCODE_TPL' => htmlspecialchars($bbcode_rows[$i]['bbcode_tpl']),

				'U_EDIT' => append_sid(THIS_PAGE . '?mode=edit&amp;bbcode_id=' . $bbcode_rows[$i]['bbcode_id']),
				'U_DELETE' => append_sid(THIS_PAGE . '?mode=delete&amp;bbcode_id=' . $bbcode_rows[$i]['bbcode_id'])
				)
			);
		}
	}
}

$template->assign_vars(array(
	'S_BBCODES_ACTION' => append_sid(THIS_PAGE),
	'S_HIDDEN_FIELDS' => $s_hidden_fields
	)
);

$template->pparse('body');

include_once('./page_footer_admin.'.$phpEx);

?>