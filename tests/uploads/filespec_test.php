<?php
/**
 *
 * @package testing
 * @copyright (c) 20012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_upload.php';
require_once dirname(__FILE__) . '/../mock/fileupload.php';

class phpbb_filespec_test extends phpbb_test_case
{
	const TEST_COUNT = 100;
	const PREFIX = 'phpbb_';
	const MAX_STR_LEN = 50;

	// Hexadecimal encoded images
	public static $files = array(
		'GIF' => '47494638376101000100800000ffffffffffff2c00000000010001000002024401003b',
		'PNG' => '89504e470d0a1a0a0000000d4948445200000001000000010802000000907753de0000000c4944415408d763f8ffff3f0005fe02fedccc59e70000000049454e44ae426082',
		'TIF' => '49492a000c000000ffffff001000fe00040001000000000000000001030001000000010000000101030001000000010000000201030003000000d20000000301030001000000010000000601030001000000020000000d01020018000000d80000001101040001000000080000001201030001000000010000001501030001000000030000001601030001000000400000001701040001000000030000001a01050001000000f00000001b01050001000000f80000001c0103000100000001000000280103000100000002000000000000000800080008002f686f6d652f6b696d2f746d702f746966662e746966660000000048000000010000004800000001',
		'JPG' => 'ffd8ffe000104a46494600010101004800480000ffdb004300ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffdb004301ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffc20011080001000103012200021101031101ffc4001500010100000000000000000000000000000002ffc40014010100000000000000000000000000000000ffda000c03010002100310000001a07fffc40014100100000000000000000000000000000000ffda00080101000105027fffc40014110100000000000000000000000000000000ffda0008010301013f017fffc40014110100000000000000000000000000000000ffda0008010201013f017fffc40014100100000000000000000000000000000000ffda0008010100063f027fffc40014100100000000000000000000000000000000ffda0008010100013f217fffda000c0301000200030000001003ffc40014110100000000000000000000000000000000ffda0008010301013f107fffc40014110100000000000000000000000000000000ffda0008010201013f107fffc40014100100000000000000000000000000000000ffda0008010100013f107fffd9',
	);

	private $config;
	private $filespec;

	protected function setUp()
	{
		global $config;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;
		$config['mime_triggers'] = 'body|head|html|img|plaintext|a href|pre|script|table|title';
		$this->config = $config;

		// Write some data to files
		$path = dirname(__FILE__) . '/../../phpBB/files/';
		foreach (phpbb_filespec_test::$files as $type => $data)
		{
			file_put_contents($path . $type, hex2bin($data));
		}

		file_put_contents($path . 'TXT', '<HTML>mime trigger</HTML>');

		$this->init_filespec();
	}

	public static function additional_checks_variables()
	{
		$path = dirname(__FILE__) . '/../../phpBB/files/';
		return array(
			array($path . 'GIF', true),
			array($path . 'JPG', false),
			array($path . 'PNG', true),
			array($path . 'TIF', false),
			array($path . 'TXT', true),
		);
	}

	public static function check_content_variables()
	{
		$path = dirname(__FILE__) . '/../../phpBB/files/';
		$vars = array();
		foreach (phpbb_filespec_test::$files as $type => $data)
		{
			$vars[] = array($path . $type, true);
		}

		$vars[] = array($path . 'TXT', false);
		return $vars;
	}

	public static function get_extension_variables()
	{
		return array(
			array('file.png', 'png'),
			array('file.phpbb.gif', 'gif'),
			array('file..', ''),
			array('.file..jpg.webp', 'webp'),
		);
	}

	private function init_filespec($override = array())
	{
		// Initialise a blank filespec object for use with trivial methods
		$upload_ary = array(
			'name' => '',
			'type' => '',
			'size' => '',
			'tmp_name' => '',
			'error' => '',
		);

		$this->filespec = new filespec(array_merge($upload_ary, $override), null);
	}

	public static function is_image_variables()
	{
		$path = dirname(__FILE__) . '/../../phpBB/files/';
		$vars = array();
		foreach (phpbb_filespec_test::$files as $type => $data)
		{
			$vars[] = array($path . $type, 'image/' . $type, true);
		}

		$vars[] = array($path . 'TXT', 'text/plain', false);
		return $vars;
	}

	protected function tearDown()
	{
		$path = dirname(__FILE__) . '/../../phpBB/files/';
		unlink($path . 'TXT');
		foreach (phpbb_filespec_test::$files as $type => $data)
		{
			unlink($path . $type);
		}
	}

	/**
	 * @dataProvider additional_checks_variables
	 */
	public function test_additional_checks($filename, $expected)
	{
		global $user;
		$user = new phpbb_mock_user();

		$upload = new phpbb_mock_fileupload();
		$this->init_filespec(array('tmp_name', $filename));
		$this->filespec->upload = $upload;
		$this->filespec->file_moved = true;
		$this->filespec->filesize = $this->filespec->get_filesize($filename);

		$this->assertEquals($expected, $this->filespec->additional_checks());
	}

	/**
	 * @dataProvider check_content_variables
	 */
	public function test_check_content($filename, $expected)
	{
		$disallowed_content = explode('|', $this->config['mime_triggers']);
		$this->init_filespec(array('tmp_name' => $filename));
		$this->assertEquals($expected, $this->filespec->check_content($disallowed_content));
	}

	public function test_clean_filename_real()
	{
		$available_chars = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ\'\\" /:*?<>|[];(){},#+=-_`');
		$bad_chars = array("'", "\\", ' ', '/', ':', '*', '?', '"', '<', '>', '|');
		for ($tests = 0; $tests < self::TEST_COUNT; $tests++)
		{
			$len = mt_rand(1, self::MAX_STR_LEN);
			$str = '';
			for ($j = 0; $j < $len; $j++)
			{
				$index = mt_rand(0, sizeof($available_chars) - 1);
				$str .= $available_chars[$index];
			}

			$this->init_filespec(array('name' => $str));
			$this->filespec->clean_filename('real', self::PREFIX);
			$name = $this->filespec->realname;

			$this->assertEquals(0, preg_match('/%(\w{2})/', $name));
			foreach ($bad_chars as $char)
			{
				$this->assertFalse(strpos($name, $char));
			}
		}
	}

	public function test_clean_filename_unique()
	{
		$filenames = array();
		for ($tests = 0; $tests < self::TEST_COUNT; $tests++)
		{
			$this->init_filespec();
			$this->filespec->clean_filename('unique', self::PREFIX);
			$name = $this->filespec->realname;
			
			$this->assertTrue(strlen($name) === 32 + strlen(self::PREFIX));
			$this->assertRegExp('#^[A-Za-z0-9]+$#', substr($name, strlen(self::PREFIX)));
			$this->assertFalse(isset($filenames[$name]));
			$filenames[$name] = true;
		}
	}

	/**
	 * @dataProvider get_extension_variables
	 */
	public function test_get_extension($filename, $expected)
	{
		$this->assertEquals($expected, $this->filespec->get_extension($filename));
	}

	/**
	 * @dataProvider is_image_variables
	 */
	public function test_is_image($filename, $mimetype, $expected)
	{
		$this->init_filespec(array('tmp_name' => $filename, 'type' => $mimetype));
		$this->assertEquals($expected, $this->filespec->is_image());
	}
}
