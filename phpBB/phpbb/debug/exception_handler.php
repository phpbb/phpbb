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

namespace phpbb\debug;

use phpbb\config\config;
use phpbb\language\language;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;

/**
 * Exception handler based on Symfony ExceptionHandler code.
 * Symfony code is released under the MIT license:
 *
 * Copyright (c) 2004-2021 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Nicolas Grekas <p@tchwork.com>
 */
class exception_handler extends ExceptionHandler
{
	/** renderer\renderer_base */
	private $renderer;

	/** @var string */
	private $root_path;

	/** @var language */
	private $language;

	/** @var config */
	private $config;

	/**
	 * @param bool $debug
	 * @param $charset
	 * @param $fileLinkFormat
	 */
	public function __construct(bool $debug = true, $charset = null, $fileLinkFormat = null)
	{
		$charset = $charset ?: ini_get('default_charset') ?: 'UTF-8';

		$symfony_request = Request::createFromGlobals();
		$symfony_request->getRequestFormat();

		if ($symfony_request->getRequestFormat() == 'json' || $symfony_request->headers->get('X-Requested-With') == 'XMLHttpRequest')
		{
			$this->renderer = new renderer\json_renderer($debug, $charset);
		}
		else
		{
			$this->renderer = new renderer\html_renderer($debug, $charset);
		}

		parent::__construct($debug, $charset, $fileLinkFormat);
	}

	/**
	 * Set debug to enabled
	 *
	 * @return void
	 */
	public function set_debug_enabled(): void
	{
		$this->renderer->set_debug_enabled();
	}

	/**
	 * Set config instance
	 *
	 * @param config $config
	 * @return $this
	 */
	public function set_config(config $config): exception_handler
	{
		$this->config = $config;

		return $this;
	}

	/**
	 * Set language instance
	 *
	 * @param language $language
	 * @return $this
	 */
	public function set_language(language $language): exception_handler
	{
		$this->language = $language;

		return $this;
	}

	/**
	 * Set phpBB root path
	 *
	 * @param string $root_path
	 * @return $this
	 */
	public function set_root_path(string $root_path): exception_handler
	{
		$this->root_path = $root_path;

		return $this;
	}

	/**
	 * Init renderer
	 *
	 * @return void
	 */
	protected function init_renderer(): void
	{
		$this->renderer->set_root_path($this->root_path)
			->set_config($this->config)
			->set_language($this->language);
	}

	/**
	 * Sends the error associated with the given Exception as a plain PHP response.
	 *
	 * This method uses plain PHP functions like header() and echo to output
	 * the response.
	 *
	 * @param \Exception|FlattenException $exception An \Exception or FlattenException instance
	 */
	public function sendPhpResponse($exception)
	{
		$this->init_renderer();

		if (!$exception instanceof FlattenException)
		{
			$exception = FlattenException::create($exception);
		}

		if (!headers_sent())
		{
			header(sprintf('HTTP/1.0 %s', $exception->getStatusCode()));
			foreach ($exception->getHeaders() as $name => $value)
			{
				header($name.': '.$value, false);
			}
		}

		$this->renderer->decorate($exception);
	}
}
