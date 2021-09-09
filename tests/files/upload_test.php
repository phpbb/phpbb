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

class phpbb_files_upload_test extends phpbb_test_case
{
	/** @var \phpbb\filesystem\filesystem */
	private $filesystem;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

	/** @var \phpbb\files\factory */
	protected $factory;

	/** @var \bantu\IniGetWrapper\IniGetWrapper */
	protected $php_ini;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request_interface */
	protected $request;

	protected function setUp(): void
	{
		// Global $config required by unique_id
		global $config, $phpbb_root_path, $phpEx;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;

		$this->request = $this->createMock('\phpbb\request\request');

		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->php_ini = new \bantu\IniGetWrapper\IniGetWrapper;

		$this->container = new phpbb_mock_container_builder();
		$this->container->set('files.filespec', new \phpbb\files\filespec(
			$this->filesystem,
			$this->language,
			$this->php_ini,
			new \FastImageSize\FastImageSize(),
			$phpbb_root_path,
			new \phpbb\mimetype\guesser(array(
				'mimetype.extension_guesser' => new \phpbb\mimetype\extension_guesser(),
			))));
		$this->factory = new \phpbb\files\factory($this->container);
	}

	public function test_reset_vars()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_max_filesize(500);
		$this->assertEquals(500, $upload->max_filesize);
		$upload->reset_vars();
		$this->assertEquals(0, $upload->max_filesize);
	}

	public function test_set_disallowed_content()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$disallowed_content = new ReflectionProperty($upload, 'disallowed_content');
		$disallowed_content->setAccessible(true);

		$upload->set_disallowed_content(array('foo'));
		$this->assertEquals(array('foo'), $disallowed_content->getValue($upload));
		$upload->set_disallowed_content(array('foo', 'bar', 'meh'));
		$this->assertEquals(array('foo', 'bar', 'meh'), $disallowed_content->getValue($upload));
		$upload->set_disallowed_content(false);
		$this->assertEquals(array('foo', 'bar', 'meh'), $disallowed_content->getValue($upload));
		$this->assertINstanceOf('\phpbb\files\upload', $upload->set_disallowed_content(array()));
		$this->assertEquals(array(), $disallowed_content->getValue($upload));
		$upload->reset_vars();
		$this->assertEquals(array(), $disallowed_content->getValue($upload));
	}

	public function test_is_valid()
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$this->assertFalse($upload->is_valid('foobar'));
	}

	public function data_internal_error()
	{
		return array(
			array(UPLOAD_ERR_INI_SIZE, 'PHP_SIZE_OVERRUN'),
			array(UPLOAD_ERR_FORM_SIZE, 'WRONG_FILESIZE'),
			array(UPLOAD_ERR_PARTIAL, 'PARTIAL_UPLOAD'),
			array(UPLOAD_ERR_NO_FILE, 'NOT_UPLOADED'),
			array(UPLOAD_ERR_NO_TMP_DIR, 'NO_TEMP_DIR'),
			array(UPLOAD_ERR_CANT_WRITE, 'NO_TEMP_DIR'),
			array(UPLOAD_ERR_EXTENSION, 'PHP_UPLOAD_STOPPED'),
			array(9, false),
		);
	}

	/**
	 * @dataProvider data_internal_error
	 */
	public function test_assign_internal_error($error_code, $expected)
	{
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$this->assertSame($expected, $upload->assign_internal_error($error_code));
	}
}
