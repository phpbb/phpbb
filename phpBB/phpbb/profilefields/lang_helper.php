<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields;

/**
* Custom Profile Fields
* @package phpBB3
*/
class lang_helper
{
	/**
	* Array with the language option, grouped by field and language
	* @var array
	*/
	protected $options_lang = array();

	/**
	* Database object
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* Table where the language strings are stored
	* @var string
	*/
	protected $language_table;

	/**
	* Construct
	*
	* @param	\phpbb\db\driver\driver_interface	$db		Database object
	* @param	string		$language_table		Table where the language strings are stored
	*/
	public function __construct($db, $language_table)
	{
		$this->db = $db;
		$this->language_table = $language_table;
	}

	/**
	* Get language entries for options and store them here for later use
	*/
	public function get_option_lang($field_id, $lang_id, $field_type, $preview_options)
	{
		if ($preview_options !== false)
		{
			$lang_options = (!is_array($preview_options)) ? explode("\n", $preview_options) : $preview_options;

			foreach ($lang_options as $num => $var)
			{
				if (!isset($this->options_lang[$field_id]))
				{
					$this->options_lang[$field_id] = array();
				}
				if (!isset($this->options_lang[$field_id][$lang_id]))
				{
					$this->options_lang[$field_id][$lang_id] = array();
				}
				$this->options_lang[$field_id][$lang_id][($num + 1)] = $var;
			}
		}
		else
		{
			$sql = 'SELECT option_id, lang_value
				FROM ' . $this->language_table . '
					WHERE field_id = ' . (int) $field_id . '
					AND lang_id = ' . (int) $lang_id . "
					AND field_type = '" . $this->db->sql_escape($field_type) . "'
				ORDER BY option_id";
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->options_lang[$field_id][$lang_id][($row['option_id'] + 1)] = $row['lang_value'];
			}
			$this->db->sql_freeresult($result);
		}
	}

	/**
	* Are language options set for this field?
	*
	* @param	int		$field_id		Database ID of the field
	* @param	int		$lang_id		ID of the language
	* @param	int		$field_value	Selected value of the field
	* @return boolean
	*/
	public function is_set($field_id, $lang_id = null, $field_value = null)
	{
		$is_set = isset($this->options_lang[$field_id]);

		if ($is_set && (!is_null($lang_id) || !is_null($field_value)))
		{
			$is_set = isset($this->options_lang[$field_id][$lang_id]);
		}

		if ($is_set && !is_null($field_value))
		{
			$is_set = isset($this->options_lang[$field_id][$lang_id][$field_value]);
		}

		return $is_set;
	}

	/**
	* Get the selected language string
	*
	* @param	int		$field_id		Database ID of the field
	* @param	int		$lang_id		ID of the language
	* @param	int		$field_value	Selected value of the field
	* @return string
	*/
	public function get($field_id, $lang_id, $field_value = null)
	{
		if (is_null($field_value))
		{
			return $this->options_lang[$field_id][$lang_id];
		}

		return $this->options_lang[$field_id][$lang_id][$field_value];
	}
}
