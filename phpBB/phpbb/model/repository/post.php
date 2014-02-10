<?php
/**
 *
 * @package api
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbb\model\repository;

/**
 * @ignore
 */
use phpbb\model\exception\no_permission_exception;

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Topic repository
 * @package phpBB3
 */
class post
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/**
	 * phpBB configuration
	 * @var \phpbb\config\config
	 */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/**
	 * Root path.
	 *
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * PHP extension.
	 *
	 * @var string
	 */
	protected $php_ext;


	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\config\config $config
	 * @param \phpbb\db\driver\driver $db
	 * @param \phpbb\user $user
	 * @param $phpbb_root_path
	 * @param $php_ext
	 */
	function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver $db, \phpbb\user $user,
						 $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function new_post($data, $user_id)
	{
		$sql = 'SELECT username, user_colour, user_permissions, user_type
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$user_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$user_data)
		{
			return false;
		}

		$this->auth->acl($user_data);

		if ($data['topic_id'] == 0)
		{
			if (!$this->auth->acl_get('f_post', $data['forum_id']))
			{
				throw new no_permission_exception('User has no permission to create a topic in this forum', 403);
			}
		}
		else
		{
			if (!$this->auth->acl_get('f_reply', $data['forum_id']))
			{
				throw new no_permission_exception('User has no permission to reply to this post', 403);
			}
		}

		$old_user_data = $this->user->data;
		$this->user->data['user_id'] = $user_id;
		$this->user->data['username'] = $user_data['username'];
		$this->user->data['user_colour'] = $user_data['user_colour'];
		$this->user->data['user_permissions'] = $user_data['user_permissions'];
		$this->user->data['user_type'] = $user_data['user_type'];

		$subject = utf8_normalize_nfc($data['topic_title']);
		$message = utf8_normalize_nfc($data['message']);
		$uid = $bitfield = $options = '';
		generate_text_for_storage($subject, $uid, $bitfield, $options, false, false, false);
		generate_text_for_storage($message, $uid, $bitfield, $options, $data['enable_bbcode'], $data['enable_urls'], $data['enable_smilies']);

		$data['message'] = $message;
		$data['message_md5'] = md5($message);
		$data['bbcode_bitfield'] = $bitfield;
		$data['bbcode_uid'] = $uid;
		$data['topic_title'] = $subject;
		$data['post_edit_locked'] = false;
		$data['notify_set'] = true;
		$data['enable_indexing'] = true;
		$data['api'] = true;

		if (!function_exists('submit_post'))
		{
			include($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
		}

		submit_post(($data['topic_id'] == 0) ? 'post' : 'reply', $data['topic_title'], $data['username'], $data['topic_type'], $poll, $data);

		$this->user->data = $old_user_data;

		return $data;
	}

}
