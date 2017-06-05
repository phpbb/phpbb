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

namespace phpbb\help\controller;

use phpbb\exception\http_exception;

class help
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\event\dispatcher_interface  */
	protected $dispatcher;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper	$helper
	 * @param \phpbb\event\dispatcher_interface	$dispatcher
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\user				$user
	 * @param string					$root_path
	 * @param string					$php_ext
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\event\dispatcher_interface $dispatcher, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->dispatcher = $dispatcher;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Controller for /help/{mode} routes
	 *
	 * @param string		$mode
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 * @throws http_exception when the $mode is not known by any extension
	 */
	public function handle($mode)
	{
		$template_file = 'faq_body.html';
		switch ($mode)
		{
			case 'faq':
			case 'bbcode':
				$page_title = ($mode === 'faq') ? $this->user->lang['FAQ_EXPLAIN'] : $this->user->lang['BBCODE_GUIDE'];
				$this->user->add_lang($mode, false, true);
			break;

			default:
				$page_title = $this->user->lang['FAQ_EXPLAIN'];
				$ext_name = $lang_file = '';

				/**
				 * You can use this event display a custom help page
				 *
				 * @event core.faq_mode_validation
				 * @var	string	page_title		Title of the page
				 * @var	string	mode			FAQ that is going to be displayed
				 * @var	string	lang_file		Language file containing the help data
				 * @var	string	ext_name		Vendor and extension name where the help
				 *								language file can be loaded from
				 * @var	string	template_file	Template file name
				 * @since 3.1.4-RC1
				 * @changed 3.1.11-RC1 Added template_file var
				 */
				$vars = array(
					'page_title',
					'mode',
					'lang_file',
					'ext_name',
					'template_file',
				);
				extract($this->dispatcher->trigger_event('core.faq_mode_validation', compact($vars)));

				if ($ext_name === '' || $lang_file === '')
				{
					throw new http_exception(404, 'Not Found');
				}

				$this->user->add_lang($lang_file, false, true, $ext_name);
			break;

		}

		$this->template->assign_vars(array(
			'L_FAQ_TITLE'				=> $page_title,
			'S_IN_FAQ'					=> true,
		));

		$this->assign_to_template($this->user->help);

		make_jumpbox(append_sid("{$this->root_path}viewforum.{$this->php_ext}"));
		return $this->helper->render($template_file, $page_title);
	}

	/**
	 * Assigns the help data to the template blocks
	 *
	 * @param array $help_data
	 * @return null
	 */
	protected function assign_to_template(array $help_data)
	{
		// Pull the array data from the lang pack
		$switch_column = $found_switch = false;
		foreach ($help_data as $help_ary)
		{
			if ($help_ary[0] == '--')
			{
				if ($help_ary[1] == '--')
				{
					$switch_column = true;
					$found_switch = true;
					continue;
				}

				$this->template->assign_block_vars('faq_block', array(
					'BLOCK_TITLE'		=> $help_ary[1],
					'SWITCH_COLUMN'		=> $switch_column,
				));

				if ($switch_column)
				{
					$switch_column = false;
				}
				continue;
			}

			$this->template->assign_block_vars('faq_block.faq_row', array(
				'FAQ_QUESTION'		=> $help_ary[0],
				'FAQ_ANSWER'		=> $help_ary[1],
			));
		}

		$this->template->assign_var('SWITCH_COLUMN_MANUALLY', !$found_switch);
	}
}
