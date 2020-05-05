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
		global $config, $template, $user, $request, $phpbb_container, $phpbb_dispatcher;
		global $phpbb_root_path, $phpEx;

		add_form_key('ucp_notification');

		$start = $request->variable('start', 0);
		$form_time = $request->variable('form_time', 0);
		$form_time = ($form_time <= 0 || $form_time > time()) ? time() : $form_time;

		/* @var $phpbb_notifications \phpbb\notification\manager */
		$phpbb_notifications = $phpbb_container->get('notification_manager');

		/* @var $pagination \phpbb\pagination */
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

					foreach ($phpbb_notifications->get_subscription_types() as $group => $subscription_types)
					{
						foreach ($subscription_types as $type => $type_data)
						{
							foreach ($notification_methods as $method => $method_data)
							{
								$is_set_notify = $request->is_set_post(str_replace('.', '_', $type . '_' . $method_data['id']));
								$is_available = $method_data['method']->is_available($type_data['type']);

								/**
								* Event to perform additional actions before ucp_notifications is submitted
								*
								* @event core.ucp_notifications_submit_notification_is_set
								* @var	array	type_data		The notification type data
								* @var	array	method_data		The notification method data
								* @var	bool	is_set_notify	The notification is set or not
								* @var	bool	is_available	The notification is available or not
								* @var	array	subscriptions	The subscriptions data
								*
								* @since 3.2.10-RC1
								* @since 3.3.1-RC1
								*/
								$vars = [
									'type_data',
									'method_data',
									'is_set_notify',
									'is_available',
									'subscriptions',
								];
								extract($phpbb_dispatcher->trigger_event('core.ucp_notifications_submit_notification_is_set', compact($vars)));

								if ($is_set_notify && $is_available && (!isset($subscriptions[$type]) || !in_array($method_data['id'], $subscriptions[$type])))
								{
									$phpbb_notifications->add_subscription($type, 0, $method_data['id']);
								}
								else if ((!$is_set_notify || !$is_available) && isset($subscriptions[$type]) && in_array($method_data['id'], $subscriptions[$type]))
								{
									$phpbb_notifications->delete_subscription($type, 0, $method_data['id']);
								}
							}
						}
					}

					meta_refresh(3, $this->u_action);
					$message = $user->lang['PREFERENCES_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>');
					trigger_error($message);
				}

				$this->output_notification_methods($phpbb_notifications, $template, $user, 'notification_methods');

				$this->output_notification_types($subscriptions, $phpbb_notifications, $template, $user, $phpbb_dispatcher, 'notification_types');

				$this->tpl_name = 'ucp_notifications';
				$this->page_title = 'UCP_NOTIFICATION_OPTIONS';
			break;

			case 'notification_list':
			default:
				// Mark all items read
				if ($request->variable('mark', '') == 'all' && check_link_hash($request->variable('token', ''), 'mark_all_notifications_read'))
				{
					$phpbb_notifications->mark_notifications(false, false, $user->data['user_id'], $form_time);

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
						$phpbb_notifications->mark_notifications_by_id('notification.method.board', $mark_read, $form_time);
					}
				}

				$notifications = $phpbb_notifications->load_notifications('notification.method.board', array(
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
	* @param \phpbb\event\dispatcher_interface $phpbb_dispatcher
	* @param string $block
	*/
	public function output_notification_types($subscriptions, \phpbb\notification\manager $phpbb_notifications, \phpbb\template\template $template, \phpbb\user $user, \phpbb\event\dispatcher_interface $phpbb_dispatcher, $block = 'notification_types')
	{
		$notification_methods = $phpbb_notifications->get_subscription_methods();

		foreach ($phpbb_notifications->get_subscription_types() as $group => $subscription_types)
		{
			$template->assign_block_vars($block, array(
				'GROUP_NAME'	=> $user->lang($group),
			));

			foreach ($subscription_types as $type => $type_data)
			{
				$template->assign_block_vars($block, array(
					'TYPE'				=> $type,

					'NAME'				=> $user->lang($type_data['lang']),
					'EXPLAIN'			=> (isset($user->lang[$type_data['lang'] . '_EXPLAIN'])) ? $user->lang($type_data['lang'] . '_EXPLAIN') : '',
				));

				foreach ($notification_methods as $method => $method_data)
				{
					$tpl_ary = [
						'METHOD'			=> $method_data['id'],
						'NAME'				=> $user->lang($method_data['lang']),
						'AVAILABLE'			=> $method_data['method']->is_available($type_data['type']),
						'SUBSCRIBED'		=> (isset($subscriptions[$type]) && in_array($method_data['id'], $subscriptions[$type])) ? true : false,
					];

					/**
					* Event to perform additional actions before ucp_notifications is displayed
					*
					* @event core.ucp_notifications_output_notification_types_modify_template_vars
					* @var	array	type_data		The notification type data
					* @var	array	method_data		The notification method data
					* @var	array	tpl_ary			The template variables
					* @var	array	subscriptions	The subscriptions data
					*
					* @since 3.2.10-RC1
					* @since 3.3.1-RC1
					*/
					$vars = [
						'type_data',
						'method_data',
						'tpl_ary',
						'subscriptions',
					];
					extract($phpbb_dispatcher->trigger_event('core.ucp_notifications_output_notification_types_modify_template_vars', compact($vars)));

					$template->assign_block_vars($block . '.notification_methods', $tpl_ary);
				}
			}
		}

		$template->assign_vars(array(
			strtoupper($block) . '_COLS' => count($notification_methods) + 1,
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

		foreach ($notification_methods as $method => $method_data)
		{
			$template->assign_block_vars($block, array(
				'METHOD'			=> $method_data['id'],

				'NAME'				=> $user->lang($method_data['lang']),
			));
		}
	}
}
