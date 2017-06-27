<?php
//The logger class for logging converter action. A common object to be initialized by converter.
//A different and more detailed logger can be implemented as needs be. A simple logger is provide for basic debugging.
namespace converter\cl;

require_once 'converter_logger_interface.php';

class converter_logger implements \converter\cl_iface\converter_logger_interface
{
	/**
	 * @var string
	 */
	public $file_name;
	/**
	 * @var bool|resource
	 * Log file object
	 */
	public $file;

	/**
	 * converter_logger constructor.
	 * Creates converter_log.txt at converter/Logs. Please make this folder writable.
	 */
	public function __construct()
	{
		$this->file_name = __DIR__.'/Logs/converter_log.txt';
		$this->file = fopen($this->file_name, 'w+') or die("Unable to open or initialize log file");
	}

	/**
	 * @return string
	 * Returns current time formatted as a time_stamp.
	 */
	public static function get_time_stamp()
	{
		return '['.date('j-n-Y \a\t h:i:s A').']'." ";
	}

	/**
	 * Log start of conversion.
	 */
	public function conversion_start()
	{
		$txt = self::get_time_stamp()."Conversion started\n";
		fwrite($this->file, $txt);
	}

	/**
	 * @param $yaml_config_file
	 * Log start of conversion of a particular yaml config map.
	 */
	public function start_conversion_yaml_file($yaml_config_file)
	{
		$txt = self::get_time_stamp()."Converting file ".$yaml_config_file."\n";
		fwrite($this->file, $txt);
	}

	/**
	 * @param $error_desc
	 * @param $yaml_config_file
	 */
	public function error_during_converting_yaml_file($error_desc, $yaml_config_file)
	{
		$txt = self::get_time_stamp()."ERROR: ".$error_desc." IN CONVERSION".$yaml_config_file."\n";
		fwrite($this->file, $txt);
	}

	/**
	 * @param $error_desc
	 */
	public function error_before_conversion($error_desc)
	{
		$txt = self::get_time_stamp()."FATAL ERROR ".$error_desc."\n";
	}

	/**
	 * @param $yaml_config_file
	 * Logs end of conversion of particular yaml file
	 */
	public function end_conversion_yaml_file($yaml_config_file)
	{
		$txt = self::get_time_stamp()."SUCCESS Converted file ".$yaml_config_file."\n";
		fwrite($this->file, $txt);
	}

	/**
	 *Logs end of whole conversion process.
	 */
	public function conversion_end()
	{
		$txt = self::get_time_stamp()."Conversion Ended\n";
		fwrite($this->file, $txt);
	}
}

