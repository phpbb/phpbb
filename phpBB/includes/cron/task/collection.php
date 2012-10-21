<?php
/**
*
* @package phpBB3
* @copyright (c) 2011 phpBB Group
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
* Collects cron tasks
*
* @package phpBB3
*/
class phpbb_cron_task_collection implements ArrayAccess
{
	/**
	* ArrayAccess method
	*
	* @param mixed $offset Array offset
	*/
	public function offsetExists($offset)
	{
		return isset($this->tasks[$offset]);
	}

	/**
	* ArrayAccess method
	*
	* @param mixed $offset Array offset
	*/
	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->tasks[$offset] : null;
	}

	/**
	* ArrayAccess method
	*
	* @param mixed $offset Array offset
	* @param mixed $value New value
	*/
	public function offsetSet($offset, $value)
	{
		if ($offset === null)
		{
			$this->tasks[] = $value;
		}
		else
		{
			$this->tasks[$offset] = $value;
		}
	}

	/**
	* ArrayAccess method
	*
	* @param mixed $offset Array offset
	*/
	public function offsetUnset($offset)
	{
		$this->tasks[$offset] = null;
	}
}
