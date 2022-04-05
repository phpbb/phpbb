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

/**
* Admin activation notifications class
* This class handles notifications for users requiring admin activation
*/

class admin_activate_user extends \phpbb\notification\type\base
{
	/**
	* {@inheritdoc}
	*/
	public function get_type()
	{
		return 'notification.type.admin_activate_user';
	}

	/**
	* {@inheritdoc}
	*/
	protected $language_key = 'NOTIFICATION_ADMIN_ACTIVATE_USER';

	/**
	* {@inheritdoc}
	*/
	static public $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_ADMIN_ACTIVATE_USER',
		'group'	=> 'NOTIFICATION_GROUP_ADMINISTRATION',
	);

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\config\config */
	protected $config;

	public function set_config(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	* {@inheritdoc}
	*/
	public function is_available()
	{
		return ($this->auth->acl_get('a_user') && $this->config['require_activation'] == USER_ACTIVATION_ADMIN);
	}

	/**
	* {@inheritdoc}
	*/
	static public function get_item_id($user)
	{
		return (int) $user['user_id'];
	}

	/**
	* {@inheritdoc}
	*/
	static public function get_item_parent_id($post)
	{
		return 0;
	}

	/**
	* {@inheritdoc}
	*/
	public function find_users_for_notification($user, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'	=> array(),
		), $options);

		// Grab admins that have permission to administer users.
		$admin_ary = $this->auth->acl_get_list(false, 'a_user', false);
		$users = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

		// Grab founders
		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE user_type = ' . USER_FOUNDER;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$users[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		if (empty($users))
		{
			return array();
		}
		$users = array_unique($users);

		return $this->check_user_notification_options($users, $options);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->item_id, false, true);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->item_id, 'no_profile');

		return $this->language->lang($this->language_key, $username);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template()
	{
		return 'admin_activate';
	}

	/**
	* {@inheritdoc}
	*/
	public function get_email_template_variables()
	{
		$board_url = generate_board_url();
		$username = $this->user_loader->get_username($this->item_id, 'username');

		return array(
			'USERNAME'			=> html_entity_decode($username, ENT_COMPAT),
			'U_USER_DETAILS'	=> "{$board_url}/memberlist.{$this->php_ext}?mode=viewprofile&u={$this->item_id}",
			'U_ACTIVATE'		=> "{$board_url}/ucp.{$this->php_ext}?mode=activate&u={$this->item_id}&k={$this->get_data('user_actkey')}",
		);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_url()
	{
		return $this->user_loader->get_username($this->item_id, 'profile');
	}

	/**
	* {@inheritdoc}
	*/
	public function users_to_query()
	{
		return array($this->item_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function create_insert_array($user, $pre_create_data = array())
	{
		$this->set_data('user_actkey', $user['user_actkey']);
		$this->notification_time = $user['user_regdate'];

		parent::create_insert_array($user, $pre_create_data);
	}
}
