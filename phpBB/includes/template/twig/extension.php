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
			new phpbb_template_twig_tokenparser_begin,
			new phpbb_template_twig_tokenparser_define,
			new phpbb_template_twig_tokenparser_include,
			new phpbb_template_twig_tokenparser_includejs,
			new phpbb_template_twig_tokenparser_event,
			new phpbb_template_twig_tokenparser_php,
		);
	}

	public function getOperators()
	{
		return array(
			array(),
			array(
				// @todo check if all these are needed (or others)
				'eq' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Equal', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

				'ne' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'neq' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'<>' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

				'===' => array('precedence' => 20, 'class' => 'phpbb_template_twig_node_expression_binary_equalequal', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'!==' => array('precedence' => 20, 'class' => 'phpbb_template_twig_node_expression_binary_notequalequal', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

				'gt' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Greater', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'gte' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_GreaterEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'lt' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Less', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'lte' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_LessEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),

				'||' => array('precedence' => 10, 'class' => 'Twig_Node_Expression_Binary_Or', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'&&' => array('precedence' => 15, 'class' => 'Twig_Node_Expression_Binary_And', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
			),
		);
	}
}
