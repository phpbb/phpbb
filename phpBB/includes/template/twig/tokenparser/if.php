<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group, sections (c) 2009 Fabien Potencier
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


class phpbb_template_twig_tokenparser_if extends Twig_TokenParser_If
{
	/**
	 * Parses a token and returns a node.
	 *
	 * @param Twig_Token $token A Twig_Token instance
	 *
	 * @return Twig_NodeInterface A Twig_NodeInterface instance
	 */
	public function parse(Twig_Token $token)
	{
		$lineno = $token->getLine();
		$expr = $this->parser->getExpressionParser()->parseExpression();
		$stream = $this->parser->getStream();
		$stream->expect(Twig_Token::BLOCK_END_TYPE);
		$body = $this->parser->subparse(array($this, 'decideIfFork'));
		$tests = array($expr, $body);
		$else = null;

		$end = false;
		while (!$end) {
			switch ($stream->next()->getValue()) {
				case 'ELSE':
					$stream->expect(Twig_Token::BLOCK_END_TYPE);
					$else = $this->parser->subparse(array($this, 'decideIfEnd'));
					break;

				case 'ELSEIF':
					$expr = $this->parser->getExpressionParser()->parseExpression();
					$stream->expect(Twig_Token::BLOCK_END_TYPE);
					$body = $this->parser->subparse(array($this, 'decideIfFork'));
					$tests[] = $expr;
					$tests[] = $body;
					break;

				case 'ENDIF':
					$end = true;
					break;

				default:
					throw new Twig_Error_Syntax(sprintf('Unexpected end of template. Twig was looking for the following tags "ELSE", "ELSEIF", or "ENDIF" to close the "IF" block started at line %d)', $lineno), $stream->getCurrent()->getLine(), $stream->getFilename());
			}
		}

		$stream->expect(Twig_Token::BLOCK_END_TYPE);

		return new Twig_Node_If(new Twig_Node($tests), $else, $lineno, $this->getTag());
	}

	public function decideIfFork(Twig_Token $token)
	{
		return $token->test(array('ELSEIF', 'ELSE', 'ENDIF'));
	}

	public function decideIfEnd(Twig_Token $token)
	{
		return $token->test(array('ENDIF'));
	}

	/**
	 * Gets the tag name associated with this token parser.
	 *
	 * @return string The tag name
	 */
	public function getTag()
	{
		return 'IF';
	}
}
