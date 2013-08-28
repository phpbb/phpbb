<?php

/**
*
* @package testing
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
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class phpbb_ext_foo_bar_event_user_setup implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'	=> 'add_global_translations',
		);
	}

	public function add_global_translations($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'foo/bar',
			'lang_set' => 'foo_global',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}
}
