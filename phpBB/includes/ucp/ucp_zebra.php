<?php
/** 
*
* @package ucp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* @package ucp
* ucp_zebra
*/
class ucp_zebra
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$submit	= (isset($_POST['submit']) || isset($_GET['add'])) ? true : false;
		$s_hidden_fields = '';

		if ($submit)
		{
			$var_ary = array(
				'usernames'	=> array(0),
				'add'		=> '', 
			);

			foreach ($var_ary as $var => $default)
			{
				$data[$var] = request_var($var, $default);
			}

			$var_ary = array(
				'add'	=> array('string', false)
			);

			$error = validate_data($data, $var_ary);
			extract($data);
			unset($data);

			if ($add && !sizeof($error))
			{
				$add = explode("\n", $add);

				// Do these name/s exist on a list already? If so, ignore ... we could be
				// 'nice' and automatically handle names added to one list present on 
				// the other (by removing the existing one) ... but I have a feeling this
				// may lead to complaints
				$sql = 'SELECT z.*, u.username 
					FROM ' . ZEBRA_TABLE . ' z, ' . USERS_TABLE . ' u 
					WHERE z.user_id = ' . $user->data['user_id'] . "
						AND u.user_id = z.zebra_id";
				$result = $db->sql_query($sql);

				$friends = $foes = array();
				while ($row = $db->sql_fetchrow($result))
				{
					if ($row['friend'])
					{
						$friends[] = $row['username'];
					}
					else
					{
						$foes[] = $row['username'];
					}
				}
				$db->sql_freeresult($result);

				$add = array_diff($add, $friends, $foes, array($user->data['username']));
				unset($friends, $foes);

				$add = implode(', ', preg_replace('#^[\s]*?(.*?)[\s]*?$#e', "\"'\" . \$db->sql_escape('\\1') . \"'\"", $add));

				if ($add)
				{
					$sql = 'SELECT user_id
						FROM ' . USERS_TABLE . ' 
						WHERE username IN (' . $add . ')';
					$result = $db->sql_query($sql);

					if ($row = $db->sql_fetchrow($result))
					{
						$user_id_ary = array();
						do
						{
							if ($row['user_id'] != ANONYMOUS)
							{
								$user_id_ary[] = $row['user_id'];
							}
						}
						while ($row = $db->sql_fetchrow($result));

						// Remove users from foe list if they are admins or moderators
						if (($mode == 'foes') && sizeof($user_id_ary))
						{
							$perms = array();
							foreach ($auth->acl_get_list($user_id_ary, array('a_', 'm_')) as $forum_id => $forum_ary)
							{
								foreach ($forum_ary as $auth_option => $user_ary)
								{
									$perms = array_merge($perms, $user_ary);
								}
							}

							$perms = array_unique($perms);

							// This may not be right ... it may yield true when perms equate to deny
							$user_id_ary = array_diff($user_id_ary, $perms);
							unset($perms);
						}

						if (sizeof($user_id_ary))
						{
							$sql_mode = ($mode == 'friends') ? 'friend' : 'foe';

							$sql_ary = array();
							foreach ($user_id_ary as $zebra_id)
							{
								$sql_ary[] = array(
									'user_id'		=> $user->data['user_id'],
									'zebra_id'		=> (int) $zebra_id,
									$sql_mode		=> 1
								);
							}

							if (sizeof($sql_ary))
							{
								switch (SQL_LAYER)
								{
									case 'mysql':
									case 'mysql4':
									case 'mysqli':
										$db->sql_query('INSERT INTO ' . ZEBRA_TABLE . ' ' . $db->sql_build_array('MULTI_INSERT', $sql_ary));
									break;

									default:
										foreach ($sql_ary as $ary)
										{
											$db->sql_query('INSERT INTO ' . ZEBRA_TABLE . ' ' . $db->sql_build_array('INSERT', $ary));
										}
									break;
								}
							}
						}
						else
						{
							$error[] = 'NOT_ADDED_' . strtoupper($mode);
						}
						unset($user_id_ary);
					}
					else
					{
						$error[] = 'USER_NOT_FOUND';
					}

					$db->sql_freeresult($result);
				}
			}
			else if ($usernames && !sizeof($error))
			{
				// Force integer values
				$usernames = array_map('intval', $usernames);

				$sql = 'DELETE FROM ' . ZEBRA_TABLE . ' 
					WHERE user_id = ' . $user->data['user_id'] . ' 
						AND zebra_id IN (' . implode(', ', $usernames) . ')';
				$db->sql_query($sql);
			}

			if (!sizeof($error))
			{			
				meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
				$message = $user->lang[strtoupper($mode) . '_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');
				trigger_error($message);
			}
			else
			{
				$template->assign_var('ERROR', implode('<br />', preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error)));
			}
		}

		$sql_and = ($mode == 'friends') ? 'z.friend = 1' : 'z.foe = 1';
		$sql = 'SELECT z.*, u.username 
			FROM ' . ZEBRA_TABLE . ' z, ' . USERS_TABLE . ' u 
			WHERE z.user_id = ' . $user->data['user_id'] . "
				AND $sql_and 
				AND u.user_id = z.zebra_id";
		$result = $db->sql_query($sql);

		$s_username_options = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$s_username_options .= '<option value="' . $row['zebra_id'] . '">' . $row['username'] . '</option>';
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array( 
			'L_TITLE'			=> $user->lang['UCP_ZEBRA_' . strtoupper($mode)],

			'U_SEARCH_USER'			=> "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=ucp&amp;field=add", 

			'S_USERNAME_OPTIONS'	=> $s_username_options,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode")
		);

		$this->tpl_name = 'ucp_zebra_' . $mode;
	}
}

?>