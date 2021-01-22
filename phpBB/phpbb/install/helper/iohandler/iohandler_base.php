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

namespace phpbb\install\helper\iohandler;

/**
 * Base class for installer input-output handlers
 */
abstract class iohandler_base implements iohandler_interface
{
	/**
	 * Array of errors
	 *
	 * Errors should be added, when the installation cannot continue without
	 * user interaction. If the aim is to notify the user about something, please
	 * use a warning instead.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Array of warnings
	 *
	 * @var array
	 */
	protected $warnings;

	/**
	 * Array of logs
	 *
	 * @var array
	 */
	protected $logs;

	/**
	 * Array of success messages
	 *
	 * @var array
	 */
	protected $success;

	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var int
	 */
	protected $task_progress_count;

	/**
	 * @var int
	 */
	protected $current_task_progress;

	/**
	 * @var string
	 */
	protected $current_task_name;

	/**
	 * @var bool
	 */
	protected $restart_progress_bar;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->errors	= array();
		$this->warnings	= array();
		$this->logs		= array();
		$this->success	= array();

		$this->restart_progress_bar		= false;
		$this->task_progress_count		= 0;
		$this->current_task_progress	= 0;
		$this->current_task_name		= '';
	}

	/**
	 * Set language service
	 *
	 * @param \phpbb\language\language $language
	 */
	public function set_language(\phpbb\language\language $language)
	{
		$this->language = $language;
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_error_message($error_title, $error_description = false)
	{
		if (!is_array($error_title) && strpos($error_title, '<br />') !== false)
		{
			$error_title = strip_tags(htmlspecialchars_decode($error_title, ENT_COMPAT));
		}
		$this->errors[] = $this->translate_message($error_title, $error_description);
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_warning_message($warning_title, $warning_description = false)
	{
		$this->warnings[] = $this->translate_message($warning_title, $warning_description);
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_log_message($log_title, $log_description = false)
	{
		$this->logs[] = $this->translate_message($log_title, $log_description);
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_success_message($success_title, $success_description = false)
	{
		$this->success[] = $this->translate_message($success_title, $success_description);
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_task_count($task_count, $restart = false)
	{
		$this->task_progress_count = $task_count;
		$this->restart_progress_bar = $restart;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_progress($task_lang_key, $task_number)
	{
		$this->current_task_name = '';

		if (!empty($task_lang_key))
		{
			$this->current_task_name = $this->language->lang($task_lang_key);
		}

		$this->current_task_progress = $task_number;
	}

	/**
	 * {@inheritdoc}
	 */
	public function finish_progress($message_lang_key)
	{
		if (!empty($message_lang_key))
		{
			$this->current_task_name = $this->language->lang($message_lang_key);
		}

		$this->current_task_progress = $this->task_progress_count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function generate_form_render_data($title, $form)
	{
		return '';
	}

	/**
	 * Localize message.
	 *
	 * Note: When an array is passed into the parameters below, it will be
	 * resolved as printf($param[0], $param[1], ...).
	 *
	 * @param array|string		$title			Title of the message
	 * @param array|string|bool	$description	Description of the message
	 *
	 * @return array	Localized message in an array
	 */
	protected function translate_message($title, $description)
	{
		$message_array = array();

		$message_array['title'] = call_user_func_array(array($this->language, 'lang'), (array) $title);

		if ($description !== false)
		{
			$message_array['description'] = call_user_func_array(array($this->language, 'lang'), (array) $description);
		}

		return $message_array;
	}
}
