<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../test_framework/phpbb_search_test_case.php';

abstract class phpbb_search_common_test_case extends phpbb_search_test_case
{
	public function keywords()
	{
		return array(
			// keywords
			// terms
			// ok
			// split words
			// common words
			array(
				'fooo',
				'all',
				true,
				array('fooo'),
				array(),
			),
			array(
				'fooo baar',
				'all',
				true,
				array('fooo', 'baar'),
				array(),
			),
			// leading, trailing and multiple spaces
			array(
				'      fooo    baar   ',
				'all',
				true,
				array('fooo', 'baar'),
				array(),
			),
			// words too short
			array(
				'f',
				'all',
				false,
				null,
				// short words count as "common" words
				array('f'),
			),
			array(
				'f o o',
				'all',
				false,
				null,
				array('f', 'o', 'o'),
			),
			array(
				'f -o -o',
				'all',
				false,
				null,
				array('f', '-o', '-o'),
			),
			array(
				'fooo -baar',
				'all',
				true,
				array('-baar', 'fooo'),
				array(),
			),
			// all negative
			array(
				'-fooo',
				'all',
				true,
				array('-fooo'),
				array(),
			),
			array(
				'-fooo -baar',
				'all',
				true,
				array('-fooo', '-baar'),
				array(),
			),
			array(
				'fooo -fooo',
				'all',
				true,
				array('fooo', '-fooo'),
				array(),
			),
			array(
				'fooo fooo-',
				'all',
				true,
				array('fooo', 'fooo'),
				array(),
			),
			array(
				'-fooo fooo',
				'all',
				true,
				array('-fooo', 'fooo'),
				array(),
			),
			array(
				'fooo- fooo',
				'all',
				true,
				array('fooo', 'fooo'),
				array(),
			),
			array(
				'fooo-baar fooo',
				'all',
				true,
				array('fooo', 'baar', 'fooo'),
				array(),
			),
			array(
				'fooo-baar -fooo',
				'all',
				true,
				array('fooo', 'baar', '-fooo'),
				array(),
			),
			array(
				'fooo-baar fooo-',
				'all',
				true,
				array('fooo', 'baar', 'fooo'),
				array(),
			),
			array(
				'fooo-baar baar',
				'all',
				true,
				array('fooo', 'baar', 'baar'),
				array(),
			),
			array(
				'fooo-baar -baar',
				'all',
				true,
				array('fooo', 'baar', '-baar'),
				array(),
			),
			array(
				'fooo-baar baar-',
				'all',
				true,
				array('fooo', 'baar', 'baar'),
				array(),
			),
			array(
				'fooo-baar fooo-baar',
				'all',
				true,
				array('fooo', 'baar', 'fooo', 'baar'),
				array(),
			),
			array(
				'fooo-baar -fooo-baar',
				'all',
				true,
				array('fooo', 'baar', '-fooo', 'baar'),
				array(),
			),
			array(
				'fooo-baar fooo-baar-',
				'all',
				true,
				array('fooo', 'baar', 'fooo', 'baar'),
				array(),
			),
			array(
				'fooo-baar-baaz',
				'all',
				true,
				array('fooo', 'baar', 'baaz'),
				array(),
			),
		);
	}

	/**
	* @dataProvider keywords
	*/
	public function test_split_keywords($keywords, $terms, $ok, $split_words, $common)
	{
		$rv = $this->search->split_keywords($keywords, $terms);
		$this->assertEquals($ok, $rv);
		if ($ok)
		{
			// only check criteria if the search is going to be performed
			$this->assert_array_content_equals($split_words, $this->search->get_split_words());
		}
		$this->assert_array_content_equals($common, $this->search->get_common_words());
	}
}
