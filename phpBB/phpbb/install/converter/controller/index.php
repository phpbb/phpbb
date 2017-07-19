<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\install\converter\controller;

use phpbb\config_php_file;
use Symfony\Component\HttpFoundation\StreamedResponse;


/**
 * Class index
 *
 * @package phpbb\install\converter\controller
 */
class index
{
	/**
	 * @var \phpbb\install\converter\controller\helper
	 */
	protected $helper;
	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var \phpbb\install\helper\navigation\navigation_interface
	 */
	protected $menu_provider;

	/**
	 * @var \phpbb\install\installer_configuration
	 */
	protected $install_config;

	/**
	 * @var \phpbb\install\module_interface
	 */
	protected $module;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $iohandler_factory;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\config_php_file
	 */
	protected $config_php_file;

	/**
	 * index constructor.
	 *
	 * @param \phpbb\install\converter\controller\helper                                                                 $helper
	 * @param \phpbb\install\helper\navigation\navigation_interface|\phpbb\install\helper\navigation\navigation_provider $nav_provider
	 * @param \phpbb\language\language                                                                                   $language
	 * @param \phpbb\template\template                                                                                   $template
	 * @param \phpbb\install\module_interface                                                                            $module
	 * @param \phpbb\install\helper\config|\phpbb\install\installer_configuration                                        $install_config
	 * @param \phpbb\install\helper\iohandler\factory|\phpbb\install\helper\iohandler\iohandler_interface                $iohandler
	 * @param \phpbb\request\request_interface                                                                           $request
	 * @param                                                                                                            $phpbb_root_path
	 * @param                                                                                                            $php_ext
	 */
	public function __construct(\phpbb\install\converter\controller\helper $helper, \phpbb\install\helper\navigation\navigation_provider $nav_provider, \phpbb\language\language $language, \phpbb\template\template $template, \phpbb\install\module_interface $module, \phpbb\install\helper\config $install_config, \phpbb\install\helper\iohandler\factory $iohandler, \phpbb\request\request_interface $request, $phpbb_root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->menu_provider = $nav_provider;
		$this->language = $language;
		$this->template = $template;
		$this->config_php_file = new config_php_file($phpbb_root_path,$php_ext);
		$this->module = $module;
		$this->install_config = $install_config;
		$this->iohandler_factory = $iohandler;
		$this->request = $request;
		$this->helper->handle_language_select();
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\StreamedResponse
	 */
	public function handle()
	{
		if ($this->request->is_ajax())
		{
			$this->iohandler_factory->set_environment('ajax');
			$ajax_handler = $this->iohandler_factory->get();
			$module = $this->module;
			$module->setup($this->install_config,$ajax_handler);
			$response = new StreamedResponse();
			$response->setCallback(function() use ($ajax_handler, $module){
				/*The lock must be the first thing to be acquired as the js queries every 250ms for status
				and if we acquire the lock later the js may issue another request before previous completes
				thus stuck in an infinite loop of continue -> lock not acquired -> again continue ....
				*/
				$ajax_handler->acquire_lock();
				$module->run();
			});
			$response->headers->set('X-Accel-Buffering', 'no');
			return $response;
		}
		else
		{
			$this->menu_provider->set_nav_property(
				array('converter', 0, 'home'),
				array(
					'selected'  => true,
					'completed' => false,
				)
			);
			$this->template->assign_vars(array(
				'TITLE'    => $this->language->lang('CF_TITLE_HOME'),
				'BODY'     => $this->language->lang('CONVERTER_TEXT_INTRO'),
				'U_ACTION' => $this->helper->route('phpbb_converter_index'),
				'U_LINK'   => $this->helper->route('phpbb_converter_convert'),
			));
			return $this->helper->render('converter_main.html', 'CF_TITLE_HOME', true);
		}
	}
}
