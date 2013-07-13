<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group
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


class phpbb_template_twig_tokenparser_php extends Twig_TokenParser
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
		$stream = $this->parser->getStream();

		$stream->expect(Twig_Token::BLOCK_END_TYPE);

		$body = $this->parser->subparse(array($this, 'decideEnd'), true);

		$stream->expect(Twig_Token::BLOCK_END_TYPE);

		return new phpbb_template_twig_node_php($body, $this->parser->getEnvironment(), $token->getLine(), $this->getTag());
	}

	public function decideEnd(Twig_Token $token)
	{
		return $token->test('ENDPHP');
	}

	/**
	 * Gets the tag name associated with this token parser.
	 *
	 * @return string The tag name
	 */
	public function getTag()
	{
		return 'PHP';
    }
}
