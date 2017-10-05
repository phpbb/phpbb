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

namespace phpbb\install\controller;

use phpbb\exception\http_exception;
use phpbb\install\helper\install_helper;
use phpbb\install\helper\navigation\navigation_provider;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use phpbb\install\helper\iohandler\factory;
use phpbb\template\template;
use phpbb\request\request_interface;
use phpbb\install\installer;
use phpbb\language\language;

/**
 * Controller for installing phpBB
 */
class install
{
	/**
	 * @var helper
	 */
	protected $controller_helper;

	/**
	 * @var factory
	 */
	protected $iohandler_factory;

	/**
	 * @var navigation_provider
	 */
	protected $menu_provider;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * @var request_interface
	 */
	protected $request;

	/**
	 * @var installer
	 */
	protected $installer;

	/**
	 * @var install_helper
	 */
	protected $install_helper;

	/**
	 * Constructor
	 *
	 * @param helper 				$helper
	 * @param factory 				$factory
	 * @param navigation_provider	$nav_provider
	 * @param language				$language
	 * @param template				$template
	 * @param request_interface		$request
	 * @param installer				$installer
	 * @param install_helper		$install_helper
	 */
	public function __construct(helper $helper, factory $factory, navigation_provider $nav_provider, language $language, template $template, request_interface $request, installer $installer, install_helper $install_helper)
	{
		$this->controller_helper	= $helper;
		$this->iohandler_factory	= $factory;
		$this->menu_provider		= $nav_provider;
		$this->language				= $language;
		$this->template				= $template;
		$this->request				= $request;
		$this->installer			= $installer;
		$this->install_helper		= $install_helper;
	}

	/**
	 * Controller logic
	 *
	 * @return Response|StreamedResponse
	 *
	 * @throws http_exception When phpBB is already installed
	 */
	public function handle()
	{
		if ($this->install_helper->is_phpbb_installed())
		{
			throw new http_exception(403, 'INSTALL_PHPBB_INSTALLED');
		}

		$this->template->assign_vars(array(
			'U_ACTION' => $this->controller_helper->route('phpbb_installer_install'),
		));

		// Set up input-output handler
		if ($this->request->is_ajax())
		{
			$this->iohandler_factory->set_environment('ajax');
		}
		else
		{
			$this->iohandler_factory->set_environment('nojs');
		}

		// Set the appropriate input-output handler
		$this->installer->set_iohandler($this->iohandler_factory->get());
		$this->controller_helper->handle_language_select();

		if ($this->request->is_ajax())
		{
			$installer = $this->installer;
			$response = new StreamedResponse();
			$response->setCallback(function() use ($installer) {
				$installer->run();
			});

			// Try to bypass any server output buffers
			$response->headers->set('X-Accel-Buffering', 'no');

			return $response;
		}
		else
		{
			// Determine whether the installation was started or not
			if (true)
			{
				// Set active stage
				$this->menu_provider->set_nav_property(
					array('install', 0, 'introduction'),
					array(
						'selected'	=> true,
						'completed'	=> false,
					)
				);

				// If not, let's render the welcome page
				$this->template->assign_vars(array(
					'SHOW_INSTALL_START_FORM'	=> true,
					'TITLE'						=> $this->language->lang('INSTALL_INTRO'),
					'CONTENT'					=> $this->language->lang('INSTALL_INTRO_BODY'),
				));

				/** @var \phpbb\install\helper\iohandler\iohandler_interface $iohandler */
				$iohandler = $this->iohandler_factory->get();
				$this->controller_helper->handle_navigation($iohandler);

				return $this->controller_helper->render('installer_install.html', 'INSTALL', true);
			}

			// @todo: implement no js controller logic
		}
	}
}
