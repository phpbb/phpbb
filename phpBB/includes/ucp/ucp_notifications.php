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
		global $config, $template, $user, $request, $phpbb_container;
		global $phpbb_root_path, $phpEx;

		add_form_key('ucp_notification');

		$start = $request->variable('start', 0);
		$form_time = min($request->variable('form_time', 0), time());

		$phpbb_notifications = $phpbb_container->get('notification_manager');

		switch ($mode)
		{
			case 'notification_options':
				$subscriptions = $phpbb_notifications->get_global_subscriptions(false);

				// Add/remove subscriptions
				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('ucp_notification'))
					{
						trigger_error('FORM_INVALID');
					}

					$notification_methods = $phpbb_notifications->get_subscription_methods();

					foreach($phpbb_notifications->get_subscription_types() as $group => $subscription_types)
					{
						foreach($subscription_types as $type => $data)
						{
							foreach($notification_methods as $method => $method_data)
							{
								if ($request->is_set_post($type . '_' . $method_data['id']) && (!isset($subscriptions[$type]) || !in_array($method_data['id'], $subscriptions[$type])))
								{
									$phpbb_notifications->add_subscription($type, 0, $method_data['id']);
								}
								else if (!$request->is_set_post($type . '_' . $method_data['id']) && isset($subscriptions[$type]) && in_array($method_data['id'], $subscriptions[$type]))
								{
									$phpbb_notifications->delete_subscription($type, 0, $method_data['id']);
								}
							}

							if ($request->is_set_post($type . '_notification') && !isset($subscriptions[$type]))
							{
								$phpbb_notifications->add_subscription($type);
							}
							else if (!$request->is_set_post($type . '_notification') && isset($subscriptions[$type]))
							{
								$phpbb_notifications->delete_subscription($type);
							}
						}
					}

					meta_refresh(3, $this->u_action);
					$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
					trigger_error($message);
				}

				$this->output_notification_methods('notification_methods', $phpbb_notifications, $template, $user);

				$this->output_notification_types($subscriptions, 'notification_types', $phpbb_notifications, $template, $user);

				$this->tpl_name = 'ucp_notifications';
				$this->page_title = 'UCP_NOTIFICATION_OPTIONS';
			break;

			case 'notification_list':
			default:
				// Mark all items read
				if ($request->variable('mark', '') == 'all' && (confirm_box(true) || check_link_hash($request->variable('token', ''), 'mark_all_notifications_read')))
				{
					if (confirm_box(true))
					{
						$phpbb_notifications->mark_notifications_read(false, false, $user->data['user_id'], $form_time);

						meta_refresh(3, $this->u_action);
						$message = $user->lang['NOTIFICATIONS_MARK_ALL_READ_SUCCESS'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
						trigger_error($message);
					}
					else
					{
						confirm_box(false, 'NOTIFICATIONS_MARK_ALL_READ', build_hidden_fields(array(
							'mark'		=> 'all',
							'form_time'	=> $form_time,
						)));
					}
				}

				// Mark specific notifications read
				if ($request->is_set_post('submit'))
				{
					if (!check_form_key('ucp_notification'))
					{
						trigger_error('FORM_INVALID');
					}

					$mark_read = $request->variable('mark', array(0));

					if (!empty($mark_read))
					{
						$phpbb_notifications->mark_notifications_read_by_id($mark_read, $form_time);
					}
				}

				$notifications = $phpbb_notifications->load_notifications(array(
					'start'			=> $start,
					'limit'			=> $config['topics_per_page'],
					'count_total'	=> true,
				));

				foreach ($notifications['notifications'] as $notification)
				{
					$template->assign_block_vars('notification_list', $notification->prepare_for_display());
				}

				$base_url = append_sid("{$phpbb_root_path}ucp.$phpEx", "i=ucp_notifications&amp;mode=notification_list");
				phpbb_generate_template_pagination($template, $base_url, 'pagination', 'start', $notifications['total_count'], $config['topics_per_page'], $start);

				$template->assign_vars(array(
					'PAGE_NUMBER'	=> phpbb_on_page($template, $user, $base_url, $notifications['total_count'], $config['topics_per_page'], $start),
					'TOTAL_COUNT'	=> $user->lang('NOTIFICATIONS_COUNT', $notifications['total_count']),
					'U_MARK_ALL'	=> $base_url . '&amp;mark=all&amp;token=' . generate_link_hash('mark_all_notifications_read'),
				));

				$this->tpl_name = 'ucp_notifications';
				$this->page_title = 'UCP_NOTIFICATION_LIST';
			break;
		}

		$template->assign_vars(array(
			'TITLE'				=> $user->lang($this->page_title),
			'TITLE_EXPLAIN'		=> $user->lang($this->page_title . '_EXPLAIN'),

			'MODE'				=> $mode,

			'FORM_TIME'			=> time(),
		));
	}

	/**
	* Output all the notification types to the template
	*
	* @param string $block
	* @param phpbb_notification_manager $phpbb_notifications
	* @param phpbb_template $template
	* @param phpbb_user $user
	*/
	public function output_notification_types($subscriptions, $block = 'notification_types', phpbb_notification_manager $phpbb_notifications, phpbb_template $template, phpbb_user $user)
	{
		$notification_methods = $phpbb_notifications->get_subscription_methods();

		foreach($phpbb_notifications->get_subscription_types() as $group => $subscription_types)
		{
			$template->assign_block_vars($block, array(
				'GROUP_NAME'	=> $user->lang($group),
			));

			foreach($subscription_types as $type => $data)
			{
				$template->assign_block_vars($block, array(
					'TYPE'				=> $type,

					'NAME'				=> $user->lang($data['lang']),
					'EXPLAIN'			=> (isset($user->lang[$data['lang'] . '_EXPLAIN'])) ? $user->lang($data['lang'] . '_EXPLAIN') : '',

					'SUBSCRIBED'		=> (isset($subscriptions[$type])) ? true : false,
				));

				foreach($notification_methods as $method => $method_data)
				{
					$template->assign_block_vars($block . '.notification_methods', array(
						'METHOD'			=> $method_data['id'],

						'NAME'				=> $user->lang($method_data['lang']),

						'SUBSCRIBED'		=> (isset($subscriptions[$type]) && in_array($method_data['id'], $subscriptions[$type])) ? true : false,
					));
				}
			}
		}

		$template->assign_vars(array(
			strtoupper($block) . '_COLS' => sizeof($notification_methods) + 2,
		));
	}

	/**
	* Output all the notification methods to the template
	*
	* @param string $block
	* @param phpbb_notification_manager $phpbb_notifications
	* @param phpbb_template $template
	* @param phpbb_user $user
	*/
	public function output_notification_methods($block = 'notification_methods', phpbb_notification_manager $phpbb_notifications, phpbb_template $template, phpbb_user $user)
	{
		$notification_methods = $phpbb_notifications->get_subscription_methods();

		foreach($notification_methods as $method => $method_data)
		{
			$template->assign_block_vars($block, array(
				'METHOD'			=> $method_data['id'],

				'NAME'				=> $user->lang($method_data['lang']),
			));
		}
	}
}
