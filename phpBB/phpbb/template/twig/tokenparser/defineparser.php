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

class defineparser extends \Twig\TokenParser\AbstractTokenParser
{
	/**
	* Parses a token and returns a node.
	*
	* @param \Twig\Token $token A Twig\Token instance
	*
	* @return \Twig\Node\Node A Twig\Node instance
	* @throws \Twig\Error\SyntaxError
	* @throws \phpbb\template\twig\node\definenode
	*/
	public function parse(\Twig\Token $token)
	{
		$lineno = $token->getLine();
		$stream = $this->parser->getStream();
		$name = $this->parser->getExpressionParser()->parseExpression();

		$capture = false;
		if ($stream->test(\Twig\Token::OPERATOR_TYPE, '='))
		{
			$stream->next();
			$value = $this->parser->getExpressionParser()->parseExpression();

			if ($value instanceof \Twig\Node\Expression\NameExpression)
			{
				// This would happen if someone improperly formed their DEFINE syntax
				// e.g. <!-- DEFINE $VAR = foo -->
				throw new \Twig\Error\SyntaxError('Invalid DEFINE', $token->getLine(), $this->parser->getStream()->getSourceContext()->getPath());
			}

			$stream->expect(\Twig\Token::BLOCK_END_TYPE);
		}
		else
		{
			$capture = true;

			$stream->expect(\Twig\Token::BLOCK_END_TYPE);

			$value = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
			$stream->expect(\Twig\Token::BLOCK_END_TYPE);
		}

		return new \phpbb\template\twig\node\definenode($capture, $name, $value, $lineno, $this->getTag());
	}

	public function decideBlockEnd(\Twig\Token $token)
	{
		return $token->test('ENDDEFINE');
	}

	/**
	* Gets the tag name associated with this token parser.
	*
	* @return string The tag name
	*/
	public function getTag()
	{
		return 'DEFINE';
	}
}
