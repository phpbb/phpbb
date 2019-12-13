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
		$return       = ['status' => 'safe'];

		// Capture and normalize the BBCode name manually because there's no easy way to retrieve
		// it in TextFormatter <= 2.x
		if (preg_match('(\\[([-\\w]++))', $definition, $m))
		{
			$return['name'] = strtoupper($m[1]);
		}

		try
		{
			$configurator->BBCodes->addCustom($definition, $template);
		}
		catch (UnsafeTemplateException $e)
		{
			$return['status']     = 'unsafe';
			$return['error_text'] = $e->getMessage();
			$return['error_html'] = $e->highlightNode('<span class="highlight">');
		}
		catch (\Exception $e)
		{
			$return['status']     = (preg_match('(xml|xpath|xsl)i', $e->getMessage())) ? 'invalid_template' : 'invalid_definition';
			$return['error_text'] = $e->getMessage();
		}

		return $return;
	}
}
