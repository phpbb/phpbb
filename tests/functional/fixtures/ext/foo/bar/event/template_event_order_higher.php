<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace foo\bar\event;

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class template_event_order implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.twig_event_tokenparser_constructor'	=> 'set_template_event_priority',
		);
	}

	public function set_template_event_priority($event)
	{
		$template_event_priority_array = $event['template_event_priority_array'];
		$template_event_priority_array['foo_bar'] = [
			'event/navbar_header_quick_links_after' => 1,
		];
		$event['template_event_priority_array'] = $template_event_priority_array;
	}
}
