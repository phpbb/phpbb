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

namespace phpbb\db;

use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\FetchMode;
use phpbb\db\driver\driver_interface;

/**
 * Wrapper class for Doctrine DBAL's `ResultStatement`.
 *
 * This iterator is needed to provide access to seek operations on Doctrine's results, which is required to
 * provide BC for the old database abstraction layer.
 *
 * @deprecated 4.0.0-dev Use Doctrine DBAL directly instead of this class.
 */
final class result_iterator implements result_iterator_interface
{
	/**
	 * @var int
	 */
	private static $s_id = 0;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var ResultStatement|array
	 */
	private $data;

	/**
	 * @var int
	 */
	private $target_position = 0;

	/**
	 * @var int
	 */
	private $position = -1;

	/**
	 * @var array|null
	 */
	private $current = null;

	/**
	 * @var string
	 */
	private $sql;

	/**
	 * @var driver_interface
	 */
	private $driver;

	/**
	 * Constructor.
	 *
	 * @param ResultStatement	$statement	A Doctrine DBAL result statement.
	 * @param string			$sql		The SQL query.
	 * @param driver_interface	$driver		The database driver.
	 */
	public function __construct(ResultStatement $statement, string $sql, driver_interface $driver)
	{
		$this->data = $statement;
		$this->sql = $sql;
		$this->driver = $driver;
		$this->id = self::$s_id++;
	}

	/**
	 * Iterator destructor.
	 */
	public function __destruct()
	{
		if ($this->data instanceof ResultStatement)
		{
			$this->data->closeCursor();
		}
	}

	/**
	 * Moves the iterator to the specified position.
	 *
	 * @param int $position The position to which the iterator should be moved to.
	 */
	public function seek($position)
	{
		$this->target_position = $position;
	}

	/**
	 * Rewinds the iterator to the starting position.
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->seek(0);
	}

	/**
	 * Returns the current value to which the iterator points.
	 *
	 * Note: If the current state of the iterator was not validated by calling `valid()` the return value is
	 * 		 undefined.
	 *
	 * @return array|null
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 * Returns the current iterator position.
	 *
	 * Note: If the current state of the iterator was not validated by calling `valid()` the return value is
	 * 		 undefined.
	 *
	 * @return int The current iterator position.
	 */
	public function key()
	{
		return $this->position;
	}

	/**
	 * Advances the iterator to the next element.
	 *
	 * @return void
	 */
	public function next()
	{
		++$this->target_position;
	}

	/**
	 * Returns whether or not the state of the iterator is valid.
	 *
	 * Note: Determining whether the current position is valid requires actually fetching the data.
	 *
	 * @return bool Returns whether the current positions is valid.
	 */
	public function valid()
	{
		$this->iterate();
		return is_array($this->current);
	}

	/**
	 * {@inheritDoc}
	 */
	public function fetch_all()
	{
		if (!($this->data instanceof ResultStatement))
		{
			return $this->data;
		}

		$rows = $this->data->fetchAll(FetchMode::ASSOCIATIVE);
		$this->data->closeCursor();
		$this->data = $rows;

		return $this->data;
	}

	/**
	 * {@inheritDoc}
	 */
	public function invalidate()
	{
		if ($this->data instanceof ResultStatement)
		{
			$this->data->closeCursor();
		}

		$this->data = [];
		$this->current = null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_id()
	{
		return $this->id;
	}

	/**
	 * Move the iterator and fetch the current element.
	 *
	 * @return void
	 */
	private function iterate()
	{
		switch ($this->position <=> $this->target_position)
		{
			case 1:
				$this->iterate_backward();
			break;
			case -1:
				$this->iterate_forward();
			break;
		}

		$this->position = $this->target_position;
	}

	/**
	 * Fetch an element before the current iterator position.
	 *
	 * @return void
	 */
	private function iterate_backward()
	{
		if ($this->data instanceof ResultStatement)
		{
			$this->reexectue_query();
			$this->fetch_all();
		}

		$this->fetch_from_array();
	}

	/**
	 * Fetch an element after the current iterator position.
	 *
	 * @return void
	 */
	private function iterate_forward()
	{
		if (is_array($this->data))
		{
			$this->fetch_from_array();
			return;
		}

		$offset = $this->target_position - $this->position;
		for ($i = 0; $i < $offset; ++$i)
		{
			$this->current = $this->data->fetch(FetchMode::ASSOCIATIVE);
			if ($this->current === false)
			{
				$this->current = null;
				break;
			}
		}
	}

	/**
	 * Fetches the target position into current from the data array.
	 *
	 * @return void
	 */
	private function fetch_from_array()
	{
		$this->current = array_key_exists($this->target_position, $this->data) ? $this->data[$this->target_position] : null;
	}

	/**
	 * Executes the query once more.
	 */
	private function reexectue_query()
	{
		// @todo: ttl?
		$this->data = $this->driver->sql_query($this->sql);
	}
}
