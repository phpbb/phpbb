<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\composer\io;

class html_output_formatter extends \Composer\Console\HtmlOutputFormatter
{
	protected static $availableForegroundColors = [
			30 => 'black',
			31 => 'red',
			32 => 'green',
			33 => 'orange',
			34 => 'blue',
			35 => 'magenta',
			36 => 'cyan',
			37 => 'white',
		];

	protected static $availableBackgroundColors = [
			40 => 'black',
			41 => 'red',
			42 => 'green',
			43 => 'yellow',
			44 => 'blue',
			45 => 'magenta',
			46 => 'cyan',
			47 => 'white',
		];

	protected static $availableOptions
		= [
			1 => 'bold',
			4 => 'underscore',
		];

	/**
	 * {@inheritdoc}
	 */
	public function format(?string $message): ?string
	{
		$formatted = parent::format($message);

		return preg_replace_callback("{[\033\e]\[([0-9;]+)m(.*?)[\033\e]\[[0-9;]+m}s", [$this, 'formatHtml'], $formatted);
	}

	protected function formatHtml($matches)
	{
		$out = '<span style="';
		foreach (explode(';', $matches[1]) as $code)
		{
			if (isset(self::$availableForegroundColors[$code]))
			{
				$out .= 'color:' . self::$availableForegroundColors[$code] . ';';
			}
			else if (isset(self::$availableBackgroundColors[$code]))
			{
				$out .= 'background-color:' . self::$availableBackgroundColors[$code] . ';';
			}
			else if (isset(self::$availableOptions[$code]))
			{
				switch (self::$availableOptions[$code])
				{
					case 'bold':
						$out .= 'font-weight:bold;';
						break;

					case 'underscore':
						$out .= 'text-decoration:underline;';
						break;
				}
			}
		}

		return $out . '">' . $matches[2] . '</span>';
	}
}
