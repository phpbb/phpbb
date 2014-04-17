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

		if (strpos($content, "phpbb_dispatcher->trigger_event('") || strpos($content, "phpbb_dispatcher->dispatch('"))
		{
			$lines = explode("\n", $content);
			for ($i = 0, $num_lines = sizeof($lines); $i < $num_lines; $i++)
			{
				$event_line = 0;
				$found_trigger_event = strpos($lines[$i], "phpbb_dispatcher->trigger_event('");
				if ($found_trigger_event !== false)
				{
					$event_line = $i;
					$event_name = $lines[$event_line];
					$event_name = substr($event_name, $found_trigger_event + strlen("phpbb_dispatcher->trigger_event('"));
					$event_name = substr($event_name, 0, strpos($event_name, "'"));

					$current_line = trim($lines[$event_line]);
					$arguments = array();
					$found_inline_array = strpos($current_line, "', compact(array('");
					if ($found_inline_array !== false)
					{
						$varsarray = substr($current_line, $found_inline_array + strlen("', compact(array('"), -6);
						$arguments = explode("', '", $varsarray);
					}

					if (empty($arguments))
					{
						// Find $vars array lines
						$find_varsarray_line = 1;
						while (strpos($lines[$event_line - $find_varsarray_line], "\$vars = array('") === false)
						{
							$find_varsarray_line++;

							if ($find_varsarray_line > min(50, $event_line))
							{
								throw new LogicException('Can not find "$vars = array()"-line for event "' . $event_name . '" in file "' . $file . '"');
							}
						}
						$varsarray = substr(trim($lines[$event_line - $find_varsarray_line]), strlen("\$vars = array('"), -3);
						$arguments = explode("', '", $varsarray);
					}

					// Validate $vars array with @var
					$find_vars_line = 3;
					$doc_vars = array();
					while (strpos(trim($lines[$event_line - $find_vars_line]), '*') === 0)
					{
						$var_line = trim($lines[$event_line - $find_vars_line]);
						$var_line = preg_replace('!\s+!', ' ', $var_line);
						if (strpos($var_line, '* @var ') === 0)
						{
							$doc_line = explode(' ', $var_line);
							if (isset($doc_line[3]))
							{
								$doc_vars[] = $doc_line[3];
							}
						}
						$find_vars_line++;
					}
					if (sizeof($arguments) !== sizeof($doc_vars) && array_intersect($arguments, $doc_vars))
					{
						throw new LogicException('$vars array does not match the list of @var tags for event "' . $event_name . '" in file "' . $file . '"');
					}
				}
				$found_dispatch = strpos($lines[$i], "phpbb_dispatcher->dispatch('");
				if ($found_dispatch !== false)
				{
					$event_line = $i;
					$event_name = $lines[$event_line];
					$event_name = substr($event_name, $found_dispatch + strlen("phpbb_dispatcher->dispatch('"));
					$event_name = substr($event_name, 0, strpos($event_name, "'"));
					$arguments = array();
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
	* Find the "@since" Information line
	*
	* @param string $file
	* @param string $event_name
	* @param string $lines
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
	* @param string $lines
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
	* @param string $lines
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
	* @param string $lines
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
