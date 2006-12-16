<?php
/** 
*
* @package acp
* @version $Id$
* @copyright (c) 2005 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
* @todo Check/enter/update transport info
*/

/**
* @package acp
*/
class acp_jabber
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/board');

		include_once($phpbb_root_path . 'includes/functions_jabber.' . $phpEx);

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		if ($mode != 'settings')
		{
			return;
		}

		$this->tpl_name = 'acp_jabber';
		$this->page_title = 'ACP_JABBER_SETTINGS';

		$jab_enable			= request_var('jab_enable', $config['jab_enable']);
		$jab_host			= request_var('jab_host', $config['jab_host']);
		$jab_port			= request_var('jab_port', $config['jab_port']);
		$jab_username		= request_var('jab_username', $config['jab_username']);
		$jab_password		= request_var('jab_password', $config['jab_password']);
		$jab_resource		= request_var('jab_resource', $config['jab_resource']);
		$jab_package_size	= request_var('jab_package_size', $config['jab_package_size']);

		$jabber = new jabber($jab_host, $jab_port, $jab_username, $jab_password, $jab_resource);
		$error = array();

		$message = $user->lang['JAB_SETTINGS_CHANGED'];
		$log = 'JAB_SETTINGS_CHANGED';

		// Are changing (or initialising) a new host or username? If so run some checks and 
		// try to create account if it doesn't exist
		if ($jab_enable)
		{
			if (($jab_host != $config['jab_host'] || $jab_username != $config['jab_username']) && $jab_username)
			{
				if (!$jabber->connect())
				{
					trigger_error($user->lang['ERR_JAB_CONNECT'] . $jabber->get_log() . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// First we'll try to authorise using this account, if that fails we'll try to create it.
				if (!($result = $jabber->send_auth()))
				{
					if (($result = $jabber->account_registration($config['board_email'], $config['sitename'])) <> 2)
					{
						$error[] = ($result == 1) ? $user->lang['ERR_JAB_USERNAME'] : sprintf($user->lang['ERR_JAB_REGISTER'], $result);
					}
					else
					{
						$message = $user->lang['JAB_REGISTERED'];
						$log = 'JAB_REGISTER';
					}
				}
				else
				{
					$message = $user->lang['JAB_CHANGED'];
					$log = 'JAB_CHANGED';
				}

				sleep(1);
				$jabber->disconnect();
			}
			else if ($jab_password != $config['jab_password'])
			{
				if (!$jabber->connect())
				{
					trigger_error($user->lang['ERR_JAB_CONNECT'] . $jabber->get_log() . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$jabber->send_auth())
				{
					trigger_error($user->lang['ERR_JAB_AUTH'] . $jabber->get_log() . adm_back_link($this->u_action), E_USER_WARNING);
				}
				$jabber->send_presence(NULL, NULL, 'online');

				if (($result = $jabber->change_password($jab_password)) <> 2)
				{
					$error[] = ($result == 1) ? $user->lang['ERR_JAB_PASSCHG'] : sprintf($user->lang['ERR_JAB_PASSFAIL'], $result);
				}
				else
				{
					$message = $user->lang['JAB_PASS_CHANGED'];
					$log = 'JAB_PASSCHG';
				}

				sleep(1);
				$jabber->disconnect();
			}
		}

		// Pull relevant config data
		$sql = 'SELECT *
			FROM ' . CONFIG_TABLE . "
			WHERE config_name LIKE 'jab_%'";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$config_name = $row['config_name'];
			$config_value = $row['config_value'];

			$default_config[$config_name] = $config_value;
			$new[$config_name] = (isset($_POST[$config_name])) ? request_var($config_name, '') : $default_config[$config_name];

			if ($submit && !sizeof($error))
			{
				set_config($config_name, $new[$config_name]);
			}
		}

		if ($submit && !sizeof($error))
		{
			add_log('admin', 'LOG_' . $log);
			trigger_error($message . adm_back_link($this->u_action));
		}

		if (sizeof($error))
		{
			$template->assign_vars(array(
				'S_WARNING'		=> true,
				'WARNING_MSG'	=> implode('<br />', $error))
			);
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'JAB_ENABLE'			=> $new['jab_enable'],
			'L_JAB_SERVER_EXPLAIN'	=> sprintf($user->lang['JAB_SERVER_EXPLAIN'], '<a href="http://www.jabber.org/">', '</a>'),
			'JAB_HOST'				=> $new['jab_host'],
			'JAB_PORT'				=> $new['jab_port'],
			'JAB_USERNAME'			=> $new['jab_username'],
			'JAB_PASSWORD'			=> $new['jab_password'],
			'JAB_RESOURCE'			=> $new['jab_resource'],
			'JAB_PACKAGE_SIZE'		=> $new['jab_package_size'])
		);		
	}
}

?>