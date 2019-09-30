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

abstract class icon extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
	protected $twig;
	protected $user;

	public function __construct(environment $twig, \phpbb\user $user)
	{
		$this->twig = $twig;
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
	 * Returns a list of global variables to add to the existing list.
	 *
	 * @return array An array of global variables
	 */
	public function getGlobals()
	{
		$macros = null;

		try
		{
			$macros = $this->twig->loadTemplate('macros.html');
		}
		catch (\Twig\Error\Error $e)
		{
		}

		return [
			'macro'	=> $macros,
		];
	}

	public function getFilters()
	{
		return [
			new \Twig\TwigFilter('Png_path', [$this, 'png_path'], ['needs_environment' => true]),
		];
	}

	public function getFunctions()
	{
		return [
			new \Twig\TwigFunction('Svg_clean', [$this, 'svg_clean'], ['needs_environment' => true]),
			new \Twig\TwigFunction('Implode_attributes', [$this, 'implode_attributes']),
			new \Twig\TwigFunction('Implode_classes', [$this, 'implode_classes']),
		];
	}

	protected function png_path(environment $environment, $icon)
	{
		$board_url	= defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH;
		$web_path	= $board_url ? generate_board_url() . '/' : $environment->get_web_root_path();
		$style_path	= $this->user->style['style_path'];

		return "{$web_path}styles/{$style_path}/template/icons/png/{$icon}.png";
	}

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
		$doc->formatOutput = false;
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

	protected function implode_attributes(...$arguments)
	{
		$string = '';
		$attributes = [];

		foreach ($arguments as $argument)
		{
			if (is_string($argument))
			{
				$attributes[] = $argument;
			}
			else if (is_array($argument))
			{
				foreach ($argument as $key => $value)
				{
					if (is_integer($key) && is_string($value))
					{
						$attributes[] = $value;
					}
					else
					{
						$attributes[$key] = $value;
					}
				}
			}
		}

		foreach ($attributes as $attribute => $value)
		{
			if (is_string($attribute))
			{
				$string .= ' ' . $attribute . '="' . $value . '"';
			}
			else
			{
				$string .= ' ' . $attribute;
			}
		}

		return $string;
	}

	protected function implode_classes(...$arguments)
	{
		$classes = [];

		foreach ($arguments as $argument)
		{
			if (is_string($argument))
			{
				$classes[] = $argument;
			}
			else if (is_array($argument))
			{
				foreach ($argument as $key => $value)
				{
					if (is_integer($key) && is_string($value))
					{
						$classes[] = $value;
					}
					else if (is_string($key))
					{
						if ($value)
						{
							$classes[] = $key;
						}
					}
					else if (is_array($value))
					{
						foreach ($value as $class => $condition)
						{
							if ($condition)
							{
								$classes[] = $class;
							}
						}
					}
				}
			}
		}

		$string = implode(' ', array_unique($classes));

		return $string ? ' ' . $string : $string;
	}
}
