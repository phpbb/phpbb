<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Plugins\MediaEmbed\Configurator\TemplateGenerators;
use s9e\TextFormatter\Plugins\MediaEmbed\Configurator\TemplateGenerator;
class Iframe extends TemplateGenerator
{
	protected $defaultIframeAttributes = [
		'allowfullscreen' => '',
		'scrolling'       => 'no',
		'style'           => ['border' => '0']
	];
	protected $iframeAttributes = ['data-s9e-livepreview-ignore-attrs', 'data-s9e-livepreview-postprocess', 'onload', 'scrolling', 'src', 'style'];
	protected function getContentTemplate()
	{
		$attributes = $this->mergeAttributes($this->defaultIframeAttributes, $this->getFilteredAttributes());
		return '<iframe>' . $this->generateAttributes($attributes) . '</iframe>';
	}
	protected function getFilteredAttributes()
	{
		return \array_intersect_key($this->attributes, \array_flip($this->iframeAttributes));
	}
}