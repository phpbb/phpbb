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

class phpbb_datetime_meridiem_test extends phpbb_test_case
{
	protected function setUp(): void
	{
		global $phpbb_dispatcher;

		parent::setUp();

		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();

		// Date formats are cached per language name, start with an empty cache for every test
		$reflection = new ReflectionProperty('\phpbb\datetime', 'format_cache');
		$reflection->setAccessible(true);
		$reflection->setValue(null, array());
	}

	protected function get_user($lang_name, array $datetime)
	{
		$lang = $this->getMockBuilder('\phpbb\language\language')
			->disableOriginalConstructor()
			->getMock();
		$lang->method('get_lang_array')
			->willReturn(array(
				'datetime'	=> array_merge(array(
					'TODAY'		=> 'Today',
					'TOMORROW'	=> 'Tomorrow',
					'YESTERDAY'	=> 'Yesterday',
					'AGO'		=> array(
						0		=> 'less than a minute ago',
						1		=> '%d minute ago',
						2		=> '%d minutes ago',
					),
				), $datetime),
			));

		$user = new \phpbb\user($lang, '\phpbb\datetime');
		$user->timezone = new DateTimeZone('UTC');
		$user->lang_name = $lang_name;

		return $user;
	}

	public function format_data()
	{
		$arabic = array(
			'Fri'		=> 'الجمعة',
			'MERIDIEM'	=> array(
				'am'	=> 'ص',
				'pm'	=> 'م',
				'AM'	=> 'ص',
				'PM'	=> 'م',
			),
		);

		// Translations which contain date format characters
		$german = array(
			'MERIDIEM'	=> array(
				'am'	=> 'vorm.',
				'pm'	=> 'nachm.',
				'AM'	=> 'VORM.',
				'PM'	=> 'NACHM.',
			),
		);

		$english = array(
			'MERIDIEM'	=> array(
				'am'	=> 'am',
				'pm'	=> 'pm',
				'AM'	=> 'AM',
				'PM'	=> 'PM',
			),
		);

		$partial = array(
			'MERIDIEM'	=> array(
				'pm'	=> 'nachm.',
			),
		);

		return array(
			// Lowercase and uppercase, ante and post meridiem
			array($arabic, '2012-06-08 14:01:02', 'g:i a', '2:01 م'),
			array($arabic, '2012-06-08 09:05:00', 'g:i A', '9:05 ص'),

			// Meridiem at the start of the format and next to other characters
			array($arabic, '2012-06-08 14:01:02', 'A g:i', 'م 2:01'),
			array($arabic, '2012-06-08 14:01:02', 'g:iA', '2:01م'),

			// Combined with other translated date strings
			array($arabic, '2012-06-08 14:01:02', 'D g:i A', 'الجمعة 2:01 م'),

			// Translations containing format characters are output literally
			array($german, '2012-06-08 14:01:02', 'H:i a', '14:01 nachm.'),
			array($german, '2012-06-08 09:05:00', 'H:i A', '09:05 VORM.'),

			// Escaped format characters are not translated, escaped backslashes are kept
			array($german, '2012-06-08 14:01:02', '\A g:i a', 'A 2:01 nachm.'),
			array($german, '2012-06-08 14:01:02', 'g:i \\\\A', '2:01 \NACHM.'),

			// Formats without meridiem are not changed
			array($german, '2012-06-08 14:01:02', 'Y-m-d H:i', '2012-06-08 14:01'),

			// Untranslated meridiem and language packs without the translations
			array($english, '2012-06-08 14:01:02', 'g:i a A', '2:01 pm PM'),
			array($partial, '2012-06-08 14:01:02', 'g:i a A', '2:01 nachm. PM'),
			array(array(), '2012-06-08 14:01:02', 'g:i a A', '2:01 pm PM'),
		);
	}

	/**
	* @dataProvider format_data
	*/
	public function test_format($datetime, $time, $format, $expected)
	{
		$user = $this->get_user('xx', $datetime);
		$timestamp = $user->get_timestamp_from_format('Y-m-d H:i:s', $time, new DateTimeZone('UTC'));

		$this->assertSame($expected, $user->format_date($timestamp, $format, true));
	}

	public function test_relative_format()
	{
		// Avoid the "x minutes ago" output by using a time far away from the current time
		$testing_time = gmdate('H') <= 12 ? '18:01' : '06:01';
		$expected_meridiem = gmdate('H') <= 12 ? 'م' : 'ص';

		$user = $this->get_user('xx', array(
			'MERIDIEM'	=> array(
				'am'	=> 'ص',
				'pm'	=> 'م',
				'AM'	=> 'ص',
				'PM'	=> 'م',
			),
		));
		$timestamp = $user->get_timestamp_from_format('Y-m-d H:i', gmdate('Y-m-d') . ' ' . $testing_time, new DateTimeZone('UTC'));

		// Both testing times are 6:01 in the 12-hour format
		$this->assertSame('Today 6:01 ' . $expected_meridiem, $user->format_date($timestamp, '|Y-m-d| g:i A'));
		$this->assertSame(gmdate('Y-m-d') . ' 6:01 ' . $expected_meridiem, $user->format_date($timestamp, '|Y-m-d| g:i A', true));
	}

	public function test_user_context()
	{
		// The translation of the user passed to the datetime object has to be used, not the one of the global user
		$arabic_user = $this->get_user('ar', array(
			'MERIDIEM'	=> array(
				'am'	=> 'ص',
				'pm'	=> 'م',
				'AM'	=> 'ص',
				'PM'	=> 'م',
			),
		));
		$german_user = $this->get_user('de', array(
			'MERIDIEM'	=> array(
				'am'	=> 'vorm.',
				'pm'	=> 'nachm.',
				'AM'	=> 'VORM.',
				'PM'	=> 'NACHM.',
			),
		));

		$timestamp = $arabic_user->get_timestamp_from_format('Y-m-d H:i:s', '2012-06-08 14:01:02', new DateTimeZone('UTC'));

		$this->assertSame('2:01 م', $arabic_user->format_date($timestamp, 'g:i A', true));
		$this->assertSame('2:01 NACHM.', $german_user->format_date($timestamp, 'g:i A', true));
		$this->assertSame('2:01 م', $arabic_user->format_date($timestamp, 'g:i A', true));
	}
}
