<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @copyright Portions (c) 2009 Fabien Potencier, Armin Ronacher
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\template\twig\tokenparser;

class includeparser extends \Twig_TokenParser_Include
{
	/**
	* Parses a token and returns a node.
	*
	* @param \Twig_Token $token A Twig_Token instance
	*
	* @return \Twig_NodeInterface A Twig_NodeInterface instance
	*/
	public function parse(\Twig_Token $token)
	{
		$expr = $this->parser->getExpressionParser()->parseExpression();

		list($variables, $only, $ignoreMissing) = $this->parseArguments();

		return new \phpbb\template\twig\node\includenode($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
	}

	/**
	* Gets the tag name associated with this token parser.
	*
	* @return string The tag name
	*/
	public function getTag()
	{
		return 'INCLUDE';
	}
}
