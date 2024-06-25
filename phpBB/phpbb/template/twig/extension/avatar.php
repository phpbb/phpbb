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

use phpbb\avatar\helper;
use phpbb\avatar\manager;
use phpbb\template\twig\environment;
use Twig\Error\Error;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class avatar extends AbstractExtension
{
	/**
	 * @var helper
	 */
	private $avatar_helper;

	/**
	 * Constructor for avatar extension
	 *
	 * @param helper $avatar_helper
	 */
	public function __construct(helper $avatar_helper)
	{
		$this->avatar_helper = $avatar_helper;
	}

	/**
	 * Get the name of this extension
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return 'avatar';
	}

	/**
	 * Returns a list of global functions to add to the existing list.
	 *
	 * @return TwigFunction[] An array of global functions
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction('avatar', [$this, 'get_avatar'], ['needs_environment' => true]),
		];
	}

	/**
	 * Get avatar for placing into templates.
	 *
	 * How to use in a template:
	 * - {{ avatar('mode', row, alt, ignore_config, lazy) }}
	 *
	 * The mode and row (group_row or user_row) are required.
	 * The other fields (alt|ignore_config|lazy) are optional.
	 *
	 * @return string	The avatar HTML for the specified mode
	 */
	public function get_avatar(environment $environment, string $mode, array $row, ?string $alt, ?bool $ignore_config, ?bool $lazy): string
	{
		$alt = $alt ?? false;
		$ignore_config = $ignore_config ?? false;
		$lazy = $lazy ?? false;
		$row = manager::clean_row($row, $mode);
		$avatar = $this->avatar_helper->get_avatar($row, $alt, $ignore_config, $lazy);

		try
		{
			return $environment->render('macros/avatar.twig', [
				'SRC'		=> $avatar['lazy'] ? $this->avatar_helper->get_no_avatar_source() : $avatar['src'],
				'DATA_SRC'	=> $avatar['lazy'] ? $avatar['src'] : '',
				'WIDTH'		=> $avatar['width'],
				'HEIGHT'	=> $avatar['height'],
				'TITLE'		=> $avatar['title'],
				'LAZY'		=> $avatar['lazy'],
			]);
		}
		catch (Error $e)
		{
			return '';
		}
	}
}
