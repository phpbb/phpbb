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

class phpbb_template_twig_extension extends Twig_Extension
{
	public function getName()
	{
		return 'phpbb';
	}

	public function getTokenParsers()
	{
		return array(
			new phpbb_template_twig_tokenparser_if,
			new phpbb_template_twig_tokenparser_define,
			new phpbb_template_twig_tokenparser_include,
			new phpbb_template_twig_tokenparser_includejs,
			new phpbb_template_twig_tokenparser_event,
			new phpbb_template_twig_tokenparser_includephp,
			new phpbb_template_twig_tokenparser_php,
		);
	}

    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('subset', array($this, 'loop_subset'), array('needs_environment' => true)),
		);
    }

	public function getOperators()
	{
		return array(
			array(),
			array(
				// @todo check if all these are needed (or others) and set precedence correctly
				'eq' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Equal', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

				'ne' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'neq' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'<>' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

				'===' => array('precedence' => 20, 'class' => 'phpbb_template_twig_node_expression_binary_equalequal', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'!==' => array('precedence' => 20, 'class' => 'phpbb_template_twig_node_expression_binary_notequalequal', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

				'gt' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Greater', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'gte' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_GreaterEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'ge' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_GreaterEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'lt' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Less', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'lte' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_LessEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'le' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_LessEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

				'||' => array('precedence' => 10, 'class' => 'Twig_Node_Expression_Binary_Or', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'&&' => array('precedence' => 15, 'class' => 'Twig_Node_Expression_Binary_And', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

                'mod' => array('precedence' => 60, 'class' => 'Twig_Node_Expression_Binary_Mod', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
            ),
        );
    }

	/**
	 * Grabs a subset of a loop
	 *
	 * @param Twig_Environment $env          A Twig_Environment instance
	 * @param mixed            $item         A variable
	 * @param integer          $start        Start of the subset
	 * @param integer          $end   	     End of the subset
	 * @param Boolean          $preserveKeys Whether to preserve key or not (when the input is an array)
	 *
	 * @return mixed The sliced variable
	 */
	function loop_subset(Twig_Environment $env, $item, $start, $end = null, $preserveKeys = false)
	{
		// We do almost the same thing as array_slice, except when $end is positive
		if ($end >= 1)
		{
			// When end is > 1, subset will end on the last item in an array with the specified $end
			// This is different from slice in that it is the number we end on rather than the number
			//	of items to grab (length)

			// Start must always be the actual starting number for this calculation (not negative)
			$start = ($start < 0) ? sizeof($item) + $start : $start;
			$end = $end - $start;
		}

		// We always include the last element (this was the past design)
		$end = ($end == -1 || $end === null) ? null : $end + 1;

		return twig_slice($env, $item, $start, $end, $preserveKeys);
	}
}
