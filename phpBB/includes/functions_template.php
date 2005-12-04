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
* @package phpBB3
*
* Extension of template class - Functions needed for compiling templates only.
*
* psoTFX - Completion of file caching, decompilation routines and implementation of
* conditionals/keywords and associated changes
*
* The interface was inspired by PHPLib templates,  and the template file (formats are
* quite similar)
*
* The keyword/conditional implementation is currently based on sections of code from
* the Smarty templating engine (c) 2001 ispi of Lincoln, Inc. which is released
* (on its own and in whole) under the LGPL. Section 3 of the LGPL states that any code
* derived from an LGPL application may be relicenced under the GPL, this applies
* to this source
* 
* DEFINE directive inspired by a request by Cyberalien
*/
class template_compile
{
	/**
	* constuctor
	*/
	function template_compile($template)
	{
		$this->template = &$template;
	}
	
	/**
	* Load template source from file
	* @private
	*/
	function _tpl_load_file($handle)
	{
		// Try and open template for read
		if (!($fp = @fopen($this->template->files[$handle], 'r')))
		{
			trigger_error("template->_tpl_load_file(): File {$this->template->files[$handle]} does not exist or is empty", E_USER_ERROR);
		}

		$this->template->compiled_code[$handle] = $this->compile(trim(@fread($fp, filesize($this->template->files[$handle]))));
		@fclose($fp);

		// Actually compile the code now.
		$this->compile_write($handle, $this->template->compiled_code[$handle]);
	}

	/**
	* The all seeing all doing compile method. Parts are inspired by or directly
	* from Smarty
	* @private
	*/
	function compile($code, $no_echo = false, $echo_var = '')
	{
		global $config;

		if ($echo_var)
		{
			global $$echo_var;
		}

		// Remove any "loose" php ... we want to give admins the ability
		// to switch on/off PHP for a given template. Allowing unchecked
		// php is a no-no. There is a potential issue here in that non-php
		// content may be removed ... however designers should use entities
		// if they wish to display < and >
		$match_php_tags = array('#\<\?php .*?\?\>#is', '#\<\script language="php"\>.*?\<\/script\>#is', '#\<\?.*?\?\>#s', '#\<%.*?%\>#s');
		$code = preg_replace($match_php_tags, '', $code);

		// Pull out all block/statement level elements and seperate plain text
		preg_match_all('#<!-- PHP -->(.*?)<!-- ENDPHP -->#s', $code, $matches);
		$php_blocks = $matches[1];
		$code = preg_replace('#<!-- PHP -->(.*?)<!-- ENDPHP -->#s', '<!-- PHP -->', $code);

		preg_match_all('#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#', $code, $matches);
		$include_blocks = $matches[1];
		$code = preg_replace('#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#', '<!-- INCLUDE -->', $code);

		preg_match_all('#<!-- INCLUDEPHP ([a-zA-Z0-9\_\-\+\.\\\\]+?) -->#', $code, $matches);
		$includephp_blocks = $matches[1];
		$code = preg_replace('#<!-- INCLUDEPHP ([a-zA-Z0-9\_\-\+\.]+?) -->#', '<!-- INCLUDEPHP -->', $code);

		preg_match_all('#<!-- (.*?) (.*?)?[ ]?-->#', $code, $blocks);
		$text_blocks = preg_split('#<!-- (.*?) (.*?)?[ ]?-->#', $code);
		
		for ($i = 0, $j = sizeof($text_blocks); $i < $j; $i++)
		{
			$this->compile_var_tags($text_blocks[$i]);
		}
		$compile_blocks = array();

		for ($curr_tb = 0, $tb_size = sizeof($text_blocks); $curr_tb < $tb_size; $curr_tb++)
		{
			if (!isset($blocks[1][$curr_tb]))
			{
				$blocks[1][$curr_tb] = '';
			}

			switch ($blocks[1][$curr_tb])
			{
				case 'BEGIN':
					$this->template->block_else_level[] = false;
					$compile_blocks[] = '<?php ' . $this->compile_tag_block($blocks[2][$curr_tb]) . ' ?>';
					break;

				case 'BEGINELSE':
					$this->template->block_else_level[sizeof($this->template->block_else_level) - 1] = true;
					$compile_blocks[] = '<?php }} else { ?>';
					break;

				case 'END':
					array_pop($this->template->block_names);
					$compile_blocks[] = '<?php ' . ((array_pop($this->template->block_else_level)) ? '}' : '}}') . ' ?>';
					break;

				case 'IF':
					$compile_blocks[] = '<?php ' . $this->compile_tag_if($blocks[2][$curr_tb], false) . ' ?>';
					break;

				case 'ELSE':
					$compile_blocks[] = '<?php } else { ?>';
					break;

				case 'ELSEIF':
					$compile_blocks[] = '<?php ' . $this->compile_tag_if($blocks[2][$curr_tb], true) . ' ?>';
					break;

				case 'ENDIF':
					$compile_blocks[] = '<?php } ?>';
					break;

				case 'DEFINE':
					$compile_blocks[] = '<?php ' . $this->compile_tag_define($blocks[2][$curr_tb], true) . ' ?>';
					break;

				case 'UNDEFINE':
					$compile_blocks[] = '<?php ' . $this->compile_tag_define($blocks[2][$curr_tb], false) . ' ?>';
					break;

				case 'INCLUDE':
					$temp = '';
					list(, $temp) = each($include_blocks);
					$compile_blocks[] = '<?php ' . $this->compile_tag_include($temp) . ' ?>';
					$this->template->_tpl_include($temp, false);
					break;

				case 'INCLUDEPHP':
					if ($config['tpl_php'])
					{
						$temp = '';
						list(, $temp) = each($includephp_blocks);
						$compile_blocks[] = '<?php ' . $this->compile_tag_include_php($temp) . ' ?>';
					}
					else
					{
						$compile_blocks[] = '';
					}
					break;

				case 'PHP':
					if ($config['tpl_php'])
					{
						$temp = '';
						list(, $temp) = each($php_blocks);
						$compile_blocks[] = '<?php ' . $temp . ' ?>';
					}
					else
					{
						$compile_blocks[] = '';
					}
					break;

				default:
					$this->compile_var_tags($blocks[0][$curr_tb]);
					$trim_check = trim($blocks[0][$curr_tb]);
					$compile_blocks[] = (!$no_echo) ? ((!empty($trim_check)) ? $blocks[0][$curr_tb] : '') : ((!empty($trim_check)) ? $blocks[0][$curr_tb] : '');
					break;
			}
		}

		$template_php = '';
		for ($i = 0, $size = sizeof($text_blocks); $i < $size; $i++)
		{
			$trim_check_text = trim($text_blocks[$i]);
			$trim_check_block = trim($compile_blocks[$i]);
			$template_php .= (!$no_echo) ? ((!empty($trim_check_text)) ? $text_blocks[$i] : '') . ((!empty($compile_blocks[$i])) ? $compile_blocks[$i] : '') : ((!empty($trim_check_text)) ? $text_blocks[$i] : '') . ((!empty($compile_blocks[$i])) ? $compile_blocks[$i] : '');
		}

		// There will be a number of occassions where we switch into and out of
		// PHP mode instantaneously. Rather than "burden" the parser with this
		// we'll strip out such occurences, minimising such switching
		$template_php = str_replace(' ?><?php ', '', $template_php);

		return  (!$no_echo) ? $template_php : "\$$echo_var .= '" . $template_php . "'";
	}

	/**
	* Compile variables
	* @private
	*/
	function compile_var_tags(&$text_blocks)
	{
		// change template varrefs into PHP varrefs
		$varrefs = array();

		// This one will handle varrefs WITH namespaces
		preg_match_all('#\{(([a-z0-9\-_]+?\.)+?)(\$)?([A-Z0-9\-_]+?)\}#', $text_blocks, $varrefs);

		for ($j = 0, $size = sizeof($varrefs[1]); $j < $size; $j++)
		{
			$namespace = $varrefs[1][$j];
			$varname = $varrefs[4][$j];
			$new = $this->generate_block_varref($namespace, $varname, true, $varrefs[3][$j]);

			$text_blocks = str_replace($varrefs[0][$j], $new, $text_blocks);
		}

		// This will handle the remaining root-level varrefs
		if (!$this->template->static_lang)
		{
			$text_blocks = preg_replace('#\{L_([a-z0-9\-_]*?)\}#is', "<?php echo ((isset(\$this->_tpldata['.'][0]['L_\\1'])) ? \$this->_tpldata['.'][0]['L_\\1'] : ((isset(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '{ ' . ucfirst(strtolower(str_replace('_', ' ', '\\1'))) . ' 	}')); ?>", $text_blocks);
		}
		else
		{
			global $user;

			$text_blocks = preg_replace('#\{L_([A-Z0-9\-_]*?)\}#e', "'<?php echo ((isset(\$this->_tpldata[\'.\'][0][\'L_\\1\'])) ? \$this->_tpldata[\'.\'][0][\'L_\\1\'] : \'' . ((isset(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '') . '\'); ?>'" , $text_blocks);
		}
		
		$text_blocks = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "<?php echo (isset(\$this->_tpldata['.'][0]['\\1'])) ? \$this->_tpldata['.'][0]['\\1'] : ''; ?>", $text_blocks);
		$text_blocks = preg_replace('#\{\$([a-z0-9\-_]*?)\}#is', "<?php echo (isset(\$this->_tpldata['DEFINE']['.']['\\1'])) ? \$this->_tpldata['DEFINE']['.']['\\1'] : ''; ?>", $text_blocks);

		return;
	}

	/**
	* Compile blocks
	* @private
	*/
	function compile_tag_block($tag_args)
	{
		// Allow for control of looping (indexes start from zero):
		// foo(2)    : Will start the loop on the 3rd entry
		// foo(-2)   : Will start the loop two entries from the end
		// foo(3,4)  : Will start the loop on the fourth entry and end it on the fifth
		// foo(3,-4) : Will start the loop on the fourth entry and end it four from last
		if (preg_match('#^(.*?)\(([\-0-9]+)(,([\-0-9]+))?\)$#', $tag_args, $match))
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

			if (strlen($match[4]) < 1 || $match[4] == -1)
			{
				$loop_end = '$_' . $tag_args . '_count';
			}
			else if ($match[4] >= 0)
			{
				$loop_end = '(' . ($match[4] + 1) . ' > $_' . $tag_args . '_count ? $_' . $tag_args . '_count : ' . ($match[4] + 1) . ')';
			}
			else //if ($match[4] < -1)
			{
				$loop_end = '$_' . $tag_args . '_count' . ($match[4] + 1);
			}
		}
		else
		{
			$loop_start = 0;
			$loop_end = '$_' . $tag_args . '_count';
		}

		$tag_template_php = '';
		array_push($this->template->block_names, $tag_args);

		if (sizeof($this->template->block_names) < 2)
		{
			// Block is not nested.
			$tag_template_php = '$_' . $tag_args . "_count = (isset(\$this->_tpldata['$tag_args'])) ?  sizeof(\$this->_tpldata['$tag_args']) : 0;";
		}
		else
		{
			// This block is nested.

			// Generate a namespace string for this block.
			$namespace = implode('.', $this->template->block_names);

			// Get a reference to the data array for this block that depends on the
			// current indices of all parent blocks.
			$varref = $this->generate_block_data_ref($namespace, false);

			// Create the for loop code to iterate over this block.
			$tag_template_php = '$_' . $tag_args . '_count = (isset(' . $varref . ')) ? sizeof(' . $varref . ') : 0;';
		}

		$tag_template_php .= 'if ($_' . $tag_args . '_count) {';
		$tag_template_php .= 'for ($this->_' . $tag_args . '_i = ' . $loop_start . '; $this->_' . $tag_args . '_i < ' . $loop_end . '; $this->_' . $tag_args . '_i++){';

		return $tag_template_php;
	}

	/**
	* Compile IF tags - much of this is from Smarty with
	* some adaptions for our block level methods
	* @private
	*/
	function compile_tag_if($tag_args, $elseif)
	{
		// Tokenize args for 'if' tag.
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
				case '!':
				case '%':
				case '!==':
				case '==':
				case '===':
				case '>':
				case '<':
				case '!=':
				case '<>':
				case '<<':
				case '>>':
				case '<=':
				case '>=':
				case '&&':
				case '||':
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

				case 'eq':
					$token = '==';
					break;

				case 'ne':
				case 'neq':
					$token = '!=';
					break;

				case 'lt':
					$token = '<';
					break;

				case 'le':
				case 'lte':
					$token = '<=';
					break;

				case 'gt':
					$token = '>';
					break;

				case 'ge':
				case 'gte':
					$token = '>=';
					break;

				case 'and':
					$token = '&&';
					break;

				case 'or':
					$token = '||';
					break;

				case 'not':
					$token = '!';
					break;

				case 'mod':
					$token = '%';
					break;

				case '(':
					array_push($is_arg_stack, $i);
					break;

				case 'is':
					$is_arg_start = ($tokens[$i-1] == ')') ? array_pop($is_arg_stack) : $i-1;
					$is_arg	= implode('	', array_slice($tokens,	$is_arg_start, $i -	$is_arg_start));

					$new_tokens	= $this->_parse_is_expr($is_arg, array_slice($tokens, $i+1));

					array_splice($tokens, $is_arg_start, sizeof($tokens), $new_tokens);

					$i = $is_arg_start;

				default:
					if (preg_match('#^(([a-z0-9\-_]+?\.)+?)?(\$)?([A-Z]+[A-Z0-9\-_]+)$#s', $token, $varrefs))
					{
						$token = (!empty($varrefs[1])) ? $this->generate_block_data_ref(substr($varrefs[1], 0, -1), true, $varrefs[3]) . '[\'' . $varrefs[4] . '\']' : (($varrefs[3]) ? '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $varrefs[4] . '\']' : '$this->_tpldata[\'.\'][0][\'' . $varrefs[4] . '\']');
					}
					else if (preg_match('#^\.((([a-z0-9\-_]+)?\.?)+?)$#s', $token, $varrefs))
					{
						$_tok = $this->generate_block_data_ref($varrefs[1], false);
						$token = "(isset($_tok) && sizeof($_tok))";
					}

					break;
			}
		}

		return (($elseif) ? '} else if (' : 'if (') . (implode(' ', $tokens) . ') { ');
	}

	/**
	* Compile DEFINE tags
	* @private
	*/
	function compile_tag_define($tag_args, $op)
	{
		preg_match('#^(([a-z0-9\-_]+?\.)+?)?\$([A-Z][A-Z0-9_\-]*?)( = (\'?)(.*?)(\'?))?$#', $tag_args, $match);

		if (empty($match[3]) || (empty($match[6]) && $op))
		{
			return;
		}

		if (!$op)
		{
			return 'unset(' . (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[3] . '\']' : '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $match[3] . '\']') . ');';
		}

		// Are we a string?
		if ($match[5] && $match[7])
		{
			$match[6] = addslashes(str_replace(array('\\\'', '\\\\'), array('\'', '\\'), $match[6]));

			// Compile reference, we allow template variables in defines...
			$match[6] = $this->compile($match[6]);

			// Now replace the php code
			$match[6] = "'" . str_replace(array('<?php echo ', '; ?>'), array("' . ", " . '"), $match[6]) . "'";
		}
		else
		{
			preg_match('#(true|false|\.)#i', $match[6], $type);

			switch (strtolower($type[1]))
			{
				case 'true':
				case 'false':
					$match[6] = strtoupper($match[6]);
					break;
				case '.';
					$match[6] = doubleval($match[6]);
					break;
				default:
					$match[6] = intval($match[6]);
					break;
			}
		}

		return (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[3] . '\']' : '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $match[3] . '\']') . ' = ' . $match[6] . ';';
	}

	/**
	* Compile INCLUDE tag
	* @private
	*/
	function compile_tag_include($tag_args)
	{
		return "\$this->_tpl_include('$tag_args');";
	}

	/**
	* Compile INCLUDE_PHP tag
	* @private
	*/
	function compile_tag_include_php($tag_args)
	{
		return "include('" . $this->template->root . '/' . $tag_args . "');";
	}

	/**
	* parse expression
	* This is from Smarty
	* @private
	*/
	function _parse_is_expr($is_arg, $tokens)
	{
		$expr_end =	0;
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
					$expr_arg =	$tokens[$expr_end++];
					$expr =	"!(($is_arg	/ $expr_arg) % $expr_arg)";
				}
				else
				{
					$expr =	"!($is_arg % 2)";
				}
				break;

			case 'odd':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg =	$tokens[$expr_end++];
					$expr =	"(($is_arg / $expr_arg)	% $expr_arg)";
				}
				else
				{
					$expr =	"($is_arg %	2)";
				}
				break;

			case 'div':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg =	$tokens[$expr_end++];
					$expr =	"!($is_arg % $expr_arg)";
				}
				break;

			default:
				break;
		}

		if ($negate_expr)
		{
			$expr =	"!($expr)";
		}

		array_splice($tokens, 0, $expr_end,	$expr);

		return $tokens;
	}

	/**
	* Generates a reference to the given variable inside the given (possibly nested)
	* block namespace. This is a string of the form:
	* ' . $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['varname'] . '
	* It's ready to be inserted into an "echo" line in one of the templates.
	* NOTE: expects a trailing "." on the namespace.
	* @private
	*/
	function generate_block_varref($namespace, $varname, $echo = true, $defop = false)
	{
		// Strip the trailing period.
		$namespace = substr($namespace, 0, -1);

		// Get a reference to the data block for this namespace.
		$varref = $this->generate_block_data_ref($namespace, true, $defop);
		// Prepend the necessary code to stick this in an echo line.

		// Append the variable reference.
		$varref .= "['$varname']";
		$varref = ($echo) ? "<?php echo $varref; ?>" : ((isset($varref)) ? $varref : '');

		return $varref;
	}

	/**
	* Generates a reference to the array of data values for the given
	* (possibly nested) block namespace. This is a string of the form:
	* $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['$childN']
	*
	* If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
	* NOTE: does not expect a trailing "." on the blockname.
	* @private
	*/
	function generate_block_data_ref($blockname, $include_last_iterator, $defop = false)
	{
		// Get an array of the blocks involved.
		$blocks = explode('.', $blockname);
		$blockcount = sizeof($blocks) - 1;
		$varref = '$this->_tpldata' . (($defop) ? '[\'DEFINE\']' : '');

		// Build up the string with everything but the last child.
		for ($i = 0; $i < $blockcount; $i++)
		{
			$varref .= "['" . $blocks[$i] . "'][\$this->_" . $blocks[$i] . '_i]';
		}

		// Add the block reference for the last child.
		$varref .= "['" . $blocks[$blockcount] . "']";

		// Add the iterator for the last child if requried.
		if ($include_last_iterator)
		{
			$varref .= '[$this->_' . $blocks[$blockcount] . '_i]';
		}

		return $varref;
	}

	/**
	* Write compiled file to cache directory
	* @private
	*/
	function compile_write(&$handle, $data)
	{
		global $phpEx, $user;

		$filename = $this->template->cachepath . $this->template->filename[$handle] . '.' . (($this->template->static_lang) ? $user->data['user_lang'] . '.' : '') . $phpEx;

		if ($fp = @fopen($filename, 'wb'))
		{
			@flock($fp, LOCK_EX);
			@fwrite ($fp, $data);
			@flock($fp, LOCK_UN);
			@fclose($fp);

			@umask(0);
			@chmod($filename, 0644);
		}

		return;
	}
}

?>