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
use phpbb\config_php_file;

class converter_index
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

	protected $menu_provider;

	protected $container;

	protected $install_config;

	protected $module;

	protected $iohandler_factory;

	/**
	 * @var \phpbb\config_php_file
	 */
	protected $config_php_file;


	/**
	 * Constructor
	 *
	 * @param helper                   $helper
	 * @param \phpbb\language\language $language
	 * @param \phpbb\template\template $template
	 * @param string                   $phpbb_root_path
	 */
	public function __construct(\phpbb\install\converter\controller\helper $helper, $container, $nav_provider, \phpbb\language\language $language, \phpbb\template\template $template, $module, $install_config, $iohandler, $phpbb_root_path, $php_ext)
	{
		$this->helper = $helper;
		$this->menu_provider = $nav_provider;
		$this->language = $language;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->config_php_file = new config_php_file($phpbb_root_path,$php_ext);
		$this->module = $module;
		$this->install_config = $install_config;
		$this->iohandler_factory = $iohandler;
		//$this->db_source = $db_source;
		/*
		 * DB credentials logic now moved to controller_convert.php class
		 */



		//$this->db_source = \Doctrine\DBAL\DriverManager::getConnection($credentials_source, $this->config);

		//$this->db_destination = \Doctrine\DBAL\DriverManager::getConnection($credentials_source, $this->config);
		//$obj = (object) array('name' => 'bala');
		//$container->set('bala.object', $obj);
		//	$container->compile();
		$this->helper->handle_language_select();

	}

	public function handle()
	{

		//$this->container->set('dbal.connection.source', $this->db_source);
		//$this->container->set('dbal.connection.destination', $this->db_destination);
		//$con = $this->container->get('dbal.connection.source');
		//$qb = $con->createQueryBuilder();
		//$qb->select('user_name')->from('phpBB_user');
		//$stmt = $qb->execute();


		$title = "Converter Framework Home";
		// $data="<html><body>";
		// $data.=$this->converter->demo_load();
		// $data.="</body></html>";

		$this->menu_provider->set_nav_property(
			array('converter', 0, 'home'),
			array(
				'selected'	=> true,
				'completed'	=> false,
			)
		);
		$this->iohandler_factory->set_environment('ajax');
		$ajax_handler = $this->iohandler_factory->get();
		//$this->module->setup($this->install_config, $ajax_handler);
		//$this->module->run();

		$this->template->assign_vars(array(
			'TITLE'  => $title,
			'BODY'   => $this->language->lang('CONVERTER_TEXT_INTRO'),
			'U_LINK' => $this->helper->route('phpbb_converter_convert'),
		));



		//	$doctrine = $container->get('doctrine');
		return $this->helper->render('converter_main.html', $title, true);
		//return new \Symfony\Component\HttpFoundation\Response($data, 200);
	}
}
