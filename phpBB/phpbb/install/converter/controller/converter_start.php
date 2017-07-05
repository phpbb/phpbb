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

use Doctrine\DBAL\Driver;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use phpbb\install\helper\iohandler\factory;

use phpbb\install\helper\iohandler\ajax_iohandler;
use phpbb\install\helper\iohandler\cli_iohandler;
use phpbb\install\helper\iohandler\iohandler_interface;

class converter_start
{
	protected $helper;
	/**
	 * @var \phpbb\language\language
	 */
	protected $language;

	protected $container_factory;

	/**
	 * @var \phpbb\template\template
	 */
	protected $template;

	/**
	 * @var string
	 */
	protected $phpbb_root_path;

	protected $db_source;

	protected $db_destination;

	protected $doctrine_drivermanager;

	protected $converter;

	protected $container;

	protected $config;

	protected $request;

	protected $iohandler_factory;

	protected $ajax_iohandler;

	protected $menu_provider;

	protected $yaml_queue;

	/**
	 * Constructor
	 *
	 * @param helper                   $helper
	 * @param \phpbb\language\language $language
	 * @param \phpbb\template\template $template
	 * @param string                   $phpbb_root_path
	 */
	public function __construct($converter, \phpbb\install\converter\controller\helper $helper, $nav_provider, \phpbb\install\helper\iohandler\factory $factory, $request, \phpbb\language\language $language, $container, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->helper = $helper;
		//	$this->converter = $converter_obj;
		$this->language = $language;
		$this->menu_provider = $nav_provider;
		$this->container_factory = $container;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->converter = $converter;
		$this->request = $request;
		$this->iohandler_factory = $factory;
		//$this->ajax_iohandler = $this->factory->get('ajax');
	}

	public function handle()
	{
		$title = 'Converter Framework Conversion in Progress ....';
		$this->menu_provider->set_nav_property(
			array('converter', 0, 'progress'),
			array(
				'selected'	=> true,
				'completed'	=> false,
			)
		);
		$this->menu_provider->set_nav_property(
			array('converter', 0, 'list'),
			array(
				'selected'	=> false,
				'completed'	=> true,
			)
		);
		$this->menu_provider->set_nav_property(
			array('converter', 0, 'home'),
			array(
				'selected'	=> false,
				'completed'	=> true,
			)
		);
		$this->template->assign_vars(array(
			'TITLE'  => $title,
			'BODY'   => $this->language->lang('CONVERTER_CONVERT'),
			'U_LINK' => "/",
		));
		$this->helper->set_conversion_status(false);
		$this->converter->debug_delete_table();
		return $this->helper->render('converter_process.html', $title, true);

	}

	public function ajaxStream()
	{

		$this->iohandler_factory->set_environment('ajax');
		$ajax_handler = $this->iohandler_factory->get();
		$converter = $this->converter;
		$phpbb_root_path = $this->phpbb_root_path;
		$helper = $this->helper;
		$yaml_queue = $this->converter->get_yaml_queue();
		$helper->set_total_files(count($yaml_queue));
		$response = new StreamedResponse();
		$response->setCallback(function () use ($phpbb_root_path, $ajax_handler, $yaml_queue, $helper, $converter)
		{
			$helper->set_conversion_status(true);
			/*The lock must be the first thing to be acquired as the js queries every 250ms for status
			and if we acquire the lock later the js may issue another request before previous completes
			thus stuck in an infinite loop of continue -> lock not acquired -> again continue ....
			*/
			$ajax_handler->acquire_lock();
			$curr_index = $helper->get_file_index();
			if ($helper->get_conversion_status() && $curr_index < count($yaml_queue))
			{
				if (!$helper->get_chunk_status() || $helper->get_chunk_status() === null)
				{
					$helper->set_current_conversion_file($yaml_queue[$curr_index]);
					$ajax_handler->add_log_message('Loading..', 'Fetching next file');
					$helper->set_current_chunk(0);
					$helper->set_chunk_status(true);
					$log_msg = "Converting " . $yaml_queue[$curr_index];
					$ajax_handler->set_task_count(1, true);
					$ajax_handler->set_progress($log_msg, 0.01); //Gives 1 % value at progress bar initially
					$ajax_handler->add_log_message('Converting..', $log_msg);
					$ajax_handler->send_response();
				}
				else
				{
					$total_chunks = $helper->get_total_chunks();
					$chunk = $helper->get_current_chunk();
					$log_msg = "Converting " . $yaml_queue[$curr_index] . "Part[ " . ($chunk+1) . " ]";
					$ajax_handler->add_log_message('Converting..', $log_msg);
					$ajax_handler->set_task_count($total_chunks);
					$ajax_handler->set_progress($log_msg, ($chunk+1));
					$helper->set_current_chunk($chunk + 1);
					$ajax_handler->send_response();

				}


				$converter->begin_conversion($yaml_queue[$curr_index], $helper, $ajax_handler);
				if (!$helper->get_chunk_status())
				{
					$helper->next_file($curr_index);
					sleep(2); //sleeps 2 seconds to prevent abrupt change of progress bar.

					$ajax_handler->send_response();
				}
				$ajax_handler->release_lock();
				/*
				The moment release_lock() is called, when js queries converter_status a continue status is issued
				causing a reload of the request, thus automatically moving to the next file
				*/

			}
			else
			{
				$user = $this->container_factory->get('user');
				$auth = $this->container_factory->get('auth');
				$user->session_begin();
				$auth->acl($user->data);
				$user->setup();
				$helper->set_conversion_status(false);
				$helper->save_config();
				$acp_url = append_sid($phpbb_root_path . 'adm/index.php', 'i=acp_help_phpbb&mode=help_phpbb', true, $user->session_id);
				$ajax_handler->add_success_message('The Converter has finished Conversion'/* @todo make a lang var */, array(
					'ACP_LINK',
					$acp_url,
				));
				$ajax_handler->set_progress('The Converter has finished Conversion', count($yaml_queue));
				$ajax_handler->set_finished_stage_menu(array('converter',0,'progress'));
				$ajax_handler->set_active_stage_menu(array('converter',0,'finished'));
				$ajax_handler->send_response(true);
			}
//print(str_pad(' ', 4096) . "\n");

		});
		$response->headers->set('X-Accel-Buffering', 'no');

		return $response;
	}


//public function ajaxStream()
//	{
//
//			$this->iohandler_factory->set_environment('ajax');
//			$ajax_handler = $this->iohandler_factory->get();
//			$converter = $this->converter;
//			$phpbb_root_path=$this->phpbb_root_path;
//			$helper = $this->helper;
//			$yaml_queue = $this->converter->get_yaml_queue();
//			$response = new StreamedResponse();
//			$response->setCallback(function () use ($phpbb_root_path, $ajax_handler, $yaml_queue, $helper, $converter)
//			{
//				$helper->set_conversion_status(true);
//				/*The lock must be the first thing to be acquired as the js queries every 250ms for status
//				and if we acquire the lock later the js may issue another request before previous completes
//				thus stuck in an infinite loop of continue -> lock not acquired -> again continue ....
//				*/
//				$ajax_handler->acquire_lock();
//				$curr_index = $helper->get_file_index();
//				if ($helper->get_conversion_status() && $curr_index < count($yaml_queue))
//				{
//					if(!$helper->get_chunk_status() || $helper->get_chunk_status() === null)
//					{
//						$helper->set_current_conversion_file($yaml_queue[$curr_index]);
//						$helper->set_current_chunk(0);
//						$helper->set_chunk_status(true);
//						$log_msg = "Converting " . $yaml_queue[$curr_index];
//						$ajax_handler->set_task_count(1,true);
//						$ajax_handler->set_progress($log_msg,0.01);
//						$ajax_handler->add_log_message('Converting..', $log_msg);
//					}
//					else
//					{
//						$total_chunks = $helper->get_total_chunks();
//						$chunk = $helper->get_current_chunk();
//						$log_msg = "Converting " . $yaml_queue[$curr_index]."Part[ ".$chunk." ]";
//						$ajax_handler->add_log_message('Converting..', $log_msg);
//						$ajax_handler->set_task_count($total_chunks);
//						$ajax_handler->set_progress($log_msg,($chunk+1));
//					}
//
//					$ajax_handler->send_response();
//					$converter->begin_conversion($yaml_queue[$curr_index],$helper,$ajax_handler);
//					//sleep(5);
//					if(!$helper->get_chunk_status())
//					{
//						$helper->next_file($curr_index,count($yaml_queue));
//						$ajax_handler->set_task_count(1,true);
//						$ajax_handler->set_progress('Loading..',0.01);
//						$ajax_handler->send_response();
//					}
//					$ajax_handler->release_lock();
//					/*
//					The moment release_lock() is called, when js queries converter_status a continue status is issued
//					causing a reload of the request, thus automatically moving to the next file
//					*/
//
//				}
//				else{
//					$helper->set_conversion_status(false);
//					$helper->save_config();
//					$acp_url = append_sid($phpbb_root_path . 'adm/index.php', 'i=acp_help_phpbb&mode=help_phpbb', true, $user->session_id);
//					$ajax_handler->add_success_message('The Converter has finished Conversion'/* @todo make a lang var*/, array(
//						'ACP_LINK',
//						$acp_url,
//					));
//					$ajax_handler->set_progress('The Converter has finished Conversion',count($yaml_queue));
//					$ajax_handler->send_response(true);
//				}
//				//print(str_pad(' ', 4096) . "\n");
//
//			});
//			$response->headers->set('X-Accel-Buffering', 'no');
//
//			return $response;
//		}


}

