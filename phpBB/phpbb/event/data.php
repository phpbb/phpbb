<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\event;

use Symfony\Component\EventDispatcher\Event;

class data extends Event implements \ArrayAccess
{
	private $data;

	public function __construct(array $data = array())
	{
		$this->set_data($data);
	}

	public function set_data(array $data = array())
	{
		$this->data = $data;
	}

	public function get_data()
	{
		return $this->data;
	}

	/**
	 * Returns data filtered to only include specified keys.
	 *
	 * This effectively discards any keys added to data by hooks.
	 */
	public function get_data_filtered($keys)
	{
		return array_intersect_key($this->data, array_flip($keys));
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}

	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
}
