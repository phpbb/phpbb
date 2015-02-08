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

namespace phpbb\upload;

/**
 * This class handles all server-side plupload functions
 */
class imagesize
{
	/** @var string PNG header */
	protected $png_header = "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a";

	/** @var int PNG IHDR offset */
	protected $png_ihdr_offset = 12;

	/** @var int PNG chunk size */
	protected $png_chunk_size = 4;

	/**
	 * Get image dimensions of supplied image
	 *
	 * @param string $file Path to image that should be checked
	 * @param string $type Mimetype of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	public function get_imagesize($file, $type = '')
	{
		if (!preg_match('/\.([a-z0-9]+)$/i', $file, $match) && empty($type))
		{
			return false;
		}

		$extension = (!empty($match)) ? $match[0] : preg_replace('/.+\/([a-z0-9]+)$/i', '$1', $type);

		switch ($extension)
		{
			case 'png':
				return $this->get_png_size($file);
			break;

			default:
				return false;
		}
	}

	/**
	 * Get dimensions of PNG image
	 *
	 * @param string $filename Filename of image
	 * @return array|bool Array with image dimensions if successful, false if not
	 */
	protected function get_png_size($filename)
	{
		$data = file_get_contents($filename, null, null, 0, 24);

		// Check if header fits expected format specified by RFC 2083
		if (substr($data, 0, $this->png_ihdr_offset - $this->png_chunk_size) !== $this->png_header || substr($data, $this->png_ihdr_offset, $this->png_chunk_size) !== 'IHDR')
		{
			return false;
		}

		$size = unpack('Nwidth', substr($data, $this->png_ihdr_offset + $this->png_chunk_size, $this->png_chunk_size));
		$size = array_merge($size, unpack('Nheight', substr($data, $this->png_ihdr_offset + 2 * $this->png_chunk_size, $this->png_chunk_size)));

		return sizeof($size) ? $size : false;
	}
}
