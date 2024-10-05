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

namespace phpbb\db\migration\data\v400;

use phpbb\db\migration\migration;

class qa_captcha extends migration
{
	public function effectively_installed(): bool
	{
		return $this->db_tools->sql_table_exists($this->tables['captcha_qa_questions'])
			&& $this->db_tools->sql_table_exists($this->tables['captcha_qa_answers'])
			&& $this->db_tools->sql_table_exists($this->tables['captcha_qa_confirm']);
	}

	public static function depends_on(): array
	{
		return [
			'\phpbb\db\migration\data\v400\dev',
		];
	}

	public function update_schema(): array
	{
		return [
			'add_tables'	=> [
				$this->tables['captcha_qa_questions'] => [
					'COLUMNS' => [
						'question_id'	=> ['UINT', null, 'auto_increment'],
						'strict'		=> ['BOOL', 0],
						'lang_id'		=> ['UINT', 0],
						'lang_iso'		=> ['VCHAR:30', ''],
						'question_text'	=> ['TEXT_UNI', ''],
					],
					'PRIMARY_KEY'		=> 'question_id',
					'KEYS'				=> [
						'lang'			=> ['INDEX', 'lang_iso'],
					],
				],
				$this->tables['captcha_qa_answers'] => [
					'COLUMNS' => [
						'question_id'	=> ['UINT', 0],
						'answer_text'	=> ['STEXT_UNI', ''],
					],
					'KEYS'				=> [
						'qid'			=> ['INDEX', 'question_id'],
					],
				],
				$this->tables['captcha_qa_confirm'] => [
					'COLUMNS' => [
						'session_id'	=> ['CHAR:32', ''],
						'confirm_id'	=> ['CHAR:32', ''],
						'lang_iso'		=> ['VCHAR:30', ''],
						'question_id'	=> ['UINT', 0],
						'attempts'		=> ['UINT', 0],
						'confirm_type'	=> ['USINT', 0],
					],
					'KEYS'				=> [
						'session_id'			=> ['INDEX', 'session_id'],
						'lookup'				=> ['INDEX', ['confirm_id', 'session_id', 'lang_iso']],
					],
					'PRIMARY_KEY'		=> 'confirm_id',
				],
			],
		];
	}

	public function revert_schema(): array
	{
		return [
			'drop_tables' => [
				$this->tables['captcha_qa_questions'],
				$this->tables['captcha_qa_answers'],
				$this->tables['captcha_qa_confirm']
			],
		];
	}
}
