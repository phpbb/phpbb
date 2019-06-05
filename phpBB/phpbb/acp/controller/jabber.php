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

use phpbb\exception\form_invalid_exception;
use phpbb\exception\http_exception;

/**
 * @todo Check/enter/update transport info
 */
class jabber
{
	/** @var \phpbb\config\config */
	protected $config;

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

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config			Config object
	 * @param \phpbb\db\driver\driver_interface	$db				Database object
	 * @param \phpbb\acp\helper\controller		$helper			ACP Controller helper object
	 * @param \phpbb\language\language			$lang			Language object
	 * @param \phpbb\log\log					$log			Log object
	 * @param \phpbb\request\request			$request		Request object
	 * @param \phpbb\template\template			$template		Template object
	 * @param \phpbb\user						$user			User object
	 * @param string							$root_path		phpBB root path
	 * @param string							$php_ext		php File extension
	 * @param array								$tables			phpBB tables
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->config		= $config;
		$this->db			= $db;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	public function main()
	{
		$this->lang->add_lang('acp/board');

		if (!class_exists('jabber'))
		{
			include($this->root_path . 'includes/functions_jabber.' . $this->php_ext);
		}

		$submit = $this->request->is_set_post('submit');

		$jab_enable				= $this->request->variable('jab_enable',			(bool) $this->config['jab_enable']);
		$jab_host				= $this->request->variable('jab_host',				(string) $this->config['jab_host']);
		$jab_port				= $this->request->variable('jab_port',				(int) $this->config['jab_port']);
		$jab_username			= $this->request->variable('jab_username',			(string) $this->config['jab_username']);
		$jab_password			= $this->request->variable('jab_password',			(string) $this->config['jab_password']);
		$jab_package_size		= $this->request->variable('jab_package_size',		(int) $this->config['jab_package_size']);
		$jab_use_ssl			= $this->request->variable('jab_use_ssl',			(bool) $this->config['jab_use_ssl']);
		$jab_verify_peer		= $this->request->variable('jab_verify_peer',		(bool) $this->config['jab_verify_peer']);
		$jab_verify_peer_name	= $this->request->variable('jab_verify_peer_name',	(bool) $this->config['jab_verify_peer_name']);
		$jab_allow_self_signed	= $this->request->variable('jab_allow_self_signed',	(bool) $this->config['jab_allow_self_signed']);

		$form_key = 'acp_jabber';
		add_form_key($form_key);

		if ($submit)
		{
			if (!check_form_key($form_key))
			{
				throw new form_invalid_exception('acp_settings_jabber');
			}

			// Is this feature enabled? Then try to establish a connection
			if ($jab_enable)
			{
				$jabber = new \jabber($jab_host, $jab_port, $jab_username, $jab_password, $jab_use_ssl, $jab_verify_peer, $jab_verify_peer_name, $jab_allow_self_signed);

				if (!$jabber->connect())
				{
					// @todo <br> tags in exceptions
					throw new http_exception(400, $this->lang->lang('ERR_JAB_CONNECT') . '<br /><br />' . $jabber->get_log() . $this->helper->adm_back_link('acp_settings_jabber'));
				}

				// We'll try to authorise using this account
				if (!$jabber->login())
				{
					// @todo <br> tags in exceptions
					throw new http_exception(400, $this->lang->lang('ERR_JAB_AUTH') . '<br /><br />' . $jabber->get_log() . $this->helper->adm_back_link('acp_settings_jabber'));
				}

				$jabber->disconnect();
			}
			else
			{
				// This feature is disabled.
				// We update the user table to be sure all users that have IM as notify type are set to both as notify type
				// We set this to both because users still have their jabber address entered
				// and may want to receive jabber notifications again once it is re-enabled.
				$sql = 'UPDATE ' . $this->tables['users'] . '
					SET user_notify_type = ' . NOTIFY_BOTH . '
					WHERE user_notify_type = ' . NOTIFY_IM;
				$this->db->sql_query($sql);
			}

			$this->config->set('jab_enable', $jab_enable);
			$this->config->set('jab_host', $jab_host);
			$this->config->set('jab_port', $jab_port);
			$this->config->set('jab_username', $jab_username);
			if ($jab_password !== '********')
			{
				$this->config->set('jab_password', $jab_password);
			}
			$this->config->set('jab_package_size', $jab_package_size);
			$this->config->set('jab_use_ssl', $jab_use_ssl);
			$this->config->set('jab_verify_peer', $jab_verify_peer);
			$this->config->set('jab_verify_peer_name', $jab_verify_peer_name);
			$this->config->set('jab_allow_self_signed', $jab_allow_self_signed);

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_JAB_SETTINGS_CHANGED');

			return $this->helper->message_back('JAB_SETTINGS_CHANGED', 'acp_settings_jabber');
		}

		$this->template->assign_vars([
			'U_ACTION'				=> $this->helper->route('acp_settings_jabber'),
			'JAB_ENABLE'			=> $jab_enable,
			'L_JAB_SERVER_EXPLAIN'	=> $this->lang->lang('JAB_SERVER_EXPLAIN', '<a href="http://www.jabber.org/">', '</a>'),
			'JAB_HOST'				=> $jab_host,
			'JAB_PORT'				=> $jab_port ? $jab_port : '',
			'JAB_USERNAME'			=> $jab_username,
			'JAB_PASSWORD'			=> $jab_password !== '' ? '********' : '',
			'JAB_PACKAGE_SIZE'		=> $jab_package_size,
			'JAB_USE_SSL'			=> $jab_use_ssl,
			'JAB_VERIFY_PEER'		=> $jab_verify_peer,
			'JAB_VERIFY_PEER_NAME'	=> $jab_verify_peer_name,
			'JAB_ALLOW_SELF_SIGNED'	=> $jab_allow_self_signed,
			'S_CAN_USE_SSL'			=> \jabber::can_use_ssl(),
			'S_GTALK_NOTE'			=> (!@function_exists('dns_get_record')) ? true : false,
		]);

		return $this->helper->render('acp_jabber.html', 'ACP_SETTINGS_JABBER');
	}
}
