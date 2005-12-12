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
class acp_forums
{
	var $u_action = '';
	var $parent_id = 0;
	
	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_admin_path, $phpbb_root_path, $phpEx, $SID;

		$user->add_lang('acp/forums');

		$this->tpl_name = 'acp_forums';
		$this->page_title = 'ACP_MANAGE_FORUMS';

		$this->u_action = "{$phpbb_admin_path}index.$phpEx$SID&amp;i=$id&amp;mode=$mode";

		$action		= request_var('action', '');
		$update		= (isset($_POST['update'])) ? true : false;
		$forum_id	= request_var('f', 0);

		$this->parent_id	= request_var('parent_id', 0);

		$forum_data = $errors = array();

		// Check additional permissions
		switch ($action)
		{
			case 'delete':

				if (!$auth->acl_get('a_forumdel'))
				{
					trigger_error($user->lang['NO_PERMISSION_FORUM_DELETE'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
				}

			break;

			case 'add':

				if (!$auth->acl_get('a_forumadd'))
				{
					trigger_error($user->lang['NO_PERMISSION_FORUM_ADD'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
				}
			
			break;
		}

		// Major routines
		if ($update)
		{
			switch ($action)
			{
				case 'delete':
					$action_subforums	= request_var('action_subforums', '');
					$subforums_to_id	= request_var('subforums_to_id', 0);
					$action_posts		= request_var('action_posts', '');
					$posts_to_id		= request_var('posts_to_id', 0);

					$errors = $this->delete_forum($forum_id, $action_posts, $action_subforums, $posts_to_id, $subforums_to_id);

					if (sizeof($errors))
					{
						break;
					}

					$auth->acl_clear_prefetch();
					recalc_btree('forum_id', FORUMS_TABLE);

					trigger_error($user->lang['FORUM_DELETED'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
	
				break;

				case 'edit':
					$forum_data = array(
						'forum_id'		=>	$forum_id
					);

					// No break here

				case 'add':

					$forum_data += array(
						'parent_id'				=> $this->parent_id,
						'forum_type'			=> request_var('forum_type', FORUM_POST),
						'type_action'			=> request_var('type_action', ''),
						'forum_status'			=> request_var('forum_status', ITEM_UNLOCKED),
						'forum_name'			=> request_var('forum_name', ''),
						'forum_link'			=> request_var('forum_link', ''),
						'forum_link_track'		=> request_var('forum_link_track', false),
						'forum_desc'			=> request_var('forum_desc', ''),
						'forum_rules'			=> request_var('forum_rules', ''),
						'forum_rules_link'		=> request_var('forum_rules_link', ''),
						'forum_image'			=> request_var('forum_image', ''),
						'forum_style'			=> request_var('forum_style', 0),
						'display_on_index'		=> request_var('display_on_index', false),
						'forum_topics_per_page'	=> request_var('topics_per_page', 0), 
						'enable_indexing'		=> request_var('enable_indexing',true), 
						'enable_icons'			=> request_var('enable_icons', false),
						'enable_prune'			=> request_var('enable_prune', false),
						'prune_days'			=> request_var('prune_days', 7),
						'prune_viewed'			=> request_var('prune_viewed', 7),
						'prune_freq'			=> request_var('prune_freq', 1),
						'prune_old_polls'		=> request_var('prune_old_polls', false),
						'prune_announce'		=> request_var('prune_announce', false),
						'prune_sticky'			=> request_var('prune_sticky', false),
						'forum_password'		=> request_var('forum_password', ''),
						'forum_password_confirm'=> request_var('forum_password_confirm', ''),
						'forum_rules_flags'		=> 0,
					);

					$forum_data['show_active'] = ($forum_data['forum_type'] == FORUM_POST) ? request_var('display_recent', false) : request_var('display_active', false);

					if ($forum_data['forum_rules'])
					{
						include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);

						$allow_bbcode = request_var('parse_bbcode', false);
						$allow_smilies = request_var('parse_smilies', false);
						$allow_urls = request_var('parse_urls', false);

						$forum_data['forum_rules_flags'] = (($allow_bbcode) ? 1 : 0) + (($allow_smilies) ? 2 : 0) + (($allow_urls) ? 4 : 0);

						$message_parser = new parse_message($forum_data['forum_rules']);
						$message_parser->parse(false, $allow_bbcode, $allow_urls, $allow_smilies);
			
						$forum_data['forum_rules'] = $message_parser->message;
						$forum_data['forum_rules_bbcode_uid'] = $message_parser->bbcode_uid;
						$forum_data['forum_rules_bbcode_bitfield'] = $message_parser->bbcode_bitfield;
						unset($message_parser);
					}

					$errors = $this->update_forum_data($forum_data);
					
					if (!sizeof($errors))
					{
						$auth->acl_clear_prefetch();
						recalc_btree('forum_id', FORUMS_TABLE);

						// Redirect to permissions
						$message = ($action == 'add') ? $user->lang['FORUM_CREATED'] : $user->lang['FORUM_UPDATED'];
						$message .= '<br /><br />' . sprintf($user->lang['REDIRECT_ACL'], '<a href="' . $phpbb_admin_path . "index.$phpEx$SID&amp;i=permissions&amp;mode=forum&amp;submit_usergroups=true&amp;ug_type=forum&amp;action=usergroups&amp;f[forum][]={$forum_data['forum_id']}" . '">', '</a>');

						trigger_error($message . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
					}

				break;
			}
		}

		switch ($action)
		{
			case 'move_up':
			case 'move_down':
				
				if (!$forum_id)
				{
					trigger_error($user->lang['NO_FORUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
				}
				
				$sql = 'SELECT parent_id, left_id, right_id
					FROM ' . FORUMS_TABLE . "
					WHERE forum_id = $forum_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['NO_FORUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
				}

				$forum_info = array($forum_id => $row);

				// Get the adjacent forum
				$sql = 'SELECT forum_id, forum_name, left_id, right_id
					FROM ' . FORUMS_TABLE . "
					WHERE parent_id = $this->parent_id
						AND " . (($action == 'move_up') ? "right_id < {$row['right_id']} ORDER BY right_id DESC" : "left_id > {$row['left_id']} ORDER BY left_id ASC");
				$result = $db->sql_query_limit($sql, 1);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					// already on top or at bottom
					break;
				}

				if ($action == 'move_up')
				{
					$log_action = 'LOG_FORUM_MOVE_UP';
					$up_id = $forum_id;
					$down_id = $row['forum_id'];
				}
				else
				{
					$log_action = 'LOG_FORUM_MOVE_DOWN';
					$up_id = $row['forum_id'];
					$down_id = $forum_id;
				}

				$move_forum_name = $row['forum_name'];
				$forum_info[$row['forum_id']] = $row;
				$diff_up = $forum_info[$up_id]['right_id'] - $forum_info[$up_id]['left_id'];
				$diff_down = $forum_info[$down_id]['right_id'] - $forum_info[$down_id]['left_id'];

				$forum_ids = array();

				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE left_id > ' . $forum_info[$up_id]['left_id'] . '
						AND right_id < ' . $forum_info[$up_id]['right_id'];
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$forum_ids[] = $row['forum_id'];
				}
				$db->sql_freeresult($result);

				// Start transaction
				$db->sql_transaction('begin');

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET left_id = left_id + ' . ($diff_up + 1) . ', right_id = right_id + ' . ($diff_up + 1) . '
					WHERE left_id > ' . $forum_info[$down_id]['left_id'] . '
						AND right_id < ' . $forum_info[$down_id]['right_id'];
				$db->sql_query($sql);

				if (sizeof($forum_ids))
				{
					$sql = 'UPDATE ' . FORUMS_TABLE . '
						SET left_id = left_id - ' . ($diff_down + 1) . ', right_id = right_id - ' . ($diff_down + 1) . '
						WHERE forum_id IN (' . implode(', ', $forum_ids) . ')';
					$db->sql_query($sql);
				}

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET left_id = ' . $forum_info[$down_id]['left_id'] . ', right_id = ' . ($forum_info[$down_id]['left_id'] + $diff_up) . '
					WHERE forum_id = ' . $up_id;
				$db->sql_query($sql);

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET left_id = ' . ($forum_info[$up_id]['right_id'] - $diff_down) . ', right_id = ' . $forum_info[$up_id]['right_id'] . '
					WHERE forum_id = ' . $down_id;
				$db->sql_query($sql);

				$db->sql_transaction('commit');

				$forum_data = $this->get_forum_info($forum_id);

				add_log('admin', $log_action, $forum_data['forum_name'], $move_forum_name);
				unset($forum_data);
	
			break;

			case 'sync':
				if (!$forum_id)
				{
					trigger_error($user->lang['NO_FORUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
				}

				$sql = 'SELECT forum_name
					FROM ' . FORUMS_TABLE . "
					WHERE forum_id = $forum_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['NO_FORUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
				}

				sync('forum', 'forum_id', $forum_id);
				add_log('admin', 'LOG_FORUM_SYNC', $row['forum_name']);

				$template->assign_var('L_FORUM_RESYNCED', sprintf($user->lang['FORUM_RESYNCED'], $row['forum_name']));

			break;

			case 'add':
			case 'edit':

				if ($update)
				{
					$forum_data['forum_rules_flags']	= 0;
					$forum_data['forum_rules_flags']	+= (request_var('parse_bbcode', false)) ? 1 : 0;
					$forum_data['forum_rules_flags']	+= (request_var('parse_smilies', false)) ? 2 : 0;
					$forum_data['forum_rules_flags']	+= (request_var('parse_urls', false)) ? 4 : 0;

					$forum_data['forum_flags']	= 0;
					$forum_data['forum_flags']	+= (request_var('forum_link_track', false)) ? 1 : 0;
					$forum_data['forum_flags']	+= (request_var('prune_old_polls', false)) ? 2 : 0;
					$forum_data['forum_flags']	+= (request_var('prune_announce', false)) ? 4 : 0;
					$forum_data['forum_flags']	+= (request_var('prune_sticky', false)) ? 8 : 0;
					$forum_data['forum_flags']	+= ($forum_data['show_active']) ? 16 : 0;
				}

				// Show form to create/modify a forum
				if ($action == 'edit')
				{
					$this->page_title = 'EDIT_FORUM';
					$row = $this->get_forum_info($forum_id);
					$old_forum_type = $row['forum_type'];

					if (!$update)
					{
						$forum_data = $row;
					}

					$parents_list = make_forum_select($this->parent_id, $forum_id, false, false, false);

					$forum_data['forum_password_confirm'] = $forum_data['forum_password'];
				}
				else
				{
					$this->page_title = 'CREATE_FORUM';

					$forum_id = $this->parent_id;
					$parents_list = make_forum_select($this->parent_id, false, false, false, false);

					// Fill forum data with default values
					if (!$update)
					{
						$forum_data = array(
							'parent_id'				=> $this->parent_id,
							'forum_type'			=> FORUM_CAT,
							'forum_status'			=> ITEM_UNLOCKED,
							'forum_name'			=> request_var('forum_name', ''),
							'forum_link'			=> '',
							'forum_link_track'		=> false,
							'forum_desc'			=> '',
							'forum_rules'			=> '',
							'forum_rules_link'		=> '',
							'forum_image'			=> '',
							'forum_style'			=> 0,
							'display_on_index'		=> false,
							'forum_topics_per_page'	=> 0, 
							'enable_indexing'		=> true, 
							'enable_icons'			=> false,
							'enable_prune'			=> false,
							'prune_days'			=> 7,
							'prune_viewed'			=> 7,
							'prune_freq'			=> 1,
							'forum_flags'			=> 0,
							'forum_password'		=> '',
							'forum_password_confirm'=> '',
							'forum_rules_flags'		=> 7,
						);
					}
				}

				$forum_rules_preview = $forum_rules_plain = '';

				if ($forum_data['forum_rules'])
				{
					include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
					include_once($phpbb_root_path . 'includes/message_parser.' . $phpEx);
		
					$message_parser = new parse_message($forum_data['forum_rules']);

					if (isset($forum_data['forum_rules_bbcode_uid']))
					{
						$message_parser->bbcode_uid = $forum_data['forum_rules_bbcode_uid'];
						$message_parser->bbcode_bitfield = $forum_data['forum_rules_bbcode_bitfield'];
					}
					else
					{
						$message_parser->parse(false, ($forum_data['forum_rules_flags'] & 1), ($forum_data['forum_rules_flags'] & 4), ($forum_data['forum_rules_flags'] & 2));
					}

					$forum_rules_preview = $message_parser->format_display(false, ($forum_data['forum_rules_flags'] & 1), ($forum_data['forum_rules_flags'] & 4), ($forum_data['forum_rules_flags'] & 2), false);
					$forum_rules_plain = $message_parser->decode_message('', false);
				}

				$forum_type_options = '';
				$forum_type_ary = array(FORUM_CAT => 'CAT', FORUM_POST => 'FORUM', FORUM_LINK => 'LINK');
		
				foreach ($forum_type_ary as $value => $lang)
				{
					$forum_type_options .= '<option value="' . $value . '"' . (($value == $forum_data['forum_type']) ? ' selected="selected"' : '') . '>' . $user->lang['TYPE_' . $lang] . '</option>';
				}

				$styles_list = style_select($forum_data['forum_style'], true);

				$statuslist = '<option value="' . ITEM_UNLOCKED . '"' . (($forum_data['forum_status'] == ITEM_UNLOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['UNLOCKED'] . '</option><option value="' . ITEM_LOCKED . '"' . (($forum_data['forum_status'] == ITEM_LOCKED) ? ' selected="selected"' : '') . '>' . $user->lang['LOCKED'] . '</option>';

				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type = ' . FORUM_POST . "
						AND forum_id <> $forum_id";
				$result = $db->sql_query($sql);

				if ($db->sql_fetchrow($result))
				{
					$template->assign_vars(array(
						'S_MOVE_FORUM_OPTIONS'		=> make_forum_select($this->parent_id, $forum_id, false, true, false))
					);
				}
				$db->sql_freeresult($result);

				$s_show_display_on_index = false;
	
				if ($action == 'edit' && $this->parent_id > 0)
				{
					// if this forum is a subforum put the "display on index" checkbox
					if ($parent_info = $this->get_forum_info($this->parent_id))
					{
						if ($parent_info['parent_id'] > 0 || $parent_info['forum_type'] == FORUM_CAT)
						{
							$s_show_display_on_index = true;
						}
					}
				}

				$template->assign_vars(array(
					'S_EDIT_FORUM'	=> true,
					'S_ERROR'		=> (sizeof($errors)) ? true : false,
					'S_PARENT_ID'	=> $this->parent_id,
					'S_ADD_ACTION'	=> ($action == 'add') ? true : false,

					'U_BACK'		=> $this->u_action . '&amp;parent_id=' . $this->parent_id,
					'U_EDIT_ACTION'	=> $this->u_action . "&amp;parent_id={$this->parent_id}&amp;action=$action&amp;f=$forum_id",

					'L_TITLE'				=> $user->lang[$this->page_title],
					'ERROR_MSG'				=> (sizeof($errors)) ? implode('<br />', $errors) : '',

					'FORUM_NAME'				=> $forum_data['forum_name'],
					'FORUM_DATA_LINK'			=> $forum_data['forum_link'],
					'FORUM_DESC'				=> $forum_data['forum_desc'],
					'FORUM_IMAGE'				=> $forum_data['forum_image'],
					'FORUM_IMAGE_SRC'			=> ($forum_data['forum_image']) ? $phpbb_root_path . $forum_data['forum_image'] : '',
					'FORUM_POST'				=> FORUM_POST,
					'FORUM_LINK'				=> FORUM_LINK,
					'FORUM_CAT'					=> FORUM_CAT,
					'PRUNE_FREQ'				=> $forum_data['prune_freq'],
					'PRUNE_DAYS'				=> $forum_data['prune_days'],
					'PRUNE_VIEWED'				=> $forum_data['prune_viewed'],
					'TOPICS_PER_PAGE'			=> $forum_data['forum_topics_per_page'],
					'FORUM_PASSWORD'			=> $forum_data['forum_password'],
					'FORUM_PASSWORD_CONFIRM'	=> $forum_data['forum_password_confirm'],
					'FORUM_RULES_LINK'			=> $forum_data['forum_rules_link'],
					'FORUM_RULES'				=> $forum_data['forum_rules'],
					'FORUM_RULES_PREVIEW'		=> $forum_rules_preview,
					'FORUM_RULES_PLAIN'			=> $forum_rules_plain,
					'S_BBCODE_CHECKED'			=> ($forum_data['forum_rules_flags'] & 1) ? true : false,
					'S_SMILIES_CHECKED'			=> ($forum_data['forum_rules_flags'] & 2) ? true : false,
					'S_URLS_CHECKED'			=> ($forum_data['forum_rules_flags'] & 4) ? true : false,

					'S_FORUM_TYPE_OPTIONS'		=> $forum_type_options,
					'S_STATUS_OPTIONS'			=> $statuslist,
					'S_PARENT_OPTIONS'			=> $parents_list,
					'S_STYLES_OPTIONS'			=> $styles_list,
					'S_SHOW_DISPLAY_ON_INDEX'	=> $s_show_display_on_index,
					'S_FORUM_POST'				=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
					'S_FORUM_ORIG_POST'			=> (isset($old_forum_type) && $old_forum_type == FORUM_POST) ? true : false,
					'S_FORUM_LINK'				=> ($forum_data['forum_type'] == FORUM_LINK) ? true : false,
					'S_FORUM_CAT'				=> ($forum_data['forum_type'] == FORUM_CAT) ? true : false,
					'S_FORUM_LINK_TRACK'		=> ($forum_data['forum_flags'] & 1) ? true : false,
					'S_ENABLE_INDEXING'			=> ($forum_data['enable_indexing']) ? true : false,
					'S_TOPIC_ICONS'				=> ($forum_data['enable_icons']) ? true : false,
					'S_DISPLAY_ON_INDEX'		=> ($forum_data['display_on_index']) ? true : false,
					'S_PRUNE_ENABLE'			=> ($forum_data['enable_prune']) ? true : false,
					'S_PRUNE_OLD_POLLS'			=> ($forum_data['forum_flags'] & 2) ? true : false,
					'S_PRUNE_ANNOUNCE'			=> ($forum_data['forum_flags'] & 4) ? true : false,
					'S_PRUNE_STICKY'			=> ($forum_data['forum_flags'] & 8) ? true : false,
					'S_DISPLAY_ACTIVE_TOPICS'	=> ($forum_data['forum_flags'] & 16) ? true : false,
					)
				);

				return;

			break;

			case 'delete':

				if (!$forum_id)
				{
					trigger_error($user->lang['NO_FORUM'] . adm_back_link($this->u_action . '&amp;parent_id=' . $this->parent_id));
				}
				
				$forum_data = $this->get_forum_info($forum_id);

				$subforums_id = array();

				$subforums = get_forum_branch($forum_id, 'children');
				foreach ($subforums as $row)
				{
					$subforums_id[] = $row['forum_id'];
				}

				$forums_list = make_forum_select($this->parent_id, $subforums_id);

				$sql = 'SELECT forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type = ' . FORUM_POST . "
						AND forum_id <> $forum_id";
				$result = $db->sql_query($sql);

				if ($db->sql_fetchrow($result))
				{
					$template->assign_vars(array(
						'S_MOVE_FORUM_OPTIONS'		=> make_forum_select($this->parent_id, $subforums_id)) // , false, true, false???
					);
				}
				$db->sql_freeresult($result);

				$template->assign_vars(array(
					'S_DELETE_FORUM'		=> true,
					'U_ACTION'				=> $this->u_action . "&amp;parent_id={$this->parent_id}&amp;action=delete&amp;f=$forum_id",
					'U_BACK'				=> $this->u_action . '&amp;parent_id=' . $this->parent_id,

					'FORUM_NAME'			=> $forum_data['forum_name'],
					'S_FORUM_POST'			=> ($forum_data['forum_type'] == FORUM_POST) ? true : false,
					'S_HAS_SUBFORUMS'		=> ($forum_data['right_id'] - $forum_data['left_id'] > 1) ? true : false,
					'S_FORUMS_LIST'			=> $forums_list,
					'S_ERROR'				=> (sizeof($errors)) ? true : false,
					'ERROR_MSG'				=> (sizeof($errors)) ? implode('<br />', $errors) : '')
				);

				return;
			break;
		}

		// Default management page
		if (!$this->parent_id)
		{
			$navigation = $user->lang['FORUM_INDEX'];
		}
		else
		{
			$navigation = '<a href="' . $this->u_action . '">' . $user->lang['FORUM_INDEX'] . '</a>';

			$forums_nav = get_forum_branch($this->parent_id, 'parents', 'descending');
			foreach ($forums_nav as $row)
			{
				if ($row['forum_id'] == $this->parent_id)
				{
					$navigation .= ' -&gt; ' . $row['forum_name'];
				}
				else
				{
					$navigation .= ' -&gt; <a href="' . $this->u_action . '&amp;parent_id=' . $row['forum_id'] . '">' . $row['forum_name'] . '</a>';
				}
			}
		}

		// Jumpbox
		$forum_box = make_forum_select($this->parent_id, false, false, false, false); //make_forum_select($this->parent_id);

		if ($action == 'sync')
		{
			$template->assign_var('S_RESYNCED', true);
		}

		$sql = 'SELECT *
			FROM ' . FORUMS_TABLE . "
			WHERE parent_id = $this->parent_id
			ORDER BY left_id";
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$forum_type = $row['forum_type'];

				if ($row['forum_status'] == ITEM_LOCKED)
				{
					$folder_image = '<img src="images/icon_folder_lock.gif" width="46" height="25" alt="' . $user->lang['LOCKED'] . '" />';
				}
				else
				{
					switch ($forum_type)
					{
						case FORUM_LINK:
							$folder_image = '<img src="images/icon_folder_link.gif" width="46" height="25" alt="' . $user->lang['LINK'] . '" />';
						break;

						default:
							$folder_image = ($row['left_id'] + 1 != $row['right_id']) ? '<img src="images/icon_subfolder.gif" width="46" height="25" alt="' . $user->lang['SUBFORUM'] . '" />' : '<img src="images/icon_folder.gif" width="46" height="25" alt="' . $user->lang['FOLDER'] . '" />';
					}
				}

				$url = $this->u_action . "&amp;parent_id=$this->parent_id&amp;f={$row['forum_id']}";
			
				$forum_title = ($forum_type != FORUM_LINK) ? "<a href=\"admin_forums.$phpEx$SID&amp;parent_id=" . $row['forum_id'] . '">' : '';
				$forum_title .= $row['forum_name'];
				$forum_title .= ($forum_type != FORUM_LINK) ? '</a>' : '';

				$template->assign_block_vars('forums', array(
					'FOLDER_IMAGE'		=> $folder_image,
					'FORUM_NAME'		=> $row['forum_name'],
					'FORUM_DESCRIPTION'	=> $row['forum_desc'],
					'FORUM_TOPICS'		=> $row['forum_topics'],
					'FORUM_POSTS'		=> $row['forum_posts'],
					
					'S_FORUM_LINK'		=> ($forum_type == FORUM_LINK) ? true : false,
					'S_FORUM_POST'		=> ($forum_type == FORUM_POST) ? true : false,
					
					'U_FORUM'			=> $this->u_action . '&amp;parent_id=' . $row['forum_id'],
					'U_MOVE_UP'			=> $url . '&amp;action=move_up',
					'U_MOVE_DOWN'		=> $url . '&amp;action=move_down',
					'U_EDIT'			=> $url . '&amp;action=edit',
					'U_DELETE'			=> $url . '&amp;action=delete',
					'U_SYNC'			=> $url . '&amp;action=sync',
					)
				);
			}
			while ($row = $db->sql_fetchrow($result));
		}
		else if ($this->parent_id)
		{
			$row = $this->get_forum_info($this->parent_id);

			$url = $this->u_action . '&amp;parent_id=' . $this->parent_id . '&amp;f=' . $row['forum_id'];

			$template->assign_vars(array(
				'S_NO_FORUMS'		=> true,

				'U_EDIT'			=> $url . '&amp;action=edit',
				'U_DELETE'			=> $url . '&amp;action=delete',
				'U_SYNC'			=> $url . '&amp;action=sync')
			);
		}
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'ERROR_MSG'		=> (sizeof($errors)) ? implode('<br />', $errors) : '',
			'NAVIGATION'	=> $navigation,
			'FORUM_BOX'		=> $forum_box,
			'U_SEL_ACTION'	=> $this->u_action,
			'U_ACTION'		=> $this->u_action . '&amp;parent_id=' . $this->parent_id)
		);

	}

	/**
	* Get forum details
	*/
	function get_forum_info($forum_id)
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . FORUMS_TABLE . "
			WHERE forum_id = $forum_id";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$row)
		{
			trigger_error("Forum #$forum_id does not exist");
		}

		return $row;
	}

	/**
	* Update forum data
	*/
	function update_forum_data(&$forum_data)
	{
		global $db, $user;

		$errors = array();
	
		if (!$forum_data['forum_name'])
		{
			$errors[] = $user->lang['FORUM_NAME_EMPTY'];
		}

		if ($forum_data['forum_password'] || $forum_data['forum_password_confirm'])
		{
			if ($forum_data['forum_password'] != $forum_data['forum_password_confirm'])
			{
				$forum_data['forum_password'] = $forum_data['forum_password_confirm'] = '';
				$errors[] = $user->lang['FORUM_PASSWORD_MISMATCH'];
			}
		}

		if ($forum_data['prune_days'] < 0 || $forum_data['prune_viewed'] < 0 || $forum_data['prune_freq'] < 0)
		{
			$forum_data['prune_days'] = $forum_data['prune_viewed'] = $forum_data['prune_freq'] = 0;
			$errors[] = $user->lang['FORUM_DATA_NEGATIVE'];
		}

		// Set forum flags
		// 1 = link tracking
		// 2 = prune old polls
		// 4 = prune announcements
		// 8 = prune stickies
		// 16 = show active topics
		$forum_data['forum_flags'] = 0;
		$forum_data['forum_flags'] += ($forum_data['forum_link_track']) ? 1 : 0;
		$forum_data['forum_flags'] += ($forum_data['prune_old_polls']) ? 2 : 0;
		$forum_data['forum_flags'] += ($forum_data['prune_announce']) ? 4 : 0;
		$forum_data['forum_flags'] += ($forum_data['prune_sticky']) ? 8 : 0;
		$forum_data['forum_flags'] += ($forum_data['show_active']) ? 16 : 0;

		// Unset data that are not database fields
		unset($forum_data['forum_link_track']);
		unset($forum_data['prune_old_polls']);
		unset($forum_data['prune_announce']);
		unset($forum_data['prune_sticky']);
		unset($forum_data['show_active']);
		unset($forum_data['forum_password_confirm']);

		// What are we going to do tonight Brain? The same thing we do everynight,
		// try to take over the world ... or decide whether to continue update
		// and if so, whether it's a new forum/cat/link or an existing one
		if (sizeof($errors))
		{
			return $errors;
		}

		if (!isset($forum_data['forum_id']))
		{
			// no forum_id means we're creating a new forum
			unset($forum_data['type_action']);

			if ($forum_data['parent_id'])
			{
				$sql = 'SELECT left_id, right_id
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . $forum_data['parent_id'];
				$result = $db->sql_query($sql);

				if (!$row = $db->sql_fetchrow($result))
				{
					trigger_error($user->lang['PARENT_NOT_EXIST'] . adm_back_link($this->u_action . '&amp;' . $this->parent_id));
				}
				$db->sql_freeresult($result);

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET left_id = left_id + 2, right_id = right_id + 2
					WHERE left_id > ' . $row['right_id'];
				$db->sql_query($sql);

				$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET right_id = right_id + 2
					WHERE ' . $row['left_id'] . ' BETWEEN left_id AND right_id';
				$db->sql_query($sql);

				$forum_data['left_id'] = $row['right_id'];
				$forum_data['right_id'] = $row['right_id'] + 1;
			}
			else
			{
				$sql = 'SELECT MAX(right_id) AS right_id
					FROM ' . FORUMS_TABLE;
				$result = $db->sql_query($sql);

				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$forum_data['left_id'] = $row['right_id'] + 1;
				$forum_data['right_id'] = $row['right_id'] + 2;
			}

			$sql = 'INSERT INTO ' . FORUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $forum_data);
			$db->sql_query($sql);

			$forum_data['forum_id'] = $db->sql_nextid();
		
			add_log('admin', 'LOG_FORUM_ADD', $forum_data['forum_name']);
		}
		else
		{
			$row = $this->get_forum_info($forum_data['forum_id']);

			if ($forum_data['forum_type'] != FORUM_POST && $row['forum_type'] != $forum_data['forum_type'])
			{
				// we're turning a postable forum into a non-postable forum
				if ($forum_data['type_action'] == 'move')
				{
					if ($forum_data['to_forum_id'])
					{
						$errors = $this->move_forum_content($forum_data['forum_id'], $forum_data['to_forum_id']);
					}
					else
					{
						return array($user->lang['NO_DESTINATION_FORUM']);
					}
				}
				else if ($forum_data['type_action'] == 'delete')
				{
					$errors = $this->delete_forum_content($forum_data['forum_id']);
				}
				else
				{
					return array($user->lang['NO_FORUM_ACTION']);
				}

				$forum_data['forum_posts'] = 0;
				$forum_data['forum_topics'] = 0;
				$forum_data['forum_topics_real'] = 0;
			}

			if (sizeof($errors))
			{
				return $errors;
			}
	
			if ($row['parent_id'] != $forum_data['parent_id'])
			{
				$errors = $this->move_forum($forum_data['forum_id'], $forum_data['parent_id']);
			}
			
			if (sizeof($errors))
			{
				return $errors;
			}

			unset($forum_data['type_action']);
			
			if ($row['forum_name'] != $forum_data['forum_name'])
			{
				// the forum name has changed, clear the parents list of child forums
				$sql = 'UPDATE ' . FORUMS_TABLE . "
					SET forum_parents = ''
					WHERE left_id > " . $row['left_id'] . '
						AND right_id < ' . $row['right_id'];
				$db->sql_query($sql);
			}

			$sql = 'UPDATE ' . FORUMS_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $forum_data) . '
				WHERE forum_id = ' . $forum_data['forum_id'];
			$db->sql_query($sql);

			add_log('admin', 'LOG_FORUM_EDIT', $forum_data['forum_name']);
		}
	}

	/**
	* Move forum
	*/
	function move_forum($from_id, $to_id)
	{
		global $db;

		$moved_forums = get_forum_branch($from_id, 'children', 'descending');
		$from_data = $moved_forums[0];
		$diff = sizeof($moved_forums) * 2;

		$moved_ids = array();
		for ($i = 0; $i < sizeof($moved_forums); ++$i)
		{
			$moved_ids[] = $moved_forums[$i]['forum_id'];
		}

		// Resync parents
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET right_id = right_id - $diff, forum_parents = ''
			WHERE left_id < " . $from_data['right_id'] . "
				AND right_id > " . $from_data['right_id'];
		$db->sql_query($sql);

		// Resync righthand side of tree
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET left_id = left_id - $diff, right_id = right_id - $diff, forum_parents = ''
			WHERE left_id > " . $from_data['right_id'];
		$db->sql_query($sql);

		if ($to_id > 0)
		{
			$to_data = $this->get_forum_info($to_id);

			// Resync new parents
			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET right_id = right_id + $diff, forum_parents = ''
				WHERE " . $to_data['right_id'] . ' BETWEEN left_id AND right_id
					AND forum_id NOT IN (' . implode(', ', $moved_ids) . ')';
			$db->sql_query($sql);

			// Resync the righthand side of the tree
			$sql = 'UPDATE ' . FORUMS_TABLE . "
				SET left_id = left_id + $diff, right_id = right_id + $diff, forum_parents = ''
				WHERE left_id > " . $to_data['right_id'] . '
					AND forum_id NOT IN (' . implode(', ', $moved_ids) . ')';
			$db->sql_query($sql);

			// Resync moved branch
			$to_data['right_id'] += $diff;
		
			if ($to_data['right_id'] > $from_data['right_id'])
			{
				$diff = '+ ' . ($to_data['right_id'] - $from_data['right_id'] - 1);
			}
			else
			{
				$diff = '- ' . abs($to_data['right_id'] - $from_data['right_id'] - 1);
			}
		}
		else
		{
			$sql = 'SELECT MAX(right_id) AS right_id
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id NOT IN (' . implode(', ', $moved_ids) . ')';
			$result = $db->sql_query($sql);

			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$diff = '+ ' . ($row['right_id'] - $from_data['left_id'] + 1);
		}

		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET left_id = left_id $diff, right_id = right_id $diff, forum_parents = ''
			WHERE forum_id IN (" . implode(', ', $moved_ids) . ')';
		$db->sql_query($sql);
	}

	/**
	* Move forum content from one to another forum
	*/
	function move_forum_content($from_id, $to_id, $sync = true)
	{
		global $db;

		$table_ary = array(LOG_TABLE, POSTS_TABLE, TOPICS_TABLE, DRAFTS_TABLE, TOPICS_TRACK_TABLE);
	
		foreach ($table_ary as $table)
		{
			$sql = "UPDATE $table
				SET forum_id = $to_id
				WHERE forum_id = $from_id";
			$db->sql_query($sql);
		}
		unset($table_ary);

		$table_ary = array(FORUMS_ACCESS_TABLE, FORUMS_TRACK_TABLE, FORUMS_WATCH_TABLE, MODERATOR_TABLE);

		foreach ($table_ary as $table)
		{
			$sql = "DELETE FROM $table
				WHERE forum_id = $from_id";
			$db->sql_query($sql);
		}

		if ($sync)
		{
			// Delete ghost topics that link back to the same forum
			// then resync counters
			sync('topic_moved');
			sync('forum', 'forum_id', $to_id);
		}

		return array();
	}

	/**
	* Remove complete forum
	*/
	function delete_forum($forum_id, $action_posts = 'delete', $action_subforums = 'delete', $posts_to_id = 0, $subforums_to_id = 0)
	{
		global $db, $user, $cache;

		$forum_data = $this->get_forum_info($forum_id);

		$errors = array();
		$log_action_posts = $log_action_forums = $posts_to_name = $subforums_to_name = '';

		if ($action_posts == 'delete')
		{
			$log_action_posts = 'POSTS';
			$errors = array_merge($errors, $this->delete_forum_content($forum_id));
		}
		else if ($action_posts == 'move')
		{
			if (!$posts_to_id)
			{
				$errors[] = $user->lang['NO_DESTINATION_FORUM'];
			}
			else
			{
				$log_action_posts = 'MOVE_POSTS';

				$sql = 'SELECT forum_name 
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . $posts_to_id;
				$result = $db->sql_query($sql);

				if (!$row = $db->sql_fetchrow($result))
				{
					$errors[] = $user->lang['NO_FORUM'];
				}
				else
				{
					$posts_to_name = $row['forum_name'];

					$errors = array_merge($errors, $this->move_forum_content($forum_id, $subforums_to_id));
				}
			}
		}

		if (sizeof($errors))
		{
			return $errors;
		}

		if ($action_subforums == 'delete')
		{
			$log_action_forums = 'FORUMS';

			$forum_ids = array($forum_id);
			$rows = get_forum_branch($forum_id, 'children', 'descending', false);

			foreach ($rows as $row)
			{
				$forum_ids[] = $row['forum_id'];
				$errors = array_merge($errors, $this->delete_forum_content($row['forum_id']));
			}

			if (sizeof($errors))
			{
				return $errors;
			}

			$diff = sizeof($forum_ids) * 2;

			$sql = 'DELETE FROM ' . FORUMS_TABLE . '
				WHERE forum_id IN (' . implode(', ', $forum_ids) . ')';
			$db->sql_query($sql);
		}
		else if ($action_subforums == 'move')
		{
			if (!$subforums_to_id)
			{
				$errors[] = $user->lang['NO_DESTINATION_FORUM'];
			}
			else
			{
				$log_action_forums = 'MOVE_FORUMS';

				$sql = 'SELECT forum_name 
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . $subforums_to_id;
				$result = $db->sql_query($sql);

				if (!$row = $db->sql_fetchrow($result))
				{
					$errors[] = $user->lang['NO_FORUM'];
				}
				else
				{
					$subforums_to_name = $row['forum_name'];

					$sql = 'SELECT forum_id
						FROM ' . FORUMS_TABLE . "
						WHERE parent_id = $forum_id";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$this->move_forum($row['forum_id'], $subforums_to_id);
					}
					$db->sql_freeresult($result);

					$sql = 'UPDATE ' . FORUMS_TABLE . "
						SET parent_id = $subforums_to_id
						WHERE parent_id = $forum_id";
					$db->sql_query($sql);

					$diff = 2;
					$sql = 'DELETE FROM ' . FORUMS_TABLE . "
						WHERE forum_id = $forum_id";
					$db->sql_query($sql);
				}
			}

			if (sizeof($errors))
			{
				return $errors;
			}
		}
		else
		{
			$diff = 2;
			$sql = 'DELETE FROM ' . FORUMS_TABLE . "
				WHERE forum_id = $forum_id";
			$db->sql_query($sql);
		}

		// Resync tree
		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET right_id = right_id - $diff
			WHERE left_id < {$forum_data['right_id']} AND right_id > {$forum_data['right_id']}";
		$db->sql_query($sql);

		$sql = 'UPDATE ' . FORUMS_TABLE . "
			SET left_id = left_id - $diff, right_id = right_id - $diff
			WHERE left_id > {$forum_data['right_id']}";
		$db->sql_query($sql);

		if (!isset($forum_ids) || !is_array($forum_ids))
		{
			$forum_ids = array($forum_id);
		}

		// Delete forum ids from extension groups table
		$sql = 'SELECT group_id, allowed_forums 
			FROM ' . EXTENSION_GROUPS_TABLE . "
			WHERE allowed_forums <> ''";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$allowed_forums = unserialize(trim($row['allowed_forums']));
			$allowed_forums = array_diff($allowed_forums, $forum_ids);

			$sql = 'UPDATE ' . EXTENSION_GROUPS_TABLE . " 
				SET allowed_forums = '" . ((sizeof($allowed_forums)) ? serialize($allowed_forums) : '') . "'
				WHERE group_id = {$row['group_id']}";
			$db->sql_query($sql);
		}
		$cache->destroy('extensions');

		$log_action = implode('_', array($log_action_posts, $log_action_forums));

		switch ($log_action)
		{
			case 'MOVE_POSTS_MOVE_FORUMS':
				add_log('admin', 'LOG_FORUM_DEL_MOVE_POSTS_MOVE_FORUMS', $posts_to_name, $subforums_to_name, $forum_data['forum_name']);
			break;
			
			case 'MOVE_POSTS_FORUMS':
				add_log('admin', 'LOG_FORUM_DEL_MOVE_POSTS_FORUMS', $posts_to_name, $forum_data['forum_name']);
			break;
			
			case 'POSTS_MOVE_FORUMS':
				add_log('admin', 'LOG_FORUM_DEL_POSTS_MOVE_FORUMS', $subforums_to_name, $forum_data['forum_name']);
			break;
			
			case '_MOVE_FORUMS':
				add_log('admin', 'LOG_FORUM_DEL_MOVE_FORUMS', $subforums_to_name, $forum_data['forum_name']);
			break;
			
			case 'MOVE_POSTS_':
				add_log('admin', 'LOG_FORUM_DEL_MOVE_POSTS', $posts_to_name, $forum_data['forum_name']);
			break;

			case 'POSTS_FORUMS':
				add_log('admin', 'LOG_FORUM_DEL_POSTS_FORUMS', $forum_data['forum_name']);
			break;
			
			case '_FORUMS':
				add_log('admin', 'LOG_FORUM_DEL_FORUMS', $forum_data['forum_name']);
			break;
			
			case 'POSTS_':
				add_log('admin', 'LOG_FORUM_DEL_POSTS', $forum_data['forum_name']);
			break;
		}

		return $errors;
	}

	/**
	* Delete forum content
	*/
	function delete_forum_content($forum_id)
	{
		global $db, $config, $phpbb_root_path, $phpEx;

		include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

		$db->sql_transaction('begin');

		// Select then delete all attachments
		$sql = 'SELECT a.topic_id
			FROM ' . POSTS_TABLE . ' p, ' . ATTACHMENTS_TABLE . " a
			WHERE p.forum_id = $forum_id
				AND a.in_message = 0
				AND a.topic_id = p.topic_id";
		$result = $db->sql_query($sql);	
			
		$topic_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$topic_ids[] = $row['topic_id'];
		}
		$db->sql_freeresult($result);
				
		delete_attachments('topic', $topic_ids, false);

		switch (SQL_LAYER)
		{
			case 'mysql4':
			case 'mysqli':

				// Delete everything else and thank MySQL for offering multi-table deletion
				$tables_ary = array(
					SEARCH_MATCH_TABLE		=> 'wm.post_id',
					REPORTS_TABLE			=> 're.post_id',
					WARNINGS_TABLE			=> 'wt.post_id',
					BOOKMARKS_TABLE			=> 'bm.topic_id',
					TOPICS_WATCH_TABLE		=> 'tw.topic_id',
					TOPICS_POSTED_TABLE		=> 'tp.topic_id',
					POLL_OPTIONS_TABLE		=> 'po.topic_id',
					POLL_VOTES_TABLE		=> 'pv.topic_id',
				);

				$sql = 'DELETE ' . POSTS_TABLE . ' p';
				$sql_using = "\nFROM " . POSTS_TABLE . ' p';
				$sql_where = "\nWHERE p.forum_id = $forum_id\n";

				foreach ($tables_ary as $table => $field)
				{
					$sql .= ", $table " . strtok($field, '.');
					$sql_using .= ", $table " . strtok($field, '.');
					$sql_where .= "\nAND $field = p." . strtok('');
				}

				$db->sql_query($sql . $sql_using . $sql_where);

			break;

			default:
			
				// Delete everything else and curse your DB for not offering multi-table deletion
				$tables_ary = array(
					'post_id'	=>	array(
						SEARCH_MATCH_TABLE,
						REPORTS_TABLE,
						WARNINGS_TABLE,
					),
					
					'topic_id'	=>	array(
						BOOKMARKS_TABLE,
						TOPICS_WATCH_TABLE,
						TOPICS_POSTED_TABLE,
						POLL_OPTIONS_TABLE,
						POLL_VOTES_TABLE,
					)
				);

				foreach ($tables_ary as $field => $tables)
				{
					$start = 0;
	
					do
					{
						$sql = "SELECT $field
							FROM " . POSTS_TABLE . '
							WHERE forum_id = ' . $forum_id;
						$result = $db->sql_query_limit($sql, 500, $start);

						$ids = array();
						while ($row = $db->sql_fetchrow($result))
						{
							$ids[] = $row[$field];
						}
						$db->sql_freeresult($result);

						if (sizeof($ids))
						{
							$start += sizeof($ids);
							$id_list = implode(', ', $ids);

							foreach ($tables as $table)
							{
								$db->sql_query("DELETE FROM $table WHERE $field IN ($id_list)");
							}
						}
					}
					while ($row);
				}
				unset($ids, $id_list);

			break;
		}

		$table_ary = array(ACL_GROUPS_TABLE, ACL_USERS_TABLE, FORUMS_ACCESS_TABLE, FORUMS_TRACK_TABLE, FORUMS_WATCH_TABLE, LOG_TABLE, MODERATOR_TABLE, POSTS_TABLE, TOPICS_TABLE, TOPICS_TRACK_TABLE);

		foreach ($table_ary as $table)
		{
			$db->sql_query("DELETE FROM $table WHERE forum_id = $forum_id");
		}

		// Set forum ids to 0
		$table_ary = array(DRAFTS_TABLE);

		foreach ($tables_ary as $table)
		{
			$db->sql_query("UPDATE $table SET forum_id = 0 WHERE forum_id = $forum_id");
		}

		/**
		* @todo run cron for optimize table or redirect to database management screen
		*/

		$db->sql_transaction('commit');

		return array();
	}

}


/**
* @package module_install
*/
class acp_forums_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_forums',
			'title'		=> 'ACP_FORUM_MANAGEMENT',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'manage'	=> array('title' => 'ACP_MANAGE_FORUMS', 'auth' => 'acl_a_forum'),
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