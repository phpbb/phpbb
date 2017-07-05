<?php
/**
 *
 * phpBB mentions. A feature for the phpBB Forum Software package.
 *
 * @copyright (c) 2016, paul999, https://www.phpbbextensions.io
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\mention\helper;
use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use Symfony\Component\HttpFoundation\JsonResponse;
use phpbb\controller\helper;


class mention_helper
{
	/**
	* @var driver_interface
	*/
	private $db;

	/**
	* @var Notification Details
	*/
	private $data;

	public function __construct(driver_interface $db)
	{
		$this->db = $db;
	}

	/**
	* Function to extract the mentioned users using regex exp.
	*
	* @param string $$regular_expression_match- regex exp.
	* @param string $post_text - Text pilled up from the board.
	* @return array Consisiting of matches.
	*/
	public function get_regex_match($regular_expression_match, $post_text) {
		$matches = false;
		preg_match_all($regular_expression_match, $post_text, $matches, PREG_OFFSET_CAPTURE);
		return $matches;
	}

	/**
	* Function to extract the mentioned users using regex exp.
	*
	* @param array $matches- all matches with [mention][/mention].
	* @return array List of all different users tagged in the text..
	*/
	public function get_user_list($matches) {
		$user_list = array();
		// get list of all different users tagged.
		for ($i = 0, $len = count($matches[1]); $i < $len; $i++)
		{
			if (!in_array($matches[1][$i][0], $user_list, true))
			{

				array_push($user_list, $matches[1][$i][0]);
			}
		}
		return $user_list;
	}

	/**
	* Function to extract the mentioned users using regex exp.
	*
	* @param array $matches- all the matches with regexp.
	* @param integer $start_tag_length - Length of tag [mention].
	* @param integer $end_tag_length - Length of tag [\mention].
	* @param integer $userid_list- Ids of users tagged in the post.
	* @return array Consisiting of new text and array of userid.
	*/
	public function get_regex_substituted_text($matches, $post_text, $start_tag_length, $end_tag_length, $userid_list) {

		$users_already_mapped = array();
		//Iterate over all [mention][/mention] matches.
		for ($i = 0, $len = count($matches[1]); $i < $len; $i++)
		{
			$username_clean = utf8_clean_string($matches[1][$i][0]);
			//Inititalize all variables like length of usernames and link to all users.
			$startpos = $matches[1][$i][1] - $start_tag_length;
			$length = strlen($matches[1][$i][0]);
			$endpos = $matches[1][$i][1] + $length + $end_tag_length - 1;
			$userid = $userid_list[$username_clean];
			$add_url_tag = "[url=" . generate_board_url() . "/memberlist.php?mode=viewprofile&u=" . $userid . "]" . $username_clean . "[/url]";
			//Strip text by [mention][/mention] and then append $add_url_tag to the stripped text .
			if ($i==0)
			{
				$new_post_text = substr($post_text, 0, $startpos);
				$new_post_text = $new_post_text . $add_url_tag;
				$prev_end_pos = $endpos + 1;
			} else
			{
				$strip_mention = substr($post_text, $prev_end_pos, $startpos - $prev_end_pos);
				$new_post_text = $new_post_text . $strip_mention;
				$new_post_text = $new_post_text . $add_url_tag;
				$prev_end_pos = $endpos + 1;
			}
			//maintain al list of all different userids.
			if (!in_array($userid_list[$username_clean], $users_already_mapped, true))
			{

				array_push($users_already_mapped, $userid_list[$username_clean]);
			}
		}
		return array("post_text" => $new_post_text, "users_mapped" => $users_already_mapped) ;
	}

	/**
	* Function to extract the mentioned users and replace them with links to the user
	* profile
	*
	* @param string $post- post text
	* @param array $data - Notification details
	* @param Notification Manager object $notif_manager_obj
	* @return array Array of responder data
	*/
	public function get_mentioned_users($post_text, $data = array(), $notif_manager_obj)
	{
		$this->data = $data;
		$regular_expression_match = '#\[mention\](.*?)\[/mention\]#';
		$matches = false;
		$matches = $this->get_regex_match($regular_expression_match, $post_text);
		$start_tag_length = strlen("[mention]");
		$end_tag_length = strlen("[\mention]");
		$user_list = array();
		$userid_list = array();
		$user_list = $this->get_user_list($matches);
		$temp_notif_type_object = $notif_manager_obj->get_item_type_class("notification.type.mention");
		$userid_list = $temp_notif_type_object->find_users_for_notification($this->db, $user_list);
		$new_post_data = $this->get_regex_substituted_text($matches, $post_text, $start_tag_length, $end_tag_length, $userid_list);

		return array("new_post_text" => $new_post_data["post_text"], "users_mentioned" => $new_post_data["users_mapped"], "notif_type_object" => $temp_notif_type_object);
	}


	/**
	* Function to generate Notifications.
	* @param array $user_list - Array containing userids to send notifications to.
	* @param Notification Manager object $notif_manager_obj
	* @param Temporary notiifcation type object to call the method from new
	* notiifcation type - mention.
	*/
	public function send_notifications($user_list, $notif_manager_obj, $temp_notif_type_object)
	{
		//get notification type and method for mentioned userids.
		$notification_method_array = array();
		$notification_details_list = $temp_notif_type_object->get_notification_type_and_method($this->db, $user_list, $notif_manager_obj);
		$user_index = 0;

		//add notification details to queue and send notifications one by one.
		foreach ($notification_details_list as $details)
		{
			$notification_type = $details["notif_type"];
			$notification_method = $details["notif_method"];
			$notification_type->user_id = $details["user_id"];
			$notification_type->create_insert_array($this->data);
			$notification_method->add_to_queue($notification_type);
			array_push($notification_method_array, $notification_method);
		}
		foreach ($notification_method_array as $method)
		{
			$method->notify();
		}
	}

}