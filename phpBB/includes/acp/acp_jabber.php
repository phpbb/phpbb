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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_jabber
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/board');

		if (!class_exists('jabber'))
		{
			include($phpbb_root_path . 'includes/functions_jabber.' . $phpEx);
		}

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		if ($mode != 'settings')
		{
			return;
		}

		$this->tpl_name = 'acp_jabber';
		$this->page_title = 'ACP_JABBER_SETTINGS';

		$jab_enable			= request_var('jab_enable',			(bool) $config['jab_enable']);
		$jab_host			= request_var('jab_host',			(string) $config['jab_host']);
		$jab_port			= request_var('jab_port',			(int) $config['jab_port']);
		$jab_username		= request_var('jab_username',		(string) $config['jab_username']);
		$jab_password		= request_var('jab_password',		(string) $config['jab_password']);
		$jab_package_size	= request_var('jab_package_size',	(int) $config['jab_package_size']);
		$jab_use_ssl		= request_var('jab_use_ssl',		(bool) $config['jab_use_ssl']);

		$form_name = 'acp_jabber';
		add_form_key($form_name);

		if ($submit)
		{
			if (!check_form_key($form_name))
			{
				trigger_error($user->lang['FORM_INVALID']. adm_back_link($this->u_action), E_USER_WARNING);
			}

			$error = array();

			$message = $user->lang['JAB_SETTINGS_CHANGED'];
			$log = 'JAB_SETTINGS_CHANGED';

			// Is this feature enabled? Then try to establish a connection
			if ($jab_enable)
			{
				$jabber = new jabber($jab_host, $jab_port, $jab_username, $jab_password, $jab_use_ssl);

				if (!$jabber->connect())
				{
					trigger_error($user->lang['ERR_JAB_CONNECT'] . '<br /><br />' . $jabber->get_log() . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// We'll try to authorise using this account
				if (!$jabber->login())
				{
					trigger_error($user->lang['ERR_JAB_AUTH'] . '<br /><br />' . $jabber->get_log() . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$jabber->disconnect();
			}
			else
			{
				// This feature is disabled.
				// We update the user table to be sure all users that have IM as notify type are set to both as notify type
				// We set this to both because users still have their jabber address entered and may want to receive jabber notifications again once it is re-enabled.
				$sql_ary = array(
					'user_notify_type'		=> NOTIFY_BOTH,
				);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
					WHERE user_notify_type = ' . NOTIFY_IM;
				$db->sql_query($sql);
			}

			set_config('jab_enable', $jab_enable);
			set_config('jab_host', $jab_host);
			set_config('jab_port', $jab_port);
			set_config('jab_username', $jab_username);
			if ($jab_password !== '********')
			{
				set_config('jab_password', $jab_password);
			}
			set_config('jab_package_size', $jab_package_size);
			set_config('jab_use_ssl', $jab_use_ssl);

			add_log('admin', 'LOG_' . $log);
			trigger_error($message . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'JAB_ENABLE'			=> $jab_enable,
			'L_JAB_SERVER_EXPLAIN'	=> sprintf($user->lang['JAB_SERVER_EXPLAIN'], '<a href="http://www.jabber.org/">', '</a>'),
			'JAB_HOST'				=> $jab_host,
			'JAB_PORT'				=> ($jab_port) ? $jab_port : '',
			'JAB_USERNAME'			=> $jab_username,
			'JAB_PASSWORD'			=> $jab_password !== '' ? '********' : '',
			'JAB_PACKAGE_SIZE'		=> $jab_package_size,
			'JAB_USE_SSL'			=> $jab_use_ssl,
			'S_CAN_USE_SSL'			=> jabber::can_use_ssl(),
			'S_GTALK_NOTE'			=> (!@function_exists('dns_get_record')) ? true : false,
		));
	}
}
