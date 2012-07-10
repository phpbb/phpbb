<?php
/**
 *
 * @package testing
 * @copyright (c) 2012 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

/**
 * @group functional
 */
class phpbb_functional_plupload_test extends phpbb_functional_test_case
{
	private $path;

	public function setUp()
	{
		parent::setUp();
		$this->path = __DIR__ . '/fixtures/files/';
		$this->add_lang('posting');
		$this->login();
	}

	public function test_normal_upload()
	{
		$file = array(
			'tmp_name' => $this->path . 'valid.jpg',
			'name' => 'valid.jpg',
			'type' => 'image/jpeg',
			'size' => filesize($this->path . 'valid.jpg'),
			'error' => UPLOAD_ERR_OK,
		);

		$crawler = $this->client->request(
			'POST',
			'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid,
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

		$this->assertEquals(0, $crawler->filter('p.error')->count());
		$this->assertContains($this->lang('POSTED_ATTACHMENTS'), $crawler->filter('#postform h3')->eq(1)->text());
	}
}
