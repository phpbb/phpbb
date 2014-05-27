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

abstract class phpbb_search_test_case extends phpbb_database_test_case
{
	static protected function get_search_wrapper($class)
	{
		$wrapped = str_replace('\\', '_', $class) . '_wrapper';
		if (!class_exists($wrapped))
		{
			$code = "
class $wrapped extends $class
{
	public function get_must_contain_ids() { return \$this->must_contain_ids; }
	public function get_must_not_contain_ids() { return \$this->must_not_contain_ids; }
	public function get_split_words() { return \$this->split_words; }
}
			";
			eval($code);
		}
		return $wrapped;
	}
}
