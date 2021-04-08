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

namespace phpbb\event;

class rst_exporter
{
	/** @var array Column keys */
	private $columns = [];

	/** @var array Column headers map */
	private $column_headers = [];

	/** @var array Maximum lengths of columns */
	private $max_lengths = [];

	/** @var string rst data */
	private $rst_data = '';

	/**
	 * Set columns with array where key is column name and value is title of column in table
	 *
	 * @param array $column_data
	 */
	public function set_columns(array $column_data): void
	{
		foreach ($column_data as $column_key => $column_header)
		{
			$this->columns[] = $column_key;
			$this->column_headers[$column_key] = $column_header;
		}
	}

	/**
	 * Add header to rst page
	 *
	 * @param string $type Type of header; allowed are h2, h3, h4 corresponding to HTML
	 * @param string $header_text Text of header
	 */
	public function add_section_header(string $type, string $header_text): void
	{
		$this->rst_data .= $header_text . "\n";

		switch ($type)
		{
			case 'h2':
				$header_character = '=';
			break;

			default:
			case 'h3':
				$header_character = '-';
			break;

			case 'h4':
				$header_character = '~';
			break;
		}

		$this->rst_data .= str_repeat($header_character, strlen($header_text)) . "\n\n";
	}

	/**
	 * Fill table with event data
	 *
	 * @param array $event_data
	 */
	public function generate_events_table(array $event_data): void
	{
		$this->rst_data .= ".. table::\n";
		$this->rst_data .= "    :class: events-list\n\n";

		$this->set_max_lengths($event_data);

		// Create table header
		$this->rst_data .= $this->get_separator_line();
		$this->rst_data .= "    |";
		foreach ($this->columns as $column)
		{
			$this->rst_data .= $this->get_column($column, $this->column_headers[$column]);
		}

		$this->rst_data .= "\n" . $this->get_separator_line('=');

		foreach ($event_data as $event)
		{
			$event_data = [];
			$max_column_rows = 1;
			foreach ($event as $key => $value)
			{
				$column_rows = !is_array($value) ? substr_count($value, '<br>') + 1 : 1;
				$max_column_rows = max($max_column_rows, $column_rows);
				$event_data[$key] = $column_rows > 1 ? explode('<br>', $value) : [is_array($value) ? implode(', ', $value) : $value];
			}

			for ($i = 0; $i < $max_column_rows; $i++)
			{
				$this->rst_data .= '    |';

				foreach ($this->columns as $column)
				{
					$this->rst_data .= $this->get_column($column, $event_data[$column][$i] ?? '');
				}
				$this->rst_data .= "\n";
			}
			$this->rst_data .= $this->get_separator_line();
		}
	}

	/**
	 * Get rst output
	 *
	 * @return string
	 */
	public function get_rst_output(): string
	{
		return $this->rst_data;
	}

	/**
	 * Set maximum lengths array
	 *
	 * @param array $event_data
	 */
	private function set_max_lengths(array $event_data): void
	{
		$this->max_lengths = [];

		foreach ($this->columns as $column)
		{
			$this->max_lengths[$column] = strlen($this->column_headers[$column]);
		}

		foreach ($event_data as $event)
		{
			foreach ($this->columns as $column)
			{
				$event_column = is_array($event[$column]) ? implode(', ', $event[$column]) : $event[$column];
				$this->max_lengths[$column] = max($this->max_lengths[$column], strlen($event_column));
			}
		}
	}

	/**
	 * Get separator line
	 *
	 * @param string $separator_character
	 * @return string
	 */
	private function get_separator_line(string $separator_character = '-'): string
	{
		$line = "    +";

		foreach ($this->columns as $column)
		{
			$line .= str_repeat($separator_character, $this->max_lengths[$column] + 2) . '+';
		}

		return $line . "\n";
	}

	/**
	 * Get table data column
	 *
	 * @param string $type Column type
	 * @param string $content Column content
	 * @return string
	 */
	private function get_column(string $type, string $content): string
	{
		$content = rtrim($content);
		return ' ' . $content . str_repeat(' ' , $this->max_lengths[$type] - strlen($content) + 1) . '|';
	}
}
