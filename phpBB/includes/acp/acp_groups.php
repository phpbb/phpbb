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
class acp_groups
{
	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $SID, $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix, $file_uploads;

		$user->add_lang('acp/groups');
		$this->tpl_name = 'acp_groups';
		$this->page_title = 'ACP_GROUPS_MANAGE';

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		// Check and set some common vars
		$action		= (isset($_POST['add'])) ? 'add' : ((isset($_POST['addusers'])) ? 'addusers' : request_var('action', ''));
		$group_id	= request_var('g', 0);
		$mark_ary	= request_var('mark', array(0));
		$name_ary	= request_var('usernames', '');
		$leader		= request_var('leader', 0);
		$default	= request_var('default', 0);
		$start		= request_var('start', 0);
		$update		= (isset($_POST['update'])) ? true : false;

		// Clear some vars
		$can_upload = (file_exists($phpbb_root_path . $config['avatar_path']) && is_writeable($phpbb_root_path . $config['avatar_path']) && $file_uploads) ? true : false;
		$group_row = array();

		// Grab basic data for group, if group_id is set and exists
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
				trigger_error($user->lang['NO_GROUP'] . adm_back_link($u_action));
			}
		}

		// Which page?
		switch ($action)
		{
			case 'approve':
			case 'demote':
			case 'promote':
				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($u_action));
				}
			
				// Approve, demote or promote
				group_user_attributes($action, $group_id, $mark_ary, false, ($group_id) ? $group_row['group_name'] : false);

				switch ($action)
				{
					case 'demote':
						$message = 'GROUP_MODS_DEMOTED';
					break;
			
					case 'promote':
						$message = 'GROUP_MODS_PROMOTED';
					break;
				
					case 'approve':
						$message = 'USERS_APPROVED';
					break;
				}

				trigger_error($user->lang[$message] . adm_back_link($u_action));
			break;

			case 'default':
				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($u_action));
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

					trigger_error($user->lang['GROUP_DEFS_UPDATED'] . adm_back_link($u_action));
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

			case 'deleteusers':
			case 'delete':
				if (confirm_box(true))
				{
					if (!$group_id)
					{
						trigger_error($user->lang['NO_GROUP'] . adm_back_link($u_action));
					}

					$error = '';

					switch ($action)
					{
						case 'delete':
							$error = group_delete($group_id, $group_row['group_name']);
						break;

						case 'deleteusers':
							$error = group_user_del($group_id, $mark_ary, false, $group_row['group_name']);
						break;
					}

					if ($error)
					{
						trigger_error($user->lang[$error] . adm_back_link($u_action));
					}

					$message = ($action == 'delete') ? 'GROUP_DELETED' : 'GROUP_USERS_REMOVE';
					trigger_error($user->lang[$message] . adm_back_link($u_action));
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
				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($u_action));
				}

				if (!$name_ary)
				{
					trigger_error($user->lang['NO_USERS'] . adm_back_link($u_action));
				}

				$name_ary = array_unique(explode("\n", $name_ary));

				// Add user/s to group
				if ($error = group_user_add($group_id, false, $name_ary, $group_row['group_name'], $default, $leader, 0, $group_row))
				{
					trigger_error($user->lang[$error] . adm_back_link($u_action));
				}

				$message = ($action == 'addleaders') ? 'GROUP_MODS_ADDED' : 'GROUP_USERS_ADDED';
				trigger_error($user->lang[$message] . adm_back_link($u_action));
			break;

			case 'edit':
			case 'add':

				$data = $submit_ary = array();

				if ($action == 'edit' && !$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($u_action));
				}

				$error = array();
				$user->add_lang('ucp');
			
				$avatar_select = basename(request_var('avatar_select', ''));
				$category = basename(request_var('category', ''));

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
					$test_variables = array('rank', 'colour', 'avatar', 'avatar_type', 'avatar_width', 'avatar_height', 'receive_pm', 'message_limit');
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
						trigger_error($user->lang[$message] . adm_back_link($u_action));
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
					$avatar_img = '<img src="' . $phpbb_admin_path . 'images/no_avatar.gif" alt="" />';
				}

				$display_gallery = (isset($_POST['display_gallery'])) ? true : false;

				if ($config['allow_avatar_local'] && $display_gallery)
				{
					avatar_gallery($category, $avatar_select, 4);
				}

				$back_link = request_var('back_link', '');

				switch ($back_link)
				{
					case 'acp_users_groups':
						$u_back = $phpbb_admin_path . "index.$phpEx$SID&amp;i=users&amp;mode=groups&amp;u=" . request_var('u', 0);
					break;

					default:
						$u_back = $u_action;
					break;
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

					'U_BACK'			=> $u_back,
					'U_SWATCH'			=> "{$phpbb_admin_path}swatch.$phpEx$SID&form=settings&name=group_colour",
					'U_ACTION'			=> "{$u_action}&amp;action=$action&amp;g=$group_id",
					'L_AVATAR_EXPLAIN'	=> sprintf($user->lang['AVATAR_EXPLAIN'], $config['avatar_max_width'], $config['avatar_max_height'], round($config['avatar_filesize'] / 1024)),
					)
				);

				return;
			break;

			case 'list':

				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($u_action));
				}

				$this->page_title = 'GROUP_MEMBERS';

				// Total number of group leaders
				$sql = 'SELECT COUNT(user_id) AS total_leaders 
					FROM ' . USER_GROUP_TABLE . " 
					WHERE group_id = $group_id 
						AND group_leader = 1";
				$result = $db->sql_query($sql);

				$total_leaders = (int) $db->sql_fetchfield('total_leaders', 0, $result);
				$db->sql_freeresult($result);

				// Total number of group members (non-leaders)
				$sql = 'SELECT COUNT(user_id) AS total_members 
					FROM ' . USER_GROUP_TABLE . " 
					WHERE group_id = $group_id 
						AND group_leader <> 1";
				$result = $db->sql_query($sql);
				
				$total_members = (int) $db->sql_fetchfield('total_members', 0, $result);
				$db->sql_freeresult($result);

				// Grab the members
				$sql = 'SELECT u.user_id, u.username, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending 
					FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug 
					WHERE ug.group_id = $group_id 
						AND u.user_id = ug.user_id 
					ORDER BY ug.group_leader DESC, ug.user_pending ASC, u.username";
				$result = $db->sql_query_limit($sql, $config['topics_per_page'], $start);

				$leader = $member = 0;
				$group_data = array();

				while ($row = $db->sql_fetchrow($result))
				{
					$type = ($row['group_leader']) ? 'leader' : 'member';

					$group_data[$type][$$type]['user_id'] = $row['user_id'];
					$group_data[$type][$$type]['group_id'] = $row['group_id'];
					$group_data[$type][$$type]['username'] = $row['username'];
					$group_data[$type][$$type]['user_regdate'] = $row['user_regdate'];
					$group_data[$type][$$type]['user_posts'] = $row['user_posts'];
					$group_data[$type][$$type]['user_pending'] = ($row['user_pending']) ? 1 : 0;

					$$type++;
				}
				$db->sql_freeresult($result);

				$s_action_options = '';
				$options = array('default' => 'DEFAULT', 'approve' => 'APPROVE', 'demote' => 'DEMOTE', 'promote' => 'PROMOTE', 'deleteusers' => 'DELETE');

				foreach ($options as $option => $lang)
				{
					$s_action_options .= '<option value="' . $option . '">' . $user->lang['GROUP_' . $lang] . '</option>';
				}

				$template->assign_vars(array(
					'S_LIST'			=> true,
					'S_GROUP_SPECIAL'	=> ($group_row['group_type'] == GROUP_SPECIAL) ? true : false,
					'S_ACTION_OPTIONS'	=> $s_action_options,

					'S_ON_PAGE'		=> on_page($total_members, $config['topics_per_page'], $start),
					'PAGINATION'	=> generate_pagination($u_action . "&amp;action=$action&amp;g=$group_id", $total_members, $config['topics_per_page'], $start, true),

					'U_ACTION'			=> $u_action . "&amp;g=$group_id",
					'U_BACK'			=> $u_action,
					'U_FIND_USERNAME'	=> $phpbb_root_path . "memberlist.$phpEx$SID&amp;mode=searchuser&amp;form=list&amp;field=usernames")
				);

				if ($group_row['group_type'] != GROUP_SPECIAL)
				{
					foreach ($group_data['leader'] as $row)
					{
						$template->assign_block_vars('leader', array(
							'U_USER_EDIT'		=> $phpbb_admin_path . "index.$phpEx$SID&amp;i=users&amp;action=edit&amp;u={$row['user_id']}",

							'USERNAME'			=> $row['username'],
							'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
							'JOINED'			=> ($row['user_regdate']) ? $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']) : '-',
							'USER_POSTS'		=> $row['user_posts'],
							'USER_ID'			=> $row['user_id'])
						);
					}
				}

				$pending = false;

				foreach ($group_data['member'] as $row)
				{
					if ($row['user_pending'] && !$pending)
					{
						$template->assign_block_vars('member', array(
							'S_PENDING'		=> true)
						);

						$pending = true;
					}

					$template->assign_block_vars('member', array(
						'U_USER_EDIT'		=> $phpbb_admin_path . "index.$phpEx$SID&amp;i=users&amp;action=edit&amp;u={$row['user_id']}",

						'USERNAME'			=> $row['username'],
						'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
						'JOINED'			=> ($row['user_regdate']) ? $user->format_date($row['user_regdate'], $user->lang['DATE_FORMAT']) : '-',
						'USER_POSTS'		=> $row['user_posts'],
						'USER_ID'			=> $row['user_id'])
					);
				}

				return;
			break;
		}

		$template->assign_vars(array(
			'U_ACTION'		=> $u_action,
			)
		);

		$sql = 'SELECT g.group_id, g.group_name, g.group_type, COUNT(ug.user_id) AS total_members 
			FROM ' . GROUPS_TABLE . ' g
			LEFT JOIN ' . USER_GROUP_TABLE . ' ug USING (group_id)
			GROUP BY g.group_id 
			ORDER BY g.group_type ASC, g.group_name';
		$result = $db->sql_query($sql);

		$special = $normal = 0;
		$group_ary = array();

		while ($row = $db->sql_fetchrow($result))
		{
			$type = ($row['group_type'] == GROUP_SPECIAL) ? 'special' : 'normal';

			$group_ary[$type][$$type]['group_id'] = $row['group_id'];
			$group_ary[$type][$$type]['group_name'] = $row['group_name'];
			$group_ary[$type][$$type]['group_type'] = $row['group_type'];
			$group_ary[$type][$$type]['total_members'] = $row['total_members'];

			$$type++;
		}
		$db->sql_freeresult($result);

		$special_toggle = false;
		foreach ($group_ary as $type => $row_ary)
		{
			if ($type == 'special')
			{
				$template->assign_block_vars('groups', array(
					'S_SPECIAL'			=> true)
				);
			}

			foreach ($row_ary as $row)
			{
				$group_id = $row['group_id'];
				$group_name = (!empty($user->lang['G_' . $row['group_name']]))? $user->lang['G_' . $row['group_name']] : $row['group_name'];
				
				$template->assign_block_vars('groups', array(
					'U_LIST'		=> "{$u_action}&amp;action=list&amp;g=$group_id",
					'U_DEFAULT'		=> "{$u_action}&amp;action=default&amp;g=$group_id",
					'U_EDIT'		=> "{$u_action}&amp;action=edit&amp;g=$group_id",
					'U_DELETE'		=> "{$u_action}&amp;action=delete&amp;g=$group_id",

					'S_GROUP_SPECIAL'	=> ($row['group_type'] == GROUP_SPECIAL) ? true : false,
					
					'GROUP_NAME'	=> $group_name,
					'TOTAL_MEMBERS'	=> $row['total_members'],
					)
				);
			}
		}
	}
}

/**
* @package module_install
*/
class acp_groups_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_groups',
			'title'		=> 'ACP_GROUPS_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'		=> array('title' => 'ACP_GROUPS_MANAGE', 'auth' => 'acl_a_group'),
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