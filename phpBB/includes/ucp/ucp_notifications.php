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

class ucp_notifications
{
	public $u_action;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$phpbb_notifications = $phpbb_container->get('notifications');
		$template = $phpbb_container->get('template');
		$user = $phpbb_container->get('user');
		$request = $phpbb_container->get('request');

		$subscriptions = $phpbb_notifications->get_subscriptions(false, true);

		// Add/remove subscriptions
		if ($request->is_set_post('submit'))
		{
			$notification_methods = $phpbb_notifications->get_subscription_methods();

			foreach($phpbb_notifications->get_subscription_types() as $type => $data)
			{
				if ($request->is_set_post($type . '_notification') && !isset($subscriptions[$type]))
				{
					// add
					$phpbb_notifications->add_subscription($type);
				}
				else if (!$request->is_set_post($type . '_notification') && isset($subscriptions[$type]))
				{
					// remove
					$phpbb_notifications->delete_subscription($type);
				}

				foreach($notification_methods as $method)
				{
					if ($request->is_set_post($type . '_' . $method) && (!isset($subscriptions[$type]) || !in_array($method, $subscriptions[$type])))
					{
						// add
						$phpbb_notifications->add_subscription($type, 0, $method);
					}
					else if (!$request->is_set_post($type . '_' . $method) && isset($subscriptions[$type]) && in_array($method, $subscriptions[$type]))
					{
						// remove
						$phpbb_notifications->delete_subscription($type, 0, $method);
					}
				}
			}
		}

		// todo include language files for extensions?

		$this->output_notification_methods('notification_methods', $phpbb_notifications, $template, $user);

		$this->output_notification_types('notification_types', $phpbb_notifications, $template, $user);

		$this->tpl_name = 'ucp_notifications';
		$this->page_title = 'UCP_NOTIFICATIONS';
	}

	/**
	* Output all the notification types to the template
	*
	* @param string $block
	* @param phpbb_notifications_service $phpbb_notifications
	* @param phpbb_template $template
	* @param phpbb_user $user
	*/
	public function output_notification_types($block = 'notification_types', phpbb_notifications_service $phpbb_notifications, phpbb_template $template, phpbb_user $user)
	{
		$notification_methods = $phpbb_notifications->get_subscription_methods();
		$subscriptions = $phpbb_notifications->get_subscriptions(false, true);

		foreach($phpbb_notifications->get_subscription_types() as $type => $data)
		{
			$template->assign_block_vars($block, array(
				'TYPE'				=> $type,

				'NAME'				=> (is_array($data) && isset($data['lang'])) ? $user->lang($data['lang']) : $user->lang('NOTIFICATION_TYPE_' . strtoupper($type)),

				'SUBSCRIBED'		=> (isset($subscriptions[$type])) ? true : false,
			));

			foreach($notification_methods as $method)
			{
				$template->assign_block_vars($block . '.notification_methods', array(
					'METHOD'			=> $method,

					'NAME'				=> $user->lang('NOTIFICATION_METHOD_' . strtoupper($method)),

					'SUBSCRIBED'		=> (isset($subscriptions[$type]) && in_array($method, $subscriptions[$type])) ? true : false,
				));
			}
		}
	}

	/**
	* Output all the notification methods to the template
	*
	* @param string $block
	* @param phpbb_notifications_service $phpbb_notifications
	* @param phpbb_template $template
	* @param phpbb_user $user
	*/
	public function output_notification_methods($block = 'notification_methods', phpbb_notifications_service $phpbb_notifications, phpbb_template $template, phpbb_user $user)
	{
		$notification_methods = $phpbb_notifications->get_subscription_methods();

		foreach($notification_methods as $method)
		{
			$template->assign_block_vars($block, array(
				'METHOD'			=> $method,

				'NAME'				=> $user->lang('NOTIFICATION_METHOD_' . strtoupper($method)),
			));
		}
	}
}
