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

	protected $container;

	protected $config;

	/**
	 * Constructor
	 *
	 * @param helper                   $helper
	 * @param \phpbb\language\language $language
	 * @param \phpbb\template\template $template
	 * @param string                   $phpbb_root_path
	 */
	public function __construct(\phpbb\install\converter\controller\helper $helper, \phpbb\language\language $language, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->helper = $helper;

		$this->language = $language;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
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
