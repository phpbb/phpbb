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

namespace phpbb\members\controller;

use phpbb\auth\auth;
use phpbb\db\driver\driver_interface;
use phpbb\controller\helper;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;

class online_whois
{
	/** @var auth */
	protected $auth;

	/** @var driver_interface */
	protected $db;

	/** @var helper */
	protected $helper;

	/** @var language */
	protected $language;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string */
	private $users_table;

	/** @var string */
	private $sessions_table;

	/** @var string */
	private $phpbb_root_path;

	/** @var string */
	private $php_ex;

	/**
	 * online_whois constructor.
	 * @param auth $auth
	 * @param driver_interface $db
	 * @param helper $helper
	 * @param language $language
	 * @param template $template
	 * @param user $user
	 * @param string $users_table
	 * @param string $sessions_table
	 * @param string $phpbb_root_path
	 * @param string $php_ex
	 */
	public function __construct(auth $auth, driver_interface $db, helper $helper, language $language, template $template, user $user, string $users_table, string $sessions_table, string $phpbb_root_path, string $php_ex)
	{
		$this->auth				= $auth;
		$this->db				= $db;
		$this->helper			= $helper;
		$this->language			= $language;
		$this->template			= $template;
		$this->user				= $user;
		$this->users_table		= $users_table;
		$this->sessions_table	= $sessions_table;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ex			= $php_ex;
	}

	/**
	 * Controller for /online/whois/{session_id} route
	 *
	 * @param $session_id
	 * @return Response a Symfony response object
	 */
	public function handle($session_id): Response
	{
		if (!function_exists('user_ipwhois'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ex);
		}

		// Load language strings
		$this->language->add_lang('memberlist');

		// Can this user view profiles/memberlist?
		if (!$this->auth->acl_gets('u_viewprofile', 'a_user', 'a_useradd', 'a_userdel'))
		{
			if ($this->user->data['user_id'] != ANONYMOUS)
			{
				throw new http_exception(403, 'NO_VIEW_USERS');
			}

			login_box('', $this->language->lang('LOGIN_EXPLAIN_VIEWONLINE'));
		}

		if (!$this->auth->acl_get('a_'))
		{
			$this->language->add_lang('acp/common');

			throw new http_exception(403, 'NO_ADMIN');
		}

		$sql = 'SELECT u.user_id, u.username, u.user_type, s.session_ip
			FROM ' . $this->users_table . ' u, ' . $this->sessions_table . " s
			WHERE s.session_id = '" . $this->db->sql_escape($session_id) . "'
				AND	u.user_id = s.session_user_id";
		$result = $this->db->sql_query($sql);

		if ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_var('WHOIS', user_ipwhois($row['session_ip']));
		}
		$this->db->sql_freeresult($result);

		// Render
		return $this->helper->render('viewonline_whois.html', $this->language->lang('WHO_IS_ONLINE'));
	}
}
