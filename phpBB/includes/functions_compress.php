<?

// Zip creation class from phpMyAdmin 2.3.0 © Tobias Ratschiller, Olivier Müller, Loïc Chapeaux, Marc Delisle
// http://www.phpmyadmin.net/
//
// Modified extensively by psoTFX, © phpBB Group, 2003
//
// Based on work by Eric Mueller and Denis125
// Official ZIP file format: http://www.pkware.com/appnote.txt

// TODO
// Extract files 
class archive_zip
{
	var $datasec = array();
	var $ctrl_dir = array();
	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";

	var $old_offset = 0;
	var $datasec_len = 0;

	var $fp = 0;

	function archive_zip($mode, $dst)
	{
		return $this->fp = @fopen($phpbb_root_path . $dst, $mode . 'b');
	}

	function unix_to_dos_time($time)
	{
		$timearray = (!$time) ? getdate() : getdate($time);

		if ($timearray['year'] < 1980)
		{
			$timearray['year'] = 1980;
			$timearray['mon'] = $timearray['mday'] = 1;
			$timearray['hours'] = $timearray['minutes'] = $timearray['seconds'] = 0;
		}

		return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	}

	function add_file($src, $src_prefix = '')
	{
		global $phpbb_root_path;

		// Remove prefix from src path 
		$src_prefix = ($src_prefix) ? preg_replace('#^(' . preg_quote($src_prefix) . ')#', '', $src) : $src;

		// Clean up path, remove initial / if present, add ending / if not present
		$src_prefix = (strpos($src_prefix, '/') === 0) ? substr($src_prefix, 1) : $src_prefix;
		$src_prefix = (strrpos($src_prefix, '/') != strlen($src_prefix) - 1) ? (($src_prefix != '') ? $src_prefix . '/' : '') : $src_prefix;

		if (is_file($phpbb_root_path . $src))
		{
			if (!($fp = @fopen($phpbb_root_path . $src, 'rb')))
			{
				return false;
			}

			$data = fread($fp, filesize($phpbb_root_path . $src));
			fclose($fp);

			$this->data($src_prefix, $data);
		}
		else if (is_dir($phpbb_root_path . $src))
		{
			$filelist = filelist($phpbb_root_path . $src, '', '*');
			ksort($filelist);

			if ($src_prefix)
			{
				$this->data($src_prefix, '', filemtime($src_prefix), true);
			}

			foreach ($filelist as $path => $file_ary)
			{
				if ($path)
				{
					// Same as for src_prefix
					$path = (strpos($path, '/') === 0) ? substr($path, 1) : $path;
					$path = (strrpos($path, '/') != strlen($path) - 1) ? $path . '/' : $path;

					$this->data($src_prefix . $path, '', filemtime($src_prefix . $path), true);
				}

				foreach ($file_ary as $file)
				{
					$this->data($src_prefix . $path . $file, implode('', file($phpbb_root_path . $src . $path . $file)), filemtime($phpbb_root_path . $src . $path . $file), false);
				}
			}

		}
		return true;
	}

	function add_data($src, $name)
	{
		$this->data($name, $src);
		return true;
	}

	function extract($dst)
	{
	}

	function close()
	{
		// Write out central file directory and footer
		fwrite($this->fp, $this->file());
		fclose($this->fp);
	}

	function data($name, $data, $mtime = false, $is_dir = false)
	{
		$name = str_replace('\\', '/', $name);

		$dtime = dechex($this->unix_to_dos_time($mtime));
		$hexdtime = '\x' . $dtime[6] . $dtime[7] . '\x' . $dtime[4] . $dtime[5] . '\x' . $dtime[2] . $dtime[3] . '\x' . $dtime[0] . $dtime[1];
		eval('$hexdtime = "' . $hexdtime . '";');

		$unc_len = strlen($data);
		$crc = crc32($data);
		$zdata = gzcompress($data);
		$zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
		$c_len = strlen($zdata);
		unset($data);

		$fr = "\x50\x4b\x03\x04";
		$fr .= "\x14\x00";	// ver needed to extract
		$fr .= "\x00\x00";	// gen purpose bit flag
		$fr .= "\x08\x00";	// compression method
		$fr .= $hexdtime;				// last mod time and date
		$fr .= pack('V', $crc);			// crc32
		$fr .= pack('V', $c_len);		// compressed filesize
		$fr .= pack('V', $unc_len);		// uncompressed filesize
		$fr .= pack('v', strlen($name));// length of filename
		$fr .= pack('v', 0);			// extra field length
		$fr .= $name;
		$fr .= $zdata;
		unset($zdata);
		$fr .= pack('V', $crc);
		$fr .= pack('V', $c_len);	// compressed filesize
		$fr .= pack('V', $unc_len);	// uncompressed filesize

		$this->datasec_len += strlen($fr);

		// Add data to file ... by writing data out incrementally we
		// save some memory
		fwrite($this->fp, $fr);
		unset($fr);

		// Are we a file or a directory? Set archive for file
		$attrib = ($is_dir) ? 0x41FF0010 : 32;

		$cdrec = "\x50\x4b\x01\x02";
		$cdrec .= "\x00\x00";                // version made by
		$cdrec .= "\x14\x00";                // version needed to extract
		$cdrec .= "\x00\x00";                // gen purpose bit flag
		$cdrec .= "\x08\x00";                // compression method
		$cdrec .= $hexdtime;                 // last mod time & date
		$cdrec .= pack('V', $crc);           // crc32
		$cdrec .= pack('V', $c_len);         // compressed filesize
		$cdrec .= pack('V', $unc_len);       // uncompressed filesize
		$cdrec .= pack('v', strlen($name)); // length of filename
		$cdrec .= pack('v', 0);             // extra field length
		$cdrec .= pack('v', 0);             // file comment length
		$cdrec .= pack('v', 0);             // disk number start
		$cdrec .= pack('v', 0);             // internal file attributes
		$cdrec .= pack('V', $attrib);       // external file attributes
		$cdrec .= pack('V', $this->old_offset); // relative offset of local header
		$cdrec .= $name;

		// Save to central directory
		$this->ctrl_dir[] = $cdrec;

		$this->old_offset = $this->datasec_len;
	}

	function file()
	{
		$ctrldir = implode('', $this->ctrl_dir);

		return $ctrldir . $this->eof_ctrl_dir .
			pack('v', sizeof($this->ctrl_dir)) .	// total # of entries "on this disk"
			pack('v', sizeof($this->ctrl_dir)) .	// total # of entries overall
			pack('V', strlen($ctrldir)) .			// size of central dir
			pack('V', $this->datasec_len) .			// offset to start of central dir
			"\x00\x00";								// .zip file comment length
	}
}

// --------------------------------------------------------------------------------
// PhpConcept Library - Tar Module 1.3
// --------------------------------------------------------------------------------
// License GNU/GPL - Vincent Blavet - August 2001
// http://www.phpconcept.net
// --------------------------------------------------------------------------------
//
// Modified extensively by psoTFX, © phpBB Group, 2003
class archive_tar
{
	var $fopen = '';
	var $fclose = '';
	var $fread = '';
	var $fwrite = '';
	var $feof = '';
	var $fseek = '';

	function archive_tar($dst, $mode = 'gz')
	{
		$this->fopen = ($mode == 'gz' && extension_loaded('zlib')) ? 'gzopen' : 'fopen';
		$this->fclose = ($mode == 'gz' && extension_loaded('zlib')) ? 'gzclose' : 'fclose';
		$this->fread = ($mode == 'gz' && extension_loaded('zlib')) ? 'gzread' : 'fread';
		$this->fwrite = ($mode == 'gz' && extension_loaded('zlib')) ? 'gzwrite' : 'fwrite';
		$this->feof = ($mode == 'gz' && extension_loaded('zlib')) ? 'gzeof' : 'feof';
		$this->fseek = ($mode == 'gz' && extension_loaded('zlib')) ? 'gzseek' : 'fseek';
	}
}






function PclTarCreate($p_tarname, $p_filelist = '', $p_mode = '', $p_add_dir = '', $p_remove_dir = '')
{
	$v_result = 1;

	// Look for default mode
	if ($p_mode == '' || ($p_mode != 'tar' && $p_mode != 'tgz'))
	{
		// Extract the tar format from the extension
		if (($p_mode = PclTarHandleExtension($p_tarname)) == '')
		{
			trigger_error(PclErrorString(), E_USER_ERROR);
		}
	}

	if (is_array($p_filelist))
	{
		// Look if the $p_filelist is really an array
		$v_result = PclTarHandleCreate($p_tarname, $p_filelist, $p_mode, $p_add_dir, $p_remove_dir);
	}
	else if (is_string($p_filelist))
	{
		// Look if the $p_filelist is a string. Create a list with the elements from the string
		$v_list = explode(' ', $p_filelist);

		// Call the create fct
		$v_result = PclTarHandleCreate($p_tarname, $v_list, $p_mode, $p_add_dir, $p_remove_dir);
	}

	else
	{
		// Invalid variable
		$v_result = -3;
	}
	
	return $v_result;
}

function PclTarAdd($p_tarname, $p_filelist)
{
	$v_result = 1;
	$v_list_detail = array();

	// Extract the tar format from the extension
	if (($p_mode = PclTarHandleExtension($p_tarname)) == '')
	{
		trigger_error(PclErrorString(), E_USER_ERROR);
	}

	// Look if the $p_filelist is really an array
	if (is_array($p_filelist))
	{
		$v_result = PclTarHandleAppend($p_tarname, $p_filelist, $p_mode, $v_list_detail, "", "");
	}
	else if (is_string($p_filelist))
	{
		// Look if the $p_filelist is a string. Create a list with the elements from the string
		$v_list = explode(" ", $p_filelist);

		$v_result = PclTarHandleAppend($p_tarname, $v_list, $p_mode, $v_list_detail, "", "");
	}
	else
	{
		$v_result = -3;
	}
	unset($v_list_detail);

	return $v_result;
}

function PclTarAddList($p_tarname, $p_filelist, $p_add_dir = '', $p_remove_dir = '', $p_mode = '')
{
	$v_result = 1;
	$p_list_detail = array();

	// Extract the tar format from the extension
	if ($p_mode == '' || ($p_mode != 'tar' && $p_mode != 'tgz'))
	{
		if (($p_mode = PclTarHandleExtension($p_tarname)) == '')
		{
			trigger_error(PclErrorString(), E_USER_ERROR);
		}
	}

	// Look if the $p_filelist is really an array
	if (is_array($p_filelist))
	{
		$v_result = PclTarHandleAppend($p_tarname, $p_filelist, $p_mode, $p_list_detail, $p_add_dir, $p_remove_dir);
	}
	else if (is_string($p_filelist))
	{
		// Look if the $p_filelist is a string
		$v_list = explode(' ', $p_filelist);
		$v_result = PclTarHandleAppend($p_tarname, $v_list, $p_mode, $p_list_detail, $p_add_dir, $p_remove_dir);
	}
	else
	{
		// Invalid variable
		$v_result = -3;
	}

	return ($v_result != 1) ? 0 : $p_list_detail;
}

function PclTarList($p_tarname, $p_mode = '')
{
	$v_result = 1;

	// Extract the tar format from the extension
	if ($p_mode == '' || ($p_mode != 'tar' && $p_mode != 'tgz'))
	{
		if (($p_mode = PclTarHandleExtension($p_tarname)) == '')
		{
			trigger_error(PclErrorString(), E_USER_ERROR);
		}
	}

	// Call the extracting fct
	$p_list = array();
	if (($v_result = PclTarHandleExtract($p_tarname, 0, $p_list, 'list', '', $p_mode, '')) != 1)
	{
		unset($p_list);
		return 0;
	}

	return $p_list;
}

function PclTarExtract($p_tarname, $p_path="./", $p_remove_path = '', $p_mode = '')
{
	$v_result = 1;

	// Extract the tar format from the extension
	if ($p_mode == '' || ($p_mode != 'tar' && $p_mode != 'tgz'))
	{
		if (($p_mode = PclTarHandleExtension($p_tarname)) == '')
		{
			trigger_error(PclErrorString(), E_USER_ERROR);
		}
	}

	// Call the extracting fct
	if (($v_result = PclTarHandleExtract($p_tarname, 0, &$p_list, 'complete', $p_path, $v_tar_mode, $p_remove_path)) != 1)
	{
		return 0;
	}
	 
	return $p_list;
}

function PclTarHandleCreate($p_tarname, $p_list, $p_mode, $p_add_dir = '', $p_remove_dir = '')
{
	$v_result = 1;
	$v_list_detail = array();

	// Extract the tar format from the extension
	if ($p_mode == '' || ($p_mode != 'tar' && $p_mode != 'tgz'))
	{
		if (($p_mode = PclTarHandleExtension($p_tarname)) == '')
		{
			trigger_error(PclErrorString(), E_USER_ERROR);
		}
	}

$fopen = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzopen' : 'fopen';
$fclose = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzclose' : 'fclose';
$fread = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzread' : 'fread';
$fwrite = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzwrite' : 'fwrite';
$feof = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzeof' : 'feof';
$fseek = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzseek' : 'fseek';


	// Open the tar file
	if (!($p_tar = $fopen($p_tarname, "wb")))
	{
		trigger_error(PclErrorString(), E_USER_ERROR);
	}

	// Call the adding fct inside the tar
	if (($v_result = PclTarHandleAddList($p_tar, $p_list, $p_mode, $v_list_detail, $p_add_dir, $p_remove_dir)) == 1)
	{
		// Call the footer of the tar archive
		$v_result = PclTarHandleFooter($p_tar, $p_mode);
	}

	// Close the tarfile
	$fclose($p_tar);
	
	return $v_result;
}

function PclTarHandleAppend($p_tarname, $p_list, $p_mode, &$p_list_detail, $p_add_dir, $p_remove_dir)
{
	$v_result = 1;

	clearstatcache();
	// Check the file size
	if (!is_file($p_tarname) || ((($v_size = filesize($p_tarname)) % 512) && $p_mode == 'tar'))
	{
		trigger_error('Archive missing or corrupt', E_USER_ERROR);
	}

$fopen = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzopen' : 'fopen';
$fclose = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzclose' : 'fclose';
$fread = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzread' : 'fread';
$fwrite = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzwrite' : 'fwrite';
$feof = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzeof' : 'feof';
$fseek = ($p_tar == 'tar.gz' && extension_loaded('zlib')) ? 'gzseek' : 'fseek';

	if (($p_tar = @$fopen($p_tarname, 'rb')) == 0)
	{
		trigger_error(PclErrorString(), E_USER_ERROR);
	}

	// Open a temporary file in write mode
	$v_temp_tarname = uniqid('pcltar-') . '.tmp';
		
	if (($v_temp_tar = @$fopen($v_temp_tarname, 'wb')) == 0)
	{
		$fclose($p_tar);
		trigger_error(PclErrorString(), E_USER_ERROR);
	}

	// Read new 512 block and write the already read
	while ($v_buffer = $fread($p_tar, 512))
	{
		// Write the already read block
		$v_binary_data = pack("a512", "$v_buffer");
		$fwrite($v_temp_tar, $v_binary_data);
	}

	// Call the adding fct inside the tar
	if (($v_result = PclTarHandleAddList($v_temp_tar, $p_list, $p_mode, $p_list_detail, $p_add_dir, $p_remove_dir)) == 1)
	{
		// Call the footer of the tar archive
		$v_result = PclTarHandleFooter($v_temp_tar, $p_mode);
	}

		// Close the files
	$fclose($p_tar);
	$fclose($v_temp_tar);

	// Unlink tar file
	if (!@unlink($p_tarname))
	{
		trigger_error('Could not unlink file :: ' . $p_tarname, E_USER_ERROR);
	}

	// Rename tar file
	if (!@rename($v_temp_tarname, $p_tarname))
	{
		trigger_error('Could not unlink file :: ' . $v_temp_tarname . ' / ' . $p_tarname, E_USER_ERROR);
	}
	
	return $v_result;
}

function PclTarHandleAddList($p_tar, $p_list, $p_mode, &$p_list_detail, $p_add_dir, $p_remove_dir)
{
	
	$v_result = 1;
	$v_header = array();

	// Recuperate the current number of elt in list
	$v_nb = sizeof($p_list_detail);

	// Check the parameters
	if ($p_tar == 0)
	{
		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Check the arguments
	if (sizeof($p_list) == 0)
	{
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Loop on the files
	for ($j=0; ($j<count($p_list)) && ($v_result==1); $j++)
	{
		// Recuperate the filename
		$p_filename = $p_list[$j];

		

		// Skip empty file names
		if ($p_filename == '')
		{
		
		continue;
		}

		// Check the filename
		if (!file_exists($p_filename))
		{
		// Error log
		
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
		}

		// Check the path length
		if (strlen($p_filename) > 99)
		{
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
		}

		// Add the file
		if (($v_result = PclTarHandleAddFile($p_tar, $p_filename, $p_mode, $v_header, $p_add_dir, $p_remove_dir)) != 1)
		{
		// Return status
		
		return $v_result;
		}

		// Store the file infos
		$p_list_detail[$v_nb++] = $v_header;

		// Look for directory
		if (is_dir($p_filename))
		{
		

		// Look for path
		if ($p_filename != ".")
			$v_path = $p_filename."/";
		else
			$v_path = "";

		// Read the directory for files and sub-directories
		$p_hdir = opendir($p_filename);
		$p_hitem = readdir($p_hdir); // '.' directory
		$p_hitem = readdir($p_hdir); // '..' directory
		while ($p_hitem = readdir($p_hdir))
		{
			// Look for a file
			if (is_file($v_path.$p_hitem))
			{
			

			// Add the file
			if (($v_result = PclTarHandleAddFile($p_tar, $v_path.$p_hitem, $p_mode, $v_header, $p_add_dir, $p_remove_dir)) != 1)
			{
				// Return status
				
				return $v_result;
			}

			// Store the file infos
			$p_list_detail[$v_nb++] = $v_header;
			}

			// Recursive call to PclTarHandleAddFile()
			else
			{
			

			// Need an array as parameter
			$p_temp_list[0] = $v_path.$p_hitem;
			$v_result = PclTarHandleAddList($p_tar, $p_temp_list, $p_mode, $p_list_detail, $p_add_dir, $p_remove_dir);
			}
		}

		// Free memory for the recursive loop
		unset($p_temp_list);
		unset($p_hdir);
		unset($p_hitem);
		}
	}

	// Return
	
	return $v_result;
	}
	// --------------------------------------------------------------------------------

	// --------------------------------------------------------------------------------
	// Function : PclTarHandleAddFile()
	// Description :
	// Parameters :
	// Return Values :
	// --------------------------------------------------------------------------------
	function PclTarHandleAddFile($p_tar, $p_filename, $p_mode, &$p_header, $p_add_dir, $p_remove_dir)
	{
	
	$v_result = 1;

	// Check the parameters
	if ($p_tar == 0)
	{
		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Skip empty file names
	if ($p_filename == '')
	{
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Calculate the stored filename
	$v_stored_filename = $p_filename;
	if ($p_remove_dir != "")
	{
		if (substr($p_remove_dir, -1) != '/')
		$p_remove_dir .= "/";

		if ((substr($p_filename, 0, 2) == "./") || (substr($p_remove_dir, 0, 2) == "./"))
		{
		if ((substr($p_filename, 0, 2) == "./") && (substr($p_remove_dir, 0, 2) != "./"))
			$p_remove_dir = "./".$p_remove_dir;
		if ((substr($p_filename, 0, 2) != "./") && (substr($p_remove_dir, 0, 2) == "./"))
			$p_remove_dir = substr($p_remove_dir, 2);
		}

		if (substr($p_filename, 0, strlen($p_remove_dir)) == $p_remove_dir)
		{
		$v_stored_filename = substr($p_filename, strlen($p_remove_dir));
		
		}
	}
	if ($p_add_dir != "")
	{
		if (substr($p_add_dir, -1) == "/")
		$v_stored_filename = $p_add_dir.$v_stored_filename;
		else
		$v_stored_filename = $p_add_dir."/".$v_stored_filename;
		
	}

	// Check the path length
	if (strlen($v_stored_filename) > 99)
	{
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Look for a file
	if (is_file($p_filename))
	{
		// Open the source file
		if (($v_file = fopen($p_filename, "rb")) == 0)
		{
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
		}

		// Call the header generation
		if (($v_result = PclTarHandleHeader($p_tar, $p_filename, $p_mode, $p_header, $v_stored_filename)) != 1)
		{
		// Return status
		
		return $v_result;
		}

		// Read the file by 512 octets blocks
		$i=0;
		while (($v_buffer = fread($v_file, 512)) != "")
		{
		$v_binary_data = pack("a512", "$v_buffer");
		if ($p_mode == "tar")
			fputs($p_tar, $v_binary_data);
		else
			gzputs($p_tar, $v_binary_data);
		$i++;
		}
		

		// Close the file
		fclose($v_file);
	}

	// Look for a directory
	else
	{
		// Call the header generation
		if (($v_result = PclTarHandleHeader($p_tar, $p_filename, $p_mode, $p_header, $v_stored_filename)) != 1)
		{
		// Return status
		
		return $v_result;
		}
	}

	// Return
	
	return $v_result;
	}
	// --------------------------------------------------------------------------------

	// --------------------------------------------------------------------------------
	// Function : PclTarHandleHeader()
	// Description :
	//	 This function creates in the TAR $p_tar, the TAR header for the file
	//	 $p_filename.
	//
	//	 1. The informations needed to compose the header are recuperated and formatted
	//	 2. Two binary strings are composed for the first part of the header, before
	//			and after checksum field.
	//	 3. The checksum is calculated from the two binary strings
	//	 4. The header is write in the tar file (first binary string, binary string
	//			for checksum and last binary string).
	// Parameters :
	//	 $p_tar : a valid file descriptor, opened in write mode,
	//	 $p_filename : The name of the file the header is for,
	//	 $p_mode : The mode of the archive ("tar" or "tgz").
	//	 $p_header : A pointer to a array where will be set the file properties
	// Return Values :
	// --------------------------------------------------------------------------------
	function PclTarHandleHeader($p_tar, $p_filename, $p_mode, &$p_header, $p_stored_filename)
	{
	
	$v_result = 1;

	// Check the parameters
	if (($p_tar == 0) || ($p_filename == ''))
	{
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Filename (reduce the path of stored name)
	if ($p_stored_filename == '')
		$p_stored_filename = $p_filename;
	$v_reduce_filename = PclTarHandlePathReduction($p_stored_filename);
	

	// Get file info
	$v_info = stat($p_filename);
	$v_uid = sprintf("%6s ", DecOct($v_info[4]));
	$v_gid = sprintf("%6s ", DecOct($v_info[5]));
	
	$v_perms = sprintf("%6s ", DecOct(fileperms($p_filename)));
	

	// File mtime
	$v_mtime_data = filemtime($p_filename);
	
	$v_mtime = sprintf("%11s", DecOct($v_mtime_data));

	// File typeflag
	// '0' or '\0' is the code for regular file
	// '5' is directory
	if (is_dir($p_filename))
	{
		$v_typeflag = "5";
		$v_size = 0;
	}
	else
	{
		$v_typeflag = "";

		// Get the file size
		clearstatcache();
		$v_size = filesize($p_filename);
	}

	
	$v_size = sprintf("%11s ", DecOct($v_size));

	

	// Linkname
	$v_linkname = "";

	// Magic
	$v_magic = "";

	// Version
	$v_version = "";

	// uname
	$v_uname = "";

	// gname
	$v_gname = "";

	// devmajor
	$v_devmajor = "";

	// devminor
	$v_devminor = "";

	// prefix
	$v_prefix = "";

	// Compose the binary string of the header in two parts arround the checksum position
	$v_binary_data_first = pack("a100a8a8a8a12A12", $v_reduce_filename, $v_perms, $v_uid, $v_gid, $v_size, $v_mtime);
	$v_binary_data_last = pack("a1a100a6a2a32a32a8a8a155a12", $v_typeflag, $v_linkname, $v_magic, $v_version, $v_uname, $v_gname, $v_devmajor, $v_devminor, $v_prefix, "");

	// Calculate the checksum
	$v_checksum = 0;
	// ..... First part of the header
	for ($i=0; $i<148; $i++)
	{
		$v_checksum += ord(substr($v_binary_data_first,$i,1));
	}
	// ..... Ignore the checksum value and replace it by ' ' (space)
	for ($i=148; $i<156; $i++)
	{
		$v_checksum += ord(' ');
	}
	// ..... Last part of the header
	for ($i=156, $j=0; $i<512; $i++, $j++)
	{
		$v_checksum += ord(substr($v_binary_data_last,$j,1));
	}
	

	// Write the first 148 bytes of the header in the archive
	if ($p_mode == "tar")
		fputs($p_tar, $v_binary_data_first, 148);
	else
		gzputs($p_tar, $v_binary_data_first, 148);

	// Write the calculated checksum
	$v_checksum = sprintf("%6s ", DecOct($v_checksum));
	$v_binary_data = pack("a8", $v_checksum);
	if ($p_mode == "tar")
		fputs($p_tar, $v_binary_data, 8);
	else
		gzputs($p_tar, $v_binary_data, 8);

	// Write the last 356 bytes of the header in the archive
	if ($p_mode == "tar")
		fputs($p_tar, $v_binary_data_last, 356);
	else
		gzputs($p_tar, $v_binary_data_last, 356);

	// Set the properties in the header "structure"
	$p_header['filename'] = $v_reduce_filename;
	$p_header['mode'] = $v_perms;
	$p_header['uid'] = $v_uid;
	$p_header['gid'] = $v_gid;
	$p_header['size'] = $v_size;
	$p_header['mtime'] = $v_mtime;
	$p_header['typeflag'] = $v_typeflag;
	$p_header['status'] = "added";

	// Return
	
	return $v_result;
	}
	// --------------------------------------------------------------------------------

function PclTarHandleFooter($p_tar, $p_mode)
{
	$v_result = 1;

	// Write the last 0 filled block for end of archive
	$v_binary_data = pack("a512", "");
	if ($p_mode == "tar")
		fputs($p_tar, $v_binary_data);
	else
		gzputs($p_tar, $v_binary_data);

	// Return
	
	return $v_result;
	}

	// --------------------------------------------------------------------------------
	// Function : PclTarHandleExtract()
	// Description :
	// Parameters :
	//	 $p_tarname : Filename of the tar (or tgz) archive
	//	 $p_file_list : An array which contains the list of files to extract, this
	//									array may be empty when $p_mode is 'complete'
	//	 $p_list_detail : An array where will be placed the properties of	each extracted/listed file
	//	 $p_mode : 'complete' will extract all files from the archive,
	//						 'partial' will look for files in $p_file_list
	//						 'list' will only list the files from the archive without any extract
	//	 $p_path : Path to add while writing the extracted files
	//	 $p_tar_mode : 'tar' for GNU TAR archive, 'tgz' for compressed archive
	//	 $p_remove_path : Path to remove (from the file memorized path) while writing the
	//										extracted files. If the path does not match the file path,
	//										the file is extracted with its memorized path.
	//										$p_remove_path does not apply to 'list' mode.
	//										$p_path and $p_remove_path are commulative.
	// Return Values :
	// --------------------------------------------------------------------------------
function PclTarHandleExtract($p_tarname, $p_file_list, &$p_list_detail, $p_mode, $p_path, $p_tar_mode, $p_remove_path)
{
	
	$v_result = 1;
	$v_nb = 0;
	$v_extract_all = TRUE;
	$v_listing = FALSE;

	// Check the path
	if (($p_path == '') || ((substr($p_path, 0, 1) != "/") && (substr($p_path, 0, 3) != "../")))
		$p_path = "./".$p_path;

	// Look for path to remove format (should end by /)
	if (($p_remove_path != "") && (substr($p_remove_path, -1) != '/'))
	{
		$p_remove_path .= '/';
	}
	$p_remove_path_size = strlen($p_remove_path);

	// Study the mode
	switch ($p_mode) {
		case "complete" :
		// Flag extract of all files
		$v_extract_all = TRUE;
		$v_listing = FALSE;
		break;
		case "partial" :
			// Flag extract of specific files
			$v_extract_all = FALSE;
			$v_listing = FALSE;
		break;
		case "list" :
			// Flag list of all files
			$v_extract_all = FALSE;
			$v_listing = TRUE;
		break;
		default :
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Open the tar file
	if ($p_tar_mode == "tar")
	{
		
		$v_tar = fopen($p_tarname, "rb");
	}
	else
	{
		
		$v_tar = @gzopen($p_tarname, "rb");
	}

	// Check that the archive is open
	if ($v_tar == 0)
	{
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Read the blocks
	While (!($v_end_of_file = ($p_tar_mode == "tar"?feof($v_tar):gzeof($v_tar))))
	{
		

		// Clear cache of file infos
		clearstatcache();

		// Reset extract tag
		$v_extract_file = FALSE;
		$v_extraction_stopped = 0;

		// Read the 512 bytes header
		if ($p_tar_mode == "tar")
		$v_binary_data = fread($v_tar, 512);
		else
		$v_binary_data = gzread($v_tar, 512);

		// Read the header properties
		if (($v_result = PclTarHandleReadHeader($v_binary_data, $v_header)) != 1)
		{
		// Close the archive file
		if ($p_tar_mode == "tar")
			fclose($v_tar);
		else
			gzclose($v_tar);

		// Return
		
		return $v_result;
		}

		// Look for empty blocks to skip
		if ($v_header['filename'] == '')
		{
		
		continue;
		}

		

		// Look for partial extract
		if ((!$v_extract_all) && (is_array($p_file_list)))
		{
		

		// By default no unzip if the file is not found
		$v_extract_file = FALSE;

		// Look into the file list
		for ($i=0; $i<sizeof($p_file_list); $i++)
		{
			

			// Look if it is a directory
			if (substr($p_file_list[$i], -1) == "/")
			{
			

			// Look if the directory is in the filename path
			if ((strlen($v_header['filename']) > strlen($p_file_list[$i])) && (substr($v_header['filename'], 0, strlen($p_file_list[$i])) == $p_file_list[$i]))
			{
				// The file is in the directory, so extract it
				
				$v_extract_file = TRUE;

				// End of loop
				break;
			}
			}

			// It is a file, so compare the file names
			else if ($p_file_list[$i] == $v_header['filename'])
			{
			// File found
			
			$v_extract_file = TRUE;

			// End of loop
			break;
			}
		}

		// Trace
		if (!$v_extract_file)
		{
			
		}
		}
		else
		{
		// All files need to be extracted
		$v_extract_file = TRUE;
		}

		// Look if this file need to be extracted
		if (($v_extract_file) && (!$v_listing))
		{
		// Look for path to remove
		if (($p_remove_path != "")
			&& (substr($v_header['filename'], 0, $p_remove_path_size) == $p_remove_path))
		{
			
			// Remove the path
			$v_header['filename'] = substr($v_header['filename'], $p_remove_path_size);
			
		}

		// Add the path to the file
		if (($p_path != "./") && ($p_path != "/"))
		{
			// Look for the path end '/'
			while (substr($p_path, -1) == "/")
			{
			
			$p_path = substr($p_path, 0, strlen($p_path)-1);
			
			}

			// Add the path
			if (substr($v_header['filename'], 0, 1) == "/")
				$v_header['filename'] = $p_path.$v_header['filename'];
			else
			$v_header['filename'] = $p_path."/".$v_header['filename'];
		}

		// Trace
		

		// Check that the file does not exists
		if (file_exists($v_header['filename']))
		{
			

			// Look if file is a directory
			if (is_dir($v_header['filename']))
			{
			

			// Change the file status
			$v_header['status'] = "already_a_directory";

			// Skip the extract
			$v_extraction_stopped = 1;
			$v_extract_file = 0;
			}
			// Look if file is write protected
			else if (!is_writeable($v_header['filename']))
			{
			

			// Change the file status
			$v_header['status'] = "write_protected";

			// Skip the extract
			$v_extraction_stopped = 1;
			$v_extract_file = 0;
			}
			// Look if the extracted file is older
			else if (filemtime($v_header['filename']) > $v_header['mtime'])
			{
			

			// Change the file status
			$v_header['status'] = "newer_exist";

			// Skip the extract
			$v_extraction_stopped = 1;
			$v_extract_file = 0;
			}
		}

		// Check the directory availability and create it if necessary
		else
		{
			if ($v_header['typeflag']=="5")
			$v_dir_to_check = $v_header['filename'];
			else if (!strstr($v_header['filename'], "/"))
			$v_dir_to_check = "";
			else
			$v_dir_to_check = dirname($v_header['filename']);

			if (($v_result = PclTarHandlerDirCheck($v_dir_to_check)) != 1)
			{
			

			// Change the file status
			$v_header['status'] = "path_creation_fail";

			// Skip the extract
			$v_extraction_stopped = 1;
			$v_extract_file = 0;
			}
		}

		// Do the extraction
		if (($v_extract_file) && ($v_header['typeflag']!="5"))
		{
			// Open the destination file in write mode
			if (($v_dest_file = @fopen($v_header['filename'], "wb")) == 0)
			{
			

			// Change the file status
			$v_header['status'] = "write_error";

			// Jump to next file
			
			if ($p_tar_mode == "tar")
				fseek($v_tar, ftell($v_tar)+(ceil(($v_header['size']/512))*512));
			else
				gzseek($v_tar, gztell($v_tar)+(ceil(($v_header['size']/512))*512));
			}
			else
			{
			

			// Read data
			$n = floor($v_header['size']/512);
			for ($i=0; $i<$n; $i++)
			{
				
				if ($p_tar_mode == "tar")
				$v_content = fread($v_tar, 512);
				else
				$v_content = gzread($v_tar, 512);
				fwrite($v_dest_file, $v_content, 512);
			}
			if (($v_header['size'] % 512) != 0)
			{
				
				if ($p_tar_mode == "tar")
				$v_content = fread($v_tar, 512);
				else
				$v_content = gzread($v_tar, 512);
				fwrite($v_dest_file, $v_content, ($v_header['size'] % 512));
			}

			// Close the destination file
			fclose($v_dest_file);

			// Change the file mode, mtime
			touch($v_header['filename'], $v_header['mtime']);
			//chmod($v_header['filename'], DecOct($v_header['mode']));
			}

			// Check the file size
			clearstatcache();
			if (filesize($v_header['filename']) != $v_header['size'])
			{
			// Close the archive file
			if ($p_tar_mode == "tar")
				fclose($v_tar);
			else
				gzclose($v_tar);

			// Error log
			

			// Return
			trigger_error(PclErrorString(), E_USER_ERROR);
			
			}

			// Trace
			
		}

		else
		{
			

			// Jump to next file
			
			if ($p_tar_mode == "tar")
			fseek($v_tar, ftell($v_tar)+(ceil(($v_header['size']/512))*512));
			else
			gzseek($v_tar, gztell($v_tar)+(ceil(($v_header['size']/512))*512));
		}
		}

		// Look for file that is not to be unzipped
		else
		{
		// Trace
		
		

		// Jump to next file
		if ($p_tar_mode == "tar")
			fseek($v_tar, ($p_tar_mode=="tar"?ftell($v_tar):gztell($v_tar))+(ceil(($v_header['size']/512))*512));
		else
			gzseek($v_tar, gztell($v_tar)+(ceil(($v_header['size']/512))*512));

		
		}

		if ($p_tar_mode == "tar")
		$v_end_of_file = feof($v_tar);
		else
		$v_end_of_file = gzeof($v_tar);

		// File name and properties are logged if listing mode or file is extracted
		if ($v_listing || $v_extract_file || $v_extraction_stopped)
		{
		

		// Log extracted files
		if (($v_file_dir = dirname($v_header['filename'])) == $v_header['filename'])
			$v_file_dir = "";
		if ((substr($v_header['filename'], 0, 1) == "/") && ($v_file_dir == ''))
			$v_file_dir = "/";

		// Add the array describing the file into the list
		$p_list_detail[$v_nb] = $v_header;

		// Increment
		$v_nb++;
		}
	}

	// Close the tarfile
	if ($p_tar_mode == "tar")
		fclose($v_tar);
	else
		gzclose($v_tar);

	// Return
	
	return $v_result;
	}
	// --------------------------------------------------------------------------------

	// --------------------------------------------------------------------------------
	// Function : PclTarHandleExtractByIndexList()
	// Description :
	//	 Extract the files which are at the indexes specified. If the 'file' at the
	//	 index is a directory, the directory only is created, not all the files stored
	//	 for that directory.
	// Parameters :
	//	 $p_index_string : String of indexes of files to extract. The form of the
	//										 string is "0,4-6,8-12" with only numbers and '-' for
	//										 for range, and ',' to separate ranges. No spaces or ';'
	//										 are allowed.
	// Return Values :
	// --------------------------------------------------------------------------------
	function PclTarHandleExtractByIndexList($p_tarname, $p_index_string, &$p_list_detail, $p_path, $p_remove_path, $p_tar_mode)
	{
	
	$v_result = 1;
	$v_nb = 0;

	// TBC : I should check the string by a regexp

	// Check the path
	if (($p_path == '') || ((substr($p_path, 0, 1) != "/") && (substr($p_path, 0, 3) != "../") && (substr($p_path, 0, 2) != "./")))
		$p_path = "./".$p_path;

	// Look for path to remove format (should end by /)
	if (($p_remove_path != "") && (substr($p_remove_path, -1) != '/'))
	{
		$p_remove_path .= '/';
	}
	$p_remove_path_size = strlen($p_remove_path);

	// Open the tar file
	if ($p_tar_mode == "tar")
	{
		
		$v_tar = @fopen($p_tarname, "rb");
	}
	else
	{
		
		$v_tar = @gzopen($p_tarname, "rb");
	}

	// Check that the archive is open
	if ($v_tar == 0)
	{
		// Error log
		

		// Return
		trigger_error(PclErrorString(), E_USER_ERROR);
		
	}

	// Manipulate the index list
	$v_list = explode(",", $p_index_string);
	sort($v_list);

	// Loop on the index list
	$v_index=0;
	for ($i=0; ($i<sizeof($v_list)) && ($v_result); $i++)
	{
		

		// Extract range
		$v_index_list = explode("-", $v_list[$i]);
		$v_size_index_list = sizeof($v_index_list);
		if ($v_size_index_list == 1)
		{
		

		// Do the extraction
		$v_result = PclTarHandleExtractByIndex($v_tar, $v_index, $v_index_list[0], $v_index_list[0], $p_list_detail, $p_path, $p_remove_path, $p_tar_mode);
		}
		else if ($v_size_index_list == 2)
		{
		

		// Do the extraction
		$v_result = PclTarHandleExtractByIndex($v_tar, $v_index, $v_index_list[0], $v_index_list[1], $p_list_detail, $p_path, $p_remove_path, $p_tar_mode);
		}
	}

	// Close the tarfile
	if ($p_tar_mode == "tar")
		fclose($v_tar);
	else
		gzclose($v_tar);

	// Return
	
	return $v_result;
	}
	// --------------------------------------------------------------------------------

	// --------------------------------------------------------------------------------
	// Function : PclTarHandleExtractByIndex()
	// Description :
	// Parameters :
	// Return Values :
	// --------------------------------------------------------------------------------
	function PclTarHandleExtractByIndex($p_tar, &$p_index_current, $p_index_start, $p_index_stop, &$p_list_detail, $p_path, $p_remove_path, $p_tar_mode)
	{
	
	$v_result = 1;
	$v_nb = 0;

	// TBC : I should replace all $v_tar by $p_tar in this function ....
	$v_tar = $p_tar;

	// Look the number of elements already in $p_list_detail
	$v_nb = sizeof($p_list_detail);

	// Read the blocks
	While (!($v_end_of_file = ($p_tar_mode == "tar"?feof($v_tar):gzeof($v_tar))))
	{
		
		

		if ($p_index_current > $p_index_stop)
		{
		
		break;
		}

		// Clear cache of file infos
		clearstatcache();

		// Reset extract tag
		$v_extract_file = FALSE;
		$v_extraction_stopped = 0;

		// Read the 512 bytes header
		if ($p_tar_mode == "tar")
		$v_binary_data = fread($v_tar, 512);
		else
		$v_binary_data = gzread($v_tar, 512);

		// Read the header properties
		if (($v_result = PclTarHandleReadHeader($v_binary_data, $v_header)) != 1)
		{
		// Return
		
		return $v_result;
		}

		// Look for empty blocks to skip
		if ($v_header['filename'] == '')
		{
		
		continue;
		}

		

		// Look if file is in the range to be extracted
		if (($p_index_current >= $p_index_start) && ($p_index_current <= $p_index_stop))
		{
		
		$v_extract_file = TRUE;
		}
		else
		{
		
		$v_extract_file = FALSE;
		}

		// Look if this file need to be extracted
		if ($v_extract_file)
		{
		if (($v_result = PclTarHandleExtractFile($v_tar, $v_header, $p_path, $p_remove_path, $p_tar_mode)) != 1)
		{
			// Return
			
			return $v_result;
		}
		}

		// Look for file that is not to be extracted
		else
		{
		// Trace
		
		

		// Jump to next file
		if ($p_tar_mode == "tar")
			fseek($v_tar, ($p_tar_mode=="tar"?ftell($v_tar):gztell($v_tar))+(ceil(($v_header['size']/512))*512));
		else
			gzseek($v_tar, gztell($v_tar)+(ceil(($v_header['size']/512))*512));

		
		}

		if ($p_tar_mode == "tar")
		$v_end_of_file = feof($v_tar);
		else
		$v_end_of_file = gzeof($v_tar);

		// File name and properties are logged if listing mode or file is extracted
		if ($v_extract_file)
		{
		

		// Log extracted files
		if (($v_file_dir = dirname($v_header['filename'])) == $v_header['filename'])
			$v_file_dir = "";
		if ((substr($v_header['filename'], 0, 1) == "/") && ($v_file_dir == ''))
			$v_file_dir = "/";

		// Add the array describing the file into the list
		$p_list_detail[$v_nb] = $v_header;

		// Increment
		$v_nb++;
		}

		// Increment the current file index
		$p_index_current++;
	}

	// Return
	
	return $v_result;
}

function PclTarHandleExtractFile($p_tar, &$v_header, $p_path, $p_remove_path, $p_tar_mode)
{
	$v_result = 1;
	$v_extract_file = 1;

	$p_remove_path_size = strlen($p_remove_path);

	// Look for path to remove
	if ($p_remove_path && substr($v_header['filename'], 0, $p_remove_path_size) == $p_remove_path)
	{
		// Remove the path
		$v_header['filename'] = substr($v_header['filename'], $p_remove_path_size);
		
	}

	// Add the path to the file
	if ($p_path != './' && $p_path != '/')
	{
		// Look for the path end '/'
		while (substr($p_path, -1) == '/')
		{
			$p_path = substr($p_path, 0, strlen($p_path)-1);
		}

		// Add the path
		$v_header['filename'] = (substr($v_header['filename'], 0, 1) == '/') ? $p_path . $v_header['filename'] : $p_path . '/' . $v_header['filename'];
	}

	// Check that the file does not exists
	if (file_exists($v_header['filename']))
	{
		// Look if file is a directory
		if (is_dir($v_header['filename']))
		{
			// Change the file status
			$v_header['status'] = 'already_a_directory';

			// Skip the extract
			$v_extraction_stopped = 1;
			$v_extract_file = 0;
		}
		else if (!is_writeable($v_header['filename']))
		{
			// Look if file is write protected

			// Change the file status
			$v_header['status'] = 'write_protected';

			// Skip the extract
			$v_extraction_stopped = 1;
			$v_extract_file = 0;
		}
		else if (filemtime($v_header['filename']) > $v_header['mtime'])
		{
			// Look if the extracted file is older
			
			// Change the file status
			$v_header['status'] = 'newer_exist';

			// Skip the extract
			$v_extraction_stopped = 1;
			$v_extract_file = 0;
		}
	}
	else
	{
		// Check the directory availability and create it if necessary
		$v_dir_to_check = ($v_header['typeflag'] == '5') ? $v_header['filename'] : ((!strstr($v_header['filename'], '/')) ? '' : dirname($v_header['filename']));

		if (($v_result = PclTarHandlerDirCheck($v_dir_to_check)) != 1)
		{
			// Change the file status
			$v_header['status'] = "path_creation_fail";

			// Skip the extract
			$v_extraction_stopped = 1;
			$v_extract_file = 0;
		}
	}

	// Do the real bytes extraction (if not a directory)
	if ($v_extract_file && $v_header['typeflag'] != '5')
	{
		// Open the destination file in write mode
		if (!($v_dest_file = @fopen($v_header['filename'], "wb")))
		{
			// Change the file status
			$v_header['status'] = 'write_error';

			// Jump to next file
			$fseek($p_tar, $ftell($p_tar) + (ceil(($v_header['size'] / 512)) * 512));
		}
		else
		{
			// Read data
			$n = floor($v_header['size'] / 512);

			for ($i = 0; $i < $n; $i++)
			{
				$v_content = $fread($p_tar, 512);
				fwrite($v_dest_file, $v_content, 512);
			}

			if (($v_header['size'] % 512) != 0)
			{
				$v_content = $fread($p_tar, 512);
				fwrite($v_dest_file, $v_content, ($v_header['size'] % 512));
			}

			// Close the destination file
			fclose($v_dest_file);

			// Change the file mode, mtime
			touch($v_header['filename'], $v_header['mtime']);
		}

		// Check the file size
		clearstatcache();

		if (filesize($v_header['filename']) != $v_header['size'])
		{
			trigger_error('Invalid header', E_USER_ERROR);
		}
	}
	else
	{
		// Jump to next file
		$fseek($p_tar, $ftell($p_tar) + (ceil(($v_header['size'] / 512)) *512));
	}

	return $v_result;
}

function PclTarHandleDelete($p_tarname, $p_file_list, &$p_list_detail, $p_tar_mode)
{
	$v_result = 1;
	$v_nb = 0;

	// Look for regular tar file
	if (($v_tar = @$fopen($p_tarname, 'rb')) == 0)
	{
		trigger_error(PclErrorString(), E_USER_ERROR);
	}

	// Open a temporary file in write mode
	$v_temp_tarname = uniqid('pcltar-') . '.tmp';
	
	if (($v_temp_tar = @$fopen($v_temp_tarname, 'wb')) == 0)
	{
		// Close tar file
		$fclose($v_tar);

		trigger_error(PclErrorString(), E_USER_ERROR);
	}

	// Read the blocks
	while ($v_binary_data = $fread($v_tar, 512))
	{
		// Clear cache of file infos
		clearstatcache();

		// Reset delete tag
		$v_delete_file = FALSE;

		// Read the header properties
		if (($v_result = PclTarHandleReadHeader($v_binary_data, &$v_header)) != 1)
		{
			// Close the archive file
			$fclose($v_tar);
			$fclose($v_temp_tar);

			@unlink($v_temp_tarname);

			return $v_result;
		}

		// Look for empty blocks to skip
		if ($v_header['filename'] == '')
		{
			continue;
		}

		// Look for filenames to delete
		for ($i = 0, $v_delete_file = FALSE; $i <sizeof($p_file_list) && !$v_delete_file; $i++)
		{
			// Compare the file names
			if (($v_len = strcmp($p_file_list[$i], $v_header['filename'])) <= 0)
			{
				if ($v_len==0)
				{
					$v_delete_file = TRUE;
				}
				else
				{
					if (substr($v_header['filename'], strlen($p_file_list[$i]), 1) == "/")
					{
						$v_delete_file = TRUE;
					}
				}
			}
		}

		// Copy files that do not need to be deleted
		if (!$v_delete_file)
		{
			$fwrite($v_temp_tar, $v_binary_data, 512);

			// Write the file data
			$n = ceil($v_header['size'] / 512);

			for ($i = 0; $i < $n; $i++)
			{
				$v_content = $fread($v_tar, 512);
				$fwrite($v_temp_tar, $v_content, 512);
			}

			// File name and properties are logged if listing mode or file is extracted

			// Add the array describing the file into the list
			$p_list_detail[$v_nb] = $v_header;
			$p_list_detail[$v_nb]['status'] = 'ok';

			// Increment
			$v_nb++;
		}
		else
		{
			// Look for file that is to be deleted
			// Jump to next file
			$fseek($v_tar, $ftell($v_tar) + (ceil(($v_header['size'] / 512)) *512));
		}
	}

	// Write the last empty buffer
	PclTarHandleFooter($v_temp_tar, $p_tar_mode);

	// Close the tarfile
	$fclose($v_tar);
	$fclose($v_temp_tar);

	// Unlink tar file
	if (!@unlink($p_tarname))
	{
		trigger_error('', E_USER_ERROR);
	}

	// Rename tar file
	if (!@rename($v_temp_tarname, $p_tarname))
	{
		trigger_error('', E_USER_ERROR);
	}

	return $v_result;
}

function PclTarHandleUpdate($p_tarname, $p_file_list, &$p_list_detail, $p_tar_mode, $p_add_dir, $p_remove_dir)
{
	$v_result = 1;
	$v_nb = 0;
	$v_found_list = array();

	// Look for regular tar file
	if (!($v_tar = @$fopen($p_tarname, 'rb')))
	{
		trigger_error('Could not open archive :: ' . $p_tarname, E_USER_ERROR);
	}

	// Open a temporary file in write mode
	$v_temp_tarname = uniqid('pcltar-') . '.tmp';
	if (!($v_temp_tar = @$fopen($v_temp_tarname, 'wb')))
	{
		// Close tar file
		@$fclose($v_tar);
		trigger_error(PclErrorString(), E_USER_ERROR);
	
	}

	// Prepare the list of files
	for ($i = 0; $i < sizeof($p_file_list); $i++)
	{
		// Reset the found list
		$v_found_list[$i] = 0;

		// Calculate the stored filename
		$v_stored_list[$i] = $p_file_list[$i];

		if ($p_remove_dir != '')
		{
			if (substr($p_file_list[$i], -1) != '/')
			{
				$p_remove_dir .= '/';
			}

			if (substr($p_file_list[$i], 0, strlen($p_remove_dir)) == $p_remove_dir)
			{
				$v_stored_list[$i] = substr($p_file_list[$i], strlen($p_remove_dir));
			}
		}

		if ($p_add_dir != '')
		{
			$v_stored_list[$i] = (substr($p_add_dir, -1) == '/') ? $p_add_dir . $v_stored_list[$i] : $p_add_dir . '/' . $v_stored_list[$i];
		}

		$v_stored_list[$i] = PclTarHandlePathReduction($v_stored_list[$i]);
	}


	// Update file cache
	clearstatcache();

	// Read the blocks
	while ($v_binary_data = $fread($v_tar, 512))
	{
		// Clear cache of file infos
		clearstatcache();

		// Reset current found filename
		$v_current_filename = '';

		// Reset delete tag
		$v_delete_file = FALSE;

		// Read the header properties
		if (($v_result = PclTarHandleReadHeader($v_binary_data, &$v_header)) != 1)
		{
			// Close the archive file
			$fclose($v_tar);
			$fclose($v_temp_tar);

			@unlink($v_temp_tarname);

			return $v_result;
		}

		// Look for empty blocks to skip
		if ($v_header['filename'] == '')
		{
			continue;
		}

		// Look for filenames to update
		for ($i = 0, $v_update_file = FALSE, $v_found_file = FALSE; $i < sizeof($v_stored_list) && !$v_update_file; $i++)
		{
			// Compare the file names
			if ($v_stored_list[$i] == $v_header['filename'])
			{
				// Store found informations
				$v_found_file = TRUE;
				$v_current_filename = $p_file_list[$i];

				// Look if the file need to be updated
				$v_update_file = (filemtime($p_file_list[$i]) > $v_header['mtime']) ? TRUE : FALSE;

				// Flag the name in order not to add the file at the end
				$v_found_list[$i] = 1;
			}
		}

		// Copy files that do not need to be updated
		if (!$v_update_file)
		{
			// Write the file header
			$fwrite($v_temp_tar, $v_binary_data, 512);

			// Write the file data
			$n = ceil($v_header['size'] / 512);

			for ($j = 0; $j < $n; $j++)
			{
				$v_content = $fread($v_tar, 512);
				$fwrite($v_temp_tar, $v_content, 512);
			}

			// Add the array describing the file into the list
			$p_list_detail[$v_nb] = $v_header;
			$p_list_detail[$v_nb]['status'] = ($v_found_file?"not_updated":"ok");

			// Increment
			$v_nb++;
		}
		else
		{
			// Look for file that need to be updated

			// Store the old file size
			$v_old_size = $v_header['size'];

			// Add the file
			if (($v_result = PclTarHandleAddFile($v_temp_tar, $v_current_filename, $p_tar_mode, $v_header, $p_add_dir, $p_remove_dir)) != 1)
			{
				// Close the tarfile
				$fclose($v_tar);
				$fclose($v_temp_tar);

				@unlink($p_temp_tarname);

				return $v_result;
			}

			// Jump to next file
			$fseek($v_tar, $ftell($v_tar) + (ceil(($v_header['size'] / 512)) *512));

			// Add the array describing the file into the list
			$p_list_detail[$v_nb] = $v_header;
			$p_list_detail[$v_nb]['status'] = 'updated';

			// Increment
			$v_nb++;
		}
	}

	// Look for files that does not exists in the archive and need to be added
	for ($i=0; $i<sizeof($p_file_list); $i++)
	{
		// Look if file not found in the archive
		if (!$v_found_list[$i])
		{
			// Add the file
			if (($v_result = PclTarHandleAddFile($v_temp_tar, $p_file_list[$i], $p_tar_mode, $v_header, $p_add_dir, $p_remove_dir)) != 1)
			{
				$fclose($v_tar);
				$fclose($v_temp_tar);

				@unlink($p_temp_tarname);

				return $v_result;
			}

			// Add the array describing the file into the list
			$p_list_detail[$v_nb] = $v_header;
			$p_list_detail[$v_nb][status] = 'added';

			// Increment
			$v_nb++;
		}
	}

	// Write the last empty buffer
	PclTarHandleFooter($v_temp_tar, $p_tar_mode);

	// Close the tarfile
	$fclose($v_tar);
	$fclose($v_temp_tar);

	// Unlink tar file
	if (!@unlink($p_tarname))
	{
		trigger_error('', E_USER_ERROR);
	}

	// Rename tar file
	if (!@rename($v_temp_tarname, $p_tarname))
	{
		trigger_error('', E_USER_ERROR);
	}
	
	return $v_result;
}

function PclTarHandleReadHeader($v_binary_data, &$v_header)
{
	$v_result = 1;

	// Look for no more block
	if (strlen($v_binary_data)==0)
	{
		$v_header['filename'] = '';
		$v_header['status'] = 'empty';

		return $v_result;
	}

	// Look for invalid block size
	if (strlen($v_binary_data) != 512)
	{
		$v_header['filename'] = '';
		$v_header['status'] = 'invalid_header';

		trigger_error('Invalid header in archive', E_USER_ERROR);
	}

	// Calculate the checksum
	$v_checksum = 0;
	for ($i = 0; $i < 148; $i++)
	{
		$v_checksum += ord(substr($v_binary_data,$i,1));
	}

	// ..... Ignore the checksum value and replace it by ' ' (space)
	for ($i = 148; $i < 156; $i++)
	{
		$v_checksum += ord(' ');
	}

	// ..... Last part of the header
	for ($i = 156; $i < 512; $i++)
	{
		$v_checksum += ord(substr($v_binary_data,$i,1));
	}

	$v_data = unpack('a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor', $v_binary_data);

	// Extract the checksum for check
	$v_header['checksum'] = octdec(trim($v_data['checksum']));
	if ($v_header['checksum'] != $v_checksum)
	{
		$v_header['filename'] = '';
		$v_header['status'] = 'invalid_header';

		// Look for last block (empty block)
		if ($v_checksum == 256 && $v_header['checksum'] == 0)
		{
			$v_header['status'] = 'empty';
			return $v_result;
		}

		trigger_error('Invalid header in archive', E_USER_ERROR);
	}

	// Extract the properties
	$v_header['filename'] = trim($v_data['filename']);
	$v_header['mode'] = octdec(trim($v_data['mode']));
	$v_header['uid'] = octdec(trim($v_data['uid']));
	$v_header['gid'] = octdec(trim($v_data['gid']));
	$v_header['size'] = octdec(trim($v_data['size']));
	$v_header['mtime'] = octdec(trim($v_data['mtime']));
	if (($v_header['typeflag'] = $v_data['typeflag']) == '5')
	{
		$v_header['size'] = 0;
	}
	$v_header['status'] = 'ok';

	return $v_result;
}

// Check if a directory exists, if not it creates it and all the parents directory
// which may be useful.
function PclTarHandlerDirCheck($p_dir)
{
	$v_result = 1;

	// Check the directory availability
	if (is_dir($p_dir) || $p_dir == '')
	{
		return 1;
	}

	// Extract parent directory
	$p_parent_dir = dirname($p_dir);

	// Look for parent directory
	if ($p_parent_dir != $p_dir && $p_parent_dir != '')
	{
		if (($v_result = PclTarHandlerDirCheck($p_parent_dir)) != 1)
		{
			return $v_result;
		}
	}

	umask(0);
	// Create the directory
	if (!@mkdir($p_dir, 0777))
	{
		trigger_error('Failed to create archive directory in :: ' . $p_dir, E_USER_ERROR);
	}
	 
	return 1;
}

// Look for file extension
function PclTarHandleExtension($p_tarname)
{
	return (substr($p_tarname, -7) == '.tar.gz' || substr($p_tarname, -4) == '.tgz') ? 'tgz' : '';
}

function PclTarHandlePathReduction($p_dir)
{
	$v_result = '';

	// Look for not empty path
	if ($p_dir)
	{
		// Explode path by directory names
		$v_list = explode('/', $p_dir);

		// Study directories from last to first
		for ($i = sizeof($v_list) - 1; $i >= 0; $i--)
		{
			// Look for current path
			if ($v_list[$i] == '.')
			{
			}
			else if ($v_list[$i] == '..')
			{
				// Ignore it and ignore the $i-1
				$i--;
			}
			else if ($v_list[$i] == '' && $i != (sizeof($v_list) - 1 && $i))
			{
			}
			else
			{
				$v_result = $v_list[$i] . ($i != (sizeof($v_list) - 1) ? "/$v_result" : '');
			}
		}
	}

	return $v_result;
}

?>