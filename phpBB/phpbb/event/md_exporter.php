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
* Crawls through a markdown file and grabs all events
*/
class md_exporter
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
	protected $filter;

	/** @var string */
	protected $current_event;

	/** @var array */
	protected $events;

	/**
	* @param string $phpbb_root_path
	* @param mixed $extension	String 'vendor/ext' to filter, null for phpBB core
	* @param string $min_version
	* @param string $max_version
	*/
	public function __construct($phpbb_root_path, $extension = null, $min_version = null, $max_version = null)
	{
		$this->root_path = $phpbb_root_path;
		$this->path = $this->root_path;
		if ($extension)
		{
			$this->path .= 'ext/' . $extension . '/';
		}

		$this->events = array();
		$this->events_by_file = array();
		$this->filter = $this->current_event = '';
		$this->min_version = $min_version;
		$this->max_version = $max_version;
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
	* @param string $md_file	Relative from phpBB root
	* @return int		Number of events found
	* @throws \LogicException
	*/
	public function crawl_phpbb_directory_adm($md_file)
	{
		$this->crawl_eventsmd($md_file, 'adm');

		$file_list = $this->get_recursive_file_list($this->path  . 'adm/style/');
		foreach ($file_list as $file)
		{
			$file_name = 'adm/style/' . $file;
			$this->validate_events_from_file($file_name, $this->crawl_file_for_events($file_name));
		}

		return sizeof($this->events);
	}

	/**
	* @param string $md_file	Relative from phpBB root
	* @return int		Number of events found
	* @throws \LogicException
	*/
	public function crawl_phpbb_directory_styles($md_file)
	{
		$this->crawl_eventsmd($md_file, 'styles');

		$styles = array('prosilver');
		foreach ($styles as $style)
		{
			$file_list = $this->get_recursive_file_list(
				$this->path . 'styles/' . $style . '/template/'
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
	* @param string $md_file	Relative from phpBB root
	* @param string $filter		Should be 'styles' or 'adm'
	* @return int		Number of events found
	* @throws \LogicException
	*/
	public function crawl_eventsmd($md_file, $filter)
	{
		if (!file_exists($this->path . $md_file))
		{
			throw new \LogicException("The event docs file '{$md_file}' could not be found");
		}

		$file_content = file_get_contents($this->path . $md_file);
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

			$changed_versions = array();
			if (strpos($details, "\n* Changed: ") !== false)
			{
				list($since, $details) = explode("\n* Changed: ", $details, 2);
				while (strpos($details, "\n* Changed: ") !== false)
				{
					list($changed, $details) = explode("\n* Changed: ", $details, 2);
					$changed_versions[] = $changed;
				}
				list($changed, $description) = explode("\n* Purpose: ", $details, 2);
				$changed_versions[] = $changed;
			}
			else
			{
				list($since, $description) = explode("\n* Purpose: ", $details, 2);
				$changed_versions = array();
			}

			$files = $this->validate_file_list($file_details);
			$since = $this->validate_since($since);
			$changes = array();
			foreach ($changed_versions as $changed)
			{
				list($changed_version, $changed_description) = $this->validate_changed($changed);

				if (isset($changes[$changed_version]))
				{
					throw new \LogicException("Duplicate change information found for event '{$this->current_event}'");
				}

				$changes[$changed_version] = $changed_description;
			}
			$description = trim($description, "\n") . "\n";

			if (!$this->version_is_filtered($since))
			{
				$is_filtered = false;
				foreach ($changes as $version => $null)
				{
					if ($this->version_is_filtered($version))
					{
						$is_filtered = true;
						break;
					}
				}

				if (!$is_filtered)
				{
					continue;
				}
			}

			$this->events[$event_name] = array(
				'event'			=> $this->current_event,
				'files'			=> $files,
				'since'			=> $since,
				'changed'		=> $changes,
				'description'	=> $description,
			);
		}

		return sizeof($this->events);
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
	* Format the php events as a wiki table
	*
	* @param string $action
	* @return string		Number of events found
	*/
	public function export_events_for_wiki($action = '')
	{
		if ($this->filter === 'adm')
		{
			if ($action === 'diff')
			{
				$wiki_page = '=== ACP Template Events ===' . "\n";
			}
			else
			{
				$wiki_page = '= ACP Template Events =' . "\n";
			}
			$wiki_page .= '{| class="zebra sortable" cellspacing="0" cellpadding="5"' . "\n";
			$wiki_page .= '! Identifier !! Placement !! Added in Release !! Explanation' . "\n";
		}
		else
		{
			if ($action === 'diff')
			{
				$wiki_page = '=== Template Events ===' . "\n";
			}
			else
			{
				$wiki_page = '= Template Events =' . "\n";
			}
			$wiki_page .= '{| class="zebra sortable" cellspacing="0" cellpadding="5"' . "\n";
			$wiki_page .= '! Identifier !! Prosilver Placement (If applicable) !! Added in Release !! Explanation' . "\n";
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
				$wiki_page .= implode(', ', $event['files']['prosilver']);
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
		if (!$this->validate_version($since))
		{
			throw new \LogicException("Invalid since information found for event '{$this->current_event}'");
		}

		return $since;
	}

	/**
	* Validate "Changed" Information
	*
	* @param string $changed
	* @return string
	* @throws \LogicException
	*/
	public function validate_changed($changed)
	{
		if (strpos($changed, ' ') !== false)
		{
			list($version, $description) = explode(' ', $changed, 2);
		}
		else
		{
			$version = $changed;
			$description = '';
		}

		if (!$this->validate_version($version))
		{
			throw new \LogicException("Invalid changed information found for event '{$this->current_event}'");
		}

		return array($version, $description);
	}

	/**
	* Validate "version" Information
	*
	* @param string $version
	* @return bool True if valid, false otherwise
	*/
	public function validate_version($version)
	{
		return preg_match('#^\d+\.\d+\.\d+(?:-(?:a|b|RC|pl)\d+)?$#', $version);
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
			'adm'			=> array(),
		);

		// Multi file list
		if (strpos($file_details, "* Locations:\n    + ") === 0)
		{
			$file_details = substr($file_details, strlen("* Locations:\n    + "));
			$files = explode("\n    + ", $file_details);
			foreach ($files as $file)
			{
				if (!file_exists($this->path . $file) || substr($file, -5) !== '.html')
				{
					throw new \LogicException("Invalid file '{$file}' not found for event '{$this->current_event}'", 1);
				}

				if (($this->filter !== 'adm') && strpos($file, 'styles/prosilver/template/') === 0)
				{
					$files_list['prosilver'][] = substr($file, strlen('styles/prosilver/template/'));
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
			if (!file_exists($this->path . $file) || substr($file, -5) !== '.html')
			{
				throw new \LogicException("Invalid file '{$file}' not found for event '{$this->current_event}'", 1);
			}

			$files_list['adm'][] =  substr($file, strlen('adm/style/'));

			$this->events_by_file[$file][] = $this->current_event;
		}
		else
		{
			throw new \LogicException("Invalid file list found for event '{$this->current_event}'", 2);
		}

		return $files_list;
	}

	/**
	* Get all template events in a template file
	*
	* @param string $file
	* @return array
	* @throws \LogicException
	*/
	public function crawl_file_for_events($file)
	{
		if (!file_exists($this->path . $file))
		{
			throw new \LogicException("File '{$file}' does not exist", 1);
		}

		$event_list = array();
		$file_content = file_get_contents($this->path . $file);

		$events = explode('<!-- EVENT ', $file_content);
		// Remove the code before the first event
		array_shift($events);
		foreach ($events as $event)
		{
			$event = explode(' -->', $event, 2);
			$event_list[] = array_shift($event);
		}

		return $event_list;
	}

	/**
	* Validates whether all events from $file are in the md file and vice-versa
	*
	* @param string $file
	* @param array $events
	* @return true
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
	* @return	array	List of files (including directories)
	*/
	public function get_recursive_file_list($dir)
	{
		try
		{
			$iterator = new \RecursiveIteratorIterator(
				new \phpbb\recursive_dot_prefix_filter_iterator(
					new \RecursiveDirectoryIterator(
						$dir,
						\FilesystemIterator::SKIP_DOTS
					)
				),
				\RecursiveIteratorIterator::SELF_FIRST
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
			if ($file_info->isDir())
			{
				continue;
			}

			$relative_path = $iterator->getInnerIterator()->getSubPathname();

			if (substr($relative_path, -5) == '.html')
			{
				$files[] = str_replace(DIRECTORY_SEPARATOR, '/', $relative_path);
			}
		}

		return $files;
	}
}
