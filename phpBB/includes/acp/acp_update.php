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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_update
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $user, $template, $request;
		global $phpbb_root_path, $phpEx, $phpbb_container;

		$user->add_lang('install');

		$this->tpl_name = 'acp_update';
		$this->page_title = 'ACP_VERSION_CHECK';

		/* @var $version_helper \phpbb\version_helper */
		$version_helper = $phpbb_container->get('version_helper');
		try
		{
			$recheck = $request->variable('versioncheck_force', false);
			$do_update = $request->variable('do_update', false);
			$token = $request->variable('form_token', false);

			$form_validator = $phpbb_container->get('form_helper');

			$updates_available = $version_helper->get_update_on_branch($recheck);
			$upgrades_available = $version_helper->get_suggested_updates();
			$branch = '';
			if (!empty($upgrades_available))
			{
				$branch = array_key_last($upgrades_available);
				$upgrades_available = array_pop($upgrades_available);
			}

			if ($do_update && $token && $form_validator->check_form_tokens('auto_updater') && !empty($updates_available))
			{
				$updater = $phpbb_container->get('updater.controller');
				$update_info = $phpbb_container->get('updater.url_provider');
				$current_version = $config['version'];
				$new_version = $upgrades_available['current'];
				$download_url = $update_info->get_download_url();
				$download_url .= $branch . '/' . $new_version . '/';
				$download_url .= 'phpBB-' . $current_version . '_to_' . $new_version . '.zip';
				$data = $updater->handle(
					$download_url
				);

				$response = new \phpbb\json_response();
				$response->send($data);
			}
		}
		catch (\RuntimeException $e)
		{
			$template->assign_var('S_VERSIONCHECK_FAIL', true);

			$updates_available = array();
		}

		if (!empty($updates_available))
		{
			$template->assign_block_vars('updates_available', $updates_available);
		}

		$update_link = $phpbb_root_path . 'install/app.' . $phpEx;

		$updater_token_details = $form_validator->get_form_tokens('auto_updater');
		$template_ary = [
			'S_UP_TO_DATE'				=> empty($updates_available),
			'U_ACTION'					=> $this->u_action,
			'U_VERSIONCHECK_FORCE'		=> append_sid($this->u_action . '&amp;versioncheck_force=1'),
			'S_SHOW_UPDATE_LINK'		=> $user->data['user_type'] == USER_FOUNDER,
			'U_UPDATE_BOARD'			=> $this->u_action . '&amp;do_update=1&amp;',
			'UPDATE_FORM_TOKEN'			=> $updater_token_details['form_token'],
			'UPDATE_FORM_TIME'			=> $updater_token_details['creation_time'],

			'CURRENT_VERSION'			=> $config['version'],

			'UPDATE_INSTRUCTIONS'		=> $user->lang('UPDATE_INSTRUCTIONS', $update_link),
			'S_VERSION_UPGRADEABLE'		=> !empty($upgrades_available),
			'UPGRADE_INSTRUCTIONS'		=> !empty($upgrades_available) ? $user->lang('UPGRADE_INSTRUCTIONS', $upgrades_available['current'], $upgrades_available['announcement']) : false,
		];

		$template->assign_vars($template_ary);

		// Incomplete update?
		if (phpbb_version_compare($config['version'], PHPBB_VERSION, '<'))
		{
			$database_update_link = $phpbb_root_path . 'install/app.php/update';

			$template->assign_vars(array(
				'S_UPDATE_INCOMPLETE'		=> true,
				'FILES_VERSION'				=> PHPBB_VERSION,
				'INCOMPLETE_INSTRUCTIONS'	=> $user->lang('UPDATE_INCOMPLETE_EXPLAIN', $database_update_link),
			));
		}
	}
}
