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
	echo "all:\n";
	echo "    Generate the complete wikipage for https://wiki.phpbb.com/Event_List\n";
	echo "\n";
	echo "php:\n";
	echo "    Generate the PHP event section of Event_List\n";
	echo "\n";
	echo "acp:\n";
	echo "    Generate the ACP Template event section of Event_List\n";
	echo "\n";
	echo "styles:\n";
	echo "    Generate the Styles Template event section of Event_List\n";
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
require __DIR__ . '/../phpbb/event/php_exporter.' . $phpEx;
require __DIR__ . '/../phpbb/event/md_exporter.' . $phpEx;

switch ($action)
{
	case 'all':
		echo '__FORCETOC__' . "\n";

	case 'php':
		$exporter = new \phpbb\event\php_exporter($phpbb_root_path);
		$exporter->crawl_phpbb_directory_php();
		echo $exporter->export_events_for_wiki();

		if ($action === 'php')
		{
			break;
		}
		echo "\n";
		// no break;

	case 'styles':
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path);
		$exporter->crawl_eventsmd('docs/events.md', 'styles');
		echo $exporter->export_events_for_wiki();

		if ($action === 'styles')
		{
			break;
		}
		echo "\n";
		// no break;

	case 'adm':
		$exporter = new \phpbb\event\md_exporter($phpbb_root_path);
		$exporter->crawl_eventsmd('docs/events.md', 'adm');
		echo $exporter->export_events_for_wiki();

		if ($action === 'all')
		{
			echo "\n" . '[[Category:Events and Listeners]]' . "\n";
		}
	break;

	default:
		usage();
}
