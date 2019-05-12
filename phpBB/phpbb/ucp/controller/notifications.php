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

namespace phpbb\mcp\controller;

class notifications
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $lang;

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

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config					Config object
	 * @param \phpbb\language\language			$lang					Language object
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
		\phpbb\language\language $lang,
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
		$this->lang					= $lang;
		$this->notification_manager	= $notification_manager;
		$this->pagination			= $pagination;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;

		$this->root_path			= $root_path;
		$this->php_ext				= $php_ext;
	}

	public function main($id, $mode)
	{
		$form_key = 'ucp_notificaiton';
		add_form_key($form_key);

		$start		= $this->request->variable('start', 0);
		$form_time	= $this->request->variable('form_time', 0);
		$form_time	= ($form_time <= 0 || $form_time > time()) ? time() : $form_time;

		switch ($mode)
		{
			case 'notification_options':
				$subscriptions = $this->notification_manager->get_global_subscriptions(false);

				// Add/remove subscriptions
				if ($this->request->is_set_post('submit'))
				{
					if (!check_form_key($form_key))
					{
						trigger_error($this->lang->lang('FORM_INVALID'));
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

					meta_refresh(3, $this->u_action);
					$message = $this->lang->lang('PREFERENCES_UPDATED') . '<br /><br />' . $this->lang->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');
					trigger_error($message);
				}

				$this->output_notification_methods('notification_methods');

				$this->output_notification_types($subscriptions, 'notification_types');

				$this->page_title = 'UCP_NOTIFICATION_OPTIONS';
				$this->tpl_name = 'ucp_notifications';
			break;

			case 'notification_list':
			default:
				// Mark all items read
				if ($this->request->variable('mark', '') === 'all' && check_link_hash($this->request->variable('token', ''), 'mark_all_notifications_read'))
				{
					$this->notification_manager->mark_notifications(false, false, $this->user->data['user_id'], $form_time);

					meta_refresh(3, $this->u_action);
					$message = $this->lang->lang('NOTIFICATIONS_MARK_ALL_READ_SUCCESS');

					if ($this->request->is_ajax())
					{
						$json_response = new \phpbb\json_response();
						$json_response->send([
							'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
							'MESSAGE_TEXT'	=> $message,
							'success'		=> true,
						]);
					}
					$message .= '<br /><br />' . $this->lang->lang('RETURN_UCP', '<a href="' . $this->u_action . '">', '</a>');

					trigger_error($message);
				}

				// Mark specific notifications read
				if ($this->request->is_set_post('submit'))
				{
					if (!check_form_key($form_key))
					{
						trigger_error('FORM_INVALID');
					}

					$mark_read = $this->request->variable('mark', [0]);

					if (!empty($mark_read))
					{
						$this->notification_manager->mark_notifications_by_id('notification.method.board', $mark_read, $form_time);
					}
				}

				$notifications = $this->notification_manager->load_notifications('notification.method.board', [
					'start'			=> $start,
					'limit'			=> $this->config['topics_per_page'],
					'count_total'	=> true,
				]);

				/** @var \phpbb\notification\type\base $notification */
				foreach ($notifications['notifications'] as $notification)
				{
					$this->template->assign_block_vars('notification_list', $notification->prepare_for_display());
				}

				$base_url = append_sid("{$this->root_path}ucp.$this->php_ext", "i=ucp_notifications&amp;mode=notification_list");
				$start = $this->pagination->validate_start($start, $this->config['topics_per_page'], $notifications['total_count']);
				$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $notifications['total_count'], $this->config['topics_per_page'], $start);

				$this->template->assign_vars([
					'TOTAL_COUNT'	=> $notifications['total_count'],
					'U_MARK_ALL'	=> $base_url . '&amp;mark=all&amp;token=' . generate_link_hash('mark_all_notifications_read'),
				]);

				$this->page_title = 'UCP_NOTIFICATION_LIST';
				$this->tpl_name = 'ucp_notifications';
			break;
		}

		$this->template->assign_vars([
			'TITLE'				=> $this->lang->lang($this->page_title),
			'TITLE_EXPLAIN'		=> $this->lang->lang($this->page_title . '_EXPLAIN'),

			'MODE'				=> $mode,
			'FORM_TIME'			=> time(),
		]);
	}

	/**
	 * Output all the notification types to the template.
	 *
	 * @param array		$subscriptions		Array containing global subscriptions
	 * @param string	$block
	 * @return void
	 */
	public function output_notification_types(array $subscriptions, $block = 'notification_types')
	{
		$notification_methods = $this->notification_manager->get_subscription_methods();

		foreach ($this->notification_manager->get_subscription_types() as $group => $subscription_types)
		{
			$this->template->assign_block_vars($block, [
				'GROUP_NAME'	=> $this->lang->lang($group),
			]);

			foreach ($subscription_types as $type => $type_data)
			{
				$this->template->assign_block_vars($block, [
					'TYPE'		=> $type,
					'NAME'		=> $this->lang->lang($type_data['lang']),
					'EXPLAIN'	=> $this->lang->is_set($type_data['lang'] . '_EXPLAIN') ? $this->lang->lang($type_data['lang'] . '_EXPLAIN') : '',
				]);

				foreach ($notification_methods as $method => $method_data)
				{
					/** @var \phpbb\notification\method\method_interface $method_type */
					$method_type = $method_data['method'];

					$this->template->assign_block_vars($block . '.notification_methods', [
						'METHOD'		=> $method_data['id'],
						'NAME'			=> $this->lang->lang($method_data['lang']),
						'AVAILABLE'		=> $method_type->is_available($type_data['type']),
						'SUBSCRIBED'	=> (isset($subscriptions[$type]) && in_array($method_data['id'], $subscriptions[$type])) ? true : false,
					]);
				}
			}
		}

		$this->template->assign_var(strtoupper($block) . '_COLS', $notification_methods + 1);
	}

	/**
	 * Output all the notification methods to the template
	 *
	 * @param string	$block		The template block name
	 * @return void
	 */
	public function output_notification_methods($block = 'notification_methods')
	{
		$notification_methods = $this->notification_manager->get_subscription_methods();

		foreach ($notification_methods as $method => $method_data)
		{
			$this->template->assign_block_vars($block, [
				'METHOD'	=> $method_data['id'],
				'NAME'		=> $this->lang->lang($method_data['lang']),
			]);
		}
	}
}
