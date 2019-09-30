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

abstract class icon extends \Twig\Extension\AbstractExtension
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
	 * Returns the name of this extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'icon';
	}

	/**
	 * Returns a list of filters to add to the existing list.
	 *
	 * @return \Twig\TwigFilter[]
	 */
	public function getFilters()
	{
		return [
			new \Twig\TwigFilter('Png_path', [$this, 'png_path'], ['needs_environment' => true]),
		];
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return \Twig\TwigFunction[]
	 */
	public function getFunctions()
	{
		return [
			new \Twig\TwigFunction('Svg_clean', [$this, 'svg_clean'], ['needs_environment' => true]),
		];
	}

	/**
	 * Create a path to a PNG template icon.
	 *
	 * @param environment	$environment	Twig environment object
	 * @param string		$icon			The icon name
	 * @return string
	 */
	protected function png_path(environment $environment, $icon)
	{
		$board_url	= defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH;
		$web_path	= $board_url ? generate_board_url() . '/' : $environment->get_web_root_path();
		$style_path	= $this->user->style['style_path'];

		return "{$web_path}styles/{$style_path}/template/icons/png/{$icon}.png";
	}

	/**
	 * Load and clean an SVG template icon.
	 *
	 * @param environment	$environment	Twig environment object
	 * @param string		$icon			The icon name
	 * @return string
	 */
	protected function svg_clean(environment $environment, $icon)
	{
		try
		{
			$file = $environment->load('icons/svg/' . $icon . '.svg');
		}
		catch (\Twig\Error\Error $e)
		{
			return '';
		}

		$src = $file->getSourceContext();
		$svg = $src->getCode();

		$doc = new \DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->strictErrorChecking = false;

		if (!$doc->loadXML($svg))
		{
			return '';
		}

		foreach ($doc->childNodes as $child) {
			if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
				$child->parentNode->removeChild($child);
			}
		}

		$xpath = new \DOMXPath($doc);

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

		$string = $doc->saveXML($doc->documentElement, LIBXML_NOEMPTYTAG);
		$string = preg_replace('/\s+/', ' ', $string);

		return $string;
	}
}
