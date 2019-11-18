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

namespace phpbb\acp\controller;

class php_info
{
	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\acp\helper\controller	$helper		ACP Controller helper object
	 * @param \phpbb\language\language		$language	Language object
	 * @param \phpbb\template\template		$template	Template object
	 */
	public function __construct(
		\phpbb\acp\helper\controller $helper,
		\phpbb\language\language $language,
		\phpbb\template\template $template
	)
	{
		$this->helper	= $helper;
		$this->language	= $language;
		$this->template	= $template;
	}

	public function main()
	{
		ob_start();
		phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES);
		$phpinfo = ob_get_clean();

		$phpinfo = trim($phpinfo);

		// Here we play around a little with the PHP Info HTML to try and stylise
		// it along phpBB's lines ... hopefully without breaking anything. The idea
		// for this was nabbed from the PHP annotated manual
		preg_match_all('#<body[^>]*>(.*)</body>#si', $phpinfo, $output);

		if (empty($phpinfo) || empty($output[1][0]))
		{
			return trigger_error('NO_PHPINFO_AVAILABLE', E_USER_WARNING);
		}

		$output = $output[1][0];

		// expose_php can make the image not exist
		if (preg_match('#<a[^>]*><img[^>]*></a>#', $output))
		{
			$output = preg_replace('#<tr class="v"><td>(.*?<a[^>]*><img[^>]*></a>)(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2"><tr><td>\2</td><td>\1</td></tr></table></td></tr>', $output);
		}
		else
		{
			$output = preg_replace('#<tr class="v"><td>(.*?)</td></tr>#s', '<tr class="row1"><td><table class="type2"><tr><td>\1</td></tr></table></td></tr>', $output);
		}
		$output = preg_replace('#<table[^>]+>#i', '<table>', $output);
		$output = preg_replace('#<img border="0"#i', '<img', $output);
		$output = str_replace(['class="e"', 'class="v"', 'class="h"', '<hr />', '<font', '</font>'], ['class="row1"', 'class="row2"', '', '', '<span', '</span>'], $output);

		// Fix invalid anchor names (eg "module_Zend Optimizer")
		$output = preg_replace_callback('#<a name="([^"]+)">#', [$this, 'remove_spaces'], $output);

		if (empty($output))
		{
			return trigger_error('NO_PHPINFO_AVAILABLE', E_USER_WARNING);
		}

		$orig_output = $output;

		preg_match_all('#<div class="center">(.*)</div>#siU', $output, $output);
		$output = !empty($output[1][0]) ? $output[1][0] : $orig_output;

		$this->template->assign_var('PHPINFO', $output);

		return $this->helper->render('acp_php_info.html', $this->language->lang('ACP_PHP_INFO'));
	}

	/**
	 * Remove spaces from anchor names.
	 *
	 * @param array		$matches
	 * @return string
	 */
	protected function remove_spaces(array $matches)
	{
		return '<a name="' . str_replace(' ', '_', $matches[1]) . '">';
	}
}
