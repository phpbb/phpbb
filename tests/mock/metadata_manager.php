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

class phpbb_mock_metadata_manager extends \phpbb\extension\metadata_manager
{
	public function set_metadata($metadata)
	{
		array_walk_recursive($metadata, array($this, 'sanitize_json'));
		$this->metadata = $metadata;
	}

	public function merge_metadata($metadata)
	{
		array_walk_recursive($metadata, array($this, 'sanitize_json'));
		$this->metadata = array_merge($this->metadata, $metadata);
	}
}
