<?php
/**
*
* @package acp
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_groups
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $cache;
		global $phpbb_root_path, $phpbb_admin_path, $phpEx, $table_prefix, $file_uploads;
		global $request;

		$user->add_lang('acp/groups');
		$this->tpl_name = 'acp_groups';
		$this->page_title = 'ACP_GROUPS_MANAGE';

		$form_key = 'acp_groups';
		add_form_key($form_key);

		if ($mode == 'position')
		{
			$this->manage_position();
			return;
		}

		include($phpbb_root_path . 'includes/functions_user.' . $phpEx);

		// Check and set some common vars
		$action		= (isset($_POST['add'])) ? 'add' : ((isset($_POST['addusers'])) ? 'addusers' : request_var('action', ''));
		$group_id	= request_var('g', 0);
		$mark_ary	= request_var('mark', array(0));
		$name_ary	= request_var('usernames', '', true);
		$leader		= request_var('leader', 0);
		$default	= request_var('default', 0);
		$start		= request_var('start', 0);
		$update		= (isset($_POST['update'])) ? true : false;


		// Clear some vars
		$can_upload = (file_exists($phpbb_root_path . $config['avatar_path']) && phpbb_is_writable($phpbb_root_path . $config['avatar_path']) && $file_uploads) ? true : false;
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
				trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// Check if the user is allowed to manage this group if set to founder only.
			if ($user->data['user_type'] != USER_FOUNDER && $group_row['group_founder_manage'])
			{
				trigger_error($user->lang['NOT_ALLOWED_MANAGE_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
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
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Approve, demote or promote
				$group_name = ($group_row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $group_row['group_name']] : $group_row['group_name'];
				$error = group_user_attributes($action, $group_id, $mark_ary, false, $group_name);

				if (!$error)
				{
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

					trigger_error($user->lang[$message] . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id));
				}
				else
				{
					trigger_error($user->lang[$error] . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id), E_USER_WARNING);
				}

			break;

			case 'default':
				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$group_name = ($group_row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $group_row['group_name']] : $group_row['group_name'];

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

								group_user_attributes('default', $group_id, $mark_ary, false, $group_name, $group_row);

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
						group_user_attributes('default', $group_id, $mark_ary, false, $group_name, $group_row);
					}

					trigger_error($user->lang['GROUP_DEFS_UPDATED'] . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id));
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
				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				else if ($action === 'delete' && $group_row['group_type'] == GROUP_SPECIAL)
				{
					trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$error = '';

					switch ($action)
					{
						case 'delete':
							if (!$auth->acl_get('a_groupdel'))
							{
								trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
							}

							$error = group_delete($group_id, $group_row['group_name']);
						break;

						case 'deleteusers':
							$group_name = ($group_row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $group_row['group_name']] : $group_row['group_name'];
							$error = group_user_del($group_id, $mark_ary, false, $group_name);
						break;
					}

					$back_link = ($action == 'delete') ? $this->u_action : $this->u_action . '&amp;action=list&amp;g=' . $group_id;

					if ($error)
					{
						trigger_error($user->lang[$error] . adm_back_link($back_link), E_USER_WARNING);
					}

					$message = ($action == 'delete') ? 'GROUP_DELETED' : 'GROUP_USERS_REMOVE';
					trigger_error($user->lang[$message] . adm_back_link($back_link));
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
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$name_ary)
				{
					trigger_error($user->lang['NO_USERS'] . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id), E_USER_WARNING);
				}

				$name_ary = array_unique(explode("\n", $name_ary));
				$group_name = ($group_row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $group_row['group_name']] : $group_row['group_name'];

				// Add user/s to group
				if ($error = group_user_add($group_id, false, $name_ary, $group_name, $default, $leader, 0, $group_row))
				{
					trigger_error($user->lang[$error] . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id), E_USER_WARNING);
				}

				$message = ($leader) ? 'GROUP_MODS_ADDED' : 'GROUP_USERS_ADDED';
				trigger_error($user->lang[$message] . adm_back_link($this->u_action . '&amp;action=list&amp;g=' . $group_id));
			break;

			case 'edit':
			case 'add':

				include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

				$data = $submit_ary = array();

				if ($action == 'edit' && !$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if ($action == 'add' && !$auth->acl_get('a_groupadd'))
				{
					trigger_error($user->lang['NO_AUTH_OPERATION'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$error = array();
				$user->add_lang('ucp');

				$avatar_select = basename(request_var('avatar_select', ''));
				$category = basename(request_var('category', ''));

				// Did we submit?
				if ($update)
				{
					if (!check_form_key($form_key))
					{
						trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					$group_name	= utf8_normalize_nfc(request_var('group_name', '', true));
					$group_desc = utf8_normalize_nfc(request_var('group_desc', '', true));
					$group_type	= request_var('group_type', GROUP_FREE);

					$allow_desc_bbcode	= request_var('desc_parse_bbcode', false);
					$allow_desc_urls	= request_var('desc_parse_urls', false);
					$allow_desc_smilies	= request_var('desc_parse_smilies', false);

					$data['uploadurl']	= request_var('uploadurl', '');
					$data['remotelink']	= request_var('remotelink', '');
					$data['width']		= request_var('width', '');
					$data['height']		= request_var('height', '');
					$delete				= request_var('delete', '');

					$submit_ary = array(
						'colour'			=> request_var('group_colour', ''),
						'rank'				=> request_var('group_rank', 0),
						'receive_pm'		=> isset($_REQUEST['group_receive_pm']) ? 1 : 0,
						'legend'			=> isset($_REQUEST['group_legend']) ? 1 : 0,
						'teampage'			=> isset($_REQUEST['group_teampage']) ? 1 : 0,
						'message_limit'		=> request_var('group_message_limit', 0),
						'max_recipients'	=> request_var('group_max_recipients', 0),
						'founder_manage'	=> 0,
						'skip_auth'			=> request_var('group_skip_auth', 0),
					);

					if ($user->data['user_type'] == USER_FOUNDER)
					{
						$submit_ary['founder_manage'] = isset($_REQUEST['group_founder_manage']) ? 1 : 0;
					}

					$uploadfile = $request->file('uploadfile');
					if (!empty($uploadfile['tmp_name']) || $data['uploadurl'] || $data['remotelink'])
					{
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

							if ((!empty($uploadfile['tmp_name']) || $data['uploadurl']) && $can_upload)
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
					else if ($data['width'] && $data['height'])
					{
						// Only update the dimensions?
						if ($config['avatar_max_width'] || $config['avatar_max_height'])
						{
							if ($data['width'] > $config['avatar_max_width'] || $data['height'] > $config['avatar_max_height'])
							{
								$error[] = phpbb_avatar_error_wrong_size($data['width'], $data['height']);
							}
						}

						if (!sizeof($error))
						{
							if ($config['avatar_min_width'] || $config['avatar_min_height'])
							{
								if ($data['width'] < $config['avatar_min_width'] || $data['height'] < $config['avatar_min_height'])
								{
									$error[] = phpbb_avatar_error_wrong_size($data['width'], $data['height']);
								}
							}
						}

						if (!sizeof($error))
						{
							$submit_ary['avatar_width'] = $data['width'];
							$submit_ary['avatar_height'] = $data['height'];
						}
					}

					if ((isset($submit_ary['avatar']) && $submit_ary['avatar'] && (!isset($group_row['group_avatar']))) || $delete)
					{
						if (isset($group_row['group_avatar']) && $group_row['group_avatar'])
						{
							avatar_delete('group', $group_row, true);
						}
					}

					// Validate the length of "Maximum number of allowed recipients per private message" setting.
					// We use 16777215 as a maximum because it matches MySQL unsigned mediumint maximum value
					// which is the lowest amongst DBMSes supported by phpBB3
					if ($max_recipients_error = validate_data($submit_ary, array('max_recipients' => array('num', false, 0, 16777215))))
					{
						// Replace "error" string with its real, localised form
						$error = array_merge($error, array_map(array(&$user, 'lang'), $max_recipients_error));
					}

					if (!sizeof($error))
					{
						// Only set the rank, colour, etc. if it's changed or if we're adding a new
						// group. This prevents existing group members being updated if no changes
						// were made.
						// However there are some attributes that need to be set everytime,
						// otherwise the group gets removed from the feature.
						$set_attributes = array('legend', 'teampage');

						$group_attributes = array();
						$test_variables = array(
							'rank'			=> 'int',
							'colour'		=> 'string',
							'avatar'		=> 'string',
							'avatar_type'	=> 'int',
							'avatar_width'	=> 'int',
							'avatar_height'	=> 'int',
							'receive_pm'	=> 'int',
							'legend'		=> 'int',
							'teampage'		=> 'int',
							'message_limit'	=> 'int',
							'max_recipients'=> 'int',
							'founder_manage'=> 'int',
							'skip_auth'		=> 'int',
						);

						foreach ($test_variables as $test => $type)
						{
							if (isset($submit_ary[$test]) && ($action == 'add' || $group_row['group_' . $test] != $submit_ary[$test] || isset($group_attributes['group_avatar']) && strpos($test, 'avatar') === 0 || in_array($test, $set_attributes)))
							{
								settype($submit_ary[$test], $type);
								$group_attributes['group_' . $test] = $group_row['group_' . $test] = $submit_ary[$test];
							}
						}

						if (!($error = group_create($group_id, $group_type, $group_name, $group_desc, $group_attributes, $allow_desc_bbcode, $allow_desc_urls, $allow_desc_smilies)))
						{
							$group_perm_from = request_var('group_perm_from', 0);

							// Copy permissions?
							// If the user has the a_authgroups permission and at least one additional permission ability set the permissions are fully transferred.
							// We do not limit on one auth category because this can lead to incomplete permissions being tricky to fix for the admin, roles being assigned or added non-default permissions.
							// Since the user only has the option to copy permissions from non leader managed groups this seems to be a good compromise.
							if ($group_perm_from && $action == 'add' && $auth->acl_get('a_authgroups') && $auth->acl_gets('a_aauth', 'a_fauth', 'a_mauth', 'a_uauth'))
							{
								$sql = 'SELECT group_founder_manage
									FROM ' . GROUPS_TABLE . '
									WHERE group_id = ' . $group_perm_from;
								$result = $db->sql_query($sql);
								$check_row = $db->sql_fetchrow($result);
								$db->sql_freeresult($result);

								// Check the group if non-founder
								if ($check_row && ($user->data['user_type'] == USER_FOUNDER || $check_row['group_founder_manage'] == 0))
								{
									// From the mysql documentation:
									// Prior to MySQL 4.0.14, the target table of the INSERT statement cannot appear in the FROM clause of the SELECT part of the query. This limitation is lifted in 4.0.14.
									// Due to this we stay on the safe side if we do the insertion "the manual way"

									// Copy permisisons from/to the acl groups table (only group_id gets changed)
									$sql = 'SELECT forum_id, auth_option_id, auth_role_id, auth_setting
										FROM ' . ACL_GROUPS_TABLE . '
										WHERE group_id = ' . $group_perm_from;
									$result = $db->sql_query($sql);

									$groups_sql_ary = array();
									while ($row = $db->sql_fetchrow($result))
									{
										$groups_sql_ary[] = array(
											'group_id'			=> (int) $group_id,
											'forum_id'			=> (int) $row['forum_id'],
											'auth_option_id'	=> (int) $row['auth_option_id'],
											'auth_role_id'		=> (int) $row['auth_role_id'],
											'auth_setting'		=> (int) $row['auth_setting']
										);
									}
									$db->sql_freeresult($result);

									// Now insert the data
									$db->sql_multi_insert(ACL_GROUPS_TABLE, $groups_sql_ary);

									$auth->acl_clear_prefetch();
								}
							}

							$cache->destroy('sql', GROUPS_TABLE);

							$message = ($action == 'edit') ? 'GROUP_UPDATED' : 'GROUP_CREATED';
							trigger_error($user->lang[$message] . adm_back_link($this->u_action));
						}
					}

					if (sizeof($error))
					{
						$group_rank = $submit_ary['rank'];

						$group_desc_data = array(
							'text'			=> $group_desc,
							'allow_bbcode'	=> $allow_desc_bbcode,
							'allow_smilies'	=> $allow_desc_smilies,
							'allow_urls'	=> $allow_desc_urls
						);
					}
				}
				else if (!$group_id)
				{
					$group_name = utf8_normalize_nfc(request_var('group_name', '', true));
					$group_desc_data = array(
						'text'			=> '',
						'allow_bbcode'	=> true,
						'allow_smilies'	=> true,
						'allow_urls'	=> true
					);
					$group_rank = 0;
					$group_type = GROUP_OPEN;
				}
				else
				{
					$group_name = $group_row['group_name'];
					$group_desc_data = generate_text_for_edit($group_row['group_desc'], $group_row['group_desc_uid'], $group_row['group_desc_options']);
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

				$avatar_img = (!empty($group_row['group_avatar'])) ? get_user_avatar($group_row['group_avatar'], $group_row['group_avatar_type'], $group_row['group_avatar_width'], $group_row['group_avatar_height'], 'GROUP_AVATAR') : '<img src="' . $phpbb_admin_path . 'images/no_avatar.gif" alt="" />';

				$display_gallery = (isset($_POST['display_gallery'])) ? true : false;

				if ($config['allow_avatar_local'] && $display_gallery)
				{
					avatar_gallery($category, $avatar_select, 4);
				}

				$back_link = request_var('back_link', '');

				switch ($back_link)
				{
					case 'acp_users_groups':
						$u_back = append_sid("{$phpbb_admin_path}index.$phpEx", 'i=users&amp;mode=groups&amp;u=' . request_var('u', 0));
					break;

					default:
						$u_back = $this->u_action;
					break;
				}

				$template->assign_vars(array(
					'S_EDIT'			=> true,
					'S_ADD_GROUP'		=> ($action == 'add') ? true : false,
					'S_GROUP_PERM'		=> ($action == 'add' && $auth->acl_get('a_authgroups') && $auth->acl_gets('a_aauth', 'a_fauth', 'a_mauth', 'a_uauth')) ? true : false,
					'S_INCLUDE_SWATCH'	=> true,
					'S_CAN_UPLOAD'		=> $can_upload,
					'S_ERROR'			=> (sizeof($error)) ? true : false,
					'S_SPECIAL_GROUP'	=> ($group_type == GROUP_SPECIAL) ? true : false,
					'S_DISPLAY_GALLERY'	=> ($config['allow_avatar_local'] && !$display_gallery) ? true : false,
					'S_IN_GALLERY'		=> ($config['allow_avatar_local'] && $display_gallery) ? true : false,
					'S_USER_FOUNDER'	=> ($user->data['user_type'] == USER_FOUNDER) ? true : false,

					'ERROR_MSG'				=> (sizeof($error)) ? implode('<br />', $error) : '',
					'GROUP_NAME'			=> ($group_type == GROUP_SPECIAL) ? $user->lang['G_' . $group_name] : $group_name,
					'GROUP_INTERNAL_NAME'	=> $group_name,
					'GROUP_DESC'			=> $group_desc_data['text'],
					'GROUP_RECEIVE_PM'		=> (isset($group_row['group_receive_pm']) && $group_row['group_receive_pm']) ? ' checked="checked"' : '',
					'GROUP_FOUNDER_MANAGE'	=> (isset($group_row['group_founder_manage']) && $group_row['group_founder_manage']) ? ' checked="checked"' : '',
					'GROUP_LEGEND'			=> (isset($group_row['group_legend']) && $group_row['group_legend']) ? ' checked="checked"' : '',
					'GROUP_TEAMPAGE'		=> (isset($group_row['group_teampage']) && $group_row['group_teampage']) ? ' checked="checked"' : '',
					'GROUP_MESSAGE_LIMIT'	=> (isset($group_row['group_message_limit'])) ? $group_row['group_message_limit'] : 0,
					'GROUP_MAX_RECIPIENTS'	=> (isset($group_row['group_max_recipients'])) ? $group_row['group_max_recipients'] : 0,
					'GROUP_COLOUR'			=> (isset($group_row['group_colour'])) ? $group_row['group_colour'] : '',
					'GROUP_SKIP_AUTH'		=> (!empty($group_row['group_skip_auth'])) ? ' checked="checked"' : '',

					'S_DESC_BBCODE_CHECKED'	=> $group_desc_data['allow_bbcode'],
					'S_DESC_URLS_CHECKED'	=> $group_desc_data['allow_urls'],
					'S_DESC_SMILIES_CHECKED'=> $group_desc_data['allow_smilies'],

					'S_RANK_OPTIONS'		=> $rank_options,
					'S_GROUP_OPTIONS'		=> group_select_options(false, false, (($user->data['user_type'] == USER_FOUNDER) ? false : 0)),
					'AVATAR'				=> $avatar_img,
					'AVATAR_IMAGE'			=> $avatar_img,
					'AVATAR_MAX_FILESIZE'	=> $config['avatar_filesize'],
					'AVATAR_WIDTH'			=> (isset($group_row['group_avatar_width'])) ? $group_row['group_avatar_width'] : '',
					'AVATAR_HEIGHT'			=> (isset($group_row['group_avatar_height'])) ? $group_row['group_avatar_height'] : '',

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
					'U_SWATCH'			=> append_sid("{$phpbb_admin_path}swatch.$phpEx", 'form=settings&amp;name=group_colour'),
					'U_ACTION'			=> "{$this->u_action}&amp;action=$action&amp;g=$group_id",
					'L_AVATAR_EXPLAIN'	=> phpbb_avatar_explanation_string(),
				));

				return;
			break;

			case 'list':

				if (!$group_id)
				{
					trigger_error($user->lang['NO_GROUP'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$this->page_title = 'GROUP_MEMBERS';

				// Grab the leaders - always, on every page...
				$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_regdate, u.user_colour, u.user_posts, u.group_id, ug.group_leader, ug.user_pending
					FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug
					WHERE ug.group_id = $group_id
						AND u.user_id = ug.user_id
						AND ug.group_leader = 1
					ORDER BY ug.group_leader DESC, ug.user_pending ASC, u.username_clean";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('leader', array(
						'U_USER_EDIT'		=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;action=edit&amp;u={$row['user_id']}"),

						'USERNAME'			=> $row['username'],
						'USERNAME_COLOUR'	=> $row['user_colour'],
						'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
						'JOINED'			=> ($row['user_regdate']) ? $user->format_date($row['user_regdate']) : ' - ',
						'USER_POSTS'		=> $row['user_posts'],
						'USER_ID'			=> $row['user_id'],
					));
				}
				$db->sql_freeresult($result);

				// Total number of group members (non-leaders)
				$sql = 'SELECT COUNT(user_id) AS total_members
					FROM ' . USER_GROUP_TABLE . "
					WHERE group_id = $group_id
						AND group_leader = 0";
				$result = $db->sql_query($sql);
				$total_members = (int) $db->sql_fetchfield('total_members');
				$db->sql_freeresult($result);

				$s_action_options = '';
				$options = array('default' => 'DEFAULT', 'approve' => 'APPROVE', 'demote' => 'DEMOTE', 'promote' => 'PROMOTE', 'deleteusers' => 'DELETE');

				foreach ($options as $option => $lang)
				{
					$s_action_options .= '<option value="' . $option . '">' . $user->lang['GROUP_' . $lang] . '</option>';
				}

				$base_url = $this->u_action . "&amp;action=$action&amp;g=$group_id";
				phpbb_generate_template_pagination($template, $base_url, 'pagination', 'start', $total_members, $config['topics_per_page'], $start);

				$template->assign_vars(array(
					'S_LIST'			=> true,
					'S_GROUP_SPECIAL'	=> ($group_row['group_type'] == GROUP_SPECIAL) ? true : false,
					'S_ACTION_OPTIONS'	=> $s_action_options,

					'S_ON_PAGE'		=> phpbb_on_page($template, $user, $base_url, $total_members, $config['topics_per_page'], $start),
					'GROUP_NAME'	=> ($group_row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $group_row['group_name']] : $group_row['group_name'],

					'U_ACTION'			=> $this->u_action . "&amp;g=$group_id",
					'U_BACK'			=> $this->u_action,
					'U_FIND_USERNAME'	=> append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=searchuser&amp;form=list&amp;field=usernames'),
					'U_DEFAULT_ALL'		=> "{$this->u_action}&amp;action=default&amp;g=$group_id",
				));

				// Grab the members
				$sql = 'SELECT u.user_id, u.username, u.username_clean, u.user_colour, u.user_regdate, u.user_posts, u.group_id, ug.group_leader, ug.user_pending
					FROM ' . USERS_TABLE . ' u, ' . USER_GROUP_TABLE . " ug
					WHERE ug.group_id = $group_id
						AND u.user_id = ug.user_id
						AND ug.group_leader = 0
					ORDER BY ug.group_leader DESC, ug.user_pending ASC, u.username_clean";
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
						'U_USER_EDIT'		=> append_sid("{$phpbb_admin_path}index.$phpEx", "i=users&amp;action=edit&amp;u={$row['user_id']}"),

						'USERNAME'			=> $row['username'],
						'USERNAME_COLOUR'	=> $row['user_colour'],
						'S_GROUP_DEFAULT'	=> ($row['group_id'] == $group_id) ? true : false,
						'JOINED'			=> ($row['user_regdate']) ? $user->format_date($row['user_regdate']) : ' - ',
						'USER_POSTS'		=> $row['user_posts'],
						'USER_ID'			=> $row['user_id'])
					);
				}
				$db->sql_freeresult($result);

				return;
			break;
		}

		$template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
			'S_GROUP_ADD'	=> ($auth->acl_get('a_groupadd')) ? true : false)
		);

		// Get us all the groups
		$sql = 'SELECT g.group_id, g.group_name, g.group_type
			FROM ' . GROUPS_TABLE . ' g
			ORDER BY g.group_type ASC, g.group_name';
		$result = $db->sql_query($sql);

		$lookup = $cached_group_data = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$type = ($row['group_type'] == GROUP_SPECIAL) ? 'special' : 'normal';

			// used to determine what type a group is
			$lookup[$row['group_id']] = $type;

			// used for easy access to the data within a group
			$cached_group_data[$type][$row['group_id']] = $row;
			$cached_group_data[$type][$row['group_id']]['total_members'] = 0;
		}
		$db->sql_freeresult($result);

		// How many people are in which group?
		$sql = 'SELECT COUNT(ug.user_id) AS total_members, ug.group_id
			FROM ' . USER_GROUP_TABLE . ' ug
			WHERE ' . $db->sql_in_set('ug.group_id', array_keys($lookup)) . '
			GROUP BY ug.group_id';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$type = $lookup[$row['group_id']];
			$cached_group_data[$type][$row['group_id']]['total_members'] = $row['total_members'];
		}
		$db->sql_freeresult($result);

		// The order is... normal, then special
		ksort($cached_group_data);

		foreach ($cached_group_data as $type => $row_ary)
		{
			if ($type == 'special')
			{
				$template->assign_block_vars('groups', array(
					'S_SPECIAL'			=> true)
				);
			}

			foreach ($row_ary as $group_id => $row)
			{
				$group_name = (!empty($user->lang['G_' . $row['group_name']]))? $user->lang['G_' . $row['group_name']] : $row['group_name'];

				$template->assign_block_vars('groups', array(
					'U_LIST'		=> "{$this->u_action}&amp;action=list&amp;g=$group_id",
					'U_EDIT'		=> "{$this->u_action}&amp;action=edit&amp;g=$group_id",
					'U_DELETE'		=> ($auth->acl_get('a_groupdel')) ? "{$this->u_action}&amp;action=delete&amp;g=$group_id" : '',

					'S_GROUP_SPECIAL'	=> ($row['group_type'] == GROUP_SPECIAL) ? true : false,

					'GROUP_NAME'	=> $group_name,
					'TOTAL_MEMBERS'	=> $row['total_members'],
				));
			}
		}
	}

	public function manage_position()
	{
		global $config, $db, $template, $user;

		$this->tpl_name = 'acp_groups_position';
		$this->page_title = 'ACP_GROUPS_POSITION';

		$field = request_var('field', '');
		$action = request_var('action', '');
		$group_id = request_var('g', 0);

		if ($field && !in_array($field, array('legend', 'teampage')))
		{
			// Invalid mode
			trigger_error($user->lang['NO_MODE'] . adm_back_link($this->u_action), E_USER_WARNING);
		}
		else if ($field)
		{
			$group_position = new phpbb_group_positions($db, $field, $this->u_action);
		}

		switch ($action)
		{
			case 'set_config_legend':
				set_config('legend_sort_groupname', request_var('legend_sort_groupname', 0));
			break;

			case 'set_config_teampage':
				set_config('teampage_forums', request_var('teampage_forums', 0));
				set_config('teampage_memberships', request_var('teampage_memberships', 0));
			break;

			case 'add':
				$group_position->add_group($group_id);
			break;

			case 'delete':
				$group_position->delete_group($group_id);
			break;

			case 'move_up':
				$group_position->move_up($group_id);
			break;

			case 'move_down':
				$group_position->move_down($group_id);
			break;
		}

		$sql = 'SELECT group_id, group_name, group_colour, group_type, group_legend
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_legend, group_name ASC';
		$result = $db->sql_query($sql);

		$s_group_select_legend = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];
			if ($row['group_legend'])
			{
				$template->assign_block_vars('legend', array(
					'GROUP_NAME' => $group_name,
					'GROUP_COLOUR' => ($row['group_colour']) ? ' style="color: #' . $row['group_colour'] . '"' : '',
					'GROUP_TYPE' => $user->lang[phpbb_group_positions::group_type_language($row['group_type'])],

					'U_MOVE_DOWN' => "{$this->u_action}&amp;field=legend&amp;action=move_down&amp;g=" . $row['group_id'],
					'U_MOVE_UP' => "{$this->u_action}&amp;field=legend&amp;action=move_up&amp;g=" . $row['group_id'],
					'U_DELETE' => "{$this->u_action}&amp;field=legend&amp;action=delete&amp;g=" . $row['group_id'],
				));
			}
			else
			{
				$s_group_select_legend .= '<option value="' . (int) $row['group_id'] . '">' . $group_name . '</option>';
			}
		}
		$db->sql_freeresult($result);

		$sql = 'SELECT group_id, group_name, group_colour, group_type, group_teampage
			FROM ' . GROUPS_TABLE . '
			ORDER BY group_teampage, group_name ASC';
		$result = $db->sql_query($sql);

		$s_group_select_teampage = '';
		while ($row = $db->sql_fetchrow($result))
		{
			$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $row['group_name']] : $row['group_name'];
			if ($row['group_teampage'])
			{
				$template->assign_block_vars('teampage', array(
					'GROUP_NAME' => $group_name,
					'GROUP_COLOUR' => ($row['group_colour']) ? ' style="color: #' . $row['group_colour'] . '"' : '',
					'GROUP_TYPE' => $user->lang[phpbb_group_positions::group_type_language($row['group_type'])],

					'U_MOVE_DOWN' => "{$this->u_action}&amp;field=teampage&amp;action=move_down&amp;g=" . $row['group_id'],
					'U_MOVE_UP' => "{$this->u_action}&amp;field=teampage&amp;action=move_up&amp;g=" . $row['group_id'],
					'U_DELETE' => "{$this->u_action}&amp;field=teampage&amp;action=delete&amp;g=" . $row['group_id'],
				));
			}
			else
			{
				$s_group_select_teampage .= '<option value="' . (int) $row['group_id'] . '">' . $group_name . '</option>';
			}
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'U_ACTION' => $this->u_action,
			'U_ACTION_LEGEND' => $this->u_action . '&amp;field=legend',
			'U_ACTION_TEAMPAGE' => $this->u_action . '&amp;field=teampage',

			'S_GROUP_SELECT_LEGEND'		=> $s_group_select_legend,
			'S_GROUP_SELECT_TEAMPAGE'	=> $s_group_select_teampage,
			'DISPLAY_FORUMS'			=> ($config['teampage_forums']) ? true : false,
			'DISPLAY_MEMBERSHIPS'		=> $config['teampage_memberships'],
			'LEGEND_SORT_GROUPNAME'		=> ($config['legend_sort_groupname']) ? true : false,
		));
	}
}
