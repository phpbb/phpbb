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
		$submit = $request->is_set_post('submit');

		$this->tpl_name = 'acp_bbcodes';
		$this->page_title = 'ACP_BBCODES';
		$form_key = 'acp_bbcodes';

		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

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
				$bbcode_tpl = htmlspecialchars($row['bbcode_tpl']);
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
				$bbcode_tpl = htmlspecialchars_decode($request->variable('bbcode_tpl', '', true));
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
				*									submitting form when $warn_text is true
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

				$warn_text = preg_match('%<[^>]*\{text[\d]*\}[^>]*>%i', $bbcode_tpl);
				if (!$warn_text || confirm_box(true))
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
						if ($info['test'] === '1' || in_array(strtolower($data['bbcode_tag']), $hard_coded) || (preg_match('#\[/([^[]*)]$#', $bbcode_match, $regs) && in_array(strtolower($regs[1]), $hard_coded)))
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

					if (!preg_match('%\\[' . $test . '[^]]*].*?\\[/' . $test . ']%s', $bbcode_match))
					{
						trigger_error($user->lang['BBCODE_OPEN_ENDED_TAG'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (strlen($data['bbcode_tag']) > 16)
					{
						trigger_error($user->lang['BBCODE_TAG_TOO_LONG'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (strlen($bbcode_match) > 4000)
					{
						trigger_error($user->lang['BBCODE_TAG_DEF_TOO_LONG'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

					if (strlen($bbcode_helpline) > 255)
					{
						trigger_error($user->lang['BBCODE_HELPLINE_TOO_LONG'] . adm_back_link($this->u_action), E_USER_WARNING);
					}

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

					trigger_error($user->lang[$lang] . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $user->lang['BBCODE_DANGER'], build_hidden_fields(array_merge($hidden_fields, array(
						'action'				=> $action,
						'bbcode'				=> $bbcode_id,
						'bbcode_match'			=> $bbcode_match,
						'bbcode_tpl'			=> htmlspecialchars($bbcode_tpl),
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
						$db->sql_query('DELETE FROM ' . BBCODES_TABLE . " WHERE bbcode_id = $bbcode_id");
						$cache->destroy('sql', BBCODES_TABLE);
						$phpbb_container->get('text_formatter.cache')->invalidate();
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_BBCODE_DELETE', false, array($row['bbcode_tag']));

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
		$bbcode_tpl = trim($bbcode_tpl);

		// Allow unicode characters for URL|LOCAL_URL|RELATIVE_URL|INTTEXT tokens
		$utf8 = preg_match('/(URL|LOCAL_URL|RELATIVE_URL|INTTEXT)/', $bbcode_match);

		$fp_match = preg_quote($bbcode_match, '!');
		$fp_replace = preg_replace('#^\[(.*?)\]#', '[$1:$uid]', $bbcode_match);
		$fp_replace = preg_replace('#\[/(.*?)\]$#', '[/$1:$uid]', $fp_replace);

		$sp_match = preg_quote($bbcode_match, '!');
		$sp_match = preg_replace('#^\\\\\[(.*?)\\\\\]#', '\[$1:$uid\]', $sp_match);
		$sp_match = preg_replace('#\\\\\[/(.*?)\\\\\]$#', '\[/$1:$uid\]', $sp_match);
		$sp_replace = $bbcode_tpl;

		// @todo Make sure to change this too if something changed in message parsing
		$tokens = array(
			'URL'	 => array(
				'!(?:(' . str_replace(array('!', '\#'), array('\!', '#'), get_preg_expression('url')) . ')|(' . str_replace(array('!', '\#'), array('\!', '#'), get_preg_expression('www_url')) . '))!ie'	=>	"\$this->bbcode_specialchars(('\$1') ? '\$1' : 'http://\$2')"
			),
			'LOCAL_URL'	 => array(
				'!(' . str_replace(array('!', '\#'), array('\!', '#'), get_preg_expression('relative_url')) . ')!e'	=>	"\$this->bbcode_specialchars('$1')"
			),
			'RELATIVE_URL'	=> array(
				'!(' . str_replace(array('!', '\#'), array('\!', '#'), get_preg_expression('relative_url')) . ')!e'	=>	"\$this->bbcode_specialchars('$1')"
			),
			'EMAIL' => array(
				'!(' . get_preg_expression('email') . ')!ie'	=>	"\$this->bbcode_specialchars('$1')"
			),
			'TEXT' => array(
				'!(.*?)!es'	 =>	"str_replace(array(\"\\r\\n\", '\\\"', '\\'', '(', ')'), array(\"\\n\", '\"', '&#39;', '&#40;', '&#41;'), trim('\$1'))"
			),
			'SIMPLETEXT' => array(
				'!([a-zA-Z0-9-+.,_ ]+)!'	 =>	"$1"
			),
			'INTTEXT' => array(
				'!([\p{L}\p{N}\-+,_. ]+)!u'	 =>	"$1"
			),
			'IDENTIFIER' => array(
				'!([a-zA-Z0-9-_]+)!'	 =>	"$1"
			),
			'COLOR' => array(
				'!([a-z]+|#[0-9abcdef]+)!i'	=>	'$1'
			),
			'NUMBER' => array(
				'!([0-9]+)!'	=>	'$1'
			)
		);

		$sp_tokens = array(
			'URL'	 => '(?i)((?:' . str_replace(array('!', '\#'), array('\!', '#'), get_preg_expression('url')) . ')|(?:' . str_replace(array('!', '\#'), array('\!', '#'), get_preg_expression('www_url')) . '))(?-i)',
			'LOCAL_URL'	 => '(?i)(' . str_replace(array('!', '\#'), array('\!', '#'), get_preg_expression('relative_url')) . ')(?-i)',
			'RELATIVE_URL'	 => '(?i)(' . str_replace(array('!', '\#'), array('\!', '#'), get_preg_expression('relative_url')) . ')(?-i)',
			'EMAIL' => '(' . get_preg_expression('email') . ')',
			'TEXT' => '(.*?)',
			'SIMPLETEXT' => '([a-zA-Z0-9-+.,_ ]+)',
			'INTTEXT' => '([\p{L}\p{N}\-+,_. ]+)',
			'IDENTIFIER' => '([a-zA-Z0-9-_]+)',
			'COLOR' => '([a-zA-Z]+|#[0-9abcdefABCDEF]+)',
			'NUMBER' => '([0-9]+)',
		);

		$pad = 0;
		$modifiers = 'i';
		$modifiers .= ($utf8) ? 'u' : '';

		if (preg_match_all('/\{(' . implode('|', array_keys($tokens)) . ')[0-9]*\}/i', $bbcode_match, $m))
		{
			foreach ($m[0] as $n => $token)
			{
				$token_type = $m[1][$n];

				reset($tokens[strtoupper($token_type)]);
				list($match, $replace) = each($tokens[strtoupper($token_type)]);

				// Pad backreference numbers from tokens
				if (preg_match_all('/(?<!\\\\)\$([0-9]+)/', $replace, $repad))
				{
					$repad = $pad + count(array_unique($repad[0]));
					$replace = preg_replace_callback('/(?<!\\\\)\$([0-9]+)/', function ($match) use ($pad) {
						return '${' . ($match[1] + $pad) . '}';
					}, $replace);
					$pad = $repad;
				}

				// Obtain pattern modifiers to use and alter the regex accordingly
				$regex = preg_replace('/!(.*)!([a-z]*)/', '$1', $match);
				$regex_modifiers = preg_replace('/!(.*)!([a-z]*)/', '$2', $match);

				for ($i = 0, $size = strlen($regex_modifiers); $i < $size; ++$i)
				{
					if (strpos($modifiers, $regex_modifiers[$i]) === false)
					{
						$modifiers .= $regex_modifiers[$i];

						if ($regex_modifiers[$i] == 'e')
						{
							$fp_replace = "'" . str_replace("'", "\\'", $fp_replace) . "'";
						}
					}

					if ($regex_modifiers[$i] == 'e')
					{
						$replace = "'.$replace.'";
					}
				}

				$fp_match = str_replace(preg_quote($token, '!'), $regex, $fp_match);
				$fp_replace = str_replace($token, $replace, $fp_replace);

				$sp_match = str_replace(preg_quote($token, '!'), $sp_tokens[$token_type], $sp_match);

				// Prepend the board url to local relative links
				$replace_prepend = ($token_type === 'LOCAL_URL') ? generate_board_url() . '/' : '';

				$sp_replace = str_replace($token, $replace_prepend . '${' . ($n + 1) . '}', $sp_replace);
			}

			$fp_match = '!' . $fp_match . '!' . $modifiers;
			$sp_match = '!' . $sp_match . '!s' . (($utf8) ? 'u' : '');

			if (strpos($fp_match, 'e') !== false)
			{
				$fp_replace = str_replace("'.'", '', $fp_replace);
				$fp_replace = str_replace(".''.", '.', $fp_replace);
			}
		}
		else
		{
			// No replacement is present, no need for a second-pass pattern replacement
			// A simple str_replace will suffice
			$fp_match = '!' . $fp_match . '!' . $modifiers;
			$sp_match = $fp_replace;
			$sp_replace = '';
		}

		// Lowercase tags
		$bbcode_tag = preg_replace('/.*?\[([a-z0-9_-]+).*/i', '$1', $bbcode_match);
		$bbcode_search = preg_replace('/.*?\[([a-z0-9_-]+).*/i', '$1', $bbcode_match);

		if (!preg_match('/^[a-zA-Z0-9_-]+$/', $bbcode_tag))
		{
			global $user;
			trigger_error($user->lang['BBCODE_INVALID'] . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$fp_match = preg_replace_callback('#\[/?' . $bbcode_search . '#i', function ($match) {
			return strtolower($match[0]);
		}, $fp_match);
		$fp_replace = preg_replace_callback('#\[/?' . $bbcode_search . '#i', function ($match) {
			return strtolower($match[0]);
		}, $fp_replace);
		$sp_match = preg_replace_callback('#\[/?' . $bbcode_search . '#i', function ($match) {
			return strtolower($match[0]);
		}, $sp_match);
		$sp_replace = preg_replace_callback('#\[/?' . $bbcode_search . '#i', function ($match) {
			return strtolower($match[0]);
		}, $sp_replace);

		return array(
			'bbcode_tag'				=> $bbcode_tag,
			'first_pass_match'			=> $fp_match,
			'first_pass_replace'		=> $fp_replace,
			'second_pass_match'			=> $sp_match,
			'second_pass_replace'		=> $sp_replace
		);
	}
}
