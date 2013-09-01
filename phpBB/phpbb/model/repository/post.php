<?php
/**
 *
 * @package api
 * @copyright (c) 2013 phpBB Group
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
 * Topic repository
 * @package phpBB3
 */
class phpbb_model_repository_post
{
	/** @var phpbb_auth */
	protected $auth;

	/**
	 * phpBB configuration
	 * @var phpbb_config
	 */
	protected $config;

	/** @var phpbb_db_driver */
	protected $db;

	/** @var phpbb_user */
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
	 * @param phpbb_auth $auth
	 * @param phpbb_config $config
	 * @param phpbb_db_driver $db
	 * @param phpbb_user $user
	 * @param $phpbb_root_path
	 * @param $php_ext
	 */
	function __construct(phpbb_auth $auth, phpbb_config $config, phpbb_db_driver $db, phpbb_user $user,
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

		if (!function_exists('submit_post'))
		{
			include($this->phpbb_root_path . 'includes/functions_posting.' . $this->php_ext);
		}

		submit_post(($data['topic_id'] == 0) ? 'post' : 'reply', $data['topic_title'], $data['username'], $data['topic_type'], $poll, $data);

		$this->user->data = $old_user_data;

		return $data;
	}

}
