<?php
/**
*
* @package notifications
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\notification\type;

class group_request_approved extends \phpbb\notification\type\base
{
	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'group_request_approved';
	}

	/**
	* {@inheritdoc}
	*/
	public function is_available()
	{
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public static function get_item_id($group)
	{
		return (int) $group['group_id'];
	}

	/**
	* {@inheritdoc}
	*/
	public static function get_item_parent_id($group)
	{
		return 0;
	}

	/**
	* {@inheritdoc}
	*/
	public function find_users_for_notification($group, $options = array())
	{
		$users = array();

		$group['user_ids'] = (!is_array($group['user_ids'])) ? array($group['user_ids']) : $group['user_ids'];

		foreach ($group['user_ids'] as $user_id)
		{
			$users[$user_id] = array('');
		}

		return $users;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_title()
	{
		return $this->user->lang('NOTIFICATION_GROUP_REQUEST_APPROVED', $this->get_data('group_name'));
	}

	/**
	* {@inheritdoc}
	*/
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, "mode=group&g={$this->item_id}");
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($group, $pre_create_data = array())
	{
		$this->set_data('group_name', $group['group_name']);

		return parent::create_insert_array($group, $pre_create_data);
	}

	/**
	* {@inheritdoc}
	*/
	public function users_to_query()
	{
		return array();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template()
	{
		return false;
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template_variables()
	{
		return array();
	}
}
