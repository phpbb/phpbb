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

namespace phpbb\notification\controller;

use phpbb\controller\helper;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\notification\manager;
use phpbb\notification\method\email;
use phpbb\request\request;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets recipients of notification emails unsubscribe without logging in
 *
 * Requests act on the user the signed link was created for, not on the current
 * session. Mail clients send one-click requests (RFC 8058) without cookies, so
 * the link signature takes the place of a form key. Nothing changes on GET
 * requests, as mail scanners and link previews fetch URLs on their own.
 */
class email_unsubscribe
{
	/** @var driver_interface */
	protected $db;

	/** @var email */
	protected $email_method;

	/** @var helper */
	protected $helper;

	/** @var language */
	protected $language;

	/** @var log_interface */
	protected $log;

	/** @var manager */
	protected $manager;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string */
	protected $forums_table;

	/** @var string */
	protected $forums_watch_table;

	/** @var string */
	protected $topics_table;

	/** @var string */
	protected $topics_watch_table;

	/** @var string */
	protected $users_table;

	/**
	 * Constructor
	 *
	 * @param driver_interface $db
	 * @param email $email_method
	 * @param helper $helper
	 * @param language $language
	 * @param log_interface $log
	 * @param manager $manager
	 * @param request $request
	 * @param template $template
	 * @param user $user
	 * @param string $forums_table
	 * @param string $forums_watch_table
	 * @param string $topics_table
	 * @param string $topics_watch_table
	 * @param string $users_table
	 */
	public function __construct(driver_interface $db, email $email_method, helper $helper, language $language, log_interface $log,
								manager $manager, request $request, template $template, user $user, string $forums_table,
								string $forums_watch_table, string $topics_table, string $topics_watch_table, string $users_table)
	{
		$this->db = $db;
		$this->email_method = $email_method;
		$this->helper = $helper;
		$this->language = $language;
		$this->log = $log;
		$this->manager = $manager;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->forums_table = $forums_table;
		$this->forums_watch_table = $forums_watch_table;
		$this->topics_table = $topics_table;
		$this->topics_watch_table = $topics_watch_table;
		$this->users_table = $users_table;
	}

	/**
	 * Handle a request to the unsubscribe link
	 *
	 * @return Response
	 */
	public function handle(): Response
	{
		$this->language->add_lang('ucp');

		$params = [
			'user_id'		=> $this->request->variable('user_id', 0),
			'type'			=> $this->request->variable('type', ''),
			'watch_mode'	=> $this->request->variable('watch_mode', ''),
			'watch_id'		=> $this->request->variable('watch_id', 0),
			'time'			=> $this->request->variable('time', 0),
			'hash'			=> $this->request->variable('hash', ''),
		];

		$user_row = $this->get_user_row($params['user_id']);

		if ($user_row === null || !$this->email_method->check_unsubscribe_params($user_row, $params))
		{
			return $this->message('EMAIL_UNSUBSCRIBE_LINK_INVALID', Response::HTTP_NOT_FOUND);
		}

		if ($this->email_method->is_unsubscribe_link_expired($params))
		{
			return $this->message('EMAIL_UNSUBSCRIBE_LINK_EXPIRED', Response::HTTP_GONE);
		}

		$type_option = $this->manager->get_subscription_option($params['type']);

		$item_title = null;
		if ($this->has_watch_item($params))
		{
			$item_title = $this->get_watched_item_title($params['user_id'], $params['watch_mode'], $params['watch_id']);
		}

		if ($this->request->variable('List-Unsubscribe', '', false, request_interface::POST) === 'One-Click')
		{
			return $this->handle_one_click($user_row, $params, $type_option, $item_title);
		}

		if ($this->request->is_set_post('unsubscribe'))
		{
			$scope = $this->request->variable('scope', '', false, request_interface::POST);

			if ($this->apply_scope($scope, $user_row, $params, $type_option, $item_title))
			{
				$this->template->assign_vars(['S_EMAIL_UNSUBSCRIBE_DONE' => true]);

				return $this->render_page();
			}
		}

		$type_label = $type_option !== null && $this->language->is_set($type_option['lang']) ? $this->language->lang($type_option['lang']) : '';

		$this->template->assign_vars([
			'EMAIL_UNSUBSCRIBE_ITEM'	=> $item_title !== null ? $this->language->lang('EMAIL_UNSUBSCRIBE_SCOPE_' . strtoupper($params['watch_mode']), censor_text($item_title)) : '',
			'EMAIL_UNSUBSCRIBE_TYPE'	=> $type_option !== null ? ($type_label !== '' ? $this->language->lang('EMAIL_UNSUBSCRIBE_SCOPE_TYPE', $type_label) : $this->language->lang('EMAIL_UNSUBSCRIBE_SCOPE_TYPE_GENERIC')) : '',
			'U_EMAIL_UNSUBSCRIBE'		=> $this->helper->route('phpbb_notifications_unsubscribe', $params),
		]);

		return $this->render_page();
	}

	/**
	 * Handle a one-click request sent by a mail client
	 *
	 * Emails caused by watching a topic or forum only stop watching that item,
	 * like the stop watching link in the email does. All other emails turn off
	 * their notification type.
	 *
	 * @param array $user_row
	 * @param array $params
	 * @param array|null $type_option
	 * @param string|null $item_title
	 *
	 * @return Response
	 */
	protected function handle_one_click(array $user_row, array $params, array|null $type_option, string|null $item_title): Response
	{
		if ($this->has_watch_item($params))
		{
			// Mail clients may repeat the request, an item that is no longer watched needs no change
			if ($item_title !== null)
			{
				$this->unsubscribe_item((int) $user_row['user_id'], $params['watch_mode'], $params['watch_id']);
				$this->log_action($user_row, 'LOG_USER_EMAIL_UNSUBSCRIBE_ITEM', [$item_title]);
			}

			return $this->message('EMAIL_UNSUBSCRIBE_DONE');
		}

		if ($type_option === null)
		{
			return $this->message('EMAIL_UNSUBSCRIBE_LINK_INVALID', Response::HTTP_NOT_FOUND);
		}

		$this->unsubscribe_type((int) $user_row['user_id'], $type_option['id']);
		$this->log_action($user_row, 'LOG_USER_EMAIL_UNSUBSCRIBE_TYPE', [$type_option['id']]);

		return $this->message('EMAIL_UNSUBSCRIBE_DONE');
	}

	/**
	 * Apply the scope chosen on the unsubscribe page
	 *
	 * @param string $scope
	 * @param array $user_row
	 * @param array $params
	 * @param array|null $type_option
	 * @param string|null $item_title
	 *
	 * @return bool False if the scope is not available for this link
	 */
	protected function apply_scope(string $scope, array $user_row, array $params, array|null $type_option, string|null $item_title): bool
	{
		$user_id = (int) $user_row['user_id'];

		if ($scope === 'item' && $item_title !== null)
		{
			$this->unsubscribe_item($user_id, $params['watch_mode'], $params['watch_id']);
			$this->log_action($user_row, 'LOG_USER_EMAIL_UNSUBSCRIBE_ITEM', [$item_title]);

			return true;
		}

		if ($scope === 'type' && $type_option !== null)
		{
			$this->unsubscribe_type($user_id, $type_option['id']);
			$this->log_action($user_row, 'LOG_USER_EMAIL_UNSUBSCRIBE_TYPE', [$type_option['id']]);

			return true;
		}

		if ($scope === 'all')
		{
			$this->unsubscribe_all($user_id);
			$this->log_action($user_row, 'LOG_USER_EMAIL_UNSUBSCRIBE_ALL');

			return true;
		}

		return false;
	}

	/**
	 * Stop watching a topic or forum
	 *
	 * @param int $user_id
	 * @param string $mode	'topic' or 'forum'
	 * @param int $item_id
	 *
	 * @return void
	 */
	protected function unsubscribe_item(int $user_id, string $mode, int $item_id): void
	{
		[$table, $column] = $this->get_watch_table($mode);

		$sql = 'DELETE FROM ' . $table . '
			WHERE ' . $this->db->sql_build_array('DELETE', [
				$column		=> $item_id,
				'user_id'	=> $user_id,
			]);
		$this->db->sql_query($sql);
	}

	/**
	 * Turn off emails for one notification preference
	 *
	 * @param int $user_id
	 * @param string $option_id	Preference ID as returned by manager::get_subscription_option()
	 *
	 * @return void
	 */
	protected function unsubscribe_type(int $user_id, string $option_id): void
	{
		$this->manager->delete_subscription($option_id, 0, $this->email_method->get_type(), $user_id);
	}

	/**
	 * Turn off all notification emails and mass emails
	 *
	 * Email is not a default notification method. No rows mean no emails, so rows
	 * with notify = 0 stay effective for notification types that are added later.
	 * Topic and forum subscriptions are kept for users returning to the board.
	 *
	 * @param int $user_id
	 *
	 * @return void
	 */
	protected function unsubscribe_all(int $user_id): void
	{
		$this->manager->disable_subscriptions($user_id, $this->email_method->get_type());

		$sql = 'UPDATE ' . $this->users_table . '
			SET ' . $this->db->sql_build_array('UPDATE', ['user_allow_massemail' => 0]) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Get the title of an item if the user is watching it
	 *
	 * @param int $user_id
	 * @param string $mode	'topic' or 'forum'
	 * @param int $item_id
	 *
	 * @return string|null Title of the item, null if the user is not watching it
	 */
	protected function get_watched_item_title(int $user_id, string $mode, int $item_id): string|null
	{
		if (!$this->is_watching($user_id, $mode, $item_id))
		{
			return null;
		}

		if ($mode === 'topic')
		{
			$sql = 'SELECT topic_title AS title
				FROM ' . $this->topics_table . '
				WHERE topic_id = ' . (int) $item_id;
		}
		else
		{
			$sql = 'SELECT forum_name AS title
				FROM ' . $this->forums_table . '
				WHERE forum_id = ' . (int) $item_id;
		}

		$result = $this->db->sql_query($sql);
		$title = $this->db->sql_fetchfield('title');
		$this->db->sql_freeresult($result);

		return $title === false ? null : (string) $title;
	}

	/**
	 * Check if a user is watching an item
	 *
	 * @param int $user_id
	 * @param string $mode	'topic' or 'forum'
	 * @param int $item_id
	 *
	 * @return bool
	 */
	protected function is_watching(int $user_id, string $mode, int $item_id): bool
	{
		[$table, $column] = $this->get_watch_table($mode);

		$sql = 'SELECT user_id
			FROM ' . $table . '
			WHERE ' . $this->db->sql_build_array('SELECT', [
				$column		=> $item_id,
				'user_id'	=> $user_id,
			]);
		$result = $this->db->sql_query($sql);
		$watching = $this->db->sql_fetchfield('user_id') !== false;
		$this->db->sql_freeresult($result);

		return $watching;
	}

	/**
	 * Check if the link was created for an email caused by watching a topic or forum
	 *
	 * @param array $params
	 *
	 * @return bool
	 */
	protected function has_watch_item(array $params): bool
	{
		return $params['watch_mode'] === 'topic' || $params['watch_mode'] === 'forum';
	}

	/**
	 * Get the table and item column for a watch mode
	 *
	 * @param string $mode 'topic' or 'forum'
	 *
	 * @return array Table name and column name
	 */
	protected function get_watch_table(string $mode): array
	{
		return $mode === 'topic' ? [$this->topics_watch_table, 'topic_id'] : [$this->forums_watch_table, 'forum_id'];
	}

	/**
	 * Render the unsubscribe page
	 *
	 * @return Response
	 */
	protected function render_page(): Response
	{
		return $this->secure($this->helper->render('notification_email_unsubscribe.html', $this->language->lang('EMAIL_UNSUBSCRIBE')));
	}

	/**
	 * Get the user row an unsubscribe link was created for
	 *
	 * @param int $user_id
	 *
	 * @return array|null User row, null if the user can't have received notifications
	 */
	protected function get_user_row(int $user_id): array|null
	{
		$sql = 'SELECT user_id, user_email, user_type
			FROM ' . $this->users_table . '
			WHERE user_id = ' . (int) $user_id . '
				AND user_id <> ' . ANONYMOUS . '
				AND user_type <> ' . USER_IGNORE;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: null;
	}

	/**
	 * Add a log entry for the user the link was created for
	 *
	 * @param array $user_row
	 * @param string $operation
	 * @param array $data
	 *
	 * @return void
	 */
	protected function log_action(array $user_row, string $operation, array $data = []): void
	{
		$data = array_merge(['reportee_id' => (int) $user_row['user_id']], $data);
		$this->log->add('user', (int) $user_row['user_id'], $this->user->ip, $operation, false, $data);
	}

	/**
	 * Create a message response
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @return Response
	 */
	protected function message(string $message, int $code = Response::HTTP_OK): Response
	{
		return $this->secure($this->helper->message($message, [], 'INFORMATION', $code));
	}

	/**
	 * Keep the signed link out of caches, search engines and referrers
	 *
	 * @param Response $response
	 *
	 * @return Response
	 */
	protected function secure(Response $response): Response
	{
		$response->headers->set('Cache-Control', 'no-store, private');
		$response->headers->set('Referrer-Policy', 'no-referrer');
		$response->headers->set('X-Robots-Tag', 'noindex');

		return $response;
	}
}
