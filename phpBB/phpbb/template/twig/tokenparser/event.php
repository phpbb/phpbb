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

class event extends \Twig\TokenParser\AbstractTokenParser
{
	/** @var \phpbb\template\twig\environment */
	protected $environment;

	/** @var array */
	protected $template_event_priority_array;

	/**
	* Constructor
	*
	* @param \phpbb\template\twig\environment $environment
	*/
	public function __construct(\phpbb\template\twig\environment $environment)
	{
		$this->environment = $environment;
		$phpbb_dispatcher = $this->environment->get_phpbb_dispatcher();

		$template_event_priority_array = [];
		/**
		 * Allows assigning priority to template event listeners
		 *
		 * @event core.twig_event_tokenparser_constructor
		 * @var	array	template_event_priority_array	Array with template event priority assignments per extension namespace
		 *
		 * @since 4.0.0-a1
		 */
		if ($phpbb_dispatcher)
		{
			$vars = ['template_event_priority_array'];
			extract($phpbb_dispatcher->trigger_event('core.twig_event_tokenparser_constructor', compact($vars)));
		}

		$this->template_event_priority_array = $template_event_priority_array;
		unset($template_event_priority_array);
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
		$expr = $this->parser->getExpressionParser()->parseExpression();

		$stream = $this->parser->getStream();
		$stream->expect(\Twig\Token::BLOCK_END_TYPE);

		return new \phpbb\template\twig\node\event($expr, $this->environment, $token->getLine(), $this->getTag(), $this->template_event_priority_array);
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
