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

class update
{
	var $u_action;

	public function main($id, $mode)
	{
		$this->language->add_lang('install');

		$this->tpl_name = 'acp_update';
		$this->page_title = 'ACP_VERSION_CHECK';

		/* @var $version_helper \phpbb\version_helper */
		$version_helper = $phpbb_container->get('version_helper');
		try
		{
			$recheck = $this->request->variable('versioncheck_force', false);
			$updates_available = $this->version_helper->get_update_on_branch($recheck);
			$upgrades_available = $this->version_helper->get_suggested_updates();
			if (!empty($upgrades_available))
			{
				$upgrades_available = array_pop($upgrades_available);
			}
		}
		catch (\RuntimeException $e)
		{
			$this->template->assign_var('S_VERSIONCHECK_FAIL', true);

			$updates_available = [];
		}

		if (!empty($updates_available))
		{
			$this->template->assign_block_vars('updates_available', $updates_available);
		}

		$update_link = $this->root_path . 'install/app.' . $this->php_ext;

		$template_ary = [
			'S_UP_TO_DATE'				=> empty($updates_available),
			'U_ACTION'					=> $this->u_action,
			'U_VERSIONCHECK_FORCE'		=> append_sid($this->u_action . '&amp;versioncheck_force=1'),

			'CURRENT_VERSION'			=> $this->config['version'],

			'UPDATE_INSTRUCTIONS'		=> $this->language->lang('UPDATE_INSTRUCTIONS', $update_link),
			'S_VERSION_UPGRADEABLE'		=> !empty($upgrades_available),
			'UPGRADE_INSTRUCTIONS'		=> !empty($upgrades_available) ? $this->language->lang('UPGRADE_INSTRUCTIONS', $upgrades_available['current'], $upgrades_available['announcement']) : false,
		];

		$this->template->assign_vars($template_ary);

		// Incomplete update?
		if (phpbb_version_compare($this->config['version'], PHPBB_VERSION, '<'))
		{
			$database_update_link = $this->root_path . 'install/app.php/update';

			$this->template->assign_vars([
				'S_UPDATE_INCOMPLETE'		=> true,
				'FILES_VERSION'				=> PHPBB_VERSION,
				'INCOMPLETE_INSTRUCTIONS'	=> $this->language->lang('UPDATE_INCOMPLETE_EXPLAIN', $database_update_link),
			]);
		}
	}
}
