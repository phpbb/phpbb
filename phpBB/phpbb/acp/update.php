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

namespace phpbb\acp;

class update
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $lang;

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

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config		$config				Config object
	 * @param \phpbb\language\language	$lang				Language object
	 * @param \phpbb\request\request	$request			Request object
	 * @param \phpbb\template\template	$template			Template object
	 * @param \phpbb\version_helper		$version_helper		Version helper object
	 * @param string					$root_path			phpBB root path
	 * @param string					$php_ext			php File extension
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\language\language $lang,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\version_helper $version_helper,
		$root_path,
		$php_ext
	)
	{
		$this->config			= $config;
		$this->lang				= $lang;
		$this->request			= $request;
		$this->template			= $template;
		$this->version_helper	= $version_helper;

		$this->root_path		= $root_path;
		$this->php_ext			= $php_ext;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang('install');

		$this->tpl_name = 'acp_update';
		$this->page_title = 'ACP_VERSION_CHECK';

		try
		{
			$recheck = $this->request->variable('versioncheck_force', false);

			$updates_available	= $this->version_helper->get_update_on_branch($recheck);
			$upgrades_available	= $this->version_helper->get_suggested_updates();

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
			'CURRENT_VERSION'		=> $this->config['version'],

			'UPDATE_INSTRUCTIONS'	=> $this->lang->lang('UPDATE_INSTRUCTIONS', $this->root_path . 'install/app.' . $this->php_ext),
			'UPGRADE_INSTRUCTIONS'	=> !empty($upgrades_available) ? $this->lang->lang('UPGRADE_INSTRUCTIONS', $upgrades_available['current'], $upgrades_available['announcement']) : false,

			'S_VERSION_UPGRADEABLE'	=> !empty($upgrades_available),
			'S_UP_TO_DATE'			=> empty($updates_available),

			'U_ACTION'				=> $this->u_action,
			'U_VERSIONCHECK_FORCE'	=> append_sid($this->u_action . '&amp;versioncheck_force=1'),
		]);

		// Incomplete update?
		if (phpbb_version_compare($this->config['version'], PHPBB_VERSION, '<'))
		{
			$this->template->assign_vars([
				'S_UPDATE_INCOMPLETE'		=> true,
				'FILES_VERSION'				=> PHPBB_VERSION,
				'INCOMPLETE_INSTRUCTIONS'	=> $this->lang->lang('UPDATE_INCOMPLETE_EXPLAIN', $this->root_path . 'install/app.php/update'),
			]);
		}
	}
}
