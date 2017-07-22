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

require_once 'util_conversion_functions.php';

//Get the YAML component ready to use
use \Symfony\Component\Yaml\Yaml;
use \Exception;

/**
 * Class config_map
 */
class config_map
{
	/**
	 * @var object
	 * Allows you to interface with your YAML map
	 */
	protected $data_map;
	/**
	 * @var mixed
	 * It is recommended to use the data_map object to interact with your object
	 */
	protected $data_map_arr;
	/**
	 * @var
	 * Please use this object to fire your SQL Queries.
	 * All Query processing is done as for as possible via Query Builder Objects, rather than firing direct SQL queries.
	 * As for as possible please adhere to the same.
	 */
	protected $db_source;

	/**
	 * @var
	 * Same as $db_source for destination DB connection
	 */
	protected $db_destination;

	/**
	 * @var array
	 */
	public $source_col = array();
	/**
	 * @var array
	 */
	public $dest_col = array();
	/**
	 * @var array
	 */
	public $conversion_function = array(); //Array to hold function to be applied on source data. ie $dest=func($source)
	/**
	 * @var
	 */
	protected $table_source;
	/**
	 * @var
	 */
	protected $table_destination;

	protected $phpbb_root;

	/**
	 * @var phpBB helper object
	 */
	public $helper;

	/**
	 * @var int
	 */
	public $total_rows;

	/**
	 * @var int
	 */
	public static $chunk_size =100;

	/**
	 * @var string
	 */
	public $config_file_base = 'phpbb/install/converter/configmap/';

	/**
	 * config_map constructor.
	 * @param $con_source
	 * @param $con_destination
	 * @param $file
	 */
	public function __construct($con_source, $con_destination, $file, \phpbb\install\converter\controller\helper $helper, $phpbb_root)
	{
		//The constructor will initialize the constructor object, and intiitalize the mapping object data_map to begin conversion$this->db_source = $con_source;
		$this->db_destination = $con_destination;
		$this->db_source = $con_source;
		$this->phpbb_root = $phpbb_root;
		$file_link= $phpbb_root.$this->config_file_base.$file;
		//$con and thus $db are Doctrine\DBAL\DriverManager::getConnection() object. Basically the DBAL connection object.
		try
		{
			//$fobj = file_get_contents($file_link);
			$fobj = false;
			if($fobj == false )
			{
				throw new Exception('Config file not found exception');
			}
		}
		catch(\Exception $e)
		{
			throw $e;
		}
		$this->data_map_arr = Yaml::parse($fobj);
		$this->data_map = (object)$this->data_map_arr;
		$this->set_table();
		$this->set_col();
		$this->set_total_records();
		//$this->get_conversion_function();
		//$this->copy_data_OLD();
	}

	/**
	 * Setter for source and destination table names
	 */
	public function set_table()
	{
		$this->table_source = $this->data_map->table_def['table_source'];
		$this->table_destination = $this->data_map->table_def['table_destination'];
	}

	/**
	 * Setter for source and destination column names
	 */
	public function set_col()
	{
		for ($i = 0; $i < count($this->data_map->col_def); $i++)
		{
			array_push($this->source_col, $this->data_map->col_def[$i]['col1']);
			array_push($this->dest_col, $this->data_map->col_def[$i]['col2']);
			//Check if 'function' key exists
			if (array_key_exists('function', $this->data_map->col_def[$i]))
			{
				array_push($this->conversion_function, $this->data_map->col_def[$i]['function']);
			} else
			{
				array_push($this->conversion_function, null);//simulates a NULL
			}
		}
	}

	/**
	 *Dummy getter
	 * @todo remove it after testing
	 */
	public function get_source_and_dest_col()
	{
		print_r($this->source_col);
		echo '<br/>';
		print_r($this->dest_col);
	}

	/**
	 * @return array
	 * Dummy getter for testing.
	 * @todo remove it after testing
	 */
	public function get_source_col()
	{
		return $this->source_col;
	}

	/**
	 * @return array
	 * Dummy getter for testing.
	 * @todo remove it after testing
	 */
	public function get_dest_col()
	{
		return $this->dest_col;
	}

	/**
	 *Getter to make array of conversion functions.
	 * @todo give a util conversion func file
	 */
	public function get_conversion_function()
	{
		print_r($this->conversion_function);
		echo '<br/>';
		for ($i=0; $i < count($this->conversion_function); $i++)
		{
			if ($this->conversion_function[$i]!=null)
			{
				print_r($this->conversion_function[$i]('dummy_val'));
				echo '<br/>';
			}
		}
	}

	/*
	 * Get total records which will be converted;
	 */
	public function set_total_records()
	{
		$query = 'SELECT COUNT(*) FROM '.$this->table_source;
		//var_dump($query);
		$stmt = $this->db_source->prepare($query);
		$stmt->execute();
		$this->total_rows = $stmt->fetchColumn(0);
	}

	public function get_total_records()
	{
		return $this->total_rows;
	}

	/**
	 * @param $chunk
	 *
	 */
	public function copy_data($chunk)
	{
		/*
		 * @todo check for timeout over here and gracefully fail since we cannot even convert 100 entries before a timeout
		 *
		 */
 		$offset = $chunk*self::$chunk_size;
 		$limit = ($this->total_rows-$offset>self::$chunk_size)?self::$chunk_size:($this->total_rows-$offset);
		$query_source = $this->db_source->createQueryBuilder(); //Query Builder object;
		var_dump($this->total_rows);
		$query_source->select($this->source_col)->from($this->table_source)->setFirstResult($offset)->setMaxResults($limit);
		$stmt_source = $query_source->execute();
		while ($each_row = $stmt_source->fetch())
		{ //As every row is fetched keep inserting
			 //Holds final converted values
			$values_row = array();
			$values_orig_row = array_values($each_row); //we just want the values and not coloumn names from row
			//Apply conversion functions to $values_orig_row
			for ($i = 0; $i < count($values_orig_row); $i++)
			{
				if ($this->conversion_function[$i] == null)
				{ // == used since 0, '', NULL must all get treated same
					array_push($values_row, $values_orig_row[$i]);
				} else
				{
					array_push($values_row, $this->conversion_function[$i]($values_orig_row[$i]));
				}
			}
			$insert_array = array_combine($this->dest_col, $values_row); //An array of dest-col names as keys and corresponding to be inserted values as pairs.
			$this->db_destination->insert($this->table_destination, $insert_array);
			//print_r("Succesfully completed"); //Debug
		}
	}


	/**
	 * Effects the actual conversion. @todo Old function to be deprecated
	 */
	public function copy_data_OLD()
	{
		$query_source = $this->db_source->createQueryBuilder(); //Query Builder object;
		//var_dump($this->table_source);
		$query_source->select($this->source_col)->from($this->table_source);
		$stmt_source = $query_source->execute();
		while ($each_row = $stmt_source->fetch())
		{ //As every row is fetched keep inserting
			 //Holds final converted values
			$values_row = array();
			$values_orig_row = array_values($each_row); //we just want the values and not coloumn names from row
			//Apply conversion functions to $values_orig_row
			for ($i = 0; $i < count($values_orig_row); $i++)
			{
			   if ($this->conversion_function[$i] == null)
				{ // == used since 0, '', NULL must all get treated same
				  array_push($values_row, $values_orig_row[$i]);
				} else
				{
				   array_push($values_row, $this->conversion_function[$i]($values_orig_row[$i]));
				}
			}
			$insert_array = array_combine($this->dest_col, $values_row); //An array of dest-col names as keys and corresponding to be inserted values as pairs.
			$this->db_destination->insert($this->table_destination, $insert_array);
			//print_r("Succesfully completed"); //Debug
		}
	}
}
