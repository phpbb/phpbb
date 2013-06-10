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
			new phpbb_template_twig_tokenparser_include,
			new phpbb_template_twig_tokenparser_event,
			new phpbb_template_twig_tokenparser_begin,
			new phpbb_template_twig_tokenparser_define,
		);
	}

	public function getOperators()
	{
		return array(
			array(),
			array(
				'eq' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_Equal', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
				'!==' => array('precedence' => 20, 'class' => 'Twig_Node_Expression_Binary_NotEqual', 'associativity' => Twig_ExpressionParser::OPERATOR_LEFT),
			),
		);
	}
}
