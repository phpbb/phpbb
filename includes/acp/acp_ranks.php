<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_ranks
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $template, $cache, $request, $phpbb_dispatcher;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpbb_log;

		$user->add_lang('acp/posting');

		// Set up general vars
		$action = $request->variable('action', '');
		$action = (isset($_POST['add'])) ? 'add' : $action;
		$action = (isset($_POST['save'])) ? 'save' : $action;
		$rank_id = $request->variable('id', 0);

		$this->tpl_name = 'acp_ranks';
		$this->page_title = 'ACP_MANAGE_RANKS';

		$form_name = 'acp_ranks';
		add_form_key($form_name);

		switch ($action)
		{
			case 'save':

				if (!check_form_key($form_name))
				{
					trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
				}
				$rank_title = $request->variable('title', '', true);
				$special_rank = $request->variable('special_rank', 0);
				$min_posts = ($special_rank) ? 0 : max(0, $request->variable('min_posts', 0));
				$rank_image = $request->variable('rank_image', '');

				// The rank image has to be a jpg, gif or png
				if ($rank_image != '' && !preg_match('#(\.gif|\.png|\.jpg|\.jpeg)$#i', $rank_image))
				{
					$rank_image = '';
				}

				if (!$rank_title)
				{
					trigger_error($user->lang['NO_RANK_TITLE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql_ary = array(
					'rank_title'		=> $rank_title,
					'rank_special'		=> $special_rank,
					'rank_min'			=> $min_posts,
					'rank_image'		=> htmlspecialchars_decode($rank_image)
				);

				/**
				* Modify the SQL array when saving a rank
				*
				* @event core.acp_ranks_save_modify_sql_ary
				* @var	int		rank_id		The ID of the rank (if available)
				* @var	array	sql_ary		Array with the rank's data
				* @since 3.1.0-RC3
				*/
				$vars = array('rank_id', 'sql_ary');
				extract($phpbb_dispatcher->trigger_event('core.acp_ranks_save_modify_sql_ary', compact($vars)));

				if ($rank_id)
				{
					$sql = 'UPDATE ' . RANKS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE rank_id = $rank_id";
					$message = $user->lang['RANK_UPDATED'];

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RANK_UPDATED', false, array($rank_title));
				}
				else
				{
					$sql = 'INSERT INTO ' . RANKS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
					$message = $user->lang['RANK_ADDED'];

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RANK_ADDED', false, array($rank_title));
				}
				$db->sql_query($sql);

				$cache->destroy('_ranks');

				trigger_error($message . adm_back_link($this->u_action));

			break;

			case 'delete':

				if (!$rank_id)
				{
					trigger_error($user->lang['MUST_SELECT_RANK'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$sql = 'SELECT rank_title
						FROM ' . RANKS_TABLE . '
						WHERE rank_id = ' . $rank_id;
					$result = $db->sql_query($sql);
					$rank_title = (string) $db->sql_fetchfield('rank_title');
					$db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . RANKS_TABLE . "
						WHERE rank_id = $rank_id";
					$db->sql_query($sql);

					$sql = 'UPDATE ' . USERS_TABLE . "
						SET user_rank = 0
						WHERE user_rank = $rank_id";
					$db->sql_query($sql);

					$cache->destroy('_ranks');

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_RANK_REMOVED', false, array($rank_title));

					if ($request->is_ajax())
					{
						$json_response = new \phpbb\json_response;
						$json_response->send(array(
							'MESSAGE_TITLE'	=> $user->lang['INFORMATION'],
							'MESSAGE_TEXT'	=> $user->lang['RANK_REMOVED'],
							'REFRESH_DATA'	=> array(
								'time'	=> 3
							)
						));
					}
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
						'i'			=> $id,
						'mode'		=> $mode,
						'rank_id'	=> $rank_id,
						'action'	=> 'delete',
					)));
				}

			break;

			case 'edit':
			case 'add':

				$ranks = $existing_imgs = array();

				$sql = 'SELECT *
					FROM ' . RANKS_TABLE . '
					ORDER BY rank_min ASC, rank_special ASC';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$existing_imgs[] = $row['rank_image'];

					if ($action == 'edit' && $rank_id == $row['rank_id'])
					{
						$ranks = $row;
					}
				}
				$db->sql_freeresult($result);

				$imglist = filelist($phpbb_root_path . $config['ranks_path'], '');
				$edit_img = $filename_list = '';

				foreach ($imglist as $path => $img_ary)
				{
					sort($img_ary);

					foreach ($img_ary as $img)
					{
						$img = $path . $img;

						if ($ranks && $img == $ranks['rank_image'])
						{
							$selected = ' selected="selected"';
							$edit_img = $img;
						}
						else
						{
							$selected = '';
						}

						if (strlen($img) > 255)
						{
							continue;
						}

						$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . ((in_array($img, $existing_imgs)) ? ' ' . $user->lang['RANK_IMAGE_IN_USE'] : '') . '</option>';
					}
				}

				$filename_list = '<option value=""' . (($edit_img == '') ? ' selected="selected"' : '') . '>----------</option>' . $filename_list;
				unset($existing_imgs, $imglist);

				$tpl_ary = array(
					'S_EDIT'			=> true,
					'U_BACK'			=> $this->u_action,
					'RANKS_PATH'		=> $phpbb_root_path . $config['ranks_path'],
					'U_ACTION'			=> $this->u_action . '&amp;id=' . $rank_id,

					'RANK_TITLE'		=> (isset($ranks['rank_title'])) ? $ranks['rank_title'] : '',
					'S_FILENAME_LIST'	=> $filename_list,
					'RANK_IMAGE'		=> ($edit_img) ? $phpbb_root_path . $config['ranks_path'] . '/' . $edit_img : htmlspecialchars($phpbb_admin_path) . 'images/spacer.gif',
					'S_SPECIAL_RANK'	=> (isset($ranks['rank_special']) && $ranks['rank_special']) ? true : false,
					'MIN_POSTS'			=> (isset($ranks['rank_min']) && !$ranks['rank_special']) ? $ranks['rank_min'] : 0,
				);

				/**
				* Modify the template output array for editing/adding ranks
				*
				* @event core.acp_ranks_edit_modify_tpl_ary
				* @var	array	ranks		Array with the rank's data
				* @var	array	tpl_ary		Array with the rank's template data
				* @since 3.1.0-RC3
				*/
				$vars = array('ranks', 'tpl_ary');
				extract($phpbb_dispatcher->trigger_event('core.acp_ranks_edit_modify_tpl_ary', compact($vars)));

				$template->assign_vars($tpl_ary);
				return;

			break;
		}

		$template->assign_vars(array(
			'U_ACTION'		=> $this->u_action)
		);

		$sql = 'SELECT *
			FROM ' . RANKS_TABLE . '
			ORDER BY rank_special DESC, rank_min ASC, rank_title ASC';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$rank_row = array(
				'S_RANK_IMAGE'		=> ($row['rank_image']) ? true : false,
				'S_SPECIAL_RANK'	=> ($row['rank_special']) ? true : false,

				'RANK_IMAGE'		=> $phpbb_root_path . $config['ranks_path'] . '/' . $row['rank_image'],
				'RANK_TITLE'		=> $row['rank_title'],
				'MIN_POSTS'			=> $row['rank_min'],

				'U_EDIT'			=> $this->u_action . '&amp;action=edit&amp;id=' . $row['rank_id'],
				'U_DELETE'			=> $this->u_action . '&amp;action=delete&amp;id=' . $row['rank_id'],
			);

			/**
			* Modify the template output array for each listed rank
			*
			* @event core.acp_ranks_list_modify_rank_row
			* @var	array	row			Array with the rank's data
			* @var	array	rank_row	Array with the rank's template data
			* @since 3.1.0-RC3
			*/
			$vars = array('row', 'rank_row');
			extract($phpbb_dispatcher->trigger_event('core.acp_ranks_list_modify_rank_row', compact($vars)));

			$template->assign_block_vars('ranks', $rank_row);
		}
		$db->sql_freeresult($result);

	}
}
