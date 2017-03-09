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

namespace phpbb\report\controller;

use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\RedirectResponse;

class report
{
	/**
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * @var \phpbb\user
	 */
	protected $user;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\controller\helper
	 */
	protected $helper;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\captcha\factory
	 */
	protected $captcha_factory;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var string
	 */
	protected $php_ext;

	/**
	 * @var \phpbb\report\report_handler_interface
	 */
	protected $report_handler;

	/**
	 * @var \phpbb\report\report_reason_list_provider
	 */
	protected $report_reason_provider;

	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\template\template $template, \phpbb\controller\helper $helper, \phpbb\request\request_interface $request, \phpbb\captcha\factory $captcha_factory, \phpbb\report\handler_factory $report_factory, \phpbb\report\report_reason_list_provider $ui_provider, $phpbb_root_path, $php_ext)
	{
		$this->config			= $config;
		$this->user				= $user;
		$this->template			= $template;
		$this->helper			= $helper;
		$this->request			= $request;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ext			= $php_ext;
		$this->captcha_factory	= $captcha_factory;
		$this->report_handler	= $report_factory;

		// User interface factory
		$this->report_reason_provider = $ui_provider;
	}

	/**
	 * Controller for /path_to_entities/{id}/report routes
	 *
	 * Because of how phpBB organizes routes $mode must be set in the route config.
	 *
	 * @param int		$id		ID of the entity to report
	 * @param string	$mode
	 * @return \Symfony\Component\HttpFoundation\Response a Symfony response object
	 * @throws \phpbb\exception\http_exception when $mode or $id is invalid for some reason
	 */
	public function handle($id, $mode)
	{
		// Get report handler
		$this->report_handler = $this->report_handler->get_instance($mode);

		$this->user->add_lang('mcp');

		$user_notify	= ($this->user->data['is_registered']) ? $this->request->variable('notify', 0) : false;
		$reason_id		= $this->request->variable('reason_id', 0);
		$report_text	= $this->request->variable('report_text', '', true);

		$submit = $this->request->variable('submit', '');
		$cancel = $this->request->variable('cancel', '');

		$error = array();
		$s_hidden_fields = '';

		$redirect_url = append_sid(
			$this->phpbb_root_path . ( ($mode === 'pm') ? 'ucp' : 'viewtopic' ) . ".{$this->php_ext}",
			($mode == 'pm') ? "i=pm&mode=view&p=$id" : "p=$id"
		);
		$redirect_url .= ($mode === 'post') ? "#p$id" : '';

		// Set up CAPTCHA if necessary
		if ($this->config['enable_post_confirm'] && !$this->user->data['is_registered'])
		{
			$captcha = $this->captcha_factory->get_instance($this->config['captcha_plugin']);
			$captcha->init(CONFIRM_REPORT);
		}

		//Has the report been cancelled?
		if (!empty($cancel))
		{
			return new RedirectResponse($redirect_url, 302);
		}

		// Check CAPTCHA, if the form was submited
		if (!empty($submit) && isset($captcha))
		{
			$captcha_template_array = $this->check_captcha($captcha);
			$error = $captcha_template_array['error'];
			$s_hidden_fields = $captcha_template_array['hidden_fields'];
		}

		// Handle request
		try
		{
			if (!empty($submit) && sizeof($error) === 0)
			{
				$this->report_handler->add_report(
					(int) $id,
					(int) $reason_id,
					(string) $report_text,
					(int) $user_notify
				);

				// Send success message
				switch ($mode)
				{
					case 'pm':
						$lang_return = $this->user->lang['RETURN_PM'];
						$lang_success = $this->user->lang['PM_REPORTED_SUCCESS'];
					break;
					case 'post':
						$lang_return = $this->user->lang['RETURN_TOPIC'];
						$lang_success = $this->user->lang['POST_REPORTED_SUCCESS'];
					break;
				}

				$this->helper->assign_meta_refresh_var(3, $redirect_url);
				$message = $lang_success . '<br /><br />' . sprintf($lang_return, '<a href="' . $redirect_url . '">', '</a>');
				return $this->helper->message($message);
			}
			else
			{
				$this->report_handler->validate_report_request($id);
			}
		}
		catch (\phpbb\report\exception\pm_reporting_disabled_exception $exception)
		{
			throw new http_exception(404, 'PAGE_NOT_FOUND');
		}
		catch (\phpbb\report\exception\already_reported_exception $exception)
		{
			switch ($mode)
			{
				case 'pm':
					$message = $this->user->lang['ALREADY_REPORTED_PM'];
					$message .= '<br /><br />' . sprintf($this->user->lang['RETURN_PM'], '<a href="' . $redirect_url . '">', '</a>');
				break;
				case 'post':
					$message = $this->user->lang['ALREADY_REPORTED'];
					$message .= '<br /><br />' . sprintf($this->user->lang['RETURN_TOPIC'], '<a href="' . $redirect_url . '">', '</a>');
				break;
			}

			return $this->helper->message($message);
		}
		catch (\phpbb\report\exception\report_permission_denied_exception $exception)
		{
			$message = $exception->getMessage();
			if (isset($this->user->lang[$message]))
			{
				$message = $this->user->lang[$message];
			}

			throw new http_exception(403, $message);
		}
		catch (\phpbb\report\exception\entity_not_found_exception $exception)
		{
			$message = $exception->getMessage();
			if (isset($this->user->lang[$message]))
			{
				$message = $this->user->lang[$message];
			}

			throw new http_exception(404, $message);
		}
		catch (\phpbb\report\exception\empty_report_exception $exception)
		{
			$error[] = $this->user->lang['EMPTY_REPORT'];
		}
		catch (\phpbb\report\exception\invalid_report_exception $exception)
		{
			return $this->helper->message($exception->getMessage());
		}

		// Setting up an rendering template
		$page_title = ($mode === 'pm') ? $this->user->lang['REPORT_MESSAGE'] : $this->user->lang['REPORT_POST'];
		$this->assign_template_data(
			$mode,
			$id,
			$reason_id,
			$report_text,
			$user_notify,
			$error,
			$s_hidden_fields,
			( isset($captcha) ? $captcha : false )
		);

		return $this->helper->render('report_body.html', $page_title);
	}

	/**
	 * Assigns template variables
	 *
	 * @param	int		$mode
	 * @param	int		$id
	 * @param	int		$reason_id
	 * @param	string	$report_text
	 * @param	mixed	$user_notify
	 * @param 	array	$error
	 * @param	string	$s_hidden_fields
	 * @param	mixed	$captcha
	 * @return	null
	 */
	protected function assign_template_data($mode, $id, $reason_id, $report_text, $user_notify, $error = array(), $s_hidden_fields = '', $captcha = false)
	{
		if ($captcha !== false && $captcha->is_solved() === false)
		{
			$this->template->assign_vars(array(
				'S_CONFIRM_CODE'	=> true,
				'CAPTCHA_TEMPLATE'	=> $captcha->get_template(),
			));
		}

		$this->report_reason_provider->display_reasons($reason_id);

		switch ($mode)
		{
			case 'pm':
				$report_route = $this->helper->route('phpbb_report_pm_controller', array('id' => $id));
			break;
			case 'post':
				$report_route = $this->helper->route('phpbb_report_post_controller', array('id' => $id));
			break;
		}

		$this->template->assign_vars(array(
			'ERROR'				=> (sizeof($error) > 0) ? implode('<br />', $error) : '',
			'S_REPORT_POST'		=> ($mode === 'pm') ? false : true,
			'REPORT_TEXT'		=> $report_text,
			'S_HIDDEN_FIELDS'	=> (!empty($s_hidden_fields)) ? $s_hidden_fields : null,
			'S_REPORT_ACTION'	=> $report_route,

			'S_NOTIFY'			=> $user_notify,
			'S_CAN_NOTIFY'		=> ($this->user->data['is_registered']) ? true : false,
			'S_IN_REPORT'		=> true,
		));
	}

	/**
	 * Check CAPTCHA
	 *
	 * @param	object	$captcha	A phpBB CAPTCHA object
	 * @return	array	template variables which ensures that CAPTCHA's work correctly
	 */
	protected function check_captcha($captcha)
	{
		$error = array();
		$captcha_hidden_fields = '';

		$visual_confirmation_response = $captcha->validate();
		if ($visual_confirmation_response)
		{
			$error[] = $visual_confirmation_response;
		}

		if (sizeof($error) === 0)
		{
			$captcha->reset();
		}
		else if ($captcha->is_solved() !== false)
		{
			$captcha_hidden_fields = build_hidden_fields($captcha->get_hidden_fields());
		}

		return array(
			'error' => $error,
			'hidden_fields' => $captcha_hidden_fields,
		);
	}
}
