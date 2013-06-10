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
 * Includes a template.
 *
 * <pre>
 *   {% include 'header.html' %}
 *     Body
 *   {% include 'footer.html' %}
 * </pre>
 */
class phpbb_template_twig_tokenparser_event extends Twig_TokenParser_Include
{
    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $ignoreMissing = true;

        $variables = null;
        if ($stream->test(Twig_Token::NAME_TYPE, 'with')) {
            $stream->next();

            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $only = false;
        if ($stream->test(Twig_Token::NAME_TYPE, 'only')) {
            $stream->next();

            $only = true;
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return array($variables, $only, $ignoreMissing);
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'EVENT';
    }
}
