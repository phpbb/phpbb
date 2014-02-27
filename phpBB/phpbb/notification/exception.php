<?php
/**
*
* @package notifications
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
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
