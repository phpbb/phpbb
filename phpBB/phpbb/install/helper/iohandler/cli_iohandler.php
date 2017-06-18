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

use phpbb\install\exception\installer_exception;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * Input-Output handler for the CLI frontend
 */
class cli_iohandler extends iohandler_base
{
	/**
	 * @var OutputInterface
	 */
	protected $output;

	/**
	 * @var OutputStyle
	 */
	protected $io;

	/**
	 * @var array
	 */
	protected $input_values = array();

	/**
	 * @var \Symfony\Component\Console\Helper\ProgressBar
	 */
	protected $progress_bar;

	/**
	 * Set the style and output used to display feedback;
	 *
	 * @param OutputStyle 		$style
	 * @param OutputInterface	$output
	 */
	public function set_style(OutputStyle $style, OutputInterface $output)
	{
		$this->io = $style;
		$this->output = $output;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_input($name, $default, $multibyte = false)
	{
		$result = $default;

		if (isset($this->input_values[$name]))
		{
			$result = $this->input_values[$name];
		}

		if ($multibyte)
		{
			return utf8_normalize_nfc($result);
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_raw_input($name, $default)
	{
		return $this->get_input($name, $default, true);
	}

	/**
	 * Set input variable
	 *
	 * @param string $name Name of input variable
	 * @param mixed $value Value of input variable
	 */
	public function set_input($name, $value)
	{
		$this->input_values[$name] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_server_variable($name, $default = '')
	{
		return $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_header_variable($name, $default = '')
	{
		return $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_secure()
	{
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_user_form_group($title, $form)
	{
		throw new installer_exception('MISSING_DATA');
	}

	/**
	 * {@inheritdoc}
	 */
	public function send_response($no_more_output = false)
	{
	}

	/**
	 * {@inheritdoc
	 */
	public function add_error_message($error_title, $error_description = false)
	{
		$this->io->newLine();
		$message = $this->translate_message($error_title, $error_description);
		$message_string = $message['title'] . (!empty($message['description']) ? "\n" . $message['description'] : '');

		if (strpos($message_string, '<br />') !== false)
		{
			$message_string = strip_tags(str_replace('<br />', "\n", $message_string));
		}

		$this->io->error($message_string);

		if ($this->progress_bar !== null)
		{
			$this->io->newLine(2);
			$this->progress_bar->display();
		}
	}

	/**
	 * {@inheritdoc
	 */
	public function add_warning_message($warning_title, $warning_description = false)
	{
		$this->io->newLine();

		$message = $this->translate_message($warning_title, $warning_description);
		$message_string = $message['title'] . (!empty($message['description']) ? "\n" . $message['description'] : '');
		$this->io->warning($message_string);

		if ($this->progress_bar !== null)
		{
			$this->io->newLine(2);
			$this->progress_bar->display();
		}
	}

	/**
	 * {@inheritdoc
	 */
	public function add_log_message($log_title, $log_description = false)
	{
		if ($this->output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL)
		{
			$message = $this->translate_message($log_title, $log_description);
			$this->output->writeln(sprintf('[%3d/%-3d] ---- %s', $this->current_task_progress, $this->task_progress_count, $message['title']));
		}
	}

	/**
	 * {@inheritdoc
	 */
	public function add_success_message($error_title, $error_description = false)
	{
		$this->io->newLine();

		$message = $this->translate_message($error_title, $error_description);
		$message_string = $message['title'] . (!empty($message['description']) ? "\n" . $message['description'] : '');
		$this->io->success($message_string);

		if ($this->progress_bar !== null)
		{
			$this->io->newLine(2);
			$this->progress_bar->display();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_task_count($task_count, $restart = false)
	{
		parent::set_task_count($task_count, $restart);

		if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_NORMAL)
		{
			if ($this->progress_bar !== null)
			{
				// Symfony's ProgressBar is immutable regarding task_count, so delete the old and create a new one.
				$this->progress_bar->clear();
			}
			else
			{
				$this->io->newLine(2);
			}

			$this->progress_bar = $this->io->createProgressBar($task_count);
			$this->progress_bar->setFormat(
				"    %current:3s%/%max:-3s% %bar%  %percent:3s%%\n" .
				"             %message%\n");
			$this->progress_bar->setBarWidth(60);

			if (!defined('PHP_WINDOWS_VERSION_BUILD'))
			{
				$this->progress_bar->setEmptyBarCharacter('░'); // light shade character \u2591
				$this->progress_bar->setProgressCharacter('');
				$this->progress_bar->setBarCharacter('▓'); // dark shade character \u2593
			}

			$this->progress_bar->setMessage('');
			$this->progress_bar->start();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_progress($task_lang_key, $task_number)
	{
		parent::set_progress($task_lang_key, $task_number);

		if ($this->progress_bar !== null)
		{
			$this->progress_bar->setProgress($this->current_task_progress);
			$this->progress_bar->setMessage($this->current_task_name);
		}
		else
		{
			$this->output->writeln(sprintf('[%3d/%-3d] %s', $this->current_task_progress, $this->task_progress_count, $this->current_task_name));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function finish_progress($message_lang_key)
	{
		parent::finish_progress($message_lang_key);

		if ($this->progress_bar !== null)
		{
			$this->progress_bar->finish();
			$this->progress_bar = null;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function request_refresh()
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_active_stage_menu($menu_path)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_finished_stage_menu($menu_path)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_cookie($cookie_name, $cookie_value)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_download_link($route, $title, $msg = null)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function render_update_file_status($status_array)
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function redirect($url, $use_ajax = false)
	{
	}
}
