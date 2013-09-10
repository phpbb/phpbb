<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group, sections (c) 2009 Fabien Potencier
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\template\twig\tokenparser;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


class includeparser extends \Twig_TokenParser_Include
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
