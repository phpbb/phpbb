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

namespace phpbb\debug\renderer;

use Symfony\Component\Debug\Exception\FlattenException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;
use Twig\TwigTest;

/**
 * HTML renderer for debug output
 */
class html_renderer extends renderer_base
{
	/** @var Environment */
	protected $twig;

	/**
	 * Setup twig for rendering
	 *
	 * @return void
	 */
	protected function setup_twig(): void
	{
		$loader = new FilesystemLoader($this->root_path . '/styles/all');

		$path = $this->root_path . 'styles/all/';
		$template_path = $path . 'template/';

		try
		{
			$loader->addPath($template_path);
		}
		catch (LoaderError $error)
		{
			// ignore loader errors, nothing we can do
		}

		$this->twig = new Environment($loader);
		$this->twig->addFunction(new TwigFunction('lang', [$this, 'lang']));
		$this->twig->addTest(new TwigTest('numeric', function ($value)
			{
				return is_numeric($value);
			}
		));
	}

	/**
	 * Twig wrapper function for language->lang()
	 *
	 * @return string
	 */
	public function lang(): string
	{
		$args = func_get_args();
		return call_user_func_array(array($this->language, 'lang'), $args);
	}

	/**
	 * {@inheritDoc}
	 */
	public function decorate(FlattenException $exception): void
	{
		$this->setup_twig();

		$template_data = [
			'board_contact'		=> $this->config['board_contact'] ? $this->escape_html($this->config['board_contact']) : '',
			'page_title'		=> $this->get_title($exception),
			'return_index'		=> $this->root_path,
		];

		if ($this->debug_enabled)
		{
			$template_data += $this->get_stack_trace($exception);
		}

		if (!headers_sent())
		{
			header('Content-Type: text/html; charset=' . $this->charset);
		}

		echo $this->twig->render('exception.html', $template_data);

		garbage_collection();
		exit_handler();
	}

	/**
	 * Get stack trace for exception
	 *
	 * @param FlattenException $exception
	 * @return array|array[]
	 */
	protected function get_stack_trace(FlattenException $exception): array
	{
		$stack_trace = [];

		try
		{
			$count = count($exception->getAllPrevious());
			$total = $count + 1;
			foreach ($exception->toArray() as $position => $stack_item)
			{
				$index = $count - $position + 1;

				$stack_item_data = [
					'index'			=> $index,
					'total'			=> $total,
					'message'		=> $this->separate_paragraphs($stack_item['message']),
					'traces'		=> [],
				];

				$stack_item_data += $this->format_class($stack_item['class']);

				foreach ($stack_item['trace'] as $trace)
				{
					$trace_data = [];

					if ($trace['function'])
					{
						$trace_data += [
							'type'		=> $trace['type'],
							'method'	=> $trace['function'],
							'args'		=> $this->format_args($trace['args']),
						];

						$trace_data += $this->format_class($trace['class']);
					}
					if (isset($trace['file']) && isset($trace['line']))
					{
						$trace_data += $this->format_path($trace['file'], $trace['line']);
					}

					$stack_item_data['traces'][] = $trace_data;
				}

				$stack_trace['stack'][] = $stack_item_data;
			}
		}
		catch (\Exception $e)
		{
			// something nasty happened and we cannot throw an exception anymore
			if ($this->debug_enabled)
			{
				$stack_trace = [
					'stack_error'	=> [
						'class'		=> \get_class($e),
						'message'	=> $this->escape_html($e->getMessage()),
					]
				];
			}
		}

		return $stack_trace;
	}

	/**
	 * Format class for HTML
	 *
	 * @param string $class
	 * @return array
	 */
	private function format_class(string $class): array
	{
		$parts = explode('\\', $class);

		return [
			'full_class'		=> $class,
			'class'				=> array_pop($parts),
		];
	}

	/**
	 * Format path for output
	 *
	 * @param string $path
	 * @param int $line
	 * @return array
	 */
	private function format_path(string $path, int $line): array
	{
		$file = preg_match('#[^/\\\\]*+$#', $path, $file) ? $file[0] : $path;

		return [
			'path'		=> $this->escape_html($path),
			'file'		=> $this->escape_html($file),
			'line'		=> $line,
		];
	}

	/**
	 * Formats an array as a string.
	 *
	 * @param array $args The argument array
	 *
	 * @return array
	 */
	private function format_args(array $args): array
	{
		$args_info = [];

		foreach ($args as $key => $item)
		{
			$argument_type = '';
			$argument_value = '';

			if ('object' === $item[0])
			{
				$argument_type = 'object';
				$argument_value = $this->format_class($item[1]);
			}
			else if ('array' === $item[0])
			{
				$argument_type = 'array';
				$argument_value = \is_array($item[1]) ? $this->format_args($item[1]) : $item[1];
			}
			else if ('null' === $item[0])
			{
				$argument_type = 'null';
			}
			else if ('boolean' === $item[0])
			{
				$argument_type = strtolower(var_export($item[1], true));
			}
			else if ('resource' === $item[0])
			{
				$argument_type = 'resource';
			}
			else
			{
				$argument_value = str_replace("\n", '', $this->escape_html(var_export($item[1], true)));
			}

			$args_info[] = [
				'type'		=> $argument_type,
				'key'		=> is_int($key) ? $key : $this->escape_html($key),
				'value'		=> $argument_value,
			];
		}

		return $args_info;
	}

	/**
	 * HTML-encodes a string.
	 */
	private function escape_html(string $str): string
	{
		return htmlspecialchars($str, ENT_COMPAT, $this->charset);
	}

	/**
	 * Separate paragraphs of messages
	 *
	 * @param string $text Message to separate by paragraphs
	 *
	 * @return array|false|string[]
	 */
	private function separate_paragraphs(string $text)
	{
		return preg_split('/(<br>)+/', $text, -1, PREG_SPLIT_NO_EMPTY);
	}
}
