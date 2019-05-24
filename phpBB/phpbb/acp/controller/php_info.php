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

use phpbb\exception\http_exception;

class php_info
{
	/** @var \phpbb\acp\helper\controller */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\acp\helper\controller	$helper		ACP Controller helper object
	 * @param \phpbb\template\template		$template	Template object
	 */
	public function __construct(\phpbb\acp\helper\controller $helper, \phpbb\template\template $template)
	{
		$this->helper	= $helper;
		$this->template	= $template;
	}

	function main()
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
			throw new http_exception(400, 'NO_PHPINFO_AVAILABLE');
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
			throw new http_exception(400, 'NO_PHPINFO_AVAILABLE');
		}

		$orig_output = $output;

		preg_match_all('#<div class="center">(.*)</div>#siU', $output, $output);
		$output = !empty($output[1][0]) ? $output[1][0] : $orig_output;

		$this->template->assign_var('PHPINFO', $output);

		return $this->helper->render('acp_php_info.html', 'ACP_PHP_INFO');
	}

	function remove_spaces($matches)
	{
		return '<a name="' . str_replace(' ', '_', $matches[1]) . '">';
	}
}
