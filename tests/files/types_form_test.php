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

class phpbb_files_types_form_test extends phpbb_test_case
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

	public function data_upload_form()
	{
		return array(
			array(
				array(),
				array(''),
			),
			array(
				array(
					'tmp_name'		=> 'foo',
					'name'			=> 'foo',
					'size'			=> 500,
					'type'			=> 'image/png',
					'error'			=> UPLOAD_ERR_PARTIAL,
				),
				array('PARTIAL_UPLOAD'),
			),
			array(
				array(
					'tmp_name'		=> 'foo',
					'name'			=> 'foo',
					'size'			=> 500,
					'type'			=> 'image/png',
					'error'			=> -9,
				),
				array('NOT_UPLOADED'),
			),
			array(
				array(
					'tmp_name'		=> 'foo',
					'name'			=> 'foo',
					'size'			=> 0,
					'type'			=> 'image/png',
				),
				array('EMPTY_FILEUPLOAD'),
			),
			array(
				array(
					'tmp_name'		=> 'none',
					'name'			=> 'none',
					'size'			=> 50,
					'type'			=> 'image/png',
				),
				array('PHP_SIZE_OVERRUN'),
			),
			array(
				array(
					'tmp_name'		=> 'tests/upload/fixture/png',
					'name'			=> 'foo.png',
					'size'			=> 500,
					'type'			=> 'image/png',
					'local_mode'	=> true,
				),
				array(),
				array('local_mode' => true),
			),
		);
	}

	/**
	 * @dataProvider data_upload_form
	 */
	public function test_upload_form($upload, $expected, $plupload = array())
	{
		$this->request = $this->createMock('\phpbb\request\request');
		$this->request->expects($this->any())
			->method('file')
			->willReturn($upload);
		$filespec = new \phpbb\files\filespec(
			$this->filesystem,
			$this->language,
			$this->php_ini,
			new \FastImageSize\FastImageSize(),
			$this->phpbb_root_path,
			new \phpbb\mimetype\guesser(array(
				'mimetype.extension_guesser' => new \phpbb\mimetype\extension_guesser(),
			)));
		$this->container->set('files.filespec', $filespec);
		$this->factory = new \phpbb\files\factory($this->container);
		$this->plupload = $this->getMockBuilder('\phpbb\plupload\plupload')
			->disableOriginalConstructor()
			->getMock();
		$this->plupload->expects($this->any())
			->method('handle_upload')
			->willReturn($plupload);

		$type_form = new \phpbb\files\types\form($this->factory, $this->language, $this->php_ini, $this->plupload, $this->request);
		$upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$upload->set_allowed_extensions(array('png'));
		$type_form->set_upload($upload);


		$file = $type_form->upload('foobar');
		$this->assertSame($expected, $file->error);
		$this->assertInstanceOf('\phpbb\files\filespec', $file);
	}
}
