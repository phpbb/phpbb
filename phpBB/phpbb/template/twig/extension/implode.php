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

class implode extends \Twig\Extension\AbstractExtension
{
	/**
	 * Returns the name of this extension.
	 *
	 * @return string						The extension name
	 */
	public function getName()
	{
		return 'implode';
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return \Twig\TwigFunction[]			Array of twig functions
	 */
	public function getFunctions()
	{
		return [
			new \Twig\TwigFunction('Implode_attributes', [$this, 'implode_attributes']),
			new \Twig\TwigFunction('Implode_classes', [$this, 'implode_classes']),
		];
	}

	/**
	 * Implode an array of attributes to a string.
	 *
	 * This string will be prepended by a space for ease-of-use.
	 *
	 * Examples would be:
	 * Implode_attributes('checked', {'data-ajax': 'true'})
	 * Implode_attributes(['checked', {'data-ajax': 'true'}])
	 *
	 * @param mixed		$arguments			Attributes to implode
	 * @return string						The attributes string
	 */
	public function implode_attributes(...$arguments)
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
					else if (is_array($value))
					{
						foreach ($value as $k => $v)
						{
							if (is_integer($k) && is_string($v))
							{
								$attributes[] = $v;
							}
							else
							{

								$attributes[$k] = $v;
							}
						}
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
				$value = is_bool($value) ? ($value ? 'true' : 'false') : $value;

				$string .= ' ' . $attribute . '="' . $value . '"';
			}
			else
			{
				$string .= ' ' . $value;
			}
		}

		return $string;
	}

	/**
	 * Implode an array or classes to a string.
	 *
	 * This string will be prepended with a space for ease-of-use.
	 *
	 * Conditions can be added to the classes, which will determine if the classes is added to the string.
	 * @see https://twig.symfony.com/doc/2.x/functions/html_classes.html
	 *
	 * An example would be:
	 * Implode_classes('a-class', 'another-class', {
	 * 		'reported-class': S_POST_REPORTED,
	 * 		'hidden-class': S_POST_HIDDEN,
	 * })
	 *
	 * This function differs from the html_classes function linked above,
	 * in that it allows another depth level, so it also supports a single argument.
	 *
	 * An example would be:
	 * Implode_classes(['a-class', 'another-class', {
	 * 		'reported-class': S_POST_REPORTED,
	 * 		'hidden-class': S_POST_HIDDEN,
	 * }])
	 *
	 * @param mixed		$arguments			The classes to implode
	 * @return string						The classes string prepended with a space
	 */
	public function implode_classes(...$arguments)
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
