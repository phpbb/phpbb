<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

class type_text extends type_string_common implements type_interface
{
	/**
	*
	*/
	public function __construct($user)
	{
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$options = array(
			0 => array('TITLE' => $this->user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" max="99999" name="rows" size="5" value="' . $field_data['rows'] . '" /> ' . $this->user->lang['ROWS'] . '</dd><dd><input type="number" min="0" max="99999" name="columns" size="5" value="' . $field_data['columns'] . '" /> ' . $this->user->lang['COLUMNS'] . ' <input type="hidden" name="field_length" value="' . $field_data['field_length'] . '" />'),
			1 => array('TITLE' => $this->user->lang['MIN_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="9999999999" name="field_minlen" size="10" value="' . $field_data['field_minlen'] . '" />'),
			2 => array('TITLE' => $this->user->lang['MAX_FIELD_CHARS'],	'FIELD' => '<input type="number" min="0" max="9999999999" name="field_maxlen" size="10" value="' . $field_data['field_maxlen'] . '" />'),
			3 => array('TITLE' => $this->user->lang['FIELD_VALIDATION'],	'FIELD' => '<select name="field_validation">' . $this->validate_options($field_data) . '</select>')
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_values()
	{
		return array(
			'field_length'		=> '5|80',
			'field_minlen'		=> 0,
			'field_maxlen'		=> 1000,
			'field_validation'	=> '.*',
			'field_novalue'		=> '',
			'field_default_value'	=> '',
		);
	}
}
