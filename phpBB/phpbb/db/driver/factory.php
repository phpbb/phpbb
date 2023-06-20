<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\db\driver;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Database Abstraction Layer
*/
class factory implements driver_interface
{
	/**
	* @var driver_interface
	*/
	protected $driver = null;

	/**
	* @var ContainerInterface
	*/
	protected $container;

	/**
	* Constructor.
	*
	* @param ContainerInterface $container A ContainerInterface instance
	*/
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	* Return the current driver (and retrieved it from the container if necessary)
	*
	* @return driver_interface
	*/
	protected function get_driver()
	{
		if ($this->driver === null)
		{
			$this->driver = $this->container->get('dbal.conn.driver');
		}

		return $this->driver;
	}

	/**
	* Set the current driver
	*
	* @param driver_interface $driver
	*/
	public function set_driver(driver_interface $driver)
	{
		$this->driver = $driver;
	}

	/**
	* {@inheritdoc}
	*/
	public function set_debug_load_time($value)
	{
		$this->get_driver()->set_debug_load_time($value);
	}

	/**
	* {@inheritdoc}
	*/
	public function set_debug_sql_explain($value)
	{
		$this->get_driver()->set_debug_sql_explain($value);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_layer()
	{
		return $this->get_driver()->get_sql_layer();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_db_name()
	{
		return $this->get_driver()->get_db_name();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_any_char()
	{
		return $this->get_driver()->get_any_char();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_one_char()
	{
		return $this->get_driver()->get_one_char();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_db_connect_id()
	{
		return $this->get_driver()->get_db_connect_id();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_error_triggered()
	{
		return $this->get_driver()->get_sql_error_triggered();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_error_sql()
	{
		return $this->get_driver()->get_sql_error_sql();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_transaction()
	{
		return $this->get_driver()->get_transaction();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_time()
	{
		return $this->get_driver()->get_sql_time();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_sql_error_returned()
	{
		return $this->get_driver()->get_sql_error_returned();
	}

	/**
	* {@inheritdoc}
	*/
	public function get_multi_insert()
	{
		return $this->get_driver()->get_multi_insert();
	}

	/**
	* {@inheritdoc}
	*/
	public function set_multi_insert($multi_insert)
	{
		$this->get_driver()->set_multi_insert($multi_insert);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_row_count($table_name)
	{
		return $this->get_driver()->get_row_count($table_name);
	}

	/**
	* {@inheritdoc}
	*/
	public function get_estimated_row_count($table_name)
	{
		return $this->get_driver()->get_estimated_row_count($table_name);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_lower_text($column_name)
	{
		return $this->get_driver()->sql_lower_text($column_name);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_error($sql = '')
	{
		return $this->get_driver()->sql_error($sql);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_buffer_nested_transactions()
	{
		return $this->get_driver()->sql_buffer_nested_transactions();
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_bit_or($column_name, $bit, $compare = '')
	{
		return $this->get_driver()->sql_bit_or($column_name, $bit, $compare);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_server_info($raw = false, $use_cache = true)
	{
		return $this->get_driver()->sql_server_info($raw, $use_cache);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_return_on_error($fail = false)
	{
		return $this->get_driver()->sql_return_on_error($fail);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_build_array($query, $assoc_ary = array())
	{
		return $this->get_driver()->sql_build_array($query, $assoc_ary);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_fetchrowset($query_id = false)
	{
		return $this->get_driver()->sql_fetchrowset($query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_transaction($status = 'begin')
	{
		return $this->get_driver()->sql_transaction($status);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_concatenate($expr1, $expr2)
	{
		return $this->get_driver()->sql_concatenate($expr1, $expr2);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_case($condition, $action_true, $action_false = false)
	{
		return $this->get_driver()->sql_case($condition, $action_true, $action_false);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_build_query($query, $array)
	{
		return $this->get_driver()->sql_build_query($query, $array);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_fetchfield($field, $rownum = false, $query_id = false)
	{
		return $this->get_driver()->sql_fetchfield($field, $rownum, $query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_fetchrow($query_id = false)
	{
		return $this->get_driver()->sql_fetchrow($query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function cast_expr_to_bigint($expression)
	{
		return $this->get_driver()->cast_expr_to_bigint($expression);
	}

	/**
	 * {@inheritdoc}
	 */
	public function sql_nextid()
	{
		return $this->get_driver()->sql_last_inserted_id();
	}

	/**
	 * {@inheritdoc}
	 */
	public function sql_last_inserted_id()
	{
		return $this->get_driver()->sql_last_inserted_id();
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_add_num_queries($cached = false)
	{
		return $this->get_driver()->sql_add_num_queries($cached);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_query_limit($query, $total, $offset = 0, $cache_ttl = 0)
	{
		return $this->get_driver()->sql_query_limit($query, $total, $offset, $cache_ttl);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_query($query = '', $cache_ttl = 0)
	{
		return $this->get_driver()->sql_query($query, $cache_ttl);
	}

	/**
	* {@inheritdoc}
	*/
	public function cast_expr_to_string($expression)
	{
		return $this->get_driver()->cast_expr_to_string($expression);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_connect($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false, $new_link = false)
	{
		throw new \Exception('Disabled method.');
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_bit_and($column_name, $bit, $compare = '')
	{
		return $this->get_driver()->sql_bit_and($column_name, $bit, $compare);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_freeresult($query_id = false)
	{
		return $this->get_driver()->sql_freeresult($query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_num_queries($cached = false)
	{
		return $this->get_driver()->sql_num_queries($cached);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_multi_insert($table, $sql_ary)
	{
		return $this->get_driver()->sql_multi_insert($table, $sql_ary);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_affectedrows()
	{
		return $this->get_driver()->sql_affectedrows();
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_close()
	{
		return $this->get_driver()->sql_close();
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_rowseek($rownum, &$query_id)
	{
		return $this->get_driver()->sql_rowseek($rownum, $query_id);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_escape($msg)
	{
		return $this->get_driver()->sql_escape($msg);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_like_expression($expression)
	{
		return $this->get_driver()->sql_like_expression($expression);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_not_like_expression($expression)
	{
		return $this->get_driver()->sql_not_like_expression($expression);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_report($mode, $query = '')
	{
		return $this->get_driver()->sql_report($mode, $query);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_in_set($field, $array, $negate = false, $allow_empty_set = false)
	{
		return $this->get_driver()->sql_in_set($field, $array, $negate, $allow_empty_set);
	}

	/**
	* {@inheritdoc}
	*/
	public function sql_quote($msg)
	{
		return $this->get_driver()->sql_quote($msg);
	}

	/**
	 * {@inheritDoc}
	 */
	public function clean_query_id($query_id)
	{
		return $this->get_driver()->clean_query_id($query_id);
	}
}
