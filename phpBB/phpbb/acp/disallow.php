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

namespace phpbb\acp;

class disallow
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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

	/** @var string Disallowed username table */
	protected $disallow_table;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cache\driver\driver_interface	$cache				Cache object
	 * @param \phpbb\db\driver\driver_interface		$db					Database object
	 * @param \phpbb\language\language				$lang				Language object
	 * @param \phpbb\log\log						$log				Log object
	 * @param \phpbb\request\request				$request			Request object
	 * @param \phpbb\template\template				$template			Template object
	 * @param \phpbb\user							$user				User object
	 * @param string								$disallow_table		Disallowed username table
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$disallow_table
	)
	{
		$this->cache			= $cache;
		$this->db				= $db;
		$this->lang				= $lang;
		$this->log				= $log;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;

		$this->disallow_table	= $disallow_table;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang('acp/posting');

		// Set up general vars
		$allow		= $this->request->is_set_post('allow');
		$disallow	= $this->request->is_set_post('disallow');

		$form_key = 'acp_disallow';
		add_form_key($form_key);

		if (($allow || $disallow) && !check_form_key($form_key))
		{
			trigger_error($this->lang->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($disallow)
		{
			$disallowed_user = str_replace('*', '%', $this->request->variable('disallowed_user', '', true));

			if (!$disallowed_user)
			{
				trigger_error($this->lang->lang('NO_USERNAME_SPECIFIED') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT disallow_id
				FROM ' . $this->disallow_table . "
				WHERE disallow_username = '" . $this->db->sql_escape($disallowed_user) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row !== false)
			{
				trigger_error($this->lang->lang('DISALLOWED_ALREADY') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'INSERT INTO ' . $this->disallow_table . ' ' . $this->db->sql_build_array('INSERT', ['disallow_username' => $disallowed_user]);
			$this->db->sql_query($sql);

			$this->cache->destroy('_disallowed_usernames');

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DISALLOW_ADD', false, [str_replace('%', '*', $disallowed_user)]);

			trigger_error($this->lang->lang('DISALLOW_SUCCESSFUL') . adm_back_link($this->u_action));
		}
		else if ($allow)
		{
			$disallowed_id = $this->request->variable('disallowed_id', 0);

			if (!$disallowed_id)
			{
				trigger_error($this->lang->lang('NO_USERNAME_SPECIFIED') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'DELETE FROM ' . $this->disallow_table . '
				WHERE disallow_id = ' . (int) $disallowed_id;
			$this->db->sql_query($sql);

			$this->cache->destroy('_disallowed_usernames');

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DISALLOW_DELETE');

			trigger_error($this->lang->lang('DISALLOWED_DELETED') . adm_back_link($this->u_action));
		}

		$disallow_select = '';

		// Grab the current list of disallowed usernames...
		$sql = 'SELECT *
			FROM ' . $this->disallow_table;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$disallow_select .= '<option value="' . $row['disallow_id'] . '">' . str_replace('%', '*', $row['disallow_username']) . '</option>';
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'S_DISALLOWED_NAMES'	=> $disallow_select,
			'U_ACTION'				=> $this->u_action,
		]);

		$this->tpl_name = 'acp_disallow';
		$this->page_title = 'ACP_DISALLOW_USERNAMES';
		// @todo return $this->helper->render('acp_disallow.html', $this->lang->lang('ACP_DISALLOW_USERNAMES'));
	}
}
