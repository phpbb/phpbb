#!/usr/bin/env php
<?php
/**
*
* @package build
* @copyright (c) 2011 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU General Public License
*
*/

if ($_SERVER['argc'] != 2)
{
	echo "Please specify the new version as argument (e.g. build_changelog.php '1.0.2').\n";
	exit(1);
}

$fixVersion = $_SERVER['argv'][1];

$query = 'project = PHPBB3
	AND resolution = Fixed
	AND fixVersion = "' . $fixVersion . '"
	AND status IN ("Unverified Fix", Closed)';

$url = 'http://tracker.phpbb.com/sr/jira.issueviews:searchrequest-xml/temp/SearchRequest.xml?jqlQuery=' . urlencode($query) . '&tempMax=1000';
$xml = simplexml_load_string(file_get_contents($url));

foreach ($xml->xpath('//item') as $item)
{
	$key = (string) $item->key;

	$keyUrl = 'http://tracker.phpbb.com/browse/' . $key;
	$keyLink = '<a href="' . $keyUrl . '">' . $key . '</a>';

	$value = str_replace($key, $keyLink, htmlspecialchars($item->title));
	$value = str_replace(']', '] -', $value);

	$types[(string) $item->type][$key] = $value;
}

ksort($types);
foreach ($types as $type => $tickets)
{
	echo "<h4>$type</h4>\n";
	echo "<ul>\n";

	uksort($tickets, 'strnatcasecmp');

	foreach ($tickets as $ticket)
	{
		echo "<li>$ticket</li>\n";
	}
	echo "</ul>\n";
}
