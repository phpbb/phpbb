<?php
// -------------------------------------------------------------
//
// $Id$
//
// FILENAME  : write_lang_files.php
// STARTED   : Sat Nov 01 2003
// COPYRIGHT : © 2003 phpBB Group
// WWW       : http://www.phpbb.com/
// LICENCE   : GPL vs2.0 [ see /docs/COPYING ] 
// 
// -------------------------------------------------------------

/*
	This script writes down all $user->lang occurrences used by php files.
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

$phpfiles_directories = array('../', '../includes/', '../includes/acm/', '../includes/auth/', '../includes/mcp/', '../includes/ucp/');
$ext = 'php';
$store_dir = '../store/main/';

if (!is_writable($store_dir))
{
	die("Directory $store_dir is not writeable!");
}

// Open Language File
include('../language/en/lang_main.php');
include('../language/en/lang_admin.php');

$files_to_parse = $php_files = array();

$num = 0;
foreach ($phpfiles_directories as $directory)
{
	$dhandler = opendir($directory);
	if (!$dhandler)
	{
		die("Unable to open $directory");
	}

	while ($file = readdir($dhandler))
	{
		if (is_file($directory . $file) && preg_match('#\.php$#i', $file))
		{
			$php_files[$num]['filename'] = $directory . $file;
			$php_files[$num]['single_filename'] = $file;
			$num++;
		}
	}
	closedir($dhandler);
}

echo '<br>Parsing PHP Files';

$dependency = array();

// Parse PHP Files and get our filenames
foreach ($php_files as $file_num => $data)
{
	echo '.';
	flush();
	$contents = implode('', file($data['filename'], filesize($data['filename'])));

	$lang_entries = array();
	preg_match_all('#' . preg_quote('$user->lang[\'') . '([A-Za-z0-9\-_]*?)' . preg_quote("']") . '#s', $contents, $lang_entries);
	$php_files[$file_num]['lang_entries'] = array_unique($lang_entries[1]);
	foreach ($php_files[$file_num]['lang_entries'] as $var)
	{
		$dependency[$var][] = $data['single_filename'];
	}
}

// Not only write down all language files, place them into a specific array, named by the template file
// All Language vars assigned to more than one template will be placed into a common file
$entry = array();
$merge = array('ucp', 'mcp', 'functions');
$common_fp = fopen($store_dir . 'lang_common.php', 'w');
fwrite($common_fp, "<?php\n\n \$lang = array(\n");

echo '<br>Write Language Files';

asort($dependency);
ksort($dependency);

foreach ($dependency as $lang_var => $filenames)
{
	$var = $lang_var;
	
	if (sizeof($filenames) != 1)
	{
		fwrite($common_fp, (($entry['common']) ? ",\n" : '') . "\t'$var' => '" . $lang[$var] . "'");
		$entry['common'] = true;
	}
	else if (sizeof($filenames) == 1)
	{
		// Merge logical - hardcoded
		$fname = (preg_match('#^(' . implode('|', $merge) . ')#', $filenames[0], $match)) ? $match[0] . '.php' : $filenames[0];

		if (!$lang_fp[$fname])
		{
			$lang_fp[$fname] = fopen($store_dir . 'lang_' . $fname, 'w');
			fwrite($lang_fp[$fname], "<?php\n\n\$lang = array(\n");
			$entry[$fname] = false;
		}
		fwrite($lang_fp[$fname], (($entry[$fname]) ? ",\n" : '') . "\t'$var' => '" . $lang[$var] . "'");
		$entry[$fname] = true;
	}
}

fwrite($common_fp, ")\n);\n?>");
fclose($common_fp);

foreach ($lang_fp as $filepointer)
{
	fwrite($filepointer, ")\n);\n?>");
	fclose($filepointer);
}

echo '<br>Finished!';
flush();

?>