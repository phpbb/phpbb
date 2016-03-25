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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';

class phpbb_text_processing_make_clickable_test extends phpbb_test_case
{
	public function make_clickable_data()
	{
		// value => whether it should work
		$prefix_texts = array(
			'' => true,
			"np \n" => true,
			'bp text ' => true,
			'cp text>' => true,
			'ep text.' => array('w' => false), // doesn't work for www. type urls, but for everything else
		);
		$suffix_texts = array(
			'' => true,
			"\n ns" => true,
			' bs text.' => true,
			'&gt;cs text' => true,
			'&quot;ds text' => true,
			'. es text.' => true,
			', fs text.' => true,
		);

		$urls = array(
			'http://example.com' => array('tag' => 'm', 'url' => false, 'text' => false), // false means same as key
			'http://example.com/' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://example.com/path?query=abc' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://example.com/1' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://example.com/some/very/long/path/with/over/55/characters?and=a&amp;long=query&amp;too=1' => array('tag' => 'm', 'url' => false, 'text' => 'http://example.com/some/very/long/path/ ... uery&amp;too=1'),
			'http://localhost' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://localhost/#abc' => array('tag' => 'm', 'url' => false, 'text' => false),

			'www.example.com/path/' => array('tag' => 'w', 'url' => 'http://www.example.com/path/', 'text' => false),
			'randomwww.example.com/path/' => false,

			'http://thisdomain.org' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://thisdomain.org/' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://thisdomain.org/1' => array('tag' => 'l', 'url' => false, 'text' => '1'),
			'http://thisdomain.org/path/some?query=abc#test' => array('tag' => 'l', 'url' => false, 'text' => 'path/some?query=abc#test'),

			'javascript:www.example.com/' => false,
		);

		$test_data = array();

		// run the test for each combination
		foreach ($prefix_texts as $prefix => $prefix_success)
		{
			foreach ($suffix_texts as $suffix => $suffix_success)
			{
				foreach ($urls as $url => $url_type)
				{
					$input = $prefix . $url . $suffix;
					// no valid url => no change
					$output = $input;

					if (
						($prefix_success && $suffix_success && is_array($url_type)) &&
						// handle except syntax for prefix/suffix
						(!is_array($prefix_success) || !isset($prefix_success[$url_type['tag']]) || $prefix_success[$url_type['tag']] == true) &&
						(!is_array($suffix_success) || !isset($suffix_success[$url_type['tag']]) || $suffix_success[$url_type['tag']] == true)
					)
					{
						// false means it's the same as the url, less typing
						$url_type['url'] = ($url_type['url']) ? $url_type['url'] : $url;
						$url_type['text'] = ($url_type['text']) ? $url_type['text'] : $url;

						$class = ($url_type['tag'] === 'l') ? 'postlink-local' : 'postlink';

						// replace the url with the desired output format
						$output = $prefix . '<!-- ' . $url_type['tag'] . ' --><a class="' . $class . '" href="' . $url_type['url'] . '">' . $url_type['text'] . '</a><!-- ' . $url_type['tag'] . ' -->' . $suffix;
					}
					$test_data[] = array($input, $output);
				}
			}
		}

		return $test_data;
	}

	/**
	* @dataProvider make_clickable_data
	*/
	public function test_make_clickable($input, $expected)
	{
		$result = make_clickable($input, 'http://thisdomain.org');

		$label = 'Making text clickable: ' . $input;
		$this->assertEquals($expected, $result, $label);
	}

	public function make_clickable_mixed_serverurl_data()
	{
		$urls = array(
			'http://thisdomain.org' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://thisdomain.org/' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://thisdomain.org/1' => array('tag' => 'm', 'url' => false, 'text' => false),
			'http://thisdomain.org/path/some?query=abc#test' => array('tag' => 'm', 'url' => false, 'text' => false),

			'https://www.phpbb.com' => array('tag' => 'm', 'url' => false, 'text' => false),
			'https://www.phpbb.com/' => array('tag' => 'm', 'url' => false, 'text' => false),
			'https://www.phpbb.com/1' => array('tag' => 'l', 'url' => false, 'text' => '1'),
			'https://www.phpbb.com/path/some?query=abc#test' => array('tag' => 'l', 'url' => false, 'text' => 'path/some?query=abc#test'),
		);

		$test_data = array();

		// run the test for each combination
		foreach ($urls as $url => $url_type)
		{
			// false means it's the same as the url, less typing
			$url_type['url'] = ($url_type['url']) ? $url_type['url'] : $url;
			$url_type['text'] = ($url_type['text']) ? $url_type['text'] : $url;

			$class = ($url_type['tag'] === 'l') ? 'postlink-local' : 'postlink';

			// replace the url with the desired output format
			$output = '<!-- ' . $url_type['tag'] . ' --><a class="' . $class . '" href="' . $url_type['url'] . '">' . $url_type['text'] . '</a><!-- ' . $url_type['tag'] . ' -->';

			$test_data[] = array($url, $output);
		}

		return $test_data;
	}

	/**
	* @dataProvider make_clickable_mixed_serverurl_data
	*/
	public function test_make_clickable_mixed_serverurl($input, $expected)
	{
		$result = make_clickable($input, 'https://www.phpbb.com');

		$label = 'Making text clickable: ' . $input;
		$this->assertEquals($expected, $result, $label);
	}

}

