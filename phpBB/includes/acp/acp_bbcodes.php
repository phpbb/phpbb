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

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class acp_bbcodes
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $template, $cache, $request, $phpbb_dispatcher, $phpbb_container;
		global $phpbb_log;

		$user->add_lang('acp/posting');

		// Set up general vars
		$action	= $request->variable('action', '');
		$bbcode_id = $request->variable('bbcode', 0);

		$this->tpl_name = 'acp_bbcodes';
		$this->page_title = 'ACP_BBCODES';
		$form_key = 'acp_bbcodes';

		add_form_key($form_key);

		// Set up mode-specific vars
		switch ($action)
		{
			case 'add':
				$bbcode_match = $bbcode_tpl = $bbcode_helpline = '';
				$display_on_posting = 0;
			break;

			case 'edit':
				$sql = 'SELECT bbcode_match, bbcode_tpl, display_on_posting, bbcode_helpline
					FROM ' . BBCODES_TABLE . '
					WHERE bbcode_id = ' . $bbcode_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['BBCODE_NOT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$bbcode_match = $row['bbcode_match'];
				$bbcode_tpl = htmlspecialchars($row['bbcode_tpl'], ENT_COMPAT);
				$display_on_posting = $row['display_on_posting'];
				$bbcode_helpline = $row['bbcode_helpline'];
			break;

			case 'modify':
				$sql = 'SELECT bbcode_id, bbcode_tag
					FROM ' . BBCODES_TABLE . '
					WHERE bbcode_id = ' . $bbcode_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (!$row)
				{
					trigger_error($user->lang['BBCODE_NOT_EXIST'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

			// No break here

			case 'create':
				$display_on_posting = $request->variable('display_on_posting', 0);

				$bbcode_match = $request->variable('bbcode_match', '');
				$bbcode_tpl = html_entity_decode($request->variable('bbcode_tpl', '', true), ENT_COMPAT);
				$bbcode_helpline = $request->variable('bbcode_helpline', '', true);
			break;
		}

		// Do major work
		switch ($action)
		{
			case 'edit':
			case 'add':

				$tpl_ary = array(
					'S_EDIT_BBCODE'		=> true,
					'U_BACK'			=> $this->u_action,
					'U_ACTION'			=> $this->u_action . '&amp;action=' . (($action == 'add') ? 'create' : 'modify') . (($bbcode_id) ? "&amp;bbcode=$bbcode_id" : ''),

					'L_BBCODE_USAGE_EXPLAIN'=> sprintf($user->lang['BBCODE_USAGE_EXPLAIN'], '<a href="#down">', '</a>'),
					'BBCODE_MATCH'			=> $bbcode_match,
					'BBCODE_TPL'			=> $bbcode_tpl,
					'BBCODE_HELPLINE'		=> $bbcode_helpline,
					'DISPLAY_ON_POSTING'	=> $display_on_posting,
				);

				$bbcode_tokens = array('TEXT', 'SIMPLETEXT', 'INTTEXT', 'IDENTIFIER', 'NUMBER', 'EMAIL', 'URL', 'LOCAL_URL', 'RELATIVE_URL', 'COLOR');

				/**
				* Modify custom bbcode template data before we display the add/edit form
				*
				* @event core.acp_bbcodes_edit_add
				* @var	string	action			Type of the action: add|edit
				* @var	array	tpl_ary			Array with custom bbcode add/edit data
				* @var	int		bbcode_id		When editing: the bbcode id,
				*								when creating: 0
				* @var	array	bbcode_tokens	Array of bbcode tokens
				* @since 3.1.0-a3
				*/
				$vars = array('action', 'tpl_ary', 'bbcode_id', 'bbcode_tokens');
				extract($phpbb_dispatcher->trigger_event('core.acp_bbcodes_edit_add', compact($vars)));

				$template->assign_vars($tpl_ary);

				foreach ($bbcode_tokens as $token)
				{
					$template->assign_block_vars('token', array(
						'TOKEN'		=> '{' . $token . '}',
						'EXPLAIN'	=> ($token === 'LOCAL_URL') ? $user->lang(array('tokens', $token), generate_board_url() . '/') : $user->lang(array('tokens', $token)),
					));
				}

				return;

			break;

			case 'modify':
			case 'create':

				$sql_ary = $hidden_fields = array();

				/**
				* Modify custom bbcode data before the modify/create action
				*
				* @event core.acp_bbcodes_modify_create
				* @var	string	action				Type of the action: modify|create
				* @var	array	sql_ary				Array with new bbcode data
				* @var	int		bbcode_id			When editing: the bbcode id,
				*									when creating: 0
				* @var	bool	display_on_posting	Display bbcode on posting form
				* @var	string	bbcode_match		The bbcode usage string to match
				* @var	string	bbcode_tpl			The bbcode HTML replacement string
				* @var	string	bbcode_helpline		The bbcode help line string
				* @var	array	hidden_fields		Array of hidden fields for use when
				*									submitting form when $warn_unsafe is true
				* @since 3.1.0-a3
				*/
				$vars = array(
					'action',
					'sql_ary',
					'bbcode_id',
					'display_on_posting',
					'bbcode_match',
					'bbcode_tpl',
					'bbcode_helpline',
					'hidden_fields',
				);
				extract($phpbb_dispatcher->trigger_event('core.acp_bbcodes_modify_create', compact($vars)));

				$acp_utils   = $phpbb_container->get('text_formatter.acp_utils');
				$bbcode_info = $acp_utils->analyse_bbcode($bbcode_match, $bbcode_tpl);
				$warn_unsafe = ($bbcode_info['status'] === $acp_utils::BBCODE_STATUS_UNSAFE);

				if ($bbcode_info['status'] === $acp_utils::BBCODE_STATUS_INVALID_TEMPLATE)
				{
					trigger_error($user->lang['BBCODE_INVALID_TEMPLATE'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
				if ($bbcode_info['status'] === $acp_utils::BBCODE_STATUS_INVALID_DEFINITION)
				{
					trigger_error($user->lang['BBCODE_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$warn_unsafe && !check_form_key($form_key))
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (!$warn_unsafe || confirm_box(true))
				{
					$data = $this->build_regexp($bbcode_match, $bbcode_tpl);

					// Make sure the user didn't pick a "bad" name for the BBCode tag.
					$hard_coded = array('code', 'quote', 'quote=', 'attachment', 'attachment=', 'b', 'i', 'url', 'url=', 'img', 'size', 'size=', 'color', 'color=', 'u', 'list', 'list=', 'email', 'email=', 'flash', 'flash=');

					if (($action == 'modify' && strtolower($data['bbcode_tag']) !== strtolower($row['bbcode_tag'])) || ($action == 'create'))
					{
						$sql = 'SELECT 1 as test
							FROM ' . BBCODES_TABLE . "
							WHERE LOWER(bbcode_tag) = '" . $db->sql_escape(strtolower($data['bbcode_tag'])) . "'";
						$result = $db->sql_query($sql);
						$info = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						// Grab the end, interrogate the last closing tag
						if (isset($info['test']) && $info['test'] === '1'
							|| in_array(strtolower($data['bbcode_tag']), $hard_coded)
							|| (preg_match('#\[/([^[]*)]$#', $bbcode_match, $regs) && in_array(strtolower($regs[1]), $hard_coded))
						)
						{
							trigger_error($user->lang['BBCODE_INVALID_TAG_NAME'] . adm_back_link($this->u_action), E_USER_WARNING);
						}
					}

					if (substr($data['bbcode_tag'], -1) === '=')
					{
						$test = substr($data['bbcode_tag'], 0, -1);
					}
					else
					{
						$test = $data['bbcode_tag'];
					}

					if (strlen($data['bbcode_tag']) > 16)
					{
						trigger_error($user->lang['BBCODE_TAG_TOO_LONG'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (strlen($bbcode_match) > 4000)
					{
						trigger_error($user->lang['BBCODE_TAG_DEF_TOO_LONG'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (strlen($bbcode_helpline) > 3000)
					{
						trigger_error($user->lang['BBCODE_HELPLINE_TOO_LONG'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					/**
					 * Replace Emojis and other 4bit UTF-8 chars not allowed by MySQL to UCR/NCR.
					 * Using their Numeric Character Reference's Hexadecimal notation.
					 */
					$bbcode_helpline = utf8_encode_ucr($bbcode_helpline);

					$sql_ary = array_merge($sql_ary, array(
						'bbcode_tag'				=> $data['bbcode_tag'],
						'bbcode_match'				=> $bbcode_match,
						'bbcode_tpl'				=> $bbcode_tpl,
						'display_on_posting'		=> $display_on_posting,
						'bbcode_helpline'			=> $bbcode_helpline,
						'first_pass_match'			=> $data['first_pass_match'],
						'first_pass_replace'		=> $data['first_pass_replace'],
						'second_pass_match'			=> $data['second_pass_match'],
						'second_pass_replace'		=> $data['second_pass_replace']
					));

					if ($action == 'create')
					{
						$sql = 'SELECT MAX(bbcode_id) as max_bbcode_id
							FROM ' . BBCODES_TABLE;
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);

						if ($row)
						{
							$bbcode_id = (int) $row['max_bbcode_id'] + 1;

							// Make sure it is greater than the core bbcode ids...
							if ($bbcode_id <= NUM_CORE_BBCODES)
							{
								$bbcode_id = NUM_CORE_BBCODES + 1;
							}
						}
						else
						{
							$bbcode_id = NUM_CORE_BBCODES + 1;
						}

						if ($bbcode_id > BBCODE_LIMIT)
						{
							trigger_error($user->lang['TOO_MANY_BBCODES'] . adm_back_link($this->u_action), E_USER_WARNING);
						}

						$sql_ary['bbcode_id'] = (int) $bbcode_id;

						$db->sql_query('INSERT INTO ' . BBCODES_TABLE . $db->sql_build_array('INSERT', $sql_ary));
						$cache->destroy('sql', BBCODES_TABLE);
						$phpbb_container->get('text_formatter.cache')->invalidate();

						$lang = 'BBCODE_ADDED';
						$log_action = 'LOG_BBCODE_ADD';
					}
					else
					{
						$sql = 'UPDATE ' . BBCODES_TABLE . '
							SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE bbcode_id = ' . $bbcode_id;
						$db->sql_query($sql);
						$cache->destroy('sql', BBCODES_TABLE);
						$phpbb_container->get('text_formatter.cache')->invalidate();

						$lang = 'BBCODE_EDITED';
						$log_action = 'LOG_BBCODE_EDIT';
					}

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, $log_action, false, array($data['bbcode_tag']));

					/**
					* Event after a BBCode has been added or updated
					*
					* @event core.acp_bbcodes_modify_create_after
					* @var	string	action		Type of the action: modify|create
					* @var	int		bbcode_id	The id of the added or updated bbcode
					* @var	array	sql_ary		Array with bbcode data (read only)
					* @since 3.2.4-RC1
					*/
					$vars = array(
						'action',
						'bbcode_id',
						'sql_ary',
					);
					extract($phpbb_dispatcher->trigger_event('core.acp_bbcodes_modify_create_after', compact($vars)));

					trigger_error($user->lang[$lang] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $user->lang['BBCODE_DANGER'], build_hidden_fields(array_merge($hidden_fields, array(
						'action'				=> $action,
						'bbcode'				=> $bbcode_id,
						'bbcode_match'			=> $bbcode_match,
						'bbcode_tpl'			=> htmlspecialchars($bbcode_tpl, ENT_COMPAT),
						'bbcode_helpline'		=> $bbcode_helpline,
						'display_on_posting'	=> $display_on_posting,
						)))
					, 'confirm_bbcode.html');
				}

			break;

			case 'delete':

				$sql = 'SELECT bbcode_tag
					FROM ' . BBCODES_TABLE . "
					WHERE bbcode_id = $bbcode_id";
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if ($row)
				{
					if (confirm_box(true))
					{
						$bbcode_tag = $row['bbcode_tag'];

						$db->sql_query('DELETE FROM ' . BBCODES_TABLE . " WHERE bbcode_id = $bbcode_id");
						$cache->destroy('sql', BBCODES_TABLE);
						$phpbb_container->get('text_formatter.cache')->invalidate();
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_BBCODE_DELETE', false, array($bbcode_tag));

						/**
						* Event after a BBCode has been deleted
						*
						* @event core.acp_bbcodes_delete_after
						* @var	string	action		Type of the action: delete
						* @var	int		bbcode_id	The id of the deleted bbcode
						* @var	string	bbcode_tag	The tag of the deleted bbcode
						* @since 3.2.4-RC1
						*/
						$vars = array(
							'action',
							'bbcode_id',
							'bbcode_tag',
						);
						extract($phpbb_dispatcher->trigger_event('core.acp_bbcodes_delete_after', compact($vars)));

						if ($request->is_ajax())
						{
							$json_response = new \phpbb\json_response;
							$json_response->send(array(
								'MESSAGE_TITLE'	=> $user->lang['INFORMATION'],
								'MESSAGE_TEXT'	=> $user->lang['BBCODE_DELETED'],
								'REFRESH_DATA'	=> array(
									'time'	=> 3
								)
							));
						}
					}
					else
					{
						confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
							'bbcode'	=> $bbcode_id,
							'i'			=> $id,
							'mode'		=> $mode,
							'action'	=> $action))
						);
					}
				}

			break;
		}

		$u_action = $this->u_action;

		$template_data = array(
			'U_ACTION'		=> $this->u_action . '&amp;action=add',
		);

		$sql_ary = array(
			'SELECT'	=> 'b.*',
			'FROM'		=> array(BBCODES_TABLE => 'b'),
			'ORDER_BY'	=> 'b.bbcode_tag',
		);

		/**
		*  Modify custom bbcode template data before we display the form
		*
		* @event core.acp_bbcodes_display_form
		* @var	string	action			Type of the action: modify|create
		* @var	array	sql_ary			The SQL array to get custom bbcode data
		* @var	array	template_data	Array with form template data
		* @var	string	u_action		The u_action link
		* @since 3.1.0-a3
		*/
		$vars = array('action', 'sql_ary', 'template_data', 'u_action');
		extract($phpbb_dispatcher->trigger_event('core.acp_bbcodes_display_form', compact($vars)));

		$result = $db->sql_query($db->sql_build_query('SELECT', $sql_ary));

		$template->assign_vars($template_data);

		while ($row = $db->sql_fetchrow($result))
		{
			$bbcodes_array = array(
				'BBCODE_TAG'		=> $row['bbcode_tag'],
				'U_EDIT'			=> $u_action . '&amp;action=edit&amp;bbcode=' . $row['bbcode_id'],
				'U_DELETE'			=> $u_action . '&amp;action=delete&amp;bbcode=' . $row['bbcode_id'],
			);

			/**
			*  Modify display of custom bbcodes in the form
			*
			* @event core.acp_bbcodes_display_bbcodes
			* @var	array	row				Array with current bbcode data
			* @var	array	bbcodes_array	Array of bbcodes template data
			* @var	string	u_action		The u_action link
			* @since 3.1.0-a3
			*/
			$vars = array('bbcodes_array', 'row', 'u_action');
			extract($phpbb_dispatcher->trigger_event('core.acp_bbcodes_display_bbcodes', compact($vars)));

			$template->assign_block_vars('bbcodes', $bbcodes_array);

		}
		$db->sql_freeresult($result);
	}

	/*
	* Build regular expression for custom bbcode
	*/
	function build_regexp(&$bbcode_match, &$bbcode_tpl)
	{
		$bbcode_match = trim($bbcode_match);
		$bbcode_tag = preg_replace('/.*?\[([a-z0-9_-]+).*/i', '$1', $bbcode_match);

		if (!preg_match('/^[a-zA-Z0-9_-]+$/', $bbcode_tag))
		{
			global $user;
			trigger_error($user->lang['BBCODE_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		return array(
			'bbcode_tag'				=> $bbcode_tag,
			'first_pass_match'			=> '/(?!)/',
			'first_pass_replace'		=> '',
			// Use a non-matching, valid regexp to effectively disable this BBCode
			'second_pass_match'			=> '/(?!)/',
			'second_pass_replace'		=> ''
		);
	}
}
