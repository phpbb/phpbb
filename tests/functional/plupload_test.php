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
		$query = "
			UPDATE phpbb_extension_groups
			SET allow_in_pm = '$val'
			WHERE group_name = 'IMAGES'
		";
		$this->db->sql_query($query);
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->purge_cache();
		$this->set_extension_group_permission(1);
		$this->path = __DIR__ . '/fixtures/files/';
		$this->add_lang('posting');
		$this->login();
	}

	protected function tearDown(): void
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

	public static function get_urls()
	{
		return array(
			array('posting.php?mode=reply&t=1'),
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

		$crawler = self::$client->request('POST', $url . '&sid=' . $this->sid);

		$file_form_data = $this->get_hidden_fields($crawler, $url);

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
				array_merge(array(
					'chunk' => $i,
					'chunks' => self::CHUNKS,
					'name' => md5('valid') . '.jpg',
					'real_filename' => 'valid.jpg',
					'add_file' => $this->lang('ADD_FILE'),
				), $file_form_data),
				array('fileupload' => $file),
				array('X-PHPBB-USING-PLUPLOAD' => '1')
			);

			if ($i < self::CHUNKS - 1)
			{
				$this->assertStringContainsString('{"jsonrpc":"2.0","id":"id","result":null}', self::get_content());
			}
			else
			{
				$response = json_decode(self::get_content(), true);
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

		$file_form_data = $this->get_hidden_fields(null, $url);

		self::$client->setServerParameter('HTTP_X_PHPBB_USING_PLUPLOAD', '1');
		self::$client->request(
			'POST',
			$url . '&sid=' . $this->sid,
			array_merge(array(
				'chunk' => '0',
				'chunks' => '1',
				'name' => md5('valid') . '.jpg',
				'real_filename' => 'valid.jpg',
				'add_file' => $this->lang('ADD_FILE'),
			), $file_form_data),
			array('fileupload' => $file)
		);

		$response = json_decode(self::get_content(), true);
		$this->assertEquals('valid.jpg', $response['data'][0]['real_filename']);
	}
}
