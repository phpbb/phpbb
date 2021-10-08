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

namespace phpbb\textformatter\s9e;

use phpbb\textformatter\acp_utils_interface;
use s9e\TextFormatter\Configurator\Exceptions\UnsafeTemplateException;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;

class acp_utils implements acp_utils_interface
{
	/**
	* @var factory $factory
	*/
	protected $factory;

	/**
	* @param factory $factory
	*/
	public function __construct(factory $factory)
	{
		$this->factory = $factory;
	}

	/**
	* {@inheritdoc}
	*/
	public function analyse_bbcode(string $definition, string $template): array
	{
		$configurator = $this->factory->get_configurator();
		$return       = ['status' => self::BBCODE_STATUS_SAFE];

		// Capture and normalize the BBCode name manually because there's no easy way to retrieve
		// it in TextFormatter <= 2.x
		if (preg_match('(\\[([-\\w]++))', $definition, $m))
		{
			$return['name'] = strtoupper($m[1]);
		}

		try
		{
			$configurator->BBCodes->addCustom($definition, $template);

			if (!$this->template_can_be_used_in_element($template, 'div'))
			{
				$return['status'] = self::BBCODE_STATUS_INVALID_TEMPLATE;
			}
		}
		catch (UnsafeTemplateException $e)
		{
			$return['status']     = self::BBCODE_STATUS_UNSAFE;
			$return['error_text'] = $e->getMessage();
			$return['error_html'] = $e->highlightNode('<span class="highlight">');
		}
		catch (\Exception $e)
		{
			$return['status']     = (preg_match('(xml|xpath|xsl)i', $e->getMessage())) ? self::BBCODE_STATUS_INVALID_TEMPLATE : self::BBCODE_STATUS_INVALID_DEFINITION;
			$return['error_text'] = $e->getMessage();
		}

		return $return;
	}

	protected function template_can_be_used_in_element(string $template, string $element): bool
	{
		$parent = new TemplateInspector('<' . $element . '><xsl:apply-templates/></' . $element . '>');
		$child  = new TemplateInspector($template);

		return $parent->allowsChild($child);
	}
}
