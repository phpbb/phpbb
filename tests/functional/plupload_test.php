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

/**
 * @group functional
 */
class phpbb_functional_plupload_test extends phpbb_functional_test_case
{
	const CHUNKS = 4;
	private $path;

	protected function set_extension_group_permission($val)
	{
		$db = $this->get_db();
		$query = "
			UPDATE phpbb_extension_groups
			SET allow_in_pm = '$val'
			WHERE group_name = 'IMAGES'
		";
		$db->sql_query($query);
	}

	public function setUp()
	{
		parent::setUp();
		$this->set_extension_group_permission(1);
		$this->path = __DIR__ . '/fixtures/files/';
		$this->add_lang('posting');
		$this->login();
	}

	public function tearDown()
	{
		$this->set_extension_group_permission(0);
		$iterator = new DirectoryIterator(__DIR__ . '/../../phpBB/files/');
		foreach ($iterator as $fileinfo)
		{
			if (
				$fileinfo->isDot()
				|| $fileinfo->isDir()
				|| $fileinfo->getFilename() === 'index.htm'
				|| $fileinfo->getFilename() === '.htaccess'
			)
			{
				continue;
			}

			unlink($fileinfo->getPathname());
		}
	}

	public function get_urls()
	{
		return array(
			array('posting.php?mode=reply&f=2&t=1'),
			array('ucp.php?i=pm&mode=compose'),
		);
	}

	/**
	 * @dataProvider get_urls
	 */
	public function test_chunked_upload($url)
	{
		$chunk_size = ceil(filesize($this->path . 'valid.jpg') / self::CHUNKS);
		$handle = fopen($this->path . 'valid.jpg', 'rb');

		for ($i = 0; $i < self::CHUNKS; $i++)
		{
			$chunk = fread($handle, $chunk_size);
			file_put_contents($this-> path . 'chunk', $chunk);

			$file = array(
				'tmp_name' => $this->path . 'chunk',
				'name' => 'blob',
				'type' => 'application/octet-stream',
				'size' => strlen($chunk),
				'error' => UPLOAD_ERR_OK,
			);

			self::$client->setServerParameter('HTTP_X_PHPBB_USING_PLUPLOAD', '1');

			$crawler = self::$client->request(
				'POST',
				$url . '&sid=' . $this->sid,
				array(
					'chunk' => $i,
					'chunks' => self::CHUNKS,
					'name' => md5('valid') . '.jpg',
					'real_filename' => 'valid.jpg',
					'add_file' => $this->lang('ADD_FILE'),
				),
				array('fileupload' => $file),
				array('X-PHPBB-USING-PLUPLOAD' => '1')
			);

			if ($i < self::CHUNKS - 1)
			{
				$this->assertContains('{"jsonrpc":"2.0","id":"id","result":null}', self::$client->getResponse()->getContent());
			}
			else
			{
				$response = json_decode(self::$client->getResponse()->getContent(), true);
				$this->assertEquals('valid.jpg', $response['data'][0]['real_filename']);
			}

			unlink($this->path . 'chunk');
		}

		fclose($handle);
	}

	/**
	 * @dataProvider get_urls
	 */
	public function test_normal_upload($url)
	{
		$file = array(
			'tmp_name' => $this->path . 'valid.jpg',
			'name' => 'valid.jpg',
			'type' => 'image/jpeg',
			'size' => filesize($this->path . 'valid.jpg'),
			'error' => UPLOAD_ERR_OK,
		);

		$crawler = self::$client->request(
			'POST',
			$url . '&sid=' . $this->sid,
			array(
				'chunk' => '0',
				'chunks' => '1',
				'name' => md5('valid') . '.jpg',
				'real_filename' => 'valid.jpg',
				'add_file' => $this->lang('ADD_FILE'),
			),
			array('fileupload' => $file),
			array('X-PHPBB-USING-PLUPLOAD' => '1')
		);

		$response = json_decode(self::$client->getResponse()->getContent(), true);
		$this->assertEquals('valid.jpg', $response['data'][0]['real_filename']);
	}
}
