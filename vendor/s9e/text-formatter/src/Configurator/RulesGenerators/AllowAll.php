<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\RulesGenerators;
use s9e\TextFormatter\Configurator\Helpers\TemplateInspector;
use s9e\TextFormatter\Configurator\RulesGenerators\Interfaces\TargetedRulesGenerator;
class AllowAll implements TargetedRulesGenerator
{
	public function generateTargetedRules(TemplateInspector $src, TemplateInspector $trg)
	{
		return ['allowChild', 'allowDescendant'];
	}
}