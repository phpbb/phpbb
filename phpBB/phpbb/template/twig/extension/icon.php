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
use Twig\Extension\AbstractExtension;

class icon extends AbstractExtension
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
	 * @param array|string	$icon			Icon name (eg. "bold")
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
		$icon = is_array($icon) ? $this->get_first_icon($icon) : $icon;

		if (empty($icon))
		{
			return '';
		}

		$not_found	= false;
		$source		= '';
		$view_box	= '';

		switch ($type)
		{
			case 'font':
				$classes = $this->insert_fa_class($classes);
			break;

			case 'png':
				$filesystem	= $environment->get_filesystem();
				$root_path	= $environment->get_web_root_path();

				// Iterate over the user's styles and check for icon existance
				foreach ($this->get_style_list() as $style_path)
				{
					if ($filesystem->exists("{$root_path}styles/{$style_path}/theme/png/{$icon}.png"))
					{
						$source = "{$root_path}styles/{$style_path}/theme/png/{$icon}.png";

						break;
					}
				}

				// Check if the icon was found or not
				$not_found = empty($source);
			break;

			case 'svg':
				try
				{
					// Try to load and prepare the SVG icon
					$file	= $environment->load('svg/' . $icon . '.svg');
					$source	= $this->prepare_svg($file, $view_box);

					if (empty($view_box))
					{
						return '';
					}
				}
				catch (\Twig\Error\LoaderError $e)
				{
					// Icon was not found
					$not_found = true;
				}
				catch (\Twig\Error\Error $e)
				{
					return $e->getMessage();
				}
			break;

			default:
				return '';
		}

		// If no PNG or SVG icon was found, display a default 404 SVG icon.
		if ($not_found)
		{
			try
			{
				$file	= $environment->load('svg/404.svg');
				$source	= $this->prepare_svg($file, $view_box);
			}
			catch (\Twig\Error\Error $e)
			{
				return $e->getMessage();
			}

			$type = 'svg';
			$icon = '404';
		}

		try
		{
			return $environment->render("macros/icons/{$type}.twig", [
				'ATTRIBUTES'	=> (string) $this->implode_attributes($attributes),
				'CLASSES'		=> (string) $classes,
				'ICON'			=> (string) $icon,
				'SOURCE'		=> (string) $source,
				'TITLE'			=> (string) $title,
				'TITLE_ID'		=> $title && $type === 'svg' ? unique_id() : '',
				'VIEW_BOX'		=> (string) $view_box,
				'S_HIDDEN'		=> (bool) $hidden,
			]);
		}
		catch (\Twig\Error\Error $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Insert fa class into class string by checking if class string contains any fa classes
	 *
	 * @param string $class_string
	 * @return string Updated class string or original class string if fa class is already set or string is empty
	 */
	protected function insert_fa_class(string $class_string): string
	{
		if (empty($class_string))
		{
			return $class_string;
		}

		// These also include pro class name we don't use, but handle them properly anyway
		$fa_classes = ['fa-solid', 'fas', 'fa-regular', 'far', 'fal', 'fa-light', 'fab', 'fa-brands'];

		// Split the class string into individual words
		$icon_classes = explode(' ', $class_string);

		// Check if the class string contains any of the fa classes, just return class string in that case
		foreach ($icon_classes as $word)
		{
			if (in_array($word, $fa_classes))
			{
				return $class_string;
			}
		}

		// If we reach this it means we didn't have any fa classes in the class string.
		// Prepend class string with fas for fa-solid
		return 'fas ' . $class_string;
	}

	/**
	 * Prepare an SVG for usage in the template icon.
	 *
	 * This removes any <?xml ?> and <!DOCTYPE> elements,
	 * aswell as the root <svg> and any <title> elements.
	 *
	 * @param \Twig\TemplateWrapper	$file		The SVG file loaded from the environment
	 * @param string				$view_box	The viewBox attribute value
	 * @return string							The cleaned SVG
	 */
	protected function prepare_svg(\Twig\TemplateWrapper $file, &$view_box = '')
	{
		$code = $file->render();
		$code = preg_replace( "/<\?xml.+?\?>/", '', $code);

		$doc = new \DOMDocument();
		$doc->preserveWhiteSpace = false;

		// Hide html5/svg errors
		libxml_use_internal_errors(true);

		// Options parameter prevents $dom->saveHTML() from adding an <html> element.
		$doc->loadHTML($code, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

		// Remove any DOCTYPE
		foreach ($doc->childNodes as $child)
		{
			if ($child->nodeType === XML_DOCUMENT_TYPE_NODE)
			{
				$child->parentNode->removeChild($child);
			}
		}

		$xpath = new \DOMXPath($doc);

		/**
		 * Remove the root <svg> element
		 * and all <title> elements.
		 *
		 * @var \DOMElement $element
		 */
		foreach ($xpath->query('/svg | //title') as $element)
		{
			if ($element->nodeName === 'svg')
			{
				// Return the viewBox attribute value of the root SVG element by reference
				$view_box = $element->getAttribute('viewbox');

				$width = $element->getAttribute('width');
				$height = $element->getAttribute('height');

				if (empty($view_box) && $width && $height)
				{
					$view_box = "0 0 {$width} {$height}";
				}

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
	 * Finds the first icon that has a "true" value and returns it.
	 *
	 * This allows sending an array to the Icon() function,
	 * where the keys are the icon names and the values are their checks.
	 *
	 * {{ Icon('font', {
	 * 		'bullhorn': topicrow.S_POST_GLOBAL or topicrow.S_POST_ANNOUNCE,
	 * 		'thumbtack': topicrow.S_POST_STICKY,
	 * 		'lock': topicrow.S_TOPIC_LOCKED,
	 * 		'fire': topicrow.S_TOPIC_HOT,
	 * 		'file': true,
	 * }, lang('MY_TITLE'), true) }}
	 *
	 * @param array		$icons			Array of icons and their booleans
	 * @return string					The first 'true' icon
	 */
	protected function get_first_icon(array $icons)
	{
		foreach ($icons as $icon => $boolean)
		{
			// In case the key is not a string,
			// this icon does not have a check
			// so instantly return it
			if (!is_string($icon))
			{
				return $boolean;
			}

			if ($boolean)
			{
				return $icon;
			}
		}

		return '';
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

	/**
	 * Get the style tree of the style preferred by the current user.
	 *
	 * @return array					Style tree, most specific first
	 */
	protected function get_style_list()
	{
		$style_list = [$this->user->style['style_path']];

		if ($this->user->style['style_parent_id'])
		{
			$style_list = array_merge($style_list, array_reverse(explode('/', $this->user->style['style_parent_tree'])));
		}

		return $style_list;
	}
}
