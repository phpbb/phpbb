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

/**
* Class php_exporter
* Crawls through a list of files and grabs all php-events
*/
class php_exporter
{
	/** @var string Path where we look for files*/
	protected $path;

	/** @var string phpBB Root Path */
	protected $root_path;

	/** @var string The minimum version for the events to return */
	protected $min_version;

	/** @var string The maximum version for the events to return */
	protected $max_version;

	/** @var string */
	protected $current_file;

	/** @var string */
	protected $current_event;

	/** @var int */
	protected $current_event_line;

	/** @var array */
	protected $events;

	/** @var array */
	protected $file_lines;

	/**
	* @param string $phpbb_root_path
	* @param mixed $extension	String 'vendor/ext' to filter, null for phpBB core
	* @param string $min_version
	* @param string $max_version
	*/
	public function __construct($phpbb_root_path, $extension = null, $min_version = null, $max_version = null)
	{
		$this->root_path = $phpbb_root_path;
		$this->path = $phpbb_root_path;
		$this->events = $this->file_lines = array();
		$this->current_file = $this->current_event = '';
		$this->current_event_line = 0;
		$this->min_version = $min_version;
		$this->max_version = $max_version;

		$this->path = $this->root_path;
		if ($extension)
		{
			$this->path .= 'ext/' . $extension . '/';
		}
	}

	/**
	* Get the list of all events
	*
	* @return array		Array with events: name => details
	*/
	public function get_events()
	{
		return $this->events;
	}

	/**
	* Set current event data
	*
	* @param string	$name	Name of the current event (used for error messages)
	* @param int	$line	Line where the current event is placed in
	* @return null
	*/
	public function set_current_event($name, $line)
	{
		$this->current_event = $name;
		$this->current_event_line = $line;
	}

	/**
	* Set the content of this file
	*
	* @param array $content		Array with the lines of the file
	* @return null
	*/
	public function set_content($content)
	{
		$this->file_lines = $content;
	}

	/**
	* Crawl the phpBB/ directory for php events
	* @return int	The number of events found
	*/
	public function crawl_phpbb_directory_php()
	{
		$files = $this->get_recursive_file_list();
		$this->events = array();
		foreach ($files as $file)
		{
			$this->crawl_php_file($file);
		}
		ksort($this->events);

		return count($this->events);
	}

	/**
	* Returns a list of files in $dir
	*
	* @return	array	List of files (including the path)
	*/
	public function get_recursive_file_list()
	{
		try
		{
			$iterator = new \RecursiveIteratorIterator(
				new \phpbb\event\recursive_event_filter_iterator(
					new \RecursiveDirectoryIterator(
						$this->path,
						\FilesystemIterator::SKIP_DOTS
					),
					$this->path
				),
				\RecursiveIteratorIterator::LEAVES_ONLY
			);
		}
		catch (\Exception $e)
		{
			return array();
		}

		$files = array();
		foreach ($iterator as $file_info)
		{
			/** @var \RecursiveDirectoryIterator $file_info */
			$relative_path = $iterator->getInnerIterator()->getSubPathname();
			$files[] = str_replace(DIRECTORY_SEPARATOR, '/', $relative_path);
		}

		return $files;
	}

	/**
	* Format the php events as a wiki table
	*
	* @param string $action
	* @return string
	*/
	public function export_events_for_wiki($action = '')
	{
		if ($action === 'diff')
		{
			$wiki_page = '=== PHP Events (Hook Locations) ===' . "\n";
		}
		else
		{
			$wiki_page = '= PHP Events (Hook Locations) =' . "\n";
		}
		$wiki_page .= '{| class="sortable zebra" cellspacing="0" cellpadding="5"' . "\n";
		$wiki_page .= '! Identifier !! Placement !! Arguments !! Added in Release !! Explanation' . "\n";
		foreach ($this->events as $event)
		{
			$wiki_page .= '|- id="' . $event['event'] . '"' . "\n";
			$wiki_page .= '| [[#' . $event['event'] . '|' . $event['event'] . ']] || ' . $event['file'] . ' || ' . implode(', ', $event['arguments']) . ' || ' . $event['since'] . ' || ' . $event['description'] . "\n";
		}
		$wiki_page .= '|}' . "\n";

		return $wiki_page;
	}

	/**
	* @param string $file
	* @return int Number of events found in this file
	* @throws \LogicException
	*/
	public function crawl_php_file($file)
	{
		$this->current_file = $file;
		$this->file_lines = array();
		$content = file_get_contents($this->path . $this->current_file);
		$num_events_found = 0;

		if (strpos($content, 'dispatcher->trigger_event(') || strpos($content, 'dispatcher->dispatch('))
		{
			$this->set_content(explode("\n", $content));
			for ($i = 0, $num_lines = count($this->file_lines); $i < $num_lines; $i++)
			{
				$event_line = false;
				$found_trigger_event = strpos($this->file_lines[$i], 'dispatcher->trigger_event(');
				$arguments = array();
				if ($found_trigger_event !== false)
				{
					$event_line = $i;
					$this->set_current_event($this->get_event_name($event_line, false), $event_line);

					// Find variables of the event
					$arguments = $this->get_vars_from_array();
					$doc_vars = $this->get_vars_from_docblock();
					$this->validate_vars_docblock_array($arguments, $doc_vars);
				}
				else
				{
					$found_dispatch = strpos($this->file_lines[$i], 'dispatcher->dispatch(');
					if ($found_dispatch !== false)
					{
						$event_line = $i;
						$this->set_current_event($this->get_event_name($event_line, true), $event_line);
					}
				}

				if ($event_line)
				{
					// Validate @event
					$event_line_num = $this->find_event();
					$this->validate_event($this->current_event, $this->file_lines[$event_line_num]);

					// Validate @since
					$since_line_num = $this->find_since();
					$since = $this->validate_since($this->file_lines[$since_line_num]);

					$changed_line_nums = $this->find_changed('changed');
					if (empty($changed_line_nums))
					{
						$changed_line_nums = $this->find_changed('change');
					}
					$changed_versions = array();
					if (!empty($changed_line_nums))
					{
						foreach ($changed_line_nums as $changed_line_num)
						{
							$changed_versions[] = $this->validate_changed($this->file_lines[$changed_line_num]);
						}
					}

					if (!$this->version_is_filtered($since))
					{
						$valid_version = false;
						foreach ($changed_versions as $changed)
						{
							$valid_version = $valid_version || $this->version_is_filtered($changed);
						}

						if (!$valid_version)
						{
							continue;
						}
					}

					// Find event description line
					$description_line_num = $this->find_description();
					$description_lines = array();

					while (true)
					{
						$description_line = substr(trim($this->file_lines[$description_line_num]), strlen('*'));
						$description_line = trim(str_replace("\t", " ", $description_line));

						// Reached end of description if line is a tag
						if (strlen($description_line) && $description_line[0] == '@')
						{
							break;
						}

						$description_lines[] = $description_line;
						$description_line_num++;
					}

					// If there is an empty line between description and first tag, remove it
					if (!strlen(end($description_lines)))
					{
						array_pop($description_lines);
					}

					$description = trim(implode('<br/>', $description_lines));

					if (isset($this->events[$this->current_event]))
					{
						throw new \LogicException("The event '{$this->current_event}' from file "
							. "'{$this->current_file}:{$event_line_num}' already exists in file "
							. "'{$this->events[$this->current_event]['file']}'", 10);
					}

					sort($arguments);
					$this->events[$this->current_event] = array(
						'event'			=> $this->current_event,
						'file'			=> $this->current_file,
						'arguments'		=> $arguments,
						'since'			=> $since,
						'description'	=> $description,
					);
					$num_events_found++;
				}
			}
		}

		return $num_events_found;
	}

	/**
	 * The version to check
	 *
	 * @param string $version
	 * @return bool
	 */
	protected function version_is_filtered($version)
	{
		return (!$this->min_version || phpbb_version_compare($this->min_version, $version, '<='))
			&& (!$this->max_version || phpbb_version_compare($this->max_version, $version, '>='));
	}

	/**
	* Find the name of the event inside the dispatch() line
	*
	* @param int $event_line
	* @param bool $is_dispatch Do we look for dispatch() or trigger_event() ?
	* @return string	Name of the event
	* @throws \LogicException
	*/
	public function get_event_name($event_line, $is_dispatch)
	{
		$event_text_line = $this->file_lines[$event_line];
		$event_text_line = ltrim($event_text_line, "\t ");

		if ($is_dispatch)
		{
			$regex = '#\$[a-z](?:[a-z0-9_]|->)*';
			$regex .= '->dispatch\((\[)?';
			$regex .= '\'' . $this->preg_match_event_name() . '(?(1)\', \'(?2))+\'';
			$regex .= '(?(1)\])\);#';
		}
		else
		{
			$regex = '#extract\(\$[a-z](?:[a-z0-9_]|->)*';
			$regex .= '->trigger_event\((\[)?';
			$regex .= '\'' . $this->preg_match_event_name() . '(?(1)\', \'(?2))+\'';
			$regex .= '(?(1)\]), compact\(\$vars\)\)\);#';
		}

		$match = array();
		preg_match($regex, $event_text_line, $match);
		if (!isset($match[2]))
		{
			throw new \LogicException("Can not find event name in line '{$event_text_line}' "
				. "in file '{$this->current_file}:{$event_line}'", 1);
		}

		return $match[2];
	}

	/**
	* Returns a regex match for the event name
	*
	* @return string
	*/
	protected function preg_match_event_name()
	{
		return '([a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+)';
	}

	/**
	* Find the $vars array
	*
	* @return array		List of variables
	* @throws \LogicException
	*/
	public function get_vars_from_array()
	{
		$line = ltrim($this->file_lines[$this->current_event_line - 1], "\t");
		if ($line === ');' || $line === '];')
		{
			$vars_array = $this->get_vars_from_multi_line_array();
		}
		else
		{
			$vars_array = $this->get_vars_from_single_line_array($line);
		}

		foreach ($vars_array as $var)
		{
			if (!preg_match('#^[a-z_][a-z0-9_]*$#i', $var))
			{
				throw new \LogicException("Found invalid var '{$var}' in array for event '{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 3);
			}
		}

		sort($vars_array);
		return $vars_array;
	}

	/**
	* Find the variables in single line array
	*
	* @param	string	$line
	* @param	bool	$throw_multiline	Throw an exception when there are too
	*										many arguments in one line.
	* @return array		List of variables
	* @throws \LogicException
	*/
	public function get_vars_from_single_line_array($line, $throw_multiline = true)
	{
		$match = array();
		preg_match('#^\$vars = (?:(\[)|array\()\'([a-z0-9_\' ,]+)\'(?(1)\]|\));$#i', $line, $match);

		if (isset($match[2]))
		{
			$vars_array = explode("', '", $match[2]);
			if ($throw_multiline && count($vars_array) > 6)
			{
				throw new \LogicException('Should use multiple lines for $vars definition '
					. "for event '{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 2);
			}
			return $vars_array;
		}
		else
		{
			throw new \LogicException("Can not find '\$vars = array();'-line for event '{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 1);
		}
	}

	/**
	* Find the variables in single line array
	*
	* @return array		List of variables
	* @throws \LogicException
	*/
	public function get_vars_from_multi_line_array()
	{
		$current_vars_line = 2;
		$var_lines = array();
		while (!in_array(ltrim($this->file_lines[$this->current_event_line - $current_vars_line], "\t"), ['$vars = array(', '$vars = [']))
		{
			$var_lines[] = substr(trim($this->file_lines[$this->current_event_line - $current_vars_line]), 0, -1);

			$current_vars_line++;
			if ($current_vars_line > $this->current_event_line)
			{
				// Reached the start of the file
				throw new \LogicException("Can not find end of \$vars array for event '{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 2);
			}
		}

		return $this->get_vars_from_single_line_array('$vars = array(' . implode(", ", $var_lines) . ');', false);
	}

	/**
	* Find the $vars array
	*
	* @return array		List of variables
	* @throws \LogicException
	*/
	public function get_vars_from_docblock()
	{
		$doc_vars = array();
		$current_doc_line = 1;
		$found_comment_end = false;
		while (ltrim($this->file_lines[$this->current_event_line - $current_doc_line], "\t") !== '/**')
		{
			if (ltrim($this->file_lines[$this->current_event_line - $current_doc_line], "\t ") === '*/')
			{
				$found_comment_end = true;
			}

			if ($found_comment_end)
			{
				$var_line = trim($this->file_lines[$this->current_event_line - $current_doc_line]);
				$var_line = preg_replace('!\s+!', ' ', $var_line);
				if (strpos($var_line, '* @var ') === 0)
				{
					$doc_line = explode(' ', $var_line, 5);
					if (count($doc_line) !== 5)
					{
						throw new \LogicException("Found invalid line '{$this->file_lines[$this->current_event_line - $current_doc_line]}' "
						. "for event '{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 1);
					}
					$doc_vars[] = $doc_line[3];
				}
			}

			$current_doc_line++;
			if ($current_doc_line > $this->current_event_line)
			{
				// Reached the start of the file
				throw new \LogicException("Can not find end of docblock for event '{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 2);
			}
		}

		if (empty($doc_vars))
		{
			// Reached the start of the file
			throw new \LogicException("Can not find @var lines for event '{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 3);
		}

		foreach ($doc_vars as $var)
		{
			if (!preg_match('#^[a-z_][a-z0-9_]*$#i', $var))
			{
				throw new \LogicException("Found invalid @var '{$var}' in docblock for event "
					. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 4);
			}
		}

		sort($doc_vars);
		return $doc_vars;
	}

	/**
	* Find the "@since" Information line
	*
	* @return int Absolute line number
	* @throws \LogicException
	*/
	public function find_since()
	{
		return $this->find_tag('since', array('event', 'var'));
	}

	/**
	* Find the "@changed" Information lines
	*
	* @param string $tag_name Should be 'change', not 'changed'
	* @return array Absolute line numbers
	* @throws \LogicException
	*/
	public function find_changed($tag_name)
	{
		$lines = array();
		$last_line = 0;
		try
		{
			while ($line = $this->find_tag($tag_name, array('since'), $last_line))
			{
				$lines[] = $line;
				$last_line = $line;
			}
		}
		catch (\LogicException $e)
		{
			// Not changed? No problem!
		}

		return $lines;
	}

	/**
	* Find the "@event" Information line
	*
	* @return int Absolute line number
	*/
	public function find_event()
	{
		return $this->find_tag('event', array());
	}

	/**
	* Find a "@*" Information line
	*
	* @param string $find_tag		Name of the tag we are trying to find
	* @param array $disallowed_tags		List of tags that must not appear between
	*									the tag and the actual event
	* @param int $skip_to_line		Skip lines until this one
	* @return int Absolute line number
	* @throws \LogicException
	*/
	public function find_tag($find_tag, $disallowed_tags, $skip_to_line = 0)
	{
		$find_tag_line = $skip_to_line ? $this->current_event_line - $skip_to_line + 1 : 0;
		$found_comment_end = ($skip_to_line) ? true : false;
		while (strpos(ltrim($this->file_lines[$this->current_event_line - $find_tag_line], "\t "), '* @' . $find_tag . ' ') !== 0)
		{
			if ($found_comment_end && ltrim($this->file_lines[$this->current_event_line - $find_tag_line], "\t") === '/**')
			{
				// Reached the start of this doc block
				throw new \LogicException("Can not find '@{$find_tag}' information for event "
					. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 1);
			}

			foreach ($disallowed_tags as $disallowed_tag)
			{
				if ($found_comment_end && strpos(ltrim($this->file_lines[$this->current_event_line - $find_tag_line], "\t "), '* @' . $disallowed_tag) === 0)
				{
					// Found @var after the @since
					throw new \LogicException("Found '@{$disallowed_tag}' information after '@{$find_tag}' for event "
						. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 3);
				}
			}

			if (ltrim($this->file_lines[$this->current_event_line - $find_tag_line], "\t ") === '*/')
			{
				$found_comment_end = true;
			}

			$find_tag_line++;
			if ($find_tag_line >= $this->current_event_line)
			{
				// Reached the start of the file
				throw new \LogicException("Can not find '@{$find_tag}' information for event "
					. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 2);
			}
		}

		return $this->current_event_line - $find_tag_line;
	}

	/**
	* Find a "@*" Information line
	*
	* @return int Absolute line number
	* @throws \LogicException
	*/
	public function find_description()
	{
		$find_desc_line = 0;
		while (ltrim($this->file_lines[$this->current_event_line - $find_desc_line], "\t") !== '/**')
		{
			$find_desc_line++;
			if ($find_desc_line > $this->current_event_line)
			{
				// Reached the start of the file
				throw new \LogicException("Can not find a description for event "
					. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 1);
			}
		}

		$find_desc_line = $this->current_event_line - $find_desc_line + 1;

		$desc = trim($this->file_lines[$find_desc_line]);
		if (strpos($desc, '* @') === 0 || $desc[0] !== '*' || substr($desc, 1) == '')
		{
			// First line of the doc block is a @-line, empty or only contains "*"
			throw new \LogicException("Can not find a description for event "
				. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 2);
		}

		return $find_desc_line;
	}

	/**
	* Validate "@since" Information
	*
	* @param string $line
	* @return string
	* @throws \LogicException
	*/
	public function validate_since($line)
	{
		$match = array();
		preg_match('#^\* @since (\d+\.\d+\.\d+(?:-(?:a|b|RC|pl)\d+)?)$#', ltrim($line, "\t "), $match);
		if (!isset($match[1]))
		{
			throw new \LogicException("Invalid '@since' information for event "
				. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'");
		}

		return $match[1];
	}

	/**
	* Validate "@changed" Information
	*
	* @param string $line
	* @return string
	* @throws \LogicException
	*/
	public function validate_changed($line)
	{
		$match = array();
		$line = str_replace("\t", ' ', ltrim($line, "\t "));
		preg_match('#^\* @changed (\d+\.\d+\.\d+(?:-(?:a|b|RC|pl)\d+)?)( (?:.*))?$#', $line, $match);
		if (!isset($match[2]))
		{
			throw new \LogicException("Invalid '@changed' information for event "
				. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'");
		}

		return $match[2];
	}

	/**
	* Validate "@event" Information
	*
	* @param string $event_name
	* @param string $line
	* @return string
	* @throws \LogicException
	*/
	public function validate_event($event_name, $line)
	{
		$event = substr(ltrim($line, "\t "), strlen('* @event '));

		if ($event !== trim($event))
		{
			throw new \LogicException("Invalid '@event' information for event "
				. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 1);
		}

		if ($event !== $event_name)
		{
			throw new \LogicException("Event name does not match '@event' tag for event "
				. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'", 2);
		}

		return $event;
	}

	/**
	* Validates that two arrays contain the same strings
	*
	* @param array $vars_array		Variables found in the array line
	* @param array $vars_docblock	Variables found in the doc block
	* @return null
	* @throws \LogicException
	*/
	public function validate_vars_docblock_array($vars_array, $vars_docblock)
	{
		$vars_array = array_unique($vars_array);
		$vars_docblock = array_unique($vars_docblock);
		$sizeof_vars_array = count($vars_array);

		if ($sizeof_vars_array !== count($vars_docblock) || $sizeof_vars_array !== count(array_intersect($vars_array, $vars_docblock)))
		{
			throw new \LogicException("\$vars array does not match the list of '@var' tags for event "
				. "'{$this->current_event}' in file '{$this->current_file}:{$this->current_event_line}'");
		}
	}
}
