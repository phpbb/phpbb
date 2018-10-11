<?php
/**
*
* @package db requests
 * @version : class_db.php,v 1.5 2008/02/27 16:12:56 orynider Exp $
* @copyright (c) 2001-2008 phpBB Group, Icy Phoenix
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	die('Hacking attempt');
}

/**
* This class manages all db requests
*/
class class_db
{

	var $main_db_table = '';
	var $main_db_item = '';

	/*
	* Get items from DB
	*/
	function get_items($n_items = 0, $start = 0, $sort_order = null, $sort_dir = null, $sql_select_extra = '', $sql_from_extra = '', $sql_where_extra = '', $filter_item = '', $filter_item_value = '')
	{
		global $db, $cache;

		$sql_filter_by = (!empty($filter_item) ? (" WHERE i." . $filter_item . " = " . $db->sql_validate_value($filter_item_value) . " ") : '');
		$sql_order_by = (!empty($sort_order) ? ((" ORDER BY i." . $sort_order . " ") . (!empty($sort_dir) ? ($sort_dir . " ") : '')) : '');
		$sql_limit = (!empty($n_items) ? (" LIMIT " . (!empty($start) ? ($start . ", " . $n_items) : $n_items) . " ") : '');
		$sql_where_extra = (!empty($sql_where_extra) ? ((empty($sql_filter_by) ? " WHERE " : " AND ") . $sql_where_extra) : '');

		$sql = "SELECT i.*" . $sql_select_extra . " FROM " . $this->main_db_table . " i" . $sql_from_extra . $sql_filter_by . $sql_where_extra . $sql_order_by . $sql_limit;
		$result = $db->sql_query($sql);

		$items_array = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$items_array[] = $row;
		}
		$db->sql_freeresult($result);

		return $items_array;
	}

	/*
	* Get single item from DB
	*/
	function get_item($item_id, $sql_select_extra = '', $sql_from_extra = '', $sql_where_extra = '')
	{
		global $db, $cache;

		$sql_where_extra = (!empty($sql_where_extra) ? (" AND " . $sql_where_extra) : '');

		$sql = "SELECT i.*" . $sql_select_extra . "
						FROM " . $this->main_db_table . " i" . $sql_from_extra . "
						WHERE i." . $this->main_db_item . " = " . $item_id . "
							" . $sql_where_extra . "
						LIMIT 1";
		$result = $db->sql_query($sql);

		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		return $row;
	}

	/*
	* Insert new item
	*/
	function insert_item($inputs_array)
	{
		global $db, $cache;

		$sql = "INSERT INTO " . $this->main_db_table . " " . $db->sql_build_insert_update($inputs_array, true);
		$result = $db->sql_query($sql);

		return true;
	}

	/*
	* Update existing item
	*/
	function update_item($item_id, $inputs_array)
	{
		global $db, $cache;

		$sql = "UPDATE " . $this->main_db_table . " SET
			" . $db->sql_build_insert_update($inputs_array, false) . "
			WHERE " . $this->main_db_item . " = " . $item_id;
		$result = $db->sql_query($sql);

		return true;
	}

	/*
	* Delete existing item
	*/
	function delete_item($item_id)
	{
		global $db, $cache;

		$sql = "DELETE FROM " . $this->main_db_table . " WHERE " . $this->main_db_item . " = " . $item_id;
		$result = $db->sql_query($sql);

		return true;
	}

	/*
	* Get total items
	*/
	function get_total_items($sql_where_extra = '', $filter_item = '', $filter_item_value = '')
	{
		global $db, $cache;

		$sql_filter_by = (!empty($filter_item) ? (" WHERE " . $filter_item . " = " . $db->sql_validate_value($filter_item_value) . " ") : '');
		$sql_where_extra = (!empty($sql_where_extra) ? ((empty($sql_filter_by) ? " WHERE " : " AND ") . $sql_where_extra) : '');

		$sql = "SELECT COUNT(*) AS total FROM " . $this->main_db_table . $sql_filter_by . $sql_where_extra;
		$result = $db->sql_query($sql);

		$total_items = 0;
		if ($total = $db->sql_fetchrow($result))
		{
			$total_items = $total['total'];
		}
		$db->sql_freeresult($result);

		return $total_items;
	}

	/*
	* Change item order
	*/
	function change_items_order($item_id, $item_order_field, $move)
	{
		global $db, $cache;

		$move = ($move == 1) ? 1 : 0;
		$order = ($move == 1) ? 'DESC' : 'ASC';
		$sql = "SELECT * FROM " . $this->main_db_table . " ORDER BY " . $item_order_field . " " . $order;
		$result = $db->sql_query($sql);

		$items_array = array();
		$items_counter = 0;
		while($row = $db->sql_fetchrow($result))
		{
			$items_counter++;
			if (($item_id == $row[$this->main_db_item]) && ($items_counter > 1))
			{
				$items_array[$items_counter] = $items_array[$items_counter - 1];
				$items_array[$items_counter - 1] = $row[$this->main_db_item];
			}
			else
			{
				$items_array[$items_counter] = $row[$this->main_db_item];
			}
		}

		$items_array_order = array();
		if ($move == 0)
		{
			for ($i = 1; $i <= sizeof($items_array); $i++)
			{
				$items_array_order[] = $items_array[$i];
			}
		}
		else
		{
			for ($i = sizeof($items_array); $i > 0; $i--)
			{
				$items_array_order[] = $items_array[$i];
			}
		}

		for ($i = 0; $i < sizeof($items_array_order); $i++)
		{
			$sql = "UPDATE " . $this->main_db_table . " SET " . $item_order_field . " = " . ($i + 1) . " WHERE " . $this->main_db_item . " = " . $items_array_order[$i];
			$result = $db->sql_query($sql);
		}
	}
}

?>