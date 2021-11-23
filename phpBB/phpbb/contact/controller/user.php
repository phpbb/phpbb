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

namespace phpbb\contact\controller;

use messenger;
use phpbb\contact\form\form;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use Symfony\Component\HttpFoundation\Response;

class user
{
	/** @var form */
	protected $form;

	/** @var helper */
	protected $helper;

	/** @var language */
	protected $language;

	/** @var request_interface */
	protected $request;

	/** @var template */
	protected $template;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ex;

	/**
	 * user constructor.
	 *
	 * @param form $form
	 * @param helper $helper
	 * @param language $language
	 * @param request_interface $request
	 * @param template $template
	 * @param string $phpbb_root_path
	 * @param string $php_ex
	 */
	public function __construct(form $form, helper $helper, language $language, request_interface $request, template $template, string $phpbb_root_path, string $php_ex)
	{
		$this->form				= $form;
		$this->helper			= $helper;
		$this->language			= $language;
		$this->request			= $request;
		$this->template			= $template;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->php_ex			= $php_ex;
	}

	/**
	 * Controller for /contact/user/{user_id} routes
	 *
	 * @param int		$user_id		User id
	 *
	 * @return Response a Symfony response object
	 */
	public function handle(int $user_id): Response
	{
		if (!class_exists('messenger'))
		{
			include($this->phpbb_root_path . 'includes/functions_messenger.' . $this->php_ex);
		}

		// Load language strings
		$this->language->add_lang('memberlist');

		// Form stuff
		$this->request->overwrite('user_id', $user_id); // Dirty hack
		$this->form->bind();

		$error = $this->form->check_allow();

		if ($error)
		{
			return $this->helper->message($error);
		}

		if ($this->request->is_set_post('submit'))
		{
			$messenger = new messenger(false);
			$this->form->submit($messenger);
		}

		$this->form->render();

		// Breadcrumbs
		$this->render_breadcrumbs($user_id);

		// Render
		return $this->helper->render($this->form->get_template_file(), $this->form->get_page_title());
	}

	/**
	 * Assign template variables related with breadcrumbs
	 */
	protected function render_breadcrumbs(int $user_id): void
	{
		$this->template->assign_block_vars('navlinks', array(
			'BREADCRUMB_NAME'	=> $this->language->lang('SEND_EMAIL'),
			'U_BREADCRUMB'		=> $this->helper->route('phpbb_contact_user', ['user_id' => $user_id]),
		));
	}
}
