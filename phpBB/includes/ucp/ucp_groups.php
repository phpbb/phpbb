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
class ucp_groups
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $SID, $template, $phpbb_root_path, $phpEx;

		$user->add_lang('groups');

		$return_page = '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $phpbb_root_path . "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode\">", '</a>');

		$mark_ary	= request_var('mark', array(0));
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
					GROUP BY g.group_id
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

				$action		= (isset($_POST['addusers'])) ? 'addusers' : request_var('action', '');
				$group_id	= request_var('g', 0);

				if ($group_id)
				{
					$sql = 'SELECT * 
						FROM ' . GROUPS_TABLE . " 
						WHERE group_id = $group_id";
					$result = $db->sql_query($sql);
					$group_row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if (!$group_row)
					{
						trigger_error($user->lang['NO_GROUP'] . $return_page);
					}
				}

				switch ($action)
				{
					case 'edit':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])) || !$row[0]['group_leader'])
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}

						$file_uploads 	= (@ini_get('file_uploads') || strtolower(@ini_get('file_uploads')) == 'on') ? true : false;
						$user->add_lang(array('acp/groups', 'acp/common'));

						$data = $submit_ary = array();

						$update	= (isset($_POST['update'])) ? true : false;

						$error = array();

						$avatar_select = basename(request_var('avatar_select', ''));
						$category = basename(request_var('category', ''));

						$can_upload = (file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && $file_uploads) ? true : false;

						// Did we submit?
						if ($update)
						{
							$group_name	= request_var('group_name', '');
							$group_description = request_var('group_description', '');
							$group_type	= request_var('group_type', GROUP_FREE);

							$data['uploadurl']	= request_var('uploadurl', '');
							$data['remotelink'] = request_var('remotelink', '');
							$delete				= request_var('delete', '');

							$submit_ary = array(
								'colour'		=> request_var('group_colour', ''),
								'rank'			=> request_var('group_rank', 0),
								'receive_pm'	=> isset($_REQUEST['group_receive_pm']) ? 1 : 0,
								'message_limit'	=> request_var('group_message_limit', 0)
							);

							if (!empty($_FILES['uploadfile']['tmp_name']) || $data['uploadurl'] || $data['remotelink'])
							{
								$data['width']		= request_var('width', '');
								$data['height']		= request_var('height', '');

								// Avatar stuff
								$var_ary = array(
									'uploadurl'		=> array('string', true, 5, 255), 
									'remotelink'	=> array('string', true, 5, 255), 
									'width'			=> array('string', true, 1, 3), 
									'height'		=> array('string', true, 1, 3), 
								);

								if (!($error = validate_data($data, $var_ary)))
								{
									$data['user_id'] = "g$group_id";

									if ((!empty($_FILES['uploadfile']['tmp_name']) || $data['uploadurl']) && $can_upload)
									{
										list($submit_ary['avatar_type'], $submit_ary['avatar'], $submit_ary['avatar_width'], $submit_ary['avatar_height']) = avatar_upload($data, $error);
									}
									else if ($data['remotelink'])
									{
										list($submit_ary['avatar_type'], $submit_ary['avatar'], $submit_ary['avatar_width'], $submit_ary['avatar_height']) = avatar_remote($data, $error);
									}
								}
							}
							else if ($avatar_select && $config['allow_avatar_local'])
							{
								// check avatar gallery
								if (is_dir($phpbb_root_path . $config['avatar_gallery_path'] . '/' . $category))
								{
									$submit_ary['avatar_type'] = AVATAR_GALLERY;

									list($submit_ary['avatar_width'], $submit_ary['avatar_height']) = getimagesize($phpbb_root_path . $config['avatar_gallery_path'] . '/' . $category . '/' . $avatar_select);
									$submit_ary['avatar'] = $category . '/' . $avatar_select;
								}
							}
							else if ($delete)
							{
								$submit_ary['avatar'] = '';
								$submit_ary['avatar_type'] = $submit_ary['avatar_width'] = $submit_ary['avatar_height'] = 0;
							}

							if ((isset($submit_ary['avatar']) && $submit_ary['avatar'] && (!isset($group_row['group_avatar']) || $group_row['group_avatar'] != $submit_ary['avatar'])) || $delete)
							{
								if (isset($group_row['group_avatar']) && $group_row['group_avatar'])
								{
									avatar_delete($group_row['group_avatar']);
								}
							}

							// Only set the rank, colour, etc. if it's changed or if we're adding a new
							// group. This prevents existing group members being updated if no changes 
							// were made.
					
							$group_attributes = array();
							$test_variables = array('rank', 'colour', 'avatar', 'avatar_type', 'avatar_width', 'avatar_height');
							foreach ($test_variables as $test)
							{
								if ($action == 'add' || (isset($submit_ary[$test]) && $group_row['group_' . $test] != $submit_ary[$test]))
								{
									$group_attributes['group_' . $test] = $group_row['group_' . $test] = $submit_ary[$test];
								}
							}

							if (!($error = group_create($group_id, $group_type, $group_name, $group_description, $group_attributes)))
							{
								$message = ($action == 'edit') ? 'GROUP_UPDATED' : 'GROUP_CREATED';
								trigger_error($user->lang[$message] . $return_page);
							}
						}
						else if (!$group_id)
						{
							$group_name = request_var('group_name', '');
							$group_description = '';
							$group_rank = 0;
							$group_type = GROUP_OPEN;
						}
						else
						{
							$group_name = $group_row['group_name'];
							$group_description = $group_row['group_description'];
							$group_type = $group_row['group_type'];
							$group_rank = $group_row['group_rank'];
						}

						$sql = 'SELECT * 
							FROM ' . RANKS_TABLE . '
							WHERE rank_special = 1
							ORDER BY rank_title';
						$result = $db->sql_query($sql);

						$rank_options = '<option value="0"' . ((!$group_rank) ? ' selected="selected"' : '') . '>' . $user->lang['USER_DEFAULT'] . '</option>';
						while ($row = $db->sql_fetchrow($result))
						{
							$selected = ($group_rank && $row['rank_id'] == $group_rank) ? ' selected="selected"' : '';
							$rank_options .= '<option value="' . $row['rank_id'] . '"' . $selected . '>' . $row['rank_title'] . '</option>';
						}
						$db->sql_freeresult($result);

						$type_free		= ($group_type == GROUP_FREE) ? ' checked="checked"' : '';
						$type_open		= ($group_type == GROUP_OPEN) ? ' checked="checked"' : '';
						$type_closed	= ($group_type == GROUP_CLOSED) ? ' checked="checked"' : '';
						$type_hidden	= ($group_type == GROUP_HIDDEN) ? ' checked="checked"' : '';

						if (isset($group_row['group_avatar']) && $group_row['group_avatar'])
						{
							switch ($group_row['group_avatar_type'])
							{
								case AVATAR_UPLOAD:
									$avatar_img = $phpbb_root_path . $config['avatar_path'] . '/';
									break;
								case AVATAR_GALLERY:
									$avatar_img = $phpbb_root_path . $config['avatar_gallery_path'] . '/';
									break;
							}
							$avatar_img .= $group_row['group_avatar'];

							$avatar_img = '<img src="' . $avatar_img . '" width="' . $group_row['group_avatar_width'] . '" height="' . $group_row['group_avatar_height'] . '" alt="" />';
						}
						else
						{
							$avatar_img = '<img src="' . $phpbb_root_path . 'adm/images/no_avatar.gif" alt="" />';
						}

						$display_gallery = (isset($_POST['display_gallery'])) ? true : false;

						if ($config['allow_avatar_local'] && $display_gallery)
						{
							avatar_gallery($category, $avatar_select, 4);
						}

						$template->assign_vars(array(
							'S_EDIT'			=> true,
							'S_INCLUDE_SWATCH'	=> true,
							'S_CAN_UPLOAD'		=> $can_upload,
							'S_ERROR'			=> (sizeof($error)) ? true : false,
							'S_SPECIAL_GROUP'	=> ($group_type == GROUP_SPECIAL) ? true : false,
							'S_DISPLAY_GALLERY'	=> ($config['allow_avatar_local'] && !$display_gallery) ? true : false,
							'S_IN_GALLERY'		=> ($config['allow_avatar_local'] && $display_gallery) ? true : false,

							'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
							'GROUP_NAME'			=> ($group_type == GROUP_SPECIAL) ? $user->lang['G_' . $group_name] : $group_name,
							'GROUP_INTERNAL_NAME'	=> $group_name,
							'GROUP_DESCRIPTION'		=> $group_description,
							'GROUP_RECEIVE_PM'		=> (isset($group_row['group_receive_pm']) && $group_row['group_receive_pm']) ? ' checked="checked"' : '',
							'GROUP_MESSAGE_LIMIT'	=> (isset($group_row['group_message_limit'])) ? $group_row['group_message_limit'] : 0,
							'GROUP_COLOUR'			=> (isset($group_row['group_colour'])) ? $group_row['group_colour'] : '',

							'S_RANK_OPTIONS'		=> $rank_options,
							'AVATAR_IMAGE'			=> $avatar_img,
							'AVATAR_MAX_FILESIZE'	=> $config['avatar_filesize'],
							'GROUP_AVATAR_WIDTH'	=> (isset($group_row['group_avatar_width'])) ? $group_row['group_avatar_width'] : '',
							'GROUP_AVATAR_HEIGHT'	=> (isset($group_row['group_avatar_height'])) ? $group_row['group_avatar_height'] : '',

							'GROUP_TYPE_FREE'		=> GROUP_FREE,
							'GROUP_TYPE_OPEN'		=> GROUP_OPEN,
							'GROUP_TYPE_CLOSED'		=> GROUP_CLOSED,
							'GROUP_TYPE_HIDDEN'		=> GROUP_HIDDEN,
							'GROUP_TYPE_SPECIAL'	=> GROUP_SPECIAL,

							'GROUP_FREE'		=> $type_free,
							'GROUP_OPEN'		=> $type_open,
							'GROUP_CLOSED'		=> $type_closed,
							'GROUP_HIDDEN'		=> $type_hidden,

							'U_SWATCH'			=> "{$phpbb_root_path}adm/swatch.$phpEx$SID&form=settings&name=group_colour",
							'U_ACTION'			=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;action=$action&amp;g=$group_id",
							'L_AVATAR_EXPLAIN'	=> sprintf($user->lang['AVATAR_EXPLAIN'], $config['avatar_max_width'], $config['avatar_max_height'], round($config['avatar_filesize'] / 1024)))
						);

					break;

					case 'list':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])) || !$row[0]['group_leader'])
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}

						$user->add_lang(array('acp/groups', 'acp/common'));

						// Total number of group members (non-leaders)
						$sql = 'SELECT COUNT(user_id) AS total_members 
							FROM ' . USER_GROUP_TABLE . " 
							WHERE group_id = $group_id 
								AND group_leader <> 1";
						$result = $db->sql_query($sql);
						
						$total_members = (int) $db->sql_fetchfield('total_members', 0, $result);
						$db->sql_freeresult($result);

						$start = request_var('start', 0);

						// Grab the members
						$sql = 'SELECT u.user_id, u.username, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending 
							FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug 
							WHERE ug.group_id = $group_id 
								AND u.user_id = ug.user_id 
								AND ug.group_leader = 0
							ORDER BY ug.group_leader DESC, ug.user_pending ASC, u.username";
						$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

						$pending = false;

						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['user_pending'] && !$pending)
							{
								$template->assign_block_vars('member', array(
									'S_PENDING'		=> true)
								);

								$pending = true;
							}

							$template->assign_block_vars('member', array(
								'USERNAME'			=> $row['username'],
								'U_USER_VIEW'		=> "memberlist.$phpEx$SID&amp;mode=viewprofile&amp;u={$row['user_id']}",
								'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
								'JOINED'			=> ($row['user_regdate']) ? $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']) : '-',
								'USER_POSTS'		=> $row['user_posts'],
								'USER_ID'			=> $row['user_id'])
							);
						}
						$db->sql_freeresult($result);

						$s_action_options = '';
						$options = array('default' => 'DEFAULT', 'approve' => 'APPROVE', 'deleteusers' => 'DELETE');

						foreach ($options as $option => $lang)
						{
							$s_action_options .= '<option value="' . $option . '">' . $user->lang['GROUP_' . $lang] . '</option>';
						}

						$template->assign_vars(array(
							'S_LIST'			=> true,
							'S_ACTION_OPTIONS'	=> $s_action_options,
							'S_ON_PAGE'		=> on_page($total_members, $config['topics_per_page'], $start),
							'PAGINATION'	=> generate_pagination("ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;action=$action&amp;g=$group_id", $total_members, $config['topics_per_page'], $start, true),

							'U_ACTION'			=> "ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;g=$group_id",
							'U_FIND_USERNAME'	=> "memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=list&amp;field=usernames")
						);

					break;

					case 'approve':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])) || !$row[0]['group_leader'])
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}

						$user->add_lang('acp/groups');

						// Approve, demote or promote
						group_user_attributes('approve', $group_id, $mark_ary, false, ($group_id) ? $group_row['group_name'] : false);

						trigger_error($user->lang['USERS_APPROVED'] . $return_page);
					break;

					case 'default':

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])) || !$row[0]['group_leader'])
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}

						if (confirm_box(true))
						{
							if (!sizeof($mark_ary))
							{
								$start = 0;
				
								do
								{
									$sql = 'SELECT user_id 
										FROM ' . USER_GROUP_TABLE . "
										WHERE group_id = $group_id 
										ORDER BY user_id";
									$result = $db->sql_query_limit($sql, 200, $start);

									$mark_ary = array();
									if ($row = $db->sql_fetchrow($result))
									{
										do
										{
											$mark_ary[] = $row['user_id'];
										}
										while ($row = $db->sql_fetchrow($result));

										group_user_attributes('default', $group_id, $mark_ary, false, $group_row['group_name'], $group_row);

										$start = (sizeof($mark_ary) < 200) ? 0 : $start + 200;
									}
									else
									{
										$start = 0;
									}
									$db->sql_freeresult($result);
								}
								while ($start);
							}
							else
							{
								group_user_attributes('default', $group_id, $mark_ary, false, $group_row['group_name'], $group_row);
							}

							$user->add_lang('acp/groups');

							trigger_error($user->lang['GROUP_DEFS_UPDATED'] . $return_page);
						}
						else
						{

							$user->add_lang('acp/common');

							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
								'mark'		=> $mark_ary,
								'g'			=> $group_id,
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action))
							);
						}

					break;

					case 'deleteusers':

						$user->add_lang(array('acp/groups', 'acp/common'));

						if (!($row = group_memberships($group_id, $user->data['user_id'])) || !$row[0]['group_leader'])
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}

						if (confirm_box(true))
						{
							if (!$group_id)
							{
								trigger_error($user->lang['NO_GROUP'] . $return_page);
							}
									
							$error = group_user_del($group_id, $mark_ary, false, $group_row['group_name']);

							if ($error)
							{
								trigger_error($user->lang[$error] . $return_page);
							}

							trigger_error($user->lang['GROUP_USERS_REMOVE'] . $return_page);
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
								'mark'		=> $mark_ary,
								'g'			=> $group_id,
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action))
							);
						}
					break;

					case 'addusers':

						$user->add_lang(array('acp/groups', 'acp/common'));

						$name_ary = request_var('usernames', '');

						if (!$group_id)
						{
							trigger_error($user->lang['NO_GROUP'] . $return_page);
						}

						if (!$name_ary)
						{
							trigger_error($user->lang['NO_USERS'] . $return_page);
						}

						if (!($row = group_memberships($group_id, $user->data['user_id'])) || !$row[0]['group_leader'])
						{
							trigger_error($user->lang['NOT_MEMBER_OF_GROUP'] . $return_page);
						}

						$name_ary = array_unique(explode("\n", $name_ary));

						$default = request_var('default', 0);

						// Add user/s to group
						if ($error = group_user_add($group_id, false, $name_ary, $group_row['group_name'], $default, 0, 0, $group_row))
						{
							trigger_error($user->lang[$error] . $return_page);
						}

						trigger_error($user->lang['GROUP_USERS_ADDED'] . $return_page);
					break;

					default:
						$user->add_lang('acp/common');

						$sql = 'SELECT g.group_id, g.group_name, g.group_description, g.group_type, ug.group_leader
							FROM ' . GROUPS_TABLE . ' g, ' . USER_GROUP_TABLE . ' ug
							WHERE ug.user_id = ' . $user->data['user_id'] . '
								AND g.group_id = ug.group_id
								AND ug.group_leader = 1
							ORDER BY g.group_type DESC, g.group_name';
						$result = $db->sql_query($sql);

						while ($value = $db->sql_fetchrow($result))
						{
							$template->assign_block_vars('leader', array(
								'GROUP_NAME'	=> ($value['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $value['group_name']] : $value['group_name'],
								'GROUP_DESC'	=> $value['group_description'],
								'GROUP_TYPE'	=> $value['group_type'],
								'GROUP_ID'		=> $value['group_id'],

								'U_LIST'	=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;action=list&amp;g={$value['group_id']}",
								'U_EDIT'	=> "{$phpbb_root_path}ucp.$phpEx$SID&amp;i=$id&amp;mode=$mode&amp;action=edit&amp;g={$value['group_id']}")
							);
						}

						$db->sql_freeresult($result);

					break;
				}

			break;
		}

		$this->tpl_name = 'ucp_groups_' . $mode;
	}
}

/**
* @package module_install
*/
class ucp_groups_info
{
	function module()
	{
		return array(
			'filename'	=> 'ucp_groups',
			'title'		=> 'UCP_USERGROUPS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'membership'	=> array('title' => 'UCP_USERGROUPS_MEMBER', 'auth' => ''),
				'manage'		=> array('title' => 'UCP_USERGROUPS_MANAGE', 'auth' => ''),
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