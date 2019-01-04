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

namespace phpbb\viewtopic;

use phpbb\viewtopic\exception\forum_password_required_exception;
use phpbb\viewtopic\exception\login_required_exception;
use phpbb\viewtopic\exception\permission_denied_exception;
use phpbb\viewtopic\exception\post_invisible_exception;
use phpbb\viewtopic\exception\topic_not_found_exception;

class topic_visibility_manager
{
	/**
	 * @var \phpbb\auth\auth
	 */
	protected $auth;

	/**
	 * @var \phpbb\content_visibility
	 */
	protected $content_visibility;

	/**
	 * @var \phpbb\event\dispatcher_interface
	 */
	protected $dispatcher;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/*
	 * @todo
	 */
	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\content_visibility $content_visibility,
		\phpbb\event\dispatcher_interface $dispatcher,
		\phpbb\request\request_interface $request,
		\phpbb\user $user)
	{
		$this->auth = $auth;
		$this->content_visibility = $content_visibility;
		$this->dispatcher = $dispatcher;
		$this->request = $request;
		$this->user = $user;
	}

	/*
	 * @todo
	 */
	public function check(&$topic_data, $post = false)
	{
		$this->check_topic_visibility($topic_data);
		$this->check_topic_settings($topic_data);

		if ($post)
		{
			$this->check_post_visibility($topic_data);
		}
	}

	/*
	 * @todo
	 */
	protected function check_topic_settings(&$topic_data)
	{
		$overrides_f_read_check = false;
		$overrides_forum_password_check = false;
		$topic_tracking_info = (isset($topic_data['__extra_vars']['topic_tracking_info'])) ?
			$topic_data['__extra_vars']['topic_tracking_info'] :
			null;

		/**
		 * Event to apply extra permissions and to override original phpBB's f_read permission and forum password check
		 * on viewtopic access
		 *
		 * @event core.viewtopic_before_f_read_check
		 * @var	int		forum_id						The forum id from where the topic belongs
		 * @var	int		topic_id						The id of the topic the user tries to access
		 * @var	int		post_id							The id of the post the user tries to start viewing at.
		 *												It may be 0 for none given.
		 * @var	array	topic_data						All the information from the topic and forum tables for this topic
		 * 												It includes posts information if post_id is not 0
		 * @var	bool	overrides_f_read_check			Set true to remove f_read check afterwards
		 * @var	bool	overrides_forum_password_check	Set true to remove forum_password check afterwards
		 * @var	array	topic_tracking_info				Information upon calling get_topic_tracking()
		 *												Set it to NULL to allow auto-filling later.
		 *												Set it to an array to override original data.
		 * @since 3.1.3-RC1
		 */
		$vars = array(
			'forum_id',
			'topic_id',
			'post_id',
			'topic_data',
			'overrides_f_read_check',
			'overrides_forum_password_check',
			'topic_tracking_info',
		);
		extract($this->dispatcher->trigger_event('core.viewtopic_before_f_read_check', compact($vars)));

		$topic_data['__extra_vars']['topic_tracking_info'] = $topic_tracking_info;

		// Start auth check
		if (!$overrides_f_read_check && !$this->auth->acl_get('f_read', (int) $topic_data['forum_id']))
		{
			if (((int) $this->user->data['user_id']) !== ANONYMOUS)
			{
				throw new permission_denied_exception('SORRY_AUTH_READ');
			}

			throw new login_required_exception('LOGIN_VIEWFORUM');
		}

		// Forum is passworded ... check whether access has been granted to this
		// user this session, if not show login box
		if (!$overrides_forum_password_check && $topic_data['forum_password'])
		{
			throw new forum_password_required_exception();
		}

		// Redirect to login upon emailed notification links if user is not logged in.
		if ($this->request->is_set('e', \phpbb\request\request_interface::GET) && ((int) $this->user->data['user_id']) === ANONYMOUS)
		{
			throw new login_required_exception('LOGIN_NOTIFY_TOPIC');
		}
	}

	/**
	 * Checks if the topic is visible or not.
	 *
	 * @param array $topic_data Topic data array.
	 */
	protected function check_topic_visibility($topic_data)
	{
		if (!$this->content_visibility->is_visible('topic', $topic_data['forum_id'], $topic_data))
		{
			throw new topic_not_found_exception();
		}
	}

	/**
	 * Checks if the requested post is visible for this user.
	 *
	 * @param array $topic_data Topic data array.
	 */
	protected function check_post_visibility($topic_data)
	{
		$visibility = (int) $this->topic_data['post_visibility'];

		if (($visibility === ITEM_UNAPPROVED || $visibility === ITEM_REAPPROVE) &&
			!$this->auth->acl_get('m_approve', $topic_data['forum_id']))
		{
			// Check if we can display the topic instead of the post.
			$topic_id = (int) $topic_data['topic_id'];
			if ($topic_id >= 1)
			{
				throw new post_invisible_exception($topic_id);
			}

			throw new topic_not_found_exception();
		}
	}
}
