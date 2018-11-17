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

abstract class type_base implements type_interface
{
	/**
	* Request object
	* @var \phpbb\request\request
	*/
	protected $request;

	/**
	* Template object
	* @var \phpbb\template\template
	*/
	protected $template;

	/**
	* User object
	* @var \phpbb\user
	*/
	protected $user;

	/**
	* Construct
	*
	* @param	\phpbb\request\request		$request	Request object
	* @param	\phpbb\template\template	$template	Template object
	* @param	\phpbb\user					$user		User object
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
	public function get_name()
	{
		return $this->user->lang('FIELD_' . strtoupper($this->get_name_short()));
	}

	/**
	* {@inheritDoc}
	*/
	public function get_service_name()
	{
		return 'profilefields.type.' . $this->get_name_short();
	}

	/**
	* {@inheritDoc}
	*/
	public function get_template_filename()
	{
		return 'profilefields/' . $this->get_name_short() . '.html';
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
	public function get_field_name($field_name)
	{
		return isset($this->user->lang[$field_name]) ? $this->user->lang[$field_name] : $field_name;
	}

	/**
	* {@inheritDoc}
	*/
	public function get_profile_contact_value($field_value, $field_data)
	{
		return $this->get_profile_value($field_value, $field_data);
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
				$field_data[$key][$lang_id] = is_array($options) ? $options : explode("\n", $options);
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
			$default_value = '';
			$lang_fields = array(
				'l_lang_name',
				'l_lang_explain',
				'l_lang_default_value',
				'l_lang_options',
			);

			if (in_array($key, $lang_fields))
			{
				$default_value = array(0 => '');
			}
			return $this->request->variable($key, $default_value, true);
		}
	}

	/**
	* {@inheritDoc}
	*/
	public function display_options(&$template_vars, &$field_data)
	{
		return;
	}

	/**
	* Return templated value/field. Possible values for $mode are:
	* change == user is able to set/enter profile values; preview == just show the value
	*/
	public function process_field_row($mode, $profile_row)
	{
		$preview_options = ($mode == 'preview') ? $profile_row['lang_options'] : false;

		// set template filename
		$this->template->set_filenames(array(
			'cp_body'		=> $this->get_template_filename(),
		));

		// empty previously filled blockvars
		$this->template->destroy_block_vars($this->get_name_short());

		// Assign template variables
		$this->generate_field($profile_row, $preview_options);

		return $this->template->assign_display('cp_body');
	}
}
