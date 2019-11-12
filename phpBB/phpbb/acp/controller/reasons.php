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

class reasons
{
	var $u_action;

	public function main($id, $mode)
	{
		$this->language->add_lang(['mcp', 'acp/posting']);

		// Set up general vars
		$action = $this->request->variable('action', '');
		$submit = ($this->request->is_set_post('submit')) ? true : false;
		$reason_id = $this->request->variable('id', 0);

		$this->tpl_name = 'acp_reasons';
		$this->page_title = 'ACP_REASONS';

		$form_name = 'acp_reason';
		add_form_key('acp_reason');

		$error = [];

		switch ($action)
		{
			case 'add':
			case 'edit':

				$reason_row = [
					'reason_title'			=> $this->request->variable('reason_title', '', true),
					'reason_description'	=> $this->request->variable('reason_description', '', true),
				];

				if ($submit)
				{
					if (!check_form_key($form_name))
					{
						$error[] = $this->language->lang('FORM_INVALID');
					}
					// Reason specified?
					if (!$reason_row['reason_title'] || !$reason_row['reason_description'])
					{
						$error[] = $this->language->lang('NO_REASON_INFO');
					}

					$check_double = ($action == 'add') ? true : false;

					if ($action == 'edit')
					{
						$sql = 'SELECT reason_title
							FROM ' . REPORTS_REASONS_TABLE . "
							WHERE reason_id = $reason_id";
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if (strtolower($row['reason_title']) == 'other' || strtolower($reason_row['reason_title']) == 'other')
						{
							$reason_row['reason_title'] = 'other';
						}

						if ($row['reason_title'] != $reason_row['reason_title'])
						{
							$check_double = true;
						}
					}

					// Check for same reason if adding it...
					if ($check_double)
					{
						$sql = 'SELECT reason_id
							FROM ' . REPORTS_REASONS_TABLE . "
							WHERE reason_title = '" . $this->db->sql_escape($reason_row['reason_title']) . "'";
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if ($row || ($action == 'add' && strtolower($reason_row['reason_title']) == 'other'))
						{
							$error[] = $this->language->lang('REASON_ALREADY_EXIST');
						}
					}

					if (!count($error))
					{
						// New reason?
						if ($action == 'add')
						{
							// Get new order...
							$sql = 'SELECT MAX(reason_order) as max_reason_order
								FROM ' . REPORTS_REASONS_TABLE;
							$result = $this->db->sql_query($sql);
							$max_order = (int) $this->db->sql_fetchfield('max_reason_order');
							$this->db->sql_freeresult($result);

							$sql_ary = [
								'reason_title'			=> (string) $reason_row['reason_title'],
								'reason_description'	=> (string) $reason_row['reason_description'],
								'reason_order'			=> $max_order + 1,
							];

							$this->db->sql_query('INSERT INTO ' . REPORTS_REASONS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));

							$log = 'ADDED';
						}
						else if ($reason_id)
						{
							$sql_ary = [
								'reason_title'			=> (string) $reason_row['reason_title'],
								'reason_description'	=> (string) $reason_row['reason_description'],
							];

							$this->db->sql_query('UPDATE ' . REPORTS_REASONS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE reason_id = ' . $reason_id);

							$log = 'UPDATED';
						}

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_REASON_' . $log, false, [$reason_row['reason_title']]);
						trigger_error($this->language->lang('REASON_' . $log) . adm_back_link($this->u_action));
					}
				}
				else if ($reason_id)
				{
					$sql = 'SELECT *
						FROM ' . REPORTS_REASONS_TABLE . '
						WHERE reason_id = ' . $reason_id;
					$result = $this->db->sql_query($sql);
					$reason_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if (!$reason_row)
					{
						trigger_error($this->language->lang('NO_REASON') . adm_back_link($this->u_action), E_USER_WARNING);
					}
				}

				$l_title = ($action == 'edit') ? 'EDIT' : 'ADD';

				$translated = false;

				// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
				if (isset($this->language->lang('report_reasons')['TITLE'][strtoupper($reason_row['reason_title'])]) && isset($this->language->lang('report_reasons')['DESCRIPTION'][strtoupper($reason_row['reason_title'])]))
				{
					$translated = true;
				}

				$this->template->assign_vars([
					'L_TITLE'		=> $this->language->lang('REASON_' . $l_title),
					'U_ACTION'		=> $this->u_action . "&amp;id=$reason_id&amp;action=$action",
					'U_BACK'		=> $this->u_action,
					'ERROR_MSG'		=> (count($error)) ? implode('<br />', $error) : '',

					'REASON_TITLE'			=> $reason_row['reason_title'],
					'REASON_DESCRIPTION'	=> $reason_row['reason_description'],

					'TRANSLATED_TITLE'		=> ($translated) ? $this->language->lang('report_reasons')['TITLE'][strtoupper($reason_row['reason_title'])] : '',
					'TRANSLATED_DESCRIPTION'=> ($translated) ? $this->language->lang('report_reasons')['DESCRIPTION'][strtoupper($reason_row['reason_title'])] : '',

					'S_AVAILABLE_TITLES'	=> implode($this->language->lang('COMMA_SEPARATOR'), array_map('htmlspecialchars', array_keys($this->language->lang('report_reasons')['TITLE']))),
					'S_EDIT_REASON'			=> true,
					'S_TRANSLATED'			=> $translated,
					'S_ERROR'				=> (count($error)) ? true : false,
					]
				);

				return;
			break;

			case 'delete':

				$sql = 'SELECT *
					FROM ' . REPORTS_REASONS_TABLE . '
					WHERE reason_id = ' . $reason_id;
				$result = $this->db->sql_query($sql);
				$reason_row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if (!$reason_row)
				{
					trigger_error($this->language->lang('NO_REASON') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (strtolower($reason_row['reason_title']) == 'other')
				{
					trigger_error($this->language->lang('NO_REMOVE_DEFAULT_REASON') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Let the deletion be confirmed...
				if (confirm_box(true))
				{
					$sql = 'SELECT reason_id
						FROM ' . REPORTS_REASONS_TABLE . "
						WHERE LOWER(reason_title) = 'other'";
					$result = $this->db->sql_query($sql);
					$other_reason_id = (int) $this->db->sql_fetchfield('reason_id');
					$this->db->sql_freeresult($result);

					switch ($this->db->get_sql_layer())
					{
						// The ugly one!
						case 'mysqli':
							// Change the reports using this reason to 'other'
							$sql = 'UPDATE ' . REPORTS_TABLE . '
								SET reason_id = ' . $other_reason_id . ", report_text = CONCAT('" . $this->db->sql_escape($reason_row['reason_description']) . "\n\n', report_text)
								WHERE reason_id = $reason_id";
						break;

						// Standard? What's that?
						case 'mssql_odbc':
						case 'mssqlnative':
							// Change the reports using this reason to 'other'
							$sql = "DECLARE @ptrval binary(16)

									SELECT @ptrval = TEXTPTR(report_text)
										FROM " . REPORTS_TABLE . "
									WHERE reason_id = " . $reason_id . "

									UPDATETEXT " . REPORTS_TABLE . ".report_text @ptrval 0 0 '" . $this->db->sql_escape($reason_row['reason_description']) . "\n\n'

									UPDATE " . REPORTS_TABLE . '
										SET reason_id = ' . $other_reason_id . "
									WHERE reason_id = $reason_id";
						break;

						// Teh standard
						case 'postgres':
						case 'oracle':
						case 'sqlite3':
							// Change the reports using this reason to 'other'
							$sql = 'UPDATE ' . REPORTS_TABLE . '
								SET reason_id = ' . $other_reason_id . ", report_text = '" . $this->db->sql_escape($reason_row['reason_description']) . "\n\n' || report_text
								WHERE reason_id = $reason_id";
						break;
					}
					$this->db->sql_query($sql);

					$this->db->sql_query('DELETE FROM ' . REPORTS_REASONS_TABLE . ' WHERE reason_id = ' . $reason_id);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_REASON_REMOVED', false, [$reason_row['reason_title']]);
					trigger_error($this->language->lang('REASON_REMOVED') . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> $action,
						'id'		=> $reason_id])
					);
				}

			break;

			case 'move_up':
			case 'move_down':

				if (!check_link_hash($this->request->variable('hash', ''), 'acp_reasons'))
				{
					trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT reason_order
					FROM ' . REPORTS_REASONS_TABLE . "
					WHERE reason_id = $reason_id";
				$result = $this->db->sql_query($sql);
				$order = $this->db->sql_fetchfield('reason_order');
				$this->db->sql_freeresult($result);

				if ($order === false || ($order == 0 && $action == 'move_up'))
				{
					break;
				}
				$order = (int) $order;
				$order_total = $order * 2 + (($action == 'move_up') ? -1 : 1);

				$sql = 'UPDATE ' . REPORTS_REASONS_TABLE . '
					SET reason_order = ' . $order_total . ' - reason_order
					WHERE reason_order IN (' . $order . ', ' . (($action == 'move_up') ? $order - 1 : $order + 1) . ')';
				$this->db->sql_query($sql);

				if ($this->request->is_ajax())
				{
					$json_response = new \phpbb\json_response;
					$json_response->send([
						'success'	=> (bool) $this->db->sql_affectedrows(),
					]);
				}
			break;
		}

		// By default, check that order is valid and fix it if necessary
		$sql = 'SELECT reason_id, reason_order
			FROM ' . REPORTS_REASONS_TABLE . '
			ORDER BY reason_order';
		$result = $this->db->sql_query($sql);

		if ($row = $this->db->sql_fetchrow($result))
		{
			$order = 0;
			do
			{
				++$order;

				if ($row['reason_order'] != $order)
				{
					$sql = 'UPDATE ' . REPORTS_REASONS_TABLE . "
						SET reason_order = $order
						WHERE reason_id = {$row['reason_id']}";
					$this->db->sql_query($sql);
				}
			}
			while ($row = $this->db->sql_fetchrow($result));
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars([
			'U_ACTION'			=> $this->u_action,
			]
		);

		// Reason count
		$sql = 'SELECT reason_id, COUNT(reason_id) AS reason_count
			FROM ' . REPORTS_TABLE . '
			GROUP BY reason_id';
		$result = $this->db->sql_query($sql);

		$reason_count = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$reason_count[$row['reason_id']] = $row['reason_count'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT *
			FROM ' . REPORTS_REASONS_TABLE . '
			ORDER BY reason_order ASC';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$translated = false;
			$other_reason = ($row['reason_title'] == 'other') ? true : false;

			// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
			if (isset($this->language->lang('report_reasons')['TITLE'][strtoupper($row['reason_title'])]) && isset($this->language->lang('report_reasons')['DESCRIPTION'][strtoupper($row['reason_title'])]))
			{
				$row['reason_description'] = $this->language->lang('report_reasons')['DESCRIPTION'][strtoupper($row['reason_title'])];
				$row['reason_title'] = $this->language->lang('report_reasons')['TITLE'][strtoupper($row['reason_title'])];

				$translated = true;
			}

			$this->template->assign_block_vars('reasons', [
				'REASON_TITLE'			=> $row['reason_title'],
				'REASON_DESCRIPTION'	=> $row['reason_description'],
				'REASON_COUNT'			=> (isset($reason_count[$row['reason_id']])) ? $reason_count[$row['reason_id']] : 0,

				'S_TRANSLATED'		=> $translated,
				'S_OTHER_REASON'	=> $other_reason,

				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $row['reason_id'],
				'U_DELETE'		=> (!$other_reason) ? $this->u_action . '&amp;action=delete&amp;id=' . $row['reason_id'] : '',
				'U_MOVE_UP'		=> $this->u_action . '&amp;action=move_up&amp;id=' . $row['reason_id'] . '&amp;hash=' . generate_link_hash('acp_reasons'),
				'U_MOVE_DOWN'	=> $this->u_action . '&amp;action=move_down&amp;id=' . $row['reason_id'] . '&amp;hash=' . generate_link_hash('acp_reasons')]
			);
		}
		$this->db->sql_freeresult($result);
	}
}
