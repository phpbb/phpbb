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

namespace phpbb\template\twig\extension;

use phpbb\template\twig\environment;

class icon extends \Twig\Extension\AbstractExtension
{
	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\user	$user			User object
	 */
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	/**
	 * Returns the name of this extension.
	 *
	 * @return string						The extension name
	 */
	public function getName()
	{
		return 'icon';
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return \Twig\TwigFunction[]			Array of twig functions
	 */
	public function getFunctions()
	{
		return [
			new \Twig\TwigFunction('Icon', [$this, 'icon'], ['needs_environment' => true]),
		];
	}

	/**
	 * Generate icon HTML for use in the template, depending on the mode.
	 *
	 * @param environment	$environment	Twig environment object
	 * @param string		$type			Icon type (font|png|svg)
	 * @param string		$icon			Icon name (eg. "bold")
	 * @param string		$title			Icon title
	 * @param bool			$hidden			Hide the icon title from view
	 * @param string		$classes		Additional classes (eg. "fa-fw")
	 * @param array			$attributes		Additional attributes for the icon, where the key is the attribute.
	 *                      				{'data-ajax': 'mark_forums'} results in ' data-ajax="mark_forums"'
	 * @return string
	 */
	public function icon(environment $environment, $type, $icon, $title = '', $hidden = false, $classes = '', array $attributes = [])
	{
		$type = strtolower($type);

		switch ($type)
		{
			case 'font':
				$source = '';
			break;

			case 'png':
				$board_url	= defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH;
				$web_path	= $board_url ? generate_board_url() . '/' : $environment->get_web_root_path();
				$style_path	= $this->user->style['style_path'];

				$source = "{$web_path}styles/{$style_path}/theme/icons/png/{$icon}.png";
			break;

			case 'svg':
				try
				{
					$file	= $environment->load('icons/svg/' . $icon . '.svg');
					$source	= $this->prepare_svg($file);
				}
				catch (\Twig\Error\Error $e)
				{
					return '';
				}
			break;

			default:
				return '';
			break;
		}

		try
		{
			return $environment->render("icons/{$type}.html", [
				'ATTRIBUTES'	=> (string) $this->implode_attributes($attributes),
				'CLASSES'		=> (string) $classes,
				'ICON'			=> (string) $icon,
				'SOURCE'		=> (string) $source,
				'TITLE'			=> (string) $title,
				'S_HIDDEN'		=> (bool) $hidden,
			]);
		}
		catch (\Twig\Error\Error $e)
		{
			return '';
		}
	}

	/**
	 * Prepare an SVG for usage in the template icon.
	 *
	 * @param \Twig\TemplateWrapper	$file	The SVG file loaded from the environment
	 * @return string
	 */
	protected function prepare_svg(\Twig\TemplateWrapper $file)
	{
		$doc = new \DOMDocument();
		$doc->preserveWhiteSpace = false;

		/**
		 * Suppression is needed as DOMDocument does not like HTML5 and SVGs.
		 * Options parameter prevents $dom->saveHTML() from adding an <html> element.
		 */
		@$doc->loadHTML($file->render(), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

		$xpath = new \DOMXPath($doc);

		// Remove all <svg> and <title> elements
		foreach ($xpath->query('//svg | //title') as $element)
		{
			if ($element->nodeName === 'svg')
			{
				while (isset($element->firstChild))
				{
					$element->parentNode->insertBefore($element->firstChild, $element);
				}
			}

			$element->parentNode->removeChild($element);
		}

		$string = $doc->saveHTML();
		$string = preg_replace('/\s+/', ' ', $string);

		return $string;
	}

	/**
	 * Implode an associated array of attributes to a string for usage in a template.
	 *
	 * @param array		$attributes		Associated array of attributes
	 * @return string
	 */
	protected function implode_attributes(array $attributes)
	{
		$string = '';

		foreach ($attributes as $key => $value)
		{
			$string .= ' ' . $key . '="' . $value . '"';
		}

		return $string;
	}
}
