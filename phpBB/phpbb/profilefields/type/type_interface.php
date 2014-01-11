<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

interface type_interface
{
	/*
	public function validate(&$value, $validation);

	public function prepare_for_storage($value);

	public function prepare_for_display($value);

	public function prepare_for_edit($value);
	*/

	/**
	* Get dropdown options for second step in ACP
	*
	* @param string	$default_lang_id	ID of the default language
	* @param array	$field_data			Array with data for this field
	* @return array	with the acp options
	*/
	public function get_options($default_lang_id, $field_data);

	/**
	* Get default values for this type
	*
	* @return array with values like default field size and more
	*/
	public function get_default_values();
}
