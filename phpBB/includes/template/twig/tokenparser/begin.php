<?php
/**
*
* @package phpBB3
* @copyright (c) 2013 phpBB Group, sections (c) 2009 Fabien Potencier
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_template_twig_tokenparser_begin extends Twig_TokenParser_For
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
		$beginName = $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();

		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
		$body = $this->parser->subparse(array($this, 'decideBeginFork'));
		if ($this->parser->getStream()->next()->getValue() == 'BEGINELSE') {
			$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
			$else = $this->parser->subparse(array($this, 'decideBeginEnd'), true);
		} else {
			$else = null;
		}
		$this->parser->getStream()->expect(Twig_Token::NAME_TYPE, $beginName);
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

		return new phpbb_template_twig_node_begin($beginName, $body, $else, $lineno, $this->getTag());
	}

    public function decideBeginFork(Twig_Token $token)
    {
        return $token->test(array('BEGINELSE', 'END'));
    }

    public function decideBeginEnd(Twig_Token $token)
    {
        return $token->test('END');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'BEGIN';
    }
}
