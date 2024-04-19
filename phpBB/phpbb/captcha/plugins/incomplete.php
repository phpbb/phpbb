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
use phpbb\exception\runtime_exception;
use phpbb\template\template;

class incomplete extends captcha_abstract
{
	/**
	 * Constructor for incomplete captcha
	 *
	 * @param config $config
	 * @param template $template
	 * @param string $phpbb_root_path
	 * @param string $phpEx
	 */
	public function __construct(protected config $config, protected template $template,
								protected string $phpbb_root_path, protected string $phpEx)
	{}

	/**
	 * @return bool True if captcha is available, false if not
	 */
	public function is_available(): bool
	{
		return true;
	}

	/**
	 * Dummy implementation, not supported by this captcha
	 *
	 * @throws runtime_exception
	 * @return void
	 */
	public function get_generator_class(): void
	{
		throw new runtime_exception('NO_GENERATOR_CLASS');
	}

	/**
	 * Get CAPTCHA name language variable
	 *
	 * @return string Language variable
	 */
	public static function get_name(): string
	{
		return 'CAPTCHA_INCOMPLETE';
	}

	/**
	 * Init CAPTCHA
	 *
	 * @param int $type CAPTCHA type
	 * @return void
	 */
	public function init($type)
	{
		$this->type = (int) $type;
	}

	/**
	 * Execute demo
	 *
	 * @return void
	 */
	public function execute_demo()
	{
	}

	/**
	 * Execute CAPTCHA
	 *
	 * @return void
	 */
	public function execute()
	{
	}

	/**
	 * Get template data for demo
	 *
	 * @param int|string $id ACP module ID
	 *
	 * @return string Demo template file name
	 */
	public function get_demo_template($id): string
	{
		return '';
	}

	/**
	 * Get template data for CAPTCHA
	 *
	 * @return string CAPTCHA template file name
	 */
	public function get_template(): string
	{
		$contact_link = phpbb_get_board_contact_link($this->config, $this->phpbb_root_path, $this->phpEx);

		$this->template->assign_vars([
			'CONFIRM_LANG'	=> $this->type != CONFIRM_POST ? 'CONFIRM_INCOMPLETE' : 'POST_CONFIRM_INCOMPLETE',
			'CONTACT_LINK'	=> $contact_link,
		]);

		return 'captcha_incomplete.html';
	}

	/**
	 * Validate CAPTCHA
	 *
	 * @return false Incomplete CAPTCHA will never validate
	 */
	public function validate(): bool
	{
		return false;
	}

	/**
	 * Check whether CAPTCHA is solved
	 *
	 * @return false Incomplete CAPTCHA will never be solved
	 */
	public function is_solved(): bool
	{
		return false;
	}
}
