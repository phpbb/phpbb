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
* ucp_groups
*/
class ucp_groups extends module
{
	function ucp_groups($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$user->add_lang('groups');

		$return_page = '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $phpbb_root_path . "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');

		$submit		= (!empty($_POST['submit'])) ? true : false;
		$delete		= (!empty($_POST['delete'])) ? true : false;
		$error = $data = array();

		switch ($mode)
		{
			case 'membership':

				if ($submit || isset($_POST['change_default']))
				{
					$action = (isset($_POST['change_default'])) ? 'change_default' : request_var('action', '');
					$group_id = ($action == 'change_default') ? request_var('default', 0) : request_var('selected', 0);

					if (!$group_id)
					{
						trigger_error('NO_GROUP_SELECTED');
					}

					$sql = 'SELECT group_id, group_name, group_type
						FROM ' . GROUPS_TABLE . "
						WHERE group_id IN ($group_id, {$user->data['group_id']})";
					$result = $db->sql_query($sql);
					$group_row = array();

					while ($row = $db->sql_fetchrow($result))
					{
						$group_row[$row['group_id']] = $row;
					}
					$db->sql_freeresult($result);

					if (!sizeof($group_row))
					{
						trigger_error('GROUP_NOT_EXIST');
					}

					$group_row[$group_id]['group_name'] = ($group_row[$group_id]['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $group_row[$group_id]['group_name']] : $group_row[$group_id]['group_name'];
					$group_row[$user->data['group_id']]['group_name'] = ($group_row[$user->data['group_id']]['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $group_row[$user->data['group_id']]['group_name']] : $group_row[$user->data['group_id']]['group_name'];


					switch ($action)
					{
						case 'change_default':
							// User already having this group set as default?
							if ($group_id == $user->data['group_id'])
							{
								trigger_error($user->lang['ALREADY_DEFAULT_GROUP'] . $return_page);
							}

							// User needs to be member of the group in order to make it default
							if (!group_memberships($group_id, $user->data['user_id'], true))
							{
								trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
							}

							if (confirm_box(true))
							{
								group_user_attributes('default', $group_id, $user->data['user_id']);

								add_log('user', $user->data['user_id'], 'LOG_USER_GROUP_CHANGE', sprintf($user->lang['USER_GROUP_CHANGE'], $group_row[$group_id]['group_name'], $group_row[$user->data['group_id']]['group_name']));
								
								meta_refresh(3, $phpbb_root_path . "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
								trigger_error($user->lang['CHANGED_DEFAULT_GROUP'] . $return_page);
							}
							else
							{
								$s_hidden_fields = array(
									'default'		=> $group_id,
									'change_default'=> true
								);
								
								confirm_box(false, sprintf($user->lang['GROUP_CHANGE_DEFAULT'], $group_row[$group_id]['group_name']), build_hidden_fields($s_hidden_fields));
							}

						break;

						case 'resign':
							
							if (!($row = group_memberships($group_id, $user->data['user_id'])))
							{
								trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
							}
							list(, $row) = each($row);
							
							if (confirm_box(true))
							{
								group_user_del($group_id, $user->data['user_id']);
								
								add_log('user', $user->data['user_id'], 'LOG_USER_GROUP_RESIGN', $group_row[$group_id]['group_name']);
								
								meta_refresh(3, $phpbb_root_path . "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
								trigger_error($user->lang[($row['user_pending']) ? 'GROUP_RESIGNED_PENDING' : 'GROUP_RESIGNED_MEMBERSHIP'] . $return_page);
							}
							else
							{
								$s_hidden_fields = array(
									'selected'		=> $group_id,
									'action'		=> 'resign',
									'submit'		=> true
								);
								
								confirm_box(false, ($row['user_pending']) ? 'GROUP_RESIGN_PENDING' : 'GROUP_RESIGN_MEMBERSHIP', build_hidden_fields($s_hidden_fields));
							}

						break;

						case 'join':

							if (group_memberships($group_id, $user->data['user_id'], true))
							{
								trigger_error($user->lang['ALREADY_IN_GROUP'] . $return_page);
							}
							
							// Check permission to join (open group or request)
							if ($group_row[$group_id]['group_type'] != GROUP_OPEN && $group_row[$group_id]['group_type'] != GROUP_FREE)
							{
								trigger_error($user->lang['CANNOT_JOIN_GROUP'] . $return_page);
							}

							if (confirm_box(true))
							{
								if ($group_row[$group_id]['group_type'] == GROUP_FREE)
								{
									group_user_add($group_id, $user->data['user_id']);
									
									$email_template = 'group_added';
								}
								else
								{
									group_user_add($group_id, $user->data['user_id'], false, false, false, 0, 1);
									
									$email_template = 'group_request';
								}
								
								include_once($phpbb_root_path . 'includes/functions_messenger.'.$phpEx);
								$messenger = new messenger();

								$email_sig = str_replace('<br />', "\n", "-- \n" . $config['board_email_sig']);

								$sql = 'SELECT u.username, u.user_email, u.user_notify_type, u.user_jabber, u.user_lang
									FROM ' . USER_GROUP_TABLE . ' ug, ' . USERS_TABLE . ' u
									WHERE ug.user_id = u.user_id
										AND ' . (($group_row[$group_id]['group_type'] == GROUP_FREE) ? "ug.user_id = {$user->data['user_id']}" : 'ug.group_leader = 1') . "
										AND ug.group_id = $group_id";
								$result = $db->sql_query($sql);
								
								while ($row = $db->sql_fetchrow($result))
								{
									$messenger->template($email_template, $row['user_lang']);

									$messenger->replyto($config['board_email']);
									$messenger->to($row['user_email'], $row['username']);
									$messenger->im($row['user_jabber'], $row['username']);

									$messenger->assign_vars(array(
										'EMAIL_SIG'		=> $email_sig,
										'SITENAME'		=> $config['sitename'],
										'USERNAME'		=> $row['username'],
										'GROUP_NAME'	=> $group_row[$group_id]['group_name'],

										'U_PENDING'		=> generate_board_url() . "/ucp.$phpEx?i=usergroups&amp;mode=manage",
										'U_GROUP'		=> generate_board_url() . "/memberlist.$phpEx?mode=group&amp;g=$group_id")
									);

									$messenger->send($row['user_notify_type']);
									$messenger->reset();
								}
								$db->sql_freeresult($result);
						
								$messenger->save_queue();
								
								add_log('user', $user->data['user_id'], 'LOG_USER_GROUP_JOIN' . (($group_row[$group_id]['group_type'] == GROUP_FREE) ? '' : '_PENDING'), $group_row[$group_id]['group_name']);
								
								meta_refresh(3, $phpbb_root_path . "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
								trigger_error($user->lang[($group_row[$group_id]['group_type'] == GROUP_FREE) ? 'GROUP_JOINED' : 'GROUP_JOINED_PENDING'] . $return_page);
							}
							else
							{
								$s_hidden_fields = array(
									'selected'		=> $group_id,
									'action'		=> 'join',
									'submit'		=> true
								);
								
								confirm_box(false, ($group_row[$group_id]['group_type'] == GROUP_FREE) ? 'GROUP_JOIN' : 'GROUP_JOIN_PENDING', build_hidden_fields($s_hidden_fields));
							}

						break;

						case 'demote':

							if (!($row = group_memberships($group_id, $user->data['user_id'])))
							{
								trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
							}
							list(, $row) = each($row);

							if (!$row['group_leader'])
							{
								trigger_error($user->lang['NOT_LEADER_OF_GROUP'] . $return_page);
							}

							if (confirm_box(true))
							{
								group_user_attributes('demote', $group_id, $user->data['user_id']);

								add_log('user', $user->data['user_id'], 'LOG_USER_GROUP_DEMOTE', $group_row[$group_id]['group_name']);
								
								meta_refresh(3, $phpbb_root_path . "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode");
								trigger_error($user->lang['USER_GROUP_DEMOTED'] . $return_page);
							}
							else
							{
								$s_hidden_fields = array(
									'selected'		=> $group_id,
									'action'		=> 'demote',
									'submit'		=> true
								);
								
								confirm_box(false, 'USER_GROUP_DEMOTE', build_hidden_fields($s_hidden_fields));
							}

						break;
					}
				}

				$sql = 'SELECT g.group_id, g.group_name, g.group_description, g.group_type, ug.group_leader, ug.user_pending
					FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
					WHERE ug.user_id = ' . $user->data['user_id'] . '
						AND g.group_id = ug.group_id
					ORDER BY g.group_type DESC, g.group_name';
				$result = $db->sql_query($sql);

				$group_id_ary = array();
				$leader_count = $member_count = $pending_count = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					$block = ($row['group_leader']) ? 'leader' : (($row['user_pending']) ? 'pending' : 'member');

					switch ($row['group_type'])
					{
						case GROUP_OPEN:
							$group_status = 'OPEN';
						break;
						
						case GROUP_CLOSED:
							$group_status = 'CLOSED';
						break;
				
						case GROUP_HIDDEN:
							$group_status = 'HIDDEN';
						break;
	
						case GROUP_SPECIAL:
							$group_status = 'SPECIAL';
						break;
		
						case GROUP_FREE:
							$group_status = 'FREE';
						break;
					}

					$template->assign_block_vars($block, array(
						'GROUP_ID'		=> $row['group_id'],
						'GROUP_NAME'	=> ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'],
						'GROUP_DESC'	=> ($row['group_type'] <> GROUP_SPECIAL) ? $row['group_description'] : $user->lang['GROUP_IS_SPECIAL'],
						'GROUP_SPECIAL'	=> ($row['group_type'] <> GROUP_SPECIAL) ? false : true,
						'GROUP_STATUS'	=> $user->lang['GROUP_IS_' . $group_status],

						'U_VIEW_GROUP'	=> "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=group&amp;g={$row['group_id']}",

						'S_GROUP_DEFAULT'	=> ($row['group_id'] == $user->data['group_id']) ? true : false,
						'S_ROW_COUNT'		=> ${$block . '_count'}++)
					);

					$group_id_ary[] = $row['group_id'];
				}
				$db->sql_freeresult($result);

				// Hide hidden groups unless user is an admin with group privileges
				$sql_and = ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? '<> ' . GROUP_SPECIAL : 'NOT IN (' . GROUP_SPECIAL . ', ' . GROUP_HIDDEN . ')';
				$sql = 'SELECT group_id, group_name, group_description, group_type
					FROM ' . GROUPS_TABLE . '
					WHERE group_id NOT IN (' . implode(', ', $group_id_ary) . ")
						AND group_type $sql_and
					ORDER BY group_type DESC, group_name";
				$result = $db->sql_query($sql);

				$nonmember_count = 0;
				while ($row = $db->sql_fetchrow($result))
				{
					switch ($row['group_type'])
					{
						case GROUP_OPEN:
							$group_status = 'OPEN';
						break;
						
						case GROUP_CLOSED:
							$group_status = 'CLOSED';
						break;
				
						case GROUP_HIDDEN:
							$group_status = 'HIDDEN';
						break;
	
						case GROUP_SPECIAL:
							$group_status = 'SPECIAL';
						break;
		
						case GROUP_FREE:
							$group_status = 'FREE';
						break;
					}

					$template->assign_block_vars('nonmember', array(
						'GROUP_ID'		=> $row['group_id'],
						'GROUP_NAME'	=> ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'],
						'GROUP_DESC'	=> ($row['group_type'] <> GROUP_SPECIAL) ? $row['group_description'] : $user->lang['GROUP_IS_SPECIAL'],
						'GROUP_SPECIAL'	=> ($row['group_type'] <> GROUP_SPECIAL) ? false : true,
						'GROUP_CLOSED'	=> ($row['group_type'] <> GROUP_CLOSED || $auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel')) ? false : true,
						'GROUP_STATUS'	=> $user->lang['GROUP_IS_' . $group_status],
						'S_CAN_JOIN'	=> ($row['group_type'] == GROUP_OPEN || $row['group_type'] == GROUP_FREE) ? true : false,

						'U_VIEW_GROUP'	=> "{$phpbb_root_path}memberlist.$phpEx$SID&amp;mode=group&amp;g={$row['group_id']}",

						'S_ROW_COUNT'	=> $nonmember_count++)
					);
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'S_CHANGE_DEFAULT'	=> ($auth->acl_get('u_chggrp')) ? true : false,
					'S_LEADER_COUNT'	=> $leader_count,
					'S_MEMBER_COUNT'	=> $member_count,
					'S_PENDING_COUNT'	=> $pending_count,
					'S_NONMEMBER_COUNT'	=> $nonmember_count,
					
					'S_UCP_ACTION'			=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode")
				);

				break;

			case 'manage':
				break;
		}

		$this->display($user->lang['UCP_GROUPS_' . strtoupper($mode)], 'ucp_groups_' . $mode . '.html');
	}
}

?>