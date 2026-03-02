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

class includeparser extends \Twig\TokenParser\AbstractTokenParser
{
	/**
	* Parses a token and returns a node.
	*
	* @param \Twig\Token $token A Twig\Token instance
	*
	* @return \Twig\Node\Node A Twig\Node instance
	*/
	public function parse(\Twig\Token $token) : \Twig\Node\Node
	{
		$expr = $this->parser->parseExpression();

		$variables = null;
		$only = false;
		$ignoreMissing = false;

		if ($this->parser->getStream()->nextIf(\Twig\Token::NAME_TYPE, 'with'))
		{
			$variables = $this->parser->parseExpression();
		}

		if ($this->parser->getStream()->nextIf(\Twig\Token::NAME_TYPE, 'only'))
		{
			$only = true;
		}

		if ($this->parser->getStream()->nextIf(\Twig\Token::NAME_TYPE, 'ignore'))
		{
			$this->parser->getStream()->expect(\Twig\Token::NAME_TYPE, 'missing');
			$ignoreMissing = true;
		}

		$this->parser->getStream()->expect(\Twig\Token::BLOCK_END_TYPE);

		return new \phpbb\template\twig\node\includenode($expr, $variables, $only, $ignoreMissing, $token->getLine(), $this->getTag());
	}

	/**
	* Gets the tag name associated with this token parser.
	*
	* @return string The tag name
	*/
	public function getTag() : string
	{
		return 'INCLUDE';
	}
}
