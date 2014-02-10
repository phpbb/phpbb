<?php
/**
*
* @package extension
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\extension;

/**
 * Exception class for metadata
 */
class exception extends \UnexpectedValueException
{
	public function __toString()
	{
		return $this->getMessage();
	}
}
