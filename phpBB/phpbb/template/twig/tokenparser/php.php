<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\template\twig\tokenparser;

class php extends \Twig\TokenParser\AbstractTokenParser
{
	/** @var \phpbb\template\twig\environment */
	protected $environment;

	/**
	* Constructor
	*
	* @param \phpbb\template\twig\environment $environment
	*/
	public function __construct(\phpbb\template\twig\environment $environment)
	{
		$this->environment = $environment;
	}

	/**
	* Parses a token and returns a node.
	*
	* @param \Twig\Token $token A Twig\Token instance
	*
	* @return \Twig\Node\Node A Twig\Node instance
	*/
	public function parse(\Twig\Token $token)
	{
		$stream = $this->parser->getStream();

		$stream->expect(\Twig\Token::BLOCK_END_TYPE);

		$body = $this->parser->subparse(array($this, 'decideEnd'), true);

		$stream->expect(\Twig\Token::BLOCK_END_TYPE);

		return new \phpbb\template\twig\node\php($body, $this->environment, $token->getLine(), $this->getTag());
	}

	public function decideEnd(\Twig\Token $token)
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
