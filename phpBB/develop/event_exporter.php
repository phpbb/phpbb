<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class event_exporter
{
	/** @var string */
	protected $root_path;

	/**
	* @param string $phpbb_root_path
	*/
	public function __construct($phpbb_root_path)
	{
		$this->root_path = $phpbb_root_path;
	}

	function export_from_eventsmd($filter)
	{
		$file_content = file_get_contents($this->root_path . 'docs/events.md');

		$events = explode("\n\n", $file_content);
		foreach ($events as $event)
		{
			// Last row of the file
			if (strpos($event, "\n===\n") === false) continue;

			list($event_name, $details) = explode("\n===\n", $event);

			if ($filter == 'acp' && strpos($event_name, 'acp_') !== 0) continue;
			if ($filter == 'styles' && strpos($event_name, 'acp_') === 0) continue;

			list($file_details, $details) = explode("\n* Since: ", $details);
			list($version, $explanition) = explode("\n* Purpose: ", $details);

			echo "|- id=\"{$event_name}\"\n";
			echo "| [[#{$event_name}|{$event_name}]] || ";

			if (strpos($file_details, "* Locations:\n    + ") === 0)
			{
				$file_details = substr($file_details, strlen("* Locations:\n    + "));
				$files = explode("\n    + ", $file_details);
				$prosilver = $subsilver2 = $adm = array();
				foreach ($files as $file)
				{
					if (strpos($file, 'styles/prosilver/template/') === 0)
					{
						$prosilver[] = substr($file, strlen('styles/prosilver/template/'));
					}
					if (strpos($file, 'styles/subsilver2/template/') === 0)
					{
						$subsilver2[] = substr($file, strlen('styles/subsilver2/template/'));
					}
					if (strpos($file, 'adm/style/') === 0)
					{
						$adm[] = substr($file, strlen('adm/style/'));
					}
				}
				if ($filter == 'acp')
				{
					echo implode(', ', $adm);
				}
				else
				{
					echo implode(', ', $prosilver) . ' || ' . implode(', ', $subsilver2);
				}
			}
			else if ($filter == 'acp')
			{
				echo substr($file_details, strlen("* Location: adm/style/"));
			}
			echo " || {$version} || " . str_replace("\n", ' ', $explanition) . "\n";

		}
	}

	function export_from_php()
	{
		$files = $this->get_file_list($this->root_path);
		$events = array();
		foreach ($files as $file)
		{
			$file_events = $this->check_for_events($file);
			if (!empty($file_events))
			{
				$events = array_merge($events, $file_events);
			}
		}
		ksort($events);

		foreach ($events as $event)
		{
			echo '|- id="' . $event['event'] . '"' . "\n";
			echo '| [[#' . $event['event'] . '|' . $event['event'] . ']] || ' . $event['file'] . ' || ' . implode(', ', $event['arguments']) . ' || ' . $event['since'] . ' || ' . $event['description'] . "\n";
		}
	}

	public function check_for_events($file)
	{
		$events = array();
		$content = file_get_contents($this->root_path . $file);

		if (strpos($content, "dispatcher->trigger_event('") || strpos($content, "dispatcher->dispatch('"))
		{
			$lines = explode("\n", $content);
			for ($i = 0, $num_lines = sizeof($lines); $i < $num_lines; $i++)
			{
				$event_line = false;
				$found_trigger_event = strpos($lines[$i], "dispatcher->trigger_event('");
				if ($found_trigger_event !== false)
				{
					$event_line = $i;
					$event_name = $this->get_trigger_event_name($file, $lines[$event_line]);

					// Find variables of the event
					$arguments = $this->get_vars_from_array($file, $event_name, $lines, $event_line);
					$doc_vars = $this->get_vars_from_docblock($file, $event_name, $lines, $event_line);
					$this->validate_vars_docblock_array($file, $event_name, $arguments, $doc_vars);
				}
				else
				{
					$found_dispatch = strpos($lines[$i], "dispatcher->dispatch('");
					if ($found_dispatch !== false)
					{
						$event_line = $i;
						$event_name = $this->get_dispatch_name($file, $lines[$event_line]);
						$arguments = array();
					}
				}

				if ($event_line)
				{
					// Validate @event
					$event_line_num = $this->find_event($file, $event_name, $lines, $event_line);
					$this->validate_event($file, $event_name, $lines[$event_line_num]);

					// Validate @since
					$since_line_num = $this->find_since($file, $event_name, $lines, $event_line);
					$since = $this->validate_since($file, $event_name, $lines[$since_line_num]);

					// Find event description line
					$description_line_num = $this->find_description($file, $event_name, $lines, $event_line);
					$description = substr(trim($lines[$description_line_num]), strlen('* '));

					$events[$event_name] = array(
						'event'			=> $event_name,
						'file'			=> $file,
						'arguments'		=> $arguments,
						'since'			=> $since,
						'description'	=> $description,
					);
				}
			}
		}

		return $events;
	}

	/**
	* Find the name of the event inside the dispatch() line
	*
	* @param string $file
	* @param string $event_line
	* @return int Absolute line number
	*/
	public function get_dispatch_name($file, $event_line)
	{
		$event_line = ltrim($event_line, "\t");

		$regex = '#\$([a-z](?:[a-z0-9_]|->)*)';
		$regex .= '->dispatch\(';
		$regex .= '\'' . $this->preg_match_event_name() . '\'';
		$regex .= '\);#';

		$match = array();
		preg_match($regex, $event_line, $match);
		if (!isset($match[2]))
		{
			throw new LogicException('Can not find event name in line "' . $event_line . '" in file "' . $file . '"', 1);
		}

		return $match[2];
	}

	/**
	* Find the name of the event inside the trigger_event() line
	*
	* @param string $file
	* @param string $event_line
	* @return int Absolute line number
	*/
	public function get_trigger_event_name($file, $event_line)
	{
		$event_line = ltrim($event_line, "\t");

		$regex = '#extract\(\$([a-z](?:[a-z0-9_]|->)*)';
		$regex .= '->trigger_event\(';
		$regex .= '\'' . $this->preg_match_event_name() . '\'';
		$regex .= ', compact\(\$vars\)\)\);#';

		$match = array();
		preg_match($regex, $event_line, $match);
		if (!isset($match[2]))
		{
			throw new LogicException('Can not find event name in line "' . $event_line . '" in file "' . $file . '"', 1);
		}

		return $match[2];
	}

	/**
	* Find the name of the event inside the trigger_event() line
	*
	* @return string Returns a regex match for the event name
	*/
	protected function preg_match_event_name()
	{
		return '([a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+)';
	}

	/**
	* Find the $vars array
	*
	* @param string $file
	* @param string $event_name
	* @param array $lines
	* @param int $event_line		Index of the event call in $lines
	* @return array		List of variables
	*/
	public function get_vars_from_array($file, $event_name, $lines, $event_line)
	{
		$vars_line = ltrim($lines[$event_line - 1], "\t");
		if (strpos($vars_line, "\$vars = array('") !== 0 || substr($vars_line, -3) !== '\');')
		{
			throw new LogicException('Can not find "$vars = array();"-line for event "' . $event_name . '" in file "' . $file . '"', 1);
		}

		$vars_array = substr($vars_line, strlen("\$vars = array('"), 0 - strlen('\');'));
		if ($vars_array === '')
		{
			throw new LogicException('Found empty $vars array for event "' . $event_name . '" in file "' . $file . '"', 2);
		}

		$vars_array = explode("', '", $vars_array);

		foreach ($vars_array as $var)
		{
			if (!preg_match('#^([a-zA-Z_][a-zA-Z0-9_]*)$#', $var))
			{
				throw new LogicException('Found invalid var "' . $var . '" in array for event "' . $event_name . '" in file "' . $file . '"', 3);
			}
		}

		sort($vars_array);
		return $vars_array;
	}

	/**
	* Find the $vars array
	*
	* @param string $file
	* @param string $event_name
	* @param array $lines
	* @param int $event_line		Index of the event call in $lines
	* @return array		List of variables
	*/
	public function get_vars_from_docblock($file, $event_name, $lines, $event_line)
	{
		$doc_vars = array();
		$current_doc_line = 1;
		$found_comment_end = false;
		while (ltrim($lines[$event_line - $current_doc_line], "\t") !== '/**')
		{
			if (ltrim($lines[$event_line - $current_doc_line], "\t") === '*/')
			{
				$found_comment_end = true;
			}

			if ($found_comment_end)
			{
				$var_line = trim($lines[$event_line - $current_doc_line]);
				$var_line = preg_replace('!\s+!', ' ', $var_line);
				if (strpos($var_line, '* @var ') === 0)
				{
					$doc_line = explode(' ', $var_line, 5);
					if (sizeof($doc_line) !== 5)
					{
						throw new LogicException('Found invalid line "' . $lines[$event_line - $current_doc_line]
							. '" for event "' . $event_name . '" in file "' . $file . '"', 1);
					}
					$doc_vars[] = $doc_line[3];
				}
			}

			$current_doc_line++;
			if ($current_doc_line > $event_line)
			{
				// Reached the start of the file
				throw new LogicException('Can not find end of docblock for event "' . $event_name . '" in file "' . $file . '"', 2);
			}
		}

		if (empty($doc_vars))
		{
			// Reached the start of the file
			throw new LogicException('Can not find @var lines for event "' . $event_name . '" in file "' . $file . '"', 3);
		}

		foreach ($doc_vars as $var)
		{
			if (!preg_match('#^([a-zA-Z_][a-zA-Z0-9_]*)$#', $var))
			{
				throw new LogicException('Found invalid @var "' . $var . '" in docblock for event "' . $event_name . '" in file "' . $file . '"', 4);
			}
		}

		sort($doc_vars);
		return $doc_vars;
	}

	/**
	* Find the "@since" Information line
	*
	* @param string $file
	* @param string $event_name
	* @param array $lines
	* @param int $event_line		Index of the event call in $lines
	* @return int Absolute line number
	*/
	public function find_since($file, $event_name, $lines, $event_line)
	{
		return $this->find_tag($file, $event_name, $lines, $event_line, 'since', array('event', 'var'));
	}

	/**
	* Find the "@event" Information line
	*
	* @param string $file
	* @param string $event_name
	* @param array $lines
	* @param int $event_line		Index of the event call in $lines
	* @return int Absolute line number
	*/
	public function find_event($file, $event_name, $lines, $event_line)
	{
		return $this->find_tag($file, $event_name, $lines, $event_line, 'event', array());
	}

	/**
	* Find a "@*" Information line
	*
	* @param string $file
	* @param string $event_name
	* @param array $lines
	* @param int $event_line		Index of the event call in $lines
	* @param string $find_tag		Name of the tag we are trying to find
	* @param array $disallowed_tags		List of tags that must not appear between
	*									the tag and the actual event
	* @return int Absolute line number
	*/
	public function find_tag($file, $event_name, $lines, $event_line, $find_tag, $disallowed_tags)
	{
		$find_tag_line = 0;
		$found_comment_end = false;
		while (strpos(ltrim($lines[$event_line - $find_tag_line], "\t"), '* @' . $find_tag . ' ') !== 0)
		{
			if ($found_comment_end && ltrim($lines[$event_line - $find_tag_line], "\t") === '/**')
			{
				// Reached the start of this doc block
				throw new LogicException('Can not find @' . $find_tag . ' information for event "' . $event_name . '" in file "' . $file . '"', 1);
			}

			foreach ($disallowed_tags as $disallowed_tag)
			{
				if ($found_comment_end && strpos(ltrim($lines[$event_line - $find_tag_line], "\t"), '* @' . $disallowed_tag) === 0)
				{
					// Found @var after the @since
					throw new LogicException('Found @' . $disallowed_tag . ' information after @' . $find_tag . ' for event "' . $event_name . '" in file "' . $file . '"', 3);
				}
			}

			if (ltrim($lines[$event_line - $find_tag_line], "\t") === '*/')
			{
				$found_comment_end = true;
			}

			$find_tag_line++;
			if ($find_tag_line >= $event_line)
			{
				// Reached the start of the file
				throw new LogicException('Can not find @' . $find_tag . ' information for event "' . $event_name . '" in file "' . $file . '"', 2);
			}
		}

		return $event_line - $find_tag_line;
	}

	/**
	* Find a "@*" Information line
	*
	* @param string $file
	* @param string $event_name
	* @param array $lines
	* @param int $event_line		Index of the event call in $lines
	* @return int Absolute line number
	*/
	public function find_description($file, $event_name, $lines, $event_line)
	{
		$find_desc_line = 0;
		while (ltrim($lines[$event_line - $find_desc_line], "\t") !== '/**')
		{
			$find_desc_line++;
			if ($find_desc_line > $event_line)
			{
				// Reached the start of the file
				throw new LogicException('Can not find a description for event "' . $event_name . '" in file "' . $file . '"', 1);
			}
		}

		$find_desc_line = $event_line - $find_desc_line + 1;

		$desc = trim($lines[$find_desc_line]);
		if (strpos($desc, '* @') === 0 || $desc[0] !== '*' || substr($desc, 1) == '')
		{
			// First line of the doc block is a @-line, empty or only contains "*"
			throw new LogicException('Can not find a description for event "' . $event_name . '" in file "' . $file . '"', 2);
		}

		return $find_desc_line;
	}

	/**
	* Validate "@since" Information
	*
	* @param string $file
	* @param string $event_name
	* @param string $line
	* @return string
	*/
	public function validate_since($file, $event_name, $line)
	{
		$since = substr(ltrim($line, "\t"), strlen('* @since '));

		if ($since !== trim($since))
		{
			throw new LogicException('Invalid @since information for event "' . $event_name . '" in file "' . $file . '"', 1);
		}

		$since = ($since === '3.1-A1') ? '3.1.0-a1' : $since;

		if (!preg_match('#^\d+\.\d+\.\d+(?:-(?:a|b|rc|pl)\d+)?$#', $since))
		{
			throw new LogicException('Invalid @since information for event "' . $event_name . '" in file "' . $file . '"', 2);
		}

		return $since;
	}

	/**
	* Validate "@event" Information
	*
	* @param string $file
	* @param string $event_name
	* @param string $line
	* @return string
	*/
	public function validate_event($file, $event_name, $line)
	{
		$event = substr(ltrim($line, "\t"), strlen('* @event '));

		if ($event !== trim($event))
		{
			throw new LogicException('Invalid @event information for event "' . $event_name . '" in file "' . $file . '"', 1);
		}

		if ($event !== $event_name)
		{
			throw new LogicException('Event name does not match @event tag for event "' . $event_name . '" in file "' . $file . '"', 2);
		}

		return $event;
	}

	/**
	* Validates that two arrays contain the same strings
	*
	* @param string $file
	* @param string $event_name
	* @param array $vars_array		Variables found in the array line
	* @param array $vars_docblock	Variables found in the doc block
	* @return null
	*/
	public function validate_vars_docblock_array($file, $event_name, $vars_array, $vars_docblock)
	{
		$vars_array = array_unique($vars_array);
		$vars_docblock = array_unique($vars_docblock);
		$sizeof_vars_array = sizeof($vars_array);

		if ($sizeof_vars_array !== sizeof($vars_docblock) || $sizeof_vars_array !== sizeof(array_intersect($vars_array, $vars_docblock)))
		{
			throw new LogicException('$vars array does not match the list of @var tags for event "' . $event_name . '" in file "' . $file . '"');
		}
	}

	/**
	 * Returns a list of files in that directory
	 *
	 * Works recursive with any depth
	 *
	 * @param	string	$dir	Directory to go through
	 * @return	array	List of files (including directories from within $dir
	 */
	function get_file_list($dir, $path = '')
	{
		try
		{
			$iterator = new \DirectoryIterator($dir);
		}
		catch (Exception $e)
		{
			return array();
		}

		$files = array();
		foreach ($iterator as $file_info)
		{
			if ($file_info->isDot())
			{
				continue;
			}

			// Do not scan some directories
			if ($file_info->isDir() && (
					($path == '' && in_array($file_info->getFilename(), array('cache', 'develop', 'ext', 'files', 'language', 'store', 'vendor')))
					|| ($path == '/includes' && in_array($file_info->getFilename(), array('utf')))
					|| ($path == '/phpbb/db/migration' && in_array($file_info->getFilename(), array('data')))
					|| ($path == '/phpbb' && in_array($file_info->getFilename(), array('event')))
				))
			{
				continue;
			}
			else if ($file_info->isDir())
			{
				$sub_dir = $this->get_file_list($file_info->getPath() . '/' . $file_info->getFilename(), $path . '/' . $file_info->getFilename());
				foreach ($sub_dir as $file)
				{
					$files[] = $file_info->getFilename() . '/' . $file;
				}
			}
			else if (substr($file_info->getFilename(), -4) == '.php')
			{
				$files[] = $file_info->getFilename();
			}
		}

		return $files;
	}
}
