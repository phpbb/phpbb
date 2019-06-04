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

namespace phpbb\mcp\helper;

use phpbb\exception\http_exception;

class constructor implements \phpbb\cp\constructor_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cp\helper\identifiers */
	protected $cp_ids;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\auth\auth					$auth			Auth object
	 * @param \phpbb\cp\helper\identifiers		$cp_ids			Control panel identifiers object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\controller\helper			$helper			Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\cp\helper\identifiers $cp_ids,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->cp_ids		= $cp_ids;
		$this->db			= $db;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setup()
	{
		include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
		include($this->root_path . 'includes/functions_mcp.' . $this->php_ext);

		$this->lang->add_lang('mcp');

		// Setting a variable to let the style designer know where he is...
		$this->template->assign_var('S_IN_MCP', true);

		// Only Moderators can go beyond this point
		if (!$this->user->data['is_registered'])
		{
			if ($this->user->data['is_bot'])
			{
				redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
			}

			login_box('', $this->lang->lang('LOGIN_EXPLAIN_MCP'));
		}

		// Get all identifiers
		$this->cp_ids->get_identifiers('mcp');
		$forum_id	= $this->cp_ids->get_forum_id();
		$topic_id	= $this->cp_ids->get_topic_id();
		$post_id	= $this->cp_ids->get_post_id();

		// If the user doesn't have any moderator powers (globally or locally) he can't access the mcp
		if (!$this->auth->acl_getf_global('m_'))
		{
			$allow_user = false;

			// Except if the user is using one of the quickmod tools for users
			if ($this->request->is_set('quickmod', false))
			{
				$action = $this->request->variable('action', ['' => '']);
				$action = is_array($action) && !empty($action) ? key($action) : $this->request->variable('action', '');

				// User's quickmod action's authentication
				$action_auth = [
					'lock'			=> 'f_user_lock',
					'make_sticky'	=> 'f_sticky',
					'make_announce'	=> 'f_announce',
					'make_global'	=> 'f_announce_global',
					'make_normal'	=> ['f_announce', 'f_announce_global', 'f_sticky']
				];

				if (isset($action_auth[$action]) && $this->auth->acl_gets($action_auth[$action], $forum_id))
				{
					$topic_info = phpbb_get_topic_data([$topic_id]);

					if (!empty($topic_info[$topic_id]) && $topic_info[$topic_id]['topic_poster'] == $this->user->data['user_id'])
					{
						$allow_user = true;
					}
				}
			}

			if (!$allow_user)
			{
				throw new http_exception(403, 'NOT_AUTHORISED');
			}
		}

		// if the user cannot read the forum he tries to access then we won't allow mcp access either
		if ($forum_id && !$this->auth->acl_get('f_read', $forum_id))
		{
			throw new http_exception(403, 'NOT_AUTHORISED');
		}

		// Generate urls for letting the moderation control panel being accessed in different modes
		$this->template->assign_vars([
			'U_MCP'			=> $this->helper->route('mcp_index'),
			'U_MCP_FORUM'	=> ($forum_id) ? $this->helper->route('mcp_view_forum', ['f' => $forum_id]) : '',
			'U_MCP_TOPIC'	=> ($forum_id && $topic_id) ? $this->helper->route('mcp_view_topic', ['f' => $forum_id, 't' => $topic_id]) : '',
			'U_MCP_POST'	=> ($forum_id && $topic_id && $post_id) ? $this->helper->route('mcp_view_post', ['f' => $forum_id, 't' => $topic_id, 'p' => $post_id]) : '',
		]);
	}
}
