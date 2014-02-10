<?php
/**
*
* @package entity
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\model\entity;
 
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
abstract class entity
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
		$this->data[$field] = $value;
	}
}
