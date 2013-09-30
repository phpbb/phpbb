<?php

/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace foo\bar\event;

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class permission implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.permissions'	=> 'add_permissions',
		);
	}

	public function add_permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions['u_foo'] = array('lang' => 'ACL_U_FOOBAR', 'cat' => 'post');
		$event['permissions'] = $permissions;
	}
}
