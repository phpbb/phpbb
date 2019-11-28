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

use phpbb\textformatter\s9e\factory;
use s9e\TextFormatter\Configurator\Helpers\TemplateHelper;
use s9e\TextFormatter\Configurator\Items\UnsafeTemplate;

class bbcode_merger
{
	/**
	* @var \s9e\TextFormatter\Configurator $configurator Configurator instance used to inspect BBCodes
	*/
	protected $configurator;

	/**
	* @param \phpbb\textformatter\s9e\factory $factory
	*/
	public function __construct(factory $factory)
	{
		$this->configurator = $factory->get_configurator();
	}

	/**
	* Merge two BBCode definitions
	*
	* All of the arrays contain a "usage" element and a "template" element
	*
	* @throws InvalidArgumentException if a definition cannot be interpreted
	* @throws RuntimeException if something unexpected occurs
	*
	* @param  array $without BBCode definition without an attribute
	* @param  array $with    BBCode definition with an attribute
	* @return array          Merged definition
	*/
	public function merge_bbcodes(array $without, array $with)
	{
		$without = $this->create_bbcode($without);
		$with    = $this->create_bbcode($with);

		// Select the appropriate strategy for merging this BBCode
		if (!$this->is_optional_bbcode($without, $with) && $this->is_content_bbcode($without, $with))
		{
			$merged = $this->merge_content_bbcode($without, $with);
		}
		else
		{
			$merged = $this->merge_optional_bbcode($without, $with);
		}

		$merged['template'] = $this->normalize_template($merged['template']);

		return $merged;
	}

	/**
	* Create a custom BBCode for inspection
	*
	* @param  array $definition Original BBCode definition
	* @return array             Updated definition containing a BBCode object and a Tag
	*/
	protected function create_bbcode(array $definition)
	{
		$bbcode = $this->configurator->BBCodes->addCustom(
			$definition['usage'],
			new UnsafeTemplate($definition['template'])
		);

		$definition['bbcode'] = $bbcode;
		$definition['tag']    = $this->configurator->tags[$bbcode->tagName];

		return $definition;
	}

	/**
	* Indent given template for readability
	*
	* @param  string $template
	* @return string
	*/
	protected function indent_template($template)
	{
		$dom = TemplateHelper::loadTemplate($template);
		$dom->formatOutput = true;
		$template = TemplateHelper::saveTemplate($dom);

		// Remove the first level of indentation if the template starts with whitespace
		if (preg_match('(^\\n +)', $template, $m))
		{
			$template = str_replace($m[0], "\n", $template);
		}

		return trim($template);
	}

	/**
	* Test whether the two definitions form a "content"-style BBCode
	*
	* Such BBCodes include the [url] BBCode, which uses its text content as
	* attribute if none is provided
	*
	* @param  array $without BBCode definition without an attribute
	* @param  array $with    BBCode definition with an attribute
	* @return bool
	*/
	protected function is_content_bbcode(array $without, array $with)
	{
		// Test whether we find the same non-TEXT token between "]" and "[" in the usage
		// as between ">" and "<" in the template
		return (preg_match('(\\]\\s*(\\{(?!TEXT)[^}]+\\})\\s*\\[)', $without['usage'], $m)
			&& preg_match('(>[^<]*?' . preg_quote($m[1]) . '[^>]*?<)s', $without['template']));
	}

	/**
	* Test whether the two definitions form BBCode with an optional attribute
	*
	* @param  array $without BBCode definition without an attribute
	* @param  array $with    BBCode definition with an attribute
	* @return bool
	*/
	protected function is_optional_bbcode(array $without, array $with)
	{
		// Remove the default attribute from the definition
		$with['usage'] = preg_replace('(=[^\\]]++)', '', $with['usage']);

		// Test whether both definitions are the same, regardless of case
		return strcasecmp($without['usage'], $with['usage']) === 0;
	}

	/**
	* Merge the two BBCode definitions of a "content"-style BBCode
	*
	* @param  array $without BBCode definition without an attribute
	* @param  array $with    BBCode definition with an attribute
	* @return array          Merged definition
	*/
	protected function merge_content_bbcode(array $without, array $with)
	{
		// Convert [x={X}] into [x={X;useContent}]
		$usage = preg_replace('(\\})', ';useContent}', $with['usage'], 1);

		// Use the template from the definition that uses an attribute
		$template = $with['tag']->template;

		return ['usage' => $usage, 'template' => $template];
	}

	/**
	* Merge the two BBCode definitions of a BBCode with an optional argument
	*
	* Such BBCodes include the [quote] BBCode, which takes an optional argument
	* but otherwise does not behave differently
	*
	* @param  array $without BBCode definition without an attribute
	* @param  array $with    BBCode definition with an attribute
	* @return array          Merged definition
	*/
	protected function merge_optional_bbcode(array $without, array $with)
	{
		// Convert [X={X}] into [X={X?}]
		$usage = preg_replace('(\\})', '?}', $with['usage'], 1);

		// Build a template for both versions
		$template = '<xsl:choose><xsl:when test="@' . $with['bbcode']->defaultAttribute . '">' . $with['tag']->template . '</xsl:when><xsl:otherwise>' . $without['tag']->template . '</xsl:otherwise></xsl:choose>';

		return ['usage' => $usage, 'template' => $template];
	}

	/**
	* Normalize a template
	*
	* @param  string $template
	* @return string
	*/
	protected function normalize_template($template)
	{
		// Normalize the template to simplify it
		$template = $this->configurator->templateNormalizer->normalizeTemplate($template);

		// Convert xsl:value-of elements back to {L_} tokens where applicable
		$template = preg_replace('(<xsl:value-of select="\\$(L_\\w+)"/>)', '{$1}', $template);

		// Beautify the template
		$template = $this->indent_template($template);

		return $template;
	}
}
