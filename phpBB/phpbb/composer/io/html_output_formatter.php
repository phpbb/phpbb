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

class html_output_formatter extends \Composer\Console\HtmlOutputFormatter
{
	/**
	 * {@inheritdoc}
	 */
	public function format($message)
	{
		$formatted = parent::format($message);

		return preg_replace_callback("{[\033\e]\[([0-9;]+)m(.*?)[\033\e]\[[0-9;]+m}s", array($this, 'formatHtml'), $formatted);
	}
}
