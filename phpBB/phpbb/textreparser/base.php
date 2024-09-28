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
	 * @var string The reparser name
	 */
	protected $name;

	/**
	* @var bool Whether to save changes to the database
	*/
	protected $save_changes = true;

	/**
	* {@inheritdoc}
	*/
	abstract public function get_max_id();

	/**
	* Return all records in given range
	*
	* @param  integer $min_id Lower bound
	* @param  integer $max_id Upper bound
	* @return array           Array of records
	*/
	abstract protected function get_records_by_range($min_id, $max_id);

	/**
	* {@inheritdoc}
	*/
	abstract protected function save_record(array $record);

	/**
	* Add fields to given record, if applicable
	*
	* The enable_* fields are not always saved to the database. Sometimes we need to guess their
	* original value based on the text content or possibly other fields
	*
	* @param  array $record Original record
	* @return array         Complete record
	*/
	protected function add_missing_fields(array $record)
	{
		if (!isset($record['enable_bbcode'], $record['enable_smilies'], $record['enable_magic_url']))
		{
			if (isset($record['options']))
			{
				$record += array(
					'enable_bbcode'    => (bool) ($record['options'] & OPTION_FLAG_BBCODE),
					'enable_smilies'   => (bool) ($record['options'] & OPTION_FLAG_SMILIES),
					'enable_magic_url' => (bool) ($record['options'] & OPTION_FLAG_LINKS),
				);
			}
			else
			{
				$record += array(
					'enable_bbcode'    => $this->guess_bbcodes($record),
					'enable_smilies'   => $this->guess_smilies($record),
					'enable_magic_url' => $this->guess_magic_url($record),
				);
			}
		}

		// Those BBCodes are disabled based on context and user permissions and that value is never
		// stored in the database. Here we test whether they were used in the original text.
		$bbcodes = array('flash', 'img', 'quote', 'url');
		foreach ($bbcodes as $bbcode)
		{
			$field_name = 'enable_' . $bbcode . '_bbcode';
			$record[$field_name] = $this->guess_bbcode($record, $bbcode);
		}

		// Magic URLs are tied to the URL BBCode, that's why if magic URLs are enabled we make sure
		// that the URL BBCode is also enabled
		if ($record['enable_magic_url'])
		{
			$record['enable_url_bbcode'] = true;
		}

		return $record;
	}

	/**
	 * Returns the name of the reparser
	 *
	 * @return string Name of reparser
	 */
	public function get_name()
	{
		return $this->name;
	}

	/**
	 * Sets the name of the reparser
	 *
	 * @param string $name The reparser name
	 */
	public function set_name($name)
	{
		$this->name = $name;
	}

	/**
	* Disable saving changes to the database
	*/
	public function disable_save()
	{
		$this->save_changes = false;
	}

	/**
	* Enable saving changes to the database
	*/
	public function enable_save()
	{
		$this->save_changes = true;
	}

	/**
	* Guess whether given BBCode is in use in given record
	*
	* @param  array  $record
	* @param  string $bbcode
	* @return bool
	*/
	protected function guess_bbcode(array $record, $bbcode)
	{
		if (!empty($record['bbcode_uid']))
		{
			// Look for the closing tag, e.g. [/url]
			$match = '[/' . $bbcode . ':' . $record['bbcode_uid'];
			if (strpos($record['text'], $match) !== false)
			{
				return true;
			}
		}

		if (substr($record['text'], 0, 2) === '<r')
		{
			// Look for the closing tag inside of a e element, in an element of the same name, e.g.
			// <e>[/url]</e></URL>
			$match = '<e>[/' . $bbcode . ']</e></' . $bbcode . '>';
			if (stripos($record['text'], $match) !== false)
			{
				return true;
			}
		}

		return false;
	}

	/**
	* Guess whether any BBCode is in use in given record
	*
	* @param  array $record
	* @return bool
	*/
	protected function guess_bbcodes(array $record)
	{
		if (!empty($record['bbcode_uid']))
		{
			// Test whether the bbcode_uid is in use
			$match = ':' . $record['bbcode_uid'];
			if (strpos($record['text'], $match) !== false)
			{
				return true;
			}
		}

		if (substr($record['text'], 0, 2) === '<r')
		{
			// Look for a closing tag inside of an e element
			return (bool) preg_match('(<e>\\[/\\w+\\]</e>)', $match);
		}

		return false;
	}

	/**
	* Guess whether magic URLs are in use in given record
	*
	* @param  array $record
	* @return bool
	*/
	protected function guess_magic_url(array $record)
	{
		// Look for magic URL markers or for a URL tag that's not immediately followed by <s>
		return preg_match('#<!-- ([lmwe]) -->.*?<!-- \1 -->#', $record['text']) || preg_match('(<URL [^>]++>(?!<s>))', $record['text']);
	}

	/**
	* Guess whether smilies are in use in given record
	*
	* @param  array $record
	* @return bool
	*/
	protected function guess_smilies(array $record)
	{
		return (strpos($record['text'], '<!-- s') !== false || strpos($record['text'], '<E>') !== false);
	}

	/**
	* {@inheritdoc}
	*/
	public function reparse_range($min_id, $max_id, bool $force_bbcode_reparsing = false)
	{
		foreach ($this->get_records_by_range($min_id, $max_id) as $record)
		{
			$this->reparse_record($record, $force_bbcode_reparsing);
		}
	}

	/**
	* Reparse given record
	*
	* @param array $record Associative array containing the record's data
	* @param bool $force_bbcode_reparsing Flag indicating if BBCode should be reparsed unconditionally
	*/
	protected function reparse_record(array $record, bool $force_bbcode_reparsing = false)
	{
		// Guess magic URL state based on actual record content before adding fields
		$record['enable_magic_url'] = $this->guess_magic_url($record);
		$record = $this->add_missing_fields($record);

		$flags = ($record['enable_bbcode'] || $force_bbcode_reparsing) ? OPTION_FLAG_BBCODE : 0;
		$flags |= ($record['enable_smilies'] || $force_bbcode_reparsing) ? OPTION_FLAG_SMILIES : 0;
		$flags |= ($record['enable_magic_url'] || $force_bbcode_reparsing) ? OPTION_FLAG_LINKS : 0;
		$unparsed = array_merge(
			$record,
			generate_text_for_edit($record['text'], $record['bbcode_uid'], $flags)
		);

		// generate_text_for_edit() and decode_message() actually return the text as HTML. It has to
		// be decoded to plain text before it can be reparsed
		$text = html_entity_decode($unparsed['text'], ENT_QUOTES, 'UTF-8');
		$bitfield = $flags = null;
		generate_text_for_storage(
			$text,
			$unparsed['bbcode_uid'],
			$bitfield,
			$flags,
			$unparsed['enable_bbcode'] || $force_bbcode_reparsing,
			$unparsed['enable_magic_url'] || $force_bbcode_reparsing,
			$unparsed['enable_smilies'] || $force_bbcode_reparsing,
			$unparsed['enable_img_bbcode'] || $force_bbcode_reparsing,
			$unparsed['enable_flash_bbcode'],
			$unparsed['enable_quote_bbcode'] || $force_bbcode_reparsing,
			$unparsed['enable_url_bbcode'] || $force_bbcode_reparsing,
			'text_reparser.' . $this->get_name()
		);

		// Save the new text if it has changed and it's not a dry run
		if ($text !== $record['text'] && $this->save_changes)
		{
			$record['text'] = $text;
			$this->save_record($record);
		}
	}
}
