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

namespace phpbb\ucp\helper;

class constructor implements \phpbb\cp\constructor_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

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
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\controller\helper			$helper			Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $lang,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->helper		= $helper;
		$this->lang			= $lang;
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
		$this->lang->add_lang('ucp');

		require($this->root_path . 'includes/functions_user.' . $this->php_ext);

		// Only registered users can go beyond this point
		if (!$this->user->data['is_registered'])
		{
			if ($this->user->data['is_bot'])
			{
				redirect(append_sid("{$this->root_path}index.{$this->php_ext}"));
			}

			login_box('', $this->lang->lang('LOGIN_EXPLAIN_UCP'));
		}

		$this->output_friends();

		// Setting a variable to let the style designer know where he is...
		$this->template->assign_var('S_IN_UCP', true);

		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME'	=> $this->lang->lang('UCP'),
			'U_BREADCRUMB'		=> $this->helper->route('ucp_index'),
		]);
	}

	/**
	 * Output the user's friends list.
	 *
	 * @return void
	 */
	protected function output_friends()
	{
		// Output listing of friends online
		$update_time = $this->config['load_online_time'] * 60;

		$sql_ary = [
			'SELECT'	=> 'u.user_id, u.username, u.username_clean, u.user_colour, MAX(s.session_time) as online_time, MIN(s.session_viewonline) AS viewonline',

			'FROM'		=> [
				$this->tables['users']	=> 'u',
				$this->tables['zebra']	=> 'z',
			],

			'LEFT_JOIN'	=> [
				[
					'FROM'	=> [$this->tables['sessions'] => 's'],
					'ON'	=> 's.session_user_id = z.zebra_id',
				],
			],

			'WHERE'		=> 'z.friend = 1
				AND u.user_id = z.zebra_id
				AND z.user_id = ' . (int) $this->user->data['user_id'],

			'GROUP_BY'	=> 'z.zebra_id, u.user_id, u.username_clean, u.user_colour, u.username',

			'ORDER_BY'	=> 'u.username_clean ASC',
		];

		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_ary);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$which = (time() - $update_time < $row['online_time'] && ($row['viewonline'] || $this->auth->acl_get('u_viewonline'))) ? 'online' : 'offline';

			$this->template->assign_block_vars("friends_{$which}", [
				'USER_ID'		=> $row['user_id'],

				'U_PROFILE'		=> get_username_string('profile', $row['user_id'], $row['username'], $row['user_colour']),
				'USER_COLOUR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME'		=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour']),
				'USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
			]);
		}
		$this->db->sql_freeresult($result);
	}
}
