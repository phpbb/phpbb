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

namespace phpbb\template;

class assets_bag
{
	/** @var asset[] */
	protected $stylesheets = [];

	/** @var asset[] */
	protected $scripts = [];

	/**
	 * Add a css asset to the bag
	 *
	 * @param asset $asset
	 */
	public function add_stylesheet(asset $asset)
	{
		$this->stylesheets[] = $asset;
	}

	/**
	 * Add a js script asset to the bag
	 *
	 * @param asset $asset
	 */
	public function add_script(asset $asset)
	{
		$this->scripts[] = $asset;
	}

	/**
	 * Returns all css assets
	 *
	 * @return asset[]
	 */
	public function get_stylesheets()
	{
		return $this->stylesheets;
	}

	/**
	 * Returns all js assets
	 *
	 * @return asset[]
	 */
	public function get_scripts()
	{
		return $this->scripts;
	}

	/**
	 * Returns the HTML code to includes all css assets
	 *
	 * @return string
	 */
	public function get_stylesheets_content()
	{
		$output = '';
		foreach ($this->stylesheets as $stylesheet)
		{
			$output .= "<link href=\"{$stylesheet->get_url()}\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
		}

		return $output;
	}

	/**
	 * Returns the HTML code to includes all js assets
	 *
	 * @return string
	 */
	public function get_scripts_content()
	{
		$output = '';
		foreach ($this->scripts as $script)
		{
			$output .= "<script type=\"text/javascript\" src=\"{$script->get_url()}\"></script>\n";
		}

		return $output;
	}
}
