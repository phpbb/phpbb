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
	var $u_action;

	public function main($id, $mode)
	{
		$this->language->add_lang('acp/posting');

		// Set up general vars
		$action = $this->request->variable('action', '');
		$action = ($this->request->is_set_post('add')) ? 'add' : (($this->request->is_set_post('save')) ? 'save' : $action);

		$s_hidden_fields = '';
		$word_info = [];

		$this->tpl_name = 'acp_words';
		$this->page_title = 'ACP_WORDS';

		$form_key = 'acp_words';
		add_form_key($form_key);

		switch ($action)
		{
			case 'edit':

				$word_id = $this->request->variable('id', 0);

				if (!$word_id)
				{
					trigger_error($this->language->lang('NO_WORD') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$sql = 'SELECT *
					FROM ' . WORDS_TABLE . "
					WHERE word_id = $word_id";
				$result = $this->db->sql_query($sql);
				$word_info = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$s_hidden_fields .= '<input type="hidden" name="id" value="' . $word_id . '" />';

			case 'add':

				$this->template->assign_vars([
					'S_EDIT_WORD'		=> true,
					'U_ACTION'			=> $this->u_action,
					'U_BACK'			=> $this->u_action,
					'WORD'				=> (isset($word_info['word'])) ? $word_info['word'] : '',
					'REPLACEMENT'		=> (isset($word_info['replacement'])) ? $word_info['replacement'] : '',
					'S_HIDDEN_FIELDS'	=> $s_hidden_fields]
				);

				return;

			break;

			case 'save':

				if (!check_form_key($form_key))
				{
					trigger_error($this->language->lang('FORM_INVALID'). adm_back_link($this->u_action), E_USER_WARNING);
				}

				$word_id		= $this->request->variable('id', 0);
				$word			= $this->request->variable('word', '', true);
				$replacement	= $this->request->variable('replacement', '', true);

				if ($word === '' || $replacement === '')
				{
					trigger_error($this->language->lang('ENTER_WORD') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				// Replace multiple consecutive asterisks with single one as those are not needed
				$word = preg_replace('#\*{2,}#', '*', $word);

				$sql_ary = [
					'word'			=> $word,
					'replacement'	=> $replacement,
				];

				if ($word_id)
				{
					$this->db->sql_query('UPDATE ' . WORDS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE word_id = ' . $word_id);
				}
				else
				{
					$this->db->sql_query('INSERT INTO ' . WORDS_TABLE . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
				}

				$this->cache->destroy('_word_censors');
				$phpbb_container->get('text_formatter.cache')->invalidate();

				$log_action = ($word_id) ? 'LOG_WORD_EDIT' : 'LOG_WORD_ADD';

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, $log_action, false, [$word]);

				$message = ($word_id) ? $this->language->lang('WORD_UPDATED') : $this->language->lang('WORD_ADDED');
				trigger_error($message . adm_back_link($this->u_action));

			break;

			case 'delete':

				$word_id = $this->request->variable('id', 0);

				if (!$word_id)
				{
					trigger_error($this->language->lang('NO_WORD') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				if (confirm_box(true))
				{
					$sql = 'SELECT word
						FROM ' . WORDS_TABLE . "
						WHERE word_id = $word_id";
					$result = $this->db->sql_query($sql);
					$deleted_word = $this->db->sql_fetchfield('word');
					$this->db->sql_freeresult($result);

					$sql = 'DELETE FROM ' . WORDS_TABLE . "
						WHERE word_id = $word_id";
					$this->db->sql_query($sql);

					$this->cache->destroy('_word_censors');
					$phpbb_container->get('text_formatter.cache')->invalidate();

					$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_WORD_DELETE', false, [$deleted_word]);

					trigger_error($this->language->lang('WORD_REMOVED') . adm_back_link($this->u_action));
				}
				else
				{
					confirm_box(false, $this->language->lang('CONFIRM_OPERATION'), build_hidden_fields([
						'i'			=> $id,
						'mode'		=> $mode,
						'id'		=> $word_id,
						'action'	=> 'delete',
					]));
				}

			break;
		}

		$this->template->assign_vars([
			'U_ACTION'			=> $this->u_action,
			'S_HIDDEN_FIELDS'	=> $s_hidden_fields]
		);

		$sql = 'SELECT *
			FROM ' . WORDS_TABLE . '
			ORDER BY word';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('words', [
				'WORD'			=> $row['word'],
				'REPLACEMENT'	=> $row['replacement'],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;id=' . $row['word_id'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;id=' . $row['word_id']]
			);
		}
		$this->db->sql_freeresult($result);
	}
}
