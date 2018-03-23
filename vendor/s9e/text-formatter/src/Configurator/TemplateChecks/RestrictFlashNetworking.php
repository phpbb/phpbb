<?php

/*
* @package   s9e\TextFormatter
* @copyright Copyright (c) 2010-2017 The s9e Authors
* @license   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
namespace s9e\TextFormatter\Configurator\TemplateChecks;
class RestrictFlashNetworking extends AbstractFlashRestriction
{
	public $defaultSetting = 'all';
	protected $settingName = 'allowNetworking';
	protected $settings = [
		'all'      => 3,
		'internal' => 2,
		'none'     => 1
	];
}