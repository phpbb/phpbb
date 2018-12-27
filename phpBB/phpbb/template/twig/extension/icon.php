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

class icon extends \Twig_Extension
{
	/** @var \phpbb\user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\user	$user	User object
	 */
	public function __construct(\phpbb\user $user)
	{
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'icon';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('icon', array($this, 'icon'), array('needs_environment' => true)),
		);
	}

	/**
	 * Generate icon HTML for use in the template, depending on the mode.
	 *
	 * @param \phpbb\template\twig\environment	$environment	Environment object
	 * @param string							$type			Icon type (font|png|svg)
	 * @param string							$icon			Icon name (eg. "bold")
	 * @param string							$classes		Additional classes (eg. "fa-fw")
	 * @param string							$title			Icon title
	 * @param bool								$hidden			Hide the icon title from view
	 * @param array								$attributes		Additional attributes for the icon, where key is the attribute.
	 *                             								{'data-ajax': 'mark_forums'} results in ' data-ajax="mark_forums"'
	 * @return string
	 */
	public function icon($environment, $type = '', $icon = '', $classes = '', $title = '', $hidden = false, $attributes = array())
	{
		$path = $s_title = $s_attributes = '';
		$icon_path = 'imgs/icons/' . $type . '/' . $icon . '.' . $type;

		switch ($type)
		{
			case 'font':
				// Continue
			break;

			case 'png':
				$root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $environment->get_web_root_path();
				//$style_path = 'styles/' . rawurlencode($this->user->style['style_path']) . '/'; @todo
				$style_path = 'styles/all/';
				$path = $root_path . $style_path . $icon_path;
			break;

			case 'svg':
				// The title needs to be stripped of cases and replace the spaces with a dash
				$s_title = str_replace(' ', '-', utf8_strtolower($title));

				// Try and render the svg file
				try
				{
					$svg = $environment->render('../' . $icon_path); // @todo ../ to get outside of /template and into /imgs
					preg_match('/d=(["\'])([^"\']+?)\1/', $svg, $match);
					$path = $match[2];
				}
				catch (\Twig_Error $e)
				{
					// If rendering was not possible, we return an empty string
					return '';
				}
			break;

			default:
				// Not a supported type (font|png|svg), return an empty string
				return '';
			break;
		}

		// Implode any additional attributes
		if ($attributes)
		{
			foreach ($attributes as $attribute => $value)
			{
				$s_attributes .= ' ' . $attribute . '="' . $value . '"';
			}
		}

		// Try and render the icon
		try
		{
			return $environment->render('../views/macros/icons.twig', array(
				'TYPE'			=> $type,
				'ICON'			=> $icon,

				'CLASSES'		=> $classes,
				'S_CLASSES'		=> $classes ? ' ' . $classes : '',		// Add space for easier templating

				'TITLE'			=> $title,
				'TITLE_CLEAN'	=> $s_title,

				'S_ATTRIBUTES'	=> $s_attributes,						// Attributes already have space in front
				'S_HIDDEN'		=> $hidden,

				'T_PATH'		=> $path,
			));
		}
		catch (\Twig_Error $e)
		{
			// If rendering was not possible, we return an empty string
			return '';
		}
	}
}
