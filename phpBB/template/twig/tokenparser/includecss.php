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

class includecss extends \Twig_TokenParser
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

		$stream = $this->parser->getStream();
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);

		return new \phpbb\template\twig\node\includecss($expr, $this->parser->getEnvironment(), $token->getLine(), $this->getTag());
	}

	/**
	* Gets the tag name associated with this token parser.
	*
	* @return string The tag name
	*/
	public function getTag()
	{
		return 'INCLUDECSS';
	}
}
