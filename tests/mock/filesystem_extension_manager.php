<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_mock_filesystem_extension_manager extends phpbb_mock_extension_manager
{
	public function __construct($phpbb_root_path)
	{
		$extensions = array();
		$iterator = new DirectoryIterator($phpbb_root_path . 'ext/');
		foreach ($iterator as $fileinfo)
		{
			if ($fileinfo->isDir() && substr($fileinfo->getFilename(), 0, 1) != '.')
			{
				$name = $fileinfo->getFilename();
				$extension = array(
					'ext_name' => $name,
					'ext_active' => true,
					'ext_path' => 'ext/' . $name . '/',
				);
				$extensions[$name] = $extension;
			}
		}
		ksort($extensions);
		parent::__construct($phpbb_root_path, $extensions);
	}
}
