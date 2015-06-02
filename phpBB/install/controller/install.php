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

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controller for installing phpBB
 */
class install
{
	/**
	 * @var \phpbb\install\controller\helper
	 */
	protected $controller_helper;

	/**
	 * @var \phpbb\install\helper\iohandler\factory
	 */
	protected $iohandler_factory;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\install\installer
	 */
	protected $installer;

	/**
	 * Constructor
	 *
	 * @param helper $helper
	 * @param \phpbb\install\helper\iohandler\factory $factory
	 * @param \phpbb\request\request_interface $request
	 * @param \phpbb\install\installer $installer
	 */
	public function __construct(helper $helper, \phpbb\install\helper\iohandler\factory $factory, \phpbb\template\template $template, \phpbb\request\request_interface $request, \phpbb\install\installer $installer)
	{
		$this->controller_helper = $helper;
		$this->iohandler_factory = $factory;
		$this->template = $template;
		$this->request = $request;
		$this->installer = $installer;
	}

	/**
	 * Controller logic
	 *
	 * @return \Symfony\Component\HttpFoundation\Response|StreamedResponse
	 */
	public function handle()
	{
		// @todo check that phpBB is not already installed

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
		//$this->installer->set_iohandler($this->iohandler_factory->get());

		if ($this->request->is_ajax())
		{
			// @todo: remove this line, and use the above
			$this->installer->set_iohandler($this->iohandler_factory->get());

			$installer = $this->installer;
			$response = new StreamedResponse();
			$response->setCallback(function() use ($installer) {
				$installer->run();
			});

			return $response;
		}
		else
		{
			// Determine whether the installation was started or not
			if (true)
			{
				// If not, let's render the welcome page
				$this->template->assign_vars(array(
					'SHOW_INSTALL_START_FORM' => true,
				));
				return $this->controller_helper->render('installer_install.html', 'INSTALL');
			}

			// @todo: implement no js controller logic
		}
	}
}
