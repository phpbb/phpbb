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
		$form_time = $request->variable('form_time', 0);
		$form_time = ($form_time <= 0 || $form_time > time()) ? time() : $form_time;

		$phpbb_notifications = $phpbb_container->get('notification_manager');
		$pagination = $phpbb_container->get('pagination');

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
								if ($request->is_set_post(str_replace('.', '_', $type . '_' . $method_data['id'])) && (!isset($subscriptions[$type]) || !in_array($method_data['id'], $subscriptions[$type])))
								{
									$phpbb_notifications->add_subscription($type, 0, $method_data['id']);
								}
								else if (!$request->is_set_post(str_replace('.', '_', $type . '_' . $method_data['id'])) && isset($subscriptions[$type]) && in_array($method_data['id'], $subscriptions[$type]))
								{
									$phpbb_notifications->delete_subscription($type, 0, $method_data['id']);
								}
							}

							if ($request->is_set_post(str_replace('.', '_', $type) . '_notification') && !isset($subscriptions[$type]))
							{
								$phpbb_notifications->add_subscription($type);
							}
							else if (!$request->is_set_post(str_replace('.', '_', $type) . '_notification') && isset($subscriptions[$type]))
							{
								$phpbb_notifications->delete_subscription($type);
							}
						}
					}

					meta_refresh(3, $this->u_action);
					$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
					trigger_error($message);
				}

				$this->output_notification_methods($phpbb_notifications, $template, $user, 'notification_methods');

				$this->output_notification_types($subscriptions, $phpbb_notifications, $template, $user, 'notification_types');

				$this->tpl_name = 'ucp_notifications';
				$this->page_title = 'UCP_NOTIFICATION_OPTIONS';
			break;

			case 'notification_list':
			default:
				// Mark all items read
				if ($request->variable('mark', '') == 'all' && check_link_hash($request->variable('token', ''), 'mark_all_notifications_read'))
				{
					$phpbb_notifications->mark_notifications_read(false, false, $user->data['user_id'], $form_time);

					meta_refresh(3, $this->u_action);
					$message = $user->lang['NOTIFICATIONS_MARK_ALL_READ_SUCCESS'];

					if ($request->is_ajax())
					{
						$json_response = new \phpbb\json_response();
						$json_response->send(array(
							'MESSAGE_TITLE'	=> $user->lang['INFORMATION'],
							'MESSAGE_TEXT'	=> $message,
							'success'		=> true,
						));
					}
					$message .= '<br /><br />' . $user->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');

					trigger_error($message);
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
				$start = $pagination->validate_start($start, $config['topics_per_page'], $notifications['total_count']);
				$pagination->generate_template_pagination($base_url, 'pagination', 'start', $notifications['total_count'], $config['topics_per_page'], $start);

				$template->assign_vars(array(
					'TOTAL_COUNT'	=> $notifications['total_count'],
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
	* @param array $subscriptions Array containing global subscriptions
	* @param \phpbb\notification\manager $phpbb_notifications
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param string $block
	*/
	public function output_notification_types($subscriptions, \phpbb\notification\manager $phpbb_notifications, \phpbb\template\template $template, \phpbb\user $user, $block = 'notification_types')
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
	* @param \phpbb\notification\manager $phpbb_notifications
	* @param \phpbb\template\template $template
	* @param \phpbb\user $user
	* @param string $block
	*/
	public function output_notification_methods(\phpbb\notification\manager $phpbb_notifications, \phpbb\template\template $template, \phpbb\user $user, $block = 'notification_methods')
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
