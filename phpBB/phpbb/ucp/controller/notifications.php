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

namespace phpbb\ucp\controller;

class notifications
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\controller\helper			$helper					Controller helper object
	 * @param \phpbb\language\language			$language				Language object
	 * @param \phpbb\notification\manager		$notification_manager	Notification manager object
	 * @param \phpbb\pagination					$pagination				Pagination object
	 * @param \phpbb\request\request			$request				Request object
	 * @param \phpbb\template\template			$template				Template object
	 * @param \phpbb\user						$user					User object
	 * @param string							$root_path				phpBB root path
	 * @param string							$php_ext				php File extension
	 */
	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\language\language $language,
		\phpbb\notification\manager $notification_manager,
		\phpbb\pagination $pagination,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$root_path,
		$php_ext
	)
	{
		$this->config				= $config;
		$this->helper				= $helper;
		$this->language				= $language;
		$this->notification_manager	= $notification_manager;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
	}

	/**
	 * Display and handle the notifications modes.
	 *
	 * @param string	$mode		The notifications mode (manage|settings)
	 * @param int		$page		The page number
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function main($mode, $page = 1)
	{
		$form_key = 'ucp_notification';
		add_form_key($form_key);

		$limit		= (int) $this->config['topics_per_page'];
		$start		= ($page - 1) * $limit;

		$form_time = $this->request->variable('form_time', 0);
		$form_time = $form_time <= 0 || $form_time > time() ? time() : $form_time;

		$route	= $mode === 'settings' ? 'ucp_settings_notifications' : 'ucp_manage_notifications';
		$return	= '<br /><br />' . $this->language->lang('RETURN_UCP', '<a href="' . $this->helper->route($route) . '">', '</a>');

		switch ($mode)
		{
			case 'settings':
				$subscriptions = $this->notification_manager->get_global_subscriptions(false);

				// Add/remove subscriptions
				if ($this->request->is_set_post('submit'))
				{
					if (!check_form_key($form_key))
					{
						return trigger_error($this->language->lang('FORM_INVALID') . $return, E_USER_WARNING);
					}

					$notification_methods = $this->notification_manager->get_subscription_methods();

					foreach ($this->notification_manager->get_subscription_types() as $group => $subscription_types)
					{
						foreach ($subscription_types as $type => $data)
						{
							foreach ($notification_methods as $method => $method_data)
							{
								if ($this->request->is_set_post(str_replace('.', '_', $type . '_' . $method_data['id'])) && (!isset($subscriptions[$type]) || !in_array($method_data['id'], $subscriptions[$type])))
								{
									$this->notification_manager->add_subscription($type, 0, $method_data['id']);
								}
								else if (!$this->request->is_set_post(str_replace('.', '_', $type . '_' . $method_data['id'])) && isset($subscriptions[$type]) && in_array($method_data['id'], $subscriptions[$type]))
								{
									$this->notification_manager->delete_subscription($type, 0, $method_data['id']);
								}
							}
						}
					}

					$this->helper->assign_meta_refresh_var(3, $this->helper->route($route));

					return $this->helper->message($this->language->lang('PREFERENCES_UPDATED') . $return);
				}

				$this->output_notification_methods('notification_methods');

				$this->output_notification_types($subscriptions, 'notification_types');
			break;

			default:
			case 'manage':
				// Mark all items read
				if ($this->request->variable('mark', '') === 'all' && check_link_hash($this->request->variable('token', ''), 'mark_all_notifications_read'))
				{
					$this->notification_manager->mark_notifications(false, false, $this->user->data['user_id'], $form_time);

					$message = $this->language->lang('NOTIFICATIONS_MARK_ALL_READ_SUCCESS');

					if ($this->request->is_ajax())
					{
						$json_response = new \phpbb\json_response();
						$json_response->send([
							'MESSAGE_TITLE'	=> $this->language->lang('INFORMATION'),
							'MESSAGE_TEXT'	=> $message,
							'success'		=> true,
						]);
					}

					$this->helper->assign_meta_refresh_var(3, $this->helper->route($route));

					return $this->helper->message($message . $return);
				}

				// Mark specific notifications read
				if ($this->request->is_set_post('submit'))
				{
					if (!check_form_key($form_key))
					{
						return trigger_error($this->language->lang('FORM_INVALID') . $return, E_USER_WARNING);
					}

					$mark_read = $this->request->variable('mark', [0]);

					if (!empty($mark_read))
					{
						$this->notification_manager->mark_notifications_by_id('notification.method.board', $mark_read, $form_time);
					}
				}

				$notifications = $this->notification_manager->load_notifications('notification.method.board', [
					'start'			=> $start,
					'limit'			=> $limit,
					'count_total'	=> true,
				]);

				/** @var \phpbb\notification\type\base $notification */
				foreach ($notifications['notifications'] as $notification)
				{
					$this->template->assign_block_vars('notification_list', $notification->prepare_for_display());
				}

				$start = $this->pagination->validate_start($start, $limit, $notifications['total_count']);
				$this->pagination->generate_template_pagination([
					'routes' => ['ucp_manage_notifications', 'ucp_manage_notifications_pagination'],
				], 'pagination', 'page', $notifications['total_count'], $limit, $start);

				$this->template->assign_vars([
					'TOTAL_COUNT'	=> $notifications['total_count'],
					'U_MARK_ALL'	=> $this->helper->route('ucp_manage_notifications', ['mark' => 'all', 'token' => generate_link_hash('mark_all_notifications_read')]),
				]);
			break;
		}

		$l_mode = $this->language->lang('UCP_' . utf8_strtoupper($mode) . '_NOTIFICATIONS');
		$s_mode = $mode === 'settings' ? 'notification_options' : 'notification_list';

		$this->template->assign_vars([
			'TITLE'				=> $l_mode,
			'TITLE_EXPLAIN'		=> $this->language->lang('UCP_' . utf8_strtoupper($s_mode) . '_EXPLAIN'),

			'MODE'				=> $s_mode,
			'FORM_TIME'			=> time(),
		]);

		return $this->helper->render('ucp_notifications.html', $l_mode);
	}

	/**
	 * Output all the notification types to the template
	 *
	 * @param array		$subscriptions	Array containing global subscriptions
	 * @param string	$block			The template block name
	 * @return void
	 */
	public function output_notification_types(array $subscriptions, $block = 'notification_types')
	{
		$notification_methods = $this->notification_manager->get_subscription_methods();

		foreach ($this->notification_manager->get_subscription_types() as $group => $subscription_types)
		{
			$this->template->assign_block_vars($block, ['GROUP_NAME' => $this->language->lang($group)]);

			foreach ($subscription_types as $type => $type_data)
			{
				$this->template->assign_block_vars($block, [
					'TYPE'				=> $type,
					'NAME'				=> $this->language->lang($type_data['lang']),
					'EXPLAIN'			=> $this->language->is_set($type_data['lang'] . '_EXPLAIN') ? $this->language->lang($type_data['lang'] . '_EXPLAIN') : '',
				]);

				foreach ($notification_methods as $method => $method_data)
				{
					/** @var \phpbb\notification\method\method_interface $method_type */
					$method_type = $method_data['method'];

					$this->template->assign_block_vars($block . '.notification_methods', [
						'METHOD'			=> $method_data['id'],
						'NAME'				=> $this->language->lang($method_data['lang']),
						'AVAILABLE'			=> $method_type->is_available($type_data['type']),
						'SUBSCRIBED'		=> isset($subscriptions[$type]) && in_array($method_data['id'], $subscriptions[$type]),
					]);
				}
			}
		}

		$this->template->assign_var(strtoupper($block) . '_COLS', count($notification_methods) + 1);
	}

	/**
	 * Output all the notification methods to the template
	 *
	 * @param string	$block			The template block name
	 * @return void
	 */
	public function output_notification_methods($block = 'notification_methods')
	{
		$notification_methods = $this->notification_manager->get_subscription_methods();

		foreach ($notification_methods as $method => $method_data)
		{
			$this->template->assign_block_vars($block, [
				'METHOD'	=> $method_data['id'],
				'NAME'		=> $this->language->lang($method_data['lang']),
			]);
		}
	}
}
