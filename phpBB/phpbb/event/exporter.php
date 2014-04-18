<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\event;

class exporter
{
	/** @var string */
	protected $root_path;

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
	*/
	public function __construct($phpbb_root_path)
	{
		$this->root_path = $phpbb_root_path;
		$this->events = $this->file_lines = array();
		$this->events['php'] = array();
		$this->current_file = $this->current_event = '';
		$this->current_event_line = 0;
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

	public function get_events()
	{
		return $this->events;
	}

	public function set_current_event($name, $line)
	{
		$this->current_event = $name;
		$this->current_event_line = $line;
	}

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
		$files = $this->get_recursive_file_list($this->root_path);
		$this->events['php'] = array();
		foreach ($files as $file)
		{
			$this->crawl_php_file($file);
		}
		ksort($this->events['php']);

		return sizeof($this->events['php']);
	}

	/**
	* Returns a list of files in $dir
	*
	* Works recursive with any depth
	*
	* @param	string	$dir	Directory to go through
	* @param	string	$path	Path from root to $dir
	* @return	array	List of files (including directories)
	*/
	public function get_recursive_file_list($dir, $path = '')
	{
		try
		{
			$iterator = new \DirectoryIterator($dir);
		}
		catch (\Exception $e)
		{
			return array();
		}

		$files = array();
		foreach ($iterator as $file_info)
		{
			/** @var \DirectoryIterator $file_info */
			if ($file_info->isDot())
			{
				continue;
			}

			// Do not scan some directories
			if ($file_info->isDir() && (
					($path == '' && in_array($file_info->getFilename(), array(
						'cache',
						'develop',
						'ext',
						'files',
						'language',
						'store',
						'vendor',
					)))
					|| ($path == '/includes' && in_array($file_info->getFilename(), array('utf')))
					|| ($path == '/phpbb/db/migration' && in_array($file_info->getFilename(), array('data')))
					|| ($path == '/phpbb' && in_array($file_info->getFilename(), array('event')))
				))
			{
				continue;
			}
			else if ($file_info->isDir())
			{
				$sub_dir = $this->get_recursive_file_list($file_info->getPath() . '/' . $file_info->getFilename(), $path . '/' . $file_info->getFilename());
				foreach ($sub_dir as $file)
				{
					$files[] = $file_info->getFilename() . '/' . $file;
				}
			}
			else if ($file_info->getExtension() == 'php')
			{
				$files[] = $file_info->getFilename();
			}
		}

		return $files;
	}

	/**
	* Format the php events as a wiki table
	* @return string
	*/
	public function export_php_events_for_wiki()
	{
		$wiki_page = '';
		foreach ($this->events['php'] as $event)
		{
			$wiki_page .= '|- id="' . $event['event'] . '"' . "\n";
			$wiki_page .= '| [[#' . $event['event'] . '|' . $event['event'] . ']] || ' . $event['file'] . ' || ' . implode(', ', $event['arguments']) . ' || ' . $event['since'] . ' || ' . $event['description'] . "\n";
		}

		return $wiki_page;
	}

	/**
	* @param $file
	* @throws \LogicException
	*/
	public function crawl_php_file($file)
	{
		$this->current_file = $file;
		$this->file_lines = array();
		$content = file_get_contents($this->root_path . $this->current_file);

		if (strpos($content, "dispatcher->trigger_event('") || strpos($content, "dispatcher->dispatch('"))
		{
			$this->set_content(explode("\n", $content));
			for ($i = 0, $num_lines = sizeof($this->file_lines); $i < $num_lines; $i++)
			{
				$event_line = false;
				$found_trigger_event = strpos($this->file_lines[$i], "dispatcher->trigger_event('");
				$arguments = array();
				if ($found_trigger_event !== false)
				{
					$event_line = $i;
					$this->set_current_event($this->get_trigger_event_name($this->file_lines[$event_line]), $event_line);

					// Find variables of the event
					$arguments = $this->get_vars_from_array();
					$doc_vars = $this->get_vars_from_docblock();
					$this->validate_vars_docblock_array($arguments, $doc_vars);
				}
				else
				{
					$found_dispatch = strpos($this->file_lines[$i], "dispatcher->dispatch('");
					if ($found_dispatch !== false)
					{
						$event_line = $i;
						$this->set_current_event($this->get_dispatch_name($this->file_lines[$event_line]), $event_line);
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

					// Find event description line
					$description_line_num = $this->find_description();
					$description = substr(trim($this->file_lines[$description_line_num]), strlen('* '));

					if (isset($this->events['php'][$this->current_event]))
					{
						throw new \LogicException('The event "' . $this->current_event . '" from file "' . $this->current_file
							. '" already exists in file "'. $this->events['php'][$this->current_event]['file'] . '"', 10);
					}

					$this->events['php'][$this->current_event] = array(
						'event'			=> $this->current_event,
						'file'			=> $this->current_file,
						'arguments'		=> $arguments,
						'since'			=> $since,
						'description'	=> $description,
					);
				}
			}
		}
	}

	/**
	* Find the name of the event inside the dispatch() line
	*
	* @param string $event_line
	* @return int Absolute line number
	* @throws \LogicException
	*/
	public function get_dispatch_name($event_line)
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
			throw new \LogicException('Can not find event name in line "' . $event_line . '" in file "' . $this->current_file . '"', 1);
		}

		return $match[2];
	}

	/**
	* Find the name of the event inside the trigger_event() line
	*
	* @param string $event_line
	* @return int Absolute line number
	* @throws \LogicException
	*/
	public function get_trigger_event_name($event_line)
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
			throw new \LogicException('Can not find event name in line "' . $event_line . '" in file "' . $this->current_file . '"', 1);
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
	* @return array		List of variables
	* @throws \LogicException
	*/
	public function get_vars_from_array()
	{
		$vars_line = ltrim($this->file_lines[$this->current_event_line - 1], "\t");
		if (strpos($vars_line, "\$vars = array('") !== 0 || substr($vars_line, -3) !== '\');')
		{
			throw new \LogicException('Can not find "$vars = array();"-line for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 1);
		}

		$vars_array = substr($vars_line, strlen("\$vars = array('"), 0 - strlen('\');'));
		if ($vars_array === '')
		{
			throw new \LogicException('Found empty $vars array for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 2);
		}

		$vars_array = explode("', '", $vars_array);

		foreach ($vars_array as $var)
		{
			if (!preg_match('#^([a-zA-Z_][a-zA-Z0-9_]*)$#', $var))
			{
				throw new \LogicException('Found invalid var "' . $var . '" in array for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 3);
			}
		}

		sort($vars_array);
		return $vars_array;
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
			if (ltrim($this->file_lines[$this->current_event_line - $current_doc_line], "\t") === '*/')
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
					if (sizeof($doc_line) !== 5)
					{
						throw new \LogicException('Found invalid line "' . $this->file_lines[$this->current_event_line - $current_doc_line]
							. '" for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 1);
					}
					$doc_vars[] = $doc_line[3];
				}
			}

			$current_doc_line++;
			if ($current_doc_line > $this->current_event_line)
			{
				// Reached the start of the file
				throw new \LogicException('Can not find end of docblock for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 2);
			}
		}

		if (empty($doc_vars))
		{
			// Reached the start of the file
			throw new \LogicException('Can not find @var lines for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 3);
		}

		foreach ($doc_vars as $var)
		{
			if (!preg_match('#^([a-zA-Z_][a-zA-Z0-9_]*)$#', $var))
			{
				throw new \LogicException('Found invalid @var "' . $var . '" in docblock for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 4);
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
	* @return int Absolute line number
	* @throws \LogicException
	*/
	public function find_tag($find_tag, $disallowed_tags)
	{
		$find_tag_line = 0;
		$found_comment_end = false;
		while (strpos(ltrim($this->file_lines[$this->current_event_line - $find_tag_line], "\t"), '* @' . $find_tag . ' ') !== 0)
		{
			if ($found_comment_end && ltrim($this->file_lines[$this->current_event_line - $find_tag_line], "\t") === '/**')
			{
				// Reached the start of this doc block
				throw new \LogicException('Can not find @' . $find_tag . ' information for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 1);
			}

			foreach ($disallowed_tags as $disallowed_tag)
			{
				if ($found_comment_end && strpos(ltrim($this->file_lines[$this->current_event_line - $find_tag_line], "\t"), '* @' . $disallowed_tag) === 0)
				{
					// Found @var after the @since
					throw new \LogicException('Found @' . $disallowed_tag . ' information after @' . $find_tag . ' for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 3);
				}
			}

			if (ltrim($this->file_lines[$this->current_event_line - $find_tag_line], "\t") === '*/')
			{
				$found_comment_end = true;
			}

			$find_tag_line++;
			if ($find_tag_line >= $this->current_event_line)
			{
				// Reached the start of the file
				throw new \LogicException('Can not find @' . $find_tag . ' information for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 2);
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
				throw new \LogicException('Can not find a description for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 1);
			}
		}

		$find_desc_line = $this->current_event_line - $find_desc_line + 1;

		$desc = trim($this->file_lines[$find_desc_line]);
		if (strpos($desc, '* @') === 0 || $desc[0] !== '*' || substr($desc, 1) == '')
		{
			// First line of the doc block is a @-line, empty or only contains "*"
			throw new \LogicException('Can not find a description for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 2);
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
		$since = substr(ltrim($line, "\t"), strlen('* @since '));

		if ($since !== trim($since))
		{
			throw new \LogicException('Invalid @since information for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 1);
		}

		$since = ($since === '3.1-A1') ? '3.1.0-a1' : $since;

		if (!preg_match('#^\d+\.\d+\.\d+(?:-(?:a|b|rc|pl)\d+)?$#', $since))
		{
			throw new \LogicException('Invalid @since information for event "' . $this->current_event . '" in file "' . $this->current_file . '"', 2);
		}

		return $since;
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
		$event = substr(ltrim($line, "\t"), strlen('* @event '));

		if ($event !== trim($event))
		{
			throw new \LogicException('Invalid @event information for event "' . $event_name . '" in file "' . $this->current_file . '"', 1);
		}

		if ($event !== $event_name)
		{
			throw new \LogicException('Event name does not match @event tag for event "' . $event_name . '" in file "' . $this->current_file . '"', 2);
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
		$sizeof_vars_array = sizeof($vars_array);

		if ($sizeof_vars_array !== sizeof($vars_docblock) || $sizeof_vars_array !== sizeof(array_intersect($vars_array, $vars_docblock)))
		{
			throw new \LogicException('$vars array does not match the list of @var tags for event "' . $this->current_event . '" in file "' . $this->current_file . '"');
		}
	}
}
