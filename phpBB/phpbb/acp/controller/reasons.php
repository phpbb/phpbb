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
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var array phpBB tables */
	protected $tables;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\db\driver\driver_interface	$db			Database object
	 * @param \phpbb\acp\helper\controller		$helper		ACP Controller helper object
	 * @param \phpbb\language\language			$language	Language object
	 * @param \phpbb\log\log					$log		Log object
	 * @param \phpbb\request\request			$request	Request object
	 * @param \phpbb\template\template			$template	Template object
	 * @param \phpbb\user						$user		User object
	 * @param array								$tables		phpBB tables
	 */
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		$tables
	)
	{
		$this->db		= $db;
		$this->helper	= $helper;
		$this->language	= $language;
		$this->log		= $log;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;

		$this->tables	= $tables;
	}

	public function main()
	{
		$this->language->add_lang(['mcp', 'acp/posting']);

		// Set up general vars
		$action = $this->request->variable('action', '');
		$submit = $this->request->is_set_post('submit');
		$reason_id = $this->request->variable('id', 0);

		$reasons_lang = $this->language->get_lang_array()['report_reasons'];

		$form_key = 'acp_reason';
		add_form_key('acp_reason');

		$errors = [];

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
					if (!check_form_key($form_key))
					{
						$errors[] = $this->language->lang('FORM_INVALID');
					}

					// Reason specified?
					if (!$reason_row['reason_title'] || !$reason_row['reason_description'])
					{
						$errors[] = $this->language->lang('NO_REASON_INFO');
					}

					$check_double = $action === 'add';

					if ($action === 'edit')
					{
						$sql = 'SELECT reason_title
							FROM ' . $this->tables['reports_reasons'] . '
							WHERE reason_id = ' . (int) $reason_id;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if (strtolower($row['reason_title']) === 'other' || strtolower($reason_row['reason_title']) === 'other')
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
							FROM ' . $this->tables['reports_reasons'] . "
							WHERE reason_title = '" . $this->db->sql_escape($reason_row['reason_title']) . "'";
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						if ($row || ($action === 'add' && strtolower($reason_row['reason_title']) === 'other'))
						{
							$errors[] = $this->language->lang('REASON_ALREADY_EXIST');
						}
					}

					if (empty($errors))
					{
						$log_action = '';

						// New reason?
						if ($action === 'add')
						{
							// Get new order...
							$sql = 'SELECT MAX(reason_order) as max_reason_order
								FROM ' . $this->tables['reports_reasons'];
							$result = $this->db->sql_query($sql);
							$max_order = (int) $this->db->sql_fetchfield('max_reason_order');
							$this->db->sql_freeresult($result);

							$sql_ary = [
								'reason_title'			=> (string) $reason_row['reason_title'],
								'reason_description'	=> (string) $reason_row['reason_description'],
								'reason_order'			=> $max_order + 1,
							];

							$sql = 'INSERT INTO ' . $this->tables['reports_reasons'] . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
							$this->db->sql_query($sql);

							$log_action = 'ADDED';
						}
						else if ($reason_id)
						{
							$sql_ary = [
								'reason_title'			=> (string) $reason_row['reason_title'],
								'reason_description'	=> (string) $reason_row['reason_description'],
							];

							$sql = 'UPDATE ' . $this->tables['reports_reasons'] . '
								SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
								WHERE reason_id = ' . (int) $reason_id;
							$this->db->sql_query($sql);

							$log_action = 'UPDATED';
						}

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_REASON_' . $log_action, false, [$reason_row['reason_title']]);

						return $this->helper->message_back('REASON_' . $log_action, 'acp_reasons');
					}
				}
				else if ($reason_id)
				{
					$sql = 'SELECT *
						FROM ' . $this->tables['reports_reasons'] . '
						WHERE reason_id = ' . (int) $reason_id;
					$result = $this->db->sql_query($sql);
					$reason_row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);

					if ($reason_row === false)
					{
						return trigger_error($this->language->lang('NO_REASON') . $this->helper->adm_back_route('acp_reasons'), E_USER_WARNING);
					}
				}

				$l_title = $action === 'edit' ? 'EDIT' : 'ADD';

				$translated = false;

				// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
				if (isset($reasons_lang['TITLE'][strtoupper($reason_row['reason_title'])]) && isset($reasons_lang['DESCRIPTION'][strtoupper($reason_row['reason_title'])]))
				{
					$translated = true;
				}

				$s_errors = !empty($errors);

				$this->template->assign_vars([
					'S_ERROR'		=> $s_errors,
					'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',

					'REASON_TITLE'			=> $reason_row['reason_title'],
					'REASON_DESCRIPTION'	=> $reason_row['reason_description'],

					'TRANSLATED_TITLE'		=> $translated ? $reasons_lang['TITLE'][strtoupper($reason_row['reason_title'])] : '',
					'TRANSLATED_DESCRIPTION'=> $translated ? $reasons_lang['DESCRIPTION'][strtoupper($reason_row['reason_title'])] : '',

					'L_TITLE'				=> $this->language->lang('REASON_' . $l_title),

					'S_AVAILABLE_TITLES'	=> implode($this->language->lang('COMMA_SEPARATOR'), array_map('htmlspecialchars', array_keys($reasons_lang['TITLE']))),
					'S_EDIT_REASON'			=> true,
					'S_TRANSLATED'			=> $translated,

					'U_ACTION'		=> $this->helper->route('acp_reasons', ['action' => $action, 'id' => $reason_id]),
					'U_BACK'		=> $this->helper->route('acp_reasons'),
				]);

				return $this->helper->render('acp_reasons.html', $this->language->lang('ACP_REASONS'));
			break;

			case 'delete':
				$sql = 'SELECT *
					FROM ' . $this->tables['reports_reasons'] . '
					WHERE reason_id = ' . (int) $reason_id;
				$result = $this->db->sql_query($sql);
				$reason_row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($reason_row === false)
				{
					return trigger_error($this->language->lang('NO_REASON') . $this->helper->adm_back_route('acp_reasons'), E_USER_WARNING);
				}

				if (strtolower($reason_row['reason_title']) === 'other')
				{
					return trigger_error($this->language->lang('NO_REMOVE_DEFAULT_REASON') . $this->helper->adm_back_route('acp_reasons'), E_USER_WARNING);
				}

				// Let the deletion be confirmed...
				if (confirm_box(true))
				{
					$sql = 'SELECT reason_id
						FROM ' . $this->tables['reports_reasons'] . '
						WHERE ' . $this->db->sql_lower_text('reason_title') . " = 'other'";
					$result = $this->db->sql_query($sql);
					$other_reason_id = (int) $this->db->sql_fetchfield('reason_id');
					$this->db->sql_freeresult($result);

					switch ($this->db->get_sql_layer())
					{
						// The ugly one!
						case 'mysqli':
							// Change the reports using this reason to 'other'
							$sql = 'UPDATE ' . $this->tables['reports'] . '
								SET reason_id = ' . (int) $other_reason_id . ", report_text = CONCAT('" . $this->db->sql_escape($reason_row['reason_description']) . "\n\n', report_text)
								WHERE reason_id = $reason_id";
						break;

						// Standard? What's that?
						case 'mssql_odbc':
						case 'mssqlnative':
							// Change the reports using this reason to 'other'
							$sql = "DECLARE @ptrval binary(16)

									SELECT @ptrval = TEXTPTR(report_text)
										FROM " . $this->tables['reports'] . "
									WHERE reason_id = " . (int) $reason_id . "

									UPDATETEXT " . $this->tables['reports'] . ".report_text @ptrval 0 0 '" . $this->db->sql_escape($reason_row['reason_description']) . "\n\n'

									UPDATE " . $this->tables['reports'] . '
										SET reason_id = ' . (int) $other_reason_id . '
									WHERE reason_id = ' . (int) $reason_id;
						break;

						// Teh standard
						case 'postgres':
						case 'oracle':
						case 'sqlite3':
							// Change the reports using this reason to 'other'
							$sql = 'UPDATE ' . $this->tables['reports'] . '
								SET reason_id = ' . (int) $other_reason_id . ", report_text = '" . $this->db->sql_escape($reason_row['reason_description']) . "\n\n' || report_text
								WHERE reason_id = $reason_id";
						break;
					}
					$this->db->sql_query($sql);

					$sql = 'DELETE FROM ' . $this->tables['reports_reasons'] . ' WHERE reason_id = ' . (int) $reason_id;
					$this->db->sql_query($sql);

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_REASON_REMOVED', false, [$reason_row['reason_title']]);

					return $this->helper->message_back('REASON_REMOVED', 'acp_reasons');
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'action'	=> $action,
						'id'		=> $reason_id,
					]));

					return redirect($this->helper->route('acp_reasons'));
				}
			break;

			case 'move_up':
			case 'move_down':
				if (!check_link_hash($this->request->variable('hash', ''), 'acp_reasons'))
				{
					return trigger_error($this->language->lang('FORM_INVALID') . $this->helper->adm_back_route('acp_reasons'), E_USER_WARNING);
				}

				$sql = 'SELECT reason_order
					FROM ' . $this->tables['reports_reasons'] . '
					WHERE reason_id = ' . (int) $reason_id;
				$result = $this->db->sql_query($sql);
				$order = $this->db->sql_fetchfield('reason_order');
				$this->db->sql_freeresult($result);

				if ($order === false || ($order == 0 && $action === 'move_up'))
				{
					break;
				}
				$order = (int) $order;
				$order_total = $order * 2 + ($action === 'move_up' ? -1 : 1);

				$sql = 'UPDATE ' . $this->tables['reports_reasons'] . '
					SET reason_order = ' . $order_total . ' - reason_order
					WHERE reason_order IN (' . $order . ', ' . ($action === 'move_up' ? $order - 1 : $order + 1) . ')';
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
			FROM ' . $this->tables['reports_reasons'] . '
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
					$sql = 'UPDATE ' . $this->tables['reports_reasons'] . "
						SET reason_order = $order
						WHERE reason_id = {$row['reason_id']}";
					$this->db->sql_query($sql);
				}
			}
			while ($row = $this->db->sql_fetchrow($result));
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_var('U_ACTION', $this->helper->route('acp_reasons'));

		// Reason count
		$reason_count = [];

		$sql = 'SELECT reason_id, COUNT(reason_id) AS reason_count
			FROM ' . $this->tables['reports'] . '
			GROUP BY reason_id';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$reason_count[$row['reason_id']] = $row['reason_count'];
		}
		$this->db->sql_freeresult($result);

		$sql = 'SELECT *
			FROM ' . $this->tables['reports_reasons'] . '
			ORDER BY reason_order ASC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$translated = false;
			$other_reason = $row['reason_title'] === 'other';

			// If the reason is defined within the language file, we will use the localized version, else just use the database entry...
			if (isset($reasons_lang['TITLE'][strtoupper($row['reason_title'])]) && isset($reasons_lang['DESCRIPTION'][strtoupper($row['reason_title'])]))
			{
				$row['reason_description'] = $reasons_lang['DESCRIPTION'][strtoupper($row['reason_title'])];
				$row['reason_title'] = $reasons_lang['TITLE'][strtoupper($row['reason_title'])];

				$translated = true;
			}

			$this->template->assign_block_vars('reasons', [
				'REASON_TITLE'			=> $row['reason_title'],
				'REASON_DESCRIPTION'	=> $row['reason_description'],
				'REASON_COUNT'			=> isset($reason_count[$row['reason_id']]) ? $reason_count[$row['reason_id']] : 0,

				'S_TRANSLATED'		=> $translated,
				'S_OTHER_REASON'	=> $other_reason,

				'U_EDIT'		=> $this->helper->route('acp_reasons', ['action' => 'edit', 'id' => $row['reason_id']]),
				'U_DELETE'		=> !$other_reason ? $this->helper->route('acp_reasons', ['action' => 'delete', 'id' => $row['reason_id']]) : '',
				'U_MOVE_UP'		=> $this->helper->route('acp_reasons', ['action' => 'move_up', 'id' => $row['reason_id'], 'hash' => generate_link_hash('acp_reasons')]),
				'U_MOVE_DOWN'	=> $this->helper->route('acp_reasons', ['action' => 'move_down', 'id' => $row['reason_id'], 'hash' => generate_link_hash('acp_reasons')]),
			]);
		}
		$this->db->sql_freeresult($result);

		return $this->helper->render('acp_reasons.html', 'ACP_REASONS');
	}
}
