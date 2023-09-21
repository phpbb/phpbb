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

use phpbb\config\config;
use phpbb\language\language;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * Base class for rendering debug output
 */
abstract class renderer_base implements renderer_interface
{
	/** @var bool */
	protected $debug_enabled;

	/** @var string */
	protected $charset;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var config Config */
	protected $config;

	/** @var language Language */
	protected $language;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(bool $debug = true, string $charset = 'UTF-8')
	{
		$this->debug_enabled = $debug;
		$this->charset = $charset;
	}

	/**
	 * Set debug to enabled
	 *
	 * @return void
	 */
	public function set_debug_enabled(): void
	{
		$this->debug_enabled = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_root_path(string $root_path): renderer_interface
	{
		$this->root_path = $root_path;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_config(config $config): renderer_interface
	{
		$this->config = $config;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_language(language $language): renderer_interface
	{
		$this->language = $language;

		return $this;
	}

	/**
	 * Get page title for exception
	 *
	 * @param \Exception|FlattenException $exception
	 *
	 * @return string
	 */
	protected function get_title($exception): string
	{
		switch ($exception->getStatusCode())
		{
			case 404:
				$title = 'PAGE_NOT_FOUND';
				break;

			default:
				$title = 'EXCEPTION_TITLE';
		}

		return $this->language->lang($title);
	}
}
