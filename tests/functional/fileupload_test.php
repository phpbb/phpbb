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
class phpbb_functional_fileupload_test extends phpbb_functional_test_case
{
	public function test_form_upload()
	{
		$this->markTestIncomplete();
		$path = __DIR__ . '/fixtures/files/';
		$this->add_lang('posting');
		$this->login();

		// Test 1: Invalid extension
		$crawler = $this->request('GET', 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid);
		$form = $crawler->selectButton('add_file')->form();
		$form['fileupload']->upload($path . 'illegal-extension.bif');
		$crawler = $this->client->submit($form);
		$this->assertEquals(1, $crawler->filter('p.error')->count());

		// Test 2: Empty file
		$crawler = $this->request('GET', 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid);
		$form = $crawler->selectButton('add_file')->form();
		$form['fileupload']->upload($path . 'empty.png');
		$crawler = $this->client->submit($form);
		$this->assertEquals(1, $crawler->filter('p.error')->count());

		// Test 3: File too large
		$crawler = $this->request('GET', 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid);
		$form = $crawler->selectButton('add_file')->form();
		$form['fileupload']->upload($path . 'too-large.png');
		$crawler = $this->client->submit($form);
		$this->assertEquals(1, $crawler->filter('div#message')->count());

		// Test 4: Valid file
		$crawler = $this->request('GET', 'posting.php?mode=reply&f=2&t=1&sid=' . $this->sid);
		$form = $crawler->selectButton('add_file')->form();
		$form['fileupload']->upload($path . 'valid.jpg');
		$crawler = $this->client->submit($form);
		$this->assertEquals(0, $crawler->filter('p.error')->count());
		$this->assertContains($this->lang('POSTED_ATTACHMENTS'), $crawler->filter('#postform h3')->eq(1)->text());
	}

	public function test_remote_upload()
	{
		// Note: we cannot check for the actual value of the error messages
		// since they are passed through the translator which will result in
		// blank strings within this test framework.

		// Only doing this within the functional framework because we need a
		// URL

		// Global $config required by unique_id
		global $config;

		if (!is_array($config))
		{
			$config = array();
		}

		$config['rand_seed'] = '';
		$config['rand_seed_last_update'] = time() + 600;

		// Test 1: Invalid extension
		$upload = new fileupload('', array('jpg'), 100);
		$file = $upload->remote_upload('http://example.com/image.gif');
		$this->assertEquals(1, sizeof($file->error));

		// Test 2: Non-existant file
		$upload = new fileupload('', array('jpg'), 100);
		$file = $upload->remote_upload('http://example.com/image.jpg');
		$this->assertEquals(1, sizeof($file->error));

		// Test 3: File too large
		$upload = new fileupload('', array('gif'), 100);
		$file = $upload->remote_upload($this->root_url . 'styles/prosilver/theme/images/forum_read.gif');
		$this->assertEquals(1, sizeof($file->error));

		// Test 4: Successful upload
		$upload = new fileupload('', array('gif'), 1000);
		$file = $upload->remote_upload($this->root_url . 'styles/prosilver/theme/images/forum_read.gif');
		$this->assertEquals(0, sizeof($file->error));
		$this->assertTrue(file_exists($file->filename));

		$config = array();
	}
}
