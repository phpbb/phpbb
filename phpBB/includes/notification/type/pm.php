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
class phpbb_notification_type_pm extends phpbb_notification_type_base
{
	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_PM',
	);

	/**
	* Get the id of the
	*
	* @param array $pm The data from the private message
	*/
	public static function get_item_id($pm)
	{
		return (int) $pm['msg_id'];
	}

	/**
	* Get the id of the parent
	*
	* @param array $pm The data from the pm
	*/
	public static function get_item_parent_id($pm)
	{
		// No parent
		return 0;
	}

	/**
	* Find the users who want to receive notifications
	*
	* @param array $pm Data from
	*
	* @return array
	*/
	public function find_users_for_notification($pm, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=> array(),
		), $options);

		if (!sizeof($pm['recipients']))
		{
			return array();
		}

		$this->notification_manager->load_users(array_keys($pm['recipients']));

		$notify_users = array();

		$sql = 'SELECT *
			FROM ' . USER_NOTIFICATIONS_TABLE . "
			WHERE item_type = '" . get_class($this) . "'
				AND " . $this->db->sql_in_set('user_id', array_keys($pm['recipients'])) . '
				AND user_id <> ' . $pm['from_user_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (isset($options['ignore_users'][$row['user_id']]) && in_array($row['method'], $options['ignore_users'][$row['user_id']]))
			{
				continue;
			}

			if (!isset($rowset[$row['user_id']]))
			{
				$notify_users[$row['user_id']] = array();
			}

			$notify_users[$row['user_id']][] = $row['method'];
		}
		$this->db->sql_freeresult($result);

		return $notify_users;
	}

	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->_get_avatar($this->get_data('from_user_id'));
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		$user_data = $this->notification_manager->get_user($this->get_data('from_user_id'));

		$username = get_username_string('no_profile', $user_data['user_id'], $user_data['username'], $user_data['user_colour']);

		return $this->user->lang('NOTIFICATION_PM', $username, $this->get_data('message_subject'));
	}

	/**
	* Get email template
	*
	* @return string|bool
	*/
	public function get_email_template()
	{
		return 'privmsg_notify';
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		$user_data = $this->notification_manager->get_user($this->get_data('from_user_id'));

		return array(
			'AUTHOR_NAME'				=> htmlspecialchars_decode($user_data['username']),
			'SUBJECT'					=> htmlspecialchars_decode(censor_text($this->get_data('message_subject'))),

			'U_VIEW_MESSAGE'			=> generate_board_url() . '/ucp.' . $this->php_ext . "?i=pm&mode=view&p={$this->item_id}",
		);
	}

	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'ucp.' . $this->php_ext, "i=pm&amp;mode=view&amp;p={$this->item_id}");
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->data['from_user_id']);
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $post Data from submit_post
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($pm, $pre_create_data = array())
	{
		$this->set_data('from_user_id', $pm['from_user_id']);

		$this->set_data('message_subject', $pm['message_subject']);

		return parent::create_insert_array($pm, $pre_create_data);
	}
}
