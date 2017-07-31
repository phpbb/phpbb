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

namespace phpbb\template\twig\extension;

// I will use this to generate forms in twig until there is something better
class form extends \Twig_Extension
{

	/**
	* Constructor
	*/
	public function __construct()
	{
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('input', '\\phpbb\\template\\twig\\extension\\form::generate_input'),
			new \Twig_SimpleFunction('adm_block', '\\phpbb\\template\\twig\\extension\\form::generate_block'),
		];
	}

	public static function generate_input($options)
	{
		$input = '<input ';

		switch ($options['type'])
		{
			case 'radio':
				break;

			default:
				foreach ($options as $key => $value)
				{
					if (in_array($key, ['lang']))
						continue;

					$input .= "$key=\"$value\" ";
				}
				break;
		}

		$input .= '>';

		return $input;
	}

	public static function generate_block($name, $description = '', $content)
	{
		return <<<EOF
			<dl>
				<dt><label for="">$name</label><br /><span>$description</span></dt>
				<dd>
					$content
				</dd>
			</dl>
EOF;
	}
}
