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
* @package acp
*/
class acp_disallow
{
	function main($id, $mode)
	{
		global $db, $user, $auth, $template, $cache;
		global $config, $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		$user->add_lang('acp/posting');

		// Set up general vars
		$this->tpl_name = 'acp_disallow';
		$this->page_header = 'ACP_DISALLOW_USERNAMES';

		$disallow = (isset($_POST['disallow'])) ? true : false;
		$allow = (isset($_POST['allow'])) ? true : false;

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		if ($disallow)
		{
			$disallowed_user = str_replace('*', '%', request_var('disallowed_user', ''));
			$message = validate_username($disallowed_user);

			if (!$message)
			{
				$sql = 'INSERT INTO ' . DISALLOW_TABLE . ' ' . $db->sql_build_array('INSERT', array('disallow_username' => $disallowed_user));
				$db->sql_query($sql);

				$message = $user->lang['DISALLOW_SUCCESSFUL'];
				add_log('admin', 'LOG_DISALLOW_ADD', str_replace('%', '*', $disallowed_user));
			}

			trigger_error($message . adm_back_link($u_action));
		}
		else if ($allow)
		{
			$disallowed_id = request_var('disallowed_id', 0);

			if (!$disallowed_id)
			{
				trigger_error($user->lang['NO_USER'] . adm_back_link($u_action));
			}

			$sql = 'DELETE FROM ' . DISALLOW_TABLE . "
				WHERE disallow_id = $disallowed_id";
			$db->sql_query($sql);

			add_log('admin', 'LOG_DISALLOW_DELETE');

			trigger_error($user->lang['DISALLOWED_DELETED'] . adm_back_link($u_action));
		}

		// Grab the current list of disallowed usernames...
		$sql = 'SELECT *
			FROM ' . DISALLOW_TABLE;
		$result = $db->sql_query($sql);

		$disallow_select = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$disallow_select .= '<option value="' . $row['disallow_id'] . '">' . str_replace('%', '*', $row['disallow_username']) . '</option>';
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'U_ACTION'				=> $u_action,
			'S_DISALLOWED_NAMES'	=> $disallow_select)
		);
	}
}

/**
* @package module_install
*/
class acp_disallow_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_disallow',
			'title'		=> 'ACP_DISALLOW',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'usernames'		=> array('title' => 'ACP_DISALLOW_USERNAMES', 'auth' => 'acl_a_names'),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}


?>