<?php

namespace phpbb\install\converter\bin;

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


	function __construct($db_source, $db_destination,\phpbb\install\converter\controller\helper $helper){
		$this->db_source = $db_source;
		$this->db_destination = $db_destination;
		$this->helper = $helper;
		$this->yamlQ = Yaml::parse(file_get_contents('http://localhost.phpbb/phpbb/install/converter/configmap/conversionQ.yml'));
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

	function begin_conversion($file)
	{
		//Function responsible for starting the conversion by generating the configMap object.
		//This function will be wrapped over by another function to process every yaml class from yamlQ;
		//Since we havent created a Q system, we will just be using this function for now.
		$cf = new config_map($this->con_source, $this->con_destination ,$file);

	}
}
