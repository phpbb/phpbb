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

if (php_sapi_name() != 'cli')
{
	die("This program must be run from the command line.\n");
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
$phpbb_root_path = __DIR__ . '/../';
define('IN_PHPBB', true);

function usage()
{
	echo "Usage: export_events_for_wiki.php COMMAND [VERSION] [EXTENSION]\n";
	echo "\n";
	echo "COMMAND:\n";
	echo "    all:\n";
	echo "        Generate the complete wikipage for https://wiki.phpbb.com/Event_List\n";
	echo "\n";
	echo "    diff:\n";
	echo "        Generate the Event Diff for the release highlights\n";
	echo "\n";
	echo "    php:\n";
	echo "        Generate the PHP event section of Event_List\n";
	echo "\n";
	echo "    adm:\n";
	echo "        Generate the ACP Template event section of Event_List\n";
	echo "\n";
	echo "    styles:\n";
	echo "        Generate the Styles Template event section of Event_List\n";
	echo "\n";
	echo "VERSION (diff only):\n";
	echo "    Filter events (minimum version)\n";
	echo "\n";
	echo "EXTENSION (Optional):\n";
	echo "    If not given, only core events will be exported.\n";
	echo "    Otherwise only events from the extension will be exported.\n";
	echo "\n";
	exit(2);
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
$extension = isset($argv[2]) ? $argv[2] : null;
$min_version = null;
require __DIR__ . '/../phpbb/event/php_exporter.' . $phpEx;
require __DIR__ . '/../phpbb/event/md_exporter.' . $phpEx;
require __DIR__ . '/../includes/functions.' . $phpEx;
require __DIR__ . '/../phpbb/event/recursive_event_filter_iterator.' . $phpEx;
require __DIR__ . '/../phpbb/recursive_dot_prefix_filter_iterator.' . $phpEx;

switch ($action)
{

	case 'diff':
		echo '== Event changes ==' . "\n";
		$min_version = $extension;
		$extension = isset($argv[3]) ? $argv[3] : null;

	case 'all':
		if ($action === 'all')
		{
			echo '__FORCETOC__' . "\n";
		}


	case 'php':
		$exporter = new \phpbb\event\php_exporter($phpbb_root_path, $extension, $min_version);
		$exporter->crawl_phpbb_directory_php();
		echo $exporter->export_events_for_wiki($action);

		if ($action === 'php')
		{
			break;
		}
		echo "\n";
		// no break;

	case 'styles':
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path, $extension, $min_version);
		if ($min_version && $action === 'diff')
		{
			$exporter->crawl_eventsmd('docs/events.md', 'styles');
		}
		else
		{
			$exporter->crawl_phpbb_directory_styles('docs/events.md');
		}
		echo $exporter->export_events_for_wiki($action);

		if ($action === 'styles')
		{
			break;
		}
		echo "\n";
		// no break;

	case 'adm':
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path, $extension, $min_version);
		if ($min_version && $action === 'diff')
		{
			$exporter->crawl_eventsmd('docs/events.md', 'adm');
		}
		else
		{
			$exporter->crawl_phpbb_directory_adm('docs/events.md');
		}
		echo $exporter->export_events_for_wiki($action);

		if ($action === 'all')
		{
			echo "\n" . '[[Category:Events and Listeners]]' . "\n";
		}
	break;

	default:
		usage();
}
