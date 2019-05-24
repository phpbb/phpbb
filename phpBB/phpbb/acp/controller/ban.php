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

use phpbb\exception\back_exception;
use phpbb\exception\form_invalid_exception;

class ban
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\acp\helper\controller */
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
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\event\dispatcher			$dispatcher		Event dispatcher object
	 * @param \phpbb\acp\helper\controller		$helper			ACP Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main($mode)
	{
		$this->lang->add_lang(['acp/ban', 'acp/users']);

		if (!function_exists('user_ban'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$submit_ban		= $this->request->is_set_post('bansubmit');
		$submit_unban	= $this->request->is_set_post('unbansubmit');

		$u_mode = "acp_ban_{$mode}s";

		$form_key = 'acp_ban';
		add_form_key($form_key);

		if (($submit_ban || $submit_unban) && !check_form_key($form_key))
		{
			throw new form_invalid_exception($u_mode);
		}

		// Ban submitted?
		if ($submit_ban)
		{
			// Grab the list of entries
			$ban				= $this->request->variable('ban', '', true);
			$ban_length			= $this->request->variable('banlength', 0);
			$ban_length_other	= $this->request->variable('banlengthother', '');
			$ban_exclude		= $this->request->variable('banexclude', 0);
			$ban_reason			= $this->request->variable('banreason', '', true);
			$ban_give_reason	= $this->request->variable('bangivereason', '', true);

			if ($ban)
			{
				$abort_ban = false;

				/**
				 * Use this event to modify the ban details before the ban is performed
				 *
				 * @event core.acp_ban_before
				 * @var string	mode				One of the following: user, ip, email
				 * @var string	ban					Either string or array with usernames, ips or email addresses
				 * @var int		ban_length			Ban length in minutes
				 * @var string	ban_length_other	Ban length as a date (YYYY-MM-DD)
				 * @var bool	ban_exclude			Are we banning or excluding from another ban
				 * @var string	ban_reason			Ban reason displayed to moderators
				 * @var string	ban_give_reason		Ban reason displayed to the banned user
				 * @var mixed	abort_ban			Either false, or an error message that is displayed to the user.
				 *									If a string is given the bans are not issued.
				 * @since 3.1.0-RC5
				 */
				$vars = [
					'mode',
					'ban',
					'ban_length',
					'ban_length_other',
					'ban_exclude',
					'ban_reason',
					'ban_give_reason',
					'abort_ban',
				];
				extract($this->dispatcher->trigger_event('core.acp_ban_before', compact($vars)));

				if ($abort_ban)
				{
					throw new back_exception(400, $abort_ban, $u_mode);
				}

				user_ban($mode, $ban, $ban_length, $ban_length_other, $ban_exclude, $ban_reason, $ban_give_reason);

				/**
				 * Use this event to perform actions after the ban has been performed
				 *
				 * @event core.acp_ban_after
				 * @var string	mode				One of the following: user, ip, email
				 * @var string	ban					Either string or array with usernames, ips or email addresses
				 * @var int		ban_length			Ban length in minutes
				 * @var string	ban_length_other	Ban length as a date (YYYY-MM-DD)
				 * @var bool	ban_exclude			Are we banning or excluding from another ban
				 * @var string	ban_reason			Ban reason displayed to moderators
				 * @var string	ban_give_reason		Ban reason displayed to the banned user
				 * @since 3.1.0-RC5
				 */
				$vars = [
					'mode',
					'ban',
					'ban_length',
					'ban_length_other',
					'ban_exclude',
					'ban_reason',
					'ban_give_reason',
				];
				extract($this->dispatcher->trigger_event('core.acp_ban_after', compact($vars)));

				return $this->helper->message_back('BAN_UPDATE_SUCCESSFUL', $u_mode);
			}
		}
		else if ($submit_unban)
		{
			$ban = $this->request->variable('unban', ['']);

			if ($ban)
			{
				user_unban($mode, $ban);

				return $this->helper->message_back('BAN_UPDATE_SUCCESSFUL', $u_mode);
			}
		}

		$this->display_ban_options($mode);

		// Define language vars
		$mode_upper = strtoupper($mode);
		$l_title	= $this->lang->lang($mode_upper . '_BAN');
		$l_ban_cell	= [
			'email'	=> 'EMAIL_ADDRESS',
			'ip'	=> 'IP_HOSTNAME',
			'user'	=> 'USERNAME',
		];

		$this->template->assign_vars([
			'L_TITLE'				=> $l_title,
			'L_EXPLAIN'				=> $this->lang->lang($mode_upper . '_BAN_EXPLAIN'),
			'L_UNBAN_TITLE'			=> $this->lang->lang($mode_upper . '_UNBAN'),
			'L_UNBAN_EXPLAIN'		=> $this->lang->lang($mode_upper . '_UNBAN_EXPLAIN'),
			'L_BAN_CELL'			=> $this->lang->lang($l_ban_cell[$mode]),
			'L_BAN_EXCLUDE_EXPLAIN'	=> $this->lang->lang($mode_upper . '_BAN_EXCLUDE_EXPLAIN'),
			'L_NO_BAN_CELL'			=> $this->lang->lang($mode_upper . '_NO_BANNED'),

			'S_USERNAME_BAN'		=> $mode === 'user',

			'U_ACTION'				=> $this->helper->route($u_mode),
			'U_FIND_USERNAME'		=> append_sid("{$this->root_path}memberlist.$this->php_ext", 'mode=searchuser&amp;form=acp_ban&amp;field=ban'),
		]);

		return $this->helper->render('acp_ban.html', $l_title);
	}

	/**
	 * Display ban options.
	 *
	 * @html
	 *
	 * @param string	$mode	The ban mode(user|ip|email)
	 * @return void
	 */
	public function display_ban_options($mode)
	{
		$sql = $field = '';
		$ban_end_options = $options = '';
		$banned_options = $excluded_options = [];

		// Ban length options
		$ban_end_text = [0 => $this->lang->lang('PERMANENT'), 30 => $this->lang->lang('30_MINS'), 60 => $this->lang->lang('1_HOUR'), 360 => $this->lang->lang('6_HOURS'), 1440 => $this->lang->lang('1_DAY'), 10080 => $this->lang->lang('7_DAYS'), 20160 => $this->lang->lang('2_WEEKS'), 40320 => $this->lang->lang('1_MONTH'), -1 => $this->lang->lang('UNTIL') . ' -&gt; '];

		foreach ($ban_end_text as $length => $text)
		{
			$ban_end_options .= '<option value="' . $length . '">' . $text . '</option>';
		}

		switch ($mode)
		{
			case 'user':
				$field = 'username';

				$sql = 'SELECT b.*, u.user_id, u.username, u.username_clean
					FROM ' . $this->tables['banlist'] . ' b, ' . $this->tables['users'] . ' u
					WHERE (b.ban_end >= ' . time() . ' OR b.ban_end = 0)
						AND u.user_id = b.ban_userid
					ORDER BY u.username_clean ASC';
			break;

			case 'ip':
				$field = 'ban_ip';

				$sql = 'SELECT *
					FROM ' . $this->tables['banlist'] . '
					WHERE (ban_end >= ' . time() . " OR ban_end = 0)
						AND ban_ip <> ''
					ORDER BY ban_ip";
			break;

			case 'email':
				$field = 'ban_email';

				$sql = 'SELECT *
					FROM ' . $this->tables['banlist'] . '
					WHERE (ban_end >= ' . time() . " OR ban_end = 0)
						AND ban_email <> ''
					ORDER BY ban_email";
			break;
		}

		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$option = '<option value="' . $row['ban_id'] . '">' . $row[$field] . '</option>';

			if ($row['ban_exclude'])
			{
				$excluded_options[] = $option;
			}
			else
			{
				$banned_options[] = $option;
			}

			$time_length = $row['ban_end'] ? ($row['ban_end'] - $row['ban_start']) / 60 : 0;

			if ($time_length === 0)
			{
				// Banned permanently
				$ban_length = $this->lang->lang('PERMANENT');
			}
			else if (isset($ban_end_text[$time_length]))
			{
				// Banned for a given duration
				$ban_length = $this->lang->lang('BANNED_UNTIL_DURATION', $ban_end_text[$time_length], $this->user->format_date($row['ban_end'], false, true));
			}
			else
			{
				// Banned until given date
				$ban_length = $this->lang->lang('BANNED_UNTIL_DATE', $this->user->format_date($row['ban_end'], false, true));
			}

			$this->template->assign_block_vars('bans', [
				'BAN_ID'		=> (int) $row['ban_id'],
				'LENGTH'		=> $ban_length,
				'A_LENGTH'		=> addslashes($ban_length),
				'REASON'		=> $row['ban_reason'],
				'A_REASON'		=> addslashes($row['ban_reason']),
				'GIVE_REASON'	=> $row['ban_give_reason'],
				'A_GIVE_REASON'	=> addslashes($row['ban_give_reason']),
			]);
		}
		$this->db->sql_freeresult($result);

		if ($excluded_options)
		{
			$options .= '<optgroup label="' . $this->lang->lang('OPTIONS_EXCLUDED') . '">';
			$options .= implode('', $excluded_options);
			$options .= '</optgroup>';
		}

		if ($banned_options)
		{
			$options .= '<optgroup label="' . $this->lang->lang('OPTIONS_BANNED') . '">';
			$options .= implode('', $banned_options);
			$options .= '</optgroup>';
		}

		$this->template->assign_vars([
			'BANNED_OPTIONS'	=> $options,
			'S_BANNED_OPTIONS'	=> $banned_options || $excluded_options,
			'S_BAN_END_OPTIONS'	=> $ban_end_options,
		]);
	}
}
