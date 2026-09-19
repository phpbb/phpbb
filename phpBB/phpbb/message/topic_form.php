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

namespace phpbb\message;

use phpbb\auth\auth;
use phpbb\config\config;
use phpbb\content_visibility;
use phpbb\db\driver\driver_interface;
use phpbb\user;

/**
* Class topic_form
* Form used to send topics as notification emails
*/
class topic_form extends form
{
	/** @var content_visibility */
	protected $content_visibility;

	/** @var int */
	protected $topic_id;
	/** @var array */
	protected $topic_row;
	/** @var string */
	protected $recipient_address;
	/** @var string */
	protected $recipient_name;
	/** @var string */
	protected $recipient_lang;

	/**
	 * Construct
	 *
	 * @param auth $auth
	 * @param config $config
	 * @param content_visibility $content_visibility
	 * @param driver_interface $db
	 * @param user $user
	 * @param string $phpbb_root_path
	 * @param string $phpEx
	 */
	public function __construct(auth $auth, config $config, content_visibility $content_visibility, driver_interface $db, user $user, $phpbb_root_path, $phpEx)
	{
		parent::__construct($auth, $config, $db, $user, $phpbb_root_path, $phpEx);
		$this->content_visibility = $content_visibility;
	}

	/**
	* Get the data of the topic
	*
	* @param int $topic_id
	* @return	false|array		false if the topic does not exist, array otherwise
	*/
	protected function get_topic_row($topic_id)
	{
		$sql = 'SELECT forum_id, topic_title, topic_poster, topic_visibility
			FROM ' . TOPICS_TABLE . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $this->content_visibility->is_visible('topic', $row['forum_id'], $row) ? $row : [];
	}

	/**
	* {inheritDoc}
	*/
	public function check_allow()
	{
		$error = parent::check_allow();
		if ($error)
		{
			return $error;
		}

		if (!$this->auth->acl_get('u_sendemail'))
		{
			return 'NO_EMAIL';
		}

		if (!$this->topic_row)
		{
			return 'NO_TOPIC';
		}

		if (!$this->auth->acl_get('f_read', $this->topic_row['forum_id']))
		{
			if ($this->user->data['user_id'] != ANONYMOUS)
			{
				send_status_line(403, 'Forbidden');
			}
			else
			{
				send_status_line(401, 'Unauthorized');
			}
			return 'SORRY_AUTH_READ';
		}

		if (!$this->auth->acl_get('f_email', $this->topic_row['forum_id']))
		{
			return 'NO_EMAIL';
		}

		return false;
	}

	/**
	* {inheritDoc}
	*/
	public function bind(\phpbb\request\request_interface $request)
	{
		parent::bind($request);

		$this->topic_id = $request->variable('t', 0);
		$this->recipient_address = $request->variable('email', '');
		$this->recipient_name = $request->variable('name', '', true);
		$this->recipient_lang = $request->variable('lang', $this->config['default_lang']);

		$this->topic_row = $this->get_topic_row($this->topic_id);
	}

	/**
	* {inheritDoc}
	*/
	public function submit(\phpbb\di\service_collection $messenger)
	{
		if (!$this->recipient_address || !preg_match('/^' . get_preg_expression('email') . '$/i', $this->recipient_address))
		{
			$this->errors[] = $this->user->lang['EMPTY_ADDRESS_EMAIL'];
		}

		if (!$this->recipient_name)
		{
			$this->errors[] = $this->user->lang['EMPTY_NAME_EMAIL'];
		}

		$this->message->set_template('email_notify');
		$this->message->set_template_vars(array(
			'TOPIC_NAME'	=> html_entity_decode($this->topic_row['topic_title'], ENT_COMPAT),
			'U_TOPIC'		=> generate_board_url() . '/viewtopic.' . $this->phpEx . '?t=' . $this->topic_id,
		));
		$this->message->set_body($this->body);
		$this->message->add_recipient(
			$this->recipient_name,
			$this->recipient_address,
			$this->recipient_lang
		);

		parent::submit($messenger);
	}

	/**
	* {inheritDoc}
	*/
	public function get_return_message()
	{
		return sprintf($this->user->lang['RETURN_TOPIC'],  '<a href="' . append_sid($this->phpbb_root_path . 'viewtopic.' . $this->phpEx, 't=' . $this->topic_id) . '">', '</a>');
	}

	/**
	* {inheritDoc}
	*/
	public function render(\phpbb\template\template $template)
	{
		parent::render($template);

		$this->user->add_lang('viewtopic');

		$lang_options = phpbb_language_select($this->db, $this->recipient_lang);

		$template->assign_vars(array(
			'EMAIL'				=> $this->recipient_address,
			'NAME'				=> $this->recipient_name,
			'LANG_OPTIONS'		=> [
				'id'		=> 'lang',
				'name'		=> 'lang',
				'options'	=> $lang_options,
			],
			'MESSAGE'			=> $this->body,

			'L_EMAIL_BODY_EXPLAIN'	=> $this->user->lang['EMAIL_TOPIC_EXPLAIN'],
			'S_POST_ACTION'			=> append_sid($this->phpbb_root_path . 'memberlist.' . $this->phpEx, 'mode=email&amp;t=' . $this->topic_id))
		);
	}
}
