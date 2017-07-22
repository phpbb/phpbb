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

namespace phpbb\install\converter\bin;

require_once 'config_map.php';

use \Symfony\Component\Yaml\Yaml;
/**
 * Class converter
 *
 * @package phpbb\install\converter\bin
 */
class converter
{

	/**
	 * @var \Doctrine\DBAL\DriverManager
	 */
	protected $db_source;

	/**
	 * @var \Doctrine\DBAL\DriverManager
	 */
	protected $db_destination;

	/**
	 * @var
	 */
	protected $config;

	/**
	 * @var mixed
	 */
	protected $yamlQ;

	/**
	 * @var string
	 */
	protected $phpbb_root;

	/**
	 * @var \phpbb\install\helper\iohandler\factory
	 */
	protected $iohandler_factory;

	/**
	 * @var \phpbb\install\helper\iohandler\iohandler_interface
	 */
	protected $ajax_handler;

	/**
	 * @var \phpbb\install\converter\controller\helper
	 */
	protected $helper;

	/**
	 * @var int
	 */
	public static $limit=100;

	/**
	 * converter constructor.
	 *
	 * @param \Doctrine\DBAL\DriverManager               $db_source
	 * @param \Doctrine\DBAL\DriverManager               $db_destination
	 * @param \phpbb\install\helper\iohandler\factory    $factory
	 * @param \phpbb\install\converter\controller\helper $helper
	 * @param string                                     $phpbb_root
	 */
	function __construct($db_source, $db_destination,\phpbb\install\helper\iohandler\factory $factory, \phpbb\install\converter\controller\helper $helper,$phpbb_root){
		$this->phpbb_root = $phpbb_root;
		$this->iohandler_factory = $factory;
		$this->iohandler_factory->set_environment('ajax');
		$this->ajax_handler = $this->iohandler_factory->get();
		$this->helper = $helper;
		$this->db_source = $db_source;
		$this->db_destination = $db_destination;
		$this->yamlQ = Yaml::parse(file_get_contents(/*@todo fix the file links */'http://localhost.phpbb/phpbb/install/converter/configmap/conversionQ.yml'));
	}

	/**
	 * @return mixed
	 */
	function get_yaml_queue()
	{
		return $this->yamlQ;
	}

	/**
	 * @return mixed
	 */
	function build_process_queue()
	{
		//Queue Builder Function to get a Quee of YAML files to process.
		//Hardcoded file name as conversionQ.yml
		$this->yamlQ = Yaml::parse(file_get_contents('http://localhost.phpbb/phpbb/install/converter/configmap/conversionQ.yml'));
		return $this->yamlQ;
	}
	function convert()
	{
		foreach($this->yamlQ as $config_file)
		{
			$this->helper->set_current_conversion_file($config_file);
		}
	}

	/**
	 * @return mixed
	 */
	function demo_load()
	{	$qb = $this->db_destination->createQueryBuilder();
		$qb->select('user_name')->from('phpBB_user');
		$stmt = $qb->execute();
		return $stmt;
	}

	function dummy_load()
	{
		sleep(5);
	}

	function debug_delete_table()
	{
		$sql1='DELETE FROM phpBB_user';
		$sql2= 'DELETE FROM phpBB_posts';
		$this->db_destination->query($sql1);
		$this->db_destination->query($sql2);
	}

	/**
	 * @param $file
	 * @param $helper
	 * @param $ajax_handler
	 */
	function begin_conversion($file,$helper,$ajax_handler)
	{
		//Function responsible for starting the conversion by generating the configMap object.
		try
		{
			$cf = new config_map($this->db_source, $this->db_destination, $file, $helper, $this->phpbb_root);
		}
		catch(\Exception $e)
		{
			throw $e;
		}
		$total_records = $cf->get_total_records();
		$length = $total_records/self::$limit;
		$helper->set_total_chunks(ceil($length));
		$current_chunk = $helper->get_current_chunk();
		if($current_chunk>=$length)
		{
			$helper->set_chunk_status(false);
			$helper->set_current_chunk(0);
		}
		else
		{
			var_dump($current_chunk);
			$helper->set_chunk_status(true);
			$cf->copy_data($current_chunk);
		}
	}
}
