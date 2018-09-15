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

class thread_action
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

		return new \Symfony\Component\HttpFoundation\RedirectResponse($this->helper->route('phpbb_privatemessage_thread', array('id' => $id)));
	}

	public function edit_title($id)
	{
		$new_title = $this->request->variable('new_title', '', true);

		$sql = 'UPDATE ' . $this->privmsgs_table . "
			SET message_subject = '" . $this->db->sql_escape($new_title) . "'
			WHERE msg_id = " . (int) $id;
		$this->db->sql_query($sql);

		return new \Symfony\Component\HttpFoundation\RedirectResponse($this->helper->route('phpbb_privatemessage_thread', array('id' => $id)));
	}
}
