<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Twig\TokenParser;

use Symfony\Bridge\Twig\Node\StopwatchNode;
use Twig\Node\Expression\AssignNameExpression;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Token Parser for the stopwatch tag.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class StopwatchTokenParser extends AbstractTokenParser
{
    protected $stopwatchIsAvailable;

    public function __construct($stopwatchIsAvailable)
    {
        $this->stopwatchIsAvailable = $stopwatchIsAvailable;
    }

    public function parse(Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        // {% stopwatch 'bar' %}
        $name = $this->parser->getExpressionParser()->parseExpression();

        $stream->expect(Token::BLOCK_END_TYPE);

        // {% endstopwatch %}
        $body = $this->parser->subparse(array($this, 'decideStopwatchEnd'), true);
        $stream->expect(Token::BLOCK_END_TYPE);

        if ($this->stopwatchIsAvailable) {
            return new StopwatchNode($name, $body, new AssignNameExpression($this->parser->getVarName(), $token->getLine()), $lineno, $this->getTag());
        }

        return $body;
    }

    public function decideStopwatchEnd(Token $token)
    {
        return $token->test('endstopwatch');
    }

    public function getTag()
    {
        return 'stopwatch';
    }
}
