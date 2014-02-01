<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group, sections (c) 2009 Fabien Potencier
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\template\twig\tokenparser;


class defineparser extends \Twig_TokenParser
{
	/**
	 * Parses a token and returns a node.
	 *
	 * @param Twig_Token $token A Twig_Token instance
	 *
	 * @return Twig_NodeInterface A Twig_NodeInterface instance
	 */
	public function parse(\Twig_Token $token)
	{
		$lineno = $token->getLine();
		$stream = $this->parser->getStream();
		$name = $this->parser->getExpressionParser()->parseExpression();

		$capture = false;
		if ($stream->test(\Twig_Token::OPERATOR_TYPE, '=')) {
			$stream->next();
			$value = $this->parser->getExpressionParser()->parseExpression();

			if ($value instanceof \Twig_Node_Expression_Name)
			{
				// This would happen if someone improperly formed their DEFINE syntax
				// e.g. <!-- DEFINE $VAR = foo -->
				throw new \Twig_Error_Syntax('Invalid DEFINE', $token->getLine(), $this->parser->getFilename());
			}

			$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		} else {
			$capture = true;

			$stream->expect(\Twig_Token::BLOCK_END_TYPE);

			$value = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
			$stream->expect(\Twig_Token::BLOCK_END_TYPE);
		}

		return new \phpbb\template\twig\node\definenode($capture, $name, $value, $lineno, $this->getTag());
	}

	public function decideBlockEnd(\Twig_Token $token)
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
