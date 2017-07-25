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

namespace phpbb\mention\helper;

use phpbb\db\driver\driver_interface;
use phpbb\notification\manager;

class mention_helper
{
	/**
	* @var driver_interface
	*/
	private $db;

	/**
	 * @var \phpbb\notification\manager
	 */
	protected $notification_manager;

	/**
	* @var Notification Details
	*/
	private $data;

	/**
	* User Mention Helper Constructor
	*
	* @param $db   					    \phpbb\db\driver\driver_interface
	* @param $notification_manager      \phpbb\notification\manager
	*
	* @return \phpbb\mention\helper\mention_helper
	*/
	public function __construct(driver_interface $db, manager $notification_manager)
	{
		$this->db = $db;
		$this->notification_manager = $notification_manager;
	}

	/**
	* Extract the mentioned users using regex exp.
	*
	* @param string    $regular_expression_match     regex exp.
	* @param string    $post_text                    Text pilled up from the board.
	*
	* @return array Consisiting of matches.
	*/
	private function get_regex_match($regular_expression_match, $post_text)
	{
		$matches = [];
		preg_match_all($regular_expression_match, $post_text, $matches, PREG_OFFSET_CAPTURE);
		return $matches;
	}

	/**
	* Extract the mentioned users using regex exp. and make list of all unique users
	* tagged.
	*
	* @param array    $matches   all matches with [mention][/mention].
	*
	* @return array List of all different users tagged in the text..
	*/
	private function get_user_list($matches)
	{
		$user_list = [];
		$regexp_matches_count = count($matches[1]);
		for ($i = 0; $i < $regexp_matches_count; $i++)
		{
			if (!in_array($matches[1][$i][0], $user_list, true))
			{
				$user_list[] = $matches[1][$i][0];
			}
		}
		return $user_list;
	}

	/**
	* Extract the mentioned users using regex exp and substitute them with
	* links to profiles of respective users.
	*
	* @param array    $matches            all the matches with regexp.
	* @param string   $post_text          post text
	* @param integer  $start_tag_length   Length of tag [mention].
	* @param integer  $end_tag_length     Length of tag [\mention].
	* @param integer  $userid_list        Ids of users tagged in the post.
	*
	* @return array Consisiting of new text and array of userid.
	*/
	private function get_regex_substituted_text($matches, $post_text, $start_tag_length, $end_tag_length, $userid_list)
	{
		$users_already_mapped = [];
		$regexp_matches_count = count($matches[1]);
		for ($i = 0; $i < $regexp_matches_count; $i++)
		{
			$username_clean = utf8_clean_string($matches[1][$i][0]);
			$startpos = $matches[1][$i][1] - $start_tag_length;
			$length = strlen($matches[1][$i][0]);
			$endpos = $matches[1][$i][1] + $length + $end_tag_length - 1;
			$userid = $userid_list[$username_clean];
			$add_url_tag = '[url=' . generate_board_url() . '/memberlist.php?mode=viewprofile&u=' . $userid . ']' . $username_clean . '[/url]';
			if ($i == 0)
			{
				$new_post_text = substr($post_text, 0, $startpos);
				$new_post_text = $new_post_text . $add_url_tag;
				$prev_end_pos = $endpos + 1;
			}
			else
			{
				$strip_mention = substr($post_text, $prev_end_pos, $startpos - $prev_end_pos);
				$new_post_text = $new_post_text . $strip_mention;
				$new_post_text = $new_post_text . $add_url_tag;
				$prev_end_pos = $endpos + 1;
			}
			if (!in_array($userid_list[$username_clean], $users_already_mapped, true))
			{
				$users_already_mapped[] = $userid_list[$username_clean];
			}
		}
		return ['post_text' => $new_post_text, 'users_mapped' => $users_already_mapped] ;
	}

	/**
	* Centralized function for calling other functions to extract users, sustitute their
	* profile links and return the new text back to posting.php.
	*
	* @param string   $post_text              post text
	*
	* @return array Array of responder data
	*/
	public function get_mentioned_users($post_text)
	{
		$regular_expression_match = '#\[mention\](.*?)\[/mention\]#';
		$matches = [];
		$matches = $this->get_regex_match($regular_expression_match, $post_text);
		if (count($matches[1]) > 0)
		{
			$start_tag_length = strlen('[mention]');
			$end_tag_length = strlen('[\mention]');
			$user_list = [];
			$userid_list = [];
			$user_list = $this->get_user_list($matches);
			if (count($user_list) > 0)
			{
				$temp_notif_type_object = $this->notification_manager->get_item_type_class('notification.type.mention');
				$userid_list = $temp_notif_type_object->find_users_for_notification($this->db, $user_list);
				if (count($userid_list) > 0)
				{
					$new_post_data = $this->get_regex_substituted_text($matches, $post_text, $start_tag_length, $end_tag_length, $userid_list);
					return ['new_post_text' => $new_post_data['post_text'], 'users_mentioned' => $new_post_data['users_mapped'], 'notif_type_object' => $temp_notif_type_object];
				}
			}
		}
		return ['new_post_text' => $post_text];
	}

	/**
	* Function to generate Notifications.
	*
	* @param $user_list    \Array           Array containing userids to send
	*                                       notifications to.
	* @param $temp_notif_type_object   \phpbb\notification\type\mention    Mention Type
	* 																	   object
	* @param $data        \Array        Notification Data Array
	*
	*/
	public function send_notifications($user_list, $temp_notif_type_object, $data)
	{
		$this->data = $data;
		$notification_method_array = [];
		$notification_details_list = $temp_notif_type_object->get_notification_type_and_method($this->db, $user_list);
		$this->notification_manager->add_notifications_for_users('notification.type.mention', $this->data, $notification_details_list);
	}

	/**
	* Function to generate list of all users eligible to be tagged in posts.
	*
	* @param $keyword    string     Extract only users whose username matches keyword.
	*
	* @return $return_usernames_userid   array   Array containing user details matching
	*                                            the keyword.
	*
	*/
	public function get_allusers($keyword)
	{
		$sql_query = 'SELECT user_id, username
					  FROM ' . USERS_TABLE . '
					  WHERE user_id <> ' . ANONYMOUS . '
						AND ' . $this->db->sql_in_set('user_type', [USER_NORMAL, USER_FOUNDER]) .  '
						AND username_clean ' . $this->db->sql_like_expression($keyword . $this->db->get_any_char());
		$result = $this->db->sql_query($sql_query);
		$return_usernames_userid = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$return_usernames_userid[] = [
				'name'  => $row['username'],
				'id'    => $row['user_id'],
			];
		}
		$this->db->sql_freeresult($result);
		return $return_usernames_userid;
	}
}
