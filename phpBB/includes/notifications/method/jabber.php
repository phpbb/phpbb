<?php
/**
*
* @package notifications
* @copyright (c) 2012 phpBB Group
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
* Jabber notification method class
* This class handles sending Jabber messages for notifications
*
* @package notifications
*/
class phpbb_notifications_method_jabber extends phpbb_notifications_method_email
{
	/**
	* Notify method (since jabber gets sent through the same messenger, we let the jabber class inherit from this to reduce code duplication)
	*
	* @var mixed
	*/
	protected $notify_method = NOTIFY_IM;

	/**
	* Is this method available for the user?
	* This is checked on the notifications options
	*/
	public function is_available()
	{
		return ($this->global_available() && $this->phpbb_container->get('user')->data['jabber']);
	}

	/**
	* Is this method available at all?
	* This is checked before notifications are sent
	*/
	public function global_available()
	{
		$config = $this->phpbb_container->get('config');

		return ($config['jab_enable'] && @extension_loaded('xml'));
	}

	public function notify()
	{
		if (!$this->global_available())
		{
			return;
		}

		return parent::notify();
	}
}
