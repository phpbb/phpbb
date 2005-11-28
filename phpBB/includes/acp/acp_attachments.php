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
class acp_attachments
{
	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $SID, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		$user->add_lang(array('posting', 'viewtopic', 'acp/attachments'));

		$error = $notify = array();
		$submit = (isset($_POST['submit'])) ? true : false;
		$action = request_var('action', '');

		switch ($mode)
		{
			case 'attach':
				$l_title = 'ACP_ATTACHMENT_SETTINGS';
			break;

			case 'extensions':
				$l_title = 'ACP_MANAGE_EXTENSIONS';
			break;

			case 'ext_groups':
				$l_title = 'ACP_EXTENSION_GROUPS';
			break;
	
			case 'orphan':
				$l_title = 'ACP_ORPHAN_ATTACHMENTS';
			break;

			default:
				trigger_error('NO_MODE');
		}

		$this->tpl_name = 'acp_attachments';
		$this->page_title = $l_title;

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$l_title],
			'L_TITLE_EXPLAIN'	=> $user->lang[$l_title . '_EXPLAIN'],
			'U_ACTION'			=> $u_action,
			)
		);

		switch ($mode)
		{
			case 'attach':

				include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		
				$config_sizes = array('max_filesize' => 'size', 'attachment_quota' => 'quota_size', 'max_filesize_pm' => 'pm_size');
				foreach ($config_sizes as $cfg_key => $var)
				{
					$$var = request_var($var, '');
				}

				// Pull all config data
				$sql = 'SELECT *
					FROM ' . CONFIG_TABLE;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$config_name = $row['config_name'];
					$config_value = $row['config_value'];

					$default_config[$config_name] = $config_value;
					$new[$config_name] = request_var($config_name, $default_config[$config_name]);

					foreach ($config_sizes as $cfg_key => $var)
					{
						if (empty($$var) && !$submit && $config_name == $cfg_key)
						{
							$$var = (intval($default_config[$config_name]) >= 1048576) ? 'mb' : ((intval($default_config[$config_name]) >= 1024) ? 'kb' : 'b');
						}

						if (!$submit && $config_name == $cfg_key)
						{
							$new[$config_name] = ($new[$config_name] >= 1048576) ? round($new[$config_name] / 1048576 * 100) / 100 : (($new[$config_name] >= 1024) ? round($new[$config_name] / 1024 * 100) / 100 : $new[$config_name]);
						}

						if ($submit && $config_name == $cfg_key)
						{
							$old = $new[$config_name];
							$new[$config_name] = ($$var == 'kb') ? round($new[$config_name] * 1024) : (($$var == 'mb') ? round($new[$config_name] * 1048576) : $new[$config_name]);
						}
					} 

					if ($submit)
					{
						set_config($config_name, $new[$config_name]);
				
						if (in_array($config_name, array('max_filesize', 'attachment_quota', 'max_filesize_pm')))
						{
							$new[$config_name] = $old;
						}
					}
				}
				$db->sql_freeresult($result);

				$this->perform_site_list();

				if ($submit)
				{
					add_log('admin', 'LOG_CONFIG_ATTACH');

					// Check Settings
					$this->test_upload($error, $new['upload_path'], false);

					if (!sizeof($error))
					{
						trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($u_action));
					}
				}

				$template->assign_var('S_ATTACHMENT_SETTINGS', true);
				
				if ($action == 'imgmagick')
				{
					$new['img_imagick'] = $this->search_imagemagick();
				}

				// We strip eventually manual added convert program, we only want the patch
				$new['img_imagick'] = str_replace(array('convert', '.exe'), array('', ''), $new['img_imagick']);

				$select_size_mode = size_select('size', $size);
				$select_quota_size_mode = size_select('quota_size', $quota_size);
				$select_pm_size_mode = size_select('pm_size', $pm_size);

				$sql = 'SELECT group_name, cat_id
					FROM ' . EXTENSION_GROUPS_TABLE . '
					WHERE cat_id > 0
					ORDER BY cat_id';
				$result = $db->sql_query($sql);

				$s_assigned_groups = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$s_assigned_groups[$row['cat_id']][] = $row['group_name'];
				}
				$db->sql_freeresult($result);

				$supported_types = get_supported_image_types();

				// Check Thumbnail Support
				if (!$new['img_imagick'] && (!isset($supported_types['format']) || !sizeof($supported_types['format'])))
				{
					$new['img_create_thumbnail'] = '0';
				}

				$template->assign_vars(array(
					'UPLOAD_PATH'			=> $new['upload_path'],
					'DISPLAY_ORDER'			=> $new['display_order'],
					'ATTACHMENT_QUOTA'		=> $new['attachment_quota'],
					'MAX_FILESIZE'			=> $new['max_filesize'],
					'MAX_PM_FILESIZE'		=> $new['max_filesize_pm'],
					'MAX_ATTACHMENTS'		=> $new['max_attachments'],
					'MAX_ATTACHMENTS_PM'	=> $new['max_attachments_pm'],
					'SECURE_DOWNLOADS'		=> $new['secure_downloads'],
					'SECURE_ALLOW_DENY'		=> $new['secure_allow_deny'],
					'ALLOW_EMPTY_REFERER'	=> $new['secure_allow_empty_referer'],
					'ASSIGNED_GROUPS'		=> (sizeof($s_assigned_groups[ATTACHMENT_CATEGORY_IMAGE])) ? implode(', ', $s_assigned_groups[ATTACHMENT_CATEGORY_IMAGE]) : $user->lang['NONE'],
					'DISPLAY_INLINED'		=> $new['img_display_inlined'],
					'CREATE_THUMBNAIL'		=> $new['img_create_thumbnail'],
					'MIN_THUMB_FILESIZE'	=> $new['img_min_thumb_filesize'],
					'IMG_IMAGICK'			=> $new['img_imagick'],
					'MAX_WIDTH'				=> $new['img_max_width'],
					'MAX_HEIGHT'			=> $new['img_max_height'],
					'LINK_WIDTH'			=> $new['img_link_width'],
					'LINK_HEIGHT'			=> $new['img_link_height'],

					'U_SEARCH_IMAGICK'		=> $u_action . '&amp;action=imgmagick',

					'S_QUOTA_SELECT'			=> $select_quota_size_mode,
					'S_MAX_FILESIZE_SELECT'		=> $select_size_mode,
					'S_MAX_PM_FILESIZE_SELECT'	=> $select_pm_size_mode,
					'S_THUMBNAIL_SUPPORT'		=> (!$new['img_imagick'] && (!isset($supported_types['format']) || !sizeof($supported_types['format']))) ? false : true,
					)
				);

				// Secure Download Options - Same procedure as with banning
				$allow_deny = ($new['secure_allow_deny']) ? 'ALLOWED' : 'DISALLOWED';
		
				$sql = 'SELECT *
					FROM ' . SITELIST_TABLE;
				$result = $db->sql_query($sql);

				$defined_ips = '';
				$ips = array();

				while ($row = $db->sql_fetchrow($result))
				{
					$value = ($row['site_ip']) ? $row['site_ip'] : $row['site_hostname'];
					if ($value)
					{
						$defined_ips .=  '<option' . (($row['ip_exclude']) ? ' class="sep"' : '') . ' value="' . $row['site_id'] . '">' . $value . '</option>';
						$ips[$row['site_id']] = $value;
					}
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'S_SECURE_DOWNLOADS'	=> $new['secure_downloads'],
					'S_DEFINED_IPS'			=> ($defined_ips != '') ? true : false,

					'DEFINED_IPS'			=> $defined_ips,

					'L_SECURE_TITLE'		=> $user->lang['DEFINE_' . $allow_deny . '_IPS'],
					'L_IP_EXCLUDE'			=> $user->lang['EXCLUDE_FROM_' . $allow_deny . '_IP'],
					'L_REMOVE_IPS'			=> $user->lang['REMOVE_' . $allow_deny . '_IPS'],
					)
				);

			break;

			case 'extensions':

				if ($submit || isset($_POST['add_extension_check']))
				{
					if ($submit)
					{

						// Change Extensions ?
						$extension_change_list	= (isset($_POST['extension_change_list'])) ? array_map('intval', $_POST['extension_change_list']) : array();
						$group_select_list		= (isset($_POST['group_select'])) ? array_map('intval', $_POST['group_select']) : array();

						// Generate correct Change List
						$extensions = array();

						for ($i = 0, $size = sizeof($extension_change_list); $i < $size; $i++)
						{
							$extensions[$extension_change_list[$i]]['group_id'] = $group_select_list[$i];
						}

						$sql = 'SELECT *
							FROM ' . EXTENSIONS_TABLE . '
							ORDER BY extension_id';
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['group_id'] != $extensions[$row['extension_id']]['group_id'])
							{
								$sql = 'UPDATE ' . EXTENSIONS_TABLE . ' 
									SET group_id = ' . (int) $extensions[$row['extension_id']]['group_id'] . '
									WHERE extension_id = ' . $row['extension_id'];
								$db->sql_query($sql);	
								add_log('admin', 'LOG_ATTACH_EXT_UPDATE', $row['extension']);
							}
						}
						$db->sql_freeresult($result);

						// Delete Extension ?
						$extension_id_list = (isset($_POST['extension_id_list'])) ? array_map('intval', $_POST['extension_id_list']) : array();

						if (sizeof($extension_id_list))
						{
							$sql = 'SELECT extension 
								FROM ' . EXTENSIONS_TABLE . '
								WHERE extension_id IN (' . implode(', ', $extension_id_list) . ')';
							$result = $db->sql_query($sql);
							
							$extension_list = '';
							while ($row = $db->sql_fetchrow($result))
							{
								$extension_list .= ($extension_list == '') ? $row['extension'] : ', ' . $row['extension'];
							}
							$db->sql_freeresult($result);

							$sql = 'DELETE 
								FROM ' . EXTENSIONS_TABLE . '
								WHERE extension_id IN (' . implode(', ', $extension_id_list) . ')';
							$db->sql_query($sql);

							add_log('admin', 'LOG_ATTACH_EXT_DEL', $extension_list);
						}
					}
					
					// Add Extension ?
					$add_extension			= strtolower(request_var('add_extension', ''));
					$add_extension_group	= request_var('add_group_select', 0);
					$add					= (isset($_POST['add_extension_check'])) ? true : false;

					if ($add_extension != '' && $add)
					{
						if (!sizeof($error))
						{
							$sql = 'SELECT extension_id
								FROM ' . EXTENSIONS_TABLE . "
								WHERE extension = '" . $db->sql_escape($add_extension) . "'";
							$result = $db->sql_query($sql);
							
							if ($row = $db->sql_fetchrow($result))
							{
								$error[] = sprintf($user->lang['EXTENSION_EXIST'], $add_extension);
							}
							$db->sql_freeresult($result);

							if (!sizeof($error))
							{
								$sql_ary = array(
									'group_id'	=>	$add_extension_group,
									'extension'	=>	$add_extension
								);
								
								$db->sql_query('INSERT INTO ' . EXTENSIONS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
								add_log('admin', 'LOG_ATTACH_EXT_ADD', $add_extension);
							}
						}
					}

					if (!sizeof($error))
					{
						$notify[] = $user->lang['EXTENSIONS_UPDATED'];
					}
					
					$cache->destroy('_extensions');
				}

				$template->assign_vars(array(
					'S_EXTENSIONS'			=> true,
					'ADD_EXTENSION'			=> (isset($add_extension)) ? $add_extension : '',
					'GROUP_SELECT_OPTIONS'	=> (isset($_POST['add_extension_check'])) ? $this->group_select('add_group_select', $add_extension_group, 'extension_group') : $this->group_select('add_group_select', false, 'extension_group'))
				);

				$sql = 'SELECT * 
					FROM ' . EXTENSIONS_TABLE . ' 
					ORDER BY group_id, extension';
				$result = $db->sql_query($sql);

				if ($row = $db->sql_fetchrow($result))
				{
					$old_group_id = $row['group_id'];
					do
					{
						$s_spacer = false;

						$current_group_id = $row['group_id'];
						if ($old_group_id != $current_group_id)
						{
							$s_spacer = true;
							$old_group_id = $current_group_id;
						}

						$template->assign_block_vars('extensions', array(
							'S_SPACER'		=> $s_spacer,
							'EXTENSION_ID'	=> $row['extension_id'],
							'EXTENSION'		=> $row['extension'],
							'GROUP_OPTIONS'	=> $this->group_select('group_select[]', $row['group_id']))
						);
					}
					while ($row = $db->sql_fetchrow($result));
				}
				$db->sql_freeresult($result);

			break;

			case 'ext_groups':

				$template->assign_var('S_EXTENSION_GROUPS', true);

				if ($submit)
				{
					$action = request_var('action', '');
					$group_id = request_var('g', 0);
					
					if ($action != 'add' && $action != 'edit')
					{
						trigger_error('WRONG_MODE');
					}

					if (!$group_id && $action == 'edit')
					{
						trigger_error('NO_EXT_GROUP_SPECIFIED');
					}

					if ($group_id)
					{
						$sql = 'SELECT * FROM ' . EXTENSION_GROUPS_TABLE . "
							WHERE group_id = $group_id";
						$result = $db->sql_query($sql);
						$ext_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);
					}
					else
					{
						$ext_row = array();
					}

					$group_name = request_var('group_name', '');
					$new_group_name = ($action == 'add') ? $group_name : (($ext_row['group_name'] != $group_name) ? $group_name : '');

					if (!$group_name)
					{
						$error[] = $user->lang['NO_EXT_GROUP_NAME'];
					}

					// Check New Group Name
					if ($new_group_name)
					{
						$sql = 'SELECT group_id 
							FROM ' . EXTENSION_GROUPS_TABLE . "
							WHERE LOWER(group_name) = '" . $db->sql_escape(strtolower($new_group_name)) . "'";
						$result = $db->sql_query($sql);
						if ($db->sql_fetchrow($result))
						{
							$error[] = sprintf($user->lang['EXTENSION_GROUP_EXIST'], $new_group_name);
						}
						$db->sql_freeresult($result);
					}

					if (!sizeof($error))
					{
						// Ok, build the update/insert array
						$upload_icon	= request_var('upload_icon', 'no_image');
						$size_select	= request_var('size_select', 'b');
						$forum_select	= request_var('forum_select', false);
						$allowed_forums	= isset($_POST['allowed_forums']) ? array_map('intval', array_values($_POST['allowed_forums'])) : array();
						$allow_in_pm	= isset($_POST['allow_in_pm']) ? true : false;
						$max_filesize	= request_var('max_filesize', 0);
						$max_filesize	= ($size_select == 'kb') ? round($max_filesize * 1024) : (($size_select == 'mb') ? round($max_filesize * 1048576) : $max_filesize);

						if ($max_filesize == $config['max_filesize'])
						{
							$max_filesize = 0;
						}	

						if (!sizeof($allowed_forums))
						{
							$forum_select = false;
						}

						$group_ary = array(
							'group_name'	=> $group_name,
							'cat_id'		=> request_var('special_category', ATTACHMENT_CATEGORY_NONE),
							'allow_group'	=> (isset($_POST['allow_group'])) ? 1 : 0,
							'download_mode'	=> request_var('download_mode', INLINE_LINK),
							'upload_icon'	=> ($upload_icon == 'no_image') ? '' : $upload_icon,
							'max_filesize'	=> $max_filesize,
							'allowed_forums'=> ($forum_select) ? serialize($allowed_forums) : '',
							'allow_in_pm'	=> ($allow_in_pm) ? 1 : 0
						);

						$sql = ($action == 'add') ? 'INSERT INTO ' . EXTENSION_GROUPS_TABLE . ' ' : 'UPDATE ' . EXTENSION_GROUPS_TABLE . ' SET ';
						$sql .= $db->sql_build_array((($action == 'add') ? 'INSERT' : 'UPDATE'), $group_ary);
						$sql .= ($action == 'edit') ? " WHERE group_id = $group_id" : '';

						$db->sql_query($sql);
						
						if ($action == 'add')
						{
							$group_id = $db->sql_nextid();
						}

						add_log('admin', 'LOG_ATTACH_EXTGROUP_' . strtoupper($action), $group_name);
					}

					$extension_list = isset($_REQUEST['extensions']) ? array_map('intval', array_values($_REQUEST['extensions'])) : array();

					if ($action == 'edit' && sizeof($extension_list))
					{
						$sql = 'UPDATE ' . EXTENSIONS_TABLE . "
							SET group_id = 0
							WHERE group_id = $group_id";
						$db->sql_query($sql);
					}

					if (sizeof($extension_list))
					{
						$sql = 'UPDATE ' . EXTENSIONS_TABLE . " 
							SET group_id = $group_id
							WHERE extension_id IN (" . implode(', ', $extension_list) . ")";
						$db->sql_query($sql);
					}

					$this->rewrite_extensions();

					if (!sizeof($error))
					{
						$notify[] = $user->lang['SUCCESS_EXTENSION_GROUP_' . strtoupper($action)];
					}
				}
			
				$cat_lang = array(
					ATTACHMENT_CATEGORY_NONE	=> $user->lang['NONE'],
					ATTACHMENT_CATEGORY_IMAGE	=> $user->lang['CAT_IMAGES'],
					ATTACHMENT_CATEGORY_WM		=> $user->lang['CAT_WM_FILES'],
					ATTACHMENT_CATEGORY_RM		=> $user->lang['CAT_RM_FILES']
				);

				$group_id = request_var('g', 0);
				$action = (isset($_POST['add'])) ? 'add' : $action;
//				$action = (($action == 'add' || $action == 'edit') && $submit && !sizeof($error)) ? 'show' : $action;

				switch ($action)
				{
					case 'delete':

						if (confirm_box(true))
						{
							$sql = 'SELECT group_name 
								FROM ' . EXTENSION_GROUPS_TABLE . "
								WHERE group_id = $group_id";
							$result = $db->sql_query($sql);
							$group_name = $db->sql_fetchfield('group_name', 0, $result);
							$db->sql_freeresult($result);

							$sql = 'DELETE 
								FROM ' . EXTENSION_GROUPS_TABLE . " 
								WHERE group_id = $group_id";
							$db->sql_query($sql);

							// Set corresponding Extensions to a pending Group
							$sql = 'UPDATE ' . EXTENSIONS_TABLE . "
								SET group_id = 0
								WHERE group_id = $group_id";
							$db->sql_query($sql);
					
							add_log('admin', 'LOG_ATTACH_EXTGROUP_DEL', $group_name);

							$this->rewrite_extensions();

							trigger_error($user->lang['EXTENSION_GROUP_DELETED'] . adm_back_link($u_action));
						}
						else
						{
							confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action,
								'group_id'	=> $group_id,
								'action'	=> 'delete',
							)));
						}

					break;

					case 'edit':
					
						if (!$group_id)
						{
							trigger_error($user->lang['NO_EXTENSION_GROUP'] . adm_back_link($u_action));
						}

						$sql = 'SELECT * FROM ' . EXTENSION_GROUPS_TABLE . "
							WHERE group_id = $group_id";
						$result = $db->sql_query($sql);
						$ext_group_row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						$forum_ids = (!$ext_group_row['allowed_forums']) ? array() : unserialize(trim($ext_group_row['allowed_forums']));

					case 'add':
						
						if ($action == 'add')
						{
							$ext_group_row = array(
								'group_name'	=> request_var('group_name', ''),
								'cat_id'		=> 0,
								'allow_group'	=> 1,
								'allow_in_pm'	=> 1,
								'download_mode'	=> 1,
								'upload_icon'	=> '',
								'max_filesize'	=> 0,
							);
							
							$forum_ids = array();
						}

						$extensions = array();

						$sql = 'SELECT * FROM ' . EXTENSIONS_TABLE . "
							WHERE group_id = $group_id OR group_id = 0
							ORDER BY extension";
						$result = $db->sql_query($sql);
						$extensions = $db->sql_fetchrowset($result);
						$db->sql_freeresult($result);

						if ($ext_group_row['max_filesize'] == 0)
						{
							$ext_group_row['max_filesize'] = (int) $config['max_filesize'];
						}

						$size_format = ($ext_group_row['max_filesize'] >= 1048576) ? 'mb' : (($ext_group_row['max_filesize'] >= 1024) ? 'kb' : 'b');

						$ext_group_row['max_filesize'] = ($ext_group_row['max_filesize'] >= 1048576) ? round($ext_group_row['max_filesize'] / 1048576 * 100) / 100 : (($ext_group_row['max_filesize'] >= 1024) ? round($ext_group_row['max_filesize'] / 1024 * 100) / 100 : $ext_group_row['max_filesize']);

						$img_path = $config['upload_icons_path'];

						$imglist = filelist($phpbb_root_path . $img_path);
						$imglist = array_values($imglist);
						$imglist = $imglist[0];

						$filename_list = '';
						$no_image_select = false;
						foreach ($imglist as $key => $img)
						{
							if (!$ext_group_row['upload_icon'])
							{
								$no_image_select = true;
								$selected = '';
							}
							else
							{
								$selected = ($ext_group_row['upload_icon'] == $img) ? ' selected="selected"' : '';
							}

							$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . htmlspecialchars($img) . '</option>';
						}

						$i = 0;
						$assigned_extensions = '';
						foreach ($extensions as $num => $row)
						{
							if ($row['group_id'] == $group_id && $group_id)
							{
								$assigned_extensions .= ($i) ? ', ' . $row['extension'] : $row['extension'];
								$i++;
							}
						}

						$s_extension_options = '';
						foreach ($extensions as $row)
						{
							$s_extension_options .= '<option' . ((!$row['group_id']) ? ' class="disabled"' : '') . ' value="' . $row['extension_id'] . '"' . (($row['group_id'] == $group_id && $group_id) ? ' selected="selected"' : '') . '>' . $row['extension'] . '</option>';
						}

						$template->assign_vars(array(
							'PHPBB_ROOT_PATH'	=> $phpbb_root_path,
							'IMG_PATH'			=> $img_path,
							'ACTION'			=> $action,
							'GROUP_ID'			=> $group_id,
							'GROUP_NAME'		=> $ext_group_row['group_name'],
							'ALLOW_GROUP'		=> $ext_group_row['allow_group'],
							'ALLOW_IN_PM'		=> $ext_group_row['allow_in_pm'],
							'UPLOAD_ICON_SRC'	=> $phpbb_root_path . $img_path . '/' . $ext_group_row['upload_icon'],
							'EXTGROUP_FILESIZE'	=> $ext_group_row['max_filesize'],
							'ASSIGNED_EXTENSIONS'	=> $assigned_extensions,
							
							'S_CATEGORY_SELECT'	=> $this->category_select('special_category', $group_id, 'category'),
							'S_DOWNLOAD_SELECT'	=> $this->download_select('download_mode', $group_id, 'download_mode'),
							'S_EXT_GROUP_SIZE'	=> size_select('size_select', $size_format),
							'S_EXTENSION_OPTIONS'	=> $s_extension_options,
							'S_FILENAME_LIST'	=> $filename_list,
							'S_EDIT_GROUP'		=> true,
							'S_NO_IMAGE'		=> $no_image_select,
							'S_FORUM_IDS'		=> (sizeof($forum_ids)) ? true : false,

							'U_EXTENSIONS'		=> $phpbb_admin_path . "index.$phpEx$SID&amp;i=$id&amp;mode=extensions",

							'L_LEGEND'			=> $user->lang[strtoupper($action) . '_EXTENSION_GROUP'],
							)
						);

						$s_forum_id_options = '';

						$sql = 'SELECT forum_id, forum_name, parent_id, forum_type, left_id, right_id
							FROM ' . FORUMS_TABLE . '
							ORDER BY left_id ASC';
						$result = $db->sql_query($sql);

						$right = $cat_right = $padding_inc = 0;
						$padding = $forum_list = $holding = '';
						$padding_store = array('0' => '');
						while ($row = $db->sql_fetchrow($result))
						{
							if ($row['forum_type'] == FORUM_CAT && ($row['left_id'] + 1 == $row['right_id']))
							{
								// Non-postable forum with no subforums, don't display
								continue;
							}

							if (!$auth->acl_get('f_list', $row['forum_id']))
							{
								// if the user does not have permissions to list this forum skip
								continue;
							}

							if ($row['left_id'] < $right)
							{
								$padding .= '&nbsp; &nbsp;';
								$padding_store[$row['parent_id']] = $padding;
							}
							else if ($row['left_id'] > $right + 1)
							{
								$padding = $padding_store[$row['parent_id']];
							}

							$right = $row['right_id'];

							$selected = (in_array($row['forum_id'], $forum_ids)) ? ' selected="selected"' : '';

							if ($row['left_id'] > $cat_right)
							{
								$holding = '';
							}

							if ($row['right_id'] - $row['left_id'] > 1)
							{
								$cat_right = max($cat_right, $row['right_id']);

								$holding .= '<option value="' . $row['forum_id'] . '"' . (($row['forum_type'] == FORUM_POST) ? ' class="blue"' : '') . $selected . '>' . $padding . $row['forum_name'] . '</option>';
							}
							else
							{
								$s_forum_id_options .= $holding . '<option value="' . $row['forum_id'] . '"' . (($row['forum_type'] == FORUM_POST) ? ' class="blue"' : '') . $selected . '>' . $padding . $row['forum_name'] . '</option>';
								$holding = '';
							}
						}
						$db->sql_freeresult($result);
						unset($padding_store);

						$template->assign_vars(array(
							'S_FORUM_ID_OPTIONS'	=> $s_forum_id_options)
						);
					
					break;

					case 'deactivate':
					case 'activate':
				
						if (!$group_id)
						{
							trigger_error($user->lang['NO_EXTENSION_GROUP'] . adm_back_link($u_action));
						}

						$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . '
							SET allow_group = ' . (($action == 'activate') ? '1' : '0') . "
							WHERE group_id = $group_id";
						$db->sql_query($sql);

						$this->rewrite_extensions();

					break;
				}

				$sql = 'SELECT *
					FROM ' . EXTENSION_GROUPS_TABLE . '
					ORDER BY allow_group DESC, group_name';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$s_add_spacer = ($row['allow_group'] == 0 && $act_deact == 'deactivate') ? true : false;
					
					$act_deact = ($row['allow_group']) ? 'deactivate' : 'activate';
			
					$template->assign_block_vars('groups', array(
						'S_ADD_SPACER'	=> $s_add_spacer,

						'U_EDIT'		=> $u_action . "&amp;action=edit&amp;g={$row['group_id']}",
						'U_DELETE'		=> $u_action . "&amp;action=delete&amp;g={$row['group_id']}",
						'U_ACT_DEACT'	=> $u_action . "&amp;action=$act_deact&amp;g={$row['group_id']}",
						
						'L_ACT_DEACT'	=> $user->lang[strtoupper($act_deact)],
						'GROUP_NAME'	=> $row['group_name'],
						'CATEGORY'		=> $cat_lang[$row['cat_id']],
						)
					);

				}
				$db->sql_freeresult($result);

			break;

			case 'orphan':

				if ($submit)
				{

					$delete_files = (isset($_POST['delete'])) ? array_keys(request_var('delete', array('' => 0))) : array();
					$add_files = (isset($_POST['add'])) ? array_keys(request_var('add', array('' => 0))) : array();
					$post_ids = request_var('post_id', array('' => 0));

					foreach ($delete_files as $delete)
					{
						phpbb_unlink($delete);
						phpbb_unlink($delete, 'thumbnail');
					}

					if (sizeof($delete_files))
					{
						add_log('admin', sprintf($user->lang['LOG_ATTACH_ORPHAN_DEL'], implode(', ', $delete_files)));
						$notify[] = sprintf($user->lang['LOG_ATTACH_ORPHAN_DEL'], implode(', ', $delete_files));
					}

					$upload_list = array();
					foreach ($add_files as $file)
					{
						if (!in_array($file, $delete_files) && $post_ids[$file])
						{
							$upload_list[$post_ids[$file]] = $file;
						}
					}
					unset($add_files);

					if (sizeof($upload_list))
					{
						$template->assign_var('S_UPLOADING_FILES', true);

						include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
						$message_parser = new parse_message();

						$sql = 'SELECT forum_id, forum_name
							FROM ' . FORUMS_TABLE;
						$result = $db->sql_query($sql);
						
						$forum_names = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$forum_names[$row['forum_id']] = $row['forum_name'];
						}
						$db->sql_freeresult($result);

						$sql = 'SELECT forum_id, topic_id, post_id 
							FROM ' . POSTS_TABLE . '
							WHERE post_id IN (' . implode(', ', array_keys($upload_list)) . ')';
						$result = $db->sql_query($sql);

						while ($row = $db->sql_fetchrow($result))
						{
							$return = true;

							if ($auth->acl_gets('f_attach', 'u_attach', $row['forum_id']))
							{
								$return = $this->upload_file($row['post_id'], $row['topic_id'], $row['forum_id'], $config['upload_path'], $upload_list[$row['post_id']]);
							}
				
							$template->assign_block_vars('upload', array(
								'FILE_INFO'		=> sprintf($user->lang['UPLOADING_FILE_TO'], $upload_list[$row['post_id']], $row['post_id']),
								'S_DENIED'		=> (!$auth->acl_gets('f_attach', 'u_attach', $row['forum_id'])) ? true : false,
								'L_DENIED'		=> (!$auth->acl_gets('f_attach', 'u_attach', $row['forum_id'])) ? sprintf($user->lang['UPLOAD_DENIED_FORUM'], $forum_names[$row['forum_id']]) : '',
								'ERROR_MSG'		=> ($return === true) ? false : $return)
							);
						}
						$db->sql_freeresult($result);

						unset($message_parser);
					}
				}

				$template->assign_vars(array(
					'S_ORPHAN'		=> true)
				);
				
				$attach_filelist = array();

				$dir = @opendir($phpbb_root_path . $config['upload_path']);
				while ($file = @readdir($dir))
				{
					if (is_file($phpbb_root_path . $config['upload_path'] . '/' . $file) && filesize($phpbb_root_path . $config['upload_path'] . '/' . $file) && $file{0} != '.' && $file != 'index.htm' && !preg_match('#^thumb\_#', $file))
					{
						$attach_filelist[$file] = $file;
					}
				}
				@closedir($dir);

				$sql = 'SELECT physical_filename 
					FROM ' . ATTACHMENTS_TABLE;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					unset($attach_filelist[$row['physical_filename']]);
				}
				$db->sql_freeresult($result);

				$i = 0;
				foreach ($attach_filelist as $file)
				{
					$filesize = @filesize($phpbb_root_path . $config['upload_path'] . '/' . $file);
					$size_lang = ($filesize >= 1048576) ? $user->lang['MB'] : ( ($filesize >= 1024) ? $user->lang['KB'] : $user->lang['BYTES'] );
					$filesize = ($filesize >= 1048576) ? round((round($filesize / 1048576 * 100) / 100), 2) : (($filesize >= 1024) ? round((round($filesize / 1024 * 100) / 100), 2) : $filesize);

					$template->assign_block_vars('orphan', array(
						'FILESIZE'		=> $filesize . ' ' . $size_lang,
						'U_FILE'		=> $phpbb_root_path . $config['upload_path'] . '/' . $file,
						'FILE'			=> $file,
						'POST_IDS'		=> (!empty($post_ids[$file])) ? $post_ids[$file] : '')
					);
				}
				
			break;
		}

		if (sizeof($error))
		{
			$template->assign_vars(array(
				'S_WARNING'		=> true,
				'WARNING_MSG'	=> implode('<br />', $error))
			);
		}

		if (sizeof($notify))
		{
			$template->assign_vars(array(
				'S_NOTIFY'		=> true,
				'NOTIFY_MSG'	=> implode('<br />', $notify))
			);
		}

	}

	/**
	* Build Select for category items
	*/
	function category_select($select_name, $group_id = false, $key = '')
	{
		global $db, $user;

		$types = array(
			ATTACHMENT_CATEGORY_NONE	=> $user->lang['NONE'],
			ATTACHMENT_CATEGORY_IMAGE	=> $user->lang['CAT_IMAGES'],
			ATTACHMENT_CATEGORY_WM		=> $user->lang['CAT_WM_FILES'],
			ATTACHMENT_CATEGORY_RM		=> $user->lang['CAT_RM_FILES']
		);
		
		if ($group_id)
		{
			$sql = 'SELECT cat_id
				FROM ' . EXTENSION_GROUPS_TABLE . '
				WHERE group_id = ' . (int) $group_id;
			$result = $db->sql_query($sql);
			
			$cat_type = (!($row = $db->sql_fetchrow($result))) ? ATTACHMENT_CATEGORY_NONE : $row['cat_id'];

			$db->sql_freeresult($result);
		}
		else
		{
			$cat_type = ATTACHMENT_CATEGORY_NONE;
		}
		
		$group_select = '<select name="' . $select_name . '"' . (($key) ? ' id="' . $key . '"' : '') . '>';

		foreach ($types as $type => $mode)
		{
			$selected = ($type == $cat_type) ? ' selected="selected"' : '';
			$group_select .= '<option value="' . $type . '"' . $selected . '>' . $mode . '</option>';
		}

		$group_select .= '</select>';

		return $group_select;
	}

	/**
	* Extension group select
	*/
	function group_select($select_name, $default_group = false, $key = '')
	{
		global $db, $user;
			
		$group_select = '<select name="' . $select_name . '"' . (($key) ? ' id="' . $key . '"' : '') . '>';

		$sql = 'SELECT group_id, group_name
			FROM ' . EXTENSION_GROUPS_TABLE . '
			ORDER BY group_name';
		$result = $db->sql_query($sql);

		$group_name = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$group_name[] = $row;
		}
		$db->sql_freeresult($result);

		$row['group_id'] = 0;
		$row['group_name'] = $user->lang['NOT_ASSIGNED'];
		$group_name[] = $row;
		
		for ($i = 0; $i < sizeof($group_name); $i++)
		{
			if ($default_group === false)
			{
				$selected = ($i == 0) ? ' selected="selected"' : '';
			}
			else
			{
				$selected = ($group_name[$i]['group_id'] == $default_group) ? ' selected="selected"' : '';
			}

			$group_select .= '<option value="' . $group_name[$i]['group_id'] . '"' . $selected . '>' . $group_name[$i]['group_name'] . '</option>';
		}

		$group_select .= '</select>';

		return $group_select;
	}

	/**
	* Build select for download modes
	*/
	function download_select($select_name, $group_id = false, $key = '')
	{
		global $db, $user;
			
		$types = array(
			INLINE_LINK => $user->lang['MODE_INLINE'],
			PHYSICAL_LINK => $user->lang['MODE_PHYSICAL']
		);
		
		if ($group_id)
		{
			$sql = "SELECT download_mode
				FROM " . EXTENSION_GROUPS_TABLE . "
				WHERE group_id = " . (int) $group_id;
			$result = $db->sql_query($sql);
			
			$download_mode = (!($row = $db->sql_fetchrow($result))) ? INLINE_LINK : $row['download_mode'];

			$db->sql_freeresult($result);
		}
		else
		{
			$download_mode = INLINE_LINK;
		}

		$group_select = '<select name="' . $select_name . '"' . (($key) ? ' id="' . $key . '"' : '') . '>';

		foreach ($types as $type => $mode)
		{
			$selected = ($type == $download_mode) ? ' selected="selected"' : '';
			$group_select .= '<option value="' . $type . '"' . $selected . '>' . $mode . '</option>';
		}

		$group_select .= '</select>';

		return $group_select;
	}

	/** 
	* Upload already uploaded file... huh? are you kidding?
	* @todo integrate into upload class
	*/
	function upload_file($post_id, $topic_id, $forum_id, $upload_dir, $filename)
	{
		global $message_parser, $db, $user, $phpbb_root_path;

		$message_parser->attachment_data = array();

		$message_parser->filename_data['filecomment'] = '';
		$message_parser->filename_data['filename'] = $phpbb_root_path . $upload_dir . '/' . basename($filename);

		$filedata = upload_attachment('local', $forum_id, true, $phpbb_root_path . $upload_dir . '/' . basename($filename));

		if ($filedata['post_attach'] && !sizeof($filedata['error']))
		{
			$message_parser->attachment_data = array(
				'post_msg_id'		=> $post_id,
				'poster_id'			=> $user->data['user_id'],
				'topic_id'			=> $topic_id,
				'in_message'		=> 0,
				'physical_filename'	=> $filedata['physical_filename'],
				'real_filename'		=> $filedata['real_filename'],
				'comment'			=> $message_parser->filename_data['filecomment'],
				'extension'			=> $filedata['extension'],
				'mimetype'			=> $filedata['mimetype'],
				'filesize'			=> $filedata['filesize'],
				'filetime'			=> $filedata['filetime'],
				'thumbnail'			=> $filedata['thumbnail']
			);

			$message_parser->filename_data['filecomment'] = '';
			$filedata['post_attach'] = false;

			// Submit Attachment
			$attach_sql = $message_parser->attachment_data;

			$db->sql_transaction();

			$sql = 'INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $attach_sql);
			$db->sql_query($sql);

			$sql = 'UPDATE ' . POSTS_TABLE . "
				SET post_attachment = 1
				WHERE post_id = $post_id";
			$db->sql_query($sql);

			$sql = 'UPDATE ' . TOPICS_TABLE . "
				SET topic_attachment = 1
				WHERE topic_id = $topic_id";
			$db->sql_query($sql);

			$db->sql_transaction('commit');

			add_log('admin', sprintf($user->lang['LOG_ATTACH_FILEUPLOAD'], $post_id, $filename));

			return true;
		}
		else if (sizeof($filedata['error']))
		{
			return sprintf($user->lang['ADMIN_UPLOAD_ERROR'], implode('<br />', $filedata['error']));
		}
	}

	/**
	* Search Imagick
	*/
	function search_imagemagick()
	{
		$imagick = '';
		
		$exe = ((defined('PHP_OS')) && (preg_match('#win#i', PHP_OS))) ? '.exe' : '';

		if (empty($_ENV['MAGICK_HOME']))
		{
			$locations = array('C:/WINDOWS/', 'C:/WINNT/', 'C:/WINDOWS/SYSTEM/', 'C:/WINNT/SYSTEM/', 'C:/WINDOWS/SYSTEM32/', 'C:/WINNT/SYSTEM32/', '/usr/bin/', '/usr/sbin/', '/usr/local/bin/', '/usr/local/sbin/', '/opt/', '/usr/imagemagick/', '/usr/bin/imagemagick/');

			foreach ($locations as $location)
			{
				if (@is_readable($location . 'mogrify' . $exe) && @filesize($location . 'mogrify' . $exe) > 3000)
				{
					$imagick = str_replace('\\', '/', $location);
					continue;
				}
			}
		}
		else
		{
			$imagick = str_replace('\\', '/', $_ENV['MAGICK_HOME']);
		}

		return $imagick;
	}

	/**
	* Test Settings
	*/
	function test_upload(&$error, $upload_dir, $create_directory = false)
	{
		global $user, $phpbb_root_path;

		// Does the target directory exist, is it a directory and writeable.
		if ($create_directory)
		{
			if (!file_exists($phpbb_root_path . $upload_dir))
			{
				@mkdir($phpbb_root_path . $upload_dir, 0777);
				@chmod($phpbb_root_path . $upload_dir, 0777);
			}
		}

		if (!file_exists($phpbb_root_path . $upload_dir))
		{
			$error[] = sprintf($user->lang['NO_UPLOAD_DIR'], $upload_dir);
			return;
		}
		
		if (!is_dir($phpbb_root_path . $upload_dir))
		{
			$error[] = sprintf($user->lang['UPLOAD_NOT_DIR'], $upload_dir);
			return;
		}
		
		if (!is_writable($phpbb_root_path . $upload_dir))
		{
			$error[] = sprintf($user->lang['NO_WRITE_UPLOAD'], $upload_dir);
			return;
		}
	}

	/**
	* Perform operations on sites for external linking
	*/
	function perform_site_list()
	{
		global $db, $user;

		if (isset($_REQUEST['securesubmit']))
		{
			// Grab the list of entries
			$ips = request_var('ips', '');
			$ip_list = array_unique(explode("\n", $ips));
			$ip_list_log = implode(', ', $ip_list);

			$ip_exclude = (!empty($_POST['ipexclude'])) ? 1 : 0;

			$iplist = array();
			$hostlist = array();

			foreach ($ip_list as $item)
			{
				if (preg_match('#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})[ ]*\-[ ]*([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#', trim($item), $ip_range_explode))
				{
					// Don't ask about all this, just don't ask ... !
					$ip_1_counter = $ip_range_explode[1];
					$ip_1_end = $ip_range_explode[5];

					while ($ip_1_counter <= $ip_1_end)
					{
						$ip_2_counter = ($ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[2] : 0;
						$ip_2_end = ($ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[6];

						if ($ip_2_counter == 0 && $ip_2_end == 254)
						{
							$ip_2_counter = 256;
							$ip_2_fragment = 256;

							$iplist[] = "'$ip_1_counter.*'";
						}

						while ($ip_2_counter <= $ip_2_end)
						{
							$ip_3_counter = ($ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[3] : 0;
							$ip_3_end = ($ip_2_counter < $ip_2_end || $ip_1_counter < $ip_1_end) ? 254 : $ip_range_explode[7];

							if ($ip_3_counter == 0 && $ip_3_end == 254)
							{
								$ip_3_counter = 256;
								$ip_3_fragment = 256;

								$iplist[] = "'$ip_1_counter.$ip_2_counter.*'";
							}

							while ($ip_3_counter <= $ip_3_end)
							{
								$ip_4_counter = ($ip_3_counter == $ip_range_explode[3] && $ip_2_counter == $ip_range_explode[2] && $ip_1_counter == $ip_range_explode[1]) ? $ip_range_explode[4] : 0;
								$ip_4_end = ($ip_3_counter < $ip_3_end || $ip_2_counter < $ip_2_end) ? 254 : $ip_range_explode[8];

								if ($ip_4_counter == 0 && $ip_4_end == 254)
								{
									$ip_4_counter = 256;
									$ip_4_fragment = 256;

									$iplist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.*'";
								}

								while ($ip_4_counter <= $ip_4_end)
								{
									$iplist[] = "'$ip_1_counter.$ip_2_counter.$ip_3_counter.$ip_4_counter'";
									$ip_4_counter++;
								}
								$ip_3_counter++;
							}
							$ip_2_counter++;
						}
						$ip_1_counter++;
					}
				}
				else if (preg_match('#^([0-9]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})\.([0-9\*]{1,3})$#', trim($item)) || preg_match('#^[a-f0-9:]+\*?$#i', trim($item)))
				{
					$iplist[] = "'" . trim($item) . "'";
				}
				else if (preg_match('#^([\w\-_]\.?){2,}$#is', trim($item)))
				{
					$hostlist[] = "'" . trim($item) . "'";
				}
				else if (preg_match("#^([a-z0-9\-\*\._/]+?)$#is", trim($item)))
				{
					$hostlist[] = "'" . trim($item) . "'";
				}
			}

			$sql = 'SELECT site_ip, site_hostname
				FROM ' . SITELIST_TABLE . "
				WHERE ip_exclude = $ip_exclude";
			$result = $db->sql_query($sql);

			if ($row = $db->sql_fetchrow($result))
			{
				$iplist_tmp = array();
				$hostlist_tmp = array();
				do
				{
					if ($row['site_ip'])
					{
						$iplist_tmp[] = "'" . $row['site_ip'] . "'";
					}
					else if ($row['site_hostname'])
					{
						$hostlist_tmp[] = "'" . $row['site_hostname'] . "'";
					}
					break;
				}
				while ($row = $db->sql_fetchrow($result));

				$iplist = array_unique(array_diff($iplist, $iplist_tmp));
				$hostlist = array_unique(array_diff($hostlist, $hostlist_tmp));
				unset($iplist_tmp);
				unset($hostlist_tmp);
			}

			if (sizeof($iplist))
			{
				foreach ($iplist as $ip_entry)
				{
					$sql = 'INSERT INTO ' . SITELIST_TABLE . " (site_ip, ip_exclude)
						VALUES ($ip_entry, $ip_exclude)";
					$db->sql_query($sql);
				}
			}

			if (sizeof($hostlist))
			{
				foreach ($hostlist as $host_entry)
				{
					$sql = 'INSERT INTO ' . SITELIST_TABLE . " (site_hostname, ip_exclude)
						VALUES ($host_entry, $ip_exclude)";
					$db->sql_query($sql);
				}
			}
			
			if (!empty($ip_list_log))
			{
				// Update log
				$log_entry = ($ip_exclude) ? 'LOG_DOWNLOAD_EXCLUDE_IP' : 'LOG_DOWNLOAD_IP';
				add_log('admin', $log_entry, $ip_list_log);
			}

			trigger_error($user->lang['SECURE_DOWNLOAD_UPDATE_SUCCESS']);
		}
		else if (isset($_POST['unsecuresubmit']))
		{
			$unip_sql = implode(', ', array_map('intval', $_POST['unip']));

			if ($unip_sql != '')
			{
				$l_unip_list = '';
			
				// Grab details of ips for logging information later
				$sql = 'SELECT site_ip, site_hostname
					FROM ' . SITELIST_TABLE . "
					WHERE site_id IN ($unip_sql)";
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$l_unip_list .= (($l_unip_list != '') ? ', ' : '') . (($row['site_ip']) ? $row['site_ip'] : $row['site_hostname']);
				}

				$sql = 'DELETE FROM ' . SITELIST_TABLE . "
					WHERE site_id IN ($unip_sql)";
				$db->sql_query($sql);

				add_log('admin', 'LOG_DOWNLOAD_REMOVE_IP', $l_unip_list);
			}

			trigger_error($user->lang['SECURE_DOWNLOAD_UPDATE_SUCCESS']);
		}
	}

	/**
	* Re-Write extensions cache file
	*/
	function rewrite_extensions()
	{
		global $db, $cache;

		$sql = 'SELECT e.extension, g.*
			FROM ' . EXTENSIONS_TABLE . ' e, ' . EXTENSION_GROUPS_TABLE . ' g
			WHERE e.group_id = g.group_id
				AND g.allow_group = 1';
		$result = $db->sql_query($sql);

		$extensions = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$extension = $row['extension'];

			$extensions[$extension]['display_cat']	= (int) $row['cat_id'];
			$extensions[$extension]['download_mode']= (int) $row['download_mode'];
			$extensions[$extension]['upload_icon']	= (string) $row['upload_icon'];
			$extensions[$extension]['max_filesize']	= (int) $row['max_filesize'];

			$allowed_forums = ($row['allowed_forums']) ? unserialize(trim($row['allowed_forums'])) : array();
				
			if ($row['allow_in_pm'])
			{
				$allowed_forums = array_merge($allowed_forums, array(0));
			}
				
			// Store allowed extensions forum wise
			$extensions['_allowed_'][$extension] = (!sizeof($allowed_forums)) ? 0 : $allowed_forums;
		}
		$db->sql_freeresult($result);

		$cache->destroy('_extensions');
		$cache->put('_extensions', $extensions);
	}

}

/**
* @package module_install
*/
class acp_attachments_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_attachments',
			'title'		=> 'ACP_ATTACHMENTS',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'attach'		=> array('title' => 'ACP_ATTACHMENT_SETTINGS', 'auth' => 'acl_a_attach'),
				'extensions'	=> array('title' => 'ACP_MANAGE_EXTENSIONS', 'auth' => 'acl_a_attach'),
				'ext_groups'	=> array('title' => 'ACP_EXTENSION_GROUPS', 'auth' => 'acl_a_attach'),
				'orphan'		=> array('title' => 'ACP_ORPHAN_ATTACHMENTS', 'auth' => 'acl_a_attach')
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