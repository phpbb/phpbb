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

namespace phpbb\attachment;

use phpbb\config\config;

/**
 * Attachment thumbnail class
 */
class thumbnail
{
	/** @var config phpBB config */
	protected $config;

	/** @var resize Resize class */
	protected $resize;

	/**
	 * Thumbnail constructor
	 *
	 * @param config $config phpBB config
	 * @param resize $resize Resize class
	 */
	public function __construct(config $config, resize $resize)
	{
		$this->config = $config;
		$this->resize = $resize;
	}

	/**
	 * Create thumbnail for image
	 *
	 * @param string $source Source file path
	 * @param string $destination Destination file path
	 * @param string $mime_type File mime type
	 *
	 * @return bool True if thumbnail was created, false if not
	 */
	public function create($source, $destination, $mime_type)
	{
		$this->resize
			->set_min_file_size($this->config['img_min_thumb_filesize'])
			->set_target_size($this->config['img_max_thumb_width'], $this->config['img_max_thumb_width']);

		if ($this->config['img_imagick'])
		{
			$this->resize->set_imagick_path($this->config['img_imagick']);
		}

		return $this->resize->create($source, $destination, $mime_type);
	}
}
