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

/**
 * @todo [words] check regular expressions for special char replacements (stored specialchared in db)
 */
class words
{
	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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

	/** @var string phpBB words table */
	protected $words_table;

	/** @todo */
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\cache\driver\driver_interface	$cache			Cache object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\language\language				$lang			Language object
	 * @param \phpbb\log\log						$log			Log object
	 * @param \phpbb\request\request				$request		Request object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\textformatter\cache_interface	$tf_cache		Textformatter cache object
	 * @param \phpbb\user							$user			User object
	 * @param string								$words_table	phpBB words table
	 */
	public function __construct(
		\phpbb\cache\driver\driver_interface $cache,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\language\language $lang,
		\phpbb\log\log $log,
		\phpbb\request\request $request,
		\phpbb\template\template $template,
		\phpbb\textformatter\cache_interface $tf_cache,
		\phpbb\user $user,
		$words_table
	)
	{
		$this->cache		= $cache;
		$this->db			= $db;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->tf_cache		= $tf_cache;
		$this->user			= $user;

		$this->words_table	= $words_table;
	}

	function main($id, $mode)
	{
		$this->lang->add_lang('acp/posting');

		// Set up general vars
		$action = $this->request->variable('action', '');
		$action = $this->request->is_set_post('save') ? 'save' : $action;
		$action = $this->request->is_set_post('add') ? 'add' : $action;

		$word_id = $this->request->variable('id', 0);
		$word_info = [];
		$s_hidden_fields = '';

		$this->tpl_name = 'acp_words';
		$this->page_title = 'ACP_WORDS';

		$form_key = 'acp_words';
		add_form_key($form_key);

		switch ($action)
		{
			/** @noinspection PhpMissingBreakStatementInspection */
			case 'edit':
				if (!$word_id)
				{
					trigger_error($this->lang->lang('NO_WORD') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . $this->words_table . '
					WHERE word_id = ' . (int) $word_id;
				$result = $this->db->sql_query($sql);
				$word_info = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '" />';
			// no break;

			case 'add':
				$this->template->assign_vars([
					'S_EDIT_WORD'		=> true,
					'WORD'				=> isset($word_info['word']) ? $word_info['word'] : '',
					'REPLACEMENT'		=> isset($word_info['replacement']) ? $word_info['replacement'] : '',

					'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
					'U_ACTION'			=> $this->u_action,
					'U_BACK'			=> $this->u_action,
				]);

				return;
			break;

			case 'save':
				if (!check_form_key($form_key))
				{
					trigger_error($this->lang->lang('FORM_INVALID'). adm_back_link($this->u_action), E_USER_WARNING);
				}

				$word_id		= $this->request->variable('id', 0);
				$word			= $this->request->variable('word', '', true);
				$replacement	= $this->request->variable('replacement', '', true);

				if ($word === '' || $replacement === '')
				{
					trigger_error($this->lang->lang('ENTER_WORD') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Replace multiple consecutive asterisks with single one as those are not needed
				$word = preg_replace('#\*{2,}#', '*', $word);

				$sql_ary = [
					'word'			=> $word,
					'replacement'	=> $replacement,
				];

				if ($word_id)
				{
					$sql = 'UPDATE ' . $this->words_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE word_id = ' . (int) $word_id;
					$this->db->sql_query($sql);
				}
				else
				{
					$sql = 'INSERT INTO ' . $this->words_table . ' ' . $this->db->sql_build_array('INSERT', $sql_ary);
					$this->db->sql_query($sql);
				}

				$this->cache->destroy('_word_censors');
				$this->tf_cache->invalidate();

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, ($word_id ? 'LOG_WORD_EDIT' : 'LOG_WORD_ADD'), false, [$word]);

				trigger_error($this->lang->lang($word_id ? 'WORD_UPDATED' : 'WORD_ADDED') . adm_back_link($this->u_action));
			break;

			case 'delete':
				if (!$word_id)
				{
					trigger_error($this->lang->lang('NO_WORD') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$sql = 'SELECT word
						FROM ' . $this->words_table . '
						WHERE word_id = ' . (int) $word_id;
					$result = $this->db->sql_query($sql);
					$deleted_word = $this->db->sql_fetchfield('word');
					$this->db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . $this->words_table . '
						WHERE word_id = ' . (int) $word_id;
					$this->db->sql_query($sql);

					$this->cache->destroy('_word_censors');
					$this->tf_cache->invalidate();

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_WORD_DELETE', false, [$deleted_word]);

					trigger_error($this->lang->lang('WORD_REMOVED') . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $this->lang->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'action'	=> 'delete',
						'id'		=> $word_id,
					]));
				}
			break;
		}

		$this->template->assign_vars([
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields,
			'U_ACTION'			=> $this->u_action,
		]);

		$sql = 'SELECT *
			FROM ' . $this->words_table . '
			ORDER BY word';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('words', [
				'WORD'			=> $row['word'],
				'REPLACEMENT'	=> $row['replacement'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $row['word_id'],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $row['word_id'],
			]);
		}
		$this->db->sql_freeresult($result);
	}
}
