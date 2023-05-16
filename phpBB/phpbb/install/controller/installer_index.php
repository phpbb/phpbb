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

namespace phpbb\install\controller;

use phpbb\path_helper;

class installer_index
{
	/**
	 * @var helper
	 */
	protected $helper;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/** @var path_helper */
	protected $path_helper;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param helper 					$helper
	 * @param \phpbb\language\language	$language
	 * @param path_helper				$path_helper
	 * @param \phpbb\template\template	$template
	 * @param string					$phpbb_root_path
	 */
	public function __construct(helper $helper, \phpbb\language\language $language, path_helper $path_helper, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->helper = $helper;
		$this->language = $language;
		$this->path_helper = $path_helper;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function handle($mode)
	{
		$this->helper->handle_language_select();

		switch ($mode)
		{
			case "intro":
				$title = $this->language->lang('INTRODUCTION_TITLE');
				$install_docs_path = $this->path_helper->update_web_root_path($this->phpbb_root_path . 'docs/INSTALL.html');
				$body = $this->language->lang('INTRODUCTION_BODY', $install_docs_path);
			break;
			case "support":
				$title = $this->language->lang('SUPPORT_TITLE');
				$body = $this->language->lang('SUPPORT_BODY');
			break;
			case "license":
				$title = $this->language->lang('LICENSE_TITLE');
				$body = implode("<br/>\n", file($this->phpbb_root_path . 'docs/LICENSE.txt'));
			break;
		}

		$this->template->assign_vars(array(
			'TITLE'	=> $title,
			'BODY'	=> $body,
		));

		return $this->helper->render('installer_main.html', $title, true);
	}
}
