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

class phpbb_template_twig_lexer extends Twig_Lexer
{
	protected function lexExpression()
	{
		parent::lexExpression();

		// Last element parsed
		$last_element = end($this->tokens);

		/**
		* Check for old fashioned INCLUDE statements without enclosed quotes
		*/
		if ($last_element->getValue() === 'INCLUDE')
		{
	        if (preg_match('#^\s*([a-zA-Z0-9_]+\.[a-zA-Z0-9]+)#', substr($this->code, $this->cursor), $match))
	        {
	            $this->pushToken(Twig_Token::STRING_TYPE, stripcslashes($match[1]));
	            $this->moveCursor($match[0]);
	        }
		}

		/**
		* This is some compatibility code to continue supporting expressions such as:
		* <!-- IF .blah -->
		*/
		if ($last_element->getValue() === 'IF')
		{
	        if (preg_match('#^\s*\.([a-zA-Z0-9_\.]+)#', substr($this->code, $this->cursor), $match))
	        {
	            $this->pushToken(Twig_Token::STRING_TYPE, stripcslashes($match[1]));
	            $this->moveCursor($match[0]);
	        }
		}

		/**
		* This is some compatibility code to continue supporting expressions such as:
		* <!-- DEFINE $VAR = 'foo' -->
		*/
		if ($last_element->getValue() === 'DEFINE')
		{
	        if (preg_match('#^\s*\$([A-Z0-9]+)#', substr($this->code, $this->cursor), $match))
	        {
	            $this->pushToken(Twig_Token::STRING_TYPE, stripcslashes($match[1]));
	            $this->moveCursor($match[1]);
	        }
		}
	}
}
