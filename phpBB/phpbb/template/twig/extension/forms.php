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
use phpbb\user;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class forms extends AbstractExtension
{
	/** @var user */
	protected $user;

	/**
	 * Constructor.
	 *
	 * @param user	$user			User object
	 */
	public function __construct(user $user)
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
		return 'forms';
	}

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return TwigFunction[]			Array of twig functions
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction('FormsBuildTemplate', [$this, 'build_template'], ['needs_environment' => true]),
			new TwigFunction('FormsDimension', [$this, 'dimension'], ['needs_environment' => true]),
			new TwigFunction('FormsInput', [$this, 'input'], ['needs_environment' => true]),
			new TwigFunction('FormsRadioButtons', [$this, 'radio_buttons'], ['needs_environment' => true]),
			new TwigFunction('FormsSelect', [$this, 'select'], ['needs_environment' => true]),
			new TwigFunction('FormsTextarea', [$this, 'textarea'], ['needs_environment' => true]),
		];
	}

	/**
	 * Renders a form template
	 *
	 * @param environment $environment
	 * @param array $form_data
	 *
	 * @return string Rendered form template
	 */
	public function build_template(environment $environment, array $form_data): string
	{
		try
		{
			return $environment->render('macros/forms/build_template.twig', [
				'form_data' => $form_data ?? [],
			]);
		}
		catch (\Twig\Error\Error $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Renders form dimension fields
	 *
	 * @param environment $environment The twig environment
	 * @param array $form_data The form data
	 *
	 * @return string Form dimension fields
	 */
	public function dimension(environment $environment, array $form_data): string
	{
		try
		{
			return $environment->render('macros/forms/dimension.twig', [
				'WIDTH'		=> $form_data['width'],
				'HEIGHT'		=> $form_data['height'],
			]);
		}
		catch (\Twig\Error\Error $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Renders a form input field
	 *
	 * @param environment	$environment		The twig environment
	 * @param array			$form_data			The form data
	 *
	 * @return string Form input field
	 */
	public function input(environment $environment, array $form_data): string
	{
		try
		{
			return $environment->render('macros/forms/input.twig', [
				'CLASS'		=> (string) ($form_data['class'] ?? ''),
				'ID'		=> (string) ($form_data['id'] ?? ''),
				'DATA'		=> $form_data['data'] ?? [],
				'TYPE'		=> (string) $form_data['type'],
				'NAME'		=> (string) $form_data['name'],
				'SIZE'		=> (int) ($form_data['size'] ?? 0),
				'MAXLENGTH'	=> (int) ($form_data['maxlength'] ?? 0),
				'MIN'		=> (int) ($form_data['min'] ?? 0),
				'MAX'		=> (int) ($form_data['max'] ?? 0),
				'STEP'		=> (int) ($form_data['step'] ?? 0),
				'CHECKED'	=> (bool) ($form_data['checked'] ?? false),
				'DISABLED'	=> (bool) ($form_data['disabled'] ?? false),
				'VALUE'		=> (string) ($form_data['value']),
			]);
		}
		catch (\Twig\Error\Error $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Renders form radio buttons
	 *
	 * @param environment $environment The twig environment
	 * @param array $form_data The form data
	 *
	 * @return string Form radio buttons
	 */
	public function radio_buttons(environment $environment, array $form_data): string
	{
		try
		{
			return $environment->render('macros/forms/radio_buttons.twig', [
				'BUTTONS'	=>	$form_data['buttons'],
			]);
		}
		catch (\Twig\Error\Error $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Renders a form select field
	 *
	 * @param environment	$environment		The twig environment
	 * @param array			$form_data			The form data
	 *
	 * @return string Form select field
	 */
	public function select(environment $environment, array $form_data): string
	{
		try
		{
			return $environment->render('macros/forms/select.twig', [
				'CLASS'			=> (string) ($form_data['class'] ?? ''),
				'ID'			=> (string) ($form_data['id'] ?? ''),
				'DATA'			=> $form_data['data'] ?? [],
				'NAME'			=> (string) ($form_data['name'] ?? ''),
				'TOGGLEABLE'	=> (bool) ($form_data['toggleable'] ?? false),
				'OPTIONS'		=> $form_data['options'] ?? [],
				'GROUP_ONLY'	=> (bool) ($form_data['group_only'] ?? false),
				'SIZE'			=> (int) ($form_data['size'] ?? 0),
				'MULTIPLE'		=> (bool) ($form_data['multiple'] ?? false),
				'ONCHANGE'		=> (string) ($form_data['onchange'] ?? ''),
			]);
		}
		catch (\Twig\Error\Error $e)
		{
			return $e->getMessage();
		}
	}

	/**
	 * Renders a form textarea field
	 *
	 * @param environment $environment
	 * @param array $form_data
	 *
	 * @return string Form textarea field
	 */
	public function textarea(environment $environment, array $form_data): string
	{
		try
		{
			return $environment->render('macros/forms/textarea.twig', [
				'CLASS'		=> (string) ($form_data['class'] ?? ''),
				'ID'		=> (string) $form_data['id'],
				'DATA'		=> $form_data['data'] ?? [],
				'NAME'		=> (string) $form_data['name'],
				'ROWS'		=> (int) ($form_data['rows'] ?? ''),
				'COLS'		=> (int) ($form_data['cols'] ?? ''),
				'CONTENT'	=> (string) ($form_data['content'] ?? ''),
				'PLACEHOLDER'	=> (string) ($form_data['placeholder'] ?? ''),
			]);
		}
		catch (\Twig\Error\Error $e)
		{
			return $e->getMessage();
		}
	}
}
