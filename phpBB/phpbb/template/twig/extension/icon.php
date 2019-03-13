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

class icon extends \Twig_Extension
{
	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\user	$user		User object
	 */
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	/**
	* Get the name of this extension.
	*
	* @return string
	*/
	public function getName()
	{
		return 'icon';
	}

	/**
	* Returns a list of global functions to add to the existing list.
	*
	* @return array		An array of global functions
	*/
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('Icon', [$this, 'icon'], ['needs_environment' => true]),
		];
	}

	/**
	 * Generate icon HTML for use in the template, depending on the mode.
	 *
	 * @param environment	$environment	Twig environment object
	 * @param string		$type			Icon type (font|png|svg)
	 * @param string		$icon			Icon name (eg. "bold")
	 * @param string		$classes		Additional classes (eg. "fa-fw")
	 * @param string		$title			Icon title
	 * @param bool			$hidden			Hide the icon title from view
	 * @param array			$attributes		Additional attributes for the icon, where the key is the attribute.
	 *                      				{'data-ajax': 'mark_forums'} results in ' data-ajax="mark_forums"'
	 * @return string
	 */
	public function icon(environment $environment, $type = '', $icon = '', $classes = '', $title = '', $hidden = false, array $attributes = [])
	{
		switch ($type)
		{
			case 'font':
				$src = '';
			break;

			case 'png':
				$board_url = defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH;
				$web_path = $board_url ? generate_board_url() . '/' : $environment->get_web_root_path();

				$src = "{$web_path}styles/" . $this->user->style['style_path'] . "/template/icons/png/{$icon}.png";
			break;

			case 'svg':
				try
				{
					$file = $environment->load('icons/svg/' . $icon . '.svg');
				}
				catch (\Twig_Error $e)
				{
					return '';
				}

				$src = $this->filter_svg($file);
			break;

			default:
				// Not a supported icon type (font|png|svg), return an empty string
				return '';
			break;
		}

		try
		{
			return $environment->render("icons/{$type}.html", [
				'ATTRIBUTES'	=> (string) $this->implode_attributes($attributes),
				'CLASSES'		=> (string) $classes,
				'ICON'			=> (string) $icon,
				'SOURCE'		=> (string) $src,
				'TITLE'			=> (string) $title,
				'S_HIDDEN'		=> (bool) $hidden,
			]);
		}
		catch (\Twig_Error $e)
		{
			return '';
		}
	}

	/**
	 * Implode an associated array of attributes to a string for usage in a template.
	 *
	 * @param array		$attributes		Associated array of attributes
	 * @return string
	 */
	protected function implode_attributes(array $attributes)
	{
		$attr_str = '';

		foreach ($attributes as $attribute => $value)
		{
			$attr_str .= ' ' . $attribute . '="' . $value . '"';
		}

		return $attr_str;
	}

	/**
	 * Filter a SVG for usage in the template.
	 *
	 * @param \Twig_TemplateWrapper	$file	The svg file loaded from the environment
	 * @return string
	 */
	protected function filter_svg(\Twig_TemplateWrapper $file)
	{
		/** @var \Twig_Source $src */
		$src = $file->getSourceContext();
		$svg = $src->getCode();

		/** @var \DOMDocument $dom */
		$dom = new \DOMDocument();
		$dom->preserveWhiteSpace = false;

		/**
		 * Suppression is needed as DOMDocument does not like HTML5 and SVGs.
		 * Options parameter prevents $dom->saveHTML() from adding an <html> element.
		 */
		@$dom->loadHTML($svg, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

		/** @var \DOMXPath $xpath */
		$xpath = new \DOMXPath($dom);

		/** @var \DOMNode $element */
		foreach ($xpath->query('//svg | //title') as $element)
		{
			if ($element->nodeName === 'svg')
			{
				$children = [];

				/** @var \DOMNode $node */
				foreach ($element->childNodes as $node)
				{
					$children[] = $node;
				}

				/** @var \DOMNode $child */
				foreach ($children as $child)
				{
					$element->parentNode->insertBefore($child, $element);
				}
			}

			$element->parentNode->removeChild($element);
		}

		/** @var \DOMElement $attribute */
		foreach ($xpath->query('//@fill') as $attribute)
		{
			if ($attribute->nodeName === 'fill' && $attribute->nodeValue === 'none')
			{
				continue;
			}

			$attribute->parentNode->removeAttribute($attribute->nodeName);
		}

		return $dom->saveHTML();
	}
}
