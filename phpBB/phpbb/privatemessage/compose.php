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

namespace phpbb\privatemessage;

class compose
{
	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\request\request
	 */
	protected $request;

	/**
	 * @var string
	 */
	protected $root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\request\request $request, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->config = $config;
		$this->db = $db;
		$this->language = $language;
		$this->template = $template;
		$this->request = $request;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function handle()
	{
		if (!$this->user->data['is_registered'])
		{
			return $this->helper->error('NO_MESSAGE', 401);
		}

		if (!$this->config['allow_privmsg'])
		{
			return $this->helper->error('PM_DISABLED', 403);
		}

		$this->language->add_lang(array('privatemessage', 'ucp', 'posting'));

		include($this->root_path . 'includes/ucp/ucp_pm_compose.' . $this->php_ext);
		compose_pm($this->request->variable('action', ''));

		$this->template->assign_vars(array(
			'S_PM_COMPOSE'	=> true,
		));

		return $this->helper->render('posting_pm_layout.html', '');
	}
}
