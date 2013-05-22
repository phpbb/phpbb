<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group, sections (c) 2001 ispi of Lincoln Inc
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

/**
* The template filter that does the actual compilation
*
* psoTFX, phpBB Development Team - Completion of file caching, decompilation
* routines and implementation of conditionals/keywords and associated changes
*
* The interface was inspired by PHPLib templates, and the template file (formats are
* quite similar)
*
* The keyword/conditional implementation is currently based on sections of code from
* the Smarty templating engine (c) 2001 ispi of Lincoln, Inc. which is released
* (on its own and in whole) under the LGPL. Section 3 of the LGPL states that any code
* derived from an LGPL application may be relicenced under the GPL, this applies
* to this source
*
* DEFINE directive inspired by a request by Cyberalien
*
* @see template_compile
* @package phpBB3
*/
class phpbb_template_filter extends php_user_filter
{
	const REGEX_NS = '[a-z_][a-z_0-9]+';

	const REGEX_VAR = '[A-Z_][A-Z_0-9]+';
	const REGEX_VAR_SUFFIX = '[A-Z_0-9]+';

	const REGEX_TAG = '<!-- ([A-Z][A-Z_0-9]+)(?: (.*?) ?)?-->';

	const REGEX_TOKENS = '~<!-- ([A-Z][A-Z_0-9]+)(?: (.*?) ?)?-->|{((?:[a-z_][a-z_0-9]+\.)*\\$?[A-Z][A-Z_0-9]+)}~';

	/**
	* @var array
	*/
	private $block_names = array();

	/**
	* @var array
	*/
	private $block_else_level = array();

	/**
	* @var string
	*/
	private $chunk;

	/**
	* @var bool
	*/
	private $in_php;

	/**
	* Whether inline PHP code, <!-- PHP --> and <!-- INCLUDEPHP --> tags
	* are allowed. If this is false all PHP code will be silently
	* removed from the template during compilation.
	*
	* @var bool
	*/
	private $allow_php;

	/**
	* Whether cleanup will be performed on resulting code, see compile()
	* (Preserve whitespace)
	*
	* @var bool
	*/
	private $cleanup = true;

	/**
	* Resource locator.
	*
	* @var phpbb_template_locator
	*/
	private $locator;

	/**
	* @var string phpBB root path
	*/
	private $phpbb_root_path;

	/**
	* Name of the style that the template being compiled and/or rendered
	* belongs to, and its parents, in inheritance tree order.
	*
	* Used to invoke style-specific template events.
	*
	* @var array
	*/
	private $style_names;

	/**
	* Extension manager.
	*
	* @var phpbb_extension_manager
	*/
	private $extension_manager;

	/**
	* Current user
	*
	* @var phpbb_user
	*/
	private $user;

	/**
	* Template compiler.
	*
	* @var phpbb_template_compile
	*/
	private $template_compile;

	/**
	* Stream filter
	*
	* Is invoked for evey chunk of the stream, allowing us
	* to work on a chunk at a time, which saves memory.
	*/
	public function filter($in, $out, &$consumed, $closing)
	{
		$written = false;
		$first = false;

		while ($bucket = stream_bucket_make_writeable($in))
		{
			$consumed += $bucket->datalen;

			$data = $this->chunk . $bucket->data;
			$last_nl = strrpos($data, "\n");
			$this->chunk = substr($data, $last_nl);
			$data = substr($data, 0, $last_nl);

			if (!strlen($data))
			{
				continue;
			}

			$written = true;

			$data = $this->compile($data);
			if (!$first)
			{
				$data = $this->prepend_preamble($data);
				$first = false;
			}
			$bucket->data = $data;
			$bucket->datalen = strlen($bucket->data);
			stream_bucket_append($out, $bucket);
		}

		if ($closing && strlen($this->chunk))
		{
			$written = true;
			$bucket = stream_bucket_new($this->stream, $this->compile($this->chunk));
			stream_bucket_append($out, $bucket);
		}

		return $written ? PSFS_PASS_ON : PSFS_FEED_ME;
	}

	/**
	* Initializer, called on creation.
	*
	* Get the allow_php option, style_names, root directory and locator from params,
	* which are passed to stream_filter_append.
	*
	* @return boolean Returns true
	*/
	public function onCreate()
	{
		$this->chunk = '';
		$this->in_php = false;
		$this->allow_php = $this->params['allow_php'];
		$this->locator = $this->params['locator'];
		$this->phpbb_root_path = $this->params['phpbb_root_path'];
		$this->style_names = $this->params['style_names'];
		$this->extension_manager = $this->params['extension_manager'];
		$this->cleanup = $this->params['cleanup'];
		if (isset($this->params['user']))
		{
			$this->user = $this->params['user'];
		}
		$this->template_compile = $this->params['template_compile'];
		return true;
	}

	/**
	* Compiles a chunk of template.
	*
	* The chunk must comprise of one or more complete lines from the source
	* template.
	*
	* @param string $data Chunk of source template to compile
	* @return string Compiled PHP/HTML code
	*/
	private function compile($data)
	{
		$block_start_in_php = $this->in_php;

		$data = preg_replace('#<(?:[\\?%]|script)#s', '<?php echo\'\\0\';?>', $data);
		$data = preg_replace_callback(self::REGEX_TOKENS, array($this, 'replace'), $data);

		// Remove php
		if (!$this->allow_php)
		{
			if ($block_start_in_php
				&& $this->in_php
				&& strpos($data, '<!-- PHP -->') === false
				&& strpos($data, '<!-- ENDPHP -->') === false)
			{
				// This is just php code
				return '';
			}
			$data = preg_replace('~^.*?<!-- ENDPHP -->~', '', $data);
			$data = preg_replace('~<!-- PHP -->.*?<!-- ENDPHP -->~', '', $data);
			$data = preg_replace('~<!-- ENDPHP -->.*?$~', '', $data);
		}

		if ($this->cleanup)
		{
			/*

			Preserve whitespace.
			PHP removes a newline after the closing tag (if it's there).
			This is by design:

			http://www.php.net/manual/en/language.basic-syntax.phpmode.php
			http://www.php.net/manual/en/language.basic-syntax.instruction-separation.php


			Consider the following template:

				<!-- IF condition -->
				some content
				<!-- ENDIF -->

			If we were to simply preserve all whitespace, we could simply
			replace all "?>" tags with "?>\n".
			Doing that, would add additional newlines to the compiled
			template in place of the IF and ENDIF statements. These
			newlines are unwanted (and one is conditional). The IF and
			ENDIF are usually on their own line for ease of reading.

			This replacement preserves newlines only for statements that
			are not the only statement on a line. It will NOT preserve
			newlines at the end of statements in the above example.
			It will preserve newlines in situations like:

				<!-- IF condition -->inline content<!-- ENDIF -->

			*/

			$data = preg_replace('~(?<!^)(<\?php.+(?<!/\*\*/)\?>)$~m', "$1\n", $data);
			$data = str_replace('/**/?>', "?>\n", $data);
			$data = str_replace('?><?php', '', $data);
		}

		return $data;
	}

	/**
	* Prepends a preamble to compiled template.
	* Currently preamble checks if IN_PHPBB is defined and calls exit() if it is not.
	*
	* @param string $data Compiled template chunk
	* @return string Compiled template chunk with preamble prepended
	*/
	private function prepend_preamble($data)
	{
		$data = "<?php if (!defined('IN_PHPBB')) exit;" . ((strncmp($data, '<?php', 5) === 0) ? substr($data, 5) : ' ?>' . $data);
		return $data;
	}

	/**
	* Callback for replacing matched tokens with compiled template code.
	*
	* Compiled template code is an HTML stream with embedded PHP.
	*
	* @param array $matches Regular expression matches
	* @return string compiled template code
	*/
	private function replace($matches)
	{
		if ($this->in_php && $matches[1] != 'ENDPHP')
		{
			return '';
		}

		if (isset($matches[3]))
		{
			return $this->compile_var_tags($matches[0]);
		}

		switch ($matches[1])
		{
			case 'BEGIN':
				$this->block_else_level[] = false;
				return '<?php ' . $this->compile_tag_block($matches[2]) . ' ?>';
			break;

			case 'BEGINELSE':
				$this->block_else_level[sizeof($this->block_else_level) - 1] = true;
				return '<?php }} else { ?>';
			break;

			case 'END':
				array_pop($this->block_names);
				return '<?php ' . ((array_pop($this->block_else_level)) ? '}' : '}}') . ' ?>';
			break;

			case 'IF':
				return '<?php ' . $this->compile_tag_if($matches[2], false) . ' ?>';
			break;

			case 'ELSE':
				return '<?php } else { ?>';
			break;

			case 'ELSEIF':
				return '<?php ' . $this->compile_tag_if($matches[2], true) . ' ?>';
			break;

			case 'ENDIF':
				return '<?php } ?>';
			break;

			case 'DEFINE':
				return '<?php ' . $this->compile_tag_define($matches[2], true) . ' ?>';
			break;

			case 'UNDEFINE':
				return '<?php ' . $this->compile_tag_define($matches[2], false) . ' ?>';
			break;

			case 'ENDDEFINE':
				return '<?php ' . $this->compile_tag_enddefine() . ' ?>';
			break;

			case 'INCLUDE':
				return '<?php ' . $this->compile_tag_include($matches[2]) . ' ?>';
			break;

			case 'INCLUDEPHP':
				return ($this->allow_php) ? '<?php ' . $this->compile_tag_include_php($matches[2]) . ' ?>' : '';
			break;

			case 'INCLUDEJS':
				return '<?php ' . $this->compile_tag_include_js($matches[2]) . ' ?>';
			break;

			case 'PHP':
				if ($this->allow_php)
				{
					$this->in_php = true;
					return '<?php ';
				}
				return '<!-- PHP -->';
			break;

			case 'ENDPHP':
				if ($this->allow_php)
				{
					$this->in_php = false;
					return ' ?>';
				}
				return '<!-- ENDPHP -->';
			break;

			case 'EVENT':
				return '<?php ' . $this->compile_tag_event($matches[2]) . '?>';
			break;

			default:
				return $matches[0];
			break;

		}
		return '';
	}

	/**
	* Convert template variables into PHP varrefs
	*
	* @param string $text_blocks Variable reference in source template
	* @param bool $is_expr Returns whether the source was an expression type variable (i.e. S_FIRST_ROW)
	* @return string PHP variable name
	*/
	private function get_varref($text_blocks, &$is_expr)
	{
		// change template varrefs into PHP varrefs
		$varrefs = array();

		// This one will handle varrefs WITH namespaces
		preg_match_all('#\{((?:' . self::REGEX_NS . '\.)+)(\$)?(' . self::REGEX_VAR . ')\}#', $text_blocks, $varrefs, PREG_SET_ORDER);

		foreach ($varrefs as $var_val)
		{
			$namespace = $var_val[1];
			$varname = $var_val[3];
			$new = $this->generate_block_varref($namespace, $varname, $is_expr, $var_val[2]);

			$text_blocks = str_replace($var_val[0], $new, $text_blocks);
		}

		// Language variables cannot be reduced to a single varref, so they must be skipped
		// These two replacements would break language variables, so we can only run them on non-language types
		if (strpos($text_blocks, '{L_') === false && strpos($text_blocks, '{LA_') === false)
		{
			// This will handle the remaining root-level varrefs
			$text_blocks = preg_replace('#\{(' . self::REGEX_VAR . ')\}#', "\$_rootref['\\1']", $text_blocks);
			$text_blocks = preg_replace('#\{\$(' . self::REGEX_VAR . ')\}#', "\$_tpldata['DEFINE']['.']['\\1']", $text_blocks);
		}

		return $text_blocks;
	}

	/**
	* Parse paths of the form {FOO}/a/{BAR}/b
	*
	* Note: this method assumes at least one variable in the path, this should
	* be checked before this method is called.
	*
	* @param string $path The path to parse
	* @param string $include_type The type of template function to call
	* @return string An appropriately formatted string to include in the
	* 	template or an empty string if an expression like S_FIRST_ROW was
	* 	incorrectly used
	*/
	private function parse_dynamic_path($path, $include_type)
	{
		$matches = array();
		$replace = array();
		$is_expr = true;

		preg_match_all('#\{((?:' . self::REGEX_NS . '\.)*)(\$)?(' . self::REGEX_VAR . ')\}#', $path, $matches);
		foreach ($matches[0] as $var_str)
		{
			$tmp_is_expr = false;
			$var = $this->get_varref($var_str, $tmp_is_expr);
			$is_expr = $is_expr && $tmp_is_expr;
			$replace[] = "' . $var . '";
		}

		if (!$is_expr)
		{
			return " \$_template->$include_type('" . str_replace($matches[0], $replace, $path) . "', true);";
		}
		else
		{
			return '';
		}
	}

	/**
	* Compile variables
	*
	* @param string $text_blocks Variable reference in source template
	* @return string compiled template code
	*/
	private function compile_var_tags(&$text_blocks)
	{
		$text_blocks = $this->get_varref($text_blocks, $is_expr);
		$lang_replaced = $this->compile_language_tags($text_blocks);

		if(!$lang_replaced)
		{
			$text_blocks = '<?php echo ' . ($is_expr ? "$text_blocks" : "(isset($text_blocks)) ? $text_blocks : ''") . '; /**/?>';
		}

		return $text_blocks;
	}

	/**
	* Handles special language tags L_ and LA_
	*
	* @param string $text_blocks Variable reference in source template
	* @return bool Whether a replacement occurred or not
	*/
	private function compile_language_tags(&$text_blocks)
	{
		$replacements = 0;

		// transform vars prefixed by L_ into their language variable pendant if nothing is set within the tpldata array
		if (strpos($text_blocks, '{L_') !== false)
		{
			$text_blocks = preg_replace('#\{L_(' . self::REGEX_VAR_SUFFIX . ')\}#', "<?php echo ((isset(\$_rootref['L_\\1'])) ? \$_rootref['L_\\1'] : ((isset(\$_lang['\\1'])) ? \$_lang['\\1'] : '{ \\1 }')); /**/?>", $text_blocks, -1, $replacements);
			return (bool) $replacements;
		}

		// Handle addslashed language variables prefixed with LA_
		// If a template variable already exist, it will be used in favor of it...
		if (strpos($text_blocks, '{LA_') !== false)
		{
			$text_blocks = preg_replace('#\{LA_(' . self::REGEX_VAR_SUFFIX . '+)\}#', "<?php echo ((isset(\$_rootref['LA_\\1'])) ? \$_rootref['LA_\\1'] : ((isset(\$_rootref['L_\\1'])) ? addslashes(\$_rootref['L_\\1']) : ((isset(\$_lang['\\1'])) ? addslashes(\$_lang['\\1']) : '{ \\1 }'))); /**/?>", $text_blocks, -1, $replacements);
			return (bool) $replacements;
		}

		return false;
	}

	/**
	* Compile blocks
	*
	* @param string $tag_args Block contents in source template
	* @return string compiled template code
	*/
	private function compile_tag_block($tag_args)
	{
		$no_nesting = false;

		// Is the designer wanting to call another loop in a loop?
		// <!-- BEGIN loop -->
		// <!-- BEGIN !loop2 -->
		// <!-- END !loop2 -->
		// <!-- END loop -->
		// 'loop2' is actually on the same nesting level as 'loop' you assign
		// variables to it with template->assign_block_vars('loop2', array(...))
		if (strpos($tag_args, '!') === 0)
		{
			// Count the number if ! occurrences (not allowed in vars)
			$no_nesting = substr_count($tag_args, '!');
			$tag_args = substr($tag_args, $no_nesting);
		}

		// Allow for control of looping (indexes start from zero):
		// foo(2)    : Will start the loop on the 3rd entry
		// foo(-2)   : Will start the loop two entries from the end
		// foo(3,4)  : Will start the loop on the fourth entry and end it on the fifth
		// foo(3,-4) : Will start the loop on the fourth entry and end it four from last
		$match = array();

		if (preg_match('#^([^()]*)\(([\-\d]+)(?:,([\-\d]+))?\)$#', $tag_args, $match))
		{
			$tag_args = $match[1];

			if ($match[2] < 0)
			{
				$loop_start = '($_' . $tag_args . '_count ' . $match[2] . ' < 0 ? 0 : $_' . $tag_args . '_count ' . $match[2] . ')';
			}
			else
			{
				$loop_start = '($_' . $tag_args . '_count < ' . $match[2] . ' ? $_' . $tag_args . '_count : ' . $match[2] . ')';
			}

			if (!isset($match[3]) || strlen($match[3]) < 1 || $match[3] == -1)
			{
				$loop_end = '$_' . $tag_args . '_count';
			}
			else if ($match[3] >= 0)
			{
				$loop_end = '(' . ($match[3] + 1) . ' > $_' . $tag_args . '_count ? $_' . $tag_args . '_count : ' . ($match[3] + 1) . ')';
			}
			else //if ($match[3] < -1)
			{
				$loop_end = '$_' . $tag_args . '_count' . ($match[3] + 1);
			}
		}
		else
		{
			$loop_start = 0;
			$loop_end = '$_' . $tag_args . '_count';
		}

		$tag_template_php = '';
		array_push($this->block_names, $tag_args);

		if ($no_nesting !== false)
		{
			// We need to implode $no_nesting times from the end...
			$block = array_slice($this->block_names, -$no_nesting);
		}
		else
		{
			$block = $this->block_names;
		}

		if (sizeof($block) < 2)
		{
			// Block is not nested.
			$tag_template_php = '$_' . $tag_args . "_count = (isset(\$_tpldata['$tag_args'])) ? sizeof(\$_tpldata['$tag_args']) : 0;";
			$varref = "\$_tpldata['$tag_args']";
		}
		else
		{
			// This block is nested.
			// Generate a namespace string for this block.
			$namespace = implode('.', $block);

			// Get a reference to the data array for this block that depends on the
			// current indices of all parent blocks.
			$varref = $this->generate_block_data_ref($namespace, false);

			// Create the for loop code to iterate over this block.
			$tag_template_php = '$_' . $tag_args . '_count = (isset(' . $varref . ')) ? sizeof(' . $varref . ') : 0;';
		}

		$tag_template_php .= 'if ($_' . $tag_args . '_count) {';

		/**
		* The following uses foreach for iteration instead of a for loop, foreach is faster but requires PHP to make a copy of the contents of the array which uses more memory
		* <code>
		*	if (!$offset)
		*	{
		*		$tag_template_php .= 'foreach (' . $varref . ' as $_' . $tag_args . '_i => $_' . $tag_args . '_val){';
		*	}
		* </code>
		*/

		$tag_template_php .= 'for ($_' . $tag_args . '_i = ' . $loop_start . '; $_' . $tag_args . '_i < ' . $loop_end . '; ++$_' . $tag_args . '_i){';
		$tag_template_php .= '$_' . $tag_args . '_val = &' . $varref . '[$_' . $tag_args . '_i];';

		return $tag_template_php;
	}

	/**
	* Compile a general expression - much of this is from Smarty with
	* some adaptions for our block level methods
	*
	* @param string $tag_args Expression (tag arguments) in source template
	* @return string compiled template code
	*/
	private function compile_expression($tag_args)
	{
		$match = array();
		preg_match_all('/(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
			\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
			[(),]                                  |
			[^\s(),]+)/x', $tag_args, $match);

		$tokens = $match[0];
		$is_arg_stack = array();

		for ($i = 0, $size = sizeof($tokens); $i < $size; $i++)
		{
			$token = &$tokens[$i];

			switch ($token)
			{
				case '!==':
				case '===':
				case '<<':
				case '>>':
				case '|':
				case '^':
				case '&':
				case '~':
				case ')':
				case ',':
				case '+':
				case '-':
				case '*':
				case '/':
				case '@':
				break;

				case '==':
				case 'eq':
					$token = '==';
				break;

				case '!=':
				case '<>':
				case 'ne':
				case 'neq':
					$token = '!=';
				break;

				case '<':
				case 'lt':
					$token = '<';
				break;

				case '<=':
				case 'le':
				case 'lte':
					$token = '<=';
				break;

				case '>':
				case 'gt':
					$token = '>';
				break;

				case '>=':
				case 'ge':
				case 'gte':
					$token = '>=';
				break;

				case '&&':
				case 'and':
					$token = '&&';
				break;

				case '||':
				case 'or':
					$token = '||';
				break;

				case '!':
				case 'not':
					$token = '!';
				break;

				case '%':
				case 'mod':
					$token = '%';
				break;

				case '(':
					array_push($is_arg_stack, $i);
				break;

				case 'is':
					$is_arg_start = ($tokens[$i-1] == ')') ? array_pop($is_arg_stack) : $i-1;
					$is_arg = implode(' ', array_slice($tokens, $is_arg_start, $i - $is_arg_start));

					$new_tokens = $this->_parse_is_expr($is_arg, array_slice($tokens, $i+1));

					array_splice($tokens, $is_arg_start, sizeof($tokens), $new_tokens);

					$i = $is_arg_start;

				// no break

				default:
					$varrefs = array();
					if (preg_match('#^((?:' . self::REGEX_NS . '\.)+)?(\$)?(?=[A-Z])([A-Z0-9\-_]+)#s', $token, $varrefs))
					{
						if (!empty($varrefs[1]))
						{
							$namespace = substr($varrefs[1], 0, -1);
							$dot_pos = strrchr($namespace, '.');
							if ($dot_pos !== false)
							{
								$namespace = substr($dot_pos, 1);
							}

							// S_ROW_COUNT is deceptive, it returns the current row number not the number of rows
							// hence S_ROW_COUNT is deprecated in favour of S_ROW_NUM
							switch ($varrefs[3])
							{
								case 'S_ROW_NUM':
								case 'S_ROW_COUNT':
									$token = "\$_${namespace}_i";
								break;

								case 'S_NUM_ROWS':
									$token = "\$_${namespace}_count";
								break;

								case 'S_FIRST_ROW':
									$token = "(\$_${namespace}_i == 0)";
								break;

								case 'S_LAST_ROW':
									$token = "(\$_${namespace}_i == \$_${namespace}_count - 1)";
								break;

								case 'S_BLOCK_NAME':
									$token = "'$namespace'";
								break;

								default:
									$token = $this->generate_block_data_ref(substr($varrefs[1], 0, -1), true, $varrefs[2]) . '[\'' . $varrefs[3] . '\']';
									$token = '(isset(' . $token . ') ? ' . $token . ' : null)';
								break;
							}
						}
						else
						{
							$token = ($varrefs[2]) ? '$_tpldata[\'DEFINE\'][\'.\'][\'' . $varrefs[3] . '\']' : '$_rootref[\'' . $varrefs[3] . '\']';
							$token = '(isset(' . $token . ') ? ' . $token . ' : null)';
						}

					}
					else if (preg_match('#^\.((?:' . self::REGEX_NS . '\.?)+)$#s', $token, $varrefs))
					{
						// Allow checking if loops are set with .loopname
						// It is also possible to check the loop count by doing <!-- IF .loopname > 1 --> for example
						$blocks = explode('.', $varrefs[1]);

						// If the block is nested, we have a reference that we can grab.
						// If the block is not nested, we just go and grab the block from _tpldata
						if (sizeof($blocks) > 1)
						{
							$block = array_pop($blocks);
							$namespace = implode('.', $blocks);
							$varref = $this->generate_block_data_ref($namespace, true);

							// Add the block reference for the last child.
							$varref .= "['" . $block . "']";
						}
						else
						{
							$varref = '$_tpldata';

							// Add the block reference for the last child.
							$varref .= "['" . $blocks[0] . "']";
						}
						$token = "(isset($varref) ? sizeof($varref) : 0)";
					}

				break;
			}
		}

		return $tokens;
	}

	/**
	* Compile IF tags
	*
	* @param string $tag_args Expression given with IF in source template
	* @param bool $elseif True if compiling an IF tag, false if compiling an ELSEIF tag
	* @return string compiled template code
	*/
	private function compile_tag_if($tag_args, $elseif)
	{
		$tokens = $this->compile_expression($tag_args);

		$tpl = ($elseif) ? '} else if (' : 'if (';

		$tpl .= implode(' ', $tokens);
		$tpl .= ') { ';

		return $tpl;
	}

	/**
	* Compile DEFINE tags
	*
	* @param string $tag_args Expression given with DEFINE in source template
	* @param bool $op True if compiling a DEFINE tag, false if compiling an UNDEFINE tag
	* @return string compiled template code
	*/
	private function compile_tag_define($tag_args, $op)
	{
		$match = array();
		preg_match('#^((?:' . self::REGEX_NS . '\.)+)?\$(?=[A-Z])([A-Z0-9_\-]*)(?: = (.*?))?$#', $tag_args, $match);

		if (!empty($match[2]) && !isset($match[3]) && $op)
		{
			// DEFINE tag with ENDDEFINE
			$array = "\$_tpldata['DEFINE']['.vars']";
			$code = 'ob_start(); ';
			$code .= "if (!isset($array)) { $array = array(); } ";
			$code .= "{$array}[] = '{$match[2]}'";
			return $code;
		}

		if (empty($match[2]) || (!isset($match[3]) && $op))
		{
			return '';
		}

		if (!$op)
		{
			return 'unset(' . (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[2] . '\']' : '$_tpldata[\'DEFINE\'][\'.\'][\'' . $match[2] . '\']') . ');';
		}

		/*
		* Define tags that contain template variables (enclosed in curly brackets)
		* need to be treated differently.
		*/
		if (substr($match[3], 1, 1) == '{' && substr($match[3], -2, 1) == '}')
		{
			$parsed_statement = implode(' ', $this->compile_expression(substr($match[3], 2, -2)));
		}
		else
		{
			$parsed_statement = implode(' ', $this->compile_expression($match[3]));
		}

		return (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[2] . '\']' : '$_tpldata[\'DEFINE\'][\'.\'][\'' . $match[2] . '\']') . ' = ' . $parsed_statement . ';';
	}

	/**
	* Compile ENDDEFINE tag
	*
	* @return string compiled template code
	*/
	private function compile_tag_enddefine()
	{
		$array = "\$_tpldata['DEFINE']['.vars']";
		$code = "if (!isset($array) || !sizeof($array)) { trigger_error('ENDDEFINE tag without DEFINE in ' . basename(__FILE__), E_USER_ERROR); }";
		$code .= "\$define_var = array_pop($array); ";
		$code .= "\$_tpldata['DEFINE']['.'][\$define_var] = ob_get_clean();";
		return $code;
	}

	/**
	* Compile INCLUDE tag
	*
	* @param string $tag_args Expression given with INCLUDE in source template
	* @return string compiled template code
	*/
	private function compile_tag_include($tag_args)
	{
		// Process dynamic includes
		if (strpos($tag_args, '{') !== false)
		{
			return $this->parse_dynamic_path($tag_args, '_tpl_include');
		}

		return "\$_template->_tpl_include('$tag_args');";
	}

	/**
	* Compile INCLUDE_PHP tag
	*
	* @param string $tag_args Expression given with INCLUDEPHP in source template
	* @return string compiled template code
	*/
	private function compile_tag_include_php($tag_args)
	{
		if (strpos($tag_args, '{') !== false)
		{
			return $this->parse_dynamic_path($tag_args, '_php_include');
		}

		return "\$_template->_php_include('$tag_args');";
	}

	/**
	* Compile EVENT tag.
	*
	* $tag_args should be a single string identifying the event.
	* The event name can contain letters, numbers and underscores only.
	* If an invalid event name is specified, an E_USER_ERROR will be
	* triggered.
	*
	* Event tags are only functional when the template engine has
	* an instance of the extension manager. Extension manager would
	* be called upon to find all extensions listening for the specified
	* event, and to obtain additional template fragments. All such
	* template fragments will be compiled and included in the generated
	* compiled template code for the current template being compiled.
	*
	* The above means that whenever an extension is enabled or disabled,
	* template cache should be cleared in order to update the compiled
	* template code for the active set of template event listeners.
	*
	* This also means that extensions cannot return different template
	* fragments at different times. Once templates are compiled, changing
	* such template fragments would have no effect.
	*
	* @param string $tag_args EVENT tag arguments, as a string - for EVENT this is the event name
	* @return string compiled template code
	*/
	private function compile_tag_event($tag_args)
	{
		if (!preg_match('/^\w+$/', $tag_args))
		{
			// The event location is improperly formatted,
			if ($this->user)
			{
				trigger_error($this->user->lang('ERR_TEMPLATE_EVENT_LOCATION', $tag_args), E_USER_ERROR);
			}
			else
			{
				trigger_error(sprintf('The specified template event location <em>[%s]</em> is improperly formatted.', $tag_args), E_USER_ERROR);
			}
		}
		$location = $tag_args;

		if ($this->extension_manager)
		{
			$finder = $this->extension_manager->get_finder();

			$files = $finder
				->extension_prefix($location)
				->extension_suffix('.html')
				->extension_directory("/styles/all/template")
				->get_files();

			foreach ($this->style_names as $style_name)
			{
				$more_files = $finder
					->extension_prefix($location)
					->extension_suffix('.html')
					->extension_directory("/styles/" . $style_name . "/template")
					->get_files();
				if (!empty($more_files))
				{
					$files = array_merge($files, $more_files);
					break;
				}
			}

			$all_compiled = '';
			foreach ($files as $file)
			{
				$this->template_compile->set_filter_params(array(
					'cleanup'	=> false,
				));

				$compiled = $this->template_compile->compile_file($file);

				$this->template_compile->reset_filter_params();

				if ($compiled === false)
				{
					if ($this->user)
					{
						trigger_error($this->user->lang('ERR_TEMPLATE_COMPILATION', phpbb_filter_root_path($file)), E_USER_ERROR);
					}
					else
					{
						trigger_error(sprintf('The file could not be compiled: %s', phpbb_filter_root_path($file)), E_USER_ERROR);
					}
				}

				$all_compiled .= $compiled;
			}
			// Need spaces inside php tags as php cannot grok
			// < ?php? > sans the spaces
			return ' ?' . '>' . $all_compiled . '<?php ';
		}
	}

	/**
	* parse expression
	* This is from Smarty
	*/
	private function _parse_is_expr($is_arg, $tokens)
	{
		$expr_end = 0;
		$negate_expr = false;

		if (($first_token = array_shift($tokens)) == 'not')
		{
			$negate_expr = true;
			$expr_type = array_shift($tokens);
		}
		else
		{
			$expr_type = $first_token;
		}

		switch ($expr_type)
		{
			case 'even':
				if (isset($tokens[$expr_end]) && $tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "!(($is_arg / $expr_arg) & 1)";
				}
				else
				{
					$expr = "!($is_arg & 1)";
				}
			break;

			case 'odd':
				if (isset($tokens[$expr_end]) && $tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "(($is_arg / $expr_arg) & 1)";
				}
				else
				{
					$expr = "($is_arg & 1)";
				}
			break;

			case 'div':
				if (isset($tokens[$expr_end]) && $tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "!($is_arg % $expr_arg)";
				}
			break;
		}

		if ($negate_expr)
		{
			if ($expr[0] == '!')
			{
				// Negated expression, de-negate it.
				$expr = substr($expr, 1);
			}
			else
			{
				$expr = "!($expr)";
			}
		}

		array_splice($tokens, 0, $expr_end, $expr);

		return $tokens;
	}

	/**
	* Compile INCLUDEJS tag
	*
	* @param string $tag_args Expression given with INCLUDEJS in source template
	* @return string compiled template code
	*/
	private function compile_tag_include_js($tag_args)
	{
		// Process dynamic includes
		if (strpos($tag_args, '{') !== false)
		{
			return $this->parse_dynamic_path($tag_args, '_js_include');
		}

		// Locate file
		$filename = $this->locator->get_first_file_location(array($tag_args), false, true);

		if ($filename === false)
		{
			// File does not exist, find it during run time
			return ' $_template->_js_include(\'' . addslashes($tag_args) . '\', true); ';
		}

		if (substr($filename, 0, strlen($this->phpbb_root_path)) != $this->phpbb_root_path)
		{
			// Absolute path, include as is
			return ' $_template->_js_include(\'' . addslashes($filename) . '\', false, false); ';
		}

		// Relative path, remove root path from it
		$filename = substr($filename, strlen($this->phpbb_root_path));
		return ' $_template->_js_include(\'' . addslashes($filename) . '\', false, true); ';
	}

	/**
	* Generates a reference to the given variable inside the given (possibly nested)
	* block namespace. This is a string of the form:
	* ' . $_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['varname'] . '
	* It's ready to be inserted into an "echo" line in one of the templates.
	*
	* @param string $namespace Namespace to access (expects a trailing "." on the namespace)
	* @param string $varname Variable name to use
	* @param bool $expr Returns whether the source was an expression type
	* @param bool $defop If true this is a variable created with the DEFINE construct, otherwise template variable
	* @return string Code to access variable or echo it if $echo is true
	*/
	private function generate_block_varref($namespace, $varname, &$expr, $defop = false)
	{
		// Strip the trailing period.
		$namespace = substr($namespace, 0, -1);

		if (($pos = strrpos($namespace, '.')) !== false)
		{
			$local_namespace = substr($namespace, $pos + 1);
		}
		else
		{
			$local_namespace = $namespace;
		}

		$expr = true;

		// S_ROW_COUNT is deceptive, it returns the current row number now the number of rows
		// hence S_ROW_COUNT is deprecated in favour of S_ROW_NUM
		switch ($varname)
		{
			case 'S_ROW_NUM':
			case 'S_ROW_COUNT':
				$varref = "\$_${local_namespace}_i";
			break;

			case 'S_NUM_ROWS':
				$varref = "\$_${local_namespace}_count";
			break;

			case 'S_FIRST_ROW':
				$varref = "(\$_${local_namespace}_i == 0)";
			break;

			case 'S_LAST_ROW':
				$varref = "(\$_${local_namespace}_i == \$_${local_namespace}_count - 1)";
			break;

			case 'S_BLOCK_NAME':
				$varref = "'$local_namespace'";
			break;

			default:
				// Get a reference to the data block for this namespace.
				$varref = $this->generate_block_data_ref($namespace, true, $defop);
				// Prepend the necessary code to stick this in an echo line.

				// Append the variable reference.
				$varref .= "['$varname']";

				$expr = false;
			break;
		}
		// @todo Test the !$expr more

		return $varref;
	}

	/**
	* Generates a reference to the array of data values for the given
	* (possibly nested) block namespace. This is a string of the form:
	* $_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['$childN']
	*
	* @param string $blockname Block to access (does not expect a trailing "." on the blockname)
	* @param bool $include_last_iterator If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
	* @param bool $defop If true this is a variable created with the DEFINE construct, otherwise template variable
	* @return string Code to access variable
	*/
	private function generate_block_data_ref($blockname, $include_last_iterator, $defop = false)
	{
		// Get an array of the blocks involved.
		$blocks = explode('.', $blockname);
		$blockcount = sizeof($blocks) - 1;

		// DEFINE is not an element of any referenced variable, we must use _tpldata to access it
		if ($defop)
		{
			$varref = '$_tpldata[\'DEFINE\']';
			// Build up the string with everything but the last child.
			for ($i = 0; $i < $blockcount; $i++)
			{
				$varref .= "['" . $blocks[$i] . "'][\$_" . $blocks[$i] . '_i]';
			}
			// Add the block reference for the last child.
			$varref .= "['" . $blocks[$blockcount] . "']";
			// Add the iterator for the last child if requried.
			if ($include_last_iterator)
			{
				$varref .= '[$_' . $blocks[$blockcount] . '_i]';
			}
			return $varref;
		}
		else if ($include_last_iterator)
		{
			return '$_'. $blocks[$blockcount] . '_val';
		}
		else
		{
			return '$_'. $blocks[$blockcount - 1] . '_val[\''. $blocks[$blockcount]. '\']';
		}
	}
}
