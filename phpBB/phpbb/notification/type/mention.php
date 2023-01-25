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

namespace phpbb\notification\type;

use phpbb\textformatter\s9e\mention_helper;

/**
* Post mentioning notifications class
* This class handles notifying users when they have been mentioned in a post
*/

class mention extends post
{
	/**
	* @var mention_helper
	*/
	protected $helper;

	/**
	* {@inheritDoc}
	*/
	public function get_type()
	{
		return 'notification.type.mention';
	}

	/**
	 * {@inheritDoc}
	 */
	protected $language_key = 'NOTIFICATION_MENTION';

	/**
	 * {@inheritDoc}
	 */
	public static $notification_option = [
		'lang'	=> 'NOTIFICATION_TYPE_MENTION',
		'group'	=> 'NOTIFICATION_GROUP_POSTING',
	];

	/**
	 * {@inheritDoc}
	 */
	public function is_available()
	{
		return $this->config['allow_mentions'] && $this->auth->acl_get('u_mention');
	}

	/**
	 * {@inheritDoc}
	 */
	public function find_users_for_notification($type_data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		$user_ids = $this->helper->get_mentioned_user_ids($type_data['post_text']);

		$user_ids = array_unique($user_ids);

		$user_ids = array_diff($user_ids, [(int) $type_data['poster_id']]);

		if (empty($user_ids))
		{
			return array();
		}

		return $this->get_authorised_recipients($user_ids, $type_data['forum_id'], $options, true);
	}

	/**
	* Update a notification
	*
	* @param array $post Data specific for this type that will be updated
	* @return true
	*/
	public function update_notifications($post)
	{
		$old_notifications = $this->notification_manager->get_notified_users($this->get_type(), array(
			'item_id'	=> static::get_item_id($post),
		));

		// Find the new users to notify
		$notifications = $this->find_users_for_notification($post);

		// Find the notifications we must delete
		$remove_notifications = array_diff(array_keys($old_notifications), array_keys($notifications));

		// Find the notifications we must add
		$add_notifications = array();
		foreach (array_diff(array_keys($notifications), array_keys($old_notifications)) as $user_id)
		{
			$add_notifications[$user_id] = $notifications[$user_id];
		}

		// Add the necessary notifications
		$this->notification_manager->add_notifications_for_users($this->get_type(), $post, $add_notifications);

		// Remove the necessary notifications
		if (!empty($remove_notifications))
		{
			$this->notification_manager->delete_notifications($this->get_type(), static::get_item_id($post), false, $remove_notifications);
		}

		// return true to continue with the update code in the notifications service (this will update the rest of the notifications)
		return true;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_redirect_url()
	{
		return $this->get_url();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_email_template()
	{
		return 'mention';
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_email_template_variables()
	{
		$user_data = $this->user_loader->get_user($this->get_data('poster_id'));

		return array_merge(parent::get_email_template_variables(), array(
			'AUTHOR_NAME'		=> htmlspecialchars_decode($user_data['username']),
		));
	}

	/**
	* Set the helper service used to retrieve mentioned used
	*
	* @param mention_helper $helper
	*/
	public function set_helper(mention_helper $helper): void
	{
		$this->helper = $helper;
	}
}
