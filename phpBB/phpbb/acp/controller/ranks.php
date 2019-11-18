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

namespace phpbb\acp\controller;

class ranks
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB admin path */
	protected $admin_path;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpBB web path */
	protected $web_path;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 * @param \phpbb\config\config					$config			Config object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\event\dispatcher				$dispatcher		Event dispatcher object
	 * @param \phpbb\acp\helper\controller			$helper			ACP Controller helper object
	 * @param \phpbb\language\language				$language		Language object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\path_helper					$path_helper	Path helper object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 * @param string								$admin_path		phpBB admin path
	 * @param string								$root_path		phpBB root path
	 * @param array									$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\path_helper $path_helper,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$admin_path,
		$root_path,
		$tables
	)
	{
		$this->cache		= $cache;
		$this->config		= $config;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->language		= $language;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->admin_path	= $admin_path;
		$this->root_path	= $root_path;
		$this->web_path		= $path_helper->update_web_root_path($root_path);
		$this->tables		= $tables;
	}

	public function main()
	{
		$this->language->add_lang('acp/posting');

		// Set up general vars
		$action = $this->request->variable('action', '');
		$action = $this->request->is_set_post('add') ? 'add' : $action;
		$action = $this->request->is_set_post('save') ? 'save' : $action;
		$rank_id = $this->request->variable('id', 0);

		$form_key = 'acp_ranks';
		add_form_key($form_key);

		switch ($action)
		{
			case 'save':
				if (!check_form_key($form_key))
				{
					return trigger_error($this->language->lang('FORM_INVALID'). $this->helper->adm_back_route('acp_ranks'), E_USER_WARNING);
				}

				$rank_image = $this->request->variable('rank_image', '');
				$rank_title = $this->request->variable('title', '', true);
				$special_rank = $this->request->variable('special_rank', 0);
				$min_posts = $special_rank ? 0 : max(0, $this->request->variable('min_posts', 0));

				// The rank image has to be a jpg, gif or png
				if ($rank_image !== '' && !preg_match('#(\.gif|\.png|\.jpg|\.jpeg)$#i', $rank_image))
				{
					$rank_image = '';
				}

				if (!$rank_title)
				{
					return trigger_error($this->language->lang('NO_RANK_TITLE') . $this->helper->adm_back_route('acp_ranks'), E_USER_WARNING);
				}

				$sql_ary = [
					'rank_title'		=> $rank_title,
					'rank_special'		=> $special_rank,
					'rank_min'			=> $min_posts,
					'rank_image'		=> htmlspecialchars_decode($rank_image),
				];

				/**
				 * Modify the SQL array when saving a rank
				 *
				 * @event core.acp_ranks_save_modify_sql_ary
				 * @var int		rank_id		The ID of the rank (if available)
				 * @var array	sql_ary		Array with the rank's data
				 * @since 3.1.0-RC3
				 */
				$vars = ['rank_id', 'sql_ary'];
				extract($this->dispatcher->trigger_event('core.acp_ranks_save_modify_sql_ary', compact($vars)));

				if ($rank_id)
				{
					$sql = 'UPDATE ' . $this->tables['ranks'] . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . " WHERE rank_id = $rank_id";
					$message = $this->language->lang('RANK_UPDATED');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RANK_UPDATED', false, [$rank_title]);
				}
				else
				{
					$sql = 'INSERT INTO ' . $this->tables['ranks'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
					$message = $this->language->lang('RANK_ADDED');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RANK_ADDED', false, [$rank_title]);
				}
				$this->db->sql_query($sql);

				$this->cache->destroy('_ranks');

				return $this->helper->message_back($message, 'acp_ranks');
			break;

			case 'delete':
				if (!$rank_id)
				{
					return trigger_error($this->language->lang('MUST_SELECT_RANK') . $this->helper->adm_back_route('acp_ranks'), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$sql = 'SELECT rank_title
						FROM ' . $this->tables['ranks'] . '
						WHERE rank_id = ' . (int) $rank_id;
					$result = $this->db->sql_query($sql);
					$rank_title = (string) $this->db->sql_fetchfield('rank_title');
					$this->db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . $this->tables['ranks'] . "
						WHERE rank_id = $rank_id";
					$this->db->sql_query($sql);

					$sql = 'UPDATE ' . $this->tables['users'] . '
						SET user_rank = 0
						WHERE user_rank = ' . (int) $rank_id;
					$this->db->sql_query($sql);

					$this->cache->destroy('_ranks');

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_RANK_REMOVED', false, [$rank_title]);

					if ($this->request->is_ajax())
					{
						$json_response = new \phpbb\json_response;
						$json_response->send([
							'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
							'MESSAGE_TEXT'	=> $this->language->lang('RANK_REMOVED'),
							'REFRESH_DATA'	=> [
								'time'	=> 3,
							],
						]);
					}
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'rank_id'	=> $rank_id,
						'action'	=> 'delete',
					]));

					return redirect($this->helper->route('acp_ranks'));
				}
			break;

			case 'edit':
			case 'add':
				$ranks = $existing_imgs = [];

				$sql = 'SELECT *
					FROM ' . $this->tables['ranks'] . '
					ORDER BY rank_min ASC, rank_special ASC';
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					$existing_imgs[] = $row['rank_image'];

					if ($action === 'edit' && $rank_id == $row['rank_id'])
					{
						$ranks = $row;
					}
				}
				$this->db->sql_freeresult($result);

				$img_list = filelist($this->root_path . $this->config['ranks_path'], '');
				$edit_img = $filename_list = '';

				foreach ($img_list as $path => $img_ary)
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

						$filename_list .= '<option value="' . htmlspecialchars($img) . '"' . $selected . '>' . $img . (in_array($img, $existing_imgs) ? ' ' . $this->language->lang('RANK_IMAGE_IN_USE') : '') . '</option>';
					}
				}

				$filename_list = '<option value=""' . ($edit_img === '' ? ' selected="selected"' : '') . '>----------</option>' . $filename_list;
				unset($existing_imgs, $img_list);

				$tpl_ary = [
					'MIN_POSTS'			=> isset($ranks['rank_min']) && !$ranks['rank_special'] ? $ranks['rank_min'] : 0,
					'RANK_TITLE'		=> isset($ranks['rank_title']) ? $ranks['rank_title'] : '',
					'RANK_IMAGE'		=> $edit_img ? $this->web_path . $this->config['ranks_path'] . '/' . $edit_img : htmlspecialchars($this->web_path . $this->admin_path) . 'images/spacer.gif',
					'RANKS_PATH'		=> $this->web_path . $this->config['ranks_path'],
					'S_EDIT'			=> true,
					'S_FILENAME_LIST'	=> $filename_list,
					'S_SPECIAL_RANK'	=> isset($ranks['rank_special']) && $ranks['rank_special'],
					'U_ACTION'			=> $this->helper->route('acp_ranks', ['id' => $rank_id]),
					'U_BACK'			=> $this->helper->route('acp_ranks'),
				];

				/**
				 * Modify the template output array for editing/adding ranks
				 *
				 * @event core.acp_ranks_edit_modify_tpl_ary
				 * @var array	ranks		Array with the rank's data
				 * @var array	tpl_ary		Array with the rank's template data
				 * @since 3.1.0-RC3
				 */
				$vars = ['ranks', 'tpl_ary'];
				extract($this->dispatcher->trigger_event('core.acp_ranks_edit_modify_tpl_ary', compact($vars)));

				$this->template->assign_vars($tpl_ary);

				return $this->helper->render('acp_ranks.html', 'ACP_MANAGE_RANKS');
			break;
		}

		$this->template->assign_var('U_ACTION', $this->helper->route('acp_ranks'));

		$sql = 'SELECT *
			FROM ' . $this->tables['ranks'] . '
			ORDER BY rank_special DESC, rank_min ASC, rank_title ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rank_row = [
				'RANK_IMAGE'		=> $this->web_path . $this->config['ranks_path'] . '/' . $row['rank_image'],
				'RANK_TITLE'		=> $row['rank_title'],
				'MIN_POSTS'			=> $row['rank_min'],

				'S_RANK_IMAGE'		=> (bool) $row['rank_image'],
				'S_SPECIAL_RANK'	=> (bool) $row['rank_special'],

				'U_DELETE'			=> $this->helper->route('acp_ranks', ['action' => 'delete', 'id' => $row['rank_id']]),
				'U_EDIT'			=> $this->helper->route('acp_ranks', ['action' => 'edit', 'id' => $row['rank_id']]),
			];

			/**
			 * Modify the template output array for each listed rank
			 *
			 * @event core.acp_ranks_list_modify_rank_row
			 * @var array	row			Array with the rank's data
			 * @var array	rank_row	Array with the rank's template data
			 * @since 3.1.0-RC3
			 */
			$vars = ['row', 'rank_row'];
			extract($this->dispatcher->trigger_event('core.acp_ranks_list_modify_rank_row', compact($vars)));

			$this->template->assign_block_vars('ranks', $rank_row);
		}
		$this->db->sql_freeresult($result);

		return $this->helper->render('acp_ranks.html', $this->language->lang('ACP_MANAGE_RANKS'));
	}
}
