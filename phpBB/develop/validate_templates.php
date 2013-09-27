<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

//
// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it");

set_time_limit(0);

include('template_validator.php');
$validator = new template_validator();

check_dir('..', array('docs', 'vendor'));

/**
* Check directory
*
* @param string $dir Directory to check
* @param array $skip List of directories to skip
*/
function check_dir($dir, $skip = array())
{
	foreach (new DirectoryIterator($dir) as $file)
	{
		$filename = $file->getFilename();
		if ($file->isDot())
		{
			continue;
		}
		elseif ($file->isDir() && !in_array($file->getFilename(), $skip))
		{
			// Do not pass $skip, it applies to root directories only
			check_dir($file->getPathname());
		}
		elseif ($file->isFile() && preg_match('/\.html$/', $file->getFilename()))
		{
			validate_template($file->getPathname());
		}
	}
}

/**
* Validate template
*
* @param string $filename Path to template
*/
function validate_template($filename)
{
	global $validator;
	$validator->load_file($filename);
	$error = $validator->validate();
	if ($error !== false)
	{
		echo $filename, ': ', $error, (isset($_SERVER['HTTP_HOST']) ? '<br />' : "\n");
	}
}
