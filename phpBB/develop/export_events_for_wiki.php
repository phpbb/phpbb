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
require __DIR__ . '/../phpbb/event/exporter.' . $phpEx;

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

function validate_argument_count($arguments, $count)
{
	if ($arguments <= $count)
	{
		usage();
	}
}

validate_argument_count($argc, 1);

$action = $argv[1];
$exporter = new \phpbb\event\exporter($phpbb_root_path);

switch ($action)
{
	case 'acp':
		$exporter->export_from_eventsmd('acp');
		break;

	case 'styles':
		$exporter->export_from_eventsmd('styles');
		break;

	case 'php':
		$exporter->crawl_phpbb_directory_php();
		echo $exporter->export_php_events_for_wiki();
		break;

	default:
		usage();
}
