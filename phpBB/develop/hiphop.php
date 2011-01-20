<?php
/**
 * This script is for generating compiled template files and performing various checks
 * before phpBB is compiled with HipHop. It is not intended for production use, and
 * should be deleted or disabled after HipHop is set up
 *
 * @author ckwalsh
 **/

// Security message:
//
// This script is potentially dangerous.
// Remove or comment the next line (die(".... ) to enable this script.
// Do NOT FORGET to either remove this script or disable it after you have used it.
//
die("Please read the first lines of this script for instructions on how to enable it\n");


define('IN_PHPBB', true);
$phpbb_root_path = './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require_once($phpbb_root_path . 'config.php');

if (!defined('PHPBB_INSTALLED'))
{
	die("ERROR: Must install phpBB using standard php runtime before preparing for HipHop.\n");
}
if ($dbms != 'mysql')
{
	die("ERROR: \$dbms must be set to 'mysql' in config.php.\n");
}

if ($acm_type == 'file')
{
	echo("WARNING: For best performance, \$acm_type should be changed from the 'file' default in config.php. 'apc' is recommended.\n");
}

require_once($phpbb_root_path . 'includes/functions.php');
require_once($phpbb_root_path . 'includes/constants.php');
require_once($phpbb_root_path . 'includes/template.php');
require_once($phpbb_root_path . 'includes/functions_template.php');

// Disable errors so we don't have to put up with notices
error_reporting(0);

$template = new template();
$style_dir = opendir($phpbb_root_path . 'styles');

while ($style = readdir($style_dir))
{
	$tpl_dir = $phpbb_root_path . "styles/$style/template";

	if (!file_exists($tpl_dir))
	{
		continue;
	}

	$template->set_custom_template($tpl_dir, $style);
	$template->cachepath = $phpbb_root_path . 'cache/tpl_' . str_replace('_', '-', $style) . '_';
	$tpl_h = opendir($tpl_dir);

	while ($tpl = readdir($tpl_h))
	{
		if ($tpl[0] == '.')
		{
			continue;
		}

		$template->set_filenames(array(
			'body' => $tpl
		 ));
		 $template->assign_display('body');
	}

}

// Now for the admin style

$admin_dir = opendir($phpbb_root_path . 'adm/style');

$template->set_custom_template($phpbb_root_path . 'adm/style', 'admin');

while ($adm = readdir($admin_dir))
{
	if ($adm[0] == '.')
	{
		continue;
	}
	
	$template->set_filenames(array(
		'body' => $adm
	));
	
	$template->assign_display('body');
}

$cmd = 'cd ' . $phpbb_root_path . '; find . -name "*.php" | grep -v "/install/" | grep -v "/develop/" > files.list';

`$cmd`;

echo("Now execute this command to compile phpBB3\n");
echo("(Set the constants as specified at https://github.com/facebook/hiphop-php/wiki/Running-HipHop\n");
echo("\$HPHP_HOME/src/hphp/hphp --input-list=files.list -k 1 --log=3 --force=1 --cluster-count=50 -o /tmp/phpbb3\n");
echo("\n");
echo("To run the compiled program, run\n");
echo('/tmp/phpbb3/program -m server -v "Server.SourceRoot=`pwd`" -v "Server.DefaultDocument=index.php" -c $HPHP_HOME/bin/mime.hdf -p8080' . "\n");
