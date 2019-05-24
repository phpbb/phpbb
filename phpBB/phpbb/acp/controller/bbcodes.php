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

use phpbb\exception\back_exception;
use phpbb\exception\form_invalid_exception;

class bbcodes
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher */
	protected $dispatcher;

	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\textformatter\cache_interface */
	protected $tf_cache;

	/** @var \phpbb\user */
	protected $user;

	/** @var string BBCode table */
	protected $bbcode_table;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\event\dispatcher				$dispatcher		Event dispatcher object
	 * @param \phpbb\acp\helper\controller			$helper			ACP Controller helper object
	 * @param \phpbb\language\language				$lang			Language object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\textformatter\cache_interface	$tf_cache		Textformatter cache object
	 * @param \phpbb\user							$user			User object
	 * @param string								$bbcode_table	BBCode table
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\event\dispatcher $dispatcher,
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\textformatter\cache_interface $tf_cache,
		\phpbb\user $user,
		$bbcode_table
	)
	{
		$this->cache		= $cache;
		$this->db			= $db;
		$this->dispatcher	= $dispatcher;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->tf_cache		= $tf_cache;
		$this->user			= $user;

		$this->bbcode_table	= $bbcode_table;
	}

	function main()
	{
		$this->lang->add_lang('acp/posting');

		// Set up general vars
		$action		= $this->request->variable('action', '');
		$submit		= $this->request->is_set_post('submit');
		$bbcode_id	= $this->request->variable('bbcode', 0);

		$form_key = 'acp_bbcodes';
		add_form_key($form_key);

		if ($submit && !check_form_key($form_key))
		{
			throw new form_invalid_exception('acp_bbcodes');
		}

		$bbcode_match = $bbcode_tpl = $bbcode_helpline = '';
		$display_on_posting = 0;
		$row = [];

		// Set up mode-specific vars
		switch ($action)
		{
			case 'edit':
				$sql = 'SELECT bbcode_match, bbcode_tpl, display_on_posting, bbcode_helpline
					FROM ' . $this->bbcode_table . '
					WHERE bbcode_id = ' . $bbcode_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row === false)
				{
					throw new back_exception(404, 'BBCODE_NOT_EXIST', 'acp_bbcodes');
				}

				$bbcode_tpl			= htmlspecialchars($row['bbcode_tpl']);
				$bbcode_match		= $row['bbcode_match'];
				$bbcode_helpline	= $row['bbcode_helpline'];
				$display_on_posting	= $row['display_on_posting'];
			break;

			/** @noinspection PhpMissingBreakStatementInspection */
			case 'modify':
				$sql = 'SELECT bbcode_id, bbcode_tag
					FROM ' . $this->bbcode_table . '
					WHERE bbcode_id = ' . $bbcode_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row === false)
				{
					throw new back_exception(404, 'BBCODE_NOT_EXIST', 'acp_bbcodes');
				}
			// No break here

			case 'create':
				$bbcode_tpl			= htmlspecialchars_decode($this->request->variable('bbcode_tpl', '', true));
				$bbcode_match		= $this->request->variable('bbcode_match', '');
				$bbcode_helpline	= $this->request->variable('bbcode_helpline', '', true);
				$display_on_posting	= $this->request->variable('display_on_posting', 0);
			break;
		}

		// Do major work
		switch ($action)
		{
			case 'edit':
			case 'add':
				$params = ['action' => ($action === 'add' ? 'create' : 'modify')];

				if ($bbcode_id)
				{
					$params['bbcode'] = $bbcode_id;
				}

				$tpl_ary = [
					'S_EDIT_BBCODE'		=> true,
					'U_BACK'			=> $this->helper->route('acp_bbcodes'),
					'U_ACTION'			=> $this->helper->route('acp_bbcodes', $params),

					'L_BBCODE_USAGE_EXPLAIN'	=> $this->lang->lang('BBCODE_USAGE_EXPLAIN', '<a href="#down">', '</a>'),
					'BBCODE_MATCH'				=> $bbcode_match,
					'BBCODE_TPL'				=> $bbcode_tpl,
					'BBCODE_HELPLINE'			=> $bbcode_helpline,
					'DISPLAY_ON_POSTING'		=> $display_on_posting,
				];

				$bbcode_tokens = ['TEXT', 'SIMPLETEXT', 'INTTEXT', 'IDENTIFIER', 'NUMBER', 'EMAIL', 'URL', 'LOCAL_URL', 'RELATIVE_URL', 'COLOR'];

				/**
				 * Modify custom bbcode template data before we display the add/edit form
				 *
				 * @event core.acp_bbcodes_edit_add
				 * @var string	action			Type of the action: add|edit
				 * @var array	tpl_ary			Array with custom bbcode add/edit data
				 * @var int		bbcode_id		When editing: the bbcode id,
				 *								when creating: 0
				 * @var array	bbcode_tokens	Array of bbcode tokens
				 * @since 3.1.0-a3
				 */
				$vars = ['action', 'tpl_ary', 'bbcode_id', 'bbcode_tokens'];
				extract($this->dispatcher->trigger_event('core.acp_bbcodes_edit_add', compact($vars)));

				$this->template->assign_vars($tpl_ary);

				foreach ($bbcode_tokens as $token)
				{
					$this->template->assign_block_vars('token', [
						'TOKEN'		=> '{' . $token . '}',
						'EXPLAIN'	=> ($token === 'LOCAL_URL') ? $this->lang->lang(['tokens', $token], generate_board_url() . '/') : $this->lang->lang(['tokens', $token]),
					]);
				}

				return $this->helper->render('acp_bbcodes.html', 'ACP_BBCODES');
			break;

			case 'modify':
			case 'create':
				$sql_ary = $hidden_fields = [];

				/**
				 * Modify custom bbcode data before the modify/create action
				 *
				 * @event core.acp_bbcodes_modify_create
				 * @var string	action				Type of the action: modify|create
				 * @var array	sql_ary				Array with new bbcode data
				 * @var int		bbcode_id			When editing: the bbcode id,
				 *									when creating: 0
				 * @var bool	display_on_posting	Display bbcode on posting form
				 * @var string	bbcode_match		The bbcode usage string to match
				 * @var string	bbcode_tpl			The bbcode HTML replacement string
				 * @var string	bbcode_helpline		The bbcode help line string
				 * @var array	hidden_fields		Array of hidden fields for use when
				 *									submitting form when $warn_text is true
				 * @since 3.1.0-a3
				 */
				$vars = [
					'action',
					'sql_ary',
					'bbcode_id',
					'display_on_posting',
					'bbcode_match',
					'bbcode_tpl',
					'bbcode_helpline',
					'hidden_fields',
				];
				extract($this->dispatcher->trigger_event('core.acp_bbcodes_modify_create', compact($vars)));

				$warn_text = preg_match('%<[^>]*\{text[\d]*\}[^>]*>%i', $bbcode_tpl);
				if (!$warn_text || confirm_box(true))
				{
					$data = $this->build_regexp($bbcode_match, $bbcode_tpl);

					// Make sure the user didn't pick a "bad" name for the BBCode tag.
					$hard_coded = ['code', 'quote', 'quote=', 'attachment', 'attachment=', 'b', 'i', 'url', 'url=', 'img', 'size', 'size=', 'color', 'color=', 'u', 'list', 'list=', 'email', 'email=', 'flash', 'flash='];

					if (($action === 'modify' && strtolower($data['bbcode_tag']) !== strtolower($row['bbcode_tag'])) || ($action === 'create'))
					{
						$sql = 'SELECT 1 as test
							FROM ' . $this->bbcode_table . '
							WHERE ' . $this->db->sql_lower_text('bbcode_tag') . " = '" . $this->db->sql_escape(strtolower($data['bbcode_tag'])) . "'";
						$result = $this->db->sql_query($sql);
						$info = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

						// Grab the end, interrogate the last closing tag
						if ($info['test'] === '1' || in_array(strtolower($data['bbcode_tag']), $hard_coded) || (preg_match('#\[/([^[]*)]$#', $bbcode_match, $regs) && in_array(strtolower($regs[1]), $hard_coded)))
						{
							throw new back_exception(400, 'BBCODE_INVALID_TAG_NAME', 'acp_bbcodes');
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
						throw new back_exception(400, 'BBCODE_OPEN_ENDED_TAG', 'acp_bbcodes');
					}

					if (strlen($data['bbcode_tag']) > 16)
					{
						throw new back_exception(400, 'BBCODE_TAG_TOO_LONG', 'acp_bbcodes');
					}

					if (strlen($bbcode_match) > 4000)
					{
						throw new back_exception(400, 'BBCODE_TAG_DEF_TOO_LONG', 'acp_bbcodes');
					}

					if (strlen($bbcode_helpline) > 255)
					{
						throw new back_exception(400, 'BBCODE_HELPLINE_TOO_LONG', 'acp_bbcodes');
					}

					$sql_ary = array_merge($sql_ary, [
						'bbcode_tag'				=> $data['bbcode_tag'],
						'bbcode_match'				=> $bbcode_match,
						'bbcode_tpl'				=> $bbcode_tpl,
						'display_on_posting'		=> $display_on_posting,
						'bbcode_helpline'			=> $bbcode_helpline,
						'first_pass_match'			=> $data['first_pass_match'],
						'first_pass_replace'		=> $data['first_pass_replace'],
						'second_pass_match'			=> $data['second_pass_match'],
						'second_pass_replace'		=> $data['second_pass_replace'],
					]);

					if ($action === 'create')
					{
						$sql = 'SELECT MAX(bbcode_id) as max_bbcode_id
							FROM ' . $this->bbcode_table;
						$result = $this->db->sql_query($sql);
						$row = $this->db->sql_fetchrow($result);
						$this->db->sql_freeresult($result);

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
							throw new back_exception(400, 'TOO_MANY_BBCODES', 'acp_bbcodes');
						}

						$sql_ary['bbcode_id'] = (int) $bbcode_id;

						$sql = 'INSERT INTO ' . $this->bbcode_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
						$this->db->sql_query($sql);
						$this->cache->destroy('sql', $this->bbcode_table);
						$this->tf_cache->invalidate();

						$lang = 'BBCODE_ADDED';
						$log_action = 'LOG_BBCODE_ADD';
					}
					else
					{
						$sql = 'UPDATE ' . $this->bbcode_table . '
							SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
							WHERE bbcode_id = ' . $bbcode_id;
						$this->db->sql_query($sql);
						$this->cache->destroy('sql', $this->bbcode_table);
						$this->tf_cache->invalidate();

						$lang = 'BBCODE_EDITED';
						$log_action = 'LOG_BBCODE_EDIT';
					}

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_action, false, [$data['bbcode_tag']]);

					/**
					 * Event after a BBCode has been added or updated
					 *
					 * @event core.acp_bbcodes_modify_create_after
					 * @var string	action		Type of the action: modify|create
					 * @var int		bbcode_id	The id of the added or updated bbcode
					 * @var array	sql_ary		Array with bbcode data (read only)
					 * @since 3.2.4-RC1
					 */
					$vars = [
						'action',
						'bbcode_id',
						'sql_ary',
					];
					extract($this->dispatcher->trigger_event('core.acp_bbcodes_modify_create_after', compact($vars)));

					return $this->helper->message_back($lang, 'acp_bbcodes');
				}
				else
				{
					confirm_box(false, $this->lang->lang('BBCODE_DANGER'), build_hidden_fields(array_merge($hidden_fields, [
						'action'				=> $action,
						'bbcode'				=> $bbcode_id,
						'bbcode_match'			=> $bbcode_match,
						'bbcode_tpl'			=> htmlspecialchars($bbcode_tpl),
						'bbcode_helpline'		=> $bbcode_helpline,
						'display_on_posting'	=> $display_on_posting,
					])), 'confirm_bbcode.html');

					return redirect($this->helper->route('acp_bbcodes'));
				}
			break;

			case 'delete':
				$sql = 'SELECT bbcode_tag
					FROM ' . $this->bbcode_table . '
					WHERE bbcode_id = ' . (int) $bbcode_id;
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				if ($row)
				{
					if (confirm_box(true))
					{
						$bbcode_tag = $row['bbcode_tag'];

						$sql = 'DELETE FROM ' . $this->bbcode_table . ' WHERE bbcode_id = ' . (int) $bbcode_id;
						$this->db->sql_query($sql);

						$this->cache->destroy('sql', $this->bbcode_table);

						$this->tf_cache->invalidate();

						$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_BBCODE_DELETE', false, [$bbcode_tag]);

						/**
						 * Event after a BBCode has been deleted
						 *
						 * @event core.acp_bbcodes_delete_after
						 * @var string	action		Type of the action: delete
						 * @var int		bbcode_id	The id of the deleted bbcode
						 * @var string	bbcode_tag	The tag of the deleted bbcode
						 * @since 3.2.4-RC1
						 */
						$vars = [
							'action',
							'bbcode_id',
							'bbcode_tag',
						];
						extract($this->dispatcher->trigger_event('core.acp_bbcodes_delete_after', compact($vars)));

						if ($this->request->is_ajax())
						{
							$json_response = new \phpbb\json_response;
							$json_response->send([
								'MESSAGE_TITLE'	=> $this->lang->lang('INFORMATION'),
								'MESSAGE_TEXT'	=> $this->lang->lang('BBCODE_DELETED'),
								'REFRESH_DATA'	=> [
									'time'	=> 3,
								],
							]);
						}

						return $this->helper->message_back('BBCODE_DELETED', 'acp_bbcodes');
					}
					else
					{
						confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
							'action'	=> $action,
							'bbcode'	=> $bbcode_id,
						]));

						return redirect($this->helper->route('acp_bbcodes'));
					}
				}
			break;
		}

		$template_data = ['U_ACTION' => $this->helper->route('acp_bbcodes', ['action' => 'add'])];

		$sql_ary = [
			'SELECT'	=> 'b.*',
			'FROM'		=> [$this->bbcode_table => 'b'],
			'ORDER_BY'	=> 'b.bbcode_tag',
		];

		/**
		 * Modify custom bbcode template data before we display the form
		 *
		 * @event core.acp_bbcodes_display_form
		 * @var string	action			Type of the action: modify|create
		 * @var array	sql_ary			The SQL array to get custom bbcode data
		 * @var array	template_data	Array with form template data
		 * @since 3.1.0-a3
		 */
		$vars = ['action', 'sql_ary', 'template_data'];
		extract($this->dispatcher->trigger_event('core.acp_bbcodes_display_form', compact($vars)));

		$this->template->assign_vars($template_data);

		$sql = $this->db->sql_build_query('SELECT', $sql_ary);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$bbcodes_array = [
				'BBCODE_TAG'		=> $row['bbcode_tag'],
				'U_EDIT'			=> $this->helper->route('acp_bbcodes', ['action' => 'edit', 'bbcode' => $row['bbcode_id']]),
				'U_DELETE'			=> $this->helper->route('acp_bbcodes', ['action' => 'delete', 'bbcode' => $row['bbcode_id']]),
			];

			/**
			 * Modify display of custom bbcodes in the form
			 *
			 * @event core.acp_bbcodes_display_bbcodes
			 * @var array	row				Array with current bbcode data
			 * @var array	bbcodes_array	Array of bbcodes template data
			 * @since 3.1.0-a3
			 */
			$vars = ['bbcodes_array', 'row'];
			extract($this->dispatcher->trigger_event('core.acp_bbcodes_display_bbcodes', compact($vars)));

			$this->template->assign_block_vars('bbcodes', $bbcodes_array);
		}
		$this->db->sql_freeresult($result);

		return $this->helper->render('acp_bbcodes.html', 'ACP_BBCODES');
	}

	/**
	 * Build regular expression for custom bbcode.
	 *
	 * @param string	$bbcode_match
	 * @param string	$bbcode_tpl
	 * @return array
	 */
	function build_regexp(&$bbcode_match, &$bbcode_tpl)
	{
		$bbcode_match = trim($bbcode_match);
		$bbcode_tag = preg_replace('/.*?\[([a-z0-9_-]+).*/i', '$1', $bbcode_match);

		if (!preg_match('/^[a-zA-Z0-9_-]+$/', $bbcode_tag))
		{
			throw new back_exception(400, 'BBCODE_INVALID', 'acp_bbcodes');
		}

		return [
			'bbcode_tag'				=> $bbcode_tag,
			'first_pass_match'			=> '/(?!)/',
			'first_pass_replace'		=> '',

			// Use a non-matching, valid regexp to effectively disable this BBCode
			'second_pass_match'			=> '/(?!)/',
			'second_pass_replace'		=> '',
		];
	}
}
