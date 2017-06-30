<?php

namespace phpbb\install\converter\bin;

require_once 'config_map.php';

use \Symfony\Component\Yaml\Yaml;

class converter
{
	protected $credentials_source;
	protected $credentials_destination;
	protected $db_source;
	protected $db_destination;
	protected $config;
	protected $yamlQ;
	protected $container;
	protected $phpbb_root;
	protected $iohandler_factory;
	protected $ajax_handler;
	protected $helper;
	public static $limit=100;



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

	function get_yaml_queue()
	{
		return $this->yamlQ;
	}

	function build_process_queue()
	{
		//Quee Builder Function to get a Quee of YAML files to process.
		//Hardcode file name as conversionQ.yml
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

	function begin_conversion($file,$helper,$ajax_handler)
	{
		//Function responsible for starting the conversion by generating the configMap object.
		//This function will be wrapped over by another function to process every yaml class from yamlQ;
		//Since we havent created a Q system, we will just be using this function for now.
		$cf = new config_map($this->db_source, $this->db_destination ,$file, $helper, $this->phpbb_root);
		$total_records = $cf->get_total_records();
		$length = $total_records/self::$limit;
		$current_chunk = $helper->get_current_chunk();
		if($current_chunk>$length)
		{
			$helper->set_chunk_status(false);
			$helper->set_current_chunk(0);

		}
		else
		{
			var_dump($current_chunk);
			$helper->set_chunk_status(true);
			$cf->copy_data($current_chunk);
			$helper->set_current_chunk($current_chunk + 1);
		}

	}
}
