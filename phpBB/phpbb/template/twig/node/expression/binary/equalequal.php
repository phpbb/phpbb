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

namespace phpbb\template\twig\node\expression\binary;

class equalequal extends \Twig_Node_Expression_Binary
{
	public function operator(\Twig_Compiler $compiler)
	{
		return $compiler->raw('===');
	}
}
