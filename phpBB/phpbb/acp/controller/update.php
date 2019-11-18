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
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\version_helper */
	protected $version_helper;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config			$config				Config object
	 * @param \phpbb\acp\helper\controller	$helper				ACP Controller helper object
	 * @param \phpbb\language\language		$language			Language object
	 * @param \phpbb\request\request		$request			Request object
	 * @param \phpbb\template\template		$template			Template object
	 * @param \phpbb\version_helper			$version_helper		Version helper object
	 * @param string						$root_path			phpBB root path
	 * @param string						$php_ext			php File extension
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\version_helper $version_helper,
		$root_path,
		$php_ext
	)
	{
		$this->config			= $config;
		$this->helper			= $helper;
		$this->language			= $language;
		$this->request			= $request;
		$this->template			= $template;
		$this->version_helper	= $version_helper;

		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
	}

	public function main()
	{
		$this->language->add_lang('install');

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

		$this->template->assign_vars([
			'CURRENT_VERSION'			=> $this->config['version'],
			'UPDATE_INSTRUCTIONS'		=> $this->language->lang('UPDATE_INSTRUCTIONS', $this->root_path . 'install/app.' . $this->php_ext),
			'UPGRADE_INSTRUCTIONS'		=> !empty($upgrades_available) ? $this->language->lang('UPGRADE_INSTRUCTIONS', $upgrades_available['current'], $upgrades_available['announcement']) : false,

			'S_VERSION_UPGRADEABLE'		=> !empty($upgrades_available),
			'S_UP_TO_DATE'				=> empty($updates_available),

			'U_ACTION'					=> $this->helper->route('acp_update'),
			'U_VERSIONCHECK_FORCE'		=> $this->helper->route('acp_update', ['versioncheck_force' => true]),
		]);

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

		return $this->helper->render('acp_update.html', $this->language->lang('ACP_VERSION_CHECK'));
	}
}
