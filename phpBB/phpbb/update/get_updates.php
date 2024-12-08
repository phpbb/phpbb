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

namespace phpbb\update;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use phpbb\filesystem\exception\filesystem_exception;
use phpbb\filesystem\filesystem_interface;
use SodiumException;
use ZipArchive;

class get_updates
{
	/** @var filesystem_interface Filesystem manager */
	protected filesystem_interface $filesystem;

	/** @var Client HTTP client */
	protected Client $http_client;

	/** @var ZipArchive Zip extractor */
	protected ZipArchive $zipper;

	/** @var string Public key to verify package  */
	protected string $public_key;

	/** @var string phpBB root path */
	private string $phpbb_root_path;

	/**
	 * Constructor
	 *
	 * @param filesystem_interface $filesystem
	 * @param string $public_key
	 * @param string $phpbb_root_path
	 */
	public function __construct(
		filesystem_interface $filesystem,
		string $public_key,
		string $phpbb_root_path)
	{
		$this->filesystem = $filesystem;
		$this->http_client = new Client();
		$this->zipper = new ZipArchive();
		$this->public_key = base64_decode($public_key);
		$this->phpbb_root_path = $phpbb_root_path;
	}

	/**
	 * Download the update package.
	 *
	 * @param string $url			Download link to the update.
	 * @param string $storage_path	Location for the download.
	 *
	 * @return bool Whether the download completed successfully.
	 */
	public function download(string $url, string $storage_path): bool
	{
		try
		{
			$this->http_client->request('GET', $url, [
				'sink' => $storage_path,
				'allow_redirects' => false
			]);
		}
		catch (GuzzleException)
		{
			return false;
		}

		return true;
	}

	/**
	 * Validate the downloaded file.
	 *
	 * @param string $file_path			Path to the download.
	 * @param string $signature_path	The signature file.
	 *
	 * @return bool Whether the signature is correct or not.
	 */
	public function validate(string $file_path, string $signature_path): bool
	{
		if (file_exists($file_path) === false || !is_readable($file_path))
		{
			return false;
		}

		if (file_exists($signature_path) === false || !is_readable($signature_path))
		{
			return false;
		}

		$signature = file_get_contents($signature_path);

		$hash = hash_file('sha384', $file_path, true);
		if ($hash === false)
		{
			return false;
		}

		$raw_signature = base64_decode($signature, true);
		if ($raw_signature === false)
		{
			return false;
		}

		$raw_public_key = base64_decode($this->public_key, true);
		if ($raw_public_key === false)
		{
			return false;
		}

		try
		{
			return sodium_crypto_sign_verify_detached($raw_signature, $hash, $raw_public_key);
		}
		catch (SodiumException)
		{
			return false;
		}
	}

	/**
	 * Extract the downloaded archive.
	 *
	 * @param string $zip_file	Path to the archive.
	 * @param string $to		Path to where to extract the archive to.
	 *
	 * @return bool Whether the extraction completed successfully.
	 */
	public function extract(string $zip_file, string $to): bool
	{
		if ($this->zipper->open($zip_file) === false)
		{
			return false;
		}

		$result = $this->zipper->extractTo($to);
		$this->zipper->close();

		return $result;
	}

	/**
	 * Copy the update package to the root folder.
	 *
	 * @param string $src_dir Where to copy from.
	 *
	 * @return bool Whether the files were copied successfully.
	 */
	public function copy(string $src_dir): bool
	{
		try
		{
			$this->filesystem->mirror($src_dir, $this->phpbb_root_path);
		}
		catch (filesystem_exception)
		{
			return false;
		}

		return true;
	}
}
