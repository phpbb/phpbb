<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group, sections (c) 2009 Fabien Potencier, Armin Ronacher
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


class phpbb_template_twig_tokenparser_includephp extends Twig_TokenParser
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
		$expr = $this->parser->getExpressionParser()->parseExpression();

		$stream = $this->parser->getStream();

		$ignoreMissing = false;
		if ($stream->test(Twig_Token::NAME_TYPE, 'ignore')) {
			$stream->next();
			$stream->expect(Twig_Token::NAME_TYPE, 'missing');

			$ignoreMissing = true;
		}

		$stream->expect(Twig_Token::BLOCK_END_TYPE);

		return new phpbb_template_twig_node_includephp($expr, $this->parser->getEnvironment(), $ignoreMissing, $token->getLine(), $this->getTag());
	}

	/**
	 * Gets the tag name associated with this token parser.
	 *
	 * @return string The tag name
	 */
	public function getTag()
	{
		return 'INCLUDEPHP';
	}
}
