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

class extension extends \Twig_Extension
{
	/** @var \phpbb\template\context */
	protected $context;

	/** @var \phpbb\language\language */
	protected $language;

	/**
	* Constructor
	*
	* @param \phpbb\template\context $context
	* @param \phpbb\language\language $language
	* @return \phpbb\template\twig\extension
	*/
	public function __construct(\phpbb\template\context $context, $language)
	{
		$this->context = $context;
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
	* @return array An array of Twig_TokenParser instances
	*/
	public function getTokenParsers()
	{
		return array(
			new \phpbb\template\twig\tokenparser\defineparser,
			new \phpbb\template\twig\tokenparser\includeparser,
			new \phpbb\template\twig\tokenparser\includejs,
			new \phpbb\template\twig\tokenparser\includecss,
			new \phpbb\template\twig\tokenparser\event,
			new \phpbb\template\twig\tokenparser\includephp,
			new \phpbb\template\twig\tokenparser\php,
		);
	}

	/**
	* Returns a list of filters to add to the existing list.
	*
	* @return array An array of filters
	*/
	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('subset', array($this, 'loop_subset'), array('needs_environment' => true)),
			// @deprecated 3.2.0 Uses twig's JS escape method instead of addslashes
			new \Twig_SimpleFilter('addslashes', 'addslashes'),
		);
	}

	/**
	* Returns a list of global functions to add to the existing list.
	*
	* @return array An array of global functions
	*/
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('lang', array($this, 'lang')),
		);
	}

	/**
	* Returns a list of operators to add to the existing list.
	*
	* @return array An array of operators
	*/
	public function getOperators()
	{
		return array(
			array(
				'!' => array('precedence' => 50, 'class' => 'Twig_Node_Expression_Unary_Not'),
			),
			array(
				// precedence settings are copied from similar operators in Twig core extension
				'||' => array('precedence' => 10, 'class' => 'Twig_Node_Expression_Binary_Or', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'&&' => array('precedence' => 15, 'class' => 'Twig_Node_Expression_Binary_And', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),

				'eq' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Equal', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),

				'ne' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'neq' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'<>' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),

				'===' => array('precedence' => 20, 'class' => '\phpbb\template\twig\node\expression\binary\equalequal', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'!==' => array('precedence' => 20, 'class' => '\phpbb\template\twig\node\expression\binary\notequalequal', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),

				'gt' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Greater', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'gte' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_GreaterEqual', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'ge' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_GreaterEqual', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'lt' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Less', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'lte' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_LessEqual', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
				'le' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_LessEqual', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),

				'mod' => array('precedence' => 60, 'class' => 'Twig_Node_Expression_Binary_Mod', 'associativity' => \Twig_ExpressionParser::OPERATOR_LEFT),
			),
		);
	}

	/**
	* Grabs a subset of a loop
	*
	* @param \Twig_Environment $env          A Twig_Environment instance
	* @param mixed            $item         A variable
	* @param integer          $start        Start of the subset
	* @param integer          $end   	     End of the subset
	* @param Boolean          $preserveKeys Whether to preserve key or not (when the input is an array)
	*
	* @return mixed The sliced variable
	*/
	function loop_subset(\Twig_Environment $env, $item, $start, $end = null, $preserveKeys = false)
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

		return twig_slice($env, $item, $start, $end, $preserveKeys);
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
	function lang()
	{
		$args = func_get_args();
		$key = $args[0];

		$context_vars = $this->context->get_root_ref();

		if (isset($context_vars['L_' . $key]))
		{
			return $context_vars['L_' . $key];
		}

		// LA_ is transformed into lang(\'$1\')|escape('js'), so we should not
		// need to check for it

		return call_user_func_array(array($this->language, 'lang'), $args);
	}
}
