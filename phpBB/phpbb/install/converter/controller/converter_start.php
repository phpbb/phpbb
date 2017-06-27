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

	protected $yaml_queue;

	/**
	 * Constructor
	 *
	 * @param helper                   $helper
	 * @param \phpbb\language\language $language
	 * @param \phpbb\template\template $template
	 * @param string                   $phpbb_root_path
	 */
	public function __construct($converter, \phpbb\install\converter\controller\helper $helper, $factory, $request, \phpbb\language\language $language, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->helper = $helper;
		//	$this->converter = $converter_obj;
		$this->language = $language;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->converter = $converter;
		$this->request = $request;
		$this->iohandler_factory = $factory;
		//$this->ajax_iohandler = $this->factory->get('ajax');


	}

	public function handle()
	{

		    $title='Converter Framework Conversion in Progress ....';
			$this->template->assign_vars(array(
				'TITLE'        => $title,
				'BODY'         => "The following YAML files would be processed and converted",
				'U_LINK' => "/",
			));

			$this->helper->set_conversion_status(false);



			return $this->helper->render('converter_process.html', $title, true);

	}

	public function ajaxStream()
	{

			$this->iohandler_factory->set_environment('ajax');
			$ajax_handler = $this->iohandler_factory->get();
			$converter = $this->converter;
			$phpbb_root_path=$this->phpbb_root_path;
			$helper = $this->helper;
			$yaml_queue = $this->converter->get_yaml_queue();
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
					$helper->set_current_conversion_file($yaml_queue[$curr_index]);
					$helper->save_config();
					$log_msg = "Converting " . $yaml_queue[$curr_index];
					$ajax_handler->add_log_message('Converting..', $log_msg);
					$ajax_handler->set_task_count(count($yaml_queue));
					$ajax_handler->set_progress($yaml_queue[$curr_index],$curr_index+1);
					$ajax_handler->send_response();
					$converter->begin_conversion($yaml_queue[$curr_index]);
					//sleep(5);
					$helper->next_file();
					$ajax_handler->release_lock();
					/*
					The moment release_lock() is called, when js queries converter_status a continue status is issued
					causing a reload of the request, thus automatically moving to the next file
					*/

				}
				else{
					$helper->set_conversion_status(false);
					$helper->save_config();
					$acp_url = append_sid($phpbb_root_path . 'adm/index.php', 'i=acp_help_phpbb&mode=help_phpbb', true, $user->session_id);
					$ajax_handler->add_success_message('The Converter has finished Conversion'/* @todo make a lang var*/, array(
						'ACP_LINK',
						$acp_url,
					));
					$ajax_handler->set_progress('The Converter has finished Conversion',count($yaml_queue));
					$ajax_handler->send_response(true);
				}
				//print(str_pad(' ', 4096) . "\n");

			});
			$response->headers->set('X-Accel-Buffering', 'no');

			return $response;
		}



}

