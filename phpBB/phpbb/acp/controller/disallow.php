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

namespace phpbb\acp\controller;

class disallow
{
	var $u_action;

	public function main($id, $mode)
	{
		$this->language->add_lang('acp/posting');

		// Set up general vars
		$this->tpl_name = 'acp_disallow';
		$this->page_title = 'ACP_DISALLOW_USERNAMES';

		$form_key = 'acp_disallow';
		add_form_key($form_key);

		$disallow = ($this->request->is_set_post() ? true : false;
		$allow = ($this->request->is_set_post('allow')) ? true : false;

		if (($allow || $disallow) && !check_form_key($form_key))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($disallow)
		{
			$disallowed_user = str_replace('*', '%', $this->request->variable('disallowed_user', '', true));

			if (!$disallowed_user)
			{
				trigger_error($this->language->lang('NO_USERNAME_SPECIFIED') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'SELECT disallow_id
				FROM ' . DISALLOW_TABLE . "
				WHERE disallow_username = '" . $this->db->sql_escape($disallowed_user) . "'";
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($row)
			{
				trigger_error($this->language->lang('DISALLOWED_ALREADY') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'INSERT INTO ' . DISALLOW_TABLE . ' ' . $this->db->sql_build_array('INSERT', ['disallow_username' => $disallowed_user]);
			$this->db->sql_query($sql);

			$this->cache->destroy('_disallowed_usernames');

			$message = $this->language->lang('DISALLOW_SUCCESSFUL');
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DISALLOW_ADD', false, [str_replace('%', '*', $disallowed_user)]);

			trigger_error($message . adm_back_link($this->u_action));
		}
		else if ($allow)
		{
			$disallowed_id = $this->request->variable('disallowed_id', 0);

			if (!$disallowed_id)
			{
				trigger_error($this->language->lang('NO_USERNAME_SPECIFIED') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$sql = 'DELETE FROM ' . DISALLOW_TABLE . '
				WHERE disallow_id = ' . $disallowed_id;
			$this->db->sql_query($sql);

			$this->cache->destroy('_disallowed_usernames');

			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_DISALLOW_DELETE');

			trigger_error($this->language->lang('DISALLOWED_DELETED') . adm_back_link($this->u_action));
		}

		// Grab the current list of disallowed usernames...
		$sql = 'SELECT *
			FROM ' . DISALLOW_TABLE;
		$result = $this->db->sql_query($sql);

		$disallow_select = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$disallow_select .= '<option value="' . $row['disallow_id'] . '">' . str_replace('%', '*', $row['disallow_username']) . '</option>';
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'U_ACTION'				=> $this->u_action,
			'S_DISALLOWED_NAMES'	=> $disallow_select]
		);
	}
}
