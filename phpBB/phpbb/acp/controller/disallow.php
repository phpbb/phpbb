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

class disallow
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cache\driver\driver_interface	$cache				Cache object
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \phpbb\acp\helper\controller			$helper				ACP Controller helper object
	 * @param \phpbb\language\language				$lang				Language object
	 * @param \phpbb\log\log						$log				Log object
	 * @param \phpbb\request\request				$request			Request object
	 * @param \phpbb\template\template				$template			Template object
	 * @param \phpbb\user							$user				User object
	 * @param array									$tables				phpBB tables
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$tables
	)
	{
		$this->cache			= $cache;
		$this->db				= $db;
		$this->helper			= $helper;
		$this->lang				= $lang;
		$this->log				= $log;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;

		$this->tables			= $tables;
	}

	public function main()
	{
		$this->lang->add_lang('acp/posting');

		// Set up general vars
		$allow		= $this->request->is_set_post('allow');
		$disallow	= $this->request->is_set_post('disallow');

		$form_key = 'acp_disallow';
		add_form_key($form_key);

		if (($allow || $disallow) && !check_form_key($form_key))
		{
			throw new form_invalid_exception('acp_disallow_usernames');
		}

		if ($disallow)
		{
			$disallowed_user = str_replace('*', '%', $this->request->variable('disallowed_user', '', true));

			if (!$disallowed_user)
			{
				throw new back_exception(400, 'NO_USERNAME_SPECIFIED', 'acp_disallow_usernames');
			}

			$sql = 'SELECT disallow_id
				FROM ' . $this->tables['disallow'] . "
				WHERE disallow_username = '" . $this->db->sql_escape($disallowed_user) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row !== false)
			{
				throw new back_exception(400, 'DISALLOW_ALREADY', 'acp_disallow_usernames');
			}

			$sql = 'INSERT INTO ' . $this->tables['disallow'] . ' ' . $this->db->sql_build_array('INSERT', ['disallow_username' => $disallowed_user]);
			$this->db->sql_query($sql);

			$this->cache->destroy('_disallowed_usernames');

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DISALLOW_ADD', false, [str_replace('%', '*', $disallowed_user)]);

			return $this->helper->message_back('DISALLOW_SUCCESSFUL', 'acp_disallow_usernames');
		}
		else if ($allow)
		{
			$disallowed_id = $this->request->variable('disallowed_id', 0);

			if (!$disallowed_id)
			{
				throw new back_exception(400, 'NO_USERNAME_SPECIFIED', 'acp_disallow_usernames');
			}

			$sql = 'DELETE FROM ' . $this->tables['disallow'] . '
				WHERE disallow_id = ' . (int) $disallowed_id;
			$this->db->sql_query($sql);

			$this->cache->destroy('_disallowed_usernames');

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DISALLOW_DELETE');

			return $this->helper->message_back('DISALLOW_DELETED', 'acp_disallow_usernames');
		}

		$disallow_select = '';

		// Grab the current list of disallowed usernames...
		$sql = 'SELECT *
			FROM ' . $this->tables['disallow'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$disallow_select .= '<option value="' . $row['disallow_id'] . '">' . str_replace('%', '*', $row['disallow_username']) . '</option>';
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'S_DISALLOWED_NAMES'	=> $disallow_select,
			'U_ACTION'				=> $this->helper->route('acp_disallow_usernames'),
		]);

		return $this->helper->render('acp_disallow.html', 'ACP_DISALLOW_USERNAMES');
	}
}
