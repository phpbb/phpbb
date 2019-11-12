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
 * @todo Check/enter/update transport info
 */

namespace phpbb\acp\controller;

class jabber
{
	var $u_action;

	public function main($id, $mode)
	{
		$this->language->add_lang('acp/board');

		if (!class_exists('jabber'))
		{
			include($this->root_path . 'includes/functions_jabber.' . $this->php_ext);
		}

		$submit = ($this->request->is_set_post('submit')) ? true : false;

		if ($mode != 'settings')
		{
			return;
		}

		$this->tpl_name = 'acp_jabber';
		$this->page_title = 'ACP_JABBER_SETTINGS';

		$jab_enable				= $this->request->variable('jab_enable',			(bool) $this->config['jab_enable']);
		$jab_host				= $this->request->variable('jab_host',			(string) $this->config['jab_host']);
		$jab_port				= $this->request->variable('jab_port',			(int) $this->config['jab_port']);
		$jab_username			= $this->request->variable('jab_username',		(string) $this->config['jab_username']);
		$jab_password			= $this->request->variable('jab_password',		(string) $this->config['jab_password']);
		$jab_package_size		= $this->request->variable('jab_package_size',	(int) $this->config['jab_package_size']);
		$jab_use_ssl			= $this->request->variable('jab_use_ssl',		(bool) $this->config['jab_use_ssl']);
		$jab_verify_peer		= $this->request->variable('jab_verify_peer',		(bool) $this->config['jab_verify_peer']);
		$jab_verify_peer_name	= $this->request->variable('jab_verify_peer_name',	(bool) $this->config['jab_verify_peer_name']);
		$jab_allow_self_signed	= $this->request->variable('jab_allow_self_signed',	(bool) $this->config['jab_allow_self_signed']);

		$form_name = 'acp_jabber';
		add_form_key($form_name);

		if ($submit)
		{
			if (!check_form_key($form_name))
			{
				trigger_error($this->language->lang('FORM_INVALID'). adm_back_link($this->u_action), E_USER_WARNING);
			}

			$message = $this->language->lang('JAB_SETTINGS_CHANGED');
			$log = 'JAB_SETTINGS_CHANGED';

			// Is this feature enabled? Then try to establish a connection
			if ($jab_enable)
			{
				$jabber = new jabber($jab_host, $jab_port, $jab_username, $jab_password, $jab_use_ssl, $jab_verify_peer, $jab_verify_peer_name, $jab_allow_self_signed);

				if (!$jabber->connect())
				{
					trigger_error($this->language->lang('ERR_JAB_CONNECT') . '<br /><br />' . $jabber->get_log() . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// We'll try to authorise using this account
				if (!$jabber->login())
				{
					trigger_error($this->language->lang('ERR_JAB_AUTH') . '<br /><br />' . $jabber->get_log() . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$jabber->disconnect();
			}
			else
			{
				// This feature is disabled.
				// We update the user table to be sure all users that have IM as notify type are set to both as notify type
				// We set this to both because users still have their jabber address entered and may want to receive jabber notifications again once it is re-enabled.
				$sql_ary = [
					'user_notify_type'		=> NOTIFY_BOTH,
				];

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
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

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_' . $log);
			trigger_error($message . adm_back_link($this->u_action));
		}

		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action,
			'JAB_ENABLE'			=> $jab_enable,
			'L_JAB_SERVER_EXPLAIN'	=> sprintf($this->language->lang('JAB_SERVER_EXPLAIN'), '<a href="http://www.jabber.org/">', '</a>'),
			'JAB_HOST'				=> $jab_host,
			'JAB_PORT'				=> ($jab_port) ? $jab_port : '',
			'JAB_USERNAME'			=> $jab_username,
			'JAB_PASSWORD'			=> $jab_password !== '' ? '********' : '',
			'JAB_PACKAGE_SIZE'		=> $jab_package_size,
			'JAB_USE_SSL'			=> $jab_use_ssl,
			'JAB_VERIFY_PEER'		=> $jab_verify_peer,
			'JAB_VERIFY_PEER_NAME'	=> $jab_verify_peer_name,
			'JAB_ALLOW_SELF_SIGNED'	=> $jab_allow_self_signed,
			'S_CAN_USE_SSL'			=> jabber::can_use_ssl(),
			'S_GTALK_NOTE'			=> (!@function_exists('dns_get_record')) ? true : false,
		]);
	}
}
