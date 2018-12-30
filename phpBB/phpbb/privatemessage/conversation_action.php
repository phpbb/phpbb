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

namespace phpbb\privatemessage;

class conversation_action
{
	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	 * @var \phpbb\request\request
	 */
	protected $request;

	/**
	 * @var string
	 */
	protected $privmsgs_table;

	/**
	 * @var string
	 */
	protected $privmsgs_to_table;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, $privmsgs_table, $privmsgs_to_table)
	{
		$this->helper = $helper;
		$this->user = $user;
		$this->db = $db;
		$this->request = $request;
		$this->privmsgs_table = $privmsgs_table;
		$this->privmsgs_to_table = $privmsgs_to_table;
	}

	public function important($id)
	{
		// toggle marked flag
		$sql = 'UPDATE ' . $this->privmsgs_to_table . '
			SET pm_marked = 1 - pm_marked
			WHERE msg_id = ' . (int) $id . '
				AND user_id = ' . (int) $this->user->data['user_id'];
		$this->db->sql_query($sql);

		return new \Symfony\Component\HttpFoundation\RedirectResponse($this->helper->route('phpbb_privatemessage_conversation', array('id' => $id)));
	}

	public function edit_title($id)
	{
		$new_title = $this->request->variable('new_title', '', true);

		$sql = 'UPDATE ' . $this->privmsgs_table . "
			SET message_subject = '" . $this->db->sql_escape($new_title) . "'
			WHERE msg_id = " . (int) $id;
		$this->db->sql_query($sql);

		return new \Symfony\Component\HttpFoundation\RedirectResponse($this->helper->route('phpbb_privatemessage_conversation', array('id' => $id)));
	}

	public function delete($id)
	{
		// TODO: big todo here - sync user unread messages

		if (confirm_box(true))
		{
			// get IDs of all messages in the conversation
			$sql = 'SELECT msg_id
				FROM ' . $this->privmsgs_table . '
				WHERE msg_id = ' . (int) $id . '
					OR root_level = ' . (int) $id;
			$result = $this->db->sql_query($sql);
			$msg_ids = array_column($this->db->sql_fetchrowset($result), 'msg_id');
			$this->db->sql_freeresult($result);

			// delete the user messages
			$sql = 'DELETE FROM ' . $this->privmsgs_to_table . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . '
				AND ' . $this->db->sql_in_set('msg_id', $msg_ids);
			$this->db->sql_query($sql);

			// check if there are any messages without user to read it
			$sql = 'SELECT pt.msg_id
				FROM (
					SELECT msg_id, COUNT(msg_id) as count
					FROM ' . $this->privmsgs_to_table . '
					WHERE ' . $this->db->sql_in_set('msg_id', $msg_ids) . '
					GROUP BY msg_id
				) pt
				WHERE pt.count > 0';
			$result = $this->db->sql_query($sql);
			$accessible_msg_ids = array_column($this->db->sql_fetchrowset($result), 'msg_id');
			$this->db->sql_freeresult($result);

			// delete messages that aren't used by anyone else
			$dead_msg_ids = array_diff($msg_ids, $accessible_msg_ids);
			if (!empty($dead_msg_ids))
			{
				$sql = 'DELETE FROM ' . $this->privmsgs_table . '
					WHERE ' . $this->db->sql_in_set('msg_id', $dead_msg_ids);
				$this->db->sql_query($sql);
			}
		}
		else
		{
			confirm_box(false, $this->user->lang('CONFIRM_OPERATION'));
		}

		return new \Symfony\Component\HttpFoundation\RedirectResponse($this->helper->route('phpbb_privatemessage_index'));
	}
}
