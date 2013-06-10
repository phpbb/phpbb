<?php

/*
 * This file is part of Twig.
 *
 * (c) 2009 Fabien Potencier
 * (c) 2009 Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Loops over each item of a sequence.
 *
 * <pre>
 * <ul>
 *  {% for user in users %}
 *    <li>{{ user.username|e }}</li>
 *  {% endfor %}
 * </ul>
 * </pre>
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
		if ($this->parser->getStream()->next()->getValue() == 'ELSE') {
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

    public function decideForEnd(Twig_Token $token)
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
