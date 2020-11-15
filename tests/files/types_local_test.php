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

class phpbb_files_types_local_test extends phpbb_test_case
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

	/** @var \phpbb\plupload\plupload */
	protected $plupload;

	/** @var string phpBB root path */
	protected $phpbb_root_path;

	protected function setUp(): void
	{
		global $phpbb_root_path, $phpEx;

		$this->request = $this->createMock('\phpbb\request\request');
		$this->request->expects($this->any())
			->method('file')
			->willReturn(array());

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
		$this->plupload = $this->getMockBuilder('\phpbb\plupload\plupload')
			->disableOriginalConstructor()
			->getMock();
		$this->plupload->expects($this->any())
			->method('handle_upload')
			->willReturn(array());

		$this->phpbb_root_path = $phpbb_root_path;
	}

	public function test_upload_init_error()
	{
		$filespec = $this->getMockBuilder('\phpbb\files\filespec')
			->disableOriginalConstructor()
			->getMock();
		$filespec->expects($this->any())
			->method('init_error')
			->willReturn(true);
		$filespec->expects($this->any())
			->method('set_upload_ary')
			->willReturnSelf();
		$filespec->expects($this->any())
			->method('set_upload_namespace')
			->willReturnSelf();
		$this->container->set('files.filespec', $filespec);
		$this->factory = new \phpbb\files\factory($this->container);

		$type_local = new \phpbb\files\types\local($this->factory, $this->language, $this->php_ini, $this->request);


		$file = $type_local->upload('foo', false);
		$this->assertSame(array(''), $file->error);
		$this->assertInstanceOf('\phpbb\files\filespec', $file);
	}

	public function data_upload_form()
	{
		return array(
			array(
				'foo',
				array(
					'realname'		=> null,
					'tmp_name'		=> 'foo',
					'size'			=> 500,
					'type'			=> 'image/png',
				),
				array('NOT_UPLOADED'),
			),
			array(
				'none',
				array(
					'realname'		=> null,
					'size'			=> null,
					'type'			=> null,
				),
				array('PHP_SIZE_OVERRUN'),
			),
			array(
				'tests/upload/fixture/png',
				array(
					'realname'		=> 'foo.png',
					'size'			=> 500,
					'type'			=> 'image/png',
					'local_mode'	=> true,
				),
				array(),
			),
		);
	}

	/**
	 * @dataProvider data_upload_form
	 */
	public function test_upload_form($filename, $upload_ary, $expected)
	{
		$filespec = new \phpbb\files\filespec(
			$this->filesystem,
			$this->language,
			$this->php_ini,
			new \FastImageSize\FastImageSize(),
			$this->phpbb_root_path,
			new \phpbb\mimetype\guesser(array(
				'mimetype.extension_guesser' => new \phpbb\mimetype\extension_guesser(),
			)));
		$filespec_local = new ReflectionProperty($filespec, 'local');
		$filespec_local->setAccessible(true);
		$filespec_local->setValue($filespec, true);
		$this->container->set('files.filespec', $filespec);
		$this->factory = new \phpbb\files\factory($this->container);

		$type_local = new \phpbb\files\types\local($this->factory, $this->language, $this->php_ini, $this->request);
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('png'));
		$type_local->set_upload($upload);

		$file = $type_local->upload($filename, $upload_ary);
		$this->assertSame($expected, $file->error);
		$this->assertInstanceOf('\phpbb\files\filespec', $file);
	}
}
