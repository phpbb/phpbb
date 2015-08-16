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

namespace phpbb\install\helper\iohandler;

/**
 * Input-Output handler for the AJAX frontend
 */
class ajax_iohandler extends iohandler_base
{
	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var string
	 */
	protected $form;

	/**
	 * @var bool
	 */
	protected $request_client_refresh;

	/**
	 * @var array
	 */
	protected $nav_data;

	/**
	 * @var array
	 */
	protected $cookies;

	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request_interface	$request	HTTP request interface
	 * @param \phpbb\template\template			$template	Template engine
	 */
	public function __construct(\phpbb\request\request_interface $request, \phpbb\template\template $template)
	{
		$this->request	= $request;
		$this->template	= $template;
		$this->form		= '';
		$this->nav_data	= array();
		$this->cookies	= array();

		parent::__construct();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_input($name, $default, $multibyte = false)
	{
		return $this->request->variable($name, $default, $multibyte);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_server_variable($name, $default = '')
	{
		return $this->request->server($name, $default);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_header_variable($name, $default = '')
	{
		return $this->request->header($name, $default);
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_secure()
	{
		return $this->request->is_secure();
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_user_form_group($title, $form)
	{
		$this->template->assign_var('S_FORM_ELEM_COUNT', sizeof($form));

		$this->template->assign_block_vars('options', array(
			'LEGEND'	=> $this->language->lang($title),
			'S_LEGEND'	=> true,
		));

		foreach ($form as $input_name => $input_options)
		{
			if (!isset($input_options['type']))
			{
				continue;
			}

			$tpl_ary = array();

			$tpl_ary['TYPE'] = $input_options['type'];
			$tpl_ary['TITLE'] = $this->language->lang($input_options['label']);
			$tpl_ary['KEY'] = $input_name;
			$tpl_ary['S_EXPLAIN'] = false;

			if (isset($input_options['default']))
			{
				$default = $input_options['default'];
				$default = preg_replace_callback('#\{L_([A-Z0-9\-_]*)\}#s', array($this, 'lang_replace_callback'), $default);
				$tpl_ary['DEFAULT'] = $default;
			}

			if (isset($input_options['description']))
			{
				$tpl_ary['TITLE_EXPLAIN'] = $this->language->lang($input_options['description']);
				$tpl_ary['S_EXPLAIN'] = true;
			}

			if (in_array($input_options['type'], array('select', 'radio')))
			{
				for ($i = 0, $total = sizeof($input_options['options']); $i < $total; $i++)
				{
					if (isset($input_options['options'][$i]['label']))
					{
						$input_options['options'][$i]['label'] = $this->language->lang($input_options['options'][$i]['label']);
					}
				}

				$tpl_ary['OPTIONS'] = $input_options['options'];
			}

			$this->template->assign_block_vars('options', $tpl_ary);
		}

		$this->template->set_filenames(array(
			'form_install' => 'installer_form.html',
		));

		$this->form = $this->template->assign_display('form_install');
	}

	/**
	 * {@inheritdoc}
	 */
	public function send_response()
	{
		$json_data_array = $this->prepare_json_array();
		$json_data = json_encode($json_data_array);

		// Try to push content to the browser
		print(str_pad(' ', 4096) . "\n");
		print($json_data . "\n\n");
		flush();
	}

	/**
	 * Prepares iohandler's data to be sent out to the client.
	 *
	 * @return array
	 */
	protected function prepare_json_array()
	{
		$json_array = array(
			'errors' => $this->errors,
			'warnings' => $this->warnings,
			'logs' => $this->logs,
			'success' => $this->success,
		);

		if (!empty($this->form))
		{
			$json_array['form'] = $this->form;
			$this->form = '';
		}

		// If current task name is set, we push progress message to the client side
		if (!empty($this->current_task_name))
		{
			$json_array['progress'] = array(
				'task_name'		=> $this->current_task_name,
				'task_num'		=> $this->current_task_progress,
				'task_count'	=> $this->task_progress_count,
			);
		}

		if (!empty($this->nav_data))
		{
			$json_array['nav'] = $this->nav_data;
		}

		$this->errors = array();
		$this->warnings = array();
		$this->logs = array();
		$this->success = array();
		$this->nav_data = array();

		if ($this->request_client_refresh)
		{
			$json_array['refresh'] = true;
			$this->request_client_refresh = false;
		}

		if (!empty($this->cookies))
		{
			$json_array['cookies'] = $this->cookies;
			$this->cookies = array();
		}

		return $json_array;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_progress($task_lang_key, $task_number)
	{
		parent::set_progress($task_lang_key, $task_number);
		$this->send_response();
	}

	/**
	 * {@inheritdoc}
	 */
	public function request_refresh()
	{
		$this->request_client_refresh = true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_active_stage_menu($menu_path)
	{
		$this->nav_data['active'] = $menu_path[sizeof($menu_path) - 1];
		$this->send_response();
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_finished_stage_menu($menu_path)
	{
		$this->nav_data['finished'][] = $menu_path[sizeof($menu_path) - 1];
		$this->send_response();
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_cookie($cookie_name, $cookie_value)
	{
		$this->cookies[] = array(
			'name' => $cookie_name,
			'value' => $cookie_value
		);
	}

	/**
	 * Callback function for language replacing
	 *
	 * @param array	$matches
	 * @return string
	 */
	public function lang_replace_callback($matches)
	{
		if (!empty($matches[1]))
		{
			return $this->language->lang($matches[1]);
		}

		return '';
	}
}
