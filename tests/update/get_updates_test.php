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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem_interface;
use phpbb\update\get_updates;

class phpbb_update_get_updates_test extends phpbb_test_case
{
	private $filesystem;
	private $http_client;
	private $zipper;
	private $update;
	private $public_key = 'atest_public_keyatest_public_keyatest_public_keyatest_public_key';

	private $file_path = __DIR__ . '/../tmp/download.zip';

	private $signature_path = __DIR__ . '/../tmp/signature.sig';

	private $phpbb_root_path;

	public function setUp(): void
	{
		global $phpbb_root_path;

		parent::setUp();

		$this->filesystem = $this->createMock(filesystem_interface::class);
		$this->http_client = $this->createMock(Client::class);
		$this->zipper = $this->createMock(ZipArchive::class);
		$this->phpbb_root_path = $phpbb_root_path;

		// Set up the `get_updates` instance with injected mocks.
		$this->update = new get_updates($this->filesystem, base64_encode($this->public_key), $this->phpbb_root_path);
	}

	public function tearDown(): void
	{
		if (file_exists($this->file_path))
		{
			unlink($this->file_path);
		}

		if (file_exists($this->signature_path))
		{
			unlink($this->signature_path);
		}

		parent::tearDown();
	}

	public function test_download_success()
	{
		$this->http_client->expects($this->once())
			->method('request')
			->with('GET', 'http://example.com/update.zip', [
				'sink' => '/path/to/storage',
				'allow_redirects' => false
			])
			->willReturn(true);

		$client_reflection = new \ReflectionProperty($this->update, 'http_client');
		$client_reflection->setValue($this->update, $this->http_client);

		$result = $this->update->download('http://example.com/update.zip', '/path/to/storage');
		$this->assertTrue($result);
	}

	public function test_download_failure()
	{
		$this->http_client->expects($this->once())
			->method('request')
			->willReturnCallback(function ($method, $url, $options)
			{
				throw new ClientException('bad client', new \GuzzleHttp\Psr7\Request($method, $url));
			});
		$client_reflection = new \ReflectionProperty($this->update, 'http_client');
		$client_reflection->setValue($this->update, $this->http_client);

		$result = $this->update->download('http://example.com/update.zip', '/path/to/storage');
		$this->assertFalse($result);
	}

	public function test_validate_success()
	{
		$keypair = sodium_crypto_sign_keypair();

		$secret_key = sodium_crypto_sign_secretkey($keypair);
		$public_key = base64_encode(sodium_crypto_sign_publickey($keypair));

		file_put_contents($this->file_path, 'test file content');

		$hash = hash_file('sha384', $this->file_path, true);
		file_put_contents($this->signature_path, base64_encode(sodium_crypto_sign_detached($hash, $secret_key)));

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, $public_key);

		$this->assertTrue($this->update->validate($this->file_path, $this->signature_path));
	}

	public function test_validate_file_not_exist()
	{
		$file_path = __DIR__ . '/../tmp/download.zip';
		$signature_path = __DIR__ . '/../tmp/signature.sig';

		$keypair = sodium_crypto_sign_keypair();

		$public_key = base64_encode(sodium_crypto_sign_publickey($keypair));

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, $public_key);

		$this->assertFalse($this->update->validate($file_path, $signature_path));
	}

	public function test_validate_sig_not_exist()
	{
		$keypair = sodium_crypto_sign_keypair();

		$public_key = base64_encode(sodium_crypto_sign_publickey($keypair));

		file_put_contents($this->file_path, 'test file content');

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, $public_key);

		$this->assertFalse($this->update->validate($this->file_path, $this->signature_path));
	}

	public function test_validate_file_not_accessible()
	{
		$keypair = sodium_crypto_sign_keypair();

		$public_key = base64_encode(sodium_crypto_sign_publickey($keypair));

		file_put_contents($this->file_path, 'test file content');

		chmod($this->file_path, 0000);

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, $public_key);

		$this->assertFalse($this->update->validate($this->file_path, $this->signature_path));

		chmod($this->file_path, 0666);
	}

	public function test_validate_sig_not_accessible()
	{
		$keypair = sodium_crypto_sign_keypair();

		$secret_key = sodium_crypto_sign_secretkey($keypair);
		$public_key = base64_encode(sodium_crypto_sign_publickey($keypair));

		file_put_contents($this->file_path, 'test file content');

		$hash = hash_file('sha384', $this->file_path, true);
		file_put_contents($this->signature_path, base64_encode(sodium_crypto_sign_detached($hash, $secret_key)));

		chmod($this->signature_path, 0000);

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, $public_key);

		$this->assertFalse($this->update->validate($this->file_path, $this->signature_path));

		chmod($this->signature_path, 0666);
	}

	public function test_validate_sig_not_base64()
	{
		$keypair = sodium_crypto_sign_keypair();

		$public_key = base64_encode(sodium_crypto_sign_publickey($keypair));

		file_put_contents($this->file_path, 'test file content');

		file_put_contents($this->signature_path, 'SGVsbG8gV29ybGQ===');

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, $public_key);

		$this->assertFalse($this->update->validate($this->file_path, $this->signature_path));
	}

	public function test_validate_invalid_pub_key()
	{
		$keypair = sodium_crypto_sign_keypair();

		$secret_key = sodium_crypto_sign_secretkey($keypair);

		file_put_contents($this->file_path, 'test file content');

		$hash = hash_file('sha384', $this->file_path, true);
		file_put_contents($this->signature_path, base64_encode(sodium_crypto_sign_detached($hash, $secret_key)));

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, '!not!base64');

		$this->assertFalse($this->update->validate($this->file_path, $this->signature_path));
	}

	public function test_validate_fail()
	{
		$keypair = sodium_crypto_sign_keypair();

		$secret_key = sodium_crypto_sign_secretkey($keypair);

		// Recreate keypair for different public key
		$keypair = sodium_crypto_sign_keypair();
		$public_key = base64_encode(sodium_crypto_sign_publickey($keypair));

		file_put_contents($this->file_path, 'test file content');

		$hash = hash_file('sha384', $this->file_path, true);
		file_put_contents($this->signature_path, base64_encode(sodium_crypto_sign_detached($hash, $secret_key)));

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, $public_key);

		$this->assertFalse($this->update->validate($this->file_path, $this->signature_path));
	}

	public function test_validate_invalid_pub_key_length()
	{
		$keypair = sodium_crypto_sign_keypair();

		$secret_key = sodium_crypto_sign_secretkey($keypair);
		$public_key = base64_encode(sodium_crypto_sign_publickey($keypair) . 'Foo=');

		file_put_contents($this->file_path, 'test file content');

		$hash = hash_file('sha384', $this->file_path, true);
		file_put_contents($this->signature_path, base64_encode(sodium_crypto_sign_detached($hash, $secret_key)));

		$client_reflection = new \ReflectionProperty($this->update, 'public_key');
		$client_reflection->setValue($this->update, $public_key);

		$this->assertFalse($this->update->validate($this->file_path, $this->signature_path));
	}

	public function test_extract_success()
	{
		$this->zipper->expects($this->once())
			->method('open')
			->with('/path/to/zipfile.zip')
			->willReturn(true);

		$this->zipper->expects($this->once())
			->method('extractTo')
			->with('/path/to/extract')
			->willReturn(true);

		$this->zipper->expects($this->once())
			->method('close');

		$zipperReflection = new \ReflectionProperty($this->update, 'zipper');
		$zipperReflection->setValue($this->update, $this->zipper);

		$result = $this->update->extract('/path/to/zipfile.zip', '/path/to/extract');
		$this->assertTrue($result);
	}

	public function test_extract_failure()
	{
		$this->zipper->expects($this->once())
			->method('open')
			->with('/path/to/zipfile.zip')
			->willReturn(false);

		$zipperReflection = new \ReflectionProperty($this->update, 'zipper');
		$zipperReflection->setValue($this->update, $this->zipper);

		$result = $this->update->extract('/path/to/zipfile.zip', '/path/to/extract');
		$this->assertFalse($result);
	}

	public function test_copy_success()
	{
		$this->filesystem->expects($this->once())
			->method('mirror')
			->with('/source/dir', $this->phpbb_root_path);

		$result = $this->update->copy('/source/dir');
		$this->assertTrue($result);
	}

	public function test_copy_failure()
	{
		$this->filesystem->expects($this->once())
			->method('mirror')
			->willThrowException(new filesystem_exception());

		$result = $this->update->copy('/source/dir');
		$this->assertFalse($result);
	}
}
