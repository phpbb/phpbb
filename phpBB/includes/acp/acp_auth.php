<?php
/**
*
* @package acp
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_auth {
	var $u_action;
	var $new_config;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/auth');

		$form_key = 'acp_auth';
		add_form_key($form_key);

		switch($mode)
		{
			case 'index':
			$this->page_title = 'ACP_AUTH';
			$this->tpl_name = 'acp_auth';
			$display_vars = array(
					'title'	=> 'ACP_AUTH_SETTINGS',
					'vars'	=> array(
					)
				);
			break;
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if wished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		// Temporary array until provider list is generated dynamically
		$providers_loop = array(
			'facebook_connect'	=> array(
				'ENABLED' => false,
			),
			'olympus'			=> array(
				'ENABLED' => true,
			),
			'openid'			=> array(
				'ENABLED' => true,
			),
		);

		foreach($providers_loop as $provider => $index) {
			if ($index['ENABLED'] == true)
			{
				$enabled_disabled = '<input type="radio" name="' . $provider . '_ENABLED_DISABLED" value="ENABLED" checked> {L_ENABLED} <input type="radio" name="' . $provider . '_ENABLED_DISABLED" value="DISABLED"> {L_DISABLED}';
			}
			else
			{
				$enabled_disabled = '<input type="radio" name="' . $provider . '_ENABLED_DISABLED" value="ENABLED"> {L_ENABLED} <input type="radio" name="' . $provider . '_ENABLED_DISABLED" value="DISABLED" checked> {L_DISABLED}';
			}

			$template->assign_block_vars('providers_loop', array(
				'PROVIDER'			=> $provider,
				'ENABLED_DISABLED'	=> $enabled_disabled,
			));
		}

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action,
		));
	}
}
