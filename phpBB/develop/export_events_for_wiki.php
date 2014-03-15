<?php
/**
*
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (php_sapi_name() != 'cli')
{
	die("This program must be run from the command line.\n");
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path = __DIR__ . '/../';

function usage()
{
	echo "Usage: export_events_for_wiki.php COMMAND\n";
	echo "\n";
	echo "acp:\n";
	echo "    Export all events for files in the acp style.\n";
	echo "\n";
	echo "styles:\n";
	echo "    Export all events for files in the prosilver and subsilver2 styles.\n";
	echo "\n";
	echo "php:\n";
	echo "    Export all events for php-files.\n";
	exit(2);
}

function export_from_eventsmd($phpbb_root_path, $filter)
{
	$file_content = file_get_contents($phpbb_root_path . 'docs/events.md');

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

function export_from_php($phpbb_root_path)
{
	$files = get_file_list($phpbb_root_path);
	$events = array();
	foreach ($files as $file)
	{
		$file_events = check_for_events($phpbb_root_path, $file);
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

function check_for_events($phpbb_root_path, $file)
{
	$events = array();
	$content = file_get_contents($phpbb_root_path . $file);

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
					while (strpos($lines[$event_line - $find_varsarray_line], "vars = array('") === false)
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
				// Validate @event name
				$find_event_line = 1;
				while (strpos($lines[$event_line - $find_event_line], '* @event ') === false)
				{
					$find_event_line++;

					if ($find_event_line > min(50, $event_line))
					{
						throw new LogicException('Can not find @event tag for event "' . $event_name . '" in file "' . $file . '"');
					}
				}
				$event_name_tag = substr(trim($lines[$event_line - $find_event_line]), strlen('* @event '));
				if ($event_name_tag !== $event_name)
				{
					throw new LogicException('Event name does not match @event tag for event "' . $event_name . '" in file "' . $file . '"');
				}

				// Find @since
				$find_since_line = 1;
				while (strpos($lines[$event_line - $find_since_line], '* @since ') === false)
				{
					$find_since_line++;

					if ($find_since_line > min(50, $event_line))
					{
						throw new LogicException('Can not find @since tag for event "' . $event_name . '" in file "' . $file . '"');
					}
				}
				$since = substr(trim($lines[$event_line - $find_since_line]), strlen('* @since '));
				$since = ($since == '3.1-A1') ? '3.1.0-a1' : $since;

				// Find event description line
				$find_description_line = 3;
				while (strpos(trim($lines[$event_line - $find_description_line]), '*') === 0)
				{
					$find_description_line++;

					if ($find_description_line > min(50, $event_line))
					{
						throw new LogicException('Can not find description-line for event "' . $event_name . '" in file "' . $file . '"');
					}
				}
				$description = substr(trim($lines[$event_line - $find_description_line + 1]), strlen('* '));

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
			$sub_dir = get_file_list($file_info->getPath() . '/' . $file_info->getFilename(), $path . '/' . $file_info->getFilename());
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

function validate_argument_count($arguments, $count)
{
	if ($arguments <= $count)
	{
		usage();
	}
}

validate_argument_count($argc, 1);

$action = $argv[1];

switch ($action)
{
	case 'acp':
		export_from_eventsmd($phpbb_root_path, 'acp');
		break;

	case 'styles':
		export_from_eventsmd($phpbb_root_path, 'styles');
		break;

	case 'php':
		export_from_php($phpbb_root_path);
		break;

	default:
		usage();
}
