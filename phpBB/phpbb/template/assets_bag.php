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

use phpbb\assets\iconify_bundler;

class assets_bag
{
	/** @var asset[] */
	protected $stylesheets = [];

	/** @var asset[] */
	protected $scripts = [];

	/** @var string[] */
	protected $iconify_icons = [];

	/**
	 * Constructor for assets bag
	 *
	 * @param iconify_bundler $iconify_bundler
	 */
	public function __construct(protected iconify_bundler $iconify_bundler)
	{}

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

	public function add_iconify_icon(string $icon): void
	{
		$this->iconify_icons[] = $icon;
	}

	/**
	 * Inject iconify icons into template
	 *
	 * @param string $output Output before injection
	 * @param string $variable_name Variable name for injection
	 * @param bool $use_cdn Flag whether to use CDN or local data
	 *
	 * @return string Output after injection
	 */
	public function inject_iconify_icons(string $output, string $variable_name, bool $use_cdn): string
	{
		if (str_contains($output, $variable_name))
		{
			$output = str_replace($variable_name, $use_cdn ? '' : $this->get_iconify_content(), $output);
		}

		return $output;
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
	 * Returns the HTML code to include all css assets
	 *
	 * @return string
	 */
	public function get_stylesheets_content(): string
	{
		$output = '';
		foreach ($this->stylesheets as $stylesheet)
		{
			$output .= "<link href=\"{$stylesheet->get_url()}\" rel=\"stylesheet\" media=\"screen\">\n";
		}

		return $output;
	}

	/**
	 * Returns the HTML code to include all js assets
	 *
	 * @return string
	 */
	public function get_scripts_content(): string
	{
		$output = '';
		foreach ($this->scripts as $script)
		{
			$output .= "<script src=\"{$script->get_url()}\"></script>\n";
		}

		return $output;
	}

	/**
	 * Gets the HTML code to include all iconify icons
	 *
	 * @return string HTML code for iconify bundle
	 */
	public function get_iconify_content(): string
	{
		$output = '';
		if (count($this->iconify_icons))
		{
			$output .= '<script>';
			$this->iconify_bundler->add_icons($this->iconify_icons);
			$output .= $this->iconify_bundler->run();
			$output .= '</script>';
		}
		return $output;
	}
}
