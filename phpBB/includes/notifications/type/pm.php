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

/**
* Private message notifications class
* This class handles notifications for private messages
*
* @package notifications
*/
class phpbb_notifications_type_pm extends phpbb_notifications_type_base
{
	/**
	* Get the type of notification this is
	* phpbb_notifications_type_
	*/
	public static function get_item_type()
	{
		return 'pm';
	}

	/**
	* Get the id of the
	*
	* @param array $pm The data from the private message
	*/
	public static function get_item_id($pm)
	{
		return $pm['msg_id'];
	}

	/**
	* Get the title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		$user_data = $this->get_user($this->get_data('author_id'));

		$username = get_username_string('no_profile', $user_data['user_id'], $user_data['username'], $user_data['user_colour']);

		return $username . ' sent you a private message titled: ' . $this->get_data('message_subject');
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'ucp.' . $this->php_ext, "i=pm&amp;mode=view&p={$this->item_id}");
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->data['author_id']);
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $post Data from submit_post
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($pm)
	{
		$this->item_id = $pm['msg_id'];

		$this->set_data('author_id', $pm['author_id']);

		$this->set_data('message_subject', $pm['message_subject']);

		return parent::create_insert_array($pm);
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $pm Data from
	* @return array
	*/
	public function find_users_for_notification($pm)
	{
		$user = $this->phpbb_container->get('user');

		// Exclude guests, current user and banned users from notifications
		unset($pm['recipients'][ANONYMOUS], $pm['recipients'][$user->data['user_id']]);

		if (!sizeof($pm['recipients']))
		{
			return;
		}

		if (!function_exists('phpbb_get_banned_user_ids'))
		{
			include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
		}
		$banned_users = phpbb_get_banned_user_ids(array_keys($pm['recipients']));
		$pm['recipients'] = array_diff(array_keys($pm['recipients']), $banned_users);

		if (!sizeof($pm['recipients']))
		{
			return;
		}

		$sql = 'SELECT user_id, user_notify_pm, user_notify_type
			FROM ' . USERS_TABLE . '
			WHERE ' . $db->sql_in_set('user_id', $pm['recipients']);
		$result = $db->sql_query($sql);

		$pm['recipients'] = array();

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['user_notify_pm'])
			{
				$pm['recipients'][$row['user_id']] = array();

				if ($row['user_notify_type'] == NOTIFY_EMAIL || $row['user_notify_type'] == NOTIFY_BOTH)
				{
					$pm['recipients'][$row['user_id']][] = 'email';
				}

				if ($row['user_notify_type'] == NOTIFY_IM || $row['user_notify_type'] == NOTIFY_BOTH)
				{
					$pm['recipients'][$row['user_id']][] = 'jabber';
				}
			}
		}
		$db->sql_freeresult($result);

		return $pm['recipients'];
	}
}
