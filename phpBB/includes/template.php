<?php
/***************************************************************************
 *                              template.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id$
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

/*
	Template class.

	Nathan Codding - Original version design and implementation
	Crimsonbane - Initial caching proposal and work
	psoTFX - Completion of file caching, decompilation routines and implementation of
	conditionals/keywords and associated changes

	The interface was inspired by PHPLib templates,  and the template file (formats are
	quite similar)

	The keyword/conditional implementation is currently based on sections of code from
	the Smarty templating engine (c) 2001 ispi of Lincoln, Inc. which is released
	(on its own and in whole) under the LGPL. Section 3 of the LGPL states that any code
	derived from an LGPL application may be relicenced under the GPL, this applies
	to this source
*/

class template
{

	// variable that holds all the data we'll be substituting into
	// the compiled templates. Takes form:
	// --> $this->_tpldata[block.][iteration#][child.][iteration#][child2.][iteration#][variablename] == value
	// if it's a root-level variable, it'll be like this:
	// --> $this->_tpldata[.][0][varname] == value
	var $_tpldata = array();

	// Root dir and hash of filenames for each template handle.
	var $tpl = '';
	var $root = '';
	var $cache_root = 'cache/templates/';
	var $files = array();

	// this will hash handle names to the compiled/uncompiled code for that handle.
	var $compiled_code = array();

	// Various counters and storage arrays
	var $block_names = array();
	var $block_else_level = array();
	var $block_nesting_level = 0;

	var $static_lang;
	var $force_recompile;

	function set_template($static_lang = false, $force_recompile = false)
	{
		global $phpbb_root_path, $config, $user;

		if (file_exists($phpbb_root_path . 'styles/templates/' . $user->theme['primary']['template_path']))
		{
//			$this->tpl = 'primary';
			$this->root = $phpbb_root_path . 'styles/templates/' . $user->theme['primary']['template_path'];
			$this->cachedir = $phpbb_root_path . $this->cache_root . $user->theme['primary']['template_path'] . '/';
		}
		else
		{
//			$this->tpl = 'secondary';
			$this->root = $phpbb_root_path . 'styles/templates/' . $user->theme['secondary']['template_path'];
			$this->cachedir = $phpbb_root_path . $this->cache_root . $user->theme['secondary']['template_path'] . '/';
		}

		$this->static_lang = $static_lang;
		$this->force_recompile = $force_recompile;

		if (!file_exists($this->cachedir))
		{
			@umask(0);
			mkdir($this->cachedir, 0777);
		}

		return true;
	}

	// Sets the template filenames for handles. $filename_array
	// should be a hash of handle => filename pairs.
	function set_filenames($filename_array)
	{
		if (!is_array($filename_array))
		{
			return false;
		}

		$template_names = '';
		foreach ($filename_array as $handle => $filename)
		{
			if (empty($filename))
			{
				trigger_error("template error - Empty filename specified for $handle", E_USER_ERROR);
			}

			$this->filename[$handle] = $filename;
			$this->files[$handle] = $this->root . '/' . $filename;
		}

		return true;
	}


	// Destroy template data set
	function destroy()
	{
		$this->_tpldata = array();
	}


	// Methods for loading and evaluating the templates
	function display($handle)
	{
		global $user;

		if ($filename = $this->_tpl_load($handle))
		{
			include($filename);
		}
		else
		{
			eval(' ?>' . $this->compiled_code[$handle] . '<?php ');
		}

		return true;
	}


	// Load a compiled template if possible, if not, recompile it
	function _tpl_load(&$handle)
	{
		global $phpEx, $user;

		$filename = $this->cachedir . $this->filename[$handle] . '.' . (($this->static_lang) ? $user->data['user_lang'] . '.' : '') . $phpEx;

		// Recompile page if the original template is newer, otherwise load the compiled version
		if (file_exists($filename) && !$this->force_recompile)
		{
			return $filename;
		}


		// If the file for this handle is already loaded and compiled, do nothing.
		if (!empty($this->uncompiled_code[$handle]))
		{
			return true;
		}

		// If we don't have a file assigned to this handle, die.
		if (!isset($this->files[$handle]))
		{
			trigger_error("template->_tpl_load(): No file specified for handle $handle", E_USER_ERROR);
		}

		if (!file_exists($this->files[$handle]))
		{
//			$this->tpl = 'secondary';
			$this->files[$handle] = $phpbb_root_path . 'styles/templates/' . $user->theme['secondary']['template_path'] . '/' . $this->filename[$handle];
		}

		$str = '';
		// Try and open template for read
		if (!($fp = @fopen($this->files[$handle], 'r')))
		{
			trigger_error("template->_tpl_load(): File $filename does not exist or is empty", E_USER_ERROR);
		}

		$str = fread($fp, filesize($this->files[$handle]));
		@fclose($fp);

		// Actually compile the code now.
		$this->compiled_code[$handle] = $this->compile(trim($str));
		$this->compile_write($handle, $this->compiled_code[$handle]);

		return false;
	}


	// Assign key variable pairs from an array
	function assign_vars($vararray)
	{
		foreach ($vararray as $key => $val)
		{
			$this->_tpldata['.'][0][$key] = $val;
		}

		return true;
	}

	// Assign a single variable to a single key
	function assign_var($varname, $varval)
	{
		$this->_tpldata['.'][0][$varname] = $varval;

		return true;
	}

	// Assign key variable pairs from an array to a specified block
	function assign_block_vars($blockname, $vararray)
	{
		if (strstr($blockname, '.'))
		{
			// Nested block.
			$blocks = explode('.', $blockname);
			$blockcount = sizeof($blocks) - 1;

			$str = &$this->_tpldata; 
			for ($i = 0; $i < $blockcount; $i++) 
			{
				$str = &$str[$blocks[$i]]; 
				$str = &$str[sizeof($str) - 1]; 
			} 

			// Now we add the block that we're actually assigning to.
			// We're adding a new iteration to this block with the given
			// variable assignments.
			$str[$blocks[$blockcount]][] = $vararray;
		}
		else
		{
			// Top-level block.
			// Add a new iteration to this block with the variable assignments
			// we were given.
			$this->_tpldata[$blockname][] = $vararray;
		}

		return true;
	}
	

	// Include a seperate template
	function _tpl_include($filename, $include = true)
	{
		global $user;

		$handle = $filename;
		$this->filename[$handle] = $filename;
		$this->files[$handle] = $this->root . '/' . $filename;

 		$filename = $this->_tpl_load($handle);
		
		if ($include)
		{
			if (!$this->force_recompile && $filename)
			{
				include($filename);
			}
			else
			{
				eval(' ?>' . $this->compiled_code[$handle] . '<?php ');
			}
		}
	}



	// This next set of methods could be seperated off and included since
	// they deal exclusively with compilation ... which is done infrequently
	// and would save a fair few kb


	// The all seeing all doing compile method. Parts are inspired by or directly
	// from Smarty
	function compile($code, $no_echo = false, $echo_var = '')
	{
		global $config;

		// Remove any "loose" php ... we want to give admins the ability
		// to switch on/off PHP for a given template. Allowing unchecked
		// php is a no-no. There is a potential issue here in that non-php
		// content may be removed ... however designers should use entities 
		// if they wish to display < and >
		$match_php_tags = array('#\<\?php .*?\?\>#is', '#\<\script language="php"\>.*?\<\/script\>#is', '#\<\?.*?\?\>#s', '#\<%.*?%\>#s');
		$code = preg_replace($match_php_tags, '', $code);

		// Pull out all block/statement level elements and seperate
		// plain text
		preg_match_all('#<!-- PHP -->(.*?)<!-- ENDPHP -->#s', $code, $matches);
		$php_blocks = $matches[1];
		$code = preg_replace('#<!-- PHP -->(.*?)<!-- ENDPHP -->#s', '<!-- PHP -->', $code);

		preg_match_all('#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#', $code, $matches);
		$include_blocks = $matches[1];
		$code = preg_replace('#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#', '<!-- INCLUDE -->', $code);

		preg_match_all('#<!-- INCLUDEPHP ([a-zA-Z0-9\_\-\+\.\\\\]+?) -->#', $code, $matches);
		$includephp_blocks = $matches[1];
		$code = preg_replace('#<!-- INCLUDEPHP ([a-zA-Z0-9\_\-\+\.]+?) -->#', '<!-- INCLUDEPHP -->', $code);

		preg_match_all('#<!-- (.*?) (.*?)?[ ]?-->#s', $code, $blocks);
		$text_blocks = preg_split('#<!-- (.*?) (.*?)?[ ]?-->#s', $code);
		for($i = 0; $i < count($text_blocks); $i++)
		{
			$this->compile_var_tags($text_blocks[$i]);
		}

		$compile_blocks = array();

		for ($curr_tb = 0; $curr_tb < count($text_blocks); $curr_tb++)
		{
			switch ($blocks[1][$curr_tb])
			{
				case 'BEGIN':
					$this->block_else_level[] = false;
					$compile_blocks[] = '<?php ' . $this->compile_tag_block($blocks[2][$curr_tb]) . ' ?>';
					break;

				case 'BEGINELSE':
					$this->block_else_level[sizeof($this->block_else_level) - 1] = true;
					$compile_blocks[] = '<?php }} else { ?>';
					break;

				case 'END':
					array_pop($this->block_names);
					$compile_blocks[] = '<?php ' . ((array_pop($this->block_else_level)) ? '}' : '}}') . ' ?>';
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

				case 'INCLUDE':
					$temp = '';
					list(, $temp) = each($include_blocks);
					$compile_blocks[] = '<?php ' . $this->compile_tag_include($temp) . ' ?>';
					$this->_tpl_include($temp, false);
					break;

				case 'INCLUDEPHP':
					if ($config['tpl_php'])
					{
						$temp = '';
						list(, $temp) = each($includephp_blocks);
						$compile_blocks[] = '<?php ' . $this->compile_tag_include_php($temp) . ' ?>';
					}
					break;

				case 'PHP':
					if ($config['tpl_php'])
					{
						$temp = '';
						list(, $temp) = each($php_blocks);
						$compile_blocks[] = '<?php ' . $temp . ' ?>';
					}
					break;

				default:
					$this->compile_var_tags($blocks[0][$curr_tb]);
					$trim_check = trim($blocks[0][$curr_tb]);
					$compile_blocks[] = (!$do_not_echo) ? ((!empty($trim_check)) ? $blocks[0][$curr_tb] : '') : ((!empty($trim_check)) ? $blocks[0][$curr_tb] : '');
					break;
			}
		}

		$template_php = '';
		for ($i = 0; $i < count($text_blocks); $i++)
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

	function compile_var_tags(&$text_blocks)
	{
		// change template varrefs into PHP varrefs
		$varrefs = array();

		// This one will handle varrefs WITH namespaces
		preg_match_all('#\{(([a-z0-9\-_]+?\.)+?)([a-z0-9\-_]+?)\}#is', $text_blocks, $varrefs);

		for ($j = 0; $j < sizeof($varrefs[1]); $j++)
		{
			$namespace = $varrefs[1][$j];
			$varname = $varrefs[3][$j];
			$new = $this->generate_block_varref($namespace, $varname);

			$text_blocks = str_replace($varrefs[0][$j], $new, $text_blocks);
		}

		// This will handle the remaining root-level varrefs
		if (!$this->static_lang)
		{
			$text_blocks = preg_replace('#\{L_([a-z0-9\-_]*?)\}#is', "<?php echo ((isset(\$this->_tpldata['.'][0]['L_\\1'])) ? \$this->_tpldata['.'][0]['L_\\1'] : ((isset(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '{ ' . ucfirst(strtolower(str_replace('_', ' ', '\\1'))) . ' 	}')); ?>", $text_blocks);
		}
		else
		{
			global $user;

			$text_blocks = preg_replace('#\{L_([A-Z0-9\-_]*?)\}#e', "'<?php echo ((isset(\$this->_tpldata[\'.\'][0][\'L_\\1\'])) ? \$this->_tpldata[\'.\'][0][\'L_\\1\'] : \'' . ((isset(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '') . '\'); ?>'" , $text_blocks);
		}
		$text_blocks = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "<?php echo \$this->_tpldata['.'][0]['\\1']; ?>", $text_blocks);

		return;
	}

	function compile_tag_block($tag_args)
	{
		$tag_template_php = '';
		array_push($this->block_names, $tag_args);

		if (sizeof($this->block_names) < 2)
		{
			// Block is not nested.
			$tag_template_php = '$_' . $tag_args . "_count = (isset(\$this->_tpldata['$tag_args'])) ?  sizeof(\$this->_tpldata['$tag_args']) : 0;";
		}
		else
		{
			// This block is nested.

			// Generate a namespace string for this block.
			$namespace = implode('.', $this->block_names);

			// Get a reference to the data array for this block that depends on the
			// current indices of all parent blocks.
			$varref = $this->generate_block_data_ref($namespace, false);

			// Create the for loop code to iterate over this block.
			$tag_template_php = '$_' . $tag_args . '_count = (isset(' . $varref . ')) ? sizeof(' . $varref . ') : 0;';
		}

		$tag_template_php .= 'if ($_' . $tag_args . '_count) {';
		$tag_template_php .= 'for ($this->_' . $tag_args . '_i = 0; $this->_' . $tag_args . '_i < $_' . $tag_args . '_count; $this->_' . $tag_args . '_i++){';

		return $tag_template_php;
	}

	//
	// Compile IF tags - much of this is from Smarty with
	// some adaptions for our block level methods
	//
	function compile_tag_if($tag_args, $elseif)
	{
        /* Tokenize args for 'if' tag. */
        preg_match_all('/(?:
                         "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
                         \'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
                         [(),]                                  |
                         [^\s(),]+)/x', $tag_args, $match);

        $tokens = $match[0];
        $is_arg_stack = array();

        for ($i = 0; $i < count($tokens); $i++)
		{
			$token = &$tokens[$i];

			switch (strtolower($token))
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

					array_splice($tokens, $is_arg_start, count($tokens), $new_tokens);

					$i = $is_arg_start;

				default:
					if (preg_match('#^(([a-z0-9\-_]+?\.)+?)?([A-Z]+[A-Z0-9\-_]+?)$#s', $token, $varrefs))
					{
						$token = (!empty($varrefs[1])) ? $this->generate_block_data_ref(substr($varrefs[1], 0, strlen($varrefs[1]) - 1), true) . '[\'' . $varrefs[3] . '\']' : '$this->_tpldata[\'.\'][0][\'' . $varrefs[3] . '\']';
					}
					break;
            }
        }

		return (($elseif) ? '} elseif (' : 'if (') . (implode(' ', $tokens) . ') { ');
	}

	function compile_tag_include($tag_args)
	{
        return "\$this->_tpl_include('$tag_args');";
	}

	function compile_tag_include_php($tag_args)
	{
        return "include('" . $this->root . '/' . $tag_args . "');";
	}

	// This is from Smarty
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
	 */
	function generate_block_varref($namespace, $varname)
	{
		// Strip the trailing period.
		$namespace = substr($namespace, 0, strlen($namespace) - 1);

		// Get a reference to the data block for this namespace.
		$varref = $this->generate_block_data_ref($namespace, true);
		// Prepend the necessary code to stick this in an echo line.

		// Append the variable reference.
		$varref .= "['$varname']";
		$varref = "<?php echo $varref; ?>";

		return $varref;

	}

	/**
	 * Generates a reference to the array of data values for the given
	 * (possibly nested) block namespace. This is a string of the form:
	 * $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['$childN']
	 *
	 * If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
	 * NOTE: does not expect a trailing "." on the blockname.
	 */
	function generate_block_data_ref($blockname, $include_last_iterator)
	{
		// Get an array of the blocks involved.
		$blocks = explode('.', $blockname);
		$blockcount = sizeof($blocks) - 1;
		$varref = '$this->_tpldata';

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

	function compile_write(&$handle, $data)
	{
		global $phpEx, $user;

		$filename = $this->cachedir . $this->filename[$handle] . '.' . (($this->static_lang) ? $user->data['user_lang'] . '.' : '') . $phpEx;

		if ($fp = @fopen($filename, 'w+'))
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