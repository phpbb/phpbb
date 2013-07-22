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
		// Our phpBB tags
		// Commented out tokens are handled separately from the main replace
		$phpbb_tags = array(
			/*'BEGIN',
			'BEGINELSE',
			'END',
			'IF',
			'ELSE',
			'ELSEIF',
			'ENDIF',
			'DEFINE',
			'UNDEFINE',*/
			'ENDDEFINE',
			'INCLUDE',
			'INCLUDEPHP',
			'INCLUDEJS',
			'INCLUDECSS',
			'PHP',
			'ENDPHP',
			'EVENT',
		);

		// Twig tag masks
		$twig_tags = array(
			'autoescape',
			'endautoescape',
			'if',
			'elseif',
			'else',
			'endif',
			'block',
			'endblock',
			'use',
			'extends',
			'embed',
			'filter',
			'endfilter',
			'flush',
			'for',
			'endfor',
			'macro',
			'endmacro',
			'import',
			'from',
			'sandbox',
			'endsandbox',
			'set',
			'endset',
			'spaceless',
			'endspaceless',
			'verbatim',
			'endverbatim',
		);

		// Fix tokens that may have inline variables (e.g. <!-- DEFINE $TEST = '{FOO}')
		$code = $this->fix_inline_variable_tokens(array(
			'DEFINE.+=',
			'INCLUDE',
			'INCLUDEPHP',
			'INCLUDEJS',
			'INCLUDECSS',
		), $code);

		// Fix our BEGIN statements
		$code = $this->fix_begin_tokens($code);

		// Fix our IF tokens
		$code = $this->fix_if_tokens($code);

		// Fix our DEFINE tokens
		$code = $this->fix_define_tokens($code);

		// Replace all of our starting tokens, <!-- TOKEN --> with Twig style, {% TOKEN %}
		// This also strips outer parenthesis, <!-- IF (blah) --> becomes <!-- IF blah -->
		$code = preg_replace('#<!-- (' . implode('|', $phpbb_tags) . ')(?: (.*?) ?)?-->#', '{% $1 $2 %}', $code);

		// Replace all of our twig masks with Twig code (e.g. <!-- BLOCK .+ --> with {% block $1 %})
		$code = $this->replace_twig_tag_masks($code, $twig_tags);

		// Replace all of our language variables, {L_VARNAME}, with Twig style, {{ lang('NAME') }}
		// Appends any filters after lang()
		$code = preg_replace('#{L_([a-zA-Z0-9_\.]+)(\|[^}]+?)?}#', '{{ lang(\'$1\')$2 }}', $code);

		// Replace all of our escaped language variables, {LA_VARNAME}, with Twig style, {{ lang('NAME')|addslashes }}
		// Appends any filters after lang(), but before addslashes
		$code = preg_replace('#{LA_([a-zA-Z0-9_\.]+)(\|[^}]+?)?}#', '{{ lang(\'$1\')$2|addslashes }}', $code);

		// Replace all of our variables, {VARNAME}, with Twig style, {{ VARNAME }}
		// Appends any filters
		$code = preg_replace('#{([a-zA-Z0-9_\.]+)(\|[^}]+?)?}#', '{{ $1$2 }}', $code);

		return parent::tokenize($code, $filename);
	}

	/**
	* Fix tokens that may have inline variables
	*
	* E.g. <!-- INCLUDE {TEST}.html
	*
	* @param array $tokens array of tokens to search for (imploded to a regular expression)
	* @param string $code
	* @return string
	*/
	protected function fix_inline_variable_tokens($tokens, $code)
	{
		$callback = function($matches)
		{
			// Remove any quotes that may have been used in different implementations
			// E.g. DEFINE $TEST = 'blah' vs INCLUDE foo
			// Replace {} with start/end to parse variables (' ~ TEST ~ '.html)
			$matches[2] = str_replace(array('"', "'", '{', '}'), array('', '', "' ~ ", " ~ '"), $matches[2]);

			// Surround the matches in single quotes ('' ~ TEST ~ '.html')
			return "<!-- {$matches[1]} '{$matches[2]}' -->";
		};

		return preg_replace_callback('#<!-- (' . implode('|', $tokens) . ') (.+?) -->#', $callback, $code);
	}

	/**
	* Fix begin tokens (convert our BEGIN to Twig for)
	*
	* Not meant to be used outside of this context, public because the anonymous function calls this
	*
	* @param string $code
	* @param array $parent_nodes (used in recursion)
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

			// Remove all parent nodes, e.g. foo, bar from foo.bar.foobar.VAR
			foreach ($parent_nodes as $node)
			{
				$body = preg_replace('#([^a-zA-Z0-9_])' . $node . '\.([a-zA-Z0-9_]+)\.#', '$1$2.', $body);
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
	* Fix IF statements
	*
	* @param string $code
	* @return string
	*/
	protected function fix_if_tokens($code)
	{
		$callback = function($matches)
		{
			$inner = $matches[2];
			// Replace $TEST with definition.TEST
			$inner = preg_replace('#\s\$([a-zA-Z_0-9]+)#', ' definition.$1', $inner);

			// Replace .test with test|length
			$inner = preg_replace('#\s\.([a-zA-Z_0-9\.]+)#', ' $1|length', $inner);

			return "<!-- {$matches[1]}IF{$inner}-->";
		};

		// Replace our "div by" with Twig's divisibleby (Twig does not like test names with spaces)
		$code = preg_replace('# div by ([0-9]+)#', ' divisibleby($1)', $code);

		return preg_replace_callback('#<!-- (ELSE)?IF((.*)[\s][\$|\.|!]([^\s]+)(.*))-->#', $callback, $code);
	}

	/**
	* Fix DEFINE statements and {$VARNAME} variables
	*
	* @param string $code
	* @return string
	*/
	protected function fix_define_tokens($code)
	{
		/**
		* Changing $VARNAME to definition.varname because set is only local
		* context (e.g. DEFINE $TEST will only make $TEST available in current
		* template and any child templates, but not any parent templates).
		*
		* DEFINE handles setting it properly to definition in its node, but the
		* variables reading FROM it need to be altered to definition.VARNAME
		*
		* Setting up definition as a class in the array passed to Twig
		* ($context) makes set definition.TEST available in the global context
		*/

		// Replace <!-- DEFINE $NAME with {% DEFINE definition.NAME
		$code = preg_replace('#<!-- DEFINE \$(.*)-->#', '{% DEFINE $1 %}', $code);

		// Changing UNDEFINE NAME to DEFINE NAME = null to save from creating an extra token parser/node
		$code = preg_replace('#<!-- UNDEFINE \$(.*)-->#', '{% DEFINE $1= null %}', $code);

		// Replace all of our variables, {$VARNAME}, with Twig style, {{ definition.VARNAME }}
		$code = preg_replace('#{\$([a-zA-Z0-9_\.]+)}#', '{{ definition.$1 }}', $code);

		// Replace all of our variables, ~ $VARNAME ~, with Twig style, ~ definition.VARNAME ~
		$code = preg_replace('#~ \$([a-zA-Z0-9_\.]+) ~#', '~ definition.$1 ~', $code);

		return $code;
	}

	/**
	* Replace Twig tag masks with Twig tag calls
	*
	* E.g. <!-- BLOCK foo --> with {% block foo %}
	*
	* @param string $code
	* @param array $twig_tags All tags we want to create a mask for
	* @return string
	*/
	protected function replace_twig_tag_masks($code, $twig_tags)
	{
		$callback = function ($matches)
		{
			$matches[1] = strtolower($matches[1]);

			return "{% {$matches[1]}{$matches[2]}%}";
		};

		foreach ($twig_tags as &$tag)
		{
			$tag = strtoupper($tag);
		}

		// twig_tags is an array of the twig tags, which are all lowercase, but we use all uppercase tags
		$code = preg_replace_callback('#<!-- (' . implode('|', $twig_tags) . ')(.*?)-->#',$callback, $code);

		return $code;
	}
}
