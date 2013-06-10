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
	public function tokenize($code, $filename = null)
	{
		$valid_starting_tokens = array(
			'BEGIN',
			'BEGINELSE',
			'END',
			'IF',
			'ELSE',
			'ELSEIF',
			'ENDIF',
			'DEFINE',
			'DEFINE',
			'UNDEFINE',
			'ENDDEFINE',
			/*'INCLUDE',
			'INCLUDEPHP',
			'INCLUDEJS',*/
			'PHP',
			'ENDPHP',
			'EVENT',
		);
		
		// Replace <!-- INCLUDE blah.html --> with {% include 'blah.html' %}
		$code = preg_replace('#<!-- INCLUDE(PHP|JS)? (.*?) -->#', "{% INCLUDE$1 '$2' %}", $code);

		// Replace all of our starting tokens, <!-- TOKEN --> with Twig style, {% TOKEN %}
		// This also strips the $ inside of a tag directly after the token, which was used in <!-- DEFINE $NAME
		// This also strips the . inside of a tag directly after the token, which was used in <!-- IF .blah
		// This also strips outer parenthesis, <!-- IF (blah) --> becomes <!-- IF blah -->
		$code = preg_replace('#<!-- (' . implode('|', $valid_starting_tokens) . ') (not )?(\$|\.)?(?:(.*?) ?)?-->#', '{% $1 $2$4 %}', $code);
		
		// Replace all of our variables, {VARNAME} or {$VARNAME}, with Twig style, {{ VARNAME }}
		$code = preg_replace('#{\$?([A-Z_][A-Z_0-9]+)}#', '{{ $1 }}', $code);
//echo $code;
//exit;
		return parent::tokenize($code, $filename);
	}
}
