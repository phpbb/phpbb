<?php
//The logger interface for logging converter actions. A common object to be initialized by converter.
namespace converter\cl_iface;

interface converter_logger_interface
{

	public function conversion_start(); //Function to log start of conversion routine

	public function start_conversion_yaml_file($yaml_config_file); //Indicates conversion of a particular config file has started

	public function error_during_converting_yaml_file($yaml_config_file, $error_desc); //Indicates error occured during conversion

	public function error_before_conversion($error_desc); //Log Database connectivity/file not found errors

	public function end_conversion_yaml_file($yaml_config_file); //Logs end of one yaml file conversion

	public function conversion_end(); //Logs the end of a conversion routine

}


