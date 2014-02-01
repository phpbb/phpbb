#!/usr/bin/env php
<?php
/**
*
* @package build
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if ($_SERVER['argc'] != 3)
{
	die("Please specify the previous and current version as arguments (e.g. build_diff.php '1.0.2' '1.0.3').");
}

$old_version = trim($_SERVER['argv'][1]);
$new_version = trim($_SERVER['argv'][2]);

$substitute_old = $old_version;
$substitute_new = $new_version;
$simple_name_old = 'release-' . $old_version;
$simple_name_new = 'release-' . $new_version;
$echo_changes = false;

// DO NOT EVER USE THE FOLLOWING! Fix the script to generate proper changes,
// do NOT manually create them.

// Set this to true to just compress the changes and do not build them again
// This should be used for building custom modified txt file. ;)
$package_changed_files = false;

//$debug_file = 'includes/functions_user.php'; //'styles/prosilver/style.cfg';
$debug_file = false;

if ($debug_file !== false)
{
	$echo_changes = false;
}

$s_name = 'save_' . $substitute_old . '_to_' . $substitute_new;

$location = dirname(__FILE__);

if (!$package_changed_files)
{
	if (!$echo_changes)
	{
		// Create directory...
		run_command("mkdir $location/save/{$s_name}");
		run_command("mkdir $location/save/{$s_name}/language");
		run_command("mkdir $location/save/{$s_name}/prosilver");
		run_command("mkdir $location/save/{$s_name}/subsilver2");
	}
}

// Build code changes and place them into 'save'
if (!$package_changed_files)
{
	build_code_changes('language');
	build_code_changes('prosilver');
	build_code_changes('subsilver2');
}

// Package code changes
$code_changes_filename = 'phpBB-' . $substitute_old . '_to_' . $substitute_new . '-codechanges';

if (!$echo_changes)
{
	// Now compress the files...
	// Build Main phpBB Release
	$compress_programs = array(
//		'tar.gz'	=> 'tar -czf',
		'tar.bz2'	=> 'tar -cjf',
		'zip'		=> 'zip -r'
	);

	chdir($location . '/save/' . $s_name);
	foreach ($compress_programs as $extension => $compress_command)
	{
		echo "Packaging code changes for $extension\n";
		run_command("rm ./../../new_version/release_files/{$code_changes_filename}.{$extension}");
		flush();

		// Build Package
		run_command("$compress_command ./../../new_version/release_files/{$code_changes_filename}.{$extension} *");
		flush();
	}
}

/**
* $output_format can be: language, prosilver and subsilver2
*/
function build_code_changes($output_format)
{
	global $substitute_new, $substitute_old, $simple_name_old, $simple_name_new, $echo_changes, $package_changed_files, $location, $debug_file, $s_name;

	// Global array holding the data entries
	$data = array(
		'header'		=> array(),
		'diff'			=> array(),
	);

	// Read diff file and prepare the output filedata...
	//$patch_filename = '../new_version/patches/phpBB-' . $substitute_old . '_to_' . $substitute_new . '.patch';
	$release_filename = 'phpbb-' . $substitute_old . '_to_' . $substitute_new . '_' . $output_format . '.txt';

	if (!$package_changed_files)
	{
		if (!$echo_changes)
		{
			$fp = fopen('save/' . $s_name . '/' . $output_format . '/' . $release_filename, 'wb');

			if (!$fp)
			{
				die('Unable to create ' . $release_filename);
			}
		}
	}

	include_once($location . '/build_helper.php');
	$package = new build_package(array($substitute_old, $substitute_new), false);

	$titles = array(
		'language'		=> 'phpBB ' . $substitute_old . ' to phpBB ' . $substitute_new . ' Language Pack Changes',
		'prosilver'		=> 'phpBB ' . $substitute_old . ' to phpBB ' . $substitute_new . ' prosilver Changes',
		'subsilver2'	=> 'phpBB ' . $substitute_old . ' to phpBB ' . $substitute_new . ' subsilver2 Changes',
	);

	$data['header'] = array(
		'title'		=> $titles[$output_format],
		'intro'		=> '

These are the ' . $titles[$output_format] . ' summed up into a little Mod. These changes are only partial and do not include any code changes, therefore not meant for updating phpBB.

	',
		'included_files'	=> array(),
	);

	// We collect the files we want to diff first (ironically we grab this from a diff file)
	if (!$echo_changes)
	{
		echo "\n\nCollecting Filenames:";
	}

	// We re-create the patch file
	foreach ($package->old_packages as $_package_name => $dest_package_filename)
	{
		chdir($package->locations['old_versions']);

		if (!$echo_changes)
		{
			echo "\n\n" . 'Creating patch/diff files for phpBB-' . $dest_package_filename . $package->get('new_version_number');
		}

		$dest_package_filename = $location . '/save/' . $s_name . '/phpBB-' . $dest_package_filename . $package->get('new_version_number') . '.patch';
		$package->run_command('diff ' . $package->diff_options . ' ' . $_package_name . ' ' . $package->get('simple_name') . ' > ' . $dest_package_filename);

		// Parse this diff to determine file changes from the checked versions and save them
		$result = $package->collect_diff_files($dest_package_filename, $_package_name);
		$package->run_command('rm ' . $dest_package_filename);
	}

	chdir($location);

	$filenames = array();
	foreach ($result['files'] as $filename)
	{
		if ($debug_file !== false && $filename != $debug_file)
		{
			continue;
		}

		// Decide which files to compare...
		switch ($output_format)
		{
			case 'language':
				if (strpos($filename, 'language/en/') !== 0)
				{
					continue 2;
				}
			break;

			case 'prosilver':
				if (strpos($filename, 'styles/prosilver/') !== 0)
				{
					continue 2;
				}
			break;

			case 'subsilver2':
				if (strpos($filename, 'styles/subsilver2/') !== 0)
				{
					continue 2;
				}
			break;
		}

		if (!file_exists($location . '/old_versions/' . $simple_name_old . '/' . $filename))
		{
			// New file... include it
			$data['header']['included_files'][] = array(
				'old'				=> $location . '/old_versions/' . $simple_name_old . '/' . $filename,
				'new'				=> $location . '/old_versions/' . $simple_name_new . '/' . $filename,
				'phpbb_filename'	=> $filename,
			);
			continue;
		}

		$filenames[] = array(
			'old'				=> $location . '/old_versions/' . $simple_name_old . '/' . $filename,
			'new'				=> $location . '/old_versions/' . $simple_name_new . '/' . $filename,
			'phpbb_filename'	=> $filename,
		);
	}

	// Now let us go through the filenames list and create a more comprehensive diff
	if (!$echo_changes)
	{
		fwrite($fp, build_header($output_format, $filenames, $data['header']));
	}
	else
	{
		//echo build_header('text', $filenames, $data['header']);
	}

	// Copy files...
	$files_to_copy = array();

	foreach ($data['header']['included_files'] as $filename)
	{
		$files_to_copy[] = $filename['phpbb_filename'];
	}

	// First step is to copy the new version over (clean structure)
	if (!$echo_changes && sizeof($files_to_copy))
	{
		foreach ($files_to_copy as $file)
		{
			// Create directory?
			$dirname = dirname($file);

			if ($dirname)
			{
				$dirname = explode('/', $dirname);
				$__dir = array();

				foreach ($dirname as $i => $dir)
				{
					$__dir[] = $dir;
					run_command("mkdir -p $location/save/" . $s_name . '/' . $output_format . '/' . implode('/', $__dir));
				}
			}

			$source_file = $location . '/new_version/phpBB3/' . $file;
			$dest_file = $location . '/save/' . $s_name . '/' . $output_format . '/';
			$dest_file .= $file;

			$command = "cp -p $source_file $dest_file";
			$result = trim(`$command`);
			echo "- Copied File: " . $source_file . " -> " . $dest_file . "\n";
		}
	}

	include_once('diff_class.php');

	if (!$echo_changes)
	{
		echo "\n\nDiffing Codebases:";
	}

	foreach ($filenames as $file_ary)
	{
		if (!file_exists($file_ary['old']))
		{
			$lines1 = array();
		}
		else
		{
			$lines1 = file($file_ary['old']);
		}
		$lines2 = file($file_ary['new']);

		if (!sizeof($lines1))
		{
			// New File
		}
		else
		{
			$diff = new Diff($lines1, $lines2);
			$fmt = new BBCodeDiffFormatter(false, 5, $debug_file);

			if (!$echo_changes)
			{
				fwrite($fp, $fmt->format_open($file_ary['phpbb_filename']));
				fwrite($fp, $fmt->format($diff, $lines1));
				fwrite($fp, $fmt->format_close($file_ary['phpbb_filename']));
			}
			else
			{
				echo $fmt->format_open($file_ary['phpbb_filename']);
				echo $fmt->format($diff, $lines1);
				echo $fmt->format_close($file_ary['phpbb_filename']);
			}

			if ($debug_file !== false)
			{
				echo $fmt->format_open($file_ary['phpbb_filename']);
				echo $fmt->format($diff, $lines1);
				echo $fmt->format_close($file_ary['phpbb_filename']);
				exit;
			}
		}
	}

	if (!$echo_changes)
	{
		fwrite($fp, build_footer($output_format));

		// Close file
		fclose($fp);

		chmod('save/' . $s_name . '/' . $output_format . '/' . $release_filename, 0666);
	}
	else
	{
		echo build_footer($output_format);
	}
}

/**
* Build Footer
*/
function build_footer($mode)
{
	$html = '';

	$html .= "# \n";
	$html .= "#-----[ SAVE/CLOSE ALL FILES ]------------------------------------------ \n";
	$html .= "# \n";
	$html .= "# EoM";

	return $html;
}

/**
* Build Header
*/
function build_header($mode, $filenames, $header)
{
	global $substitute_old;

	$html = '';

	$html .= "############################################################## \n";
	$html .= "## Title: " . $header['title'] . "\n";
	$html .= "## Author: naderman < naderman@phpbb.com > (Nils Adermann) http://www.phpbb.com \n";
	$html .= "## Description: \n";

	$intr = explode("\n", $header['intro']);
	$introduction = '';
	foreach ($intr as $_line)
	{
		$introduction .= wordwrap($_line, 80) . "\n";
	}
	$intr = explode("\n", $introduction);

	foreach ($intr as $_line)
	{
		$html .= "##		" . $_line . "\n";
	}
	$html .= "## \n";
	$html .= "## Files To Edit: \n";

	foreach ($filenames as $file_ary)
	{
		$html .= "##		" . $file_ary['phpbb_filename'] . "\n";
	}
	$html .= "##\n";
	if (sizeof($header['included_files']))
	{
		$html .= "## Included Files: \n";
		foreach ($header['included_files'] as $filename)
		{
			$html .= "##		{$filename['phpbb_filename']}\n";
		}
	}
	$html .= "## License: http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2 \n";
	$html .= "############################################################## \n";
	$html .= "\n";

	// COPY Statement?
	if (sizeof($header['included_files']))
	{
		$html .= "#\n#-----[ COPY ]------------------------------------------\n#\n";
		foreach ($header['included_files'] as $filename)
		{
			$html .= "copy {$filename['phpbb_filename']} to {$filename['phpbb_filename']}\n";
		}
		$html .= "\n";
	}

	return $html;
}

function run_command($command)
{
	$result = trim(`$command`);
	echo "\n- Command Run: " . $command . "\n";
}
