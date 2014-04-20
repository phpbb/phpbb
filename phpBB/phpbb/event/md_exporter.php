<?php
/**
*
* @package phpBB3
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace phpbb\event;

/**
* Class md_exporter
* Crawls through a markdown file and grabs all events
*
* @package phpbb\event
*/
class md_exporter
{
	/** @var string */
	protected $root_path;

	/** @var string */
	protected $filter;

	/** @var string */
	protected $current_event;

	/** @var array */
	protected $events;

	/**
	* @param string $phpbb_root_path
	*/
	public function __construct($phpbb_root_path)
	{
		$this->root_path = $phpbb_root_path;
		$this->events = array();
		$this->events_by_file = array();
		$this->filter = $this->current_event = '';
	}

	public function get_events()
	{
		return $this->events;
	}

	/**
	* @param string $md_file
	* @param string $filter
	* @return int		Number of events found
	* @throws \LogicException
	*/
	public function crawl_phpbb_directory_adm($md_file)
	{
		$this->crawl_eventsmd($md_file, 'adm');

		$file_list = $this->get_recursive_file_list($this->root_path . 'adm/style/', 'adm/style/');
		foreach ($file_list as $file)
		{
			$file_name = 'adm/style/' . $file;
			$this->validate_events_from_file($file_name, $this->crawl_file_for_events($file_name));
		}

		return sizeof($this->events);
	}

	/**
	* @param string $md_file
	* @param string $filter
	* @return int		Number of events found
	* @throws \LogicException
	*/
	public function crawl_phpbb_directory_styles($md_file)
	{
		$this->crawl_eventsmd($md_file, 'styles');

		$styles = array('prosilver', 'subsilver2');
		foreach ($styles as $style)
		{
			$file_list = $this->get_recursive_file_list(
				$this->root_path . 'styles/' . $style . '/template/',
				'styles/' . $style . '/template/'
			);

			foreach ($file_list as $file)
			{
				$file_name = 'styles/' . $style . '/template/' . $file;
				$this->validate_events_from_file($file_name, $this->crawl_file_for_events($file_name));
			}
		}

		return sizeof($this->events);
	}

	/**
	* @param string $md_file
	* @param string $filter
	* @return int		Number of events found
	* @throws \LogicException
	*/
	public function crawl_eventsmd($md_file, $filter)
	{
		$file_content = file_get_contents($this->root_path . $md_file);
		$this->filter = $filter;

		$events = explode("\n\n", $file_content);
		foreach ($events as $event)
		{
			// Last row of the file
			if (strpos($event, "\n===\n") === false)
			{
				continue;
			}

			list($event_name, $details) = explode("\n===\n", $event, 2);
			$this->validate_event_name($event_name);
			$this->current_event = $event_name;

			if (isset($this->events[$this->current_event]))
			{
				throw new \LogicException("The event '{$this->current_event}' is defined multiple times");
			}

			if (($this->filter == 'adm' && strpos($this->current_event, 'acp_') !== 0)
				|| ($this->filter == 'styles' && strpos($this->current_event, 'acp_') === 0))
			{
				continue;
			}

			list($file_details, $details) = explode("\n* Since: ", $details, 2);
			list($since, $description) = explode("\n* Purpose: ", $details, 2);

			$files = $this->validate_file_list($file_details);
			$since = $this->validate_since($since);

			$this->events[$event_name] = array(
				'event'			=> $this->current_event,
				'files'			=> $files,
				'since'			=> $since,
				'description'	=> $description,
			);
		}

		return sizeof($this->events);
	}

	/**
	* Format the php events as a wiki table
	* @return string		Number of events found
	*/
	public function export_events_for_wiki()
	{
		if ($this->filter === 'acp')
		{
			$wiki_page = '= ACP Template Events =' . "\n";
			$wiki_page .= '{| class="zebra sortable" cellspacing="0" cellpadding="5"' . "\n";
			$wiki_page .= '! Identifier !! Placement !! Added in Release !! Explanation' . "\n";
		}
		else
		{
			$wiki_page = '= Template Events =' . "\n";
			$wiki_page .= '{| class="zebra sortable" cellspacing="0" cellpadding="5"' . "\n";
			$wiki_page .= '! Identifier !! Prosilver Placement (If applicable) !! Subsilver Placement (If applicable) !! Added in Release !! Explanation' . "\n";
		}

		foreach ($this->events as $event_name => $event)
		{
			$wiki_page .= "|- id=\"{$event_name}\"\n";
			$wiki_page .= "| [[#{$event_name}|{$event_name}]] || ";

			if ($this->filter === 'adm')
			{
				$wiki_page .= implode(', ', $event['files']['adm']);
			}
			else
			{
				$wiki_page .= implode(', ', $event['files']['prosilver']) . ' || ' . implode(', ', $event['files']['subsilver2']);
			}

			$wiki_page .= " || {$event['since']} || " . str_replace("\n", ' ', $event['description']) . "\n";
		}
		$wiki_page .= '|}' . "\n";

		return $wiki_page;
	}

	/**
	* Validates a template event name
	*
	* @param $event_name
	* @return null
	* @throws \LogicException
	*/
	public function validate_event_name($event_name)
	{
		if (!preg_match('#^([a-z][a-z0-9]*(?:_[a-z][a-z0-9]*)+)$#', $event_name))
		{
			throw new \LogicException("Invalid event name '{$event_name}'");
		}
	}

	/**
	* Validate "Since" Information
	*
	* @param string $since
	* @return string
	* @throws \LogicException
	*/
	public function validate_since($since)
	{
		$since = ($since === '3.1-A1') ? '3.1.0-a1' : $since;

		if (!preg_match('#^\d+\.\d+\.\d+(?:-(?:a|b|rc|pl)\d+)?$#', $since))
		{
			throw new \LogicException("Invalid since information found for event '{$this->current_event}'");
		}

		return $since;
	}

	/**
	* Validate the files list
	*
	* @param string $file_details
	* @return array
	* @throws \LogicException
	*/
	public function validate_file_list($file_details)
	{
		$files_list = array(
			'prosilver'		=> array(),
			'subsilver2'	=> array(),
			'adm'			=> array(),
		);

		// Multi file list
		if (strpos($file_details, "* Locations:\n    + ") === 0)
		{
			$file_details = substr($file_details, strlen("* Locations:\n    + "));
			$files = explode("\n    + ", $file_details);
			foreach ($files as $file)
			{
				if (!file_exists($this->root_path . $file))
				{
					throw new \LogicException("Invalid file '{$file}' not found for event '{$this->current_event}'", 1);
				}

				if (($this->filter !== 'adm') && strpos($file, 'styles/prosilver/template/') === 0)
				{
					$files_list['prosilver'][] = substr($file, strlen('styles/prosilver/template/'));
				}
				else if (($this->filter !== 'adm') && strpos($file, 'styles/subsilver2/template/') === 0)
				{
					$files_list['subsilver2'][] = substr($file, strlen('styles/subsilver2/template/'));
				}
				else if (($this->filter === 'adm') && strpos($file, 'adm/style/') === 0)
				{
					$files_list['adm'][] = substr($file, strlen('adm/style/'));
				}
				else
				{
					throw new \LogicException("Invalid file '{$file}' not found for event '{$this->current_event}'", 2);
				}

				$this->events_by_file[$file][] = $this->current_event;
			}
		}
		else if ($this->filter == 'adm')
		{
			$file = substr($file_details, strlen('* Location: '));
			$files_list['adm'][] =  substr($file, strlen('adm/style/'));

			$this->events_by_file[$file][] = $this->current_event;
		}
		else
		{
			throw new \LogicException("Invalid file list found for event '{$this->current_event}'", 2);
		}

		return $files_list;
	}

	public function crawl_file_for_events($file)
	{
		if (!file_exists($this->root_path . $file))
		{
			throw new \LogicException("File '{$file}' does not exist", 1);
		}

		$event_list = array();
		$file_content = file_get_contents($this->root_path . $file);

		$events = explode('<!-- EVENT ', $file_content);
		// Remove the code before the first event
		array_shift($events);
		foreach ($events as $event)
		{
			list($event_name, $null) = explode(' -->', $event, 2);
			$event_list[] = $event_name;
		}

		return $event_list;
	}

	/**
	* Validates whether all events from $file are in the md file and vice-versa
	*
	* @param string $file
	* @param array $events
	* @return null
	* @throws \LogicException
	*/
	public function validate_events_from_file($file, array $events)
	{
		if (empty($this->events_by_file[$file]) && empty($events))
		{
			return true;
		}
		else if (empty($this->events_by_file[$file]))
		{
			$event_list = implode("', '", $events);
			throw new \LogicException("File '{$file}' should not contain events, but contains: "
				. "'{$event_list}'", 1);
		}
		else if (empty($events))
		{
			$event_list = implode("', '", $this->events_by_file[$file]);
			throw new \LogicException("File '{$file}' contains no events, but should contain: "
				. "'{$event_list}'", 1);
		}

		$missing_events_from_file = array();
		foreach ($this->events_by_file[$file] as $event)
		{
			if (!in_array($event, $events))
			{
				$missing_events_from_file[] = $event;
			}
		}

		if (!empty($missing_events_from_file))
		{
			$event_list = implode("', '", $missing_events_from_file);
			throw new \LogicException("File '{$file}' does not contain events: '{$event_list}'", 2);
		}

		$missing_events_from_md = array();
		foreach ($events as $event)
		{
			if (!in_array($event, $this->events_by_file[$file]))
			{
				$missing_events_from_md[] = $event;
			}
		}

		if (!empty($missing_events_from_md))
		{
			$event_list = implode("', '", $missing_events_from_md);
			throw new \LogicException("File '{$file}' contains additional events: '{$event_list}'", 3);
		}

		return true;
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
			if ($file_info->isDir())
			{
				$sub_dir = $this->get_recursive_file_list($file_info->getPath() . '/' . $file_info->getFilename(), $path . '/' . $file_info->getFilename());
				foreach ($sub_dir as $file)
				{
					$files[] = $file_info->getFilename() . '/' . $file;
				}
			}
			else if (substr($file_info->getFilename(), -5) == '.html')
			{
				$files[] = $file_info->getFilename();
			}
		}

		return $files;
	}
}
