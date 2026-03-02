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

namespace phpbb\template\twig;

use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Runtime\EscaperRuntime;

class extension extends \Twig\Extension\AbstractExtension
{
	/** @var \phpbb\template\context */
	protected $context;

	/** @var \phpbb\template\twig\environment */
	protected $environment;

	/** @var \phpbb\language\language */
	protected $language;

	/**
	* Constructor
	*
	* @param \phpbb\template\context $context
	* @param \phpbb\template\twig\environment $environment
	* @param \phpbb\language\language $language
	*/
	public function __construct(\phpbb\template\context $context, \phpbb\template\twig\environment $environment, $language)
	{
		$this->context = $context;
		$this->environment = $environment;
		$this->language = $language;
	}

	/**
	* Get the name of this extension
	*
	* @return string
	*/
	public function getName()
	{
		return 'phpbb';
	}

	/**
	* Returns the token parser instance to add to the existing list.
	*
	* @return \Twig\TokenParser\TokenParserInterface[] An array of \Twig\TokenParser\AbstractTokenParser instances
	*/
	public function getTokenParsers()
	{
		return array(
			new \phpbb\template\twig\tokenparser\defineparser,
			new \phpbb\template\twig\tokenparser\includeparser,
			new \phpbb\template\twig\tokenparser\includejs,
			new \phpbb\template\twig\tokenparser\includecss,
			new \phpbb\template\twig\tokenparser\event($this->environment),
		);
	}

	/**
	* Returns a list of filters to add to the existing list.
	*
	* @return \Twig\TwigFilter[] An array of filters
	*/
	public function getFilters()
	{
		return array(
			new \Twig\TwigFilter('subset', array($this, 'loop_subset'), array('needs_environment' => true)),
			new \Twig\TwigFilter('spaceless', array($this, 'spaceless_filter'), array('is_safe' => array('html'))),
			// @deprecated 3.2.0 Uses twig's JS escape method instead of addslashes
			new \Twig\TwigFilter('addslashes', 'addslashes'),
			new \Twig\TwigFilter('int', 'intval'),
			new \Twig\TwigFilter('float', 'floatval'),
		);
	}

	/**
	* Returns a list of global functions to add to the existing list.
	*
	* @return \Twig\TwigFunction[] An array of global functions
	*/
	public function getFunctions(): array
	{
		return array(
			new \Twig\TwigFunction('lang', array($this, 'lang')),
			new \Twig\TwigFunction('lang_defined', array($this, 'lang_defined')),
			new \Twig\TwigFunction('lang_js', [$this, 'lang_js']),
			new \Twig\TwigFunction('lang_raw', [$this, 'lang_raw']),
			new \Twig\TwigFunction('get_class', 'get_class'),
		);
	}

	/**
	* Returns a list of expression parsers to add to the existing list.
	*
	* @return array An array of expression parsers
	*/
	public function getExpressionParsers(): array
	{
		return [
			new \Twig\ExpressionParser\Prefix\UnaryOperatorExpressionParser('\Twig\Node\Expression\Unary\NotUnary', '!', 50),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\OrBinary', '||', 10),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\AndBinary', '&&', 15),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\EqualBinary', 'eq', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\NotEqualBinary', 'ne', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\NotEqualBinary', 'neq', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\NotEqualBinary', '<>', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\phpbb\template\twig\node\expression\binary\equalequal', '===', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\phpbb\template\twig\node\expression\binary\notequalequal', '!==', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\GreaterBinary', 'gt', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\GreaterEqualBinary', 'gte', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\GreaterEqualBinary', 'ge', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\LessBinary', 'lt', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\LessEqualBinary', 'lte', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\LessEqualBinary', 'le', 20),
			new \Twig\ExpressionParser\Infix\BinaryOperatorExpressionParser('\Twig\Node\Expression\Binary\ModBinary', 'mod', 60),
		];
	}

	/**
	* Grabs a subset of a loop
	*
	* @param \Twig\Environment $env          A Twig\Environment instance
	* @param mixed            $item         A variable
	* @param integer          $start        Start of the subset
	* @param integer          $end   	     End of the subset
	* @param boolean          $preserveKeys Whether to preserve key or not (when the input is an array)
	*
	* @return mixed The sliced variable
	*/
	public function loop_subset(\Twig\Environment $env, $item, $start, $end = null, $preserveKeys = false)
	{
		// We do almost the same thing as Twig's slice (array_slice), except when $end is positive
		if ($end >= 1)
		{
			// When end is > 1, subset will end on the last item in an array with the specified $end
			// This is different from slice in that it is the number we end on rather than the number
			//  of items to grab (length)

			// Start must always be the actual starting number for this calculation (not negative)
			$start = ($start < 0) ? count($item) + $start : $start;
			$end = $end - $start;
		}

		// We always include the last element (this was the past design)
		$end = ($end == -1 || $end === null) ? null : $end + 1;

		return CoreExtension::slice($env->getCharset(), $item, $start, $end, $preserveKeys);
	}

	/**
	* Remove whitespace between HTML tags
	*
	* @param string $content HTML content
	* @return string Content with whitespace removed
	*/
	public function spaceless_filter($content)
	{
		return trim(preg_replace('/>\s+</', '><', $content));
	}

	/**
	* Get output for a language variable (L_FOO, LA_FOO)
	*
	* This function checks to see if the language var was outputted to $context
	* (e.g. in the ACP, L_TITLE)
	* If not, we return the result of $user->lang()
	*
	* @return string
	*/
	public function lang()
	{
		$args = func_get_args();
		$key = $args[0];

		$context_vars = $this->context->get_root_ref();

		if (is_string($key) && isset($context_vars['L_' . $key]))
		{
			return $context_vars['L_' . $key];
		}

		// LA_ is transformed into lang(\'$1\')|escape('js'), so we should not
		// need to check for it

		return call_user_func_array(array($this->language, 'lang'), $args);
	}

	/**
	 * Check if a language variable exists
	 *
	 * @return bool
	 */
	public function lang_defined($key)
	{
		return call_user_func_array([$this->language, 'is_set'], [$key]);
	}

	/**
	 * Get output for language variable in JS code
	 *
	 * @throws RuntimeError When data passed to twig_escape_filter is not a UTF8 string
	 */
	public function lang_js(): string
	{
		$args = func_get_args();

		return $this->environment->getRuntime(EscaperRuntime::class)->escape(call_user_func_array([$this, 'lang'], $args), 'js');
	}

	/**
	 * Get raw value associated with lang key
	 *
	 * @param string $key
	 *
	 * @return array|string Raw value associated with lang key
	 */
	public function lang_raw(string $key): array|string
	{
		return call_user_func_array(array($this->language, 'lang_raw'), [$key]);
	}
}
