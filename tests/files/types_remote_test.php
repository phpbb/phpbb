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

require_once dirname(__FILE__) . '/type_foo.php';

class phpbb_files_types_remote_test extends phpbb_test_case
{
	private $path;

	private $filesystem;

	/** @var \phpbb\config\config */
	protected $config;

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

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	protected function setUp(): void
	{
		global $config, $phpbb_root_path, $phpEx;

		$config = new \phpbb\config\config(array());
		$this->config = $config;
		$this->config->set('remote_upload_verify', 0);
		$this->request = $this->createMock('\phpbb\request\request');

		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->php_ini = new \bantu\IniGetWrapper\IniGetWrapper;

		$this->container = new phpbb_mock_container_builder($phpbb_root_path, $phpEx);
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

		$this->path = __DIR__ . '/fixture/';
		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function test_upload_fsock_fail()
	{
		$type_remote = new \phpbb\files\types\remote($this->config, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload->set_allowed_extensions(array('png'));
		$type_remote->set_upload($upload);

		$file = $type_remote->upload('https://bärföö.com/foo.png');

		$this->assertSame(array('NOT_UPLOADED'), $file->error);
	}

	public function data_get_max_file_size()
	{
		return array(
			array('', 'http://phpbb.com/foo/bar.png'),
			array('2k', 'http://phpbb.com/foo/bar.png'),
			array('500k', 'http://phpbb.com/foo/bar.png'),
			array('500M', 'http://phpbb.com/foo/bar.png'),
			array('500m', 'http://phpbb.com/foo/bar.png'),
			array('500k', 'http://google.com/?.png', array('DISALLOWED_EXTENSION', 'DISALLOWED_CONTENT')),
			array('1', 'http://google.com/?.png', array('WRONG_FILESIZE')),
			array('500g', 'http://phpbb.com/foo/bar.png'),
			array('foobar', 'http://phpbb.com/foo/bar.png'),
			array('-5k', 'http://phpbb.com/foo/bar.png'),
		);
	}

	/**
	 * @dataProvider data_get_max_file_size
	 */
	public function test_get_max_file_size($max_file_size, $link, $expected = array('URL_NOT_FOUND'))
	{
		$php_ini = $this->createMock('\bantu\IniGetWrapper\IniGetWrapper', array('getString'));
		$php_ini->expects($this->any())
			->method('getString')
			->willReturn($max_file_size);
		$type_remote = new \phpbb\files\types\remote($this->config, $this->factory, $this->language, $php_ini, $this->request, $this->phpbb_root_path);
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload->set_allowed_extensions(array('png'));
		$type_remote->set_upload($upload);

		$file = $type_remote->upload($link);

		$this->assertSame($expected, $file->error);
	}

	public function test_upload_wrong_path()
	{
		$type_remote = new \phpbb\files\types\foo($this->config, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload = new \phpbb\files\upload($this->filesystem, $this->factory, $this->language, $this->php_ini, $this->request, $this->phpbb_root_path);
		$upload->set_allowed_extensions(array('png'));
		$type_remote->set_upload($upload);
		$type_remote::$tempnam_path = $this->phpbb_root_path . 'cache/wrong/path';

		$file = $type_remote->upload('http://google.com/?.png');

		$this->assertSame(array('NOT_UPLOADED'), $file->error);
		$type_remote::$tempnam_path = '';
	}
}
