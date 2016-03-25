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

require_once dirname(__FILE__) . '/base.php';

/**
* @group functional
*/
class phpbb_functional_search_native_test extends phpbb_functional_search_base
{
	protected $search_backend = '\phpbb\search\fulltext_native';
}
