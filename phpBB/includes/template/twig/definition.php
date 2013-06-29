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

/**
* This class holds all DEFINE variables from the current page load
*/
class phpbb_template_twig_definition
{
	/** @var array **/
	protected $definitions = array();

	/**
	* Get a DEFINE'd variable
	*
	* @param string $name
	* @return mixed Null if not found
	*/
	public function __call($name, $arguments)
	{
		return (isset($this->definitions[$name])) ? $this->definitions[$name] : null;
	}

	/**
	* DEFINE a variable
	*
	* @param string $name
	* @param mixed $value
	* @return phpbb_template_twig_definition
	*/
	public function set($name, $value)
	{
		$this->definitions[$name] = $value;

		return $this;
	}
}
