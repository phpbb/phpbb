<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

class type_int implements type_interface
{
	/**
	*
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\user $user)
	{
		$this->request = $request;
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_options($default_lang_id, $field_data)
	{
		$options = array(
			0 => array('TITLE' => $this->user->lang['FIELD_LENGTH'],		'FIELD' => '<input type="number" min="0" max="99999" name="field_length" size="5" value="' . $field_data['field_length'] . '" />'),
			1 => array('TITLE' => $this->user->lang['MIN_FIELD_NUMBER'],	'FIELD' => '<input type="number" min="0" max="99999" name="field_minlen" size="5" value="' . $field_data['field_minlen'] . '" />'),
			2 => array('TITLE' => $this->user->lang['MAX_FIELD_NUMBER'],	'FIELD' => '<input type="number" min="0" max="99999" name="field_maxlen" size="5" value="' . $field_data['field_maxlen'] . '" />'),
			3 => array('TITLE' => $this->user->lang['DEFAULT_VALUE'],		'FIELD' => '<input type="post" name="field_default_value" value="' . $field_data['field_default_value'] . '" />')
		);

		return $options;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_default_values()
	{
		return array(
			'field_length'		=> 5,
			'field_minlen'		=> 0,
			'field_maxlen'		=> 100,
			'field_validation'	=> '',
			'field_novalue'		=> 0,
			'field_default_value'	=> 0,
		);
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_field($profile_row)
	{
		$var_name = 'pf_' . $profile_row['field_ident'];
		if ($this->request->is_set($var_name) && $this->request->variable($var_name, '') === '')
		{
			return null;
		}
		else
		{
			return $this->request->variable($var_name, (int) $profile_row['field_default_value']);
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_profile_field(&$field_value, $field_data)
	{
		if (trim($field_value) === '' && !$field_data['field_required'])
		{
			return false;
		}

		$field_value = (int) $field_value;

		if ($field_value < $field_data['field_minlen'])
		{
			return 'FIELD_TOO_SMALL';
		}
		else if ($field_value > $field_data['field_maxlen'])
		{
			return 'FIELD_TOO_LARGE';
		}

		return false;
	}
}
