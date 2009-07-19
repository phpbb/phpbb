<?php
/**
*
* @package VC
* @version $Id: captcha_abstract.php 9709 2009-06-30 14:23:16Z Kellanved $
* @copyright (c) 2006, 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

global $table_prefix;
define('QUESTIONS_TABLE',	$table_prefix . 'captcha_questions');
define('ANSWERS_TABLE',		$table_prefix . 'captcha_answers');
define('QA_CONFIRM_TABLE',	$table_prefix . 'qa_confirm');



/**
* QA CAPTCHA sample implementation
*
* @package VC
*/
class phpbb_captcha_qa
{
	var $confirm_id;
	var $confirm_code;
	var $answer;
	var $question_ids;
	var $question_text;
	var $question_lang;
	var $question_strict;
	var $attempts = 0;
	var $type;
	var $solved = 0;
	var $captcha_vars = false;

	function init($type)
	{
		global $config, $db, $user;

		$user->add_lang('captcha_qa');
		// read input
		$this->confirm_id = request_var('confirm_id', '');
		$this->answer = request_var('answer', '');

		$this->type = (int) $type;
		$this->question_lang = $user->data['user_lang'];

		$sql = 'SELECT question_id FROM ' . QUESTIONS_TABLE . ' WHERE lang_iso = \'' . $db->sql_escape($user->data['user_lang']) . '\''; 
		$result = $db->sql_query($sql, 3600);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->question_ids[$row['question_id']] = $row['question_id'];
		}
		$db->sql_freeresult($result);
		if (!sizeof($this->question_ids))
		{
			$this->question_lang = $config['default_lang'];
			$sql = 'SELECT question_id FROM ' . QUESTIONS_TABLE . ' WHERE lang_iso = \'' . $db->sql_escape($config['default_lang']) . '\''; 
			$result = $db->sql_query($sql, 7200);
			while ($row = $db->sql_fetchrow($result))
			{
				$this->question_ids[$row['question_id']] = $row['question_id'];
			}
			$db->sql_freeresult($result);
		}

		if (!strlen($this->confirm_id) || !$this->load_answer())
		{
			// we have no confirm ID, better get ready to display something
			$this->select_question();
		}
	}
	
	
	function &get_instance()
	{
		$instance =& new phpbb_captcha_qa();
		return $instance;
	}


	function is_installed()
	{
		global $db, $phpbb_root_path, $phpEx;

		if (!class_exists('phpbb_db_tools'))
		{
			include("$phpbb_root_path/includes/db/db_tools.$phpEx");
		}
		$db_tool = new phpbb_db_tools($db);
		return $db_tool->sql_table_exists(QUESTIONS_TABLE);
	}
	
	function is_available()
	{
		global $config, $db, $phpbb_root_path, $phpEx, $user;
		
		$user->add_lang('captcha_qa');
		
		if (!self::is_installed())
		{
			return false;
		}
		$sql = 'SELECT COUNT(question_id) as count FROM ' . QUESTIONS_TABLE . ' WHERE lang_iso = \'' . $db->sql_escape($config['default_lang']) . '\''; 
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		return ((bool) $row['count']);
	}

	function get_name()
	{
		return 'CAPTCHA_QA';
	}

	function get_class_name()
	{
		return 'phpbb_captcha_qa';
	}


	function execute_demo()
	{
	}

	function execute()
	{
	}

	function get_template()
	{
		global $config, $user, $template, $phpEx, $phpbb_root_path;
		
		$template->assign_vars(array(
			'CONFIRM_QUESTION'			=> $this->question_text,
			'CONFIRM_ID'				=> $this->confirm_id,
			'S_CONFIRM_CODE'			=> true,
			'S_TYPE'					=> $this->type,
		));

		return 'captcha_qa.html';
	}

	function get_demo_template($id)
	{
		return 'captcha_qa_acp_demo.html';
	}

	function get_hidden_fields()
	{
		$hidden_fields = array();

		// this is required - otherwise we would forget about the captcha being already solved
		if ($this->solved)
		{
			$hidden_fields['answer'] = $this->answer;
		}
		$hidden_fields['confirm_id'] = $this->confirm_id;
		return $hidden_fields;
	}

	function garbage_collect($type)
	{
		global $db, $config;

		$sql = 'SELECT DISTINCT c.session_id
			FROM ' . QA_CONFIRM_TABLE . ' c
			LEFT JOIN ' . SESSIONS_TABLE . ' s ON (c.session_id = s.session_id)
			WHERE s.session_id IS NULL' .
				((empty($type)) ? '' : ' AND c.confirm_type = ' . (int) $type);
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			$sql_in = array();
			do
			{
				$sql_in[] = (string) $row['session_id'];
			}
			while ($row = $db->sql_fetchrow($result));

			if (sizeof($sql_in))
			{
				$sql = 'DELETE FROM ' . QA_CONFIRM_TABLE . '
					WHERE ' . $db->sql_in_set('session_id', $sql_in);
				$db->sql_query($sql);
			}
		}
		$db->sql_freeresult($result);
	}

	function uninstall()
	{
		$this->garbage_collect(0);
	}

	function install()
	{
		global $db, $phpbb_root_path, $phpEx;
		
		if (!class_exists('phpbb_db_tools'))
		{
			include("$phpbb_root_path/includes/db/db_tools.$phpEx");
		}
		$db_tool = new phpbb_db_tools($db);
		$tables = array(QUESTIONS_TABLE, ANSWERS_TABLE, QA_CONFIRM_TABLE);
		
		$schemas = array(
				QUESTIONS_TABLE		=> array (
								'COLUMNS' => array(
									'question_id'	=> array('UINT', Null, 'auto_increment'),
									'strict'		=> array('BOOL', 0),
									'lang_id'		=> array('UINT', 0),
									'lang_iso'		=> array('VCHAR:30', 0),
									'question_text'	=> array('TEXT', 0),
								),
								'PRIMARY_KEY'		=> 'question_id',
								'KEYS'				=> array(
									'question_id'			=> array('INDEX', array('question_id', 'lang_iso')),
								),
				),
				ANSWERS_TABLE		=> array (
								'COLUMNS' => array(
									'question_id'	=> array('UINT', 0),
									'answer_text'	=> array('TEXT', 0),
								),
								'KEYS'				=> array(
									'question_id'			=> array('INDEX', 'question_id'),
								),
				),
				QA_CONFIRM_TABLE		=> array (
								'COLUMNS' => array(
									'session_id'	=> array('CHAR:32', ''),
									'confirm_id'	=> array('CHAR:32', ''),
									'lang_iso'		=> array('VCHAR:30', 0),
									'question_id'	=> array('UINT', 0),
									'attempts'		=> array('UINT', 0),
									'confirm_type'	=> array('USINT', 0),
								),
								'KEYS'				=> array(
									'confirm_id'			=> array('INDEX', 'confirm_id'),
									'lookup'				=> array('INDEX', array('confirm_id', 'session_id', 'lang_iso')),
								),
								'PRIMARY_KEY'		=> 'confirm_id',
				),
		);
		

		foreach($schemas as $table => $schema)
		{
			if (!$db_tool->sql_table_exists($table))
			{
				$db_tool->sql_create_table($table, $schema);
			}
		}
	}


	function validate()
	{
		global $config, $db, $user;
		
		$error = '';
		if (!$this->confirm_id)
		{
			$error = $user->lang['CONFIRM_QUESTION_WRONG'];
		}
		else
		{
			if ($this->check_answer())
			{
				// $this->delete_code(); commented out to allow posting.php to repeat the question
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

		$this->confirm_id = md5(unique_id($user->ip));
		$this->question = (int) array_rand($this->question_ids);
		
		$sql = 'INSERT INTO ' . QA_CONFIRM_TABLE . ' ' . $db->sql_build_array('INSERT', array(
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

		$this->question = (int) array_rand($this->question_ids);
		$this->solved = 0;
		// compute $seed % 0x7fffffff

		$sql = 'UPDATE ' . QA_CONFIRM_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
				'question'			=> (int) $this->question,)) . '
				WHERE
				confirm_id = \'' . $db->sql_escape($this->confirm_id) . '\' 
					AND session_id = \'' . $db->sql_escape($user->session_id) . '\'';
		$db->sql_query($sql);
		$this->load_answer();
	}

	/**
	* New Question, if desired.
	*/
	function new_attempt()
	{
		global $db, $user;

		$this->question = (int) array_rand($this->question_ids);
		$this->solved = 0;
		// compute $seed % 0x7fffffff

		$sql = 'UPDATE ' . QA_CONFIRM_TABLE . ' SET ' . $db->sql_build_array('UPDATE', array(
				'question_id'			=> (int) $this->question)) . ', 
				attempts = attempts + 1 
				WHERE
				confirm_id = \'' . $db->sql_escape($this->confirm_id) . '\' 
					AND session_id = \'' . $db->sql_escape($user->session_id) . '\'';
		$db->sql_query($sql);
		$this->load_answer();
	}
	
	/**
	* Look up everything we need.
	*/
	function load_answer()
	{
		global $db, $user;

		$sql = 'SELECT con.question_id, attempts, question_text, strict
			FROM ' . QA_CONFIRM_TABLE . ' con, ' . QUESTIONS_TABLE . " qes 
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

	function check_answer()
	{
		global $db;
		
		$answer = ($this->question_strict) ? request_var('answer', '') : utf8_clean_string(request_var('answer', ''));
		
		$sql = 'SELECT answer_text
					FROM ' . ANSWERS_TABLE . '
					WHERE question_id = ' . (int) $this->question;
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$solution = ($this->question_strict) ? $row['answer_text'] : utf8_clean_string($row['answer_text'] );
			if ($solution === $answer)
			{
				$this->solved = true;
				break;
			}
		}
		$db->sql_freeresult($result);
		return $this->solved;
	}

	function delete_code()
	{
		global $db, $user;

		$sql = 'DELETE FROM ' . QA_CONFIRM_TABLE . "
			WHERE confirm_id = '" . $db->sql_escape($confirm_id) . "'
				AND session_id = '" . $db->sql_escape($user->session_id) . "'
				AND confirm_type = " . $this->type;
		$db->sql_query($sql);
	}

	function get_attempt_count()
	{
		return $this->attempts;
	}

	function reset()
	{
		global $db, $user;

		$sql = 'DELETE FROM ' . QA_CONFIRM_TABLE . "
			WHERE session_id = '" . $db->sql_escape($user->session_id) . "'
				AND confirm_type = " . (int) $this->type;
		$db->sql_query($sql);

		// we leave the class usable by generating a new question
		$this->generate_code();
	}
	
	function is_solved()
	{
		if (request_var('answer', false) && $this->solved === 0)
		{
			$this->validate();
		}
		return (bool) $this->solved;
	}
	
	function acp_page($id, &$module)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang('acp/board');
		$user->add_lang('captcha_qa');

		if (!$this->is_installed())
		{
			$this->install();
		}
		$module->tpl_name = 'captcha_qa_acp';
		$module->page_title = 'ACP_VC_SETTINGS';
		$form_key = 'acp_captcha';
		add_form_key($form_key);

		$submit = request_var('submit', false);
		$question_id = request_var('question_id', 0);
		$action = request_var('action', '');
		
		
		$template->assign_vars(array(
				'U_ACTION'		=> $module->u_action,
				'QUESTION_ID'	=> $question_id ,
				'CLASS'			=> $this->get_class_name(),
		));
		
		if (!$question_id && $action != 'add')
		{
			$this->acp_question_list($module);
		}
		else if ($question_id && $action == 'delete')
		{
			if (confirm_box(true))
			{
				$this->acp_delete_question($question_id);
				trigger_error($user->lang['QUESTION_DELETED'] . adm_back_link($module->u_action));
			}
			else
			{
				confirm_box(false, $user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
					'question_id'		=> $question_id,
					'action'			=> $action,
					'configure'			=> 1,
					'select_captcha'	=> $this->get_class_name(),
					))
				);
			}
		}
		else
		{
			
			$error = false;
			$input_question = request_var('question_text', '');
			$input_answers = request_var('answers', '');
			$input_lang = request_var('lang_iso', '');
			$input_strict = request_var('strict', false);
			$langs = $this->get_languages();
			foreach ($langs as $lang => $entry)
			{
				$template->assign_block_vars('langs', array(
					'ISO' => $lang,
					'NAME' => $entry['name'],
				));
			}
			
			if ($question_id)
			{
				if ($question = $this->acp_get_question_data($question_id))
				{
					$answers = (isset($input_answers[$lang])) ? $input_answers[$lang] : implode("\n", $question['answers']);
					$template->assign_vars(array(
						'QUESTION_TEXT'		=> ($input_question) ? $input_question : $question['question_text'],
						'LANG_ISO'			=> ($input_lang) ? $input_lang : $question['lang_iso'],
						'STRICT'			=> (isset($_REQUEST['strict'])) ? $input_strict : $question['strict'],
						'ANSWERS'			=> $answers,
					));
				}
				else
				{
					trigger_error($user->lang['FORM_INVALID'] . adm_back_link($module->u_action));
				}
			}
			else
			{
			
				$template->assign_vars(array(
						'QUESTION_TEXT'		=> $input_question,
						'LANG_ISO'			=> $input_lang,
						'STRICT'			=> $input_strict,
						'ANSWERS'			=> $input_answers,
				));
			}
			
			if ($submit && check_form_key($form_key))
			{
				$data = $this->acp_get_question_input();
				if (!$this->validate_input($data))
				{
					$template->assign_vars(array(
						'S_ERROR'			=> true,
					));
				}
				else
				{
					if ($question_id)
					{
						$this->acp_update_question($data, $question_id);
					}
					else
					{
						$this->acp_add_question($data);
					}
					
					trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($module->u_action . "&amp;configure=1&amp;select_captcha=" . $this->get_class_name()));
				}
			}
			else if ($submit)
			{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($module->u_action));
			}
		}
	}
	
	function acp_question_list(&$module)
	{
		global $db, $template;
		
		$sql = 'SELECT * FROM ' . QUESTIONS_TABLE . ' WHERE 1';
		$result = $db->sql_query($sql);
		$template->assign_vars(array(
						'S_LIST'			=> true,
		));

		while($row = $db->sql_fetchrow($result))
		{
			$url = $module->u_action . "&amp;question_id={$row['question_id']}&amp;configure=1&amp;select_captcha=" . $this->get_class_name() . "&amp;";
			
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
	
	function acp_get_question_data($question_id)
	{
		global $db;


		if ($question_id)
		{
			$sql = 'SELECT * FROM ' . QUESTIONS_TABLE . ' WHERE question_id = ' . $question_id;
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result))
			{
				$question = $row;
			}
			else
			{
				$db->sql_freeresult($result);
				return false;
			}
			$question['answers'] = array();
			$sql = 'SELECT * FROM ' . ANSWERS_TABLE . ' WHERE question_id = ' . $question_id;
			$result = $db->sql_query($sql);
			while($row = $db->sql_fetchrow($result))
			{
				$question['answers'][] = $row['answer_text'];
			}
			$db->sql_freeresult($result);
			return $question;
		}
		
	}
	
	
	function acp_get_question_input()
	{
		global $db;

		$question = array(
			'question_text'	=> request_var('question_text', ''),
			'strict'		=> request_var('strict', false),
			'lang_iso'		=> request_var('lang_iso', ''),
			'answers'		=> explode("\n", request_var('answers', '')),
		);
		
		return $question;
	}



	function acp_update_question($data, $question_id)
	{
		global $db;

		$sql = "DELETE FROM " . ANSWERS_TABLE . " WHERE question_id = $question_id";
		$db->sql_query($sql);
		$langs = $this->get_languages();
		$question_ary = $data;
		$question_ary['lang_id'] = $langs[$question_ary['lang_iso']]['id'];
		unset($question_ary['answers']);
		$sql = "UPDATE " . QUESTIONS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $question_ary) . "
				WHERE question_id = $question_id";
		$db->sql_query($sql);
		$this->acp_insert_answers($data, $question_id);
	}
	
	function acp_add_question($data)
	{
		global $db;
	
		$langs = $this->get_languages();
		$question_ary = $data;
		
		$question_ary['lang_id'] = $langs[$data['lang_iso']]['id'];
		unset($question_ary['answers']);
		$sql = "INSERT INTO " . QUESTIONS_TABLE . $db->sql_build_array('INSERT', $question_ary);
		$db->sql_query($sql);
		$question_id = $db->sql_nextid();
		$this->acp_insert_answers($data, $question_id);
	}
	
	function acp_insert_answers($data, $question_id)
	{
		global $db;
		
		foreach($data['answers'] as $answer)
		{
			$answer_ary = array(
				'question_id'	=> $question_id,
				'answer_text'	=> $answer,
			);
			$sql = "INSERT INTO " . ANSWERS_TABLE . $db->sql_build_array('INSERT', $answer_ary);
			$db->sql_query($sql);
		}
	}
	


	function acp_delete_question($question_id)
	{
		global $db;
		
		$tables = array(QUESTIONS_TABLE, ANSWERS_TABLE);
		foreach($tables as $table)
		{
			$sql = "DELETE FROM $table WHERE question_id = $question_id";
			$db->sql_query($sql);
		}
	}
	
	
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
			!$question_data['question_text'] ||
			!sizeof($question_data['answers']))
		{
			return false;
		}
		
		return true;
	}
	
	function get_languages()
	{
		global $db;
		
		$langs = array();
		$sql = 'SELECT * FROM ' . LANG_TABLE . ' WHERE 1';
		$result = $db->sql_query($sql);
		while($row = $db->sql_fetchrow($result))
		{
			$langs[$row['lang_iso']] = array(
				'name'	=> $row['lang_local_name'],
				'id'	=> $row['lang_id'],
			);
		}
		$db->sql_freeresult($result);
		return $langs;
	}
	
}

?>