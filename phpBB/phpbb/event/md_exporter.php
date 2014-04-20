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

		foreach ($this->events_by_file as $file => $events)
		{
			$this->validate_events_for_file($file, $events);
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

	/**
	* Validates whether a list of events is named in $file
	*
	* @param string $file
	* @param array $events
	* @return null
	* @throws \LogicException
	*/
	public function validate_events_for_file($file, array $events)
	{
		if (!file_exists($this->root_path . $file))
		{
			$event_list = implode("', '", $events);
			throw new \LogicException("File '{$file}' not found for event '{$event_list}'", 1);
		}

		$file_content = file_get_contents($this->root_path . $file);
		foreach ($events as $event)
		{
			if (($this->filter !== 'adm') && strpos($file, 'adm/style/') !== 0
				|| ($this->filter === 'adm') && strpos($file, 'adm/style/') === 0)
			{
				if (strpos($file_content, '<!-- EVENT ' . $event . ' -->') === false)
				{
					throw new \LogicException("Event '{$event}' not found in file '{$file}'", 2);
				}
			}
		}
	}
}
