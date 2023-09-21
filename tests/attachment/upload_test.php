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

require_once(__DIR__ . '/../../phpBB/includes/functions_posting.php');

class phpbb_attachment_upload_test extends \phpbb_database_test_case
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\files\upload */
	protected $files_upload;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\mimetype\guesser */
	protected $mimetype_guesser;

	/** @var \phpbb\event\dispatcher */
	protected $phpbb_dispatcher;

	/** @var \phpbb\plupload\plupload */
	protected $plupload;

	/** @var \phpbb\storage\storage */
	protected $storage;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\attachment\upload */
	protected $upload;

	/** @var \phpbb\filesystem\filesystem */
	private $filesystem;

	/** @var \phpbb\filesystem\temp */
	protected $temp;

	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

	/** @var \phpbb\files\factory */
	protected $factory;

	/** @var \bantu\IniGetWrapper\IniGetWrapper */
	protected $php_ini;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var string */
	protected $phpbb_root_path;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/resync.xml');
	}

	protected function setUp(): void
	{
		global $config, $phpbb_root_path, $phpEx;

		parent::setUp();

		$this->auth = new \phpbb\auth\auth();
		$this->config = new \phpbb\config\config(array(
			'img_create_thumbnail'	=> true,
		));
		$config = $this->config;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->db = $this->new_dbal();
		$this->phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$this->cache = new \phpbb\cache\service(new \phpbb\cache\driver\dummy(), $this->config, $this->db, $this->phpbb_dispatcher, $phpbb_root_path, $phpEx);
		$this->request = $this->createMock('\phpbb\request\request');

		$this->filesystem = new \phpbb\filesystem\filesystem();
		$this->language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$this->php_ini = new \bantu\IniGetWrapper\IniGetWrapper;
		$guessers = array(
			new \Symfony\Component\Mime\FileinfoMimeTypeGuesser(),
			new \Symfony\Component\Mime\FileBinaryMimeTypeGuesser(),
			new \phpbb\mimetype\content_guesser(),
			new \phpbb\mimetype\extension_guesser(),
		);
		$guessers[2]->set_priority(-2);
		$guessers[3]->set_priority(-2);
		$this->mimetype_guesser = new \phpbb\mimetype\guesser($guessers);
		$this->plupload = new \phpbb\plupload\plupload($phpbb_root_path, $this->config, $this->request, new \phpbb\user($this->language, '\phpbb\datetime'), $this->php_ini, $this->mimetype_guesser);

		$this->storage = $this->createMock('\phpbb\storage\storage');
		$this->storage->expects($this->any())
			->method('free_space')
			->willReturn(1024*1024); // 1gb

		$factory_mock = $this->getMockBuilder('\phpbb\files\factory')
			->disableOriginalConstructor()
			->getMock();
		$factory_mock->expects($this->any())
			->method('get')
			->willReturn(new \phpbb\files\filespec_storage(
				$this->language,
				new \FastImageSize\FastImageSize(),
				$this->mimetype_guesser
			));

		$this->container = new phpbb_mock_container_builder();
		$this->container->set('files.filespec_storage', new \phpbb\files\filespec_storage(
			$this->language,
			new \FastImageSize\FastImageSize(),
			new \phpbb\mimetype\guesser(array(
				'mimetype.extension_guesser' => new \phpbb\mimetype\extension_guesser(),
			))));
		$this->container->set('files.types.form', new \phpbb\files\types\form(
			$factory_mock,
			$this->language,
			$this->php_ini,
			$this->plupload,
			$this->request
		));
		$this->container->set('files.types.local_storage', new \phpbb\files\types\local_storage(
			$factory_mock,
			$this->language,
			$this->php_ini,
			$this->request
		));
		$this->factory = new \phpbb\files\factory($this->container);
		$this->files_upload = new \phpbb\files\upload($this->factory, $this->language, $this->php_ini, $this->request);
		$this->phpbb_dispatcher = new phpbb_mock_event_dispatcher();
		$this->temp = new \phpbb\filesystem\temp($this->filesystem, '');
		$this->user = new \phpbb\user($this->language, '\phpbb\datetime');
		$this->user->data['user_id'] = ANONYMOUS;

		$this->upload = new \phpbb\attachment\upload(
			$this->auth,
			$this->cache,
			$this->config,
			$this->files_upload,
			$this->language,
			$this->phpbb_dispatcher,
			$this->plupload,
			$this->storage,
			$this->temp,
			$this->user
		);
	}

	public function data_upload()
	{
		return array(
			array('foobar', 1, false,
				array(),
				array(
					'error' => array(
						'Upload initiated but no valid file upload form found.',
					),
					'post_attach'	=> false,
				)
			),
			array('foobar', 1, true,
				array(
					'realname'		=> 'foobar.jpg',
					'type'			=> 'jpg',
					'size'			=> 100,
				),
				array(
					'error' => array(
						'NOT_UPLOADED',
						'The image file you tried to attach is invalid.',
					),
					'post_attach'	=> false,
					'thumbnail'		=> 1,
				)
			),
			array('foobar', 1, true,
				// Instead of setting to false or empty array, set default filedata array
				// as otherwise it throws PHP undefined array key warnings
				// in different file upload related services
				array(
					'realname'		=> null,
					'type'			=> null,
					'size'			=> null,
				),
				array(
					'error' => array(
						'NOT_UPLOADED',
					),
					'post_attach'	=> false,
					'thumbnail'		=> 0,
				)
			),
		);
	}

	/**
	 * @dataProvider data_upload
	 */
	public function test_upload($form_name, $forum_id, $local, $filedata, $expected)
	{
		$filedata = $this->upload->upload($form_name, $forum_id, $local, '', false, $filedata);

		$this->assertSame($expected, $filedata);
	}

	public function test_init_error()
	{
		$filespec = $this->getMockBuilder('\phpbb\files\filespec_storage')
			->disableOriginalConstructor()
			->getMock();
		$filespec->expects($this->any())
			->method('init_error')
			->willReturn(true);
		$filespec->expects($this->any())
			->method('set_upload_namespace')
			->willReturnSelf();
		$filespec->expects($this->any())
			->method('set_upload_ary')
			->willReturnSelf();
		$this->container->set('files.filespec_storage', $filespec);
		$factory_mock = $this->getMockBuilder('\phpbb\files\factory')
			->disableOriginalConstructor()
			->getMock();
		$factory_mock->expects($this->any())
			->method('get')
			->willReturn($filespec);
		$this->container->set('files.types.local_storage', new \phpbb\files\types\local_storage(
			$factory_mock,
			$this->language,
			$this->php_ini,
			$this->request
		));

		$this->upload = new \phpbb\attachment\upload(
			$this->auth,
			$this->cache,
			$this->config,
			$this->files_upload,
			$this->language,
			$this->phpbb_dispatcher,
			$this->plupload,
			$this->storage,
			$this->temp,
			$this->user
		);

		// Instead of setting to false or empty array, set default filedata array
		// as otherwise it throws PHP undefined array key warnings
		// in different file upload related services
		$filedata = $this->upload->upload('foobar', 1, true, '', false,
			[
				'realname'		=> null,
				'type'			=> null,
				'size'			=> null,
			]
		);

		$this->assertSame(array(
			'error'		=> array(),
			'post_attach'	=> false,
		), $filedata);
	}

	public function data_image_upload()
	{
		return array(
			array(false, false, array(),
				array(
					'error'			=> array('The image file you tried to attach is invalid.'),
					'post_attach'	=> false,
					'thumbnail'		=> 1,
				)
			),
			array(false, true, array(),
				array(
					'error'			=> array('The image file you tried to attach is invalid.'),
					'post_attach'	=> false,
					'thumbnail'		=> 1,
				)
			),
			array(true, false, array(),
				array(
					'error'			=> array(),
					'post_attach'	=> true,
					// thumbnail gets reset to 0 as creation was not possible
					'thumbnail'		=> 0,
					'filesize'		=> 100,
					'mimetype'		=> 'jpg',
					'extension'		=> 'jpg',
					'real_filename'	=> 'foobar.jpg',
				)
			),
			array(true, false,
				array(
					'check_attachment_content'	=> true,
					'mime_triggers'	=> '',
				),
				array(
					'error'			=> array(),
					'post_attach'	=> true,
					// thumbnail gets reset to 0 as creation was not possible
					'thumbnail'		=> 0,
					'filesize'		=> 100,
					'mimetype'		=> 'jpg',
					'extension'		=> 'jpg',
					'real_filename'	=> 'foobar.jpg',
				)
			),
			array(true, false,
				array(
					'attachment_quota'	=> 150,
				),
				array(
					'error'			=> array(),
					'post_attach'	=> true,
					// thumbnail gets reset to 0 as creation was not possible
					'thumbnail'		=> 0,
					'filesize'		=> 100,
					'mimetype'		=> 'jpg',
					'extension'		=> 'jpg',
					'real_filename'	=> 'foobar.jpg',
				)
			),
			array(true, false,
				array(
					'attachment_quota'	=> 50,
				),
				array(
					'error'			=> array(
						'ATTACH_QUOTA_REACHED',
					),
					'post_attach'	=> false,
					'thumbnail'		=> 1,
					'filesize'		=> 100,
					'mimetype'		=> 'jpg',
					'extension'		=> 'jpg',
					'real_filename'	=> 'foobar.jpg',
				)
			),
		);
	}

	/**
	 * @dataProvider data_image_upload
	 */
	public function test_image_upload($is_image, $plupload_active, $config_data, $expected)
	{
		$filespec = $this->getMockBuilder('\phpbb\files\filespec_storage')
			->setMethods(array(
				'init_error',
				'is_image',
				'move_file',
				'is_uploaded',
			))
			->setConstructorArgs(array(
				$this->language,
				new \FastImageSize\FastImageSize(),
				$this->mimetype_guesser,
				$this->plupload
			))
			->getMock();
		foreach ($config_data as $key => $value)
		{
			$this->config[$key] = $value;
		}
		$filespec->set_upload_namespace($this->files_upload);
		$filespec->expects($this->any())
			->method('init_error')
			->willReturn(false);
		$filespec->expects($this->any())
			->method('is_image')
			->willReturn($is_image);
		$filespec->expects($this->any())
			->method('is_uploaded')
			->willReturn(true);
		$filespec->expects($this->any())
			->method('move_file')
			->willReturn(false);
		$this->container->set('files.filespec_storage', $filespec);
		$factory_mock = $this->getMockBuilder('\phpbb\files\factory')
			->disableOriginalConstructor()
			->getMock();
		$factory_mock->expects($this->any())
			->method('get')
			->willReturn($filespec);
		$this->container->set('files.types.local_storage', new \phpbb\files\types\local_storage(
			$factory_mock,
			$this->language,
			$this->php_ini,
			$this->request
		));

		$plupload = $this->getMockBuilder('\phpbb\plupload\plupload')
			->disableOriginalConstructor()
			->getMock();
		$plupload->expects($this->any())
			->method('is_active')
			->willReturn($plupload_active);
		if ($plupload_active)
		{
			$plupload->expects($this->once())
				->method('emit_error')
				->with(104, 'ATTACHED_IMAGE_NOT_IMAGE')
				->willReturn(false);
		}
		$this->upload = new \phpbb\attachment\upload(
			$this->auth,
			$this->cache,
			$this->config,
			$this->files_upload,
			$this->language,
			$this->phpbb_dispatcher,
			$plupload,
			$this->storage,
			$this->temp,
			$this->user
		);

		$filedata = $this->upload->upload('foobar', 1, true, '', false, array(
			'realname'		=> 'foobar.jpg',
			'type'			=> 'jpg',
			'size'			=> 100,
		));

		foreach ($expected as $key => $entry)
		{
			$this->assertEquals($entry, $filedata[$key]);
		}

		// Reset config data
		foreach ($config_data as $key => $value)
		{
			$this->config->delete($key);
		}
	}
}
