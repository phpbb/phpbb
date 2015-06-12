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

namespace phpbb\bbcode;


class text_formatter_data_access extends \phpbb\textformatter\data_access
{

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
	public function __construct(\phpbb\db\driver\driver_interface $db)
	{
		// resend arguments to parent, whatever they are
		call_user_func_array('parent::__construct', func_get_args());
	}

	/**
	* Return the list of custom BBCodes
	*
	* @return array
	*/
	public function get_bbcodes()
	{
		$sql = 'SELECT bbcode_match, wysiwyg_tpl AS bbcode_tpl FROM ' . $this->bbcodes_table;
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Returns extra data about BBCodes
	* Note: This doesn't return the main data about the BBCode
	* This returns:
	* bbcode_helpline
	* display_on_posting
	*
	* @return array With the tag as key and the metadata as value
	*/
	public function get_bbcodes_settings()
	{
		$metadata = array();
		$sql = 'SELECT bbcode_tag,
					bbcode_helpline,
					display_on_posting
				FROM ' . $this->bbcodes_table;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$metadata[$row['bbcode_tag']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $metadata;
	}
}
