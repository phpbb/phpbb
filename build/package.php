#!/usr/bin/env php
<?php
/**
*
* @package build
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

//$versions = array('3.0.2', '3.0.3', '3.0.4', '3.0.5', '3.0.6', '3.0.7-RC1', '3.0.7-RC2', '3.0.7', '3.0.7-PL1');

if ($_SERVER['argc'] < 2)
{
	die("Please specify a list of versions as the first argument (e.g. package.php '1.0.0, 1.0.1, 1.0.2').");
}

$versions = explode(',', $_SERVER['argv'][1]);
$versions = array_map('trim', $versions);

$verbose = true;

require('build_helper.php');

$package = new build_package($versions, $verbose);

echo "Building Release Packages\n";
echo "Now all three package types (patch, files, release) are built as well as the update package (update).\n";

// Go trough all versions making a diff if we even have old versions
// For phpBB 3.0.x we might choose a different update method, rendering the things below useless...
if (sizeof($package->old_packages))
{
	chdir($package->locations['old_versions']);

	// This array is for holding the filenames change
	$diff_file_changes = array();

	foreach ($package->old_packages as $_package_name => $dest_package_filename)
	{
		$package->begin_status('Parsing patch/diff files for phpBB-' . $dest_package_filename . $package->get('new_version_number'));

		// Parse this diff to determine file changes from the checked versions and save them
		$diff_file_changes[$_package_name] = $package->collect_diff_files(
			$package->get('patch_directory') . '/phpBB-' . $dest_package_filename . $package->get('new_version_number') . '.patch',
			$_package_name
		);
	}

	// Now put those files determined within the correct directories
	foreach ($diff_file_changes as $_package_name => $file_contents)
	{
		$package->begin_status('Creating files-only informations for ' . $package->old_packages[$_package_name] . $package->get('new_version_number'));

		$dest_filename_dir = $package->get('files_directory') . '/' . $package->old_packages[$_package_name] . $package->get('new_version_number');

		if (!file_exists($dest_filename_dir))
		{
			$package->run_command('mkdir ' . $dest_filename_dir);
		}

		// Now copy the file contents
		foreach ($file_contents['all'] as $file)
		{
			$source_filename = $package->get('dest_dir') . '/' . $file;
			$dest_filename = $dest_filename_dir . '/' . $file;

			// Create Directories along the way?
			$file = explode('/', $file);
			// Remove filename portion
			$file[sizeof($file)-1] = '';

			chdir($dest_filename_dir);
			foreach ($file as $entry)
			{
				$entry = trim($entry);
				if ($entry)
				{
					if (!file_exists('./' . $entry))
					{
						$package->run_command('mkdir ' . $entry);
					}
					chdir('./' . $entry);
				}
			}

			$package->run_command('cp ' . $source_filename . ' ' . $dest_filename);
		}
	}

	// Because there might be binary changes, we re-create the patch files... without parsing file differences.
	$package->run_command('rm -Rv ' . $package->get('patch_directory'));

	if (!file_exists($package->get('patch_directory')))
	{
		$package->run_command('mkdir ' . $package->get('patch_directory'));
	}

	chdir($package->locations['old_versions']);

	foreach ($package->old_packages as $_package_name => $dest_package_filename)
	{
		$package->begin_status('Creating patch/diff files for phpBB-' . $dest_package_filename . $package->get('new_version_number'));

		$dest_package_filename = $package->get('patch_directory') . '/phpBB-' . $dest_package_filename . $package->get('new_version_number') . '.patch';
		$package->run_command('diff ' . $package->diff_options_long . ' ' . $_package_name . ' ' . $package->get('simple_name') . ' > ' . $dest_package_filename);
	}

	$packages = $diff_file_changes;

	foreach ($packages as $_package_name => $file_contents)
	{
		$package->begin_status('Building specific update files for ' . $package->old_packages[$_package_name] . $package->get('new_version_number'));

		$dest_filename_dir = $package->get('update_directory') . '/' . $package->old_packages[$_package_name] . $package->get('new_version_number');

		if (!file_exists($dest_filename_dir))
		{
			$package->run_command('mkdir ' . $dest_filename_dir);
		}

		$package->run_command('cp -Rp ' . $package->get('dest_dir') . '/docs ' . $dest_filename_dir);
		$package->run_command('cp -Rp ' . $package->get('dest_dir') . '/install ' . $dest_filename_dir);

		$package->run_command('mkdir ' . $dest_filename_dir . '/install/update');
		$package->run_command('mkdir ' . $dest_filename_dir . '/install/update/old');
		$package->run_command('mkdir ' . $dest_filename_dir . '/install/update/new');

		// Remove some files
		$package->run_command('rm -v ' . $dest_filename_dir . '/install/install_install.php');
		$package->run_command('rm -v ' . $dest_filename_dir . '/install/install_convert.php');
		$package->run_command('rm -Rv ' . $dest_filename_dir . '/install/schemas');
		$package->run_command('rm -Rv ' . $dest_filename_dir . '/install/convertors');

		foreach ($file_contents['all'] as $index => $file)
		{
			if (strpos($file, 'recode_cjk') !== false)
			{
				unset($file_contents['all'][$index]);
			}
		}

		// First of all, fill the 'old' directory
		foreach ($file_contents['all'] as $file)
		{
			$source_filename = $package->locations['old_versions'] . $_package_name . '/' . $file;
			$dest_filename = $dest_filename_dir . '/install/update/old/' . $file;

			if (!file_exists($source_filename))
			{
				continue;
			}

			// Create Directories along the way?
			$file = explode('/', $file);
			// Remove filename portion
			$file[sizeof($file)-1] = '';

			chdir($dest_filename_dir . '/install/update/old');
			foreach ($file as $entry)
			{
				$entry = trim($entry);
				if ($entry)
				{
					if (!file_exists('./' . $entry))
					{
						$package->run_command('mkdir ' . $entry);
					}
					chdir('./' . $entry);
				}
			}

			$package->run_command('cp ' . $source_filename . ' ' . $dest_filename);
		}

		// Then fill the 'new' directory
		foreach ($file_contents['all'] as $file)
		{
			$source_filename = $package->locations['old_versions'] . $package->get('simple_name') . '/' . $file;
			$dest_filename = $dest_filename_dir . '/install/update/new/' . $file;

			if (!file_exists($source_filename))
			{
				continue;
			}

			// Create Directories along the way?
			$file = explode('/', $file);
			// Remove filename portion
			$file[sizeof($file)-1] = '';

			chdir($dest_filename_dir . '/install/update/new');
			foreach ($file as $entry)
			{
				$entry = trim($entry);
				if ($entry)
				{
					if (!file_exists('./' . $entry))
					{
						$package->run_command('mkdir ' . $entry);
					}
					chdir('./' . $entry);
				}
			}

			$package->run_command('cp ' . $source_filename . ' ' . $dest_filename);
		}

		// Build index.php file for holding the file structure
		$index_contents = '<?php

if (!defined(\'IN_PHPBB\'))
{
	exit;
}

// Set update info with file structure to update
$update_info = array(
	\'version\'	=> array(\'from\' => \'' . str_replace('_to_', '', $package->old_packages[$_package_name]) . '\', \'to\' => \'' . $package->get('new_version_number') . '\'),
';

		if (sizeof($file_contents['all']))
		{
			$index_contents .= '\'files\'		=> array(\'' . implode("',\n\t'", $file_contents['all']) . '\'),
';
		}
		else
		{
			$index_contents .= '\'files\'		=> array(),
';
		}

		if (sizeof($file_contents['binary']))
		{
			$index_contents .= '\'binary\'		=> array(\'' . implode("',\n\t'", $file_contents['binary']) . '\'),
';
		}
		else
		{
			$index_contents .= '\'binary\'		=> array(),
';
		}

		$index_contents .= ');

?' . '>';

		$fp = fopen($dest_filename_dir . '/install/update/index.php', 'wt');
		fwrite($fp, $index_contents);
		fclose($fp);
	}
	unset($diff_file_changes);

	$package->begin_status('Clean up all install files');

	// Copy the install files to their respective locations
	$package->run_command('cp -Rp ' . $package->get('dest_dir') . '/docs ' . $package->get('patch_directory'));
	$package->run_command('cp -Rp ' . $package->get('dest_dir') . '/install ' . $package->get('patch_directory'));

	// Remove some files
	chdir($package->get('patch_directory') . '/install');

	$package->run_command('rm -v install_install.php');
	$package->run_command('rm -v install_update.php');
	$package->run_command('rm -v install_convert.php');
	$package->run_command('rm -Rv schemas');
	$package->run_command('rm -Rv convertors');
}

// Build Main phpBB Release
$compress_programs = array(
//	'tar.gz'	=> 'tar -czf',
	'tar.bz2'	=> 'tar -cjf',
	'zip'		=> 'zip -r'
);

if (sizeof($package->old_packages))
{
	// Build Patch Files
	chdir($package->get('patch_directory'));

	foreach ($compress_programs as $extension => $compress_command)
	{
		$package->begin_status('Packaging phpBB Patch Files for ' . $extension);

		// Build Package
		$package->run_command($compress_command . ' ../release_files/' . $package->get('release_filename') . '-patch.' . $extension . ' *');

		// Build MD5 Sum
		$package->run_command('md5sum ../release_files/' . $package->get('release_filename') . '-patch.' . $extension . ' > ../release_files/' . $package->get('release_filename') . '-patch.' . $extension . '.md5');
	}

	// Build Files Package
	chdir($package->get('files_directory'));

	foreach ($compress_programs as $extension => $compress_command)
	{
		$package->begin_status('Packaging phpBB Files for ' . $extension);

		$package->run_command('mkdir ' . $package->get('files_directory') . '/release');
		$package->run_command('cp -Rp ' . $package->get('dest_dir') . '/docs ' . $package->get('files_directory') . '/release');
		$package->run_command('cp -Rp ' . $package->get('dest_dir') . '/install ' . $package->get('files_directory') . '/release');

		$package->run_command('rm -v ' . $package->get('files_directory') . '/release/install/install_install.php');
		$package->run_command('rm -v ' . $package->get('files_directory') . '/release/install/install_update.php');
		$package->run_command('rm -v ' . $package->get('files_directory') . '/release/install/install_convert.php');
		$package->run_command('rm -Rv ' . $package->get('files_directory') . '/release/install/schemas');
		$package->run_command('rm -Rv ' . $package->get('files_directory') . '/release/install/convertors');

		// Pack files
		foreach ($package->old_packages as $_package_name => $package_path)
		{
			chdir($package_path . $package->get('new_version_number'));
			$command = ($extension == 'zip') ? 'zip -r' : 'tar cf';
			$_ext = ($extension == 'zip') ? 'zip' : 'tar';
			$package->run_command("$command ../release/phpBB-$package_path" . $package->get('new_version_number') . ".$_ext *");
			chdir('..');
		}

		chdir('./release');
		$package->run_command("$compress_command ../../release_files/" . $package->get('release_filename') . '-files.' . $extension . ' *');
		// Build MD5 Sum
		$package->run_command('md5sum ../../release_files/' . $package->get('release_filename') . '-files.' . $extension . ' > ../../release_files/' . $package->get('release_filename') . '-files.' . $extension . '.md5');
		chdir('..');

		$package->run_command('rm -Rv ' . $package->get('files_directory') . '/release');
	}

	// Build Update Package
	foreach ($compress_programs as $extension => $compress_command)
	{
		chdir($package->get('update_directory'));

		$package->begin_status('Packaging phpBB Update for ' . $extension);

		$package->run_command('mkdir ' . $package->get('update_directory') . '/release');

		// Pack update files
		$packages = $package->old_packages;

		foreach ($packages as $_package_name => $package_path)
		{
			chdir($package_path . $package->get('new_version_number'));

			$package->run_command('rm -v install/install_install.php');
			$package->run_command('rm -v install/install_convert.php');
			$package->run_command('rm -v includes/utf/data/recode_cjk.php');
			$package->run_command('rm -Rv install/schemas');
			$package->run_command('rm -Rv install/convertors');

			$command = ($extension == 'zip') ? 'zip -r' : 'tar cf';
			$_ext = ($extension == 'zip') ? 'zip' : 'tar';
			$package->run_command("$command ../release/$package_path" . $package->get('new_version_number') . ".$_ext *");
			chdir('..');

			$last_version = $package_path . $package->get('new_version_number');

//			chdir('./release');
//			$package->run_command("$compress_command ../../release_files/" . $package->get('release_filename') . '-update.' . $extension . ' *');
//			chdir('..');

			chdir('./' . $last_version);
			// Copy last package over...
			$package->run_command('rm -v ../release_files/phpBB-' . $last_version . ".$extension");
			$package->run_command("$compress_command ../../release_files/phpBB-$last_version.$extension *");

			// Build MD5 Sum
			$package->run_command("md5sum ../../release_files/phpBB-$last_version.$extension > ../../release_files/phpBB-$last_version.$extension.md5");
			chdir('..');
		}

		$package->run_command('rm -Rv ' . $package->get('update_directory') . '/release');
	}

}

// Delete updater and convertor from main archive
chdir($package->get('dest_dir') . '/install');

// $package->run_command('rm -v database_update.php');
$package->run_command('rm -v install_update.php');

chdir($package->locations['package_dir']);
foreach ($compress_programs as $extension => $compress_command)
{
	$package->begin_status('Packaging phpBB for ' . $extension);
	$package->run_command('rm -v ./release_files/' . $package->get('release_filename') . ".{$extension}");

	// Build Package
	$package->run_command("$compress_command ./release_files/" . $package->get('release_filename') . '.' . $extension . ' ' . $package->get('package_name'));

	// Build MD5 Sum
	$package->run_command('md5sum ./release_files/' . $package->get('release_filename') . '.' . $extension . ' > ./release_files/' . $package->get('release_filename') . '.' . $extension . '.md5');
}

// Microsoft Web PI packaging
$package->begin_status('Packaging phpBB for Microsoft WebPI');
$file = './release_files/' . $package->get('release_filename') . '.webpi.zip';
$package->run_command('cp -p ./release_files/' . $package->get('release_filename') . ".zip $file");
$package->run_command('cd ./../webpi && ' . $compress_programs['zip'] . " ./../new_version/$file *");
$package->run_command("md5sum $file  > $file.md5");

// verify results
chdir($package->locations['root']);
$package->begin_status('********** Verifying packages **********');
$package->run_command('./compare.sh ' . $package->package_infos['release_filename']);

echo "Done.\n";
