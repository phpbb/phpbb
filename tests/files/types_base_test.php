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

class phpbb_files_types_base_test extends phpbb_test_case
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
		global $phpbb_root_path, $phpEx;

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

	public function data_check_upload_size()
	{
		return array(
			array('foo', '500KB', array()),
			array('none', '500KB', array('PHP_SIZE_OVERRUN')),
			array('none', '', array('PHP_SIZE_NA')),
		);
	}

	/**
	 * @dataProvider data_check_upload_size
	 */
	public function test_check_upload_size($filename, $max_filesize, $expected)
	{
		$php_ini = $this->createMock('\bantu\IniGetWrapper\IniGetWrapper');
		$php_ini->expects($this->any())
			->method('getString')
			->willReturn($max_filesize);
		$upload = new \phpbb\files\upload($this->factory, $this->language, $php_ini, $this->request);
		$type_form = new \phpbb\files\types\local($this->factory, $this->language, $php_ini, $this->request);
		$file = $this->getMockBuilder('\phpbb\files\filespec')
			->disableOriginalConstructor()
			->getMock();
		$file->expects($this->any())
			->method('get')
			->willReturn($filename);
		$type_form->set_upload($upload);
		$type_form->check_upload_size($file);

		$this->assertSame($expected, $file->error);
	}
}
