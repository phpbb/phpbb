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

	/**
	 * Constructor
	 *
	 * @param helper                   $helper
	 * @param \phpbb\language\language $language
	 * @param \phpbb\template\template $template
	 * @param string                   $phpbb_root_path
	 */
	public function __construct($converter, \phpbb\install\converter\controller\helper $helper, \phpbb\language\language $language, \phpbb\template\template $template, $phpbb_root_path)
	{
		$this->helper = $helper;
		//	$this->converter = $converter_obj;
		$this->language = $language;
		$this->template = $template;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->converter = $converter;


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

		$stmt = $this->converter->demo_load();
		$val = array();
		while($row = $stmt->fetch())
		{
			$val=$row;
			break;
		}
		$val = array_values($val);


		$this->template->assign_vars(array(
			'TITLE' => $title,
			'BODY'  => $val[0],
		));


		return $this->helper->render('converter_main.html', $title, true);

	}
}
