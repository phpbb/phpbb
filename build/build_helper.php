<?php
/**
*
* @package build
* @version $Id$
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

class build_package
{
	var $package_infos;
	var $old_packages;
	var $versions;
	var $locations;
	var $clean_directory_structure;
	var $files_to_copy;
	var $files_to_remove;
	var $remove_from_diff_structure;

	// -c - context diff
	// -r - compare recursive
	// -N - Treat missing files as empty
	// -E - Ignore tab expansions
	//		not used: -b - Ignore space changes.
	// -w - Ignore all whitespace
	// -B - Ignore blank lines
	// -d - Try to find smaller set of changes
	var $diff_options = '-crNEBwd';
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
			'simple_name'			=> 'phpbb' . str_replace('.', '', $_latest),
			'new_version_number'	=> $_latest,
			'short_version_number'	=> str_replace('.', '', $_latest),
			'release_filename'		=> 'phpBB-' . $_latest,
			'last_version'			=> 'phpbb' . str_replace('.', '', $_before),
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

			$this->old_packages['phpbb' . str_replace('.', '', $package_version)] = $package_version . '_to_';
		}

		// We need to make sure this is up to date with the latest version
		$this->clean_directory_structure = array(
			'adm'		=> array(
				'images'	=> '',
				'style'		=> '',
			),
			'cache'		=> '',
			'docs'		=> '',
			'download'	=> '',
			'files'		=> '',
			'images'	=> array(
				'avatars'	=> array(
					'gallery'	=> '',
					'upload'	=> '',
				),
				'icons'		=> array(
					'misc'	=> '',
					'smile'	=> '',
				),
				'ranks'			=> '',
				'smilies'		=> '',
				'upload_icons'	=> '',
			),
			'includes'		=> array(
				'acm'		=> '',
				'acp'		=> array(
					'info'	=> '',
				),
				'auth'		=> '',
				'captcha'	=> array(
					'plugins'	=> '',
				),
				'diff'		=> '',
				'db'		=> '',
				'hooks'		=> '',
				'mcp'		=> array(
					'info'	=> '',
				),
				'questionnaire' => '',
				'search'	=> '',
				'ucp'		=> array(
					'info'	=> '',
				),
				'utf'		=> array(
					'data'	=> '',
				),
			),
			'install'		=> array(
				'convertors'=> '',
				'schemas'	=> '',
//				'data'		=> '',
			),
			'language'		=> array(
				'en'		=> array(
					'acp'		=> '',
					'email'		=> '',
					'mods'		=> '',
				),
			),
			'store'			=> '',
			'styles'		=> array(
				'subsilver2'	=> array(
					'imageset'		=> array(
						'en'	=> '',
					),
					'template'	=> '',
					'theme'		=> array(
						'images'	=> '',
					),
				),
				'prosilver'	=> array(
					'imageset'		=> array(
						'en'	=> '',
					),
					'template'	=> '',
					'theme'		=> array(
						'images'	=> '',
					),
				),
			),
		);

		// Files to remove (not include within package)
		$this->files_to_remove = array(); //array('includes/utf/data/recode_cjk.php');

		// Files within the main directory to copy - do not include config.php
		$this->files_to_copy = array(
			'.htaccess', 'common.php', 'cron.php', 'faq.php', 'feed.php', 'index.php', 'mcp.php', 'memberlist.php', 'posting.php', 'report.php',
			'search.php', 'style.php', 'ucp.php', 'viewforum.php', 'viewonline.php', 'viewtopic.php'
		);

		// These files/directories will be removed and not used for creating the patch files
		$this->remove_from_diff_structure = array(
			'config.php', 'cache', 'docs', 'files', 'install', 'store', 'develop'
		);

		// Writeable directories
		$this->writeable = array('cache', 'store', 'images/avatars/upload', 'files');

		// Fill the rest of the files_to_copy array
		foreach ($this->clean_directory_structure as $cur_dir => $dir_struct)
		{
			$this->_fill_files_to_copy($this->locations['new_version'] . $cur_dir, $cur_dir, $dir_struct);
		}
	}

	function get($var)
	{
		return $this->package_infos[$var];
	}

	function _fill_files_to_copy($directory, $cur_dir, $dir_struct)
	{
		$dh = opendir($directory);

		while ($file = readdir($dh))
		{
			if (is_file($directory . '/' . $file) && $file != '.' && $file != '..')
			{
				$_loc = str_replace($this->locations['new_version'], '', $directory . '/' . $file);

				if (in_array($_loc, $this->files_to_remove))
				{
					continue;
				}

				$this->files_to_copy[] = $cur_dir . '/' . $file;
			}
		}
		closedir($dh);

		if (is_array($dir_struct))
		{
			foreach ($dir_struct as $_cur_dir => $_dir_struct)
			{
				$this->_fill_files_to_copy($directory . '/' . $_cur_dir, $cur_dir . '/' . $_cur_dir, $_dir_struct);
			}
		}
	}

	function adjust_permissions($directory)
	{
		$dh = opendir($directory);

		while ($file = readdir($dh))
		{
			if ($file == '.' || $file == '..' || $file == '.svn')
			{
				continue;
			}

			// If file, then 644
			if (is_file($directory . '/' . $file))
			{
				chmod($directory . '/' . $file, 0644);
			}
			else if (is_dir($directory . '/' . $file))
			{
				$_loc = str_replace($this->package_infos['dest_dir'] . '/', '', $directory . '/' . $file);

				// If directory is within the writeable chmod to 777, else 755
				$mode = (in_array($_loc, $this->writeable)) ? 0777 : 0755;
				chmod($directory . '/' . $file, $mode);

				// Now traverse to the directory
				$this->adjust_permissions($directory . '/' . $file);
			}
		}
		closedir($dh);
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
			if (preg_match('/^Binary files ' . $package_name . '\/(.*) and [a-z0-9_-]+\/\1 differ/i', $line, $match))
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
}
