<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

class phpbb_mock_metadata_manager extends phpbb_extension_metadata_manager
{
	public function set_metadata($metadata)
	{
		$this->metadata = $metadata;
	}

	public function merge_metadata($metadata)
	{
		$this->metadata = array_merge($this->metadata, $metadata);
	}
}
