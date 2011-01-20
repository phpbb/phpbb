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
 */
class phpbb_module_auth_parser
{
	/**
	* Contains the tokens parsed within the string
	* @var array(string)
	*/
	private $tokens;
	
	/**
	* Reference to the token currently being parsed
	* @var string
	*/
	private $current;
	
	/**
	* Current position within the token array
	* @var integer
	*/
	private $position;
	
	/**
	* Forum ID this permission is relative to
	* @var integer
	*/
	public $forum_id;
	
	/**
	 * Parses the expression and returns the result.
	 * Calls _variable_callback for variable values.
	 * 
	 * @param $string string with epxression to parse
	 * 
	 * @return bool Expression result
	 */
	public function parse($string)
	{
		$this->tokens = array();
		$this->position = 0;
		
		preg_match_all('~\$id|&&|\|\||\(|\)|!|[a-z][a-zA-Z0-9_]*(?:,\$id)?~', $string, $this->tokens);
		$this->tokens = $this->tokens[0];
		$this->current = &$this->tokens[0];
		
		return $this->expr(false);
	}

	/**
	 * Returns the value of variable $var_name
	 * @param $var_name variable name
	 * 
	 * @return bool variable value
	 */
	private function variable_callback($var_name)
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

	private function expr($get)
	{
		$left = $this->prim($get);
		while (true)
		{
			switch ($this->current)
			{
				case '&&':
					$right = $this->prim(true);
					$left = $left && $right;
				break;
				case '||':
					$right = $this->prim(true);
					$left = $left || $right;
				break;
				default:
					return $left;
			}
		}
	}
	
	private function prim($get)
	{
		if ($get) $this->get_token();

		switch ($this->current)
		{
			case '$id':
				$ret = $this->variable_callback('$id');
				$this->get_token();
				return $ret;
			break;
			case '!':
				return !$this->prim(true);
			break;
			case '(':
				$e = $this->expr(true);
				if ($this->current != ')')
				{
					print 'missing closing )';
					return 0;
				}
				$this->get_token();
				return $e;
			break;
			case '':
				return false;
			default:
				$ret = $this->variable_callback($this->current);
				$this->get_token();
				return $ret;
		}
	}
	
	private function get_token()
	{
		++$this->position;
		if ($this->position > sizeof($this->tokens))
		{
			return '';
		}
		$this->current = &$this->tokens[$this->position];
	}
}
