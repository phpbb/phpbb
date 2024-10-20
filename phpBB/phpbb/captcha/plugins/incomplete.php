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

namespace phpbb\captcha\plugins;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;

class incomplete extends base
{
	/**
	 * Constructor for incomplete captcha
	 *
	 * @param config $config
	 * @param driver_interface $db
	 * @param language $language
	 * @param request_interface $request
	 * @param template $template
	 * @param user $user
	 * @param string $phpbb_root_path
	 * @param string $phpEx
	 */
	public function __construct(config $config, driver_interface $db, language $language, request_interface $request,
								protected template $template, user $user, protected string $phpbb_root_path, protected string $phpEx)
	{
		parent::__construct($config, $db, $language, $request, $user);
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_available(): bool
	{
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has_config(): bool
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_name(): string
	{
		return 'CAPTCHA_INCOMPLETE';
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_name(string $name): void
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function init(confirm_type $type): void
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_demo_template(): string
	{
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_template(): string
	{
		$contact_link = phpbb_get_board_contact_link($this->config, $this->phpbb_root_path, $this->phpEx);

		$this->template->assign_vars([
			'CONFIRM_LANG'	=> 'CONFIRM_INCOMPLETE',
			'CONTACT_LINK'	=> $contact_link,
		]);

		return 'captcha_incomplete.html';
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(): bool
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_error(): string
	{
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function is_solved(): bool
	{
		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_attempt_count(): int
	{
		return 0;
	}
}
