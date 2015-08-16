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

/**
 * BBCode help page
 */
abstract class controller
{
	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\help\manager */
	protected $manager;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper	$helper
	 * @param \phpbb\help\manager		$manager
	 * @param \phpbb\template\template	$template
	 * @param \phpbb\language\language	$language
	 * @param string					$root_path
	 * @param string					$php_ext
	 */
	public function __construct(\phpbb\controller\helper $helper, \phpbb\help\manager $manager, \phpbb\template\template $template, \phpbb\language\language $language, $root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->manager = $manager;
		$this->template = $template;
		$this->language = $language;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * @return string
	 */
	abstract protected function display();

	public function handle()
	{
		$title = $this->display();

		$this->template->assign_vars(array(
			'L_FAQ_TITLE'	=> $title,
			'S_IN_FAQ'		=> true,
		));

		make_jumpbox(append_sid("{$this->root_path}viewforum.{$this->php_ext}"));
		return $this->helper->render('faq_body.html', $title);
	}
}
