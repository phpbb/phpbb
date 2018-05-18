<?php
/** ------------------------------------------------------------------------
 *		Subject				: mxBB - a fully modular portal and CMS (for phpBB)
 *		Author				: Jon Ohlsson and the mxBB Team
 *		Credits				: The phpBB Group & Marc Morisette, Mohd Basri & paFileDB 3.0 ©2001/2002 PHP Arena
 *		Copyright          	: (C) 2002-2005 mxBB Portal
 *		Email             	: jon@mxbb.net
 *		Project site		: www.mxbb.net
 * -------------------------------------------------------------------------
 *
 *    $Id: template_pafiledb.php,v 1.2 2010/10/11 23:10:47 orynider Exp $
 */

/**
 * This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 */

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

if ( !defined( 'IN_PORTAL' ) )
{
	die( "Hacking attempt" );
}

class Template
{
	// variable that holds all the data we'll be substituting into
	// the compiled templates. Takes form:
	// --> $this->_tpldata[block.][iteration#][child.][iteration#][child2.][iteration#][variablename] == value
	// if it's a root-level variable, it'll be like this:
	// --> $this->_tpldata[.][0][varname] == value
	var $_tpldata = array();
	// Root dir and hash of filenames for each template handle.
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

	function set_template( $template = '', $static_lang = false, $force_recompile = false )
	{
		/* - orig
		global $phpbb_root_path;

		$this->root = $phpbb_root_path . 'templates/' . $template;
        $this->cachedir = $phpbb_root_path . $this->cache_root . $template . '/';
*/
		// MX
		global $module_root_path;
		// Added for mx
		if ( file_exists( $module_root_path . 'templates/' . $template ) )
		{
			$this->root = $module_root_path . 'templates/' . $template;
			$this->cachedir = $module_root_path . $this->cache_root . $template . '/';
		}
		else
		{
			$this->root = $module_root_path . 'templates/subSilver';
			$this->cachedir = $module_root_path . $this->cache_root . $template . '/';
		}

		$this->static_lang = $static_lang;
		$this->force_recompile = $force_recompile;

		if ( !file_exists( $this->cachedir ) )
		{
			@umask( 0 );
			mkdir( $this->cachedir, 0777 );
		}

		return true;
	}
	// Sets the template filenames for handles. $filename_array
	// should be a hash of handle => filename pairs.
	function set_filenames( $filename_array )
	{
		if ( !is_array( $filename_array ) )
		{
			return false;
		}

		$template_names = '';
		foreach ( $filename_array as $handle => $filename )
		{
			if ( empty( $filename ) )
			{
				mx_message_die( GENERAL_ERROR, "Template error - Empty filename specified for $handle" );
			}

			$this->filename[$handle] = $filename;
			$this->files[$handle] = $this->make_filename( $filename );
		}

		return true;
	}
	// Generates a full path+filename for the given filename, which can either
	// be an absolute name, or a name relative to the rootdir for this Template
	// object.
	function make_filename( $filename, $xs_include = false )
	{
		global $module_root_path, $phpbb_root_path, $theme;

		if ( file_exists( $this->root . '/' . $filename ) )
		{
			// Check if it's an absolute or relative path.
			return ( substr( $filename, 0, 1 ) != '/' ) ? $this->root . '/' . $filename : $filename;
			// Added for mx...to use default theme is theme folder doesn't exist
		}
		elseif ( file_exists( $module_root_path . 'templates/subSilver/' . $filename ) )
		{
			return ( substr( $filename, 0, 1 ) != '/' ) ? $module_root_path . 'templates/subSilver/' . $filename : $filename;
		}
	}
	// Destroy template data set
	function destroy()
	{
		$this->_tpldata = array();
	}
	// Methods for loading and evaluating the templates
	function display( $handle )
	{
		global $user;

		if ( $filename = $this->_tpl_load( $handle ) )
		{
			include( $filename );
		}
		else
		{
			eval( ' ?>' . $this->compiled_code[$handle] . '<?php ' );
		}

		return true;
	}
	// Load a compiled template if possible, if not, recompile it
	function _tpl_load( &$handle )
	{
		global $phpEx, $userdata;

		$filename = $this->cachedir . $this->filename[$handle] . '.' . ( ( $this->static_lang ) ? $userdata['user_lang'] . '.' : '' ) . $phpEx;
		// Recompile page if the original template is newer, otherwise load the compiled version
		if ( file_exists( $filename ) && !$this->force_recompile && @filemtime( $filename ) == @filemtime( $this->files[$handle] ) )
		{
			return $filename;
		}
		// If we don't have a file assigned to this handle, die.
		if ( !isset( $this->files[$handle] ) )
		{
			mx_message_die( GENERAL_ERROR, "Pafiledb Template->loadfile(): No file specified for handle $handle" );
		}

		$str = '';
		// Try and open template for read
		if ( !( $fp = @fopen( $this->files[$handle], 'r' ) ) )
		{
			mx_message_die( GENERAL_ERROR, "Pafiledb Template->_tpl_load(): File $filename does not exist or is empty" );
		}

		$str = fread( $fp, filesize( $this->files[$handle] ) );
		@fclose( $fp );
		// Actually compile the code now.
		$this->compiled_code[$handle] = $this->compile( trim( $str ) );
		$this->compile_write( $handle, $this->compiled_code[$handle] );

		return false;
	}
	// Assign key variable pairs from an array
	function assign_vars( $vararray )
	{
		foreach ( $vararray as $key => $val )
		{
			$this->_tpldata['.'][0][$key] = $val;
		}

		return true;
	}
	// Assign a single variable to a single key
	function assign_var( $varname, $varval )
	{
		$this->_tpldata['.'][0][$varname] = $varval;

		return true;
	}
	// Assign key variable pairs from an array to a specified block
	function assign_block_vars( $blockname, $vararray )
	{
		if ( strstr( $blockname, '.' ) )
		{
			// Nested block.
			$blocks = explode( '.', $blockname );
			$blockcount = sizeof( $blocks ) - 1;

			$str = &$this->_tpldata;
			for ( $i = 0; $i < $blockcount; $i++ )
			{
				$str = &$str[$blocks[$i]];
				$str = &$str[sizeof( $str ) - 1];
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
	function _tpl_include( $filename, $include = true )
	{
		global $user;

		$handle = $filename;
		$this->filename[$handle] = $filename;
		$this->files[$handle] = $this->make_filename( $filename );

		$filename = $this->_tpl_load( $handle );

		if ( $include )
		{
			if ( $filename )
			{
				include( $filename );
			}
			else
			{
				eval( ' ?>' . $this->compiled_code[$handle] . '<?php ' );
			}
		}
	}
	// This next set of methods could be seperated off and included since
	// they deal exclusively with compilation ... which is done infrequently
	// and would save a fair few kb
	// The all seeing all doing compile method. Parts are inspired by or directly
	// from Smarty
	function compile( $code, $no_echo = false, $echo_var = '' )
	{
		global $pafiledb_config;
		// Remove any "loose" php ... we want to give admins the ability
		// to switch on/off PHP for a given template. Allowing unchecked
		// php is a no-no. There is a potential issue here in that non-php
		// content may be removed ... however designers should use entities
		// if they wish to display < and >
		$match_php_tags = array( '#\<\?php .*?\?\>#is', '#\<\script language="php"\>.*?\<\/script\>#is', '#\<\?.*?\?\>#s', '#\<%.*?%\>#s' );
		$code = preg_replace( $match_php_tags, '', $code );
		// Pull out all block/statement level elements and seperate
		// plain text
		preg_match_all( '#<!-- PHP -->(.*?)<!-- ENDPHP -->#s', $code, $matches );
		$php_blocks = $matches[1];
		$code = preg_replace( '#<!-- PHP -->(.*?)<!-- ENDPHP -->#s', '<!-- PHP -->', $code );

		preg_match_all( '#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#', $code, $matches );
		$include_blocks = $matches[1];
		$code = preg_replace( '#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\.]+?) -->#', '<!-- INCLUDE -->', $code );

		preg_match_all( '#<!-- INCLUDEPHP ([a-zA-Z0-9\_\-\+\.\\\\]+?) -->#', $code, $matches );
		$includephp_blocks = $matches[1];
		$code = preg_replace( '#<!-- INCLUDEPHP ([a-zA-Z0-9\_\-\+\.]+?) -->#', '<!-- INCLUDEPHP -->', $code );

		preg_match_all( '#<!-- (.*?) (.*?)?[ ]?-->#s', $code, $blocks );
		$text_blocks = preg_split( '#<!-- (.*?) (.*?)?[ ]?-->#s', $code );
		for( $i = 0; $i < count( $text_blocks ); $i++ )
		{
			$this->compile_var_tags( $text_blocks[$i] );
		}

		$compile_blocks = array();

		for ( $curr_tb = 0; $curr_tb < count( $text_blocks ); $curr_tb++ )
		{
			switch ( $blocks[1][$curr_tb] )
			{
				case 'BEGIN':
					$this->block_else_level[] = false;
					$compile_blocks[] = '<?php ' . $this->compile_tag_block( $blocks[2][$curr_tb] ) . ' ?>';
					break;

				case 'BEGINELSE':
					$this->block_else_level[sizeof( $this->block_else_level ) - 1] = true;
					$compile_blocks[] = '<?php }} else { ?>';
					break;

				case 'END':
					array_pop( $this->block_names );
					$compile_blocks[] = '<?php ' . ( ( array_pop( $this->block_else_level ) ) ? '}' : '}}' ) . ' ?>';
					break;

				case 'IF':
					$compile_blocks[] = '<?php ' . $this->compile_tag_if( $blocks[2][$curr_tb], false ) . ' ?>';
					break;

				case 'ELSE':
					$compile_blocks[] = '<?php } else { ?>';
					break;

				case 'ELSEIF':
					$compile_blocks[] = '<?php ' . $this->compile_tag_if( $blocks[2][$curr_tb], true ) . ' ?>';
					break;

				case 'ENDIF':
					$compile_blocks[] = '<?php } ?>';
					break;
					
				case 'DEFINE':
					$str = $this->compile_tag_define( $blocks[2][$curr_tb] );
					$compile_blocks[] = '<?php ' . $str . ' ?>';				
					break;
					
				case 'INCLUDE':
					$temp = '';
					list( , $temp ) = each( $include_blocks );
					$compile_blocks[] = '<?php ' . $this->compile_tag_include( $temp ) . ' ?>';
					$this->_tpl_include( $temp, false );
					break;

				case 'INCLUDEPHP':
					if ( $pafiledb_config['tpl_php'] )
					{
						$temp = '';
						list( , $temp ) = each( $includephp_blocks );
						$compile_blocks[] = '<?php ' . $this->compile_tag_include_php( $temp ) . ' ?>';
					}
					break;

				case 'PHP':
					if ( $pafiledb_config['tpl_php'] )
					{
						$temp = '';
						list( , $temp ) = each( $php_blocks );
						$compile_blocks[] = '<?php ' . $temp . ' ?>';
					}
					break;

				default:
					$this->compile_var_tags( $blocks[0][$curr_tb] );
					$trim_check = trim( $blocks[0][$curr_tb] );
					$compile_blocks[] = ( !$do_not_echo ) ? ( ( !empty( $trim_check ) ) ? $blocks[0][$curr_tb] : '' ) : ( ( !empty( $trim_check ) ) ? $blocks[0][$curr_tb] : '' );
					break;
			}
		}

		$template_php = '';
		for ( $i = 0; $i < count( $text_blocks ); $i++ )
		{
			$trim_check_text = trim( $text_blocks[$i] );
			$trim_check_block = trim( $compile_blocks[$i] );
			$template_php .= ( !$no_echo ) ? ( ( !empty( $trim_check_text ) ) ? $text_blocks[$i] : '' ) . ( ( !empty( $compile_blocks[$i] ) ) ? $compile_blocks[$i] : '' ) : ( ( !empty( $trim_check_text ) ) ? $text_blocks[$i] : '' ) . ( ( !empty( $compile_blocks[$i] ) ) ? $compile_blocks[$i] : '' );
		}
		// There will be a number of occassions where we switch into and out of
		// PHP mode instantaneously. Rather than "burden" the parser with this
		// we'll strip out such occurences, minimising such switching
		$template_php = str_replace( ' ?><?php ', '', $template_php );

		return ( !$no_echo ) ? $template_php : "\$$echo_var .= '" . $template_php . "'";
	}

	function compile_var_tags( &$text_blocks )
	{
		// change template varrefs into PHP varrefs
		$varrefs = array();
		// This one will handle varrefs WITH namespaces
		preg_match_all( '#\{(([a-z0-9\-_]+?\.)+?)([a-z0-9\-_]+?)\}#is', $text_blocks, $varrefs );

		for ( $j = 0; $j < sizeof( $varrefs[1] ); $j++ )
		{
			$namespace = $varrefs[1][$j];
			$varname = $varrefs[3][$j];
			$new = $this->generate_block_varref( $namespace, $varname );

			$text_blocks = str_replace( $varrefs[0][$j], $new, $text_blocks );
		}
		// This will handle the remaining root-level varrefs
		if ( !$this->static_lang )
		{
			$text_blocks = preg_replace( '#\{L_([a-z0-9\-_]*?)\}#is', "<?php echo ((isset(\$this->_tpldata['.'][0]['L_\\1'])) ? \$this->_tpldata['.'][0]['L_\\1'] : ((isset(\$lang['\\1'])) ? \$lang['\\1'] : '{ ' . ucfirst(strtolower(str_replace('_', ' ', '\\1'))) . ' 	}')); ?>", $text_blocks );
		}
		else
		{
			global $lang;

			$text_blocks = preg_replace( '#\{L_([A-Z0-9\-_]*?)\}#e', "'<?php echo ((isset(\$this->_tpldata[\'.\'][0][\'L_\\1\'])) ? \$this->_tpldata[\'.\'][0][\'L_\\1\'] : \'' . ((isset(\$lang['\\1'])) ? \$lang['\\1'] : '') . '\'); ?>'" , $text_blocks );
		}
		$text_blocks = preg_replace( '#\{([a-z0-9\-_]*?)\}#is', "<?php echo \$this->_tpldata['.'][0]['\\1']; ?>", $text_blocks );

		return;
	}

	function compile_tag_block( $tag_args )
	{
		// Allow for control of looping (indexes start from zero):
		// foo(2)    : Will start the loop on the 3rd entry
		// foo(-2)   : Will start the loop two entries from the end
		// foo(3,4)  : Will start the loop on the fourth entry and end it on the fourth
		// foo(3,-4) : Will start the loop on the fourth entry and end it four from last
		if ( preg_match( '#^(.*?)\(([\-0-9]+)(,([\-0-9]+))?\)$#', $tag_args, $match ) )
		{
			$tag_args = $match[1];
			$loop_start = ( $match[2] < 0 ) ? '$_' . $tag_args . '_count ' . ( $match[2] - 1 ) : $match[2];
			$loop_end = ( $match[4] ) ? ( ( $match[4] < 0 ) ? '$_' . $tag_args . '_count ' . $match[4] : ( $match[4] + 1 ) ) : '$_' . $tag_args . '_count';
		}
		else
		{
			$loop_start = 0;
			$loop_end = '$_' . $tag_args . '_count';
		}

		$tag_template_php = '';
		array_push( $this->block_names, $tag_args );

		if ( sizeof( $this->block_names ) < 2 )
		{
			// Block is not nested.
			$tag_template_php = '$_' . $tag_args . "_count = (isset(\$this->_tpldata['$tag_args'])) ?  sizeof(\$this->_tpldata['$tag_args']) : 0;";
		}
		else
		{
			// This block is nested.
			// Generate a namespace string for this block.
			$namespace = implode( '.', $this->block_names );
			// Get a reference to the data array for this block that depends on the
			// current indices of all parent blocks.
			$varref = $this->generate_block_data_ref( $namespace, false );
			// Create the for loop code to iterate over this block.
			$tag_template_php = '$_' . $tag_args . '_count = (isset(' . $varref . ')) ? sizeof(' . $varref . ') : 0;';
		}

		$tag_template_php .= 'if ($_' . $tag_args . '_count) {';
		$tag_template_php .= 'for ($this->_' . $tag_args . '_i = ' . $loop_start . '; $this->_' . $tag_args . '_i < ' . $loop_end . '; $this->_' . $tag_args . '_i++){';

		return $tag_template_php;
	}

	// Compile IF tags - much of this is from Smarty with
	// some adaptions for our block level methods

	function compile_tag_if( $tag_args, $elseif )
	{
		/* Tokenize args for 'if' tag. */
		preg_match_all( '/(?:
                         "[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
                         \'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
                         [(),]                                  |
                         [^\s(),]+)/x', $tag_args, $match );

		$tokens = $match[0];
		$is_arg_stack = array();

		for ( $i = 0; $i < count( $tokens ); $i++ )
		{
			$token = &$tokens[$i];

			switch ( strtolower( $token ) )
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
					array_push( $is_arg_stack, $i );
					break;

				case 'is':
					$is_arg_start = ( $tokens[$i-1] == ')' ) ? array_pop( $is_arg_stack ) : $i-1;
					$is_arg = implode( '	', array_slice( $tokens, $is_arg_start, $i - $is_arg_start ) );

					$new_tokens = $this->_parse_is_expr( $is_arg, array_slice( $tokens, $i + 1 ) );

					array_splice( $tokens, $is_arg_start, count( $tokens ), $new_tokens );

					$i = $is_arg_start;

				default:
					if ( preg_match( '#^(([a-z0-9\-_]+?\.)+?)?([A-Z]+[A-Z0-9\-_]+?)$#s', $token, $varrefs ) )
					{
						$token = ( !empty( $varrefs[1] ) ) ? $this->generate_block_data_ref( substr( $varrefs[1], 0, strlen( $varrefs[1] ) - 1 ), true ) . '[\'' . $varrefs[3] . '\']' : '$this->_tpldata[\'.\'][0][\'' . $varrefs[3] . '\']';
					}
					break;
			}
		}

		return ( ( $elseif ) ? '} elseif (' : 'if (' ) . ( implode( ' ', $tokens ) . ') { ' );
	}
	
	function compile_tag_define($tag_args)
	{
		preg_match('#^(([a-z0-9\-_]+?\.)+?)?\$([A-Z][A-Z0-9_\-]*?) = (\'?)(.*?)(\'?)$#', $tag_args, $match);

		if (empty($match[3]) || empty($match[5]))
		{
			return '';
		}

		// Are we a string?
		if ($match[4] && $match[6])
		{
			$match[5] = "'" . addslashes(str_replace(array('\\\'', '\\\\'), array('\'', '\\'), $match[5])) . "'";
		}
		else
		{
			preg_match('#(true|false|\.)#i', $match[5], $type);

			switch (strtolower($type[1]))
			{
				case 'true':
				case 'false':
					$match[5] = strtoupper($match[5]);
					break;
				case '.';
					$match[5] = doubleval($match[5]);
					break;
				default:
					$match[5] = intval($match[5]);
					break;
			}
		}

		return (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[3] . '\']' : '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $match[3] . '\']') . ' = ' . $match[5] . ';';
	}

	function compile_tag_undefine($tag_args)
	{
		preg_match('#^(([a-z0-9\-_]+?\.)+?)?\$([A-Z][A-Z0-9_\-]*?)$#', $tag_args, $match);
		if (empty($match[3]))
		{
			return '';
		}
		return 'unset(' . (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[3] . '\']' : '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $match[3] . '\']') . ');';
	}
	
	function compile_tag_include( $tag_args )
	{
		return "\$this->_tpl_include('$tag_args');";
	}

	function compile_tag_include_php( $tag_args )
	{
		return "include('" . $this->root . '/' . $tag_args . "');";
	}
	// This is from Smarty
	function _parse_is_expr( $is_arg, $tokens )
	{
		$expr_end = 0;
		$negate_expr = false;

		if ( ( $first_token = array_shift( $tokens ) ) == 'not' )
		{
			$negate_expr = true;
			$expr_type = array_shift( $tokens );
		}
		else
		{
			$expr_type = $first_token;
		}

		switch ( $expr_type )
		{
			case 'even':
				if ( @$tokens[$expr_end] == 'by' )
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "!(($is_arg	/ $expr_arg) % $expr_arg)";
				}
				else
				{
					$expr = "!($is_arg % 2)";
				}
				break;

			case 'odd':
				if ( @$tokens[$expr_end] == 'by' )
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "(($is_arg / $expr_arg)	% $expr_arg)";
				}
				else
				{
					$expr = "($is_arg %	2)";
				}
				break;

			case 'div':
				if ( @$tokens[$expr_end] == 'by' )
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "!($is_arg % $expr_arg)";
				}
				break;

			default:
				break;
		}

		if ( $negate_expr )
		{
			$expr = "!($expr)";
		}

		array_splice( $tokens, 0, $expr_end, $expr );

		return $tokens;
	}

	/**
	 * Generates a reference to the given variable inside the given (possibly nested)
	 * block namespace. This is a string of the form:
	 * ' . $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['varname'] . '
	 * It's ready to be inserted into an "echo" line in one of the templates.
	 * NOTE: expects a trailing "." on the namespace.
	 */
	function generate_block_varref( $namespace, $varname )
	{
		// Strip the trailing period.
		$namespace = substr( $namespace, 0, strlen( $namespace ) - 1 );
		// Get a reference to the data block for this namespace.
		$varref = $this->generate_block_data_ref( $namespace, true );
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
	function generate_block_data_ref( $blockname, $include_last_iterator )
	{
		// Get an array of the blocks involved.
		$blocks = explode( '.', $blockname );
		$blockcount = sizeof( $blocks ) - 1;
		$varref = '$this->_tpldata';
		// Build up the string with everything but the last child.
		for ( $i = 0; $i < $blockcount; $i++ )
		{
			$varref .= "['" . $blocks[$i] . "'][\$this->_" . $blocks[$i] . '_i]';
		}
		// Add the block reference for the last child.
		$varref .= "['" . $blocks[$blockcount] . "']";
		// Add the iterator for the last child if requried.
		if ( $include_last_iterator )
		{
			$varref .= '[$this->_' . $blocks[$blockcount] . '_i]';
		}

		return $varref;
	}

	function compile_write( &$handle, $data )
	{
		global $phpEx, $user;

		$filename = $this->cachedir . $this->filename[$handle] . '.' . ( ( $this->static_lang ) ? $userdata['user_lang'] . '.' : '' ) . $phpEx;

		if ( $fp = @fopen( $filename, 'w+' ) )
		{
			@flock( $fp, LOCK_EX );
			@fwrite ( $fp, $data );
			@flock( $fp, LOCK_UN );
			@fclose( $fp );

			@umask( 0 );
			@touch( $filename, filemtime( $this->files[$handle] ) );
			@chmod( $filename, 0644 );
		}

		return;
	}

	function compile_cache_clear( $template = false )
	{
		global $phpbb_root_path;
		// MX
		global $mx_root_path, $module_root_path, $is_block, $phpEx;

		$template_list = array();

		if ( !$template )
		{
			/* - orig
			$dp = opendir($phpbb_root_path . $this->cache_root);
*/
			// MX
			$dp = opendir( $module_root_path . $this->cache_root );
			while ( $dir = readdir( $dp ) )
			{
				/* - orig
				$template_dir = $phpbb_root_path . $this->cache_root . $dir;
*/
				// MX
				$template_dir = $module_root_path . $this->cache_root . $dir;
				if ( !is_file( $template_dir ) && !is_link( $template_dir ) && $dir != '.' && $dir != '..' )
				{
					array_push( $template_list, $dir );
				}
			}
			closedir( $dp );
		}
		else
		{
			array_push( $template_list, $template );
		}

		foreach ( $template_list as $template )
		{
			/* - orig
			$dp = opendir($phpbb_root_path . $this->cache_root . $template);
			while ($file = readdir($dp))
			{
				@unlink($phpbb_root_path . $this->cache_root . $template . '/' . $file);
			}
*/
			// MX
			$dp = opendir( $module_root_path . $this->cache_root . $template );
			while ( $file = readdir( $dp ) )
			{
				@unlink( $module_root_path . $this->cache_root . $template . '/' . $file );
			}
			closedir( $dp );
		}

		return;
	}
}

?>