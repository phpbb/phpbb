<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group, sections (c) 2001 ispi of Lincoln Inc
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
 * @see template_compile
 *
 */
class template_filter extends php_user_filter
{
	private $regex = '~<!-- ([A-Z][A-Z_0-9]+)(?: (.*?) ?)?-->|{((?:[a-z][a-z_0-9]+\.)*\\$?[A-Z][A-Z_0-9]+)}~';
	private $blocks = array();

	private $block_names = array();
	private $block_else_level = array();

	private $chunk;

	function filter($in, $out, &$consumed, $closing)
	{
		$written = false;

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

			$bucket->data = $this->compile($data);
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

	public function onCreate()
	{
		$this->chunk = '';
		return true;
	}

	private function compile($data)
	{
		$data = preg_replace('#<(?:[\\?%]|script)#s', '<?php echo\'\\0\';?>', $data);
		return str_replace('?><?php', '', preg_replace_callback($this->regex, array($this, 'replace'), $data));
	}

	private function replace($matches)
	{
		global $config;

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

			case 'INCLUDE':
				return '<?php ' . $this->compile_tag_include($matches[2]) . ' ?>';
			break;

			case 'INCLUDEPHP':
				return ($config['tpl_allow_php']) ? '<?php ' . $this->compile_tag_include_php($matches[2]) . ' ?>' : '';
			break;

			case 'PHP':
				return ($config['tpl_allow_php']) ? '<?php ' : '<!-- ';
			break;

			case 'ENDPHP':
				return ($config['tpl_allow_php']) ? ' ?>' : ' -->';
			break;

			default:
				return $matches[0];
			break;

		}
		return '';
	}

	/**
	* Compile variables
	* @access private
	*/
	private function compile_var_tags(&$text_blocks)
	{
		// change template varrefs into PHP varrefs
		$varrefs = array();

		// This one will handle varrefs WITH namespaces
		preg_match_all('#\{((?:[a-z0-9\-_]+\.)+)(\$)?([A-Z0-9\-_]+)\}#', $text_blocks, $varrefs, PREG_SET_ORDER);

		foreach ($varrefs as $var_val)
		{
			$namespace = $var_val[1];
			$varname = $var_val[3];
			$new = $this->generate_block_varref($namespace, $varname, true, $var_val[2]);

			$text_blocks = str_replace($var_val[0], $new, $text_blocks);
		}

		// This will handle the remaining root-level varrefs
		// transform vars prefixed by L_ into their language variable pendant if nothing is set within the tpldata array
		if (strpos($text_blocks, '{L_') !== false)
		{
			$text_blocks = preg_replace('#\{L_([a-z0-9\-_]*)\}#is', "<?php echo ((isset(\$_rootref['L_\\1'])) ? \$_rootref['L_\\1'] : ((isset(\$_lang['\\1'])) ? \$_lang['\\1'] : '{ \\1 }')); ?>", $text_blocks);
		}

		// Handle addslashed language variables prefixed with LA_
		// If a template variable already exist, it will be used in favor of it...
		if (strpos($text_blocks, '{LA_') !== false)
		{
			$text_blocks = preg_replace('#\{LA_([a-z0-9\-_]*)\}#is', "<?php echo ((isset(\$_rootref['LA_\\1'])) ? \$_rootref['LA_\\1'] : ((isset(\$_rootref['L_\\1'])) ? addslashes(\$_rootref['L_\\1']) : ((isset(\$_lang['\\1'])) ? addslashes(\$_lang['\\1']) : '{ \\1 }'))); ?>", $text_blocks);
		}

		// Handle remaining varrefs
		$text_blocks = preg_replace('#\{([a-z0-9\-_]*)\}#is', "<?php echo (isset(\$_rootref['\\1'])) ? \$_rootref['\\1'] : ''; ?>", $text_blocks);
		$text_blocks = preg_replace('#\{\$([a-z0-9\-_]*)\}#is', "<?php echo (isset(\$_tpldata['DEFINE']['.']['\\1'])) ? \$_tpldata['DEFINE']['.']['\\1'] : ''; ?>", $text_blocks);

		return $text_blocks;
	}

	/**
	* Compile blocks
	* @access private
	*/
	private function compile_tag_block($tag_args)
	{
		$no_nesting = false;

		// Is the designer wanting to call another loop in a loop?
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

			if (strlen($match[3]) < 1 || $match[3] == -1)
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
		$tag_template_php .= '$_'. $tag_args . '_val = &' . $varref . '[$_'. $tag_args. '_i];';

		return $tag_template_php;
	}

	/**
	* Compile a general expression - much of this is from Smarty with
	* some adaptions for our block level methods
	* @access private
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
					if (preg_match('#^((?:[a-z0-9\-_]+\.)+)?(\$)?(?=[A-Z])([A-Z0-9\-_]+)#s', $token, $varrefs))
					{
						if (!empty($varrefs[1]))
						{
							$namespace = substr($varrefs[1], 0, -1);
							$namespace = (strpos($namespace, '.') === false) ? $namespace : strrchr($namespace, '.');

							// S_ROW_COUNT is deceptive, it returns the current row number now the number of rows
							// hence S_ROW_COUNT is deprecated in favour of S_ROW_NUM
							switch ($varrefs[3])
							{
								case 'S_ROW_NUM':
								case 'S_ROW_COUNT':
									$token = "\$_${namespace}_i";
								break;

								case 'S_FIRST_ROW':
									$token = "(\$_${namespace}_i == 0)";
								break;

								case 'S_LAST_ROW':
									$token = "(\$_${namespace}_i == \$_${namespace}_count - 1)";
								break;

								default:
									$token = $this->generate_block_data_ref(substr($varrefs[1], 0, -1), true, $varrefs[2]) . '[\'' . $varrefs[3] . '\']';
								break;
							}
						}
						else
						{
							$token = ($varrefs[2]) ? '$_tpldata[\'DEFINE\'][\'.\'][\'' . $varrefs[3] . '\']' : '$_rootref[\'' . $varrefs[3] . '\']';
						}
					}
					else if (preg_match('#^\.((?:[a-z0-9\-_]+\.?)+)$#s', $token, $varrefs))
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
						$token = "isset($varref) && sizeof($varref)";
					}

				break;
			}
		}

		return $tokens;
	}


	private function compile_tag_if($tag_args, $elseif)
	{
		$tokens = $this->compile_expression($tag_args);
		return (($elseif) ? '} else if (' : 'if (') . (implode(' ', $tokens) . ') { ');
	}

	/**
	* Compile DEFINE tags
	* @access private
	*/
	private function compile_tag_define($tag_args, $op)
	{
		$match = array();
		preg_match('#^((?:[a-z0-9\-_]+\.)+)?\$(?=[A-Z])([A-Z0-9_\-]*)(?: = (.*?))?$#', $tag_args, $match);

		if (empty($match[2]) || (!isset($match[3]) && $op))
		{
			return '';
		}

		if (!$op)
		{
			return 'unset(' . (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[2] . '\']' : '$_tpldata[\'DEFINE\'][\'.\'][\'' . $match[2] . '\']') . ');';
		}

		$parsed_statement = implode(' ', $this->compile_expression($match[3]));

		return (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[2] . '\']' : '$_tpldata[\'DEFINE\'][\'.\'][\'' . $match[2] . '\']') . ' = ' . $parsed_statement . ';';
	}

	/**
	* Compile INCLUDE tag
	* @access private
	*/
	private function compile_tag_include($tag_args)
	{
		return "\$this->_tpl_include('$tag_args');";
	}

	/**
	* Compile INCLUDE_PHP tag
	* @access private
	*/
	private function compile_tag_include_php($tag_args)
	{
		return "include('" . $tag_args . "');";
	}

	/**
	* parse expression
	* This is from Smarty
	* @access private
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
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "!(($is_arg / $expr_arg) % $expr_arg)";
				}
				else
				{
					$expr = "!($is_arg & 1)";
				}
			break;

			case 'odd':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "(($is_arg / $expr_arg) % $expr_arg)";
				}
				else
				{
					$expr = "($is_arg & 1)";
				}
			break;

			case 'div':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "!($is_arg % $expr_arg)";
				}
			break;
		}

		if ($negate_expr)
		{
			$expr = "!($expr)";
		}

		array_splice($tokens, 0, $expr_end, $expr);

		return $tokens;
	}

	/**
	* Generates a reference to the given variable inside the given (possibly nested)
	* block namespace. This is a string of the form:
	* ' . $_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['varname'] . '
	* It's ready to be inserted into an "echo" line in one of the templates.
	* NOTE: expects a trailing "." on the namespace.
	* @access private
	*/
	function generate_block_varref($namespace, $varname, $echo = true, $defop = false)
	{
		// Strip the trailing period.
		$namespace = substr($namespace, 0, -1);

		// S_ROW_COUNT is deceptive, it returns the current row number now the number of rows
		// hence S_ROW_COUNT is deprecated in favour of S_ROW_NUM
		switch ($varname)
		{
			case 'S_ROW_NUM':
			case 'S_ROW_COUNT':
				$varref = "\$_${namespace}_i";
			break;

			case 'S_FIRST_ROW':
				$varref = "(\$_${namespace}_i == 0)";
			break;

			case 'S_LAST_ROW':
				$varref = "(\$_${namespace}_i == \$_${namespace}_count - 1)";
			break;

			default:
				// Get a reference to the data block for this namespace.
				$varref = $this->generate_block_data_ref($namespace, true, $defop);
				// Prepend the necessary code to stick this in an echo line.

				// Append the variable reference.
				$varref .= "['$varname']";
			break;
		}
		$varref = ($echo) ? "<?php echo $varref; ?>" : ((isset($varref)) ? $varref : '');

		return $varref;
	}

	/**
	* Generates a reference to the array of data values for the given
	* (possibly nested) block namespace. This is a string of the form:
	* $_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['$childN']
	*
	* If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
	* NOTE: does not expect a trailing "." on the blockname.
	* @access private
	*/
	function generate_block_data_ref($blockname, $include_last_iterator, $defop = false)
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

stream_filter_register('template', 'template_filter');

/**
* Extension of template class - Functions needed for compiling templates only.
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
* @package phpBB3
*/
class template_compile
{

	private $template;

	/**
	* Constructor
	*/
	function __construct(template $template)
	{
		$this->template = $template;
	}

	/**
	* Load template source from file
	* @access public
	*/
	public function _tpl_load_file($handle/*, $store_in_db = false*/)
	{
		// Try and open template for read
		if (!file_exists($this->template->files[$handle]))
		{
			trigger_error("template->_tpl_load_file(): File {$this->template->files[$handle]} does not exist or is empty", E_USER_ERROR);
		}

		// Actually compile the code now.
		return $this->compile_write($handle, $this->template->files[$handle]);

	}

	public function _tpl_gen_src($handle)
	{
		// Try and open template for read
		if (!file_exists($this->template->files[$handle]))
		{
			trigger_error("template->_tpl_load_file(): File {$this->template->files[$handle]} does not exist or is empty", E_USER_ERROR);
		}

		// Actually compile the code now.
		return $this->compile_gen(/*$handle, */$this->template->files[$handle]);
	}

	/**
	* Write compiled file to cache directory
	* @access private
	*/
	private function compile_write($handle, $source_file)
	{
		$filename = $this->template->cachepath . str_replace('/', '.', $this->template->filename[$handle]) . '.' . PHP_EXT;

		$source_handle = @fopen($source_file, 'rb');
		$destination_handle = @fopen($filename, 'wb');

		if (!$source_handle || !$destination_handle)
		{
			return false;
		}

		@flock($destination_handle, LOCK_EX);

		stream_filter_append($source_handle, 'template');
		stream_copy_to_stream($source_handle, $destination_handle);

		@fclose($source_handle);
		@flock($destination_handle, LOCK_UN);
		@fclose($destination_handle);

		phpbb_chmod($filename, CHMOD_WRITE);

		return true;
	}

	/**
	* Generate source for eval()
	* @access private
	*/
	private function compile_gen(/*$handle, */$source_file)
	{
		$source_handle = @fopen($source_file, 'rb');
		$destination_handle = @fopen('php://temp' ,'r+b');

		if (!$source_handle || !$destination_handle)
		{
			return false;
		}

		stream_filter_append($source_handle, 'template');
		stream_copy_to_stream($source_handle, $destination_handle);

		@fclose($source_handle);

		rewind($destination_handle);
		return stream_get_contents($destination_handle);
	}
}

?>