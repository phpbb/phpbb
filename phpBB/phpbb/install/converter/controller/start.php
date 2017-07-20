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

use phpbb\install\converter\bin\converter;
use phpbb\install\converter\controller\helper;
use phpbb\install\helper\config;
use phpbb\install\helper\container_factory;
use phpbb\install\helper\iohandler\factory;
use phpbb\install\helper\navigation\navigation_provider;
use phpbb\install\module_interface;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class start
 *
 * @package phpbb\install\converter\controller
 */
class start
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
	 * @var \phpbb\install\helper\container_factory
	 */
	protected $container_factory;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	/**
	 * @var \phpbb\install\converter\bin\converter
	 */
	protected $converter;

	/**
	 * @var
	 */
	protected $container;

	/**
	 * @var \phpbb\install\helper\config
	 */
	protected $install_config;

	/**
	 * @var \phpbb\request\request_interface
	 */
	protected $request;

	/**
	 * @var \phpbb\install\helper\iohandler\factory
	 */
	protected $iohandler_factory;

	/**
	 * @var
	 */
	protected $ajax_iohandler;

	/**
	 * @var \phpbb\install\helper\navigation\navigation_provider
	 */
	protected $menu_provider;

	/**
	 * @var yaml_queue variable
	 */
	protected $yaml_queue;

	/**
	 * @var \phpbb\install\module_interface
	 */
	protected $module;

	/**
	 * start constructor.
	 *
	 * @param \phpbb\install\converter\bin\converter               $converter
	 * @param \phpbb\install\converter\controller\helper           $helper
	 * @param \phpbb\install\helper\navigation\navigation_provider $nav_provider
	 * @param \phpbb\install\helper\iohandler\factory              $factory
	 * @param \phpbb\install\helper\config                         $config
	 * @param \phpbb\install\module_interface                      $module
	 * @param \phpbb\request\request_interface                     $request
	 * @param \phpbb\language\language                             $language
	 * @param \phpbb\install\helper\container_factory              $container
	 * @param \phpbb\template\template                             $template
	 * @param                                                      $phpbb_root_path
	 */
	public function __construct(converter $converter, helper $helper, navigation_provider $nav_provider, factory $factory, config $config, module_interface $module, request_interface $request, language $language, container_factory $container, template $template, $phpbb_root_path)
	{
		$this->helper = $helper;
		$this->language = $language;
		$this->menu_provider = $nav_provider;
		$this->container_factory = $container;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->converter = $converter;
		$this->request = $request;
		$this->iohandler_factory = $factory;
		$this->module = $module;
		$this->install_config = $config;
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
			$module->setup($this->install_config, $ajax_handler);
			$converter = $this->converter;
			$phpbb_root_path = $this->phpbb_root_path;
			$helper = $this->helper;
			$yaml_queue = $this->converter->get_yaml_queue();
			$helper->set_total_files(count($yaml_queue));
			$response = new StreamedResponse();
			$container_factory = $this->container_factory;
			$response->setCallback(function () use ($container_factory, $module, $phpbb_root_path, $ajax_handler, $yaml_queue, $helper, $converter)
			{
				/*The lock must be the first thing to be acquired as the js queries every 250ms for status
				and if we acquire the lock later the js may issue another request before previous completes
				thus stuck in an infinite loop of continue -> lock not acquired -> again continue ....
				*/
				$ajax_handler->acquire_lock();
				$module->run();
				if ($helper->get_conversion_status() && ($helper->get_file_index() < count($yaml_queue)))
				{
					$ajax_handler->release_lock();
				}
				else
				{
					$user = $container_factory->get('user');
					$auth = $container_factory->get('auth');
					$user->session_begin();
					$auth->acl($user->data);
					$user->setup();
					$helper->set_conversion_status(false);
					$helper->save_config();
					$acp_url = append_sid($phpbb_root_path . 'adm/index.php', 'i=acp_help_phpbb&mode=help_phpbb', true, $user->session_id);
					$ajax_handler->add_success_message('CF_FINISHED'/* @todo make a lang var */, array(
						'ACP_LINK',
						$acp_url,
					));// todo language files to be added.
					$ajax_handler->set_progress('The Converter has finished Conversion', count($yaml_queue));
					$ajax_handler->set_finished_stage_menu(array('converter', 0, 'progress'));
					$ajax_handler->set_active_stage_menu(array('converter', 0, 'finished'));
					$ajax_handler->send_response(true);
				}
			});
			$response->headers->set('X-Accel-Buffering', 'no');
			return $response;
		}
		else
		{
			$this->menu_provider->set_nav_property(
				array('converter', 0, 'progress'),
				array(
					'selected'  => true,
					'completed' => false,
				)
			);
			$this->menu_provider->set_nav_property(
				array('converter', 0, 'list'),
				array(
					'selected'  => false,
					'completed' => true,
				)
			);
			$this->menu_provider->set_nav_property(
				array('converter', 0, 'home'),
				array(
					'selected'  => false,
					'completed' => true,
				)
			);
			$this->template->assign_vars(array(
				'TITLE'  => $this->language->lang('CF_IN_PROGRESS'),
				'BODY'   => $this->language->lang('CONVERTER_CONVERT'),
				'U_LINK' => $this->helper->route('phpbb_converter_start'),
			));
			$this->helper->set_conversion_status(false);
			//$this->converter->debug_delete_table();
			return $this->helper->render('converter_process.html', 'CF_IN_PROGRESS', true);
		}
	}
}