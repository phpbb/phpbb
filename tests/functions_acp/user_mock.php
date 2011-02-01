<?php
/**
*
* @package testing
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* phpbb_mock_lang
* mock a user with some language-keys specified
*/
class phpbb_mock_lang implements ArrayAccess
{
	public function offsetExists($offset)
	{
		return true;
	}

	public function offsetGet($offset)
	{
		return $offset;
	}

	public function offsetSet($offset, $value)
	{
	}

	public function offsetUnset($offset)
	{
	}
}
