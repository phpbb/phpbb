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

namespace phpbb\install\module\obtain_data\task;

use phpbb\install\exception\user_interaction_required_exception;

/**
 * This class obtains default data from the user related to board (Board name, Board descritpion, etc...)
 */
class obtain_board_data extends \phpbb\install\task_base implements \phpbb\install\task_interface
{
	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $io_handler;

	/**
	 * @var \phpbb\language\language_file_helper
	 */
	protected $language_helper;

	/**
	 * Constructor
	 *
	 * @param \phpbb\install\helper\config							$config			Installer's config
	 * @param \phpbb\install\helper\iohandler\iohandler_interface	$iohandler		Installer's input-output handler
	 * @param \phpbb\language\language_file_helper					$lang_helper	Language file helper
	 */
	public function __construct(\phpbb\install\helper\config $config,
								\phpbb\install\helper\iohandler\iohandler_interface $iohandler,
								\phpbb\language\language_file_helper $lang_helper)
	{
		$this->install_config	= $config;
		$this->io_handler		= $iohandler;
		$this->language_helper	= $lang_helper;

		parent::__construct(true);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		// Check if data is sent
		if ($this->io_handler->get_input('submit_board', false))
		{
			$this->process_form();
		}
		else
		{
			$this->request_form_data();
		}
	}

	/**
	 * Process form data
	 */
	protected function process_form()
	{
		// Board data
		$default_lang	= $this->io_handler->get_input('default_lang', '');
		$board_name		= $this->io_handler->get_input('board_name', '', true);
		$board_desc		= $this->io_handler->get_input('board_description', '', true);

		// Check default lang
		$langs = $this->language_helper->get_available_languages();
		$lang_valid = false;

		foreach ($langs as $lang)
		{
			if ($lang['iso'] === $default_lang)
			{
				$lang_valid = true;
				break;
			}
		}

		$this->install_config->set('board_name', $board_name);
		$this->install_config->set('board_description', $board_desc);

		if ($lang_valid)
		{
			$this->install_config->set('default_lang', $default_lang);
		}
		else
		{
			$this->request_form_data(true);
		}
	}

	/**
	 * Request data from the user
	 *
	 * @param bool $use_request_data Whether to use submited data
	 *
	 * @throws user_interaction_required_exception When the user is required to provide data
	 */
	protected function request_form_data($use_request_data = false)
	{
		if ($use_request_data)
		{
			$board_name		= $this->io_handler->get_input('board_name', '', true);
			$board_desc		= $this->io_handler->get_input('board_description', '', true);
		}
		else
		{
			$board_name		= '{L_CONFIG_SITENAME}';
			$board_desc		= '{L_CONFIG_SITE_DESC}';
		}

		// Use language because we only check this to be valid
		$default_lang	= $this->install_config->get('user_language', 'en');

		$langs = $this->language_helper->get_available_languages();
		$lang_options = array();

		foreach ($langs as $lang)
		{
			$lang_options[] = array(
				'value'		=> $lang['iso'],
				'label'		=> $lang['local_name'],
				'selected'	=> ($default_lang === $lang['iso']),
			);
		}

		$board_form = array(
			'default_lang' => array(
				'label'		=> 'DEFAULT_LANGUAGE',
				'type'		=> 'select',
				'options'	=> $lang_options,
			),
			'board_name' => array(
				'label'		=> 'BOARD_NAME',
				'type'		=> 'text',
				'default'	=> $board_name,
			),
			'board_description' => array(
				'label'		=> 'BOARD_DESCRIPTION',
				'type'		=> 'text',
				'default'	=> $board_desc,
			),
			'submit_board'	=> array(
				'label'	=> 'SUBMIT',
				'type'	=> 'submit',
			),
		);

		$this->io_handler->add_user_form_group('BOARD_CONFIG', $board_form);

		throw new user_interaction_required_exception();
	}

	/**
	 * {@inheritdoc}
	 */
	static public function get_step_count()
	{
		return 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_task_lang_name()
	{
		return '';
	}
}
