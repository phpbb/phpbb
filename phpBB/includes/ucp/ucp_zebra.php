<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : ucp_zebra.php
// STARTED   : Sun Sep 28, 2003
// COPYRIGHT : © 2001, 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

class ucp_zebra extends module
{
	function ucp_zebra($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$submit	= (!empty($_POST['submit']) || !empty($_GET['add'])) ? true : false;

		if ($submit)
		{
			$var_ary = array(
				'usernames'	=> 0,
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

			if ($add)
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
				unset($friends);
				unset($foes);

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
							$user_id_ary[] = $row['user_id'];
						}
						while ($row = $db->sql_fetchrow($result));

						// Remove users from foe list if they are admins or moderators
						if ($mode == 'foes')
						{
							$perms = array();
							foreach ($auth->acl_get_list($user_id_ary, array('a_', 'm_')) as $forum_id => $forum_ary)
							{
								foreach ($forum_ary as $auth_option => $user_ary)
								{
									$perms += $user_ary;
								}
							}

							// This may not be right ... it may yield true when perms equate to deny
							$user_id_ary = array_diff($user_id_ary, $perms);
							unset($perms);
						}

						if (sizeof($user_id_ary))
						{
							$sql_mode = ($mode == 'friends') ? 'friend' : 'foe';

							switch (SQL_LAYER)
							{
								case 'mysql':
								case 'mysql4':
									$sql = 'INSERT INTO ' . ZEBRA_TABLE . " (user_id, zebra_id, $sql_mode) 
										VALUES " . implode(', ', preg_replace('#^([0-9]+)$#', '(' . $user->data['user_id'] . ", \\1, 1)",  $user_id_ary));
									$db->sql_query($sql);
									break;

								case 'mssql':
								case 'mssql-odbc':
								case 'sqlite':
									$sql = 'INSERT INTO ' . ZEBRA_TABLE . " (user_id, zebra_id, $sql_mode) 
										" . implode(' UNION ALL ', preg_replace('#^([0-9]+)$#', '(' . $user->data['user_id'] . ", \\1, 1)",  $user_id_ary));
									$db->sql_query($sql);
									break;

								default:
									foreach ($user_id_ary as $zebra_id)
									{
										$sql = 'INSERT INTO ' . ZEBRA_TABLE . " (user_id, zebra_id, $sql_mode)
											VALUES (" . $user->data['user_id'] . ", $zebra_id, 1)";
										$db->sql_query($sql);
									}
									break;
							}
						}
						unset($user_id_ary);
					}
					$db->sql_freeresult($result);
				}
			}
			else if ($usernames)
			{
				// Force integer values
				$usernames = array_map('intval', $usernames);

				$sql = 'DELETE FROM ' . ZEBRA_TABLE . ' 
					WHERE user_id = ' . $user->data['user_id'] . ' 
						AND zebra_id IN (' . implode(', ', $usernames) . ')';
				$db->sql_query($sql);
			}

			meta_refresh(3, "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
			$message = $user->lang[strtoupper($mode) . '_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], "<a href=\"ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');
			trigger_error($message);
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
			'L_TITLE'	=> $user->lang['UCP_' . strtoupper($mode)],

			'U_SEARCH_USER'		=> "memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=ucp&amp;field=add", 

			'S_USERNAME_OPTIONS'	=> $s_username_options,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
			'S_UCP_ACTION'			=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode")
		);

		$this->display($user->lang['UCP_ZEBRA'], 'ucp_zebra_' . $mode . '.html');
	}
}

?>