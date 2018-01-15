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

namespace phpbb\profilefields\type;

interface type_interface
{
	/**
	* Get the translated name of the type
	*
	* @return string Translated name of the field type
	*/
	public function get_name();

	/**
	* Get the short name of the type, used for error messages and template loops
	*
	* @return string lowercase version of the fields type
	*/
	public function get_name_short();

	/**
	* Get the name of service representing the type
	*
	* @return string lowercase version of the fields type
	*/
	public function get_service_name();

	/**
	* Get the name of template file for this type
	*
	* @return string Returns the name of the template file
	*/
	public function get_template_filename();

	/**
	* Get dropdown options for second step in ACP
	*
	* @param string	$default_lang_id	ID of the default language
	* @param array	$field_data			Array with data for this field
	* @return array	with the acp options
	*/
	public function get_options($default_lang_id, $field_data);

	/**
	* Get default values for the options of this type
	*
	* @return array with values like default field size and more
	*/
	public function get_default_option_values();

	/**
	* Get default value for this type
	*
	* @param array	$field_data			Array with data for this field
	* @return mixed default value for new users when no value is given
	*/
	public function get_default_field_value($field_data);

	/**
	* Get profile field value on submit
	*
	* @param array	$profile_row			Array with data for this field
	* @return mixed		Submitted value of the profile field
	*/
	public function get_profile_field($profile_row);

	/**
	* Validate entered profile field data
	*
	* @param mixed	$field_value		Field value to validate
	* @param array	$field_data			Array with requirements of the field
	* @return mixed		String with the error message
	*/
	public function validate_profile_field(&$field_value, $field_data);

	/**
	* Get Profile Value for display
	*
	* @param mixed	$field_value		Field value as stored in the database
	* @param array	$field_data			Array with requirements of the field
	* @return mixed		Field value to display
	*/
	public function get_profile_value($field_value, $field_data);

	/**
	* Get Profile Value ID for display (the raw, unprocessed user data)
	*
	* @param mixed	$field_value		Field value as stored in the database
	* @param array	$field_data			Array with requirements of the field
	* @return mixed		Field value ID to display
	*/
	public function get_profile_value_raw($field_value, $field_data);

	/**
	* Get Profile Value for display
	*
	* When displaying a contact field, we don't want to have links already parsed and more
	*
	* @param mixed	$field_value		Field value as stored in the database
	* @param array	$field_data			Array with requirements of the field
	* @return mixed		Field value to display
	*/
	public function get_profile_contact_value($field_value, $field_data);

	/**
	* Generate the input field for display
	*
	* @param array	$profile_row		Array with data for this field
	* @param mixed	$preview_options	When previewing we use different data
	* @return null
	*/
	public function generate_field($profile_row, $preview_options = false);

	/**
	* Get the ident of the field
	*
	* Some types are multivalue, we can't give them a field_id
	* as we would not know which to pick.
	*
	* @param array	$field_data		Array with data for this field
	* @return string ident of the field
	*/
	public function get_field_ident($field_data);

	/**
	* Get the localized name of the field
	*
	* @param string $field_name		Unlocalized name of this field
	* @return string 	Localized name of the field
	*/
	public function get_field_name($field_name);

	/**
	* Get the column type for the database
	*
	* @return string	Returns the database column type
	*/
	public function get_database_column_type();

	/**
	* Get the options we need to display for the language input fields in the ACP
	*
	* @param array	$field_data		Array with data for this field
	* @return array		Returns the language options we need to generate
	*/
	public function get_language_options($field_data);

	/**
	* Get the input for the supplied language options
	*
	* @param array	$field_data		Array with data for this field
	* @return array		Returns the language options we need to generate
	*/
	public function get_language_options_input($field_data);

	/**
	* Allows exclusion of options in single steps of the creation process
	*
	* @param array	$exclude_options		Array with options that should be excluded in the steps
	* @param array	$visibility_options		Array with options responsible for the fields visibility
	* @return mixed		Returns the provided language options
	*/
	public function prepare_options_form(&$exclude_options, &$visibility_options);

	/**
	* Allows exclusion of options in single steps of the creation process
	*
	* @param array	$error					Array with error messages
	* @param array	$field_data		Array with data for this field
	* @return array		Array with error messages
	*/
	public function validate_options_on_submit($error, $field_data);

	/**
	* Allows manipulating the intended variables if needed
	*
	* @param string	$key			Name of the option
	* @param string	$action			Currently performed action (create|edit)
	* @param mixed	$current_value	Currently value of the option
	* @param array	$field_data		Array with data for this field
	* @param int	$step			Step on which the option is excluded
	* @return mixed		Final value of the option
	*/
	public function get_excluded_options($key, $action, $current_value, &$field_data, $step);

	/**
	* Allows manipulating the intended variables if needed
	*
	* @param int	$step			Step on which the option is hidden
	* @param string	$key			Name of the option
	* @param string	$action			Currently performed action (create|edit)
	* @param array	$field_data		Array with data for this field
	* @return mixed		Final value of the option
	*/
	public function prepare_hidden_fields($step, $key, $action, &$field_data);

	/**
	* Allows assigning of additional template variables
	*
	* @param array	$template_vars	Template variables we are going to assign
	* @param array	$field_data		Array with data for this field
	* @return null
	*/
	public function display_options(&$template_vars, &$field_data);

	/**
	* Return templated value/field. Possible values for $mode are:
	* change == user is able to set/enter profile values; preview == just show the value
	*
	* @param string	$mode			Mode for displaying the field (preview|change)
	* @param array	$profile_row	Array with data for this field
	* @return null
	*/
	public function process_field_row($mode, $profile_row);
}
