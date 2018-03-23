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

namespace phpbb\passwords;

class helper
{
	/**
	* Get hash settings from combined hash
	*
	* @param string $hash Password hash of combined hash
	*
	* @return array An array containing the hash settings for the hash
	*		types in successive order as described by the combined
	*		password hash or an empty array if hash does not
	*		properly fit the combined hash format
	*/
	public function get_combined_hash_settings($hash)
	{
		$output = array();

		preg_match('#^\$([a-zA-Z0-9\\\]*?)\$#', $hash, $match);
		$hash_settings = substr($hash, strpos($hash, $match[1]) + strlen($match[1]) + 1);
		$matches = explode('\\', $match[1]);
		foreach ($matches as $cur_type)
		{
			$dollar_position = strpos($hash_settings, '$');
			$output[] = substr($hash_settings, 0, ($dollar_position != false) ? $dollar_position : strlen($hash_settings));
			$hash_settings = substr($hash_settings, $dollar_position + 1);
		}

		return $output;
	}

	/**
	* Combine hash prefixes, settings, and actual hash
	*
	* @param array $data Array containing the keys 'prefix' and 'settings'.
	*			It will hold the prefixes and settings
	* @param string $type Data type of the supplied value
	* @param string $value Value that should be put into the data array
	*
	* @return string|null Return complete combined hash if type is neither
	*			'prefix' nor 'settings', nothing if it is
	*/
	public function combine_hash_output(&$data, $type, $value)
	{
		if ($type == 'prefix')
		{
			$data[$type] .= ($data[$type] !== '$') ? '\\' : '';
			$data[$type] .= str_replace('$', '', $value);
		}
		else if ($type == 'settings')
		{
			$data[$type] .= ($data[$type] !== '$') ? '$' : '';
			$data[$type] .= $value;
		}
		else
		{
			// Return full hash
			return $data['prefix'] . $data['settings'] . '$' . $value;
		}
	}

	/**
	* Rebuild hash for hashing functions
	*
	* @param string $prefix Hash prefix
	* @param string $settings Hash settings
	*
	* @return string Rebuilt hash for hashing functions
	*/
	public function rebuild_hash($prefix, $settings)
	{
		$rebuilt_hash = $prefix;
		if (strpos($settings, '\\') !== false)
		{
			$settings = str_replace('\\', '$', $settings);
		}
		$rebuilt_hash .= $settings;
		return $rebuilt_hash;
	}

	/**
	* Obtain only the actual hash after the prefixes
	*
	* @param string		$hash The full password hash
	* @return string	Actual hash (incl. settings)
	*/
	public function obtain_hash_only($hash)
	{
		return substr($hash, strripos($hash, '$') + 1);
	}
}
