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

class controller_convert
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

	protected $menu_provider;

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
	public function __construct($converter, \phpbb\install\converter\controller\helper $helper, $nav_provider, $factory, $request, \phpbb\language\language $language, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->helper = $helper;
		//	$this->converter = $converter_obj;
		$this->language = $language;
		$this->menu_provider = $nav_provider;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->converter = $converter;
		$this->request = $request;
		$this->factory = $factory;
		//$this->ajax_iohandler = $this->factory->get('ajax');


	}

	public function handle()
	{


		$this->helper->handle_language_select();

		$title = "Converter Framework Home";
		// $data="<html><body>";
		// $data.=$this->converter->demo_load();
		// $data.="</body></html>";
		//
		// $con =new \Symfony\Component\DependencyInjection\Reference('bala.object');
		//also tried using $container->get. Get will give an
		//error saying the DIC container does not know to construct the synthetic service.

		/* DB config @todo Make use of request object to set credentials array*/
		$credentials_source = array(
			'dbname'   => 'phpBBgsoc',
			'user'     => 'root',
			'password' => '123',
			'host'     => 'localhost',
			'driver'   => 'pdo_mysql',
		);
		$credentials_destination = array(
			'dbname'   => 'phpBBgsoc_dest',
			'user'     => 'root',
			'password' => '123',
			'host'     => 'localhost',
			'driver'   => 'pdo_mysql',
		);
		$this->helper->set_source_db($credentials_source);
		$this->helper->set_destination_db($credentials_destination);

		$this->yaml_queue = $this->converter->get_yaml_queue();

		$this->helper->set_conversion_status(true);
		$this->helper->set_file_index(0);
		//$this->helper->set_total_files(count($this->yaml_queue));
		$this->helper->set_chunk_status(false);
		$this->helper->set_current_chunk(0);




//		$stmt = $this->converter->demo_load();
//		$val = array();
//		while ($row = $stmt->fetch())
//		{
//			$val = $row;
//			break;
//		}
//		$val = array_values($val);
		$this->menu_provider->set_nav_property(
			array('converter', 0, 'list'),
			array(
				'selected'	=> true,
				'completed'	=> false,
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
			'TITLE'        => $title,
			'BODY'         => $this->language->lang('YAML_LIST_DESC'),
			'T_YAML_FILES' => $this->yaml_queue,
			'U_LINK' => $this->helper->route('phpbb_converter_start'),
		));


		return $this->helper->render('converter_list.html', $title, true);

	}

	public function ajaxResponse()
	{

	}
}

