<?php
/**
*
* @package entity
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
* Enitity base class
* @package phpBB3
*/
abstract class phpbb_model_entity
{
	protected $data = array();

	public function __construct($row)
	{
		foreach ($row as $field => $value)
		{
			$this->set($field, $value);
		}
	}

	public function get($field)
	{
		return $this->data[$field];
	}

	public function set($field, $value)
	{
		$this->$field = $value;
	}
}
