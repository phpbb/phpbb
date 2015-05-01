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

namespace phpbb\textreparser;

abstract class base implements reparser_interface
{
	/**
	* {@inheritdoc}
	*/
	abstract public function get_max_id();

	/**
	* Return all records in given range
	*
	* @param  integer $min_id Lower bound
	* @param  integer $max_id Upper bound
	* @return array           Array of record
	*/
	abstract protected function get_records($min_id, $max_id);

	/**
	* {@inheritdoc}
	*/
	public function reparse_range($min_id, $max_id)
	{
		foreach ($this->get_records($min_id, $max_id) as $record)
		{
			$this->reparse_record($record);
		}
	}

	/**
	* Reparse given record
	*
	* @param  array $record Associative array containing the record's data
	*/
	protected function reparse_record(array $record)
	{
		$unparsed = array_merge(
			$record,
			generate_text_for_edit(
				$record['text'],
				$record['bbcode_uid'],
				OPTION_FLAG_BBCODE | OPTION_FLAG_SMILIES | OPTION_FLAG_LINKS
			)
		);
		$bitfield = $flags = null;
		$parsed_text = $unparsed['text'];
		generate_text_for_storage(
			$parsed_text,
			$unparsed['bbcode_uid'],
			$bitfield,
			$flags,
			$unparsed['enable_bbcode'],
			$unparsed['enable_smilies'],
			$unparsed['enable_magic_url']
		);

		// Save the new text if it has changed
		if ($parsed_text !== $record['text'])
		{
			$record['text'] = $parsed_text;
			$this->save_record($record);
		}
	}

	/**
	* {@inheritdoc}
	*/
	abstract protected function save_record(array $record);
}
