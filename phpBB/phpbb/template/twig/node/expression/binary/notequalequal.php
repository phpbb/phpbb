<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\template\twig\node\expression\binary;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


class notequalequal extends \Twig_Node_Expression_Binary
{
	public function operator(\Twig_Compiler $compiler)
	{
		return $compiler->raw('!==');
	}
}
