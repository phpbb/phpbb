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

namespace phpbb\composer\io;

use Composer\IO\BufferIO;
use phpbb\language\language;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\StreamOutput;

class web_io extends BufferIO implements io_interface
{
	use translate_composer_trait;

	/**
	 * @param language					$language	Language object
	 * @param string					$input		Input string
	 * @param int						$verbosity	Verbosity level
	 * @param OutputFormatterInterface	$formatter	Output formatter
	 */
	public function __construct(language $language, $input = '', $verbosity = StreamOutput::VERBOSITY_NORMAL, OutputFormatterInterface $formatter = null)
	{
		$this->language = $language;

		parent::__construct($input, $verbosity, $formatter);
	}
}
