<?php
/**
*
* @package phpBB3
* @version $Id$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * Formatted text class to handle any text that can contain BBCodes, smilies, magic URLs or under word censor.
 */
class formatted_text
{
	/**
	 * Unformated text.
	 *
	 * @var string
	 */
	private $text;

	/**
	 * Internal representation (first_pass data).
	 *
	 * @var string
	 */
	private $meta;
	
	/**
	 * Formatting options as bit flag.
	 * 
	 * @see bbcode_parser
	 *
	 * @var int
	 */
	private $flags;
	
	/**
	 * Compiled $text. For dispaly.
	 *
	 * @var string
	 */
	private $compiled;
	
	/**
	 * Set to true if text, meta or flags have been changed, false otherwise.
	 *
	 * @var bool
	 */
	private $changed;
	
	/**
	 * DB table to update
	 *
	 * @var string
	 */
	private $update_table = '';
	
	/**
	 * Column in $update_table to update.
	 *
	 * @var string
	 */
	private $update_column = '';
	
	/**
	 * Where clause for auto update.
	 *
	 * @var string
	 */
	private $update_where = '';
	
	/**
	 * Creates a new instance.
	 *
	 * @param string $text
	 * @param string $meta
	 * @param int $flags
	 */
	public function __construct($text, $meta = '', $flags = 0)
	{
		$this->text		= $text;
		$this->meta		= $meta;
		$this->flags	= $flags;
		$this->compiled	= '';

		if ($meta == '')
		{
			$this->changed = true;
		}
		else
		{
			$this->changed = false;
		}
	}

	public function __destruct()
	{
		if ($this->changed && $this->update_table)
		{
			$this->to_db($this->update_table, $this->update_column, $this->update_where);
		}
	}
	
	/**
	 * Convieniently initialize a formatted_text object from
	 * a database result set. The array must contain the following indexes:
	 * $column, {$column}_meta and {$column}_flags
	 * 
	 *
	 * @param array $data
	 * @param string $column
	 * @return formatted_text
	 */
	public static function from_db_data(array $data, $column)
	{
		return new formatted_text($data[$column], $data[$column . '_meta'], (int) $data[$column . '_flags']);
	}
	
	/**
	 * Returns the $text formatted, ready to be displayed on a webpage.
	 *
	 * @return string
	 */
	public function to_display()
	{
		$this->set_compiled();
		return $this->compiled;
	}

	/**
	 * Updates $table, sets $column, {$column}_meta and {$column}_flags.
	 * All 3 columns must exist.
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $where
	 * @return bool
	 */
	public function to_db($table, $column, $where = '1')
	{
		global $db;
		$this->changed = false;
		
		$sql = 'UPDATE ' . $table . ' SET ' . $db->sql_build_query('UPDATE', $this->to_db_data($column))
					. ' WHERE ' . $where;
		return (bool) $db->sql_query($sql);
	}

	/**
	 * Returns an array containing $column, {$column}_meta and {$column}_flags
	 * indexes to be used with query generating functions.
	 *
	 * @param string $column
	 * @return array
	 */
	public function to_db_data($column)
	{
		$this->set_meta();
		return array($column => $this->text, $column . '_meta' => $this->meta, $column . '_flags' => $this->flags);
	}

	/**
	 * Enable automatic database update on
	 *
	 * @param string $table
	 * @param string $column
	 * @param string $where
	 */
	public function set_auto_update($table, $column, $where = '1')
	{
		$this->update_table = $table;
		$this->update_column = $column;
		$this->update_where = $where;
	}
	
	/**
	 * Sets $meta if not set.
	 */
	private function set_meta()
	{
		if (strlen($this->meta))
		{
			return;
		}

		$parser = new phpbb_bbcode_parser;
		$this->meta = $parser->first_pass($this->text);
	}
	
	/**
	 * Sets $compiled if not set.
	 */
	private function set_compiled()
	{
		$this->set_meta();

		if (strlen($this->compiled))
		{
			return;
		}

		$parser = new phpbb_bbcode_parser;
		$parser->set_flags($this->flags);
		$this->compiled = $parser->second_pass($this->meta);
	}

	/**
	 * Sets $text.
	 *
	 * @param string $text
	 */
	public function set_text($text)
	{
		if ($this->text != $text)
		{
			$this->text = (string) $text;
			$this->meta = '';
			$this->compiled = '';
		}
	}

	/**
	 * Sets $flags.
	 *
	 * @param int $flags
	 */
	public function set_flags($flags)
	{
		$flags = (int) $flags;
		if ($this->flags != $flags)
		{
			$this->flags = $flags;
			$this->compiled = '';
		}
	}
	
	/**
	 * Returns the current text.
	 *
	 * @return string
	 */
	public function get_text()
	{
		return $this->text;
	}
	
	/**
	 * Returns the current(!!) metadata.
	 *
	 * @return string
	 */
	public function get_meta()
	{
		return $this->meta;
	}
	
	/**
	 * Returns the current flags.
	 *
	 * @return int
	 */
	public function get_flags()
	{
		return $this->flags;
	}
	
	/**
	 * Returns true if $this is equal to $other.
	 * Objects are only equal if $text and $flags are equal.
	 *
	 * @param formatted_text $other
	 * @return bool
	 */
	public function eq(formatted_text $other)
	{
		return $this->flags == $other->get_flags() && $this->text == $other->get_text();
	}

	/**
	 * Cast to string. Object is represented by the formatted version of $text with respect to $flags.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->to_display();
	}
}
