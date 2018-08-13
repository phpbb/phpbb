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

namespace phpbb\textformatter;

/**
* Data access layer that fetchs BBCodes, smilies and censored words from the database.
* To be extended to include insert/update/delete operations.
*
* Also used to get templates.
*/
class data_access
{
	/**
	* @var string Name of the BBCodes table
	*/
	protected $bbcodes_table;

	/**
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* @var string Name of the smilies table
	*/
	protected $smilies_table;

	/**
	* @var string Name of the styles table
	*/
	protected $styles_table;

	/**
	* @var string Path to the styles dir
	*/
	protected $styles_path;

	/**
	* @var string Name of the words table
	*/
	protected $words_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param string $bbcodes_table Name of the BBCodes table
	* @param string $smilies_table Name of the smilies table
	* @param string $styles_table  Name of the styles table
	* @param string $words_table   Name of the words table
	* @param string $styles_path   Path to the styles dir
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $bbcodes_table, $smilies_table, $styles_table, $words_table, $styles_path)
	{
		$this->db = $db;

		$this->bbcodes_table = $bbcodes_table;
		$this->smilies_table = $smilies_table;
		$this->styles_table  = $styles_table;
		$this->words_table   = $words_table;

		$this->styles_path = $styles_path;
	}

	/**
	* Return the list of custom BBCodes
	*
	* @return array
	*/
	public function get_bbcodes()
	{
		$sql = 'SELECT bbcode_match, bbcode_tpl FROM ' . $this->bbcodes_table;

		return $this->fetch_decoded_rowset($sql, ['bbcode_match']);
	}

	/**
	* Return the list of smilies
	*
	* @return array
	*/
	public function get_smilies()
	{
		// NOTE: smilies that are displayed on the posting page are processed first because they're
		//       typically the most used smilies and it ends up producing a slightly more efficient
		//       renderer
		$sql = 'SELECT code, emotion, smiley_url, smiley_width, smiley_height
			FROM ' . $this->smilies_table . '
			ORDER BY display_on_posting DESC';

		return $this->fetch_decoded_rowset($sql, ['code', 'emotion', 'smiley_url']);
	}

	/**
	* Return the list of installed styles
	*
	* @return array
	*/
	protected function get_styles()
	{
		$sql = 'SELECT style_id, style_path, style_parent_id, bbcode_bitfield FROM ' . $this->styles_table;

		return $this->fetch_decoded_rowset($sql);
	}

	/**
	* Return the bbcode.html template for every installed style
	*
	* @return array 2D array. style_id as keys, each element is an array with a "template" element that contains the style's bbcode.html and a "bbcodes" element that contains the name of each BBCode that is to be stylised
	*/
	public function get_styles_templates()
	{
		$templates = array();

		$bbcode_ids = array(
			'quote' => 0,
			'b'     => 1,
			'i'     => 2,
			'url'   => 3,
			'img'   => 4,
			'size'  => 5,
			'color' => 6,
			'u'     => 7,
			'code'  => 8,
			'list'  => 9,
			'*'     => 9,
			'email' => 10,
			'flash' => 11,
			'attachment' => 12,
		);

		$styles = array();
		foreach ($this->get_styles() as $row)
		{
			$styles[$row['style_id']] = $row;
		}

		foreach ($styles as $style_id => $style)
		{
			$bbcodes = array();

			// Collect the name of the BBCodes whose bit is set in the style's bbcode_bitfield
			$template_bitfield = new \bitfield($style['bbcode_bitfield']);
			foreach ($bbcode_ids as $bbcode_name => $bit)
			{
				if ($template_bitfield->get($bit))
				{
					$bbcodes[] = $bbcode_name;
				}
			}

			$filename = $this->resolve_style_filename($styles, $style);
			if ($filename === false)
			{
				// Ignore this style, it will use the default templates
				continue;
			}

			$templates[$style_id] = array(
				'bbcodes'  => $bbcodes,
				'template' => file_get_contents($filename),
			);
		}

		return $templates;
	}

	/**
	* Resolve inheritance for given style and return the path to their bbcode.html file
	*
	* @param  array       $styles Associative array of [style_id => style] containing all styles
	* @param  array       $style  Style for which we resolve
	* @return string|bool         Path to this style's bbcode.html, or FALSE
	*/
	protected function resolve_style_filename(array $styles, array $style)
	{
		// Look for a bbcode.html in this style's dir
		$filename = $this->styles_path . $style['style_path'] . '/template/bbcode.html';
		if (file_exists($filename))
		{
			return $filename;
		}

		// Resolve using this style's parent
		$parent_id = $style['style_parent_id'];
		if ($parent_id && !empty($styles[$parent_id]))
		{
			return $this->resolve_style_filename($styles, $styles[$parent_id]);
		}

		return false;
	}

	/**
	* Return the list of censored words
	*
	* @return array
	*/
	public function get_censored_words()
	{
		$sql = 'SELECT word, replacement FROM ' . $this->words_table;

		return $this->fetch_decoded_rowset($sql, ['word', 'replacement']);
	}

	/**
	* Decode HTML special chars in given rowset
	*
	* @param  array $rows    Original rowset
	* @param  array $columns List of columns to decode
	* @return array          Decoded rowset
	*/
	protected function decode_rowset(array $rows, array $columns)
	{
		foreach ($rows as &$row)
		{
			foreach ($columns as $column)
			{
				$row[$column] = htmlspecialchars_decode($row[$column], ENT_COMPAT);
			}
		}

		return $rows;
	}

	/**
	* Fetch all rows for given query and decode plain text columns
	*
	* @param  string $sql     SELECT query
	* @param  array  $columns List of columns to decode
	* @return array
	*/
	protected function fetch_decoded_rowset($sql, array $columns = [])
	{
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $this->decode_rowset($rows, $columns);
	}
}
