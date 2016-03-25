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

class build_package
{
	var $package_infos;
	var $old_packages;
	var $versions;
	var $locations;

	// -c - context diff
	// -r - compare recursive
	// -N - Treat missing files as empty
	// -E - Ignore tab expansions
	// -Z - Ignore white space at line end.
	// -b - Ignore changes in the amount of white space.
	// -B - Ignore blank lines
	// -d - Try to find smaller set of changes
	var $diff_options = '-crNEBZbd';
	var $diff_options_long = '-x images -crNEB'; // -x fonts -x imageset //imageset not used here, because it includes the imageset.cfg file. ;)

	var $verbose = false;
	var $status_begun = false;
	var $num_dots = 0;

	function build_package($versions, $verbose = false)
	{
		$this->versions = $versions;
		$this->verbose = $verbose;

		// Get last two entries
		$_latest = $this->versions[sizeof($this->versions) - 1];
		$_before = $this->versions[sizeof($this->versions) - 2];

		$this->locations = array(
			'new_version'	=> dirname(dirname(__FILE__)) . '/phpBB/',
			'old_versions'	=> dirname(__FILE__) . '/old_versions/',
			'root'			=> dirname(__FILE__) . '/',
			'package_dir'	=> dirname(__FILE__) . '/new_version/'
		);

		$this->package_infos = array(
			'package_name'			=> 'phpBB3',
			'name_prefix'			=> 'phpbb',
			'simple_name'			=> 'release-' . $_latest,
			'new_version_number'	=> $_latest,
			'short_version_number'	=> str_replace('.', '', $_latest),
			'release_filename'		=> 'phpBB-' . $_latest,
			'last_version'			=> 'release-' . $_before,
			'last_version_number'	=> $_before,
		);

		$this->package_infos['dest_dir'] = $this->locations['package_dir'] . $this->package_infos['package_name'];
		$this->package_infos['diff_dir'] = $this->locations['old_versions'] . $this->package_infos['simple_name'];
		$this->package_infos['patch_directory'] = $this->locations['package_dir'] . 'patches';
		$this->package_infos['files_directory'] = $this->locations['package_dir'] . 'files';
		$this->package_infos['update_directory'] = $this->locations['package_dir'] . 'update';
		$this->package_infos['release_directory'] = $this->locations['package_dir'] . 'release_files';

		// Old packages always exclude the latest version. ;)
		$this->old_packages = array();

		foreach ($this->versions as $package_version)
		{
			if ($package_version == $_latest)
			{
				continue;
			}

			$this->old_packages['release-' . $package_version] = $package_version . '_to_';
		}
	}

	function get($var)
	{
		return $this->package_infos[$var];
	}

	function begin_status($headline)
	{
		if ($this->status_begun)
		{
			echo "\nDone.\n\n";
		}

		$this->num_dots = 0;

		echo $headline . "\n    ";

		$this->status_begun = true;
	}

	function run_command($command)
	{
		$result = trim(`$command`);

		if ($this->verbose)
		{
			echo "    command : " . getcwd() . '$ ' . $command . "\n";
			echo "    result  : " . $result . "\n";
		}
		else
		{
			if ($this->num_dots > 70)
			{
				echo "\n";
				$this->num_dots = 0;
			}
			echo '.';
			$this->num_dots++;
		}

		flush();
	}

	function create_directory($directory, $dir_struct)
	{
		if (!file_exists($directory))
		{
			$this->run_command("mkdir $directory");
		}

		if (is_array($dir_struct))
		{
			foreach ($dir_struct as $_dir => $_dir_struct)
			{
				$this->create_directory($directory . '/' . $_dir, $_dir_struct);
			}
		}
	}

	function collect_diff_files($diff_filename, $package_name)
	{
		$diff_result = $binary = array();
		$diff_contents = file($diff_filename);

		$special_diff_contents = array();

		foreach ($diff_contents as $num => $line)
		{
			$line = trim($line);

			if (!$line)
			{
				continue;
			}

			// Special diff content?
			if (strpos($line, 'diff ' . $this->diff_options . ' ') === 0 || strpos($line, '*** ') === 0 || strpos($line, '--- ') === 0 || (strpos($line, ' Exp $') !== false && strpos($line, '$Id:') !== false))
			{
				$special_diff_contents[] = $line;
			}
			else if (strpos($line, 'diff ' . $this->diff_options . ' ') === 0 || strpos($line, '*** ') === 0 || strpos($line, '--- ') === 0 || (strpos($line, ' Exp $') !== false && strpos($line, '$Id:') !== false) || (strpos($line, ' $') !== false && strpos($line, '$Id:') !== false))
			{
				$special_diff_contents[] = $line;
			}

			// Is diffing line?
			if (strstr($line, 'diff ' . $this->diff_options . ' '))
			{
				$next_line = $diff_contents[$num+1];
				if (strpos($next_line, '***') === 0)
				{
	//			*** phpbb208/admin/admin_board.php	Sat Jul 10 20:16:26 2004
					$next_line = explode("\t", $next_line);
					$next_line = trim($next_line[0]);
					$next_line = str_replace('*** ' . $package_name . '/', '', $next_line);
					$diff_result[] = $next_line;
				}
			}

			// Is binary?
			if (preg_match('/^Binary files ' . $package_name . '\/(.*) and [a-z0-9._-]+\/\1 differ/i', $line, $match))
			{
				$binary[] = trim($match[1]);
			}
		}

		// Now go through the list again and find out which files have how many changes...
		$num_changes = array();

	/*  [1070] => diff -crN phpbb200/includes/usercp_avatar.php phpbb2023/includes/usercp_avatar.php
		[1071] => *** phpbb200/includes/usercp_avatar.php	Sat Jul 10 20:16:13 2004
		[1072] => --- phpbb2023/includes/usercp_avatar.php	Wed Feb  6 22:28:04 2008
		[1073] => *** 6,12 ****
		[1074] => !  *   $Id$
		[1075] => --- 6,12 ----
		[1076] => *** 51,59 ****
		[1077] => --- 51,60 ----
		[1078] => *** 62,80 ****
		[1079] => --- 63,108 ----
		[1080] => *** 87,97 ****
	*/
		while (($line = array_shift($special_diff_contents)) !== NULL)
		{
			$line = trim($line);

			if (!$line)
			{
				continue;
			}

			// Is diffing line?
			if (strstr($line, 'diff ' . $this->diff_options . ' '))
			{
				$next_line = array_shift($special_diff_contents);
				if (strpos($next_line, '*** ') === 0)
				{
	//			*** phpbb208/admin/admin_board.php	Sat Jul 10 20:16:26 2004
					$next_line = explode("\t", $next_line);
					$next_line = trim($next_line[0]);
					$next_line = str_replace('*** ' . $package_name . '/', '', $next_line);

					$is_reached = false;
					$prev_line = '';

					while (!$is_reached)
					{
						$line = array_shift($special_diff_contents);

						if (strpos($line, 'diff ' . $this->diff_options) === 0 || empty($special_diff_contents))
						{
							$is_reached = true;
							array_unshift($special_diff_contents, $line);
							continue;
						}

						if (strpos($line, '*** ') === 0 && strpos($line, ' ****') !== false)
						{
							$is_comment = false;
							while (!(strpos($line, '--- ') === 0 && strpos($line, ' ----') !== false))
							{
								$line = array_shift($special_diff_contents);
								if (strpos($line, ' Exp $') !== false || strpos($line, '$Id:') !== false)
								{
									$is_comment = true;
								}
							}

							if (!$is_comment)
							{
								if (!isset($num_changes[$next_line]))
								{
									$num_changes[$next_line] = 1;
								}
								else
								{
									$num_changes[$next_line]++;
								}
							}
						}
					}
				}
			}
		}

		// Now remove those results not having changes
		$return = array();

		foreach ($diff_result as $key => $value)
		{
			if (isset($num_changes[$value]))
			{
				$return[] = $value;
			}
		}

		foreach ($binary as $value)
		{
			$return[] = $value;
		}

		$diff_result = $return;
		unset($return);
		unset($special_diff_contents);

		$result = array(
			'files'		=> array(),
			'binary'	=> array(),
			'all'		=> $diff_result,
		);

		$binary_extensions = array('gif', 'jpg', 'jpeg', 'png', 'ttf');

		// Split into file and binary
		foreach ($diff_result as $filename)
		{
			if (strpos($filename, '.') === false)
			{
				$result['files'][] = $filename;
				continue;
			}

			$extension = explode('.', $filename);
			$extension = array_pop($extension);

			if (in_array($extension, $binary_extensions))
			{
				$result['binary'][] = $filename;
			}
			else
			{
				$result['files'][] = $filename;
			}
		}

		return $result;
	}

	/**
	* Collect the list of the deleted files from a list of deleted files and folders.
	*
	* @param string $deleted_filename   The full path to a file containing the list of deleted files and directories
	* @param string $package_name       The name of the package
	* @return array
	*/
	public function collect_deleted_files($deleted_filename, $package_name)
	{
		$result = array();
		$file_contents = file($deleted_filename);

		foreach ($file_contents as $filename)
		{
			$filename = trim($filename);

			if (!$filename)
			{
				continue;
			}

			$filename = str_replace('Only in ' . $package_name, '', $filename);
			$filename = ltrim($filename, '/');

			if (substr($filename, 0, 1) == ':')
			{
				$replace = '';
			}
			else
			{
				$replace = '/';
			}

			$filename = str_replace(': ', $replace, $filename);

			if (is_dir("{$this->locations['old_versions']}{$package_name}/{$filename}"))
			{
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator(
						"{$this->locations['old_versions']}{$package_name}/{$filename}",
						\FilesystemIterator::UNIX_PATHS | \FilesystemIterator::SKIP_DOTS
					),
					\RecursiveIteratorIterator::LEAVES_ONLY
				);

				foreach ($iterator as $file_info)
				{
					$result[] = "{$filename}/{$iterator->getSubPathname()}";
				}
			}
			else
			{
				$result[] = $filename;
			}
		}

		return $result;
	}
}
