<?php
/**
*
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

define('IN_PHPBB', 1);
define('ANONYMOUS', 1);
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path = __DIR__.'/../';

include($phpbb_root_path . 'common.'.$phpEx);

function usage()
{
	echo "Usage: export_events_for_wiki.php COMMAND\n";
	echo "\n";
	echo "acp:\n";
	echo "    Export all events for files in the acp style.\n";
	echo "\n";
	echo "styles:\n";
	echo "    Export all events for files in the prosilver and subsilver2 styles.\n";
	exit(2);
}

function export_from_eventsmd($filter)
{
	global $phpbb_root_path;
	$file_content = file_get_contents($phpbb_root_path . 'docs/events.md');

	$events = explode("\n\n", $file_content);
	foreach ($events as $event)
	{
		// Last row of the file
		if (strpos($event, "\n===\n") === false) continue;

		list($event_name, $details) = explode("\n===\n", $event);

		if ($filter == 'acp' && strpos($event_name, 'acp_') !== 0) continue;
		if ($filter == 'styles' && strpos($event_name, 'acp_') === 0) continue;

		list($file_details, $explanition) = explode("\n* Purpose: ", $details);

		echo "|- id=\"{$event_name}\"\n";
		echo "| [[#{$event_name}|{$event_name}]] || ";

		if (strpos($file_details, "* Locations:\n    + ") === 0)
		{
			$file_details = substr($file_details, strlen("* Locations:\n    + "));
			$files = explode("\n    + ", $file_details);
			$prosilver = $subsilver2 = array();
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
			}
			echo implode(', ', $prosilver) . ' || ' . implode(', ', $subsilver2);
		}
		else if ($filter == 'acp')
		{
			echo substr($file_details, strlen("* Location: adm/style/"));
		}
		echo " || 3.1.0-a1 || " . str_replace("\n", ' ', $explanition) . "\n";

	}
}

function validate_argument_count($count)
{
	global $argv;

	if (count($argv) <= $count)
	{
		usage();
	}
}

validate_argument_count(1);

$action = $argv[1];

switch ($action)
{
	case 'acp':
		export_from_eventsmd('acp');
		break;

	case 'styles':
		export_from_eventsmd('styles');
		break;

	default:
		usage();
}
