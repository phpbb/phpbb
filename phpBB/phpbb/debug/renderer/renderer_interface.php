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
 * Renderer interface for debug rendering, used for exceptions as of now
 */
interface renderer_interface
{
	/**
	 * Constructor for renderer
	 *
	 * @param bool $debug True if debug is enabled, otherwise false
	 * @param string $charset Charset to use
	 */
	public function __construct(bool $debug = true, string $charset = 'UTF-8');

	/**
	 * Set phpBB root path
	 *
	 * @param string $root_path
	 * @return renderer_interface
	 */
	public function set_root_path(string $root_path): renderer_interface;

	/**
	 * Set phpBB config
	 *
	 * @param config $config
	 * @return renderer_interface
	 */
	public function set_config(config $config): renderer_interface;

	/**
	 * Set phpBB language
	 *
	 * @param language $language
	 * @return renderer_interface
	 */
	public function set_language(language $language): renderer_interface;

	/**
	 * Decorate exception and output it
	 *
	 * @param FlattenException $exception
	 * @return void
	 */
	public function decorate(FlattenException $exception): void; // @todo: replace void with never if min PHP 8.1 is required
}
