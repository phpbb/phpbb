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
use Symfony\Component\HttpFoundation\JsonResponse;

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

	protected $factory;

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
		$this->factory = $factory;
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

	public function ajaxResponse()
	{
		$this->yaml_queue = $this->converter->get_yaml_queue();
		$this->helper->set_conversion_status(true);

		$curr_index = $this->helper->get_file_index();

		if ($this->helper->get_conversion_status() && $curr_index < count($this->yaml_queue))
		{
			$this->helper->set_current_conversion_file($this->yaml_queue[$curr_index]);
			$this->converter->dummy_load($this->yaml_queue[$curr_index]);
			sleep(10);
			$this->helper->next_file();
			return new Response('reload',Response::HTTP_OK,array('content-type'=>'text/html'));
		}
		else{
			$this->helper->set_conversion_status(false);
			return new Response('end',Response::HTTP_OK,array('content-type'=>'text/html'));
		}

	}

	public function ajaxStatus()
	{
		if($this->helper->get_conversion_status())
		{
			$data=array(
				'file' =>$this->helper->get_current_conversion_file(),
				'index'=>$this->helper->get_file_index(),
				'total'=>count($this->yaml_queue),

			);


			return new JsonResponse($data, Response::HTTP_OK, array('content-type' => 'text/html'));
		}
		else
		{
			return new Response("no-conversion", Response::HTTP_OK, array('content-type' => 'application/json'));
		}
	}

//	public function ajaxResponse()
//	{
//		$response = new StreamedResponse();
//		$response->setCallback(function(){
//
//			$x=array(
//				'string' =>'hello world',
//				'stage' => 'converting',
//			);
//			//print(str_pad(' ', 4096) . "\n");
//			print("start");
//			ob_flush();
//			flush();
//			sleep(5);
//			//print(str_pad(' ', 4096) . "\n");
//			print("end");
//			ob_flush();
//			flush();
//
//
//		});
//		$response->headers->set('X-Accel-Buffering', 'no');
//
//		return $response;
//	}
}

