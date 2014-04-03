<?php
/**
*
* @package notifications
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\notification;

/**
* Notifications exception
*
* @package notifications
*/
class exception extends \Exception
{
	public function __toString()
	{
		return $this->getMessage();
	}
}
