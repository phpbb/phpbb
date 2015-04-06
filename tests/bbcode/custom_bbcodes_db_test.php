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
require_once dirname(__FILE__) . '/../../phpBB/includes/bbcode.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/message_parser.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/acp/acp_bbcodes.php';

class phpbb_custom_bbcodes_db_test extends phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/custom_bbcodes.xml');
	}

	public function setUp()
	{
		parent::setUp();

		global $cache, $config, $db, $phpbb_dispatcher, $phpbb_path_helper, $request, $user;

		$config = new \phpbb\config\config(array(
			'max_post_chars'		=> 0,
			'min_post_chars'		=> 0,
			'max_post_smilies'		=> 0,
			'max_post_urls'			=> 0,
			'max_post_font_size'	=> 0,
			'max_post_img_height'	=> 0,
			'max_post_img_width'	=> 0,
		));
		set_config(null, null, null, $config);
		set_config_count(null, null, null, $config);

		$db = $this->new_dbal();
		$filesystem = new \phpbb\filesystem();
		$phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$request = new phpbb_mock_request();

		$user = new phpbb_mock_user();
		$user->optionset('viewcensors', true);
		$user->style = array(
			'style_path'		=> 'prosilver',
		);

		$phpbb_root_path = $filesystem->clean_path(dirname(__FILE__) . '/../../phpBB/');
		$phpEx = substr(strrchr(__FILE__, '.'), 1);

		$cache = new \phpbb\cache\service(
			new phpbb\cache\driver\null(),
			$config,
			$db,
			$phpbb_root_path,
			$phpEx
		);

		$phpbb_path_helper = new \phpbb\path_helper(
			new \phpbb\symfony_request($request),
			$filesystem,
			$this->getMock('\phpbb\request\request'),
			$phpbb_root_path,
			$phpEx
		);

		$acp_bbcodes = new acp_bbcodes();

		$sql = 'SELECT bbcode_id, bbcode_match, bbcode_tpl
			FROM ' . BBCODES_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$bbcode_data = $acp_bbcodes->build_regexp($row['bbcode_match'], $row['bbcode_tpl']);

			$sql = 'UPDATE ' . BBCODES_TABLE . '
				SET ' . $db->sql_build_array('UPDATE', $bbcode_data) . '
				WHERE bbcode_id = ' . (int) $row['bbcode_id'];
			$db->sql_query($sql);
		}
		$db->sql_freeresult($result);
	}

	public function custom_bbcode_data()
	{
		return array(
			array(
				'Custom BBCode with INTTEXT may not allow ".',
				'[inttext]Text with quotations marks "" should not work[/inttext]',
				'[inttext]Text with quotations marks "" should not work[/inttext]',
			),
			// I copied these words from the ticket description - they probably don't make any sense together
			array(
				'Custom BBCode with INTTEXT must allow Arabic letters.',
				'[inttext]Text with Arabic letters تغير تغيّر should work[/inttext]',
				'Text with Arabic letters تغير تغيّر should work',
			),
			array(
				'Custom BBCode with INTTEXT must allow other UTF8-characters.',
				'[inttext]Text with some other ÜTF8-characters pℎpBB should work[/inttext]',
				'Text with some other ÜTF8-characters pℎpBB should work',
			),
			array(
				'Custom BBCode with INTTEXT must allow normal characters.',
				'Cool text: [inttext]Nice text.[/inttext]',
				'Cool text: Nice text.',
			),
		);
	}

	/**
	* @dataProvider custom_bbcode_data
	*/
	public function test_custom_bbcode($test_msg, $text, $expected)
	{
		$message_parser = new \parse_message($text);
		$message_parser->parse(true, true, true);
		$message_parser->format_display(true, true, true);

		$this->assertEquals($expected, $message_parser->message, $test_msg);
	}
}
