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

namespace phpbb\template\twig\tokenparser;

use Twig\Error\SyntaxError;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class include_js extends includejs
{
	/**
	* Gets the tag name associated with this token parser.
	*
	* @return string The tag name
	*/
	public function getTag(): string
	{
		return 'include_js';
	}
}
