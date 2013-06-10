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
		/**
		* This is some compatibility code to continue supporting expressions such as:
		* <!-- IF .blah -->
		* 
		* This does not seem very efficient, but I have not been able to find a better 
		* 	method which works properly (maybe lexData can do it better, @todo test this)
		*/
		$last_element = end($this->tokens);
		if ($last_element->getValue() === '.')
		{
			$last_element2 = prev($this->tokens);

			if ($last_element2->getValue() === 'IF')
			{
				array_pop($this->tokens);
			}
		}
        
		parent::lexExpression();
	}
}
