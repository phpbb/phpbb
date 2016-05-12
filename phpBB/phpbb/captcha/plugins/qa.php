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

namespace phpbb\captcha\plugins;

/**
* And now to something completely different. Let's make a captcha without extending the abstract class.
* QA CAPTCHA sample implementation
*/
class qa
{
	var $confirm_id;
	var $answer;
	var $question_ids;
	var $question_text;
	var $question_lang;
	var $question_strict;
	var $attempts = 0;
	var $type;
	// dirty trick: 0 is false, but can still encode that the captcha is not yet validated
	var $solved = 0;

	protected $table_captcha_questions;
	protected $table_captcha_answers;
	protected $table_qa_confirm;

	/**
	* @var string name of the service.
	*/
	protected $service_name;

	/**
	* Constructor
	*
	* @param string $table_captcha_questions
	* @param string $table_captcha_answers
	* @param string $table_qa_confirm
	*/
	function __construct($table_captcha_questions, $table_captcha_answers, $table_qa_confirm)
	{
		$this->table_captcha_questions = $table_captcha_questions;
		$this->table_captcha_answers = $table_captcha_answers;
		$this->table_qa_confirm = $table_qa_confirm;
	}

	/**
	* @param int $type  as per the CAPTCHA API docs, the type
	*/
	function init($type)
	{
		global $config, $db, $user, $request;

		// load our language file
		$user->add_lang('captcha_qa');

		// read input
		$this->confirm_id = $request->variable('qa_confirm_id', '');
		$this->answer = $request->variable('qa_answer', '', true);

		$this->type = (int) $type;
		$this->question_lang = $user->lang_name;

		// we need all defined questions - shouldn't be too many, so we can just grab them
		// try the user's lang first
		$sql = 'SELECT question_id
			FROM ' . $this->table_captcha_questions . "
			WHERE lang_iso = '" . $db->sql_escape($user->lang_name) . "'";
		$result = $db->sql_query($sql, 3600);

		while ($row = $db->sql_fetchrow($result))
		{
			$this->question_ids[$row['question_id']] = $row['question_id'];
		}
		$db->sql_freeresult($result);

		// fallback to the board default lang
		if (!sizeof($this->question_ids))
		{
			$this->question_lang = $config['default_lang'];

			$sql = 'SELECT question_id
				FROM ' . $this->table_captcha_questions . "
				WHERE lang_iso = '" . $db->sql_escape($config['default_lang']) . "'";
			$result = $db->sql_query($sql, 7200);

			while ($row = $db->sql_fetchrow($result))
			{
				$this->question_ids[$row['question_id']] = $row['question_id'];
			}
			$db->sql_freeresult($result);
		}

		// final fallback to any language
		if (!sizeof($this->question_ids))
		{
			$this->question_lang = '';

			$sql = 'SELECT q.question_id, q.lang_iso
				FROM ' . $this->table_captcha_questions . ' q, ' . $this->table_captcha_answers . ' a
				WHERE q.question_id = a.question_id
				GROUP BY lang_iso';
			$result = $db->sql_query($sql, 7200);

			while ($row = $db->sql_fetchrow($result))
			{
				if (empty($this->question_lang))
				{
					$this->question_lang = $row['lang_iso'];
				}
				$this->question_ids[$row['question_id']] = $row['question_id'];
			}
			$db->sql_freeresult($result);
		}

		// okay, if there is a confirm_id, we try to load that confirm's state. If not, we try to find one
		if (!$this->load_answer() && (!$this->load_confirm_id() || !$this->load_answer()))
		{
			// we have no valid confirm ID, better get ready to ask something
			$this->select_question();
		}
	}

	/**
	* See if the captcha has created its tables.
	*/
	public function is_installed()
	{
		global $phpbb_container;

		$db_tool = $phpbb_container->get('dbal.tools');

		return $db_tool->sql_table_exists($this->table_captcha_questions);
	}

	/**
	*  API function - for the captcha to be available, it must have installed itself and there has to be at least one question in the board's default lang
	*/
	public function is_available()
	{
		global $config, $db, $user;

		// load language file for pretty display in the ACP dropdown
		$user->add_lang('captcha_qa');

		if (!$this->is_installed())
		{
			return false;
		}

		$sql = 'SELECT COUNT(question_id) AS question_count
			FROM ' . $this->table_captcha_questions . "
			WHERE lang_iso = '" . $db->sql_escape($config['default_lang']) . "'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return ((bool) $row['question_count']);
	}

	/**
	*  API function
	*/
	function has_config()
	{
		return true;
	}

	/**
	*  API function
	*/
	static public function get_name()
	{
		return 'CAPTCHA_QA';
	}

	/**
	* @return string the name of the service corresponding to the plugin
	*/
	function get_service_name()
	{
		return $this->service_name;
	}

	/**
	* Set the name of the plugin
	*
	* @param string $name
	*/
	public function set_name($name)
	{
		$this->service_name = $name;
	}

	/**
	*  API function - not needed as we don't display an image
	*/
	function execute_demo()
	{
	}

	/**
	*  API function - not needed as we don't display an image
	*/
	function execute()
	{
	}

	/**
	*  API function - send the question to the template
	*/
	function get_template()
	{
		global $phpbb_log, $template, $user;

		if ($this->is_solved())
		{
			return false;
		}
		else if (empty($this->question_text) || !count($this->question_ids))
		{
			/** @var \phpbb\log\log_interface $phpbb_log */
			$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'LOG_ERROR_CAPTCHA', time(), array($user->lang('CONFIRM_QUESTION_MISSING')));
			return false;
		}
		else
		{
			$template->assign_vars(array(
				'QA_CONFIRM_QUESTION'	=> $this->question_text,
				'QA_CONFIRM_ID'			=> $this->confirm_id,
				'S_CONFIRM_CODE'		=> true,
				'S_TYPE'				=> $this->type,
			));

			return 'captcha_qa.html';
		}
	}

	/**
	*  API function - we just display a mockup so that the captcha doesn't need to be installed
	*/
	function get_demo_template()
	{
		global $config, $db, $template;

		if ($this->is_available())
		{
			$sql = 'SELECT question_text
				FROM ' . $this->table_captcha_questions . "
				WHERE lang_iso = '" . $db->sql_escape($config['default_lang']) . "'";
			$result = $db->sql_query_limit($sql, 1);
			if ($row = $db->sql_fetchrow($result))
			{
				$template->assign_vars(array(
					'QA_CONFIRM_QUESTION'		=> $row['question_text'],
				));
			}
			$db->sql_freeresult($result);
		}
		return 'captcha_qa_acp_demo.html';
	}

	/**
	*  API function
	*/
	function get_hidden_fields()
	{
		$hidden_fields = array();

		// this is required - otherwise we would forget about the captcha being already solved
		if ($this->solved)
		{
			$hidden_fields['qa_answer'] = $this->answer;
		}
		$hidden_fields['qa_confirm_id'] = $this->confirm_id;

		return $hidden_fields;
	}

	/**
	*  API function
	*/
	function garbage_collect($type = 0)
	{
		global $db;

		$sql = 'SELECT c.confirm_id
			FROM ' . $this->table_qa_confirm . ' c
			LEFT JOIN ' . SESSIONS_TABLE . ' s
				ON (c.session_id = s.session_id)
			WHERE s.session_id IS NULL' .
				((empty($type)) ? '' : ' AND c.confirm_type = ' . (int) $type);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$sql_in = array();

			do
			{
				$sql_in[] = (string) $row['confirm_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			if (sizeof($sql_in))
			{
				$sql = 'DELETE FROM ' . $this->table_qa_confirm . '
					WHERE ' . $db->sql_in_set('confirm_id', $sql_in);
				$db->sql_query($sql);
			}
		}
		$db->sql_freeresult($result);
	}

	/**
	*  API function - we don't drop the tables here, as that would cause the loss of all entered questions.
	*/
	function uninstall()
	{
		$this->garbage_collect(0);
	}

	/**
	*  API function - set up shop
	*/
	function install()
	{
		global $phpbb_container;

		$db_tool = $phpbb_container->get('dbal.tools');
		$schemas = array(
				$this->table_captcha_questions		=> array (
					'COLUMNS' => array(
						'question_id'	=> array('UINT', null, 'auto_increment'),
						'strict'		=> array('BOOL', 0),
						'lang_id'		=> array('UINT', 0),
						'lang_iso'		=> array('VCHAR:30', ''),
						'question_text'	=> array('TEXT_UNI', ''),
					),
					'PRIMARY_KEY'		=> 'question_id',
					'KEYS'				=> array(
						'lang'			=> array('INDEX', 'lang_iso'),
					),
				),
				$this->table_captcha_answers		=> array (
					'COLUMNS' => array(
						'question_id'	=> array('UINT', 0),
						'answer_text'	=> array('STEXT_UNI', ''),
					),
					'KEYS'				=> array(
						'qid'			=> array('INDEX', 'question_id'),
					),
				),
				$this->table_qa_confirm		=> array (
					'COLUMNS' => array(
						'session_id'	=> array('CHAR:32', ''),
						'confirm_id'	=> array('CHAR:32', ''),
						'lang_iso'		=> array('VCHAR:30', ''),
						'question_id'	=> array('UINT', 0),
						'attempts'		=> array('UINT', 0),
						'confirm_type'	=> array('USINT', 0),
					),
					'KEYS'				=> array(
						'session_id'			=> array('INDEX', 'session_id'),
						'lookup'				=> array('INDEX', array('confirm_id', 'session_id', 'lang_iso')),
					),
					'PRIMARY_KEY'		=> 'confirm_id',
				),
		);

		foreach ($schemas as $table => $schema)
		{
			if (!$db_tool->sql_table_exists($table))
			{
				$db_tool->sql_create_table($table, $schema);
			}
		}
	}

	/**
	*  API function - see what has to be done to validate
	*/
	function validate()
	{
		global $phpbb_log, $user;

		$error = '';

		if (!sizeof($this->question_ids))
		{
			/** @var \phpbb\log\log_interface $phpbb_log */
			$phpbb_log->add('critical', $user->data['user_id'], $user->ip, 'LOG_ERROR_CAPTCHA', time(), array($user->lang('CONFIRM_QUESTION_MISSING')));
			return $user->lang('CONFIRM_QUESTION_MISSING');
		}

		if (!$this->confirm_id)
		{
			$error = $user->lang['CONFIRM_QUESTION_WRONG'];
		}
		else
		{
			if ($this->check_answer())
			{
				$this->solved = true;
			}
			else
			{
				$error = $user->lang['CONFIRM_QUESTION_WRONG'];
			}
		}

		if (strlen($error))
		{
			// okay, incorrect answer. Let's ask a new question.
			$this->new_attempt();
			$this->solved = false;

			return $error;
		}
		else
		{
			return false;
		}
	}

	/**
	*  Select a question
	*/
	function select_question()
	{
		global $db, $user;

		if (!sizeof($this->question_ids))
		{
			return;
		}
		$this->confirm_id = md5(unique_id($user->ip));
		$this->question = (int) array_rand($this->question_ids);

		$sql = 'INSERT INTO ' . $this->table_qa_confirm . ' ' . $db->sql_build_array('INSERT', array(
			'confirm_id'	=> (string) $this->confirm_id,
			'session_id'	=> (string) $user->session_id,
			'lang_iso'		=> (string) $this->question_lang,
			'confirm_type'	=> (int) $this->type,
			'question_id'	=> (int) $this->question,
		));
		$db->sql_query($sql);

		$this->load_answer();
	}

	/**
	* New Question, if desired.
	*/
	function reselect_question()
	{
		global $db, $user;

		if (!sizeof($this->question_ids))
		{
			return;
		}

		$this->question = (int) array_rand($this->question_ids);
		$this->solved = 0;

		$sql = 'UPDATE ' . $this->table_qa_confirm . '
			SET question_id = ' . (int) $this->question . "
			WHERE confirm_id = '" . $db->sql_escape($this->confirm_id) . "'
				AND session_id = '" . $db->sql_escape($user->session_id) . "'";
		$db->sql_query($sql);

		$this->load_answer();
	}

	/**
	* Wrong answer, so we increase the attempts and use a different question.
	*/
	function new_attempt()
	{
		global $db, $user;

		// yah, I would prefer a stronger rand, but this should work
		$this->question = (int) array_rand($this->question_ids);
		$this->solved = 0;

		$sql = 'UPDATE ' . $this->table_qa_confirm . '
			SET question_id = ' . (int) $this->question . ",
				attempts = attempts + 1
			WHERE confirm_id = '" . $db->sql_escape($this->confirm_id) . "'
				AND session_id = '" . $db->sql_escape($user->session_id) . "'";
		$db->sql_query($sql);

		$this->load_answer();
	}


	/**
	* See if there is already an entry for the current session.
	*/
	function load_confirm_id()
	{
		global $db, $user;

		$sql = 'SELECT confirm_id
			FROM ' . $this->table_qa_confirm . "
			WHERE
				session_id = '" . $db->sql_escape($user->session_id) . "'
				AND lang_iso = '" . $db->sql_escape($this->question_lang) . "'
				AND confirm_type = " . $this->type;
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$this->confirm_id = $row['confirm_id'];
			return true;
		}
		return false;
	}

	/**
	* Look up everything we need and populate the instance variables.
	*/
	function load_answer()
	{
		global $db, $user;

		if (!strlen($this->confirm_id) || !sizeof($this->question_ids))
		{
			return false;
		}

		$sql = 'SELECT con.question_id, attempts, question_text, strict
			FROM ' . $this->table_qa_confirm . ' con, ' . $this->table_captcha_questions . " qes
			WHERE con.question_id = qes.question_id
				AND confirm_id = '" . $db->sql_escape($this->confirm_id) . "'
				AND session_id = '" . $db->sql_escape($user->session_id) . "'
				AND qes.lang_iso = '" . $db->sql_escape($this->question_lang) . "'
				AND confirm_type = " . $this->type;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$this->question = $row['question_id'];

			$this->attempts = $row['attempts'];
			$this->question_strict = $row['strict'];
			$this->question_text = $row['question_text'];

			return true;
		}

		return false;
	}

	/**
	*  The actual validation
	*/
	function check_answer()
	{
		global $db, $request;

		$answer = ($this->question_strict) ? $request->variable('qa_answer', '', true) : utf8_clean_string($request->variable('qa_answer', '', true));

		$sql = 'SELECT answer_text
			FROM ' . $this->table_captcha_answers . '
			WHERE question_id = ' . (int) $this->question;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$solution = ($this->question_strict) ? $row['answer_text'] : utf8_clean_string($row['answer_text']);

			if ($solution === $answer)
			{
				$this->solved = true;

				break;
			}
		}
		$db->sql_freeresult($result);

		return $this->solved;
	}

	/**
	*  API function
	*/
	function get_attempt_count()
	{
		return $this->attempts;
	}

	/**
	*  API function
	*/
	function reset()
	{
		global $db, $user;

		$sql = 'DELETE FROM ' . $this->table_qa_confirm . "
			WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
				AND confirm_type = " . (int) $this->type;
		$db->sql_query($sql);

		// we leave the class usable by generating a new question
		$this->select_question();
	}

	/**
	*  API function
	*/
	function is_solved()
	{
		global $request;

		if ($request->variable('qa_answer', false) && $this->solved === 0)
		{
			$this->validate();
		}

		return (bool) $this->solved;
	}

	/**
	*  API function - The ACP backend, this marks the end of the easy methods
	*/
	function acp_page($id, &$module)
	{
		global $config, $request, $phpbb_log, $template, $user;

		$user->add_lang('acp/board');
		$user->add_lang('captcha_qa');

		if (!self::is_installed())
		{
			$this->install();
		}

		$module->tpl_name = 'captcha_qa_acp';
		$module->page_title = 'ACP_VC_SETTINGS';
		$form_key = 'acp_captcha';
		add_form_key($form_key);

		$submit = $request->variable('submit', false);
		$question_id = $request->variable('question_id', 0);
		$action = $request->variable('action', '');

		// we have two pages, so users might want to navigate from one to the other
		$list_url = $module->u_action . "&amp;configure=1&amp;select_captcha=" . $this->get_service_name();

		$template->assign_vars(array(
			'U_ACTION'		=> $module->u_action,
			'QUESTION_ID'	=> $question_id ,
			'CLASS'			=> $this->get_service_name(),
		));

		// show the list?
		if (!$question_id && $action != 'add')
		{
			$this->acp_question_list($module);
		}
		else if ($question_id && $action == 'delete')
		{
			if ($this->get_service_name() !== $config['captcha_plugin'] || !$this->acp_is_last($question_id))
			{
				if (confirm_box(true))
				{
					$this->acp_delete_question($question_id);

					trigger_error($user->lang['QUESTION_DELETED'] . adm_back_link($list_url));
				}
				else
				{
					confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
						'question_id'		=> $question_id,
						'action'			=> $action,
						'configure'			=> 1,
						'select_captcha'	=> $this->get_service_name(),
						))
					);
				}
			}
			else
			{
				trigger_error($user->lang['QA_LAST_QUESTION'] . adm_back_link($list_url), E_USER_WARNING);
			}
		}
		else
		{
			// okay, show the editor
			$question_input = $this->acp_get_question_input();
			$langs = $this->get_languages();

			foreach ($langs as $lang => $entry)
			{
				$template->assign_block_vars('langs', array(
					'ISO' => $lang,
					'NAME' => $entry['name'],
				));
			}

			$template->assign_vars(array(
				'U_LIST' => $list_url,
			));

			if ($question_id)
			{
				if ($question = $this->acp_get_question_data($question_id))
				{
					$template->assign_vars(array(
						'QUESTION_TEXT'		=> ($question_input['question_text']) ? $question_input['question_text'] : $question['question_text'],
						'LANG_ISO'			=> ($question_input['lang_iso']) ? $question_input['lang_iso'] : $question['lang_iso'],
						'STRICT'			=> (isset($_REQUEST['strict'])) ? $question_input['strict'] : $question['strict'],
						'ANSWERS'			=> implode("\n", $question['answers']),
					));
				}
				else
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($list_url));
				}
			}
			else
			{
				$template->assign_vars(array(
					'QUESTION_TEXT'		=> $question_input['question_text'],
					'LANG_ISO'			=> $question_input['lang_iso'],
					'STRICT'			=> $question_input['strict'],
					'ANSWERS'			=> (is_array($question_input['answers'])) ? implode("\n", $question_input['answers']) : '',
				));
			}

			if ($submit && check_form_key($form_key))
			{
				if (!$this->validate_input($question_input))
				{
					$template->assign_vars(array(
						'S_ERROR'			=> true,
					));
				}
				else
				{
					if ($question_id)
					{
						$this->acp_update_question($question_input, $question_id);
					}
					else
					{
						$this->acp_add_question($question_input);
					}

					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LOG_CONFIG_VISUAL');
					trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($list_url));
				}
			}
			else if ($submit)
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($list_url), E_USER_WARNING);
			}
		}
	}

	/**
	*  This handles the list overview
	*/
	function acp_question_list(&$module)
	{
		global $db, $template;

		$sql = 'SELECT *
			FROM ' . $this->table_captcha_questions;
		$result = $db->sql_query($sql);

		$template->assign_vars(array(
			'S_LIST'			=> true,
		));

		while ($row = $db->sql_fetchrow($result))
		{
			$url = $module->u_action . "&amp;question_id={$row['question_id']}&amp;configure=1&amp;select_captcha=" . $this->get_service_name() . '&amp;';

			$template->assign_block_vars('questions', array(
				'QUESTION_TEXT'		=> $row['question_text'],
				'QUESTION_ID'		=> $row['question_id'],
				'QUESTION_LANG'		=> $row['lang_iso'],
				'U_DELETE'			=> "{$url}action=delete",
				'U_EDIT'			=> "{$url}action=edit",
			));
		}
		$db->sql_freeresult($result);
	}

	/**
	*  Grab a question and bring it into a format the editor understands
	*/
	function acp_get_question_data($question_id)
	{
		global $db;

		if ($question_id)
		{
			$sql = 'SELECT *
				FROM ' . $this->table_captcha_questions . '
				WHERE question_id = ' . $question_id;
			$result = $db->sql_query($sql);
			$question = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$question)
			{
				return false;
			}

			$question['answers'] = array();

			$sql = 'SELECT *
				FROM ' . $this->table_captcha_answers . '
				WHERE question_id = ' . $question_id;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$question['answers'][] = $row['answer_text'];
			}
			$db->sql_freeresult($result);

			return $question;
		}

		return false;
	}

	/**
	*  Grab a question from input and bring it into a format the editor understands
	*/
	function acp_get_question_input()
	{
		global $request;

		$answers = $request->variable('answers', '', true);

		// Convert answers into array and filter if answers are set
		if (strlen($answers))
		{
			$answers = array_filter(array_map('trim', explode("\n", $answers)), function ($value) {
				return $value !== '';
			});
		}

		$question = array(
			'question_text'	=> $request->variable('question_text', '', true),
			'strict'		=> $request->variable('strict', false),
			'lang_iso'		=> $request->variable('lang_iso', ''),
			'answers'		=> $answers,
		);
		return $question;
	}

	/**
	*  Update a question.
	* param mixed $data : an array as created from acp_get_question_input or acp_get_question_data
	*/
	function acp_update_question($data, $question_id)
	{
		global $db, $cache;

		// easier to delete all answers than to figure out which to update
		$sql = 'DELETE FROM ' . $this->table_captcha_answers . " WHERE question_id = $question_id";
		$db->sql_query($sql);

		$langs = $this->get_languages();
		$question_ary = $data;
		$question_ary['lang_id'] = $langs[$question_ary['lang_iso']]['id'];
		unset($question_ary['answers']);

		$sql = 'UPDATE ' . $this->table_captcha_questions . '
			SET ' . $db->sql_build_array('UPDATE', $question_ary) . "
			WHERE question_id = $question_id";
		$db->sql_query($sql);

		$this->acp_insert_answers($data, $question_id);

		$cache->destroy('sql', $this->table_captcha_questions);
	}

	/**
	*  Insert a question.
	* param mixed $data : an array as created from acp_get_question_input or acp_get_question_data
	*/
	function acp_add_question($data)
	{
		global $db, $cache;

		$langs = $this->get_languages();
		$question_ary = $data;

		$question_ary['lang_id'] = $langs[$data['lang_iso']]['id'];
		unset($question_ary['answers']);

		$sql = 'INSERT INTO ' . $this->table_captcha_questions . ' ' . $db->sql_build_array('INSERT', $question_ary);
		$db->sql_query($sql);

		$question_id = $db->sql_nextid();

		$this->acp_insert_answers($data, $question_id);

		$cache->destroy('sql', $this->table_captcha_questions);
	}

	/**
	*  Insert the answers.
	* param mixed $data : an array as created from acp_get_question_input or acp_get_question_data
	*/
	function acp_insert_answers($data, $question_id)
	{
		global $db, $cache;

		foreach ($data['answers'] as $answer)
		{
			$answer_ary = array(
				'question_id'	=> $question_id,
				'answer_text'	=> $answer,
			);

			$sql = 'INSERT INTO ' . $this->table_captcha_answers . ' ' . $db->sql_build_array('INSERT', $answer_ary);
			$db->sql_query($sql);
		}

		$cache->destroy('sql', $this->table_captcha_answers);
	}

	/**
	*  Delete a question.
	*/
	function acp_delete_question($question_id)
	{
		global $db, $cache;

		$tables = array($this->table_captcha_questions, $this->table_captcha_answers);

		foreach ($tables as $table)
		{
			$sql = "DELETE FROM $table
				WHERE question_id = $question_id";
			$db->sql_query($sql);
		}

		$cache->destroy('sql', $tables);
	}

	/**
	*  Check if the entered data can be inserted/used
	* param mixed $data : an array as created from acp_get_question_input or acp_get_question_data
	*/
	function validate_input($question_data)
	{
		$langs = $this->get_languages();

		if (!isset($question_data['lang_iso']) ||
			!isset($question_data['question_text']) ||
			!isset($question_data['strict']) ||
			!isset($question_data['answers']))
		{
			return false;
		}

		if (!isset($langs[$question_data['lang_iso']]) ||
			!strlen($question_data['question_text']) ||
			!sizeof($question_data['answers']) ||
			!is_array($question_data['answers']))
		{
			return false;
		}

		return true;
	}

	/**
	* List the installed language packs
	*/
	function get_languages()
	{
		global $db;

		$sql = 'SELECT *
			FROM ' . LANG_TABLE;
		$result = $db->sql_query($sql);

		$langs = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$langs[$row['lang_iso']] = array(
				'name'	=> $row['lang_local_name'],
				'id'	=> (int) $row['lang_id'],
			);
		}
		$db->sql_freeresult($result);

		return $langs;
	}



	/**
	*  See if there is a question other than the one we have
	*/
	function acp_is_last($question_id)
	{
		global $config, $db;

		if ($question_id)
		{
			$sql = 'SELECT question_id
				FROM ' . $this->table_captcha_questions . "
				WHERE lang_iso = '" . $db->sql_escape($config['default_lang']) . "'
					AND  question_id <> " .  (int) $question_id;
			$result = $db->sql_query_limit($sql, 1);
			$question = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			if (!$question)
			{
				return true;
			}
			return false;
		}
	}
}
