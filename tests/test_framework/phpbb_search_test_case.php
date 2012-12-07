<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

abstract class phpbb_search_test_case extends phpbb_database_test_case
{
	static protected function get_search_wrapper($class)
	{
		$wrapped = $class . '_wrapper';
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
