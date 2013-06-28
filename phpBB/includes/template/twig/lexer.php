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
			/*'BEGIN',
			'BEGINELSE',
			'END',*/
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

		// Fix our BEGIN statements
		$code = $this->fix_begin_tokens($code);

		// Replace <!-- INCLUDE blah.html --> with {% include 'blah.html' %}
		$code = preg_replace('#<!-- INCLUDE(PHP)? (.*?) -->#', "{% INCLUDE$1 '$2' %}", $code);

		// This strips the $ inside of a tag directly after the token, which was used in <!-- DEFINE $NAME
		$code = preg_replace('#<!-- DEFINE \$(.*)-->#', '<!-- DEFINE $1-->', $code);

		// This strips the . or $ inside of a tag directly before a variable name, which was used in <!-- IF .blah and <!-- IF $BLAH
		// In case of .varname, it replaces it with varname|length (as this is how it was treated before)
		$code = preg_replace_callback('#<!-- IF((.*)[\s][\$|\.]([^\s]+)(.*))-->#', array($this, 'tag_if_cleanup'), $code);

		// Replace all of our starting tokens, <!-- TOKEN --> with Twig style, {% TOKEN %}
		// This also strips outer parenthesis, <!-- IF (blah) --> becomes <!-- IF blah -->
		$code = preg_replace('#<!-- (' . implode('|', $valid_starting_tokens) . ')(?: (.*?) ?)?-->#', '{% $1 $2 %}', $code);

		// Replace all of our variables, {VARNAME} or {$VARNAME}, with Twig style, {{ VARNAME }}
		$code = preg_replace('#{\$?([a-zA-Z0-9_\.]+)}#', '{{ $1 }}', $code);

		return parent::tokenize($code, $filename);
	}

	/**
	* Fix begin tokens (convert our BEGIN to Twig for)
	*
	* Not meant to be used outside of this context, public because the anonymous function calls this
	*
	* @param string $code
	* @param array $parent_nodes
	* @return string
	*/
	public function fix_begin_tokens($code, $parent_nodes = array())
	{
		// PHP 5.3 cannot use $this in an anonymous function, so use this as a work-around
		$parent_class = $this;
		$callback = function ($matches) use ($parent_class, $parent_nodes)
		{
			$name = $matches[1];
			$subset = trim(substr($matches[2], 1, -1)); // Remove parenthesis
			$body = $matches[3];

			// Is the designer wanting to call another loop in a loop?
			// <!-- BEGIN loop -->
			// <!-- BEGIN !loop2 -->
			// <!-- END !loop2 -->
			// <!-- END loop -->
			// 'loop2' is actually on the same nesting level as 'loop' you assign
			// variables to it with template->assign_block_vars('loop2', array(...))
			if (strpos($name, '!') === 0)
			{
				// Count the number if ! occurrences
				$count = substr_count($name, '!');
				for ($i = 0; $i < $count; $i++)
				{
					array_pop($parent_nodes);
					$name = substr($name, 1);
				}
			}

			// Remove all parent nodes, e.g. foo, bar from foo.bar.foobar
			foreach ($parent_nodes as $node)
			{
				$body = preg_replace('#([^a-zA-Z0-9])' . $node . '\.#', '$1', $body);
			}

			// Add current node to list of parent nodes for child nodes
			$parent_nodes[] = $name;

			// Recursive...fix any child nodes
			$body = $parent_class->fix_begin_tokens($body, $parent_nodes);

			// Rename loopname vars (to prevent collisions, loop children are named (loop name)_loop_element)
			$body = str_replace($name . '.', $name . '_loop_element.', $body);

			// Need the parent variable name
			array_pop($parent_nodes);
			$parent = (!empty($parent_nodes)) ? end($parent_nodes) . '_loop_element.' : '';

			if ($subset !== '')
			{
				$subset = '|subset(' . $subset . ')';
			}

			// Turn into a Twig for loop, using (loop name)_loop_element for each child
			return "{% for {$name}_loop_element in {$parent}{$name}{$subset} %}{$body}{% endfor %}";
		};

		// Replace <!-- BEGINELSE --> correctly, only needs to be done once
		$code = str_replace('<!-- BEGINELSE -->', '{% else %}', $code);

		return preg_replace_callback('#<!-- BEGIN ([!a-zA-Z0-9_]+)(\([0-9,\-]+\))? -->(.+?)<!-- END \1 -->#s', $callback, $code);
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
		// Replace $TEST with TEST
		$matches[1] = preg_replace('#\s\$([a-zA-Z_0-9]+)#', ' $1', $matches[1]);
		$matches[1] = preg_replace('#\s\.([a-zA-Z_0-9]+)#', ' $1|length', $matches[1]);

		return '<!-- IF ' . $matches[1] . ' -->';
	}
}
