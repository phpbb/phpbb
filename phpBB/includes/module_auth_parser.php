<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
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
 * Used to parse module auth strings in the database.
 * This is slower than eval, but eval is evil, and this will only be done once
 * on a page.
 *
 * @author APTX
 **/
class phpbb_module_auth_parser
{
	
	var $_tokens;
	var $_c;
	var $_i;
	var $forum_id;
	
	/**
	 * Parses the expression and returns the result.
	 * Calls _variable_callback for variable values.
	 * 
	 * @param $string string with epxression to parse
	 * 
	 * @return bool Expression result
	 */
	function parse($string)
	{
		$this->_tokens = array();
		$this->_i = 0;
		
		preg_match_all('~\$id|&&|\|\||\(|\)|!|[a-z][a-zA-Z0-9_]*(?:,\$id)?~', $string, $this->_tokens);
		$this->_tokens = $this->_tokens[0];
		$this->_c = &$this->_tokens[0];
		
		return $this->_expr(false);
	}

	/**
	 * Returns the value of variable $var_name
	 * @param $var_nbmae variable name
	 * 
	 * @return bool variable value
	 */
	function _variable_callback($var_name)
	{
		global $auth, $config, $request;
		$prefix = substr($var_name, 0, 4);
		
		switch ($prefix)
		{
			case '$id':
				return $this->forum_id;
			
			case 'acl_':
				$parts = array();
				preg_match('#acl_([a-z0-9_]+)(,\$id)?#', $var_name, $parts);
				
				if (empty($parts[2]))
				{
					return $auth->acl_get($parts[1]);
				}
				else
				{
					return $auth->acl_get($parts[1], $this->forum_id);
				}

			case 'aclf':
				return $auth->acl_getf_global(substr($var_name, 5));

			case 'cfg_':
				return $config[substr($var_name, 4)];

			case 'requ':
				return $request->variable(substr($var_name, 8), false);
			
			default:
				return false;
		}
	}

	function _expr($get)
	{
		$left = $this->_prim($get);
		while (true)
		{
			switch ($this->_c)
			{
				case '&&':
					$right = $this->_prim(true);
					$left = $left && $right;
				break;
				case '||':
					$right = $this->_prim(true);
					$left = $left || $right;
				break;
				default:
					return $left;
			}
		}
	}
	
	function _prim($get)
	{
		if ($get) $this->_get_token();

		switch ($this->_c)
		{
			case '$id':
				$ret = $this->_variable_callback('$id');
				$this->_get_token();
				return $ret;
			break;
			case '!':
				return !$this->_prim(true);
			break;
			case '(':
				$e = $this->_expr(true);
				if ($this->_c != ')')
				{
					print 'missing closing )';
					return 0;
				}
				$this->_get_token();
				return $e;
			break;
			case '':
				return false;
			default:
				$ret = $this->_variable_callback($this->_c);
				$this->_get_token();
				return $ret;
		}
	}
	
	function _get_token()
	{
		++$this->_i;
		if ($this->_i > sizeof($this->_tokens))
			return '';
		$this->_c = &$this->_tokens[$this->_i];
	}
}
