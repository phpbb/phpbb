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

namespace phpbb;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use phpbb\exception\runtime_exception;

class file_downloader
{
	const OK = 200;
	const NOT_FOUND = 404;
	const REQUEST_TIMEOUT = 408;

	/** @var string Error string */
	protected $error_string = '';

	/** @var int Error number */
	protected $error_number = 0;

	/**
	 * Create new guzzle client
	 *
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 *
	 * @return Client
	 */
	protected function create_client(string $host, int $port = 443, int $timeout = 6): Client
	{
		// Only set URL scheme if not specified in URL
		$url_parts = parse_url($host);
		if (!isset($url_parts['scheme']))
		{
			$host = (($port === 443) ? 'https://' : 'http://') . $host;
		}

		// Initialize Guzzle client
		return new Client([
			'base_uri' => $host,
			'timeout'  => $timeout,
			'headers' => [
				'user-agent' => 'phpBB/' . PHPBB_VERSION,
				'accept' => '*/*',
			  ],
		]);
	}

	/**
	 * Retrieve contents from remotely stored file
	 *
	 * @param string    $host            File host
	 * @param string    $directory       Directory file is in
	 * @param string    $filename        Filename of file to retrieve
	 * @param int       $port            Port to connect to; default: 443
	 * @param int       $timeout         Connection timeout in seconds; default: 6
	 *
	 * @return false|string File data as string if file can be read and there is no
	 *         timeout, false if there were errors or the connection timed out
	 *
	 * @throws runtime_exception If data can't be retrieved and no error
	 *         message is returned
	 */
	public function get(string $host, string $directory, string $filename, int $port = 443, int $timeout = 6)
	{
		try
		{
			// Initialize Guzzle client
			$client = $this->create_client($host, $port, $timeout);
		}
		catch (\RuntimeException $exception)
		{
			throw new runtime_exception('HTTP_HANDLER_NOT_FOUND');
		}

		// Set default values for error variables
		$this->error_number = 0;
		$this->error_string = '';

		try
		{
			$response = $client->request('GET', "$directory/$filename");

			// Check if the response status code is 200 (OK)
			if ($response->getStatusCode() == self::OK)
			{
				return $response->getBody()->getContents();
			}
			else
			{
				$this->error_number = $response->getStatusCode();
				throw new runtime_exception('FILE_NOT_FOUND', [$filename]);
			}
		}
		catch (RequestException $exception)
		{
			if ($exception->hasResponse())
			{
				$this->error_number = $exception->getResponse()->getStatusCode();

				if ($this->error_number == self::NOT_FOUND)
				{
					throw new runtime_exception('FILE_NOT_FOUND', [$filename]);
				}
			}
			else
			{
				$this->error_number = self::REQUEST_TIMEOUT;
				throw new runtime_exception('FSOCK_TIMEOUT');
			}

			$this->error_string = utf8_convert_message($exception->getMessage());
			return false;
		}
		catch (runtime_exception $exception)
		{
			// Rethrow runtime_exceptions, only handle unknown cases below
			throw $exception;
		}
		catch (\Throwable $exception)
		{
			$this->error_string = utf8_convert_message($exception->getMessage());
			throw new runtime_exception('FSOCK_DISABLED');
		}
	}

	/**
	 * Get error string
	 *
	 * @return string Error string
	 */
	public function get_error_string(): string
	{
		return $this->error_string;
	}

	/**
	 * Get error number
	 *
	 * @return int Error number
	 */
	public function get_error_number(): int
	{
		return $this->error_number;
	}
}
