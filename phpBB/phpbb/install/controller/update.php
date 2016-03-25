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
use phpbb\install\helper\iohandler\factory;
use phpbb\install\helper\navigation\navigation_provider;
use phpbb\install\installer;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Updater controller
 */
class update
{
	/**
	 * @var helper
	 */
	protected $controller_helper;

	/**
	 * @var installer
	 */
	protected $installer;

	/**
	 * @var install_helper
	 */
	protected $install_helper;

	/**
	 * @var factory
	 */
	protected $iohandler_factory;

	/**
	 * @var language
	 */
	protected $language;

	/**
	 * @var navigation_provider
	 */
	protected $menu_provider;

	/**
	 * @var request_interface
	 */
	protected $request;

	/**
	 * @var template
	 */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param helper				$controller_helper
	 * @param installer				$installer
	 * @param install_helper		$install_helper
	 * @param factory				$iohandler
	 * @param language				$language
	 * @param navigation_provider	$menu_provider
	 * @param request_interface		$request
	 * @param template				$template
	 */
	public function __construct(helper $controller_helper, installer $installer, install_helper $install_helper, factory $iohandler, language $language, navigation_provider $menu_provider, request_interface $request, template $template)
	{
		$this->controller_helper	= $controller_helper;
		$this->installer			= $installer;
		$this->install_helper		= $install_helper;
		$this->iohandler_factory	= $iohandler;
		$this->language				= $language;
		$this->menu_provider		= $menu_provider;
		$this->request				= $request;
		$this->template				= $template;
	}

	/**
	 * Controller entry point
	 *
	 * @return Response|StreamedResponse
	 *
	 * @throws http_exception When phpBB is not installed
	 */
	public function handle()
	{
		if (!$this->install_helper->is_phpbb_installed())
		{
			throw new http_exception(403, 'INSTALL_PHPBB_NOT_INSTALLED');
		}

		$this->template->assign_vars(array(
			'U_ACTION' => $this->controller_helper->route('phpbb_installer_update'),
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

		// Render the intro page
		if ($this->request->is_ajax())
		{
			$installer = $this->installer;
			$response = new StreamedResponse();
			$response->setCallback(function() use ($installer) {
				$installer->run();
			});

			// Try to bypass any server output buffers
			$response->headers->set('X-Accel-Buffering', 'no');
			$response->headers->set('Content-type', 'application/json');

			return $response;
		}
		else
		{
			// Set active stage
			$this->menu_provider->set_nav_property(
				array('update', 0, 'introduction'),
				array(
					'selected'	=> true,
					'completed'	=> false,
				)
			);

			$this->template->assign_vars(array(
				'SHOW_INSTALL_START_FORM'	=> true,
				'TITLE'						=> $this->language->lang('UPDATE_INSTALLATION'),
				'CONTENT'					=> $this->language->lang('UPDATE_INSTALLATION_EXPLAIN'),
			));

			/** @var \phpbb\install\helper\iohandler\iohandler_interface $iohandler */
			$iohandler = $this->iohandler_factory->get();
			$this->controller_helper->handle_navigation($iohandler);

			return $this->controller_helper->render('installer_update.html', 'UPDATE_INSTALLATION', true);
		}
	}
}
