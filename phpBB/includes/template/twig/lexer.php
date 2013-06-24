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
			'INCLUDEPHP',*/
			'INCLUDEJS',
			'PHP',
			'ENDPHP',
			'EVENT',
		);

		// Replace <!-- INCLUDE blah.html --> with {% include 'blah.html' %}
		$code = preg_replace('#<!-- INCLUDE(PHP)? (.*?) -->#', "{% INCLUDE$1 '$2' %}", $code);

		// This strips the $ inside of a tag directly after the token, which was used in <!-- DEFINE $NAME
		$code = preg_replace('#<!-- DEFINE \$(.*)-->#', '<!-- DEFINE $1-->', $code);

		// This strips the . or $ inside of a tag directly before a variable name, which was used in <!-- IF .blah
		$code = preg_replace_callback('#<!-- IF((.*)[\s][\$|\.]([^\s]+)(.*))-->#', array($this, 'tag_if_cleanup'), $code);

		// Replace all of our starting tokens, <!-- TOKEN --> with Twig style, {% TOKEN %}
		// This also strips outer parenthesis, <!-- IF (blah) --> becomes <!-- IF blah -->
		$code = preg_replace('#<!-- (' . implode('|', $valid_starting_tokens) . ')(?: (.*?) ?)?-->#', '{% $1 $2 %}', $code);

		// Replace all of our variables, {VARNAME} or {$VARNAME}, with Twig style, {{ VARNAME }}
		$code = preg_replace('#{\$?([a-zA-Z0-9_\.]+)}#', '{{ $1 }}', $code);

		return parent::tokenize($code, $filename);
	}

	/**
	* preg_replace_callback to clean up IF statements
	*
	* This strips the . or $ inside of a tag directly before a variable name.
	* Was used in <!-- IF .blah or <!-- IF $BLAH
	*
	* @param mixed $matches
	*/
	protected function tag_if_cleanup($matches)
	{
		return '<!-- IF ' . preg_replace('#\s[\.|\$]([a-zA-Z_0-9]+)#', ' $1', $matches[1]) . ' -->';
	}
}
