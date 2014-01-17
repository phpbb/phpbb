<?php
/**
*
* @package phpBB
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\profilefields\type;

abstract class type_base implements type_interface
{
	/**
	*
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_service_name()
	{
		return 'profilefields.type.' . $this->get_name();
	}

	/**
	* {@inheritDoc}
	*/
	public function get_field_ident($field_data)
	{
		return 'pf_' . $field_data['field_ident'];
	}

	/**
	* {@inheritDoc}
	*/
	public function get_language_options_input($field_data)
	{
		$field_data['l_lang_name']			= $this->request->variable('l_lang_name', array(0 => ''), true);
		$field_data['l_lang_explain']			= $this->request->variable('l_lang_explain', array(0 => ''), true);
		$field_data['l_lang_default_value']	= $this->request->variable('l_lang_default_value', array(0 => ''), true);
		$field_data['l_lang_options']			= $this->request->variable('l_lang_options', array(0 => ''), true);

		return $field_data;
	}

	/**
	* {@inheritDoc}
	*/
	public function prepare_options_form(&$exclude_options, &$visibility_options)
	{
		return $this->request->variable('lang_options', '', true);
	}

	/**
	* {@inheritDoc}
	*/
	public function validate_options_on_submit($error, $field_data)
	{
		return $error;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_excluded_options($key, $action, $current_value, &$field_data, $step)
	{
		if ($step == 3 && ($field_data[$key] || $action != 'edit') && $key == 'l_lang_options' && is_array($field_data[$key]))
		{
			foreach ($field_data[$key] as $lang_id => $options)
			{
				$field_data[$key][$lang_id] = explode("\n", $options);
			}

			return $current_value;
		}

		return $current_value;
	}

	/**
	* {@inheritDoc}
	*/
	public function prepare_hidden_fields($step, $key, $action, &$field_data)
	{
		if (!$this->request->is_set($key))
		{
			// Do not set this variable, we will use the default value
			return null;
		}
		else if ($key == 'field_ident' && isset($field_data[$key]))
		{
			return $field_data[$key];
		}
		else
		{
			return $this->request->variable($key, '', true);
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function display_options(&$template_vars, &$field_data)
	{
		return;
	}
}
